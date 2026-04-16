<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MobileAuthController extends Controller
{
    /**
     * POST /api/mobile/login
     * Returns a Sanctum token for use in subsequent API calls.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Email atau password tidak valid.',
                'data'    => null,
            ], 401);
        }

        $deviceName = $request->device_name ?? 'Mobile App';

        // Revoke old tokens from this device name to prevent accumulation
        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName, ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Login berhasil.',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'expires_in' => '30 days',
                'user'       => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
                ],
            ],
        ], 200);
    }

    /**
     * POST /api/mobile/register
     * Creates a new user account and returns a Sanctum token directly
     * so the user is logged in immediately after registration.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'petugas', // default role for self-registered users
        ]);

        $deviceName = $request->device_name ?? 'Mobile App';
        $token = $user->createToken($deviceName, ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Registrasi berhasil. Selamat datang di HERA!',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'expires_in' => '30 days',
                'user'       => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
                ],
            ],
        ], 201);
    }

    /**
     * POST /api/mobile/logout
     * Revokes the current token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Logout berhasil. Token telah dicabut.',
            'data'    => null,
        ], 200);
    }

    /**
     * GET /api/mobile/profile
     * Returns the authenticated user's profile.
     */
    public function profile(Request $request)
    {
        return response()->json([
            'status'  => true,
            'message' => 'OK',
            'data'    => [
                'id'    => $request->user()->id,
                'name'  => $request->user()->name,
                'email' => $request->user()->email,
                'role'  => $request->user()->role,
            ],
        ], 200);
    }
}
