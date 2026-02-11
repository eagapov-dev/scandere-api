<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);
    }    public function test_it_lists_all_orders()
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();

        // Create orders with items
        for ($i = 0; $i < 5; $i++) {
            $order = Order::factory()->create(['user_id' => $customer->id]);
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'price' => 100,
                'quantity' => 1,
            ]);
        }

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/orders');

        $response->assertOk()
            ->assertJsonStructure([
                'orders' => [
                    'data' => [
                        '*' => [
                            'id',
                            'user_id',
                            'total',
                            'status',
                            'payment_id',
                            'paid_at',
                            'created_at',
                            'user',
                            'items',
                        ],
                    ],
                ],
                'total_revenue',
                'total_orders',
            ]);

        $this->assertCount(5, $response->json('orders.data'));
    }    public function test_it_requires_admin_to_view_orders()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/admin/orders');

        $response->assertStatus(403);
    }    public function test_it_requires_authentication()
    {
        $response = $this->getJson('/api/admin/orders');

        $response->assertStatus(401);
    }    public function test_orders_are_paginated()
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();

        for ($i = 0; $i < 35; $i++) {
            $order = Order::factory()->create(['user_id' => $customer->id]);
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'price' => 100,
                'quantity' => 1,
            ]);
        }

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/orders');

        $response->assertOk()
            ->assertJsonStructure([
                'orders' => [
                    'data',
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
                'total_revenue',
                'total_orders',
            ]);

        $data = $response->json('orders');
        $this->assertEquals(1, $data['current_page']);
        $this->assertGreaterThan(1, $data['last_page']);
    }    public function test_orders_include_user_and_items()
    {
        $customer = User::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        $product = Product::factory()->create(['title' => 'Test Product']);

        $order = Order::factory()->create(['user_id' => $customer->id]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'price' => 100,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/orders');

        $response->assertOk();

        $orderData = $response->json('orders.data.0');
        $this->assertEquals('John', $orderData['user']['first_name']);
        $this->assertEquals('Doe', $orderData['user']['last_name']);
        $this->assertEquals('Test Product', $orderData['items'][0]['product']['title']);
    }    public function test_orders_are_sorted_by_latest_first()
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();

        $oldOrder = Order::factory()->create([
            'user_id' => $customer->id,
            'created_at' => now()->subDays(5),
        ]);
        OrderItem::create([
            'order_id' => $oldOrder->id,
            'product_id' => $product->id,
            'price' => 100,
            'quantity' => 1,
        ]);

        $newOrder = Order::factory()->create([
            'user_id' => $customer->id,
            'created_at' => now(),
        ]);
        OrderItem::create([
            'order_id' => $newOrder->id,
            'product_id' => $product->id,
            'price' => 100,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/orders');

        $orders = $response->json('orders.data');
        $this->assertEquals($newOrder->id, $orders[0]['id']);
        $this->assertEquals($oldOrder->id, $orders[1]['id']);
    }    public function test_it_shows_completed_and_pending_orders()
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();

        $completedOrder = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => 'completed',
        ]);
        OrderItem::create([
            'order_id' => $completedOrder->id,
            'product_id' => $product->id,
            'price' => 100,
            'quantity' => 1,
        ]);

        $pendingOrder = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => 'pending',
        ]);
        OrderItem::create([
            'order_id' => $pendingOrder->id,
            'product_id' => $product->id,
            'price' => 100,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/orders');

        $response->assertOk();
        $this->assertCount(2, $response->json('orders.data'));
    }
}
