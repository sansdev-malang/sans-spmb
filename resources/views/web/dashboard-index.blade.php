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
                                    {{ $draft->candidate_name ?? 'Calon Murid' }} — <span class="text-emerald-700 dark:text-emerald-400 font-bold">{{ $draft->unit->name ?? 'Unit Sekolah' }}</span>
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
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Draf Formulir
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
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                    <span class="flex items-center gap-1.5"><i data-lucide="layers" class="w-3.5 h-3.5 text-slate-400"></i> Jenjang / Tingkat</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $reg->grade->name ?? ($reg->admission_level ?: '-') }}</span>
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
                                @if($reg->extraServices && $reg->extraServices->count() > 0)
                                    <div class="flex justify-between items-center text-slate-600 dark:text-slate-400 border-t border-slate-200/60 dark:border-slate-800 pt-1.5 mt-1.5">
                                        <span class="flex items-center gap-1.5"><i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i> Layanan Non-Formal</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200 truncate max-w-[140px]">{{ $reg->extraServices->pluck('name')->implode(', ') }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Stage Next Action Banner / Hint -->
                            <!-- Stage Next Action Banner / Hint -->
                            <div class="mb-4">
                                @if(!$isPaid)
                                    <div class="p-2.5 bg-rose-50 dark:bg-rose-950/30 rounded-xl border border-rose-200 dark:border-rose-900/40 text-[10px] text-rose-700 dark:text-rose-400 font-bold flex items-center gap-2">
                                        <i data-lucide="credit-card" class="w-4 h-4 flex-shrink-0"></i>
                                        <span>Biaya pendaftaran formulir belum diselesaikan.</span>
                                    </div>
                                @elseif($status === 'draft')
                                    <div class="p-2.5 bg-amber-50 dark:bg-amber-950/30 rounded-xl border border-amber-200 dark:border-amber-900/40 text-[10px] text-amber-700 dark:text-amber-400 font-bold flex items-center gap-2">
                                        <i data-lucide="edit-3" class="w-4 h-4 flex-shrink-0"></i>
                                        <span>Formulir & berkas belum dikirim ke panitia.</span>
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
                            <div class="pt-2">
                                <a href="{{ $stageTargetUrl }}" class="w-full py-3.5 px-4 bg-slate-900 hover:bg-emerald-600 dark:bg-slate-800 dark:hover:bg-emerald-600 text-white text-xs font-black rounded-2xl transition-all duration-200 flex items-center justify-center gap-2 shadow-sm group-hover:shadow-md">
                                    <span>{{ $stageButtonLabel }}</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                                </a>
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

                <!-- Baris 4: Pilihan Layanan & Tingkatan Kelas (PAUD 2-Dropdowns) -->
                <div id="paudSection" class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Dropdown 1: Pilihan Layanan PAUD -->
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400"></i>
                                Pilihan Layanan
                            </label>
                            <select id="paudProgramSelect" onchange="handlePaudProgramChange()" class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-xl px-4 py-3 text-slate-850 dark:text-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all truncate">
                                <option value="KB">Kelompok Bermain (KB)</option>
                                <option value="TK">Taman Kanak-Kanak (TK)</option>
                                <option value="TPA">Daycare (TPA)</option>
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
                            <select id="paudGradeSelect" onchange="handlePaudSelectionChange()" class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-xl px-4 py-3 text-slate-850 dark:text-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all truncate">
                            </select>
                        </div>
                    </div>

                    <!-- Dynamic Small Age Info Helper -->
                    <div id="paudAgeInfoBadge" class="text-[11px] text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5 font-medium bg-emerald-50/60 dark:bg-emerald-950/30 px-3 py-1.5 rounded-xl border border-emerald-200/50 dark:border-emerald-800/40">
                        <i data-lucide="info" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i>
                        <span id="paudAgeInfoText">Ketentuan usia: Minimal 2 tahun (2–3 tahun)</span>
                    </div>

                    <!-- Checkbox Layanan Daycare (muncul untuk KB & TK) -->
                    <div id="paudDaycareAddonWrapper" class="pt-0.5">
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border border-emerald-200/80 dark:border-emerald-800/70 bg-emerald-50/50 dark:bg-emerald-950/30 cursor-pointer shadow-2xs hover:bg-emerald-100/50 transition">
                            <input type="checkbox" id="paudDaycareCheckbox" onchange="handlePaudSelectionChange()" class="w-4 h-4 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex-1">
                                <span id="paudDaycareCheckboxLabel">+ Tambah Layanan Daycare (TPA 2)</span>
                                <span class="block text-[10px] text-slate-400 font-normal">+ Biaya pendaftaran TPA Rp 300.000</span>
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
    var activePeriodData = @json($activePeriod);

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

    function handlePaudProgramChange(targetGradeId = null) {
        const progSelect = document.getElementById('paudProgramSelect');
        const gradeSelect = document.getElementById('paudGradeSelect');
        const addonWrapper = document.getElementById('paudDaycareAddonWrapper');
        const addonLabel = document.getElementById('paudDaycareCheckboxLabel');
        const daycareCheckbox = document.getElementById('paudDaycareCheckbox');

        if (!progSelect || !gradeSelect) return;
        const prog = progSelect.value;

        gradeSelect.innerHTML = '';

        if (prog === 'KB') {
            if (addonWrapper) addonWrapper.classList.remove('hidden');
            if (addonLabel) addonLabel.textContent = '+ Tambah Layanan Daycare (TPA 2)';

            const opt1 = new Option('KB-A (2–3 thn)', '1');
            const opt2 = new Option('KB-B (3–4 thn)', '14');
            gradeSelect.add(opt1);
            gradeSelect.add(opt2);
        } else if (prog === 'TK') {
            if (addonWrapper) addonWrapper.classList.remove('hidden');
            if (addonLabel) addonLabel.textContent = '+ Tambah Layanan Daycare (TPA 3)';

            const opt1 = new Option('TK-A (4–5 thn)', '2');
            const opt2 = new Option('TK-B (5–6 thn)', '3');
            gradeSelect.add(opt1);
            gradeSelect.add(opt2);
        } else if (prog === 'TPA') {
            if (addonWrapper) addonWrapper.classList.add('hidden');
            if (daycareCheckbox) daycareCheckbox.checked = false;

            const opt1 = new Option('TPA 1 Guru YPAS (0–2 thn)', '13:0');
            const opt2 = new Option('TPA 1 Umum (1–2 thn)', '15:0');
            const opt3 = new Option('KB-A & TPA 2 (2–3 thn)', '1:1');
            const opt4 = new Option('KB-B & TPA 2 (3–4 thn)', '14:1');
            const opt5 = new Option('TK-A & TPA 3 (4–5 thn)', '2:1');
            const opt6 = new Option('TK-B & TPA 3 (5–6 thn)', '3:1');

            gradeSelect.add(opt1);
            gradeSelect.add(opt2);
            gradeSelect.add(opt3);
            gradeSelect.add(opt4);
            gradeSelect.add(opt5);
            gradeSelect.add(opt6);
        }

        if (targetGradeId) {
            for (let i = 0; i < gradeSelect.options.length; i++) {
                if (gradeSelect.options[i].value === String(targetGradeId) || gradeSelect.options[i].value.startsWith(targetGradeId + ':')) {
                    gradeSelect.selectedIndex = i;
                    break;
                }
            }
        }

        handlePaudSelectionChange();
    }

    function handlePaudSelectionChange() {
        const progSelect = document.getElementById('paudProgramSelect');
        const gradeSelect = document.getElementById('paudGradeSelect');
        const daycareCheckbox = document.getElementById('paudDaycareCheckbox');
        const hiddenGradeIdInput = document.getElementById('hiddenGradeIdInput');
        const hiddenIncludeTpaInput = document.getElementById('hiddenIncludeTpaInput');
        const breakdownElem = document.getElementById('previewFeeBreakdown');
        const totalElem = document.getElementById('previewFeeTotal');
        const ageBadge = document.getElementById('paudAgeInfoBadge');
        const ageTextElem = document.getElementById('paudAgeInfoText');

        if (!progSelect || !gradeSelect || !hiddenGradeIdInput || !hiddenIncludeTpaInput) return;

        const prog = progSelect.value;
        const gradeVal = gradeSelect.value;

        if (prog === 'TPA') {
            const parts = (gradeVal || '13:0').split(':');
            const gradeId = parts[0];
            const includeTpa = parts[1] || '0';

            hiddenGradeIdInput.value = gradeId;
            hiddenIncludeTpaInput.value = includeTpa;

            // Update Dynamic Small Age Info Helper with gold styling for Teacher
            if (ageTextElem && ageBadge) {
                if (gradeId == 13) {
                    ageBadge.className = 'text-[11px] text-amber-800 dark:text-amber-300 flex items-center gap-1.5 font-bold bg-amber-50 dark:bg-amber-950/40 px-3 py-1.5 rounded-xl border border-amber-200 dark:border-amber-800/50';
                    ageTextElem.textContent = '⭐ Khusus putra/putri Guru & Karyawan YPAS (Bebas Biaya / Rp 0)';
                } else if (gradeId == 15) {
                    ageBadge.className = 'text-[11px] text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5 font-medium bg-emerald-50/60 dark:bg-emerald-950/30 px-3 py-1.5 rounded-xl border border-emerald-200/50 dark:border-emerald-800/40';
                    ageTextElem.textContent = 'Ketentuan usia TPA 1 Umum: 1–2 tahun';
                } else if (gradeId == 1) {
                    ageBadge.className = 'text-[11px] text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5 font-medium bg-emerald-50/60 dark:bg-emerald-950/30 px-3 py-1.5 rounded-xl border border-emerald-200/50 dark:border-emerald-800/40';
                    ageTextElem.textContent = 'Ketentuan usia: 2–3 tahun (Kelas KB-A & Layanan TPA 2 Playgroup)';
                } else if (gradeId == 14) {
                    ageBadge.className = 'text-[11px] text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5 font-medium bg-emerald-50/60 dark:bg-emerald-950/30 px-3 py-1.5 rounded-xl border border-emerald-200/50 dark:border-emerald-800/40';
                    ageTextElem.textContent = 'Ketentuan usia: 3–4 tahun (Kelas KB-B & Layanan TPA 2 Playgroup)';
                } else if (gradeId == 2) {
                    ageBadge.className = 'text-[11px] text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5 font-medium bg-emerald-50/60 dark:bg-emerald-950/30 px-3 py-1.5 rounded-xl border border-emerald-200/50 dark:border-emerald-800/40';
                    ageTextElem.textContent = 'Ketentuan usia: 4–5 tahun (Kelas TK-A & Layanan TPA 3 Kindergarten)';
                } else if (gradeId == 3) {
                    ageBadge.className = 'text-[11px] text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5 font-medium bg-emerald-50/60 dark:bg-emerald-950/30 px-3 py-1.5 rounded-xl border border-emerald-200/50 dark:border-emerald-800/40';
                    ageTextElem.textContent = 'Ketentuan usia: 5–6 tahun (Kelas TK-B & Layanan TPA 3 Kindergarten)';
                }
            }

            if (breakdownElem && totalElem) {
                breakdownElem.textContent = 'Enrollment Fee';
                if (gradeId == 13) {
                    totalElem.textContent = 'Rp 0';
                    totalElem.className = 'text-base font-black text-emerald-600 dark:text-emerald-400 font-mono';
                } else if (gradeId == 15) {
                    totalElem.textContent = 'Rp 300.000';
                    totalElem.className = 'text-base font-black text-brand-emerald dark:text-emerald-400 font-mono';
                } else {
                    totalElem.textContent = 'Rp 600.000';
                    totalElem.className = 'text-base font-black text-brand-emerald dark:text-emerald-400 font-mono';
                }
            }
        } else {
            const gradeId = gradeVal || (prog === 'KB' ? '1' : '2');
            const isIncludeTpa = daycareCheckbox ? daycareCheckbox.checked : false;

            hiddenGradeIdInput.value = gradeId;
            hiddenIncludeTpaInput.value = isIncludeTpa ? '1' : '0';

            // Update Dynamic Small Age Info Helper
            if (ageTextElem && ageBadge) {
                ageBadge.className = 'text-[11px] text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5 font-medium bg-emerald-50/60 dark:bg-emerald-950/30 px-3 py-1.5 rounded-xl border border-emerald-200/50 dark:border-emerald-800/40';
                if (prog === 'KB') {
                    if (gradeId == 1) {
                        ageTextElem.textContent = 'Ketentuan usia: Minimal 2 tahun (Usia 2–3 tahun per Juli)';
                    } else {
                        ageTextElem.textContent = 'Ketentuan usia: Minimal 3 tahun (Usia 3–4 tahun per Juli)';
                    }
                } else if (prog === 'TK') {
                    if (gradeId == 2) {
                        ageTextElem.textContent = 'Ketentuan usia: Minimal 4 tahun (Usia 4–5 tahun per Juli)';
                    } else {
                        ageTextElem.textContent = 'Ketentuan usia: Minimal 5 tahun (Usia 5–6 tahun per Juli)';
                    }
                }
            }

            if (breakdownElem && totalElem) {
                breakdownElem.textContent = 'Enrollment Fee';
                if (isIncludeTpa) {
                    totalElem.textContent = 'Rp 600.000';
                    totalElem.className = 'text-base font-black text-brand-emerald dark:text-emerald-400 font-mono';
                } else {
                    totalElem.textContent = 'Rp 300.000';
                    totalElem.className = 'text-base font-black text-brand-emerald dark:text-emerald-400 font-mono';
                }
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
        const unit = unitsData.find(u => u.id == unitId);
        const unitCode = unit ? (unit.code || '').toUpperCase() : '';

        if (breakdownElem && totalElem) {
            breakdownElem.textContent = 'Enrollment Fee';
            if (unitCode === 'SD') {
                totalElem.textContent = 'Rp 350.000';
                totalElem.className = 'text-base font-black text-brand-emerald dark:text-emerald-400 font-mono';
            } else if (unitCode === 'SMP') {
                totalElem.textContent = 'Rp 350.000';
                totalElem.className = 'text-base font-black text-brand-emerald dark:text-emerald-400 font-mono';
            } else {
                totalElem.textContent = 'Rp 300.000';
            }
        }
    }

    function populateUnitOptions(unitId, targetGradeId = null) {
        const unit = unitsData.find(u => u.id == unitId);
        const unitCode = unit ? (unit.code || '').toUpperCase() : '';
        
        // Update header modal info (Unit only, no TA)
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

        // 3. Switch between PAUD Section and Standard Grade Selector
        const paudSection = document.getElementById('paudSection');
        const standardGradeSelectWrapper = document.getElementById('standardGradeSelectWrapper');
        const progSelect = document.getElementById('paudProgramSelect');

        if (unitCode === 'PAUD') {
            if (paudSection) paudSection.classList.remove('hidden');
            if (standardGradeSelectWrapper) standardGradeSelectWrapper.classList.add('hidden');

            if (progSelect) {
                if (targetGradeId == 2 || targetGradeId == 3) {
                    progSelect.value = 'TK';
                } else if (targetGradeId == 13 || targetGradeId == 15) {
                    progSelect.value = 'TPA';
                } else {
                    progSelect.value = 'KB';
                }
            }
            handlePaudProgramChange(targetGradeId);
        } else {
            if (paudSection) paudSection.classList.add('hidden');
            if (standardGradeSelectWrapper) standardGradeSelectWrapper.classList.remove('hidden');

            const gradeSelect = document.getElementById('gradeSelect');
            if (gradeSelect) {
                gradeSelect.innerHTML = '<option value="">Pilih Tingkatan...</option>';
                let availableGrades = (unit && unit.grades) ? unit.grades : gradesData.filter(g => g.spmb_unit_id == unitId && (g.is_active === undefined || g.is_active == 1 || g.is_active == true));
                
                if (unitCode === 'SD') {
                    availableGrades = availableGrades.filter(g => g.name.toLowerCase().includes('kelas 1') || g.name.trim() === '1');
                } else if (unitCode === 'SMP') {
                    availableGrades = availableGrades.filter(g => g.name.toLowerCase().includes('kelas 7') || g.name.trim() === '7');
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
                    gradeSelect.innerHTML = '<option value="">Tidak ada tingkatan aktif</option>';
                    gradeSelect.disabled = true;
                }
            }
            handleStandardGradeChange();
        }
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
    
    // Listener saat jalur diubah
    var typeSelectElem = document.getElementById('typeSelect');
    if (typeSelectElem) {
        typeSelectElem.addEventListener('change', function() {
            const hiddenUnitInput = document.getElementById('hiddenUnitInput');
            const unitId = hiddenUnitInput ? hiddenUnitInput.value : '';
            const unit = unitsData.find(u => u.id == unitId);
            const unitCode = unit ? (unit.code || '').toUpperCase() : '';
            if (unitCode !== 'PAUD') {
                populateUnitOptions(unitId);
            }
        });
    }

    // Listener saat unit diubah
    var unitSelectElem = document.getElementById('unitSelect');
    if (unitSelectElem) {
        unitSelectElem.addEventListener('change', function() {
            populateUnitOptions(this.value);
        });
    }
</script>
@endpush
