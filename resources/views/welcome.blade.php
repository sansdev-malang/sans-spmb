@extends('layouts.portal')

@section('title', 'Sekolah Anak Saleh - Penerimaan Siswa Baru')

@section('content')
@php
    $activePeriodYear = \App\Models\SpmbPeriod::where('is_active', true)->value('year') ?? '2026/2027';
    $schoolName = \App\Models\Setting::get('school_name', 'Sekolah Anak Saleh');
    $schoolLogo = \App\Models\Setting::get('school_logo_url', '');
    $heroTitle = \App\Models\Setting::get('portal_hero_title', 'Membangun Generasi Cerdas, Sholeh, dan Berakhlak Mulia.');
    $heroDesc = \App\Models\Setting::get('portal_hero_description', 'Bergabunglah bersama Sekolah Anak Saleh. Kami menyajikan kurikulum yang mengintegrasikan nilai-nilai Islam dengan pendidikan modern untuk menyiapkan pemimpin masa depan.');
    $heroImages = json_decode(\App\Models\Setting::get('school_hero_images', '[]'), true) ?: [];
    $activeUnits = \App\Models\SpmbUnit::where('is_active', true)->get();
@endphp

{{-- Hero Animation Styles --}}
<style>
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(24px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.96) translateY(8px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }
    .hero-text-animate  { animation: fadeUp  0.7s ease-out 0.1s both; }
    .hero-image-animate { animation: scaleIn 0.8s ease-out 0.25s both; }
    .floating-card-1    { animation: fadeUp  0.6s ease-out 0.55s both; }
    .floating-card-2    { animation: fadeUp  0.6s ease-out 0.75s both; }
</style>

