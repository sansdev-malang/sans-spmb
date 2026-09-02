<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SPMB Sekolah Anak Saleh')</title>
    @php
        $primaryColor = \App\Models\Setting::get('portal_primary_color', '#0D3B2C');
        $secondaryColor = \App\Models\Setting::get('portal_secondary_color', '#ffc107');
        $schoolName = \App\Models\Setting::get('school_name', 'Sekolah Anak Saleh');
        $schoolTagline = \App\Models\Setting::get('school_tagline', 'Yayasan Pendidikan Anak Saleh');
        $schoolLogo = \App\Models\Setting::get('school_logo_url', '');
        $schoolFavicon = \App\Models\Setting::get('school_favicon_url', '');
        $navbarUnits = \App\Models\SpmbUnit::where('is_active', true)->get();
        
        $footerContactUrl = \App\Models\Setting::get('footer_contact_url', '#');
        $footerPrivacyUrl = \App\Models\Setting::get('footer_privacy_url', '#');
        $footerTermsUrl = \App\Models\Setting::get('footer_terms_url', '#');
        $footerFaqUrl = \App\Models\Setting::get('footer_faq_url', '#');
        
        $rawCopyright = \App\Models\Setting::get('footer_copyright_text', '© 2026 {SchoolName}. All rights reserved.');
        $footerCopyright = str_replace(['{SchoolName}', '{Year}'], [$schoolName, date('Y')], $rawCopyright);

        if (!isset($registration) && auth()->check() && !auth()->user()->isAdmin()) {
            $registration = \App\Models\Registration::where('user_id', auth()->id())
                ->where(function($q) {
                    $q->whereHas('payments', function($pq) {
                        $pq->where('payment_type', 'registration_fee')
                           ->where('status', 'success');
                    })
                    ->orWhere('registration_status', '!=', 'draft');
                })
                ->orderBy('created_at', 'desc')
                ->first();
        }
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
    <link href="https://fonts.cdnfonts.com/css/nasalization" rel="stylesheet">
    
    <!-- Local Compiled CSS/JS via Vite -->
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
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 5.5rem;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f7fafc;
            transition: background-color 0.3s, color 0.3s;
        }
        /* Custom Brand Colors for Tailwind */
        .bg-brand-emerald { background-color: {{ $primaryColor }}; }
        .text-brand-emerald { color: {{ $primaryColor }}; }
        .border-brand-emerald { border-color: {{ $primaryColor }}; }
        
        .bg-brand-yellow { background-color: {{ $secondaryColor }}; }
        .text-brand-yellow { color: {{ $secondaryColor }}; }
        
        .hover-emerald:hover { background-color: {{ $primaryColor }}e0; }

        /* Custom Dynamic Helper Colors */
        .bg-custom-primary { background-color: {{ $primaryColor }}; }
        .text-custom-primary { color: {{ $primaryColor }}; }
        .border-custom-primary { border-color: {{ $primaryColor }}; }
        .hover\:bg-custom-primary:hover { background-color: {{ $primaryColor }}e0; }
        .dark\:text-custom-primary { color: {{ $primaryColor }}; }
        
        /* Layout overrides for header buttons */
        .btn-custom-header {
            background-color: {{ $primaryColor }};
        }
        .btn-custom-header:hover {
            background-color: {{ $primaryColor }}e0;
        }
        .text-custom-header {
            color: {{ $primaryColor }};
        }

        /* Dark Mode Custom Styles */
        html.dark body {
            background-color: #020617;
            color: #cbd5e1;
        }
        html.dark #main-nav {
            background-color: transparent !important;
            border-color: transparent !important;
        }
        html.dark header {
            background-color: #0f172a;
            border-color: #334155;
        }
        html.dark footer {
            background-color: #0f172a;
            border-color: #1e293b;
            color: #64748b;
        }
        html.dark .bg-white {
            background-color: #0f172a;
            border-color: #1e293b;
        }
        html.dark .partnership-logo-bg {
            background-color: #ffffff !important;
        }
        html.dark .text-slate-800, html.dark h1, html.dark h2, html.dark h3, html.dark h4 {
            color: #f8fafc;
        }
        html.dark .text-slate-700, html.dark .text-slate-600 {
            color: #cbd5e1;
        }
        html.dark .text-slate-500, html.dark .text-slate-400 {
            color: #64748b;
        }
        html.dark .bg-slate-50, html.dark .bg-slate-50\/50, html.dark .bg-slate-50\/30 {
            background-color: #020617;
        }
        html.dark .border-slate-100, html.dark .border-slate-200 {
            border-color: #1e293b;
        }
        html.dark input, html.dark select, html.dark textarea {
            background-color: #0f172a;
            border-color: #334155;
            color: #f8fafc;
        }
        html.dark input:focus, html.dark select:focus, html.dark textarea:focus {
            border-color: #10b981;
        }
        html.dark .hover\:bg-slate-50:hover {
            background-color: #1e293b;
        }
        html.dark .shadow-sm, html.dark .shadow-md, html.dark .shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.4), 0 2px 4px -1px rgba(0, 0, 0, 0.3);
        }

        /* YouTube-style dynamic top progress loading bar */
        #top-loading-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background-color: {{ $primaryColor }};
            z-index: 99999;
            width: 0;
            opacity: 0;
            transition: width 0.4s ease, opacity 0.2s ease;
            box-shadow: 0 0 10px {{ $primaryColor }}, 0 0 5px {{ $primaryColor }};
        }
    </style>
