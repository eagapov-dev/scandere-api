<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SocialLink;

class SocialLinksSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate table first to avoid duplicates
        SocialLink::truncate();

        $links = [
            ['platform' => 'Facebook', 'url' => 'https://facebook.com', 'icon' => 'FaFacebookF', 'sort_order' => 0],
            ['platform' => 'Twitter', 'url' => 'https://twitter.com', 'icon' => 'FaTwitter', 'sort_order' => 1],
            ['platform' => 'Instagram', 'url' => 'https://instagram.com', 'icon' => 'FaInstagram', 'sort_order' => 2],
            ['platform' => 'LinkedIn', 'url' => 'https://linkedin.com', 'icon' => 'FaLinkedinIn', 'sort_order' => 3],
            ['platform' => 'YouTube', 'url' => 'https://youtube.com', 'icon' => 'FaYoutube', 'sort_order' => 4],
            ['platform' => 'Pinterest', 'url' => 'https://pinterest.com', 'icon' => 'FaPinterestP', 'sort_order' => 5],
            ['platform' => 'TikTok', 'url' => 'https://tiktok.com', 'icon' => 'FaTiktok', 'sort_order' => 6],
            ['platform' => 'Threads', 'url' => 'https://threads.net', 'icon' => 'FaThreads', 'sort_order' => 7],
            ['platform' => 'Reddit', 'url' => 'https://reddit.com', 'icon' => 'FaRedditAlien', 'sort_order' => 8],
        ];

        foreach ($links as $link) {
            SocialLink::create(array_merge($link, ['is_active' => true]));
        }
    }
}
