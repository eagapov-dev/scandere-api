<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index()
    {
        return response()->json(Page::orderBy('sort_order')->orderBy('created_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'content' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $page = Page::create([
            ...$v,
            'slug' => $v['slug'] ?? Str::slug($v['title']),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $v['sort_order'] ?? 0,
        ]);

        return response()->json($page, 201);
    }

    public function show(Page $page)
    {
        return response()->json($page);
    }

    public function update(Request $request, Page $page)
    {
        $v = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug,' . $page->id,
            'content' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $page->update([
            ...$v,
            'slug' => $v['slug'] ?? Str::slug($v['title']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return response()->json($page->fresh());
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return response()->json(['message' => 'Deleted.']);
    }
}
