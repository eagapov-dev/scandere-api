# Email Notification System - Documentation

## Overview

The Scandere AI Store now has a complete email notification system with 6 different types of emails:

1. **Welcome Email** - Sent after user registration
2. **Password Reset** - Customized password reset notification
3. **Order Confirmation** - Sent after successful payment with download links
4. **Contact Form** - Confirmation to user + notification to admin
5. **Newsletter Welcome** - Sent after newsletter subscription
6. **Comment Notifications** - Confirmation on submission + approval notification

## Architecture

### Email Classes

**Mailables** (located in `app/Mail/`):
- `OrderCompleted.php` - Order confirmation with download links
- `ContactFormReceived.php` - User confirmation for contact form
- `ContactFormAdminNotification.php` - Admin notification for contact form
- `NewsletterWelcome.php` - Newsletter welcome message
- `CommentSubmitted.php` - Comment submission confirmation
- `CommentApproved.php` - Comment approval notification

**Notifications** (located in `app/Notifications/`):
- `WelcomeUser.php` - Welcome email for new users
- `ResetPasswordNotification.php` - Password reset email

### Email Templates

All templates use Laravel's Markdown format and are located in `resources/views/emails/`:

```
emails/
├── layout/
│   └── base.blade.php          # Base HTML layout (not used by markdown, kept for future)
├── orders/
│   └── completed.blade.php      # Order confirmation template
├── contact/
│   ├── confirmation.blade.php   # User confirmation
│   └── admin-notification.blade.php  # Admin notification
├── newsletter/
│   └── welcome.blade.php        # Newsletter welcome
├── comments/
│   ├── submitted.blade.php      # Comment submission
│   └── approved.blade.php       # Comment approval
└── notifications/
    ├── welcome.blade.php        # User welcome
    └── reset-password.blade.php # Password reset
```

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Mail configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@scandereai.store
MAIL_PASSWORD=YOUR_MAILGUN_PASSWORD_HERE
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@scandereai.store"
MAIL_FROM_NAME="Scandere AI"

# Admin emails
ADMIN_EMAIL=admin@scandereai.store
ADMIN_NOTIFICATION_EMAIL=notifications@scandereai.store

