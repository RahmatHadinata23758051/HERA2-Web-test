<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $app_settings['app_name'] ?? 'HERA' }} - {{ $app_settings['app_description'] ?? 'Real-time Chromium Monitoring' }}</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": {
                "on-error-container": "#93000a",
                "surface-container-lowest": "#ffffff",
                "on-secondary-container": "#446d58",
                "on-error": "#ffffff",
                "inverse-on-surface": "#eff1f3",
                "on-primary": "#ffffff",
                "primary-fixed": "#85f8c4",
                "on-surface": "#191c1e",
                "error": "#ba1a1a",
                "surface-container-high": "#e6e8ea",
                "background": "#f7f9fb",
                "tertiary-container": "#ba5551",
                "surface": "#f7f9fb",
                "surface-container": "#eceef0",
                "on-primary-container": "#f5fff7",
                "error-container": "#ffdad6",
                "tertiary-fixed": "#ffdad7",
                "on-primary-fixed-variant": "#005137",
                "outline": "#6d7a72",
                "tertiary": "#9b3e3b",
                "primary": "#006948",
                "secondary-container": "#c0edd3",
                "secondary": "#3e6753",
                "surface-container-low": "#f2f4f6",
                "on-background": "#191c1e",
                "primary-fixed-dim": "#68dba9",
                "surface-variant": "#e0e3e5",
                "on-secondary": "#ffffff",
                "secondary-fixed": "#c0edd3",
                "on-surface-variant": "#3d4a42",
                "on-primary-fixed": "#002114",
                "outline-variant": "#bccac0",
                "surface-bright": "#f7f9fb",
                "inverse-primary": "#68dba9",
                "primary-container": "#00855d",
                "surface-container-highest": "#e0e3e5",
                "on-tertiary": "#ffffff",
                "tertiary-fixed-dim": "#ffb3ae",
              },
              "fontFamily": {
                "headline": ["Manrope", "sans-serif"],
                "body": ["Inter", "sans-serif"],
                "label": ["Inter", "sans-serif"]
              }
            },
          },
        };
    </script>
    
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Manrope', sans-serif; }
        
        /* Custom Scrollbar */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .value-update-flash { animation: textFlash 1.5s ease-out; }
        @keyframes textFlash {
            0% { color: #006948; text-shadow: 0 0 10px rgba(0,105,72,0.4); }
            100% { color: inherit; text-shadow: none; }
        }
    </style>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

<body class="bg-background text-on-surface min-h-screen overflow-x-hidden flex flex-col" x-data="{ mobileMenuOpen: false }">

    <!-- ══════════════════════════════════════════════════ -->
    <!-- 2-TIER TOP NAVBAR (Clinical Light Mode)           -->
    <!-- ══════════════════════════════════════════════════ -->
    <header class="fixed top-0 w-full z-50">
        
        <!-- Tier 1: Branding & Profile -->
        <div class="h-14 bg-white border-b border-surface-container-high shadow-sm">
            <div class="px-4 md:px-8 lg:px-12 h-full flex items-center justify-between max-w-[1800px] mx-auto">
                
                <!-- Logo & App Name -->
                <div class="flex items-center gap-3">
                    @if(!empty($app_settings['app_logo']))
                        <img src="{{ asset($app_settings['app_logo']) }}" alt="Logo" class="h-8 object-contain">
                    @else
                        <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center shadow-sm shadow-primary/30">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                    @endif
                    <h1 class="text-lg font-black tracking-tight text-on-surface whitespace-nowrap font-headline">
                        {{ $app_settings['app_name'] ?? 'HERA' }}
                    </h1>
                </div>

                <!-- Right: User Profile + Mobile Toggle -->
                <div class="flex items-center gap-3">
                    
                    <!-- Desktop Profile Dropdown -->
                    <div class="hidden md:block">
                        <div x-data="{ userMenu: false }" @click.away="userMenu = false" class="relative">
                            <button @click="userMenu = !userMenu" 
                                    class="flex items-center gap-2 pl-3 pr-2 py-1.5 rounded-full hover:bg-surface-container-low transition-colors border border-transparent hover:border-outline-variant focus:outline-none">
                                @if(auth()->user()->picture)
                                    <img src="{{ asset('storage/' . auth()->user()->picture) }}" alt="Avatar" class="w-7 h-7 rounded-full object-cover ring-2 ring-primary/20">
                                @else
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=006948&color=fff&rounded=true" alt="Avatar" class="w-7 h-7 rounded-full ring-2 ring-primary/20">
                                @endif
                                <div class="text-left">
                                    <p class="text-xs font-bold text-on-surface leading-none">{{ Str::words(auth()->user()->name, 1, '') }}</p>
                                    <p class="text-[9px] font-bold uppercase tracking-widest text-primary leading-none mt-0.5">{{ auth()->user()->role }}</p>
                                </div>
                                <svg class="w-3.5 h-3.5 text-outline transition-transform" :class="userMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            
                            <!-- User Dropdown Menu -->
                            <div x-show="userMenu" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" style="display:none;"
                                 class="absolute right-0 mt-2 w-52 bg-white border border-surface-container-high rounded-xl shadow-xl py-1.5 z-50 origin-top-right">
                                <div class="px-4 py-2.5 border-b border-surface-container-high mb-1">
                                    <p class="text-sm font-bold text-on-surface truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-primary truncate">{{ auth()->user()->role }}</p>
                                </div>
                                <a href="{{ route('profile') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-on-surface-variant hover:text-primary hover:bg-surface-container-low transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Edit Profil
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-error hover:bg-error-container transition-colors text-left">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Hamburger -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-on-surface-variant hover:text-primary p-2 rounded-lg hover:bg-surface-container-low transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="mobileMenuOpen" style="display:none;" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tier 2: Navigation Links -->
        <div class="hidden md:block h-12 bg-white border-b border-surface-container-highest">
            <div class="px-4 md:px-8 lg:px-12 h-full flex items-center gap-1 max-w-[1800px] mx-auto">
                
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-1.5 h-full px-3 text-sm font-medium transition-all duration-200 border-b-2 rounded-t-sm {{ request()->routeIs('dashboard') ? 'text-primary border-primary font-bold bg-primary/[.06]' : 'text-on-surface-variant border-transparent hover:text-primary hover:border-primary/50 hover:bg-primary/[.04]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>

                <!-- Monitoring -->
                <a href="{{ route('monitoring') }}" 
                   class="flex items-center gap-1.5 h-full px-3 text-sm font-medium transition-all duration-200 border-b-2 rounded-t-sm {{ request()->routeIs('monitoring*') ? 'text-primary border-primary font-bold bg-primary/[.06]' : 'text-on-surface-variant border-transparent hover:text-primary hover:border-primary/50 hover:bg-primary/[.04]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Monitoring
                </a>

                <!-- Analisis Data Excel Dropdown -->
                <div x-data="{ openAnalysis: false }" @mouseenter="openAnalysis = true" @mouseleave="openAnalysis = false" class="relative h-full flex items-center">
                    <button @click="openAnalysis = !openAnalysis" 
                            class="flex items-center gap-1.5 h-full px-3 text-sm font-medium outline-none transition-all duration-200 border-b-2 rounded-t-sm {{ request()->routeIs('analisis.*') ? 'text-primary border-primary font-bold bg-primary/[.06]' : 'text-on-surface-variant border-transparent hover:text-primary hover:border-primary/50 hover:bg-primary/[.04]' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                        Analisis Data Excel
                        <svg class="w-3 h-3 transition-transform" :class="openAnalysis ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openAnalysis" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" style="display:none;"
                         class="absolute top-12 left-0 w-52 bg-white border border-surface-container-high rounded-xl shadow-xl py-1.5 z-50">
                        <a href="{{ route('analisis.rq.nitrat') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('analisis.rq.nitrat') ? 'text-primary font-bold bg-surface-container-low' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-low' }}">RQ Nitrat</a>
                        <a href="{{ route('analisis.rq.pb') }}"    class="block px-4 py-2 text-sm {{ request()->routeIs('analisis.rq.pb') ? 'text-primary font-bold bg-surface-container-low' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-low' }}">RQ Pb</a>
                        <a href="{{ route('analisis.rq.cd') }}"    class="block px-4 py-2 text-sm {{ request()->routeIs('analisis.rq.cd') ? 'text-primary font-bold bg-surface-container-low' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-low' }}">RQ Cd</a>
                        <a href="{{ route('analisis.rq.ph') }}"    class="block px-4 py-2 text-sm {{ request()->routeIs('analisis.rq.ph') ? 'text-primary font-bold bg-surface-container-low' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-low' }}">RQ Ph</a>
                        <a href="{{ route('analisis.rq.f') }}"     class="block px-4 py-2 text-sm {{ request()->routeIs('analisis.rq.f') ? 'text-primary font-bold bg-surface-container-low' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-low' }}">RQ F</a>
                        <div class="border-t border-surface-container-high my-1 mx-2"></div>
                        <a href="{{ route('analisis.input') }}"    class="block px-4 py-2 text-sm {{ request()->routeIs('analisis.input') ? 'text-primary font-bold bg-surface-container-low' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-low' }}">Input Data Manual</a>
                    </div>
                </div>

                @if(auth()->user()->isDireksi())
                <!-- Manajemen Akun -->
                <a href="{{ route('admin.users.index') }}" 
                   class="flex items-center gap-1.5 h-full px-3 text-sm font-medium transition-all duration-200 border-b-2 rounded-t-sm {{ request()->routeIs('admin.users*') ? 'text-primary border-primary font-bold bg-primary/[.06]' : 'text-on-surface-variant border-transparent hover:text-primary hover:border-primary/50 hover:bg-primary/[.04]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Manajemen Akun
                </a>
                @endif

                <!-- Laporan Dropdown -->
                <div x-data="{ openLaporan: false }" @mouseenter="openLaporan = true" @mouseleave="openLaporan = false" class="relative h-full flex items-center">
                    <button @click="openLaporan = !openLaporan" 
                            class="flex items-center gap-1.5 h-full px-3 text-sm font-medium outline-none transition-all duration-200 border-b-2 rounded-t-sm {{ request()->routeIs('laporan.*') || request()->routeIs('pengujian.*') ? 'text-primary border-primary font-bold bg-primary/[.06]' : 'text-on-surface-variant border-transparent hover:text-primary hover:border-primary/50 hover:bg-primary/[.04]' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Laporan
                        <svg class="w-3 h-3 transition-transform" :class="openLaporan ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openLaporan" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" style="display:none;"
                         class="absolute top-12 left-0 w-56 bg-white border border-surface-container-high rounded-xl shadow-xl py-1.5 z-50">
                        <a href="{{ route('laporan.index') }}"           class="block px-4 py-2.5 text-sm {{ request()->routeIs('laporan.index') ? 'text-primary font-bold bg-surface-container-low' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-low' }}">Laporan Historis</a>
                        <div class="border-t border-surface-container-high my-1 mx-2"></div>
                        <a href="{{ route('laporan.pengujian.index') }}" class="block px-4 py-2.5 text-sm {{ request()->routeIs('laporan.pengujian.*') ? 'text-primary font-bold bg-surface-container-low' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-low' }}">Data Pengujian Lapangan</a>
                    </div>
                </div>

                @if(auth()->user()->isDireksi())
                {{-- Log Aktivitas --}}
                <a href="{{ route('activity-log.index') }}"
                   class="flex items-center gap-1.5 h-full px-3 text-sm font-medium transition-all duration-200 border-b-2 rounded-t-sm {{ request()->routeIs('activity-log*') ? 'text-primary border-primary font-bold bg-primary/[.06]' : 'text-on-surface-variant border-transparent hover:text-primary hover:border-primary/50 hover:bg-primary/[.04]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Log Aktivitas
                </a>

                {{-- Pengaturan --}}
                <a href="{{ route('settings.index') }}"
                   class="flex items-center gap-1.5 h-full px-3 text-sm font-medium transition-all duration-200 border-b-2 rounded-t-sm {{ request()->routeIs('settings*') ? 'text-primary border-primary font-bold bg-primary/[.06]' : 'text-on-surface-variant border-transparent hover:text-primary hover:border-primary/50 hover:bg-primary/[.04]' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Pengaturan
                </a>
                @endif
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display:none;" 
             class="md:hidden bg-white border-b border-surface-container-high shadow-md pb-4 px-4 space-y-1">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">Dashboard</a>
            <a href="{{ route('monitoring') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('monitoring*') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">Monitoring</a>
            <div class="px-3 pt-1 pb-2">
                <p class="text-[10px] font-bold text-outline uppercase tracking-widest mb-1">Analisis Data Excel</p>
                <div class="pl-2 border-l-2 border-surface-container-high space-y-1">
                    <a href="{{ route('analisis.rq.nitrat') }}" class="block text-sm py-1 {{ request()->routeIs('analisis.rq.nitrat') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }}">RQ Nitrat</a>
                    <a href="{{ route('analisis.rq.pb') }}"    class="block text-sm py-1 {{ request()->routeIs('analisis.rq.pb') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }}">RQ Pb</a>
                    <a href="{{ route('analisis.rq.cd') }}"    class="block text-sm py-1 {{ request()->routeIs('analisis.rq.cd') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }}">RQ Cd</a>
                    <a href="{{ route('analisis.rq.ph') }}"    class="block text-sm py-1 {{ request()->routeIs('analisis.rq.ph') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }}">RQ Ph</a>
                    <a href="{{ route('analisis.rq.f') }}"     class="block text-sm py-1 {{ request()->routeIs('analisis.rq.f') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }}">RQ F</a>
                    <a href="{{ route('analisis.input') }}"    class="block text-sm py-1 mt-1 pt-2 border-t border-surface-container-high {{ request()->routeIs('analisis.input') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }}">Input Manual</a>
                </div>
            </div>
            @if(auth()->user()->isDireksi())
            <a href="{{ route('admin.users.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.users*') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">Manajemen Akun</a>
            @endif
            <div class="px-3 pt-1 pb-2">
                <p class="text-[10px] font-bold text-outline uppercase tracking-widest mb-1">Laporan</p>
                <div class="pl-2 border-l-2 border-surface-container-high space-y-1">
                    <a href="{{ route('laporan.index') }}"           class="block text-sm py-1 {{ request()->routeIs('laporan.index') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }}">Laporan Historis</a>
                    <a href="{{ route('laporan.pengujian.index') }}" class="block text-sm py-1 {{ request()->routeIs('laporan.pengujian.*') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }}">Pengujian Lapangan</a>
                </div>
            </div>
            @if(auth()->user()->isDireksi())
            <a href="{{ route('activity-log.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('activity-log*') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">Log Aktivitas</a>
            <a href="{{ route('settings.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('settings*') ? 'bg-primary/10 text-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container-low' }}">Pengaturan</a>
            @endif
            <div class="border-t border-surface-container-high pt-3 mt-1 flex flex-col gap-1">
                <a href="{{ route('profile') }}" class="block px-3 py-2 text-sm text-on-surface-variant hover:text-primary">Edit Profil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 text-sm text-error hover:bg-error-container rounded-lg">Keluar</button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content: pt-[104px] = 56px Tier1 + 48px Tier2 -->
    <main class="pt-[104px] flex-1 flex flex-col min-h-screen relative overflow-x-hidden">
        <!-- Subtle ambient glow -->
        <div class="fixed top-0 right-0 w-[40%] h-[40%] rounded-full bg-primary/5 blur-[150px] pointer-events-none z-0"></div>

        <div class="relative z-10 w-full px-4 sm:px-6 lg:px-10 max-w-[1600px] mx-auto pb-10 flex-1 pt-6">
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="relative z-10 w-full border-t border-surface-container-high mt-auto hidden md:block bg-white">
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 py-5 text-center text-sm text-outline">
                &copy; {{ $app_settings['app_year'] ?? date('Y') }} 
                <span class="text-on-surface-variant font-medium">{{ $app_settings['app_copyright'] ?? 'Universitas Hasanuddin' }}</span>. All rights reserved.
                <br>
                <span class="text-xs">{{ $app_settings['app_institution'] ?? '' }}</span>
            </div>
        </footer>
    </main>

    <!-- Global Flash Notification Toast -->
    <div x-data="toastNotification()" x-init="initToast()"
         style="position: fixed !important; top: 7rem; right: 1.5rem; left: auto; z-index: 99999; display: flex; flex-direction: column; gap: 0.5rem; pointer-events: none; width: 320px;">
        
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
                     'border-radius': '0.75rem',
                     'padding': '0.75rem 1rem',
                     'display': 'flex',
                     'align-items': 'center',
                     'gap': '0.75rem',
                     'border-left': '4px solid',
                     'background': '#ffffff',
                     'box-shadow': '0 4px 12px rgba(0,0,0,0.08)',
                     'border-color': toast.type === 'success' ? '#006948' :
                                     toast.type === 'error'   ? '#ba1a1a' :
                                     toast.type === 'warning' ? '#eab308' :
                                                                '#3b82f6',
                 }">
                <p style="font-size:0.875rem; font-weight:600; flex:1; margin:0; color: #191c1e;" x-text="toast.message"></p>
                <button @click="remove(toast.id)" style="flex-shrink:0; background:none; border:none; cursor:pointer; padding:0; color: #6d7a72;">
                    <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
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
                    if (type && message) this.add(type, message);
                },
                add(type, message) {
                    const id = Date.now();
                    this.toasts.push({ id, type, message, visible: true });
                    setTimeout(() => this.remove(id), 4000);
                },
                remove(id) {
                    const toast = this.toasts.find(t => t.id === id);
                    if (toast) {
                        toast.visible = false;
                        setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 300);
                    }
                }
            }));
        });
    </script>
</body>
</html>
