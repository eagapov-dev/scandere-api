<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\Category;
use App\Models\Product;
use App\Services\FileService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private FileService $fileService) {}

    public function index(Request $request)
    {
        $query = Product::active()->with('category:id,name,slug');

        if ($slug = $request->query('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $slug));
        }
        if ($search = $request->query('search')) {
            $query->where(fn($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"));
        }

        return response()->json($query->orderBy('sort_order')->latest()->paginate(12));
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)->active()->with('category:id,name,slug')->firstOrFail();

        $hasPurchased = false;
        if (auth('sanctum')->check()) {
            $hasPurchased = auth('sanctum')->user()->hasPurchased($product);
        }

        $comments = $product->comments()->approved()
            ->with('user:id,first_name,last_name')
            ->latest()->take(50)->get();

        $related = Product::active()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->select('id', 'title', 'slug', 'price', 'is_free', 'short_description')
            ->take(4)->get();

        return response()->json([
            'product' => $product,
            'has_purchased' => $hasPurchased,
            'comments' => $comments,
            'related' => $related,
        ]);
    }

    public function featured()
    {
        $products = Product::active()->featured()->with('category:id,name,slug')
            ->orderBy('sort_order')->take(6)->get();

        $bundles = Bundle::active()->with('products:id,title,slug,price')->get();

        return response()->json(['products' => $products, 'bundles' => $bundles]);
    }

    public function categories()
    {
        return response()->json(Category::active()->orderBy('sort_order')->get(['id', 'name', 'slug']));
    }

    public function download(Product $product)
    {
        $user = auth()->user();
        if (!$product->is_free && !$user->hasPurchased($product)) {
            return response()->json(['message' => 'Purchase required.'], 403);
        }

        $product->increment('download_count');
        return $this->fileService->secureDownload($product);
    }
}
