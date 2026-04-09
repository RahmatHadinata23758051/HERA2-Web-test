@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-gray-800/50 pb-5">
        <div>
            <h2 class="text-3xl font-bold tracking-tight text-white">Laporan Raw Data</h2>
            <p class="text-gray-400 mt-1">Ekspor dan filter data telemetri historis sensor HERA</p>
        </div>
        <div class="flex gap-3">
            <div x-data="{ openExport: false }" class="relative">
                <button @click="openExport = !openExport" @click.away="openExport = false" class="inline-flex items-center gap-2 bg-emerald-600/20 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-600 hover:text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all shadow-lg shadow-emerald-500/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export Data
                    <svg class="w-4 h-4 transition-transform" :class="openExport ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="openExport" x-transition.opacity.duration.200ms style="display: none;" class="absolute right-0 mt-2 w-48 bg-gray-800 border border-gray-700 rounded-lg shadow-xl overflow-hidden z-50">
                    @php 
                        $xlsxReq = array_merge(request()->all(), ['format' => 'xlsx']);
                        $csvReq = array_merge(request()->all(), ['format' => 'csv']);
                    @endphp
                    <a href="{{ route('laporan.export.excel', $xlsxReq) }}" class="block px-4 py-3 text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">
                        Format Excel (.xlsx)
                    </a>
                    <div class="border-t border-gray-700/50"></div>
                    <a href="{{ route('laporan.export.excel', $csvReq) }}" class="block px-4 py-3 text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">
                        Format CSV (.csv)
                    </a>
                </div>
            </div>
            <a href="{{ route('laporan.export.pdf', request()->all()) }}" class="inline-flex items-center gap-2 bg-red-600/20 text-red-400 border border-red-500/30 hover:bg-red-600 hover:text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all shadow-lg shadow-red-500/10" target="_blank">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export PDF
            </a>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="glass-card rounded-xl p-5" x-data="{ showFilter: {{ request()->anyFilled(['from_date', 'to_date', 'status']) ? 'true' : 'false' }} }">
        <div class="flex justify-between items-center cursor-pointer" @click="showFilter = !showFilter">
            <h3 class="font-semibold text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Filter Spesifik
            </h3>
            <svg class="w-5 h-5 text-gray-500 transition-transform" :class="showFilter ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>

        <div x-show="showFilter" x-transition class="mt-4 pt-4 border-t border-gray-700/50">
            <form action="{{ route('laporan.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Tanggal Mulai</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full bg-gray-900/50 border border-gray-700 text-gray-200 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Tanggal Akhir</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full bg-gray-900/50 border border-gray-700 text-gray-200 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Status Sensor</label>
                    <select name="status" class="w-full bg-gray-900/50 border border-gray-700 text-gray-200 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 outline-none">
                        <option value="Semua" {{ request('status') == 'Semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="normal" {{ request('status') == 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="warning" {{ request('status') == 'warning' ? 'selected' : '' }}>Warning</option>
                        <option value="danger" {{ request('status') == 'danger' ? 'selected' : '' }}>Danger</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm px-4 py-2.5 transition-colors">Terapkan Filter</button>
                    @if(request()->anyFilled(['from_date', 'to_date', 'status']))
                        <a href="{{ route('laporan.index') }}" class="w-full bg-gray-700 hover:bg-gray-600 text-white text-center font-medium rounded-lg text-sm px-4 py-2.5 transition-colors">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-300">
                <thead class="text-xs text-gray-400 uppercase bg-gray-800/80 border-b border-gray-700/50">
                    <tr>
                        <th class="px-5 py-4 whitespace-nowrap">Tanggal & Waktu</th>
                        <th class="px-5 py-4 text-center">Cr (µg/L)</th>
                        <th class="px-5 py-4 text-center">pH</th>
                        <th class="px-5 py-4 text-center">EC (µS/cm)</th>
                        <th class="px-5 py-4 text-center">TDS (mg/L)</th>
                        <th class="px-5 py-4 text-center">Suhu (Air/Ling.)</th>
                        <th class="px-5 py-4 text-center">Kelembapan</th>
                        <th class="px-5 py-4 text-center">Tegangan</th>
                        <th class="px-5 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/50">
                    @forelse($readings as $row)
                        <tr class="hover:bg-gray-800/30 transition-colors">
                            <td class="px-5 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-200">{{ $row->created_at->format('d M Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $row->created_at->format('H:i:s') }}</div>
                            </td>
                            <td class="px-5 py-3 text-center font-medium">{{ number_format($row->cr_estimated, 2) }}</td>
                            <td class="px-5 py-3 text-center">{{ number_format($row->ph, 2) }}</td>
                            <td class="px-5 py-3 text-center">{{ number_format($row->ec, 1) }}</td>
                            <td class="px-5 py-3 text-center">{{ number_format($row->tds, 1) }}</td>
                            <td class="px-5 py-3 text-center text-xs">
                                <span class="text-blue-400">{{ $row->suhu_air }}°C</span> / <span class="text-pink-400">{{ $row->suhu_lingkungan }}°C</span>
                            </td>
                            <td class="px-5 py-3 text-center">{{ $row->kelembapan }}%</td>
                            <td class="px-5 py-3 text-center text-gray-400 text-xs">{{ $row->tegangan }}V</td>
                            <td class="px-5 py-3 text-center">
                                @if($row->status === 'normal')
                                    <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">Normal</span>
                                @elseif($row->status === 'warning')
                                    <span class="bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">Warning</span>
                                @else
                                    <span class="bg-red-500/10 text-red-400 border border-red-500/20 text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider animate-pulse">Danger</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-10 text-center text-gray-500 bg-gray-900/20">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-10 h-10 mb-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                    <p>Tidak ada data telemetri yang ditemukan pada kriteria filter ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($readings->hasPages())
        <div class="px-5 py-4 border-t border-gray-800/50 bg-gray-900/30">
            {{ $readings->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
