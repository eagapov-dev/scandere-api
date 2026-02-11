<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_cart(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/cart');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'items',
                'subtotal',
                'bundle',
                'bundle_savings',
            ]);
    }

    public function test_authenticated_user_can_add_product_to_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/cart/add', [
                'product_id' => $product->id,
                'quantity' => 1,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_authenticated_user_can_remove_product_from_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Add to cart first
        $user->cart()->create([
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/cart/{$product->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_authenticated_user_can_clear_cart(): void
    {
        $user = User::factory()->create();
        $products = Product::factory()->count(3)->create();
        $token = $user->createToken('test-token')->plainTextToken;

        foreach ($products as $product) {
            $user->cart()->create([
                'product_id' => $product->id,
                'quantity' => 1,
            ]);
        }

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/cart');

        $response->assertStatus(200);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_guest_cannot_access_cart(): void
    {
        $response = $this->getJson('/api/cart');

        $response->assertStatus(401);
    }
}