<!-- Hero Section -->
<div class="relative bg-slate-50 dark:bg-slate-950 overflow-hidden">

    {{-- Organic Background Elements --}}
    <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
        {{-- Soft green blob left --}}
        <div class="absolute -top-32 -left-32 w-[520px] h-[520px] bg-emerald-300/10 dark:bg-emerald-400/5 rounded-full blur-3xl"></div>
        {{-- Yellow accent right --}}
        <div class="absolute top-12 right-0 translate-x-1/4 w-96 h-96 bg-amber-300/10 dark:bg-amber-300/5 rounded-full blur-3xl"></div>
        {{-- Bottom subtle green --}}
        <div class="absolute bottom-0 left-1/3 w-72 h-56 bg-emerald-200/10 dark:bg-emerald-700/5 rounded-full blur-2xl"></div>
        {{-- Decorative dot pattern --}}
        <svg class="absolute top-10 right-24 w-40 h-40 opacity-[0.18] dark:opacity-[0.08]" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="dot-pattern" x="0" y="0" width="12" height="12" patternUnits="userSpaceOnUse">
                    <circle cx="2" cy="2" r="1.5" fill="#10b981"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#dot-pattern)"/>
        </svg>
        {{-- Small yellow accent curve --}}
        <div class="absolute bottom-12 right-8 w-24 h-24 bg-amber-400/15 dark:bg-amber-400/8 rounded-full blur-xl"></div>
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center pt-28 pb-20 md:pt-36 md:pb-28">

        {{-- Hero Text (Left 7 Columns) --}}
        <div class="lg:col-span-7 space-y-7 hero-text-animate">

            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 font-bold text-[11px] px-4 py-2 rounded-full border border-emerald-100 dark:border-emerald-800/60 shadow-sm">
                <span class="relative flex h-2 w-2 flex-shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                </span>
                <span class="text-amber-500 font-black text-xs">✦</span>
                <span class="uppercase tracking-wider">PENERIMAAN SISWA BARU {{ $activePeriodYear }}</span>
                <span class="h-3.5 w-px bg-emerald-200 dark:bg-emerald-700 flex-shrink-0"></span>
                <span class="text-emerald-600 dark:text-emerald-500 font-extrabold">Pendaftaran Dibuka</span>
            </div>

            {{-- Headline Editorial --}}
            <h1 class="text-4xl md:text-5xl lg:text-[3.25rem] xl:text-[3.75rem] font-black leading-[1.08] tracking-tight text-slate-800 dark:text-slate-100">
                Membangun Generasi<br>
                <span class="text-custom-primary dark:text-emerald-400">Cerdas, Sholeh,</span><br>
                dan Berakhlak Mulia.
            </h1>

            {{-- Description --}}
            <p class="text-slate-500 dark:text-slate-400 text-base leading-relaxed max-w-lg">
                Bergabunglah bersama {{ $schoolName }} dan nikmati pendidikan yang mengintegrasikan nilai-nilai Islam dengan kurikulum modern untuk menyiapkan pemimpin masa depan.
            </p>

            {{-- CTA Buttons --}}
            <div class="flex flex-wrap gap-3 pt-1">
                <a href="#pendaftaran"
                   class="inline-flex items-center gap-2 bg-custom-primary hover:opacity-90 text-white px-7 py-3.5 rounded-xl font-bold text-sm shadow-md transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5">
                    Daftar Sekarang <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
                <a href="#program"
                   class="inline-flex items-center gap-2 border-2 border-custom-primary text-custom-primary dark:text-emerald-400 dark:border-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 px-7 py-3.5 rounded-xl font-bold text-sm transition-all duration-200">
                    Jelajahi Program
                </a>
            </div>
        </div>

        {{-- Hero Image (Right 5 Columns) --}}
        <div class="lg:col-span-5 relative flex justify-center hero-image-animate">
            <div class="relative w-full max-w-md">
                {{-- Decorative orbs behind image --}}
                <div class="absolute -top-8 -left-8 w-48 h-48 bg-amber-300/15 dark:bg-amber-300/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute -bottom-8 -right-8 w-48 h-48 bg-emerald-400/15 dark:bg-emerald-400/10 rounded-full blur-2xl pointer-events-none"></div>

                {{-- Main Image Container --}}
                <div class="relative overflow-hidden rounded-3xl shadow-2xl border border-white/60 dark:border-slate-700 aspect-[4/3] bg-slate-100 dark:bg-slate-800">
                    @if(count($heroImages) > 0)
                        @foreach($heroImages as $index => $img)
                            <img src="{{ $img }}"
                                 alt="Slide {{ $index }}"
                                 class="hero-slide absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 ease-in-out {{ $index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' }}" />
                        @endforeach
                    @else
                        {{-- Fallback default image --}}
                        <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&q=80&w=800"
                             alt="Siswa Sekolah"
                             class="absolute inset-0 w-full h-full object-cover" />
                    @endif
                </div>

                {{-- Floating Card 1 — Terakreditasi (bottom-left) --}}
                <div class="floating-card-1 absolute -bottom-5 -left-5 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm p-3.5 rounded-2xl shadow-xl border border-white dark:border-slate-700/80 flex items-center gap-3 z-20 max-w-[220px]">
                    <div class="h-10 w-10 rounded-xl bg-emerald-50 dark:bg-emerald-950 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="award" class="w-5 h-5 text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div>
                        <p class="font-extrabold text-[11px] text-custom-primary dark:text-emerald-400">✓ Terakreditasi A</p>
                        <p class="text-[9px] text-slate-400 font-semibold mt-0.5">Standar Pendidikan Nasional</p>
                    </div>
                </div>

                {{-- Floating Card 2 — 20+ Tahun (top-right) --}}
                <div class="floating-card-2 absolute -top-5 -right-5 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm px-4 py-3 rounded-2xl shadow-xl border border-white dark:border-slate-700/80 z-20 text-center min-w-[90px]">
                    <p class="font-black text-xl text-custom-primary dark:text-emerald-400 leading-none">20+</p>
                    <p class="text-[9px] text-slate-400 font-semibold mt-0.5 whitespace-nowrap">Tahun Pengalaman</p>
                </div>
            </div>
        </div>

    </div>
</div>


