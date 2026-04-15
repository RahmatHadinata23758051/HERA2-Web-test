@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="bg-white p-5 rounded-xl shadow-sm border border-surface-container-high">
        <h2 class="text-2xl font-extrabold tracking-tight text-on-surface font-headline">Input Data Simulasi Manual</h2>
        <p class="text-on-surface-variant text-sm mt-1">Gunakan form ini untuk mensimulasikan perhitungan Intake & Risk Quotient untuk satu subjek tunggal.</p>
    </div>

    {{-- Error Alert --}}
    @if($errors->any())
    <div class="p-4 bg-error-container border border-error/30 rounded-xl flex items-start gap-3">
        <svg class="w-5 h-5 text-error flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <ul class="list-disc list-inside text-sm text-error space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Form Card --}}
    <div class="bg-white rounded-xl border border-surface-container-high overflow-hidden shadow-sm">
        
        {{-- Card Header --}}
        <div class="bg-surface-container-lowest border-b border-surface-container-high px-6 py-4">
            <h3 class="font-bold text-on-surface text-sm font-headline">Formulir Input Data</h3>
        </div>
        
        <form action="{{ route('analisis.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            {{-- Polutan Selector --}}
            <div>
                <label class="block text-sm font-bold text-on-surface mb-1.5">Target Polutan</label>
                <select name="pollutant_type" required
                        class="w-full bg-white border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all text-sm">
                    <option value="" disabled selected>Pilih Polutan...</option>
                    @foreach(\App\Models\RqAnalysis::$pollutantLabels as $key => $label)
                        <option value="{{ $key }}">{{ $label }} (Default RfD: {{ \App\Models\RqAnalysis::$rfdDefaults[$key] }})</option>
                    @endforeach
                </select>
            </div>

            <hr class="border-surface-container-high">

            {{-- Two Column Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                {{-- Informasi Responden --}}
                <div class="space-y-4">
                    <h4 class="text-xs font-black text-primary uppercase tracking-widest border-b border-primary/20 pb-2">
                        Informasi Responden
                    </h4>
                    
                    <div>
                        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Nama Subjek</label>
                        <input type="text" name="nama" required
                               class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Umur (tahun)</label>
                        <input type="number" step="1" name="umur" required
                               class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Berat Badan — Wb (kg)</label>
                        <input type="number" step="0.1" name="wb" required
                               class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    </div>
                </div>

                {{-- Variabel Rumus --}}
                <div class="space-y-4">
                    <h4 class="text-xs font-black text-secondary uppercase tracking-widest border-b border-secondary/20 pb-2">
                        Variabel Rumus
                    </h4>

                    <div class="grid grid-cols-2 gap-3">
                        @php
                            $varFields = [
                                ['name'=>'c',        'label'=>'Konsentrasi (C)',    'step'=>'0.0001'],
                                ['name'=>'r',        'label'=>'Laju Asupan (R)',    'step'=>'0.01'],
                                ['name'=>'f',        'label'=>'Frekuensi (f) /th',  'step'=>'1'],
                                ['name'=>'rfd',      'label'=>'RfD (Dosis Acuan)',   'step'=>'0.000001'],
                                ['name'=>'tavg',     'label'=>'Waktu Avg (tavg)',    'step'=>'1'],
                                ['name'=>'dt_input', 'label'=>'Durasi Pajanan (Dt)', 'step'=>'1'],
                            ];
                        @endphp
                        @foreach($varFields as $field)
                        <div>
                            <label class="block text-xs font-medium text-on-surface-variant mb-1.5">{{ $field['label'] }}</label>
                            <input type="number" step="{{ $field['step'] }}" name="{{ $field['name'] }}" required
                                   class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-3 py-2 text-on-surface text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="pt-4 flex justify-end gap-3 border-t border-surface-container-high">
                <a href="{{ url()->previous() }}"
                   class="px-5 py-2.5 rounded-lg border border-surface-container-high text-on-surface-variant hover:bg-surface-container-low transition-colors text-sm font-medium">
                    Batal
                </a>
                <button type="submit"
                        class="px-5 py-2.5 rounded-lg bg-primary text-on-primary font-bold hover:brightness-110 shadow-sm transition-all text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M12 7h.01M15 7h.01M9 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-4"/></svg>
                    Kalkulasi & Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
