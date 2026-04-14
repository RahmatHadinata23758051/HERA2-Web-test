@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Laporan Data Pengujian Lapangan</h1>
            <p class="text-gray-400 text-sm mt-1">Rekam jejak spesifik pembacaan sensor yang divalidasi langsung di lapangan melalui perangkat Mobile.</p>
        </div>
    </div>

    <!-- Data Table -->
    <div class="glass-card rounded-xl overflow-hidden border border-gray-800/60 shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1200px]">
                <thead>
                    <tr class="bg-gray-800/80 border-b border-gray-700">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center border-r border-gray-700/50">Waktu Validasi</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center border-r border-gray-700/50">Nama Petugas</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center border-r border-gray-700/50">Koordinat GPS</th>
                        <th class="px-4 py-3 text-xs font-semibold text-emerald-300 uppercase tracking-wider text-center border-b border-r border-gray-700/50 bg-emerald-900/10" colspan="7">Data Sensor Terkalibrasi</th>
                        <th class="px-4 py-3 text-xs font-semibold text-purple-300 uppercase tracking-wider text-center border-b border-gray-700/50 bg-purple-900/10">Prediksi AI</th>
                    </tr>
                    <tr class="bg-gray-800/50 border-b border-gray-700">
                        <!-- Empty Headers for Spanning -->
                        <th class="px-4 py-2 border-r border-gray-700/50"></th>
                        <th class="px-4 py-2 border-r border-gray-700/50"></th>
                        <th class="px-4 py-2 border-r border-gray-700/50"></th>
                        <!-- Metrics -->
                        <th class="px-4 py-2 text-xs font-medium text-emerald-400 whitespace-nowrap text-center">pH</th>
                        <th class="px-4 py-2 text-xs font-medium text-emerald-400 whitespace-nowrap text-center">TDS (ppm)</th>
                        <th class="px-4 py-2 text-xs font-medium text-emerald-400 whitespace-nowrap text-center">EC (mS)</th>
                        <th class="px-4 py-2 text-xs font-medium text-emerald-400 whitespace-nowrap text-center">Suhu Air (°C)</th>
                        <th class="px-4 py-2 text-xs font-medium text-emerald-400 whitespace-nowrap text-center">Suhu Udr (°C)</th>
                        <th class="px-4 py-2 text-xs font-medium text-emerald-400 whitespace-nowrap text-center">Kelembapan (%)</th>
                        <th class="px-4 py-2 text-xs font-medium text-emerald-400 whitespace-nowrap text-center border-r border-gray-700/50">Tegangan (V)</th>
                        <!-- ML -->
                        <th class="px-4 py-2 text-xs font-medium text-purple-400 whitespace-nowrap text-center">CR Est. (mg/L)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/50">
                    @forelse($tests as $test)
                    <tr class="hover:bg-gray-800/30 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-300 font-mono text-center border-r border-gray-700/50">{{ $test->created_at->format('d M Y - H:i') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-200 font-medium whitespace-nowrap text-center border-r border-gray-700/50">{{ optional($test->user)->name ?? 'Unknown' }}</td>
                        <td class="px-4 py-3 text-sm text-blue-400 text-center border-r border-gray-700/50">
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $test->latitude }},{{ $test->longitude }}" target="_blank" class="hover:underline flex items-center justify-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                {{ number_format($test->latitude, 5) }}, {{ number_format($test->longitude, 5) }}
                            </a>
                        </td>
                        
                        <td class="px-4 py-3 text-sm font-mono text-emerald-200 text-center bg-emerald-900/5">{{ $test->ph ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-200 text-center bg-emerald-900/5">{{ $test->tds ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-200 text-center bg-emerald-900/5">{{ $test->ec ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-200 text-center bg-emerald-900/5">{{ $test->suhu_air ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-200 text-center bg-emerald-900/5">{{ $test->suhu_lingkungan ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-200 text-center bg-emerald-900/5">{{ $test->kelembapan ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-200 text-center border-r border-gray-700/50 bg-emerald-900/5">{{ $test->tegangan ?? '-' }}</td>

                        <!-- CR Estimated (Predicted value by ML) -->
                        <td class="px-4 py-3 text-sm font-mono font-bold text-purple-300 text-center bg-purple-900/5">{{ $test->cr_estimated ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-4 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <p class="text-lg font-medium">Belum ada Riwayat Pengujian Lapangan.</p>
                            <p class="mt-1">Gunakan aplikasi mobile dan tekan tombol "Simpan Data Pengujian" di lokasi rujukan untuk mengisi riwayat ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Links -->
        @if($tests->hasPages())
        <div class="px-4 py-3 border-t border-gray-800 bg-gray-900/50">
            {{ $tests->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
