<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SPMB Sekolah Anak Saleh')</title>
    <!-- Plus Jakarta Sans Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Local Compiled CSS/JS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Theme init on load (Inline to prevent flash) -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme');
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
        .bg-brand-emerald { background-color: #0f5132; }
        .text-brand-emerald { color: #0f5132; }
        .border-brand-emerald { border-color: #0f5132; }
        
        .bg-brand-yellow { background-color: #ffc107; }
        .text-brand-yellow { color: #ffc107; }
        
        .hover-emerald:hover { background-color: #146c43; }

        /* Dark Mode Custom Styles */
        html.dark body {
            background-color: #0f172a !important;
            color: #cbd5e1 !important;
        }
        html.dark nav, html.dark header {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }
        html.dark footer {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #64748b !important;
        }
        html.dark .bg-white {
            background-color: #1e293b !important;
            border-color: #334155 !important;
        }
        html.dark .text-slate-800, html.dark h1, html.dark h2, html.dark h3, html.dark h4 {
            color: #f8fafc !important;
        }
        html.dark .text-slate-700, html.dark .text-slate-600 {
            color: #cbd5e1 !important;
        }
        html.dark .text-slate-500, html.dark .text-slate-400 {
            color: #64748b !important;
        }
        html.dark .bg-slate-50, html.dark .bg-slate-50\/50, html.dark .bg-slate-50\/30 {
            background-color: #0f172a !important;
        }
        html.dark .border-slate-100, html.dark .border-slate-200 {
            border-color: #334155 !important;
        }
        html.dark input, html.dark select, html.dark textarea {
            background-color: #0f172a !important;
            border-color: #475569 !important;
            color: #f8fafc !important;
        }
        html.dark input:focus, html.dark select:focus, html.dark textarea:focus {
            border-color: #10b981 !important;
        }
        html.dark .hover\:bg-slate-50:hover {
            background-color: #334155 !important;
        }
        html.dark .shadow-sm, html.dark .shadow-md, html.dark .shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2) !important;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col text-slate-800">

    <!-- Header Navigation -->
    <nav class="bg-brand-emerald text-white shadow-lg sticky top-0 z-50 transition border-b border-transparent">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand logo -->
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 bg-brand-yellow rounded-xl flex items-center justify-center font-bold text-slate-900 text-xs shadow">
                        SANS
                    </div>
                    <div>
                        <span class="font-extrabold text-lg tracking-wider block leading-none">SANS SPMB</span>
                        <span class="text-[10px] text-brand-yellow tracking-widest font-semibold">PORTAL PENDAFTARAN</span>
                    </div>
                </div>

                <!-- Navigation links -->
                <div class="flex items-center gap-4 text-sm font-medium">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-yellow transition py-2 font-bold text-brand-yellow">Admin Dashboard</a>
                        @else
                            <a href="{{ route('dashboard') }}" class="hover:text-brand-yellow transition py-2 font-bold">Dashboard Pendaftar</a>
                        @endif
                        
                        <div class="h-4 w-[1px] bg-emerald-700"></div>

                        <!-- Toggle Dark/Light Mode Button -->
                        <button onclick="toggleDarkMode()" class="p-2 text-slate-200 hover:text-brand-yellow rounded-xl transition" title="Toggle Tema">
                            <i id="theme-toggle-icon" data-lucide="moon" class="w-4.5 h-4.5"></i>
                        </button>

                        <!-- Notifications Toggles with Badge -->
                        <div class="relative">
                            <button onclick="toggleNotifDropdown(event)" class="p-2 text-slate-200 hover:text-brand-yellow rounded-xl transition relative" title="Notifikasi">
                                <i data-lucide="bell" class="w-4.5 h-4.5"></i>
                                <span class="absolute top-1.5 right-1.5 h-2 w-2 bg-red-500 rounded-full"></span>
                            </button>
                            <!-- Notifications Dropdown Box -->
                            <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 text-xs text-slate-700">
                                <div class="px-4 py-2 border-b border-slate-100 font-bold text-slate-800 flex justify-between items-center">
                                    <span>Notifikasi Masuk</span>
                                    <span class="bg-red-50 text-red-600 text-[9px] px-2 py-0.5 rounded-full font-bold">3 Baru</span>
                                </div>
                                <div class="divide-y divide-slate-100 max-h-64 overflow-y-auto">
                                    <a href="#" class="block px-4 py-3 hover:bg-slate-50 transition">
                                        <div class="font-bold text-slate-800">Pembayaran Terkonfirmasi</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Terima kasih, tagihan pendaftaran Anda telah lunas diverifikasi.</div>
                                        <div class="text-[9px] text-brand-emerald font-bold mt-1">Baru saja</div>
                                    </a>
                                    <a href="#" class="block px-4 py-3 hover:bg-slate-50 transition">
                                        <div class="font-bold text-slate-800">Jadwal Observasi Rilis</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Jadwal wawancara/observasi ananda Ahmad Raihan telah dijadwalkan.</div>
                                        <div class="text-[9px] text-brand-emerald font-bold mt-1">10 menit yang lalu</div>
                                    </a>
                                    <a href="#" class="block px-4 py-3 hover:bg-slate-50 transition">
                                        <div class="font-bold text-slate-800">Registrasi Berhasil</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Selamat datang di portal SPMB Sekolah Anak Saleh. Silakan isi form.</div>
                                        <div class="text-[9px] text-brand-emerald font-bold mt-1">1 jam yang lalu</div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Dropdown Button -->
                        <div class="relative">
                            <button onclick="toggleProfileDropdown(event)" class="flex items-center gap-2 p-2 hover:bg-emerald-800 rounded-xl transition text-xs font-bold">
                                <i data-lucide="user" class="w-4 h-4 text-brand-yellow"></i>
                                <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-300"></i>
                            </button>
                            <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 text-xs text-slate-700">
                                <div class="px-4 py-2 border-b border-slate-100">
                                    <p class="font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5 truncate">{{ auth()->user()->email }}</p>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 hover:bg-slate-50 transition font-medium text-slate-700 flex items-center gap-2">
                                    <i data-lucide="settings" class="w-3.5 h-3.5 text-slate-400"></i> Edit Profil
                                </a>
                                <div class="border-t border-slate-100 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2.5 hover:bg-red-50 text-red-600 transition font-semibold flex items-center gap-2">
                                        <i data-lucide="log-out" class="w-3.5 h-3.5 text-red-500"></i> Keluar (Logout)
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="/" class="hover:text-brand-yellow transition">Home</a>
                        <a href="{{ route('login') }}" class="hover:text-brand-yellow transition">Masuk</a>
                        <a href="{{ route('register') }}" class="bg-brand-yellow text-slate-900 hover:bg-yellow-400 px-4 py-2 rounded-xl text-xs font-bold transition shadow">
                            Daftar Sekarang
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Sub-Navbar Navigation for Candidate Dashboard Tabs -->
    @auth
        @if(!auth()->user()->isAdmin())
            <div class="bg-emerald-800 text-white border-t border-emerald-700/50 shadow shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex gap-2 overflow-x-auto text-[10px] py-3.5 font-bold tracking-wider uppercase">
                    <a href="{{ route('dashboard') }}" class="hover:text-brand-yellow transition px-3.5 py-2 rounded-lg {{ Route::is('dashboard') ? 'bg-emerald-900 text-brand-yellow font-black' : 'text-emerald-100' }} flex items-center gap-1.5">
                        <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i> Dashboard
                    </a>
                    <a href="{{ route('dashboard.form') }}" class="hover:text-brand-yellow transition px-3.5 py-2 rounded-lg {{ Route::is('dashboard.form') ? 'bg-emerald-900 text-brand-yellow font-black' : 'text-emerald-100' }} flex items-center gap-1.5">
                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Formulir
                    </a>
                    <a href="{{ route('dashboard.payment') }}" class="hover:text-brand-yellow transition px-3.5 py-2 rounded-lg {{ Route::is('dashboard.payment') ? 'bg-emerald-900 text-brand-yellow font-black' : 'text-emerald-100' }} flex items-center gap-1.5">
                        <i data-lucide="credit-card" class="w-3.5 h-3.5"></i> Payment
                    </a>
                    <a href="{{ route('dashboard.verification') }}" class="hover:text-brand-yellow transition px-3.5 py-2 rounded-lg {{ Route::is('dashboard.verification') ? 'bg-emerald-900 text-brand-yellow font-black' : 'text-emerald-100' }} flex items-center gap-1.5">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Verification
                    </a>
                    <a href="{{ route('dashboard.observation') }}" class="hover:text-brand-yellow transition px-3.5 py-2 rounded-lg {{ Route::is('dashboard.observation') ? 'bg-emerald-900 text-brand-yellow font-black' : 'text-emerald-100' }} flex items-center gap-1.5">
                        <i data-lucide="video" class="w-3.5 h-3.5"></i> Observation
                    </a>
                    <a href="{{ route('dashboard.result') }}" class="hover:text-brand-yellow transition px-3.5 py-2 rounded-lg {{ Route::is('dashboard.result') ? 'bg-emerald-900 text-brand-yellow font-black' : 'text-emerald-100' }} flex items-center gap-1.5">
                        <i data-lucide="award" class="w-3.5 h-3.5"></i> Final Result
                    </a>
                </div>
            </div>
        @endif
    @endauth

    <!-- Main Content Container -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 text-xs py-8 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 text-center sm:flex sm:justify-between sm:items-center">
            <p>&copy; {{ date('Y') }} Sekolah Anak Saleh. Hak Cipta Dilindungi.</p>
            <p class="mt-2 sm:mt-0 flex justify-center gap-4">
                <span class="text-slate-500">Integrasi Winpay SNAP API (Mode Simulator)</span>
            </p>
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
            if (!icon) return;
            const isDark = document.documentElement.classList.contains('dark');
            icon.setAttribute('data-lucide', isDark ? 'sun' : 'moon');
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
</body>
</html>
