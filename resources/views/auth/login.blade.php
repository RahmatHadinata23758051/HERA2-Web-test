<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ $app_settings['app_name'] ?? 'HERA' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-card {
            background: rgba(17, 24, 39, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.07);
        }
        .input-field {
            background: rgba(31, 41, 55, 0.8);
            border: 1px solid rgba(75, 85, 99, 0.5);
            color: #f9fafb;
            transition: all 0.2s ease;
        }
        .input-field:focus {
            outline: none;
            border-color: rgba(99, 102, 241, 0.8);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
            background: rgba(31, 41, 55, 1);
        }
        .input-field::placeholder { color: #6b7280; }
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca, #6d28d9);
            transform: translateY(-1px);
            box-shadow: 0 8px 20px -4px rgba(99, 102, 241, 0.5);
        }
        .btn-primary:active { transform: translateY(0); }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen flex items-center justify-center relative overflow-hidden selection:bg-indigo-500">

    {{-- Ambient Background --}}
    <div class="fixed top-[-20%] left-[-15%] w-[60%] h-[60%] rounded-full bg-indigo-900/20 blur-[140px] pointer-events-none"></div>
    <div class="fixed bottom-[-20%] right-[-10%] w-[50%] h-[60%] rounded-full bg-purple-900/20 blur-[140px] pointer-events-none"></div>

    <div class="relative z-10 w-full max-w-md px-4">

        {{-- Header --}}
        <div class="text-center mb-8">
            @if(!empty($app_settings['app_logo']))
                <img src="{{ asset($app_settings['app_logo']) }}" alt="App Logo" class="w-16 h-16 object-contain mx-auto mb-5">
            @else
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-xl shadow-indigo-500/30 mb-5">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            @endif
            <h1 class="text-3xl font-bold tracking-tight text-white">{{ $app_settings['app_name'] ?? 'HERA' }}</h1>
            <p class="mt-2 text-sm text-gray-400">{{ $app_settings['app_description'] ?? 'Real-time Hexavalent Chromium Monitoring System' }}</p>
        </div>

        {{-- Card --}}
        <div class="glass-card rounded-2xl p-8 shadow-2xl">
            <h2 class="text-lg font-semibold text-white mb-6">Masuk ke Akun Anda</h2>

            {{-- Error Message --}}
            @if ($errors->any())
            <div class="mb-5 flex items-start gap-3 bg-red-500/10 border border-red-500/30 rounded-lg px-4 py-3">
                <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-sm text-red-400">{{ $errors->first() }}</p>
            </div>
            @endif

            <form method="POST" action="/login" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="email"
                        required
                        value="{{ old('email') }}"
                        placeholder="nama@instansi.ac.id"
                        class="input-field w-full rounded-lg px-4 py-2.5 text-sm"
                    >
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
                    <div class="relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            placeholder="••••••••"
                            class="input-field w-full rounded-lg px-4 py-2.5 text-sm pr-10"
                        >
                        <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
                            <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <div class="flex items-center gap-2">
                    <input id="remember" name="remember" type="checkbox" class="w-4 h-4 rounded border-gray-600 bg-gray-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-gray-900">
                    <label for="remember" class="text-sm text-gray-400 cursor-pointer">Ingat saya</label>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="btn-primary w-full py-2.5 px-4 rounded-lg text-sm font-semibold text-white shadow-lg"
                >
                    Masuk ke Dashboard
                </button>
            </form>
        </div>

        <p class="text-center mt-6 text-xs text-gray-600">
            © {{ $app_settings['app_year'] ?? date('Y') }} {{ $app_settings['app_name'] ?? 'HERA' }} — {{ $app_settings['app_copyright'] ?? 'Universitas Hasanuddin' }}. All rights reserved.
        </p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
