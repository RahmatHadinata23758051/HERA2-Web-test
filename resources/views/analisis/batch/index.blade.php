@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ openCreateModal: false, openEditModal: false, editBatchId: null, editBatchName: '', openConfirmModal: false, confirmTitle: '', confirmMessage: '', confirmAction: '' }">

    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-5 rounded-xl shadow-sm border border-surface-container-high">
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-on-surface font-headline">Analisis Risiko Kesehatan Lingkungan (ARKL / RQ)</h2>
            <p class="text-on-surface-variant text-sm mt-1">Kelola data responden riset logam berat berdasarkan kelompok/batch penelitian Anda.</p>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            {{-- Search Bar --}}
            <form action="{{ route('analisis.index') }}" method="GET" class="relative flex items-center">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama batch..."
                       class="w-full sm:w-64 bg-surface-container-low border border-surface-container-high text-on-surface text-sm rounded-lg pl-9 pr-4 py-2.5 outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary block transition">
                <svg class="w-4 h-4 text-outline absolute left-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                @if(request()->filled('search'))
                    <a href="{{ route('analisis.index') }}" class="absolute right-3 text-outline hover:text-on-surface" title="Clear">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </form>

            <button @click="openCreateModal = true"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-on-primary rounded-lg hover:brightness-110 transition-all shadow-sm font-bold text-sm whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Batch Baru
            </button>
        </div>
    </div>

    {{-- Batches Grid --}}
    @if($batches->isEmpty())
    <div class="bg-white rounded-xl border border-surface-container-high p-16 text-center shadow-sm">
        <div class="flex flex-col items-center gap-3">
            <div class="p-4 bg-surface-container rounded-full text-primary">
                <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <h3 class="font-extrabold text-lg text-on-surface mt-2">Belum ada Batch Penelitian</h3>
            <p class="text-sm text-on-surface-variant max-w-md text-center">Buatlah batch pertama Anda untuk mulai mengimpor atau menginput data responden riset logam berat.</p>
            <button @click="openCreateModal = true"
                    class="mt-3 px-4 py-2 bg-primary/10 text-primary border border-primary/20 rounded-lg hover:bg-primary hover:text-on-primary transition-all text-sm font-bold">
                Mulai Buat Batch
            </button>
        </div>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($batches as $batch)
        <div class="bg-white rounded-xl border border-surface-container-high shadow-sm hover:shadow-md hover:border-primary/30 transition-all duration-300 flex flex-col justify-between overflow-hidden group">
            <div class="p-5 space-y-4">
                <div class="flex justify-between items-start gap-3">
                    <div class="p-2.5 bg-primary/10 text-primary rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="flex items-center gap-1">
                        <button @click="openEditModal = true; editBatchId = {{ $batch->id }}; editBatchName = '{{ addslashes($batch->name) }}'"
                                class="p-1.5 text-outline hover:text-primary hover:bg-primary/10 rounded-lg transition-colors" title="Edit Nama Batch">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button type="button"
                                @click="openConfirmModal = true; confirmTitle = 'Hapus Batch Penelitian'; confirmMessage = 'Apakah Anda yakin ingin menghapus batch &quot;{{ addslashes($batch->name) }}&quot;? Seluruh data responden di dalamnya akan terhapus secara permanen.'; confirmAction = '{{ route('analisis.batch.destroy', $batch->id) }}'"
                                class="p-1.5 text-outline hover:text-error hover:bg-error-container rounded-lg transition-colors" title="Hapus Batch">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>

                <div>
                    <h3 class="font-extrabold text-on-surface group-hover:text-primary transition-colors text-base line-clamp-1">
                        {{ $batch->name }}
                    </h3>
                    <p class="text-xs text-on-surface-variant mt-1">Dibuat oleh: <span class="font-bold">{{ optional($batch->user)->name ?? 'Sistem' }}</span></p>
                </div>

                <div class="flex items-center justify-between text-xs text-on-surface-variant bg-surface-container/30 px-3 py-2 rounded-lg border border-surface-container">
                    <span class="font-medium">Total Responden:</span>
                    <span class="font-bold text-primary">{{ $batch->analyses_count }} Responden</span>
                </div>
            </div>

            <div class="border-t border-surface-container-high bg-slate-50 px-5 py-3.5 flex justify-between items-center">
                <span class="text-[10px] font-mono text-outline">{{ $batch->created_at->format('d M Y - H:i') }}</span>
                <a href="{{ route('analisis.batch.show', $batch->id) }}"
                   class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:gap-2 transition-all">
                    Buka Batch
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($batches->hasPages())
    <div class="bg-white rounded-xl border border-surface-container-high p-4 shadow-sm">
        {{ $batches->links() }}
    </div>
    @endif
    @endif

    {{-- Modal Create Batch --}}
    <div x-show="openCreateModal"
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/50"
         style="display: none;">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full border border-surface-container-high overflow-hidden"
             @click.away="openCreateModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="px-6 py-4 border-b border-surface-container-high flex justify-between items-center">
                <h3 class="font-extrabold text-on-surface text-base">Buat Batch Analisis Baru</h3>
                <button @click="openCreateModal = false" class="p-1 hover:bg-surface-container rounded-lg text-outline">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form action="{{ route('analisis.batch.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Nama Batch Penelitian</label>
                        <input type="text" name="name" required placeholder="Contoh: ARKL Dusun Melati - Kelompok Dewasa"
                               class="w-full bg-surface-container-low border border-surface-container-high text-on-surface text-sm rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary block p-2.5 outline-none transition">
                        <p class="text-[10px] text-outline mt-1.5">Masukkan nama batch yang spesifik agar mudah dibedakan dengan penelitian lainnya.</p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-surface-container-high flex justify-end gap-2.5">
                    <button type="button" @click="openCreateModal = false"
                            class="px-4 py-2 border border-surface-container-high hover:bg-surface-container text-on-surface-variant rounded-lg text-sm font-bold transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-primary text-on-primary hover:brightness-110 rounded-lg text-sm font-bold transition-all shadow-sm">
                        Simpan Batch
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Batch --}}
    <div x-show="openEditModal"
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/50"
         style="display: none;">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full border border-surface-container-high overflow-hidden"
             @click.away="openEditModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="px-6 py-4 border-b border-surface-container-high flex justify-between items-center">
                <h3 class="font-extrabold text-on-surface text-base">Ubah Nama Batch Analisis</h3>
                <button @click="openEditModal = false" class="p-1 hover:bg-surface-container rounded-lg text-outline">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'{{ url('/analisis/batch') }}/' + editBatchId" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-1.5">Nama Batch Penelitian Baru</label>
                        <input type="text" name="name" required x-model="editBatchName"
                               class="w-full bg-surface-container-low border border-surface-container-high text-on-surface text-sm rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary block p-2.5 outline-none transition">
                        <p class="text-[10px] text-outline mt-1.5">Ubah nama batch agar sesuai dengan kelompok penelitian yang bersangkutan.</p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-surface-container-high flex justify-end gap-2.5">
                    <button type="button" @click="openEditModal = false"
                            class="px-4 py-2 border border-surface-container-high hover:bg-surface-container text-on-surface-variant rounded-lg text-sm font-bold transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-primary text-on-primary hover:brightness-110 rounded-lg text-sm font-bold transition-all shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    <div x-show="openConfirmModal"
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/50"
         style="display: none;"
         x-transition>
        <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full border border-surface-container-high overflow-hidden"
             @click.away="openConfirmModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <div class="p-6 text-center space-y-4">
                {{-- Hazard Warning Icon --}}
                <div class="mx-auto w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center animate-bounce">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                
                <div class="space-y-1">
                    <h3 class="font-extrabold text-on-surface text-lg" x-text="confirmTitle">Konfirmasi Hapus</h3>
                    <p class="text-xs text-on-surface-variant leading-relaxed" x-text="confirmMessage"></p>
                </div>
            </div>

            <div class="px-6 py-4 bg-slate-50 border-t border-surface-container-high flex justify-center gap-3">
                <button type="button" @click="openConfirmModal = false"
                        class="px-4 py-2 border border-surface-container-high hover:bg-surface-container text-on-surface-variant rounded-lg text-sm font-bold transition-colors w-24">
                    Batal
                </button>
                <form :action="confirmAction" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white hover:bg-red-700 rounded-lg text-sm font-bold transition-all shadow-sm w-24">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
