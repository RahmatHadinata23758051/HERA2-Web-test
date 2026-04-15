@extends('layouts.app')

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endpush

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-xl shadow-sm border border-surface-container-high">
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-on-surface font-headline">Laporan Pengujian Lapangan</h2>
            <p class="text-on-surface-variant text-sm mt-1">Rekam jejak pembacaan sensor yang divalidasi langsung di lapangan melalui perangkat Mobile.</p>
        </div>
        <div class="flex items-center gap-2 text-xs text-on-surface-variant bg-surface-container rounded-lg px-3 py-2 border border-surface-container-high">
            <div class="w-2 h-2 rounded-full bg-primary animate-pulse"></div>
            <span class="font-bold">{{ $tests->total() }} titik</span> terekam
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="bg-white rounded-xl border border-surface-container-high shadow-sm overflow-hidden">
        <div class="flex border-b border-surface-container-high">
            <button id="tab-table" onclick="switchTab('table')"
                    class="flex items-center gap-2 px-6 py-3.5 text-sm font-bold text-primary border-b-2 border-primary transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                Tabel Data
            </button>
            <button id="tab-maps" onclick="switchTab('maps')"
                    class="flex items-center gap-2 px-6 py-3.5 text-sm font-medium text-on-surface-variant border-b-2 border-transparent hover:text-primary hover:border-primary/40 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l5.447 2.724A1 1 0 0021 16.382V5.618a1 1 0 00-1.447-.894L15 7m0 13V7m0 0L9 4"/></svg>
                Peta Pengujian
            </button>
        </div>

        {{-- Tab 1: Tabel Data --}}
        <div id="view-table" class="tab-view overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1200px]">
                <thead>
                    {{-- Group row --}}
                    <tr class="border-b border-surface-container-high bg-surface-container-low">
                        <th class="px-4 py-3 text-[10px] font-black text-on-surface-variant uppercase tracking-widest text-center border-r border-surface-container-high">Waktu Validasi</th>
                        <th class="px-4 py-3 text-[10px] font-black text-on-surface-variant uppercase tracking-widest text-center border-r border-surface-container-high">Nama Petugas</th>
                        <th class="px-4 py-3 text-[10px] font-black text-on-surface-variant uppercase tracking-widest text-center border-r border-surface-container-high">Koordinat GPS</th>
                        <th colspan="7" class="px-4 py-2.5 text-[10px] font-black text-emerald-700 uppercase tracking-widest text-center border-b border-r border-surface-container-high bg-emerald-50">Data Sensor Terkalibrasi</th>
                        <th class="px-4 py-2.5 text-[10px] font-black text-purple-700 uppercase tracking-widest text-center bg-purple-50">Prediksi AI</th>
                    </tr>
                    {{-- Sub-column row --}}
                    <tr class="border-b-2 border-surface-container-high bg-surface-container-low text-[10px] font-bold uppercase tracking-wider">
                        <th class="px-4 py-2 border-r border-surface-container-high"></th>
                        <th class="px-4 py-2 border-r border-surface-container-high"></th>
                        <th class="px-4 py-2 border-r border-surface-container-high"></th>
                        <th class="px-4 py-2 text-emerald-700 bg-emerald-50 text-center">pH</th>
                        <th class="px-4 py-2 text-emerald-700 bg-emerald-50 text-center">TDS (ppm)</th>
                        <th class="px-4 py-2 text-emerald-700 bg-emerald-50 text-center">EC (mS)</th>
                        <th class="px-4 py-2 text-emerald-700 bg-emerald-50 text-center whitespace-nowrap">Suhu Air (°C)</th>
                        <th class="px-4 py-2 text-emerald-700 bg-emerald-50 text-center whitespace-nowrap">Suhu Udr (°C)</th>
                        <th class="px-4 py-2 text-emerald-700 bg-emerald-50 text-center whitespace-nowrap">Kelembapan (%)</th>
                        <th class="px-4 py-2 text-emerald-700 bg-emerald-50 text-center border-r border-surface-container-high whitespace-nowrap">Tegangan (V)</th>
                        <th class="px-4 py-2 text-purple-700 bg-purple-50 text-center whitespace-nowrap">CR Est. (mg/L)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-high">
                    @forelse($tests as $test)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-4 py-3 text-xs text-on-surface-variant font-mono text-center border-r border-surface-container-high whitespace-nowrap">{{ $test->created_at->format('d M Y - H:i') }}</td>
                        <td class="px-4 py-3 text-sm text-on-surface font-bold text-center border-r border-surface-container-high whitespace-nowrap">{{ optional($test->user)->name ?? 'Unknown' }}</td>
                        <td class="px-4 py-3 text-xs text-primary font-mono text-center border-r border-surface-container-high">
                            {{ number_format($test->latitude, 4) }}, {{ number_format($test->longitude, 4) }}
                        </td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-700 text-center bg-emerald-50/40">{{ $test->ph ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-700 text-center bg-emerald-50/40">{{ $test->tds ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-700 text-center bg-emerald-50/40">{{ $test->ec ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-700 text-center bg-emerald-50/40">{{ $test->suhu_air ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-700 text-center bg-emerald-50/40">{{ $test->suhu_lingkungan ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-700 text-center bg-emerald-50/40">{{ $test->kelembapan ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-700 text-center border-r border-surface-container-high bg-emerald-50/40">{{ $test->tegangan ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-mono font-bold text-purple-700 text-center bg-purple-50/40">{{ $test->cr_estimated ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="p-4 bg-surface-container rounded-full">
                                    <svg class="h-10 w-10 text-outline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="font-bold text-on-surface">Belum ada Riwayat Pengujian Lapangan.</p>
                                <p class="text-sm text-on-surface-variant max-w-sm text-center">Gunakan aplikasi mobile dan tekan tombol <span class="font-bold text-primary">"Simpan Data Pengujian"</span> di lokasi rujukan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            @if($tests->hasPages())
            <div class="px-5 py-4 border-t border-surface-container-high bg-surface-container-lowest">
                {{ $tests->links() }}
            </div>
            @endif
        </div>

        {{-- Tab 2: Peta Pengujian --}}
        <div id="view-maps" class="tab-view hidden" style="height: 600px;">
            <div class="flex h-full">
                {{-- Map (Left) --}}
                <div class="flex-1 border-r border-surface-container-high">
                    <div id="map-container-main" style="width: 100%; height: 100%;"></div>
                </div>

                {{-- Sidebar List (Right) --}}
                <div class="w-72 bg-surface-container-lowest border-l border-surface-container-high overflow-y-auto no-scrollbar">
                    <div class="p-4 space-y-2.5">
                        <h3 class="text-sm font-black text-on-surface uppercase tracking-widest border-b border-surface-container-high pb-3 mb-4">
                            Daftar Titik Pengujian
                        </h3>
                        @forelse($tests as $test)
                        <div class="marker-item bg-white rounded-xl p-3.5 border border-surface-container-high border-l-4 border-l-primary cursor-pointer hover:shadow-md hover:border-primary/40 transition-all"
                             onclick="jumpToMarker({{ $test->id }}, {{ $test->latitude }}, {{ $test->longitude }})">
                            <p class="text-xs text-on-surface-variant font-mono mb-1">{{ $test->created_at->format('d M Y, H:i') }}</p>
                            <p class="text-sm font-bold text-on-surface">{{ optional($test->user)->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-primary font-mono mt-1">{{ number_format($test->latitude, 4) }}, {{ number_format($test->longitude, 4) }}</p>
                        </div>
                        @empty
                        <p class="text-center text-on-surface-variant text-sm py-8">Belum ada data pengujian</p>
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
        document.getElementById('view-table').classList.toggle('hidden', tab !== 'table');
        document.getElementById('view-maps').classList.toggle('hidden', tab !== 'maps');

        const updateTabStyle = (id, active) => {
            const btn = document.getElementById(id);
            if (active) {
                btn.classList.remove('border-transparent', 'text-on-surface-variant', 'font-medium');
                btn.classList.add('border-primary', 'text-primary', 'font-bold');
            } else {
                btn.classList.remove('border-primary', 'text-primary', 'font-bold');
                btn.classList.add('border-transparent', 'text-on-surface-variant', 'font-medium');
            }
        };
        updateTabStyle('tab-table', tab === 'table');
        updateTabStyle('tab-maps',  tab === 'maps');

        if (tab === 'maps') {
            setTimeout(() => {
                if (!mainMap) { initializeMainMap(); }
                else { mainMap.invalidateSize(true); }
            }, 150);
        }
    }

    function initializeMainMap() {
        try {
            const mapContainer = document.getElementById('map-container-main');
            if (!mapContainer) return;

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
                    officer:   '{{ addslashes(optional($test->user)->name ?? 'Unknown') }}',
                    ph:        '{{ $test->ph ?? '-' }}',
                    tds:       '{{ $test->tds ?? '-' }}',
                    ec:        '{{ $test->ec ?? '-' }}',
                    suhu_air:  '{{ $test->suhu_air ?? '-' }}',
                    suhu_lingkungan: '{{ $test->suhu_lingkungan ?? '-' }}',
                    kelembapan: '{{ $test->kelembapan ?? '-' }}',
                    tegangan:  '{{ $test->tegangan ?? '-' }}',
                    cr:        '{{ $test->cr_estimated ?? '-' }}'
                }
            );
            @endforeach

            setTimeout(() => mainMap.invalidateSize(true), 200);
        } catch(e) {
            console.error('Map initialization error:', e);
        }
    }

    // Default icon (Emerald)
    const defaultIcon = L.divIcon({
        className: 'leaflet-marker-default',
        html: `<div class="modern-marker-container"><div class="modern-marker-pulse"></div><div class="modern-marker-dot"></div></div>`,
        iconSize: [40, 40], iconAnchor: [20, 20], popupAnchor: [0, -14]
    });

    // Active icon (Amber)
    const activeIcon = L.divIcon({
        className: 'leaflet-marker-active',
        html: `<div class="modern-marker-container active-marker"><div class="modern-marker-pulse"></div><div class="modern-marker-dot"></div></div>`,
        iconSize: [40, 40], iconAnchor: [20, 20], popupAnchor: [0, -14]
    });

    function addMarkerToMap(id, lat, lng, data) {
        if (!mainMap) return;

        const popupHtml = `
            <div style="font-family:'Inter',sans-serif; min-width:200px; font-size:12px; padding:4px;">
                <div style="font-weight:800; font-size:13px; color:#191c1e; margin-bottom:6px; padding-bottom:6px; border-bottom:1px solid #e0e3e5; display:flex;align-items:center;gap:6px;">
                    <span style="background:#006948;color:#fff;padding:2px 6px;border-radius:6px;font-size:10px;font-weight:700;">FIELD TEST</span>
                    Data Sensor Valid
                </div>
                <div style="color:#3d4a42; margin-bottom:4px;"><span style="color:#6d7a72">Waktu:</span> <b>${data.timestamp}</b></div>
                <div style="color:#3d4a42; margin-bottom:8px;"><span style="color:#6d7a72">Petugas:</span> <b>${data.officer}</b></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;">
                    <div style="background:#f2f4f6;padding:4px 6px;border-radius:6px;"><span style="color:#6d7a72;font-size:10px;">pH</span><br><b style="color:#006948">${data.ph}</b></div>
                    <div style="background:#f2f4f6;padding:4px 6px;border-radius:6px;"><span style="color:#6d7a72;font-size:10px;">TDS</span><br><b style="color:#006948">${data.tds} ppm</b></div>
                    <div style="background:#f2f4f6;padding:4px 6px;border-radius:6px;"><span style="color:#6d7a72;font-size:10px;">EC</span><br><b style="color:#006948">${data.ec} mS</b></div>
                    <div style="background:#f2f4f6;padding:4px 6px;border-radius:6px;"><span style="color:#6d7a72;font-size:10px;">Suhu Air</span><br><b style="color:#006948">${data.suhu_air}°C</b></div>
                    <div style="background:#f2f4f6;padding:4px 6px;border-radius:6px;"><span style="color:#6d7a72;font-size:10px;">Udara</span><br><b style="color:#006948">${data.suhu_lingkungan}°C</b></div>
                    <div style="background:#f2f4f6;padding:4px 6px;border-radius:6px;"><span style="color:#6d7a72;font-size:10px;">Kelembapan</span><br><b style="color:#006948">${data.kelembapan}%</b></div>
                    <div style="background:#f1e8ff;padding:4px 6px;border-radius:6px;grid-column:span 2;text-align:center;"><span style="color:#6d7a72;font-size:10px;">🤖 CR Estimated</span><br><b style="color:#7e22ce;font-size:14px;">${data.cr} mg/L</b></div>
                </div>
            </div>
        `;

        const marker = L.marker([lat, lng], { icon: defaultIcon }).addTo(mainMap);
        marker.bindPopup(popupHtml, { maxWidth: 260, closeButton: false });
        markers[id] = marker;
    }

    function jumpToMarker(id, lat, lng) {
        if (!mainMap) return;
        mainMap.flyTo([lat, lng], 18, { animate: true, duration: 1.0 });

        Object.values(markers).forEach(m => { m.setIcon(defaultIcon); m.setZIndexOffset(0); });

        setTimeout(() => {
            const targetMarker = markers[id];
            if (targetMarker) {
                targetMarker.setIcon(activeIcon);
                targetMarker.setZIndexOffset(1000);
                targetMarker.openPopup();
            }
        }, 1100);
    }
</script>

<style>
.modern-marker-container { position:relative; width:40px; height:40px; display:flex; align-items:center; justify-content:center; cursor:pointer; }
.modern-marker-dot { width:14px; height:14px; background:#006948; border:3px solid #fff; border-radius:50%; box-shadow:0 2px 8px rgba(0,105,72,0.5); position:relative; z-index:2; transition:all .3s; }
.modern-marker-pulse { position:absolute; width:100%; height:100%; background:rgba(0,105,72,0.25); border-radius:50%; z-index:1; animation:markerPulse 2s infinite ease-out; }
.modern-marker-container:hover .modern-marker-dot { transform:scale(1.6); box-shadow:0 0 15px rgba(0,105,72,0.7); background:#00a36f; border-color:#fff; }
@keyframes markerPulse { 0%{transform:scale(.5);opacity:1} 100%{transform:scale(2.2);opacity:0} }
.active-marker .modern-marker-dot { background:#f59e0b !important; border-color:#fff !important; box-shadow:0 0 15px rgba(245,158,11,.8) !important; transform:scale(1.6); }
.active-marker .modern-marker-pulse { background:rgba(245,158,11,.4) !important; animation:markerPulseActive 1.2s infinite ease-out !important; }
@keyframes markerPulseActive { 0%{transform:scale(.5);opacity:1} 100%{transform:scale(2.6);opacity:0} }
</style>
@endpush
