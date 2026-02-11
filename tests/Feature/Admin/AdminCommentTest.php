<?php

namespace Tests\Feature\Admin;

use App\Mail\CommentApproved;
use App\Models\Comment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminCommentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);
    }    public function test_it_lists_all_comments()
    {
        $product = Product::factory()->create();
        Comment::factory()->count(5)->create([
            'product_id' => $product->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/comments');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'user_id', 'product_id', 'body', 'status', 'created_at', 'user', 'product'],
                ],
            ]);

        $this->assertCount(5, $response->json('data'));
    }    public function test_it_requires_admin_to_view_comments()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/admin/comments');

        $response->assertStatus(403);
    }    public function test_it_can_update_comment()
    {
        $product = Product::factory()->create();
        $comment = Comment::factory()->create([
            'product_id' => $product->id,
            'user_id' => $this->user->id,
            'body' => 'Original comment',
            'answer' => null,
        ]);

        $data = [
            'answer' => 'Thank you for your feedback!',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/admin/comments/{$comment->id}", $data);

        $response->assertOk();

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'answer' => 'Thank you for your feedback!',
        ]);
    }    public function test_it_can_approve_comment()
    {
        Mail::fake();

        $product = Product::factory()->create();
        $comment = Comment::factory()->create([
            'product_id' => $product->id,
            'user_id' => $this->user->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/comments/{$comment->id}/approve");

        $response->assertOk()
            ->assertJson(['message' => 'Comment published.']);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'published',
        ]);

        // Assert email was queued
        Mail::assertQueued(CommentApproved::class, function ($mail) use ($comment) {
            return $mail->comment->id === $comment->id;
        });
    }    public function test_it_can_delete_comment()
    {
        $product = Product::factory()->create();
        $comment = Comment::factory()->create([
            'product_id' => $product->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/admin/comments/{$comment->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }    public function test_it_requires_admin_to_update_comment()
    {
        $product = Product::factory()->create();
        $comment = Comment::factory()->create([
            'product_id' => $product->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/admin/comments/{$comment->id}", []);

        $response->assertStatus(403);
    }    public function test_it_requires_admin_to_approve_comment()
    {
        $product = Product::factory()->create();
        $comment = Comment::factory()->create([
            'product_id' => $product->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/admin/comments/{$comment->id}/approve");

        $response->assertStatus(403);
    }    public function test_it_requires_admin_to_delete_comment()
    {
        $product = Product::factory()->create();
        $comment = Comment::factory()->create([
            'product_id' => $product->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/admin/comments/{$comment->id}");

        $response->assertStatus(403);
    }    public function test_comments_are_paginated()
    {
        $product = Product::factory()->create();
        Comment::factory()->count(35)->create([
            'product_id' => $product->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/comments');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'current_page',
                'last_page',
                'per_page',
                'total',
            ]);

        $data = $response->json();
        $this->assertEquals(1, $data['current_page']);
        $this->assertGreaterThan(1, $data['last_page']);
    }    public function test_comments_include_user_and_product()
    {
        $product = Product::factory()->create(['title' => 'Test Product']);
        $user = User::factory()->create(['first_name' => 'John']);

        Comment::factory()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/comments');

        $response->assertOk();

        $commentData = $response->json('data.0');
        $this->assertEquals('John', $commentData['user']['first_name']);
        $this->assertEquals('Test Product', $commentData['product']['title']);
    }
}
