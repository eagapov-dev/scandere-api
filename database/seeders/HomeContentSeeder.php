<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HeroSlide;
use App\Models\HomeFeature;
use App\Models\HomeStat;
use App\Models\HomeShowcase;

class HomeContentSeeder extends Seeder
{
    public function run(): void
    {
        // Hero Slides
        HeroSlide::create([
            'title' => 'Launch Your Business With Confidence',
            'subtitle' => 'Professional templates, checklists, and guides designed for small business owners.',
            'cta_text' => 'Browse Products',
            'cta_link' => '/products',
            'bg_gradient' => 'from-brand-700 via-brand-800 to-brand-900',
            'icon' => 'FiTrendingUp',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        HeroSlide::create([
            'title' => 'Bundle Deal — Save Over $7!',
            'subtitle' => 'Get all three essential resources for just $24.99. Everything you need to start strong.',
            'cta_text' => 'View Bundle',
            'cta_link' => '/products#bundle',
            'bg_gradient' => 'from-emerald-600 via-emerald-700 to-emerald-900',
            'icon' => 'FiPackage',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        HeroSlide::create([
            'title' => 'Download Instantly After Purchase',
            'subtitle' => 'Secure payment via Stripe. Access your files immediately from your dashboard.',
            'cta_text' => 'Get Started',
            'cta_link' => '/register',
            'bg_gradient' => 'from-violet-600 via-violet-700 to-violet-900',
            'icon' => 'FiDownload',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        HeroSlide::create([
            'title' => 'Trusted by 1000+ Entrepreneurs',
            'subtitle' => 'Join successful business owners who launched with our proven templates and resources.',
            'cta_text' => 'See Success Stories',
            'cta_link' => '/products',
            'bg_gradient' => 'from-orange-600 via-orange-700 to-orange-900',
            'icon' => 'FiAward',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // Features (Why Choose Us)
        HomeFeature::create([
            'icon' => 'FiZap',
            'title' => 'Instant Access',
            'description' => 'Download immediately after purchase, no waiting',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        HomeFeature::create([
            'icon' => 'FiLock',
            'title' => 'Secure Payments',
            'description' => 'Protected by Stripe, your data is safe',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        HomeFeature::create([
            'icon' => 'FiStar',
            'title' => 'Premium Quality',
            'description' => 'Professionally designed and tested templates',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        HomeFeature::create([
            'icon' => 'FiClock',
            'title' => 'Lifetime Updates',
            'description' => 'Get free updates and improvements forever',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // Stats (Testimonials)
        HomeStat::create([
            'value' => '1000+',
            'label' => 'Happy Customers',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        HomeStat::create([
            'value' => '15+',
            'label' => 'Premium Templates',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        HomeStat::create([
            'value' => '4.9/5',
            'label' => 'Average Rating',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        HomeStat::create([
            'value' => '24/7',
            'label' => 'Download Access',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        // Showcases (Zig-zag section)
        HomeShowcase::create([
            'title' => 'Step-by-Step Business Launch Checklist',
            'description' => 'Never miss a critical step when starting your business. Our comprehensive PDF checklist guides you through registration, licensing, banking, insurance, and your first 90 days. Used by over 500+ entrepreneurs.',
            'icon' => 'FiCheckCircle',
            'gradient' => 'from-blue-500 to-cyan-500',
            'features' => ['Complete startup roadmap', '90-day action plan', 'Legal compliance guide'],
            'reverse' => false,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        HomeShowcase::create([
            'title' => 'Professional Financial Templates',
            'description' => 'Take control of your finances with our ready-to-use Excel template. Track budgets, forecast revenue, analyze cash flow, and monitor profit — all with built-in formulas. No accounting degree required.',
            'icon' => 'FiCreditCard',
            'gradient' => 'from-green-500 to-emerald-500',
            'features' => ['Budget tracking sheets', 'Cash flow forecasting', 'Profit & loss templates'],
            'reverse' => true,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        HomeShowcase::create([
            'title' => 'Complete Marketing Strategy Guide',
            'description' => 'Build a marketing engine that drives real results. From social media to SEO, email campaigns to paid ads — this comprehensive Word document is your 12-month roadmap to sustainable growth.',
            'icon' => 'FiTarget',
            'gradient' => 'from-purple-500 to-pink-500',
            'features' => ['12-month content calendar', 'Social media templates', 'SEO optimization guide'],
            'reverse' => false,
            'sort_order' => 2,
            'is_active' => true,
        ]);
    }
}
