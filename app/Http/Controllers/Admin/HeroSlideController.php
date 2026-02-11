<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\Request;

class HeroSlideController extends Controller
{
    public function index()
    {
        $slides = HeroSlide::orderBy('sort_order')->orderBy('id')->get();
        return response()->json($slides);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'required|string',
            'cta_text' => 'required|string|max:100',
            'cta_link' => 'required|string|max:255',
            'bg_gradient' => 'required|string|max:100',
            'icon' => 'required|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $slide = HeroSlide::create($validated);
        return response()->json($slide, 201);
    }

    public function update(Request $request, HeroSlide $heroSlide)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
            'cta_text' => 'nullable|string|max:100',
            'cta_link' => 'nullable|string|max:255',
            'bg_gradient' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $heroSlide->update($validated);
        return response()->json($heroSlide);
    }

    public function destroy(HeroSlide $heroSlide)
    {
        $heroSlide->delete();
        return response()->json(['message' => 'Hero slide deleted successfully.']);
    }
}
