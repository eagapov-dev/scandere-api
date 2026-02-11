<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class UpdateCategoriesSortOrderSeeder extends Seeder
{
    public function run(): void
    {
        // Set proper sort order for categories
        $sortOrders = [
            'templates' => 1,
            'checklists' => 2,
            'documents' => 3,
            'test' => 999, // test category last (or could be removed)
            'bundles' => 100, // Bundles after regular categories
        ];

        foreach ($sortOrders as $slug => $order) {
            Category::where('slug', $slug)->update(['sort_order' => $order]);
        }

        // Set any remaining categories without sort_order to default
        Category::whereNull('sort_order')->update(['sort_order' => 50]);
        Category::where('sort_order', 0)->update(['sort_order' => 50]);
    }
}
