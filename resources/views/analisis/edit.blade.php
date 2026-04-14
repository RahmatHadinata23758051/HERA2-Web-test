@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <div class="flex items-center gap-3">
            <a href="{{ route('analisis.rq.' . $record->pollutant_type) }}" class="p-1.5 rounded-lg bg-gray-800 text-gray-400 hover:text-white hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-2xl font-bold text-white tracking-tight">Edit Data Responden #{{ $record->id }}</h1>
        </div>
        <p class="text-gray-400 text-sm mt-1 ml-10">Ubah parameter untuk merubah hasil kalkulasi Intake & Risk Quotient.</p>
    </div>

    @if($errors->any())
    <div class="p-4 bg-red-900/40 border border-red-500/50 rounded-xl">
        <ul class="list-disc list-inside text-sm text-red-300 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="glass-card rounded-xl border border-gray-800/60 overflow-hidden shadow-xl">
        <div class="bg-gray-800/40 border-b border-gray-700/50 px-6 py-4">
            <h3 class="font-medium text-gray-200">Formulator Ubah Data</h3>
        </div>
        
        <form action="{{ route('analisis.update', $record->id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Block 1 -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Target Polutan</label>
                <select name="pollutant_type" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
                    <option value="" disabled>Pilih Polutan...</option>
                    @foreach(\App\Models\RqAnalysis::$pollutantLabels as $key => $label)
                        <option value="{{ $key }}" {{ $record->pollutant_type === $key ? 'selected' : '' }}>
                            {{ $label }} (Default RfD: {{ \App\Models\RqAnalysis::$rfdDefaults[$key] }})
                        </option>
                    @endforeach
                </select>
            </div>

            <hr class="border-gray-800">

            <!-- Block 2 -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-blue-400 uppercase tracking-widest">Informasi Responden</h4>
                    
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Nama Subjek</label>
                        <input type="text" name="nama" value="{{ old('nama', $record->nama) }}" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Umur (tahun)</label>
                        <input type="number" step="1" name="umur" value="{{ old('umur', $record->umur) }}" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Berat Badan - Wb (kg)</label>
                        <input type="number" step="0.1" name="wb" value="{{ old('wb', $record->wb) }}" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:border-blue-500 transition-colors">
                    </div>
                </div>

                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-emerald-400 uppercase tracking-widest">Variabel Rumus</h4>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Konsentrasi (C)</label>
                            <input type="number" step="0.0001" name="c" value="{{ old('c', $record->c) }}" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Laju Asupan (R)</label>
                            <input type="number" step="0.01" name="r" value="{{ old('r', $record->r) }}" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Frekuensi (f) /th</label>
                            <input type="number" step="1" name="f" value="{{ old('f', $record->f) }}" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">RfD (Dosis acuan)</label>
                            <input type="number" step="0.000001" name="rfd" value="{{ old('rfd', $record->rfd) }}" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Waktu Avg (tavg)</label>
                            <input type="number" step="1" name="tavg" value="{{ old('tavg', $record->tavg) }}" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Durasi Pajanan (Dt)</label>
                            <input type="number" step="1" name="dt_input" value="{{ old('dt_input', $record->dt_input) }}" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white outline-none focus:border-emerald-500">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-gray-800">
                <a href="{{ route('analisis.rq.' . $record->pollutant_type) }}" class="px-5 py-2 rounded-lg border border-gray-700 text-gray-300 hover:bg-gray-800 transition-colors">Batal</a>
                <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-500 shadow-lg shadow-blue-500/20 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
