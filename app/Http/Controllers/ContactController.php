<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Subscriber;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/contact",
     *     summary="Submit contact form",
     *     description="Send a contact message. Optionally subscribe to newsletter (default: true). Rate limited to 5 requests per minute.",
     *     tags={"Contact"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "email", "message"},
     *             @OA\Property(property="first_name", type="string", maxLength=255, example="John", description="Sender's first name"),
     *             @OA\Property(property="last_name", type="string", maxLength=255, example="Doe", description="Sender's last name"),
     *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="john.doe@example.com", description="Sender's email address"),
     *             @OA\Property(property="message", type="string", maxLength=5000, example="I'm interested in your business templates. Can you tell me more about customization options?", description="Contact message (max 5000 characters)"),
     *             @OA\Property(property="subscribe_newsletter", type="boolean", example=true, description="Subscribe to newsletter (default: true)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Thank you! We'll be in touch soon.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests (rate limit exceeded: 5 per minute)"
     *     )
     * )
     */
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

        // Send confirmation email to user
        \Mail::to($v['email'])
            ->queue(new \App\Mail\ContactFormReceived(
                $v['first_name'] . ' ' . $v['last_name'],
                $v['email'],
                $v['message']
            ));

        // Send notification to admin
        \Mail::to(config('services.admin.notification_email'))
            ->queue(new \App\Mail\ContactFormAdminNotification(
                $v['first_name'] . ' ' . $v['last_name'],
                $v['email'],
                $v['message']
            ));

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
