<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavigationLink;
use Illuminate\Http\Request;

class NavigationLinkController extends Controller
{
    public function index()
    {
        $links = NavigationLink::orderBy('location')->orderBy('sort_order')->orderBy('id')->get();
        return response()->json($links);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:100',
            'url' => 'required|string|max:255',
            'location' => 'required|in:header,footer',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $link = NavigationLink::create($validated);
        return response()->json($link, 201);
    }

    public function update(Request $request, NavigationLink $navigationLink)
    {
        $validated = $request->validate([
            'label' => 'nullable|string|max:100',
            'url' => 'nullable|string|max:255',
            'location' => 'nullable|in:header,footer',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $navigationLink->update($validated);
        return response()->json($navigationLink);
    }

    public function destroy(NavigationLink $navigationLink)
    {
        $navigationLink->delete();
        return response()->json(['message' => 'Navigation link deleted successfully.']);
    }
}
