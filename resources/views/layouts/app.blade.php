<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HERA 2.0 - Water Quality Analytics</title>
    
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
    </style>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen flex selection:bg-indigo-500 selection:text-white">

    <!-- Sidebar -->
    <aside class="w-64 glass-panel border-r border-gray-800 flex-shrink-0 fixed h-full z-20 transition-all duration-300">
        <div class="h-16 flex items-center px-6 border-b border-gray-800/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center shadow-lg shadow-blue-500/30">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h1 class="text-xl font-bold tracking-tight text-gradient">HERA <span class="text-sm font-medium text-gray-400">2.0</span></h1>
            </div>
        </div>
        
        <nav class="p-4 space-y-1">
            <a href="#" class="flex items-center gap-3 px-3 py-2.5 bg-gray-800/50 text-white rounded-lg border border-gray-700/50 shadow-sm transition-all relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-purple-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <svg class="w-5 h-5 text-blue-400 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span class="font-medium text-sm relative z-10">Live Dashboard</span>
            </a>
            
            <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800/30 rounded-lg transition-all group">
                <svg class="w-5 h-5 group-hover:text-purple-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span class="font-medium text-sm">Analytics</span>
            </a>
            
            <a href="#" class="flex items-center gap-3 px-3 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800/30 rounded-lg transition-all group">
                <svg class="w-5 h-5 group-hover:text-green-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium text-sm">History Log</span>
            </a>
        </nav>
        
        <div class="absolute bottom-0 w-full p-4 border-t border-gray-800/50">
            <div class="flex items-center gap-3">
                <img src="https://ui-avatars.com/api/?name=Data+Scientist&background=3b82f6&color=fff&rounded=true" alt="User" class="w-9 h-9 rounded-full ring-2 ring-gray-800">
                <div>
                    <p class="text-sm font-medium text-gray-200">Data Scientist</p>
                    <p class="text-xs text-green-400 font-medium tracking-wide">● Online</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 ml-64 flex flex-col min-h-screen relative overflow-x-hidden">
        <!-- Ambient Background Glows -->
        <div class="fixed top-[-15%] left-[-10%] w-[50%] h-[50%] rounded-full bg-blue-900/10 blur-[150px] pointer-events-none z-0"></div>
        <div class="fixed bottom-[-10%] right-[-10%] w-[40%] h-[50%] rounded-full bg-purple-900/15 blur-[150px] pointer-events-none z-0"></div>

        <div class="relative z-10 w-full p-6 lg:p-8">
            @yield('content')
        </div>
    </main>
    
    @stack('scripts')
</body>
</html>
