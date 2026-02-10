<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);

        Subscriber::updateOrCreate(
            ['email' => $request->email],
            [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'source' => $request->input('source', 'newsletter'),
                'ip_address' => $request->ip(),
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]
        );

        return response()->json(['message' => 'Thank you for subscribing!']);
    }

    public function unsubscribe(string $email)
    {
        Subscriber::where('email', $email)->update(['unsubscribed_at' => now()]);
        return response()->json(['message' => 'You have been unsubscribed.']);
    }
}
