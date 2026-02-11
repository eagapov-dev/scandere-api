<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeStat;
use Illuminate\Http\Request;

class HomeStatController extends Controller
{
    public function index()
    {
        $stats = HomeStat::orderBy('sort_order')->orderBy('id')->get();
        return response()->json($stats);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'value' => 'required|string|max:50',
            'label' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $stat = HomeStat::create($validated);
        return response()->json($stat, 201);
    }

    public function update(Request $request, HomeStat $homeStat)
    {
        $validated = $request->validate([
            'value' => 'nullable|string|max:50',
            'label' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $homeStat->update($validated);
        return response()->json($homeStat);
    }

    public function destroy(HomeStat $homeStat)
    {
        $homeStat->delete();
        return response()->json(['message' => 'Stat deleted successfully.']);
    }
}
