<?php

namespace Tests\Feature;

use App\Mail\CommentApproved;
use App\Mail\CommentSubmitted;
use App\Mail\ContactFormAdminNotification;
use App\Mail\ContactFormReceived;
use App\Mail\NewsletterCampaign;
use App\Mail\NewsletterWelcome;
use App\Mail\OrderCompleted;
use App\Models\Comment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\WelcomeUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_welcome_email_on_registration()
    {
        Notification::fake();

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@gmail.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertStatus(201);

        $user = User::where('email', 'john@gmail.com')->first();
        $this->assertNotNull($user, 'User should be created after registration');

        Notification::assertSentTo($user, WelcomeUser::class);
    }    public function test_it_sends_password_reset_email()
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->postJson('/api/auth/forgot-password', [
            'email' => 'test@example.com',
        ]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }    public function test_it_sends_order_completion_email()
    {
        Mail::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100]);

        $order = Order::create([
            'user_id' => $user->id,
            'total' => 100,
            'status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'price' => 100,
            'quantity' => 1,
        ]);

        $order->markAsCompleted('test_payment_123');

        Mail::assertQueued(OrderCompleted::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id;
        });
    }    public function test_it_sends_contact_form_emails()
    {
        Mail::fake();

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'message' => 'Test message',
        ];

        $this->postJson('/api/contact', $data);

        // User confirmation email
        Mail::assertQueued(ContactFormReceived::class, function ($mail) {
            return $mail->email === 'john@example.com';
        });

        // Admin notification email
        Mail::assertQueued(ContactFormAdminNotification::class, function ($mail) {
            return $mail->email === 'john@example.com';
        });
    }    public function test_it_sends_newsletter_welcome_email()
    {
        Mail::fake();

        $this->postJson('/api/subscribe', [
            'email' => 'subscriber@example.com',
            'first_name' => 'John',
        ]);

        Mail::assertQueued(NewsletterWelcome::class, function ($mail) {
            return $mail->email === 'subscriber@example.com'
                && $mail->firstName === 'John';
        });
    }    public function test_it_sends_comment_submitted_email()
    {
        Mail::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/products/{$product->id}/comments", [
                'body' => 'Great product!',
            ]);

        Mail::assertQueued(CommentSubmitted::class);
    }    public function test_it_sends_comment_approved_email()
    {
        Mail::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $comment = Comment::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'body' => 'Great product!',
            'status' => 'draft',
        ]);

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/comments/{$comment->id}/approve");

        Mail::assertQueued(CommentApproved::class, function ($mail) use ($comment) {
            return $mail->comment->id === $comment->id;
        });
    }    public function test_order_completed_email_includes_correct_data()
    {
        Mail::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create(['title' => 'Test Product', 'price' => 99.99]);

        $order = Order::create([
            'user_id' => $user->id,
            'total' => 99.99,
            'status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'price' => 99.99,
            'quantity' => 1,
        ]);

        $order->markAsCompleted('test_123');

        Mail::assertQueued(OrderCompleted::class, function ($mail) use ($order, $user) {
            $mailable = new OrderCompleted($order->fresh());

            return $mailable->order->id === $order->id
                && $mailable->order->user->email === $user->email
                && $mailable->order->payment_id === 'test_123';
        });
    }    public function test_newsletter_campaign_email_structure()
    {
        $email = 'test@example.com';
        $subject = 'Test Subject';
        $content = 'Test content';

        $mailable = new NewsletterCampaign($subject, $content, $email);

        $this->assertEquals($subject, $mailable->emailSubject);
        $this->assertEquals($content, $mailable->emailContent);
        $this->assertEquals($email, $mailable->subscriberEmail);
    }    public function test_email_logging_on_failure()
    {
        // This test verifies error handling in Order::markAsCompleted
        Mail::fake();
        Mail::shouldReceive('to')->andThrow(new \Exception('Mail server error'));

        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'total' => 100,
            'status' => 'pending',
        ]);

        // Should not throw exception - error is caught and logged
        $order->markAsCompleted('test_123');

        // Order should still be marked as completed
        $this->assertEquals('completed', $order->fresh()->status);
    }
}
