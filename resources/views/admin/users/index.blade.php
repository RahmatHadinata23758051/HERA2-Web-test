@extends('layouts.app')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-white">Manajemen Akun</h2>
            <p class="text-gray-400 mt-1 text-sm">Kelola semua pengguna sistem HERA 2.0</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
           class="flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-sm font-semibold rounded-lg shadow-lg shadow-indigo-500/20 transition-all hover:-translate-y-px">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pengguna
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-500/10 border border-green-500/30 rounded-xl px-5 py-3.5" x-data x-init="setTimeout(() => $el.remove(), 5000)">
        <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm text-green-400 font-medium">{{ session('success') }}</p>
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/30 rounded-xl px-5 py-3.5" x-data x-init="setTimeout(() => $el.remove(), 5000)">
        <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <p class="text-sm text-red-400 font-medium">{{ session('error') }}</p>
    </div>
    @endif

    {{-- Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700/50 flex items-center justify-between">
            <h3 class="font-semibold text-white">Daftar Pengguna</h3>
            <span class="text-xs text-gray-500">Total: {{ $users->total() }} akun</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-400">
                <thead class="text-xs text-gray-500 uppercase bg-gray-800/30 border-b border-gray-700/50">
                    <tr>
                        <th class="px-6 py-3.5 font-medium w-10">No</th>
                        <th class="px-6 py-3.5 font-medium">Nama</th>
                        <th class="px-6 py-3.5 font-medium">Email</th>
                        <th class="px-6 py-3.5 font-medium">Role</th>
                        <th class="px-6 py-3.5 font-medium">Bergabung</th>
                        <th class="px-6 py-3.5 font-medium text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60">
                    @forelse ($users as $i => $user)
                    <tr class="hover:bg-gray-800/20 transition-colors group">
                        <td class="px-6 py-4 text-gray-500 text-xs">{{ $users->firstItem() + $i }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background={{ $user->isDireksi() ? '7c3aed' : '1d4ed8' }}&color=fff&rounded=true&size=32" alt="" class="w-8 h-8 rounded-full flex-shrink-0">
                                <div>
                                    <p class="font-medium text-gray-200">{{ $user->name }}</p>
                                    @if($user->id === auth()->id())
                                    <span class="text-[10px] text-indigo-400 font-semibold">● Anda</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-gray-400">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2.5 py-1 rounded-md text-[11px] font-bold uppercase tracking-wider
                                {{ $user->isDireksi() ? 'bg-purple-500/15 text-purple-400 border border-purple-500/25' : 'bg-blue-500/15 text-blue-400 border border-blue-500/25' }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-500">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Edit --}}
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="px-3 py-1.5 text-xs font-medium text-indigo-400 bg-indigo-500/10 hover:bg-indigo-500/20 border border-indigo-500/20 rounded-lg transition-colors">
                                    Edit
                                </a>

                                {{-- Delete --}}
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                    x-data
                                    @submit.prevent="if(confirm('Hapus akun {{ addslashes($user->name) }}? Tindakan ini tidak dapat dibatalkan.')) $el.submit()">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-3 py-1.5 text-xs font-medium text-red-400 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 rounded-lg transition-colors">
                                        Hapus
                                    </button>
                                </form>
                                @else
                                <span class="px-3 py-1.5 text-xs text-gray-600 cursor-not-allowed">Hapus</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-gray-600">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <p class="text-sm">Belum ada pengguna terdaftar</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-700/50 flex items-center justify-between">
            <p class="text-xs text-gray-500">
                Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} pengguna
            </p>
            <div class="flex items-center gap-1">
                @if($users->onFirstPage())
                    <span class="px-3 py-1.5 text-xs text-gray-600 bg-gray-800/30 rounded-lg cursor-not-allowed">← Sebelumnya</span>
                @else
                    <a href="{{ $users->previousPageUrl() }}" class="px-3 py-1.5 text-xs text-gray-400 bg-gray-800/50 hover:bg-gray-700/50 rounded-lg transition-colors">← Sebelumnya</a>
                @endif

                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="px-3 py-1.5 text-xs text-gray-400 bg-gray-800/50 hover:bg-gray-700/50 rounded-lg transition-colors">Berikutnya →</a>
                @else
                    <span class="px-3 py-1.5 text-xs text-gray-600 bg-gray-800/30 rounded-lg cursor-not-allowed">Berikutnya →</span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
