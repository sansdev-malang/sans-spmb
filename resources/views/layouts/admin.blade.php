@php
    $schoolLogo = \App\Models\Setting::get('school_logo_url', '');
    $schoolFavicon = \App\Models\Setting::get('school_favicon_url', '');
    $schoolName = \App\Models\Setting::get('school_name', 'Sekolah Anak Saleh');

    // Global Academic Year Queries
    $globalPeriods = \App\Models\SpmbPeriod::orderBy('year', 'desc')->get();
    $selectedPeriodId = session('selected_period_id', function() {
        return \App\Models\SpmbPeriod::where('is_active', true)->value('id') 
            ?? \App\Models\SpmbPeriod::value('id');
    });
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel - ' . $schoolName)</title>
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
    
    <!-- Quill Rich Text Editor (Loaded globally for subpages) -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    
    <!-- Local Compiled CSS/JS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Dark mode initialization (runs before page renders) -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    
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
        /* Sidebar Modernization */
        #sidebar-left {
            background: linear-gradient(180deg, #0f172a 0%, #1a2844 100%);
            overflow-x: hidden;
        }
        
        #sidebar-left nav {
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.1) transparent;
        }
        
        /* Subtle custom scrollbar */
        #sidebar-left nav::-webkit-scrollbar {
            width: 4px;
        }
        #sidebar-left nav::-webkit-scrollbar-track {
            background: transparent;
        }
        #sidebar-left nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
        #sidebar-left nav::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        /* Prevent text wrapping during sidebar width transitions (prevents scroll flash) */
        #sidebar-left nav a,
        #sidebar-left nav button,
        #sidebar-left .sidebar-text,
        #sidebar-left .category-section-header,
        #sidebar-left .brand-header {
            white-space: nowrap;
        }
        
        /* Smooth sidebar collapse animation */
        #sidebar-left.sidebar-collapsed {
            width: 80px;
        }
        #sidebar-left.sidebar-collapsed .sidebar-text,
        #sidebar-left.sidebar-collapsed .sidebar-label,
        #sidebar-left.sidebar-collapsed .category-section-header,
        #sidebar-left.sidebar-collapsed #spmbSubmenu,
        #sidebar-left.sidebar-collapsed #techSubmenu {
            display: none !important;
        }
        #sidebar-left.sidebar-collapsed .brand-header {
            padding: 0 !important;
            justify-content: center !important;
        }
        #sidebar-left.sidebar-collapsed .sidebar-collapse-btn {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 8px 0;
            margin: 0;
        }
        #sidebar-left.sidebar-collapsed nav a,
        #sidebar-left.sidebar-collapsed nav button {
            justify-content: center !important;
            align-items: center !important;
            padding: 10px 0 !important;
            margin-left: 8px !important;
            margin-right: 8px !important;
            width: calc(100% - 16px) !important;
            gap: 0 !important;
        }
        #sidebar-left.sidebar-collapsed nav a > i,
        #sidebar-left.sidebar-collapsed nav button > span > i,
        #sidebar-left.sidebar-collapsed nav button > i {
            margin: 0 auto !important;
            flex-shrink: 0;
        }
        #sidebar-left.sidebar-collapsed #profileDropdownButton {
            justify-content: center;
            padding: 6px 0;
        }
        
        /* Brand header improved */
        #sidebar-left .brand-header {
            background: linear-gradient(135deg, #0f172a 0%, #10b981/10 100%);
        }
        
        /* Navigation menu item hover/active with gradient */
        nav a, nav button {
            position: relative;
            overflow: hidden;
        }
        
        /* Active menu item with subtle glow */
        nav a.active, 
        nav button.active {
            background: linear-gradient(135deg, #0f5132 0%, #10b981/20 100%);
            box-shadow: 0 0 20px rgba(15, 81, 50, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }
        
        /* Operasional category - Green accent */
        .menu-category-operasional {
            --accent-color: #10b981;
        }
        .menu-category-operasional a:hover {
            background: rgba(16, 185, 129, 0.1);
            border-left: 3px solid #10b981;
            padding-left: calc(12px - 3px);
        }
        
        /* Riwayat category - Blue accent */
        .menu-category-riwayat {
            --accent-color: #3b82f6;
        }
        .menu-category-riwayat a:hover {
            background: rgba(59, 130, 246, 0.1);
            border-left: 3px solid #3b82f6;
            padding-left: calc(12px - 3px);
        }
        
        /* Konfigurasi category - Purple accent */
        .menu-category-konfigurasi {
            --accent-color: #a855f7;
        }
        .menu-category-konfigurasi button:hover,
        .menu-category-konfigurasi a:hover {
            background: rgba(168, 85, 247, 0.1);
            border-left: 3px solid #a855f7;
            padding-left: calc(12px - 3px);
        }
        
        /* Category label with accent color */
        .menu-category-label {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .menu-category-label::before {
            content: '';
            display: inline-block;
            width: 2px;
            height: 12px;
            background: var(--accent-color);
            border-radius: 1px;
        }
        
        /* Submenu smooth animation */
        #spmbSubmenu, #techSubmenu {
            max-height: 1000px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 1;
        }
        #spmbSubmenu.hidden, #techSubmenu.hidden {
            max-height: 0;
            opacity: 0;
            pointer-events: none;
            margin-top: 0;
        }
        
        /* Dropdown arrow animation */
        #spmbDropdownArrow, #techDropdownArrow {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-block;
        }
        
        /* Submenu items with smooth hover */
        #spmbSubmenu a, #techSubmenu a {
            transition: all 0.2s ease;
            border-left: 2px solid transparent;
            padding-left: calc(12px - 2px);
        }
        #spmbSubmenu a:hover, #techSubmenu a:hover {
            background: rgba(16, 185, 129, 0.08);
            border-left-color: #10b981;
            transform: translateX(4px);
        }
        
        /* Category section header styling */
        .category-section-header {
            position: relative;
            padding-left: 12px;
        }
        .category-section-header::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 12px;
            background: var(--accent-color, #10b981);
            border-radius: 2px;
        }
        
        /* Better spacing for nav items */
        nav a, nav button {
            margin: 0 4px;
            padding: 10px 12px;
            font-size: 13px;
            letter-spacing: 0.3px;
        }
        
        /* Icon styling */
        nav i[data-lucide] {
            transition: all 0.3s ease;
            flex-shrink: 0;
        }
        nav a:hover i[data-lucide],
        nav button:hover i[data-lucide] {
            transform: translateX(2px);
        }
        nav a.active i[data-lucide] {
            color: #10b981;
            filter: drop-shadow(0 0 4px rgba(16, 185, 129, 0.5));
        }
        
        /* Collapse button styling */
        .sidebar-collapse-btn {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #10b981;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 12px;
        }
        .sidebar-collapse-btn:hover {
            background: rgba(16, 185, 129, 0.2);
            border-color: rgba(16, 185, 129, 0.4);
            box-shadow: 0 0 12px rgba(16, 185, 129, 0.2);
        }
        
    </style>
