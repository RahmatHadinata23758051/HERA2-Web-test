@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-white p-5 rounded-xl shadow-sm border border-surface-container-high flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-on-surface font-headline">Log Aktivitas Sistem</h2>
            <p class="text-on-surface-variant text-sm mt-1">Riwayat seluruh aktivitas pengguna dalam aplikasi HERA.</p>
        </div>
        <span class="text-xs text-on-surface-variant bg-surface-container px-3 py-1.5 rounded-lg font-mono flex-shrink-0">
            Total: <strong>{{ $logs->total() }}</strong> catatan
        </span>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('activity-log.index') }}"
          class="bg-white rounded-xl border border-surface-container-high shadow-sm p-4 flex flex-wrap gap-3 items-end">

        <div class="flex-1 min-w-[160px]">
            <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wide">Tipe Aksi</label>
            <select name="log_action"
                    class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-3 py-2 text-on-surface text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                <option value="">Semua Aksi</option>
                @foreach($logActions as $action)
                    <option value="{{ $action }}" @selected(request('log_action') === $action)>{{ $action }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex-1 min-w-[140px]">
            <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wide">Dari Tanggal</label>
            <input type="date" name="log_from" value="{{ request('log_from') }}"
                   class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-3 py-2 text-on-surface text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
        </div>

        <div class="flex-1 min-w-[140px]">
            <label class="block text-xs font-bold text-on-surface-variant mb-1.5 uppercase tracking-wide">Sampai Tanggal</label>
            <input type="date" name="log_to" value="{{ request('log_to') }}"
                   class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-3 py-2 text-on-surface text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
        </div>

        <div class="flex gap-2 flex-shrink-0">
            <button type="submit"
                    class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary rounded-lg hover:brightness-110 transition-all text-sm font-bold shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Filter
            </button>
            @if(request()->hasAny(['log_action','log_from','log_to']))
            <a href="{{ route('activity-log.index') }}"
               class="flex items-center gap-2 px-4 py-2 bg-surface-container text-on-surface-variant border border-surface-container-high rounded-lg hover:bg-surface-container-high transition-colors text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Reset
            </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-surface-container-high">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-[10px] text-on-surface-variant uppercase tracking-widest font-black bg-surface-container-low border-b border-surface-container-high">
                    <tr>
                        <th class="px-6 py-4 w-40">Waktu</th>
                        <th class="px-6 py-4">Pengguna</th>
                        <th class="px-6 py-4 w-36">Aksi</th>
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
                                @elseif(in_array($log->action, ['Update User', 'Update Settings', 'Update Threshold'])) bg-yellow-100 text-yellow-700 border-yellow-200
                                @else bg-primary/10 text-primary border-primary/20 @endif">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-on-surface-variant text-sm">{{ $log->details ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="p-4 bg-surface-container rounded-full">
                                    <svg class="w-8 h-8 text-outline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <p class="text-on-surface-variant text-sm">
                                    {{ request()->hasAny(['log_action','log_from','log_to']) ? 'Tidak ada aktivitas sesuai filter.' : 'Belum ada catatan aktivitas.' }}
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-surface-container-high bg-surface-container-lowest flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-on-surface-variant">
                Menampilkan <strong>{{ $logs->firstItem() }}–{{ $logs->lastItem() }}</strong>
                dari <strong>{{ $logs->total() }}</strong> catatan
            </p>
            <div class="flex items-center gap-1">
                {{-- Prev --}}
                @if($logs->onFirstPage())
                    <span class="px-3 py-1.5 rounded-lg text-sm text-on-surface-variant bg-surface-container cursor-not-allowed opacity-50">‹</span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}"
                       class="px-3 py-1.5 rounded-lg text-sm text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors">‹</a>
                @endif

                {{-- Page Numbers --}}
                @foreach($logs->getUrlRange(max(1, $logs->currentPage()-2), min($logs->lastPage(), $logs->currentPage()+2)) as $page => $url)
                    @if($page == $logs->currentPage())
                        <span class="px-3 py-1.5 rounded-lg text-sm font-bold bg-primary text-on-primary shadow-sm">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                           class="px-3 py-1.5 rounded-lg text-sm text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}"
                       class="px-3 py-1.5 rounded-lg text-sm text-on-surface-variant hover:bg-surface-container-high hover:text-primary transition-colors">›</a>
                @else
                    <span class="px-3 py-1.5 rounded-lg text-sm text-on-surface-variant bg-surface-container cursor-not-allowed opacity-50">›</span>
                @endif
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
