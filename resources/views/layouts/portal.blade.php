<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🎓</text></svg>">
    @endif

    <!-- Plus Jakarta Sans Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
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
            background-color: #0f172a;
            color: #cbd5e1;
        }
        html.dark nav, html.dark header {
            background-color: #1e293b;
            border-color: #334155;
        }
        html.dark footer {
            background-color: #1e293b;
            border-color: #334155;
            color: #64748b;
        }
        html.dark .bg-white {
            background-color: #1e293b;
            border-color: #334155;
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
            background-color: #0f172a;
        }
        html.dark .border-slate-100, html.dark .border-slate-200 {
            border-color: #334155;
        }
        html.dark input, html.dark select, html.dark textarea {
            background-color: #0f172a;
            border-color: #475569;
            color: #f8fafc;
        }
        html.dark input:focus, html.dark select:focus, html.dark textarea:focus {
            border-color: #10b981;
        }
        html.dark .hover\:bg-slate-50:hover {
            background-color: #334155;
        }
        html.dark .shadow-sm, html.dark .shadow-md, html.dark .shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col text-slate-800 bg-slate-50 dark:bg-slate-950 dark:text-slate-200">

    <!-- Header Navigation -->
    <nav class="bg-white border-b border-slate-100 sticky top-0 z-50 transition dark:bg-slate-900 dark:border-slate-800 text-slate-800 dark:text-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand logo -->
                <a href="{{ auth()->check() ? route('dashboard') : '/' }}" class="flex items-center gap-2.5">
                    @if(!empty($schoolLogo))
                        <img src="{{ $schoolLogo }}" alt="{{ $schoolName }}" class="h-9 object-contain">
                    @else
                        <div class="h-9 w-9 bg-brand-yellow rounded-xl flex items-center justify-center font-bold text-slate-900 text-sm shadow-sm">
                            🎓
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
                        <!-- Guest Navigation links -->
                        <div class="flex items-center gap-8 text-xs font-bold text-slate-500 dark:text-slate-400">
                            @foreach($navbarUnits as $nu)
                                @php
                                    $uCode = strtolower($nu->code);
                                    $isActive = request()->routeIs('unit.detail') && request()->route('code') === $uCode;
                                @endphp
                                <a href="{{ route('unit.detail', $uCode) }}" 
                                   class="transition pb-1 {{ $isActive ? 'text-custom-primary dark:text-emerald-400 font-extrabold border-b-2 border-custom-primary' : 'hover:text-custom-primary dark:hover:text-emerald-400 text-slate-500 dark:text-slate-400' }}">
                                    {{ strtoupper($nu->code) }}
                                </a>
                            @endforeach
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
                                <a href="{{ route('dashboard') }}" class="transition pb-1 {{ (Route::is('dashboard') || Route::is('dashboard.detail')) ? 'text-custom-primary dark:text-emerald-400 font-extrabold border-b-2 border-custom-primary' : 'hover:text-custom-primary text-slate-500 dark:text-slate-400' }}">
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
                <div class="flex items-center gap-4 text-xs font-bold">
                    @guest
                        <!-- Toggle Dark/Light Mode Button -->
                        <button onclick="toggleDarkMode()" class="p-2 text-slate-500 hover:text-custom-primary dark:text-slate-400 dark:hover:text-emerald-400 rounded-xl transition" title="Toggle Tema">
                            <i id="theme-toggle-icon-guest" data-lucide="moon" class="w-4.5 h-4.5"></i>
                        </button>

                        <div class="h-4 w-[1px] bg-slate-200 dark:bg-slate-800"></div>

                        <a href="{{ route('login') }}" class="text-custom-primary dark:text-slate-200 hover:opacity-85 transition py-2">Login</a>
                        <a href="{{ route('register') }}" class="bg-custom-primary hover:bg-custom-primary/95 text-white px-5 py-2.5 rounded-full transition shadow-sm dark:bg-emerald-600 dark:hover:bg-emerald-500">
                            Register Now
                        </a>
                    @else
                        <!-- Toggle Dark/Light Mode Button -->
                        <button onclick="toggleDarkMode()" class="p-2 text-slate-500 hover:text-custom-primary dark:text-slate-400 dark:hover:text-emerald-400 rounded-xl transition" title="Toggle Tema">
                            <i id="theme-toggle-icon" data-lucide="moon" class="w-4.5 h-4.5"></i>
                        </button>

                        <!-- Notifications Toggles with Badge -->
                        <div class="relative">
                            <button onclick="toggleNotifDropdown(event)" class="p-2 text-slate-500 hover:text-custom-primary dark:text-slate-400 dark:hover:text-emerald-400 rounded-xl transition relative" title="Notifikasi">
                                <i data-lucide="bell" class="w-4.5 h-4.5"></i>
                                <span class="absolute top-1.5 right-1.5 h-2 w-2 bg-red-500 rounded-full"></span>
                            </button>
                            <!-- Notifications Dropdown Box -->
                            <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-72 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-800 py-2 z-50 text-xs text-slate-700 dark:text-slate-300">
                                <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800 font-bold text-slate-850 dark:text-white flex justify-between items-center">
                                    <span>Notifikasi Masuk</span>
                                    <span class="bg-red-50 dark:bg-red-950/30 text-red-650 dark:text-red-400 text-[9px] px-2 py-0.5 rounded-full font-bold">3 Baru</span>
                                </div>
                                <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-64 overflow-y-auto">
                                    <a href="#" class="block px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                        <div class="font-bold text-slate-800 dark:text-slate-200">Pembayaran Terkonfirmasi</div>
                                        <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Terima kasih, tagihan pendaftaran Anda telah lunas diverifikasi.</div>
                                        <div class="text-[9px] text-brand-emerald dark:text-emerald-400 font-bold mt-1">Baru saja</div>
                                    </a>
                                    <a href="#" class="block px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                                        <div class="font-bold text-slate-800 dark:text-slate-200">Jadwal Observasi Rilis</div>
                                        <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Jadwal wawancara/observasi ananda Ahmad Raihan telah dijadwalkan.</div>
                                        <div class="text-[9px] text-brand-emerald dark:text-emerald-400 font-bold mt-1">10 menit yang lalu</div>
                                    </a>
                                </div>
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
                                <form method="POST" action="{{ route('logout') }}">
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
    </nav>

    <!-- Main Content Container -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-50 text-slate-500 text-[11px] py-12 border-t border-slate-100 dark:bg-slate-900 dark:border-slate-800 dark:text-slate-450">
        <div class="max-w-7xl mx-auto px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2">
                @if(!empty($schoolLogo))
                    <img src="{{ $schoolLogo }}" alt="{{ $schoolName }}" class="h-7 object-contain">
                @else
                    <div class="h-7 w-7 bg-brand-yellow rounded-lg flex items-center justify-center text-sm shadow-sm">
                        🎓
                    </div>
                @endif
                <div class="flex flex-col">
                    <span class="font-extrabold text-sm leading-tight text-custom-primary dark:text-emerald-400">{{ $schoolName }}</span>
                    @if(!empty($schoolTagline))
                        <span class="text-[9px] text-slate-400 dark:text-slate-500 font-semibold leading-none mt-0.5">{{ $schoolTagline }}</span>
                    @endif
                </div>
            </div>
            
            <div class="flex flex-wrap justify-center gap-6 font-bold text-slate-500">
                <a href="{{ $footerContactUrl }}" target="_blank" class="hover:text-custom-primary dark:hover:text-emerald-400">Contact Us</a>
                <a href="{{ $footerPrivacyUrl }}" target="_blank" class="hover:text-custom-primary dark:hover:text-emerald-400">Privacy Policy</a>
                <a href="{{ $footerTermsUrl }}" target="_blank" class="hover:text-custom-primary dark:hover:text-emerald-400">Terms of Service</a>
                <a href="{{ $footerFaqUrl }}" target="_blank" class="hover:text-custom-primary dark:hover:text-emerald-400">FAQ</a>
            </div>
            
            <div class="text-center md:text-right space-y-1">
                <p class="text-slate-400 font-semibold dark:text-slate-500">{{ $footerCopyright }}</p>
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
        function toggleNotifDropdown(event) {
            event.stopPropagation();
            document.getElementById('profileDropdown')?.classList.add('hidden');
            const dropdown = document.getElementById('notifDropdown');
            dropdown.classList.toggle('hidden');
        }

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
            if (notifDropdown && !notifDropdown.classList.contains('hidden') && !notifDropdown.contains(e.target)) {
                notifDropdown.classList.add('hidden');
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
        

        
        // Initialize Lucide Icons & Auto Session Toasts
        document.addEventListener("DOMContentLoaded", function() {
            if (window.lucide) {
                lucide.createIcons();
            }
            updateThemeIcon();
            
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
