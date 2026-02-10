<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Order, Product, Subscriber, User, ContactMessage};

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'total_users' => User::count(),
            'total_subscribers' => Subscriber::active()->count(),
            'total_products' => Product::count(),
            'total_orders' => Order::completed()->count(),
            'total_revenue' => (float) Order::completed()->sum('total'),
            'unread_messages' => ContactMessage::where('is_read', false)->count(),
            'recent_orders' => Order::completed()->with(['user:id,first_name,last_name,email'])->latest('paid_at')->take(10)->get(),
            'recent_subscribers' => Subscriber::active()->latest('subscribed_at')->take(10)->get(['id', 'email', 'first_name', 'source', 'subscribed_at']),
        ]);
    }
}
