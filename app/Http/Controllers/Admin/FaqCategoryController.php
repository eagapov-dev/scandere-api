<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaqCategory;
use Illuminate\Http\Request;

class FaqCategoryController extends Controller
{
    public function index()
    {
        $categories = FaqCategory::withCount('faqs')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $category = FaqCategory::create($validated);
        return response()->json($category, 201);
    }

    public function update(Request $request, FaqCategory $faqCategory)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $faqCategory->update($validated);
        return response()->json($faqCategory);
    }

    public function destroy(FaqCategory $faqCategory)
    {
        // Check if category has FAQs
        if ($faqCategory->faqs()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with FAQs. Please reassign or delete FAQs first.'
            ], 422);
        }

        $faqCategory->delete();
        return response()->json(['message' => 'FAQ category deleted successfully.']);
    }
}