</head>
<body class="min-h-screen flex text-slate-800 bg-slate-50 dark:bg-slate-950 dark:text-slate-200 font-sans" hx-boost="true" hx-target="#admin-layout-wrapper" hx-select="#admin-layout-wrapper">
    <!-- YouTube-style dynamic top progress loading bar -->
    <div id="top-loading-bar"></div>

    <div id="admin-layout-wrapper" class="flex w-full min-h-screen">
        <!-- Sidebar Left Layout -->
    <aside id="sidebar-left" class="w-64 bg-admin-dark text-slate-300 flex flex-col fixed inset-y-0 left-0 z-40 border-r border-slate-800 transition-all duration-300 transform -translate-x-full lg:translate-x-0">
        
        <!-- Brand Logo / Info -->
        <div class="brand-header h-16 flex items-center gap-3 px-6 border-b border-slate-800">
            @if(!empty($schoolLogo))
                <div class="h-8 w-8 rounded-lg bg-white flex items-center justify-center p-1 shadow overflow-hidden shrink-0 select-none">
                    <img src="{{ $schoolLogo }}" alt="{{ $schoolName }}" class="max-h-full max-w-full object-contain">
                </div>
            @else
                <div class="h-8 w-8 bg-brand-yellow rounded-lg flex items-center justify-center shadow shrink-0 select-none">
                   <span class="flex items-center justify-center text-lg leading-none font-bold text-black" style="font-family: 'Nasalization Rg', sans-serif; font-weight: 700; color: #000000; line-height: 1; transform: translateY(-0.5px);">S</span>
                </div>
            @endif
            <div class="min-w-0 sidebar-text">
                <span class="font-extrabold text-sm tracking-wider block text-white leading-none truncate max-w-[150px]" title="{{ $schoolName }}">{{ $schoolName }}</span>
                <span class="text-[9px] text-brand-yellow tracking-widest font-semibold uppercase block mt-1">Admin Panel</span>
            </div>
        </div>

        <!-- Mobile Academic Year Selector (Only visible on mobile sidebar) -->
        <div class="px-6 py-3.5 border-b border-slate-800 lg:hidden bg-slate-950/20">
            <form action="{{ route('admin.change-period') }}" method="POST" id="globalPeriodFormMobile">
                @csrf
                <label for="global_period_selector_mobile" class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Tahun Ajaran</label>
                <select name="selected_period_id" id="global_period_selector_mobile" onchange="document.getElementById('globalPeriodFormMobile').submit()" class="w-full bg-slate-900 border border-slate-800 text-[11px] font-bold text-slate-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                    @foreach($globalPeriods as $p)
                        <option value="{{ $p->id }}" {{ $p->id == $selectedPeriodId ? 'selected' : '' }}>
                            TA {{ $p->year }} {{ $p->is_active ? '● (Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
 
        <!-- Navigation Menus -->
        <nav class="flex-grow py-6 px-2 space-y-0.5 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" 
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold tracking-wide transition {{ Route::is('admin.dashboard') ? 'active' : '' }}
                {{ Route::is('admin.dashboard') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }} mb-4">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> <span class="sidebar-text">Dashboard</span>
            </a>
 
            <!-- 1. Operasional Category -->
            <div class="menu-category-operasional">
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest px-3 block mb-2 category-section-header">Operasional</span>
                
                <a href="{{ route('admin.verification') }}" 
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold tracking-wide transition 
                    {{ Route::is('admin.verification') ? 'active' : '' }}
                    {{ Route::is('admin.verification') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                    <i data-lucide="check-square" class="w-4 h-4"></i> <span class="sidebar-text">Verifikasi Data</span>
                </a>
                
                <a href="{{ route('admin.candidates') }}" 
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold tracking-wide transition 
                    {{ Route::is('admin.candidates') ? 'active' : '' }}
                    {{ Route::is('admin.candidates') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                    <i data-lucide="users" class="w-4 h-4"></i> <span class="sidebar-text">Data Pendaftar</span>
                </a>
                
                <a href="{{ route('admin.payments.data') }}" 
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold tracking-wide transition 
                    {{ Route::is('admin.payments.data') ? 'active' : '' }}
                    {{ Route::is('admin.payments.data') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                    <i data-lucide="wallet" class="w-4 h-4"></i> <span class="sidebar-text">Data Pembayaran</span>
                </a>
            </div>

            <!-- 2. Riwayat & Log Category -->
            <div class="menu-category-riwayat mt-4">
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest px-3 block mb-2 category-section-header" style="--accent-color: #3b82f6;">Riwayat & Log</span>
                
                <a href="{{ route('admin.history') }}" 
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold tracking-wide transition 
                    {{ Route::is('admin.history') ? 'active' : '' }}
                    {{ Route::is('admin.history') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                    <i data-lucide="history" class="w-4 h-4"></i> <span class="sidebar-text">Log Pendaftaran</span>
                </a>
                
                <a href="{{ route('admin.payments') }}" 
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold tracking-wide transition 
                    {{ Route::is('admin.payments') ? 'active' : '' }}
                    {{ Route::is('admin.payments') ? 'bg-brand-emerald text-white shadow' : 'hover:bg-slate-800/50 hover:text-white' }}">
                    <i data-lucide="receipt" class="w-4 h-4"></i> <span class="sidebar-text">Log Pembayaran</span>
                </a>
            </div>

            <!-- 3. Pengaturan SPMB (Dropdown) -->
            @php
                $isSpmbActive = Request::is('admin/spmb-settings/registration*') || Request::is('admin/spmb-settings') || Request::is('admin/spmb-settings/units-grades*') || Request::is('admin/spmb-settings/fees*') || Request::is('admin/spmb-settings/form*') || Request::is('admin/spmb-settings/instructions*') || Request::is('admin/spmb-settings/agreements*') || Request::is('admin/spmb-settings/qrcode*') || Request::is('admin/spmb-settings/customer-service*') || Request::is('admin/spmb-settings/brochures*');
            @endphp
            <div class="menu-category-konfigurasi mt-4 space-y-1">
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest px-3 block mb-2 category-section-header" style="--accent-color: #a855f7;">Konfigurasi</span>
                <button type="button" onclick="toggleSpmbDropdown()" 
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition duration-200 group {{ $isSpmbActive ? 'active bg-slate-800 text-white shadow-sm font-bold' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
                    <span class="flex items-center gap-3">
                        <i data-lucide="graduation-cap" class="w-4 h-4 text-slate-400 group-hover:text-white"></i>
                        <span class="sidebar-text">Pengaturan SPMB</span>
                    </span>
                    <i data-lucide="chevron-down" id="spmbDropdownArrow" class="w-4 h-4 text-slate-400 group-hover:text-white sidebar-text transition-transform duration-300 {{ $isSpmbActive ? 'rotate-180 text-brand-yellow' : '' }}"></i>
                </button>
                <div id="spmbSubmenu" class="ml-4 pl-3.5 border-l border-slate-800/80 space-y-0.5 my-1.5 {{ $isSpmbActive ? '' : 'hidden' }}">
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.spmb-settings.registration') }}" class="group flex items-center gap-2.5 py-2 px-2.5 rounded-lg text-xs font-medium transition {{ Route::is('admin.spmb-settings.registration') ? 'text-brand-yellow font-bold bg-slate-800/60 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                            <i data-lucide="toggle-left" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300"></i> <span class="sidebar-text">Aktivasi SPMB</span>
                        </a>
                        <a href="{{ route('admin.spmb-settings.units-grades') }}" class="group flex items-center gap-2.5 py-2 px-2.5 rounded-lg text-xs font-medium transition {{ Route::is('admin.spmb-settings.units-grades') ? 'text-brand-yellow font-bold bg-slate-800/60 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                            <i data-lucide="building-2" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300"></i> <span class="sidebar-text">Struktur Sekolah</span>
                        </a>
                        <a href="{{ route('admin.spmb-settings') }}" class="group flex items-center gap-2.5 py-2 px-2.5 rounded-lg text-xs font-medium transition {{ Route::is('admin.spmb-settings') ? 'text-brand-yellow font-bold bg-slate-800/60 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                            <i data-lucide="git-branch" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300"></i> <span class="sidebar-text">Jalur & Gelombang</span>
                        </a>
                    @endif
                    <a href="{{ route('admin.spmb-settings.fees') }}" class="group flex items-center gap-2.5 py-2 px-2.5 rounded-lg text-xs font-medium transition {{ Route::is('admin.spmb-settings.fees') ? 'text-brand-yellow font-bold bg-slate-800/60 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                        <i data-lucide="coins" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300"></i> <span class="sidebar-text">Tarif & Biaya</span>
                    </a>
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.spmb-settings.form') }}" class="group flex items-center gap-2.5 py-2 px-2.5 rounded-lg text-xs font-medium transition {{ Route::is('admin.spmb-settings.form') ? 'text-brand-yellow font-bold bg-slate-800/60 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                            <i data-lucide="settings-2" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300"></i> <span class="sidebar-text">Setting Formulir</span>
                        </a>
                    @endif
                    <a href="{{ route('admin.spmb-settings.instructions') }}" class="group flex items-center gap-2.5 py-2 px-2.5 rounded-lg text-xs font-medium transition {{ Route::is('admin.spmb-settings.instructions') ? 'text-brand-yellow font-bold bg-slate-800/60 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                            <i data-lucide="scroll-text" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300"></i> <span class="sidebar-text">Instruksi Daftar</span>
                    </a>
                    <a href="{{ route('admin.spmb-settings.agreements') }}" class="group flex items-center gap-2.5 py-2 px-2.5 rounded-lg text-xs font-medium transition {{ Route::is('admin.spmb-settings.agreements') ? 'text-brand-yellow font-bold bg-slate-800/60 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                        <i data-lucide="file-signature" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300"></i> <span class="sidebar-text">Surat Pernyataan</span>
                    </a>
                    <a href="{{ route('admin.spmb-settings.qrcode') }}" class="group flex items-center gap-2.5 py-2 px-2.5 rounded-lg text-xs font-medium transition {{ Route::is('admin.spmb-settings.qrcode') ? 'text-brand-yellow font-bold bg-slate-800/60 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                        <i data-lucide="qr-code" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300"></i> <span class="sidebar-text">QR Code SPMB</span>
                    </a>
                    <a href="{{ route('admin.spmb-settings.cs') }}" class="group flex items-center gap-2.5 py-2 px-2.5 rounded-lg text-xs font-medium transition {{ Route::is('admin.spmb-settings.cs') ? 'text-brand-yellow font-bold bg-slate-800/60 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                        <i data-lucide="headphones" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300"></i> <span class="sidebar-text">Customer Service</span>
                    </a>
                    <a href="{{ route('admin.spmb-settings.brochures') }}" class="group flex items-center gap-2.5 py-2 px-2.5 rounded-lg text-xs font-medium transition {{ Route::is('admin.spmb-settings.brochures') ? 'text-brand-yellow font-bold bg-slate-800/60 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                        <i data-lucide="book-open" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300"></i> <span class="sidebar-text">Brosur & Dokumen</span>
                    </a>
                </div>
            </div>

            <!-- 4. Pengaturan Teknis (Dropdown) -->
            @if(auth()->user()->isSuperAdmin())
                @php
                    $isTechActive = Request::is('admin/api-integrations*') || Request::is('admin/payment-gateways*') || Request::is('admin/payment-channels*') || Request::is('admin/activity-logs*') || Request::is('admin/logs*') || Request::is('admin/ui-settings*') || Request::is('admin/users*') || Request::is('admin/settings*');
                @endphp
                <div class="menu-category-konfigurasi mt-4 space-y-1">
                    <button type="button" onclick="toggleTechDropdown()" 
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold tracking-wide transition duration-200 group {{ $isTechActive ? 'active bg-slate-800 text-white shadow-sm font-bold' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white' }}">
                        <span class="flex items-center gap-3">
                            <i data-lucide="settings" class="w-4 h-4 text-slate-400 group-hover:text-white"></i>
                            <span class="sidebar-text">Pengaturan Teknis</span>
                        </span>
                        <i data-lucide="chevron-down" id="techDropdownArrow" class="w-4 h-4 text-slate-400 group-hover:text-white sidebar-text transition-transform duration-300 {{ $isTechActive ? 'rotate-180 text-brand-yellow' : '' }}"></i>
                    </button>
                    <div id="techSubmenu" class="ml-4 pl-3.5 border-l border-slate-800/80 space-y-0.5 my-1.5 {{ $isTechActive ? '' : 'hidden' }}">
                        <a href="{{ route('admin.ui-settings') }}" class="group flex items-center gap-2.5 py-2 px-2.5 rounded-lg text-xs font-medium transition {{ Route::is('admin.ui-settings') ? 'text-brand-yellow font-bold bg-slate-800/60 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                            <i data-lucide="palette" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300"></i> <span class="sidebar-text">Tampilan Portal</span>
                        </a>
                        <a href="{{ route('admin.users') }}" class="group flex items-center gap-2.5 py-2 px-2.5 rounded-lg text-xs font-medium transition {{ Route::is('admin.users') ? 'text-brand-yellow font-bold bg-slate-800/60 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                            <i data-lucide="users-round" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300"></i> <span class="sidebar-text">Manajemen User</span>
                        </a>
                        <a href="{{ route('admin.api-integrations') }}" class="group flex items-center gap-2.5 py-2 px-2.5 rounded-lg text-xs font-medium transition {{ Route::is('admin.api-integrations') ? 'text-brand-yellow font-bold bg-slate-800/60 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                            <i data-lucide="blocks" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300"></i> <span class="sidebar-text">Integrasi API</span>
                        </a>
                        <a href="{{ route('admin.payment-gateways.index') }}" class="group flex items-center gap-2.5 py-2 px-2.5 rounded-lg text-xs font-medium transition {{ Route::is('admin.payment-gateways.index') ? 'text-brand-yellow font-bold bg-slate-800/60 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                            <i data-lucide="credit-card" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300"></i> <span class="sidebar-text">CRUD Gateway</span>
                        </a>
                        <a href="{{ route('admin.payment-channels.index') }}" class="group flex items-center gap-2.5 py-2 px-2.5 rounded-lg text-xs font-medium transition {{ Route::is('admin.payment-channels.index') ? 'text-brand-yellow font-bold bg-slate-800/60 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                            <i data-lucide="shuffle" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300"></i> <span class="sidebar-text">CRUD Channel</span>
                        </a>
                        @foreach($sidebarGateways as $sgw)
                            <a href="{{ route('admin.payment-gateways.settings', $sgw->code) }}" class="group flex items-center gap-2.5 py-2 px-2.5 rounded-lg text-xs font-medium transition {{ Request::is('admin/payment-gateways/' . $sgw->code . '/settings') ? 'text-brand-yellow font-bold bg-slate-800/60 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                                <i data-lucide="settings-2" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300"></i> <span class="sidebar-text">Set {{ $sgw->name }}</span>
                            </a>
                        @endforeach
                        <a href="{{ route('admin.settings') }}" class="group flex items-center gap-2.5 py-2 px-2.5 rounded-lg text-xs font-medium transition {{ Route::is('admin.settings') ? 'text-brand-yellow font-bold bg-slate-800/60 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                            <i data-lucide="percent" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300"></i> <span class="sidebar-text">Biaya Admin</span>
                        </a>
                        <a href="{{ route('admin.activity-logs') }}" class="group flex items-center gap-2.5 py-2 px-2.5 rounded-lg text-xs font-medium transition {{ Route::is('admin.activity-logs') ? 'text-brand-yellow font-bold bg-slate-800/60 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                            <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300"></i> <span class="sidebar-text">Log Aktivitas</span>
                        </a>
                        <a href="{{ route('admin.logs') }}" class="group flex items-center gap-2.5 py-2 px-2.5 rounded-lg text-xs font-medium transition {{ Route::is('admin.logs') ? 'text-brand-yellow font-bold bg-slate-800/60 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/30' }}">
                            <i data-lucide="scroll-text" class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300"></i> <span class="sidebar-text">Log Sistem</span>
                        </a>
                    </div>
                </div>
            @endif
        </nav>

        <!-- User profile section bottom -->
        <div class="p-1 border-t border-slate-800 bg-slate-950/40 relative text-xs select-none">
            <!-- Upward Floating Dropdown Menu - placed BEFORE button so it floats above -->
            <div id="profileDropdown" class="hidden absolute bottom-full left-2 right-2 mb-2 bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl py-2 z-[200] animate-fade-in text-xs text-slate-300">
                <div class="px-4 py-2 border-b border-slate-800 text-[10px] text-slate-500 font-bold uppercase tracking-wider">
                    Menu Akun
                </div>
                <div class="divide-y divide-slate-800/50">
                    <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-slate-800 hover:text-white transition rounded-lg mx-1 my-0.5">
                        <i data-lucide="user" class="w-4 h-4 text-slate-400 flex-shrink-0"></i>
                        <span>Profil Saya</span>
                    </a>
                    <a href="{{ route('admin.profile.password') }}" class="flex items-center gap-2.5 px-4 py-2.5 hover:bg-slate-800 hover:text-white transition rounded-lg mx-1 my-0.5">
                        <i data-lucide="key-round" class="w-4 h-4 text-slate-400 flex-shrink-0"></i>
                        <span>Ganti Password</span>
                    </a>
                    <div>
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-red-400 hover:bg-red-950/30 hover:text-red-300 transition font-bold text-left rounded-lg mx-1 my-0.5 mt-1.5" style="width: calc(100% - 8px);">
                                <i data-lucide="log-out" class="w-4 h-4 flex-shrink-0"></i>
                                <span>Logout / Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Profile Capsule Button -->
            <button id="profileDropdownButton" type="button" onclick="toggleProfileDropdown(event)" class="w-full flex items-center justify-between hover:bg-slate-800/30 p-1.5 rounded-xl transition duration-200 text-left">
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 flex-shrink-0 rounded-full bg-brand-emerald text-brand-yellow font-black flex items-center justify-center border border-emerald-800 shadow-inner uppercase">
                        {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="overflow-hidden sidebar-text">
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
                <i data-lucide="chevrons-up-down" class="w-4 h-4 text-slate-500 sidebar-text flex-shrink-0"></i>
            </button>
        </div>
    </aside>

    <!-- Sidebar Mobile Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/60 z-40 hidden transition-opacity duration-300 opacity-0 lg:hidden" onclick="closeSidebar()"></div>

    <!-- Right Content Area -->
    <div id="main-content-wrapper" class="flex-grow pl-0 lg:pl-64 flex flex-col min-h-screen transition-all duration-300">
        
        <!-- Header Panel -->
        <header class="h-16 bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between px-4 lg:px-8 sticky top-0 z-30 shadow-sm transition">
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Hamburger menu button visible only on mobile/tablet -->
                <button type="button" onclick="openSidebar()" class="lg:hidden p-2 text-slate-500 hover:text-brand-emerald rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition" title="Buka Menu Mobile">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <!-- Sidebar Collapse Toggle Button for Desktop -->
                <button id="sidebarToggleBtn" type="button" onclick="toggleSidebarCollapse()" class="hidden lg:flex p-2 text-slate-500 hover:text-brand-emerald rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition items-center justify-center" title="Toggle Sidebar">
                    <i data-lucide="panel-left-close" class="w-5 h-5"></i>
                </button>
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                    <span class="hidden sm:inline">{{ $schoolName }}</span>
                    <span class="hidden sm:inline">/</span>
                    <span class="text-brand-emerald font-bold">@yield('page_title', 'Dashboard')</span>
                </div>
            </div>
            
            <div class="flex items-center gap-1 text-xs font-medium">
                <!-- Global Academic Year Selector Form -->
                <form action="{{ route('admin.change-period') }}" method="POST" id="globalPeriodForm" class="mr-1 hidden lg:block">
                    @csrf
                    <label for="global_period_selector" class="sr-only">Tahun Ajaran</label>
                    <select name="selected_period_id" id="global_period_selector" onchange="document.getElementById('globalPeriodForm').submit()" class="bg-slate-50 border border-slate-200 dark:bg-slate-800 dark:border-slate-700 dark:text-white rounded-xl py-1.5 text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-emerald cursor-pointer">
                        @foreach($globalPeriods as $p)
                            <option value="{{ $p->id }}" {{ $p->id == $selectedPeriodId ? 'selected' : '' }}>
                                TA {{ $p->year }} {{ $p->is_active ? '● (Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <!-- Toggle Dark/Light Mode Button -->
                <button onclick="toggleDarkMode()" class="p-2 text-slate-500 hover:text-brand-emerald rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition" title="Toggle Tema">
                    <i id="theme-toggle-icon" data-lucide="moon" class="w-4 h-4"></i>
                </button>

                <!-- Notifications Toggles with Badge -->
                <div class="relative">
                    <button id="notifBellButton" type="button" onclick="toggleNotifDropdown(event)" class="p-2 text-slate-500 hover:text-brand-emerald rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition relative" title="Notifikasi">
                        <i data-lucide="bell" class="w-4 h-4"></i>
                        <span id="unread-notifications-badge" class="absolute top-1.5 right-1.5 flex h-2 w-2"></span>
                    </button>
                    <!-- Notifications Dropdown Box -->
                    <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-72 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 py-2 z-50 animate-fade-in text-xs text-slate-700 dark:text-slate-300">
                        <div class="px-4 py-2 border-b border-slate-400 text-xs text-slate-500 font-bold uppercase tracking-wider">
                            Notifikasi
                        </div>
                        <div id="notifDropdownContent" class="max-h-64 overflow-y-auto">
                            <p class="text-center text-xs text-slate-400 py-3">Tidak ada notifikasi baru.</p>
                        </div>
                    </div>
                </div>

                <a href="{{ url('/') }}" target="_blank" class="bg-emerald-50 text-brand-emerald hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400 dark:hover:bg-emerald-900/50 px-2 sm:px-3 py-1.5 rounded-lg font-bold transition flex items-center gap-1.5" title="Buka Portal Pendaftar">
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
        <footer class="bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 py-4 text-center text-[10px] text-slate-400 dark:text-slate-500 transition">
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
                <form id="confirmDeleteForm" method="POST" action="" hx-boost="false" class="inline-block">
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

        // Sidebar State Controller
        function restoreSidebarState() {
            const isCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
            const sidebar = document.getElementById('sidebar-left');
            const mainWrapper = document.getElementById('main-content-wrapper');
            const btn = document.getElementById('sidebarToggleBtn');

            if (sidebar) {
                if (isCollapsed) {
                    sidebar.classList.add('sidebar-collapsed');
                } else {
                    sidebar.classList.remove('sidebar-collapsed');
                }
            }

            if (mainWrapper) {
                if (isCollapsed) {
                    mainWrapper.classList.remove('lg:pl-64');
                    mainWrapper.classList.add('lg:pl-20');
                } else {
                    mainWrapper.classList.remove('lg:pl-20');
                    mainWrapper.classList.add('lg:pl-64');
                }
            }

            if (btn) {
                const iconName = isCollapsed ? 'panel-left-open' : 'panel-left-close';
                btn.innerHTML = `<i data-lucide="${iconName}" class="w-5 h-5"></i>`;
                btn.setAttribute('title', isCollapsed ? 'Buka Sidebar' : 'Tutup Sidebar');
            }

            if (window.lucide) {
                lucide.createIcons();
            }
        }

        // Sidebar collapse toggle functionality
        function toggleSidebarCollapse() {
            const isCurrentlyCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
            localStorage.setItem('sidebar-collapsed', !isCurrentlyCollapsed);
            restoreSidebarState();
        }
        
        // Initial execution on script load
        restoreSidebarState();

        // Submenu SPMB & Tech Dropdown Handlers
        function toggleSpmbDropdown() {
            const sidebar = document.getElementById('sidebar-left');
            if (sidebar && sidebar.classList.contains('sidebar-collapsed')) {
                toggleSidebarCollapse();
                const submenu = document.getElementById('spmbSubmenu');
                const arrow = document.getElementById('spmbDropdownArrow');
                if (submenu) submenu.classList.remove('hidden');
                if (arrow) {
                    arrow.classList.add('rotate-180', 'text-white');
                }
                return;
            }
            const submenu = document.getElementById('spmbSubmenu');
            const arrow = document.getElementById('spmbDropdownArrow');
            if (!submenu) return;
            submenu.classList.toggle('hidden');
            if (arrow) {
                arrow.classList.toggle('rotate-180');
                arrow.classList.toggle('text-white');
            }
        }

        function toggleTechDropdown() {
            const sidebar = document.getElementById('sidebar-left');
            if (sidebar && sidebar.classList.contains('sidebar-collapsed')) {
                toggleSidebarCollapse();
                const submenu = document.getElementById('techSubmenu');
                const arrow = document.getElementById('techDropdownArrow');
                if (submenu) submenu.classList.remove('hidden');
                if (arrow) {
                    arrow.classList.add('rotate-180', 'text-white');
                }
                return;
            }
            const submenu = document.getElementById('techSubmenu');
            const arrow = document.getElementById('techDropdownArrow');
            if (!submenu) return;
            submenu.classList.toggle('hidden');
            if (arrow) {
                arrow.classList.toggle('rotate-180');
                arrow.classList.toggle('text-white');
            }
        }
        

        // Notification dropdown handler
        let isFetchingNotif = false;
        function toggleNotifDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('notifDropdown');
            if (dropdown) {
                const isHidden = dropdown.classList.contains('hidden');
                
                // Close profile dropdown first
                const profileDropdown = document.getElementById('profileDropdown');
                if (profileDropdown) {
                    profileDropdown.classList.add('hidden');
                }
                
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
            
            // Show a simple text loader ONLY if the dropdown is completely empty
            if (!silent && (!dropdown.innerHTML || dropdown.innerHTML.trim() === '')) {
                dropdown.innerHTML = `
                    <div class="px-4 py-8 text-center text-slate-400">
                        <p class="text-[10px]">Memuat...</p>
                    </div>
                `;
            }

            fetch('/admin/notifications/dropdown')
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
            fetch('/admin/notifications/unread-count')
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
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('/admin/notifications/mark-all-read', {
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
            const bellButton = document.getElementById('notifBellButton');
            if (dropdown && !dropdown.classList.contains('hidden')) {
                if (!dropdown.contains(e.target) && (!bellButton || !bellButton.contains(e.target))) {
                    dropdown.classList.add('hidden');
                    fetchNotifications(true); // Fetch silently in background after closing!
                }
            }

            const profileDropdown = document.getElementById('profileDropdown');
            const profileButton = document.getElementById('profileDropdownButton');
            if (profileDropdown && !profileDropdown.classList.contains('hidden')) {
                if (!profileDropdown.contains(e.target) && (!profileButton || !profileButton.contains(e.target))) {
                    profileDropdown.classList.add('hidden');
                }
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
            updateThemeIcon();
            
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
            
            // Listen to HTMX page swaps to re-initialize notifications count and content silently
            document.addEventListener('htmx:afterSwap', () => {
                fetchNotificationCount();
                fetchNotifications(true);
            });
        })();

        // Toast Engine function
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            if (!container) return;
            
            const toast = document.createElement('div');
            toast.className = `flex items-center gap-3 bg-white dark:bg-slate-900 text-xs font-bold px-4 py-3 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-800 transform translate-y-2 opacity-0 transition-all duration-300 pointer-events-auto max-w-sm`;
            
            let iconColor = type === 'success' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400';
            let iconName = type === 'success' ? 'check-circle' : 'alert-circle';
            
            toast.innerHTML = `
                <i data-lucide="${iconName}" class="w-4 h-4 ${iconColor} flex-shrink-0"></i>
                <span class="text-slate-700 dark:text-slate-200 flex-grow">${message}</span>
                <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-0.5">&times;</button>
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
            }, 2000);
        }

        // Coming Soon Feature Notifier
        function showFeatureComingSoon(featureName = 'Fitur') {
            const container = document.getElementById('toastContainer');
            if (!container) return;
            
            const toast = document.createElement('div');
            toast.className = `flex items-center gap-3 bg-white dark:bg-slate-900 text-xs font-bold px-4 py-3 rounded-2xl shadow-2xl border border-amber-200 dark:border-amber-900/60 transform translate-y-2 opacity-0 transition-all duration-300 pointer-events-auto max-w-sm`;
            
            toast.innerHTML = `
                <div class="h-8 w-8 rounded-xl bg-amber-100 dark:bg-amber-950/70 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="sparkles" class="w-4 h-4 text-amber-500"></i>
                </div>
                <div class="flex-grow space-y-0.5 pr-1">
                    <div class="flex items-center gap-1.5">
                        <span class="text-amber-900 dark:text-amber-300 font-extrabold text-xs">${featureName}</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-amber-200 dark:bg-amber-900/60 text-amber-800 dark:text-amber-200">Soon</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium leading-tight">Sedang dalam tahap finalisasi pengembangan & integrasi.</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 text-base leading-none">&times;</button>
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
            }, 2000);
        }
        
        // Initialize Lucide Icons & Auto Session Toasts
        document.addEventListener("DOMContentLoaded", function() {
            lucide.createIcons();
            updateThemeIcon();
            
            // Re-render Lucide icons & maintain sidebar state after htmx swaps content
            document.body.addEventListener('htmx:afterSwap', function(evt) {
                if (window.lucide) {
                    lucide.createIcons();
                }
                restoreSidebarState();
                if (window.innerWidth < 1024) {
                    closeSidebar();
                }
            });
            
            // HTMX top loading bar animation
            document.body.addEventListener('htmx:configRequest', function(evt) {
                const path = evt.detail.path || '';
                if (path.includes('/notifications')) {
                    return;
                }
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
            
            document.body.addEventListener('htmx:afterRequest', function(evt) {
                const path = evt.detail.path || '';
                if (path.includes('/notifications')) {
                    return;
                }
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
