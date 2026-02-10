<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function checkout(Request $request)
    {
        $user = auth()->user();
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty.'], 400);
        }

        // Calculate total — check for bundle discount
        $total = $cartItems->sum(fn($i) => $i->product->price * $i->quantity);
        $productIds = $cartItems->pluck('product_id')->sort()->values()->all();

        $appliedBundle = null;
        $bundles = Bundle::active()->with('products:id')->get();
        foreach ($bundles as $bundle) {
            $bundleProductIds = $bundle->products->pluck('id')->sort()->values()->all();
            if (empty(array_diff($bundleProductIds, $productIds))) {
                $total = $bundle->price;
                $appliedBundle = $bundle;
                break;
            }
        }

        // Create order
        $order = Order::create([
            'user_id' => $user->id,
            'total' => $total,
            'status' => 'pending',
        ]);

        foreach ($cartItems as $item) {
            $order->items()->create([
                'product_id' => $item->product_id,
                'price' => $item->product->price,
                'quantity' => $item->quantity,
            ]);
        }

        // Free order
        if ($total <= 0) {
            $order->markAsCompleted('free');
            $user->cartItems()->delete();
            return response()->json(['message' => 'Order completed!', 'order_id' => $order->id]);
        }

        try {
            $session = $this->paymentService->createCheckoutSession($user, $order, $cartItems, $total, $appliedBundle);
            return response()->json(['checkout_url' => $session->url]);
        } catch (\Exception $e) {
            Log::error('Checkout failed', ['error' => $e->getMessage()]);
            $order->update(['status' => 'failed']);
            return response()->json(['message' => 'Payment error.'], 500);
        }
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        if (!$sessionId) return response()->json(['message' => 'Missing session.'], 400);

        try {
            $order = $this->paymentService->handleSuccessfulPayment($sessionId);
            auth()->user()->cartItems()->delete();
            return response()->json(['message' => 'Payment successful!', 'order' => $order->load('items.product:id,title,slug')]);
        } catch (\Exception $e) {
            Log::error('Payment verify failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Verification issue.'], 500);
        }
    }

    public function webhook(Request $request)
    {
        try {
            $this->paymentService->handleWebhook($request->getContent(), $request->header('Stripe-Signature'));
            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('Webhook failed', ['error' => $e->getMessage()]);
            return response('Error', 400);
        }
    }
}
