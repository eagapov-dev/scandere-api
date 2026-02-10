<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;

class SubscriberController extends Controller
{
    public function index()
    {
        return response()->json([
            'subscribers' => Subscriber::latest('subscribed_at')->paginate(30),
            'total_active' => Subscriber::active()->count(),
            'total_unsubscribed' => Subscriber::whereNotNull('unsubscribed_at')->count(),
        ]);
    }

    public function export()
    {
        $subs = Subscriber::active()->get(['email', 'first_name', 'last_name', 'source', 'subscribed_at']);
        $csv = "Email,First Name,Last Name,Source,Subscribed At\n";
        foreach ($subs as $s) {
            $csv .= "\"{$s->email}\",\"{$s->first_name}\",\"{$s->last_name}\",\"{$s->source}\",\"{$s->subscribed_at}\"\n";
        }
        return response($csv)->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="subscribers-' . date('Y-m-d') . '.csv"');
    }
}
