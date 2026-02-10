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

    public function index() { return response()->json(Product::with('category:id,name')->latest()->paginate(20)); }

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
            'is_featured' => 'boolean',
            'file' => 'required|file|max:51200',
            'preview_image' => 'nullable|image|max:2048',
        ]);

        $fileData = $this->fileService->storeProduct($request->file('file'));

        $product = Product::create([
            ...$v, ...$fileData,
            'slug' => Str::slug($v['title']),
            'is_free' => $request->boolean('is_free'),
            'is_active' => $request->boolean('is_active', true),
            'is_featured' => $request->boolean('is_featured'),
            'preview_image' => $request->hasFile('preview_image')
                ? $request->file('preview_image')->store('public/previews') : null,
        ]);

        return response()->json($product, 201);
    }

    public function show(Product $product) { return response()->json($product->load('category')); }

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
            'is_featured' => 'boolean',
            'file' => 'nullable|file|max:51200',
        ]);

        $data = [...$v, 'is_free' => $request->boolean('is_free'), 'is_active' => $request->boolean('is_active', true), 'is_featured' => $request->boolean('is_featured')];

        if ($request->hasFile('file')) {
            $this->fileService->deleteProduct($product);
            $data = array_merge($data, $this->fileService->storeProduct($request->file('file')));
        }

        $product->update($data);
        return response()->json($product->fresh());
    }

    public function destroy(Product $product)
    {
        $this->fileService->deleteProduct($product);
        $product->delete();
        return response()->json(['message' => 'Deleted.']);
    }
}
