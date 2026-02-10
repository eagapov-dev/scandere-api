<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products(): void
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'slug', 'price'],
                ],
            ]);
    }

    public function test_can_view_single_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->slug}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $product->id,
                'title' => $product->title,
                'price' => (string) $product->price,
            ]);
    }

    public function test_can_filter_products_by_category(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);
        Product::factory()->count(2)->create();

        $response = $this->getJson("/api/products?category={$category->slug}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_list_featured_products(): void
    {
        Product::factory()->featured()->count(3)->create();
        Product::factory()->count(2)->create(['is_featured' => false]);

        $response = $this->getJson('/api/featured');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_list_categories(): void
    {
        Category::factory()->count(5)->create();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonCount(5)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'slug', 'description'],
            ]);
    }

    public function test_admin_can_create_product(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/admin/products', [
                'title' => 'New Product',
                'description' => 'Product description',
                'price' => 29.99,
                'category_id' => $category->id,
                'preview_image' => 'https://example.com/image.jpg',
                'file_path' => 'files/test.zip',
                'file_name' => 'test.zip',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'title' => 'New Product',
                'price' => '29.99',
            ]);

        $this->assertDatabaseHas('products', [
            'title' => 'New Product',
        ]);
    }

    public function test_non_admin_cannot_create_product(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/admin/products', [
                'title' => 'New Product',
                'category_id' => $category->id,
                'price' => 29.99,
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/admin/products/{$product->id}", [
                'title' => 'Updated Product',
                'description' => $product->description,
                'price' => 49.99,
                'category_id' => $product->category_id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'title' => 'Updated Product',
                'price' => '49.99',
            ]);
    }

    public function test_admin_can_delete_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/admin/products/{$product->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}
