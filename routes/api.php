<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\{ProductController, CartController, PaymentController, CommentController, ContactController, SubscriberController, FaqController, HomeController};
use App\Http\Controllers\Admin\{DashboardController as AdminDash, ProductController as AdminProduct, SubscriberController as AdminSub, OrderController as AdminOrder, CommentController as AdminComment, ContactController as AdminContact, CategoryController as AdminCategory, FaqController as AdminFaq, FaqCategoryController as AdminFaqCategory, HeroSlideController, HomeFeatureController, HomeStatController, HomeShowcaseController, SocialLinkController, NavigationLinkController, NewsletterCampaignController};
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
Route::get('/faqs', [FaqController::class, 'index']);
Route::get('/home-content', [HomeController::class, 'content']);

// Public comments (read only)
Route::get('/products/{product}/comments', [CommentController::class, 'index']);
Route::get('/recent-qa', [CommentController::class, 'recentQA']);

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
    Route::post('/comments/general', [CommentController::class, 'storeGeneral']);

    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    */
    Route::middleware(\App\Http\Middleware\IsAdmin::class)->prefix('admin')->group(function () {
        Route::get('/stats', [AdminDash::class, 'stats']);
        Route::apiResource('products', AdminProduct::class);
        Route::apiResource('bundles', \App\Http\Controllers\Admin\BundleController::class);
        Route::apiResource('categories', AdminCategory::class);
        Route::apiResource('faqs', AdminFaq::class);
        Route::apiResource('faq-categories', AdminFaqCategory::class);
        Route::apiResource('hero-slides', HeroSlideController::class);
        Route::apiResource('home-features', HomeFeatureController::class);
        Route::apiResource('home-stats', HomeStatController::class);
        Route::apiResource('home-showcases', HomeShowcaseController::class);
        Route::apiResource('social-links', SocialLinkController::class);
        Route::apiResource('navigation-links', NavigationLinkController::class);
        Route::get('/subscribers', [AdminSub::class, 'index']);
        Route::get('/subscribers/export', [AdminSub::class, 'export']);
        Route::get('/orders', [AdminOrder::class, 'index']);
        Route::get('/comments', [AdminComment::class, 'index']);
        Route::put('/comments/{comment}', [AdminComment::class, 'update']);
        Route::patch('/comments/{comment}/approve', [AdminComment::class, 'approve']);
        Route::delete('/comments/{comment}', [AdminComment::class, 'destroy']);
        Route::get('/messages', [AdminContact::class, 'index']);
        Route::patch('/messages/{message}/read', [AdminContact::class, 'markRead']);
        Route::post('/newsletter/send', [NewsletterCampaignController::class, 'send']);
        Route::get('/newsletter/stats', [NewsletterCampaignController::class, 'stats']);
    });
});

// Stripe webhook
Route::post('/webhook/stripe', [PaymentController::class, 'webhook']);
