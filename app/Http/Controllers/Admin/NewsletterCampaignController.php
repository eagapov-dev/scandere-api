<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterCampaign;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class NewsletterCampaignController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/admin/newsletter/send",
     *     summary="Send newsletter campaign to all subscribers",
     *     description="Send a manual newsletter campaign to all active subscribers. Requires admin authentication. Rate limited to prevent abuse.",
     *     tags={"Admin - Newsletter"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"subject", "content"},
     *             @OA\Property(property="subject", type="string", example="New AI Tools Released!", description="Email subject line"),
     *             @OA\Property(property="content", type="string", example="We're excited to announce new AI tools available in our store...", description="Email message content (supports line breaks)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Newsletter campaign queued successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Newsletter campaign queued for 150 subscribers"),
     *             @OA\Property(property="subscribers_count", type="integer", example=150)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (not admin)"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
        ]);

        // Get all active subscribers (not unsubscribed)
        $subscribers = Subscriber::whereNull('unsubscribed_at')
            ->get();

        if ($subscribers->isEmpty()) {
            return response()->json([
                'message' => 'No active subscribers found',
                'subscribers_count' => 0
            ]);
        }

        // Queue emails to all subscribers
        foreach ($subscribers as $subscriber) {
            Mail::to($subscriber->email)
                ->queue(new NewsletterCampaign(
                    $validated['subject'],
                    $validated['content'],
                    $subscriber->email
                ));
        }

        return response()->json([
            'message' => "Newsletter campaign queued for {$subscribers->count()} subscribers",
            'subscribers_count' => $subscribers->count()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/newsletter/stats",
     *     summary="Get newsletter statistics",
     *     description="Get subscriber statistics. Requires admin authentication.",
     *     tags={"Admin - Newsletter"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Newsletter statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_subscribers", type="integer", example=250),
     *             @OA\Property(property="active_subscribers", type="integer", example=200),
     *             @OA\Property(property="unsubscribed", type="integer", example=50)
     *         )
     *     )
     * )
     */
    public function stats()
    {
        $totalSubscribers = Subscriber::count();
        $activeSubscribers = Subscriber::whereNull('unsubscribed_at')->count();
        $unsubscribed = Subscriber::whereNotNull('unsubscribed_at')->count();

        return response()->json([
            'total_subscribers' => $totalSubscribers,
            'active_subscribers' => $activeSubscribers,
            'unsubscribed' => $unsubscribed,
        ]);
    }
}
