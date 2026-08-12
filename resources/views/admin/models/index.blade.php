@extends('layouts.app')

@section('content')
<div class="px-4 md:px-8 lg:px-12 py-8 max-w-[1800px] mx-auto space-y-8" x-data="{ uploadModalOpen: false, selectedTarget: 'chromium', fileName: '' }">

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-surface-container-high pb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('settings.index') }}" class="text-xs font-bold text-outline hover:text-primary transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">arrow_back</span> Pengaturan
                </a>
                <span class="text-xs text-outline-variant">/</span>
                <span class="text-xs font-bold text-primary">Model AI</span>
            </div>
            <h1 class="text-2xl font-black text-on-surface font-headline tracking-tight">Manajemen Model AI & Dynamic Swapping</h1>
            <p class="text-xs text-on-surface-variant mt-1">Kelola dan perbarui file model Machine Learning (.pkl) secara live tanpa downtime dengan validasi dry-run otomatis.</p>
        </div>

        <!-- AI Service Health Badge -->
        <div class="flex items-center gap-3 bg-white px-4 py-2.5 rounded-xl border border-surface-container-high shadow-sm">
            <div class="flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded-full {{ $online ? 'bg-green-500 animate-pulse' : 'bg-red-500' }}"></span>
                <span class="text-xs font-bold {{ $online ? 'text-green-600' : 'text-red-500' }}">
                    AI Service: {{ $online ? 'Connected (Zero Downtime Ready)' : 'Offline' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-start gap-3 shadow-sm animate-fade-in">
        <span class="material-symbols-outlined text-emerald-600 text-xl flex-shrink-0">check_circle</span>
        <div>
            <p class="font-bold">Sukses!</p>
            <p class="text-xs mt-0.5">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium flex items-start gap-3 shadow-sm animate-fade-in">
        <span class="material-symbols-outlined text-red-600 text-xl flex-shrink-0">error</span>
        <div>
            <p class="font-bold">Proses Gagal / Validasi Ditolak</p>
            <p class="text-xs mt-0.5">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    @if ($errors->any())
    <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium flex items-start gap-3 shadow-sm">
        <span class="material-symbols-outlined text-red-600 text-xl flex-shrink-0">warning</span>
        <div>
            <p class="font-bold">Kesalahan Input Form:</p>
            <ul class="list-disc list-inside text-xs mt-1 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- Cards Grid (2 Parameter Models) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Chromium Model Card -->
        <div class="bg-white rounded-2xl p-6 border border-surface-container-high shadow-sm hover:border-primary/30 transition-all flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -right-6 -bottom-6 text-primary/5 pointer-events-none group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-9xl">psychology</span>
            </div>

            <div>
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-primary/10 rounded-xl text-primary">
                            <span class="material-symbols-outlined text-2xl">analytics</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-primary bg-primary/10 px-2 py-0.5 rounded-full">Cr⁶⁺ Parameter</span>
                            <h3 class="font-bold text-lg text-on-surface mt-0.5">Chromium (Hexavalent) Model</h3>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 flex items-center gap-1 border border-emerald-200">
                        <span class="material-symbols-outlined text-xs">check_circle</span> Aktif
                    </span>
                </div>

                <div class="space-y-3 my-6 text-xs text-on-surface-variant border-t border-b border-surface-container-low py-4">
                    <div class="flex justify-between items-center">
                        <span class="font-medium">Nama Algoritma:</span>
                        <span class="font-bold text-on-surface font-mono">{{ $models['chromium']['name'] ?? 'XGBoost Regressor' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-medium">Nama File Aktif:</span>
                        <span class="font-bold text-primary font-mono bg-primary/10 px-2 py-0.5 rounded">{{ $models['chromium']['filename'] ?? 'best_model_chromium_v2.pkl' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-medium">Ukuran File:</span>
                        <span class="font-bold text-on-surface font-mono">{{ $models['chromium']['size_kb'] ?? 0 }} KB</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-medium">Terakhir Diperbarui:</span>
                        <span class="font-bold text-on-surface font-mono">{{ $models['chromium']['last_modified'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <button @click="selectedTarget = 'chromium'; uploadModalOpen = true; fileName = ''"
                    class="w-full py-2.5 px-4 bg-primary hover:bg-primary-container text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-base">cloud_upload</span>
                Unggah Model Chromium Baru
            </button>
        </div>

        <!-- Nickel Model Card -->
        <div class="bg-white rounded-2xl p-6 border border-surface-container-high shadow-sm hover:border-indigo-500/30 transition-all flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -right-6 -bottom-6 text-indigo-500/5 pointer-events-none group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-9xl">psychology</span>
            </div>

            <div>
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600">
                            <span class="material-symbols-outlined text-2xl">analytics</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">Ni²⁺ Parameter</span>
                            <h3 class="font-bold text-lg text-on-surface mt-0.5">Nickel (Dissolved) Model</h3>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 flex items-center gap-1 border border-indigo-200">
                        <span class="material-symbols-outlined text-xs">check_circle</span> Aktif
                    </span>
                </div>

                <div class="space-y-3 my-6 text-xs text-on-surface-variant border-t border-b border-surface-container-low py-4">
                    <div class="flex justify-between items-center">
                        <span class="font-medium">Nama Algoritma:</span>
                        <span class="font-bold text-on-surface font-mono">{{ $models['nickel']['name'] ?? 'Random Forest Regressor' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-medium">Nama File Aktif:</span>
                        <span class="font-bold text-indigo-700 font-mono bg-indigo-100 px-2 py-0.5 rounded">{{ $models['nickel']['filename'] ?? 'best_model_nickel_v2.pkl' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-medium">Ukuran File:</span>
                        <span class="font-bold text-on-surface font-mono">{{ $models['nickel']['size_kb'] ?? 0 }} KB</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-medium">Terakhir Diperbarui:</span>
                        <span class="font-bold text-on-surface font-mono">{{ $models['nickel']['last_modified'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <button @click="selectedTarget = 'nickel'; uploadModalOpen = true; fileName = ''"
                    class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-base">cloud_upload</span>
                Unggah Model Nickel Baru
            </button>
        </div>

    </div>

    <!-- Security & Technical Guidelines -->
    <div class="bg-surface-container-lowest rounded-2xl p-6 border border-surface-container-high shadow-sm space-y-4">
        <div class="flex items-center gap-2 text-on-surface font-bold text-sm border-b border-surface-container-high pb-3">
            <span class="material-symbols-outlined text-amber-500">verified_user</span>
            <h3>Aturan & Garansi Keamanan Hot-Reload</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-on-surface-variant">
            <div class="p-3.5 bg-white rounded-xl border border-surface-container-high space-y-1">
                <p class="font-bold text-on-surface">1. Validasi Dry-Run Otomatis</p>
                <p class="leading-relaxed">Setiap file yang diunggah akan diuji coba dengan data 7 sensor dummy sebelum diterapkan ke memori.</p>
            </div>
            <div class="p-3.5 bg-white rounded-xl border border-surface-container-high space-y-1">
                <p class="font-bold text-on-surface">2. Thread-Safe Atomic Swap</p>
                <p class="leading-relaxed">Pergantian objek model terlindungi oleh muteks internal untuk menjamin nol kegagalan data saat telemetry berjalan.</p>
            </div>
            <div class="p-3.5 bg-white rounded-xl border border-surface-container-high space-y-1">
                <p class="font-bold text-on-surface">3. Rollback Otomatis</p>
                <p class="leading-relaxed">Jika terjadi kesalahan sistem saat penulisan file, versi model sebelumnya (.pkl.bak) akan otomatis dipulihkan.</p>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div x-show="uploadModalOpen" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="opacity-100" 
         x-transition:leave-end="opacity-0" 
         style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        
        <div @click.away="uploadModalOpen = false" 
             class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-surface-container-high space-y-6 animate-scale-up">
            
            <div class="flex justify-between items-center border-b border-surface-container-high pb-4">
                <div>
                    <h3 class="font-bold text-base text-on-surface">Unggah Model AI Baru</h3>
                    <p class="text-xs text-on-surface-variant">Pilih file .pkl / .joblib untuk memperbarui estimasi AI.</p>
                </div>
                <button @click="uploadModalOpen = false" class="text-outline hover:text-on-surface p-1 rounded-lg hover:bg-surface-container-low transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="{{ route('admin.models.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <!-- Target Selection -->
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-2">Target Logam Berat</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center justify-between p-3 rounded-xl border cursor-pointer transition-all"
                               :class="selectedTarget === 'chromium' ? 'border-primary bg-primary/5 text-primary font-bold' : 'border-surface-container-high text-on-surface-variant'">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="target" value="chromium" x-model="selectedTarget" class="text-primary focus:ring-primary">
                                <span class="text-xs">Chromium (Cr⁶⁺)</span>
                            </div>
                        </label>
                        <label class="flex items-center justify-between p-3 rounded-xl border cursor-pointer transition-all"
                               :class="selectedTarget === 'nickel' ? 'border-indigo-600 bg-indigo-50 text-indigo-600 font-bold' : 'border-surface-container-high text-on-surface-variant'">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="target" value="nickel" x-model="selectedTarget" class="text-indigo-600 focus:ring-indigo-600">
                                <span class="text-xs">Nickel (Ni²⁺)</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Custom Model / Algorithm Name (Optional) -->
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-1">
                        Nama Model / Algoritma <span class="text-on-surface-variant font-normal lowercase">(opsional)</span>
                    </label>
                    <input type="text"
                           name="model_name"
                           placeholder="misal: XGBoost Regressor v2.1, Random Forest Tuned"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-surface-container-high text-xs text-on-surface focus:outline-none focus:border-primary transition-colors">
                    <p class="text-[10px] text-on-surface-variant mt-1">
                        Jika dikosongkan, nama model akan dideteksi dari file model.
                    </p>
                </div>

                <!-- File Dropzone -->
                <div>
                    <label class="block text-xs font-bold text-on-surface uppercase tracking-wider mb-2">File Model Machine Learning (.pkl / .joblib)</label>
                    <div class="border-2 border-dashed border-surface-container-highest hover:border-primary rounded-2xl p-6 text-center bg-surface-container-lowest transition-colors relative cursor-pointer group">
                        <input type="file" name="model_file" accept=".pkl,.joblib,.bin" required
                               @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''"
                               class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10">
                        <div class="space-y-2 pointer-events-none">
                            <span class="material-symbols-outlined text-4xl text-outline group-hover:text-primary transition-colors">cloud_upload</span>
                            <p class="text-xs font-medium text-on-surface">Klik atau seret file model ke area ini</p>
                            <p class="text-[10px] text-outline">Format yang didukung: .pkl, .joblib (Maks. 50 MB)</p>
                            <template x-if="fileName">
                                <div class="mt-3 px-3 py-1.5 bg-primary/10 text-primary text-xs font-bold rounded-lg inline-block font-mono">
                                    📄 File terpilih: <span x-text="fileName"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="flex justify-end gap-3 pt-4 border-t border-surface-container-high">
                    <button type="button" @click="uploadModalOpen = false" class="px-4 py-2.5 text-xs font-bold text-on-surface-variant hover:bg-surface-container-low rounded-xl transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-primary hover:bg-primary-container rounded-xl shadow-sm transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">bolt</span>
                        Validasi & Hot-Reload Live
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection
