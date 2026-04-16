<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MobileAuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/mobile/login",
     *     summary="Login mobile",
     *     description="Autentikasi pengguna mobile dan mengembalikan Sanctum Bearer Token yang berlaku 30 hari.",
     *     tags={"Auth Mobile"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="petugas@hera.ac.id"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="device_name", type="string", example="Samsung Galaxy A54", description="Nama perangkat (opsional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login berhasil."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="1|abc123..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="string", example="30 days"),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Budi Santoso"),
     *                     @OA\Property(property="email", type="string", example="petugas@hera.ac.id"),
     *                     @OA\Property(property="role", type="string", example="petugas")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Email atau password salah")
     * )
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
     * @OA\Post(
     *     path="/api/mobile/register",
     *     summary="Register akun baru",
     *     description="Membuat akun pengguna baru dengan role 'petugas' dan langsung mengembalikan Sanctum Bearer Token sehingga pengguna tidak perlu login ulang setelah mendaftar.",
     *     tags={"Auth Mobile"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="Andi Prasetyo"),
     *             @OA\Property(property="email", type="string", format="email", example="andi@hera.ac.id"),
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="Minimal 6 karakter"),
     *             @OA\Property(property="device_name", type="string", example="Xiaomi Redmi Note 12", description="Nama perangkat (opsional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Registrasi berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Registrasi berhasil. Selamat datang di HERA!"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="2|xyz789..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="string", example="30 days"),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=5),
     *                     @OA\Property(property="name", type="string", example="Andi Prasetyo"),
     *                     @OA\Property(property="email", type="string", example="andi@hera.ac.id"),
     *                     @OA\Property(property="role", type="string", example="petugas")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validasi gagal (email sudah terdaftar, password kurang dari 6 karakter, dsb.)"
     *     )
     * )
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
            'role'     => 'petugas',
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
     * @OA\Post(
     *     path="/api/mobile/logout",
     *     summary="Logout mobile",
     *     description="Mencabut (revoke) token Sanctum yang sedang aktif. Token tidak dapat digunakan lagi setelah ini.",
     *     tags={"Auth Mobile"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logout berhasil. Token telah dicabut.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Token tidak valid atau sudah kedaluwarsa")
     * )
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
     * @OA\Get(
     *     path="/api/mobile/profile",
     *     summary="Profil pengguna",
     *     description="Mengembalikan data profil pengguna yang sedang login berdasarkan Bearer Token.",
     *     tags={"Auth Mobile"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Data profil berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OK"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Budi Santoso"),
     *                 @OA\Property(property="email", type="string", example="petugas@hera.ac.id"),
     *                 @OA\Property(property="role", type="string", example="petugas", description="Nilai: petugas atau direksi")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Token tidak valid")
     * )
     */
    /**
     * @OA\Get(
     *     path="/api/mobile/profile",
     *     summary="Profil pengguna",
     *     description="Mengembalikan data profil pengguna yang sedang login berdasarkan Bearer Token.",
     *     tags={"Auth Mobile"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Data profil berhasil diambil")
     * )
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

    /**
     * PUT /api/mobile/me — Update nama & email
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name'  => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Profil berhasil diperbarui.',
            'data'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ], 200);
    }

    /**
     * PUT /api/mobile/password — Ganti password
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'old_password'              => 'required|string',
            'new_password'              => 'required|string|min:6|confirmed',
            'new_password_confirmation' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Password lama tidak sesuai.',
                'errors'  => ['old_password' => ['Password lama yang Anda masukkan salah.']],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        // Cabut semua token lama kecuali token aktif saat ini
        $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Kata sandi berhasil diubah.',
            'data'    => [
                'force_logout' => false,
            ],
        ], 200);
    }
}
