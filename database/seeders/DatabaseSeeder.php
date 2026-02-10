<?php

namespace Database\Seeders;

use App\Models\{Bundle, Category, Product, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@scandereai.store',
            'password' => Hash::make('Scandere!Admin2024'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Categories
        $templates = Category::create(['name' => 'Templates', 'slug' => 'templates', 'sort_order' => 1]);
        $checklists = Category::create(['name' => 'Checklists', 'slug' => 'checklists', 'sort_order' => 2]);
        $guides = Category::create(['name' => 'Guides', 'slug' => 'guides', 'sort_order' => 3]);

        // Create sample files
        $this->createSampleFiles();

        // Product 1: $7.99
        $p1 = Product::create([
            'category_id' => $checklists->id,
            'title' => 'Small Business Launch Checklist',
            'slug' => 'small-business-launch-checklist',
            'short_description' => 'A comprehensive step-by-step checklist to launch your small business with confidence.',
            'description' => "This detailed checklist covers everything you need to start your small business:\n\n• Business registration & legal structure\n• Tax ID & licensing requirements\n• Banking & bookkeeping setup\n• Insurance essentials\n• Marketing launch plan\n• First 90 days timeline\n\nDownload this PDF and check off each item as you build your business foundation.",
            'price' => 7.99,
            'is_active' => true,
            'is_featured' => true,
            'file_path' => 'products/sample-checklist.pdf',
            'file_name' => 'Small-Business-Launch-Checklist.pdf',
            'file_size' => 1024,
            'file_type' => 'pdf',
            'sort_order' => 1,
        ]);

        // Product 2: $9.99
        $p2 = Product::create([
            'category_id' => $templates->id,
            'title' => 'Financial Planning Template',
            'slug' => 'financial-planning-template',
            'short_description' => 'Professional Excel template for budgeting, forecasting, and financial tracking.',
            'description' => "Take control of your business finances with this ready-to-use Excel template:\n\n• Monthly budget tracker\n• Revenue & expense forecasting\n• Cash flow projections\n• Break-even analysis\n• Profit & loss summary\n• Dashboard with charts\n\nSimply fill in your numbers and let the formulas do the work.",
            'price' => 9.99,
            'is_active' => true,
            'is_featured' => true,
            'file_path' => 'products/sample-template.xlsx',
            'file_name' => 'Financial-Planning-Template.xlsx',
            'file_size' => 2048,
            'file_type' => 'xlsx',
            'sort_order' => 2,
        ]);

        // Product 3: $14.99
        $p3 = Product::create([
            'category_id' => $guides->id,
            'title' => 'Complete Marketing Strategy Guide',
            'slug' => 'complete-marketing-strategy-guide',
            'short_description' => 'In-depth Word document covering digital marketing strategies for small businesses.',
            'description' => "A comprehensive marketing playbook designed for small business owners:\n\n• Social media strategy & content calendar\n• Email marketing campaigns\n• SEO fundamentals & local search\n• Paid advertising (Google Ads, Facebook Ads)\n• Brand identity & messaging\n• Analytics & measuring ROI\n• 12-month marketing roadmap\n\nFollow this guide to build a marketing engine that drives real results.",
            'price' => 14.99,
            'is_active' => true,
            'is_featured' => true,
            'file_path' => 'products/sample-guide.docx',
            'file_name' => 'Complete-Marketing-Strategy-Guide.docx',
            'file_size' => 3072,
            'file_type' => 'docx',
            'sort_order' => 3,
        ]);

        // Bundle: All 3 for $24.99
        $bundle = Bundle::create([
            'title' => 'Complete Business Starter Pack',
            'slug' => 'complete-business-starter-pack',
            'description' => 'Get all three essential business resources at a discount. Save over $7 compared to buying individually!',
            'price' => 24.99,
            'original_price' => 32.97,
            'is_active' => true,
        ]);

        $bundle->products()->attach([$p1->id, $p2->id, $p3->id]);
    }

    private function createSampleFiles(): void
    {
        $disk = Storage::disk('private');
        $disk->makeDirectory('products');

        // Simple placeholder files
        $disk->put('products/sample-checklist.pdf', 'Sample PDF - Small Business Launch Checklist');
        $disk->put('products/sample-template.xlsx', 'Sample XLSX - Financial Planning Template');
        $disk->put('products/sample-guide.docx', 'Sample DOCX - Complete Marketing Strategy Guide');
    }
}
