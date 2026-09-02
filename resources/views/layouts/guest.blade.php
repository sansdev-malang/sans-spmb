<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @php
            $primaryColor = \App\Models\Setting::get('portal_primary_color', '#0D3B2C');
            $secondaryColor = \App\Models\Setting::get('portal_secondary_color', '#ffc107');
            $schoolName = \App\Models\Setting::get('school_name', 'Sekolah Anak Saleh');
            $schoolTagline = \App\Models\Setting::get('school_tagline', 'Yayasan Pendidikan Anak Saleh');
            $schoolLogo = \App\Models\Setting::get('school_logo_url', '');
            $schoolFavicon = \App\Models\Setting::get('school_favicon_url', '');
        @endphp

        @if(!empty($schoolFavicon))
            <link rel="icon" href="{{ $schoolFavicon }}" type="image/x-icon">
        @else
            <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><defs><linearGradient id='g' x1='0%' y1='0%' x2='100%' y2='100%'><stop offset='0%' stop-color='%236366f1'/><stop offset='100%' stop-color='%23a855f7'/></linearGradient></defs><rect width='100' height='100' rx='25' fill='url(%23g)'/><text x='50' y='75' font-family='Arial, sans-serif' font-size='65' font-weight='bold' fill='white' text-anchor='middle'>S</text></svg>">
        @endif

        <!-- Plus Jakarta Sans Font -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Theme init on load (Inline to prevent flash) -->
        <script>
            (function() {
                const theme = localStorage.getItem('theme') || '{{ \App\Models\Setting::get('portal_layout_mode', 'light') }}';
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            })();
        </script>

        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
            }
            .guest-panel-orb {
                position: absolute;
                border-radius: 9999px;
                filter: blur(48px);
                pointer-events: none;
                opacity: 0.6;
            }
            .guest-panel-particles {
                position: absolute;
                inset: 0;
                background-image:
                    radial-gradient(circle at 20% 20%, rgba(255,255,255,0.15) 0 2px, transparent 3px),
                    radial-gradient(circle at 80% 30%, rgba(255,255,255,0.10) 0 1.5px, transparent 3px),
                    radial-gradient(circle at 35% 75%, rgba(255,255,255,0.10) 0 1.5px, transparent 3px),
                    radial-gradient(circle at 75% 70%, rgba(255,255,255,0.14) 0 2px, transparent 3px);
                background-size: 240px 240px;
                opacity: 0.45;
                pointer-events: none;
            }
            /* Custom Brand Colors for Tailwind */
            .bg-brand-emerald { background-color: {{ $primaryColor }}; }
            .text-brand-emerald { color: {{ $primaryColor }}; }
            .border-brand-emerald { border-color: {{ $primaryColor }}; }
            
            .bg-brand-yellow { background-color: {{ $secondaryColor }}; }
            .text-brand-yellow { color: {{ $secondaryColor }}; }
            
            /* Custom Dynamic Helper Colors */
            .bg-custom-primary { background-color: {{ $primaryColor }}; }
            .text-custom-primary { color: {{ $primaryColor }}; }
            .border-custom-primary { border-color: {{ $primaryColor }}; }
            .hover\:bg-custom-primary:hover { background-color: {{ $primaryColor }}e0; }
            .dark\:text-custom-primary { color: {{ $primaryColor }}; }

            /* Dynamic style overrides for components in guest layout */
            input:focus, select:focus, textarea:focus {
                border-color: {{ $primaryColor }} !important;
                --tw-ring-color: {{ $primaryColor }} !important;
                --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color) !important;
                --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color) !important;
                box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000) !important;
            }
            .bg-gray-800 {
                background-color: {{ $primaryColor }} !important;
            }
            .hover\:bg-gray-700:hover {
                background-color: {{ $primaryColor }}e0 !important;
            }
            .focus\:bg-gray-700:focus {
                background-color: {{ $primaryColor }}e0 !important;
            }
            .active\:bg-gray-900:active {
                background-color: {{ $primaryColor }}c0 !important;
            }
            .text-indigo-600 {
                color: {{ $primaryColor }} !important;
            }
            .focus\:ring-indigo-500:focus {
                --tw-ring-color: {{ $primaryColor }} !important;
            }
            .hover\:text-gray-900:hover {
                color: {{ $primaryColor }}e0 !important;
            }
            .underline {
                color: {{ $primaryColor }};
            }
            .underline:hover {
                color: {{ $primaryColor }}e0;
            }

            /* Dark mode details */
            html.dark body {
                background-color: #0f172a;
                color: #cbd5e1;
            }
            html.dark .bg-white {
                background-color: #1e293b;
                border-color: #334155;
            }
            html.dark .text-slate-800, html.dark h1, html.dark h2, html.dark h3, html.dark h4 {
                color: #f8fafc;
            }
            html.dark .text-slate-600, html.dark .text-slate-700 {
                color: #cbd5e1;
            }
            html.dark .text-slate-400, html.dark .text-slate-500 {
                color: #64748b;
            }
            html.dark .bg-slate-50 {
                background-color: #0f172a;
            }
            html.dark .border-slate-100 {
                border-color: #334155;
            }
            html.dark input, html.dark select, html.dark textarea {
                background-color: #0f172a;
                border-color: #475569;
                color: #f8fafc;
            }

            .auth-shell {
                background:
                    radial-gradient(circle at top left, rgba(16, 185, 129, 0.08), transparent 30%),
                    radial-gradient(circle at bottom right, rgba(245, 158, 11, 0.06), transparent 30%),
                    #f8fafc;
            }
            html.dark .auth-shell {
                background:
                    radial-gradient(circle at top left, rgba(16, 185, 129, 0.08), transparent 28%),
                    radial-gradient(circle at bottom right, rgba(245, 158, 11, 0.06), transparent 28%),
                    #020817;
            }
            .auth-panel-card {
                box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
            }
        </style>
    </head>
    <body class="font-sans text-slate-800 bg-slate-50 dark:bg-slate-950 dark:text-slate-200 min-h-screen flex antialiased">
        <!-- Split Layout Container -->
        <div class="auth-shell w-full min-h-screen flex flex-col md:flex-row">
            
            <!-- Left Panel: Branding & Info (Hidden on mobile) -->
            <div class="hidden md:flex md:w-1/2 bg-custom-primary relative overflow-hidden flex-col justify-center p-8 lg:p-10 text-white">
                <!-- Clean background gradients -->
                <div class="absolute inset-0 bg-gradient-to-br from-custom-primary via-[#0f4f3a] to-[#081c15]"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.15),transparent_35%),radial-gradient(circle_at_bottom_right,rgba(255,199,0,0.14),transparent_35%)]"></div>

                <!-- Background decorative elements (static and lightweight) -->
                <div class="guest-panel-orb top-[-10%] left-[-10%] w-[50%] aspect-square bg-white/10"></div>
                <div class="guest-panel-orb bottom-[-12%] right-[-8%] w-[55%] aspect-square bg-brand-yellow/15"></div>
                <div class="guest-panel-particles"></div>

                <div class="relative z-10 h-full w-full rounded-[2rem] border border-white/15 bg-white/10 shadow-2xl p-10 lg:p-12 flex flex-col justify-between overflow-hidden">
                    <!-- Top Brand Header -->
                    <div class="relative z-10 flex items-center gap-3">
                        @if(!empty($schoolLogo))
                            <img src="{{ $schoolLogo }}" alt="{{ $schoolName }}" class="h-10 object-contain">
                        @else
                            <div class="h-10 w-10 bg-white/15 rounded-xl flex items-center justify-center font-bold text-brand-yellow text-base shadow-sm border border-white/10">
                                <span class="text-lg font-black font-sans">S</span>
                            </div>
                        @endif
                        <div class="flex flex-col text-left">
                            <span class="font-extrabold text-base tracking-tight leading-tight">{{ $schoolName }}</span>
                            @if(!empty($schoolTagline))
                                <span class="text-xs text-white/70 font-semibold leading-none mt-0.5">{{ $schoolTagline }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Center Content: Title & Features -->
                    <div class="relative z-10 my-auto max-w-md space-y-6">
                        <span class="guest-panel-badge inline-flex items-center gap-1 bg-white/15 text-brand-yellow font-extrabold text-xs uppercase tracking-widest px-3.5 py-1 rounded-full border border-white/10">
                            ✨ SPMB Online
                        </span>
                        <h1 class="guest-panel-title text-3xl lg:text-4xl font-black leading-tight">
                            Penerimaan Siswa Baru Berbasis Karakter Islami
                        </h1>
                        <p class="guest-panel-text text-sm text-white/85 leading-relaxed font-medium">
                            Selamat datang di portal pendaftaran {{ $schoolName }}. Daftarkan putra-putri terbaik Anda untuk bergabung bersama kami.
                        </p>

                        <div class="space-y-3 pt-3 text-sm font-semibold text-white/95">
                            <div class="guest-panel-item flex items-center gap-3 rounded-2xl border border-white/10 bg-white/10 px-4 py-3 shadow-sm">
                                <div class="h-6 w-6 rounded-lg bg-white/15 flex items-center justify-center text-brand-yellow flex-shrink-0">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </div>
                                <span class="text-xs font-semibold">Proses pendaftaran cepat, online, dan transparan</span>
                            </div>
                            <div class="guest-panel-item flex items-center gap-3 rounded-2xl border border-white/10 bg-white/10 px-4 py-3 shadow-sm">
                                <div class="h-6 w-6 rounded-lg bg-white/15 flex items-center justify-center text-brand-yellow flex-shrink-0">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </div>
                                <span class="text-xs font-semibold">Pengumuman hasil observasi & administrasi terintegrasi</span>
                            </div>
                            <div class="guest-panel-item flex items-center gap-3 rounded-2xl border border-white/10 bg-white/10 px-4 py-3 shadow-sm">
                                <div class="h-6 w-6 rounded-lg bg-white/15 flex items-center justify-center text-brand-yellow flex-shrink-0">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </div>
                                <span class="text-xs font-semibold">Layanan bantuan cepat via Whatsapp Panitia</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer: Back to main page -->
                    <div class="guest-panel-footer relative z-10 flex justify-between items-center text-xs text-white/70 font-semibold border-t border-white/10 pt-6">
                        <span>© {{ date('Y') }} {{ $schoolName }}</span>
                        <a href="/" class="flex items-center gap-1.5 hover:text-brand-yellow transition text-white/90">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Portal Utama
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Authentication Form -->
            <div class="w-full md:w-1/2 flex flex-col justify-center items-center p-6 sm:p-12 relative bg-slate-50 dark:bg-slate-950">
                <!-- Theme toggle button (Top Right) -->
                <div class="absolute top-6 right-6 flex items-center gap-3">
                    <button onclick="toggleDarkMode()" class="p-2 text-slate-500 hover:text-custom-primary dark:text-slate-400 dark:hover:text-emerald-400 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 transition shadow-sm" title="Toggle Tema">
                        <i id="theme-toggle-icon" data-lucide="moon" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Small Header for Mobile Only -->
                <div class="md:hidden flex flex-col items-center gap-2 mb-8 mt-12">
                    @if(!empty($schoolLogo))
                        <img src="{{ $schoolLogo }}" alt="{{ $schoolName }}" class="h-10 object-contain">
                    @else
                        <div class="h-12 w-12 bg-custom-primary rounded-2xl flex items-center justify-center font-bold text-brand-yellow text-xl shadow-md">
                            <span class="text-lg font-black font-sans">S</span>
                        </div>
                    @endif
                    <div class="text-center">
                        <h2 class="font-extrabold text-sm text-custom-primary dark:text-emerald-400 tracking-tight leading-tight">{{ $schoolName }}</h2>
                        @if(!empty($schoolTagline))
                            <p class="text-[9px] text-slate-400 font-semibold mt-0.5">{{ $schoolTagline }}</p>
                        @endif
                    </div>
                </div>

                <!-- Card Form wrapper -->
                <div class="auth-panel-card w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-[2rem] p-8 shadow-xl">
                    <!-- Dynamic header text based on page -->
                    <div class="text-center space-y-2 mb-6">
                        @if(Request::is('login'))
                            <h2 class="text-xl font-black text-slate-800 dark:text-white">Masuk Akun</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold">Silakan masuk menggunakan akun pendaftaran Anda</p>
                        @elseif(Request::is('register'))
                            <h2 class="text-xl font-black text-slate-800 dark:text-white">Daftar Akun Baru</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold">Mulai langkah awal pendaftaran sekolah anak Anda</p>
                        @elseif(Request::is('forgot-password'))
                            <h2 class="text-xl font-black text-slate-800 dark:text-white">Lupa Password</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold">Kami akan mengirimkan link reset password via email</p>
                        @else
                            <h2 class="text-xl font-black text-slate-800 dark:text-white">Autentikasi</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold">{{ config('app.name') }}</p>
                        @endif
                    </div>

                    {{ $slot }}
                </div>

                <!-- Mobile Back Button -->
                <a href="/" class="md:hidden mt-6 text-xs text-slate-500 hover:text-custom-primary font-bold transition flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Portal Utama
                </a>
            </div>
            
        </div>
        
        <!-- Lucide Icons CDN -->
        <script src="https://unpkg.com/lucide@latest"></script>
        <script>
            // Initialize Lucide Icons
            if (window.lucide) {
                lucide.createIcons();
            }
            
            // Dark/Light Theme Handler
            function toggleDarkMode() {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                updateThemeIcon();
            }

            function updateThemeIcon() {
                const icon = document.getElementById('theme-toggle-icon');
                const isDark = document.documentElement.classList.contains('dark');
                
                if (icon) {
                    icon.setAttribute('data-lucide', isDark ? 'sun' : 'moon');
                }
                if (window.lucide) {
                    lucide.createIcons();
                }
            }
            
            document.addEventListener("DOMContentLoaded", function() {
                updateThemeIcon();
            });
        </script>
    </body>
</html>
