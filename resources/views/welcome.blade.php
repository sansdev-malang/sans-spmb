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

<!-- Hero Section -->
<div class="relative bg-slate-50 dark:bg-slate-950 overflow-hidden py-16 md:py-24 transition">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
        
        <!-- Hero Text (Left 7 Columns) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 font-extrabold text-[10px] uppercase tracking-wider px-3.5 py-1.5 rounded-full shadow-sm">
                <span>⭐</span> Penerimaan Siswa Baru {{ $activePeriodYear }}
            </div>
            
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-black leading-tight tracking-tight text-custom-primary dark:text-emerald-400 font-sans">
                {{ $heroTitle }}
            </h1>
            
            <p class="text-slate-600 dark:text-slate-350 text-sm md:text-base leading-relaxed max-w-xl">
                {{ $heroDesc }}
            </p>
            
            <div class="flex flex-wrap gap-4 pt-2">
                <a href="#pendaftaran" class="bg-brand-yellow hover:opacity-90 text-slate-900 px-6 py-3.5 rounded-full font-bold text-xs shadow-md transition flex items-center gap-2">
                    Daftar Sekarang <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
                <a href="#program" class="border border-custom-primary text-custom-primary dark:text-emerald-400 dark:border-emerald-500 hover:bg-slate-100 dark:hover:bg-slate-900 px-6 py-3.5 rounded-full font-bold text-xs transition">
                    Jelajahi Program
                </a>
            </div>
        </div>
        
        <!-- Hero Image (Right 5 Columns) - Dynamic Slideshow -->
        <div class="lg:col-span-5 relative flex justify-center">
            <div class="relative w-full max-w-md">
                <!-- Background decorative shapes -->
                <div class="absolute -top-6 -left-6 w-32 h-32 bg-brand-yellow/10 rounded-full blur-xl"></div>
                <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-emerald-500/10 rounded-full blur-xl"></div>
                
                <!-- Main Image Card Container -->
                <div class="relative overflow-hidden rounded-3xl shadow-xl border border-slate-100 dark:border-slate-800 h-80 w-full bg-slate-100 dark:bg-slate-900">
                    @if(count($heroImages) > 0)
                        @foreach($heroImages as $index => $img)
                            <img src="{{ $img }}" 
                                 alt="Slide {{ $index }}" 
                                 class="hero-slide absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 ease-in-out {{ $index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' }}" />
                        @endforeach
                    @else
                        <!-- Fallback default image -->
                        <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&q=80&w=800" 
                             alt="Siswa Sekolah" 
                             class="absolute inset-0 w-full h-full object-cover" />
                    @endif
                </div>

                <!-- Floating overlay badge -->
                <div class="absolute bottom-4 left-4 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm p-3.5 rounded-2xl shadow-lg border border-slate-100 dark:border-slate-800 flex items-center gap-3 max-w-[260px] translate-y-2 z-20">
                    <div class="h-10 w-10 rounded-full bg-emerald-50 dark:bg-emerald-950 flex items-center justify-center text-brand-emerald dark:text-emerald-400">
                        <i data-lucide="award" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="font-extrabold text-[11px] text-custom-primary dark:text-emerald-400 font-sans">Terakreditasi A</p>
                        <p class="text-[9px] text-slate-400 font-semibold mt-0.5">Standar Pendidikan Nasional</p>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<!-- Program Pendidikan Section (Overview) -->
<div id="program" class="bg-white dark:bg-slate-900 py-20 border-t border-slate-100 dark:border-slate-800 transition">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        
        <!-- Section Header -->
        <div class="text-center max-w-2xl mx-auto space-y-3 mb-16">
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

<!-- Form Pendaftaran Instan Section -->
<div id="pendaftaran" class="bg-slate-50 dark:bg-slate-950 py-20 border-t border-slate-100 dark:border-slate-800 transition">
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
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
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

                </div>

                <!-- Admission Level Selector -->
                <div class="space-y-2.5">
                    <label class="font-extrabold text-[10px] text-slate-400 uppercase block tracking-wider">Pilih Jenjang Pendidikan</label>
                    <input type="hidden" name="admission_level" id="admission_level" value="SD">
                    
                    <div class="grid grid-cols-3 gap-3">
                        
                        <!-- Option PAUD -->
                        <button type="button" onclick="selectLevel('PAUD')" id="btn-PAUD"
                                class="border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center transition flex flex-col items-center justify-center gap-2 hover:bg-slate-50 dark:hover:bg-slate-800">
                            <i data-lucide="car" class="w-4 h-4 text-slate-500"></i>
                            <span class="font-bold text-[10px]">PAUD</span>
                        </button>

                        <!-- Option SD -->
                        <button type="button" onclick="selectLevel('SD')" id="btn-SD"
                                class="border-2 border-custom-primary bg-emerald-50/45 dark:bg-emerald-950/20 rounded-2xl p-4 text-center transition flex flex-col items-center justify-center gap-2">
                            <i data-lucide="book-open" class="w-4 h-4 text-custom-primary dark:text-emerald-400"></i>
                            <span class="font-extrabold text-[10px] text-custom-primary dark:text-emerald-400">SD</span>
                        </button>

                        <!-- Option SMP -->
                        <button type="button" onclick="selectLevel('SMP')" id="btn-SMP"
                                class="border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center transition flex flex-col items-center justify-center gap-2 hover:bg-slate-50 dark:hover:bg-slate-800">
                            <i data-lucide="flask-conical" class="w-4 h-4 text-slate-500"></i>
                            <span class="font-bold text-[10px]">SMP</span>
                        </button>

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

    // Custom Level Radio Toggle
    function selectLevel(level) {
        document.getElementById('admission_level').value = level;
        
        const levels = ['PAUD', 'SD', 'SMP'];
        
        levels.forEach(l => {
            const btn = document.getElementById('btn-' + l);
            if (l === level) {
                btn.className = `border-2 border-custom-primary bg-emerald-50/45 dark:bg-emerald-950/20 rounded-2xl p-4 text-center transition flex flex-col items-center justify-center gap-2`;
                const icon = btn.querySelector('svg');
                if (icon) {
                    icon.style.color = "";
                    icon.className.baseVal = "w-4 h-4 text-custom-primary dark:text-emerald-400";
                }
                const label = btn.querySelector('span');
                if (label) {
                    label.className = "font-extrabold text-[10px] text-custom-primary dark:text-emerald-400";
                }
            } else {
                btn.className = `border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-center transition flex flex-col items-center justify-center gap-2 hover:bg-slate-50 dark:hover:bg-slate-800`;
                const icon = btn.querySelector('svg');
                if (icon) {
                    icon.style.color = "";
                    icon.className.baseVal = "w-4 h-4 text-slate-500";
                }
                const label = btn.querySelector('span');
                if (label) {
                    label.className = "font-bold text-[10px] text-slate-600 dark:text-slate-400";
                }
            }
        });
    }
</script>
@endsection
