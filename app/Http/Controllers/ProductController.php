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

    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="List all products",
     *     description="Get paginated list of products with optional filtering by category and search",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by category slug",
     *         required=false,
     *         @OA\Schema(type="string", example="templates")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in title and description",
     *         required=false,
     *         @OA\Schema(type="string", example="business")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated products list",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Small Business Launch Checklist"),
     *                 @OA\Property(property="slug", type="string", example="small-business-launch-checklist"),
     *                 @OA\Property(property="short_description", type="string"),
     *                 @OA\Property(property="price", type="number", format="float", example=7.99),
     *                 @OA\Property(property="show_on_homepage", type="boolean", example=true),
     *                 @OA\Property(property="file_type", type="string", example="pdf"),
     *                 @OA\Property(property="category", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="slug", type="string")
     *                 )
     *             )),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="last_page", type="integer", example=1),
     *             @OA\Property(property="per_page", type="integer", example=12),
     *             @OA\Property(property="total", type="integer", example=3)
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $categorySlug = $request->query('category');

        // Special handling for bundles category
        if ($categorySlug === 'bundles') {
            $bundlesQuery = Bundle::active()->with('products:id,title,slug,price');

            if ($search = $request->query('search')) {
                $bundlesQuery->where(fn($q) => $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%"));
            }

            return response()->json($bundlesQuery->latest()->paginate(12));
        }

        // Regular products
        $query = Product::active()->with('category:id,name,slug');

        if ($categorySlug) {
            $query->whereHas('category', fn($q) => $q->where('slug', $categorySlug));
        }
        if ($search = $request->query('search')) {
            $query->where(fn($q) => $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"));
        }

        return response()->json($query->orderBy('sort_order')->latest()->paginate(12));
    }

    /**
     * @OA\Get(
     *     path="/api/products/{slug}",
     *     summary="Get product details",
     *     description="Get detailed product information including comments and related products",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         description="Product slug",
     *         required=true,
     *         @OA\Schema(type="string", example="small-business-launch-checklist")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product details",
     *         @OA\JsonContent(
     *             @OA\Property(property="product", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="slug", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="file_type", type="string"),
     *                 @OA\Property(property="file_size", type="integer"),
     *                 @OA\Property(property="category", type="object")
     *             ),
     *             @OA\Property(property="has_purchased", type="boolean", example=false, description="Whether current user has purchased this product"),
     *             @OA\Property(property="comments", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="related", type="array", @OA\Items(type="object"), description="Related products from same category")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     )
     * )
     */
    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)->active()->with('category:id,name,slug')->firstOrFail();

        $hasPurchased = false;
        if (auth('sanctum')->check()) {
            $hasPurchased = auth('sanctum')->user()->hasPurchased($product);
        }

        $comments = $product->comments()->published()
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

    /**
     * @OA\Get(
     *     path="/api/featured",
     *     summary="Get featured products and bundles",
     *     description="Get featured products and active bundle deals",
     *     tags={"Products"},
     *     @OA\Response(
     *         response=200,
     *         description="Featured products and bundles",
     *         @OA\JsonContent(
     *             @OA\Property(property="products", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="price", type="number"),
     *                     @OA\Property(property="show_on_homepage", type="boolean", example=true)
     *                 )
     *             ),
     *             @OA\Property(property="bundles", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string", example="Complete Business Starter Pack"),
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="price", type="number", example=24.99),
     *                     @OA\Property(property="original_price", type="number", example=32.97),
     *                     @OA\Property(property="products", type="array", @OA\Items(type="object"))
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function featured()
    {
        $products = Product::active()->featured()->with('category:id,name,slug')
            ->orderBy('sort_order')->take(6)->get();

        $bundles = Bundle::active()->onHomepage()->with('products:id,title,slug,price')->get();

        return response()->json(['products' => $products, 'bundles' => $bundles]);
    }

    /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Get all categories",
     *     description="Get list of all active product categories",
     *     tags={"Products"},
     *     @OA\Response(
     *         response=200,
     *         description="List of categories",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Templates"),
     *                 @OA\Property(property="slug", type="string", example="templates")
     *             )
     *         )
     *     )
     * )
     */
    public function categories()
    {
        return response()->json(Category::active()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']));
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}/download",
     *     summary="Download purchased product",
     *     description="Download product file (requires purchase verification)",
     *     tags={"Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File download",
     *         @OA\MediaType(
     *             mediaType="application/octet-stream",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Purchase required",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Purchase required.")
     *         )
     *     )
     * )
     */
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
