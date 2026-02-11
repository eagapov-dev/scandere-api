@component('mail::message')
# Thank You for Your Review!

Hi {{ $comment->user->name }},

Thank you for taking the time to share your thoughts about **{{ $product->name }}**. We truly appreciate your feedback!

## Your Review

**Product:** {{ $product->name }}
**Rating:** {{ $comment->rating }} / 5 stars
**Submitted:** {{ $comment->created_at->format('F j, Y') }}

@component('mail::panel')
{{ $comment->content }}
@endcomponent

## What Happens Next?

Your review is currently under moderation. Our team will review it shortly to ensure it meets our community guidelines. You'll receive another email once your review is approved and published.

**Typical Review Time:** 24-48 hours

@component('mail::button', ['url' => config('app.frontend_url') . '/products/' . $product->slug])
View {{ $product->name }}
@endcomponent

## Why Reviews Matter

Your honest feedback helps other customers make informed decisions and helps us improve our products and services.

Thank you for being part of the Scandere AI community!

Best regards,
The Scandere AI Team

@component('mail::subcopy')
This is an automated confirmation for your review submission. If you have any questions, please contact us at {{ config('services.admin.email') }}.
@endcomponent
@endcomponent
