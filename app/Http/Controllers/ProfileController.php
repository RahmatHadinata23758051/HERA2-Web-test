<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'unique:users,email,' . $user->id],
            'picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'] // Maksimal 2MB
        ]);

        $user->name  = $request->name;
        $user->email = $request->email;

        // Penanganan Unggah Foto (Avatar)
        if ($request->hasFile('picture')) {
            // Hapus gambar lama bila ada
            if ($user->picture && Storage::disk('public')->exists($user->picture)) {
                Storage::disk('public')->delete($user->picture);
            }
            
            // Simpan foto di storage publik
            $path = $request->file('picture')->store('profile_pictures', 'public');
            $user->picture = $path;
        }

        // Penanganan Ganti Logika Password Sesuai HERA 1.0
        if ($request->filled('password')) {
            $request->validate([
                'current_password' => ['required', function ($attr, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail('Password saat ini tidak sesuai.');
                    }
                }],
                // Validasi HERA 1.0: Min 6 Digit, Harus ada Angka numerik, Harus ada Karakter Spesial (Simbol)
                'password' => ['required', 'confirmed', 'min:6', 'regex:/[0-9]/', 'regex:/[^a-zA-Z0-9 ]/'],
            ], [
                'password.min'   => 'Password minimal 6 karakter',
                'password.regex' => 'Password harus mengandung kombinasi minimal satu Angka dan Simbol (karakter spesial)'
            ]);
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
