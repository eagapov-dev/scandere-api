<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Product;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Product $product)
    {
        $comments = $product->comments()->approved()
            ->with('user:id,first_name,last_name')
            ->latest()->paginate(20);

        return response()->json($comments);
    }

    public function store(Request $request, Product $product)
    {
        $request->validate(['body' => 'required|string|max:2000']);

        $comment = $product->comments()->create([
            'user_id' => auth()->id(),
            'body' => $request->body,
            'is_approved' => true,
        ]);

        $comment->load('user:id,first_name,last_name');

        return response()->json($comment, 201);
    }
}
