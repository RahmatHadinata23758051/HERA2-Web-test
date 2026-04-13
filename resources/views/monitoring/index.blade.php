@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Header & Filter --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-bold tracking-tight text-white">Monitoring</h2>
            <p class="text-gray-400 mt-1">Analisis parameter individu dan data historis</p>
        </div>

        <div class="flex bg-gray-800/80 p-1 rounded-lg border border-gray-700/50">
            <button data-filter="live"
                    class="filter-btn bg-blue-600 text-white shadow-lg px-5 py-2 text-sm font-medium rounded-md transition-all">
                Live Stream
            </button>
            <button data-filter="1h"
                    class="filter-btn text-gray-400 hover:text-gray-200 px-5 py-2 text-sm font-medium rounded-md transition-all">
                1 Jam
            </button>
            <button data-filter="today"
                    class="filter-btn text-gray-400 hover:text-gray-200 px-5 py-2 text-sm font-medium rounded-md transition-all">
                Hari Ini
            </button>
            <button data-filter="7d"
                    class="filter-btn text-gray-400 hover:text-gray-200 px-5 py-2 text-sm font-medium rounded-md transition-all">
                7 Hari
            </button>
        </div>
    </div>

    {{-- Loading Indicator Overlay --}}
    <div id="loadingOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm" style="display: none;">
        <div class="flex flex-col items-center">
            <svg class="animate-spin h-10 w-10 text-blue-500 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-white font-medium">Memuat Data...</span>
        </div>
    </div>

    {{-- Charts Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        @php
            $params = [
                ['id' => 'cr_estimated', 'title' => 'Hexavalent Chromium (Cr)', 'unit' => 'µg/L', 'color' => '#F97316', 'min' => 0],
                ['id' => 'ec', 'title' => 'Electrical Conductivity (EC)', 'unit' => 'µS/cm', 'color' => '#3B82F6', 'min' => 0],
                ['id' => 'tds', 'title' => 'Total Dissolved Solids (TDS)', 'unit' => 'mg/L', 'color' => '#10B981', 'min' => 0],
                ['id' => 'ph', 'title' => 'Acidity (pH)', 'unit' => '', 'color' => '#A855F7', 'min' => 0],
                ['id' => 'suhu_air', 'title' => 'Water Temperature', 'unit' => '°C', 'color' => '#F59E0B', 'min' => 0],
                ['id' => 'suhu_lingkungan', 'title' => 'Ambient Temperature', 'unit' => '°C', 'color' => '#EC4899', 'min' => 0],
                ['id' => 'kelembapan', 'title' => 'Humidity', 'unit' => '%', 'color' => '#06B6D4', 'min' => 0],
            ];
        @endphp

        @foreach($params as $index => $param)
            <div class="glass-card rounded-xl p-5 {{ $index === 0 ? 'xl:col-span-2' : '' }}">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-semibold text-white">{{ $param['title'] }}</h3>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-gray-500 bg-gray-800/50 px-2 py-1 rounded">
                        {{ $param['unit'] ?: 'pH' }}
                    </div>
                </div>
                <div id="chart-{{ $param['id'] }}" class="w-full h-64" data-color="{{ $param['color'] }}" x-ignore></div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentFilter = '{{ request("filter", "live") }}';
    let globalCharts = {};
    let isPusherBound = false;

    document.addEventListener("DOMContentLoaded", () => {
        initCharts();
        loadData();
        setupFilterListeners();
    });

    function setupFilterListeners() {
        const buttons = document.querySelectorAll('.filter-btn');
        buttons.forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.getAttribute('data-filter');
                if (currentFilter === type) return;
                
                // Update UI visually
                buttons.forEach(b => {
                    b.classList.remove('bg-blue-600', 'text-white', 'shadow-lg');
                    b.classList.add('text-gray-400', 'hover:text-gray-200');
                });
                this.classList.remove('text-gray-400', 'hover:text-gray-200');
                this.classList.add('bg-blue-600', 'text-white', 'shadow-lg');
                
                currentFilter = type;
                loadData();
            });
        });
    }

    function initCharts() {
        const params = ['cr_estimated', 'ec', 'tds', 'ph', 'suhu_air', 'suhu_lingkungan', 'kelembapan'];
        
        params.forEach(id => {
            const ctx = document.getElementById(`chart-${id}`);
            if (!ctx) return;
            const color = ctx.getAttribute('data-color');

            const options = {
                series: [{ name: id.toUpperCase(), data: [] }],
                chart: {
                    type: 'area',
                    height: 250,
                    background: 'transparent',
                    toolbar: { show: false },
                    animations: { enabled: true, easing: 'linear', dynamicAnimation: { speed: 1000 } }
                },
                colors: [color],
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] }
                },
                theme: { mode: 'dark' },
                grid: { borderColor: 'rgba(255,255,255,0.05)', strokeDashArray: 4 },
                dataLabels: { enabled: false },
                tooltip: { theme: 'dark', shared: true, intersect: false },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: { type: 'datetime', labels: { style: { colors: '#9ca3af' } } },
                yaxis: { labels: { style: { colors: '#9ca3af' }, formatter: (value) => value.toFixed(1) } },
            };

            globalCharts[id] = new window.ApexCharts(ctx, options);
            globalCharts[id].render();
        });
    }

    async function loadData() {
        const loader = document.getElementById('loadingOverlay');
        if (loader) loader.style.display = 'flex';
        
        unbindRealtime();

        let url = '';
        if (currentFilter === 'live') {
            url = '/api/sensor/latest';
        } else {
            const now = new Date();
            let from = new Date();
            
            if (currentFilter === '1h') {
                from.setHours(now.getHours() - 1);
            } else if (currentFilter === 'today') {
                from.setHours(0, 0, 0, 0);
            } else if (currentFilter === '7d') {
                from.setDate(now.getDate() - 7);
            }

            const fp = encodeURIComponent(from.toISOString());
            const tp = encodeURIComponent(now.toISOString());
            let limit = currentFilter === '7d' ? 5000 : 1000;
            url = `/api/sensor/history?from=${fp}&to=${tp}&limit=${limit}`;
        }

        try {
            const res = await fetch(url);
            const data = await res.json();
            updateAllChartsArray(data);

            if (currentFilter === 'live') {
                bindRealtime();
            }
        } catch (e) {
            console.error('Error fetching data', e);
        } finally {
            if (loader) loader.style.display = 'none';
        }
    }

    function updateAllChartsArray(dataArray) {
        let seriesData = { cr_estimated: [], ec: [], tds: [], ph: [], suhu_air: [], suhu_lingkungan: [], kelembapan: [] };
        
        dataArray.forEach(row => {
            let ts = new Date(row.created_at).getTime();

            Object.keys(seriesData).forEach(key => {
                seriesData[key].push([ts, row[key]]);
            });
        });

        Object.keys(seriesData).forEach(key => {
            if (globalCharts[key]) {
                globalCharts[key].updateSeries([{ data: seriesData[key] }]);
            }
        });
    }

    function bindRealtime() {
        if (!window.Echo || isPusherBound) return;
        window.Echo.channel('sensor-monitoring')
            .listen('.SensorDataUpdated', (e) => {
                if (currentFilter !== 'live') return;
                
                const record = e.sensorData || e;
                let ts = new Date(record.created_at).getTime();

                Object.keys(globalCharts).forEach(key => {
                    let chart = globalCharts[key];
                    let currentData = [...chart.w.config.series[0].data]; // Clone array to force re-render bindings
                    currentData.push([ts, record[key]]);
                    
                    if (currentData.length > 50) {
                        currentData.shift();
                    }
                    chart.updateSeries([{ data: currentData }]);
                });
            });
        isPusherBound = true;
    }

    function unbindRealtime() {
        if (window.Echo && isPusherBound) {
            window.Echo.leaveChannel('sensor-monitoring');
            isPusherBound = false;
        }
    }
</script>
@endpush
