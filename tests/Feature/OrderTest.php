<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_checkout(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 29.99]);
        $token = $user->createToken('test-token')->plainTextToken;

        // Add product to cart
        $user->cart()->create([
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/checkout');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'session_id',
                'checkout_url',
            ]);
    }

    public function test_cannot_checkout_with_empty_cart(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/checkout');

        $response->assertStatus(422);
    }

    public function test_guest_cannot_checkout(): void
    {
        $response = $this->postJson('/api/checkout');

        $response->assertStatus(401);
    }

    public function test_admin_can_list_orders(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
            ]);
    }

    public function test_non_admin_cannot_list_all_orders(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/admin/orders');

        $response->assertStatus(403);
    }
}