</head>
<body class="min-h-screen flex flex-col text-slate-800 bg-slate-50 dark:bg-slate-950 dark:text-slate-200">
    <!-- YouTube-style dynamic top progress loading bar -->
    <div id="top-loading-bar"></div>

    <!-- Header Navigation - Floating Premium (Fixed Seamless Overlay) -->
    <nav id="main-nav" class="fixed top-0 inset-x-0 z-50 px-4 lg:px-8 py-3 bg-transparent transition-all duration-300">
        <!-- Floating Nav Inner -->
        <div id="nav-inner" class="max-w-7xl mx-auto bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-200/60 dark:border-slate-800/80 shadow-sm px-4 sm:px-6 transition-all duration-300">
            <div class="flex items-center justify-between h-14">
                <!-- Brand logo -->
                <a href="{{ auth()->check() ? route('dashboard') : '/' }}" class="flex items-center gap-2.5 flex-shrink-0">
                    @if(!empty($schoolLogo))
                        <img src="{{ $schoolLogo }}" alt="{{ $schoolName }}" class="h-8 object-contain">
                    @else
                        <div class="h-8 w-8 bg-brand-yellow rounded-xl flex items-center justify-center shadow-sm">
                            <span class="flex items-center justify-center text-lg leading-none font-bold text-black" style="font-family: 'Nasalization Rg', sans-serif; font-weight: 700; color: #000000; line-height: 1; transform: translateY(-0.5px);">S</span>
                        </div>
                    @endif
                    <div class="flex flex-col text-left">
                        <span class="font-extrabold text-sm tracking-tight leading-tight text-custom-primary dark:text-emerald-400">{{ $schoolName }}</span>
                        @if(!empty($schoolTagline))
                            <span class="text-[9px] text-slate-400 dark:text-slate-500 font-semibold leading-none mt-0.5">{{ $schoolTagline }}</span>
                        @endif
                    </div>
                </a>

                <!-- Center Navigation area -->
                <div class="hidden md:flex items-center gap-8">
                    @guest
                        <!-- Guest Navigation links - Editorial -->
                        <div class="flex items-center gap-7 text-xs font-bold text-slate-500 dark:text-slate-400">
                            <a href="/#program" class="transition py-1 hover:text-custom-primary dark:hover:text-emerald-400">Program</a>
                            <a href="/#panca-karakter" class="transition py-1 hover:text-custom-primary dark:hover:text-emerald-400">Panca Karakter</a>
                            <a href="/#partnership" class="transition py-1 hover:text-custom-primary dark:hover:text-emerald-400">Partnership</a>
                            <a href="/#kata-mereka" class="transition py-1 hover:text-custom-primary dark:hover:text-emerald-400">Kata Mereka</a>
                        </div>
                    @else
                        @if(auth()->user()->isAdmin())
                            <!-- Admin Panel link -->
                            <div class="flex items-center gap-8 text-xs font-bold text-slate-500 dark:text-slate-400">
                                <a href="{{ route('admin.dashboard') }}" class="transition pb-1 text-custom-primary dark:text-emerald-400 font-extrabold border-b-2 border-custom-primary">
                                    Admin Panel
                                </a>
                            </div>
                        @elseif(isset($registration) && $registration->id)
                            <!-- Candidate Active Registration tabs -->
                            @php
                                $formPaid = $registration->payments()->where('payment_type', 'registration_fee')->where('status', 'success')->exists();
                                $status = $registration->registration_status;
                                $formUnlocked = $formPaid;
                                $verificationUnlocked = ($status !== 'draft');
                                $observationUnlocked = in_array($status, ['verified', 'taaruf_completed', 'agreement_signed', 'completed']);
                                $resultUnlocked = in_array($status, ['agreement_signed', 'completed']);
                            @endphp
                            <div class="flex items-center gap-6 text-[10px] font-bold tracking-wider uppercase">
                                <a href="{{ route('dashboard') }}" class="transition pb-1 {{ Route::is('dashboard') ? 'text-custom-primary dark:text-emerald-400 font-extrabold border-b-2 border-custom-primary' : 'hover:text-custom-primary text-slate-500 dark:text-slate-400' }}">
                                    Beranda
                                </a>

                                @if($formUnlocked)
                                    <a href="{{ route('dashboard.form', $registration->id) }}" class="transition pb-1 {{ Route::is('dashboard.form') ? 'text-custom-primary dark:text-emerald-400 font-extrabold border-b-2 border-custom-primary' : 'hover:text-custom-primary text-slate-500 dark:text-slate-400' }}">
                                        Formulir
                                    </a>
                                @else
                                    <button onclick="showToast('Menu Formulir terkunci. Selesaikan pembayaran biaya pendaftaran terlebih dahulu.', 'error')" class="text-slate-350 dark:text-slate-600 font-bold transition flex items-center gap-1">
                                        <i data-lucide="lock" class="w-3 h-3"></i> Formulir
                                    </button>
                                @endif

                                @if($verificationUnlocked)
                                    <a href="{{ route('dashboard.verification', $registration->id) }}" class="transition pb-1 {{ Route::is('dashboard.verification') ? 'text-custom-primary dark:text-emerald-400 font-extrabold border-b-2 border-custom-primary' : 'hover:text-custom-primary text-slate-500 dark:text-slate-400' }}">
                                        Verifikasi Data
                                    </a>
                                @else
                                    <button onclick="showToast('Menu Verifikasi Data terkunci. Lengkapi dan kirim formulir pendaftaran terlebih dahulu.', 'error')" class="text-slate-350 dark:text-slate-600 font-bold transition flex items-center gap-1">
                                        <i data-lucide="lock" class="w-3 h-3"></i> Verifikasi Data
                                    </button>
                                @endif

                                @if($observationUnlocked)
                                    <a href="{{ route('dashboard.observation', $registration->id) }}" class="transition pb-1 {{ Route::is('dashboard.observation') ? 'text-custom-primary dark:text-emerald-400 font-extrabold border-b-2 border-custom-primary' : 'hover:text-custom-primary text-slate-500 dark:text-slate-400' }}">
                                        Ta'Aruf
                                    </a>
                                @else
                                    <button onclick="showToast('Menu Ta\'Aruf terkunci. Tunggu berkas pendaftaran Anda selesai diverifikasi oleh panitia.', 'error')" class="text-slate-350 dark:text-slate-600 font-bold transition flex items-center gap-1">
                                        <i data-lucide="lock" class="w-3 h-3"></i> Ta'Aruf
                                    </button>
                                @endif

                                @if($resultUnlocked)
                                    <a href="{{ route('dashboard.result', $registration->id) }}" class="transition pb-1 {{ (Route::is('dashboard.result') || (Route::is('dashboard.payment') && $status === 'agreement_signed')) ? 'text-custom-primary dark:text-emerald-400 font-extrabold border-b-2 border-custom-primary' : 'hover:text-custom-primary text-slate-500 dark:text-slate-400' }}">
                                        Administrasi
                                    </a>
                                @else
                                    <button onclick="showToast('Menu Administrasi terkunci. Selesaikan tahapan observasi dan pelunasan administrasi.', 'error')" class="text-slate-350 dark:text-slate-600 font-bold transition flex items-center gap-1">
                                        <i data-lucide="lock" class="w-3 h-3"></i> Administrasi
                                    </button>
                                @endif
                            </div>
                        @endif
                    @endguest
                </div>

                <!-- Right Area: Actions / Auth -->
                <div class="flex items-center gap-3 text-xs font-bold">
                    @guest
                        <!-- Toggle Dark/Light Mode Button -->
                        <button onclick="toggleDarkMode()" class="p-2 text-slate-500 hover:text-custom-primary dark:text-slate-400 dark:hover:text-emerald-400 rounded-xl transition" title="Toggle Tema">
                            <i id="theme-toggle-icon-guest" data-lucide="moon" class="w-4 h-4"></i>
                        </button>

                        <div class="hidden md:block h-4 w-px bg-slate-200 dark:bg-slate-700"></div>

                        <a href="{{ route('login') }}" class="hidden md:block text-custom-primary dark:text-emerald-400 hover:opacity-80 transition py-2 font-bold">Login</a>
                        <a href="{{ route('register') }}" class="hidden md:inline-flex items-center gap-1.5 bg-custom-primary hover:opacity-90 text-white px-5 py-2.5 rounded-xl transition shadow-sm font-bold dark:bg-emerald-600 dark:hover:bg-emerald-500">
                            Daftar Sekarang
                        </a>

                        <!-- Mobile Hamburger Button -->
                        <button onclick="toggleMobileMenu()" class="md:hidden p-2 text-slate-500 hover:text-custom-primary dark:text-slate-400 dark:hover:text-emerald-400 rounded-xl transition" title="Menu">
                            <i id="mobile-menu-icon" data-lucide="menu" class="w-5 h-5"></i>
                        </button>
                    @else
                        <!-- Toggle Dark/Light Mode Button -->
                        <button onclick="toggleDarkMode()" class="p-2 text-slate-500 hover:text-custom-primary dark:text-slate-400 dark:hover:text-emerald-400 rounded-xl transition" title="Toggle Tema">
                            <i id="theme-toggle-icon" data-lucide="moon" class="w-4 h-4"></i>
                        </button>

                        <!-- Notifications Toggles with Badge -->
                        <div class="relative">
                            <button id="notifBellButton" type="button" onclick="toggleNotifDropdown(event)" class="p-2 text-slate-500 hover:text-custom-primary dark:text-slate-400 dark:hover:text-emerald-400 rounded-xl transition relative" title="Notifikasi">
                                <i data-lucide="bell" class="w-4 h-4"></i>
                                <span id="unread-notifications-badge" class="absolute top-1.5 right-1.5 flex h-2 w-2"></span>
                            </button>
                            <!-- Notifications Dropdown Box -->
                            <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-72 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-800 py-2 z-50 animate-fade-in text-xs text-slate-700 dark:text-slate-300">
                            </div>
                        </div>

                        <!-- User Profile Dropdown Toggle -->
                        <div class="relative">
                            <button onclick="toggleProfileDropdown(event)" class="flex items-center gap-1.5 p-1 rounded-full hover:bg-slate-50 dark:hover:bg-slate-800 transition text-xs font-bold text-slate-700 dark:text-slate-300" title="Akun">
                                <div class="h-6 w-6 rounded-full bg-custom-primary text-white flex items-center justify-center font-bold text-xs uppercase dark:bg-emerald-600">
                                    {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                                </div>
                                <span class="hidden md:inline font-bold text-slate-700 dark:text-slate-300 pr-1 max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
                            </button>
                            <!-- Dropdown Box -->
                            <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-800 py-2 z-50 text-xs">
                                <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800 font-medium text-slate-400 dark:text-slate-500 truncate">
                                    {{ auth()->user()->email }}
                                </div>
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-800 transition flex items-center gap-2">
                                    <i data-lucide="user" class="w-4 h-4 text-slate-400"></i> Edit Profile
                                </a>
                                <form method="POST" action="{{ route('logout') }}" hx-boost="false">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2.5 text-red-655 hover:bg-red-50 dark:hover:bg-red-950/20 transition flex items-center gap-2 font-bold">
                                        <i data-lucide="log-out" class="w-4 h-4 text-red-500"></i> Keluar / Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endguest
                </div>
            </div>
        </div>

        <!-- Mobile Menu Drawer (Guest Only) -->
        @guest
        <div id="mobile-menu" class="hidden md:hidden max-w-7xl mx-auto mt-2 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-lg overflow-hidden">
            <div class="px-4 py-4 space-y-1">
                <a href="/#program" onclick="closeMobileMenu()" class="flex items-center px-4 py-3 text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-custom-primary hover:bg-emerald-50/60 dark:hover:bg-slate-800 rounded-xl transition">Program</a>
                <a href="/#panca-karakter" onclick="closeMobileMenu()" class="flex items-center px-4 py-3 text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-custom-primary hover:bg-emerald-50/60 dark:hover:bg-slate-800 rounded-xl transition">Panca Karakter</a>
                <a href="/#partnership" onclick="closeMobileMenu()" class="flex items-center px-4 py-3 text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-custom-primary hover:bg-emerald-50/60 dark:hover:bg-slate-800 rounded-xl transition">Partnership</a>
                <a href="/#kata-mereka" onclick="closeMobileMenu()" class="flex items-center px-4 py-3 text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-custom-primary hover:bg-emerald-50/60 dark:hover:bg-slate-800 rounded-xl transition">Kata Mereka</a>
                <div class="pt-3 mt-2 border-t border-slate-100 dark:border-slate-800 grid grid-cols-2 gap-2">
                    <a href="{{ route('login') }}" class="text-center py-3 rounded-xl border-2 border-custom-primary text-custom-primary dark:text-emerald-400 dark:border-emerald-600 font-bold text-sm transition hover:bg-emerald-50 dark:hover:bg-emerald-950/30">Login</a>
                    <a href="{{ route('register') }}" class="text-center py-3 rounded-xl bg-custom-primary hover:opacity-90 text-white font-bold text-sm transition dark:bg-emerald-600">Daftar Sekarang</a>
                </div>
            </div>
        </div>
        @endguest
    </nav>

    <!-- Main Content Container -->
    <main class="flex-grow {{ (request()->is('/') || request()->routeIs('home')) ? '' : 'pt-20' }}">
        @yield('content')
    </main>

    <!-- Footer (Minimalist & Clean) -->
    <footer class="bg-white dark:bg-slate-950 text-slate-500 text-xs py-8 border-t border-slate-200/60 dark:border-slate-800 transition">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-4">
            {{-- Footer Quick Links --}}
            <div class="flex flex-wrap justify-center sm:justify-start gap-6 text-[11px] font-bold text-slate-500 dark:text-slate-400">
                <a href="{{ $footerContactUrl }}" target="_blank" class="hover:text-custom-primary dark:hover:text-emerald-400 transition">Hubungi Kami</a>
                <a href="{{ $footerPrivacyUrl }}" target="_blank" class="hover:text-custom-primary dark:hover:text-emerald-400 transition">Kebijakan Privasi</a>
                <a href="{{ $footerTermsUrl }}" target="_blank" class="hover:text-custom-primary dark:hover:text-emerald-400 transition">Syarat & Ketentuan</a>
                <a href="{{ $footerFaqUrl }}" target="_blank" class="hover:text-custom-primary dark:hover:text-emerald-400 transition">FAQ</a>
            </div>
            
            {{-- Copyright --}}
            <div class="text-center sm:text-right">
                <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">{{ $footerCopyright }}</p>
            </div>
        </div>
    </footer>

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Toast Notification Container -->
    <div id="toastContainer" class="fixed top-5 right-5 z-[9999] space-y-3 pointer-events-none"></div>

    <script>
        // Beautiful dynamic toast notification handler
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `flex items-center gap-3 bg-white text-xs font-bold px-4 py-3 rounded-xl shadow-lg border border-slate-100 transform translate-y-2 opacity-0 transition-all duration-300 pointer-events-auto max-w-sm`;
            
            let iconColor = type === 'success' ? 'text-emerald-600' : 'text-red-600';
            let iconName = type === 'success' ? 'check-circle' : 'alert-circle';
            
            toast.innerHTML = `
                <i data-lucide="${iconName}" class="w-4 h-4 ${iconColor} flex-shrink-0"></i>
                <span class="text-slate-700 flex-grow">${message}</span>
                <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 p-0.5">&times;</button>
            `;
            
            container.appendChild(toast);
            
            if (window.lucide) {
                lucide.createIcons();
            }
            
            setTimeout(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            }, 10);
            
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-[-10px]');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 4000);
        }

        // Notification dropdown handler
        @auth
        let isFetchingNotif = false;
        function toggleNotifDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('notifDropdown');
            if (dropdown) {
                const isHidden = dropdown.classList.contains('hidden');
                document.getElementById('profileDropdown')?.classList.add('hidden');
                
                if (isHidden) {
                    dropdown.classList.remove('hidden');
                } else {
                    dropdown.classList.add('hidden');
                    fetchNotifications(true); // Fetch silently in background after closing!
                }
            }
        }

        function fetchNotifications(silent = false) {
            if (isFetchingNotif) return;
            isFetchingNotif = true;

            const dropdown = document.getElementById('notifDropdown');
            if (!dropdown) {
                isFetchingNotif = false;
                return;
            }
            
            // Show a simple text loader ONLY if the dropdown is completely empty
            if (!silent && (!dropdown.innerHTML || dropdown.innerHTML.trim() === '')) {
                dropdown.innerHTML = `
                    <div class="px-4 py-8 text-center text-slate-400">
                        <p class="text-[10px]">Memuat...</p>
                    </div>
                `;
            }

            fetch('/dashboard/notifications/dropdown')
                .then(response => {
                    if (!response.ok) throw new Error('Failed to load');
                    return response.text();
                })
                .then(html => {
                    dropdown.innerHTML = html;
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                })
                .catch(error => {
                    console.error('Error fetching notifications:', error);
                })
                .finally(() => {
                    isFetchingNotif = false;
                });
        }

        // Notification badge count fetcher
        function fetchNotificationCount() {
            fetch('/dashboard/notifications/unread-count')
                .then(response => {
                    if (!response.ok) throw new Error();
                    return response.text();
                })
                .then(html => {
                    const badge = document.getElementById('unread-notifications-badge');
                    if (badge) {
                        badge.innerHTML = html;
                    }
                })
                .catch(() => {});
        }

        // Mark all notifications as read handler
        function markAllNotificationsAsRead(event) {
            event.preventDefault();
            event.stopPropagation();
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) return;

            fetch('/dashboard/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'text/html'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Failed to mark all as read');
                return response.text();
            })
            .then(html => {
                const dropdown = document.getElementById('notifDropdown');
                if (dropdown) {
                    dropdown.innerHTML = html;
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                }
                // Refresh unread count badge
                fetchNotificationCount();
            })
            .catch(error => {
                console.error('Error marking all read:', error);
            });
        }
        @else
        function toggleNotifDropdown(event) {}
        @endauth

        // Profile dropdown handler
        function toggleProfileDropdown(event) {
            event.stopPropagation();
            document.getElementById('notifDropdown')?.classList.add('hidden');
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('hidden');
        }

        document.addEventListener('click', function(e) {
            const notifDropdown = document.getElementById('notifDropdown');
            const profileDropdown = document.getElementById('profileDropdown');
            const bellButton = document.getElementById('notifBellButton');
            
            if (notifDropdown && !notifDropdown.classList.contains('hidden')) {
                if (!notifDropdown.contains(e.target) && (!bellButton || !bellButton.contains(e.target))) {
                    notifDropdown.classList.add('hidden');
                    @auth
                    fetchNotifications(true); // Fetch silently in background after closing!
                    @endauth
                }
            }
            if (profileDropdown && !profileDropdown.classList.contains('hidden') && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.add('hidden');
            }
        });

        // Dark/Light Theme Handler
        function toggleDarkMode() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateThemeIcon();
        }

        function updateThemeIcon() {
            const icon = document.getElementById('theme-toggle-icon');
            const iconGuest = document.getElementById('theme-toggle-icon-guest');
            const isDark = document.documentElement.classList.contains('dark');
            
            if (icon) {
                icon.setAttribute('data-lucide', isDark ? 'sun' : 'moon');
            }
            if (iconGuest) {
                iconGuest.setAttribute('data-lucide', isDark ? 'sun' : 'moon');
            }
            if (window.lucide) {
                lucide.createIcons();
            }
        }
        

        
        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const icon = document.getElementById('mobile-menu-icon');
            if (!menu) return;
            const isHidden = menu.classList.contains('hidden');
            menu.classList.toggle('hidden');
            if (icon) {
                icon.setAttribute('data-lucide', isHidden ? 'x' : 'menu');
                if (window.lucide) lucide.createIcons();
            }
        }

        function closeMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const icon = document.getElementById('mobile-menu-icon');
            if (menu) menu.classList.add('hidden');
            if (icon) {
                icon.setAttribute('data-lucide', 'menu');
                if (window.lucide) lucide.createIcons();
            }
        }

        // Navbar scroll shadow effect
        window.addEventListener('scroll', function () {
            const navInner = document.getElementById('nav-inner');
            if (!navInner) return;
            if (window.scrollY > 20) {
                navInner.classList.add('shadow-lg');
                navInner.classList.remove('shadow-sm');
            } else {
                navInner.classList.remove('shadow-lg');
                navInner.classList.add('shadow-sm');
            }
        });

        // Show loading bar & spinner on native form submits
        document.addEventListener('submit', function(e) {
            const bar = document.getElementById('top-loading-bar');
            if (bar) {
                bar.style.opacity = '1';
                bar.style.width = '40%';
                setTimeout(() => {
                    if (bar.style.opacity === '1') {
                        bar.style.width = '80%';
                    }
                }, 500);
            }
            
            // Disable submit button to prevent double-submitting
            const submitBtn = e.target.querySelector('button[type="submit"]');
            if (submitBtn) {
                setTimeout(() => {
                    submitBtn.disabled = true;
                    submitBtn.style.pointerEvents = 'none';
                    submitBtn.style.opacity = '0.7';
                }, 10);
            }
        });

        // Show loading bar on menu/link clicks (instant transition feedback)
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;
            
            const href = link.getAttribute('href');
            const target = link.getAttribute('target');
            
            // Skip empty/javascript/anchor/external-tab links
            if (!href || href.startsWith('javascript:') || target === '_blank') {
                return;
            }

            // Skip anchor links on the current page (e.g. "/#program" on home page)
            try {
                const url = new URL(href, window.location.href);
                if (url.origin === window.location.origin && 
                    url.pathname === window.location.pathname && 
                    url.search === window.location.search && 
                    url.hash) {
                    return;
                }
            } catch (err) {
                if (href.startsWith('#')) {
                    return;
                }
            }
            
            // Check if link is internal (same origin)
            const isInternal = href.startsWith('/') || href.startsWith(window.location.origin);
            if (isInternal) {
                const bar = document.getElementById('top-loading-bar');
                if (bar) {
                    bar.style.opacity = '1';
                    bar.style.width = '50%';
                    setTimeout(() => {
                        if (bar.style.opacity === '1') {
                            bar.style.width = '85%';
                        }
                    }, 400);
                }
            }
        });

        // Initialize Lucide Icons & Auto Session Toasts
        document.addEventListener("DOMContentLoaded", function() {
            if (window.lucide) {
                lucide.createIcons();
            }
            updateThemeIcon();
            
            @auth
            // Fetch initial notification count and pre-load notifications silently
            fetchNotificationCount();
            fetchNotifications(true); // Silent pre-load on page load!
            
            setInterval(fetchNotificationCount, 45000);
            // Silently auto-refresh notifications list every 45s ONLY if dropdown is closed
            setInterval(() => {
                const dropdown = document.getElementById('notifDropdown');
                if (dropdown && dropdown.classList.contains('hidden')) {
                    fetchNotifications(true);
                }
            }, 45000);
            
            // Listen to refresh count triggers if dispatched anywhere
            document.addEventListener('refresh-notification-count', () => {
                fetchNotificationCount();
                fetchNotifications(true);
            });
            @endauth
            
            @if(session('success'))
                showToast("{{ session('success') }}", 'success');
            @endif
            
            @if(session('error'))
                showToast("{{ session('error') }}", 'error');
            @endif
            
            @if($errors->any())
                @foreach($errors->all() as $error)
                    showToast("{{ $error }}", 'error');
                @endforeach
            @endif
        });
    </script>
    @stack('scripts')
</body>
</html>