# Queue configuration
QUEUE_CONNECTION=database
```

### Services Configuration

The `config/services.php` file includes:

```php
'admin' => [
    'email' => env('ADMIN_EMAIL', 'admin@scandereai.store'),
    'notification_email' => env('ADMIN_NOTIFICATION_EMAIL'),
],
```

## Queue System

### Why Queue?

All emails are queued (not sent immediately) for:
- **Fast response times** - User doesn't wait for email to send
- **Retry mechanism** - 3 automatic retries on failure
- **Error handling** - Failed emails logged without breaking app
- **Better UX** - Instant feedback to users

### Queue Configuration

Each Mailable/Notification implements `ShouldQueue` with:

```php
public $tries = 3;                      // Retry 3 times
public $backoff = [60, 300, 900];       // Wait 1min, 5min, 15min between retries
public $timeout = 120;                  // 2 minute timeout per email
```

### Running the Queue Worker

**Development (local):**

```bash
php artisan queue:work
```

**Production (with Supervisor):**

Create `/etc/supervisor/conf.d/scandere-worker.conf`:

```ini
[program:scandere-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/scandere-api/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/scandere-api/storage/logs/worker.log
stopwaitsecs=3600
```

Start the worker:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start scandere-worker:*
```

Check status:

```bash
sudo supervisorctl status scandere-worker:*
```

### Queue Monitoring

**Check queue jobs:**

```bash
# View jobs table
php artisan tinker
>>> DB::table('jobs')->count()

# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear all jobs
php artisan queue:flush
```

## Integration Points

### 1. User Registration

**File:** `app/Http/Controllers/Auth/AuthController.php:66`

```php
$user = User::create([...$v, 'password' => Hash::make($v['password'])]);
$user->notify(new \App\Notifications\WelcomeUser());
$token = $user->createToken('auth')->plainTextToken;
```

### 2. Password Reset

**File:** `app/Models/User.php`

```php
public function sendPasswordResetNotification($token)
{
    $this->notify(new \App\Notifications\ResetPasswordNotification($token));
}
```

Already integrated with Laravel's `Password::sendResetLink()`.

### 3. Order Completion

**File:** `app/Models/Order.php:20`

```php
public function markAsCompleted(string $paymentId): void
{
    $this->update([...]);

    try {
        \Mail::to($this->user->email)
            ->queue(new \App\Mail\OrderCompleted($this));
    } catch (\Exception $e) {
        \Log::error('Order email failed', [...]);
    }
}
```

### 4. Contact Form

**File:** `app/Http/Controllers/ContactController.php:59`

```php
ContactMessage::create($v);

// User confirmation
\Mail::to($v['email'])
    ->queue(new \App\Mail\ContactFormReceived(...));

// Admin notification
\Mail::to(config('services.admin.notification_email'))
    ->queue(new \App\Mail\ContactFormAdminNotification(...));
```

### 5. Newsletter Subscription

**File:** `app/Http/Controllers/SubscriberController.php:61`

```php
$subscriber = Subscriber::updateOrCreate([...]);

\Mail::to($request->email)
    ->queue(new \App\Mail\NewsletterWelcome(
        $request->email,
        $request->first_name
    ));
```

### 6. Comment Submission

**File:** `app/Http/Controllers/CommentController.php:122`

```php
$comment = $product->comments()->create([...]);

\Mail::to(auth()->user()->email)
    ->queue(new \App\Mail\CommentSubmitted($comment));
```

### 7. Comment Approval

**File:** `app/Http/Controllers/Admin/CommentController.php:124`

```php
$comment->update(['status' => 'published']);

\Mail::to($comment->user->email)
    ->queue(new \App\Mail\CommentApproved($comment));
```

## Testing

### Local Development Setup (Mailtrap)

For local testing, use [Mailtrap](https://mailtrap.io) to catch emails:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
```

### Manual Testing Checklist

- [ ] **User Registration** - Register new account, verify welcome email
- [ ] **Password Reset** - Request password reset, check email with link
- [ ] **Order Completion** - Complete test order, verify email with download links
- [ ] **Contact Form (User)** - Submit contact form, check confirmation email
- [ ] **Contact Form (Admin)** - Verify admin receives notification with reply-to
- [ ] **Newsletter Subscribe** - Subscribe to newsletter, check welcome email
- [ ] **Comment Submit** - Submit comment, verify confirmation email
- [ ] **Comment Approve** - Admin approves comment, user receives notification
- [ ] **Responsive Design** - Check emails on mobile devices
- [ ] **Email Clients** - Test in Gmail, Outlook, Apple Mail
- [ ] **Unsubscribe Link** - Verify unsubscribe link in newsletter emails

### Testing with Artisan Tinker

```php
php artisan tinker

// Test welcome email
$user = User::first();
$user->notify(new \App\Notifications\WelcomeUser());

// Test order email
$order = Order::with(['user', 'items.product'])->first();
Mail::to($order->user->email)->queue(new \App\Mail\OrderCompleted($order));

// Test contact form
Mail::to('test@example.com')->queue(new \App\Mail\ContactFormReceived('John Doe', 'test@example.com', 'Test message'));

// Test newsletter
Mail::to('test@example.com')->queue(new \App\Mail\NewsletterWelcome('test@example.com', 'John'));

// Check queue
DB::table('jobs')->count();
```

### Feature Testing Example

```php
// tests/Feature/EmailTest.php

use Illuminate\Support\Facades\Mail;
use App\Mail\OrderCompleted;

public function test_order_completion_sends_email()
{
    Mail::fake();

    $order = Order::factory()->create();
    $order->markAsCompleted('test_payment_123');

    Mail::assertQueued(OrderCompleted::class, function ($mail) use ($order) {
        return $mail->order->id === $order->id;
    });
}
```

## Production Deployment

### Pre-deployment Checklist

- [ ] Get Mailgun credentials from Mailgun dashboard
- [ ] Update `.env` with production MAIL_USERNAME and MAIL_PASSWORD
- [ ] Set ADMIN_EMAIL and ADMIN_NOTIFICATION_EMAIL
- [ ] Set QUEUE_CONNECTION=database
- [ ] Run migrations: `php artisan migrate`
- [ ] Configure Supervisor for queue worker
- [ ] Test email delivery on staging

### Deployment Steps

1. **Update production `.env`:**

```bash
MAIL_USERNAME=postmaster@scandereai.store
MAIL_PASSWORD=your_secure_mailgun_password
QUEUE_CONNECTION=database
ADMIN_EMAIL=admin@scandereai.store
ADMIN_NOTIFICATION_EMAIL=notifications@scandereai.store
```

2. **Run migrations:**

```bash
php artisan migrate --force
```

3. **Start queue worker:**

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start scandere-worker:*
```

4. **Verify worker is running:**

```bash
sudo supervisorctl status
```

5. **Test with a real email:**

```bash
php artisan tinker
>>> Mail::to('your-email@example.com')->queue(new \App\Mail\NewsletterWelcome('your-email@example.com', 'Test'));
```

6. **Monitor logs:**

```bash
tail -f storage/logs/laravel.log
tail -f storage/logs/worker.log
```

### Post-deployment Verification

- [ ] Queue worker is running (`supervisorctl status`)
- [ ] Test each email type sends successfully
- [ ] Check `failed_jobs` table is empty
- [ ] Verify emails arrive in inbox (not spam)
- [ ] Test unsubscribe links work
- [ ] Monitor Mailgun dashboard for delivery stats

## Troubleshooting

### Emails Not Sending

**Check queue worker is running:**

```bash
sudo supervisorctl status scandere-worker:*
```

**Check for failed jobs:**

```bash
php artisan queue:failed
```

**Check logs:**

```bash
tail -f storage/logs/laravel.log
grep -i "mail\|email" storage/logs/laravel.log
```

**Manually retry failed jobs:**

```bash
php artisan queue:retry all
```

### Queue Worker Stopped

**Restart worker:**

```bash
sudo supervisorctl restart scandere-worker:*
```

**Check worker logs:**

```bash
tail -f storage/logs/worker.log
```

### Emails Going to Spam

**Check SPF/DKIM records:**
- Verify Mailgun DNS records are configured
- Add SPF and DKIM records to your domain

**Check email content:**
- Avoid spam trigger words
- Include unsubscribe link
- Use proper from address

### High Email Volume

**Increase queue workers:**

Edit Supervisor config, increase `numprocs`:

```ini
numprocs=4  # Increase from 2 to 4
```

Then:

```bash
sudo supervisorctl reread
sudo supervisorctl update
```

## Maintenance

### Weekly Tasks

- [ ] Review failed jobs: `php artisan queue:failed`
- [ ] Check queue size: `DB::table('jobs')->count()`
- [ ] Monitor email delivery rates in Mailgun
- [ ] Check worker logs for errors

### Monthly Tasks

- [ ] Review unsubscribe rates
- [ ] Update email templates if needed
- [ ] Check Mailgun quota/usage
- [ ] Audit email content for improvements

### Security

- **Never commit credentials** - Keep `.env` file secure
- **Rotate passwords** - Change Mailgun password quarterly
- **Monitor failed logins** - Check Mailgun for unauthorized access
- **Sanitize content** - All user input is escaped in templates
- **Validate emails** - All email addresses validated before sending

## Email Service Helper

Located at `app/Services/EmailService.php`, provides utility methods:

```php
$emailService = new \App\Services\EmailService();

// Generate signed download URL (7 days)
$url = $emailService->generateDownloadUrl($product, $user);

// Get admin email
$adminEmail = $emailService->getAdminEmail();

// Format unsubscribe URL
$unsubUrl = $emailService->formatUnsubscribeUrl('user@example.com');

// Get product URL
$productUrl = $emailService->getProductUrl($product->slug);
```

## Future Enhancements

### Phase 2 (Post-MVP)

1. **Email Preferences** - Let users choose which emails to receive
2. **Email Templates Admin Panel** - Edit templates from admin dashboard
3. **Email Analytics** - Track open rates, click rates
4. **Newsletter Campaigns** - Send bulk newsletters to subscribers
5. **Email History** - Track all emails sent to users
6. **A/B Testing** - Test different email subject lines/content
7. **Scheduled Emails** - Send emails at specific times
8. **Email Templates** - Multiple template designs
9. **Attachment Support** - Attach PDFs, invoices, etc.
10. **Multi-language** - Send emails in user's preferred language

## Support

For issues or questions:

- **Email:** admin@scandereai.store
- **Logs:** Check `storage/logs/laravel.log`
- **Queue:** Monitor `jobs` and `failed_jobs` tables
- **Mailgun:** Check [Mailgun Dashboard](https://app.mailgun.com)

---

**Last Updated:** February 11, 2026
**Version:** 1.0.0
**Maintained by:** Scandere AI Development Team
