<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Models\Worker;
use App\Services\FinancialReconciliationService;
use App\Services\OrderSequenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderBusinessLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_customer_and_order_with_measurements(): void
    {
        $customer = Customer::create([
            'name' => 'Commander Tariqul Islam',
            'phone' => '01811223344',
            'bd_number' => 'BD/7701',
        ]);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'bd_number' => 'BD/7701',
        ]);

        $sequenceService = new OrderSequenceService();
        $nextNum = $sequenceService->getNextOrderNumber('officer', '2026-2027');
        $this->assertGreaterThan(0, $nextNum);

        $order = Order::create([
            'order_number' => $nextNum,
            'customer_id' => $customer->id,
            'bd_number' => $customer->bd_number,
            'order_category' => 'officer',
            'order_date' => now()->toDateString(),
            'total_amount' => 4500.00,
            'advance' => 1500.00,
            'due' => 3000.00,
            'status' => 'pending',
            'branch' => 'Dhaka Cantonment',
        ]);

        $item = $order->items()->create([
            'item_type' => 'shirt',
            'item_name' => 'Battle Uniform',
            'measurements' => [
                'length' => '30.5',
                'chest' => '42',
                'belly' => '38',
                'shoulder' => '18',
                'sleeve' => '24.5',
                'collar' => '16',
            ],
            'notes' => 'Air Force badge on left sleeve',
        ]);

        $this->assertDatabaseHas('order_items', [
            'id' => $item->id,
            'order_id' => $order->id,
            'item_name' => 'Battle Uniform',
        ]);

        // Test financial due calculation
        $financialService = new FinancialReconciliationService();
        $due = $financialService->recalculateCustomerDue($customer->id);

        $this->assertEquals(3000.00, $due);
        $this->assertEquals(3000.00, (float) $customer->fresh()->due_amount);

        // Test payment recording
        $payment = $financialService->recordPayment($order, 1000.00, 'partial', 'Received by Cash');
        $this->assertEquals(2000.00, (float) $order->fresh()->due);
        $this->assertEquals(2000.00, (float) $customer->fresh()->due_amount);
    }

    public function test_role_based_access_control(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@tailorpro.local'],
            ['name' => 'Admin User', 'password' => bcrypt('secret123')]
        );
        $admin->userRoles()->firstOrCreate(['role' => 'admin']);

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertEquals('admin', $admin->role);

        $viewer = User::firstOrCreate(
            ['email' => 'viewer@tailorpro.local'],
            ['name' => 'Viewer User', 'password' => bcrypt('secret123')]
        );
        $viewer->userRoles()->firstOrCreate(['role' => 'viewer']);

        $this->assertTrue($viewer->hasRole('viewer'));
        $this->assertFalse($viewer->hasRole('admin'));
        $this->assertEquals('viewer', $viewer->role);
    }
}
