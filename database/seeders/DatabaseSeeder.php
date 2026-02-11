<?php

namespace Database\Seeders;

use App\Models\{Bundle, Category, Comment, ContactMessage, Order, OrderItem, Product, Subscriber, User};
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
        $documents = Category::create(['name' => 'Documents', 'slug' => 'documents', 'sort_order' => 3]);

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
            'original_price' => 9.99,
            'is_active' => true,
            'show_on_homepage' => true,
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
            'original_price' => 12.99,
            'is_active' => true,
            'show_on_homepage' => true,
            'file_path' => 'products/sample-template.xlsx',
            'file_name' => 'Financial-Planning-Template.xlsx',
            'file_size' => 2048,
            'file_type' => 'xlsx',
            'sort_order' => 2,
        ]);

        // Product 3: $14.99
        $p3 = Product::create([
            'category_id' => $documents->id,
            'title' => 'Complete Marketing Strategy Guide',
            'slug' => 'complete-marketing-strategy-guide',
            'short_description' => 'In-depth Word document covering digital marketing strategies for small businesses.',
            'description' => "A comprehensive marketing playbook designed for small business owners:\n\n• Social media strategy & content calendar\n• Email marketing campaigns\n• SEO fundamentals & local search\n• Paid advertising (Google Ads, Facebook Ads)\n• Brand identity & messaging\n• Analytics & measuring ROI\n• 12-month marketing roadmap\n\nFollow this guide to build a marketing engine that drives real results.",
            'price' => 14.99,
            'original_price' => 19.99,
            'is_active' => true,
            'show_on_homepage' => true,
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

        // Create test data
        $this->createTestUsers();
        $this->createSubscribers();
        $this->createContactMessages();
        $this->createComments([$p1, $p2, $p3]);
        $this->createOrders([$p1, $p2, $p3]);
    }

    private function createTestUsers(): void
    {
        // Regular test users
        User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@gmail.com',
            'password' => Hash::make('Password123'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        User::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@gmail.com',
            'password' => Hash::make('Password123'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        User::create([
            'first_name' => 'Mike',
            'last_name' => 'Johnson',
            'email' => 'mike@gmail.com',
            'password' => Hash::make('Password123'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);
    }

    private function createSubscribers(): void
    {
        $emails = [
            ['email' => 'subscriber1@gmail.com', 'first_name' => 'Alice', 'last_name' => 'Williams'],
            ['email' => 'subscriber2@gmail.com', 'first_name' => 'Bob', 'last_name' => 'Brown'],
            ['email' => 'subscriber3@gmail.com', 'first_name' => 'Carol', 'last_name' => 'Davis'],
            ['email' => 'subscriber4@gmail.com', 'first_name' => 'David', 'last_name' => 'Miller'],
            ['email' => 'subscriber5@gmail.com', 'first_name' => 'Emma', 'last_name' => 'Wilson'],
            ['email' => 'john@gmail.com', 'first_name' => 'John', 'last_name' => 'Doe'],
            ['email' => 'jane@gmail.com', 'first_name' => 'Jane', 'last_name' => 'Smith'],
        ];

        foreach ($emails as $data) {
            Subscriber::create([
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'source' => 'newsletter',
                'subscribed_at' => now()->subDays(rand(1, 30)),
                'ip_address' => '127.0.0.1',
            ]);
        }

        // One unsubscribed user
        Subscriber::create([
            'email' => 'unsubscribed@gmail.com',
            'first_name' => 'Former',
            'last_name' => 'Subscriber',
            'source' => 'newsletter',
            'subscribed_at' => now()->subDays(60),
            'unsubscribed_at' => now()->subDays(10),
            'ip_address' => '127.0.0.1',
        ]);
    }

    private function createContactMessages(): void
    {
        $messages = [
            [
                'first_name' => 'Sarah',
                'last_name' => 'Connor',
                'email' => 'sarah@gmail.com',
                'message' => 'I have a question about your Financial Planning Template. Does it work with Google Sheets as well, or is it Excel only?',
            ],
            [
                'first_name' => 'Tom',
                'last_name' => 'Anderson',
                'email' => 'tom@gmail.com',
                'message' => 'Great products! I purchased the marketing guide and it helped me a lot. Do you plan to release more advanced marketing materials?',
            ],
            [
                'first_name' => 'Lisa',
                'last_name' => 'Martinez',
                'email' => 'lisa@gmail.com',
                'message' => 'I am having trouble downloading my purchase. Can you help?',
            ],
            [
                'first_name' => 'Kevin',
                'last_name' => 'Lee',
                'email' => 'kevin@gmail.com',
                'message' => 'Do you offer refunds if the product does not meet my expectations?',
            ],
        ];

        foreach ($messages as $msg) {
            ContactMessage::create($msg);
        }
    }

    private function createComments(array $products): void
    {
        $users = User::where('is_admin', false)->get();

        // Comments for Product 1 (Checklist)
        Comment::create([
            'user_id' => $users[0]->id,
            'product_id' => $products[0]->id,
            'body' => 'This checklist saved me so much time! Very comprehensive and easy to follow.',
            'status' => 'published',
            'created_at' => now()->subDays(5),
        ]);

        Comment::create([
            'user_id' => $users[1]->id,
            'product_id' => $products[0]->id,
            'body' => 'Exactly what I needed to organize my business launch. Highly recommend!',
            'status' => 'published',
            'created_at' => now()->subDays(3),
        ]);

        // Comments for Product 2 (Financial Template)
        Comment::create([
            'user_id' => $users[0]->id,
            'product_id' => $products[1]->id,
            'body' => 'Great template! The formulas are really helpful. Worth every penny.',
            'status' => 'published',
            'created_at' => now()->subDays(7),
        ]);

        Comment::create([
            'user_id' => $users[2]->id,
            'product_id' => $products[1]->id,
            'body' => 'This template is professional and easy to customize. My accountant loves it too!',
            'status' => 'published',
            'created_at' => now()->subDays(2),
        ]);

        // Comments for Product 3 (Marketing Guide)
        Comment::create([
            'user_id' => $users[1]->id,
            'product_id' => $products[2]->id,
            'body' => 'Very detailed guide. Implemented the social media strategy and already seeing results!',
            'status' => 'published',
            'created_at' => now()->subDays(10),
        ]);

        // Draft comment (pending approval)
        Comment::create([
            'user_id' => $users[2]->id,
            'product_id' => $products[2]->id,
            'body' => 'Just purchased, looking forward to reading it!',
            'status' => 'draft',
            'created_at' => now()->subHours(3),
        ]);

        // Comment with admin reply
        $comment = Comment::create([
            'user_id' => $users[0]->id,
            'product_id' => $products[0]->id,
            'body' => 'Is there a version for online businesses?',
            'status' => 'published',
            'created_at' => now()->subDays(15),
        ]);

        $comment->update([
            'answer' => 'Yes! The checklist includes sections applicable to both online and brick-and-mortar businesses. Most items work for any business type.',
        ]);
    }

    private function createOrders(array $products): void
    {
        $users = User::where('is_admin', false)->get();

        // Completed order - single product
        $order1 = Order::create([
            'user_id' => $users[0]->id,
            'total' => 7.99,
            'status' => 'completed',
            'payment_id' => 'test_payment_' . uniqid(),
            'paid_at' => now()->subDays(5),
            'created_at' => now()->subDays(5),
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $products[0]->id,
            'price' => 7.99,
            'quantity' => 1,
        ]);

        // Completed order - multiple products
        $order2 = Order::create([
            'user_id' => $users[1]->id,
            'total' => 24.98,
            'status' => 'completed',
            'payment_id' => 'test_payment_' . uniqid(),
            'paid_at' => now()->subDays(3),
            'created_at' => now()->subDays(3),
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $products[1]->id,
            'price' => 9.99,
            'quantity' => 1,
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $products[2]->id,
            'price' => 14.99,
            'quantity' => 1,
        ]);

        // Completed order - from bundle
        $order3 = Order::create([
            'user_id' => $users[2]->id,
            'total' => 24.99,
            'status' => 'completed',
            'payment_id' => 'test_payment_' . uniqid(),
            'paid_at' => now()->subDays(10),
            'created_at' => now()->subDays(10),
        ]);

        foreach ($products as $product) {
            OrderItem::create([
                'order_id' => $order3->id,
                'product_id' => $product->id,
                'price' => $product->price,
                'quantity' => 1,
            ]);
        }

        // Pending order
        $order4 = Order::create([
            'user_id' => $users[0]->id,
            'total' => 14.99,
            'status' => 'pending',
            'created_at' => now()->subHours(2),
        ]);

        OrderItem::create([
            'order_id' => $order4->id,
            'product_id' => $products[2]->id,
            'price' => 14.99,
            'quantity' => 1,
        ]);
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
