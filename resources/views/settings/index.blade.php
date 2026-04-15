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
                    <p class="text-xs text-on-surface-variant mt-3 text-center">PNG, JPG, WEBP — maks. 2MB</p>
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

    {{-- Activity Log (Direksi only) --}}
    @if(auth()->user()->role === 'direksi')
    <div>
        <div class="mb-4">
            <h3 class="text-xl font-bold tracking-tight text-on-surface font-headline">Log Aktivitas Sistem</h3>
            <p class="text-on-surface-variant text-sm mt-1">Riwayat mutakhir penggunaan dan perubahan pada aplikasi HERA.</p>
        </div>

        <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-surface-container-high">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-[10px] text-on-surface-variant uppercase tracking-widest font-black bg-surface-container-low border-b border-surface-container-high">
                        <tr>
                            <th class="px-6 py-4">Waktu</th>
                            <th class="px-6 py-4">Pengguna</th>
                            <th class="px-6 py-4">Aksi</th>
                            <th class="px-6 py-4">Rincian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-high">
                        @forelse($logs as $log)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-on-surface-variant text-xs font-mono">
                                {{ \Carbon\Carbon::parse($log->created_at)->timezone(config('app.timezone', 'Asia/Makassar'))->format('d M Y, H:i:s') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xs font-black flex-shrink-0">
                                        {{ strtoupper(substr($log->user->name ?? '?', 0, 1)) }}
                                    </div>
                                    <span class="font-bold text-on-surface text-sm">{{ $log->user->name ?? 'Sistem / Terhapus' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border
                                    @if(in_array($log->action, ['Login', 'Logout'])) bg-sky-100 text-sky-700 border-sky-200
                                    @elseif($log->action === 'Delete User') bg-error-container text-error border-error/20
                                    @elseif(in_array($log->action, ['Update User', 'Update Settings'])) bg-yellow-100 text-yellow-700 border-yellow-200
                                    @else bg-primary/10 text-primary border-primary/20 @endif">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-on-surface-variant text-sm">{{ $log->details ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <p class="text-on-surface-variant text-sm">Belum ada catatan aktivitas.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
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
