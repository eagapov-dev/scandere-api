<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/email/verify",
     *     summary="Verify email address",
     *     description="Verify user's email address using verification token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token"},
     *             @OA\Property(property="token", type="string", description="Email verification token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email verified successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid or expired token",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid or expired verification link.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function verify(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        try {
            $data = json_decode(base64_decode($request->token), true);

            if (!$data || !isset($data['id'], $data['hash'], $data['expires'])) {
                return response()->json(['message' => 'Invalid verification link.'], 400);
            }

            // Check if expired
            if (Carbon::createFromTimestamp($data['expires'])->isPast()) {
                return response()->json(['message' => 'Verification link has expired.'], 400);
            }

            $user = User::findOrFail($data['id']);

            // Verify hash
            if (!hash_equals($data['hash'], sha1($user->email))) {
                return response()->json(['message' => 'Invalid verification link.'], 400);
            }

            // Check if already verified
            if ($user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email already verified.'], 200);
            }

            // Mark as verified
            $user->markEmailAsVerified();

            return response()->json(['message' => 'Email verified successfully.']);

        } catch (\Exception $e) {
            Log::error('Email verification failed', [
                'error' => $e->getMessage(),
                'token' => $request->token
            ]);

            return response()->json(['message' => 'Invalid verification link.'], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/email/resend",
     *     summary="Resend verification email",
     *     description="Resend email verification link to authenticated user",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Verification email sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Verification email sent.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Email already verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email already verified.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function resend(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        $user->notify(new VerifyEmail());

        return response()->json(['message' => 'Verification email sent.']);
    }

    /**
     * @OA\Get(
     *     path="/api/auth/email/verification-status",
     *     summary="Check email verification status",
     *     description="Check if the authenticated user's email is verified",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Verification status",
     *         @OA\JsonContent(
     *             @OA\Property(property="verified", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function status(Request $request)
    {
        return response()->json([
            'verified' => $request->user()->hasVerifiedEmail()
        ]);
    }
}
