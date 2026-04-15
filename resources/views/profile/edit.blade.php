@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="bg-white p-5 rounded-xl shadow-sm border border-surface-container-high">
        <h2 class="text-2xl font-extrabold tracking-tight text-on-surface font-headline">Pengaturan Profil</h2>
        <p class="text-on-surface-variant mt-1 text-sm">Harap isi semua form input dengan benar lalu klik tombol simpan.</p>
    </div>

    {{-- Flash Success --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-primary/5 border border-primary/20 rounded-xl px-5 py-3.5" x-data x-init="setTimeout(() => $el.remove(), 5000)">
        <svg class="w-5 h-5 text-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm text-primary font-bold">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Form Card --}}
    <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-surface-container-high">
        <div class="bg-surface-container-lowest border-b border-surface-container-high px-6 py-4">
            <h3 class="font-bold text-on-surface text-sm">Informasi Akun</h3>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf @method('PUT')

            {{-- Avatar Upload --}}
            <div class="flex flex-col items-center justify-center py-2">
                {{-- Avatar Circle --}}
                <div class="relative w-28 h-28 rounded-full overflow-hidden border-4 border-surface-container-high shadow-md mb-4 bg-surface-container group">
                    @if($user->picture)
                        <img id="profile-picture-preview"
                             src="{{ asset('storage/' . $user->picture) }}"
                             alt="Profile Picture"
                             class="w-full h-full object-cover transition duration-300 group-hover:scale-110">
                    @else
                        <img id="profile-picture-preview"
                             src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=006948&color=fff&size=112"
                             alt="Placeholder"
                             class="w-full h-full object-cover transition duration-300 group-hover:scale-110">
                    @endif
                    {{-- Hover overlay --}}
                    <label for="profile-picture-input"
                           class="absolute inset-0 bg-primary/40 hidden group-hover:flex items-center justify-center cursor-pointer transition">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </label>
                </div>

                <label for="profile-picture-input"
                       class="cursor-pointer text-sm font-bold text-primary hover:text-primary/80 transition px-4 py-1.5 bg-primary/10 hover:bg-primary/20 rounded-lg border border-primary/20 inline-block">
                    Unggah Foto
                </label>
                <input type="file" name="picture" id="profile-picture-input" class="hidden" accept="image/*" onchange="previewProfilePicture()">
                <p class="text-xs text-on-surface-variant mt-2">PNG, JPG, WEBP – maks. 2MB</p>
                @error('picture')<p class="mt-1.5 text-xs text-error font-medium">{{ $message }}</p>@enderror
            </div>

            {{-- Name & Email --}}
            <div class="grid grid-cols-1 gap-5">
                <div>
                    <label class="block text-sm font-bold text-on-surface mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="w-full bg-surface-container-low border {{ $errors->has('name') ? 'border-error ring-1 ring-error/30' : 'border-surface-container-high' }} text-on-surface rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition"
                           placeholder="Masukan Nama" required>
                    @error('name')<p class="mt-1.5 text-xs text-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-bold text-on-surface mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="w-full bg-surface-container-low border {{ $errors->has('email') ? 'border-error ring-1 ring-error/30' : 'border-surface-container-high' }} text-on-surface rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition"
                           placeholder="Masukan Email" required>
                    @error('email')<p class="mt-1.5 text-xs text-error">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Password Section --}}
            <div class="border-t border-surface-container-high pt-6">
                <h4 class="text-sm font-bold text-on-surface mb-1">
                    Ganti Password <span class="text-on-surface-variant font-normal text-xs">(Opsional)</span>
                </h4>
                <p class="text-xs text-on-surface-variant mb-4">Biarkan kosong jika tidak ingin mengubah password.</p>

                <div class="grid grid-cols-1 gap-5">
                    {{-- Current Password --}}
                    <div>
                        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Password Saat Ini</label>
                        <input type="password" name="current_password" autocomplete="current-password"
                               placeholder="Masukkan password saat ini"
                               class="w-full bg-surface-container-low border {{ $errors->has('current_password') ? 'border-error ring-1 ring-error/30' : 'border-surface-container-high' }} text-on-surface rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition">
                        @error('current_password')<p class="mt-1.5 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- New Password --}}
                    <div>
                        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Password Baru</label>
                        <input type="password" name="password" autocomplete="new-password"
                               id="new-password-input"
                               placeholder="Masukan Password Baru"
                               class="w-full bg-surface-container-low border {{ $errors->has('password') ? 'border-error ring-1 ring-error/30' : 'border-surface-container-high' }} text-on-surface rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition">

                        {{-- Password Requirements --}}
                        <div class="mt-2.5 space-y-1.5 pl-1">
                            <div id="req-length"  class="flex items-center gap-1.5 text-xs text-on-surface-variant transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Password minimal 6 digit
                            </div>
                            <div id="req-number"  class="flex items-center gap-1.5 text-xs text-on-surface-variant transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Mengandung angka numerik
                            </div>
                            <div id="req-symbol"  class="flex items-center gap-1.5 text-xs text-on-surface-variant transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Mengandung karakter spesial
                            </div>
                        </div>
                        @error('password')<p class="mt-1.5 text-xs text-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password"
                               placeholder="Ulangi Password Baru"
                               class="w-full bg-surface-container-low border border-surface-container-high text-on-surface rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition">
                    </div>
                </div>
            </div>

            <hr class="border-surface-container-high">

            {{-- Footer Actions --}}
            <div class="flex flex-col-reverse md:flex-row justify-between items-center gap-4">
                <div class="text-xs text-on-surface-variant bg-surface-container-low px-4 py-2.5 rounded-lg border border-surface-container-high w-full md:w-auto space-y-0.5">
                    <p>* Isi semua input dengan benar</p>
                    <p>* Password opsional — isi hanya jika ingin diubah</p>
                </div>
                <button type="submit" id="btn-submit"
                        class="w-full md:w-auto px-8 py-3 bg-primary hover:brightness-110 text-on-primary text-sm font-bold rounded-lg shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Live Image Preview (Adapted from HERA 1.0)
    function previewProfilePicture() {
        const input   = document.getElementById('profile-picture-input');
        const preview = document.getElementById('profile-picture-preview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => { preview.src = e.target.result; };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Live Password Validation & Disabled Submit Button (Adapted from HERA 1.0)
    window.onload = function () {
        const elemBtn       = document.getElementById('btn-submit');
        const passwordInput = document.getElementById('new-password-input');

        if (!passwordInput) return;

        passwordInput.addEventListener('keyup', function () {
            const value      = this.value;
            const elems      = ['req-length', 'req-number', 'req-symbol'].map(id => document.getElementById(id));
            const validations = [
                value.length >= 6,
                /[0-9]/.test(value),
                /[^a-zA-Z0-9 ]/.test(value)
            ];

            validations.forEach((valid, i) => {
                if (valid) {
                    elems[i].classList.remove('text-on-surface-variant');
                    elems[i].classList.add('text-primary', 'font-bold');
                } else {
                    elems[i].classList.remove('text-primary', 'font-bold');
                    elems[i].classList.add('text-on-surface-variant');
                }
            });

            if (value.length > 0) {
                if (validations.every(v => v)) {
                    elemBtn.removeAttribute('disabled');
                } else {
                    elemBtn.setAttribute('disabled', true);
                }
            } else {
                elemBtn.removeAttribute('disabled');
                elems.forEach(el => {
                    el.classList.remove('text-primary', 'font-bold');
                    el.classList.add('text-on-surface-variant');
                });
            }
        });
    };
</script>
@endpush
