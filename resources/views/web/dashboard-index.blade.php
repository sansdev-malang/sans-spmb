@extends('layouts.portal')

@section('title', 'Pilih Pendaftaran Murid - Portal SPMB')

@section('content')
<div class="max-w-7xl mx-auto px-4 pt-4 pb-12 sm:px-6 lg:px-8 space-y-6">

    <!-- WELCOME BANNER & ONBOARDING CARD GRID (ALWAYS SHOWN) -->
    <div class="max-w-4xl mx-auto pt-2 pb-6 space-y-6 text-center">
        <div class="space-y-3">
            <h1 class="text-3xl font-extrabold text-slate-850 dark:text-white tracking-tight">Selamat Datang di Portal Penerimaan Murid Baru</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 max-w-xl mx-auto leading-relaxed">
                Langkah pertama pendidikan terbaik ananda di {{ \App\Models\Setting::get('school_name', 'Sekolah Anak Saleh') }} dimulai dari sini. Silakan daftarkan anak Anda untuk memulai proses seleksi masuk penerimaan murid baru.
            </p>
        </div>

        @if(isset($pendingDrafts) && $pendingDrafts->isNotEmpty())
            <!-- PENDING DRAFT REGISTRATION BANNER -->
            <div class="max-w-4xl mx-auto space-y-3">
                @foreach($pendingDrafts as $draft)
                    <div class="bg-amber-50/90 dark:bg-amber-950/40 border border-amber-200/90 dark:border-amber-800/80 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm text-left animate-in fade-in duration-200">
                        <div class="flex items-start gap-3.5">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0 mt-0.5 shadow-inner">
                                <i data-lucide="clock" class="w-5 h-5"></i>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider bg-amber-200/80 dark:bg-amber-800/50 text-amber-900 dark:text-amber-200 px-2 py-0.5 rounded-md">
                                        Pendaftaran Belum Selesai
                                    </span>
                                    <span class="text-xs text-slate-400 font-medium">• {{ $draft->created_at->diffForHumans() }}</span>
                                </div>
                                <h3 class="font-extrabold text-sm text-slate-850 dark:text-white">
                                    {{ $draft->candidate_name ?? 'Calon Murid' }} — <span class="text-emerald-700 dark:text-emerald-400 font-bold">{{ $draft->unit->name ?? 'Unit Sekolah' }}@if($draft->sub_unit_display_name) ({{ $draft->sub_unit_display_name }})@endif</span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                    Biaya awal pendaftaran belum dibayar. Anda dapat melanjutkan pembayaran atau mengganti pilihan unit pendaftaran.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto shrink-0 justify-end pt-1 sm:pt-0">
                            <form method="POST" action="{{ route('dashboard.registration.draft.delete', $draft->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan draf pendaftaran ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3.5 py-2.5 rounded-xl border border-slate-250 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold text-xs transition cursor-pointer">
                                    Batalkan
                                </button>
                            </form>
                            <a href="{{ route('dashboard.payment', $draft->id) }}" class="px-5 py-2.5 rounded-xl bg-brand-emerald hover:bg-emerald-600 text-white font-bold text-xs transition shadow-md flex items-center gap-1.5 cursor-pointer">
                                <i data-lucide="credit-card" class="w-4 h-4"></i> Lanjutkan Pembayaran
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Card Options for Each School Unit -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
            @foreach($units as $unit)
                @php 
                    $uCode = strtolower($unit->code);
                    $firstGrade = $unit->grades->first();
                    $firstGradeId = $firstGrade?->id ?? '';
                    $brochureUrl = \App\Models\Setting::get('unit_' . $uCode . '_brochure_url');
                    $waUrl = $unit->getWhatsappUrl();
                    $unitLogoPath = file_exists(public_path('logo/' . $uCode . '.svg')) 
                        ? 'logo/' . $uCode . '.svg' 
                        : (file_exists(public_path('logo/' . $uCode . '.png')) ? 'logo/' . $uCode . '.png' : 'storage/logo/' . $uCode . '.svg');
                    $hasUnitLogo = file_exists(public_path($unitLogoPath));
                    $unitTheme = match (strtoupper($unit->code)) {
                        'PAUD' => [
                            'card' => 'bg-slate-200 dark:bg-slate-700 border-slate-300 dark:border-slate-600',
                            'title' => 'text-slate-800 dark:text-white',
                            'description' => 'text-slate-600 dark:text-slate-200',
                            'primaryButton' => 'bg-green-500 hover:bg-green-600',
                            'secondaryButton' => 'bg-white/70 hover:bg-white text-green-800 border-white/70',
                        ],
                        'SD' => [
                            'card' => 'bg-green-500 dark:bg-green-600 border-green-400 dark:border-green-500',
                            'title' => 'text-white',
                            'description' => 'text-green-50',
                            'primaryButton' => 'bg-green-900 hover:bg-green-800',
                            'secondaryButton' => 'bg-white/15 hover:bg-white/25 text-white border-white/30',
                        ],
                        'SMP' => [
                            'card' => 'bg-indigo-800 dark:bg-indigo-900 border-indigo-700 dark:border-indigo-800',
                            'title' => 'text-white',
                            'description' => 'text-indigo-100',
                            'primaryButton' => 'bg-orange-500 hover:bg-orange-600',
                            'secondaryButton' => 'bg-white/15 hover:bg-white/25 text-white border-white/30',
                        ],
                        default => [
                            'card' => 'bg-white dark:bg-slate-900 border-slate-150/80 dark:border-slate-800',
                            'title' => 'text-slate-850 dark:text-white',
                            'description' => 'text-slate-400 dark:text-slate-500',
                            'primaryButton' => 'bg-slate-900 hover:bg-emerald-600 dark:bg-emerald-600 dark:hover:bg-emerald-500',
                            'secondaryButton' => 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border-emerald-200/60',
                        ],
                    };
                @endphp
                <div class="{{ $unitTheme['card'] }} rounded-3xl p-6 border shadow-sm flex flex-col justify-between items-center text-center space-y-4 hover:shadow-lg transition-all duration-200">
                    <div class="space-y-3 w-full flex flex-col items-center">
                        <div class="h-16 w-16 flex items-center justify-center overflow-hidden">
                            @if($hasUnitLogo)
                                <img src="{{ asset($unitLogoPath) }}" alt="Logo {{ $unit->name }}" class="h-full w-full object-contain" loading="lazy" decoding="async">
                            @else
                                <div class="h-14 w-14 rounded-2xl bg-white/20 dark:bg-white/10 backdrop-blur-sm border border-white/25 flex items-center justify-center shadow-inner">
                                    <span class="font-black text-lg tracking-wider {{ $unitTheme['title'] }}">{{ strtoupper($unit->code) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="space-y-1">
                            <h3 class="font-extrabold {{ $unitTheme['title'] }} text-sm leading-snug">{!! str_replace('Anak Saleh', '<br>Anak Saleh', e($unit->name)) !!}</h3>
                            <p class="text-xs {{ $unitTheme['description'] }} line-clamp-2">
                                {{ \App\Models\Setting::get('unit_' . $uCode . '_desc', 'Pilihan program pendidikan terbaik.') }}
                            </p>
                        </div>
                    </div>

                    <div class="w-full space-y-2 pt-2">
                        <button onclick="startRegistrationWithUnit('{{ $unit->id }}', '{{ $firstGradeId }}')" class="w-full py-3 {{ $unitTheme['primaryButton'] }} text-white rounded-xl text-xs font-black transition-all shadow-sm flex items-center justify-center gap-1.5">
                            <i data-lucide="user-plus" class="w-4 h-4"></i> Daftarkan Sekarang
                        </button>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <a href="{{ $waUrl }}" target="_blank" class="py-2 px-2.5 {{ $unitTheme['secondaryButton'] }} rounded-xl text-xs font-bold transition flex items-center justify-center gap-1 border" title="Hubungi WhatsApp Admin {{ $unit->name }}">
                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> Admin WA
                            </a>
                            @if(!empty($brochureUrl))
                                <a href="{{ $brochureUrl }}" target="_blank" download class="py-2 px-2.5 {{ $unitTheme['secondaryButton'] }} rounded-xl text-xs font-bold transition flex items-center justify-center gap-1 border" title="Unduh Brosur {{ $unit->name }}">
                                    <i data-lucide="file-down" class="w-3.5 h-3.5"></i> Brosur
                                </a>
                            @else
                                <span class="py-2 px-2.5 bg-slate-50 dark:bg-slate-800/40 text-slate-400 dark:text-slate-600 rounded-xl text-xs font-bold flex items-center justify-center gap-1 border border-slate-100 dark:border-slate-800 cursor-not-allowed" title="Brosur belum tersedia">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Brosur
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- LIST OF CURRENT ACTIVE REGISTRATIONS (SHOWN UNDERNEATH IF NOT EMPTY) -->
    @if(!$registrations->isEmpty())
        <div class="pt-8 border-t border-slate-200/60 dark:border-slate-800 space-y-6">
            <div class="text-center max-w-xl mx-auto space-y-1">
                <h2 class="text-2xl font-extrabold text-slate-850 dark:text-white tracking-tight">Pendaftaran Ananda Anda</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Kelola tahapan pendaftaran atau selesaikan administrasi murid di bawah ini.</p>
            </div>

            <div class="flex flex-wrap justify-center gap-6 max-w-7xl mx-auto pt-2">
                @foreach($registrations as $reg)
                    @php
                        $isPaid = $reg->payments->where('payment_type', 'registration_fee')->where('status', 'success')->isNotEmpty();
                        $status = $reg->registration_status;
                        $regNum = $reg->registration_number ?: ('REG-' . str_pad($reg->id, 4, '0', STR_PAD_LEFT));
                    @endphp
                    <div class="w-full md:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] max-w-md bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-150/80 dark:border-slate-800 hover:shadow-lg transition-all duration-300 relative group flex flex-col justify-between text-left">
                        
                        <div>
                            <!-- Header Bar: ID & Status Badge -->
                            <div class="flex items-center justify-between gap-2 pb-3 mb-3 border-b border-slate-100 dark:border-slate-800">
                                <span class="text-[10px] font-mono font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-lg flex items-center gap-1.5 border border-slate-200/60 dark:border-slate-700">
                                    <i data-lucide="tag" class="w-3 h-3 text-emerald-600"></i> {{ $reg->id_label }}
                                </span>
                                
                                @if($status === 'completed')
                                    <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Resmi Terdaftar
                                    </span>
                                @elseif($status === 'agreement_signed')
                                    <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-400 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pelunasan Administrasi
                                    </span>
                                @elseif($status === 'taaruf_completed')
                                    <span class="inline-flex items-center gap-1 bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-400 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Ta'aruf Selesai
                                    </span>
                                @elseif($status === 'verified')
                                    <span class="inline-flex items-center gap-1 bg-teal-100 text-teal-800 dark:bg-teal-950 dark:text-teal-400 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span> Berkas Terverifikasi
                                    </span>
                                @elseif($status === 'submitted')
                                    <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-400 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span> Verifikasi Berkas
                                    </span>
                                @elseif($status === 'failed')
                                    <span class="inline-flex items-center gap-1 bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-400 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span> Perlu Perbaikan
                                    </span>
                                @else
                                    @if($isPaid)
                                        <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider border border-amber-200 dark:border-amber-900/40">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pengisian Formulir
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider border border-rose-200 dark:border-rose-900/40">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Belum Bayar
                                        </span>
                                    @endif
                                @endif
                            </div>
                            
                            <!-- Centered Candidate Profile Header -->
                            <div class="flex flex-col items-center text-center mb-5">
                                <div class="h-16 w-16 bg-emerald-600 text-white dark:bg-emerald-500 dark:text-white rounded-2xl flex items-center justify-center text-2xl font-black shadow-lg shadow-emerald-600/20 mb-2.5 ring-4 ring-emerald-50 dark:ring-emerald-950/40 flex-shrink-0">
                                    {{ strtoupper(substr(trim($reg->candidate_name ?? 'A'), 0, 1)) }}
                                </div>
                                <h3 class="font-extrabold text-slate-850 dark:text-white text-base truncate max-w-full leading-tight">
                                    {{ $reg->candidate_name ?? 'Anak (Draft)' }}
                                </h3>
                                <p class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-1">
                                    {{ $reg->unit->name ?? '-' }}
                                </p>
                                @if($reg->sub_unit_display_name)
                                    <div class="mt-1.5 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200/70 dark:border-emerald-800/60 text-[11px] font-extrabold text-emerald-800 dark:text-emerald-300 shadow-2xs">
                                        <i data-lucide="sparkles" class="w-3 h-3 text-emerald-600 dark:text-emerald-400"></i>
                                        <span>{{ $reg->sub_unit_display_name }}</span>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Mini Visual Progress Tracker -->
                            @php
                                $stepsVisual = [
                                    ['label' => 'Bayar', 'done' => $isPaid, 'active' => !$isPaid],
                                    ['label' => 'Formulir', 'done' => ($status !== 'draft' && $status !== 'failed'), 'active' => ($isPaid && ($status === 'draft' || $status === 'failed'))],
                                    ['label' => 'Verifikasi', 'done' => in_array($status, ['verified', 'taaruf_completed', 'agreement_signed', 'completed']), 'active' => ($status === 'submitted')],
                                    ['label' => 'Ta\'aruf', 'done' => in_array($status, ['taaruf_completed', 'agreement_signed', 'completed']), 'active' => ($status === 'verified')],
                                    ['label' => 'Administrasi', 'done' => ($status === 'completed'), 'active' => in_array($status, ['taaruf_completed', 'agreement_signed'])],
                                ];
                            @endphp
                            <div class="mb-4 bg-slate-50/80 dark:bg-slate-950/60 rounded-2xl p-3 border border-slate-100 dark:border-slate-800">
                                <div class="flex items-center justify-between relative">
                                    <div class="absolute left-3 right-3 top-2.5 h-0.5 bg-slate-200 dark:bg-slate-800 -z-0"></div>
                                    @foreach($stepsVisual as $sIndex => $sv)
                                        <div class="flex flex-col items-center relative z-10">
                                            <div class="h-5 w-5 rounded-full flex items-center justify-center text-[9px] font-black transition shadow-sm {{ $sv['done'] ? 'bg-emerald-600 text-white' : ($sv['active'] ? 'bg-amber-400 text-slate-900 ring-2 ring-amber-200 dark:ring-amber-900/50 scale-110 font-black' : 'bg-slate-200 dark:bg-slate-800 text-slate-400') }}">
                                                @if($sv['done'])
                                                    <i data-lucide="check" class="w-3 h-3"></i>
                                                @else
                                                    {{ $sIndex + 1 }}
                                                @endif
                                            </div>
                                            <span class="text-[8px] font-bold mt-1 {{ $sv['done'] ? 'text-emerald-700 dark:text-emerald-400' : ($sv['active'] ? 'text-amber-600 dark:text-amber-400 font-extrabold' : 'text-slate-400') }}">
                                                {{ $sv['label'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <!-- Detailed Information Chips / Table Grid -->
                            <div class="bg-slate-50 dark:bg-slate-950 rounded-2xl p-3.5 border border-slate-100 dark:border-slate-800 text-xs space-y-2 mb-4">
                                @if($reg->sub_unit_display_name)
                                    <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                        <span class="flex items-center gap-1.5"><i data-lucide="layout-grid" class="w-3.5 h-3.5 text-slate-400"></i> Pilihan Layanan</span>
                                        <span class="font-bold text-brand-emerald dark:text-emerald-400">{{ $reg->sub_unit_display_name }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                    <span class="flex items-center gap-1.5"><i data-lucide="layers" class="w-3.5 h-3.5 text-slate-400"></i> Jenjang / Tingkat</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $reg->class_display_name }}</span>
                                </div>
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                    <span class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i> Tahun Pelajaran</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $reg->period->year ?? '2026-2027' }}</span>
                                </div>
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                    <span class="flex items-center gap-1.5"><i data-lucide="compass" class="w-3.5 h-3.5 text-slate-400"></i> Jalur & Gelombang</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $reg->type->name ?? 'Reguler' }} • {{ $reg->wave->name ?? 'Gel. 1' }}</span>
                                </div>
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                    <span class="flex items-center gap-1.5"><i data-lucide="book-open" class="w-3.5 h-3.5 text-slate-400"></i> Kategori Murid</span>
                                    <span class="font-bold text-brand-emerald dark:text-emerald-400">{{ $reg->classProgram->name ?? ($reg->getFieldValue('class_program') ?: 'Reguler') }}</span>
                                </div>
                                @if($reg->non_formal_services->isNotEmpty())
                                    <div class="flex justify-between items-center text-slate-600 dark:text-slate-400 border-t border-slate-200/60 dark:border-slate-800 pt-1.5 mt-1.5">
                                        <span class="flex items-center gap-1.5"><i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i> Layanan Non-Formal</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200 truncate max-w-[140px]">{{ $reg->non_formal_services->pluck('name')->implode(', ') }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Stage Next Action Banner / Hint -->
                            <div class="mb-4">
                                @if(!$isPaid)
                                    <div class="p-2.5 bg-rose-50 dark:bg-rose-950/30 rounded-xl border border-rose-200 dark:border-rose-900/40 text-[10px] text-rose-700 dark:text-rose-400 font-bold flex items-center gap-2">
                                        <i data-lucide="credit-card" class="w-4 h-4 flex-shrink-0"></i>
                                        <span>Biaya pendaftaran belum dibayar. Selesaikan pembayaran untuk membuka akses formulir.</span>
                                    </div>
                                @elseif($status === 'draft')
                                    <div class="p-2.5 bg-amber-50 dark:bg-amber-950/30 rounded-xl border border-amber-200 dark:border-amber-900/40 text-[10px] text-amber-700 dark:text-amber-400 font-bold flex items-center gap-2">
                                        <i data-lucide="edit-3" class="w-4 h-4 flex-shrink-0"></i>
                                        <span>Biaya pendaftaran lunas. Silakan lengkapi formulir & berkas persyaratan.</span>
                                    </div>
                                @elseif($status === 'submitted')
                                    <div class="p-2.5 bg-blue-50 dark:bg-blue-950/30 rounded-xl border border-blue-200 dark:border-blue-900/40 text-[10px] text-blue-700 dark:text-blue-400 font-bold flex items-center gap-2">
                                        <i data-lucide="clock" class="w-4 h-4 flex-shrink-0"></i>
                                        <span>Berkas sedang dalam antrean verifikasi panitia.</span>
                                    </div>
                                @elseif($status === 'verified')
                                    <div class="p-2.5 bg-teal-50 dark:bg-teal-950/30 rounded-xl border border-teal-200 dark:border-teal-900/40 text-[10px] text-teal-700 dark:text-teal-400 font-bold flex items-center gap-2">
                                        <i data-lucide="calendar-check" class="w-4 h-4 flex-shrink-0"></i>
                                        <span>Berkas lolos verifikasi! Bersiap untuk Ta'aruf.</span>
                                    </div>
                                @elseif($status === 'taaruf_completed')
                                    <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/30 rounded-xl border border-indigo-200 dark:border-indigo-900/40 text-[10px] text-indigo-700 dark:text-indigo-400 font-bold flex items-center gap-2">
                                        <i data-lucide="file-check" class="w-4 h-4 flex-shrink-0"></i>
                                        <span>Ta'aruf selesai. Silakan isi surat pernyataan.</span>
                                    </div>
                                @elseif($status === 'agreement_signed')
                                    <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/30 rounded-xl border border-emerald-200 dark:border-emerald-900/40 text-[10px] text-emerald-800 dark:text-emerald-300 font-bold flex items-center gap-2">
                                        <i data-lucide="receipt" class="w-4 h-4 text-emerald-600 flex-shrink-0"></i>
                                        <span>Tahap Pelunasan Administrasi & Daftar Ulang. Selesaikan pembayaran biaya masuk.</span>
                                    </div>
                                @elseif($status === 'completed')
                                    <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/30 rounded-xl border border-emerald-200 dark:border-emerald-900/40 text-[10px] text-emerald-800 dark:text-emerald-300 font-bold flex items-center gap-2">
                                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 flex-shrink-0"></i>
                                        <span>Alhamdulillah Pendaftaran Ananda dinyatakan Selesai & Diterima</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        @php
                            $stageTargetUrl = route('dashboard.detail', $reg->id);
                            $stageButtonLabel = 'Lihat Detail Pendaftaran';

                            if (!$isPaid) {
                                $stageTargetUrl = route('dashboard.payment', $reg->id);
                                $stageButtonLabel = 'Bayar Biaya Pendaftaran';
                            } elseif ($status === 'draft') {
                                $stageTargetUrl = route('dashboard.form', $reg->id);
                                $stageButtonLabel = 'Lengkapi Formulir Pendaftaran';
                            } elseif ($status === 'submitted') {
                                $stageTargetUrl = route('dashboard.verification', $reg->id);
                                $stageButtonLabel = 'Lihat Status Verifikasi Berkas';
                            } elseif ($status === 'verified') {
                                $stageTargetUrl = route('dashboard.observation', $reg->id);
                                $stageButtonLabel = 'Informasi & Jadwal Ta\'aruf';
                            } elseif ($status === 'taaruf_completed') {
                                $stageTargetUrl = route('dashboard.observation', $reg->id);
                                $stageButtonLabel = 'Isi Surat Pernyataan';
                            } elseif ($status === 'agreement_signed') {
                                $stageTargetUrl = route('dashboard.result', $reg->id);
                                $stageButtonLabel = 'Pelunasan Administrasi & Daftar Ulang';
                            }
                        @endphp

                        <!-- Main Action Button (Hanya tampil jika belum selesai/diterima) -->
                        @if($status !== 'completed')
                            <div class="pt-2 flex flex-col gap-2">
                                <a href="{{ $stageTargetUrl }}" class="w-full py-3.5 px-4 {{ !$isPaid ? 'bg-brand-emerald hover:bg-emerald-600 shadow-md ring-2 ring-emerald-500/30' : 'bg-slate-900 hover:bg-emerald-600 dark:bg-slate-800 dark:hover:bg-emerald-600 shadow-sm' }} text-white text-xs font-black rounded-2xl transition-all duration-200 flex items-center justify-center gap-2 group-hover:shadow-md">
                                    @if(!$isPaid)
                                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                                    @endif
                                    <span>{{ $stageButtonLabel }}</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                                </a>
                                @if(!$isPaid)
                                    <form method="POST" action="{{ route('dashboard.registration.draft.delete', $reg->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan draf pendaftaran ananda {{ $reg->candidate_name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-rose-50 hover:border-rose-200 hover:text-rose-600 dark:hover:bg-rose-950/40 text-slate-500 dark:text-slate-400 text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            <span>Batalkan Draf Pendaftaran</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Section: Bantuan Konsultasi & Layanan Informasi Panitia -->
    @php
        $csPhone = \App\Models\Setting::get('spmb_cs_whatsapp', '081234567890');
        $cleanCsPhone = preg_replace('/[^0-9]/', '', $csPhone);
        if (str_starts_with($cleanCsPhone, '0')) {
            $cleanCsPhone = '62' . substr($cleanCsPhone, 1);
        } elseif (!str_starts_with($cleanCsPhone, '62')) {
            $cleanCsPhone = '62' . $cleanCsPhone;
        }
        $csMsg = \App\Models\Setting::get('spmb_cs_message', 'Halo Panitia SPMB Sekolah Anak Saleh, saya ingin berkonsultasi mengenai pendaftaran murid baru.');
        $csTitle = \App\Models\Setting::get('spmb_cs_card_title', 'Pusat Bantuan & Konsultasi SPMB');
        $csDesc = \App\Models\Setting::get('spmb_cs_card_desc', 'Ada pertanyaan seputar persyaratan atau alur masuk? Tim panitia siap melayani Anda.');
        $csWaUrl = "https://wa.me/{$cleanCsPhone}?text=" . urlencode($csMsg);
    @endphp
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-150/80 dark:border-slate-800 shadow-sm max-w-2xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-left">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                <i data-lucide="headphones" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-extrabold text-slate-850 dark:text-white text-xs">{{ $csTitle }}</h4>
                <p class="text-xs text-slate-400 dark:text-slate-500">{{ $csDesc }}</p>
            </div>
        </div>
        <a href="{{ $csWaUrl }}" target="_blank" class="whitespace-nowrap px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-sm flex-shrink-0">
            <i data-lucide="message-square" class="w-4 h-4"></i> Konsultasi via WA
        </a>
    </div>

