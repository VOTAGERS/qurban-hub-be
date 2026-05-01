<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\ProductWoo;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_process_stores_data_correctly()
    {
        // Setup: Create a product in the database
        $product = ProductWoo::create([
            'name' => 'Sapi Qurban',
            'price' => 5000000,
            'status' => 'A',
        ]);

        $payload = [
            'product_id' => $product->id,
            'quantity' => 1,
            'total_price' => 5000000,
            'billing' => [
                'first_name' => 'John',
                'email' => 'john@example.com',
                'phone' => '081234567890',
            ],
            'shipping' => [
                'first_name' => 'Jane',
                'email' => 'jane@example.com',
                'phone' => '089876543210',
            ],
            'recipients' => [
                [
                    'qurban_name' => 'Participant 1',
                    'email' => 'p1@example.com',
                    'phone_number' => '081111111111',
                    'remarks' => 'None',
                ]
            ]
        ];

        $response = $this->postJson('/api/checkout', $payload);

        $response->assertStatus(201);
        
        // Assert data is in DB
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
        $this->assertDatabaseHas('orders', ['quantity' => 1]);
        $this->assertDatabaseHas('billings', []);
        $this->assertDatabaseHas('shippings', []);
        $this->assertDatabaseHas('order_participants', ['qurban_name' => 'Participant 1']);
        $this->assertDatabaseHas('apps_log', []);
    }
}
