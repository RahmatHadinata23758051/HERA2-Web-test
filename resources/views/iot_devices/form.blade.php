@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="bg-white p-5 rounded-xl shadow-sm border border-surface-container-high flex items-center gap-3">
        <a href="{{ route('iot-devices.index') }}"
           class="p-2 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-lg transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-on-surface font-headline">{{ $device->exists ? 'Edit Perangkat' : 'Tambah Perangkat Baru' }}</h2>
            <p class="text-on-surface-variant text-sm mt-0.5">Lengkapi data hardware dan titik lokasi deployment.</p>
        </div>
    </div>

    {{-- Error Alert --}}
    @if($errors->any())
    <div class="p-4 bg-error-container border border-error/30 rounded-xl flex items-start gap-3">
        <svg class="w-5 h-5 text-error flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <ul class="text-sm text-error space-y-0.5 list-disc list-inside">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form action="{{ $device->exists ? route('iot-devices.update', $device) : route('iot-devices.store') }}" method="POST" class="space-y-6">
        @csrf
        @if($device->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Kolom Kiri: Profil Hardware --}}
            <div class="bg-white rounded-xl shadow-sm border border-surface-container-high overflow-hidden">
                <div class="px-6 py-4 bg-surface-container-lowest border-b border-surface-container-high flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    <h3 class="font-bold text-on-surface text-sm">Profil Hardware</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wide">Nama Perangkat <span class="text-error">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $device->name) }}" placeholder="Contoh: HERA Node Sektor 4" required
                               class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wide">Identifier / MAC Address (UID)</label>
                        <input type="text" name="uid" value="{{ old('uid', $device->uid) }}" placeholder="Contoh: AA:BB:CC:DD:EE:FF"
                               class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface text-sm font-mono outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                        <p class="text-[10px] text-on-surface-variant mt-1">ID Unik ini akan dicocokkan dengan payload MQTT dari sensor.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wide">IP Address</label>
                            <input type="text" name="ip_address" value="{{ old('ip_address', $device->ip_address) }}" placeholder="192.168.x.x"
                                   class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2 text-on-surface text-sm font-mono outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wide">Versi Firmware</label>
                            <input type="text" name="firmware_version" value="{{ old('firmware_version', $device->firmware_version) }}" placeholder="v1.0.0"
                                   class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2 text-on-surface text-sm font-mono outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wide">Status Administratif <span class="text-error">*</span></label>
                        <select name="status" class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                            <option value="active" @selected(old('status', $device->status) === 'active')>🟢 Active (Normal Operation)</option>
                            <option value="inactive" @selected(old('status', $device->status) === 'inactive')>⚪ Inactive (Disabled)</option>
                            <option value="maintenance" @selected(old('status', $device->status) === 'maintenance')>🟡 Maintenance (Perbaikan)</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Lokasi & Deployment --}}
            <div class="bg-white rounded-xl shadow-sm border border-surface-container-high overflow-hidden">
                <div class="px-6 py-4 bg-surface-container-lowest border-b border-surface-container-high flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <h3 class="font-bold text-on-surface text-sm">Deployment & Lokasi</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wide">Nama Lokasi</label>
                        <input type="text" name="location_name" value="{{ old('location_name', $device->location_name) }}" placeholder="Contoh: Hulu Sungai / Bawah Jembatan Merah"
                               class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wide">Latitude</label>
                            <input type="number" step="any" name="latitude" value="{{ old('latitude', $device->latitude) }}" placeholder="-5.132x"
                                   class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2 text-on-surface text-sm font-mono outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wide">Longitude</label>
                            <input type="number" step="any" name="longitude" value="{{ old('longitude', $device->longitude) }}" placeholder="119.412x"
                                   class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2 text-on-surface text-sm font-mono outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wide">Tanggal Pemasangan</label>
                        <input type="date" name="installed_at" value="{{ old('installed_at', $device->installed_at ? $device->installed_at->format('Y-m-d') : '') }}"
                               class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wide">Catatan Teknisi</label>
                        <textarea name="notes" rows="3" placeholder="Kendala lapangan, tinggi air saat pasang, dll..."
                                  class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">{{ old('notes', $device->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('iot-devices.index') }}"
               class="px-5 py-2.5 bg-surface-container border border-surface-container-high text-on-surface-variant hover:bg-surface-container-high rounded-lg font-medium text-sm transition-colors">
                Batal
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-primary text-on-primary font-bold rounded-lg hover:brightness-110 shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ $device->exists ? 'Simpan Perubahan' : 'Catat Perangkat Baru' }}
            </button>
        </div>
    </form>
</div>
@endsection
