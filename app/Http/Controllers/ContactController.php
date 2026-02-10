<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Subscriber;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $v = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:5000',
            'subscribe_newsletter' => 'boolean',
        ]);

        ContactMessage::create($v);

        // Auto-subscribe if checkbox checked
        if ($request->boolean('subscribe_newsletter', true)) {
            Subscriber::updateOrCreate(
                ['email' => $v['email']],
                [
                    'first_name' => $v['first_name'],
                    'last_name' => $v['last_name'],
                    'source' => 'contact_form',
                    'subscribed_at' => now(),
                    'unsubscribed_at' => null,
                ]
            );
        }

        return response()->json(['message' => 'Thank you! We\'ll be in touch soon.']);
    }
}
