<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;

class PaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createCheckoutSession(User $user, Order $order, $cartItems, float $total, $bundle = null): Session
    {
        $lineItems = [];

        if ($bundle) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => (int) ($total * 100),
                    'product_data' => ['name' => $bundle->title . ' (Bundle Deal)'],
                ],
                'quantity' => 1,
            ];
        } else {
            foreach ($cartItems as $item) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'unit_amount' => (int) ($item->product->price * 100),
                        'product_data' => ['name' => $item->product->title],
                    ],
                    'quantity' => $item->quantity,
                ];
            }
        }

        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'customer_email' => $user->email,
            'metadata' => ['order_id' => $order->id, 'user_id' => $user->id],
            'success_url' => config('app.frontend_url', env('FRONTEND_URL')) . '/payment/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => config('app.frontend_url', env('FRONTEND_URL')) . '/cart',
        ]);
    }

    public function handleSuccessfulPayment(string $sessionId): Order
    {
        $session = Session::retrieve($sessionId);
        $order = Order::findOrFail($session->metadata->order_id);

        if ($order->status !== 'completed') {
            $order->markAsCompleted($session->payment_intent);
        }

        return $order;
    }

    public function handleWebhook(string $payload, string $signature): void
    {
        $event = Webhook::constructEvent($payload, $signature, config('services.stripe.webhook_secret'));

        match ($event->type) {
            'checkout.session.completed' => $this->handleSessionCompleted($event->data->object),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event->data->object),
            default => null,
        };
    }

    private function handleSessionCompleted($session): void
    {
        $order = Order::find($session->metadata->order_id);
        if ($order && $order->status !== 'completed') {
            $order->markAsCompleted($session->payment_intent);
        }
    }

    private function handlePaymentFailed($paymentIntent): void
    {
        if (isset($paymentIntent->metadata->order_id)) {
            Order::where('id', $paymentIntent->metadata->order_id)->update(['status' => 'failed']);
        }
    }

    public function calculateTotal(array $items): float
    {
        return array_reduce($items, function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);
    }

    public function formatAmountForStripe(float $amount): int
    {
        return (int) ($amount * 100);
    }

    public function isProductAvailable($product): bool
    {
        return $product->is_active ?? false;
    }
}
