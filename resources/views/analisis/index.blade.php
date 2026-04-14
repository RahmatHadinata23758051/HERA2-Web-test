@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Analisis Data Excel: {{ $label }}</h1>
            <p class="text-gray-400 text-sm mt-1">Estimasi Intake (Laju Asupan) & Risk Quotient untuk Proyeksi 30 Tahun.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('analisis.export', $type) }}" class="flex items-center gap-2 px-4 py-2 bg-emerald-600/20 text-emerald-400 border border-emerald-500/30 rounded-lg hover:bg-emerald-600/40 transition-colors font-medium text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Export Excel
            </a>
            
            <button @click="$dispatch('open-import-modal')" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition-colors shadow-lg shadow-blue-500/20 font-medium text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                Import Excel
            </button>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="glass-card rounded-xl p-4 flex items-start gap-4">
        <div class="p-2 bg-blue-500/20 rounded-lg">
            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h1m0-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <h3 class="font-medium text-gray-200">Panduan Import Data</h3>
            <p class="text-sm text-gray-400 mt-1">
                Pastikan file excel (.xlsx) Anda memiliki header kolom berikut: 
                <code class="px-1.5 py-0.5 rounded bg-gray-800 text-purple-300">nama</code>, <code class="px-1.5 py-0.5 rounded bg-gray-800 text-purple-300">umur</code>, <code class="px-1.5 py-0.5 rounded bg-gray-800 text-purple-300">wb</code>, <code class="px-1.5 py-0.5 rounded bg-gray-800 text-purple-300">f</code>, <code class="px-1.5 py-0.5 rounded bg-gray-800 text-purple-300">c</code>, <code class="px-1.5 py-0.5 rounded bg-gray-800 text-purple-300">r</code>, <code class="px-1.5 py-0.5 rounded bg-gray-800 text-purple-300">tavg</code>, <code class="px-1.5 py-0.5 rounded bg-gray-800 text-purple-300">dt_realtime</code>.
                <br>Kalkulasi Intake & RQ untuk 5-30 tahun akan dihitung otomatis oleh sistem menggunakan default RfD <span class="text-blue-300 font-bold">{{ $rfdDefault }}</span> (jika headernya tidak disuplai).
            </p>
        </div>
    </div>

    <!-- Data Table -->
    <div class="glass-card rounded-xl overflow-hidden border border-gray-800/60 shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[2000px]">
                <thead>
                    <tr class="bg-gray-800/80 border-b border-gray-700">
                        <th rowspan="2" class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center border-r border-gray-700/50">Aksi</th>
                        <th colspan="4" class="px-4 py-2 text-xs font-semibold text-gray-300 uppercase tracking-wider text-center border-b border-r border-gray-700/50 bg-gray-800/40">Data Responden</th>
                        <th colspan="6" class="px-4 py-2 text-xs font-semibold text-blue-300 uppercase tracking-wider text-center border-b border-r border-gray-700/50 bg-blue-900/10">Variabel Pajanan</th>
                        <th colspan="7" class="px-4 py-2 text-xs font-semibold text-emerald-300 uppercase tracking-wider text-center border-b border-r border-gray-700/50 bg-emerald-900/10">Kalkulasi Intake (I)</th>
                        <th colspan="7" class="px-4 py-2 text-xs font-semibold text-purple-300 uppercase tracking-wider text-center border-b border-gray-700/50 bg-purple-900/10">Kalkulasi Risk Quotient (RQ)</th>
                    </tr>
                    <tr class="bg-gray-800/50 border-b border-gray-700">
                        <!-- Data -->
                        <th class="px-4 py-2 text-xs font-medium text-gray-400 whitespace-nowrap">No</th>
                        <th class="px-4 py-2 text-xs font-medium text-gray-400 whitespace-nowrap">Nama</th>
                        <th class="px-4 py-2 text-xs font-medium text-gray-400 whitespace-nowrap">Umur</th>
                        <th class="px-4 py-2 text-xs font-medium text-gray-400 whitespace-nowrap border-r border-gray-700/50">Wb</th>
                        <!-- Input -->
                        <th class="px-4 py-2 text-xs font-medium text-blue-400 whitespace-nowrap">f</th>
                        <th class="px-4 py-2 text-xs font-medium text-blue-400 whitespace-nowrap">C</th>
                        <th class="px-4 py-2 text-xs font-medium text-blue-400 whitespace-nowrap">R</th>
                        <th class="px-4 py-2 text-xs font-medium text-blue-400 whitespace-nowrap">RfD</th>
                        <th class="px-4 py-2 text-xs font-medium text-blue-400 whitespace-nowrap">tavg</th>
                        <th class="px-4 py-2 text-xs font-medium text-blue-400 whitespace-nowrap border-r border-gray-700/50">Dt</th>
                        <!-- Intake -->
                        <th class="px-4 py-2 text-xs font-medium text-emerald-400 whitespace-nowrap">Realtime</th>
                        <th class="px-4 py-2 text-xs font-medium text-emerald-400 whitespace-nowrap">5 th</th>
                        <th class="px-4 py-2 text-xs font-medium text-emerald-400 whitespace-nowrap">10 th</th>
                        <th class="px-4 py-2 text-xs font-medium text-emerald-400 whitespace-nowrap">15 th</th>
                        <th class="px-4 py-2 text-xs font-medium text-emerald-400 whitespace-nowrap">20 th</th>
                        <th class="px-4 py-2 text-xs font-medium text-emerald-400 whitespace-nowrap">25 th</th>
                        <th class="px-4 py-2 text-xs font-medium text-emerald-400 whitespace-nowrap border-r border-gray-700/50">30 th</th>
                        <!-- RQ -->
                        <th class="px-4 py-2 text-xs font-medium text-purple-400 whitespace-nowrap">Realtime</th>
                        <th class="px-4 py-2 text-xs font-medium text-purple-400 whitespace-nowrap">5 th</th>
                        <th class="px-4 py-2 text-xs font-medium text-purple-400 whitespace-nowrap">10 th</th>
                        <th class="px-4 py-2 text-xs font-medium text-purple-400 whitespace-nowrap">15 th</th>
                        <th class="px-4 py-2 text-xs font-medium text-purple-400 whitespace-nowrap">20 th</th>
                        <th class="px-4 py-2 text-xs font-medium text-purple-400 whitespace-nowrap">25 th</th>
                        <th class="px-4 py-2 text-xs font-medium text-purple-400 whitespace-nowrap">30 th</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/50">
                    @forelse($records as $row)
                    <tr class="hover:bg-gray-800/30 transition-colors">
                        <td class="px-4 py-3 border-r border-gray-700/50 text-center flex flex-col sm:flex-row gap-2 justify-center items-center h-full min-h-[48px]">
                            <a href="{{ route('analisis.edit', $row->id) }}" class="text-blue-400 hover:text-blue-300 hover:scale-110 transition-transform" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <div class="w-px h-4 bg-gray-700 hidden sm:block"></div>
                            <form action="{{ route('analisis.destroy', $row->id) }}" method="POST" onsubmit="return confirm('Hapus data responden ini?');" class="flex">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-300 hover:scale-110 transition-transform" title="Hapus">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-300 font-mono">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 text-sm text-gray-200 font-medium whitespace-nowrap">{{ $row->nama }}</td>
                        <td class="px-4 py-3 text-sm text-gray-400">{{ $row->umur }}</td>
                        <td class="px-4 py-3 text-sm text-gray-400 border-r border-gray-700/50">{{ $row->wb }}</td>
                        
                        <td class="px-4 py-3 text-sm text-blue-200">{{ $row->f }}</td>
                        <td class="px-4 py-3 text-sm text-blue-200">{{ $row->c }}</td>
                        <td class="px-4 py-3 text-sm text-blue-200">{{ $row->r }}</td>
                        <td class="px-4 py-3 text-sm text-blue-200">{{ $row->rfd }}</td>
                        <td class="px-4 py-3 text-sm text-blue-200">{{ $row->tavg }}</td>
                        <td class="px-4 py-3 text-sm text-blue-200 border-r border-gray-700/50">{{ $row->dt_input }}</td>

                        <td class="px-4 py-3 text-sm font-mono text-emerald-200">{{ $row->intake_realtime }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-200">{{ $row->intake_5th }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-200">{{ $row->intake_10th }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-200">{{ $row->intake_15th }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-200">{{ $row->intake_20th }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-200">{{ $row->intake_25th }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-emerald-200 border-r border-gray-700/50">{{ $row->intake_30th }}</td>

                        <!-- Highlight merah jika RQ > 1 (Berisiko) -->
                        <td class="px-4 py-3 text-sm font-mono font-bold {{ $row->rq_realtime > 1 ? 'text-red-400' : 'text-purple-200' }}">{{ $row->rq_realtime }}</td>
                        <td class="px-4 py-3 text-sm font-mono font-bold {{ $row->rq_5th > 1 ? 'text-red-400' : 'text-purple-200' }}">{{ $row->rq_5th }}</td>
                        <td class="px-4 py-3 text-sm font-mono font-bold {{ $row->rq_10th > 1 ? 'text-red-400' : 'text-purple-200' }}">{{ $row->rq_10th }}</td>
                        <td class="px-4 py-3 text-sm font-mono font-bold {{ $row->rq_15th > 1 ? 'text-red-400' : 'text-purple-200' }}">{{ $row->rq_15th }}</td>
                        <td class="px-4 py-3 text-sm font-mono font-bold {{ $row->rq_20th > 1 ? 'text-red-400' : 'text-purple-200' }}">{{ $row->rq_20th }}</td>
                        <td class="px-4 py-3 text-sm font-mono font-bold {{ $row->rq_25th > 1 ? 'text-red-400' : 'text-purple-200' }}">{{ $row->rq_25th }}</td>
                        <td class="px-4 py-3 text-sm font-mono font-bold {{ $row->rq_30th > 1 ? 'text-red-400' : 'text-purple-200' }}">{{ $row->rq_30th }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="25" class="px-4 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <p class="text-lg font-medium">Belum ada data responden.</p>
                            <p class="mt-1">Silakan klik "Import Excel" untuk memulai analisis massal.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Import -->
    <div x-data="{ open: false }" @open-import-modal.window="open = true" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity"></div>

        <div x-show="open" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div @click.away="open = false" class="relative transform overflow-hidden rounded-xl glass-card text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-700">
                    <form action="{{ route('analisis.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="pollutant_type" value="{{ $type }}">
                        <div class="px-6 py-6 pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-500/20 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                    <h3 class="text-lg font-semibold leading-6 text-white" id="modal-title">Upload Data Excel</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-400">Pilih file `.xlsx` atau `.csv` yang berisi row responden. Pastikan header sesuai panduan.</p>
                                    </div>
                                    <div class="mt-4">
                                        <label class="block">
                                            <span class="sr-only">Choose profile photo</span>
                                            <input type="file" name="file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required
                                                class="block w-full text-sm text-gray-400
                                                file:mr-4 file:py-2.5 file:px-4
                                                file:rounded-lg file:border-0
                                                file:text-sm file:font-semibold
                                                file:bg-blue-600/20 file:text-blue-400
                                                hover:file:bg-blue-600/30
                                                border border-gray-700 rounded-lg bg-gray-800/50 cursor-pointer
                                                "/>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-800/50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-gray-700/50">
                            <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto transition-colors">Import & Kalkulasi</button>
                            <button type="button" @click="open = false" class="mt-3 inline-flex w-full justify-center rounded-lg bg-transparent border border-gray-600 px-3 py-2 text-sm font-semibold text-gray-300 shadow-sm hover:bg-gray-700 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
