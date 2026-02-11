@component('mail::message')
# Thank You for Contacting Us

Hi {{ $name }},

We've received your message and one of our team members will get back to you within 24-48 hours.

## Your Message

@component('mail::panel')
{{ $message }}
@endcomponent

## What Happens Next?

1. Our support team will review your message
2. We'll respond to {{ $email }} within 1-2 business days
3. You'll receive a personalized response addressing your inquiry

@component('mail::button', ['url' => config('app.frontend_url')])
Visit Our Store
@endcomponent

If you have any urgent questions, you can also reach us directly at {{ config('services.admin.email') }}.

Thank you for your patience!

Best regards,
The Scandere AI Team

@component('mail::subcopy')
This is an automated confirmation. Please do not reply to this email. We will respond to your message at {{ $email }}.
@endcomponent
@endcomponent
