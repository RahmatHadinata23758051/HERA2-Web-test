@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="monitoringModule()">
    {{-- Header & Filter --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-bold tracking-tight text-white">Monitoring</h2>
            <p class="text-gray-400 mt-1">Analisis parameter individu dan data historis</p>
        </div>

        <div class="flex bg-gray-800/80 p-1 rounded-lg border border-gray-700/50">
            <button @click="setFilter('live')"
                    :class="filter === 'live' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-400 hover:text-gray-200'"
                    class="px-5 py-2 text-sm font-medium rounded-md transition-all">
                Live Stream
            </button>
            <button @click="setFilter('1h')"
                    :class="filter === '1h' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-400 hover:text-gray-200'"
                    class="px-5 py-2 text-sm font-medium rounded-md transition-all">
                1 Jam
            </button>
            <button @click="setFilter('today')"
                    :class="filter === 'today' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-400 hover:text-gray-200'"
                    class="px-5 py-2 text-sm font-medium rounded-md transition-all">
                Hari Ini
            </button>
            <button @click="setFilter('7d')"
                    :class="filter === '7d' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-400 hover:text-gray-200'"
                    class="px-5 py-2 text-sm font-medium rounded-md transition-all">
                7 Hari
            </button>
        </div>
    </div>

    {{-- Loading Indicator Overlay --}}
    <div x-show="loading" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm" style="display: none;">
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
                <div id="chart-{{ $param['id'] }}" class="w-full h-64" data-color="{{ $param['color'] }}"></div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('monitoringModule', () => ({
            filter: 'live',
            loading: false,
            charts: {},
            lastDataLimit: 30,
            pusherBound: false,

            init() {
                this.initCharts();
                this.loadData();
            },

            setFilter(type) {
                if (this.filter === type) return;
                this.filter = type;
                this.loadData();
            },

            initCharts() {
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
                            animations: { enabled: false } // Disabled to prevent lag on bulk update
                        },
                        colors: [color],
                        fill: {
                            type: 'gradient',
                            gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] }
                        },
                        theme: { mode: 'dark' },
                        grid: { borderColor: 'rgba(255,255,255,0.05)', strokeDashArray: 4 },
                        dataLabels: { enabled: false },
                        stroke: { curve: 'smooth', width: 2 },
                        xaxis: { type: 'datetime', labels: { style: { colors: '#9ca3af' } } },
                        yaxis: { labels: { style: { colors: '#9ca3af' }, formatter: (value) => value.toFixed(1) } },
                    };

                    this.charts[id] = new window.ApexCharts(ctx, options);
                    this.charts[id].render();
                });
            },

            async loadData() {
                this.loading = true;
                this.unbindRealtime();

                let url = '';
                if (this.filter === 'live') {
                    url = '/api/sensor/latest';
                } else {
                    const now = new Date();
                    let from = new Date();
                    
                    if (this.filter === '1h') {
                        from.setHours(now.getHours() - 1);
                    } else if (this.filter === 'today') {
                        from.setHours(0, 0, 0, 0);
                    } else if (this.filter === '7d') {
                        from.setDate(now.getDate() - 7);
                    }

                    // Format dates to YYYY-MM-DD HH:mm:ss for backend
                    const fp = this.formatDate(from);
                    const tp = this.formatDate(now);
                    let limit = this.filter === '7d' ? 5000 : 1000;
                    url = `/api/sensor/history?from=${fp}&to=${tp}&limit=${limit}`;
                }

                try {
                    const res = await fetch(url);
                    const data = await res.json();
                    
                    this.updateAllChartsArray(data);

                    if (this.filter === 'live') {
                        this.bindRealtime();
                    }
                } catch (e) {
                    console.error('Error fetching data', e);
                } finally {
                    this.loading = false;
                }
            },

            updateAllChartsArray(dataArray) {
                // Prepare structure
                let seriesData = { cr_estimated: [], ec: [], tds: [], ph: [], suhu_air: [], suhu_lingkungan: [], kelembapan: [] };
                
                dataArray.forEach(row => {
                    // created_at comes back with "T" and "Z" if JSON mapped from Carbon
                    const tsMatch = row.created_at.match(/T(\d{2}:\d{2}:\d{2})/);
                    let ts;
                    if (tsMatch) {
                        ts = new Date(row.created_at).getTime();
                    } else {
                        // Safe fallback just in case formatting differs using direct parse
                        ts = Date.parse(row.created_at);
                    }

                    Object.keys(seriesData).forEach(key => {
                        seriesData[key].push([ts, row[key]]);
                    });
                });

                // Apply to charts
                Object.keys(seriesData).forEach(key => {
                    if (this.charts[key]) {
                        this.charts[key].updateSeries([{ data: seriesData[key] }]);
                    }
                });
            },

            bindRealtime() {
                if (!window.Echo || this.pusherBound) return;
                
                window.Echo.channel('sensor-monitoring')
                    .listen('.SensorDataUpdated', (e) => {
                        if (this.filter !== 'live') return;
                        
                        const record = e.sensorData || e;
                        
                        const tsMatch = record.created_at.match(/T(\d{2}:\d{2}:\d{2})/);
                        let ts = tsMatch ? new Date(record.created_at).getTime() : Date.parse(record.created_at);

                        // Append to existing charts directly to avoid re-rendering entire structure
                        Object.keys(this.charts).forEach(key => {
                            let chart = this.charts[key];
                            // Get current subset
                            let currentData = chart.w.config.series[0].data;
                            currentData.push([ts, record[key]]);
                            
                            // Keep max 50 points in live view
                            if (currentData.length > 50) {
                                currentData.shift();
                            }
                            
                            chart.updateSeries([{ data: currentData }]);
                        });
                    });
                this.pusherBound = true;
            },

            unbindRealtime() {
                if (window.Echo && this.pusherBound) {
                    window.Echo.leaveChannel('sensor-monitoring');
                    this.pusherBound = false;
                }
            },

            formatDate(date) {
                return date.getFullYear() + '-' + 
                    String(date.getMonth() + 1).padStart(2, '0') + '-' + 
                    String(date.getDate()).padStart(2, '0') + ' ' + 
                    String(date.getHours()).padStart(2, '0') + ':' + 
                    String(date.getMinutes()).padStart(2, '0') + ':' + 
                    String(date.getSeconds()).padStart(2, '0');
            }
        }));
    });
</script>
@endpush
