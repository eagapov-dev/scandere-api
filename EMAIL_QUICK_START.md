# Email System - Quick Start Guide

## Setup in 5 Minutes

### 1. Get Mailgun Credentials

1. Go to [Mailgun Dashboard](https://app.mailgun.com)
2. Navigate to **Sending** → **Domain Settings** → **SMTP Credentials**
3. Copy your SMTP username and password

### 2. Update .env File

```env
MAIL_USERNAME=postmaster@scandereai.store
MAIL_PASSWORD=YOUR_MAILGUN_PASSWORD
ADMIN_EMAIL=admin@scandereai.store
ADMIN_NOTIFICATION_EMAIL=notifications@scandereai.store
QUEUE_CONNECTION=database
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Start Queue Worker

**Local Development:**

```bash
php artisan queue:work
```

**Production (Supervisor):**

```bash
sudo supervisorctl start scandere-worker:*
```

### 5. Test It!

```bash
php artisan tinker
>>> $user = User::first();
>>> $user->notify(new \App\Notifications\WelcomeUser());
>>> exit
```

Check your queue worker console - you should see the job processing!

## Отправка рассылок через админку

**Самый простой способ!** 🎨

1. Откройте `/admin/newsletter` в админ-панели
2. Заполните тему и текст письма
3. Нажмите "Send Campaign"
4. Готово!

Подробная инструкция: `scandere-frontend/NEWSLETTER_ADMIN_GUIDE.md`

## Email Types

| Email | Trigger | Sent To |
|-------|---------|---------|
| Welcome | User registers | New user |
| Password Reset | User requests reset | User |
| Order Confirmation | Order completed | Buyer |
| Contact Confirmation | Contact form submitted | User |
| Contact Notification | Contact form submitted | Admin |
| Newsletter Welcome | Newsletter subscription | Subscriber |
| Comment Submitted | User submits comment | User |
| Comment Approved | Admin approves comment | User |

## Common Commands

```bash
# Check queue status
php artisan queue:work

# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear all jobs
php artisan queue:flush

# Restart queue worker (production)
php artisan queue:restart
```

## Troubleshooting

**Emails not sending?**
- Check queue worker is running
- Check `.env` has correct MAIL_USERNAME and MAIL_PASSWORD
- Check `failed_jobs` table: `php artisan queue:failed`

**Queue worker stopped?**
- Restart: `php artisan queue:restart`
- Or (Supervisor): `sudo supervisorctl restart scandere-worker:*`

**How to test locally?**
- Use [Mailtrap.io](https://mailtrap.io) for development
- Update `.env` with Mailtrap credentials
- All emails will be caught in Mailtrap inbox

## Files Changed

### New Files Created

**Mailables:**
- `app/Mail/OrderCompleted.php`
- `app/Mail/ContactFormReceived.php`
- `app/Mail/ContactFormAdminNotification.php`
- `app/Mail/NewsletterWelcome.php`
- `app/Mail/CommentSubmitted.php`
- `app/Mail/CommentApproved.php`

**Notifications:**
- `app/Notifications/WelcomeUser.php`
- `app/Notifications/ResetPasswordNotification.php`

**Email Templates:**
- `resources/views/emails/orders/completed.blade.php`
- `resources/views/emails/contact/confirmation.blade.php`
- `resources/views/emails/contact/admin-notification.blade.php`
- `resources/views/emails/newsletter/welcome.blade.php`
- `resources/views/emails/comments/submitted.blade.php`
- `resources/views/emails/comments/approved.blade.php`
- `resources/views/emails/notifications/welcome.blade.php`
- `resources/views/emails/notifications/reset-password.blade.php`
- `resources/views/emails/layout/base.blade.php`

**Service:**
- `app/Services/EmailService.php`

**Migrations:**
- `database/migrations/xxxx_create_jobs_table.php`
- `database/migrations/xxxx_create_failed_jobs_table.php`

### Modified Files

**Models:**
- `app/Models/Order.php` - Added email dispatch in `markAsCompleted()`
- `app/Models/User.php` - Added `sendPasswordResetNotification()`

**Controllers:**
- `app/Http/Controllers/Auth/AuthController.php` - Added welcome email on registration
- `app/Http/Controllers/ContactController.php` - Added email dispatch
- `app/Http/Controllers/SubscriberController.php` - Added newsletter welcome
- `app/Http/Controllers/CommentController.php` - Added comment submitted email
- `app/Http/Controllers/Admin/CommentController.php` - Added comment approved email

**Configuration:**
- `config/services.php` - Added admin email configuration
- `.env` - Added email and queue settings

## Next Steps

1. **Test all email types** - Go through the checklist in `EMAIL_SYSTEM_DOCUMENTATION.md`
2. **Set up monitoring** - Monitor `failed_jobs` table and logs
3. **Configure DNS** - Set up SPF/DKIM records with Mailgun
4. **Customize templates** - Update email content to match your brand
5. **Production deployment** - Set up Supervisor for queue worker

## Need Help?

See the full documentation: `EMAIL_SYSTEM_DOCUMENTATION.md`

---

**Quick Links:**
- [Mailgun Dashboard](https://app.mailgun.com)
- [Mailtrap (Dev Testing)](https://mailtrap.io)
- [Laravel Queue Docs](https://laravel.com/docs/queues)
- [Laravel Mail Docs](https://laravel.com/docs/mail)
