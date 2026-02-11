@component('mail::message')
{!! nl2br(e($messageContent)) !!}

---

@component('mail::button', ['url' => config('app.frontend_url') . '/products'])
Browse Our Products
@endcomponent

Thank you for being part of the Scandere AI community!

Best regards,
The Scandere AI Team

@component('mail::subcopy')
You're receiving this email because you subscribed to the Scandere AI newsletter. You can [unsubscribe at any time]({{ $unsubscribeUrl }}) if you no longer wish to receive these emails.
@endcomponent
@endcomponent
