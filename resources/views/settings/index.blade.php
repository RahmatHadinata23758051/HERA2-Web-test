@extends('layouts.app')

@section('content')
<div class="space-y-8">

    {{-- Header --}}
    <div class="bg-white p-5 rounded-xl shadow-sm border border-surface-container-high">
        <h2 class="text-2xl font-extrabold tracking-tight text-on-surface font-headline">Pengaturan Aplikasi</h2>
        <p class="text-on-surface-variant mt-1 text-sm">Kelola identitas, logo, dan preferensi inti sistem HERA.</p>
    </div>

    {{-- Settings Form --}}
    <div class="bg-white rounded-xl border border-surface-container-high overflow-hidden shadow-sm">
        <div class="bg-surface-container-lowest border-b border-surface-container-high px-6 py-4">
            <h3 class="font-bold text-on-surface text-sm">Identitas Sistem</h3>
        </div>

        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data"
              x-data="settingsForm('{{ $settings['app_logo'] ? asset($settings['app_logo']) : '' }}')"
              class="p-6">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Left Column: Logo Upload --}}
                <div class="lg:col-span-1 flex flex-col items-center">
                    <label class="block text-sm font-bold text-on-surface mb-4 self-start">Logo Aplikasi</label>
                    
                    <div class="relative group cursor-pointer w-44 h-44 rounded-2xl border-2 border-dashed border-outline-variant hover:border-primary bg-surface-container-low flex flex-col items-center justify-center overflow-hidden transition-all duration-300"
                         @click="$refs.logoInput.click()">
                        
                        <template x-if="imageUrl">
                            <img :src="imageUrl" class="w-full h-full object-contain p-3 z-10" alt="Logo Preview">
                        </template>
                        <template x-if="!imageUrl">
                            <div class="flex flex-col items-center text-outline z-10">
                                <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-xs font-medium">Unggah Logo</span>
                            </div>
                        </template>
                        <div class="absolute inset-0 bg-primary/20 backdrop-blur-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity z-20">
                            <span class="text-primary text-sm font-bold bg-white px-3 py-1.5 rounded-lg shadow-sm">Ubah Gambar</span>
                        </div>
                    </div>

                    <input x-ref="logoInput" type="file" name="app_logo" class="hidden" accept="image/*" @change="fileChosen">
                    <p class="text-xs text-on-surface-variant mt-3 text-center">PNG, JPG, WEBP â€” maks. 2MB</p>
                    @error('app_logo')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                </div>

                {{-- Right Column: Form Inputs --}}
                <div class="lg:col-span-2 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        {{-- App Name --}}
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-1.5">Nama Aplikasi</label>
                            <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name']) }}"
                                   class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" required>
                            @error('app_name')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                        </div>

                        {{-- Version --}}
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-1.5">Versi</label>
                            <input type="text" name="app_version" value="{{ old('app_version', $settings['app_version']) }}"
                                   class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" required>
                            @error('app_version')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                        </div>

                        {{-- Institution --}}
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-1.5">Nama Instansi</label>
                            <input type="text" name="app_institution" value="{{ old('app_institution', $settings['app_institution']) }}"
                                   class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" required>
                            @error('app_institution')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                        </div>

                        {{-- Year --}}
                        <div>
                            <label class="block text-sm font-bold text-on-surface mb-1.5">Tahun Hak Cipta</label>
                            <input type="text" name="app_year" value="{{ old('app_year', $settings['app_year']) }}"
                                   class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" required>
                            @error('app_year')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- Copyright --}}
                    <div>
                        <label class="block text-sm font-bold text-on-surface mb-1.5">Pemegang Copyright</label>
                        <input type="text" name="app_copyright" value="{{ old('app_copyright', $settings['app_copyright']) }}"
                               class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition" required>
                        @error('app_copyright')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="block text-sm font-bold text-on-surface mb-1.5">Deskripsi Aplikasi</label>
                        <textarea name="app_description" rows="3"
                                  class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface text-sm focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition resize-none">{{ old('app_description', $settings['app_description']) }}</textarea>
                        @error('app_description')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                    </div>

                    {{-- Submit --}}
                    <div class="flex justify-end pt-4 border-t border-surface-container-high">
                        <button type="submit"
                                class="bg-primary hover:brightness-110 text-on-primary font-bold py-2.5 px-6 rounded-lg shadow-sm transition-all flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Threshold Card (Direksi only) --}}
    @if(auth()->user()->role === 'direksi')
    <div class="bg-white rounded-xl border border-surface-container-high shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-amber-100 rounded-xl flex-shrink-0">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-on-surface text-sm">Threshold Kadar Chromium (Cr)</h3>
                    <p class="text-xs text-on-surface-variant mt-0.5">
                        Atur batas nilai <span class="font-bold text-primary">Normal</span>,
                        <span class="font-bold text-yellow-600">Warning</span>, dan
                        <span class="font-bold text-error">Danger</span> untuk sensor Chromium.
                        Perubahan langsung mempengaruhi alert, badge status, dan notifikasi sistem.
                    </p>
                </div>
            </div>
            <a href="{{ route('settings.threshold') }}"
               class="flex-shrink-0 flex items-center gap-2 px-4 py-2.5 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-colors text-sm font-bold shadow-sm ml-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Atur Threshold
            </a>
        </div>
    </div>
    @endif


    {{-- Activity Log Shortcut (Direksi only) --}}
    @if(auth()->user()->role === 'direksi')
    <div class="bg-white rounded-xl border border-surface-container-high shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-5">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-primary/10 rounded-xl flex-shrink-0">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-on-surface text-sm">Log Aktivitas Sistem</h3>
                    <p class="text-xs text-on-surface-variant mt-0.5">
                        Lihat riwayat seluruh aktivitas pengguna â€” login, perubahan data, pengaturan, dan lainnya.
                        Tersedia filter dan pagination di halaman terpisah.
                    </p>
                </div>
            </div>
            <a href="{{ route('activity-log.index') }}"
               class="flex-shrink-0 flex items-center gap-2 px-4 py-2.5 bg-primary text-on-primary rounded-lg hover:brightness-110 transition-all text-sm font-bold shadow-sm ml-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
                Lihat Log
            </a>
        </div>
    </div>
    @endif

</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('settingsForm', (initialUrl) => ({
            imageUrl: initialUrl,
            fileChosen(event) {
                this.fileToDataUrl(event, src => this.imageUrl = src)
            },
            fileToDataUrl(event, callback) {
                if (!event.target.files.length) return
                let file = event.target.files[0], reader = new FileReader()
                reader.readAsDataURL(file)
                reader.onload = e => callback(e.target.result)
            }
        }));
    });
</script>
@endpush
