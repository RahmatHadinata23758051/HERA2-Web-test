@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-5 rounded-xl shadow-sm border border-surface-container-high">
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-on-surface font-headline">Manajemen Akun</h2>
            <p class="text-on-surface-variant mt-1 text-sm">Kelola semua pengguna sistem HERA 2.0</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
           class="flex items-center gap-2 px-4 py-2.5 bg-primary text-on-primary text-sm font-bold rounded-lg shadow-sm hover:brightness-110 transition-all flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pengguna
        </a>
    </div>

    {{-- Table Card --}}
    <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-surface-container-high">
        
        {{-- Table Header --}}
        <div class="px-6 py-4 border-b border-surface-container-high flex items-center justify-between bg-surface-container-lowest">
            <h3 class="font-bold text-on-surface text-sm">Daftar Pengguna</h3>
            <span class="text-xs text-outline bg-surface-container px-2.5 py-1 rounded-full">Total: {{ $users->total() }} akun</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-[10px] text-on-surface-variant uppercase tracking-widest font-black bg-surface-container-low border-b border-surface-container-high">
                    <tr>
                        <th class="px-6 py-3.5 w-10">No</th>
                        <th class="px-6 py-3.5">Nama</th>
                        <th class="px-6 py-3.5">Email</th>
                        <th class="px-6 py-3.5">Role</th>
                        <th class="px-6 py-3.5">Bergabung</th>
                        <th class="px-6 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-high">
                    @forelse ($users as $i => $user)
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="px-6 py-4 text-on-surface-variant text-xs font-mono">{{ $users->firstItem() + $i }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($user->picture)
                                    <img src="{{ asset('storage/' . $user->picture) }}" alt="Avatar"
                                         class="w-9 h-9 rounded-full object-cover ring-2 ring-surface-container-high flex-shrink-0">
                                @else
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background={{ $user->isDireksi() ? '006948' : '3e6753' }}&color=fff&rounded=true&size=36"
                                         alt="Avatar" class="w-9 h-9 rounded-full flex-shrink-0">
                                @endif
                                <div>
                                    <p class="font-bold text-on-surface">{{ $user->name }}</p>
                                    @if($user->id === auth()->id())
                                        <span class="text-[10px] text-primary font-bold">● Anda</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-on-surface-variant">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider
                                {{ $user->isDireksi()
                                    ? 'bg-primary/10 text-primary border border-primary/20'
                                    : 'bg-secondary-container text-secondary border border-secondary/20' }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-on-surface-variant">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Edit --}}
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="px-3 py-1.5 text-xs font-bold text-primary bg-primary/10 hover:bg-primary/20 border border-primary/20 rounded-lg transition-colors">
                                    Edit
                                </a>
                                {{-- Delete --}}
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                      x-data
                                      @submit.prevent="if(confirm('Hapus akun {{ addslashes($user->name) }}? Tindakan ini tidak dapat dibatalkan.')) $el.submit()">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs font-bold text-error bg-error-container hover:brightness-95 border border-error/20 rounded-lg transition-colors">
                                        Hapus
                                    </button>
                                </form>
                                @else
                                <span class="px-3 py-1.5 text-xs text-outline cursor-not-allowed">Hapus</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="p-4 bg-surface-container rounded-full">
                                    <svg class="w-10 h-10 text-outline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <p class="font-bold text-on-surface">Belum ada pengguna terdaftar</p>
                                <p class="text-sm text-on-surface-variant">Klik <span class="text-primary font-bold">"Tambah Pengguna"</span> untuk membuat akun baru.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-surface-container-high flex items-center justify-between bg-surface-container-lowest">
            <p class="text-xs text-on-surface-variant">
                Menampilkan <span class="font-bold text-on-surface">{{ $users->firstItem() }}–{{ $users->lastItem() }}</span> dari {{ $users->total() }} pengguna
            </p>
            <div class="flex items-center gap-1.5">
                @if($users->onFirstPage())
                    <span class="px-3 py-1.5 text-xs text-outline bg-surface-container rounded-lg cursor-not-allowed">← Sebelumnya</span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="px-3 py-1.5 text-xs text-on-surface-variant bg-surface-container hover:bg-surface-container-high rounded-lg transition-colors">← Sebelumnya</a>
                @endif

                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="px-3 py-1.5 text-xs text-on-surface-variant bg-surface-container hover:bg-surface-container-high rounded-lg transition-colors">Berikutnya →</a>
                @else
                    <span class="px-3 py-1.5 text-xs text-outline bg-surface-container rounded-lg cursor-not-allowed">Berikutnya →</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