</div>

<!-- Modal Pendaftaran Baru (Clean Single-Selector) -->
<div id="newRegistrationModal" onclick="closeRegistrationModal()" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-slate-950/60 backdrop-blur-md opacity-0 pointer-events-none transition-all duration-200 overflow-y-auto overscroll-contain">
    <div class="bg-white dark:bg-slate-900 w-full max-w-lg flex flex-col rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-800 transform scale-95 transition-all duration-200 max-h-[calc(100dvh-1.5rem)] sm:max-h-[90vh] my-auto overflow-hidden" id="registrationModalBody" onclick="event.stopPropagation()">
        
        <!-- Header -->
        <div class="px-5 sm:px-6 py-4 sm:py-5 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/30 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-200/60 dark:border-emerald-800/50 shrink-0 shadow-xs">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-slate-850 dark:text-white tracking-tight">Mulai Pendaftaran Ananda</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                        Unit: <span id="modalHeaderUnitName" class="font-bold text-emerald-600 dark:text-emerald-400">PAUD Terpadu Anak Saleh</span>
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeRegistrationModal()" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('dashboard.registration.create') }}" class="flex flex-col flex-1 min-h-0 overflow-hidden" id="newRegistrationForm">
            @csrf
            
            <input type="hidden" name="spmb_unit_id" id="hiddenUnitInput">
            <input type="hidden" name="spmb_grade_id" id="hiddenGradeIdInput">
            <input type="hidden" name="include_tpa" id="hiddenIncludeTpaInput" value="0">
            <select id="unitSelect" style="display: none;">
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}" data-name="{{ $unit->name }}" data-code="{{ $unit->code }}">{{ $unit->name }}</option>
                @endforeach
            </select>

            <!-- Body (Scrollable with overscroll-contain & touch scrolling) -->
            <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1 min-h-0 overscroll-contain touch-pan-y" style="-webkit-overflow-scrolling: touch;">
                <!-- Baris 1: Nama Calon Murid -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <i data-lucide="user" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                        Nama Calon Murid (Sesuai Akte)
                    </label>
                    <input type="text" name="candidate_name" required class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-xl px-4 py-3 text-slate-850 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all" placeholder="Masukkan nama lengkap anak Anda">
                </div>

                <!-- Baris 2: Tahun Ajaran & Kategori Murid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Tahun Ajaran -->
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                            Tahun Ajaran
                        </label>
                        <select id="periodSelect" name="spmb_period_id" required class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-xl px-4 py-3 text-slate-850 dark:text-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all truncate">
                            <option value="">Pilih Tahun Ajaran...</option>
                            @foreach($periods as $period)
                                <option value="{{ $period->id }}">{{ $period->year ? 'TA ' . $period->year : ($period->name ?: 'Tahun Ajaran ' . $period->id) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kategori Murid -->
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i data-lucide="users" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                            Kategori Murid
                        </label>
                        <select id="classProgramSelect" name="spmb_class_program_id" required class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-xl px-4 py-3 text-slate-850 dark:text-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all truncate">
                            <option value="">Pilih Kategori Murid...</option>
                            @foreach($classPrograms as $program)
                                <option value="{{ $program->id }}">{{ $program->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Baris 3: Jalur Masuk & Gelombang -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i data-lucide="compass" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                            Jalur Masuk
                        </label>
                        <select id="typeSelect" name="spmb_type_id" required class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-xl px-4 py-3 text-slate-850 dark:text-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all truncate">
                            <option value="">Pilih Jalur Masuk...</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" data-name="{{ $type->name }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i data-lucide="layers" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                            Gelombang
                        </label>
                        <select id="waveSelect" name="spmb_wave_id" required class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-xl px-4 py-3 text-slate-850 dark:text-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all truncate">
                            <option value="">Pilih Gelombang...</option>
                            @foreach($waves as $wave)
                                <option value="{{ $wave->id }}">{{ $wave->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Baris 4: Pilihan Layanan & Tingkatan Kelas (Multi-Sub-Unit / Layanan) -->
                <div id="subUnitSection" class="space-y-3 hidden">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Dropdown 1: Pilihan Layanan / Sub-Unit -->
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                                Pilihan Layanan
                            </label>
                            <select id="subUnitSelect" onchange="handleSubUnitChange()" class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-xl px-4 py-3 text-slate-850 dark:text-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all truncate">
                            </select>
                        </div>

                        <!-- Dropdown 2: Tingkatan Kelas -->
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                                    Tingkatan / Kelas
                                </span>
                            </label>
                            <select id="subUnitGradeSelect" onchange="handleSubUnitSelectionChange()" class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-xl px-4 py-3 text-slate-850 dark:text-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all truncate">
                            </select>
                        </div>
                    </div>

                    <!-- Dynamic Small Age Info Helper -->
                    <div id="subUnitAgeInfoBadge" class="text-[11px] text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5 font-medium bg-emerald-50/60 dark:bg-emerald-950/30 px-3 py-1.5 rounded-xl border border-emerald-200/50 dark:border-emerald-800/40">
                        <i data-lucide="info" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i>
                        <span id="subUnitAgeInfoText">Ketentuan usia sesuai jenjang pendidikan.</span>
                    </div>

                    <!-- Checkbox Layanan Daycare (muncul jika memilih Playgroup atau TK) -->
                    <div id="daycareAddonWrapper" class="pt-0.5 hidden">
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border border-emerald-200/80 dark:border-emerald-800/70 bg-emerald-50/50 dark:bg-emerald-950/30 cursor-pointer shadow-2xs hover:bg-emerald-100/50 transition">
                            <input type="checkbox" id="daycareAddonCheckbox" onchange="handleSubUnitSelectionChange()" class="w-4 h-4 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex-1">
                                <span id="daycareAddonCheckboxLabel">+ Tambah Layanan Daycare (TPA)</span>
                                <span class="block text-[10px] text-slate-400 font-normal">+ Biaya pendaftaran Daycare / TPA Rp 300.000</span>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Non-PAUD Standard Grade Selector (SD / SMP) -->
                <div id="standardGradeSelectWrapper" class="hidden">
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                        Tingkatan / Kelas
                    </label>
                    <select id="gradeSelect" onchange="handleStandardGradeChange()" class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-xl px-4 py-3 text-slate-850 dark:text-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <option value="">Pilih Tingkatan...</option>
                    </select>
                </div>

                <!-- Banner Informasi Khusus Putra/Putri Guru & Karyawan YPAS -->
                <div id="teacherChildNoticeBanner" class="p-4 bg-amber-50/90 dark:bg-amber-950/40 rounded-2xl border border-amber-200 dark:border-amber-800/60 space-y-2 hidden">
                    <div class="flex items-start gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 flex items-center justify-center shrink-0 mt-0.5">
                            <i data-lucide="shield-alert" class="w-4 h-4"></i>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-xs font-extrabold text-amber-900 dark:text-amber-300">Konfirmasi Admin SPMB Diperlukan</h4>
                            <p class="text-[11px] text-amber-800 dark:text-amber-400 leading-relaxed font-medium">
                                Jalur Khusus Putra/Putri Guru & Karyawan YPAS memerlukan verifikasi identitas kepegawaian oleh Admin SPMB. Setelah klik <strong>Buat Pendaftaran</strong>, Anda akan diarahkan untuk menghubungi Admin SPMB Unit terkait guna konfirmasi & aktivasi formulir pendaftaran.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Live Fee Calculation Preview Banner -->
                <div id="modalFeePreviewBanner" class="p-3.5 bg-emerald-50/70 dark:bg-emerald-950/40 rounded-2xl border border-emerald-200/80 dark:border-emerald-800/60 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                            <i data-lucide="receipt" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span id="previewFeeBreakdown" class="text-xs font-bold text-slate-700 dark:text-slate-300">Enrollment Fee</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span id="previewFeeTotal" class="text-base font-black text-brand-emerald dark:text-emerald-400 font-mono">Rp 300.000</span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-5 sm:px-6 py-3.5 sm:py-4 border-t border-slate-100 dark:border-slate-800/80 bg-slate-50/70 dark:bg-slate-800/40 flex items-center justify-end gap-3 shrink-0">
                <button type="button" onclick="closeRegistrationModal()" class="px-4 sm:px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-700/50 transition-all">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] transition-all shadow-md shadow-emerald-600/20 flex items-center gap-2">
                    <span>Buat Pendaftaran</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Master data untuk dynamic dependency
    var gradesData = @json($grades);
    var unitsData = @json($units);
    var typesData = @json($types);
    var wavesData = @json($waves);
    var periodsData = @json($periods);
    var classProgramsData = @json($classPrograms);
    var extraServicesData = @json($extraServices ?? []);
    var activePeriodData = @json($activePeriod);
    var unitFeeMap = @json($unitFeeMap ?? []);
    var registrationFeesData = @json($registrationFees ?? []);

    function getRegistrationFeeForGrade(unitId, gradeId, typeId, classProgramId) {
        if (!unitId) return null;
        
        if (registrationFeesData && registrationFeesData.length > 0) {
            const unitFees = registrationFeesData.filter(f => !f.spmb_unit_id || f.spmb_unit_id == unitId);
            
            if (gradeId) {
                // 1. Match by applicable_grades array
                const exactGradeFee = unitFees.find(f => {
                    if (f.applicable_grades && Array.isArray(f.applicable_grades) && f.applicable_grades.length > 0) {
                        return f.applicable_grades.map(Number).includes(Number(gradeId));
                    }
                    return false;
                });
                if (exactGradeFee) {
                    return {
                        id: exactGradeFee.id,
                        name: exactGradeFee.name,
                        amount: Number(exactGradeFee.amount)
                    };
                }

                // 2. Match by grade name in fee name (e.g. "TPA 1", "TPA 2", "TPA 3", "KB", "TK", "SD", "SMP")
                const gradeObj = gradesData.find(g => g.id == gradeId);
                if (gradeObj && gradeObj.name) {
                    const gName = gradeObj.name.toLowerCase();
                    const nameMatchFee = unitFees.find(f => {
                        const fName = (f.name || '').toLowerCase();
                        if (gName.includes('tpa 1') && fName.includes('tpa 1')) return true;
                        if (gName.includes('tpa 2') && fName.includes('tpa 2')) return true;
                        if (gName.includes('tpa 3') && fName.includes('tpa 3')) return true;
                        if (gName.includes('kb') && (fName.includes('kb') || fName.includes('paud'))) return true;
                        if (gName.includes('tk') && (fName.includes('tk') || fName.includes('paud'))) return true;
                        return false;
                    });
                    if (nameMatchFee) {
                        return {
                            id: nameMatchFee.id,
                            name: nameMatchFee.name,
                            amount: Number(nameMatchFee.amount)
                        };
                    }
                }
            }

            // Fallback for general fee of unit
            const generalFee = unitFees.find(f => !f.applicable_grades || f.applicable_grades.length === 0);
            if (generalFee) {
                return {
                    id: generalFee.id,
                    name: generalFee.name,
                    amount: Number(generalFee.amount)
                };
            }
        }

        // Fallback to unitFeeMap
        if (unitFeeMap && unitFeeMap[unitId]) {
            return {
                id: null,
                name: unitFeeMap[unitId].name,
                amount: Number(unitFeeMap[unitId].amount)
            };
        }

        return {
            id: null,
            name: 'Enrollment Fee',
            amount: 300000
        };
    }

    function openRegistrationModal() {
        const modal = document.getElementById('newRegistrationModal');
        const modalBody = document.getElementById('registrationModalBody');
        if (!modal || !modalBody) return;

        document.body.classList.add('overflow-hidden');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modalBody.classList.remove('scale-95');
        modalBody.classList.add('scale-100');
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    function closeRegistrationModal() {
        const modal = document.getElementById('newRegistrationModal');
        const modalBody = document.getElementById('registrationModalBody');
        if (!modal || !modalBody) return;

        document.body.classList.remove('overflow-hidden');
        modal.classList.add('opacity-0', 'pointer-events-none');
        modalBody.classList.remove('scale-100');
        modalBody.classList.add('scale-95');
    }

    // Support keyboard ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeRegistrationModal();
        }
    });

    function formatGradeAgeLabel(grade) {
        if (!grade) return '';
        const minY = grade.min_age_years;
        const maxY = grade.max_age_years;
        if (minY !== null && minY !== undefined && maxY !== null && maxY !== undefined) {
            return `(${minY}–${maxY} th)`;
        } else if (minY !== null && minY !== undefined) {
            return `(Min. ${minY} th)`;
        }
        return '';
    }

    function formatGradeAgeRequirement(grade, isTeacherTpa = false) {
        if (!grade) return 'Ketentuan usia mengikuti regulasi unit.';
        if (isTeacherTpa || grade.id == 13) {
            return '⭐ Khusus putra/putri Guru & Karyawan YPAS';
        }
        const minY = grade.min_age_years;
        const maxY = grade.max_age_years;
        if (minY !== null && minY !== undefined && maxY !== null && maxY !== undefined) {
            return `Ketentuan usia: Minimal ${minY} tahun (Usia ${minY}–${maxY} tahun per Juli)`;
        } else if (minY !== null && minY !== undefined) {
            return `Ketentuan usia: Minimal ${minY} tahun per Juli`;
        }
        return 'Ketentuan usia sesuai standar jenjang pendidikan.';
    }

    function isGradeEligible(grade, typeId, classProgramId, waveId, periodId) {
        if (!grade) return false;
        
        // Check applicable_types
        if (typeId && Array.isArray(grade.applicable_types) && grade.applicable_types.length > 0) {
            const types = grade.applicable_types.map(Number);
            if (!types.includes(Number(typeId))) return false;
        }
        
        // Check applicable_class_programs
        if (classProgramId && Array.isArray(grade.applicable_class_programs) && grade.applicable_class_programs.length > 0) {
            const progs = grade.applicable_class_programs.map(Number);
            if (!progs.includes(Number(classProgramId))) return false;
        }

        // Check applicable_waves
        if (waveId && Array.isArray(grade.applicable_waves) && grade.applicable_waves.length > 0) {
            const waves = grade.applicable_waves.map(Number);
            if (!waves.includes(Number(waveId))) return false;
        }

        // Check applicable_periods
        if (periodId && Array.isArray(grade.applicable_periods) && grade.applicable_periods.length > 0) {
            const periods = grade.applicable_periods.map(Number);
            if (!periods.includes(Number(periodId))) return false;
        }

        return true;
    }

    function getEligibleDaycareGrade(selectedGradeObj, selectedSubUnitLower, eligibleGrades) {
        if (!eligibleGrades || eligibleGrades.length === 0) return null;

        const activeDaycareGrades = eligibleGrades.filter(g => {
            const su = (g.sub_unit || '').trim().toLowerCase();
            return (su === 'daycare' || su === 'tpa' || su.includes('daycare') || su.includes('tpa') || su.includes('penitipan')) &&
                   (g.is_active === undefined || g.is_active == 1 || g.is_active == true);
        });

        if (activeDaycareGrades.length === 0) {
            return null;
        }

        const gradeName = selectedGradeObj ? (selectedGradeObj.name || '').toLowerCase() : '';

        // If Playgroup (or grade is KB A / KB B)
        if (selectedSubUnitLower.includes('playgroup') || selectedSubUnitLower.includes('kb') || selectedSubUnitLower.includes('bermain') || gradeName.includes('kb')) {
            const tpa2 = activeDaycareGrades.find(g => {
                const n = (g.name || '').toLowerCase();
                return (n.includes('tpa 2') || n === 'tpa 2') && !n.includes('guru') && !n.includes('karyawan');
            });
            if (tpa2) return tpa2;
            
            return activeDaycareGrades.find(g => {
                return g.min_age_years <= 3 && g.max_age_years >= 3 && !g.name.toLowerCase().includes('guru');
            }) || null;
        }

        // If TK (or grade is TK A / TK B)
        if (selectedSubUnitLower.includes('tk') || selectedSubUnitLower.includes('kanak') || gradeName.includes('tk')) {
            const tpa3 = activeDaycareGrades.find(g => {
                const n = (g.name || '').toLowerCase();
                return (n.includes('tpa 3') || n === 'tpa 3') && !n.includes('guru') && !n.includes('karyawan');
            });
            if (tpa3) return tpa3;
            
            return activeDaycareGrades.find(g => {
                return g.min_age_years <= 5 && g.max_age_years >= 5 && !g.name.toLowerCase().includes('guru');
            }) || null;
        }

        return null;
    }

    function handleSubUnitChange(targetGradeId = null) {
        const subUnitSelect = document.getElementById('subUnitSelect');
        const gradeSelect = document.getElementById('subUnitGradeSelect');
        const classProgramSelect = document.getElementById('classProgramSelect');
        const waveSelect = document.getElementById('waveSelect');
        const periodSelect = document.getElementById('periodSelect');
        const typeSelect = document.getElementById('typeSelect');
        const hiddenUnitInput = document.getElementById('hiddenUnitInput');

        if (!subUnitSelect || !gradeSelect) return;

        const unitId = hiddenUnitInput ? hiddenUnitInput.value : '';
        const typeId = typeSelect ? typeSelect.value : null;
        const classProgramId = classProgramSelect ? classProgramSelect.value : null;
        const waveId = waveSelect ? waveSelect.value : null;
        const periodId = periodSelect ? periodSelect.value : null;

        const selectedSubUnit = (subUnitSelect.value || '').trim();
        const selectedSubUnitLower = selectedSubUnit.toLowerCase();

        // Get all active grades for this unit
        const unit = unitsData.find(u => u.id == unitId);
        const allUnitGrades = (unit && unit.grades && unit.grades.length > 0)
            ? unit.grades
            : gradesData.filter(g => g.spmb_unit_id == unitId && (g.is_active === undefined || g.is_active == 1 || g.is_active == true));

        // Filter eligible grades using isGradeEligible
        const eligibleGrades = allUnitGrades.filter(g => isGradeEligible(g, typeId, classProgramId, waveId, periodId));

        // Filter grades for this specific sub_unit
        const matchingGrades = eligibleGrades.filter(g => (g.sub_unit || '').trim().toLowerCase() === selectedSubUnitLower);

        gradeSelect.innerHTML = '';

        matchingGrades.forEach(g => {
            const isGuru = (g.name || '').toLowerCase().includes('guru') || (g.name || '').toLowerCase().includes('karyawan') || g.id == 13;
            const ageLabel = isGuru ? '(0–2 th)' : formatGradeAgeLabel(g);
            const opt = new Option(`${g.name} ${ageLabel}`.trim(), String(g.id));
            gradeSelect.add(opt);
        });

        if (gradeSelect.options.length === 0) {
            gradeSelect.innerHTML = '<option value="">Tidak ada tingkatan yang sesuai kriteria</option>';
            gradeSelect.disabled = true;
        } else {
            gradeSelect.disabled = false;
            let matched = false;
            if (targetGradeId) {
                for (let i = 0; i < gradeSelect.options.length; i++) {
                    if (gradeSelect.options[i].value === String(targetGradeId)) {
                        gradeSelect.selectedIndex = i;
                        matched = true;
                        break;
                    }
                }
            }
            if (!matched && gradeSelect.options.length > 0) {
                gradeSelect.selectedIndex = 0;
            }
        }

        handleSubUnitSelectionChange();
    }

    function handleSubUnitSelectionChange() {
        const subUnitSelect = document.getElementById('subUnitSelect');
        const gradeSelect = document.getElementById('subUnitGradeSelect');
        const addonWrapper = document.getElementById('daycareAddonWrapper');
        const addonLabel = document.getElementById('daycareAddonCheckboxLabel');
        const daycareCheckbox = document.getElementById('daycareAddonCheckbox');
        const classProgramSelect = document.getElementById('classProgramSelect');
        const waveSelect = document.getElementById('waveSelect');
        const periodSelect = document.getElementById('periodSelect');
        const typeSelect = document.getElementById('typeSelect');
        const hiddenGradeIdInput = document.getElementById('hiddenGradeIdInput');
        const hiddenIncludeTpaInput = document.getElementById('hiddenIncludeTpaInput');
        const hiddenUnitInput = document.getElementById('hiddenUnitInput');
        const breakdownElem = document.getElementById('previewFeeBreakdown');
        const totalElem = document.getElementById('previewFeeTotal');
        const ageBadge = document.getElementById('subUnitAgeInfoBadge');
        const ageTextElem = document.getElementById('subUnitAgeInfoText');

        if (!subUnitSelect || !gradeSelect || !hiddenGradeIdInput || !hiddenIncludeTpaInput) return;

        const unitId = hiddenUnitInput ? hiddenUnitInput.value : '';
        const typeId = typeSelect ? typeSelect.value : null;
        const classProgramId = classProgramSelect ? classProgramSelect.value : null;
        const waveId = waveSelect ? waveSelect.value : null;
        const periodId = periodSelect ? periodSelect.value : null;

        // Check if current classProgram is MBK
        const selectedProgramObj = classProgramsData.find(cp => cp.id == classProgramId);
        const isMbk = selectedProgramObj && (
            (selectedProgramObj.name || '').toLowerCase().includes('mbk') || 
            (selectedProgramObj.name || '').toLowerCase().includes('kebutuhan khusus')
        );

        const selectedSubUnit = (subUnitSelect.value || '').trim();
        const selectedSubUnitLower = selectedSubUnit.toLowerCase();

        // Get all active grades for this unit
        const unit = unitsData.find(u => u.id == unitId);
        const allUnitGrades = (unit && unit.grades && unit.grades.length > 0)
            ? unit.grades
            : gradesData.filter(g => g.spmb_unit_id == unitId && (g.is_active === undefined || g.is_active == 1 || g.is_active == true));

        // Filter eligible grades using isGradeEligible
        const eligibleGrades = allUnitGrades.filter(g => isGradeEligible(g, typeId, classProgramId, waveId, periodId));

        const gradeId = gradeSelect.value;
        const selectedGradeObj = gradesData.find(g => g.id == gradeId);

        // Check Daycare Addon eligibility dynamically
        const matchingDaycareGrade = getEligibleDaycareGrade(selectedGradeObj, selectedSubUnitLower, eligibleGrades);

        const baseFeeInfo = getRegistrationFeeForGrade(unitId, gradeId, typeId, classProgramId);
        const baseFeeAmount = baseFeeInfo ? Number(baseFeeInfo.amount) : 300000;
        const baseFeeName = baseFeeInfo ? baseFeeInfo.name : 'Enrollment Fee';

        let tpaAddonAmount = 300000;
        let tpaAddonName = 'Enrollment Fee TPA';
        if (matchingDaycareGrade) {
            const daycareFeeInfo = getRegistrationFeeForGrade(unitId, matchingDaycareGrade.id, typeId, classProgramId);
            if (daycareFeeInfo) {
                tpaAddonAmount = Number(daycareFeeInfo.amount);
                tpaAddonName = daycareFeeInfo.name;
            }
        }

        const daycareSubtextElem = document.getElementById('daycareAddonCheckboxSubtext');
        if (addonWrapper) {
            if (matchingDaycareGrade && !isMbk) {
                addonWrapper.classList.remove('hidden');
                if (addonLabel) {
                    addonLabel.textContent = `+ Tambah Layanan Daycare (${matchingDaycareGrade.name})`;
                }
                if (daycareSubtextElem) {
                    daycareSubtextElem.textContent = `+ Biaya pendaftaran Daycare / TPA Rp ${Number(tpaAddonAmount).toLocaleString('id-ID')}`;
                }
            } else {
                addonWrapper.classList.add('hidden');
                if (daycareCheckbox) daycareCheckbox.checked = false;
            }
        }

        const isIncludeTpa = (matchingDaycareGrade && !isMbk && daycareCheckbox) ? daycareCheckbox.checked : false;

        if (!gradeId) {
            if (ageBadge && ageTextElem) {
                ageBadge.className = 'text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5 font-medium bg-slate-50 dark:bg-slate-800/40 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700';
                ageTextElem.textContent = 'Silakan pilih kriteria/tingkatan yang tersedia.';
            }
            if (breakdownElem && totalElem) {
                breakdownElem.textContent = 'Enrollment Fee';
                totalElem.textContent = 'Rp 0';
            }
            hiddenGradeIdInput.value = '';
            hiddenIncludeTpaInput.value = '0';
            return;
        }

        hiddenGradeIdInput.value = gradeId;
        hiddenIncludeTpaInput.value = isIncludeTpa ? '1' : '0';

        const isTeacherTpa = selectedGradeObj && (
            selectedGradeObj.id == 13 || 
            (selectedGradeObj.name || '').toLowerCase().includes('guru') || 
            (selectedGradeObj.name || '').toLowerCase().includes('karyawan')
        );

        const teacherNoticeBanner = document.getElementById('teacherChildNoticeBanner');
        const modalFeeBanner = document.getElementById('modalFeePreviewBanner');

        if (isTeacherTpa) {
            if (modalFeeBanner) modalFeeBanner.classList.add('hidden');
            if (teacherNoticeBanner) {
                teacherNoticeBanner.classList.remove('hidden');
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        } else {
            if (teacherNoticeBanner) teacherNoticeBanner.classList.add('hidden');
            if (modalFeeBanner) modalFeeBanner.classList.remove('hidden');
        }

        // Update age badge
        if (ageBadge && ageTextElem) {
            if (isTeacherTpa) {
                ageBadge.className = 'text-[11px] text-amber-800 dark:text-amber-300 flex items-center gap-1.5 font-bold bg-amber-50 dark:bg-amber-950/40 px-3 py-1.5 rounded-xl border border-amber-200 dark:border-amber-800/50';
                ageTextElem.textContent = '⭐ Khusus putra/putri Guru & Karyawan YPAS';
            } else {
                ageBadge.className = 'text-[11px] text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5 font-medium bg-emerald-50/60 dark:bg-emerald-950/30 px-3 py-1.5 rounded-xl border border-emerald-200/50 dark:border-emerald-800/40';
                ageTextElem.textContent = formatGradeAgeRequirement(selectedGradeObj, false);
            }
        }

        const isDaycare = selectedSubUnitLower.includes('daycare') || selectedSubUnitLower.includes('tpa') || selectedSubUnitLower.includes('penitipan');

        // Update fee breakdown & preview
        if (breakdownElem && totalElem) {
            if (isIncludeTpa) {
                const totalFee = baseFeeAmount + tpaAddonAmount;
                const subUnitName = selectedSubUnit || 'Playgroup';
                const daycareGradeName = matchingDaycareGrade ? matchingDaycareGrade.name : 'TPA';
                breakdownElem.textContent = `${subUnitName} + Layanan Daycare (${daycareGradeName})`;
                totalElem.textContent = 'Rp ' + Number(totalFee).toLocaleString('id-ID');
                totalElem.className = 'text-base font-black text-brand-emerald dark:text-emerald-400 font-mono';
            } else if (isDaycare) {
                breakdownElem.textContent = baseFeeName;
                totalElem.textContent = 'Rp ' + Number(baseFeeAmount).toLocaleString('id-ID');
                totalElem.className = 'text-base font-black text-brand-emerald dark:text-emerald-400 font-mono';
            } else {
                breakdownElem.textContent = baseFeeName;
                totalElem.textContent = 'Rp ' + Number(baseFeeAmount).toLocaleString('id-ID');
                totalElem.className = 'text-base font-black text-brand-emerald dark:text-emerald-400 font-mono';
            }
        }
    }

    function handleStandardGradeChange() {
        const gradeSelect = document.getElementById('gradeSelect');
        const hiddenGradeIdInput = document.getElementById('hiddenGradeIdInput');
        const hiddenIncludeTpaInput = document.getElementById('hiddenIncludeTpaInput');
        const hiddenUnitInput = document.getElementById('hiddenUnitInput');
        const breakdownElem = document.getElementById('previewFeeBreakdown');
        const totalElem = document.getElementById('previewFeeTotal');

        if (!gradeSelect || !hiddenGradeIdInput) return;

        hiddenGradeIdInput.value = gradeSelect.value;
        hiddenIncludeTpaInput.value = '0';

        const unitId = hiddenUnitInput ? hiddenUnitInput.value : '';
        const gradeId = gradeSelect.value;
        const typeSelect = document.getElementById('typeSelect');
        const classProgramSelect = document.getElementById('classProgramSelect');
        const typeId = typeSelect ? typeSelect.value : null;
        const classProgramId = classProgramSelect ? classProgramSelect.value : null;

        const feeInfo = getRegistrationFeeForGrade(unitId, gradeId, typeId, classProgramId);
        let feeName = feeInfo ? feeInfo.name : 'Enrollment Fee';
        let feeAmount = feeInfo ? Number(feeInfo.amount) : 350000;

        if (breakdownElem && totalElem) {
            breakdownElem.textContent = feeName;
            totalElem.textContent = 'Rp ' + Number(feeAmount).toLocaleString('id-ID');
            totalElem.className = 'text-base font-black text-brand-emerald dark:text-emerald-400 font-mono';
        }
    }

    function populateUnitOptions(unitId, targetGradeId = null) {
        const unit = unitsData.find(u => u.id == unitId);
        const unitCode = unit ? (unit.code || '').toUpperCase() : '';
        
        // Update header modal info
        const headerUnit = document.getElementById('modalHeaderUnitName');
        if (headerUnit) headerUnit.textContent = unit ? unit.name : 'Sekolah Anak Saleh';

        // 1. Populate Tahun Ajaran (Periods)
        const periodSelect = document.getElementById('periodSelect');
        if (periodSelect) {
            periodSelect.innerHTML = '<option value="">Pilih Tahun Ajaran...</option>';
            const availablePeriods = (unit && unit.periods && unit.periods.length > 0) ? unit.periods : periodsData;
            availablePeriods.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.year ? `TA ${p.year}` : (p.name || `Tahun Ajaran ${p.id}`);
                periodSelect.appendChild(opt);
            });
            if (availablePeriods.length > 0) {
                periodSelect.selectedIndex = 1;
            }
        }

        // 2. Populate Kategori Murid (Class Programs)
        const classProgramSelect = document.getElementById('classProgramSelect');
        if (classProgramSelect) {
            classProgramSelect.innerHTML = '<option value="">Pilih Kategori Murid...</option>';
            const availablePrograms = (unit && unit.class_programs && unit.class_programs.length > 0) 
                ? unit.class_programs 
                : ((unit && unit.classPrograms && unit.classPrograms.length > 0) ? unit.classPrograms : classProgramsData);
            availablePrograms.forEach(cp => {
                const opt = document.createElement('option');
                opt.value = cp.id;
                opt.textContent = cp.name;
                classProgramSelect.appendChild(opt);
            });
            if (availablePrograms.length > 0) {
                classProgramSelect.selectedIndex = 1;
            }
        }

        // 3. Populate Jalur (Types)
        const typeSelect = document.getElementById('typeSelect');
        if (typeSelect) {
            typeSelect.innerHTML = '<option value="">Pilih Jalur Masuk...</option>';
            const availableTypes = (unit && unit.types && unit.types.length > 0) ? unit.types : typesData;
            availableTypes.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.name;
                opt.setAttribute('data-name', t.name);
                typeSelect.appendChild(opt);
            });
            if (availableTypes.length > 0) {
                typeSelect.selectedIndex = 1;
            }
        }

        // 4. Populate Gelombang (Waves)
        const waveSelect = document.getElementById('waveSelect');
        if (waveSelect) {
            waveSelect.innerHTML = '<option value="">Pilih Gelombang...</option>';
            const availableWaves = (unit && unit.waves && unit.waves.length > 0) ? unit.waves : wavesData;
            availableWaves.forEach(w => {
                const opt = document.createElement('option');
                opt.value = w.id;
                opt.textContent = w.name;
                waveSelect.appendChild(opt);
            });
            if (availableWaves.length > 0) {
                waveSelect.selectedIndex = 1;
            }
        }

        // 5. Check if Unit has Sub-Units
        const unitGrades = (unit && unit.grades && unit.grades.length > 0)
            ? unit.grades
            : gradesData.filter(g => g.spmb_unit_id == unitId && (g.is_active === undefined || g.is_active == 1 || g.is_active == true));

        const subUnits = [];
        unitGrades.forEach(g => {
            if (g.sub_unit && g.sub_unit.trim() !== '' && (g.is_active === undefined || g.is_active == 1 || g.is_active == true) && !subUnits.includes(g.sub_unit.trim())) {
                subUnits.push(g.sub_unit.trim());
            }
        });

        const subUnitSection = document.getElementById('subUnitSection');
        const standardGradeSelectWrapper = document.getElementById('standardGradeSelectWrapper');
        const subUnitSelect = document.getElementById('subUnitSelect');

        if (subUnits.length > 0) {
            // Multi-tier / Sub-unit unit (e.g. PG-TK-DAYCARE)
            if (subUnitSection) subUnitSection.classList.remove('hidden');
            if (standardGradeSelectWrapper) standardGradeSelectWrapper.classList.add('hidden');

            if (subUnitSelect) {
                subUnitSelect.innerHTML = '';
                subUnits.forEach(su => {
                    const opt = new Option(su, su);
                    subUnitSelect.add(opt);
                });

                if (targetGradeId) {
                    const targetGrade = unitGrades.find(g => g.id == targetGradeId);
                    if (targetGrade && targetGrade.sub_unit) {
                        subUnitSelect.value = targetGrade.sub_unit.trim();
                    }
                }
            }
            handleSubUnitChange(targetGradeId);
        } else {
            // Single-tier unit (e.g. SD, SMP)
            if (subUnitSection) subUnitSection.classList.add('hidden');
            if (standardGradeSelectWrapper) standardGradeSelectWrapper.classList.remove('hidden');

            const selectedTypeId = typeSelect ? typeSelect.value : null;
            updateStandardGrades(unitId, selectedTypeId, targetGradeId);
        }
    }

    function updateStandardGrades(unitId, selectedTypeId, targetGradeId = null) {
        const unit = unitsData.find(u => u.id == unitId);
        const unitCode = unit ? (unit.code || '').toUpperCase() : '';
        const gradeSelect = document.getElementById('gradeSelect');
        if (!gradeSelect) return;

        const classProgramSelect = document.getElementById('classProgramSelect');
        const waveSelect = document.getElementById('waveSelect');
        const periodSelect = document.getElementById('periodSelect');

        const typeId = selectedTypeId || (document.getElementById('typeSelect') ? document.getElementById('typeSelect').value : null);
        const classProgramId = classProgramSelect ? classProgramSelect.value : null;
        const waveId = waveSelect ? waveSelect.value : null;
        const periodId = periodSelect ? periodSelect.value : null;

        gradeSelect.innerHTML = '<option value="">Pilih Tingkatan...</option>';
        let allUnitGrades = (unit && unit.grades) ? unit.grades : gradesData.filter(g => g.spmb_unit_id == unitId && (g.is_active === undefined || g.is_active == 1 || g.is_active == true));
        
        // Filter with isGradeEligible
        let availableGrades = allUnitGrades.filter(g => isGradeEligible(g, typeId, classProgramId, waveId, periodId));

        // Fallback if dynamic targeting is completely unset for all unit grades
        const anyConfigured = allUnitGrades.some(g => Array.isArray(g.applicable_types) && g.applicable_types.length > 0);
        if (!anyConfigured && typeId) {
            const typeObj = typesData.find(t => t.id == typeId) || ((unit && unit.types) ? unit.types.find(t => t.id == typeId) : null);
            const typeName = typeObj ? (typeObj.name || '').toLowerCase() : '';
            const isTransfer = typeName.includes('mutasi') || typeName.includes('pindah');
            if (!isTransfer) {
                if (unitCode === 'SD') {
                    availableGrades = availableGrades.filter(g => g.name.toLowerCase().includes('kelas 1') || g.name.trim() === '1');
                } else if (unitCode === 'SMP') {
                    availableGrades = availableGrades.filter(g => g.name.toLowerCase().includes('kelas 7') || g.name.trim() === '7');
                }
            }
        }

        availableGrades.forEach(g => {
            const opt = document.createElement('option');
            opt.value = g.id;
            opt.textContent = g.name;
            gradeSelect.appendChild(opt);
        });

        if (availableGrades.length > 0) {
            gradeSelect.disabled = false;
            if (targetGradeId && availableGrades.some(g => g.id == targetGradeId)) {
                gradeSelect.value = targetGradeId;
            } else {
                gradeSelect.selectedIndex = 1;
            }
        } else {
            gradeSelect.innerHTML = '<option value="">Tidak ada tingkatan yang sesuai kriteria</option>';
            gradeSelect.disabled = true;
        }
        handleStandardGradeChange();
    }

    function startRegistrationWithUnit(unitId, gradeId) {
        openRegistrationModal();
        
        // Update hidden input dan tampilan unit
        const hiddenInput = document.getElementById('hiddenUnitInput');
        hiddenInput.value = unitId;
        
        const unitSelect = document.getElementById('unitSelect');
        unitSelect.value = unitId;
        
        // Populate unit scoped options (types, waves, period, grades)
        populateUnitOptions(unitId, gradeId);
    }
    
    // Dynamic refresh when criteria dropdowns change
    function triggerGradeRefresh() {
        const hiddenUnitInput = document.getElementById('hiddenUnitInput');
        const unitId = hiddenUnitInput ? hiddenUnitInput.value : '';
        const unit = unitsData.find(u => u.id == unitId);
        const unitGrades = (unit && unit.grades && unit.grades.length > 0)
            ? unit.grades
            : gradesData.filter(g => g.spmb_unit_id == unitId && (g.is_active === undefined || g.is_active == 1 || g.is_active == true));
        const hasSubUnits = unitGrades.some(g => g.sub_unit && g.sub_unit.trim() !== '');

        if (hasSubUnits) {
            const currentGradeVal = document.getElementById('subUnitGradeSelect') ? document.getElementById('subUnitGradeSelect').value : null;
            handleSubUnitChange(currentGradeVal);
        } else {
            const currentGradeVal = document.getElementById('gradeSelect') ? document.getElementById('gradeSelect').value : null;
            updateStandardGrades(unitId, document.getElementById('typeSelect') ? document.getElementById('typeSelect').value : null, currentGradeVal);
        }
    }

    ['typeSelect', 'classProgramSelect', 'waveSelect', 'periodSelect'].forEach(selectId => {
        const elem = document.getElementById(selectId);
        if (elem) {
            elem.addEventListener('change', triggerGradeRefresh);
        }
    });

    // Listener saat unit diubah
    var unitSelectElem = document.getElementById('unitSelect');
    if (unitSelectElem) {
        unitSelectElem.addEventListener('change', function() {
            populateUnitOptions(this.value);
        });
    }
</script>
@endpush
