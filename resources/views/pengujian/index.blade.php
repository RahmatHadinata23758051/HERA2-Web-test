@extends('layouts.app')

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endpush

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
                            <div class="flex items-center justify-center gap-2">
                                <span class="font-mono">{{ number_format($test->latitude, 4) }}, {{ number_format($test->longitude, 4) }}</span>
                                <button onclick="toggleMap(
                                    {{ $test->id }},
                                    {{ $test->latitude }},
                                    {{ $test->longitude }},
                                    {
                                        timestamp: '{{ $test->created_at->format('d M Y, H:i:s') }}',
                                        officer: '{{ addslashes(optional($test->user)->name ?? 'Unknown') }}',
                                        ph: '{{ $test->ph ?? '-' }}',
                                        tds: '{{ $test->tds ?? '-' }}',
                                        ec: '{{ $test->ec ?? '-' }}',
                                        suhu_air: '{{ $test->suhu_air ?? '-' }}',
                                        suhu_lingkungan: '{{ $test->suhu_lingkungan ?? '-' }}',
                                        kelembapan: '{{ $test->kelembapan ?? '-' }}',
                                        tegangan: '{{ $test->tegangan ?? '-' }}',
                                        cr: '{{ $test->cr_estimated ?? '-' }}'
                                    }
                                )" class="p-1 rounded bg-blue-500/20 text-blue-400 hover:bg-blue-500/40 hover:text-white transition" title="Buka/Tutup Peta">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </button>
                            </div>
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
                    
                    <!-- Accordion Map Row -->
                    <tr id="map-row-{{ $test->id }}" class="hidden bg-gray-900/30 border-b border-gray-700/50">
                        <td colspan="11" class="p-4">
                            <div class="rounded-lg overflow-hidden border border-gray-700" id="map-container-{{ $test->id }}" style="height: 300px; width: 100%;">
                                <!-- Leaflet Init Point -->
                            </div>
                        </td>
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

@push('scripts')
<script>
    // Memori Maps Cache (Biar gak re-init map instansi yang sama)
    const mapInstances = {};

    function toggleMap(id, lat, lng, data) {
        const row = document.getElementById('map-row-' + id);
        row.classList.toggle('hidden');

        if (!row.classList.contains('hidden')) {
            setTimeout(() => {
                const mapId = 'map-container-' + id;
                if (!mapInstances[mapId]) {
                    const map = L.map(mapId).setView([lat, lng], 17);

                    L.tileLayer('http://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                        maxZoom: 20,
                        attribution: '&copy; <a href="https://www.google.com/maps">Google Maps Satellite</a>'
                    }).addTo(map);

                    // Build rich popup content
                    const popupHtml = `
                        <div style="font-family: 'Inter', sans-serif; min-width: 240px; font-size: 13px;">
                            <div style="background: #1e3a5f; color: #93c5fd; padding: 8px 12px; border-radius: 6px 6px 0 0; font-weight: 700; font-size: 14px; margin: -13px -19px 10px -19px;">
                                📍 Titik Pengukuran Lapangan
                            </div>

                            <table style="width: 100%; border-collapse: collapse; color: #333;">
                                <tr style="border-bottom: 1px solid #e5e7eb;">
                                    <td style="padding: 4px 6px; color: #6b7280; white-space: nowrap;">🕐 Timestamp</td>
                                    <td style="padding: 4px 6px; font-weight: 600;">${data.timestamp}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e5e7eb;">
                                    <td style="padding: 4px 6px; color: #6b7280; white-space: nowrap;">👤 Petugas</td>
                                    <td style="padding: 4px 6px; font-weight: 600;">${data.officer}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e5e7eb; background: #f9fafb;">
                                    <td style="padding: 4px 6px; color: #6b7280; white-space: nowrap;">🌐 Latitude</td>
                                    <td style="padding: 4px 6px; font-family: monospace;">${lat}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e5e7eb; background: #f9fafb;">
                                    <td style="padding: 4px 6px; color: #6b7280; white-space: nowrap;">🌐 Longitude</td>
                                    <td style="padding: 4px 6px; font-family: monospace;">${lng}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e5e7eb; background: #f9fafb;">
                                    <td style="padding: 4px 6px; color: #6b7280; white-space: nowrap;">⛰️ Altitude</td>
                                    <td style="padding: 4px 6px; font-family: monospace;">N/A</td>
                                </tr>
                                <tr style="background: #ecfdf5; border-top: 2px solid #6ee7b7;">
                                    <td colspan="2" style="padding: 5px 6px; font-weight: 700; color: #065f46; font-size: 12px; letter-spacing: 0.05em;">PARAMETER TERUKUR</td>
                                </tr>
                                <tr style="background: #f0fdf4;">
                                    <td style="padding: 3px 6px; color: #6b7280;">pH</td>
                                    <td style="padding: 3px 6px; font-family: monospace; font-weight: 600;">${data.ph}</td>
                                </tr>
                                <tr style="background: #f0fdf4;">
                                    <td style="padding: 3px 6px; color: #6b7280;">TDS</td>
                                    <td style="padding: 3px 6px; font-family: monospace; font-weight: 600;">${data.tds} ppm</td>
                                </tr>
                                <tr style="background: #f0fdf4;">
                                    <td style="padding: 3px 6px; color: #6b7280;">EC</td>
                                    <td style="padding: 3px 6px; font-family: monospace; font-weight: 600;">${data.ec} mS</td>
                                </tr>
                                <tr style="background: #f0fdf4;">
                                    <td style="padding: 3px 6px; color: #6b7280;">Suhu Air</td>
                                    <td style="padding: 3px 6px; font-family: monospace; font-weight: 600;">${data.suhu_air} °C</td>
                                </tr>
                                <tr style="background: #f0fdf4;">
                                    <td style="padding: 3px 6px; color: #6b7280;">Suhu Udara</td>
                                    <td style="padding: 3px 6px; font-family: monospace; font-weight: 600;">${data.suhu_lingkungan} °C</td>
                                </tr>
                                <tr style="background: #f0fdf4;">
                                    <td style="padding: 3px 6px; color: #6b7280;">Kelembapan</td>
                                    <td style="padding: 3px 6px; font-family: monospace; font-weight: 600;">${data.kelembapan} %</td>
                                </tr>
                                <tr style="background: #f0fdf4;">
                                    <td style="padding: 3px 6px; color: #6b7280;">Tegangan</td>
                                    <td style="padding: 3px 6px; font-family: monospace; font-weight: 600;">${data.tegangan} V</td>
                                </tr>
                                <tr style="background: #fdf4ff; border-top: 2px solid #d8b4fe;">
                                    <td style="padding: 5px 6px; color: #7c3aed; font-weight: 600;">🤖 Cr Estimated</td>
                                    <td style="padding: 5px 6px; font-family: monospace; font-weight: 700; color: #7c3aed;">${data.cr} mg/L</td>
                                </tr>
                            </table>
                        </div>
                    `;

                    L.marker([lat, lng])
                        .addTo(map)
                        .bindPopup(popupHtml, { maxWidth: 300 })
                        .openPopup();

                    mapInstances[mapId] = map;
                } else {
                    mapInstances[mapId].invalidateSize();
                }
            }, 50);
        }
    }
</script>
@endpush
