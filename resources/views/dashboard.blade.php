@extends('layouts.app')

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* Flash animation for new values */
    .value-update-flash { animation: textFlashLight 1.5s ease-out; }
    @keyframes textFlashLight {
        0% { color: #006948; text-shadow: 0 0 10px rgba(0,105,72,0.3); }
        100% { color: inherit; text-shadow: none; }
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    <!-- Header Title -->
    <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-surface-container-high rounded-xl">
        <div>
            <h2 class="text-3xl font-extrabold tracking-tight text-on-surface font-headline">Chromium Monitoring System</h2>
            <p class="text-on-surface-variant mt-1 text-sm">Real-time IoT-based analysis for chromium prediction and physical water sensing</p>
        </div>
        <div class="flex items-center gap-4">
            <button id="toggleFeedBtn" class="px-5 py-2.5 bg-primary hover:bg-primary-container transition shadow-sm text-white text-sm font-semibold rounded-lg flex items-center gap-2">
                <svg id="feedIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span id="feedText">Pause Feed</span>
            </button>
        </div>
    </div>

    <!-- Section 1: Target Status Bar -->
    <div class="rounded-xl border border-surface-container-high bg-white p-5 flex flex-wrap gap-4 items-center justify-between shadow-sm">
        <div class="flex items-center gap-8">
            <div>
                <span class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest">FastAPI Core</span>
                <div class="flex items-center gap-2 mt-1">
                    <div id="aiHealthDot" class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></div>
                    <span id="aiHealthText" class="text-sm font-bold text-on-surface">Connected</span>
                </div>
            </div>
            <div class="w-px h-8 bg-surface-container-high"></div>
            <div>
                <span class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest">Stream State</span>
                <div class="flex items-center gap-2 mt-1">
                    <div id="wsHealthDot" class="w-2.5 h-2.5 rounded-full bg-yellow-400"></div>
                    <span id="wsHealthText" class="text-sm font-bold text-on-surface">Connecting...</span>
                </div>
            </div>
            <div class="w-px h-8 bg-surface-container-high"></div>
            <div>
                <span class="text-[10px] text-on-surface-variant uppercase font-bold tracking-widest">Last Packet</span>
                <div class="mt-1">
                    <span id="lastUpdateText" class="text-sm font-bold text-on-surface font-mono">--:--:--</span>
                </div>
            </div>
        </div>
        
        <div class="px-5 py-2 rounded-lg border flex items-center gap-3 transition-colors shadow-sm" id="masterStatusBadge" style="background: rgba(0, 105, 72, 0.05); border-color: rgba(0, 105, 72, 0.2);">
            <div class="h-2 w-2 rounded-full bg-primary" id="masterStatusDot"></div>
            <span class="font-bold tracking-wide text-primary text-sm" id="masterStatusLabel">QUALITY: NORMAL</span>
        </div>
    </div>

    <!-- Section 1.5: 4 Metrik Kartu Atas -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        <!-- Total Pembacaan -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-surface-container-highest hover:border-primary/30 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-primary/10 rounded-lg text-primary group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined" data-icon="data_usage">data_usage</span>
                </div>
                <span class="text-xs font-bold text-primary px-2 py-1 bg-primary/10 rounded-full">+Data</span>
            </div>
            <p class="text-on-surface-variant text-xs font-label uppercase tracking-wider mb-1">Total Hari Ini</p>
            <p class="text-3xl font-bold font-headline text-on-surface">{{ number_format($dailyStats['total']) }} <span class="text-sm font-medium text-on-surface-variant">Data</span></p>
        </div>

        <!-- Warning Events -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-surface-container-highest hover:border-yellow-400/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-yellow-100 rounded-lg text-yellow-600 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined" data-icon="warning">warning</span>
                </div>
                <span class="text-xs font-bold text-yellow-700 px-2 py-1 bg-yellow-100 rounded-full">Active</span>
            </div>
            <p class="text-on-surface-variant text-xs font-label uppercase tracking-wider mb-1">Peringatan</p>
            <p class="text-3xl font-bold font-headline text-on-surface">{{ number_format($dailyStats['warning']) }} <span class="text-sm font-medium text-on-surface-variant">Alerts</span></p>
        </div>

        <!-- Danger Events -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-surface-container-highest hover:border-error/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-error-container rounded-lg text-error group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined" data-icon="dangerous">dangerous</span>
                </div>
                <span class="text-xs font-bold text-error px-2 py-1 bg-error-container rounded-full">Critical</span>
            </div>
            <p class="text-on-surface-variant text-xs font-label uppercase tracking-wider mb-1">Bahaya</p>
            <p class="text-3xl font-bold font-headline text-on-surface">{{ number_format($dailyStats['danger']) }} <span class="text-sm font-medium text-on-surface-variant">Events</span></p>
        </div>

        <!-- Avg Cr -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-surface-container-highest hover:border-primary/30 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-primary/10 rounded-lg text-primary group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined" data-icon="analytics">analytics</span>
                </div>
                <span class="text-xs font-bold text-primary px-2 py-1 bg-primary/10 rounded-full">Stable</span>
            </div>
            <p class="text-on-surface-variant text-xs font-label uppercase tracking-wider mb-1">Rata-rata Cr</p>
            <p class="text-3xl font-bold font-headline text-on-surface">{{ $dailyStats['avg_cr'] }} <span class="text-sm font-medium text-on-surface-variant">mg/L</span></p>
        </div>
    </div>

    <!-- Section 2: Sensor Hub (Real-time Gauges) -->
    <div>
        <h3 class="text-xl font-bold font-headline text-on-surface mb-4">Real-time Sensor Hub</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Main AI Card (Chromium) Insight Card -->
            <div class="bg-white rounded-xl p-5 border shadow-sm border-l-4 border-l-primary relative overflow-hidden group col-span-1 md:col-span-2 lg:col-span-1 flex flex-col justify-between" id="cardContainer-cr">
                <!-- Water ripple subtle background -->
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_right,_#006948_0%,_transparent_60%)] opacity-[0.03] z-0 pointer-events-none"></div>
                
                <div class="relative z-10">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="inline-block px-2 py-1 bg-primary/10 rounded text-primary text-[10px] font-bold uppercase tracking-wider mb-2">HERA AI Insight</div>
                            <h3 class="text-on-surface-variant font-bold text-sm">Hexavalent Chromium (Cr)</h3>
                        </div>
                        <div id="badge-cr" class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-primary/10 text-primary">NORMAL</div>
                    </div>
                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-4xl font-extrabold tracking-tight text-on-surface transition-colors duration-300" id="val-cr">--</span>
                        <span class="text-sm text-on-surface-variant font-medium">mg/L</span>
                    </div>
                </div>
                
                <div class="relative z-10 mt-6">
                    <div class="flex justify-between text-[10px] text-on-surface-variant font-bold mb-1" id="minmax-cr">
                        <span>MIN: --</span><span>MAX: --</span>
                    </div>
                    <!-- Thick Progress Bar for AI -->
                    <div class="h-2 w-full bg-surface-container-high rounded-full overflow-hidden">
                        <div id="bar-cr" class="h-full bg-primary rounded-full transition-all duration-700 ease-out" style="width: 0%"></div>
                    </div>
                </div>
            </div>

            <!-- 7 Physical Sensors -->
            @php
                $sensors = [
                    ['id' => 'ec', 'name' => 'Electrical Conductivity', 'unit' => 'µS/cm', 'icon' => 'bolt'],
                    ['id' => 'tds', 'name' => 'Total Dissolved Solids', 'unit' => 'mg/L', 'icon' => 'water_drop'],
                    ['id' => 'ph', 'name' => 'Acidity (pH Level)', 'unit' => '', 'icon' => 'science'],
                    ['id' => 'suhu_air', 'name' => 'Water Temp', 'unit' => '°C', 'icon' => 'device_thermostat'],
                    ['id' => 'suhu_lingkungan', 'name' => 'Ambient Temp', 'unit' => '°C', 'icon' => 'thermostat'],
                    ['id' => 'kelembapan', 'name' => 'Humidity', 'unit' => '%', 'icon' => 'cloud'],
                    ['id' => 'tegangan', 'name' => 'Battery Level', 'unit' => 'V', 'icon' => 'battery_charging_full']
                ];
            @endphp

            @foreach($sensors as $s)
            <div class="bg-white rounded-xl p-5 border border-surface-container-high shadow-sm flex flex-col justify-between" id="cardContainer-{{$s['id']}}">
                <div class="flex justify-between items-start">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-outline text-lg" data-icon="{{$s['icon']}}">{{$s['icon']}}</span>
                        <h3 class="text-on-surface-variant font-bold text-xs">{{ $s['name'] }}</h3>
                    </div>
                    <div id="badge-{{$s['id']}}" class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-surface-container text-on-surface-variant">WAIT</div>
                </div>
                <div class="mt-4 flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold text-on-surface transition-colors duration-300" id="val-{{$s['id']}}">--</span>
                    <span class="text-xs text-on-surface-variant font-medium w-8">{{ $s['unit'] }}</span>
                </div>
                
                <div class="mt-4">
                    <div class="flex justify-between text-[9px] text-outline font-bold mb-1" id="minmax-{{$s['id']}}">
                        <span>MIN: --</span><span>MAX: --</span>
                    </div>
                    <!-- Thin Progress bar -->
                    <div class="h-1.5 w-full bg-surface-container-highest rounded-full overflow-hidden">
                        <div id="bar-{{$s['id']}}" class="h-full bg-surface-variant rounded-full transition-all duration-700 ease-out" style="width: 0%"></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Section 3: Charts Row -->
    <div class="space-y-6">
        <!-- Cr Chart Full Width -->
        <div class="bg-white rounded-xl p-8 shadow-sm border border-surface-container-high">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl font-bold font-headline text-on-surface mb-1">Chromium Prediction Trend</h3>
                    <p class="text-sm text-on-surface-variant">Real-time mapping of hexavalent chromium timeline</p>
                </div>
                <div class="hidden sm:flex gap-2">
                    <span class="flex items-center gap-1.5 text-xs font-bold text-on-surface-variant bg-surface-container-low px-3 py-1.5 rounded-lg"><div class="w-2.5 h-2.5 bg-yellow-400 rounded-full"></div> Warning (&gt;{{ $thresholds['cr_normal_max'] }} mg/L)</span>
                    <span class="flex items-center gap-1.5 text-xs font-bold text-on-surface-variant bg-surface-container-low px-3 py-1.5 rounded-lg"><div class="w-2.5 h-2.5 bg-error rounded-full"></div> Danger (&gt;{{ $thresholds['cr_warning_max'] }} mg/L)</span>
                </div>
            </div>
            <div id="chartCr" class="w-full h-[300px]"></div>
        </div>
        
        <!-- Dual Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl p-8 shadow-sm border border-surface-container-high">
                <h3 class="text-lg font-bold font-headline text-on-surface mb-4">EC & TDS Correlation</h3>
                <div id="chartEcTds" class="w-full h-64"></div>
            </div>
            <div class="bg-white rounded-xl p-8 shadow-sm border border-surface-container-high">
                <h3 class="text-lg font-bold font-headline text-on-surface mb-4">pH & Water Temperature</h3>
                <div id="chartPhSuhu" class="w-full h-64"></div>
            </div>
        </div>
    </div>

    <!-- Section 4 & 5: Logs & Data Feed -->
    <div class="grid grid-cols-1 xl:grid-cols-[2fr_1fr] gap-6">
        
        <!-- Live Table -->
        <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-surface-container-high flex flex-col h-[450px]">
            <div class="px-6 py-5 flex justify-between items-center border-b border-surface-container-highest bg-surface-container-lowest">
                <h3 class="text-lg font-bold font-headline text-on-surface">Live Data Feed</h3>
                <span class="text-xs font-bold text-on-surface-variant bg-surface-container px-2 py-1 rounded">Last 10 records</span>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="text-[10px] uppercase font-black tracking-widest text-on-surface-variant bg-surface-container-low border-b border-surface-container-high">
                        <tr>
                            <th class="px-6 py-4">Time</th>
                            <th class="px-6 py-4 text-right">Cr (mg/L)</th>
                            <th class="px-6 py-4 text-right">EC</th>
                            <th class="px-6 py-4 text-right">TDS</th>
                            <th class="px-6 py-4 text-right">pH</th>
                            <th class="px-6 py-4 text-right">Temp</th>
                            <th class="px-6 py-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="dataTableBody" class="divide-y divide-surface-container-highest">
                        <!-- Dynamic Rows Here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Alert Logs -->
        <div class="bg-white rounded-xl shadow-sm border border-surface-container-high p-6 flex flex-col h-[450px]">
            <h3 class="text-lg font-bold font-headline text-on-surface border-b border-surface-container-highest pb-4 mb-4 flex items-center justify-between">
                <span>Alert Log</span>
                <span class="material-symbols-outlined text-outline">history</span>
            </h3>
            <div class="overflow-y-auto flex-1 pr-2 no-scrollbar">
                <div id="alertContainer" class="space-y-3">
                    <!-- Dynamic Alerts Here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Section 6: Map Section -->
    <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-surface-container-high">
        <div class="px-6 py-5 border-b border-surface-container-highest flex justify-between items-center bg-surface-container-lowest">
            <div>
                <h3 class="text-lg font-bold font-headline text-on-surface">Lokasi Perangkat IoT</h3>
                <p class="text-xs text-on-surface-variant mt-0.5 font-medium">Posisi geospasial Real-time Node Sensor</p>
            </div>
            <span class="px-3 py-1 bg-primary/10 text-primary text-xs font-bold rounded-full">Node Online</span>
        </div>
        <!-- Light Mode Map Holder Constraint -->
        <div id="sensorMap" style="height: 400px; width: 100%; background: #f8fafc; z-index:1;"></div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // Init state vars from backend
    let sensorCache = @json($initialData); // chronologically sorted (last item is newest)
    let isPaused = false;
    let chartCr, chartEcTds, chartPhSuhu;

    const limitChartPoints = 30;
    const limitTableRows = 10;
    const limitAlerts = 15;
    
    let dataCr = [], dataEc = [], dataTds = [], dataPh = [], dataSuhu = [];
    
    // Clinical Theme Colors Matching Stitch Specification
    const colors = {
        normal: { bg: 'rgba(0,105,72,0.05)', border: 'rgba(0,105,72,0.2)', text: 'text-primary', tailwindBg: 'bg-primary/10', bar: '#006948', label: 'NORMAL' },
        warning: { bg: '#fef9c3', border: '#fef08a', text: 'text-yellow-700', tailwindBg: 'bg-yellow-100', bar: '#eab308', label: 'WARNING' },
        danger: { bg: '#fee2e2', border: '#fecaca', text: 'text-error', tailwindBg: 'bg-error-container', bar: '#ba1a1a', label: 'DANGER' }
    };

    function checkStatus(key, val) {
        if (key === 'ec') return val > 800 ? 'danger' : (val >= 400 ? 'warning' : 'normal');
        if (key === 'tds') return val > 900 ? 'danger' : (val >= 500 ? 'warning' : 'normal');
        if (key === 'ph') return (val < 5.5 || val > 9.0) ? 'danger' : ((val < 6.5 || val > 8.5) ? 'warning' : 'normal');
        if (key === 'suhu_air') return val > 35 ? 'danger' : (val >= 30 ? 'warning' : 'normal');
        return 'normal'; 
    }

    document.addEventListener("DOMContentLoaded", () => {
        initCharts();
        populateInitialData();
        setupWebSocket();
        pollHealth();
        setInterval(pollHealth, 10000); 
        
        document.getElementById('toggleFeedBtn').addEventListener('click', function() {
            isPaused = !isPaused;
            if(isPaused) {
                this.classList.replace('bg-primary', 'bg-surface-container-high');
                this.classList.replace('hover:bg-primary-container', 'hover:bg-surface-variant');
                this.classList.add('text-on-surface');
                document.getElementById('feedText').innerText = 'Resume Feed';
                document.getElementById('feedIcon').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
            } else {
                this.classList.replace('bg-surface-container-high', 'bg-primary');
                this.classList.replace('hover:bg-surface-variant', 'hover:bg-primary-container');
                this.classList.remove('text-on-surface');
                document.getElementById('feedText').innerText = 'Pause Feed';
                document.getElementById('feedIcon').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                
                if(sensorCache.length > 0) {
                    processIncomingData(sensorCache[sensorCache.length - 1], true);
                    renderAllCharts();
                    renderTable();
                }
            }
        });
    });

    function getLightModeOptions() {
        return {
            chart: { background: 'transparent', toolbar: { show: false }, animations: { enabled: true, easing: 'linear', dynamicAnimation: { speed: 1000 } } },
            theme: { mode: 'light' },
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
            dataLabels: { enabled: false },
            tooltip: { theme: 'light' },
            stroke: { curve: 'smooth', width: 2 }
        };
    }

    function initCharts() {
        // Cr Chart
        let optionsCr = {
            ...getLightModeOptions(),
            series: [{ name: 'Cr Estimated', data: [] }],
            chart: { ...getLightModeOptions().chart, type: 'area', height: 280 },
            colors: ['#006948'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.2, opacityTo: 0.01, stops: [0, 100] } },
            xaxis: { type: 'datetime', labels: { style: { colors: '#64748b' } } },
            yaxis: { title: { text: 'mg/L' }, labels: { style: { colors: '#64748b' } }, min: 0 },
            annotations: {
                y: [
                    { y: {{ $thresholds['cr_normal_max'] }},  borderColor: '#eab308', strokeDashArray: 3,
                      label: { borderColor: '#eab308', style: { color:'#fff', background:'#eab308', fontWeight:700, fontSize:'10px' }, text: 'Warning ({{ $thresholds["cr_normal_max"] }} mg/L)' } },
                    { y: {{ $thresholds['cr_warning_max'] }}, borderColor: '#ba1a1a', strokeDashArray: 2,
                      label: { borderColor: '#ba1a1a', style: { color:'#fff', background:'#ba1a1a', fontWeight:700, fontSize:'10px' }, text: 'Danger ({{ $thresholds["cr_warning_max"] }} mg/L)'  } }
                ]
            }
        };
        chartCr = new ApexCharts(document.querySelector("#chartCr"), optionsCr);
        chartCr.render();

        let optionsEcTds = {
            ...getLightModeOptions(),
            series: [{ name: 'EC (µS/cm)', data: [] }, { name: 'TDS (mg/L)', data: [] }],
            chart: { ...getLightModeOptions().chart, type: 'line', height: 250 },
            colors: ['#3B82F6', '#F97316'],
            stroke: { curve: 'smooth', width: [2.5, 2.5] },
            xaxis: { type: 'datetime', labels: { style: { colors: '#64748b' } } },
            yaxis: [
                { title: { text: 'EC (µS/cm)', style: { color: '#3B82F6', fontWeight: 700 } }, labels: { style: { colors: '#3B82F6' } } },
                { opposite: true, title: { text: 'TDS (mg/L)', style: { color: '#F97316', fontWeight: 700 } }, labels: { style: { colors: '#F97316' } } }
            ],
            legend: { show: true, position: 'top', horizontalAlign: 'right' }
        };
        chartEcTds = new ApexCharts(document.querySelector("#chartEcTds"), optionsEcTds);
        chartEcTds.render();

        let optionsPhSuhu = {
            ...getLightModeOptions(),
            series: [{ name: 'pH', data: [] }, { name: 'Temp (°C)', data: [] }],
            chart: { ...getLightModeOptions().chart, type: 'line', height: 250 },
            colors: ['#ba1a1a', '#006948'],
            xaxis: { type: 'datetime', labels: { style: { colors: '#64748b' } } },
            yaxis: [
                { title: { text: 'pH', style: { color: '#ba1a1a'} }, labels: { style: { colors: '#ba1a1a' } } },
                { opposite: true, title: { text: 'Temp', style: { color: '#006948'} }, labels: { style: { colors: '#006948' } } }
            ]
        };
        chartPhSuhu = new ApexCharts(document.querySelector("#chartPhSuhu"), optionsPhSuhu);
        chartPhSuhu.render();
    }

    function populateInitialData() {
        if(sensorCache.length === 0) return;
        sensorCache.forEach(row => appendChartData(row));
        renderAllCharts();
        if(!isPaused) {
            processIncomingData(sensorCache[sensorCache.length - 1], true);
            renderTable();
            buildInitialAlerts();
        }
    }

    function appendChartData(d) {
        let ts = new Date(d.created_at).getTime();
        dataCr.push([ts, d.cr_estimated]);
        dataEc.push([ts, d.ec]);
        dataTds.push([ts, d.tds]);
        dataPh.push([ts, d.ph]);
        dataSuhu.push([ts, d.suhu_air]);
        
        if(dataCr.length > limitChartPoints) {
            dataCr.shift(); dataEc.shift(); dataTds.shift(); dataPh.shift(); dataSuhu.shift();
        }
    }

    function renderAllCharts() {
        chartCr.updateSeries([{ data: dataCr }]);
        chartEcTds.updateSeries([{ data: dataEc }, { data: dataTds }]);
        chartPhSuhu.updateSeries([{ data: dataPh }, { data: dataSuhu }]);
    }

    function buildInitialAlerts() {
        fetch('/api/sensor/alerts')
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('alertContainer');
                container.innerHTML = '';
                data.forEach(item => prependAlertLog(item, false));
            });
    }

    function setupWebSocket() {
        if(!window.Echo) return;

        window.Echo.connector.pusher.connection.bind('state_change', function(states) {
            const wsDot = document.getElementById('wsHealthDot');
            const wsText = document.getElementById('wsHealthText');
            if (states.current === 'connected') {
                wsDot.className = 'w-2.5 h-2.5 rounded-full bg-green-500';
                wsText.innerText = 'Connected';
                wsText.classList.remove('text-error');
            } else {
                wsDot.className = 'w-2.5 h-2.5 rounded-full bg-error';
                wsText.innerText = 'Disconnected';
                wsText.classList.add('text-error');
            }
        });

        window.Echo.channel('sensor-monitoring')
            .listen('.SensorDataUpdated', (e) => {
                const record = e.sensorData || e;
                sensorCache.push(record);
                if(sensorCache.length > 50) sensorCache.shift();

                appendChartData(record);
                
                if (!isPaused) {
                    processIncomingData(record, false);
                    renderAllCharts();
                    renderTable();
                    if(record.status === 'warning' || record.status === 'danger') {
                        prependAlertLog(record, true);
                    }
                    if (record.status === 'danger') {
                        triggerDangerNotification(record);
                    }
                }
            });
    }

    function processIncomingData(data, isInitial = false) {
        const hmString = new Date(data.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        document.getElementById('lastUpdateText').innerText = hmString;

        updateCard('cr', data.cr_estimated, data.status, isInitial);
        
        const keys = ['ec', 'tds', 'ph', 'suhu_air', 'suhu_lingkungan', 'kelembapan', 'tegangan'];
        keys.forEach(k => {
            const s = checkStatus(k, data[k]);
            updateCard(k, data[k], s, isInitial);
        });

        const badgeInfo = colors[data.status] || colors.normal;
        document.getElementById('masterStatusBadge').style.background = badgeInfo.bg;
        document.getElementById('masterStatusBadge').style.borderColor = badgeInfo.border;
        document.getElementById('masterStatusDot').className = `h-2 w-2 rounded-full`;
        document.getElementById('masterStatusDot').style.backgroundColor = badgeInfo.bar;
        const lbl = document.getElementById('masterStatusLabel');
        lbl.className = `font-bold tracking-wide text-sm ${badgeInfo.text}`;
        lbl.innerText = `QUALITY: ${data.status.toUpperCase()}`;
    }

    function updateCard(id, val, status, isInitial) {
        const valEl = document.getElementById(`val-${id}`);
        if(valEl) {
            valEl.innerText = id==='cr' ? val.toFixed(3) : val.toFixed(1);
            if(!isInitial) {
                valEl.classList.remove('value-update-flash');
                void valEl.offsetWidth; 
                valEl.classList.add('value-update-flash');
            }
        }

        const badgeEl = document.getElementById(`badge-${id}`);
        const barEl = document.getElementById(`bar-${id}`);
        const col = colors[status] || colors.normal;
        
        if(badgeEl) {
            badgeEl.className = `px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider ${col.tailwindBg} ${col.text}`;
            badgeEl.innerText = status;
        }

        if(id === 'cr') {
            const container = document.getElementById(`cardContainer-cr`);
            container.className = `bg-white rounded-xl p-5 border shadow-sm border-l-4 relative overflow-hidden group col-span-1 md:col-span-2 lg:col-span-1 flex flex-col justify-between`;
            container.style.borderLeftColor = col.bar;
            
            if(barEl) {
                barEl.style.backgroundColor = col.bar;
                let pct = Math.min((val / ({{ $thresholds['cr_warning_max'] }} * 1.5)) * 100, 100);
                barEl.style.width = pct + '%';
            }
        } else {
            // physical sensor bars
            if(barEl) {
                barEl.style.backgroundColor = col.bar;
                // Dummy scaling for gauge visual effect
                let limit = (id==='ec')?1000:(id==='tds')?1000:(id==='ph')?14:(id.includes('suhu'))?50:100;
                let pct = Math.min((val / limit) * 100, 100);
                barEl.style.width = pct + '%';
            }
        }

        const minmaxEl = document.getElementById(`minmax-${id}`);
        if(minmaxEl && sensorCache.length > 0) {
            let column = id === 'cr' ? 'cr_estimated' : id;
            let arr = sensorCache.map(r => r[column]);
            let min = Math.min(...arr).toFixed(2);
            let max = Math.max(...arr).toFixed(2);
            minmaxEl.innerHTML = `<span>MIN: ${min}</span><span>MAX: ${max}</span>`;
        }
    }

    function prependAlertLog(data, animate) {
        const timeStr = new Date(data.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        const container = document.getElementById('alertContainer');
        const col = colors[data.status];
        
        const markup = `
            <div class="px-4 py-3 rounded-lg border bg-surface-container-lowest flex flex-col gap-1 border-l-4" style="border-left-color: ${col.bar};">
                <div class="flex justify-between items-start">
                    <span class="text-[10px] font-bold uppercase ${col.text}">${data.status} Chromium Log</span>
                    <span class="text-[10px] text-on-surface-variant font-mono">${timeStr}</span>
                </div>
                <p class="text-sm font-medium text-on-surface mt-1">Cr Limit Reached: <span class="font-bold ${col.text}">${data.cr_estimated.toFixed(4)} mg/L</span></p>
            </div>
        `;
        container.insertAdjacentHTML('afterbegin', markup);
        if(container.children.length > limitAlerts) {
            container.removeChild(container.lastElementChild);
        }
    }

    function renderTable() {
        const tb = document.getElementById('dataTableBody');
        const rows = sensorCache.slice().reverse().slice(0, limitTableRows);
        tb.innerHTML = '';
        
        rows.forEach((row, i) => {
            const timeStr = new Date(row.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            let colText = colors[row.status].text;
            let colBadge = colors[row.status].tailwindBg + " " + colors[row.status].text;
            
            tb.innerHTML += `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 font-mono text-xs text-on-surface-variant">${timeStr}</td>
                    <td class="px-6 py-4 text-right font-bold ${colText}">${row.cr_estimated.toFixed(3)}</td>
                    <td class="px-6 py-4 text-right text-on-surface text-sm font-medium">${row.ec.toFixed(1)}</td>
                    <td class="px-6 py-4 text-right text-on-surface text-sm font-medium">${row.tds.toFixed(1)}</td>
                    <td class="px-6 py-4 text-right text-on-surface text-sm font-medium">${row.ph.toFixed(1)}</td>
                    <td class="px-6 py-4 text-right text-on-surface text-sm font-medium">${row.suhu_air.toFixed(1)}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 rounded text-[9px] font-bold uppercase tracking-wider ${colBadge}">${row.status}</span>
                    </td>
                </tr>
            `;
        });
    }

    function pollHealth() {
        fetch('/api/health-check')
            .then(r => r.json())
            .then(data => {
                const aiDot = document.getElementById('aiHealthDot');
                const aiText = document.getElementById('aiHealthText');
                if(data.status === 'ok') {
                    aiDot.className = 'w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse';
                    aiText.innerText = 'Connected';
                    aiText.classList.remove('text-error');
                } else {
                    aiDot.className = 'w-2.5 h-2.5 rounded-full bg-error';
                    aiText.innerText = 'Offline';
                    aiText.classList.add('text-error');
                }
            })
            .catch(err => {
                document.getElementById('aiHealthDot').className = 'w-2.5 h-2.5 rounded-full bg-error';
                document.getElementById('aiHealthText').innerText = 'Offline';
            });
    }

    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    let lastDangerNotifTime = 0;
    function triggerDangerNotification(record) {
        const now = Date.now();
        if (now - lastDangerNotifTime < 30000) return;
        lastDangerNotifTime = now;
        const crVal = parseFloat(record.cr_estimated).toFixed(5);
        
        if ('Notification' in window && Notification.permission === 'granted') {
            const notif = new Notification('⚠️ HERA — Critical Event!', {
                body: `Chromium Level Detected at ${crVal} mg/L — Danger Threshold Broken (>{{ $thresholds['cr_warning_max'] }} mg/L)!`,
                icon: '/favicon.ico',
                tag: 'hera-danger',
                requireInteraction: true
            });
            notif.onclick = () => { window.focus(); notif.close(); };
        }
    }
</script>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function initSensorMap() {
    const mapEl = document.getElementById('sensorMap');
    if (!mapEl) return;

    const LAT = -6.967585;
    const LNG = 107.6590634;
    const LOCATION_NAME = 'Main Node HQ, Bandung Raya';

    const map = L.map('sensorMap', {
        center: [LAT, LNG],
        zoom: 15,
        zoomControl: true,
        attributionControl: false,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        opacity: 0.8
    }).addTo(map);

    const lightDangerIcon = L.divIcon({
        className: '',
        html: `
            <div style="position: relative; width: 40px; height: 40px;">
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 40px; height: 40px; border-radius: 50%; background: rgba(0, 105, 72, 0.2); animation: mapPulseLight 2s ease-out infinite;"></div>
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 14px; height: 14px; background: #006948; border: 3px solid white; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>
            </div>
        `,
        iconSize: [40, 40],
        iconAnchor: [20, 20],
        popupAnchor: [0, -20],
    });

    const marker = L.marker([LAT, LNG], { icon: lightDangerIcon }).addTo(map);
    marker.bindPopup(`
        <div style="font-family: 'Inter', sans-serif; min-width: 160px; text-align:center;">
            <div style="font-weight: 800; font-size: 13px; color: #191c1e; margin-bottom: 4px;">
                Main Node HQ
            </div>
            <div style="font-size: 11px; color: #6d7a72; margin-bottom: 8px;">
                ${LOCATION_NAME}
            </div>
            <div style="padding: 4px 8px; background: #e6f4ea; border-radius: 4px; font-size: 10px; font-weight: 700; color: #006948; display: inline-block;">
                TRANSMITTING
            </div>
        </div>
    `, { maxWidth: 240, className: 'clinical-popup' });

    marker.openPopup();

    if (!document.getElementById('leaflet-pulse-light-style')) {
        const style = document.createElement('style');
        style.id = 'leaflet-pulse-light-style';
        style.textContent = `
            @keyframes mapPulseLight {
                0%   { transform: translate(-50%, -50%) scale(1);   opacity: 1; }
                100% { transform: translate(-50%, -50%) scale(2.5); opacity: 0; }
            }
            .clinical-popup .leaflet-popup-content-wrapper {
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                border: 1px solid #e0e3e5;
            }
            .clinical-popup .leaflet-popup-tip {
                background: white;
                border-bottom: 1px solid #e0e3e5;
                border-right: 1px solid #e0e3e5;
            }
        `;
        document.head.appendChild(style);
    }
})();
</script>
@endpush