<!-- Program Pendidikan Section (Overview) -->
<div id="program" class="bg-white dark:bg-slate-900 py-8 border-t border-slate-100 dark:border-slate-800 transition">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="text-center max-w-xl mx-auto space-y-3 mb-16 md:mb-20">
            <h2 class="text-3xl font-black text-custom-primary dark:text-emerald-400 tracking-tight">Program Pendidikan Kami</h2>
            <p class="text-xs text-slate-400 dark:text-slate-400 font-semibold leading-relaxed">
                Jenjang pendidikan yang berkesinambungan untuk mengawal tumbuh kembang ananda tercinta di {{ $schoolName }}.
            </p>
        </div>

        <!-- Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($activeUnits as $u)
                @php
                    $uCode = strtolower($u->code);
                    $uDesc = \App\Models\Setting::get('unit_' . $uCode . '_desc', '');
                    $uFeatures = array_filter(explode(',', \App\Models\Setting::get('unit_' . $uCode . '_features', '')));
                    
                    // Assign icon based on unit code
                    $iconName = 'book-open';
                    if ($uCode === 'paud') {
                        $iconName = 'car';
                    } elseif ($uCode === 'smp') {
                        $iconName = 'flask-conical';
                    }
                    
                    // Highlight specifically SD as FAVORIT
                    $isFavorit = ($uCode === 'sd');
                @endphp

                @if($isFavorit)
                    <!-- Highlighted Card -->
                    <div class="bg-[#f2f8f5] dark:bg-emerald-950/20 p-8 rounded-3xl border-2 border-custom-primary dark:border-emerald-600 space-y-6 shadow-xl hover:shadow-2xl transition flex flex-col justify-between relative">
                        <!-- Highlight badge -->
                        <span class="absolute top-4 right-4 bg-brand-yellow text-slate-900 text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded-lg shadow-sm">
                            FAVORIT
                        </span>
                        
                        <div class="space-y-6">
                            <div class="h-12 w-12 bg-custom-primary text-white rounded-2xl flex items-center justify-center shadow-md">
                                <i data-lucide="{{ $iconName }}" class="w-5 h-5 text-brand-yellow"></i>
                            </div>
                            <div class="space-y-2">
                                <h3 class="font-black text-xl text-custom-primary dark:text-emerald-400">{{ $u->name }}</h3>
                                <p class="text-xs text-slate-600 dark:text-slate-350 leading-relaxed font-semibold">
                                    {{ $uDesc }}
                                </p>
                            </div>
                        </div>
                        <div class="space-y-3 pt-4 border-t border-slate-200 dark:border-slate-800 text-[11px] font-extrabold text-custom-primary dark:text-emerald-400">
                            @foreach(array_slice($uFeatures, 0, 2) as $feat)
                                <div class="flex items-center gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600"></i> <span>{{ trim($feat) }}</span>
                                </div>
                            @endforeach
                            <a href="{{ route('unit.detail', $uCode) }}" class="text-[10px] text-brand-emerald dark:text-emerald-400 font-extrabold underline block mt-2 text-left">
                                Lihat Detail Selengkapnya &rarr;
                            </a>
                        </div>
                    </div>
                @else
                    <!-- Standard Card -->
                    <div class="bg-slate-50 dark:bg-slate-950 p-8 rounded-3xl border border-slate-200/60 dark:border-slate-800 space-y-6 hover:shadow-lg transition flex flex-col justify-between">
                        <div class="space-y-6">
                            <div class="h-12 w-12 bg-custom-primary text-white rounded-2xl flex items-center justify-center shadow-md">
                                <i data-lucide="{{ $iconName }}" class="w-5 h-5 text-brand-yellow"></i>
                            </div>
                            <div class="space-y-2">
                                <h3 class="font-black text-xl text-slate-800 dark:text-white">{{ $u->name }}</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-medium">
                                    {{ $uDesc }}
                                </p>
                            </div>
                        </div>
                        <div class="space-y-3 pt-4 border-t border-slate-150 dark:border-slate-800 text-[11px] font-bold text-slate-600 dark:text-slate-450">
                            @foreach(array_slice($uFeatures, 0, 2) as $feat)
                                <div class="flex items-center gap-2">
                                    <i data-lucide="check" class="w-4 h-4 text-emerald-600"></i> <span>{{ trim($feat) }}</span>
                                </div>
                            @endforeach
                            <a href="{{ route('unit.detail', $uCode) }}" class="text-[10px] text-custom-primary dark:text-emerald-400 font-extrabold underline block mt-2 text-left">
                                Lihat Detail Selengkapnya &rarr;
                            </a>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>

