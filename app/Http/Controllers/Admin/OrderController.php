<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        return response()->json([
            'orders' => Order::with(['user:id,first_name,last_name,email', 'items.product:id,title'])->latest()->paginate(30),
            'total_revenue' => (float) Order::completed()->sum('total'),
            'total_orders' => Order::completed()->count(),
        ]);
    }
}
