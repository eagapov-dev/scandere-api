<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class BundlesCategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::firstOrCreate(
            ['slug' => 'bundles'],
            [
                'name' => 'Bundles',
                'description' => 'Bundle deals - get multiple products together and save',
                'sort_order' => 999,
                'is_active' => true
            ]
        );
    }
}
