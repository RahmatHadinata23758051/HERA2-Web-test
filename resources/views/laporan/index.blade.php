@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-xl shadow-sm border border-surface-container-high">
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-on-surface font-headline">Laporan Raw Data</h2>
            <p class="text-on-surface-variant mt-1 text-sm">Ekspor dan filter data telemetri historis sensor HERA</p>
        </div>
        <div class="flex gap-3 flex-shrink-0">
            {{-- Export Data Dropdown --}}
            <div x-data="{ openExport: false }" @click.away="openExport = false" class="relative">
                <button @click="openExport = !openExport"
                        class="inline-flex items-center gap-2 bg-secondary-container text-secondary border border-secondary/20 hover:brightness-95 px-4 py-2.5 rounded-lg text-sm font-bold transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export Data
                    <svg class="w-3.5 h-3.5 transition-transform" :class="openExport ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openExport" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" style="display:none;"
                     class="absolute right-0 mt-2 w-52 bg-white border border-surface-container-high rounded-xl shadow-xl overflow-hidden z-50">
                    @php
                        $xlsxReq = array_merge(request()->all(), ['format' => 'xlsx']);
                        $csvReq  = array_merge(request()->all(), ['format' => 'csv']);
                    @endphp
                    <a href="{{ route('laporan.export.excel', $xlsxReq) }}" class="flex items-center gap-2.5 px-4 py-3 text-sm text-on-surface-variant hover:text-primary hover:bg-surface-container-low transition-colors">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Format Excel (.xlsx)
                    </a>
                    <div class="border-t border-surface-container-high mx-3"></div>
                    <a href="{{ route('laporan.export.excel', $csvReq) }}" class="flex items-center gap-2.5 px-4 py-3 text-sm text-on-surface-variant hover:text-primary hover:bg-surface-container-low transition-colors">
                        <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Format CSV (.csv)
                    </a>
                </div>
            </div>

            {{-- Export PDF --}}
            <a href="{{ route('laporan.export.pdf', request()->all()) }}" target="_blank"
               class="inline-flex items-center gap-2 bg-error-container text-error border border-error/20 hover:brightness-95 px-4 py-2.5 rounded-lg text-sm font-bold transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export PDF
            </a>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="bg-white rounded-xl border border-surface-container-high shadow-sm overflow-hidden"
         x-data="{ showFilter: {{ request()->anyFilled(['from_date', 'to_date', 'status']) ? 'true' : 'false' }} }">
        
        <div class="flex justify-between items-center px-5 py-4 cursor-pointer hover:bg-surface-container-low transition-colors"
             @click="showFilter = !showFilter">
            <h3 class="font-bold text-on-surface text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filter Spesifik
                @if(request()->anyFilled(['from_date', 'to_date', 'status']))
                    <span class="px-2 py-0.5 bg-primary/10 text-primary text-[10px] font-bold rounded-full">Aktif</span>
                @endif
            </h3>
            <svg class="w-4 h-4 text-outline transition-transform" :class="showFilter ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>

        <div x-show="showFilter" x-transition class="px-5 pb-5 border-t border-surface-container-high pt-4">
            <form action="{{ route('laporan.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Tanggal Mulai</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}"
                           class="w-full bg-surface-container-low border border-surface-container-high text-on-surface text-sm rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary block p-2.5 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Tanggal Akhir</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}"
                           class="w-full bg-surface-container-low border border-surface-container-high text-on-surface text-sm rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary block p-2.5 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Status Sensor</label>
                    <select name="status"
                            class="w-full bg-surface-container-low border border-surface-container-high text-on-surface text-sm rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary block p-2.5 outline-none transition">
                        <option value="Semua" {{ request('status') == 'Semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="normal"  {{ request('status') == 'normal'  ? 'selected' : '' }}>Normal</option>
                        <option value="warning" {{ request('status') == 'warning' ? 'selected' : '' }}>Warning</option>
                        <option value="danger"  {{ request('status') == 'danger'  ? 'selected' : '' }}>Danger</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-primary text-on-primary font-bold rounded-lg text-sm px-4 py-2.5 transition-all hover:brightness-110 shadow-sm">
                        Terapkan
                    </button>
                    @if(request()->anyFilled(['from_date', 'to_date', 'status']))
                        <a href="{{ route('laporan.index') }}" class="flex-1 text-center bg-surface-container text-on-surface-variant hover:bg-surface-container-high font-medium rounded-lg text-sm px-4 py-2.5 transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-surface-container-high">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-[10px] text-on-surface-variant uppercase tracking-widest font-black bg-surface-container-low border-b border-surface-container-high">
                    <tr>
                        <th class="px-5 py-4 whitespace-nowrap">Tanggal & Waktu</th>
                        <th class="px-5 py-4 text-center">Cr (mg/L)</th>
                        <th class="px-5 py-4 text-center">pH</th>
                        <th class="px-5 py-4 text-center">EC (µS/cm)</th>
                        <th class="px-5 py-4 text-center">TDS (mg/L)</th>
                        <th class="px-5 py-4 text-center">Suhu (Air/Ling.)</th>
                        <th class="px-5 py-4 text-center">Kelembapan</th>
                        <th class="px-5 py-4 text-center">Tegangan</th>
                        <th class="px-5 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-high">
                    @forelse($readings as $row)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-5 py-3 whitespace-nowrap">
                            <div class="font-bold text-on-surface text-sm">{{ $row->created_at->format('d M Y') }}</div>
                            <div class="text-xs text-on-surface-variant font-mono">{{ $row->created_at->format('H:i:s') }}</div>
                        </td>
                        <td class="px-5 py-3 text-center font-mono font-bold
                            {{ $row->status === 'danger' ? 'text-error' : ($row->status === 'warning' ? 'text-yellow-600' : 'text-primary') }}">
                            {{ number_format($row->cr_estimated, 4) }}
                        </td>
                        <td class="px-5 py-3 text-center text-on-surface font-medium">{{ number_format($row->ph, 2) }}</td>
                        <td class="px-5 py-3 text-center text-on-surface font-medium">{{ number_format($row->ec, 1) }}</td>
                        <td class="px-5 py-3 text-center text-on-surface font-medium">{{ number_format($row->tds, 1) }}</td>
                        <td class="px-5 py-3 text-center text-xs">
                            <span class="text-sky-600 font-medium">{{ $row->suhu_air }}°C</span>
                            <span class="text-on-surface-variant mx-1">/</span>
                            <span class="text-pink-600 font-medium">{{ $row->suhu_lingkungan }}°C</span>
                        </td>
                        <td class="px-5 py-3 text-center text-on-surface font-medium">{{ $row->kelembapan }}%</td>
                        <td class="px-5 py-3 text-center text-on-surface-variant text-xs font-mono">{{ $row->tegangan }}V</td>
                        <td class="px-5 py-3 text-center">
                            @if($row->status === 'normal')
                                <span class="bg-primary/10 text-primary border border-primary/20 text-[10px] font-bold px-2 py-1 rounded-md uppercase tracking-wider">Normal</span>
                            @elseif($row->status === 'warning')
                                <span class="bg-yellow-100 text-yellow-700 border border-yellow-200 text-[10px] font-bold px-2 py-1 rounded-md uppercase tracking-wider">Warning</span>
                            @else
                                <span class="bg-error-container text-error border border-error/20 text-[10px] font-bold px-2 py-1 rounded-md uppercase tracking-wider animate-pulse">Danger</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="p-4 bg-surface-container rounded-full">
                                    <svg class="w-10 h-10 text-outline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                </div>
                                <p class="font-bold text-on-surface">Tidak ada data telemetri</p>
                                <p class="text-sm text-on-surface-variant">Coba ubah filter atau rentang tanggal yang dipilih.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($readings->hasPages())
        <div class="px-5 py-4 border-t border-surface-container-high bg-surface-container-lowest">
            {{ $readings->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
