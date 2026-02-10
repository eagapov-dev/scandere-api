<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\{ProductController, CartController, PaymentController, CommentController, ContactController, SubscriberController};
use App\Http\Controllers\Admin\{DashboardController as AdminDash, ProductController as AdminProduct, SubscriberController as AdminSub, OrderController as AdminOrder, CommentController as AdminComment, ContactController as AdminContact};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::post('/subscribe', [SubscriberController::class, 'store'])->middleware('throttle:5,1');
Route::get('/unsubscribe/{email}', [SubscriberController::class, 'unsubscribe']);
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1');

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::get('/featured', [ProductController::class, 'featured']);
Route::get('/categories', [ProductController::class, 'categories']);

// Public comments (read only)
Route::get('/products/{product}/comments', [CommentController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    // Dashboard
    Route::get('/dashboard', function () {
        $user = auth()->user();
        $orders = $user->orders()->completed()->with('items.product:id,title,slug,file_type')->latest('paid_at')->paginate(10);
        $purchasedIds = $user->purchasedProductIds();
        return response()->json(['user' => [...$user->only('id', 'first_name', 'last_name', 'email'), 'name' => $user->name], 'orders' => $orders, 'purchased_product_ids' => $purchasedIds]);
    });

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::delete('/cart/{product}', [CartController::class, 'remove']);
    Route::delete('/cart', [CartController::class, 'clear']);
    Route::post('/cart/bundle/{bundle}', [CartController::class, 'addBundle']);

    // Checkout
    Route::post('/checkout', [PaymentController::class, 'checkout']);
    Route::get('/payment/success', [PaymentController::class, 'success']);

    // Download
    Route::get('/products/{product}/download', [ProductController::class, 'download']);

    // Comments (post)
    Route::post('/products/{product}/comments', [CommentController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    */
    Route::middleware(\App\Http\Middleware\IsAdmin::class)->prefix('admin')->group(function () {
        Route::get('/stats', [AdminDash::class, 'stats']);
        Route::apiResource('products', AdminProduct::class);
        Route::get('/subscribers', [AdminSub::class, 'index']);
        Route::get('/subscribers/export', [AdminSub::class, 'export']);
        Route::get('/orders', [AdminOrder::class, 'index']);
        Route::get('/comments', [AdminComment::class, 'index']);
        Route::patch('/comments/{comment}/approve', [AdminComment::class, 'approve']);
        Route::delete('/comments/{comment}', [AdminComment::class, 'destroy']);
        Route::get('/messages', [AdminContact::class, 'index']);
        Route::patch('/messages/{message}/read', [AdminContact::class, 'markRead']);
    });
});

// Stripe webhook
Route::post('/webhook/stripe', [PaymentController::class, 'webhook']);
