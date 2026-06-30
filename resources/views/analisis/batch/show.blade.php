@extends('layouts.app')

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

{{-- Leaflet.markercluster Assets --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

<style>
    @keyframes radarPulseRed {
        0%   { transform: scale(0.2); opacity: 0.8; }
        100% { transform: scale(2.8); opacity: 0.0; }
    }
    @keyframes radarPulseGreen {
        0%   { transform: scale(0.2); opacity: 0.6; }
        100% { transform: scale(2.2); opacity: 0.0; }
    }
    .radar-container {
        position: relative;
        width: 32px;
        height: 32px;
        cursor: pointer;
    }
    .radar-pulse {
        position: absolute;
        top: 0;
        left: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        transform-origin: center;
        animation-play-state: paused; /* Diam secara default */
    }
    .radar-pulse.red {
        background: rgba(239, 68, 68, 0.4);
        animation: radarPulseRed 2s ease-out infinite;
    }
    .radar-pulse.green {
        background: rgba(16, 185, 129, 0.4);
        animation: radarPulseGreen 2.5s ease-out infinite;
    }
    /* Animasi aktif hanya saat kursor di-hover */
    .radar-container:hover .radar-pulse {
        animation-play-state: running;
    }
    .radar-dot {
        position: absolute;
        top: 12px;
        left: 12px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        border: 1.5px solid white;
        box-shadow: 0 1px 4px rgba(0,0,0,0.3);
    }
    .radar-dot.red { background: #ef4444; }
    .radar-dot.green { background: #10b981; }

    /* Custom styling untuk markercluster */
    .custom-cluster-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .custom-cluster {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 11px;
        font-family: 'Inter', sans-serif;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        background-clip: padding-box;
    }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="{ openImportModal: false, openManualModal: false }">

    {{-- Breadcrumb & Title --}}
    <div class="bg-white p-5 rounded-xl shadow-sm border border-surface-container-high space-y-4">
        <div class="flex items-center gap-2 text-xs text-on-surface-variant">
            <a href="{{ route('analisis.index') }}" class="hover:text-primary transition-colors">Daftar Batch</a>
            <svg class="w-3.5 h-3.5 text-outline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="font-bold text-on-surface">Detail Batch</span>
        </div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight text-on-surface font-headline">{{ $batch->name }}</h2>
                <p class="text-on-surface-variant text-sm mt-1">Dibuat oleh <span class="font-bold">{{ optional($batch->user)->name ?? 'Sistem' }}</span> pada {{ $batch->created_at->format('d F Y, H:i') }}</p>
            </div>
            
            <div class="flex items-center gap-2">
                <button @click="openImportModal = true"
                        class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary rounded-lg hover:brightness-110 transition-all shadow-sm font-bold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Import File
                </button>
                @if($pollutant !== 'dashboard')
                <button @click="openManualModal = true"
                        class="flex items-center gap-2 px-4 py-2 bg-secondary-container text-secondary border border-secondary/20 hover:brightness-95 rounded-lg font-bold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Input Manual
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-xl border border-surface-container-high shadow-sm overflow-hidden">
        <div class="flex border-b border-surface-container-high overflow-x-auto no-scrollbar">
            <a href="{{ route('analisis.batch.show', [$batch->id, 'dashboard']) }}"
               class="flex items-center gap-1.5 px-6 py-4 text-sm font-bold whitespace-nowrap border-b-2 transition-all {{ $pollutant === 'dashboard' ? 'text-primary border-primary bg-primary/[.02]' : 'text-on-surface-variant border-transparent hover:text-primary hover:border-primary/50' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                Ringkasan Batch
            </a>
            @foreach(\App\Models\RqAnalysis::$pollutantLabels as $key => $label)
            <a href="{{ route('analisis.batch.show', [$batch->id, $key]) }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-bold whitespace-nowrap border-b-2 transition-all {{ $pollutant === $key ? 'text-primary border-primary bg-primary/[.02]' : 'text-on-surface-variant border-transparent hover:text-primary hover:border-primary/50' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        <div class="p-5 space-y-6">
            @if($pollutant !== 'dashboard')
            {{-- Panduan Info Banner --}}
            <div class="bg-primary/5 border border-primary/20 rounded-xl p-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-primary/10 rounded-lg flex-shrink-0 text-primary mt-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h1m0-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-primary text-sm">Kalkulasi Analisis Risiko (ARKL) - {{ \App\Models\RqAnalysis::$pollutantLabels[$pollutant] }}</h4>
                        <p class="text-xs text-on-surface-variant mt-1 leading-relaxed">
                            Formulir input manual atau template kolom import Excel harus memiliki header: 
                            <code class="px-1 py-0.5 rounded bg-surface-container text-secondary font-mono text-xs">No,Responden,Umur,Wb,f,C,R,RfD,tavg,Dt (realtime)</code>.
                            <br>Dosis Acuan (RfD) Standar: <span class="font-bold text-primary">{{ $rfdDefault }} mg/kg/hari</span>.
                        </p>
                    </div>
                </div>
                
                {{-- Legend --}}
                <div class="bg-white p-3 rounded-lg border border-surface-container-high text-xs space-y-1.5 flex-shrink-0 w-full md:w-auto">
                    <p class="font-black text-on-surface text-[10px] uppercase tracking-wider">Kriteria Interpretasi Risiko (RQ):</p>
                    <div class="flex flex-wrap gap-x-4 gap-y-1">
                        <span class="flex items-center gap-1.5 font-bold text-red-800">
                            <span class="w-2.5 h-2.5 rounded bg-red-100 border border-red-300 text-center flex items-center justify-center text-[8px]">1</span> 1 = Berisiko (RQ > 1)
                        </span>
                        <span class="flex items-center gap-1.5 font-bold text-green-800">
                            <span class="w-2.5 h-2.5 rounded bg-green-100 border border-green-300 text-center flex items-center justify-center text-[8px]">2</span> 2 = Aman (RQ &le; 1)
                        </span>
                    </div>
                </div>
            </div>

            {{-- Filter & Search Form --}}
            <div class="bg-surface-container/20 border border-surface-container-high rounded-xl p-4">
                <form action="{{ route('analisis.batch.show', [$batch->id, $pollutant]) }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                    {{-- Search Input --}}
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Cari Responden</label>
                        <div class="relative flex items-center">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau no. responden..."
                                   class="w-full bg-white border border-surface-container-high text-on-surface text-sm rounded-lg pl-9 pr-4 py-2 outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary block transition">
                            <svg class="w-4 h-4 text-outline absolute left-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>

                    {{-- Risk Status Dropdown --}}
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Status Risiko (Realtime)</label>
                        <select name="risk_status"
                                class="w-full bg-white border border-surface-container-high text-on-surface text-sm rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary block transition">
                            <option value="" {{ request('risk_status') == '' ? 'selected' : '' }}>Semua</option>
                            <option value="berisiko" {{ request('risk_status') == 'berisiko' ? 'selected' : '' }}>1 (Beresiko)</option>
                            <option value="aman" {{ request('risk_status') == 'aman' ? 'selected' : '' }}>2 (Aman)</option>
                        </select>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-primary text-on-primary hover:brightness-110 font-bold rounded-lg text-sm px-4 py-2 transition-all shadow-sm">
                            Terapkan
                        </button>
                        @if(request()->anyFilled(['search', 'risk_status']))
                            <a href="{{ route('analisis.batch.show', [$batch->id, $pollutant]) }}"
                               class="flex-1 text-center bg-surface-container text-on-surface-variant hover:bg-surface-container-high font-medium rounded-lg text-sm px-4 py-2 transition-colors">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            @if($pollutant !== 'dashboard' && $mapRecords->isNotEmpty())
            {{-- Peta Sebaran Risiko Spasial Responden --}}
            <div class="bg-white border border-surface-container-high rounded-xl p-5 shadow-sm space-y-3">
                <div class="flex items-center justify-between border-b border-surface-container-highest pb-3">
                    <div>
                        <h4 class="font-extrabold text-on-surface text-sm">Peta Sebaran Risiko Spasial Responden</h4>
                        <p class="text-xs text-on-surface-variant mt-0.5">Sebaran lokasi responden berdasarkan tingkat bahaya kesehatan (RQ Realtime) untuk logam ini.</p>
                    </div>
                    <span class="px-2.5 py-1 bg-primary/10 text-primary text-[10px] font-black rounded-full uppercase tracking-wider">
                        {{ $mapRecords->count() }} Responden Terpetakan
                    </span>
                </div>
                <div id="mapSebaran" style="height: 350px; width: 100%; background: #f8fafc; z-index:1; border-radius: 8px;"></div>
            </div>
            @endif

            {{-- Table View --}}
            <div class="overflow-x-auto rounded-lg border border-surface-container-high">
                <table class="w-full text-left border-collapse min-w-[2000px]">
                    <thead>
                        {{-- Header Row 1 --}}
                        <tr class="border-b border-surface-container-high bg-slate-50 text-[10px] font-black text-on-surface-variant uppercase tracking-widest text-center">
                            <th rowspan="2" class="px-4 py-3 border-r border-surface-container-high w-16">Aksi</th>
                            <th rowspan="2" class="px-4 py-3 border-r border-surface-container-high w-12">No</th>
                            <th rowspan="2" class="px-4 py-3 border-r border-surface-container-high w-48">Nama</th>
                            <th rowspan="2" class="px-4 py-3 w-16">Umur</th>
                            <th rowspan="2" class="px-4 py-3 border-r border-surface-container-high w-16">Wb</th>
                            <th rowspan="2" class="px-4 py-3 border-r border-surface-container-high w-24">Latitude</th>
                            <th rowspan="2" class="px-4 py-3 border-r border-surface-container-high w-24">Longitude</th>
                            <th colspan="6" class="px-4 py-2 border-b border-r border-surface-container-high bg-sky-50 text-sky-800">Variabel Pajanan (Input)</th>
                            <th colspan="7" class="px-4 py-2 border-b border-r border-surface-container-high bg-emerald-50 text-emerald-800">Kalkulasi Intake (I)</th>
                            <th colspan="7" class="px-4 py-2 border-b border-r border-surface-container-high bg-purple-50 text-purple-800">Kalkulasi Risk Quotient (RQ)</th>
                            <th colspan="7" class="px-4 py-2 bg-pink-50 text-pink-800">Interpretasi Keputusan (RQ)</th>
                        </tr>
                        {{-- Header Row 2 --}}
                        <tr class="border-b-2 border-surface-container-high bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-center">
                            {{-- Input --}}
                            <th class="px-3 py-2 text-sky-700 bg-sky-50/50">f</th>
                            <th class="px-3 py-2 text-sky-700 bg-sky-50/50">C</th>
                            <th class="px-3 py-2 text-sky-700 bg-sky-50/50">R</th>
                            <th class="px-3 py-2 text-sky-700 bg-sky-50/50">RfD</th>
                            <th class="px-3 py-2 text-sky-700 bg-sky-50/50">tavg</th>
                            <th class="px-3 py-2 text-sky-700 bg-sky-50/50 border-r border-surface-container-high">Dt</th>
                            {{-- Intake --}}
                            <th class="px-3 py-2 text-emerald-700 bg-emerald-50/50">Realtime</th>
                            <th class="px-3 py-2 text-emerald-700 bg-emerald-50/50">5 th</th>
                            <th class="px-3 py-2 text-emerald-700 bg-emerald-50/50">10 th</th>
                            <th class="px-3 py-2 text-emerald-700 bg-emerald-50/50">15 th</th>
                            <th class="px-3 py-2 text-emerald-700 bg-emerald-50/50">20 th</th>
                            <th class="px-3 py-2 text-emerald-700 bg-emerald-50/50">25 th</th>
                            <th class="px-3 py-2 text-emerald-700 bg-emerald-50/50 border-r border-surface-container-high">30 th</th>
                            {{-- RQ --}}
                            <th class="px-3 py-2 text-purple-700 bg-purple-50/50">Realtime</th>
                            <th class="px-3 py-2 text-purple-700 bg-purple-50/50">5 th</th>
                            <th class="px-3 py-2 text-purple-700 bg-purple-50/50">10 th</th>
                            <th class="px-3 py-2 text-purple-700 bg-purple-50/50">15 th</th>
                            <th class="px-3 py-2 text-purple-700 bg-purple-50/50">20 th</th>
                            <th class="px-3 py-2 text-purple-700 bg-purple-50/50">25 th</th>
                            <th class="px-3 py-2 text-purple-700 bg-purple-50/50 border-r border-surface-container-high">30 th</th>
                            {{-- Interpretasi --}}
                            <th class="px-3 py-2 text-pink-700 bg-pink-50/50">Realtime</th>
                            <th class="px-3 py-2 text-pink-700 bg-pink-50/50">5 th</th>
                            <th class="px-3 py-2 text-pink-700 bg-pink-50/50">10 th</th>
                            <th class="px-3 py-2 text-pink-700 bg-pink-50/50">15 th</th>
                            <th class="px-3 py-2 text-pink-700 bg-pink-50/50">20 th</th>
                            <th class="px-3 py-2 text-pink-700 bg-pink-50/50">25 th</th>
                            <th class="px-3 py-2 text-pink-700 bg-pink-50/50">30 th</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-high">
                        @forelse($records as $row)
                        <tr class="hover:bg-surface-container-low transition-colors group">
                            {{-- Aksi --}}
                            <td class="px-4 py-3 border-r border-surface-container-high text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('analisis.record.edit', $row->id) }}"
                                       class="p-1 hover:bg-primary/10 text-on-surface-variant hover:text-primary rounded" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('analisis.record.destroy', $row->id) }}" method="POST"
                                          onsubmit="return confirm('Hapus responden &quot;{{ $row->nama }}&quot;?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1 hover:bg-error-container text-on-surface-variant hover:text-error rounded" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>

                            {{-- Responden --}}
                            <td class="px-4 py-3 text-xs text-on-surface-variant text-center font-mono border-r border-surface-container-high">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 text-sm text-on-surface font-extrabold border-r border-surface-container-high whitespace-nowrap">{{ $row->nama }}</td>
                            <td class="px-4 py-3 text-xs text-on-surface-variant text-center">{{ $row->umur }}</td>
                            <td class="px-4 py-3 text-xs text-on-surface-variant text-center border-r border-surface-container-high">{{ $row->wb }}</td>
                            <td class="px-4 py-3 text-xs text-on-surface-variant text-center font-mono">{{ $row->latitude ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs text-on-surface-variant text-center font-mono border-r border-surface-container-high">{{ $row->longitude ?? '-' }}</td>

                            {{-- Input --}}
                            <td class="px-3 py-3 text-xs text-center font-mono text-sky-800">{{ $row->f }}</td>
                            <td class="px-3 py-3 text-xs text-center font-mono text-sky-800">{{ $row->c }}</td>
                            <td class="px-3 py-3 text-xs text-center font-mono text-sky-800">{{ $row->r }}</td>
                            <td class="px-3 py-3 text-xs text-center font-mono text-sky-800">{{ $row->rfd }}</td>
                            <td class="px-3 py-3 text-xs text-center font-mono text-sky-800">{{ $row->tavg }}</td>
                            <td class="px-3 py-3 text-xs text-center font-mono text-sky-800 border-r border-surface-container-high">{{ $row->dt_input }}</td>

                            {{-- Intake --}}
                            <td class="px-3 py-3 text-xs text-center font-mono text-emerald-800">{{ number_format($row->intake_realtime, 5) }}</td>
                            <td class="px-3 py-3 text-xs text-center font-mono text-emerald-800">{{ number_format($row->intake_5th, 5) }}</td>
                            <td class="px-3 py-3 text-xs text-center font-mono text-emerald-800">{{ number_format($row->intake_10th, 5) }}</td>
                            <td class="px-3 py-3 text-xs text-center font-mono text-emerald-800">{{ number_format($row->intake_15th, 5) }}</td>
                            <td class="px-3 py-3 text-xs text-center font-mono text-emerald-800">{{ number_format($row->intake_20th, 5) }}</td>
                            <td class="px-3 py-3 text-xs text-center font-mono text-emerald-800">{{ number_format($row->intake_25th, 5) }}</td>
                            <td class="px-3 py-3 text-xs text-center font-mono text-emerald-800 border-r border-surface-container-high">{{ number_format($row->intake_30th, 5) }}</td>

                            {{-- RQ --}}
                            <td class="px-3 py-3 text-xs text-center font-mono text-purple-800 font-medium">{{ number_format($row->rq_realtime, 4) }}</td>
                            <td class="px-3 py-3 text-xs text-center font-mono text-purple-800 font-medium">{{ number_format($row->rq_5th, 4) }}</td>
                            <td class="px-3 py-3 text-xs text-center font-mono text-purple-800 font-medium">{{ number_format($row->rq_10th, 4) }}</td>
                            <td class="px-3 py-3 text-xs text-center font-mono text-purple-800 font-medium">{{ number_format($row->rq_15th, 4) }}</td>
                            <td class="px-3 py-3 text-xs text-center font-mono text-purple-800 font-medium">{{ number_format($row->rq_20th, 4) }}</td>
                            <td class="px-3 py-3 text-xs text-center font-mono text-purple-800 font-medium">{{ number_format($row->rq_25th, 4) }}</td>
                            <td class="px-3 py-3 text-xs text-center font-mono text-purple-800 font-medium border-r border-surface-container-high">{{ number_format($row->rq_30th, 4) }}</td>

                            {{-- Interpretasi RQ --}}
                            @php
                                $rqs = [
                                    $row->rq_realtime, $row->rq_5th, $row->rq_10th,
                                    $row->rq_15th, $row->rq_20th, $row->rq_25th, $row->rq_30th
                                ];
                            @endphp
                            @foreach($rqs as $rq)
                            <td class="px-3 py-3 text-xs text-center font-bold">
                                @if($rq > 1)
                                    <span class="px-1.5 py-0.5 bg-red-100 text-red-800 rounded font-black" title="Beresiko Tinggi (Intake > RfD)">1 (Beresiko)</span>
                                @else
                                    <span class="px-1.5 py-0.5 bg-green-100 text-green-800 rounded font-black" title="Aman (Intake &le; RfD)">2 (Aman)</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @empty
                        <tr>
                            <td colspan="34" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="p-4 bg-surface-container rounded-full text-outline">
                                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <h4 class="font-extrabold text-on-surface">Belum ada data responden untuk logam ini.</h4>
                                    <p class="text-xs text-on-surface-variant max-w-sm">Silakan gunakan tombol <span class="font-bold text-primary">"Import File"</span> untuk mengunggah Excel, atau tambahkan responden melalui tombol <span class="font-bold text-secondary">"Input Manual"</span>.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($records->hasPages())
            <div class="bg-slate-50 border border-surface-container-high rounded-lg p-4">
                {{ $records->appends(request()->query())->links() }}
            </div>
            @endif
            @else
            {{-- Dashboard Ringkasan Batch --}}
            <div class="space-y-6">
                
                {{-- KPI Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- KPI Card 1: Total Responden --}}
                    <div class="bg-white border border-surface-container-high rounded-xl p-5 shadow-sm flex items-center justify-between group hover:border-primary/30 transition-all duration-300">
                        <div class="space-y-1">
                            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Total Responden</span>
                            <h3 class="text-3xl font-black text-on-surface font-headline">{{ $totalCount }}</h3>
                            <span class="text-[10px] text-outline">Jumlah subjek penelitian terdaftar</span>
                        </div>
                        <div class="p-4 bg-primary/10 text-primary rounded-xl group-hover:scale-105 transition-transform">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 035.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    </div>

                    {{-- KPI Card 2: Logam Risiko Tertinggi --}}
                    <div class="bg-white border border-surface-container-high rounded-xl p-5 shadow-sm flex items-center justify-between group hover:border-error/30 transition-all duration-300">
                        <div class="space-y-1">
                            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Logam Risiko Tertinggi</span>
                            <h3 class="text-3xl font-black text-error font-headline line-clamp-1" title="{{ $highestRiskMetal }}">{{ $highestRiskMetal }}</h3>
                            <span class="text-[10px] text-outline">Persentase Risiko: <span class="font-bold text-error">{{ number_format($highestRiskPct, 1) }}%</span></span>
                        </div>
                        <div class="p-4 bg-error-container text-error rounded-xl group-hover:scale-105 transition-transform">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                    </div>

                    {{-- KPI Card 3: Persentase Risiko Populasi --}}
                    <div class="bg-white border border-surface-container-high rounded-xl p-5 shadow-sm flex items-center justify-between group hover:border-warning/30 transition-all duration-300">
                        <div class="space-y-1">
                            <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Risiko Populasi Realtime</span>
                            <h3 class="text-3xl font-black text-warning font-headline">
                                {{ $totalCount > 0 ? number_format(($atRiskCount / $totalCount) * 100, 1) : 0 }}%
                            </h3>
                            <span class="text-[10px] text-outline"><span class="font-bold text-warning">{{ $atRiskCount }}</span> dari {{ $totalCount }} responden berisiko</span>
                        </div>
                        <div class="p-4 bg-amber-50 text-warning rounded-xl group-hover:scale-105 transition-transform">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Chart Grid --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    {{-- Chart 1: Proyeksi 30 Tahun (Multi-Line) --}}
                    <div class="lg:col-span-2 bg-white border border-surface-container-high rounded-xl p-5 shadow-sm space-y-4">
                        <div>
                            <h4 class="font-extrabold text-on-surface text-base">Tren Proyeksi Risiko Lintas Waktu (30 Tahun)</h4>
                            <p class="text-xs text-on-surface-variant">Menunjukkan peningkatan persentase responden berisiko (RQ > 1) seiring waktu paparan.</p>
                        </div>
                        <div class="h-[350px] relative">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>

                    {{-- Chart 2: Komposisi Donat --}}
                    <div class="bg-white border border-surface-container-high rounded-xl p-5 shadow-sm flex flex-col justify-between space-y-4">
                        <div>
                            <h4 class="font-extrabold text-on-surface text-base">Status Kesehatan Populasi</h4>
                            <p class="text-xs text-on-surface-variant">Proporsi responden Aman vs Berisiko (Realtime).</p>
                        </div>
                        <div class="relative w-48 h-48 mx-auto flex items-center justify-center">
                            <canvas id="donutChart"></canvas>
                            <div class="absolute flex flex-col items-center justify-center text-center">
                                <span class="text-2xl font-black text-on-surface">
                                    {{ $totalCount > 0 ? round(($atRiskCount / $totalCount) * 100) : 0 }}%
                                </span>
                                <span class="text-[8px] font-bold text-outline uppercase tracking-wider leading-tight">Berisiko</span>
                            </div>
                        </div>
                        <div class="border-t border-surface-container-high pt-3 flex justify-around text-xs font-semibold">
                            <span class="flex items-center gap-1.5 text-error">
                                <span class="w-3 h-3 rounded bg-rose-500"></span>
                                {{ $atRiskCount }} Berisiko
                            </span>
                            <span class="flex items-center gap-1.5 text-success">
                                <span class="w-3 h-3 rounded bg-emerald-500"></span>
                                {{ $safeCount }} Aman
                            </span>
                        </div>
                    </div>

                    {{-- Chart 3: Horizontal Grouped Bar Chart (Rata-rata RQ vs Threshold) --}}
                    <div class="lg:col-span-3 bg-white border border-surface-container-high rounded-xl p-5 shadow-sm space-y-4">
                        <div>
                            <h4 class="font-extrabold text-on-surface text-base">Nilai Rata-rata Risk Quotient (RQ)</h4>
                            <p class="text-xs text-on-surface-variant">Perbandingan rata-rata tingkat bahaya (RQ) paparan Realtime vs Proyeksi 30 Tahun (Ambang batas aman = 1.0).</p>
                        </div>
                        <div class="h-[300px] relative">
                            <canvas id="barChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Summary Table per Metal --}}
                <div class="bg-white border border-surface-container-high rounded-xl p-5 shadow-sm space-y-4">
                    <div>
                        <h4 class="font-extrabold text-on-surface text-base">Rekapitulasi Parameter Logam Berat</h4>
                        <p class="text-xs text-on-surface-variant">Ringkasan statistik rata-rata konsentrasi dan risiko populasi saat ini.</p>
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-surface-container-high">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-surface-container-high text-[10px] font-black text-on-surface-variant uppercase tracking-widest text-center">
                                    <th class="px-4 py-3 text-left">Nama Logam Berat</th>
                                    <th class="px-4 py-3">Jumlah Sampel</th>
                                    <th class="px-4 py-3">Rata-rata Konsentrasi (C)</th>
                                    <th class="px-4 py-3">Rata-rata RQ (Realtime)</th>
                                    <th class="px-4 py-3">Rata-rata RQ (30 Tahun)</th>
                                    <th class="px-4 py-3">Persentase Berisiko (Realtime)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-container-high text-xs text-center font-medium">
                                @foreach(\App\Models\RqAnalysis::$pollutantLabels as $key => $label)
                                @php
                                    $data = $summaryData[$key] ?? ['count'=>0, 'avg_c'=>0, 'avg_rq_realtime'=>0, 'avg_rq_30th'=>0, 'risk_pct_realtime'=>0];
                                @endphp
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 font-extrabold text-on-surface text-left">{{ $label }}</td>
                                    <td class="px-4 py-3 text-on-surface-variant font-mono">{{ $data['count'] }}</td>
                                    <td class="px-4 py-3 text-sky-800 font-mono">{{ number_format($data['avg_c'], 5) }} mg/L</td>
                                    <td class="px-4 py-3 text-purple-800 font-mono font-bold">{{ number_format($data['avg_rq_realtime'], 4) }}</td>
                                    <td class="px-4 py-3 text-purple-800 font-mono">{{ number_format($data['avg_rq_30th'], 4) }}</td>
                                    <td class="px-4 py-3">
                                        @if($data['risk_pct_realtime'] > 0)
                                            <span class="px-2 py-0.5 rounded font-black bg-red-100 text-red-800">
                                                {{ number_format($data['risk_pct_realtime'], 1) }}% Berisiko
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded font-black bg-green-100 text-green-800">
                                                0% (Aman)
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            {{-- Chart.js Script --}}
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    
                    // 1. Line Chart: 30-Year Trend
                    const trendCtx = document.getElementById('trendChart').getContext('2d');
                    new Chart(trendCtx, {
                        type: 'line',
                        data: {
                            labels: ['Realtime', '5 Tahun', '10 Tahun', '15 Tahun', '20 Tahun', '25 Tahun', '30 Tahun'],
                            datasets: [
                                {
                                    label: 'Kromium',
                                    data: @json($trends['chromium'] ?? []),
                                    borderColor: '#6366f1',
                                    backgroundColor: 'rgba(99, 102, 241, 0.05)',
                                    fill: true,
                                    tension: 0.4,
                                    borderWidth: 3
                                },
                                {
                                    label: 'Timbal (Pb)',
                                    data: @json($trends['pb'] ?? []),
                                    borderColor: '#f43f5e',
                                    backgroundColor: 'rgba(244, 63, 94, 0.05)',
                                    fill: true,
                                    tension: 0.4,
                                    borderWidth: 3
                                },
                                {
                                    label: 'Nikel',
                                    data: @json($trends['nickel'] ?? []),
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.05)',
                                    fill: true,
                                    tension: 0.4,
                                    borderWidth: 3
                                },
                                {
                                    label: 'Arsen',
                                    data: @json($trends['arsenic'] ?? []),
                                    borderColor: '#f59e0b',
                                    backgroundColor: 'rgba(245, 158, 11, 0.05)',
                                    fill: true,
                                    tension: 0.4,
                                    borderWidth: 3
                                },
                                {
                                    label: 'Kadmium',
                                    data: @json($trends['cd'] ?? []),
                                    borderColor: '#8b5cf6',
                                    backgroundColor: 'rgba(139, 92, 246, 0.05)',
                                    fill: true,
                                    tension: 0.4,
                                    borderWidth: 3
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { font: { weight: 'bold' } }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return `${context.dataset.label}: ${context.raw}% responden berisiko`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    min: 0,
                                    max: 100,
                                    ticks: {
                                        callback: function(value) { return value + '%'; }
                                    }
                                }
                            }
                        }
                    });

                    // 2. Donut Chart: Risk Composition
                    const donutCtx = document.getElementById('donutChart').getContext('2d');
                    new Chart(donutCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Berisiko', 'Aman'],
                            datasets: [{
                                data: [{{ $atRiskCount }}, {{ $safeCount }}],
                                backgroundColor: ['#f43f5e', '#10b981'],
                                hoverOffset: 4,
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            }
                        }
                    });

                    // 3. Horizontal Bar Chart: Average RQ vs Threshold Line
                    const barCtx = document.getElementById('barChart').getContext('2d');
                    new Chart(barCtx, {
                        type: 'bar',
                        data: {
                            labels: ['Kromium', 'Timbal', 'Nikel', 'Arsen', 'Kadmium'],
                            datasets: [
                                {
                                    label: 'Rata-rata RQ Realtime',
                                    data: [
                                        {{ $summaryData['chromium']['avg_rq_realtime'] ?? 0 }},
                                        {{ $summaryData['pb']['avg_rq_realtime'] ?? 0 }},
                                        {{ $summaryData['nickel']['avg_rq_realtime'] ?? 0 }},
                                        {{ $summaryData['arsenic']['avg_rq_realtime'] ?? 0 }},
                                        {{ $summaryData['cd']['avg_rq_realtime'] ?? 0 }}
                                    ],
                                    backgroundColor: 'rgba(99, 102, 241, 0.85)',
                                    borderRadius: 4
                                },
                                {
                                    label: 'Rata-rata RQ 30 Tahun',
                                    data: [
                                        {{ $summaryData['chromium']['avg_rq_30th'] ?? 0 }},
                                        {{ $summaryData['pb']['avg_rq_30th'] ?? 0 }},
                                        {{ $summaryData['nickel']['avg_rq_30th'] ?? 0 }},
                                        {{ $summaryData['arsenic']['avg_rq_30th'] ?? 0 }},
                                        {{ $summaryData['cd']['avg_rq_30th'] ?? 0 }}
                                    ],
                                    backgroundColor: 'rgba(139, 92, 246, 0.6)',
                                    borderRadius: 4
                                }
                            ]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { font: { weight: 'bold' } }
                                }
                            },
                            scales: {
                                x: {
                                    title: { display: true, text: 'Nilai Risk Quotient (RQ)' }
                                }
                            }
                        },
                        plugins: [{
                            id: 'thresholdLine',
                            beforeDraw(chart) {
                                const { ctx, chartArea: { top, bottom }, scales: { x } } = chart;
                                ctx.save();
                                ctx.strokeStyle = '#ef4444';
                                ctx.lineWidth = 2.5;
                                ctx.setLineDash([6, 6]);
                                const xPos = x.getPixelForValue(1.0);
                                ctx.beginPath();
                                ctx.moveTo(xPos, top);
                                ctx.lineTo(xPos, bottom);
                                ctx.stroke();
                                
                                // Text label for threshold line
                                ctx.fillStyle = '#ef4444';
                                ctx.font = 'bold 9px sans-serif';
                                ctx.fillText('Ambang Batas Risiko (RQ = 1.0)', xPos + 5, top + 15);
                                ctx.restore();
                            }
                        }]
                    });

                });
            </script>
            @endif
        </div>
    </div>

    {{-- Import Modal --}}
    <div x-show="openImportModal"
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/50"
         style="display: none;">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full border border-surface-container-high overflow-hidden"
             @click.away="openImportModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="px-6 py-4 border-b border-surface-container-high flex justify-between items-center">
                <h3 class="font-extrabold text-on-surface text-base">Import Data Responden</h3>
                <button @click="openImportModal = false" class="p-1 hover:bg-surface-container rounded-lg text-outline">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('analisis.batch.import', $batch->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="pollutant_type" value="{{ $pollutant }}">

                <div class="p-6 space-y-4" x-data="{ type: 'all_sheets' }">
                    {{-- Opsi Tipe Impor --}}
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-2">Tipe Unggahan File</label>
                        <div class="grid @if($pollutant === 'dashboard') grid-cols-1 @else grid-cols-2 @endif gap-3">
                            <label class="flex flex-col items-center justify-between p-3 bg-slate-50 border border-surface-container-high rounded-xl cursor-pointer hover:border-primary/50 transition-all text-center">
                                <input type="radio" name="import_type" value="all_sheets" x-model="type" class="text-primary focus:ring-primary">
                                <span class="text-xs font-black text-on-surface mt-2">Semua Logam</span>
                                <span class="text-[9px] text-outline mt-1 leading-tight">Proses 5 sheet logam dari berkas Master Excel sekaligus</span>
                            </label>
                            @if($pollutant !== 'dashboard')
                            <label class="flex flex-col items-center justify-between p-3 bg-slate-50 border border-surface-container-high rounded-xl cursor-pointer hover:border-primary/50 transition-all text-center">
                                <input type="radio" name="import_type" value="active_tab" x-model="type" class="text-primary focus:ring-primary">
                                <span class="text-xs font-black text-on-surface mt-2">Hanya Logam Aktif</span>
                                <span class="text-[9px] text-outline mt-1 leading-tight">Impor satu lembar khusus logam aktif saat ini</span>
                            </label>
                            @endif
                        </div>
                    </div>

                    {{-- File Input --}}
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Pilih Berkas (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file" required
                               class="w-full bg-surface-container-low border border-surface-container-high text-on-surface text-sm rounded-lg block p-2 outline-none">
                    </div>

                    {{-- Download Template Link --}}
                    <div class="pt-2 flex justify-between items-center text-xs">
                        <span class="text-on-surface-variant">Membutuhkan contoh format?</span>
                        <a href="/templates/template_import.csv" download class="text-primary font-bold hover:underline inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Unduh Template CSV
                        </a>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-surface-container-high flex justify-end gap-2.5">
                    <button type="button" @click="openImportModal = false"
                            class="px-4 py-2 border border-surface-container-high hover:bg-surface-container text-on-surface-variant rounded-lg text-sm font-bold transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-primary text-on-primary hover:brightness-110 rounded-lg text-sm font-bold transition-all shadow-sm">
                        Mulai Impor
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Manual Input Modal --}}
    @if($pollutant !== 'dashboard')
    <div x-show="openManualModal"
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/50"
         style="display: none;">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full border border-surface-container-high overflow-hidden"
             @click.away="openManualModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="px-6 py-4 border-b border-surface-container-high flex justify-between items-center">
                <h3 class="font-extrabold text-on-surface text-base">Input Responden Manual — {{ \App\Models\RqAnalysis::$pollutantLabels[$pollutant] }}</h3>
                <button @click="openManualModal = false" class="p-1 hover:bg-surface-container rounded-lg text-outline">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('analisis.batch.store_manual', $batch->id) }}" method="POST">
                @csrf
                <input type="hidden" name="pollutant_type" value="{{ $pollutant }}">

                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[70vh] overflow-y-auto">
                    {{-- Nama --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-on-surface-variant mb-1">Nama Responden</label>
                        <input type="text" name="nama" required placeholder="Contoh: Budi Santoso"
                               class="w-full bg-surface-container-low border border-surface-container-high text-on-surface text-sm rounded-lg block p-2 outline-none focus:ring-1 focus:ring-primary/30 focus:border-primary">
                    </div>

                    {{-- Umur --}}
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1">Umur (tahun)</label>
                        <input type="number" name="umur" required step="1" min="0" placeholder="Contoh: 35"
                               class="w-full bg-surface-container-low border border-surface-container-high text-on-surface text-sm rounded-lg block p-2 outline-none focus:ring-1 focus:ring-primary/30 focus:border-primary">
                    </div>

                    {{-- Wb --}}
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1">Wb (Berat Badan - kg)</label>
                        <input type="number" name="wb" required step="0.1" min="0.1" placeholder="Contoh: 60"
                               class="w-full bg-surface-container-low border border-surface-container-high text-on-surface text-sm rounded-lg block p-2 outline-none focus:ring-1 focus:ring-primary/30 focus:border-primary">
                    </div>

                    {{-- f --}}
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1">f (Frekuensi Pajanan - hari/thn)</label>
                        <input type="number" name="f" value="365" required step="1" min="1"
                               class="w-full bg-surface-container-low border border-surface-container-high text-on-surface text-sm rounded-lg block p-2 outline-none focus:ring-1 focus:ring-primary/30 focus:border-primary">
                    </div>

                    {{-- C --}}
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1">C (Konsentrasi - mg/L)</label>
                        <input type="number" name="c" required step="0.00001" min="0" placeholder="Contoh: 0.0044"
                               class="w-full bg-surface-container-low border border-surface-container-high text-on-surface text-sm rounded-lg block p-2 outline-none focus:ring-1 focus:ring-primary/30 focus:border-primary">
                    </div>

                    {{-- R --}}
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1">R (Laju Asupan - L/hari)</label>
                        <input type="number" name="r" required step="0.01" min="0" placeholder="Contoh: 2.0"
                               class="w-full bg-surface-container-low border border-surface-container-high text-on-surface text-sm rounded-lg block p-2 outline-none focus:ring-1 focus:ring-primary/30 focus:border-primary">
                    </div>

                    {{-- RfD --}}
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1">RfD (Dosis Acuan - mg/kg/hari)</label>
                        <input type="number" name="rfd" value="{{ $rfdDefault }}" step="0.00001" min="0"
                               class="w-full bg-surface-container-low border border-surface-container-high text-on-surface text-sm rounded-lg block p-2 outline-none focus:ring-1 focus:ring-primary/30 focus:border-primary">
                        <span class="text-[9px] text-outline">Biarkan default atau sesuaikan.</span>
                    </div>

                    {{-- tavg --}}
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1">tavg (Rata-rata Waktu - hari)</label>
                        <input type="number" name="tavg" value="10950" required step="1" min="1"
                               class="w-full bg-surface-container-low border border-surface-container-high text-on-surface text-sm rounded-lg block p-2 outline-none focus:ring-1 focus:ring-primary/30 focus:border-primary">
                        <span class="text-[9px] text-outline">Dewasa: 10950 hari, Anak: 4380 hari</span>
                    </div>

                    {{-- Dt --}}
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1">Dt (Durasi Pajanan - tahun)</label>
                        <input type="number" name="dt_input" required step="0.1" min="0.1" placeholder="Contoh: 20"
                               class="w-full bg-surface-container-low border border-surface-container-high text-on-surface text-sm rounded-lg block p-2 outline-none focus:ring-1 focus:ring-primary/30 focus:border-primary">
                    </div>

                    {{-- Latitude --}}
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1">Latitude (Opsional)</label>
                        <input type="number" name="latitude" step="0.00000001" min="-90" max="90" placeholder="Contoh: -6.2301"
                               class="w-full bg-surface-container-low border border-surface-container-high text-on-surface text-sm rounded-lg block p-2 outline-none focus:ring-1 focus:ring-primary/30 focus:border-primary">
                    </div>

                    {{-- Longitude --}}
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1">Longitude (Opsional)</label>
                        <input type="number" name="longitude" step="0.00000001" min="-180" max="180" placeholder="Contoh: 106.8402"
                               class="w-full bg-surface-container-low border border-surface-container-high text-on-surface text-sm rounded-lg block p-2 outline-none focus:ring-1 focus:ring-primary/30 focus:border-primary">
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-surface-container-high flex justify-end gap-2.5">
                    <button type="button" @click="openManualModal = false"
                            class="px-4 py-2 border border-surface-container-high hover:bg-surface-container text-on-surface-variant rounded-lg text-sm font-bold transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-primary text-on-primary hover:brightness-110 rounded-lg text-sm font-bold transition-all shadow-sm">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>

@if($pollutant !== 'dashboard' && $mapRecords->isNotEmpty())
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const records = @json($mapRecords);
        if (records.length === 0) return;

        // Calculate map center (average of all latitudes & longitudes)
        let totalLat = 0, totalLng = 0;
        records.forEach(r => {
            totalLat += parseFloat(r.latitude);
            totalLng += parseFloat(r.longitude);
        });
        const centerLat = totalLat / records.length;
        const centerLng = totalLng / records.length;

        // Inisialisasi peta dengan menonaktifkan kontrol atribusi default agar posisinya bisa kita atur manual
        const map = L.map('mapSebaran', {
            center: [centerLat, centerLng],
            zoom: 14,
            zoomControl: true,
            attributionControl: false
        });

        // Aktifkan kembali kontrol atribusi pada posisi kiri bawah (bottomleft)
        L.control.attribution({ position: 'bottomleft' }).addTo(map);

        // 1. Definisikan Base Layers (Peta Standar & Citra Satelit)
        const streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        });

        const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19,
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
        });

        // Set default layer aktif ke Peta Standar
        streetLayer.addTo(map);

        // 2. Tambahkan Kontrol Switcher Layer di kanan atas
        const baseMaps = {
            "Peta Standar": streetLayer,
            "Satelit": satelliteLayer
        };

        L.control.layers(baseMaps, null, {
            position: 'topright',
            collapsed: false // Menampilkan menu secara terbuka agar mudah diakses
        }).addTo(map);

        // Inisialisasi Marker Cluster Group dengan konfigurasi kustom
        const clusterGroup = L.markerClusterGroup({
            maxClusterRadius: 45,
            iconCreateFunction: function(cluster) {
                const childMarkers = cluster.getAllChildMarkers();
                let atRiskCount = 0;
                
                childMarkers.forEach(m => {
                    if (m.options.isAtRisk) {
                        atRiskCount++;
                    }
                });

                const totalCount = childMarkers.length;
                const ratio = atRiskCount / totalCount;

                // Tentukan warna cluster secara proporsional terhadap tingkat risiko responden di dalamnya
                let color = '#10b981'; // Hijau (<20% berisiko)
                if (ratio > 0.5) {
                    color = '#ef4444'; // Merah (>50% berisiko)
                } else if (ratio >= 0.2) {
                    color = '#f59e0b'; // Oranye (20% s.d 50% berisiko)
                }

                return L.divIcon({
                    html: `
                        <div class="custom-cluster" style="background-color: ${color}26; border: 2px solid ${color};">
                            <span style="color: ${color};">${totalCount}</span>
                        </div>
                    `,
                    className: 'custom-cluster-wrapper',
                    iconSize: [36, 36],
                    iconAnchor: [18, 18]
                });
            }
        });

        // Plot data responden ke dalam kluster
        records.forEach(r => {
            const isAtRisk = parseFloat(r.rq_realtime) > 1.0;
            const colorClass = isAtRisk ? 'red' : 'green';
            const colorHex = isAtRisk ? '#ef4444' : '#10b981';

            const radarIcon = L.divIcon({
                className: '',
                html: `
                    <div class="radar-container">
                        <div class="radar-pulse ${colorClass}"></div>
                        <div class="radar-dot ${colorClass}"></div>
                    </div>
                `,
                iconSize: [32, 32],
                iconAnchor: [16, 16],
                popupAnchor: [0, -10]
            });

            // Daftarkan properti custom isAtRisk agar bisa dibaca oleh cluster icon creator
            const marker = L.marker([r.latitude, r.longitude], { 
                icon: radarIcon,
                isAtRisk: isAtRisk 
            });

            // Bind popup klinis responden
            marker.bindPopup(`
                <div style="font-family: 'Inter', sans-serif; min-width: 150px;">
                    <div style="font-weight: 800; font-size: 13px; color: #191c1e; margin-bottom: 2px;">
                        ${r.nama} (No. ${r.no_responden})
                    </div>
                    <div style="font-size: 11px; color: #6d7a72; margin-bottom: 6px;">
                        Usia: ${r.umur} thn | BB: ${r.wb} kg
                    </div>
                    <div style="border-top: 1px solid #e0e3e5; padding-top: 6px; margin-top: 4px;">
                        <span style="font-size: 11px; font-weight: 600; color: #191c1e;">
                            RQ Realtime: <span style="font-weight: 800; color: ${colorHex};">${parseFloat(r.rq_realtime).toFixed(4)}</span>
                        </span>
                    </div>
                    <div style="margin-top: 6px; padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: 700; text-align: center; 
                                background: ${isAtRisk ? '#fde8e8' : '#e6f4ea'}; color: ${colorHex};">
                        ${isAtRisk ? 'ZONA BERISIKO (RQ > 1)' : 'ZONA AMAN (RQ ≤ 1)'}
                    </div>
                </div>
            `);

            clusterGroup.addLayer(marker);
        });

        // Masukkan group cluster ke dalam peta utama
        map.addLayer(clusterGroup);

        // Tambahkan Legenda Kategori Risiko di kanan bawah (bottomright)
        const legend = L.control({ position: 'bottomright' });
        legend.onAdd = function() {
            const div = L.DomUtil.create('div', 'map-legend');
            div.style.background = '#ffffff';
            div.style.padding = '10px 12px';
            div.style.borderRadius = '8px';
            div.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
            div.style.fontFamily = "'Inter', sans-serif";
            div.style.fontSize = '11px';
            div.style.fontWeight = '700';
            div.style.color = '#1c1b1f'; // Material Design 3 On Surface
            div.style.border = '1px solid #e0e3e5';
            div.style.display = 'flex';
            div.style.flexDirection = 'column';
            div.style.gap = '6px';
            
            div.innerHTML = `
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444; border: 1.5px solid white; box-shadow: 0 0 0 1px #ef4444; display: inline-block;"></span>
                    <span>Berisiko (RQ > 1)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; border: 1.5px solid white; box-shadow: 0 0 0 1px #10b981; display: inline-block;"></span>
                    <span>Aman (RQ ≤ 1)</span>
                </div>
            `;
            return div;
        };
        legend.addTo(map);
    });
</script>
@endpush
@endif
@endsection
