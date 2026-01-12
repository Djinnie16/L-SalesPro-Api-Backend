<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'role' => 'Sales Manager'
        ]);
    }

    public function test_get_dashboard_summary()
    {
        // Create test data
        Order::factory()->count(5)->create([
            'status' => 'delivered',
            'total_amount' => 1000.00
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/dashboard/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_sales',
                    'order_count',
                    'average_order_value',
                    'inventory_turnover_rate'
                ],
                'message'
            ]);
    }

    public function test_dashboard_cache_works()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn([
                'total_sales' => 5000,
                'order_count' => 10,
                'average_order_value' => 500,
                'inventory_turnover_rate' => 2.5
            ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/dashboard/summary');

        $response->assertStatus(200);
    }

    public function test_sales_performance_with_different_periods()
    {
        $periods = ['today', 'week', 'month', 'quarter', 'year'];
        
        foreach ($periods as $period) {
            $response = $this->actingAs($this->user)
                ->getJson("/api/dashboard/sales-performance?period={$period}");
            
            $response->assertStatus(200);
        }
    }

    public function test_top_products_endpoint()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/dashboard/top-products?limit=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message'
            ]);
    }
}