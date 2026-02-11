<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/cart",
     *     summary="View shopping cart",
     *     description="Get cart items with automatic bundle detection and savings calculation",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cart contents",
     *         @OA\JsonContent(
     *             @OA\Property(property="items", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="product", type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="title", type="string"),
     *                         @OA\Property(property="price", type="number")
     *                     ),
     *                     @OA\Property(property="quantity", type="integer", example=1)
     *                 )
     *             ),
     *             @OA\Property(property="subtotal", type="number", example=24.99, description="Total price (with bundle discount applied if eligible)"),
     *             @OA\Property(property="bundle", type="object", nullable=true, description="Bundle applied (if cart items match a bundle)"),
     *             @OA\Property(property="bundle_savings", type="number", example=7.98, description="Amount saved with bundle discount")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $items = auth()->user()->cartItems()->with('product:id,title,slug,price,is_free,preview_image')->get();

        $subtotal = $items->sum(fn($i) => $i->product->price * $i->quantity);

        // Check if bundle deal applies
        $bundle = null;
        $bundleSavings = 0;
        $productIds = $items->pluck('product_id')->sort()->values()->all();

        $bundles = Bundle::active()->with('products:id')->get();
        foreach ($bundles as $b) {
            $bundleProductIds = $b->products->pluck('id')->sort()->values()->all();
            if (empty(array_diff($bundleProductIds, $productIds))) {
                $bundleSavings = $subtotal - $b->price;
                $bundle = $b;
                $subtotal = $b->price;
                break;
            }
        }

        return response()->json([
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'bundle' => $bundle,
            'bundle_savings' => round($bundleSavings, 2),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/cart/add",
     *     summary="Add product to cart",
     *     description="Add a product to shopping cart (prevents adding already purchased products)",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(property="product_id", type="integer", example=1, description="Product ID to add")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product added to cart",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Added to cart.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Product already purchased",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Already purchased.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function add(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        $product = Product::findOrFail($request->product_id);

        // Check if already purchased
        if (auth()->user()->hasPurchased($product)) {
            return response()->json(['message' => 'Already purchased.'], 409);
        }

        CartItem::updateOrCreate(
            ['user_id' => auth()->id(), 'product_id' => $request->product_id],
            ['quantity' => 1]
        );

        return response()->json(['message' => 'Added to cart.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/cart/{product_id}",
     *     summary="Remove product from cart",
     *     description="Remove a specific product from shopping cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         description="Product ID to remove",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product removed from cart",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Removed from cart.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function remove(Product $product)
    {
        CartItem::where('user_id', auth()->id())->where('product_id', $product->id)->delete();
        return response()->json(['message' => 'Removed from cart.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/cart",
     *     summary="Clear cart",
     *     description="Remove all items from shopping cart",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cart cleared",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cart cleared.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function clear()
    {
        auth()->user()->cartItems()->delete();
        return response()->json(['message' => 'Cart cleared.']);
    }

    /**
     * @OA\Post(
     *     path="/api/cart/bundle/{bundle_id}",
     *     summary="Add bundle to cart",
     *     description="Add all products from a bundle to cart (skips already purchased products)",
     *     tags={"Cart"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="bundle_id",
     *         in="path",
     *         description="Bundle ID to add",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bundle added to cart",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Bundle added to cart.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Bundle not found"
     *     )
     * )
     */
    public function addBundle(Bundle $bundle)
    {
        $user = auth()->user();
        foreach ($bundle->products as $product) {
            if (!$user->hasPurchased($product)) {
                CartItem::updateOrCreate(
                    ['user_id' => $user->id, 'product_id' => $product->id],
                    ['quantity' => 1]
                );
            }
        }

        return response()->json(['message' => 'Bundle added to cart.']);
    }
}
