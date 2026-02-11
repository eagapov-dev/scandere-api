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

    /**
     * @OA\Post(
     *     path="/api/checkout",
     *     summary="Create Stripe checkout session",
     *     description="Create a Stripe checkout session for cart items. Automatically detects and applies bundle discounts. For free orders (total = 0), completes immediately. For paid orders, returns Stripe checkout URL for redirect.",
     *     tags={"Payments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Checkout response - either immediate completion (free) or Stripe checkout URL (paid)",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     @OA\Property(property="message", type="string", example="Order completed!"),
     *                     @OA\Property(property="order_id", type="integer", example=1)
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="checkout_url", type="string", example="https://checkout.stripe.com/c/pay/cs_test_xxxxxxxxxxxxx")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cart is empty",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cart is empty.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Payment processing error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment error.")
     *         )
     *     )
     * )
     */
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

        // Bypass payment for testing (when BYPASS_PAYMENT=true in .env)
        if (config('services.bypass_payment')) {
            $order->markAsCompleted('test_payment_bypassed');
            $user->cartItems()->delete();
            return response()->json([
                'message' => 'Order completed (payment bypassed for testing)!',
                'order_id' => $order->id
            ]);
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

    /**
     * @OA\Get(
     *     path="/api/payment/success",
     *     summary="Verify successful payment",
     *     description="Callback endpoint after successful Stripe checkout. Verifies payment and marks order as completed.",
     *     tags={"Payments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="session_id",
     *         in="query",
     *         description="Stripe checkout session ID (provided by Stripe redirect)",
     *         required=true,
     *         @OA\Schema(type="string", example="cs_test_xxxxxxxxxxxxx")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment successful!"),
     *             @OA\Property(property="order", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="total", type="number", example=24.99),
     *                 @OA\Property(property="status", type="string", example="completed"),
     *                 @OA\Property(property="items", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="product", type="object",
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="title", type="string"),
     *                             @OA\Property(property="slug", type="string")
     *                         ),
     *                         @OA\Property(property="price", type="number"),
     *                         @OA\Property(property="quantity", type="integer")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Missing session_id parameter",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Missing session.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Payment verification failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Verification issue.")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/webhook/stripe",
     *     summary="Stripe webhook handler",
     *     description="Receives webhook events from Stripe (checkout.session.completed, payment_intent.succeeded, payment_intent.payment_failed). Verifies signature and processes payment events. This endpoint is called by Stripe servers, not by client applications.",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Stripe webhook event payload (JSON)",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="string", example="evt_xxxxxxxxxxxxx"),
     *             @OA\Property(property="type", type="string", example="checkout.session.completed"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="object", type="object", description="Event-specific data")
     *             )
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="Stripe-Signature",
     *         in="header",
     *         description="Stripe webhook signature for verification (automatically sent by Stripe)",
     *         required=true,
     *         @OA\Schema(type="string", example="t=1234567890,v1=xxxxxxxxxxxxx")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Webhook processed successfully",
     *         @OA\MediaType(
     *             mediaType="text/plain",
     *             @OA\Schema(type="string", example="OK")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Webhook processing failed (invalid signature or processing error)",
     *         @OA\MediaType(
     *             mediaType="text/plain",
     *             @OA\Schema(type="string", example="Error")
     *         )
     *     )
     * )
     */
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
