<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;

class CommentController extends Controller
{
    public function index()
    {
        return response()->json(
            Comment::with(['user:id,first_name,last_name', 'product:id,title'])
                ->latest()->paginate(30)
        );
    }

    public function approve(Comment $comment)
    {
        $comment->update(['is_approved' => true]);
        return response()->json(['message' => 'Approved.']);
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
        return response()->json(['message' => 'Deleted.']);
    }
}
