<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeFeature;
use Illuminate\Http\Request;

class HomeFeatureController extends Controller
{
    public function index()
    {
        $features = HomeFeature::orderBy('sort_order')->orderBy('id')->get();
        return response()->json($features);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'icon' => 'required|string|max:50',
            'title' => 'required|string|max:100',
            'description' => 'required|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $feature = HomeFeature::create($validated);
        return response()->json($feature, 201);
    }

    public function update(Request $request, HomeFeature $homeFeature)
    {
        $validated = $request->validate([
            'icon' => 'nullable|string|max:50',
            'title' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $homeFeature->update($validated);
        return response()->json($homeFeature);
    }

    public function destroy(HomeFeature $homeFeature)
    {
        $homeFeature->delete();
        return response()->json(['message' => 'Feature deleted successfully.']);
    }
}
