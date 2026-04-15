@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-white">Pengaturan Profil</h2>
        <p class="text-gray-400 mt-1 text-sm">Harap isi semua form input dengan benar lalu klik tombol simpan.</p>
    </div>

    {{-- Flash Success --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-5 py-3.5">
        <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm text-emerald-400 font-medium">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Info Section --}}
    <div class="glass-card rounded-xl p-6 space-y-5 shadow-2xl border border-gray-800/60">
        <h3 class="text-base font-semibold text-white border-b border-gray-700/50 pb-3">Informasi Akun</h3>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Avatar Preview & Upload --}}
            <div class="flex flex-col items-center justify-center py-2">
                <div class="relative w-28 h-28 rounded-full overflow-hidden border-4 border-gray-700/50 shadow-xl mb-3 bg-gray-900 group">
                    @if($user->picture)
                        <img id="profile-picture-preview" src="{{ asset('storage/' . $user->picture) }}" alt="Profile Picture" class="w-full h-full object-cover transition duration-300 group-hover:scale-110">
                    @else
                        <img id="profile-picture-preview" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%239ca3af'%3E%3Cpath d='M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z'/%3E%3C/svg%3E" alt="Placeholder" class="w-full h-full object-cover p-4 opacity-50">
                    @endif
                    <!-- Hover overlay for edit cues -->
                    <label for="profile-picture-input" class="absolute inset-0 bg-black/50 hidden group-hover:flex items-center justify-center cursor-pointer transition">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </label>
                </div>
                <div class="text-center">
                    <label for="profile-picture-input" class="cursor-pointer text-sm font-medium text-emerald-400 hover:text-emerald-300 transition px-4 py-1.5 bg-emerald-500/10 hover:bg-emerald-500/20 rounded-lg border border-emerald-500/20 inline-block">
                        Unggah Foto
                    </label>
                    <input type="file" name="picture" id="profile-picture-input" class="hidden" accept="image/*" onchange="previewProfilePicture()">
                    @error('picture') <p class="mt-2 text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5">
                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="w-full bg-gray-900/50 border border-gray-700/60 text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition placeholder-gray-600"
                        placeholder="Masukan Nama" required>
                    @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                        class="w-full bg-gray-900/50 border border-gray-700/60 text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition placeholder-gray-600"
                        placeholder="Masukan Email" required>
                    @error('email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Divider --}}
            <div class="border-t border-gray-700/50 pt-6 mt-4">
                <h4 class="text-sm font-semibold text-gray-300 mb-4">Ganti Password <span class="text-gray-500 font-normal">(* Opsional)</span></h4>

                <div class="grid grid-cols-1 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1.5">Password Saat Ini</label>
                        <input type="password" name="current_password" autocomplete="current-password" placeholder="Masukkan password saat ini (jika ingin mengubah)"
                            class="w-full bg-gray-900/50 border border-gray-700/60 text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition placeholder-gray-600">
                        @error('current_password') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1.5">Password Baru</label>
                        <input type="password" name="password" autocomplete="new-password" placeholder="Masukan Password Baru"
                            class="w-full bg-gray-900/50 border border-gray-700/60 text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition placeholder-gray-600">
                        
                        <div class="password-reqs text-xs text-gray-500 mt-2 space-y-1 pl-1">
                            <div id="req-length">&check; Password minimal 6 digit</div>
                            <div id="req-number">&check; Password wajib mengandung Angka numeric</div>
                            <div id="req-symbol">&check; Password wajib mengandung huruf spesial</div>
                        </div>
                        @error('password') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1.5">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password" placeholder="Ulangi Password Baru"
                            class="w-full bg-gray-900/50 border border-gray-700/60 text-white rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 transition placeholder-gray-600">
                    </div>
                </div>
            </div>

            <hr class="border-gray-700/50 mb-2">

            <div class="flex flex-col-reverse md:flex-row justify-between items-center gap-4 pt-1">
                <div class="text-xs text-blue-400/80 bg-blue-900/10 px-4 py-2.5 rounded-lg border border-blue-900/30 w-full md:w-auto">
                    <p>* Isi semua input dengan benar</p>
                    <p>* Password Optional (Isi jika ingin diubah)</p>
                </div>
                
                <button type="submit" id="btn-submit"
                    class="w-full md:w-auto px-8 py-3 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-lg shadow-lg shadow-emerald-500/20 transition-all hover:-translate-y-px disabled:opacity-50 disabled:cursor-not-allowed">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Live Image Preview (Adapted from HERA 1.0)
    function previewProfilePicture() {
        const input = document.getElementById('profile-picture-input');
        const preview = document.getElementById('profile-picture-preview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function (e) {
                preview.src = e.target.result;
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    // Live Password Validation & Disabled Submit Button (Adapted from HERA 1.0)
    window.onload = function () {
        let elemBtn = document.getElementById('btn-submit');
        let passwordInput = document.querySelector('input[name="password"]');

        if(passwordInput) {
            passwordInput.addEventListener('keyup', function () {
                let value = this.value;
                let elems = [
                    document.getElementById('req-length'),
                    document.getElementById('req-number'),
                    document.getElementById('req-symbol')
                ];
                
                let validation = [
                    (value.length >= 6),
                    /[0-9]/.test(value),
                    /[^a-zA-Z0-9 ]/.test(value)
                ];

                for (let i = 0; i < 3; i++) {
                    if (validation[i]) {
                        elems[i].classList.remove('text-gray-500');
                        elems[i].classList.add('text-emerald-400', 'font-bold');
                    } else {
                        elems[i].classList.remove('text-emerald-400', 'font-bold');
                        elems[i].classList.add('text-gray-500');
                    }
                }

                if (value.length > 0) {
                    if (validation.every(v => v)) {
                        elemBtn.removeAttribute('disabled');
                    } else {
                        elemBtn.setAttribute('disabled', true);
                    }
                } else {
                    elemBtn.removeAttribute('disabled');
                    
                    // Reset styling if empty
                    for (let i = 0; i < 3; i++) {
                        elems[i].classList.remove('text-emerald-400', 'font-bold');
                        elems[i].classList.add('text-gray-500');
                    }
                }
            });
        }
    }
</script>
@endpush
@endsection
