<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeShowcase;
use Illuminate\Http\Request;

class HomeShowcaseController extends Controller
{
    public function index()
    {
        $showcases = HomeShowcase::orderBy('sort_order')->orderBy('id')->get();
        return response()->json($showcases);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'icon' => 'required|string|max:50',
            'gradient' => 'required|string|max:100',
            'features' => 'required|array|min:1',
            'features.*' => 'string|max:255',
            'reverse' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $showcase = HomeShowcase::create($validated);
        return response()->json($showcase, 201);
    }

    public function update(Request $request, HomeShowcase $homeShowcase)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'gradient' => 'nullable|string|max:100',
            'features' => 'nullable|array|min:1',
            'features.*' => 'string|max:255',
            'reverse' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $homeShowcase->update($validated);
        return response()->json($homeShowcase);
    }

    public function destroy(HomeShowcase $homeShowcase)
    {
        $homeShowcase->delete();
        return response()->json(['message' => 'Showcase deleted successfully.']);
    }
}