<!-- Panca Karakter Section -->
<div id="panca-karakter" class="bg-slate-50 dark:bg-slate-950 py-8 border-t border-slate-100 dark:border-slate-800 transition relative overflow-hidden">
    {{-- Background decorative shapes --}}
    <div class="absolute -top-24 -right-24 w-80 h-80 bg-emerald-400/10 dark:bg-emerald-500/5 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-amber-400/10 dark:bg-amber-500/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
        <!-- Section Header -->
        <div class="text-center max-w-2xl mx-auto space-y-3 mb-16 md:mb-20">
            <h2 class="text-3xl md:text-4xl font-black text-custom-primary dark:text-emerald-400 tracking-tight">Panca Karakter Anak Saleh</h2>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                Lima pilar pembinaan holistik yang diintegrasikan dalam setiap aktivitas belajar mengajar untuk mencetak generasi berprestasi dan beradab.
            </p>
        </div>

        <!-- Cards Grid (5 Kolom Simetris di Desktop, 2 Kolom di Tablet, 1 Kolom di Mobile) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 lg:gap-6">
            <!-- Karakter 1: Sholeh -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/70 dark:border-slate-800 hover:border-amber-600 dark:hover:border-amber-600 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="h-12 w-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                            <i data-lucide="mosque" class="w-6 h-6"></i>
                        </div>
                        <span class="text-xs font-black text-slate-300 dark:text-slate-700">01</span>
                    </div>
                    <h3 class="text-base font-black text-slate-800 dark:text-slate-100 mb-2">Kesalehan Personal</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Pembentukan pribadi mandiri dan bertakwa yang fokus pada kebersihan diri, kejujuran, kedisiplinan beribadah dan belajar, serta kestabilan emosi.
                    </p>
                </div>
            </div>

            <!-- Karakter 2: Cerdas -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/70 dark:border-slate-800 hover:border-blue-300 dark:hover:border-blue-600 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="h-12 w-12 rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                            <i data-lucide="heart-handshake" class="w-6 h-6"></i>
                        </div>
                        <span class="text-xs font-black text-slate-300 dark:text-slate-700">02</span>
                    </div>
                    <h3 class="text-base font-black text-slate-800 dark:text-slate-100 mb-2">Kesalehan Sosial</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Sikap saling menghormati, menyayangi, dan menolong sesama tanpa membeda-bedakan serta menjunjung tinggi nilai kesopanan dan kebersamaan.
                    </p>
                </div>
            </div>

            <!-- Karakter 3: Mandiri -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/70 dark:border-slate-800 hover:border-emerald-300 dark:hover:border-emerald-600 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="h-12 w-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-custom-primary dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                            <i data-lucide="sprout" class="w-6 h-6"></i>
                        </div>
                        <span class="text-xs font-black text-slate-300 dark:text-slate-700">03</span>
                    </div>
                    <h3 class="text-base font-black text-slate-800 dark:text-slate-100 mb-2">Kesalehan Kealamiahan</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Kepedulian dalam menjaga kebersihan, kesehatan, dan kelangsungan makhluk hidup di alam sebagai wujud rasa syukur kepada Sang Pencipta.
                    </p>
                </div>
            </div>

            <!-- Karakter 4: Peduli -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/70 dark:border-slate-800 hover:border-rose-300 dark:hover:border-rose-600 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="h-12 w-12 rounded-2xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                            <i data-lucide="globe-lock" class="w-6 h-6"></i>
                        </div>
                        <span class="text-xs font-black text-slate-300 dark:text-slate-700">04</span>
                    </div>
                    <h3 class="text-base font-black text-slate-800 dark:text-slate-100 mb-2">Kesalehan Kebangsaan</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Kepekaan sosial, toleransi, budaya gotong royong, dan cinta kelestarian lingkungan.
                    </p>
                </div>
            </div>

            <!-- Karakter 5: Kreatif -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/70 dark:border-slate-800 hover:border-sky-300 dark:hover:border-sky-600 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group sm:col-span-2 lg:col-span-1">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="h-12 w-12 rounded-2xl bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                            <i data-lucide="brain" class="w-6 h-6"></i>
                        </div>
                        <span class="text-xs font-black text-slate-300 dark:text-slate-700">05</span>
                    </div>
                    <h3 class="text-base font-black text-slate-800 dark:text-slate-100 mb-2">Kesalehan Kecendikiaan</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Kegigihan dalam menuntut dan mengembangkan ilmu pengetahuan secara bertanggung jawab demi keselamatan dunia dan akhirat.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Marquee Keyframe Styles --}}
