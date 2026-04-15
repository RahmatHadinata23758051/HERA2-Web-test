@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Header & Filter Bar --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-5 rounded-xl shadow-sm border border-surface-container-high">
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-on-surface font-headline">Analisis Parameter Sensor</h2>
            <p class="text-on-surface-variant text-sm mt-1">Visualisasi historis dan stream langsung per-parameter sensor</p>
        </div>

        {{-- Filter Tabs --}}
        <div class="flex bg-surface-container-low p-1 rounded-xl border border-surface-container-high gap-1 flex-shrink-0">
            <button data-filter="live"
                    class="filter-btn px-5 py-2 text-sm font-bold rounded-lg transition-all bg-primary text-white shadow-sm">
                Live Stream
            </button>
            <button data-filter="1h"
                    class="filter-btn px-5 py-2 text-sm font-medium rounded-lg transition-all text-on-surface-variant hover:text-primary hover:bg-white">
                1 Jam
            </button>
            <button data-filter="today"
                    class="filter-btn px-5 py-2 text-sm font-medium rounded-lg transition-all text-on-surface-variant hover:text-primary hover:bg-white">
                Hari Ini
            </button>
            <button data-filter="7d"
                    class="filter-btn px-5 py-2 text-sm font-medium rounded-lg transition-all text-on-surface-variant hover:text-primary hover:bg-white">
                7 Hari
            </button>
        </div>
    </div>

    {{-- Loading Indicator Overlay --}}
    <div id="loadingOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-on-surface/10 backdrop-blur-sm" style="display: none !important;">
        <div class="bg-white rounded-2xl shadow-xl px-8 py-6 flex flex-col items-center gap-3">
            <svg class="animate-spin h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-on-surface font-bold text-sm">Memuat Data...</span>
        </div>
    </div>

    {{-- Charts Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        @php
            $params = [
                ['id' => 'cr_estimated', 'title' => 'Hexavalent Chromium (Cr)', 'unit' => 'mg/L',   'color' => '#006948', 'badge' => 'AI Estimated', 'badgeColor' => 'bg-primary/10 text-primary'],
                ['id' => 'ec',           'title' => 'Electrical Conductivity',   'unit' => 'µS/cm',  'color' => '#0ea5e9', 'badge' => 'Fisik',        'badgeColor' => 'bg-sky-100 text-sky-700'],
                ['id' => 'tds',          'title' => 'Total Dissolved Solids',    'unit' => 'mg/L',   'color' => '#10b981', 'badge' => 'Fisik',        'badgeColor' => 'bg-emerald-100 text-emerald-700'],
                ['id' => 'ph',           'title' => 'Acidity (pH Level)',         'unit' => 'pH',     'color' => '#a855f7', 'badge' => 'Fisik',        'badgeColor' => 'bg-purple-100 text-purple-700'],
                ['id' => 'suhu_air',     'title' => 'Water Temperature',          'unit' => '°C',     'color' => '#f59e0b', 'badge' => 'Fisik',        'badgeColor' => 'bg-amber-100 text-amber-700'],
                ['id' => 'suhu_lingkungan', 'title' => 'Ambient Temperature',    'unit' => '°C',     'color' => '#ec4899', 'badge' => 'Fisik',        'badgeColor' => 'bg-pink-100 text-pink-700'],
                ['id' => 'kelembapan',   'title' => 'Relative Humidity',          'unit' => '%',      'color' => '#06b6d4', 'badge' => 'Fisik',        'badgeColor' => 'bg-cyan-100 text-cyan-700'],
            ];
        @endphp

        @foreach($params as $index => $param)
        <div class="bg-white rounded-xl border border-surface-container-high shadow-sm {{ $index === 0 ? 'xl:col-span-2' : '' }}">
            {{-- Card Header --}}
            <div class="px-6 py-4 flex justify-between items-center border-b border-surface-container-highest">
                <div class="flex items-center gap-3">
                    <h3 class="font-bold text-on-surface text-sm font-headline">{{ $param['title'] }}</h3>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $param['badgeColor'] }}">{{ $param['badge'] }}</span>
                </div>
                <span class="text-xs font-bold text-outline bg-surface-container-low px-2 py-1 rounded-lg">
                    {{ $param['unit'] }}
                </span>
            </div>
            {{-- Chart --}}
            <div class="p-4">
                <div id="chart-{{ $param['id'] }}"
                     class="w-full {{ $index === 0 ? 'h-80' : 'h-60' }}"
                     data-color="{{ $param['color'] }}"
                     x-ignore>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
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
                
                // Update UI
                buttons.forEach(b => {
                    b.classList.remove('bg-primary', 'text-white', 'shadow-sm');
                    b.classList.add('text-on-surface-variant', 'hover:text-primary', 'hover:bg-white');
                });
                this.classList.remove('text-on-surface-variant', 'hover:text-primary', 'hover:bg-white');
                this.classList.add('bg-primary', 'text-white', 'shadow-sm');
                
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
                series: [{ name: id.replace('_', ' ').toUpperCase(), data: [] }],
                chart: {
                    type: 'area',
                    height: '100%',
                    background: 'transparent',
                    toolbar: { show: false },
                    animations: {
                        enabled: true,
                        easing: 'linear',
                        dynamicAnimation: { speed: 1000 }
                    }
                },
                colors: [color],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.15,
                        opacityTo: 0.01,
                        stops: [0, 100]
                    }
                },
                // ── LIGHT MODE ──
                theme: { mode: 'light' },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 4,
                    padding: { left: 0, right: 0 }
                },
                dataLabels: { enabled: false },
                tooltip: {
                    theme: 'light',
                    shared: true,
                    intersect: false
                },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: {
                    type: 'datetime',
                    labels: {
                        style: { colors: '#6d7a72', fontSize: '11px' }
                    },
                    axisBorder: { color: '#e0e3e5' },
                    axisTicks: { color: '#e0e3e5' }
                },
                yaxis: {
                    labels: {
                        style: { colors: '#6d7a72', fontSize: '11px' },
                        formatter: (value) => value !== undefined ? value.toFixed(2) : ''
                    }
                },
                // Cr-specific annotations
                ...(id === 'cr_estimated' ? {
                    annotations: {
                        y: [
                            {
                                y: 0.05,
                                borderColor: '#eab308',
                                strokeDashArray: 3,
                                label: {
                                    borderColor: '#eab308',
                                    style: { color: '#fff', background: '#eab308', fontWeight: 700, fontSize: '10px' },
                                    text: 'Warning (0.05 mg/L)'
                                }
                            },
                            {
                                y: 0.10,
                                borderColor: '#ba1a1a',
                                strokeDashArray: 2,
                                label: {
                                    borderColor: '#ba1a1a',
                                    style: { color: '#fff', background: '#ba1a1a', fontWeight: 700, fontSize: '10px' },
                                    text: 'Danger (0.10 mg/L)'
                                }
                            }
                        ]
                    }
                } : {})
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
            console.error('Error fetching monitoring data', e);
        } finally {
            if (loader) loader.style.display = 'none';
        }
    }

    function updateAllChartsArray(dataArray) {
        let seriesData = {
            cr_estimated: [], ec: [], tds: [], ph: [],
            suhu_air: [], suhu_lingkungan: [], kelembapan: []
        };
        
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
                    let currentData = [...chart.w.config.series[0].data];
                    currentData.push([ts, record[key]]);
                    if (currentData.length > 60) currentData.shift();
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
