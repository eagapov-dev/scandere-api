<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/categories",
     *     summary="Get all categories (admin)",
     *     tags={"Admin - Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Categories list")
     * )
     */
    public function index()
    {
        $categories = Category::orderBy('sort_order')->orderBy('name')->get();
        return response()->json($categories);
    }

    /**
     * @OA\Post(
     *     path="/api/admin/categories",
     *     summary="Create category",
     *     tags={"Admin - Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Templates"),
     *             @OA\Property(property="slug", type="string", example="templates"),
     *             @OA\Property(property="description", type="string", example="Professional templates"),
     *             @OA\Property(property="sort_order", type="integer", example=1),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Category created")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'slug' => 'nullable|string|max:100|unique:categories,slug',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $category = Category::create($validated);
        return response()->json($category, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/admin/categories/{id}",
     *     summary="Update category",
     *     tags={"Admin - Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="slug", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="sort_order", type="integer"),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Category updated")
     * )
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:100|unique:categories,name,' . $category->id,
            'slug' => 'nullable|string|max:100|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $category->update($validated);
        return response()->json($category);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/categories/{id}",
     *     summary="Delete category",
     *     tags={"Admin - Categories"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Category deleted")
     * )
     */
    public function destroy(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with products. Please reassign or delete products first.'
            ], 422);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted successfully.']);
    }
}
