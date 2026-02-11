<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    public function index()
    {
        $links = SocialLink::orderBy('sort_order')->orderBy('id')->get();
        return response()->json($links);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|string|max:50',
            'url' => 'required|url|max:255',
            'icon' => 'required|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $link = SocialLink::create($validated);
        return response()->json($link, 201);
    }

    public function update(Request $request, SocialLink $socialLink)
    {
        $validated = $request->validate([
            'platform' => 'nullable|string|max:50',
            'url' => 'nullable|url|max:255',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $socialLink->update($validated);
        return response()->json($socialLink);
    }

    public function destroy(SocialLink $socialLink)
    {
        $socialLink->delete();
        return response()->json(['message' => 'Social link deleted successfully.']);
    }
}
