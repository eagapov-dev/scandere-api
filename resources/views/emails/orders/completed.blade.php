@component('mail::message')
# Thank You for Your Order!

Hi {{ $order->user->name }},

Your order has been completed successfully and your products are now ready for download!

## Order Details

**Order ID:** #{{ $order->id }}
**Date:** {{ $order->created_at->format('F j, Y g:i A') }}
**Total:** ${{ number_format($order->total, 2) }}

@if($order->payment_id)
**Payment ID:** {{ $order->payment_id }}
@endif

## Your Products

@foreach($items as $item)
---

**{{ $item->product->name }}**
Price: ${{ number_format($item->price, 2) }}

@component('mail::button', ['url' => config('app.frontend_url') . '/downloads/' . $item->product->id])
Download {{ $item->product->name }}
@endcomponent

@endforeach

---

## Important Information

- Download links are valid for 7 days
- You can re-download your products from your account dashboard
- If you have any issues, please contact our support team

@component('mail::panel')
**Need Help?**

If you have any questions or encounter any issues with your download, please don't hesitate to reach out to our support team at {{ config('services.admin.email') }}
@endcomponent

Thank you for choosing Scandere AI Store!

Best regards,
The Scandere AI Team

@component('mail::subcopy')
This is an automated confirmation email for order #{{ $order->id }}. Please keep this email for your records.
@endcomponent
@endcomponent
