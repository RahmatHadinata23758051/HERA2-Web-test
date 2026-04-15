@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="bg-white p-5 rounded-xl shadow-sm border border-surface-container-high">
        <div class="flex items-center gap-3">
            <a href="{{ route('analisis.rq.' . $record->pollutant_type) }}"
               class="p-1.5 rounded-lg bg-surface-container-low text-on-surface-variant hover:text-primary hover:bg-primary/10 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight text-on-surface font-headline">
                    Edit Responden <span class="text-primary">#{{ $record->id }}</span>
                </h2>
                <p class="text-on-surface-variant text-sm mt-0.5">Ubah parameter untuk merubah hasil kalkulasi Intake & Risk Quotient.</p>
            </div>
        </div>
    </div>

    {{-- Error Alert --}}
    @if($errors->any())
    <div class="p-4 bg-error-container border border-error/30 rounded-xl flex items-start gap-3">
        <svg class="w-5 h-5 text-error flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
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
        <div class="bg-surface-container-lowest border-b border-surface-container-high px-6 py-4 flex items-center justify-between">
            <h3 class="font-bold text-on-surface text-sm font-headline">Formulir Ubah Data</h3>
            <span class="text-xs font-bold text-on-surface-variant bg-surface-container px-2.5 py-1 rounded-full">
                ID #{{ $record->id }}
            </span>
        </div>
        
        <form action="{{ route('analisis.update', $record->id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            {{-- Polutan Selector --}}
            <div>
                <label class="block text-sm font-bold text-on-surface mb-1.5">Target Polutan</label>
                <select name="pollutant_type" required
                        class="w-full bg-white border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all text-sm">
                    <option value="" disabled>Pilih Polutan...</option>
                    @foreach(\App\Models\RqAnalysis::$pollutantLabels as $key => $label)
                        <option value="{{ $key }}" {{ $record->pollutant_type === $key ? 'selected' : '' }}>
                            {{ $label }} (Default RfD: {{ \App\Models\RqAnalysis::$rfdDefaults[$key] }})
                        </option>
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
                        <input type="text" name="nama" value="{{ old('nama', $record->nama) }}" required
                               class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Umur (tahun)</label>
                        <input type="number" step="1" name="umur" value="{{ old('umur', $record->umur) }}" required
                               class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Berat Badan — Wb (kg)</label>
                        <input type="number" step="0.1" name="wb" value="{{ old('wb', $record->wb) }}" required
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
                                ['name'=>'c',        'label'=>'Konsentrasi (C)',     'step'=>'0.0001',   'value'=> $record->c],
                                ['name'=>'r',        'label'=>'Laju Asupan (R)',     'step'=>'0.01',     'value'=> $record->r],
                                ['name'=>'f',        'label'=>'Frekuensi (f) /th',   'step'=>'1',        'value'=> $record->f],
                                ['name'=>'rfd',      'label'=>'RfD (Dosis Acuan)',    'step'=>'0.000001', 'value'=> $record->rfd],
                                ['name'=>'tavg',     'label'=>'Waktu Avg (tavg)',     'step'=>'1',        'value'=> $record->tavg],
                                ['name'=>'dt_input', 'label'=>'Durasi Pajanan (Dt)', 'step'=>'1',        'value'=> $record->dt_input],
                            ];
                        @endphp
                        @foreach($varFields as $field)
                        <div>
                            <label class="block text-xs font-medium text-on-surface-variant mb-1.5">{{ $field['label'] }}</label>
                            <input type="number" step="{{ $field['step'] }}" name="{{ $field['name'] }}"
                                   value="{{ old($field['name'], $field['value']) }}" required
                                   class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-3 py-2 text-on-surface text-sm outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="pt-4 flex justify-end gap-3 border-t border-surface-container-high">
                <a href="{{ route('analisis.rq.' . $record->pollutant_type) }}"
                   class="px-5 py-2.5 rounded-lg border border-surface-container-high text-on-surface-variant hover:bg-surface-container-low transition-colors text-sm font-medium">
                    Batal
                </a>
                <button type="submit"
                        class="px-5 py-2.5 rounded-lg bg-primary text-on-primary font-bold hover:brightness-110 shadow-sm transition-all text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
