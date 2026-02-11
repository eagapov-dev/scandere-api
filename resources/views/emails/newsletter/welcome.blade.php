@component('mail::message')
# Welcome to Scandere AI Newsletter!

@if($firstName)
Hi {{ $firstName }},
@else
Hello!
@endif

Thank you for subscribing to the Scandere AI newsletter! You've just joined a community of AI enthusiasts and professionals who are passionate about cutting-edge AI tools and digital products.

## What to Expect

@component('mail::table')
| Benefit | Description |
|:--------|:------------|
| 🚀 **New Releases** | Be the first to know about new AI tools and products |
| 💡 **Exclusive Tips** | Get insider tips on maximizing your AI productivity |
| 🎁 **Special Offers** | Receive exclusive discounts and early-bird deals |
| 📚 **AI Insights** | Stay updated with the latest AI trends and tutorials |
@endcomponent

## Explore Our Store

Ready to discover premium AI tools? Check out our featured products:

@component('mail::button', ['url' => config('app.frontend_url') . '/products'])
Browse Products
@endcomponent

## Stay Connected

- **Website:** [scandereai.store]({{ config('app.frontend_url') }})
- **Email:** {{ config('services.admin.email') }}
- **Support:** [Contact Us]({{ config('app.frontend_url') }}/contact)

We're excited to have you on board!

Best regards,
The Scandere AI Team

@component('mail::subcopy')
You're receiving this email because you subscribed to the Scandere AI newsletter. You can [unsubscribe at any time]({{ $unsubscribeUrl }}) if you no longer wish to receive these emails.
@endcomponent
@endcomponent
