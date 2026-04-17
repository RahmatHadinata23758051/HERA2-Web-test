@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-white p-5 rounded-xl shadow-sm border border-surface-container-high flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="p-3 bg-primary/10 rounded-xl flex-shrink-0">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight text-on-surface font-headline">Manajemen Perangkat IoT</h2>
                <p class="text-on-surface-variant text-sm mt-0.5">Pantau status, kelola inventaris, dan identifikasi node sensor fisik (Hardware).</p>
            </div>
        </div>
        <a href="{{ route('iot-devices.create') }}"
           class="flex-shrink-0 flex items-center justify-center gap-2 px-5 py-2.5 bg-primary text-on-primary rounded-lg hover:brightness-110 transition-all text-sm font-bold shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Perangkat
        </a>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
    <div class="p-4 bg-primary/10 border border-primary/20 rounded-xl flex items-start gap-3">
        <svg class="w-5 h-5 text-primary flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-primary font-medium">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Filter Search --}}
    <form method="GET" action="{{ route('iot-devices.index') }}"
          class="bg-white rounded-xl border border-surface-container-high shadow-sm p-2 flex gap-2">
        <div class="flex-1 relative">
            <svg class="w-5 h-5 text-on-surface-variant absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama perangkat, lokasi, atau UID/MAC..."
                   class="w-full pl-10 pr-4 py-2 border-none bg-transparent text-on-surface text-sm outline-none">
        </div>
        <button type="submit" class="px-4 py-2 bg-surface-container-low hover:bg-surface-container border border-surface-container-high rounded-lg text-sm font-bold transition-colors">Cari</button>
        @if(request()->has('search'))
            <a href="{{ route('iot-devices.index') }}" class="px-3 py-2 bg-error-container text-error rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </a>
        @endif
    </form>

    {{-- Inventory Table --}}
    <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-surface-container-high">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="text-[10px] text-on-surface-variant uppercase tracking-widest font-black bg-surface-container-low border-b border-surface-container-high">
                    <tr>
                        <th class="px-5 py-4 w-12 text-center">Status</th>
                        <th class="px-5 py-4">Perangkat</th>
                        <th class="px-5 py-4">Lokasi & UID</th>
                        <th class="px-5 py-4">Status Admin</th>
                        <th class="px-5 py-4">Last Seen</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-high">
                    @forelse($devices as $dev)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        
                        {{-- Online / Offline Indicator --}}
                        <td class="px-5 py-4 text-center border-l-2 @if($dev->is_online) border-primary @else border-error/50 @endif">
                            <div class="relative inline-flex">
                                @if($dev->is_online)
                                    <div class="w-3 h-3 bg-primary rounded-full"></div>
                                    <div class="w-3 h-3 bg-primary rounded-full absolute inset-0 animate-ping opacity-75"></div>
                                @else
                                    <div class="w-3 h-3 bg-surface-container-highest rounded-full"></div>
                                @endif
                            </div>
                        </td>

                        {{-- Perangkat --}}
                        <td class="px-5 py-4">
                            <p class="font-bold text-on-surface text-sm">{{ $dev->name }}</p>
                            @if($dev->ip_address || $dev->firmware_version)
                                <p class="text-[10px] text-on-surface-variant font-mono mt-0.5">
                                    {{ $dev->ip_address ?? 'No IP' }} • v{{ $dev->firmware_version ?? '0' }}
                                </p>
                            @endif
                        </td>

                        {{-- Lokasi & UID --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-1.5 text-on-surface-variant mb-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $dev->location_name ?? 'Lokasi belum diset' }}</span>
                            </div>
                            <span class="text-[10px] font-mono bg-surface-container px-2 py-0.5 rounded text-outline">{{ $dev->uid ?? 'UID_UNSET' }}</span>
                        </td>

                        {{-- Admin Status --}}
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-wider border
                                @if($dev->status === 'active') bg-primary/10 text-primary border-primary/20
                                @elseif($dev->status === 'maintenance') bg-yellow-100 text-yellow-700 border-yellow-200
                                @else bg-surface-container text-on-surface-variant border-surface-container-high @endif">
                                {{ $dev->status }}
                            </span>
                        </td>

                        {{-- Last Seen --}}
                        <td class="px-5 py-4">
                            @if($dev->last_seen_at)
                                <p class="text-xs font-bold @if($dev->is_online) text-primary @else text-on-surface-variant @endif">
                                    {{ $dev->last_seen_at->diffForHumans() }}
                                </p>
                                <p class="text-[10px] text-on-surface-variant mt-0.5">{{ $dev->last_seen_at->timezone('Asia/Makassar')->format('d M Y, H:i') }}</p>
                            @else
                                <span class="text-xs text-on-surface-variant italic">Belum pernah online</span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('iot-devices.edit', $dev) }}" 
                                   class="p-2 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                                <form action="{{ route('iot-devices.destroy', $dev) }}" method="POST" onsubmit="return confirm('Hapus perangkat ini permanen? Data sensor yang lalu tidak akan terhapus namun relasinya akan putus.');" class="inline-block">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-on-surface-variant hover:text-error hover:bg-error-container rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="p-4 bg-surface-container rounded-full">
                                    <svg class="w-8 h-8 text-outline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                                    </svg>
                                </div>
                                <p class="text-on-surface-variant font-medium">Belum ada perangkat IoT yang terdaftar.</p>
                                <a href="{{ route('iot-devices.create') }}" class="mt-2 px-4 py-2 bg-primary/10 text-primary font-bold text-sm rounded-lg hover:bg-primary/20 transition-colors">Daftarkan Perangkat Pertama</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($devices->hasPages())
        <div class="px-5 py-4 border-t border-surface-container-high bg-surface-container-lowest flex items-center justify-between">
            <p class="text-xs text-on-surface-variant">
                Menampilkan <strong>{{ $devices->firstItem() }}–{{ $devices->lastItem() }}</strong> dari <strong>{{ $devices->total() }}</strong>
            </p>
            <div>{{ $devices->links('pagination::tailwind') }}</div>
        </div>
        @endif
    </div>
</div>
@endsection
