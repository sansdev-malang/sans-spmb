<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel - Sekolah Anak Saleh')</title>
    <!-- Plus Jakarta Sans Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Local Compiled CSS/JS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            transition: background-color 0.3s, color 0.3s;
        }
        .bg-admin-dark { background-color: #0f172a; } /* Slate 900 */
        .bg-brand-emerald { background-color: #0f5132; }
        .text-brand-emerald { color: #0f5132; }
        .text-brand-yellow { color: #ffc107; }
        
        /* iOS Toggle Switch Checked State Sibling style */
        .peer:checked ~ .peer-checked-emerald {
            background-color: #0f5132 !important;
        }
        /* Lucide icon alignment fallback */
        .lucide {
            display: inline-block;
            vertical-align: middle;
        }

        /* Dark Mode Custom Styles */
        html.dark body {
            background-color: #0f172a !important;
            color: #cbd5e1 !important;
        }
        html.dark header {
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
        html.dark table th {
            background-color: #0f172a !important;
            color: #94a3b8 !important;
        }
        html.dark table td {
            border-color: #334155 !important;
        }
        html.dark .shadow-sm, html.dark .shadow-md, html.dark .shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2) !important;
        }
    </style>
</head>
<body class="min-h-screen flex text-slate-800">

    <!-- Sidebar Left Layout -->
    <aside class="w-64 bg-admin-dark text-slate-300 flex flex-col fixed inset-y-0 left-0 z-40 border-r border-slate-800">
        <!-- Brand Logo / Info -->
        <div class="h-16 bg-slate-950 flex items-center gap-3 px-6 border-b border-slate-800">
            <div class="h-8 w-8 bg-brand-yellow rounded-lg flex items-center justify-center font-bold text-slate-900 text-xs shadow">
                SANS
            </div>
            <div>
                <span class="font-extrabold text-sm tracking-wider block text-white leading-none">SANS SPMB</span>
                <span class="text-[9px] text-brand-yellow tracking-widest font-semibold uppercase">Admin Panel</span>
            </div>
        </div>

        <!-- Navigation Menus -->
        <nav class="flex-grow py-6 px-4 space-y-1.5 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" 
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition 
                {{ Route::is('admin.dashboard') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }} mb-4">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
            </a>

            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest px-3 block mb-2">Penerimaan</span>
            
            <a href="{{ route('admin.peninjauan') }}" 
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition 
                {{ Route::is('admin.peninjauan') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                <i data-lucide="check-square" class="w-4 h-4"></i> Peninjauan
            </a>
            
            <a href="{{ route('admin.candidates') }}" 
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition 
                {{ Route::is('admin.candidates') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                <i data-lucide="users" class="w-4 h-4"></i> Data Pendaftar
            </a>
            
            <a href="{{ route('admin.payments') }}" 
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition 
                {{ Route::is('admin.payments') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                <i data-lucide="wallet" class="w-4 h-4"></i> Laporan Pembayaran
            </a>

            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest px-3 block mt-6 mb-2">Konfigurasi</span>
            
            <a href="{{ route('admin.ui-settings') }}" 
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition 
                {{ Route::is('admin.ui-settings') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                <i data-lucide="palette" class="w-4 h-4"></i> Setting UI Portal
            </a>

            <a href="{{ route('admin.spmb-settings.form') }}" 
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition 
                {{ Route::is('admin.spmb-settings.form') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                <i data-lucide="settings-2" class="w-4 h-4"></i> Setting Formulir
            </a>
            
            <!-- Collapsible Setting SPMB Dropdown -->
            <div class="space-y-1">
                <button type="button" onclick="toggleSpmbDropdown()" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide hover:bg-slate-800/50 hover:text-white transition">
                    <span class="flex items-center gap-3">
                        <i data-lucide="sliders" class="w-4 h-4"></i> Setting SPMB
                    </span>
                    <span id="spmbDropdownArrow" class="text-[9px] text-slate-500 font-bold">{{ Request::is('admin/spmb-settings*') ? '▲' : '▼' }}</span>
                </button>
                <div id="spmbSubmenu" class="pl-7 space-y-1 {{ Request::is('admin/spmb-settings*') ? '' : 'hidden' }}">
                    <a href="{{ route('admin.spmb-settings') }}" class="block py-1 px-3 rounded-lg text-[10px] font-semibold transition {{ Route::is('admin.spmb-settings') ? 'text-brand-yellow font-bold' : 'text-slate-400 hover:text-white' }} flex items-center gap-1.5">
                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i> Gelombang & Jalur
                    </a>
                    <a href="{{ route('admin.spmb-settings.registration') }}" class="block py-1 px-3 rounded-lg text-[10px] font-semibold transition {{ Route::is('admin.spmb-settings.registration') ? 'text-brand-yellow font-bold' : 'text-slate-400 hover:text-white' }} flex items-center gap-1.5">
                        <i data-lucide="toggle-left" class="w-3.5 h-3.5"></i> Aktivasi SPMB
                    </a>
                    <a href="{{ route('admin.spmb-settings.fees') }}" class="block py-1 px-3 rounded-lg text-[10px] font-semibold transition {{ Route::is('admin.spmb-settings.fees') ? 'text-brand-yellow font-bold' : 'text-slate-400 hover:text-white' }} flex items-center gap-1.5">
                        <i data-lucide="coins" class="w-3.5 h-3.5"></i> Setting Biaya
                    </a>
                </div>
            </div>

            <a href="{{ route('admin.api-integrations') }}" 
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition 
                {{ Route::is('admin.api-integrations') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                <i data-lucide="blocks" class="w-4 h-4"></i> Integrasi API
            </a>

            <a href="{{ route('admin.settings') }}" 
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition 
                {{ Route::is('admin.settings') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                <i data-lucide="credit-card" class="w-4 h-4"></i> Winpay Payment
            </a>

            <a href="{{ route('admin.users') }}" 
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition 
                {{ Route::is('admin.users') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                <i data-lucide="users-round" class="w-4 h-4"></i> Data User
            </a>
        </nav>

        <!-- User profile section bottom -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/40 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2">
                <div class="h-8 w-8 rounded-full bg-slate-800 text-brand-yellow font-bold flex items-center justify-center border border-slate-700">
                    P
                </div>
                <div>
                    <span class="font-bold text-slate-200 block truncate max-w-[120px]">{{ auth()->user()->name }}</span>
                    <span class="text-[9px] text-slate-500">Administrator</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" title="Logout" class="p-1.5 transition">
                    <i data-lucide="log-out" class="w-4 h-4 text-slate-400 hover:text-red-500"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Right Content Area -->
    <div class="flex-grow pl-64 flex flex-col min-h-screen">
        
        <!-- Header Panel -->
        <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-8 sticky top-0 z-30 shadow-sm transition">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                <span>Sekolah Anak Saleh</span>
                <span>/</span>
                <span class="text-brand-emerald font-bold">@yield('page_title', 'Dashboard')</span>
            </div>
            
            <div class="flex items-center gap-3 text-xs font-medium">
                <!-- Toggle Dark/Light Mode Button -->
                <button onclick="toggleDarkMode()" class="p-2 text-slate-500 hover:text-brand-emerald rounded-xl hover:bg-slate-50 transition" title="Toggle Tema">
                    <i id="theme-toggle-icon" data-lucide="moon" class="w-4.5 h-4.5"></i>
                </button>

                <!-- Notifications Toggles with Badge -->
                <div class="relative">
                    <button onclick="toggleNotifDropdown(event)" class="p-2 text-slate-500 hover:text-brand-emerald rounded-xl hover:bg-slate-50 transition relative" title="Notifikasi">
                        <i data-lucide="bell" class="w-4.5 h-4.5"></i>
                        <span class="absolute top-1.5 right-1.5 h-2 w-2 bg-red-500 rounded-full"></span>
                    </button>
                    <!-- Notifications Dropdown Box -->
                    <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-150 py-2 z-50 animate-fade-in text-xs text-slate-700">
                        <div class="px-4 py-2 border-b border-slate-100 font-bold text-slate-800 flex justify-between items-center">
                            <span>Notifikasi Masuk</span>
                            <span class="bg-red-50 text-red-600 text-[9px] px-2 py-0.5 rounded-full font-bold">3 Baru</span>
                        </div>
                        <div class="divide-y divide-slate-55 max-h-64 overflow-y-auto">
                            <a href="{{ route('admin.peninjauan') }}" class="block px-4 py-3 hover:bg-slate-50 transition">
                                <div class="font-bold text-slate-800">Pendaftaran Baru Berkas Masuk</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">Calon siswa Ahmad Raihan telah mengunggah berkas.</div>
                                <div class="text-[9px] text-brand-emerald font-bold mt-1">2 menit yang lalu</div>
                            </a>
                            <a href="{{ route('admin.payments') }}" class="block px-4 py-3 hover:bg-slate-50 transition">
                                <div class="font-bold text-slate-800">Pembayaran Sukses (Winpay)</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">Invoice INV-SPMB-20260819-2-622 telah lunas via Mandiri.</div>
                                <div class="text-[9px] text-brand-emerald font-bold mt-1">15 menit yang lalu</div>
                            </a>
                            <a href="{{ route('admin.users') }}" class="block px-4 py-3 hover:bg-slate-50 transition">
                                <div class="font-bold text-slate-800">User Baru Terdaftar</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">Akun wali murid Siti Aminah telah didaftarkan.</div>
                                <div class="text-[9px] text-brand-emerald font-bold mt-1">1 jam yang lalu</div>
                            </a>
                        </div>
                        <div class="px-4 py-2 border-t border-slate-100 text-center">
                            <a href="{{ route('admin.peninjauan') }}" class="text-[10px] text-brand-emerald font-bold hover:underline">Lihat Semua Aktifitas</a>
                        </div>
                    </div>
                </div>

                <a href="{{ route('dashboard') }}" target="_blank" class="bg-emerald-50 text-brand-emerald hover:bg-emerald-100 px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Buka Portal Pendaftar
                </a>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-grow p-8">
            @yield('content')
        </main>
        
        <!-- Footer -->
        <footer class="bg-white border-t border-slate-100 py-4 text-center text-[10px] text-slate-400 transition">
            &copy; {{ date('Y') }} Sekolah Anak Saleh Admin Panel. Integrasi Winpay SNAP API (Simulator).
        </footer>

    </div>
    <!-- Toast Notification Container -->
    <div id="toastContainer" class="fixed top-5 right-5 z-[9999] space-y-3 pointer-events-none"></div>

    <!-- Script triggers and controllers -->
    <script>
        // Collapsible dropdown
        function toggleSpmbDropdown() {
            const submenu = document.getElementById('spmbSubmenu');
            const arrow = document.getElementById('spmbDropdownArrow');
            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                arrow.innerText = '▲';
            } else {
                submenu.classList.add('hidden');
                arrow.innerText = '▼';
            }
        }

        // Notification dropdown handler
        function toggleNotifDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('notifDropdown');
            dropdown.classList.toggle('hidden');
        }

        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('notifDropdown');
            if (dropdown && !dropdown.classList.contains('hidden') && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
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

        // Theme init on load
        (function() {
            const theme = localStorage.getItem('theme');
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();

        // Toast Engine function
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
        
        // Initialize Lucide Icons & Auto Session Toasts
        document.addEventListener("DOMContentLoaded", function() {
            lucide.createIcons();
            updateThemeIcon();
            
            @if(session('success'))
                showToast("{{ session('success') }}", 'success');
            @endif
            
            @if(session('error'))
                showToast("{{ session('error') }}", 'error');
            @endif
            
            @if($errors->any() && !session('failed_modal'))
                @foreach($errors->all() as $error)
                    showToast("{{ $error }}", 'error');
                @endforeach
            @endif
        });
    </script>
</body>
</html>
