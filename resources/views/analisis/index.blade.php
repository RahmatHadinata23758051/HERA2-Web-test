@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 rounded-xl shadow-sm border border-surface-container-high">
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-on-surface font-headline">
                Analisis Data Excel: <span class="text-primary">{{ $label }}</span>
            </h2>
            <p class="text-on-surface-variant text-sm mt-1">Estimasi Intake (Laju Asupan) & Risk Quotient untuk Proyeksi 30 Tahun.</p>
        </div>
        
        <div class="flex items-center gap-3 flex-shrink-0">
            {{-- Add Manual --}}
            <a href="{{ route('analisis.input') }}?type={{ $type }}"
               class="flex items-center gap-2 px-4 py-2 bg-surface-container-low text-on-surface-variant border border-surface-container-high rounded-lg hover:bg-surface-container hover:text-primary transition-colors font-medium text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Input Manual
            </a>
            
            {{-- Export --}}
            <a href="{{ route('analisis.export', $type) }}"
               class="flex items-center gap-2 px-4 py-2 bg-secondary-container text-secondary border border-secondary-container rounded-lg hover:brightness-95 transition-all font-medium text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export Excel
            </a>
            
            {{-- Import Excel --}}
            <button @click="$dispatch('open-import-modal')"
                    class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary rounded-lg hover:brightness-110 transition-all shadow-sm font-medium text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                Import Excel
            </button>
        </div>
    </div>

    {{-- Info Banner --}}
    <div class="bg-primary/5 border border-primary/20 rounded-xl p-4 flex items-start gap-4">
        <div class="p-2 bg-primary/10 rounded-lg flex-shrink-0">
            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h1m0-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <h3 class="font-bold text-primary text-sm">Panduan Import Data</h3>
            <p class="text-sm text-on-surface-variant mt-1">
                Pastikan file excel <code class="px-1.5 py-0.5 rounded bg-surface-container text-primary font-mono text-xs">.xlsx</code> memiliki header: 
                @foreach(['nama','umur','wb','f','c','r','tavg','dt_realtime'] as $col)
                    <code class="px-1.5 py-0.5 rounded bg-surface-container text-secondary font-mono text-xs">{{ $col }}</code>{{ !$loop->last ? ',' : '.' }}
                @endforeach
                <br>Kalkulasi Intake & RQ 5–30 tahun dihitung otomatis. Default RfD: <span class="text-primary font-bold">{{ $rfdDefault }}</span>
            </p>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-surface-container-high">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[2000px]">
                <thead>
                    {{-- Group Row --}}
                    <tr class="border-b border-surface-container-high bg-surface-container-low">
                        <th rowspan="2" class="px-4 py-3 text-[10px] font-black text-on-surface-variant uppercase tracking-widest text-center border-r border-surface-container-high bg-surface-container">
                            Aksi
                        </th>
                        <th colspan="4" class="px-4 py-2.5 text-[10px] font-black text-on-surface-variant uppercase tracking-widest text-center border-b border-r border-surface-container-high bg-slate-50">
                            Data Responden
                        </th>
                        <th colspan="6" class="px-4 py-2.5 text-[10px] font-black text-sky-700 uppercase tracking-widest text-center border-b border-r border-surface-container-high bg-sky-50">
                            Variabel Pajanan
                        </th>
                        <th colspan="7" class="px-4 py-2.5 text-[10px] font-black text-emerald-700 uppercase tracking-widest text-center border-b border-r border-surface-container-high bg-emerald-50">
                            Kalkulasi Intake (I)
                        </th>
                        <th colspan="7" class="px-4 py-2.5 text-[10px] font-black text-purple-700 uppercase tracking-widest text-center border-b border-surface-container-high bg-purple-50">
                            Kalkulasi Risk Quotient (RQ)
                        </th>
                    </tr>
                    {{-- Sub-column Row --}}
                    <tr class="border-b-2 border-surface-container-high text-[10px] font-bold uppercase tracking-wider">
                        {{-- Responden --}}
                        <th class="px-4 py-2 text-on-surface-variant bg-slate-50 whitespace-nowrap">No</th>
                        <th class="px-4 py-2 text-on-surface-variant bg-slate-50 whitespace-nowrap">Nama</th>
                        <th class="px-4 py-2 text-on-surface-variant bg-slate-50 whitespace-nowrap">Umur</th>
                        <th class="px-4 py-2 text-on-surface-variant bg-slate-50 whitespace-nowrap border-r border-surface-container-high">Wb</th>
                        {{-- Input --}}
                        <th class="px-4 py-2 text-sky-600 bg-sky-50 whitespace-nowrap">f</th>
                        <th class="px-4 py-2 text-sky-600 bg-sky-50 whitespace-nowrap">C</th>
                        <th class="px-4 py-2 text-sky-600 bg-sky-50 whitespace-nowrap">R</th>
                        <th class="px-4 py-2 text-sky-600 bg-sky-50 whitespace-nowrap">RfD</th>
                        <th class="px-4 py-2 text-sky-600 bg-sky-50 whitespace-nowrap">tavg</th>
                        <th class="px-4 py-2 text-sky-600 bg-sky-50 whitespace-nowrap border-r border-surface-container-high">Dt</th>
                        {{-- Intake --}}
                        <th class="px-4 py-2 text-emerald-700 bg-emerald-50 whitespace-nowrap">Realtime</th>
                        <th class="px-4 py-2 text-emerald-700 bg-emerald-50 whitespace-nowrap">5 th</th>
                        <th class="px-4 py-2 text-emerald-700 bg-emerald-50 whitespace-nowrap">10 th</th>
                        <th class="px-4 py-2 text-emerald-700 bg-emerald-50 whitespace-nowrap">15 th</th>
                        <th class="px-4 py-2 text-emerald-700 bg-emerald-50 whitespace-nowrap">20 th</th>
                        <th class="px-4 py-2 text-emerald-700 bg-emerald-50 whitespace-nowrap">25 th</th>
                        <th class="px-4 py-2 text-emerald-700 bg-emerald-50 whitespace-nowrap border-r border-surface-container-high">30 th</th>
                        {{-- RQ --}}
                        <th class="px-4 py-2 text-purple-700 bg-purple-50 whitespace-nowrap">Realtime</th>
                        <th class="px-4 py-2 text-purple-700 bg-purple-50 whitespace-nowrap">5 th</th>
                        <th class="px-4 py-2 text-purple-700 bg-purple-50 whitespace-nowrap">10 th</th>
                        <th class="px-4 py-2 text-purple-700 bg-purple-50 whitespace-nowrap">15 th</th>
                        <th class="px-4 py-2 text-purple-700 bg-purple-50 whitespace-nowrap">20 th</th>
                        <th class="px-4 py-2 text-purple-700 bg-purple-50 whitespace-nowrap">25 th</th>
                        <th class="px-4 py-2 text-purple-700 bg-purple-50 whitespace-nowrap">30 th</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-surface-container-high">
                    @forelse($records as $row)
                    <tr class="hover:bg-surface-container-low transition-colors group">
                        {{-- Aksi --}}
                        <td class="px-4 py-3 border-r border-surface-container-high text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('analisis.edit', $row->id) }}"
                                   class="p-1.5 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-lg transition-all" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('analisis.destroy', $row->id) }}" method="POST"
                                      onsubmit="return confirm('Hapus data responden ini?');" class="flex">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 text-on-surface-variant hover:text-error hover:bg-error-container rounded-lg transition-all" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>

                        {{-- Data Responden --}}
                        <td class="px-4 py-3 text-sm text-on-surface-variant font-mono">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 text-sm text-on-surface font-bold whitespace-nowrap">{{ $row->nama }}</td>
                        <td class="px-4 py-3 text-sm text-on-surface-variant">{{ $row->umur }}</td>
                        <td class="px-4 py-3 text-sm text-on-surface-variant border-r border-surface-container-high">{{ $row->wb }}</td>

                        {{-- Variabel Pajanan --}}
                        <td class="px-4 py-3 text-sm text-sky-700 font-medium">{{ $row->f }}</td>
                        <td class="px-4 py-3 text-sm text-sky-700 font-medium">{{ $row->c }}</td>
                        <td class="px-4 py-3 text-sm text-sky-700 font-medium">{{ $row->r }}</td>
                        <td class="px-4 py-3 text-sm text-sky-700 font-medium">{{ $row->rfd }}</td>
                        <td class="px-4 py-3 text-sm text-sky-700 font-medium">{{ $row->tavg }}</td>
                        <td class="px-4 py-3 text-sm text-sky-700 font-medium border-r border-surface-container-high">{{ $row->dt_input }}</td>

                        {{-- Kalkulasi Intake --}}
                        <td class="px-4 py-3 text-sm font-mono text-emerald-700">{{ $row->intake_realtime }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-700">{{ $row->intake_5th }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-700">{{ $row->intake_10th }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-700">{{ $row->intake_15th }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-700">{{ $row->intake_20th }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-700">{{ $row->intake_25th }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-700 border-r border-surface-container-high">{{ $row->intake_30th }}</td>

                        {{-- RQ — Merah jika > 1 (Berisiko) --}}
                        @php
                            $rqs = [
                                $row->rq_realtime, $row->rq_5th, $row->rq_10th,
                                $row->rq_15th, $row->rq_20th, $row->rq_25th, $row->rq_30th
                            ];
                        @endphp
                        @foreach($rqs as $i => $rq)
                        <td class="px-4 py-3 text-sm font-mono font-bold">
                            @if($rq > 1)
                                <span class="px-2 py-0.5 bg-error-container text-error rounded-md">{{ $rq }}</span>
                            @else
                                <span class="text-purple-700">{{ $rq }}</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="25" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="p-4 bg-surface-container rounded-full">
                                    <svg class="h-10 w-10 text-outline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-lg font-bold text-on-surface">Belum ada data responden.</p>
                                <p class="text-sm text-on-surface-variant">Klik <span class="font-bold text-primary">"Import Excel"</span> untuk memulai analisis massal.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Import --}}
    <div x-data="{ open: false }" @open-import-modal.window="open = true" class="relative z-50" role="dialog" aria-modal="true">
        
        {{-- Backdrop --}}
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-on-surface/30 backdrop-blur-sm z-40" style="display:none;"></div>

        {{-- Modal Panel --}}
        <div x-show="open"
             x-transition:enter="ease-out duration-250"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="display:none;">
            
            <div @click.away="open = false"
                 class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-surface-container-high overflow-hidden">
                
                {{-- Modal Header --}}
                <div class="flex items-center gap-4 px-6 py-5 border-b border-surface-container-high bg-surface-container-lowest">
                    <div class="p-2.5 bg-primary/10 rounded-xl">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-on-surface">Upload Data Excel</h3>
                        <p class="text-xs text-on-surface-variant mt-0.5">Pilih file <code class="bg-surface-container px-1 rounded">.xlsx</code> atau <code class="bg-surface-container px-1 rounded">.csv</code></p>
                    </div>
                    <button @click="open = false" class="ml-auto p-1.5 text-on-surface-variant hover:text-on-surface hover:bg-surface-container rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal Form --}}
                <form action="{{ route('analisis.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="pollutant_type" value="{{ $type }}">

                    <div class="px-6 py-6">
                        <p class="text-sm text-on-surface-variant mb-4">
                            Pastikan header kolom sesuai panduan di atas. Sistem akan kalkulasi Intake & RQ secara otomatis.
                        </p>
                        <label class="block">
                            <span class="sr-only">Pilih file Excel</span>
                            <input type="file" name="file"
                                   accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                                   required
                                   class="block w-full text-sm text-on-surface-variant border border-surface-container-high rounded-xl bg-surface-container-low cursor-pointer
                                          file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0
                                          file:text-sm file:font-bold file:bg-primary file:text-on-primary
                                          hover:file:brightness-110 file:cursor-pointer file:transition-all"/>
                        </label>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex flex-row-reverse gap-3 px-6 py-4 border-t border-surface-container-high bg-surface-container-lowest">
                        <button type="submit"
                                class="px-5 py-2.5 bg-primary text-on-primary text-sm font-bold rounded-lg hover:brightness-110 transition-all shadow-sm">
                            Import & Kalkulasi
                        </button>
                        <button type="button" @click="open = false"
                                class="px-5 py-2.5 bg-surface-container text-on-surface-variant text-sm font-bold rounded-lg hover:bg-surface-container-high transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
