<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use Illuminate\Http\Request;

class BundleController extends Controller
{
    public function index()
    {
        return response()->json(Bundle::with('products:id,title,slug,price')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'show_on_homepage' => 'boolean',
            'product_ids' => 'required|array|min:2',
            'product_ids.*' => 'exists:products,id'
        ]);

        // Calculate original price from products FIRST
        $products = \App\Models\Product::whereIn('id', $validated['product_ids'])->get();
        $originalPrice = $products->sum('price');

        $bundle = Bundle::create([
            'title' => $validated['title'],
            'slug' => \Str::slug($validated['title']),
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'original_price' => $originalPrice,
            'is_active' => $validated['is_active'] ?? true,
            'show_on_homepage' => $validated['show_on_homepage'] ?? false,
        ]);

        $bundle->products()->attach($validated['product_ids']);

        return response()->json($bundle->load('products'), 201);
    }

    public function update(Request $request, Bundle $bundle)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'show_on_homepage' => 'boolean',
            'product_ids' => 'required|array|min:2',
            'product_ids.*' => 'exists:products,id'
        ]);

        // Calculate original price from products
        $products = \App\Models\Product::whereIn('id', $validated['product_ids'])->get();
        $originalPrice = $products->sum('price');

        $bundle->update([
            'title' => $validated['title'],
            'slug' => \Str::slug($validated['title']),
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'original_price' => $originalPrice,
            'is_active' => $validated['is_active'] ?? $bundle->is_active,
            'show_on_homepage' => $validated['show_on_homepage'] ?? $bundle->show_on_homepage,
        ]);

        $bundle->products()->sync($validated['product_ids']);

        return response()->json($bundle->load('products'));
    }

    public function destroy(Bundle $bundle)
    {
        $bundle->products()->detach();
        $bundle->delete();
        return response()->json(['message' => 'Bundle deleted.']);
    }
}
