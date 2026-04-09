@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-white">Pengaturan Profil</h2>
        <p class="text-gray-400 mt-1 text-sm">Kelola informasi akun dan ubah password Anda</p>
    </div>

    {{-- Flash Success --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-500/10 border border-green-500/30 rounded-xl px-5 py-3.5">
        <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm text-green-400 font-medium">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Info Section --}}
    <div class="glass-card rounded-xl p-6 space-y-5">
        <h3 class="text-base font-semibold text-white border-b border-gray-700/50 pb-3">Informasi Akun</h3>

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-5">
                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="w-full bg-gray-800/60 border border-gray-700/60 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/50 transition"
                        required>
                    @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                        class="w-full bg-gray-800/60 border border-gray-700/60 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/50 transition"
                        required>
                    @error('email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Role (read-only) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1.5">Role</label>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider {{ $user->isDireksi() ? 'bg-purple-500/20 text-purple-400 border border-purple-500/30' : 'bg-blue-500/20 text-blue-400 border border-blue-500/30' }}">
                            {{ $user->role }}
                        </span>
                        <span class="text-xs text-gray-500">Role tidak dapat diubah dari halaman ini</span>
                    </div>
                </div>
            </div>

            {{-- Divider --}}
            <div class="border-t border-gray-700/50 pt-5">
                <h4 class="text-sm font-semibold text-gray-300 mb-4">Ganti Password <span class="text-gray-500 font-normal">(Opsional — biarkan kosong jika tidak ingin mengganti)</span></h4>

                <div class="grid grid-cols-1 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1.5">Password Saat Ini</label>
                        <input type="password" name="current_password" autocomplete="current-password" placeholder="Masukkan password saat ini"
                            class="w-full bg-gray-800/60 border border-gray-700/60 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/50 transition">
                        @error('current_password') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1.5">Password Baru</label>
                        <input type="password" name="password" autocomplete="new-password" placeholder="Minimal 8 karakter"
                            class="w-full bg-gray-800/60 border border-gray-700/60 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/50 transition">
                        @error('password') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1.5">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password" placeholder="Ulangi password baru"
                            class="w-full bg-gray-800/60 border border-gray-700/60 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/50 transition">
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-sm font-semibold rounded-lg shadow-lg shadow-indigo-500/20 transition-all hover:-translate-y-px">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
