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
    
    <!-- Quill Rich Text Editor (Loaded globally for subpages) -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    
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
        .bg-brand-yellow { background-color: #ffc107; }
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
        
        /* HTMX Loading Progress Bar & Button Disable States */
        #top-loading-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background-color: #10b981; /* brand emerald */
            z-index: 99999;
            width: 0;
            opacity: 0;
            transition: width 0.4s ease, opacity 0.2s ease;
            box-shadow: 0 0 10px #10b981, 0 0 5px #10b981;
        }
        .htmx-request {
            opacity: 0.6 !important;
            pointer-events: none !important;
            transition: opacity 0.15s ease;
            cursor: wait !important;
        }
    </style>
</head>
<body class="min-h-screen flex text-slate-800 bg-slate-50 dark:bg-slate-950 dark:text-slate-200" hx-boost="true" hx-target="#admin-layout-wrapper" hx-select="#admin-layout-wrapper">
    <!-- YouTube-style dynamic top progress loading bar -->
    <div id="top-loading-bar"></div>

    <div id="admin-layout-wrapper" class="flex w-full min-h-screen">
        <!-- Sidebar Left Layout -->
    <aside id="sidebar-left" class="w-64 bg-admin-dark text-slate-300 flex flex-col fixed inset-y-0 left-0 z-40 border-r border-slate-800 transition-transform duration-300 transform -translate-x-full lg:translate-x-0">
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
 
            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest px-3 block mb-2">Operasional Utama</span>
            
            <a href="{{ route('admin.verification') }}" 
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition 
                {{ Route::is('admin.verification') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                <i data-lucide="check-square" class="w-4 h-4"></i> Verifikasi Data
            </a>
            
            <a href="{{ route('admin.candidates') }}" 
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition 
                {{ Route::is('admin.candidates') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                <i data-lucide="users" class="w-4 h-4"></i> Data Pendaftar
            </a>
            
            <a href="{{ route('admin.history') }}" 
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition 
                {{ Route::is('admin.history') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                <i data-lucide="history" class="w-4 h-4"></i> Riwayat Pendaftaran (Log)
            </a>
            
            <a href="{{ route('admin.payments.data') }}" 
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition 
                {{ Route::is('admin.payments.data') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                <i data-lucide="wallet" class="w-4 h-4"></i> Data Pembayaran
            </a>
            
            <a href="{{ route('admin.payments') }}" 
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition 
                {{ Route::is('admin.payments') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                <i data-lucide="receipt" class="w-4 h-4"></i> Riwayat Pembayaran (Log)
            </a>

            <!-- Konfigurasi Sistem Section -->
            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest px-3 block mt-6 mb-2">Konfigurasi Sistem</span>
            
            @if(auth()->user()->isSuperAdmin())
                <!-- 1. Aktivasi SPMB -->
                <a href="{{ route('admin.spmb-settings.registration') }}" 
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition 
                    {{ Route::is('admin.spmb-settings.registration') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                    <i data-lucide="toggle-left" class="w-4 h-4"></i> Aktivasi SPMB
                </a>
            @endif

            <!-- 2. QR Code SPMB (Top-level under Konfigurasi) -->
            <a href="{{ route('admin.spmb-settings.qrcode') }}" 
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition 
                {{ Route::is('admin.spmb-settings.qrcode') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                <i data-lucide="qr-code" class="w-4 h-4"></i> QR Code SPMB
            </a>

            <!-- 3. Manajemen User (Top-level under Konfigurasi) -->
            <a href="{{ route('admin.users') }}" 
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition 
                {{ Route::is('admin.users') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                <i data-lucide="users-round" class="w-4 h-4"></i> Manajemen User
            </a>

            <!-- 2. Data Master Collapsible Dropdown -->
             @php
                $isMasterActive = Request::is('admin/settings*') || Request::is('admin/spmb-settings') || Request::is('admin/spmb-settings/units-grades') || Request::is('admin/spmb-settings/fees') || Request::is('admin/spmb-settings/form*') || Request::is('admin/spmb-settings/instructions*') || Request::is('admin/spmb-settings/agreements*');
            @endphp
            <div class="space-y-1">
                <button type="button" onclick="toggleMasterDropdown()" 
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition
                    {{ $isMasterActive ? 'bg-slate-800 text-white font-bold' : 'hover:bg-slate-800/50 hover:text-white text-slate-400' }}">
                    <span class="flex items-center gap-3">
                        <i data-lucide="database" class="w-4 h-4"></i> Data Master
                    </span>
                    <span id="masterDropdownArrow" class="text-[9px] text-slate-500 font-bold">{{ $isMasterActive ? '▲' : '▼' }}</span>
                </button>
                <div id="masterSubmenu" class="pl-7 space-y-1 {{ $isMasterActive ? '' : 'hidden' }}">
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.spmb-settings.units-grades') }}" class="block py-1 px-3 rounded-lg text-[10px] font-semibold transition {{ Route::is('admin.spmb-settings.units-grades') ? 'text-brand-yellow font-bold' : 'text-slate-400 hover:text-white' }} flex items-center gap-1.5">
                            <i data-lucide="building-2" class="w-3.5 h-3.5"></i> Struktur Sekolah
                        </a>
                        <a href="{{ route('admin.spmb-settings') }}" class="block py-1 px-3 rounded-lg text-[10px] font-semibold transition {{ Route::is('admin.spmb-settings') ? 'text-brand-yellow font-bold' : 'text-slate-400 hover:text-white' }} flex items-center gap-1.5">
                            <i data-lucide="git-branch" class="w-3.5 h-3.5"></i> Jalur & Gelombang
                        </a>
                    @endif
                    <a href="{{ route('admin.spmb-settings.fees') }}" class="block py-1 px-3 rounded-lg text-[10px] font-semibold transition {{ Route::is('admin.spmb-settings.fees') ? 'text-brand-yellow font-bold' : 'text-slate-400 hover:text-white' }} flex items-center gap-1.5">
                        <i data-lucide="coins" class="w-3.5 h-3.5"></i> Tarif & Biaya
                    </a>
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.settings') }}" class="block py-1 px-3 rounded-lg text-[10px] font-semibold transition {{ Route::is('admin.settings') ? 'text-brand-yellow font-bold' : 'text-slate-400 hover:text-white' }} flex items-center gap-1.5">
                            <i data-lucide="percent" class="w-3.5 h-3.5"></i> Biaya Admin Transaksi
                        </a>
                        <a href="{{ route('admin.spmb-settings.form') }}" class="block py-1 px-3 rounded-lg text-[10px] font-semibold transition {{ Route::is('admin.spmb-settings.form') ? 'text-brand-yellow font-bold' : 'text-slate-400 hover:text-white' }} flex items-center gap-1.5">
                            <i data-lucide="settings-2" class="w-3.5 h-3.5"></i> Setting Formulir
                        </a>
                    @endif
                    <a href="{{ route('admin.spmb-settings.instructions') }}" class="block py-1 px-3 rounded-lg text-[10px] font-semibold transition {{ Route::is('admin.spmb-settings.instructions') ? 'text-brand-yellow font-bold' : 'text-slate-400 hover:text-white' }} flex items-center gap-1.5">
                        <i data-lucide="scroll-text" class="w-3.5 h-3.5"></i> Instruksi Daftar Ulang
                    </a>
                    <a href="{{ route('admin.spmb-settings.agreements') }}" class="block py-1 px-3 rounded-lg text-[10px] font-semibold transition {{ Route::is('admin.spmb-settings.agreements') ? 'text-brand-yellow font-bold' : 'text-slate-400 hover:text-white' }} flex items-center gap-1.5">
                        <i data-lucide="file-signature" class="w-3.5 h-3.5"></i> Surat Pernyataan
                    </a>
                </div>
            </div>

            @if(auth()->user()->isSuperAdmin())
                <!-- Standalone Desain & Tampilan Category -->
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest px-3 block mt-6 mb-2">Desain & Tampilan</span>
                <a href="{{ route('admin.ui-settings') }}" 
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition mb-2
                    {{ Route::is('admin.ui-settings') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white text-slate-400' }}">
                    <i data-lucide="palette" class="w-4 h-4"></i> Tampilan Portal
                </a>

                <!-- 3. Pengaturan Teknis Collapsible Dropdown -->
                @php
                    $isTechActive = Request::is('admin/api-integrations*') || Request::is('admin/payment-gateways*') || Request::is('admin/activity-logs*') || Request::is('admin/logs*');
                @endphp
                <div class="space-y-1">
                    <button type="button" onclick="toggleTechDropdown()" 
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition
                        {{ $isTechActive ? 'bg-slate-800 text-white font-bold' : 'hover:bg-slate-800/50 hover:text-white text-slate-400' }}">
                        <span class="flex items-center gap-3">
                            <i data-lucide="settings" class="w-4 h-4"></i> Pengaturan Teknis
                        </span>
                        <span id="techDropdownArrow" class="text-[9px] text-slate-500 font-bold">{{ $isTechActive ? '▲' : '▼' }}</span>
                    </button>
                    <div id="techSubmenu" class="pl-7 space-y-1 {{ $isTechActive ? '' : 'hidden' }}">
                        <a href="{{ route('admin.api-integrations') }}" class="block py-1 px-3 rounded-lg text-[10px] font-semibold transition {{ Route::is('admin.api-integrations') ? 'text-brand-yellow font-bold' : 'text-slate-400 hover:text-white' }} flex items-center gap-1.5">
                            <i data-lucide="blocks" class="w-3.5 h-3.5"></i> Integrasi API
                        </a>
                        <a href="{{ route('admin.payment-gateways.index') }}" class="block py-1 px-3 rounded-lg text-[10px] font-semibold transition {{ Route::is('admin.payment-gateways.index') ? 'text-brand-yellow font-bold' : 'text-slate-400 hover:text-white' }} flex items-center gap-1.5">
                            <i data-lucide="credit-card" class="w-3.5 h-3.5"></i> CRUD Gateway
                        </a>
                        @foreach($sidebarGateways as $sgw)
                            <a href="{{ route('admin.payment-gateways.settings', $sgw->code) }}" class="block py-1 px-3 rounded-lg text-[10px] font-semibold transition {{ Request::is('admin/payment-gateways/' . $sgw->code . '/settings') ? 'text-brand-yellow font-bold' : 'text-slate-400 hover:text-white' }} flex items-center gap-1.5">
                                <i data-lucide="settings" class="w-3.5 h-3.5 text-slate-500"></i> Set {{ $sgw->name }}
                            </a>
                        @endforeach
                        <a href="{{ route('admin.activity-logs') }}" class="block py-1 px-3 rounded-lg text-[10px] font-semibold transition {{ Route::is('admin.activity-logs') ? 'text-brand-yellow font-bold' : 'text-slate-400 hover:text-white' }} flex items-center gap-1.5">
                            <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i> Log Aktivitas
                        </a>
                        <a href="{{ route('admin.logs') }}" class="block py-1 px-3 rounded-lg text-[10px] font-semibold transition {{ Route::is('admin.logs') ? 'text-brand-yellow font-bold' : 'text-slate-400 hover:text-white' }} flex items-center gap-1.5">
                            <i data-lucide="scroll-text" class="w-3.5 h-3.5"></i> Log Sistem
                        </a>
                    </div>
                </div>
            @endif
        </nav>

        <!-- User profile section bottom -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/40 relative text-xs select-none">
            <!-- Profile Capsule Button (Click to toggle dropdown) -->
            <button type="button" onclick="toggleProfileDropdown(event)" class="w-full flex items-center justify-between hover:bg-slate-800/30 p-1.5 rounded-xl transition duration-200 text-left">
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-full bg-brand-emerald text-brand-yellow font-black flex items-center justify-center border border-emerald-800 shadow-inner uppercase">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="overflow-hidden">
                        <span class="font-extrabold text-slate-200 block truncate max-w-[110px]">{{ auth()->user()->name }}</span>
                        <span class="text-[9px] text-slate-500 font-semibold uppercase tracking-wider block mt-0.5">
                            @if(auth()->user()->isSuperAdmin())
                                Super Admin
                            @elseif(auth()->user()->spmb_unit_id)
                                Admin {{ auth()->user()->spmbUnit->name }}
                            @else
                                Global Admin
                            @endif
                        </span>
                    </div>
                </div>
                <i data-lucide="chevrons-up-down" class="w-4 h-4 text-slate-500"></i>
            </button>

            <!-- Upward Floating Dropdown Menu -->
            <div id="profileDropdown" class="hidden absolute bottom-16 left-4 right-4 bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl py-2 z-50 animate-fade-in text-xs text-slate-350">
                <div class="px-4 py-2 border-b border-slate-800 text-[10px] text-slate-500 font-bold uppercase tracking-wider">
                    Menu Akun
                </div>
                <div class="divide-y divide-slate-800/50">
                    <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-slate-800 hover:text-white transition">
                        <i data-lucide="user" class="w-4 h-4 text-slate-500"></i> Profil Saya
                    </a>
                    <a href="{{ route('admin.profile.password') }}" class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-slate-800 hover:text-white transition">
                        <i data-lucide="key-round" class="w-4 h-4 text-slate-500"></i> Ganti Password
                    </a>
                    
                    <div class="pt-1.5">
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-red-500 hover:bg-red-950/20 hover:text-red-400 transition font-bold text-left">
                                <i data-lucide="log-out" class="w-4 h-4"></i> Logout / Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Sidebar Mobile Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/40 z-35 hidden transition-opacity duration-300 opacity-0 lg:hidden" onclick="closeSidebar()"></div>

    <!-- Right Content Area -->
    <div class="flex-grow pl-0 lg:pl-64 flex flex-col min-h-screen transition-all duration-300">
        
        <!-- Header Panel -->
        <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between px-4 lg:px-8 sticky top-0 z-30 shadow-sm transition">
            <div class="flex items-center gap-3">
                <!-- Hamburger menu button visible only on mobile/tablet -->
                <button type="button" onclick="openSidebar()" class="lg:hidden p-2 text-slate-500 hover:text-brand-emerald rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition" title="Buka Menu">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                    <span class="hidden sm:inline">Sekolah Anak Saleh</span>
                    <span class="hidden sm:inline">/</span>
                    <span class="text-brand-emerald font-bold">@yield('page_title', 'Dashboard')</span>
                </div>
            </div>
            
            <div class="flex items-center gap-3 text-xs font-medium">
                @php
                    $globalPeriods = \App\Models\SpmbPeriod::orderBy('year', 'desc')->get();
                    $selectedPeriodId = session('selected_period_id', function() {
                        return \App\Models\SpmbPeriod::where('is_active', true)->value('id') 
                            ?? \App\Models\SpmbPeriod::value('id');
                    });
                @endphp
                <!-- Global Academic Year Selector Form -->
                <form action="{{ route('admin.change-period') }}" method="POST" id="globalPeriodForm" class="mr-1">
                    @csrf
                    <label for="global_period_selector" class="sr-only">Tahun Ajaran</label>
                    <select name="selected_period_id" id="global_period_selector" onchange="document.getElementById('globalPeriodForm').submit()" class="bg-slate-50 border border-slate-205 dark:bg-slate-800 dark:border-slate-700 dark:text-white rounded-xl px-3 py-1.5 text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-emerald cursor-pointer">
                        @foreach($globalPeriods as $p)
                            <option value="{{ $p->id }}" {{ $p->id == $selectedPeriodId ? 'selected' : '' }}>
                                TA {{ $p->year }} {{ $p->is_active ? '● (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>

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
                            <a href="{{ route('admin.verification') }}" class="block px-4 py-3 hover:bg-slate-50 transition">
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
                            <a href="{{ route('admin.verification') }}" class="text-[10px] text-brand-emerald font-bold hover:underline">Lihat Semua Aktifitas</a>
                        </div>
                    </div>
                </div>

                <a href="{{ route('dashboard') }}" target="_blank" class="bg-emerald-50 text-brand-emerald hover:bg-emerald-100 px-2 sm:px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5" title="Buka Portal Pendaftar">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    <span class="hidden md:inline">Buka Portal Pendaftar</span>
                </a>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-grow p-4 lg:p-8">
            @yield('content')
        </main>
        
        <!-- Footer -->
        <footer class="bg-white border-t border-slate-100 py-4 text-center text-[10px] text-slate-400 transition">
            &copy; {{ date('Y') }} Sekolah Anak Saleh Admin Panel. Integrasi Winpay SNAP API (Simulator).
        </footer>

    </div>
    </div>
    <!-- Toast Notification Container -->
    <div id="toastContainer" class="fixed top-5 right-5 z-[9999] space-y-3 pointer-events-none"></div>

    <!-- Global Delete Confirmation Modal -->
    <div id="confirmDeleteModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/40 opacity-0 pointer-events-none transition-all duration-100">
        <div class="bg-white w-full max-w-sm rounded-3xl shadow-xl transform scale-95 transition-all duration-100 p-6 space-y-4" id="confirmDeleteModalBody">
            <div class="flex items-center gap-3 text-rose-600">
                <div class="h-10 w-10 rounded-full bg-rose-50 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-800">Konfirmasi Hapus</h3>
            </div>
            <p id="confirmDeleteMessage" class="text-xs text-slate-500 leading-relaxed font-semibold">Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 border border-slate-200 hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-bold transition">Batal</button>
                <form id="confirmDeleteForm" method="POST" action="" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-sm">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Script triggers and controllers -->
    <script>
        // Custom Delete Confirmation Modal Controllers
        function confirmDelete(actionUrl, message = 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.') {
            const modal = document.getElementById('confirmDeleteModal');
            const modalBody = document.getElementById('confirmDeleteModalBody');
            const form = document.getElementById('confirmDeleteForm');
            const messageEl = document.getElementById('confirmDeleteMessage');

            form.action = actionUrl;
            messageEl.innerText = message;

            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalBody.classList.remove('scale-95');
            modalBody.classList.add('scale-100');
            
            if (window.lucide) {
                lucide.createIcons();
            }
        }

        function closeDeleteModal() {
            const modal = document.getElementById('confirmDeleteModal');
            const modalBody = document.getElementById('confirmDeleteModalBody');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalBody.classList.remove('scale-100');
            modalBody.classList.add('scale-95');
        }

        document.getElementById('confirmDeleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Collapsible dropdown
        function toggleMasterDropdown() {
            const submenu = document.getElementById('masterSubmenu');
            const arrow = document.getElementById('masterDropdownArrow');
            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                arrow.innerText = '▲';
            } else {
                submenu.classList.add('hidden');
                arrow.innerText = '▼';
            }
        }

        function toggleTechDropdown() {
            const submenu = document.getElementById('techSubmenu');
            const arrow = document.getElementById('techDropdownArrow');
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

        // Profile dropdown handler
        function toggleProfileDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Sidebar Mobile Controllers
        function openSidebar() {
            const sidebar = document.getElementById('sidebar-left');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            setTimeout(() => {
                overlay.classList.add('opacity-100');
            }, 10);
        }

        function closeSidebar() {
            const sidebar = document.getElementById('sidebar-left');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.add('-translate-x-full');
            overlay.classList.remove('opacity-100');
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 300);
        }

        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('notifDropdown');
            if (dropdown && !dropdown.classList.contains('hidden') && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }

            const profileDropdown = document.getElementById('profileDropdown');
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
            
            // Re-render Lucide icons after htmx swaps content
            document.body.addEventListener('htmx:afterSwap', function(evt) {
                if (window.lucide) {
                    lucide.createIcons();
                }
            });
            
            // HTMX top loading bar animation
            document.body.addEventListener('htmx:configRequest', function() {
                const bar = document.getElementById('top-loading-bar');
                if (bar) {
                    bar.style.opacity = '1';
                    bar.style.width = '30%';
                    setTimeout(() => {
                        if (bar.style.width === '30%') {
                            bar.style.width = '75%';
                        }
                    }, 150);
                }
            });
            
            document.body.addEventListener('htmx:afterRequest', function() {
                const bar = document.getElementById('top-loading-bar');
                if (bar) {
                    bar.style.width = '100%';
                    setTimeout(() => {
                        bar.style.opacity = '0';
                        setTimeout(() => {
                            bar.style.width = '0';
                        }, 250);
                    }, 100);
                }
            });

            // Helper to check if an element click/submit will be handled by HTMX
            function isHtmxRequest(el) {
                if (!el) return false;
                if (el.hasAttribute('hx-get') || el.hasAttribute('hx-post') || el.hasAttribute('hx-put') || el.hasAttribute('hx-delete') || el.hasAttribute('hx-patch')) {
                    return true;
                }
                let current = el;
                while (current && current !== document.body) {
                    if (current.getAttribute('hx-boost') === 'false') {
                        return false;
                    }
                    if (current.getAttribute('hx-boost') === 'true') {
                        return true;
                    }
                    current = current.parentElement;
                }
                return false;
            }

            // Override HTMLFormElement.prototype.submit to show progress bar on JS-triggered submits
            const originalSubmit = HTMLFormElement.prototype.submit;
            HTMLFormElement.prototype.submit = function() {
                if (!isHtmxRequest(this)) {
                    const bar = document.getElementById('top-loading-bar');
                    if (bar) {
                        bar.style.opacity = '1';
                        bar.style.width = '60%';
                        setTimeout(() => {
                            if (bar.style.opacity === '1') {
                                bar.style.width = '90%';
                            }
                        }, 500);
                    }
                }
                originalSubmit.apply(this, arguments);
            };

            // Show loading bar on native form submits (e.g. search / filters)
            document.body.addEventListener('submit', function(e) {
                if (!isHtmxRequest(e.target)) {
                    const bar = document.getElementById('top-loading-bar');
                    if (bar) {
                        bar.style.opacity = '1';
                        bar.style.width = '60%';
                        setTimeout(() => {
                            if (bar.style.opacity === '1') {
                                bar.style.width = '90%';
                            }
                        }, 500);
                    }
                }
            });

            // Show loading bar on native link clicks (to handle non-boosted transitions)
            document.body.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (link && !link.target && !link.hasAttribute('download')) {
                    const href = link.getAttribute('href');
                    if (href && !href.startsWith('#') && !href.startsWith('javascript:') && !isHtmxRequest(link)) {
                        const bar = document.getElementById('top-loading-bar');
                        if (bar) {
                            bar.style.opacity = '1';
                            bar.style.width = '50%';
                            setTimeout(() => {
                                if (bar.style.opacity === '1') {
                                    bar.style.width = '85%';
                                }
                            }, 500);
                        }
                    }
                }
            });
            
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
