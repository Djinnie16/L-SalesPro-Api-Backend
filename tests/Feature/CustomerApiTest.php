<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use Laravel\Sanctum\Sanctum;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_customers(): void
    {
        $user = User::factory()->create(['role' => 'Sales Representative']);
        Sanctum::actingAs($user);
        Customer::factory(5)->create();

        $response = $this->getJson('/api/v1/customers');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['data', 'meta'], 'message']);
    }

    public function test_manager_can_create_customer(): void
    {
        $user = User::factory()->create(['role' => 'Sales Manager']);
        Sanctum::actingAs($user);

        $data = [
            'name' => 'Test Customer',
            'type' => 'Garage',
            'category' => 'A',
            'contact_person' => 'Test Person',
            'phone' => '+254-000-000000',
            'email' => 'test@example.com',
            'tax_id' => 'P000000000T',
            'payment_terms' => 30,
            'credit_limit' => 100000,
            'territory' => 'Test Region',
        ];

        $response = $this->postJson('/api/v1/customers', $data);

        $response->assertStatus(201)
            ->assertJson(['success' => true, 'message' => 'Customer created successfully']);
        $this->assertDatabaseHas('customers', ['email' => 'test@example.com']);
    }

}