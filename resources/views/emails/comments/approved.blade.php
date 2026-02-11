@component('mail::message')
# Your Review Has Been Approved!

Hi {{ $comment->user->name }},

Great news! Your review for **{{ $product->name }}** has been approved and is now live on our website.

## Your Published Review

**Product:** {{ $product->name }}
**Rating:** {{ $comment->rating }} / 5 stars
**Published:** {{ now()->format('F j, Y') }}

@component('mail::panel')
{{ $comment->content }}
@endcomponent

@if($comment->admin_reply)
## Admin Response

Our team has also left a response to your review:

@component('mail::panel')
{{ $comment->admin_reply }}
@endcomponent
@endif

## View Your Review

Your review is now visible to all customers browsing **{{ $product->name }}**. Thank you for helping others make informed decisions!

@component('mail::button', ['url' => $productUrl])
View Your Review
@endcomponent

## Share Your Experience

Love the product? Feel free to share your review with others or leave reviews on other products you've purchased!

Thank you for being an active member of the Scandere AI community!

Best regards,
The Scandere AI Team

@component('mail::subcopy')
This email confirms the approval of your review. If you need to update or remove your review, please contact us at {{ config('services.admin.email') }}.
@endcomponent
@endcomponent