<style>
    @keyframes marqueeScroll {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .marquee-track {
        display: flex;
        width: max-content;
        animation: marqueeScroll 30s linear infinite;
    }
    .marquee-track:hover {
        animation-play-state: paused;
    }
</style>

<!-- Partnership Section - Infinite Running Logos -->
<div id="partnership" class="bg-white dark:bg-slate-900 py-16 md:py-20 border-t border-slate-100 dark:border-slate-800 transition relative overflow-hidden">
    
    {{-- Side Gradient Fades for Smooth Seamless Look --}}
    <div class="absolute inset-y-0 left-0 w-24 md:w-44 bg-gradient-to-r from-white dark:from-slate-900 to-transparent z-10 pointer-events-none"></div>
    <div class="absolute inset-y-0 right-0 w-24 md:w-44 bg-gradient-to-l from-white dark:from-slate-900 to-transparent z-10 pointer-events-none"></div>

    <div class="relative w-full overflow-hidden">
        <div class="marquee-track flex items-center gap-8 md:gap-12">
            @php
                $partners = [
                    ['name' => 'Bank Nasional Indonesia', 'logo' => asset('partnership/bni.svg'), 'label' => 'Bank Nasional Indonesia'],
                    ['name' => 'PT. Teknologi Kartu Indonesia', 'logo' => asset('partnership/tki.svg'), 'label' => 'PT. Teknologi Kartu Indonesia'],
                    ['name' => 'Samsung', 'logo' => asset('partnership/samsung.svg'), 'label' => 'Samsung'],
                    ['name' => 'Cambridge', 'logo' => asset('partnership/cambridge.svg'), 'label' => 'Cambridge'],
                    ['name' => 'Bank Syariah Indonesia', 'logo' => asset('partnership/bsi.svg'), 'label' => 'Bank Syariah Indonesia'],
                    ['name' => 'Meteor Cell', 'logo' => asset('partnership/meteorcell.svg'), 'label' => 'Meteor Cell'],
                    ['name' => 'PT. Zigma Indonesia', 'logo' => asset('partnership/zigma.svg'), 'label' => 'PT. Zigma Indonesia'],
                ];
            @endphp

            {{-- First Loop Set --}}
            @foreach($partners as $partner)
                <div class="flex items-center gap-3.5 bg-slate-50/90 dark:bg-slate-950/70 px-6 py-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm whitespace-nowrap group hover:border-emerald-300 dark:hover:border-emerald-600 transition flex-shrink-0">
                    <div class="h-9 w-9 rounded-xl bg-white flex items-center justify-center flex-shrink-0 p-1.5">
                        <img src="{{ $partner['logo'] }}"
                            alt="{{ $partner['name'] }}"
                            class="max-h-full max-w-full object-contain">
                    </div>
                    <span class="font-extrabold text-xs tracking-wide text-slate-700 dark:text-slate-200 group-hover:text-custom-primary dark:group-hover:text-emerald-400 transition">
                        {{ $partner['label'] }}
                    </span>
                </div>
            @endforeach

            {{-- Second Duplicate Set for Infinite Continuous Marquee --}}
            @foreach($partners as $partner)
                <div class="flex items-center gap-3.5 bg-slate-50/90 dark:bg-slate-950/70 px-6 py-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm whitespace-nowrap group hover:border-emerald-300 dark:hover:border-emerald-600 transition flex-shrink-0">
                    <div class="h-9 w-9 rounded-xl bg-white flex items-center justify-center flex-shrink-0 p-1.5">
                        <img src="{{ $partner['logo'] }}"
                            alt="{{ $partner['name'] }}"
                            class="max-h-full max-w-full object-contain">
                    </div>
                    <span class="font-extrabold text-xs tracking-wide text-slate-700 dark:text-slate-200 group-hover:text-custom-primary dark:group-hover:text-emerald-400 transition">
                        {{ $partner['label'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Kata Mereka (Testimoni) Section -->
<div id="kata-mereka" class="bg-slate-50 dark:bg-slate-950 py-8 border-t border-slate-100 dark:border-slate-800 transition relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
        
        <!-- Section Header -->
        <div class="text-center max-w-2xl mx-auto space-y-3 mb-16 md:mb-20">
            <h2 class="text-3xl md:text-4xl font-black text-custom-primary dark:text-emerald-400 tracking-tight">Kata Mereka Tentang Kami</h2>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                Cerita dan testimoni dari orang tua wali siswa yang mempercayakan masa depan ananda di {{ $schoolName }}.
            </p>
        </div>

        <!-- Testimonial Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Testimonial 1 -->
            <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200/60 dark:border-slate-800 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between space-y-6">
                <div class="space-y-4">
                    <div class="flex items-center gap-1 text-amber-400 text-sm">
                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-350 leading-relaxed italic">
                        "Perkembangan adab dan kemandirian ananda sangat terlihat nyata. Guru-guru mengajar dengan hati dan penuh keteladanan. Hafalan Al-Qur'annya juga berkembang pesat dengan metode yang menyenangkan."
                    </p>
                </div>
                <div class="flex items-center gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div class="h-10 w-10 rounded-full bg-emerald-50 dark:bg-emerald-950 flex items-center justify-center font-black text-custom-primary dark:text-emerald-400 text-xs">
                        BS
                    </div>
                    <div>
                        <h4 class="font-black text-xs text-slate-800 dark:text-slate-100">Bunda Sarah</h4>
                        <p class="text-[10px] text-slate-400 font-semibold">Orang Tua Siswa SD Anak Saleh</p>
                    </div>
                </div>
            </div>

            <!-- Testimonial 2 -->
            <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200/60 dark:border-slate-800 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between space-y-6">
                <div class="space-y-4">
                    <div class="flex items-center gap-1 text-amber-400 text-sm">
                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-350 leading-relaxed italic">
                        "Kurikulumnya sangat seimbang antara akademik modern dan pembinaan akhlak Islam. Fasilitasnya lengkap, ruang kelas nyaman, dan program mentoring karakternya sangat membimbing anak kami."
                    </p>
                </div>
                <div class="flex items-center gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div class="h-10 w-10 rounded-full bg-amber-50 dark:bg-amber-950 flex items-center justify-center font-black text-amber-600 dark:text-amber-400 text-xs">
                        AH
                    </div>
                    <div>
                        <h4 class="font-black text-xs text-slate-800 dark:text-slate-100">Ayah Hendra</h4>
                        <p class="text-[10px] text-slate-400 font-semibold">Orang Tua Siswa SMP Anak Saleh</p>
                    </div>
                </div>
            </div>

            <!-- Testimonial 3 -->
            <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200/60 dark:border-slate-800 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between space-y-6">
                <div class="space-y-4">
                    <div class="flex items-center gap-1 text-amber-400 text-sm">
                        <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-350 leading-relaxed italic">
                        "Lingkungan belajarnya ramah anak dan penuh kasih sayang. Setiap pagi anak saya selalu bersemangat ke sekolah. Komunikasi antara guru dan orang tua juga sangat aktif dan terbuka."
                    </p>
                </div>
                <div class="flex items-center gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div class="h-10 w-10 rounded-full bg-emerald-50 dark:bg-emerald-950 flex items-center justify-center font-black text-custom-primary dark:text-emerald-400 text-xs">
                        BF
                    </div>
                    <div>
                        <h4 class="font-black text-xs text-slate-800 dark:text-slate-100">Bunda Fatimah</h4>
                        <p class="text-[10px] text-slate-400 font-semibold">Orang Tua Siswa PAUD Anak Saleh</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Form Pendaftaran Instan Section -->
<div id="pendaftaran" class="bg-slate-50 dark:bg-slate-950 py-8 border-t border-slate-100 dark:border-slate-800 transition">
    <div class="max-w-5xl mx-auto px-6 lg:px-8">
        
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-xl overflow-hidden grid grid-cols-1 md:grid-cols-12 border border-slate-100 dark:border-slate-800">
            
            <!-- Left Panel (Dark Green) -->
            <div class="md:col-span-5 bg-custom-primary p-8 text-white flex flex-col justify-between space-y-12">
                <div class="space-y-4">
                    <h3 class="text-2xl font-black leading-tight tracking-tight">Mulai Perjalanan Pendidikan Anda</h3>
                    <p class="text-xs text-slate-350 leading-relaxed font-medium">
                        Isi formulir pendaftaran awal. Tim admisi kami akan segera menghubungi Anda untuk proses selanjutnya.
                    </p>
                </div>
                
                <div class="space-y-4 text-xs font-bold">
                    <div class="flex items-center gap-3">
                        <i data-lucide="phone" class="w-4 h-4 text-brand-yellow"></i>
                        <span>(021) 123-4567</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <i data-lucide="mail" class="w-4 h-4 text-brand-yellow"></i>
                        <span>admisi@anaksaleh.sch.id</span>
                    </div>
                </div>
            </div>

            <!-- Right Panel (Form Fields) -->
            <form action="{{ route('quick-register') }}" method="POST" class="md:col-span-7 p-8 space-y-6 text-xs text-slate-700 dark:text-slate-350">
                @csrf
                
                <!-- Full Name -->
                <div class="space-y-2">
                    <label for="candidate_name" class="font-extrabold text-[10px] text-slate-400 uppercase block tracking-wider">Nama Lengkap Calon Siswa</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="candidate_name" id="candidate_name" required placeholder="Masukkan nama lengkap" 
                               class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 focus:outline-none focus:ring-2 focus:ring-brand-emerald transition" />
                    </div>
                </div>

                <!-- 2 Column Inputs -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    <!-- Email -->
                    <div class="space-y-2">
                        <label for="email" class="font-extrabold text-[10px] text-slate-400 uppercase block tracking-wider">Email Orang Tua/Wali</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </span>
                            <input type="email" name="email" id="email" required placeholder="email@contoh.com" 
                                   class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 focus:outline-none focus:ring-2 focus:ring-brand-emerald transition" />
                        </div>
                    </div>

                    <!-- Whatsapp -->
                    <div class="space-y-2">
                        <label for="parent_phone" class="font-extrabold text-[10px] text-slate-400 uppercase block tracking-wider">Nomor Whatsapp</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                <i data-lucide="smartphone" class="w-4 h-4"></i>
                            </span>
                            <input type="text" name="parent_phone" id="parent_phone" required placeholder="0812-3456-789" 
                                   class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 focus:outline-none focus:ring-2 focus:ring-brand-emerald transition" />
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <label for="password" class="font-extrabold text-[10px] text-slate-400 uppercase block tracking-wider">Buat Password Akun</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                            </span>
                            <input type="password" name="password" id="password" required placeholder="Min. 8 karakter" minlength="8"
                                   class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 focus:outline-none focus:ring-2 focus:ring-brand-emerald transition" />
                        </div>
                    </div>

                </div>

                <!-- Admission Level Selector -->
                <div class="space-y-2.5">
                    <label class="font-extrabold text-[10px] text-slate-400 uppercase block tracking-wider">Pilih Jenjang Pendidikan</label>
                    <input type="hidden" name="spmb_unit_id" id="spmb_unit_id" value="">
                    
                    <div class="grid grid-cols-3 gap-3">
                        @foreach($activeUnits as $u)
                            @php
                                $uCode = strtoupper($u->code);
                                $icon = 'book-open';
                                if ($uCode === 'PAUD') $icon = 'car';
                                if ($uCode === 'SMP') $icon = 'flask-conical';
                            @endphp
                            <button type="button" onclick="selectUnit({{ $u->id }}, '{{ $uCode }}')" id="btn-unit-{{ $u->id }}"
                                    class="border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center transition flex flex-col items-center justify-center gap-2 hover:bg-slate-50 dark:hover:bg-slate-800">
                                <i data-lucide="{{ $icon }}" class="w-4 h-4 text-slate-500"></i>
                                <span class="font-bold text-[10px]">{{ $uCode }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>



                <!-- Submit Button -->
                <button type="submit" class="w-full bg-[#F59E0B] hover:bg-[#d97706] text-white py-3.5 rounded-xl font-bold transition flex items-center justify-center gap-2 shadow-md">
                    <span>Kirim Formulir Pendaftaran</span> <i data-lucide="send" class="w-4 h-4"></i>
                </button>
                
                <p class="text-[10px] text-slate-400 text-center font-medium leading-relaxed">
                    Dengan mengirimkan form ini, Anda menyetujui kebijakan privasi kami.
                </p>
            </form>

        </div>
    </div>
</div>

<script>
    // Cycle through slides automatically if there are multiple images
    document.addEventListener("DOMContentLoaded", function() {
        const slides = document.querySelectorAll('.hero-slide');
        if (slides.length <= 1) return;
        
        let currentSlide = 0;
        setInterval(() => {
            // Hide current slide
            slides[currentSlide].classList.remove('opacity-100', 'z-10');
            slides[currentSlide].classList.add('opacity-0', 'z-0');
            
            // Increment index
            currentSlide = (currentSlide + 1) % slides.length;
            
            // Show next slide
            slides[currentSlide].classList.remove('opacity-0', 'z-0');
            slides[currentSlide].classList.add('opacity-100', 'z-10');
        }, 4500);
    });

    function selectUnit(unitId, unitCode) {
        document.getElementById('spmb_unit_id').value = unitId;
        
        // Highlight active unit button
        @foreach($activeUnits as $u)
            (function() {
                var btn = document.getElementById('btn-unit-{{ $u->id }}');
                if (btn) {
                    if ({{ $u->id }} === unitId) {
                        btn.className = `border-2 border-brand-emerald bg-emerald-50/50 dark:bg-emerald-950/20 rounded-2xl p-4 text-center transition flex flex-col items-center justify-center gap-2 shadow-sm`;
                        var icon = btn.querySelector('svg') || btn.querySelector('i');
                        if (icon) {
                            icon.setAttribute('class', 'w-4 h-4 text-brand-emerald dark:text-emerald-450');
                        }
                        var label = btn.querySelector('span');
                        if (label) {
                            label.className = "font-extrabold text-[10px] text-brand-emerald dark:text-emerald-450";
                        }
                    } else {
                        btn.className = `border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center transition flex flex-col items-center justify-center gap-2 hover:bg-slate-50 dark:hover:bg-slate-800`;
                        var icon = btn.querySelector('svg') || btn.querySelector('i');
                        if (icon) {
                            icon.setAttribute('class', 'w-4 h-4 text-slate-500');
                        }
                        var label = btn.querySelector('span');
                        if (label) {
                            label.className = "font-bold text-[10px] text-slate-600 dark:text-slate-400";
                        }
                    }
                }
            })();
        @endforeach
    }

    // Default select SD unit on load
    document.addEventListener("DOMContentLoaded", function() {
        const sdUnit = @json($activeUnits->where('code', 'SD')->first());
        if (sdUnit) {
            selectUnit(sdUnit.id, 'SD');
        } else {
            const firstUnit = @json($activeUnits->first());
            if (firstUnit) {
                selectUnit(firstUnit.id, firstUnit.code);
            }
        }
    });
</script>
@endsection
