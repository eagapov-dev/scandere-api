<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/orders",
     *     summary="List all orders (admin)",
     *     description="Get paginated list of all orders with user and product information, plus total revenue and order count. Requires admin authentication.",
     *     tags={"Admin - Orders"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Orders list with statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="orders", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer", example=30),
     *                 @OA\Property(property="total", type="integer")
     *             ),
     *             @OA\Property(property="total_revenue", type="number", format="float", example=12345.67, description="Total revenue from completed orders"),
     *             @OA\Property(property="total_orders", type="integer", example=248, description="Total number of completed orders")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden (not admin)")
     * )
     */
    public function index()
    {
        return response()->json([
            'orders' => Order::with(['user:id,first_name,last_name,email', 'items.product:id,title'])->latest()->paginate(30),
            'total_revenue' => (float) Order::completed()->sum('total'),
            'total_orders' => Order::completed()->count(),
        ]);
    }
}
