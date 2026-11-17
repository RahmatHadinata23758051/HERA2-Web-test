@extends('layouts.app')

@section('content')
<div class="space-y-8">

    {{-- Header --}}
    <div class="bg-white p-5 rounded-xl shadow-sm border border-surface-container-high flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-on-surface font-headline">Manajemen Versi Aplikasi Mobile (OTA)</h2>
            <p class="text-on-surface-variant mt-1 text-sm">Kelola rilis APK Android dan pembaruan otomatis (Over-The-Air) langsung ke aplikasi klien HERA.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 bg-primary/10 text-primary border border-primary/20 rounded-full text-xs font-bold flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">android</span>
                Versi Aktif: {{ $latest ? 'v' . $latest->version . ' (Build ' . $latest->build_number . ')' : 'Belum Ada' }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <span class="material-symbols-outlined text-emerald-600">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- Left Form: Rilis Versi Baru --}}
        <div class="lg:col-span-1 bg-white rounded-xl border border-surface-container-high overflow-hidden shadow-sm h-fit">
            <div class="bg-surface-container-lowest border-b border-surface-container-high px-6 py-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-lg">publish</span>
                <h3 class="font-bold text-on-surface text-sm">Publikasikan Versi Baru</h3>
            </div>

            <form action="{{ route('settings.app-version.store') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-on-surface mb-1 uppercase tracking-wider">Nomor Versi (Version Name)</label>
                    <input type="text" name="version" value="{{ old('version', '1.1.0') }}" placeholder="Contoh: 1.1.0" required
                           class="w-full text-sm border-outline-variant rounded-lg focus:border-primary focus:ring-primary">
                    <p class="text-[11px] text-on-surface-variant mt-1">Sesuai format semantic (misal: 1.0.1, 1.1.0).</p>
                    @error('version')<span class="text-error text-xs">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-on-surface mb-1 uppercase tracking-wider">Build Number (Kode Angka)</label>
                    <input type="number" name="build_number" value="{{ old('build_number', ($latest ? $latest->build_number + 1 : 2)) }}" min="1" required
                           class="w-full text-sm border-outline-variant rounded-lg focus:border-primary focus:ring-primary">
                    <p class="text-[11px] text-on-surface-variant mt-1">Harus lebih besar dari build sebelumnya agar HP klien mendeteksi update.</p>
                    @error('build_number')<span class="text-error text-xs">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-on-surface mb-1 uppercase tracking-wider">URL Unduhan APK (GitHub Release Link)</label>
                    <input type="url" name="download_url" value="{{ old('download_url') }}" placeholder="https://github.com/.../releases/download/.../hera-app.apk" required
                           class="w-full text-sm border-outline-variant rounded-lg focus:border-primary focus:ring-primary">
                    <p class="text-[11px] text-on-surface-variant mt-1">Link download langsung APK dari GitHub Releases atau file server.</p>
                    @error('download_url')<span class="text-error text-xs">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-on-surface mb-1 uppercase tracking-wider">Perkiraan Ukuran File</label>
                    <input type="text" name="file_size" value="{{ old('file_size', '28.5 MB') }}" placeholder="Contoh: 28.5 MB"
                           class="w-full text-sm border-outline-variant rounded-lg focus:border-primary focus:ring-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-on-surface mb-1 uppercase tracking-wider">Catatan Rilis (Changelog)</label>
                    <textarea name="changelog" rows="4" placeholder="- Perbaikan satuan Cr(VI) dan Ni ke µg/L&#10;- Peningkatan performa telemetri"
                              class="w-full text-sm border-outline-variant rounded-lg focus:border-primary focus:ring-primary">{{ old('changelog') }}</textarea>
                    <p class="text-[11px] text-on-surface-variant mt-1">Poin-poin pembaruan yang akan tampil di dialog update HP klien.</p>
                </div>

                <div class="pt-2 border-t border-surface-container-high space-y-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_force_update" value="1" {{ old('is_force_update') ? 'checked' : '' }}
                               class="rounded border-outline-variant text-primary focus:ring-primary">
                        <span class="text-xs font-bold text-on-surface">Wajib Update (*Force Update*)</span>
                    </label>
                    <p class="text-[11px] text-on-surface-variant pl-6">Jika dicentang, klien tidak bisa menutup dialog update sebelum menginstal versi baru.</p>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked
                               class="rounded border-outline-variant text-primary focus:ring-primary">
                        <span class="text-xs font-bold text-on-surface">Aktifkan Versi Ini Sekarang</span>
                    </label>
                </div>

                <button type="submit" class="w-full mt-4 py-2.5 px-4 bg-primary text-white font-bold rounded-lg text-sm shadow hover:bg-primary/90 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">cloud_upload</span>
                    Publikasikan Pembaruan
                </button>
            </form>
        </div>

        {{-- Right Table: Riwayat Versi Rilis --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-surface-container-high overflow-hidden shadow-sm">
            <div class="bg-surface-container-lowest border-b border-surface-container-high px-6 py-4 flex items-center justify-between">
                <h3 class="font-bold text-on-surface text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-base">history</span>
                    Riwayat Rilis Aplikasi Mobile
                </h3>
                <span class="text-xs text-on-surface-variant">{{ $versions->count() }} Versi Tercatat</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-surface-container-low text-on-surface-variant font-label text-xs uppercase tracking-wider border-b border-surface-container-high">
                        <tr>
                            <th class="px-6 py-3">Versi</th>
                            <th class="px-6 py-3">Build</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Tipe</th>
                            <th class="px-6 py-3">Tanggal</th>
                            <th class="px-6 py-3 text-center">Aksi / Link</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-high">
                        @forelse($versions as $v)
                            <tr class="hover:bg-surface-container-lowest transition-colors">
                                <td class="px-6 py-4 font-bold text-on-surface">
                                    v{{ $v->version }}
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-on-surface-variant">
                                    #{{ $v->build_number }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($v->is_active)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">Aktif</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-600">Arsip</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($v->is_force_update)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-red-100 text-red-800">Wajib (Force)</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800">Opsional</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-xs text-on-surface-variant">
                                    {{ $v->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ $v->download_url }}" target="_blank" class="inline-flex items-center gap-1 text-primary hover:text-primary/80 font-bold text-xs">
                                        <span class="material-symbols-outlined text-sm">download</span> Unduh APK
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-on-surface-variant text-sm">
                                    Belum ada data versi yang dipublikasikan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
