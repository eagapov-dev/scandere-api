<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Product;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products/{product_id}/comments",
     *     summary="Get product comments",
     *     description="Get paginated list of approved comments for a product (Q&A style)",
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated comments",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="body", type="string", example="Great product! Very helpful for my business."),
     *                     @OA\Property(property="user", type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="first_name", type="string", example="John"),
     *                         @OA\Property(property="last_name", type="string", example="Doe")
     *                     ),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
     *                 )
     *             ),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer", example=20),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     */
    public function index(Product $product)
    {
        $comments = $product->comments()->published()
            ->with('user:id,first_name,last_name')
            ->latest()->paginate(20);

        return response()->json($comments);
    }

    /**
     * @OA\Post(
     *     path="/api/products/{product_id}/comments",
     *     summary="Post a comment on product",
     *     description="Add a comment/question to a product. Comment will be automatically approved (requires authentication).",
     *     tags={"Comments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"body"},
     *             @OA\Property(property="body", type="string", maxLength=2000, example="This looks great! Can I use this template for my consulting business?", description="Comment text (max 2000 characters)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Comment posted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="body", type="string"),
     *             @OA\Property(property="is_approved", type="boolean", example=true),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="first_name", type="string"),
     *                 @OA\Property(property="last_name", type="string")
     *             ),
     *             @OA\Property(property="created_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error (body required, max 2000 chars)"
     *     )
     * )
     */
    public function store(Request $request, Product $product)
    {
        $request->validate(['body' => 'required|string|max:2000']);

        $comment = $product->comments()->create([
            'user_id' => auth()->id(),
            'body' => $request->body,
            'status' => 'draft',
        ]);

        $comment->load('user:id,first_name,last_name');

        // Send confirmation email
        \Mail::to(auth()->user()->email)
            ->queue(new \App\Mail\CommentSubmitted($comment));

        return response()->json($comment, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/recent-qa",
     *     summary="Get recent Q&A with answers",
     *     description="Get latest published Q&A pairs (comments with admin answers) for homepage",
     *     tags={"Comments"},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of Q&A to return (default 6)",
     *         required=false,
     *         @OA\Schema(type="integer", example=6)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recent Q&A list",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="body", type="string", description="Question"),
     *                 @OA\Property(property="answer", type="string", description="Admin answer"),
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="product", type="object"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function recentQA(Request $request)
    {
        $limit = $request->query('limit', 6);

        $qa = Comment::published()
            ->onHomepage()
            ->whereNotNull('answer')
            ->where('answer', '!=', '')
            ->with([
                'user:id,first_name,last_name',
                'product:id,title,slug'
            ])
            ->latest()
            ->limit($limit)
            ->get();

        return response()->json($qa);
    }

    /**
     * @OA\Post(
     *     path="/api/comments/general",
     *     summary="Post a general question",
     *     description="Post a general question (not related to specific product) from homepage",
     *     tags={"Comments"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"body"},
     *             @OA\Property(property="body", type="string", maxLength=2000, example="What payment methods do you accept?")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Question posted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function storeGeneral(Request $request)
    {
        $request->validate(['body' => 'required|string|max:2000']);

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'product_id' => null, // General question, not tied to product
            'body' => $request->body,
            'status' => 'draft',
        ]);

        $comment->load('user:id,first_name,last_name');

        return response()->json($comment, 201);
    }
}
