<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentHistory;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SystemAuditFixesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'email' => 'admin@tailorpro.local',
            'name' => 'Super Tailor Admin',
            'password' => bcrypt('secret123'),
        ]);
        $this->admin->userRoles()->create(['role' => 'admin']);
    }

    public function test_user_model_saves_name_attribute(): void
    {
        $this->assertEquals('Super Tailor Admin', $this->admin->name);
        $this->assertEquals('admin', $this->admin->role);
        $this->assertTrue($this->admin->hasRole('admin'));
    }

    public function test_customer_cannot_be_deleted_if_they_have_active_orders(): void
    {
        Sanctum::actingAs($this->admin);

        $customer = Customer::create([
            'name' => 'Group Captain Tariq',
            'phone' => '01711223344',
            'bd_number' => 'BD/7788',
        ]);

        Order::create([
            'order_number' => 2001,
            'customer_id' => $customer->id,
            'bd_number' => $customer->bd_number,
            'total_amount' => 3500,
            'due' => 1500,
            'order_category' => 'officer',
        ]);

        $response = $this->deleteJson('/api/customers/' . $customer->id);

        $response->assertStatus(400)
            ->assertJsonPath('message', 'Cannot delete customer with existing orders.');

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_customer_without_orders_can_be_safely_deleted(): void
    {
        Sanctum::actingAs($this->admin);

        $customer = Customer::create([
            'name' => 'Wing Commander Farooq',
            'phone' => '01888999000',
            'bd_number' => 'BD/9900',
        ]);

        $response = $this->deleteJson('/api/customers/' . $customer->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }

    public function test_customer_financial_reconciliation_syncs_total_orders_spent_and_due(): void
    {
        Sanctum::actingAs($this->admin);

        $customer = Customer::create([
            'name' => 'Flight Lieutenant Jamal',
            'phone' => '01555666777',
            'bd_number' => 'BD/3344',
        ]);

        // Create Order 1
        $this->postJson('/api/orders', [
            'customer_id' => $customer->id,
            'bd_number' => $customer->bd_number,
            'order_category' => 'officer',
            'total_amount' => 4000,
            'advance' => 1000,
            'due' => 3000,
            'items' => [
                ['item_type' => 'shirt', 'item_name' => 'Uniform Shirt'],
            ],
        ])->assertStatus(201);

        // Create Order 2
        $this->postJson('/api/orders', [
            'customer_id' => $customer->id,
            'bd_number' => $customer->bd_number,
            'order_category' => 'officer',
            'total_amount' => 2500,
            'advance' => 500,
            'due' => 2000,
            'items' => [
                ['item_type' => 'pant', 'item_name' => 'Uniform Pant'],
            ],
        ])->assertStatus(201);

        $customer->refresh();

        $this->assertEquals(2, $customer->total_orders);
        $this->assertEquals('6500.00', $customer->total_spent);
        $this->assertEquals('5000.00', $customer->due_amount);
    }

    public function test_worker_entry_bulk_upsert_skips_blank_rows(): void
    {
        Sanctum::actingAs($this->admin);

        $worker = Worker::create([
            'name_bn' => 'করিম দর্জি',
            'name_en' => 'Karim Tailor',
        ]);

        $payload = [
            'worker_id' => $worker->id,
            'entries' => [
                [
                    'serial' => 1,
                    'name' => 'Wing Commander Arif',
                    'item' => 'Coat',
                    'quantity' => 1,
                    'unit_price' => 500,
                    'total_taka' => 500,
                    'khoroch' => 0,
                ],
                // Completely blank row (should be skipped)
                [
                    'serial' => null,
                    'name' => '',
                    'item' => '',
                    'quantity' => 1,
                    'unit_price' => 0,
                    'total_taka' => 0,
                    'khoroch' => 0,
                ],
            ],
        ];

        $response = $this->postJson('/api/worker-entries/bulk-upsert', $payload);
        $response->assertStatus(200);

        // Only 1 real entry should exist in the database
        $this->assertEquals(1, WorkerEntry::where('worker_id', $worker->id)->count());
        $this->assertDatabaseHas('worker_entries', [
            'worker_id' => $worker->id,
            'serial' => 1,
            'name' => 'Wing Commander Arif',
        ]);
    }

    public function test_order_delivery_records_discount_in_payment_history(): void
    {
        Sanctum::actingAs($this->admin);

        $customer = Customer::create([
            'name' => 'Air Commodore Habib',
            'phone' => '01700112233',
            'bd_number' => 'BD/5566',
        ]);

        $order = Order::create([
            'order_number' => 3001,
            'customer_id' => $customer->id,
            'bd_number' => $customer->bd_number,
            'total_amount' => 5000,
            'advance' => 2000,
            'due' => 3000,
            'order_category' => 'officer',
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/orders/' . $order->id . '/deliver', [
            'cash_receive' => 2800,
            'discount' => 200,
            'due' => 0,
            'receiver_name' => 'Self',
        ]);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals('delivered', $order->status->value);
        $this->assertEquals('0.00', $order->due);

        // Check payment history has both cash and discount
        $this->assertDatabaseHas('payment_history', [
            'order_id' => $order->id,
            'amount' => '2800.00',
        ]);
        $this->assertDatabaseHas('payment_history', [
            'order_id' => $order->id,
            'amount' => '200.00',
            'payment_type' => 'discount',
        ]);
    }

    public function test_next_order_number_respects_running_year(): void
    {
        Sanctum::actingAs($this->admin);

        $customer = Customer::create([
            'name' => 'Sergeant Mizan',
            'phone' => '01811223344',
            'bd_number' => 'AIR/001',
        ]);

        Order::create([
            'order_number' => 10,
            'customer_id' => $customer->id,
            'bd_number' => 'AIR/001',
            'order_category' => 'airman',
            'running_year' => '2025-2026',
        ]);

        Order::create([
            'order_number' => 50,
            'customer_id' => $customer->id,
            'bd_number' => 'AIR/002',
            'order_category' => 'airman',
            'running_year' => '2026-2027',
        ]);

        // Next number for 2025-2026 should be 11
        $res1 = $this->getJson('/api/orders/next-number?category=airman&running_year=2025-2026');
        $res1->assertStatus(200)->assertJsonPath('next_number', 11);

        // Next number for 2026-2027 should be 51
        $res2 = $this->getJson('/api/orders/next-number?category=airman&running_year=2026-2027');
        $res2->assertStatus(200)->assertJsonPath('next_number', 51);
    }
}
