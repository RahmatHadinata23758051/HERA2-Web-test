@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.users.index') }}" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800/50 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-white">Tambah Pengguna</h2>
            <p class="text-gray-400 mt-0.5 text-sm">Buat akun baru untuk anggota tim</p>
        </div>
    </div>

    <div class="glass-card rounded-xl p-6 space-y-5">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 gap-5">
                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama lengkap pengguna"
                           class="w-full bg-gray-800/60 border {{ $errors->has('name') ? 'border-red-500/60' : 'border-gray-700/60' }} text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/50 transition" required>
                    @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1.5">Email <span class="text-red-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="email@instansi.ac.id"
                           class="w-full bg-gray-800/60 border {{ $errors->has('email') ? 'border-red-500/60' : 'border-gray-700/60' }} text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/50 transition" required>
                    @error('email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Role --}}
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1.5">Role <span class="text-red-400">*</span></label>
                    <select name="role"
                            class="w-full bg-gray-800/60 border {{ $errors->has('role') ? 'border-red-500/60' : 'border-gray-700/60' }} text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/50 transition" required>
                        <option value="" class="bg-gray-900">-- Pilih Role --</option>
                        <option value="petugas" class="bg-gray-900" {{ old('role') === 'petugas' ? 'selected' : '' }}>Petugas (Operator)</option>
                        <option value="direksi" class="bg-gray-900" {{ old('role') === 'direksi' ? 'selected' : '' }}>Direksi (Administrator)</option>
                    </select>
                    <p class="mt-1.5 text-xs text-gray-500">Petugas hanya dapat melihat dashboard, monitoring, dan laporan. Direksi memiliki akses penuh.</p>
                    @error('role') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1.5">Password <span class="text-red-400">*</span></label>
                    <input type="password" name="password" autocomplete="new-password" placeholder="Minimal 8 karakter"
                           class="w-full bg-gray-800/60 border {{ $errors->has('password') ? 'border-red-500/60' : 'border-gray-700/60' }} text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/50 transition" required>
                    @error('password') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1.5">Konfirmasi Password <span class="text-red-400">*</span></label>
                    <input type="password" name="password_confirmation" autocomplete="new-password" placeholder="Ulangi password"
                           class="w-full bg-gray-800/60 border border-gray-700/60 text-white rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500/50 transition" required>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-700/50">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 text-sm font-medium text-gray-400 hover:text-white bg-gray-800/50 hover:bg-gray-700/50 rounded-lg transition-colors">Batal</a>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-sm font-semibold rounded-lg shadow-lg shadow-indigo-500/20 transition-all hover:-translate-y-px">
                    Buat Akun
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
