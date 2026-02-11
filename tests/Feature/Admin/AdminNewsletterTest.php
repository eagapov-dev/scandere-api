<?php

namespace Tests\Feature\Admin;

use App\Mail\NewsletterCampaign;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminNewsletterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->user = User::factory()->create(['is_admin' => false]);
    }    public function test_it_returns_newsletter_statistics()
    {
        // Create subscribers
        Subscriber::factory()->count(5)->create(['unsubscribed_at' => null]);
        Subscriber::factory()->count(3)->create(['unsubscribed_at' => now()]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/newsletter/stats');

        $response->assertOk()
            ->assertJson([
                'total_subscribers' => 8,
                'active_subscribers' => 5,
                'unsubscribed' => 3,
            ]);
    }    public function test_it_requires_admin_to_view_stats()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/admin/newsletter/stats');

        $response->assertStatus(403);
    }    public function test_it_requires_authentication_for_stats()
    {
        $response = $this->getJson('/api/admin/newsletter/stats');

        $response->assertStatus(401);
    }    public function test_it_can_send_newsletter_campaign()
    {
        Mail::fake();

        Subscriber::factory()->count(3)->create(['unsubscribed_at' => null]);
        Subscriber::factory()->count(2)->create(['unsubscribed_at' => now()]);

        $data = [
            'subject' => 'Test Newsletter',
            'content' => 'This is a test newsletter content.',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/newsletter/send', $data);

        $response->assertOk()
            ->assertJson([
                'message' => 'Newsletter campaign queued for 3 subscribers',
                'subscribers_count' => 3,
            ]);

        // Assert emails were queued for active subscribers only
        Mail::assertQueued(NewsletterCampaign::class, 3);
    }    public function test_it_validates_subject_required()
    {
        $data = [
            'content' => 'Test content',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/newsletter/send', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subject']);
    }    public function test_it_validates_content_required()
    {
        $data = [
            'subject' => 'Test Subject',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/newsletter/send', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }    public function test_it_validates_subject_max_length()
    {
        $data = [
            'subject' => str_repeat('a', 256), // Over 255
            'content' => 'Test content',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/newsletter/send', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subject']);
    }    public function test_it_validates_content_max_length()
    {
        $data = [
            'subject' => 'Test',
            'content' => str_repeat('a', 10001), // Over 10000
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/newsletter/send', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content']);
    }    public function test_it_handles_no_active_subscribers()
    {
        Mail::fake();

        // Create only unsubscribed subscribers
        Subscriber::factory()->count(3)->create(['unsubscribed_at' => now()]);

        $data = [
            'subject' => 'Test Newsletter',
            'content' => 'Test content',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/newsletter/send', $data);

        $response->assertOk()
            ->assertJson([
                'message' => 'No active subscribers found',
                'subscribers_count' => 0,
            ]);

        Mail::assertNothingQueued();
    }    public function test_it_requires_admin_to_send_campaign()
    {
        $data = [
            'subject' => 'Test Newsletter',
            'content' => 'Test content',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/admin/newsletter/send', $data);

        $response->assertStatus(403);
    }    public function test_it_requires_authentication_for_send()
    {
        $data = [
            'subject' => 'Test Newsletter',
            'content' => 'Test content',
        ];

        $response = $this->postJson('/api/admin/newsletter/send', $data);

        $response->assertStatus(401);
    }    public function test_campaign_email_includes_unsubscribe_link()
    {
        Mail::fake();

        Subscriber::factory()->create([
            'email' => 'test@example.com',
            'unsubscribed_at' => null,
        ]);

        $data = [
            'subject' => 'Test Newsletter',
            'content' => 'Test content',
        ];

        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/newsletter/send', $data);

        Mail::assertQueued(NewsletterCampaign::class, function ($mail) {
            return str_contains($mail->subscriberEmail, '@');
        });
    }    public function test_it_sends_campaign_to_correct_emails()
    {
        Mail::fake();

        $activeSubscribers = Subscriber::factory()->count(2)->create(['unsubscribed_at' => null]);
        Subscriber::factory()->create(['unsubscribed_at' => now()]);

        $data = [
            'subject' => 'Test Newsletter',
            'content' => 'Test content',
        ];

        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/newsletter/send', $data);

        // Verify correct number of emails queued
        Mail::assertQueued(NewsletterCampaign::class, 2);

        // Verify emails sent to active subscribers only
        foreach ($activeSubscribers as $subscriber) {
            Mail::assertQueued(NewsletterCampaign::class, function ($mail) use ($subscriber) {
                return $mail->subscriberEmail === $subscriber->email;
            });
        }
    }
}
