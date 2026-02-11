<?php

namespace App\Http\Controllers;

use App\Models\HeroSlide;
use App\Models\HomeFeature;
use App\Models\HomeStat;
use App\Models\HomeShowcase;
use App\Models\SocialLink;
use App\Models\NavigationLink;

class HomeController extends Controller
{
    public function content()
    {
        return response()->json([
            'hero_slides' => HeroSlide::active()->orderBy('sort_order')->orderBy('id')->get(),
            'features' => HomeFeature::active()->orderBy('sort_order')->orderBy('id')->get(),
            'stats' => HomeStat::active()->orderBy('sort_order')->orderBy('id')->get(),
            'showcases' => HomeShowcase::active()->orderBy('sort_order')->orderBy('id')->get(),
            'social_links' => SocialLink::active()->orderBy('sort_order')->orderBy('id')->get(),
            'header_links' => NavigationLink::active()->header()->orderBy('sort_order')->orderBy('id')->get(),
            'footer_links' => NavigationLink::active()->footer()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }
}
