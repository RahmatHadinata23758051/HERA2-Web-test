<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Login | HERA 2.0 Clinical Monitoring</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Manrope:wght@700;800&display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <!-- Tailwind CSS -->
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
                        "headline": ["Manrope"],
                        "body": ["Inter"],
                        "label": ["Inter"]
                    }
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .biological-gradient {
            background: linear-gradient(135deg, #006948 0%, #00855d 100%);
        }
        .botanical-shadow {
            box-shadow: 0px 20px 40px rgba(0, 33, 20, 0.06);
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fb;
        }
        h1, h2, h3 {
            font-family: 'Manrope', sans-serif;
            letter-spacing: -0.02em;
        }
    </style>
</head>
<body class="bg-background text-on-surface min-h-screen flex items-center justify-center p-4">
    <!-- Main Container: Asymmetric Split Layout -->
    <div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-12 overflow-hidden rounded-xl bg-surface-container-lowest botanical-shadow">
        
        <!-- Left Side: Visual/Brand Side (Clinical Conservatory Aesthetic) -->
        <div class="hidden lg:flex lg:col-span-5 biological-gradient relative flex-col justify-between p-12 text-on-primary">
            <div class="relative z-10">
                <div class="mb-8">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-primary-fixed/60 font-bold mb-3">{{ $app_settings['app_institution'] ?? 'Institutional Partner' }}</p>
                    @if(!empty($app_settings['app_logo']))
                        <div class="w-auto inline-block h-16 bg-white shrink-0 rounded-lg p-2.5 shadow-lg border border-white/20">
                            <img src="{{ asset($app_settings['app_logo']) }}" alt="Institution Logo" class="h-full w-auto object-contain">
                        </div>
                    @else
                        <div class="w-32 h-12 border-2 border-dashed border-white/20 rounded flex items-center justify-center">
                            <span class="material-symbols-outlined text-white/30 text-2xl">domain</span>
                        </div>
                    @endif
                </div>
                <div class="flex items-center gap-2 mb-10">
                    <span class="material-symbols-outlined text-4xl" data-icon="clinical_notes">clinical_notes</span>
                    <span class="text-2xl font-black tracking-tighter">HERA 2.0</span>
                </div>
                <h1 class="text-4xl font-extrabold leading-tight mb-6">
                    Precision Monitoring <br/> for Vital Ecosystems.
                </h1>
                <p class="text-primary-fixed opacity-90 text-lg leading-relaxed max-w-sm">
                    Advanced clinical water analysis and system health monitoring in a unified, high-integrity platform.
                </p>
            </div>
            
            <!-- Bottom decorative element/Social proof -->
            <div class="relative z-10 space-y-6">
                <div class="bg-white/10 backdrop-blur-md rounded-lg p-6 border border-white/10">
                    <div class="flex items-center gap-4 mb-4">
                        <span class="material-symbols-outlined text-primary-fixed" data-icon="verified_user">verified_user</span>
                        <span class="text-sm font-semibold uppercase tracking-wider">Enterprise Security</span>
                    </div>
                    <p class="text-sm opacity-80">
                        End-to-end encryption for sensitive clinical data and automated compliance reporting.
                    </p>
                </div>
            </div>
            
            <!-- Subtle Organic Texture Overlay -->
            <div class="absolute inset-0 opacity-20 pointer-events-none">
                <img alt="Macro photography of clean water ripples" class="w-full h-full object-cover mix-blend-overlay" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAbmxlnXaB9dhfs0QJeTcNDgWV7oS69MgjO0ytZ38stj1HekopLhlqHoNPAakoa8Xvb0bM8KzlQOoVHQ-9veU2M6qpCQbGTtTAUSXWv84neDg54-0IYLnNXXn-Ts7hQNVhZPY9l7Dqm64vBdQYkbx5PfUFxTlnfh3HhT7jltrfctAqiJ-BshdWxhHw7S2W2X5ehBp2uo-hHWhTXg8kK1dWrjqqPafFA4gPKcTrheFoNm3XA-Kn_gkQfdN-hzfkM7pYWMGZHpNYMSbo"/>
            </div>
        </div>

        <!-- Right Side: Authentication Form -->
        <div class="lg:col-span-7 flex flex-col justify-center p-8 md:p-16 lg:p-24 bg-surface-container-lowest relative z-10">
            <div class="max-w-md w-full mx-auto">
                <!-- Mobile Logo (Hidden on desktop) -->
                <div class="flex lg:hidden items-center gap-2 mb-12">
                    <span class="material-symbols-outlined text-3xl text-primary" data-icon="clinical_notes">clinical_notes</span>
                    <span class="text-xl font-black tracking-tighter text-on-surface">HERA 2.0</span>
                </div>
                
                <div class="mb-10">
                    <h2 class="text-3xl font-extrabold text-on-surface mb-2">Sign In</h2>
                    <p class="text-on-surface-variant font-medium">Access your Clinical Conservatory dashboard.</p>
                </div>

                {{-- Laravel Error Bag Integration --}}
                @if ($errors->any())
                <div class="mb-6 flex items-start gap-3 bg-red-50 border border-red-200 rounded-lg p-4">
                    <span class="material-symbols-outlined text-red-500 mt-0.5">error</span>
                    <p class="text-sm font-medium text-red-700">{{ $errors->first() }}</p>
                </div>
                @endif

                <form method="POST" action="/login" class="space-y-6">
                    @csrf
                    <!-- Email Input -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-on-surface-variant ml-1" for="email">Work Email</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-outline group-focus-within:text-primary transition-colors" data-icon="alternate_email">alternate_email</span>
                            </div>
                            <input class="block w-full pl-12 pr-4 py-4 bg-surface-container-low border-0 rounded-lg focus:ring-2 focus:ring-primary/40 focus:bg-white text-on-surface placeholder:text-outline transition-all duration-200" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}"
                                placeholder="name@clinical.com" 
                                required
                                type="email"/>
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="space-y-2">
                        <div class="flex justify-between items-center px-1">
                            <label class="text-sm font-semibold text-on-surface-variant" for="password">Password</label>
                            <a class="text-xs font-bold text-primary hover:underline transition-all" href="#">Forgot?</a>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-outline group-focus-within:text-primary transition-colors" data-icon="lock">lock</span>
                            </div>
                            <input class="block w-full pl-12 pr-4 py-4 bg-surface-container-low border-0 rounded-lg focus:ring-2 focus:ring-primary/40 focus:bg-white text-on-surface placeholder:text-outline transition-all duration-200" 
                                id="password" 
                                name="password" 
                                placeholder="••••••••" 
                                required
                                type="password"/>
                        </div>
                    </div>

                    <!-- Remember Me Toggle -->
                    <div class="flex items-center gap-3 px-1">
                        <input class="w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary/30 bg-surface-container-low cursor-pointer" 
                            id="remember" 
                            name="remember"
                            type="checkbox"/>
                        <label class="text-sm text-on-surface-variant font-medium select-none cursor-pointer" for="remember">Stay signed in for 30 days</label>
                    </div>

                    <!-- Primary Action -->
                    <button class="w-full py-4 px-6 bg-primary text-on-primary font-bold text-lg rounded-lg shadow-lg shadow-primary/20 hover:bg-primary-container active:scale-[0.98] transition-all duration-150 flex items-center justify-center gap-2 group" type="submit">
                        Sign In
                        <span class="material-symbols-outlined text-xl group-hover:translate-x-1 transition-transform" data-icon="arrow_forward">arrow_forward</span>
                    </button>
                </form>

                <!-- Footer Links -->
                <div class="mt-12 pt-8 border-t border-surface-container-high flex flex-col sm:flex-row justify-between items-center gap-4 text-sm font-medium text-on-surface-variant">
                    <span>New to the platform? <a class="text-primary font-bold hover:underline" href="#">Contact Admin</a></span>
                    <div class="flex gap-6">
                        <a class="hover:text-on-surface transition-colors" href="#">Privacy</a>
                        <a class="hover:text-on-surface transition-colors" href="#">Terms</a>
                    </div>
                </div>
            </div>

            <!-- Versioning / Footer Info -->
            <div class="mt-auto pt-12 text-center lg:text-left">
                <p class="text-[10px] uppercase tracking-[0.2em] text-outline font-bold">
                    HERA 2.0 • Clinical Intelligence Node • Stable Release
                </p>
            </div>
        </div>
    </div>

    <!-- Background Decoration: Asymmetric "Glass" circles -->
    <div class="fixed -top-24 -right-24 w-96 h-96 bg-primary/20 rounded-full blur-3xl -z-10"></div>
    <div class="fixed -bottom-48 -left-48 w-[32rem] h-[32rem] bg-secondary-container/50 rounded-full blur-3xl -z-10"></div>

</body>
</html>
