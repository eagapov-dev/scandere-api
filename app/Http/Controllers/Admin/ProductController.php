<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct(private FileService $fileService) {}

    /**
     * @OA\Get(
     *     path="/api/admin/products",
     *     summary="List all products (admin)",
     *     description="Get paginated list of all products with category information. Requires admin authentication.",
     *     tags={"Admin - Products"},
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
     *         description="Paginated products list",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer", example=20),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden (not admin)")
     * )
     */
    public function index() { return response()->json(Product::with('category:id,name')->latest()->paginate(20)); }

    /**
     * @OA\Post(
     *     path="/api/admin/products",
     *     summary="Create new product (admin)",
     *     description="Create a new product with file upload. Requires admin authentication. Accepts multipart/form-data for file uploads.",
     *     tags={"Admin - Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "price", "file"},
     *                 @OA\Property(property="title", type="string", maxLength=255, example="New Product Template"),
     *                 @OA\Property(property="category_id", type="integer", example=1, description="Category ID (nullable)"),
     *                 @OA\Property(property="short_description", type="string", maxLength=500, example="Brief product description"),
     *                 @OA\Property(property="description", type="string", example="Full product description with details"),
     *                 @OA\Property(property="price", type="number", minimum=0, example=9.99, description="Product price"),
     *                 @OA\Property(property="is_free", type="boolean", example=false, description="Is this a free product"),
     *                 @OA\Property(property="is_active", type="boolean", example=true, description="Is product active (default: true)"),
     *                 @OA\Property(property="show_on_homepage", type="boolean", example=false, description="Feature this product"),
     *                 @OA\Property(property="file", type="string", format="binary", description="Product file (PDF, XLSX, DOCX, etc.) - max 50MB"),
     *                 @OA\Property(property="preview_image", type="string", format="binary", description="Preview image (optional) - max 2MB")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully"
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden (not admin)"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $v = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_free' => 'boolean',
            'is_active' => 'boolean',
            'show_on_homepage' => 'boolean',
            'file' => 'required|file|max:51200',
            'preview_image' => 'nullable|image|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|string|max:255',
        ]);

        $fileData = $this->fileService->storeProduct($request->file('file'));

        $product = Product::create([
            ...$v, ...$fileData,
            'slug' => Str::slug($v['title']),
            'is_free' => $request->boolean('is_free'),
            'is_active' => $request->boolean('is_active', true),
            'show_on_homepage' => $request->boolean('show_on_homepage'),
            'preview_image' => $request->hasFile('preview_image')
                ? $request->file('preview_image')->store('public/previews') : null,
        ]);

        return response()->json($product, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/products/{id}",
     *     summary="Get product details (admin)",
     *     description="Get detailed product information with category. Requires admin authentication.",
     *     tags={"Admin - Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Product details"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden (not admin)"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function show(Product $product) { return response()->json($product->load('category')); }

    /**
     * @OA\Put(
     *     path="/api/admin/products/{id}",
     *     summary="Update product (admin)",
     *     description="Update existing product. Optionally upload new file to replace existing. Requires admin authentication.",
     *     tags={"Admin - Products"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "price"},
     *                 @OA\Property(property="title", type="string", maxLength=255),
     *                 @OA\Property(property="category_id", type="integer"),
     *                 @OA\Property(property="short_description", type="string", maxLength=500),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="price", type="number", minimum=0),
     *                 @OA\Property(property="is_free", type="boolean"),
     *                 @OA\Property(property="is_active", type="boolean"),
     *                 @OA\Property(property="show_on_homepage", type="boolean"),
     *                 @OA\Property(property="file", type="string", format="binary", description="Optional: Replace product file (max 50MB)")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Product updated successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden (not admin)"),
     *     @OA\Response(response=404, description="Product not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(Request $request, Product $product)
    {
        $v = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_free' => 'boolean',
            'is_active' => 'boolean',
            'show_on_homepage' => 'boolean',
            'file' => 'nullable|file|max:51200',
            'preview_image' => 'nullable|image|max:2048',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|string|max:255',
        ]);

        $data = [...$v, 'is_free' => $request->boolean('is_free'), 'is_active' => $request->boolean('is_active', true), 'show_on_homepage' => $request->boolean('show_on_homepage')];

        if ($request->hasFile('file')) {
            $this->fileService->deleteProduct($product);
            $data = array_merge($data, $this->fileService->storeProduct($request->file('file')));
        }

        if ($request->hasFile('preview_image')) {
            $data['preview_image'] = $request->file('preview_image')->store('public/previews');
        }

        $product->update($data);
        return response()->json($product->fresh());
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/products/{id}",
     *     summary="Delete product (admin)",
     *     description="Permanently delete product and associated file. Requires admin authentication.",
     *     tags={"Admin - Products"},
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
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Deleted.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden (not admin)"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function destroy(Product $product)
    {
        $this->fileService->deleteProduct($product);
        $product->delete();
        return response()->json(['message' => 'Deleted.']);
    }
}
