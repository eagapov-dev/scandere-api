<?php

namespace Tests\Feature\Admin;

use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSubscriberTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);
    }    public function test_it_lists_all_subscribers()
    {
        Subscriber::factory()->count(5)->create(['unsubscribed_at' => null]);
        Subscriber::factory()->count(3)->create(['unsubscribed_at' => now()]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/subscribers');

        $response->assertOk()
            ->assertJsonStructure([
                'subscribers' => [
                    'data' => [
                        '*' => ['id', 'email', 'first_name', 'last_name', 'source', 'subscribed_at', 'unsubscribed_at'],
                    ],
                ],
                'total_active',
                'total_unsubscribed',
            ]);

        $data = $response->json();
        $this->assertCount(8, $data['subscribers']['data']);
        $this->assertEquals(5, $data['total_active']);
        $this->assertEquals(3, $data['total_unsubscribed']);
    }    public function test_it_requires_admin_to_view_subscribers()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/admin/subscribers');

        $response->assertStatus(403);
    }    public function test_it_requires_authentication()
    {
        $response = $this->getJson('/api/admin/subscribers');

        $response->assertStatus(401);
    }    public function test_it_can_export_subscribers_csv()
    {
        Subscriber::factory()->count(3)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->get('/api/admin/subscribers/export');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=utf-8');
        $response->assertHeader('content-disposition');

        // Check CSV contains headers
        $content = $response->getContent();
        $this->assertStringContainsString('Email', $content);
        $this->assertStringContainsString('First Name', $content);
    }    public function test_it_requires_admin_to_export()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->get('/api/admin/subscribers/export');

        $response->assertStatus(403);
    }    public function test_export_includes_all_subscribers()
    {
        $subscribers = Subscriber::factory()->count(3)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->get('/api/admin/subscribers/export');

        $content = $response->getContent();

        foreach ($subscribers as $subscriber) {
            $this->assertStringContainsString($subscriber->email, $content);
        }
    }    public function test_subscribers_are_paginated()
    {
        Subscriber::factory()->count(35)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/subscribers');

        $response->assertOk()
            ->assertJsonStructure([
                'subscribers' => [
                    'data',
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
                'total_active',
                'total_unsubscribed',
            ]);

        $data = $response->json('subscribers');
        $this->assertEquals(1, $data['current_page']);
        $this->assertGreaterThan(1, $data['last_page']);
    }
}
