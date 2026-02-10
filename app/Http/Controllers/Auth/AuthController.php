<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $v = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $user = User::create([...$v, 'password' => Hash::make($v['password'])]);
        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'user' => [...$user->only('id', 'first_name', 'last_name', 'email', 'is_admin'), 'name' => $user->name],
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $user = Auth::user();
        return response()->json([
            'user' => [...$user->only('id', 'first_name', 'last_name', 'email', 'is_admin'), 'name' => $user->name],
            'token' => $user->createToken('auth')->plainTextToken,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }

    public function user(Request $request)
    {
        $u = $request->user();
        return response()->json([...$u->only('id', 'first_name', 'last_name', 'email', 'is_admin'), 'name' => $u->name]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));

        return response()->json(
            ['message' => $status === Password::RESET_LINK_SENT ? 'Reset link sent.' : 'Unable to send reset link.'],
            $status === Password::RESET_LINK_SENT ? 200 : 400
        );
    }
}
