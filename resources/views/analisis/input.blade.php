@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white tracking-tight">Input Data Simulasi Manual</h1>
        <p class="text-gray-400 text-sm mt-1">Gunakan form ini untuk mensimulasikan perhitungan Intake & Risk Quotient untuk satu subjek tunggal.</p>
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
            <h3 class="font-medium text-gray-200">Formulator</h3>
        </div>
        
        <form action="{{ route('analisis.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Block 1 -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Target Polutan</label>
                <select name="pollutant_type" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
                    <option value="" disabled selected>Pilih Polutan...</option>
                    @foreach(\App\Models\RqAnalysis::$pollutantLabels as $key => $label)
                        <option value="{{ $key }}">{{ $label }} (Default RfD: {{ \App\Models\RqAnalysis::$rfdDefaults[$key] }})</option>
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
                        <input type="text" name="nama" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Umur (tahun)</label>
                        <input type="number" step="1" name="umur" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Berat Badan - Wb (kg)</label>
                        <input type="number" step="0.1" name="wb" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-2 text-white outline-none focus:border-blue-500 transition-colors">
                    </div>
                </div>

                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-emerald-400 uppercase tracking-widest">Variabel Rumus</h4>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Konsentrasi (C)</label>
                            <input type="number" step="0.0001" name="c" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Laju Asupan (R)</label>
                            <input type="number" step="0.01" name="r" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Frekuensi (f) /th</label>
                            <input type="number" step="1" name="f" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">RfD (Dosis acuan)</label>
                            <input type="number" step="0.000001" name="rfd" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Waktu Avg (tavg)</label>
                            <input type="number" step="1" name="tavg" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Durasi Pajanan (Dt)</label>
                            <input type="number" step="1" name="dt_input" required class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white outline-none focus:border-emerald-500">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3 border-t border-gray-800">
                <a href="{{ url()->previous() }}" class="px-5 py-2 rounded-lg border border-gray-700 text-gray-300 hover:bg-gray-800 transition-colors">Batal</a>
                <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-500 shadow-lg shadow-blue-500/20 transition-colors">
                    Kalkulasi & Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
