@component('mail::message')
# Welcome to Scandere AI Store!

Hi {{ $user->name }},

Welcome aboard! We're thrilled to have you join Scandere AI Store, your premier destination for cutting-edge AI tools and digital products.

## Get Started

Your account is now active and ready to use. Here's what you can do:

@component('mail::table')
| Action | Description |
|:-------|:------------|
| 🔍 **Explore Products** | Discover our curated collection of AI tools |
| 💳 **Make a Purchase** | Secure checkout with instant digital delivery |
| 📚 **Access Downloads** | Download your purchases anytime from your dashboard |
| ⭐ **Leave Reviews** | Share your experience and help the community |
@endcomponent

## Featured Products

Check out some of our most popular AI tools and start enhancing your productivity today!

@component('mail::button', ['url' => config('app.frontend_url') . '/products'])
Browse Our Store
@endcomponent

## Need Help?

- **Help Center:** [Visit our FAQ]({{ config('app.frontend_url') }}/help)
- **Contact Support:** {{ config('services.admin.email') }}
- **Account Dashboard:** [Manage your account]({{ config('app.frontend_url') }}/dashboard)

## Stay Updated

Subscribe to our newsletter to receive:
- New product announcements
- Exclusive discounts and promotions
- AI tips and tutorials
- Industry insights

We're here to help you succeed with AI. If you have any questions or need assistance, don't hesitate to reach out!

Best regards,
The Scandere AI Team

@component('mail::subcopy')
You're receiving this email because you created an account at Scandere AI Store. If you didn't create this account, please contact us immediately at {{ config('services.admin.email') }}.
@endcomponent
@endcomponent
