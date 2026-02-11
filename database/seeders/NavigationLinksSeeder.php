<?php

namespace Database\Seeders;

use App\Models\NavigationLink;
use Illuminate\Database\Seeder;

class NavigationLinksSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate table first to avoid duplicates
        NavigationLink::truncate();

        $headerLinks = [
            ['label' => 'Home', 'url' => '/', 'location' => 'header', 'sort_order' => 1, 'is_active' => true],
            ['label' => 'Products', 'url' => '/products', 'location' => 'header', 'sort_order' => 2, 'is_active' => true],
            ['label' => 'FAQ', 'url' => '/faq', 'location' => 'header', 'sort_order' => 3, 'is_active' => true],
            ['label' => 'Contact', 'url' => '/contact', 'location' => 'header', 'sort_order' => 4, 'is_active' => true],
        ];

        $footerLinks = [
            // Company column - Products are loaded dynamically from categories
            ['label' => 'Contact', 'url' => '/contact', 'location' => 'footer', 'sort_order' => 1, 'is_active' => true],
            ['label' => 'FAQ', 'url' => '/faq', 'location' => 'footer', 'sort_order' => 2, 'is_active' => true],
            ['label' => 'My Account', 'url' => '/dashboard', 'location' => 'footer', 'sort_order' => 3, 'is_active' => true],
        ];

        foreach (array_merge($headerLinks, $footerLinks) as $link) {
            NavigationLink::create($link);
        }
    }
}
