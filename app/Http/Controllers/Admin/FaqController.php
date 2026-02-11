<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::with('category:id,name')->orderBy('sort_order')->orderBy('id')->get();
        return response()->json($faqs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:faq_categories,id',
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Convert empty string to null for category_id
        if (isset($validated['category_id']) && $validated['category_id'] === '') {
            $validated['category_id'] = null;
        }

        $faq = Faq::create($validated);
        $faq->load('category:id,name');
        return response()->json($faq, 201);
    }

    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:faq_categories,id',
            'question' => 'nullable|string|max:500',
            'answer' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Convert empty string to null for category_id
        if (array_key_exists('category_id', $validated) && $validated['category_id'] === '') {
            $validated['category_id'] = null;
        }

        $faq->update($validated);
        $faq->load('category:id,name');
        return response()->json($faq);
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        return response()->json(['message' => 'FAQ deleted successfully.']);
    }
}
