<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $app_settings['app_name'] ?? 'HERA' }} - {{ $app_settings['app_description'] ?? 'Real-time Hexavalent Chromium Monitoring System' }}</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS Base -->
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-panel {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .glass-card {
            background: rgba(31, 41, 55, 0.6);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .text-gradient {
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-image: linear-gradient(to right, #60A5FA, #A78BFA);
        }
        
        /* Custom Scrollbar for tables */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: rgba(17, 24, 39, 0.5); }
        ::-webkit-scrollbar-thumb { background: rgba(75, 85, 99, 0.8); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(107, 114, 128, 1); }
        
        .value-update-flash {
            animation: textFlash 1.5s ease-out;
        }
        @keyframes textFlash {
            0% { color: #fff; text-shadow: 0 0 10px rgba(255,255,255,0.8); }
            100% { color: inherit; text-shadow: none; }
        }
        .row-enter {
            animation: rowSlideIn 0.5s ease-out forwards;
        }
        @keyframes rowSlideIn {
            0% { opacity: 0; transform: translateX(-10px); background-color: rgba(59, 130, 246, 0.2); }
            100% { opacity: 1; transform: translateX(0); background-color: transparent; }
        }
        .row-highlight-danger { animation: highlightDanger 2s ease-out forwards; }
        @keyframes highlightDanger {
            0% { background-color: rgba(239, 68, 68, 0.3); }
            100% { background-color: transparent; }
        }
        .row-highlight-warning { animation: highlightWarning 2s ease-out forwards; }
        @keyframes highlightWarning {
            0% { background-color: rgba(245, 158, 11, 0.3); }
            100% { background-color: transparent; }
        }

        /* Sidebar tooltip overrides */
        .sidebar-tooltip {
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .group:hover .sidebar-tooltip {
            visibility: visible;
            opacity: 1;
        }
    </style>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen flex selection:bg-indigo-500 selection:text-white" x-data="{ sidebarOpen: true }">

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="glass-panel border-r border-gray-800 flex-shrink-0 fixed h-full z-20 transition-all duration-300">
        <!-- Logo & Header -->
        <div class="h-16 flex items-center justify-between px-4 border-b border-gray-800/50">
            <div class="flex items-center gap-3 overflow-hidden" :class="sidebarOpen ? 'ml-2' : 'ml-0.5'">
                @if(!empty($app_settings['app_logo']))
                    <img src="{{ asset($app_settings['app_logo']) }}" alt="App Logo" class="w-8 h-8 object-contain shrink-0">
                @else
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg shadow-blue-500/30 shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                @endif
                <h1 x-show="sidebarOpen" x-transition.opacity.duration.300ms class="text-xl font-bold tracking-tight text-gradient whitespace-nowrap">{{ $app_settings['app_name'] ?? 'HERA' }} <span class="text-xs text-gray-500">{{ $app_settings['app_version'] ?? '' }}</span></h1>
            </div>
            
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-white transition-colors shrink-0" :class="!sidebarOpen && 'hidden'">
                <svg class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
            </button>
            
            <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-white transition-colors shrink-0 mx-auto" x-show="!sidebarOpen" style="display: none;">
                <svg class="w-5 h-5 transition-transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
            </button>
        </div>
        
        <nav class="p-3 space-y-1 overflow-x-hidden">
            <!-- Dashboard Menu -->
            <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 {{ request()->routeIs('dashboard') ? 'bg-gray-800/50 text-white border border-gray-700/50' : 'text-gray-400 hover:text-white hover:bg-gray-800/30' }} rounded-lg transition-all relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="flex items-center" :class="sidebarOpen ? 'justify-start w-full' : 'justify-center w-full'">
                    <svg class="shrink-0 w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-blue-400' : 'group-hover:text-blue-400' }} relative z-10 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="ml-3 font-medium text-sm relative z-10 whitespace-nowrap">Live Dashboard</span>
                </div>
                
                <div x-show="!sidebarOpen" class="sidebar-tooltip absolute left-14 bg-gray-800 border border-gray-700 text-white text-xs px-2 py-1 rounded shadow-lg whitespace-nowrap z-50">Live Dashboard</div>
            </a>

            <!-- Monitoring Menu -->
            <a href="{{ route('monitoring') }}" class="flex items-center px-3 py-2.5 {{ request()->routeIs('monitoring*') ? 'bg-gray-800/50 text-white border border-gray-700/50' : 'text-gray-400 hover:text-white hover:bg-gray-800/30' }} rounded-lg transition-all group relative">
                <div class="flex items-center" :class="sidebarOpen ? 'justify-start w-full' : 'justify-center w-full'">
                    <svg class="shrink-0 w-5 h-5 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="ml-3 font-medium text-sm whitespace-nowrap">Monitoring</span>
                </div>

                <div x-show="!sidebarOpen" class="sidebar-tooltip absolute left-14 bg-gray-800 border border-gray-700 text-white text-xs px-2 py-1 rounded shadow-lg whitespace-nowrap z-50">Monitoring</div>
            </a>

            <!-- Laporan Menu -->
            <a href="{{ route('laporan.index') }}" class="flex items-center px-3 py-2.5 {{ request()->routeIs('laporan*') ? 'bg-gray-800/50 text-white border border-gray-700/50' : 'text-gray-400 hover:text-white hover:bg-gray-800/30' }} rounded-lg transition-all group relative">
                <div class="flex items-center" :class="sidebarOpen ? 'justify-start w-full' : 'justify-center w-full'">
                    <svg class="shrink-0 w-5 h-5 group-hover:text-green-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="ml-3 font-medium text-sm whitespace-nowrap">Laporan</span>
                </div>

                <div x-show="!sidebarOpen" class="sidebar-tooltip absolute left-14 bg-gray-800 border border-gray-700 text-white text-xs px-2 py-1 rounded shadow-lg whitespace-nowrap z-50">Laporan</div>
            </a>

            <!-- Analisis Data Excel Dropdown -->
            <div x-data="{ openAnalisis: {{ request()->routeIs('analisis.*') ? 'true' : 'false' }} }" class="relative">
                <button @click="openAnalisis = !openAnalisis; if(!sidebarOpen) sidebarOpen = true;" class="w-full flex items-center px-3 py-2.5 {{ request()->routeIs('analisis.*') ? 'bg-gray-800/50 text-white border border-gray-700/50' : 'text-gray-400 hover:text-white hover:bg-gray-800/30' }} rounded-lg transition-all group">
                    <div class="flex items-center flex-1" :class="sidebarOpen ? 'justify-start' : 'justify-center'">
                        <svg class="shrink-0 w-5 h-5 group-hover:text-emerald-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                        </svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="ml-3 font-medium text-sm whitespace-nowrap">Analisis Excel</span>
                    </div>
                    
                    <svg x-show="sidebarOpen" x-transition.opacity class="w-4 h-4 transition-transform duration-200" :class="openAnalisis ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                
                <div x-show="!sidebarOpen" class="sidebar-tooltip absolute left-14 top-2 bg-gray-800 border border-gray-700 text-white text-xs px-2 py-1 rounded shadow-lg whitespace-nowrap z-50">Analisis Excel</div>

                <div x-show="openAnalisis && sidebarOpen" x-collapse class="pl-11 pr-3 py-2 space-y-1">
                    <a href="{{ route('analisis.rq.nitrat') }}" class="block px-2 py-1.5 text-sm {{ request()->routeIs('analisis.rq.nitrat') ? 'text-emerald-400 font-medium' : 'text-gray-500 hover:text-gray-300' }} transition-colors whitespace-nowrap">RQ Nitrat</a>
                    <a href="{{ route('analisis.rq.pb') }}" class="block px-2 py-1.5 text-sm {{ request()->routeIs('analisis.rq.pb') ? 'text-emerald-400 font-medium' : 'text-gray-500 hover:text-gray-300' }} transition-colors whitespace-nowrap">RQ Pb</a>
                    <a href="{{ route('analisis.rq.cd') }}" class="block px-2 py-1.5 text-sm {{ request()->routeIs('analisis.rq.cd') ? 'text-emerald-400 font-medium' : 'text-gray-500 hover:text-gray-300' }} transition-colors whitespace-nowrap">RQ Cd</a>
                    <a href="{{ route('analisis.rq.ph') }}" class="block px-2 py-1.5 text-sm {{ request()->routeIs('analisis.rq.ph') ? 'text-emerald-400 font-medium' : 'text-gray-500 hover:text-gray-300' }} transition-colors whitespace-nowrap">RQ Ph</a>
                    <a href="{{ route('analisis.rq.f') }}" class="block px-2 py-1.5 text-sm {{ request()->routeIs('analisis.rq.f') ? 'text-emerald-400 font-medium' : 'text-gray-500 hover:text-gray-300' }} transition-colors whitespace-nowrap">RQ F</a>
                    <div class="h-px bg-gray-800 my-1 w-full"></div>
                    <a href="{{ route('analisis.input') }}" class="block px-2 py-1.5 text-sm {{ request()->routeIs('analisis.input') ? 'text-blue-400 font-medium' : 'text-gray-500 hover:text-gray-300' }} transition-colors whitespace-nowrap">Input Data</a>
                </div>
            </div>

            {{-- Admin only --}}
            @if(auth()->user()->isDireksi())
            <div class="pt-3 mt-2 border-t border-gray-800/50">
                <p x-show="sidebarOpen" x-transition.opacity.duration.300ms class="px-3 mb-1 text-[10px] font-bold uppercase tracking-widest text-gray-600 whitespace-nowrap">Administrasi</p>
                <div x-show="!sidebarOpen" class="h-px bg-gray-700/30 w-8 mx-auto mb-2 mt-4"></div>
                
                <a href="{{ route('admin.users.index') }}" class="flex items-center px-3 py-2.5 {{ request()->routeIs('admin.users*') ? 'bg-gray-800/50 text-white border border-gray-700/50' : 'text-gray-400 hover:text-white hover:bg-gray-800/30' }} rounded-lg transition-all group relative">
                    <div class="flex items-center" :class="sidebarOpen ? 'justify-start w-full' : 'justify-center w-full'">
                        <svg class="shrink-0 w-5 h-5 group-hover:text-purple-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="ml-3 font-medium text-sm whitespace-nowrap">Manajemen Akun</span>
                    </div>

                    <div x-show="!sidebarOpen" class="sidebar-tooltip absolute left-14 bg-gray-800 border border-gray-700 text-white text-xs px-2 py-1 rounded shadow-lg whitespace-nowrap z-50">Manajemen Akun</div>
                </a>
                
                <a href="{{ route('settings.index') }}" class="flex items-center px-3 py-2.5 mt-1 {{ request()->routeIs('settings*') ? 'bg-gray-800/50 text-white border border-gray-700/50' : 'text-gray-400 hover:text-white hover:bg-gray-800/30' }} rounded-lg transition-all group relative">
                    <div class="flex items-center" :class="sidebarOpen ? 'justify-start w-full' : 'justify-center w-full'">
                        <svg class="shrink-0 w-5 h-5 group-hover:text-amber-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span x-show="sidebarOpen" x-transition.opacity.duration.300ms class="ml-3 font-medium text-sm whitespace-nowrap">Pengaturan</span>
                    </div>

                    <div x-show="!sidebarOpen" class="sidebar-tooltip absolute left-14 bg-gray-800 border border-gray-700 text-white text-xs px-2 py-1 rounded shadow-lg whitespace-nowrap z-50">Pengaturan</div>
                </a>
            </div>
            @endif
        </nav>
        
        <div class="absolute bottom-0 w-full p-3 border-t border-gray-800/50 transition-all duration-300" x-data="{ open: false }" @click.away="open = false" :class="sidebarOpen ? 'bg-gray-900/40' : 'bg-transparent text-center px-1'">
            {{-- User Info Toggle --}}
            <button @click="if(sidebarOpen) open = !open; else sidebarOpen = true;" class="flex items-center rounded-lg hover:bg-gray-800/50 transition-colors group" :class="sidebarOpen ? 'w-full px-2 py-2 gap-3' : 'px-2 py-2 justify-center'">
                <img 
                    src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=4f46e5&color=fff&rounded=true" 
                    alt="Avatar" 
                    class="w-9 h-9 rounded-full ring-2 ring-gray-700 shrink-0 mx-auto"
                >
                <div x-show="sidebarOpen" x-transition.opacity.duration.300ms class="flex-1 text-left min-w-0">
                    <p class="text-sm font-medium text-gray-200 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] font-semibold uppercase tracking-wider {{ auth()->user()->isDireksi() ? 'text-purple-400' : 'text-blue-400' }}">
                        {{ auth()->user()->role }}
                    </p>
                </div>
                <svg x-show="sidebarOpen" x-transition.opacity class="w-4 h-4 text-gray-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            {{-- Dropdown Menu --}}
            <div x-show="open && sidebarOpen" x-transition class="mt-2 bg-gray-800/95 backdrop-blur-md border border-gray-700/50 rounded-lg overflow-hidden shadow-xl absolute bottom-[4.5rem] w-[calc(100%-1.5rem)] left-3 z-50">
                <a href="{{ route('profile') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-300 hover:bg-gray-700/50 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Edit Profil
                </a>
                <div class="border-t border-gray-700/50"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main :class="sidebarOpen ? 'ml-64' : 'ml-20'" class="flex-1 flex flex-col min-h-screen relative overflow-x-hidden transition-all duration-300 ease-in-out">
        <!-- Ambient Background Glows -->
        <div class="fixed top-[-15%] left-[-10%] w-[50%] h-[50%] rounded-full bg-blue-900/10 blur-[150px] pointer-events-none z-0"></div>
        <div class="fixed bottom-[-10%] right-[-10%] w-[40%] h-[50%] rounded-full bg-purple-900/15 blur-[150px] pointer-events-none z-0"></div>

        <div class="relative z-10 w-full p-6 lg:p-8 flex-1">
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="relative z-10 w-full p-6 border-t border-gray-800/50 mt-auto hidden md:block">
            <div class="text-center text-sm text-gray-500">
                &copy; {{ $app_settings['app_year'] ?? date('Y') }} <span class="text-gray-400 font-medium">{{ $app_settings['app_copyright'] ?? 'Universitas Hasanuddin' }}</span>. All rights reserved.
                <br>
                {{ $app_settings['app_institution'] ?? '' }}
            </div>
        </footer>
    </main>
    
    <!-- Global Flash Notification Component -->
    <div x-data="toastNotification()" x-init="initToast()"
         style="position: fixed !important; top: 1.5rem; right: 1.5rem; left: auto; z-index: 99999; display: flex; flex-direction: column; gap: 0.5rem; pointer-events: none; width: 320px;">
        
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-8"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-8"
                 role="alert"
                 :style="{
                     'pointer-events': 'auto',
                     'border-radius': '0.5rem',
                     'padding': '0.6rem 0.75rem',
                     'display': 'flex',
                     'align-items': 'center',
                     'gap': '0.5rem',
                     'border-left': '4px solid',
                     'transition': 'all 0.3s ease',
                     'cursor': 'default',
                     'background': toast.type === 'success' ? '#052e16' :
                                   toast.type === 'error'   ? '#450a0a' :
                                   toast.type === 'warning' ? '#422006' :
                                                              '#172554',
                     'border-left-color': toast.type === 'success' ? '#15803d' :
                                          toast.type === 'error'   ? '#b91c1c' :
                                          toast.type === 'warning' ? '#a16207' :
                                                                     '#1d4ed8',
                     'color': toast.type === 'success' ? '#bbf7d0' :
                              toast.type === 'error'   ? '#fecaca' :
                              toast.type === 'warning' ? '#fef08a' :
                                                         '#bfdbfe'
                 }">
                <!-- Icon -->
                <svg :style="{
                         'width': '20px',
                         'height': '20px',
                         'flex-shrink': '0',
                         'color': toast.type === 'success' ? '#4ade80' :
                                  toast.type === 'error'   ? '#f87171' :
                                  toast.type === 'warning' ? '#facc15' :
                                                             '#60a5fa'
                     }"
                     stroke="currentColor" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13 16h-1v-4h1m0-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"></path>
                </svg>
                <!-- Message -->
                <p style="font-size:0.75rem; font-weight:600; flex:1; margin:0; word-break:break-word;" x-text="toast.message"></p>
                <!-- Close Button -->
                <button @click="remove(toast.id)" style="flex-shrink:0; background:none; border:none; cursor:pointer; padding:0; opacity:0.6; line-height:1;"
                        :style="{ 'color': toast.type === 'success' ? '#86efac' : toast.type === 'error' ? '#fca5a5' : toast.type === 'warning' ? '#fde68a' : '#93c5fd' }">
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </template>
    </div>

    @stack('scripts')
    
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('toastNotification', () => ({
                toasts: [],
                initToast() {
                    let type = null;
                    let message = null;
                    
                    @if(session('success')) type = 'success'; message = "{!! addslashes(session('success')) !!}"; @endif
                    @if(session('error')) type = 'error'; message = "{!! addslashes(session('error')) !!}"; @endif
                    @if(session('warning')) type = 'warning'; message = "{!! addslashes(session('warning')) !!}"; @endif
                    @if(session('info')) type = 'info'; message = "{!! addslashes(session('info')) !!}"; @endif

                    if (type && message) {
                        this.add(type, message);
                    }
                },
                add(type, message) {
                    const id = Date.now();
                    this.toasts.push({ id, type, message, visible: true });
                    setTimeout(() => this.remove(id), 4000); // Auto dismiss after 4 seconds
                },
                remove(id) {
                    const toast = this.toasts.find(t => t.id === id);
                    if (toast) {
                        toast.visible = false;
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(t => t.id !== id);
                        }, 300); // Wait for exit animation
                    }
                }
            }));
        });
    </script>
</body>
</html>
