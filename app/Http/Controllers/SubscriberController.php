<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/subscribe",
     *     summary="Subscribe to newsletter",
     *     description="Subscribe email to newsletter. Rate limited to 5 requests per minute. If email already exists, updates the subscription.",
     *     tags={"Newsletter"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com", description="Email address to subscribe"),
     *             @OA\Property(property="first_name", type="string", example="John", description="Optional first name"),
     *             @OA\Property(property="last_name", type="string", example="Doe", description="Optional last name"),
     *             @OA\Property(property="source", type="string", example="newsletter", description="Subscription source (default: newsletter)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscribed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Thank you for subscribing!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error (invalid email format)"
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Too many requests (rate limit exceeded)"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);

        $subscriber = Subscriber::updateOrCreate(
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

        // Send welcome email
        \Mail::to($request->email)
            ->queue(new \App\Mail\NewsletterWelcome(
                $request->email,
                $request->first_name
            ));

        return response()->json(['message' => 'Thank you for subscribing!']);
    }

    /**
     * @OA\Get(
     *     path="/api/unsubscribe/{email}",
     *     summary="Unsubscribe from newsletter",
     *     description="Unsubscribe an email address from newsletter (marks as unsubscribed with timestamp)",
     *     tags={"Newsletter"},
     *     @OA\Parameter(
     *         name="email",
     *         in="path",
     *         description="Email address to unsubscribe",
     *         required=true,
     *         @OA\Schema(type="string", format="email", example="john.doe@example.com")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unsubscribed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You have been unsubscribed.")
     *         )
     *     )
     * )
     */
    public function unsubscribe(string $email)
    {
        Subscriber::where('email', $email)->update(['unsubscribed_at' => now()]);
        return response()->json(['message' => 'You have been unsubscribed.']);
    }
}
