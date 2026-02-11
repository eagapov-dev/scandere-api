<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            ->assertJsonStructure([
                'product' => ['id', 'title', 'price'],
                'has_purchased',
                'comments',
                'related'
            ])
            ->assertJson([
                'product' => [
                    'id' => $product->id,
                    'title' => $product->title,
                ],
                'has_purchased' => false,
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
        Product::factory()->count(2)->create(['show_on_homepage' => false]);

        $response = $this->getJson('/api/featured');

        $response->assertStatus(200)
            ->assertJsonStructure(['products', 'bundles']);
    }

    public function test_can_list_categories(): void
    {
        Category::factory()->count(5)->create();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonCount(5)
            ->assertJsonStructure([
                '*' => ['id', 'name', 'slug'],
            ]);
    }

    public function test_admin_can_create_product(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/admin/products', [
                'title' => 'New Product',
                'slug' => 'new-product',
                'description' => 'Product description',
                'price' => 29.99,
                'category_id' => $category->id,
                'file' => UploadedFile::fake()->create('product.zip', 1000),
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'title' => 'New Product',
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

        $response->assertStatus(200);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}
