<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/admin/comments",
     *     summary="List all comments (admin)",
     *     description="Get paginated list of all comments with user and product information for moderation. Requires admin authentication.",
     *     tags={"Admin - Comments"},
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
     *         description="Paginated comments list",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="body", type="string"),
     *                     @OA\Property(property="is_approved", type="boolean"),
     *                     @OA\Property(property="user", type="object"),
     *                     @OA\Property(property="product", type="object"),
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
        return response()->json(
            Comment::with(['user:id,first_name,last_name', 'product:id,title'])
                ->latest()->paginate(30)
        );
    }

    /**
     * @OA\Put(
     *     path="/api/admin/comments/{id}",
     *     summary="Update comment (edit question, add answer, change status)",
     *     description="Update comment body, add admin answer, or change status. Requires admin authentication.",
     *     tags={"Admin - Comments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="body", type="string", maxLength=2000, description="Question text (optional)"),
     *             @OA\Property(property="answer", type="string", maxLength=2000, description="Admin answer (optional)"),
     *             @OA\Property(property="status", type="string", enum={"draft", "published"}, description="Comment status (optional)"),
     *             @OA\Property(property="show_on_homepage", type="boolean", description="Show on homepage (optional)")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Comment updated"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden (not admin)"),
     *     @OA\Response(response=404, description="Comment not found")
     * )
     */
    public function update(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'body' => 'nullable|string|max:2000',
            'answer' => 'nullable|string|max:2000',
            'status' => 'nullable|in:draft,published',
            'show_on_homepage' => 'nullable|boolean',
        ]);

        $comment->update($validated);

        return response()->json($comment->load(['user:id,first_name,last_name', 'product:id,title']));
    }

    /**
     * @OA\Patch(
     *     path="/api/admin/comments/{id}/approve",
     *     summary="Approve comment (admin)",
     *     description="Approve a pending comment to make it visible publicly. Requires admin authentication.",
     *     tags={"Admin - Comments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Comment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment approved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Comment published.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden (not admin)"),
     *     @OA\Response(response=404, description="Comment not found")
     * )
     */
    public function approve(Comment $comment)
    {
        $comment->update(['status' => 'published']);

        // Send approval notification to user
        \Mail::to($comment->user->email)
            ->queue(new \App\Mail\CommentApproved($comment));

        return response()->json(['message' => 'Comment published.']);
    }

    /**
     * @OA\Delete(
     *     path="/api/admin/comments/{id}",
     *     summary="Delete comment (admin)",
     *     description="Permanently delete a comment. Requires admin authentication.",
     *     tags={"Admin - Comments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Comment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Deleted.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden (not admin)"),
     *     @OA\Response(response=404, description="Comment not found")
     * )
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();
        return response()->json(['message' => 'Deleted.']);
    }
}
