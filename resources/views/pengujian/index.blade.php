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

    <!-- Tab Navigation -->
    <div class="flex gap-2 border-b border-gray-700">
        <button id="tab-table" onclick="switchTab('table')" class="px-6 py-3 font-semibold text-white border-b-2 border-emerald-500 transition-all hover:text-emerald-400">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
            Tabel Data
        </button>
        <button id="tab-maps" onclick="switchTab('maps')" class="px-6 py-3 font-semibold text-gray-400 border-b-2 border-transparent hover:text-white transition-all">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 003 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 16.382V5.618a1 1 0 00-1.447-.894L15 7m0 13V7m0 0L9 4"></path></svg>
            Peta Pengujian
        </button>
    </div>

    <!-- Tab 1: Tabel Data View -->
    <div id="view-table" class="tab-view">
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
                                <span class="font-mono">{{ number_format($test->latitude, 4) }}, {{ number_format($test->longitude, 4) }}</span>
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

    <!-- Tab 2: Peta Pengujian View -->
    <div id="view-maps" class="tab-view hidden">
        <div class="glass-card rounded-xl overflow-hidden border border-gray-800/60 shadow-2xl" style="height: 600px;">
            <div class="flex h-full">
                <!-- Maps Container (Left) -->
                <div class="flex-1 border-r border-gray-700">
                    <div id="map-container-main" style="width: 100%; height: 100%;"></div>
                </div>

                <!-- Sidebar List (Right) -->
                <div class="w-80 bg-gray-800/80 border-l border-gray-700 overflow-y-auto">
                    <div class="p-4 space-y-3">
                        <h3 class="text-base font-bold text-white mb-4 border-b border-gray-700 pb-3">Daftar Titik Pengujian</h3>
                        @forelse($tests as $test)
                        <div class="marker-item bg-gray-700/40 rounded-lg p-3 border-l-4 border-emerald-500 cursor-pointer hover:bg-gray-700/60 transition" 
                             onclick="jumpToMarker({{ $test->id }}, {{ $test->latitude }}, {{ $test->longitude }})">
                            <p class="text-xs text-gray-400 mb-1">{{ $test->created_at->format('d M Y, H:i') }}</p>
                            <p class="text-sm font-semibold text-white">{{ optional($test->user)->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-blue-400 font-mono mt-1">{{ number_format($test->latitude, 4) }}, {{ number_format($test->longitude, 4) }}</p>
                        </div>
                        @empty
                        <p class="text-center text-gray-500 py-8">Belum ada data pengujian</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let mainMap = null;
    const markers = {};

    function switchTab(tab) {
        // Toggle visibility
        document.getElementById('view-table').classList.toggle('hidden', tab !== 'table');
        document.getElementById('view-maps').classList.toggle('hidden', tab !== 'maps');
        
        // Update tab styles
        const updateTabStyle = (id, active) => {
            const btn = document.getElementById(id);
            if (active) {
                btn.classList.remove('border-transparent', 'text-gray-400');
                btn.classList.add('border-emerald-500', 'text-white');
            } else {
                btn.classList.remove('border-emerald-500', 'text-white');
                btn.classList.add('border-transparent', 'text-gray-400');
            }
        };
        
        updateTabStyle('tab-table', tab === 'table');
        updateTabStyle('tab-maps', tab === 'maps');
        
        if (tab === 'maps') {
            setTimeout(() => {
                if (!mainMap) {
                    initializeMainMap();
                } else {
                    mainMap.invalidateSize(true);
                }
            }, 150);
        }
    }

    function initializeMainMap() {
        try {
            const mapContainer = document.getElementById('map-container-main');
            if (!mapContainer) {
                console.error('Map container not found');
                return;
            }

            const defaultLat = {{ $tests->first()?->latitude ?? -6.2088 }};
            const defaultLng = {{ $tests->first()?->longitude ?? 106.8456 }};

            mainMap = L.map('map-container-main').setView([defaultLat, defaultLng], 12);

            L.tileLayer('http://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                attribution: '&copy; <a href="https://www.google.com/maps">Google Maps</a>'
            }).addTo(mainMap);

            @foreach($tests as $test)
            addMarkerToMap(
                {{ $test->id }}, 
                {{ $test->latitude }}, 
                {{ $test->longitude }},
                {
                    timestamp: '{{ $test->created_at->format('d M Y, H:i') }}',
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
            );
            @endforeach

            setTimeout(() => mainMap.invalidateSize(true), 200);
        } catch(e) {
            console.error('Map initialization error:', e);
        }
    }

    function addMarkerToMap(id, lat, lng, data) {
        if (!mainMap) return;
        
        // Desain icon modern, minimalis & clean dengan animasi pulse bawaan CSS
        const customIcon = L.divIcon({
            className: '', // Kosongkan nama class bawaan leaflet agar styling murni kita
            html: `
                <div class="modern-marker-container">
                    <div class="modern-marker-pulse"></div>
                    <div class="modern-marker-dot"></div>
                </div>
            `,
            iconSize: [40, 40],
            iconAnchor: [20, 20],
            popupAnchor: [0, -14]
        });

        // Template Popup Compact (Tidak terlalu besar, format Grid)
        const popupHtml = `
            <div style="font-family: 'Inter', sans-serif; min-width: 180px; font-size: 11px;">
                <div style="font-weight: 700; font-size: 13px; color: #1f2937; margin-bottom: 6px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px;">
                    📍 Data Sensor Valid
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px; color: #4b5563;">
                    <div style="grid-column: span 2;"><span style="color:#9ca3af">Waktu:</span> <b>${data.timestamp}</b></div>
                    <div style="grid-column: span 2; margin-bottom: 4px;"><span style="color:#9ca3af">Petugas:</span> <b>${data.officer}</b></div>
                    
                    <div style="background: #f3f4f6; padding: 2px 4px; border-radius: 4px;"><span style="color:#6b7280">pH:</span> <b>${data.ph}</b></div>
                    <div style="background: #f3f4f6; padding: 2px 4px; border-radius: 4px;"><span style="color:#6b7280">TDS:</span> <b>${data.tds}</b></div>
                    <div style="background: #f3f4f6; padding: 2px 4px; border-radius: 4px;"><span style="color:#6b7280">EC:</span> <b>${data.ec}</b></div>
                    <div style="background: #f3f4f6; padding: 2px 4px; border-radius: 4px;"><span style="color:#6b7280">Air:</span> <b>${data.suhu_air}°C</b></div>
                    <div style="background: #f3f4f6; padding: 2px 4px; border-radius: 4px;"><span style="color:#6b7280">Udr:</span> <b>${data.suhu_lingkungan}°C</b></div>
                    <div style="background: #f3f4f6; padding: 2px 4px; border-radius: 4px;"><span style="color:#6b7280">Rh:</span> <b>${data.kelembapan}%</b></div>
                    <div style="background: #f3f4f6; padding: 2px 4px; border-radius: 4px; grid-column: span 2;"><span style="color:#6b7280">Batt:</span> <b>${data.tegangan} V</b></div>
                    
                    <div style="grid-column: span 2; background: #f3e8ff; padding: 4px; border-radius: 4px; margin-top: 4px; color: #7e22ce; font-weight: 700; text-align: center;">
                        🤖 CR Est: ${data.cr} mg/L
                    </div>
                </div>
            </div>
        `;

        const marker = L.marker([lat, lng], { icon: customIcon }).addTo(mainMap);
        marker.bindPopup(popupHtml, { maxWidth: 240, closeButton: false });
        markers[id] = marker;
    }
</script>

<style>
/* Desain UI CSS Marker Modern Clean */
.modern-marker-container {
    position: relative;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.modern-marker-dot {
    width: 14px;
    height: 14px;
    background-color: #10b981; /* emerald-500 */
    border: 3px solid #064e3b; /* emerald-900 border untuk efek solid */
    border-radius: 50%;
    box-shadow: 0 0 10px rgba(16, 185, 129, 0.6);
    position: relative;
    z-index: 2;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modern-marker-pulse {
    position: absolute;
    width: 100%;
    height: 100%;
    background-color: rgba(16, 185, 129, 0.4);
    border-radius: 50%;
    z-index: 1;
    animation: markerPulse 2s infinite ease-out;
}

/* Transisi Halus pada Hover, Membesarkan titik bagian dalam tanpa merusak parent box / translate3D */
.modern-marker-container:hover .modern-marker-dot {
    transform: scale(1.6);
    box-shadow: 0 0 15px rgba(16, 185, 129, 0.9);
    background-color: #34d399; /* emerald lebih terang */
    border-color: #ffffff;
}

@keyframes markerPulse {
    0% { transform: scale(0.5); opacity: 1; }
    100% { transform: scale(2.2); opacity: 0; }
}
</style>

    function jumpToMarker(id, lat, lng) {
        if (mainMap) {
            mainMap.setView([lat, lng], 17, { animate: true, duration: 1.5 });
            
            // Tunggu sedikit agar animasi zoom peta berjalan, lalu tembak buka pop-upnya
            setTimeout(() => {
                if(markers[id]) markers[id].openPopup();
            }, 600);
        }
    }
</script>
@endpush
