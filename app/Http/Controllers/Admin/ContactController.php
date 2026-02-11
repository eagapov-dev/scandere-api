<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/messages",
     *     summary="List all contact messages (admin)",
     *     description="Get paginated list of all contact form submissions. Requires admin authentication.",
     *     tags={"Admin - Messages"},
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
     *         description="Paginated messages list",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="first_name", type="string"),
     *                     @OA\Property(property="last_name", type="string"),
     *                     @OA\Property(property="email", type="string"),
     *                     @OA\Property(property="message", type="string"),
     *                     @OA\Property(property="is_read", type="boolean"),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer", example=30)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden (not admin)")
     * )
     */
    public function index()
    {
        return response()->json(ContactMessage::latest()->paginate(30));
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/messages/{id}/read",
     *     summary="Mark message as read (admin)",
     *     description="Mark a contact message as read. Requires admin authentication.",
     *     tags={"Admin - Messages"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Message ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message marked as read",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Marked as read.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden (not admin)"),
     *     @OA\Response(response=404, description="Message not found")
     * )
     */
    public function markRead(ContactMessage $message)
    {
        $message->update(['is_read' => true]);
        return response()->json(['message' => 'Marked as read.']);
    }
}
