@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="bg-white p-5 rounded-xl shadow-sm border border-surface-container-high">
        <div class="flex items-center gap-3">
            <a href="{{ route('settings.index') }}"
               class="p-1.5 text-on-surface-variant hover:text-primary hover:bg-primary/10 rounded-lg transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight text-on-surface font-headline">Pengaturan Threshold Chromium</h2>
                <p class="text-on-surface-variant text-sm mt-0.5">Atur batas nilai normal dan warning untuk kadar Chromium (Cr) yang dipantau oleh sistem.</p>
            </div>
        </div>
    </div>

    {{-- Alert Success --}}
    @if(session('success'))
    <div class="p-4 bg-primary/10 border border-primary/20 rounded-xl flex items-start gap-3">
        <svg class="w-5 h-5 text-primary flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-primary font-medium">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Alert Error --}}
    @if($errors->any())
    <div class="p-4 bg-error-container border border-error/30 rounded-xl flex items-start gap-3">
        <svg class="w-5 h-5 text-error flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <ul class="text-sm text-error space-y-0.5 list-disc list-inside">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- Info Banner --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div>
            <p class="text-sm font-bold text-amber-800">Perubahan akan langsung aktif</p>
            <p class="text-xs text-amber-700 mt-0.5">
                Setelah disimpan, semua data sensor baru yang masuk akan langsung diklasifikasikan menggunakan threshold baru ini.
                Notifikasi alert dan status badge di dashboard juga akan menyesuaikan.
            </p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-xl border border-surface-container-high overflow-hidden shadow-sm">
        <div class="bg-surface-container-lowest border-b border-surface-container-high px-6 py-4">
            <h3 class="font-bold text-on-surface text-sm">Nilai Batas Threshold</h3>
            <p class="text-xs text-on-surface-variant mt-0.5">Standar WHO untuk Chromium Heksavalen: Normal &lt;0.05 mg/L</p>
        </div>

        <form action="{{ route('settings.threshold.update') }}" method="POST" class="p-6 space-y-6">
            @csrf @method('PUT')

            {{-- Visual Preview --}}
            <div class="rounded-xl overflow-hidden border border-surface-container-high" x-data="{
                normalMax:  {{ old('cr_normal_max',  $current['cr_normal_max']['value'])  }},
                warningMax: {{ old('cr_warning_max', $current['cr_warning_max']['value']) }}
            }">
                <div class="px-4 py-3 bg-surface-container-low border-b border-surface-container-high">
                    <p class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Preview Rentang Klasifikasi</p>
                </div>
                <div class="p-5 space-y-3">
                    {{-- Bar visual --}}
                    <div class="relative h-10 rounded-lg overflow-hidden flex">
                        <div class="bg-primary/20 flex items-center justify-center flex-1">
                            <span class="text-[10px] font-black text-primary uppercase tracking-widest">Normal</span>
                        </div>
                        <div class="bg-yellow-100 flex items-center justify-center flex-1 border-x border-yellow-200">
                            <span class="text-[10px] font-black text-yellow-700 uppercase tracking-widest">Warning</span>
                        </div>
                        <div class="bg-error-container flex items-center justify-center flex-1">
                            <span class="text-[10px] font-black text-error uppercase tracking-widest">Danger</span>
                        </div>
                    </div>
                    {{-- Range labels --}}
                    <div class="flex text-xs text-on-surface-variant">
                        <div class="flex-1 text-center">
                            0 — <strong x-text="normalMax + ' mg/L'" class="text-primary"></strong>
                        </div>
                        <div class="flex-1 text-center">
                            <strong x-text="normalMax + ' — ' + warningMax + ' mg/L'" class="text-yellow-700"></strong>
                        </div>
                        <div class="flex-1 text-center">
                            ≥ <strong x-text="warningMax + ' mg/L'" class="text-error"></strong>
                        </div>
                    </div>
                </div>

                {{-- Form Inputs --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-surface-container-high p-5">

                    {{-- Normal Max --}}
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-3 h-3 rounded-full bg-primary"></div>
                            <label class="text-sm font-bold text-on-surface">Batas Atas Normal (mg/L)</label>
                        </div>
                        <p class="text-xs text-on-surface-variant mb-2">Nilai Cr di bawah batas ini = Status NORMAL ✅</p>
                        <input type="number" name="cr_normal_max" step="0.001" min="0.001" max="999"
                               value="{{ old('cr_normal_max', $current['cr_normal_max']['value']) }}"
                               x-model="normalMax"
                               class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface text-sm font-mono outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all @error('cr_normal_max') border-error @enderror"
                               required>
                        @error('cr_normal_max')
                            <p class="text-xs text-error mt-1">{{ $message }}</p>
                        @enderror
                        @if($current['cr_normal_max']['updated_by'])
                            <p class="text-[10px] text-on-surface-variant mt-1.5">
                                Terakhir diubah oleh <strong>{{ $current['cr_normal_max']['updated_by'] }}</strong>
                                pada {{ \Carbon\Carbon::parse($current['cr_normal_max']['updated_at'])->timezone('Asia/Makassar')->format('d M Y, H:i') }}
                            </p>
                        @endif
                    </div>

                    {{-- Warning Max --}}
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                            <label class="text-sm font-bold text-on-surface">Batas Atas Warning (mg/L)</label>
                        </div>
                        <p class="text-xs text-on-surface-variant mb-2">Nilai Cr di bawah batas ini = Status WARNING ⚠️ (di atas = DANGER 🔴)</p>
                        <input type="number" name="cr_warning_max" step="0.001" min="0.001" max="999"
                               value="{{ old('cr_warning_max', $current['cr_warning_max']['value']) }}"
                               x-model="warningMax"
                               class="w-full bg-surface-container-low border border-surface-container-high rounded-lg px-4 py-2.5 text-on-surface text-sm font-mono outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all @error('cr_warning_max') border-error @enderror"
                               required>
                        @error('cr_warning_max')
                            <p class="text-xs text-error mt-1">{{ $message }}</p>
                        @enderror
                        @if($current['cr_warning_max']['updated_by'])
                            <p class="text-[10px] text-on-surface-variant mt-1.5">
                                Terakhir diubah oleh <strong>{{ $current['cr_warning_max']['updated_by'] }}</strong>
                                pada {{ \Carbon\Carbon::parse($current['cr_warning_max']['updated_at'])->timezone('Asia/Makassar')->format('d M Y, H:i') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="pt-2 flex justify-end gap-3">
                <a href="{{ route('settings.index') }}"
                   class="px-5 py-2.5 rounded-lg border border-surface-container-high text-on-surface-variant hover:bg-surface-container-low transition-colors text-sm font-medium">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-primary text-on-primary font-bold hover:brightness-110 shadow-sm transition-all text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan & Terapkan
                </button>
            </div>
        </form>
    </div>

    {{-- Info Standar Referensi --}}
    <div class="bg-white rounded-xl border border-surface-container-high shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-surface-container-lowest border-b border-surface-container-high">
            <h3 class="font-bold text-on-surface text-sm">Referensi Standar Baku Mutu</h3>
        </div>
        <div class="p-6">
            <table class="w-full text-sm">
                <thead class="text-[10px] text-on-surface-variant uppercase tracking-widest font-black">
                    <tr class="border-b border-surface-container-high">
                        <th class="pb-2 text-left">Lembaga</th>
                        <th class="pb-2 text-center">Batas Normal</th>
                        <th class="pb-2 text-center">Batas Warning</th>
                        <th class="pb-2 text-left">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-high">
                    <tr class="text-on-surface-variant">
                        <td class="py-3 font-bold text-on-surface">WHO</td>
                        <td class="py-3 text-center font-mono">&lt; 0.050 mg/L</td>
                        <td class="py-3 text-center font-mono">&lt; 0.100 mg/L</td>
                        <td class="py-3 text-xs">Guidelines for Drinking-water Quality</td>
                    </tr>
                    <tr class="text-on-surface-variant">
                        <td class="py-3 font-bold text-on-surface">PP RI No.22/2021</td>
                        <td class="py-3 text-center font-mono">&lt; 0.050 mg/L</td>
                        <td class="py-3 text-center font-mono">—</td>
                        <td class="py-3 text-xs">Baku Mutu Air Kelas I (air minum)</td>
                    </tr>
                    <tr class="text-on-surface-variant">
                        <td class="py-3 font-bold text-on-surface">US EPA</td>
                        <td class="py-3 text-center font-mono">&lt; 0.100 mg/L</td>
                        <td class="py-3 text-center font-mono">—</td>
                        <td class="py-3 text-xs">Maximum Contaminant Level (MCL)</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
