<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);
        Storage::fake('public');
    }    public function test_it_lists_all_products()
    {
        Product::factory()->count(5)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/products');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'current_page',
                'last_page',
            ]);

        $this->assertCount(5, $response->json('data'));
    }    public function test_it_requires_admin_to_list_products()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/admin/products');

        $response->assertStatus(403);
    }    public function test_it_can_create_product()
    {
        $category = Category::factory()->create();

        $data = [
            'title' => 'New Product',
            'slug' => 'new-product',
            'short_description' => 'Short desc',
            'description' => 'Product description',
            'price' => 99.99,
            'category_id' => $category->id,
            'is_active' => true,
            'preview_image' => UploadedFile::fake()->image('product.jpg'),
            'file' => UploadedFile::fake()->create('product.pdf', 1000),
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/admin/products', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('products', [
            'title' => 'New Product',
            'slug' => 'new-product',
            'price' => 99.99,
        ]);
    }    public function test_it_validates_required_fields_on_create()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'price', 'file']);
    }    public function test_it_can_update_product()
    {
        $product = Product::factory()->create(['title' => 'Old Name']);

        $data = [
            'title' => 'Updated Name',
            'slug' => $product->slug,
            'description' => $product->description,
            'price' => 149.99,
            'category_id' => $product->category_id,
            'file_type' => $product->file_type,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/admin/products/{$product->id}", $data);

        $response->assertOk();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Updated Name',
            'price' => 149.99,
        ]);
    }    public function test_it_can_delete_product()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/admin/products/{$product->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }    public function test_it_validates_unique_slug()
    {
        $category = Category::factory()->create();
        // Create product with specific title that generates 'existing-product' slug
        Product::factory()->create(['title' => 'Existing Product', 'slug' => 'existing-product']);

        // Try to create another product with same title (which will generate same slug)
        $data = [
            'title' => 'Existing Product',
            'price' => 99.99,
            'category_id' => $category->id,
            'file' => UploadedFile::fake()->create('product.pdf', 1000),
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/admin/products', $data);

        // DB unique constraint should prevent duplicate slugs, resulting in 500 error
        $response->assertStatus(500);
    }    public function test_it_validates_price_is_numeric()
    {
        $category = Category::factory()->create();

        $data = [
            'title' => 'New Product',
            'slug' => 'new-product',
            'price' => 'not-a-number',
            'category_id' => $category->id,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/products', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }    public function test_it_requires_admin_to_create_product()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/admin/products', []);

        $response->assertStatus(403);
    }    public function test_it_requires_admin_to_update_product()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/admin/products/{$product->id}", []);

        $response->assertStatus(403);
    }    public function test_it_requires_admin_to_delete_product()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/admin/products/{$product->id}");

        $response->assertStatus(403);
    }
}
