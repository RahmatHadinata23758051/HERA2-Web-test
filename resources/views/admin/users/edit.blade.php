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
            <h2 class="text-2xl font-extrabold tracking-tight text-on-surface font-headline">Edit Pengguna</h2>
            <p class="text-on-surface-variant mt-0.5 text-sm">
                Memperbarui data akun: <span class="text-primary font-bold">{{ $user->name }}</span>
            </p>
        </div>
    </div>

    {{-- Current User Info Card --}}
    <div class="bg-white rounded-xl p-4 border border-surface-container-high shadow-sm flex items-center gap-4">
        @if($user->picture)
            <img src="{{ asset('storage/' . $user->picture) }}" alt="Avatar"
                 class="w-12 h-12 rounded-full object-cover ring-2 ring-surface-container-high flex-shrink-0">
        @else
            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background={{ $user->isDireksi() ? '006948' : '3e6753' }}&color=fff&rounded=true&size=48"
                 alt="Avatar" class="w-12 h-12 rounded-full flex-shrink-0">
        @endif
        <div>
            <p class="font-bold text-on-surface">{{ $user->name }}</p>
            <p class="text-xs text-on-surface-variant font-mono">{{ $user->email }}</p>
        </div>
        <span class="ml-auto px-3 py-1 rounded-lg text-xs font-black uppercase tracking-wider
            {{ $user->isDireksi()
                ? 'bg-primary/10 text-primary border border-primary/20'
                : 'bg-secondary-container text-secondary border border-secondary/20' }}">
            {{ $user->role }}
        </span>
    </div>

    {{-- Error Alert --}}
    @if($errors->any())
    <div class="p-4 bg-error-container border border-error/30 rounded-xl flex items-start gap-3">
        <svg class="w-5 h-5 text-error flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <ul class="list-disc list-inside text-sm text-error space-y-0.5">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- Form Card --}}
    <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-surface-container-high">
        <div class="bg-surface-container-lowest border-b border-surface-container-high px-6 py-4">
            <h3 class="font-bold text-on-surface text-sm">Ubah Informasi Akun</h3>
        </div>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="p-6 space-y-5">
            @csrf @method('PUT')

            {{-- Name --}}
            <div>
                <label class="block text-sm font-bold text-on-surface mb-1.5">Nama Lengkap <span class="text-error">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                       class="w-full bg-surface-container-low border {{ $errors->has('name') ? 'border-error ring-1 ring-error/30' : 'border-surface-container-high' }} text-on-surface rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition" required>
                @error('name')<p class="mt-1.5 text-xs text-error">{{ $message }}</p>@enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm font-bold text-on-surface mb-1.5">Email <span class="text-error">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="w-full bg-surface-container-low border {{ $errors->has('email') ? 'border-error ring-1 ring-error/30' : 'border-surface-container-high' }} text-on-surface rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition" required>
                @error('email')<p class="mt-1.5 text-xs text-error">{{ $message }}</p>@enderror
            </div>

            {{-- Role --}}
            <div>
                <label class="block text-sm font-bold text-on-surface mb-1.5">Role <span class="text-error">*</span></label>
                <select name="role"
                        class="w-full bg-surface-container-low border {{ $errors->has('role') ? 'border-error' : 'border-surface-container-high' }} text-on-surface rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition" required>
                    <option value="petugas" {{ old('role', $user->role) === 'petugas' ? 'selected' : '' }}>Petugas (Operator)</option>
                    <option value="direksi" {{ old('role', $user->role) === 'direksi' ? 'selected' : '' }}>Direksi (Administrator)</option>
                </select>
                @error('role')<p class="mt-1 text-xs text-error">{{ $message }}</p>@enderror
            </div>

            {{-- Password Section --}}
            <div class="border-t border-surface-container-high pt-5">
                <h4 class="text-sm font-bold text-on-surface mb-0.5">Ganti Password</h4>
                <p class="text-xs text-on-surface-variant mb-4">Biarkan kosong jika tidak ingin mengubah password pengguna ini.</p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Password Baru</label>
                        <input type="password" name="password" autocomplete="new-password" placeholder="Minimal 8 karakter"
                               class="w-full bg-surface-container-low border {{ $errors->has('password') ? 'border-error ring-1 ring-error/30' : 'border-surface-container-high' }} text-on-surface rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition">
                        @error('password')<p class="mt-1.5 text-xs text-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password" placeholder="Ulangi password baru"
                               class="w-full bg-surface-container-low border border-surface-container-high text-on-surface rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition">
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-surface-container-high">
                <a href="{{ route('admin.users.index') }}"
                   class="px-5 py-2.5 text-sm font-medium text-on-surface-variant hover:text-on-surface bg-surface-container-low hover:bg-surface-container rounded-lg transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 bg-primary text-on-primary text-sm font-bold rounded-lg shadow-sm hover:brightness-110 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
