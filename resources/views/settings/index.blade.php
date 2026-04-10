@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h2 class="text-3xl font-bold tracking-tight text-white mb-2">Pengaturan Aplikasi</h2>
        <p class="text-gray-400">Kelola identitas, logo, dan preferensi inti sistem HERA.</p>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="glass-card rounded-2xl p-8 border border-gray-800/50">
        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" 
              x-data="settingsForm('{{ $settings['app_logo'] ? asset($settings['app_logo']) : '' }}')">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                
                {{-- Left Column: Logo Upload --}}
                <div class="lg:col-span-1 flex flex-col items-center">
                    <label class="block text-sm font-medium text-gray-300 mb-4 self-start">Logo Aplikasi</label>
                    
                    <div class="relative group cursor-pointer w-48 h-48 rounded-full border-2 border-dashed border-gray-600 hover:border-indigo-500 bg-gray-800/50 flex flex-col items-center justify-center overflow-hidden transition-all duration-300"
                         @click="$refs.logoInput.click()">
                        
                        {{-- Preview Image --}}
                        <template x-if="imageUrl">
                            <img :src="imageUrl" class="w-full h-full object-contain p-4 z-10 bg-white/5" alt="Logo Preview">
                        </template>

                        {{-- Fallback Icon --}}
                        <template x-if="!imageUrl">
                            <div class="flex flex-col items-center text-gray-500 z-10">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-xs font-medium">Unggah Logo</span>
                            </div>
                        </template>

                        {{-- Hover Overlay --}}
                        <div class="absolute inset-0 bg-black/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity z-20">
                            <span class="text-white text-sm font-medium">Ubah Gambar</span>
                        </div>
                    </div>
                    
                    <input x-ref="logoInput" type="file" name="app_logo" class="hidden" accept="image/*" @change="fileChosen">
                    <p class="text-xs text-gray-500 mt-4 text-center">Format yang didukung: PNG, JPG, WEBP. Maks 2MB.</p>
                    @error('app_logo') <span class="text-red-400 text-sm mt-2">{{ $message }}</span> @enderror
                </div>

                {{-- Right Column: Form Inputs --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Nama Aplikasi</label>
                            <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name']) }}" 
                                   class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" required>
                            @error('app_name') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Versi</label>
                            <input type="text" name="app_version" value="{{ old('app_version', $settings['app_version']) }}" 
                                   class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" required>
                            @error('app_version') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Nama Instansi</label>
                            <input type="text" name="app_institution" value="{{ old('app_institution', $settings['app_institution']) }}" 
                                   class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" required>
                            @error('app_institution') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Tahun Hak Cipta</label>
                            <input type="text" name="app_year" value="{{ old('app_year', $settings['app_year']) }}" 
                                   class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" required>
                            @error('app_year') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Pemegang Copyright</label>
                        <input type="text" name="app_copyright" value="{{ old('app_copyright', $settings['app_copyright']) }}" 
                               class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" required>
                        @error('app_copyright') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Deskripsi Aplikasi</label>
                        <textarea name="app_description" rows="3" 
                                  class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">{{ old('app_description', $settings['app_description']) }}</textarea>
                        @error('app_description') <span class="text-red-400 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-800">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-6 rounded-lg shadow-lg shadow-indigo-900/20 transition-all flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

    {{-- Activity Logs Module (Audit Trail) --}}
    @if(auth()->user()->role === 'direksi')
    <div class="mt-8">
        <div>
            <h3 class="text-2xl font-bold tracking-tight text-white mb-2">Log Aktivitas Sistem</h3>
            <p class="text-gray-400 mb-6">Riwayat mutakhir penggunaan dan perubahan pada aplikasi HERA.</p>
        </div>
        
        <div class="glass-card rounded-2xl border border-gray-800/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-300">
                    <thead class="bg-gray-800/50 text-xs uppercase text-gray-400 border-b border-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-4">Waktu</th>
                            <th scope="col" class="px-6 py-4">Pengguna</th>
                            <th scope="col" class="px-6 py-4">Aksi</th>
                            <th scope="col" class="px-6 py-4">Rincian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        @forelse($logs as $log)
                        <tr class="hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-400">
                                {{ \Carbon\Carbon::parse($log->created_at)->timezone(config('app.timezone', 'Asia/Makassar'))->format('d M Y, H:i:s') }}
                            </td>
                            <td class="px-6 py-4 font-medium text-white flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-xs font-bold">
                                    {{ strtoupper(substr($log->user->name ?? '?', 0, 1)) }}
                                </div>
                                {{ $log->user->name ?? 'Sistem / Terhapus' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium border
                                    @if($log->action == 'Login' || $log->action == 'Logout') bg-blue-500/10 text-blue-400 border-blue-500/20
                                    @elseif($log->action == 'Delete User') bg-red-500/10 text-red-400 border-red-500/20
                                    @elseif($log->action == 'Update User' || $log->action == 'Update Settings') bg-amber-500/10 text-amber-400 border-amber-500/20
                                    @else bg-emerald-500/10 text-emerald-400 border-emerald-500/20 @endif">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-400">{{ $log->details ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                Belum ada catatan aktivitas.
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
                let file = event.target.files[0],
                    reader = new FileReader()
                reader.readAsDataURL(file)
                reader.onload = e => callback(e.target.result)
            }
        }));
    });
</script>
@endpush
