@extends('layouts.portal')

@section('title', 'Sekolah Anak Saleh - Penerimaan Murid Baru')

@section('content')
@php
    $activePeriodYear = \App\Models\SpmbPeriod::where('is_active', true)->value('year') ?? '2026/2027';
    $schoolName = \App\Models\Setting::get('school_name', 'Sekolah Anak Saleh');
    $schoolLogo = \App\Models\Setting::get('school_logo_url', '');
    $heroTitle = \App\Models\Setting::get('portal_hero_title', 'Membangun Generasi Cerdas, Sholeh, dan Berakhlak Mulia.');
    $heroDesc = \App\Models\Setting::get('portal_hero_description', 'Bergabunglah bersama Sekolah Anak Saleh. Kami menyajikan kurikulum yang mengintegrasikan nilai-nilai Islam dengan pendidikan modern untuk menyiapkan pemimpin masa depan.');
    $heroImages = json_decode(\App\Models\Setting::get('school_hero_images', '[]'), true) ?: [];
    if (empty($heroImages)) {
        $heroImages = [
            'https://www.sekolahanaksaleh.sch.id/wp-content/uploads/2025/07/Galeri-SD-18.jpg',
            'https://www.sekolahanaksaleh.sch.id/wp-content/uploads/2025/07/Galeri-SD-3.jpg',
        ];
    }
    $heroSlides = collect($heroImages)->map(function ($img, $index) use ($schoolName) {
        $slides = [
            [
                'title' => 'Belajar dengan Hati, Bertumbuh dengan Adab',
                'desc' => "Lingkungan belajar yang hangat dan terarah di {$schoolName}, agar anak tumbuh percaya diri, disiplin, dan berakhlak mulia.",
            ],
            [
                'title' => 'Kurikulum Modern, Jiwa Islami, Karakter Unggul',
                'desc' => "Perpaduan pembelajaran akademik dan pembinaan karakter yang menyiapkan generasi siap menghadapi masa depan.",
            ],
            [
                'title' => 'Rumah Tumbuh untuk Generasi Cerdas dan Saleh',
                'desc' => "Setiap aktivitas dirancang untuk menumbuhkan kecintaan belajar, kemandirian, dan kebiasaan baik sejak dini.",
            ],
        ];

        $meta = $slides[$index % count($slides)];

        return array_merge([
            'image' => $img,
        ], $meta);
    })->values()->all();

    if (empty($heroSlides)) {
        $heroSlides = [
            [
                'image' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&q=80&w=800',
                'title' => $heroTitle,
                'desc' => $heroDesc,
            ],
        ];
    }
    $activeUnits = \App\Models\SpmbUnit::where('is_active', true)->get();
    $activeTestimonials = \App\Models\SpmbTestimonial::with('unit')
        ->where('is_active', true)
        ->orderBy('order', 'asc')
        ->orderBy('id', 'asc')
        ->get();
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
    .hero-copy-fade     { animation: fadeUp  0.55s ease-out both; }
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

    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-10 items-center pt-28 pb-20 md:pt-36 md:pb-28">

        {{-- Hero Text (Left 7 Columns) --}}
        <div class="lg:col-span-7 space-y-6 hero-text-animate">

            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 font-bold text-[11px] px-4 py-2 rounded-full border border-emerald-100 dark:border-emerald-800/60 shadow-sm">
                <span class="relative flex h-2 w-2 flex-shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                </span>
                <span class="uppercase tracking-wider">PENERIMAAN MURID BARU {{ $activePeriodYear }}</span>
                <span class="h-3.5 w-px bg-emerald-200 dark:bg-emerald-700 flex-shrink-0"></span>
                <span class="text-emerald-600 dark:text-emerald-500 font-extrabold">Pendaftaran Dibuka</span>
            </div>

            {{-- Headline Editorial --}}
            <h1 class="text-4xl md:text-5xl lg:text-[2.85rem] xl:text-[3.15rem] font-black leading-[1.05] tracking-tight text-slate-800 dark:text-slate-100 max-w-lg">
                <span data-hero-title>{{ $heroSlides[0]['title'] }}</span>
            </h1>

            {{-- Description --}}
            <p class="text-slate-500 dark:text-slate-400 text-base leading-relaxed max-w-md" data-hero-desc>
                {{ $heroSlides[0]['desc'] }}
            </p>

            {{-- CTA Buttons --}}
            <div class="flex flex-wrap gap-3 pt-1">
                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}"
                       class="inline-flex items-center gap-2 bg-custom-primary hover:opacity-90 text-white px-7 py-3.5 rounded-xl font-bold text-sm shadow-md transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5">
                        Buka Dashboard <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                @else
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center gap-2 bg-custom-primary hover:opacity-90 text-white px-7 py-3.5 rounded-xl font-bold text-sm shadow-md transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5">
                        Daftar Sekarang <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                @endauth
                <a href="#program"
                   class="inline-flex items-center gap-2 border-2 border-custom-primary text-custom-primary dark:text-emerald-400 dark:border-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 px-7 py-3.5 rounded-xl font-bold text-sm transition-all duration-200">
                    Jelajahi Program
                </a>
            </div>
        </div>

        {{-- Hero Image (Right 5 Columns) --}}
        <div class="lg:col-span-5 relative flex justify-center hero-image-animate">
            <div class="relative w-full max-w-[27rem]">
                {{-- Decorative orbs behind image --}}
                <div class="absolute -top-8 -left-8 w-48 h-48 bg-amber-300/15 dark:bg-amber-300/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute -bottom-8 -right-8 w-48 h-48 bg-emerald-400/15 dark:bg-emerald-400/10 rounded-full blur-2xl pointer-events-none"></div>

                {{-- Main Image Container --}}
                <div class="relative overflow-hidden rounded-3xl shadow-2xl border border-white/60 dark:border-slate-700 h-[360px] md:h-[430px] bg-slate-100 dark:bg-slate-800" data-hero-slideshow>
                    @if(count($heroSlides) > 0)
                        @foreach($heroSlides as $index => $slide)
                            <img src="{{ $slide['image'] }}"
                                 alt="{{ $schoolName }} - slide hero {{ $index + 1 }}"
                                 class="hero-slide absolute inset-0 w-full h-full object-cover transition-opacity duration-[1800ms] ease-in-out will-change-opacity {{ $index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' }}" />
                        @endforeach
                    @else
                        {{-- Fallback default image --}}
                        <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&q=80&w=800"
                             alt="Murid Sekolah"
                             class="absolute inset-0 w-full h-full object-cover" />
                    @endif

                </div>

                {{-- Floating Card 1 — Terakreditasi (bottom-left) --}}
                <div class="floating-card-1 absolute -bottom-4 -left-4 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm p-3 rounded-2xl shadow-xl border border-white dark:border-slate-700/80 flex items-center gap-3 z-20 max-w-[200px]">
                    <div class="h-10 w-10 rounded-xl bg-emerald-50 dark:bg-emerald-950 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="award" class="w-5 h-5 text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div>
                        <p class="font-extrabold text-[11px] text-custom-primary dark:text-emerald-400">✓ Terakreditasi A</p>
                        <p class="text-[9px] text-slate-400 font-semibold mt-0.5">Standar Pendidikan Nasional</p>
                    </div>
                </div>

                {{-- Floating Card 2 — 20+ Tahun (top-right) --}}
                <div class="floating-card-2 absolute -top-4 -right-4 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm px-3.5 py-2.5 rounded-2xl shadow-xl border border-white dark:border-slate-700/80 z-20 text-center min-w-[84px]">
                    <p class="font-black text-lg text-custom-primary dark:text-emerald-400 leading-none">20+</p>
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
            @php
                $parseList = function($raw) {
                    if (empty($raw)) return [];
                    $lines = str_contains($raw, "\n") ? preg_split('/\r\n|\r|\n/', $raw) : explode(',', $raw);
                    $res = [];
                    foreach ($lines as $l) {
                        $t = trim(preg_replace('/^(\d+[\.\)]\s*|[\-\*\•\–]\s*)/u', '', trim($l)));
                        if ($t !== '') {
                            $res[] = str_replace('&#44;', ',', $t);
                        }
                    }
                    return $res;
                };
            @endphp
            @foreach($activeUnits as $u)
                @php
                    $uCode = strtolower($u->code);
                    $uDesc = \App\Models\Setting::get('unit_' . $uCode . '_desc', '');
                    $uFeatures = $parseList(\App\Models\Setting::get('unit_' . $uCode . '_features', ''));
                    
                    $uCardImageUrl = \App\Models\Setting::get('unit_' . $uCode . '_card_image_url', '');
                    $uBgImageUrl = \App\Models\Setting::get('unit_' . $uCode . '_bg_image_url', '');
                    
                    $displayImage = $uCardImageUrl ?: $uBgImageUrl;
                    if (empty($displayImage)) {
                        if ($uCode === 'paud') {
                            $displayImage = 'https://www.sekolahanaksaleh.sch.id/wp-content/uploads/2025/07/Galeri-Paud-13.jpg?q=80&w=800&auto=format&fit=crop';
                        } elseif ($uCode === 'sd') {
                            $displayImage = 'https://www.sekolahanaksaleh.sch.id/wp-content/uploads/2025/07/Galeri-Masjid-2.jpg?q=80&w=800&auto=format&fit=crop';
                        } elseif ($uCode === 'smp') {
                            $displayImage = 'https://www.sekolahanaksaleh.sch.id/wp-content/uploads/2026/09/PXL_20260723_011741111-scaled.jpg?q=80&w=800&auto=format&fit=crop';
                        } else {
                            $displayImage = 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?q=80&w=800&auto=format&fit=crop';
                        }
                    }
                @endphp

                <div class="group bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 overflow-hidden flex flex-col justify-between">
                    <div>
                        <!-- Modern Card Image Header -->
                        <div class="relative h-48 w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
                            <img src="{{ $displayImage }}" 
                                 alt="{{ $u->name }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out" />
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/30 to-transparent"></div>

                            <!-- Title Overlay on Image Bottom -->
                            <div class="absolute bottom-3 left-4 right-4">
                                <h3 class="font-black text-lg sm:text-xl text-white drop-shadow-md leading-tight">{{ $u->name }}</h3>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-6 space-y-4">
                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-medium line-clamp-3 min-h-[3.6rem]" title="{{ $uDesc }}">
                                {{ $uDesc }}
                            </p>

                            <!-- Features List -->
                            <div class="space-y-2 pt-3 border-t border-slate-100 dark:border-slate-800/80 text-[11px] font-bold text-slate-600 dark:text-slate-350">
                                @foreach(array_slice($uFeatures, 0, 2) as $feat)
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 h-4 rounded-full bg-emerald-50 dark:bg-emerald-950/60 flex items-center justify-center flex-shrink-0">
                                            <i data-lucide="check" class="w-2.5 h-2.5 text-emerald-600 dark:text-emerald-400"></i>
                                        </div>
                                        <span class="truncate" title="{{ trim($feat) }}">{{ trim($feat) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer Action Button -->
                    <div class="px-6 pb-6 pt-1">
                        <a href="{{ route('unit.detail', $uCode) }}" 
                           class="inline-flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-slate-50 hover:bg-custom-primary dark:bg-slate-800 dark:hover:bg-emerald-600 text-slate-700 hover:text-white dark:text-slate-200 dark:hover:text-white text-xs font-extrabold rounded-xl transition-all duration-200 shadow-xs group/btn">
                            <span>Lihat Detail Selengkapnya</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 group-hover/btn:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </div>
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
                        <div class="h-12 w-12 rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                            <img src="https://lh3.googleusercontent.com/d/1COuVw5kLp1uQcERLPLjkjDDPNwD3hKCH"
                                 alt="Ikon Kesalehan Personal"
                                 class="w-15 h-15 object-contain rounded-lg"
                                 loading="lazy" />
                        </div>
                        <span class="text-xs font-black text-slate-300 dark:text-slate-700">01</span>
                    </div>
                    <h3 class="text-base font-black text-slate-800 dark:text-slate-100 mb-2">Kesalehan Personal</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Mengenali diri sendiri dengan baik, menginternalisasi fikir, dzikir, dan amal shalih, menjadi pribadi yang bahagia, penuh cinta kasih hingga mampu transendental kepada ilahi.
                    </p>
                </div>
            </div>

            <!-- Karakter 2: Cerdas -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/70 dark:border-slate-800 hover:border-blue-300 dark:hover:border-blue-600 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="h-12 w-12 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                            <img src="https://lh3.googleusercontent.com/d/1B51_sjZl_60XvGrf3HRSfkTIx18X6l67"
                                 alt="Ikon Kesalehan Sosial"
                                 class="w-15 h-15 object-contain rounded-lg"
                                 loading="lazy" />
                        </div>
                        <span class="text-xs font-black text-slate-300 dark:text-slate-700">02</span>
                    </div>
                    <h3 class="text-base font-black text-slate-800 dark:text-slate-100 mb-2">Kesalehan Sosial</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Jiwa yang bahagia dan tenang dapat menebarkan cinta kasih kepada sesama, menjaga silaturahim, peduli sesama, dan mau berbagi, mampu menempatkan diri di masyarakat, menjunjung tinggi budaya gotong royong dan tolong menolong.
                    </p>
                </div>
            </div>

            <!-- Karakter 3: Mandiri -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/70 dark:border-slate-800 hover:border-emerald-300 dark:hover:border-emerald-600 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="h-12 w-12 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-custom-primary dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                            <img src="https://lh3.googleusercontent.com/d/1LIdIP-RXNh0wNRA3j6RH-eErV8Bp6ewY"
                                 alt="Ikon Kesalehan Kealamiahan"
                                 class="w-15 h-15 object-contain rounded-lg"
                                 loading="lazy" />
                        </div>
                        <span class="text-xs font-black text-slate-300 dark:text-slate-700">03</span>
                    </div>
                    <h3 class="text-base font-black text-slate-800 dark:text-slate-100 mb-2">Kesalehan Kealamiahan</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Manusia bukan hanya pemimpin bagi sesamanya namun juga bagi alam, anak saleh merupakan pribadi yang penuh cinta pada alam sekitar dengan merawat dan melestarikan lingkungan, hewan dan tumbuhan demi keselamatan dan keseimbangan alam.
                    </p>
                </div>
            </div>

            <!-- Karakter 4: Peduli -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/70 dark:border-slate-800 hover:border-rose-300 dark:hover:border-rose-600 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="h-12 w-12 rounded-full bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                            <img src="https://lh3.googleusercontent.com/d/1Hp_FfETsxIMfY-_WKwi8_5Upt5dY7tAI"
                                 alt="Ikon Kesalehan Kebangsaan"
                                 class="w-15 h-15 object-contain rounded-lg"
                                 loading="lazy" />
                        </div>
                        <span class="text-xs font-black text-slate-300 dark:text-slate-700">04</span>
                    </div>
                    <h3 class="text-base font-black text-slate-800 dark:text-slate-100 mb-2">Kesalehan Kebangsaan</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Pengabdian diri bukan hanya untuk agama namun juga kepada bangsa, komitmen kebangsaan diasah dalam rangka menanamkan cinta serta bangga terhadap tanah air dan bangsa Indonesia.
                    </p>
                </div>
            </div>

            <!-- Karakter 5: Kreatif -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/70 dark:border-slate-800 hover:border-sky-300 dark:hover:border-sky-600 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group sm:col-span-2 lg:col-span-1">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <div class="h-12 w-12 rounded-full bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                            <img src="https://lh3.googleusercontent.com/d/1mvC1ciPRfv8UfncFOEQ9jZdIdu0ViQl0"
                                 alt="Ikon Kesalehan Kecendikiaan"
                                 class="w-15 h-15 object-contain rounded-lg"
                                 loading="lazy" />
                        </div>
                        <span class="text-xs font-black text-slate-300 dark:text-slate-700">05</span>
                    </div>
                    <h3 class="text-base font-black text-slate-800 dark:text-slate-100 mb-2">Kesalehan Kecendikiaan</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Komitmen untuk menjadi cendekiawan muslim yang unggul dalam prestasi dengan akhlak dan agama harus ditanamkan sejak usia dini, melek pengetahuan akan dunia dalam segala aspek ilmu pengetahuan merupakan tujuan dari pendidikan Anak Saleh hingga melahirkan pemimpin-pemimpin cerdas-saleh di masa depan.
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
                    ['name' => 'Bank Nasional Indonesia', 'logo' => asset('storage/partnership/bni.svg'), 'label' => 'Bank Nasional Indonesia'],
                    ['name' => 'PT. Teknologi Kartu Indonesia', 'logo' => asset('storage/partnership/tki.svg'), 'label' => 'PT. Teknologi Kartu Indonesia'],
                    ['name' => 'Samsung', 'logo' => asset('storage/partnership/samsung.svg'), 'label' => 'Samsung'],
                    ['name' => 'Cambridge', 'logo' => asset('storage/partnership/cambridge.svg'), 'label' => 'Cambridge'],
                    ['name' => 'Bank Syariah Indonesia', 'logo' => asset('storage/partnership/bsi.svg'), 'label' => 'Bank Syariah Indonesia'],
                    ['name' => 'Winpay', 'logo' => asset('storage/partnership/winpay.svg'), 'label' => 'Winpay'],
                    ['name' => 'Meteor Cell', 'logo' => asset('storage/partnership/meteorcell.svg'), 'label' => 'Meteor Cell'],
                    ['name' => 'Google Workspace Education', 'logo' => asset('storage/partnership/google.svg'), 'label' => 'Google Workspace Education'],
                    ['name' => 'PT. Zigma Indonesia', 'logo' => asset('storage/partnership/zigma.svg'), 'label' => 'PT. Zigma Indonesia'],
                    ['name' => 'Bank Rakyat Indonesia', 'logo' => asset('storage/partnership/bri.svg'), 'label' => 'Bank Rakyat Indonesia'],
                ];
            @endphp
    
            {{-- First Loop Set --}}
            @foreach($partners as $partner)
                <div class="flex items-center gap-3.5 bg-slate-50/90 dark:bg-slate-950/70 px-6 py-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm whitespace-nowrap group hover:border-emerald-300 dark:hover:border-emerald-600 transition flex-shrink-0">
                    <div class="partnership-logo-bg h-9 w-9 rounded-xl bg-slate-50 dark:bg-white flex items-center justify-center flex-shrink-0 p-1.5">
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
                    <div class="partnership-logo-bg h-9 w-9 rounded-xl bg-slate-50 dark:bg-white flex items-center justify-center flex-shrink-0 p-1.5">
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
@php
    $testimonialEnabled = \App\Models\Setting::get('portal_testimonial_enabled', '1') !== '0';
    $testimonialTitle = \App\Models\Setting::get('portal_testimonial_title', 'Kata Mereka Tentang Kami');
    $testimonialSubtitle = \App\Models\Setting::get('portal_testimonial_subtitle', 'Simak pengalaman dan kesan nyata dari para orang tua murid serta alumni mengenai lingkungan belajar dan pembinaan karakter di Sekolah Anak Saleh.');
@endphp

@if($testimonialEnabled)
<div id="kata-mereka" class="bg-slate-50 dark:bg-slate-950 py-16 md:py-20 border-t border-slate-100 dark:border-slate-800 transition relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10 space-y-10">
        
        <!-- Section Header -->
        <div class="text-center max-w-3xl mx-auto space-y-3">
            <h2 class="text-3xl md:text-4xl font-black text-custom-primary dark:text-emerald-400 tracking-tight gap-2">
                {{ $testimonialTitle }}
            </h2>
            <!-- @if(!empty($testimonialSubtitle))
                <p class="text-sm md:text-base text-slate-500 dark:text-slate-400 leading-relaxed font-medium max-w-2xl mx-auto">
                    {{ $testimonialSubtitle }}
                </p>
            @endif -->
        </div>

        @if($activeTestimonials->isNotEmpty())
            <!-- Dynamic Jenjang Filter Pills -->
            @php
                $availableUnitIds = $activeTestimonials->pluck('spmb_unit_id')->filter()->unique();
                $filterUnits = $activeUnits->whereIn('id', $availableUnitIds);
                $hasGeneral = $activeTestimonials->whereNull('spmb_unit_id')->count() > 0;
            @endphp
            @if($filterUnits->count() > 1 || ($filterUnits->count() >= 1 && $hasGeneral))
                <div class="flex items-center justify-center gap-2 flex-wrap pb-2">
                    <button type="button" onclick="filterWelcomeTestimonials('all', this)" class="welcome-testi-btn px-4 py-2 rounded-xl text-xs font-extrabold transition-all duration-200 bg-custom-primary text-white shadow-md cursor-pointer">
                        Semua Jenjang ({{ $activeTestimonials->count() }})
                    </button>
                    @foreach($filterUnits as $u)
                        @php $uCount = $activeTestimonials->where('spmb_unit_id', $u->id)->count(); @endphp
                        @if($uCount > 0)
                            <button type="button" onclick="filterWelcomeTestimonials('{{ $u->id }}', this)" class="welcome-testi-btn px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-800 hover:border-custom-primary hover:text-custom-primary dark:hover:text-emerald-400 shadow-xs cursor-pointer">
                                {{ $u->name }} ({{ $uCount }})
                            </button>
                        @endif
                    @endforeach
                    @if($hasGeneral)
                        <button type="button" onclick="filterWelcomeTestimonials('general', this)" class="welcome-testi-btn px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-800 hover:border-custom-primary hover:text-custom-primary dark:hover:text-emerald-400 shadow-xs cursor-pointer">
                            Umum ({{ $activeTestimonials->whereNull('spmb_unit_id')->count() }})
                        </button>
                    @endif
                </div>
            @endif

            <!-- Testimonial Cards Grid -->
            <div id="welcome-testimonials-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($activeTestimonials as $testi)
                    <div data-unit-id="{{ $testi->spmb_unit_id ?? 'general' }}" class="welcome-testi-card bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200/60 dark:border-slate-800 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between space-y-6 hover:-translate-y-1">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1 text-amber-400 text-sm">
                                    @for($s = 1; $s <= ($testi->rating ?? 5); $s++)
                                        <span>★</span>
                                    @endfor
                                    @for($s = ($testi->rating ?? 5) + 1; $s <= 5; $s++)
                                        <span class="text-slate-200 dark:text-slate-700">★</span>
                                    @endfor
                                </div>
                                @if($testi->unit)
                                    <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-custom-primary dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                                        {{ $testi->unit->name }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800/60">
                                        Semua Jenjang
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-350 leading-relaxed italic">
                                "{{ trim($testi->content, "\"'\t\n\r ") }}"
                            </p>
                        </div>
                        <div class="flex items-center gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                            @if($testi->avatar_url)
                                <img src="{{ $testi->avatar_url }}" alt="{{ $testi->name }}" class="h-11 w-11 rounded-full object-cover border-2 border-slate-100 dark:border-slate-700 shadow-xs flex-shrink-0" />
                            @else
                                <div class="h-11 w-11 rounded-full bg-emerald-50 dark:bg-emerald-950/80 border border-emerald-200/80 dark:border-emerald-800/80 flex items-center justify-center font-black text-custom-primary dark:text-emerald-400 text-xs flex-shrink-0 shadow-xs">
                                    {{ $testi->initials }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <h4 class="font-extrabold text-xs text-slate-800 dark:text-slate-100 truncate">{{ $testi->name }}</h4>
                                <p class="text-[10px] text-slate-400 font-semibold truncate">{{ $testi->role_title }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Fallback Default Testimonials if none in database -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
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
                        <div class="h-11 w-11 rounded-full bg-emerald-50 dark:bg-emerald-950 flex items-center justify-center font-black text-custom-primary dark:text-emerald-400 text-xs flex-shrink-0">
                            BS
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-black text-xs text-slate-800 dark:text-slate-100 truncate">Bunda Sarah</h4>
                            <p class="text-[10px] text-slate-400 font-semibold truncate">Orang Tua Murid SD Anak Saleh</p>
                        </div>
                    </div>
                </div>

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
                        <div class="h-11 w-11 rounded-full bg-amber-50 dark:bg-amber-950 flex items-center justify-center font-black text-amber-600 dark:text-amber-400 text-xs flex-shrink-0">
                            AH
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-black text-xs text-slate-800 dark:text-slate-100 truncate">Ayah Hendra</h4>
                            <p class="text-[10px] text-slate-400 font-semibold truncate">Orang Tua Murid SMP Anak Saleh</p>
                        </div>
                    </div>
                </div>

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
                        <div class="h-11 w-11 rounded-full bg-emerald-50 dark:bg-emerald-950 flex items-center justify-center font-black text-custom-primary dark:text-emerald-400 text-xs flex-shrink-0">
                            BF
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-black text-xs text-slate-800 dark:text-slate-100 truncate">Bunda Fatimah</h4>
                            <p class="text-[10px] text-slate-400 font-semibold truncate">Orang Tua Murid PAUD Anak Saleh</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>

<script>
    function filterWelcomeTestimonials(unitId, btnElem) {
        document.querySelectorAll('.welcome-testi-btn').forEach(btn => {
            btn.classList.remove('bg-custom-primary', 'text-white', 'shadow-md');
            btn.classList.add('bg-white', 'dark:bg-slate-900', 'text-slate-600', 'dark:text-slate-300');
        });
        btnElem.classList.remove('bg-white', 'dark:bg-slate-900', 'text-slate-600', 'dark:text-slate-300');
        btnElem.classList.add('bg-custom-primary', 'text-white', 'shadow-md');

        const cards = document.querySelectorAll('.welcome-testi-card');
        cards.forEach(card => {
            const cardUnit = card.getAttribute('data-unit-id');
            if (unitId === 'all' || cardUnit === unitId) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }
</script>
@endif

    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const hero = document.querySelector('[data-hero-slideshow]');
        if (!hero) return;

        const slides = Array.from(hero.querySelectorAll('.hero-slide'));
        const heroTitle = document.querySelector('[data-hero-title]');
        const heroDesc = document.querySelector('[data-hero-desc]');
        const slideData = @json($heroSlides);
        const heroTextNodes = [heroTitle, heroDesc].filter(Boolean);
        if (slides.length <= 1) return;

        let currentSlide = 0;
        let intervalId = null;
        let textAnimId = null;

        const animateHeroText = () => {
            heroTextNodes.forEach((node) => {
                node.classList.remove('hero-copy-fade');
                void node.offsetWidth;
                node.classList.add('hero-copy-fade');
            });

            if (textAnimId) {
                clearTimeout(textAnimId);
            }

            textAnimId = setTimeout(() => {
                heroTextNodes.forEach((node) => node.classList.remove('hero-copy-fade'));
            }, 700);
        };

        const showSlide = (nextSlide) => {
            slides[currentSlide].classList.remove('opacity-100', 'z-10');
            slides[currentSlide].classList.add('opacity-0', 'z-0');

            currentSlide = nextSlide;

            slides[currentSlide].classList.remove('opacity-0', 'z-0');
            slides[currentSlide].classList.add('opacity-100', 'z-10');

            if (heroTitle && slideData[nextSlide]) {
                heroTitle.textContent = slideData[nextSlide].title || '';
            }

            if (heroDesc && slideData[nextSlide]) {
                heroDesc.textContent = slideData[nextSlide].desc || '';
            }

            animateHeroText();
        };

        const nextSlide = () => showSlide((currentSlide + 1) % slides.length);

        const start = () => {
            if (intervalId) return;
            intervalId = setInterval(nextSlide, 5500);
        };

        const stop = () => {
            if (!intervalId) return;
            clearInterval(intervalId);
            intervalId = null;
        };

        start();
    });
</script>
@endsection
