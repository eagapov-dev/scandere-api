<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Order, Product, Subscriber, User, ContactMessage};

class DashboardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/stats",
     *     summary="Get admin dashboard statistics",
     *     description="Get comprehensive statistics for admin dashboard including users, subscribers, products, orders, revenue, and recent activity. Requires admin authentication.",
     *     tags={"Admin - Dashboard"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_users", type="integer", example=150, description="Total registered users"),
     *             @OA\Property(property="total_subscribers", type="integer", example=320, description="Total active newsletter subscribers"),
     *             @OA\Property(property="total_products", type="integer", example=3, description="Total products"),
     *             @OA\Property(property="total_orders", type="integer", example=85, description="Total completed orders"),
     *             @OA\Property(property="total_revenue", type="number", format="float", example=2123.15, description="Total revenue from completed orders"),
     *             @OA\Property(property="unread_messages", type="integer", example=7, description="Number of unread contact messages"),
     *             @OA\Property(property="recent_orders", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="total", type="number"),
     *                     @OA\Property(property="status", type="string", example="completed"),
     *                     @OA\Property(property="paid_at", type="string", format="date-time"),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="first_name", type="string"),
     *                         @OA\Property(property="last_name", type="string"),
     *                         @OA\Property(property="email", type="string")
     *                     )
     *                 ),
     *                 description="10 most recent completed orders"
     *             ),
     *             @OA\Property(property="recent_subscribers", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="email", type="string"),
     *                     @OA\Property(property="first_name", type="string"),
     *                     @OA\Property(property="source", type="string", example="newsletter"),
     *                     @OA\Property(property="subscribed_at", type="string", format="date-time")
     *                 ),
     *                 description="10 most recent subscribers"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (not admin)"
     *     )
     * )
     */
    public function stats()
    {
        return response()->json([
            'total_users' => User::count(),
            'total_subscribers' => Subscriber::active()->count(),
            'total_products' => Product::count(),
            'total_orders' => Order::completed()->count(),
            'total_revenue' => (float) Order::completed()->sum('total'),
            'unread_messages' => ContactMessage::where('is_read', false)->count(),
            'recent_orders' => Order::completed()->with(['user:id,first_name,last_name,email'])->latest('paid_at')->take(10)->get(),
            'recent_subscribers' => Subscriber::active()->latest('subscribed_at')->take(10)->get(['id', 'email', 'first_name', 'source', 'subscribed_at']),
        ]);
    }
}
