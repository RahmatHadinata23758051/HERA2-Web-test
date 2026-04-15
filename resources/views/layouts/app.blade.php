<!DOCTYPE html>
<html lang="id" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $app_settings['app_name'] ?? 'HERA' }} - {{ $app_settings['app_description'] ?? 'Monitoring System' }}</title>
    
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
                      "on-tertiary-fixed": "#410004",
                      "primary-fixed": "#85f8c4",
                      "on-surface": "#191c1e",
                      "error": "#ba1a1a",
                      "surface-container-high": "#e6e8ea",
                      "surface-tint": "#006c4a",
                      "background": "#f7f9fb",
                      "secondary-fixed-dim": "#a4d0b8",
                      "tertiary-container": "#ba5551",
                      "surface": "#f7f9fb",
                      "surface-container": "#eceef0",
                      "on-primary-container": "#f5fff7",
                      "on-tertiary-container": "#fffbff",
                      "on-secondary-fixed-variant": "#264e3c",
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
                      "inverse-surface": "#2d3133",
                      "primary-fixed-dim": "#68dba9",
                      "surface-variant": "#e0e3e5",
                      "surface-dim": "#d8dadc",
                      "on-secondary": "#ffffff",
                      "secondary-fixed": "#c0edd3",
                      "on-surface-variant": "#3d4a42",
                      "on-primary-fixed": "#002114",
                      "outline-variant": "#bccac0",
                      "surface-bright": "#f7f9fb",
                      "inverse-primary": "#68dba9",
                      "primary-container": "#00855d",
                      "on-secondary-fixed": "#002114",
                      "surface-container-highest": "#e0e3e5",
                      "on-tertiary": "#ffffff",
                      "tertiary-fixed-dim": "#ffb3ae",
                      "on-tertiary-fixed-variant": "#7f2928"
              },
              "borderRadius": {
                      "DEFAULT": "0.25rem",
                      "lg": "1rem",
                      "xl": "1.25rem",
                      "full": "9999px"
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
        .no-scrollbar::-webkit-scrollbar { display: none; }
        
        /* Custom Scrollbar for tables */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .value-update-flash { animation: textFlash 1.5s ease-out; }
        @keyframes textFlash {
            0% { color: #006948; text-shadow: 0 0 10px rgba(0,105,72,0.5); }
            100% { color: inherit; text-shadow: none; }
        }
    </style>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

<body class="bg-background text-on-surface min-h-screen" x-data="{ mobileMenuOpen: false, sidebarOpen: false }">

<!-- TopNavBar -->
<header class="bg-white border-b border-surface-container-high flex justify-between items-center w-full px-4 lg:px-8 h-16 max-w-full fixed top-0 z-50 shadow-sm">
    <div class="flex items-center gap-4 lg:gap-8">
        <!-- Mobile Sidebar Toggle -->
        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 text-on-surface-variant hover:bg-slate-50 rounded-md">
            <span class="material-symbols-outlined">menu</span>
        </button>

        <div class="flex items-center gap-2">
            @if(!empty($app_settings['app_logo']))
                <img src="{{ asset($app_settings['app_logo']) }}" alt="App Logo" class="h-8 object-contain">
            @else
                <span class="material-symbols-outlined text-primary text-3xl" data-icon="clinical_notes">clinical_notes</span>
            @endif
            <span class="text-xl font-black tracking-tighter text-on-surface">{{ $app_settings['app_name'] ?? 'HERA' }}</span>
        </div>
    </div>

    <!-- Right Topbar Items -->
    <div class="flex items-center gap-2 sm:gap-4">
        <!-- Notification Placeholder -->
        <button class="hidden md:flex p-2 text-on-surface-variant hover:bg-slate-50 rounded-full transition-all">
            <span class="material-symbols-outlined">notifications</span>
        </button>
        
        <script>
            function toggleUserMenu() {
                let el = document.getElementById('userMenuDropdown');
                el.style.display = el.style.display === 'none' ? 'block' : 'none';
            }
        </script>
        
        <!-- Profile Dropdown Menu in Topbar -->
        <div class="relative">
            <button onclick="toggleUserMenu()" class="flex items-center gap-2 p-1 rounded-full hover:bg-slate-50 transition-colors border border-transparent focus:outline-none ring-2 ring-primary/20">
                @if(auth()->user()->picture)
                    <img src="{{ asset('storage/' . auth()->user()->picture) }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                @else
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=006948&color=fff&rounded=true" alt="Avatar" class="w-8 h-8 rounded-full">
                @endif
            </button>
            <div id="userMenuDropdown" class="absolute right-0 mt-2 w-48 bg-white border border-surface-container-high rounded-lg shadow-xl py-1 z-50" style="display:none;">
                <div class="px-4 py-2 border-b border-surface-container-high mb-1">
                    <p class="text-sm font-bold text-on-surface truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-primary truncate">{{ auth()->user()->role }}</p>
                </div>
                <a href="{{ route('profile') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-on-surface-variant hover:text-primary hover:bg-slate-50 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">person</span> Edit Profil
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-error hover:bg-error-container hover:text-on-error-container transition-colors text-left">
                        <span class="material-symbols-outlined text-[18px]">logout</span> Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<!-- SideNavBar -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed left-0 top-16 h-[calc(100vh-64px)] w-64 bg-surface-container-lowest lg:bg-transparent lg:translate-x-0 transition-transform duration-300 ease-in-out z-40 border-r border-surface-container-high lg:flex lg:flex-col p-4 overflow-y-auto hidden-scroll bg-white">
    <div class="mb-6 px-4 py-2">
        <h2 class="text-sm font-bold text-on-surface font-headline uppercase tracking-wider text-primary">Modul Utama</h2>
    </div>
    
    <nav class="flex flex-col gap-2 flex-grow">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 ease-out {{ request()->routeIs('dashboard') ? 'bg-primary/10 text-primary font-bold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-low font-medium' }}">
            <span class="material-symbols-outlined" data-icon="dashboard">dashboard</span>
            <span class="text-sm font-label">Overview</span>
        </a>
        
        <!-- Monitoring -->
        <a href="{{ route('monitoring') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 ease-out {{ request()->routeIs('monitoring*') ? 'bg-primary/10 text-primary font-bold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-low font-medium' }}">
            <span class="material-symbols-outlined" data-icon="opacity">opacity</span>
            <span class="text-sm font-label">Monitoring</span>
        </a>

        <!-- Analisis Data Excel Dropdown -->
        <div x-data="{ open: {{ request()->routeIs('analisis.*') ? 'true' : 'false' }} }" class="rounded-lg {{ request()->routeIs('analisis.*') ? 'bg-primary/5 border border-primary/10' : '' }}">
            <button @click="open = !open" class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-lg transition-all duration-200 ease-out text-on-surface-variant hover:bg-surface-container-low font-medium">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined" data-icon="analytics">analytics</span>
                    <span class="text-sm font-label">Analisis Data Excel</span>
                </div>
                <span class="material-symbols-outlined transition-transform text-sm" :class="open ? 'rotate-180' : ''">expand_more</span>
            </button>
            <div x-show="open" x-transition.opacity class="pl-11 pr-4 py-2 flex flex-col gap-1 border-t border-surface-container-high/50 mx-2" style="display: {{ request()->routeIs('analisis.*') ? 'flex' : 'none' }};">
                <a href="{{ route('analisis.rq.nitrat') }}" class="text-sm py-1.5 {{ request()->routeIs('analisis.rq.nitrat') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }}">RQ Nitrat</a>
                <a href="{{ route('analisis.rq.pb') }}" class="text-sm py-1.5 {{ request()->routeIs('analisis.rq.pb') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }}">RQ Pb</a>
                <a href="{{ route('analisis.rq.cd') }}" class="text-sm py-1.5 {{ request()->routeIs('analisis.rq.cd') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }}">RQ Cd</a>
                <a href="{{ route('analisis.rq.ph') }}" class="text-sm py-1.5 {{ request()->routeIs('analisis.rq.ph') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }}">RQ Ph</a>
                <a href="{{ route('analisis.rq.f') }}" class="text-sm py-1.5 {{ request()->routeIs('analisis.rq.f') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }}">RQ F</a>
                <a href="{{ route('analisis.input') }}" class="text-sm py-1.5 mt-1 border-t border-surface-container-high pt-2 {{ request()->routeIs('analisis.input') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }}">Input Manual</a>
            </div>
        </div>

        @if(auth()->user()->isDireksi())
        <!-- Manajemen Akun -->
        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 ease-out {{ request()->routeIs('admin.users*') ? 'bg-primary/10 text-primary font-bold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-low font-medium' }}">
            <span class="material-symbols-outlined" data-icon="group">group</span>
            <span class="text-sm font-label">Manajemen Akun</span>
        </a>
        @endif

        <!-- Laporan Dropdown -->
        <div x-data="{ open: {{ request()->routeIs('laporan.*') ? 'true' : 'false' }} }" class="rounded-lg {{ request()->routeIs('laporan.*') ? 'bg-primary/5 border border-primary/10' : '' }}">
            <button @click="open = !open" class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-lg transition-all duration-200 ease-out text-on-surface-variant hover:bg-surface-container-low font-medium">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined" data-icon="summarize">summarize</span>
                    <span class="text-sm font-label">Laporan Data</span>
                </div>
                <span class="material-symbols-outlined transition-transform text-sm" :class="open ? 'rotate-180' : ''">expand_more</span>
            </button>
            <div x-show="open" x-transition.opacity class="pl-11 pr-4 py-2 flex flex-col gap-1 border-t border-surface-container-high/50 mx-2" style="display: {{ request()->routeIs('laporan.*') ? 'flex' : 'none' }};">
                <a href="{{ route('laporan.index') }}" class="text-sm py-1.5 {{ request()->routeIs('laporan.index') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }}">Laporan Historis</a>
                <a href="{{ route('laporan.pengujian.index') }}" class="text-sm py-1.5 {{ request()->routeIs('laporan.pengujian.*') ? 'text-primary font-bold' : 'text-on-surface-variant hover:text-primary' }}">Pengujian Lapangan</a>
            </div>
        </div>

        @if(auth()->user()->isDireksi())
        <!-- Settings -->
        <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200 ease-out {{ request()->routeIs('settings*') ? 'bg-primary/10 text-primary font-bold shadow-sm' : 'text-on-surface-variant hover:bg-surface-container-low font-medium' }}">
            <span class="material-symbols-outlined" data-icon="settings">settings</span>
            <span class="text-sm font-label">Pengaturan Sistem</span>
        </a>
        @endif
    </nav>
    <div class="mt-auto pt-4 border-t border-surface-container-high flex flex-col gap-2">
        <div class="px-4 py-2 text-center text-xs text-outline font-label">
            &copy; {{ $app_settings['app_year'] ?? date('Y') }} HERA 2.0
            <br>
            Stable Release
        </div>
    </div>
</aside>

<!-- Overlay for mobile sidebar -->
<div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 bg-on-surface/20 z-30 lg:hidden" style="display:none;"></div>

<!-- Main Content Area -->
<main class="lg:ml-64 pt-24 pb-12 px-4 sm:px-8 min-h-screen relative z-10 w-full overflow-x-hidden">
    <!-- Optional: Subtle Background Patterns -->
    <div class="fixed top-0 right-0 w-[40%] h-[40%] rounded-full bg-primary/5 blur-[120px] pointer-events-none z-[-1]"></div>
    
    <div class="max-w-[1600px] mx-auto space-y-8">
        @yield('content')
    </div>
</main>

<!-- Toasts -->
    <div x-data="toastNotification()" x-init="initToast()"
         style="position: fixed !important; top: 5rem; right: 1.5rem; left: auto; z-index: 99999; display: flex; flex-direction: column; gap: 0.5rem; pointer-events: none; width: 320px;">
        
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-8"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-x-0"
                 x-transition:leave-end="opacity-0 translate-x-8"
                 role="alert"
                 class="shadow-lg border bg-surface-container-lowest"
                 :style="{
                     'pointer-events': 'auto',
                     'border-radius': '0.5rem',
                     'padding': '0.6rem 0.75rem',
                     'display': 'flex',
                     'align-items': 'center',
                     'gap': '0.5rem',
                     'border-left': '4px solid',
                     'transition': 'all 0.3s ease',
                     'border-left-color': toast.type === 'success' ? '#006948' :
                                          toast.type === 'error'   ? '#ba1a1a' :
                                          toast.type === 'warning' ? '#eab308' :
                                                                     '#3b82f6',
                 }">
                <!-- Message -->
                <p style="font-size:0.875rem; font-weight:600; flex:1; margin:0;" class="text-on-surface" x-text="toast.message"></p>
                <!-- Close Button -->
                <button @click="remove(toast.id)" class="text-on-surface-variant hover:text-on-surface">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
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
