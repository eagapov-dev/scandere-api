<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $title = fake()->words(3, true);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 9.99, 99.99),
            'category_id' => Category::factory(),
            'is_free' => false,
            'show_on_homepage' => fake()->boolean(20),
            'is_active' => true,
            'file_path' => 'files/' . Str::random(10) . '.zip',
            'file_name' => Str::random(10) . '.zip',
            'file_size' => fake()->numberBetween(1000000, 10000000),
            'file_type' => 'application/zip',
            'preview_image' => fake()->imageUrl(),
            'download_count' => fake()->numberBetween(0, 1000),
            'sort_order' => 0,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_on_homepage' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
