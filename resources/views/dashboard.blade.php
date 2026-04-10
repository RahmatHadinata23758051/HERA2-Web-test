@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Title -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold tracking-tight text-white">Live Operations</h2>
            <p class="text-gray-400 mt-1">Real-time water quality prediction & physical sensing</p>
        </div>
        <div class="flex items-center gap-4">
            <button id="toggleFeedBtn" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 transition shadow-lg shadow-blue-500/20 text-white text-sm font-semibold rounded-lg flex items-center gap-2">
                <svg id="feedIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span id="feedText">Pause Feed</span>
            </button>
        </div>
    </div>

    <!-- Section 1: Target Status Bar -->
    <div class="glass-card rounded-xl p-4 flex flex-wrap gap-4 items-center justify-between">
        <div class="flex items-center gap-6">
            <div>
                <span class="text-xs text-gray-400 uppercase font-semibold tracking-wider">FastAPI Core</span>
                <div class="flex items-center gap-2 mt-1">
                    <div id="aiHealthDot" class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></div>
                    <span id="aiHealthText" class="text-sm font-medium text-white">Connected</span>
                </div>
            </div>
            <div class="w-px h-8 bg-gray-800"></div>
            <div>
                <span class="text-xs text-gray-400 uppercase font-semibold tracking-wider">Stream State</span>
                <div class="flex items-center gap-2 mt-1">
                    <div id="wsHealthDot" class="w-2.5 h-2.5 rounded-full bg-yellow-400"></div>
                    <span id="wsHealthText" class="text-sm font-medium text-white">Connecting...</span>
                </div>
            </div>
            <div class="w-px h-8 bg-gray-800"></div>
            <div>
                <span class="text-xs text-gray-400 uppercase font-semibold tracking-wider">Last Packet Received</span>
                <div class="mt-1">
                    <span id="lastUpdateText" class="text-sm font-medium text-white font-mono">--:--:--</span>
                </div>
            </div>
        </div>
        
        <div class="px-5 py-2.5 rounded-lg border flex items-center gap-3 transition-colors" id="masterStatusBadge" style="background: rgba(34, 197, 94, 0.1); border-color: rgba(34, 197, 94, 0.3);">
            <div class="h-2 w-2 rounded-full bg-green-500" id="masterStatusDot"></div>
            <span class="font-bold tracking-wide text-green-400" id="masterStatusLabel">QUALITY: NORMAL</span>
        </div>
    </div>

    {{-- Section 1.5: Ringkasan Harian (Card 9.1) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Pembacaan --}}
        <div class="glass-card rounded-xl p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-500/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Total Hari Ini</p>
                <p class="text-2xl font-bold text-white mt-0.5">{{ number_format($dailyStats['total']) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">pembacaan sensor</p>
            </div>
        </div>

        {{-- Warning Events --}}
        <div class="glass-card rounded-xl p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-yellow-500/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Peringatan</p>
                <p class="text-2xl font-bold text-yellow-400 mt-0.5">{{ number_format($dailyStats['warning']) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">event warning hari ini</p>
            </div>
        </div>

        {{-- Danger Events --}}
        <div class="glass-card rounded-xl p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-red-500/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Bahaya</p>
                <p class="text-2xl font-bold text-red-400 mt-0.5">{{ number_format($dailyStats['danger']) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">event danger hari ini</p>
            </div>
        </div>

        {{-- Avg Cr --}}
        <div class="glass-card rounded-xl p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-purple-500/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Rata-rata Cr</p>
                <p class="text-2xl font-bold text-purple-400 mt-0.5">{{ $dailyStats['avg_cr'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">µg/L hari ini</p>
            </div>
        </div>
    </div>

    <!-- Section 2: Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Main AI Card (Chromium) -->
        <div class="glass-card rounded-xl p-5 border-l-4 border-l-blue-500 relative overflow-hidden group col-span-1 md:col-span-2 lg:col-span-1" id="cardContainer-cr">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-purple-600/5 z-0"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="inline-block px-2 py-1 bg-blue-500/20 rounded text-blue-400 text-[10px] font-bold uppercase tracking-wider mb-2 ring-1 ring-blue-500/30">AI Estimated</div>
                        <h3 class="text-gray-400 font-medium text-sm">Hexavalent Chromium (Cr)</h3>
                    </div>
                    <div id="badge-cr" class="px-2 py-1 rounded text-xs font-semibold bg-green-500/20 text-green-400 border border-green-500/20">Normal</div>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-4xl font-bold tracking-tight text-white transition-all duration-300" id="val-cr">--</span>
                    <span class="text-sm text-gray-400 font-medium">µg/L</span>
                </div>
                <div class="mt-4 text-xs text-gray-500 flex justify-between">
                    <span>Model: RF Soft Sensor</span>
                    <span id="minmax-cr">Min: -- / Max: --</span>
                </div>
            </div>
        </div>

        <!-- Sensor Card Template Generator -->
        @php
            $sensors = [
                ['id' => 'ec', 'name' => 'Electrical Conductivity', 'unit' => 'µS/cm'],
                ['id' => 'tds', 'name' => 'Total Dissolved Solids', 'unit' => 'mg/L'],
                ['id' => 'ph', 'name' => 'Acidity (pH)', 'unit' => ''],
                ['id' => 'suhu_air', 'name' => 'Water Temp', 'unit' => '°C'],
                ['id' => 'suhu_lingkungan', 'name' => 'Ambient Temp', 'unit' => '°C'],
                ['id' => 'kelembapan', 'name' => 'Humidity', 'unit' => '%'],
                ['id' => 'tegangan', 'name' => 'Battery Level', 'unit' => 'V']
            ];
        @endphp

        @foreach($sensors as $s)
        <div class="glass-card rounded-xl p-5 border border-gray-800/60 shadow-sm flex flex-col justify-between" id="cardContainer-{{$s['id']}}">
            <div class="flex justify-between items-start">
                <h3 class="text-gray-400 font-medium text-sm">{{ $s['name'] }}</h3>
                <div id="badge-{{$s['id']}}" class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-gray-800/50 text-gray-400">WAIT</div>
            </div>
            <div class="mt-4 flex items-baseline gap-1.5">
                <span class="text-2xl font-bold text-white transition-all duration-300" id="val-{{$s['id']}}">--</span>
                <span class="text-xs text-gray-500 font-medium w-8">{{ $s['unit'] }}</span>
            </div>
            <div class="mt-4 text-[10px] text-gray-600 font-medium tracking-wide" id="minmax-{{$s['id']}}">
                MIN: -- | MAX: --
            </div>
        </div>
        @endforeach
    </div>

    <!-- Section 3: Charts -->
    <div class="space-y-6">
        <!-- Cr Chart Full Width -->
        <div class="glass-card rounded-xl p-5">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-white">Chromium Prediction Trend</h3>
                <div class="text-xs text-gray-400 flex items-center gap-3">
                    <span class="flex items-center gap-1"><div class="w-2 h-2 bg-yellow-500 rounded-full"></div> Warning (>50)</span>
                    <span class="flex items-center gap-1"><div class="w-2 h-2 bg-red-500 rounded-full"></div> Danger (>100)</span>
                </div>
            </div>
            <div id="chartCr" class="w-full h-64"></div>
        </div>
        
        <!-- Dual Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="glass-card rounded-xl p-5">
                <h3 class="font-semibold text-white mb-4">EC & TDS Correlation</h3>
                <div id="chartEcTds" class="w-full h-64"></div>
            </div>
            <div class="glass-card rounded-xl p-5">
                <h3 class="font-semibold text-white mb-4">pH & Water Temperature</h3>
                <div id="chartPhSuhu" class="w-full h-64"></div>
            </div>
        </div>
    </div>

    <!-- Section 4 & 5: Logs & Data -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Alert Logs -->
        <div class="glass-card rounded-xl p-5 lg:col-span-1 flex flex-col h-96">
            <h3 class="font-semibold text-white mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Critical Event Log
            </h3>
            <div class="overflow-y-auto flex-1 pr-2">
                <div id="alertContainer" class="space-y-3">
                    <!-- Dynamic Alerts Here -->
                </div>
            </div>
        </div>

        <!-- Live Table -->
        <div class="glass-card rounded-xl p-5 lg:col-span-2 flex flex-col h-96">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-white">Live Data Feed</h3>
                <span class="text-xs text-gray-500">Showing last 10 records</span>
            </div>
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-sm text-gray-400 whitespace-nowrap">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-800/30 border-b border-gray-700">
                        <tr>
                            <th class="px-4 py-3 font-medium">Time</th>
                            <th class="px-4 py-3 font-medium text-right">Cr (µg/L)</th>
                            <th class="px-4 py-3 font-medium text-right">EC</th>
                            <th class="px-4 py-3 font-medium text-right">TDS</th>
                            <th class="px-4 py-3 font-medium text-right">pH</th>
                            <th class="px-4 py-3 font-medium text-right">Suhu Air</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody id="dataTableBody" class="divide-y divide-gray-800/60">
                        <!-- Dynamic Rows Here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script type="module">
    // Init state vars from backend
    let sensorCache = @json($initialData); // chronologically sorted (last item is newest)
    let isPaused = false;
    let chartCr, chartEcTds, chartPhSuhu;

    const limitChartPoints = 30;
    const limitTableRows = 10;
    const limitAlerts = 10;
    
    // Arrays for charts
    let dataCr = [], dataEc = [], dataTds = [], dataPh = [], dataSuhu = [];
    
    const colors = {
        normal: { bg: 'rgba(34, 197, 94, 0.1)', border: 'rgba(34, 197, 94, 0.3)', text: 'text-green-400', tailwindBg: 'bg-green-500/20', label: 'NORMAL' },
        warning: { bg: 'rgba(234, 179, 8, 0.1)', border: 'rgba(234, 179, 8, 0.3)', text: 'text-yellow-400', tailwindBg: 'bg-yellow-500/20', label: 'WARNING' },
        danger: { bg: 'rgba(239, 68, 68, 0.1)', border: 'rgba(239, 68, 68, 0.3)', text: 'text-red-400', tailwindBg: 'bg-red-500/20', label: 'DANGER' }
    };

    // Pre-calculate status logic function for sensors (AI handles Cr)
    function checkStatus(key, val) {
        if (key === 'ec') return val > 800 ? 'danger' : (val >= 400 ? 'warning' : 'normal');
        if (key === 'tds') return val > 900 ? 'danger' : (val >= 500 ? 'warning' : 'normal');
        if (key === 'ph') return (val < 5.5 || val > 9.0) ? 'danger' : ((val < 6.5 || val > 8.5) ? 'warning' : 'normal');
        if (key === 'suhu_air') return val > 35 ? 'danger' : (val >= 30 ? 'warning' : 'normal');
        return 'normal'; // default
    }

    document.addEventListener("DOMContentLoaded", () => {
        initCharts();
        populateInitialData();
        setupWebSocket();
        pollHealth();
        setInterval(pollHealth, 10000); // Check API health every 10s
        
        // Pause logic
        document.getElementById('toggleFeedBtn').addEventListener('click', function() {
            isPaused = !isPaused;
            if(isPaused) {
                this.classList.replace('bg-blue-600', 'bg-gray-600');
                this.classList.replace('hover:bg-blue-500', 'hover:bg-gray-500');
                document.getElementById('feedText').innerText = 'Resume Feed';
                document.getElementById('feedIcon').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
            } else {
                this.classList.replace('bg-gray-600', 'bg-blue-600');
                this.classList.replace('hover:bg-gray-500', 'hover:bg-blue-500');
                document.getElementById('feedText').innerText = 'Pause Feed';
                document.getElementById('feedIcon').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                
                // Flush cache to screen when resuming
                if(sensorCache.length > 0) {
                    processIncomingData(sensorCache[sensorCache.length - 1], true);
                    renderAllCharts();
                    renderTable();
                }
            }
        });
    });

    function getDarkModeOptions() {
        return {
            chart: { background: 'transparent', toolbar: { show: false }, animations: { enabled: true, easing: 'linear', dynamicAnimation: { speed: 1000 } } },
            theme: { mode: 'dark' },
            grid: { borderColor: 'rgba(255,255,255,0.05)', strokeDashArray: 4 },
            dataLabels: { enabled: false },
            tooltip: { theme: 'dark' },
            stroke: { curve: 'smooth', width: 2 }
        };
    }

    function initCharts() {
        // Cr Chart
        let optionsCr = {
            ...getDarkModeOptions(),
            series: [{ name: 'Cr Estimated', data: [] }],
            chart: { ...getDarkModeOptions().chart, type: 'area', height: 250 },
            colors: ['#F97316'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
            xaxis: { type: 'datetime', labels: { style: { colors: '#9ca3af' } } },
            yaxis: { title: { text: 'µg/L' }, labels: { style: { colors: '#9ca3af' } }, min: 0 },
            annotations: {
                y: [
                    { y: 50, borderColor: '#EAB308', strokeDashArray: 3, label: { borderColor: '#EAB308', style: { color: '#fff', background: '#EAB308' }, text: 'Warning (50)' } },
                    { y: 100, borderColor: '#EF4444', strokeDashArray: 2, label: { borderColor: '#EF4444', style: { color: '#fff', background: '#EF4444' }, text: 'Danger (100)' } }
                ]
            }
        };
        chartCr = new ApexCharts(document.querySelector("#chartCr"), optionsCr);
        chartCr.render();

        // EC & TDS Dual Line
        let optionsEcTds = {
            ...getDarkModeOptions(),
            series: [{ name: 'EC (µS/cm)', data: [] }, { name: 'TDS (mg/L)', data: [] }],
            chart: { ...getDarkModeOptions().chart, type: 'line', height: 250 },
            colors: ['#3B82F6', '#10B981'],
            xaxis: { type: 'datetime', labels: { style: { colors: '#9ca3af' } } },
            yaxis: [
                { title: { text: 'EC (µS/cm)', style: { color: '#3B82F6'} }, labels: { style: { colors: '#3B82F6' } } },
                { opposite: true, title: { text: 'TDS (mg/L)', style: { color: '#10B981'} }, labels: { style: { colors: '#10B981' } } }
            ]
        };
        chartEcTds = new ApexCharts(document.querySelector("#chartEcTds"), optionsEcTds);
        chartEcTds.render();

        // pH & Suhu Dual Line
        let optionsPhSuhu = {
            ...getDarkModeOptions(),
            series: [{ name: 'pH', data: [] }, { name: 'Water Temp (°C)', data: [] }],
            chart: { ...getDarkModeOptions().chart, type: 'line', height: 250 },
            colors: ['#A855F7', '#F59E0B'],
            xaxis: { type: 'datetime', labels: { style: { colors: '#9ca3af' } } },
            yaxis: [
                { title: { text: 'pH', style: { color: '#A855F7'} }, labels: { style: { colors: '#A855F7' } } },
                { opposite: true, title: { text: 'Temp (°C)', style: { color: '#F59E0B'} }, labels: { style: { colors: '#F59E0B' } } }
            ]
        };
        chartPhSuhu = new ApexCharts(document.querySelector("#chartPhSuhu"), optionsPhSuhu);
        chartPhSuhu.render();
    }

    function populateInitialData() {
        if(sensorCache.length === 0) return;
        
        sensorCache.forEach(row => {
            appendChartData(row);
        });
        
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
            dataCr.shift();
            dataEc.shift();
            dataTds.shift();
            dataPh.shift();
            dataSuhu.shift();
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
            } else {
                wsDot.className = 'w-2.5 h-2.5 rounded-full bg-red-500';
                wsText.innerText = 'Disconnected';
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
                    // Card 9.3: Trigger browser notification on danger
                    if (record.status === 'danger') {
                        triggerDangerNotification(record);
                    }
                }
            });
    }

    function processIncomingData(data, isInitial = false) {
        // Update Time
        const tsMatch = data.created_at.match(/T(\d{2}:\d{2}:\d{2})/);
        const hmString = tsMatch ? tsMatch[1] : new Date(data.created_at).toLocaleTimeString();
        document.getElementById('lastUpdateText').innerText = hmString;

        // Update CR
        updateCard('cr', data.cr_estimated, data.status, isInitial);
        
        // Update other sensors
        const keys = ['ec', 'tds', 'ph', 'suhu_air', 'suhu_lingkungan', 'kelembapan', 'tegangan'];
        keys.forEach(k => {
            const s = checkStatus(k, data[k]);
            updateCard(k, data[k], s, isInitial);
        });

        // Update Master Badge based on CR
        const badgeInfo = colors[data.status] || colors.normal;
        document.getElementById('masterStatusBadge').style.background = badgeInfo.bg;
        document.getElementById('masterStatusBadge').style.borderColor = badgeInfo.border;
        document.getElementById('masterStatusDot').className = `h-2 w-2 rounded-full ${data.status==='danger'?'bg-red-500':(data.status==='warning'?'bg-yellow-400':'bg-green-500')}`;
        const lbl = document.getElementById('masterStatusLabel');
        lbl.className = `font-bold tracking-wide ${badgeInfo.text}`;
        let statusId = data.status === 'danger' ? 'TERCEMAR BERAT' : (data.status === 'warning' ? 'TERCEMAR RINGAN' : 'BERSIH');
        lbl.innerText = `QUALITY: ${statusId}`;
    }

    function updateCard(id, val, status, isInitial) {
        const valEl = document.getElementById(`val-${id}`);
        if(valEl) {
            valEl.innerText = id==='cr' ? val.toFixed(2) : val.toFixed(1);
            if(!isInitial) {
                valEl.classList.remove('value-update-flash');
                void valEl.offsetWidth; // trigger reflow
                valEl.classList.add('value-update-flash');
            }
        }

        const badgeEl = document.getElementById(`badge-${id}`);
        if(badgeEl) {
            const col = colors[status] || colors.normal;
            badgeEl.className = `px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider ${col.tailwindBg} ${col.text}`;
            badgeEl.innerText = status;
            
            // Adjust card border color for Cr
            if(id === 'cr') {
                const container = document.getElementById(`cardContainer-cr`);
                container.className = `glass-card rounded-xl p-5 border-l-4 relative overflow-hidden group col-span-1 md:col-span-2 lg:col-span-1 ${status==='danger'?'border-l-red-500':(status==='warning'?'border-l-yellow-500':'border-l-blue-500')}`;
            }
        }

        // Calculate Min/Max from cache
        const minmaxEl = document.getElementById(`minmax-${id}`);
        if(minmaxEl && sensorCache.length > 0) {
            let column = id === 'cr' ? 'cr_estimated' : id;
            let arr = sensorCache.map(r => r[column]);
            let min = Math.min(...arr).toFixed(1);
            let max = Math.max(...arr).toFixed(1);
            minmaxEl.innerText = `MIN: ${min} | MAX: ${max}`;
        }
    }

    function prependAlertLog(data, animate) {
        const tsMatch = data.created_at.match(/T(\d{2}:\d{2}:\d{2})/);
        const timeStr = tsMatch ? tsMatch[1] : new Date(data.created_at).toLocaleTimeString();
        
        const container = document.getElementById('alertContainer');
        const col = colors[data.status];
        
        const markup = `
            <div class="px-4 py-3 rounded-lg border border-gray-800/60 bg-gray-800/20 flex justify-between items-center ${animate ? 'row-enter' : ''}">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400 font-mono">${timeStr}</span>
                        <div class="h-1.5 w-1.5 rounded-full ${data.status==='danger'?'bg-red-500':'bg-yellow-400'}"></div>
                    </div>
                    <p class="text-sm font-medium text-gray-200 mt-1">Cr Estimated: <span class="${col.text} font-bold">${data.cr_estimated.toFixed(2)} µg/L</span></p>
                </div>
                <div class="px-2 py-1 rounded ${col.tailwindBg} ${col.text} text-xs font-bold uppercase">${data.status}</div>
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
            const tsMatch = row.created_at.match(/T(\d{2}:\d{2}:\d{2})/);
            const timeStr = tsMatch ? tsMatch[1] : new Date(row.created_at).toLocaleTimeString();
            
            let bgClass = "hover:bg-gray-800/20";
            let animClass = "";
            let textCol = 'text-green-400';
            
            if(row.status === 'warning') { textCol = 'text-yellow-400'; if(i===0 && sensorCache.length > 30) animClass = "row-highlight-warning"; }
            if(row.status === 'danger') { textCol = 'text-red-400'; if(i===0 && sensorCache.length > 30) animClass = "row-highlight-danger"; }

            tb.innerHTML += `
                <tr class="transition-colors ${bgClass} ${animClass}">
                    <td class="px-4 py-3 font-mono text-xs">${timeStr}</td>
                    <td class="px-4 py-3 text-right font-semibold ${textCol}">${row.cr_estimated.toFixed(2)}</td>
                    <td class="px-4 py-3 text-right text-gray-300 font-mono">${row.ec.toFixed(1)}</td>
                    <td class="px-4 py-3 text-right text-gray-300 font-mono">${row.tds.toFixed(1)}</td>
                    <td class="px-4 py-3 text-right text-gray-300 font-mono">${row.ph.toFixed(1)}</td>
                    <td class="px-4 py-3 text-right text-gray-300 font-mono">${row.suhu_air.toFixed(1)}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded text-[10px] font-bold uppercase ${colors[row.status].tailwindBg} ${colors[row.status].text}">${row.status}</span>
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
                } else {
                    aiDot.className = 'w-2.5 h-2.5 rounded-full bg-red-500';
                    aiText.innerText = 'Offline';
                }
            })
            .catch(err => {
                document.getElementById('aiHealthDot').className = 'w-2.5 h-2.5 rounded-full bg-red-500';
                document.getElementById('aiHealthText').innerText = 'Offline';
            });
    }

    // ─────────────────────────────────────────────
    // Card 9.3: Browser Web Notification System
    // ─────────────────────────────────────────────

    // Request notification permission once on page load
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    let lastDangerNotifTime = 0; // Throttle: don't spam every second

    function triggerDangerNotification(record) {
        const now = Date.now();
        // Throttle: only once every 30 seconds
        if (now - lastDangerNotifTime < 30000) return;
        lastDangerNotifTime = now;

        const crVal = parseFloat(record.cr_estimated).toFixed(2);

        // Update browser tab title to alert user even if minimized
        const originalTitle = document.title;
        document.title = `⚠️ DANGER! Cr = ${crVal} µg/L`;
        setTimeout(() => { document.title = originalTitle; }, 8000);

        // Send native OS notification if permission granted
        if ('Notification' in window && Notification.permission === 'granted') {
            const notif = new Notification('⚠️ HERA — Peringatan Bahaya!', {
                body: `Kadar Chromium terdeteksi ${crVal} µg/L — Melebihi batas aman (>100 µg/L)!`,
                icon: '/favicon.ico',
                badge: '/favicon.ico',
                tag: 'hera-danger',       // Replace existing notif with same tag
                requireInteraction: true   // Stay until user dismisses
            });
            // Click notification -> focus dashboard tab
            notif.onclick = () => { window.focus(); notif.close(); };
        }
    }

</script>
@endpush
