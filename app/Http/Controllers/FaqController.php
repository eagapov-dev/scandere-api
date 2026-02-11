<?php

namespace App\Http\Controllers;

use App\Models\Faq;

class FaqController extends Controller
{
    public function index()
    {
        $categories = \App\Models\FaqCategory::active()
            ->with(['faqs' => function ($q) {
                $q->active()->orderBy('sort_order')->orderBy('id');
            }])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // Add uncategorized FAQs if any exist
        $uncategorizedFaqs = Faq::active()
            ->whereNull('category_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($uncategorizedFaqs->isNotEmpty()) {
            $categories->push((object)[
                'id' => null,
                'name' => 'General',
                'sort_order' => 999,
                'is_active' => true,
                'faqs' => $uncategorizedFaqs
            ]);
        }

        return response()->json($categories);
    }
}
