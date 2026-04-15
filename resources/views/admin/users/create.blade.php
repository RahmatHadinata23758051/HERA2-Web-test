@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="bg-white p-5 rounded-xl shadow-sm border border-surface-container-high flex items-center gap-4">
        <a href="{{ route('admin.users.index') }}"
           class="p-2 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-lg transition-colors flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-on-surface font-headline">Tambah Pengguna</h2>
            <p class="text-on-surface-variant mt-0.5 text-sm">Buat akun baru untuk anggota tim HERA</p>
        </div>
    </div>

    {{-- Error alert --}}
    @if($errors->any())
    <div class="p-4 bg-error-container border border-error/30 rounded-xl flex items-start gap-3">
        <svg class="w-5 h-5 text-error flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <ul class="list-disc list-inside text-sm text-error space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Form Card --}}
    <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-surface-container-high">
        <div class="bg-surface-container-lowest border-b border-surface-container-high px-6 py-4">
            <h3 class="font-bold text-on-surface text-sm">Informasi Akun</h3>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="p-6 space-y-5">
            @csrf

            {{-- Name --}}
            <div>
                <label class="block text-sm font-bold text-on-surface mb-1.5">
                    Nama Lengkap <span class="text-error">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama lengkap pengguna"
                       class="w-full bg-surface-container-low border {{ $errors->has('name') ? 'border-error ring-1 ring-error/30' : 'border-surface-container-high' }} text-on-surface rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition" required>
                @error('name')
                    <p class="mt-1.5 text-xs text-error flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm font-bold text-on-surface mb-1.5">
                    Email <span class="text-error">*</span>
                </label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="email@instansi.ac.id"
                       class="w-full bg-surface-container-low border {{ $errors->has('email') ? 'border-error ring-1 ring-error/30' : 'border-surface-container-high' }} text-on-surface rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition" required>
                @error('email')
                    <p class="mt-1.5 text-xs text-error flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Role --}}
            <div>
                <label class="block text-sm font-bold text-on-surface mb-1.5">
                    Role <span class="text-error">*</span>
                </label>
                <select name="role"
                        class="w-full bg-surface-container-low border {{ $errors->has('role') ? 'border-error' : 'border-surface-container-high' }} text-on-surface rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="petugas" {{ old('role') === 'petugas' ? 'selected' : '' }}>Petugas (Operator)</option>
                    <option value="direksi" {{ old('role') === 'direksi' ? 'selected' : '' }}>Direksi (Administrator)</option>
                </select>
                <p class="mt-1.5 text-xs text-on-surface-variant">Petugas: akses baca semua modul. Direksi: akses penuh termasuk manajemen user.</p>
                @error('role')
                    <p class="mt-1 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            <hr class="border-surface-container-high">

            {{-- Password --}}
            <div>
                <label class="block text-sm font-bold text-on-surface mb-1.5">
                    Password <span class="text-error">*</span>
                </label>
                <input type="password" name="password" autocomplete="new-password" placeholder="Minimal 8 karakter"
                       class="w-full bg-surface-container-low border {{ $errors->has('password') ? 'border-error ring-1 ring-error/30' : 'border-surface-container-high' }} text-on-surface rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition" required>
                @error('password')
                    <p class="mt-1.5 text-xs text-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label class="block text-sm font-bold text-on-surface mb-1.5">
                    Konfirmasi Password <span class="text-error">*</span>
                </label>
                <input type="password" name="password_confirmation" autocomplete="new-password" placeholder="Ulangi password"
                       class="w-full bg-surface-container-low border border-surface-container-high text-on-surface rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition" required>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-container-high">
                <a href="{{ route('admin.users.index') }}"
                   class="px-5 py-2.5 text-sm font-medium text-on-surface-variant hover:text-on-surface bg-surface-container-low hover:bg-surface-container rounded-lg transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 bg-primary text-on-primary text-sm font-bold rounded-lg shadow-sm hover:brightness-110 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    Buat Akun
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
