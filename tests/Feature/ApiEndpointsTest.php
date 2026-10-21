<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $viewer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'email' => 'admin@tailorpro.local',
            'name' => 'Admin Manager',
            'password' => bcrypt('password123'),
        ]);
        $this->admin->userRoles()->create(['role' => 'admin']);

        $this->viewer = User::create([
            'email' => 'viewer@tailorpro.local',
            'name' => 'Tailor Viewer',
            'password' => bcrypt('password123'),
        ]);
        $this->viewer->userRoles()->create(['role' => 'viewer']);
    }

    public function test_auth_login_returns_token_and_user_profile(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@tailorpro.local',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'email', 'name', 'role'],
                'token',
            ])
            ->assertJsonPath('user.role', 'admin');
    }

    public function test_admin_can_create_and_fetch_orders_via_api(): void
    {
        Sanctum::actingAs($this->admin);

        $customer = Customer::create([
            'name' => 'Squadron Leader Rafiq',
            'phone' => '01999888777',
            'bd_number' => 'BD/8821',
        ]);

        $payload = [
            'customer_id' => $customer->id,
            'bd_number' => $customer->bd_number,
            'order_category' => 'officer',
            'order_date' => now()->toDateString(),
            'delivery_date' => now()->addDays(7)->toDateString(),
            'total_amount' => 5000,
            'advance' => 2000,
            'due' => 3000,
            'measurement_notes' => 'Urgent ceremonial delivery',
            'items' => [
                [
                    'item_type' => 'pant',
                    'item_name' => 'Pant (Uniform)',
                    'measurements' => ['length' => '39', 'waist' => '34', 'hip' => '40'],
                    'notes' => 'Side pocket with zip',
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('bd_number', 'BD/8821')
            ->assertJsonPath('total_amount', '5000.00')
            ->assertJsonPath('due', '3000.00');

        $this->assertDatabaseHas('orders', ['bd_number' => 'BD/8821']);
        $this->assertDatabaseHas('order_items', ['item_name' => 'Pant (Uniform)']);
    }

    public function test_viewer_is_restricted_from_mutating_orders(): void
    {
        Sanctum::actingAs($this->viewer);

        $payload = [
            'bd_number' => 'BD/FORBIDDEN',
            'total_amount' => 1000,
            'due' => 1000,
            'items' => [['item_type' => 'shirt']],
        ];

        // Viewer should receive 403 Forbidden on write operations
        $response = $this->postJson('/api/orders', $payload);
        $response->assertStatus(403);
    }

    public function test_dashboard_metrics_api(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/dashboard/metrics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_orders',
                'total_revenue',
                'total_due',
                'total_advance',
                'total_customers',
                'officer_orders',
                'airman_orders',
            ]);
    }
}
