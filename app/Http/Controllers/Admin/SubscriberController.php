<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;

class SubscriberController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/subscribers",
     *     summary="List all subscribers (admin)",
     *     description="Get paginated list of newsletter subscribers with statistics (active vs unsubscribed counts). Requires admin authentication.",
     *     tags={"Admin - Subscribers"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscribers list with statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="subscribers", type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer", example=30),
     *                 @OA\Property(property="total", type="integer")
     *             ),
     *             @OA\Property(property="total_active", type="integer", example=320, description="Number of active subscribers"),
     *             @OA\Property(property="total_unsubscribed", type="integer", example=45, description="Number of unsubscribed users")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden (not admin)")
     * )
     */
    public function index()
    {
        return response()->json([
            'subscribers' => Subscriber::latest('subscribed_at')->paginate(30),
            'total_active' => Subscriber::active()->count(),
            'total_unsubscribed' => Subscriber::whereNotNull('unsubscribed_at')->count(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/admin/subscribers/export",
     *     summary="Export subscribers to CSV (admin)",
     *     description="Download all active subscribers as CSV file. Includes email, first name, last name, source, and subscription date. Requires admin authentication.",
     *     tags={"Admin - Subscribers"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="CSV file download",
     *         @OA\MediaType(
     *             mediaType="text/csv",
     *             @OA\Schema(
     *                 type="string",
     *                 format="binary",
     *                 example="Email,First Name,Last Name,Source,Subscribed At\njohn@example.com,John,Doe,newsletter,2024-01-15 10:30:00"
     *             )
     *         ),
     *         @OA\Header(
     *             header="Content-Disposition",
     *             description="Filename for download",
     *             @OA\Schema(type="string", example="attachment; filename=subscribers-2024-01-15.csv")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden (not admin)")
     * )
     */
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
