<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_product_comments(): void
    {
        $product = Product::factory()->create();
        Comment::factory()->count(3)->create([
            'product_id' => $product->id,
            'status' => 'published',
        ]);

        $response = $this->getJson("/api/products/{$product->id}/comments");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_only_approved_comments_are_visible(): void
    {
        $product = Product::factory()->create();
        Comment::factory()->count(2)->create([
            'product_id' => $product->id,
            'status' => 'published',
        ]);
        Comment::factory()->create([
            'product_id' => $product->id,
            'status' => 'draft',
        ]);

        $response = $this->getJson("/api/products/{$product->id}/comments");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_authenticated_user_can_post_comment(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/products/{$product->id}/comments", [
                'body' => 'Great product!',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'body' => 'Great product!',
        ]);
    }

    public function test_guest_cannot_post_comment(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson("/api/products/{$product->id}/comments", [
            'body' => 'Great product!',
        ]);

        $response->assertStatus(401);
    }

    public function test_admin_can_approve_comment(): void
    {
        $admin = User::factory()->admin()->create();
        $comment = Comment::factory()->create(['status' => 'draft']);
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson("/api/admin/comments/{$comment->id}/approve");

        $response->assertStatus(200);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'published',
        ]);
    }

    public function test_admin_can_delete_comment(): void
    {
        $admin = User::factory()->admin()->create();
        $comment = Comment::factory()->create();
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson("/api/admin/comments/{$comment->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }
}
