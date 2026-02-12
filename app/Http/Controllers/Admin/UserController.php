<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get all users with pagination and filtering
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search by name or email
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by admin status
        if ($request->has('is_admin')) {
            $query->where('is_admin', $request->is_admin);
        }

        // Filter by verification status
        if ($request->has('verified')) {
            if ($request->verified === 'true') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query->orderBy('created_at', 'desc')
                       ->paginate($request->per_page ?? 20);

        return response()->json($users);
    }

    /**
     * Update user admin status
     */
    public function updateAdminStatus(Request $request, User $user)
    {
        $request->validate([
            'is_admin' => 'required|boolean'
        ]);

        $user->update(['is_admin' => $request->is_admin]);

        return response()->json([
            'message' => 'User admin status updated successfully.',
            'user' => $user
        ]);
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account.'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.'
        ]);
    }
}
