<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
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

    public function remove(Product $product)
    {
        CartItem::where('user_id', auth()->id())->where('product_id', $product->id)->delete();
        return response()->json(['message' => 'Removed from cart.']);
    }

    public function clear()
    {
        auth()->user()->cartItems()->delete();
        return response()->json(['message' => 'Cart cleared.']);
    }

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
