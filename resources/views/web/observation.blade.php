@extends('layouts.portal')

@section('title', 'Tes Observasi & Kesanggupan - Portal SPMB')

@section('content')
<style>
    /* Styling to make sure WYSIWYG rich text content renders beautifully and matches Word document margins */
    .agreement-body ol {
        list-style-type: none !important;
        padding-left: 0 !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
        counter-reset: list-0 !important;
    }
    .agreement-body ul {
        list-style-type: disc !important;
        padding-left: 1.5rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }
    .agreement-body li {
        list-style-type: none !important;
        position: relative !important;
        padding-left: 1.5rem !important;
        margin-bottom: 0.4rem !important;
        line-height: 1.65 !important;
        color: #334155 !important;
    }
    .agreement-body li:not([class*="ql-indent"]) {
        counter-increment: list-0 !important;
        counter-reset: list-1 list-2 list-3 list-4 list-5 list-6 list-7 list-8 list-9 !important;
    }
    .agreement-body li:not([class*="ql-indent"])::before {
        content: counter(list-0, decimal) ". " !important;
        position: absolute !important;
        left: 0 !important;
        font-weight: bold !important;
        color: #334155 !important;
    }
    .dark .agreement-body li {
        color: #cbd5e1 !important;
    }
    .dark .agreement-body li:not([class*="ql-indent"])::before {
        color: #cbd5e1 !important;
    }
    /* Render lower-alpha prefixes for ql-indent-1 level list items */
    .agreement-body li.ql-indent-1 {
        list-style-type: none !important;
        position: relative !important;
        padding-left: 1.5rem !important;
        margin-left: 1.5rem !important;
        counter-increment: list-1 !important;
        counter-reset: list-2 list-3 list-4 list-5 list-6 list-7 list-8 list-9 !important;
    }
    .agreement-body li.ql-indent-1::before {
        content: counter(list-1, lower-alpha) ". " !important;
        position: absolute !important;
        left: 0 !important;
        font-weight: normal !important;
        color: #475569 !important;
    }
    .dark .agreement-body li.ql-indent-1::before {
        color: #94a3b8 !important;
    }
    /* Render level-2 nested list items further to the right */
    .agreement-body li.ql-indent-2 {
        list-style-type: none !important;
        position: relative !important;
        padding-left: 1.5rem !important;
        margin-left: 3rem !important;
        counter-increment: list-2 !important;
        counter-reset: list-3 list-4 list-5 list-6 list-7 list-8 list-9 !important;
    }
    .agreement-body li.ql-indent-2::before {
        content: "(" counter(list-2, decimal) ") " !important;
        position: absolute !important;
        left: 0 !important;
        font-weight: normal !important;
        color: #475569 !important;
    }
    .dark .agreement-body li.ql-indent-2::before {
        color: #94a3b8 !important;
    }
    .agreement-body p {
        margin-top: 0.75rem !important;
        margin-bottom: 0.75rem !important;
        line-height: 1.65 !important;
        color: #334155 !important;
    }
    .dark .agreement-body p {
        color: #cbd5e1 !important;
    }
    .dark .agreement-body strong {
        color: #f8fafc !important;
    }
    .metadata-row {
        display: grid !important;
        grid-template-columns: 165px 10px 1fr !important;
        column-gap: 8px !important;
        margin-top: 4px !important;
        margin-bottom: 4px !important;
        line-height: 1.65 !important;
    }
    /* Responsive mobile formatting */
    @media (max-width: 640px) {
        .metadata-row {
            grid-template-columns: 1fr !important;
            row-gap: 1px !important;
            margin-top: 8px !important;
            margin-bottom: 8px !important;
        }
        .metadata-row div:nth-child(2) {
            display: none !important;
        }
    }
</style>

<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
    @php
        $userAllRegs = auth()->check() ? auth()->user()->registrations()->with(['unit', 'grade', 'classProgram'])->where('registration_status', '!=', 'draft')->orWhereHas('payments', function($q) { $q->where('payment_type', 'registration_fee')->where('status', 'success'); })->latest()->get() : collect();
        $otherRegs = $userAllRegs->where('id', '!=', $registration->id);
    @endphp

    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-150/80 dark:border-slate-800 overflow-hidden">
        
        <!-- Header -->
        <div class="bg-brand-emerald text-white p-5 sm:p-6 space-y-3 sm:space-y-4">
            <div class="flex items-center justify-between gap-2.5 w-full">
                <h2 class="font-extrabold text-sm sm:text-lg text-white flex items-center gap-2 leading-tight min-w-0">
                    <i data-lucide="users" class="w-4 h-4 sm:w-5 sm:h-5 text-brand-yellow shrink-0"></i>
                    <span class="truncate sm:whitespace-normal">Observasi & Pernyataan</span>
                </h2>
                
                <div class="shrink-0 self-center sm:self-start pt-0">
                    @if(in_array($registration->registration_status, ['taaruf_completed', 'agreement_signed', 'completed']))
                        <span class="inline-flex items-center gap-1 bg-green-700 text-white font-black text-[9px] sm:text-[10px] uppercase tracking-wider px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border border-green-500 shadow-xs whitespace-nowrap">
                            <i data-lucide="check-circle" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i> Selesai
                        </span>
                    @elseif($registration->registration_status === 'verified')
                        <span class="inline-flex items-center gap-1 bg-amber-600 text-white font-black text-[9px] sm:text-[10px] uppercase tracking-wider px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border border-amber-500 shadow-xs whitespace-nowrap">
                            <i data-lucide="clock" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i> Sesi Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 bg-slate-700 text-white font-black text-[9px] sm:text-[10px] uppercase tracking-wider px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border border-slate-500 shadow-xs whitespace-nowrap">
                            <i data-lucide="lock" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i> Belum Aktif
                        </span>
                    @endif
                </div>
            </div>

            <!-- Full-width subtitle -->
            <p class="text-xs text-brand-yellow/90 font-medium leading-relaxed w-full">Ujian wawancara ta'aruf serta persetujuan komitmen biaya pendidikan.</p>

            <!-- Integrated Candidate Context Info -->
            <div class="bg-black/15 backdrop-blur-md rounded-2xl p-3 sm:p-4 border border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <!-- Left: Avatar + Candidate Details -->
                <div class="flex items-start sm:items-center gap-3 min-w-0">
                    <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl sm:rounded-2xl bg-white/20 text-white font-black text-sm sm:text-base flex items-center justify-center border border-white/20 shadow-inner shrink-0 mt-0.5 sm:mt-0">
                        {{ strtoupper(substr(trim($registration->candidate_name ?? 'A'), 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1 space-y-0.5">
                        <div class="flex items-center justify-between sm:justify-start gap-2">
                            <h4 class="font-extrabold text-sm sm:text-base text-white tracking-tight truncate">
                                {{ $registration->candidate_name ?? 'Calon Siswa' }}
                            </h4>
                            @if($registration->id_label)
                                <span class="sm:hidden text-[10px] font-mono font-bold text-emerald-200 bg-white/15 px-2 py-0.5 rounded-lg border border-white/20 inline-flex items-center gap-1 shadow-xs whitespace-nowrap shrink-0">
                                    <i data-lucide="tag" class="w-3 h-3 text-emerald-300"></i> {{ $registration->id_label }}
                                </span>
                            @endif
                        </div>
                        
                        <p class="text-xs text-emerald-100 font-semibold truncate">
                            <span class="text-emerald-300 font-bold">{{ $registration->unit?->name }}</span> • {{ $registration->grade?->name }} ({{ $registration->classProgram?->name ?? 'Reguler' }})
                        </p>
                        
                        <p class="text-[11px] text-white/75 truncate">
                            Jalur {{ $registration->type?->name ?? '-' }} • {{ $registration->wave?->name ?? '-' }}
                            @if($registration->period?->year)
                                <span class="text-white/50">(TP {{ $registration->period->year }})</span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Right: ID Label Badge (Desktop) & Child Switcher -->
                <div class="flex items-center sm:justify-end gap-2 shrink-0 {{ $otherRegs->isNotEmpty() ? 'border-t sm:border-t-0 pt-2 sm:pt-0 border-white/10' : '' }}">
                    @if($registration->id_label)
                        <span class="hidden sm:inline-flex text-[11px] font-mono font-bold text-emerald-200 bg-white/15 px-2.5 py-1 rounded-xl border border-white/20 items-center gap-1.5 shadow-xs whitespace-nowrap">
                            <i data-lucide="tag" class="w-3.5 h-3.5 text-emerald-300"></i> {{ $registration->id_label }}
                        </span>
                    @endif

                    @if($otherRegs->isNotEmpty())
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @foreach($otherRegs as $other)
                                <a href="{{ route('dashboard.observation', $other->id) }}" 
                                   class="inline-flex items-center gap-1.5 px-2 py-1 rounded-xl bg-white/15 hover:bg-white/25 text-white text-[11px] font-bold transition border border-white/20 shadow-xs"
                                   title="Beralih ke {{ $other->candidate_name }}">
                                    <span>👦 {{ $other->candidate_name }}</span>
                                    <span class="text-[9px] px-1.5 py-0.5 bg-emerald-950/80 rounded-md text-emerald-300 font-extrabold">{{ $other->unit?->code }}</span>
                                    <i data-lucide="arrow-right" class="w-3 h-3 text-emerald-300"></i>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-8">
            
            @if ($registration->registration_status === 'verified')
                @php
                    $isScheduled = !empty($registration->observation_date);
                    $unitTitle = $registration->unit?->taaruf_title ?? 'Sesi Ta\'aruf & Observasi Offline';
                    $defaultLoc = $registration->unit?->taaruf_default_location ?? 'Sekolah Anak Saleh';
                    $instructions = $registration->unit?->taaruf_instructions;
                    $requiredItems = $registration->unit?->taaruf_required_items;
                @endphp
                <!-- 1. State: Verified (Informasi & Jadwal Ta'aruf) -->
                <div class="space-y-6">
                    
                    @if($isScheduled)
                        <!-- Kartu Jadwal Resmi Terjadwal -->
                        <div class="border-2 border-brand-emerald/40 bg-gradient-to-b from-emerald-50/40 to-white dark:from-emerald-950/20 dark:to-slate-900 rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-emerald-100 dark:border-emerald-900/60 pb-5">
                                <div class="flex items-center gap-3.5">
                                    <div class="h-12 w-12 bg-gradient-to-tr from-brand-emerald to-emerald-400 text-white rounded-2xl flex items-center justify-center font-bold text-xl shadow-md shadow-emerald-500/20 flex-shrink-0">
                                        📅
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-black text-slate-800 dark:text-white text-base sm:text-lg">{{ $unitTitle }}</h3>
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold mt-0.5">Undangan Resmi Sesi Tatap Muka di {{ $registration->unit->name }}</p>
                                    </div>
                                </div>
                                <span class="self-start sm:self-auto inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                    <span class="w-2 h-2 rounded-full bg-brand-emerald animate-pulse"></span>
                                    Terjadwal Resmi
                                </span>
                            </div>

                            <!-- Rincian Waktu & Lokasi Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="p-4 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-1 shadow-sm">
                                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Hari & Tanggal Pelaksanaan</span>
                                    <div class="font-extrabold text-slate-800 dark:text-white text-sm sm:text-base flex items-center gap-2">
                                        <i data-lucide="calendar" class="w-4 h-4 text-brand-emerald"></i>
                                        <span>{{ $registration->observation_date->translatedFormat('l, d F Y') }}</span>
                                    </div>
                                </div>

                                <div class="p-4 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-1 shadow-sm">
                                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Waktu / Sesi</span>
                                    <div class="font-extrabold text-slate-800 dark:text-white text-sm sm:text-base flex items-center gap-2">
                                        <i data-lucide="clock" class="w-4 h-4 text-brand-emerald"></i>
                                        <span>{{ $registration->observation_time }}</span>
                                    </div>
                                </div>

                                <div class="p-4 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-1 shadow-sm sm:col-span-2">
                                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Lokasi & Ruangan</span>
                                    <div class="font-bold text-slate-800 dark:text-white text-xs sm:text-sm flex items-start gap-2">
                                        <i data-lucide="map-pin" class="w-4 h-4 text-rose-500 mt-0.5 flex-shrink-0"></i>
                                        <span>{{ $registration->observation_location ?: $defaultLoc }}</span>
                                    </div>
                                </div>

                                @if($registration->observation_interviewer)
                                    <div class="p-4 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-1 shadow-sm sm:col-span-2">
                                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Pewawancara / Tim Penguji</span>
                                        <div class="font-bold text-slate-800 dark:text-white text-xs sm:text-sm flex items-center gap-2">
                                            <i data-lucide="user-check" class="w-4 h-4 text-blue-500"></i>
                                            <span>{{ $registration->observation_interviewer }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if($registration->observation_notes)
                                <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-xs space-y-1">
                                    <div class="font-bold text-amber-800 dark:text-amber-300 flex items-center gap-1.5">
                                        <i data-lucide="info" class="w-4 h-4 text-amber-600"></i>
                                        <span>Catatan Khusus Panitia:</span>
                                    </div>
                                    <p class="text-slate-700 dark:text-slate-300 leading-relaxed pl-5 whitespace-pre-line">{{ $registration->observation_notes }}</p>
                                </div>
                            @endif

                            <!-- Alur & Informasi Tahap Selanjutnya -->
                            <div class="p-4 sm:p-5 rounded-2xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200/80 dark:border-blue-900/50 space-y-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="h-7 w-7 rounded-lg bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="info" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-extrabold text-slate-800 dark:text-white text-xs">Langkah & Tahapan Selanjutnya</h4>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Alur setelah pelaksanaan sesi Ta'aruf & Observasi</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                                    <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-blue-100 dark:border-blue-950/80 space-y-1 shadow-xs">
                                        <div class="flex items-center gap-1.5 font-bold text-slate-700 dark:text-slate-200 text-[11px]">
                                            <span class="w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center text-[9px] font-black flex-shrink-0">1</span>
                                            <span>Hadir Sesuai Jadwal</span>
                                        </div>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                            Hadir 15 menit sebelum sesi dimulai dengan membawa perlengkapan yang diminta.
                                        </p>
                                    </div>

                                    <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-blue-100 dark:border-blue-950/80 space-y-1 shadow-xs">
                                        <div class="flex items-center gap-1.5 font-bold text-slate-700 dark:text-slate-200 text-[11px]">
                                            <span class="w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center text-[9px] font-black flex-shrink-0">2</span>
                                            <span>Validasi Panitia</span>
                                        </div>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                            Setelah sesi tatap muka selesai, panitia akan menyelesaikan status observasi di sistem.
                                        </p>
                                    </div>

                                    <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-blue-100 dark:border-blue-950/80 space-y-1 shadow-xs">
                                        <div class="flex items-center gap-1.5 font-bold text-slate-700 dark:text-slate-200 text-[11px]">
                                            <span class="w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center text-[9px] font-black flex-shrink-0">3</span>
                                            <span>Aktivasi Tahap Lanjutan</span>
                                        </div>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-relaxed">
                                            Formulir <strong class="text-slate-700 dark:text-slate-200">Surat Pernyataan</strong> &amp; menu <strong class="text-slate-700 dark:text-slate-200">Administrasi</strong> otomatis terbuka.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Kartu Menunggu Penjadwalan -->
                        <div class="border border-brand-emerald/30 bg-emerald-50/10 rounded-3xl p-6 sm:p-8 space-y-5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-emerald-100 dark:border-emerald-900/60 pb-5">
                                <div class="flex items-center gap-3.5">
                                    <div class="h-12 w-12 bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-2xl flex items-center justify-center font-bold text-xl flex-shrink-0">
                                        ⏳
                                    </div>
                                    <div>
                                        <h3 class="font-black text-slate-800 dark:text-white text-base">{{ $unitTitle }}</h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold mt-0.5">Tatap Muka di {{ $registration->unit->name }}</p>
                                    </div>
                                </div>
                                <span class="self-start sm:self-auto inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    Menunggu Alokasi Jadwal
                                </span>
                            </div>

                            <div class="bg-white dark:bg-slate-950 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <div>
                                        <span class="text-slate-400 block">Unit Sekolah</span>
                                        <span class="font-bold text-slate-800 dark:text-white">{{ $registration->unit->name }} ({{ $registration->unit->code }})</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block">Tingkat Kelas</span>
                                        <span class="font-bold text-slate-800 dark:text-white">{{ $registration->grade->name }} ({{ $registration->classProgram->name ?? 'Reguler' }})</span>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <span class="text-slate-400 block">No. WhatsApp Orang Tua</span>
                                        <span class="font-bold text-slate-800 dark:text-white">
                                            {{ $registration->parent_phone ?? $registration->father_phone ?? $registration->mother_phone ?? $registration->guardian_phone ?? $registration->user?->phone ?? '-' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-slate-650 dark:text-slate-400 leading-relaxed">
                                Berkas pendaftaran ananda telah diverifikasi oleh panitia. Jadwal tanggal, waktu, serta ruangan pelaksanaan <strong>{{ $unitTitle }}</strong> sedang dialokasikan oleh panitia unit <strong>{{ $registration->unit->name }}</strong>. Rincian jadwal resmi akan langsung tampil otomatis pada kartu di halaman ini.
                            </p>
                        </div>
                    @endif

                    <!-- Ketentuan & Perlengkapan Bawaan Spesifik Unit -->
                    @if($instructions || $requiredItems)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($instructions)
                                <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-5 rounded-2xl text-xs space-y-2">
                                    <h4 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 text-xs uppercase tracking-wider">
                                        <i data-lucide="clipboard-list" class="w-4 h-4 text-brand-emerald"></i>
                                        Ketentuan Kehadiran ({{ $registration->unit->code }}):
                                    </h4>
                                    <div class="text-slate-650 dark:text-slate-400 leading-relaxed whitespace-pre-line pl-1">
                                        {{ $instructions }}
                                    </div>
                                </div>
                            @endif

                            @if($requiredItems)
                                <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-5 rounded-2xl text-xs space-y-2">
                                    <h4 class="font-bold text-slate-800 dark:text-white flex items-center gap-2 text-xs uppercase tracking-wider">
                                        <i data-lucide="briefcase" class="w-4 h-4 text-brand-emerald"></i>
                                        Perlengkapan Wajib Dibawa:
                                    </h4>
                                    <div class="text-slate-650 dark:text-slate-400 leading-relaxed whitespace-pre-line pl-1">
                                        {{ $requiredItems }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

            @elseif ($registration->registration_status === 'taaruf_completed')
                <!-- 2. State: Ta'aruf Completed (Form Pernyataan Kesanggupan Wali Murid) -->
                <div class="space-y-6">
                    <div class="border border-green-200 bg-green-50/10 rounded-2xl p-6 flex gap-3.5 items-start">
                        <span class="inline-flex items-center justify-center h-10 w-10 bg-green-100 text-green-700 rounded-xl flex-shrink-0">
                            <i data-lucide="check" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <h3 class="font-extrabold text-slate-800 dark:text-white text-sm">Observasi / Ta'aruf Selesai</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                Ananda telah menyelesaikan rangkaian ujian observasi kesiapan belajar. Selanjutnya, silakan baca dan setujui Pernyataan Kesanggupan berikut ini untuk melanjutkan ke tahap administrasi keuangan.
                            </p>
                        </div>
                    </div>

                    <form action="{{ route('dashboard.agreement.submit', $registration->id) }}" method="POST" class="space-y-6 border-t border-slate-100 dark:border-slate-800 pt-6">
                        @csrf
                        
                        <div class="space-y-4">
                            <h4 class="font-extrabold text-sm text-slate-800 dark:text-white">Pernyataan Kesanggupan Orang Tua / Wali</h4>
                            <div id="agreement-scrollbox" class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 text-xs text-slate-650 dark:text-slate-350 space-y-3.5 max-h-[500px] overflow-y-auto leading-relaxed">
                                @if($agreementTemplate)
                                    <div class="flex flex-col items-end mb-5 select-none">
                                        <div class="border border-brand-emerald/20 bg-brand-emerald/5 dark:border-emerald-950/40 dark:bg-emerald-950/10 p-2.5 rounded-xl flex flex-col items-center text-center max-w-[280px]">
                                            <span class="bg-brand-emerald/10 text-brand-emerald dark:bg-emerald-950/30 dark:text-emerald-400 px-2.5 py-0.5 rounded-lg font-bold text-[8px] uppercase tracking-wider border border-brand-emerald/15">
                                                Untuk Kalangan Sendiri
                                            </span>
                                            <span class="text-[8px] text-slate-450 dark:text-slate-400 font-semibold mt-1.5 leading-normal">
                                                Dilarang memfoto, mengcopy, dan menyebarluaskan dokumen ini
                                            </span>
                                        </div>
                                    </div>
                                    <p class="font-bold text-center text-slate-800 dark:text-white uppercase tracking-wide border-b border-slate-200 dark:border-slate-800 pb-2 mb-3 whitespace-pre-line">{{ $agreementTemplate->title }}</p>
                                    <div class="space-y-3 agreement-body">
                                        {!! $agreementTemplate->content !!}
                                    </div>

                                    <!-- Dynamic Signature Footer Mockup -->
                                    <div class="border-t border-slate-200 dark:border-slate-800 pt-6 mt-6 select-none text-[10px] text-slate-655 dark:text-slate-400">
                                        <!-- Date row -->
                                        <div class="flex justify-end pr-4">
                                            <p class="font-bold text-slate-750 dark:text-slate-300">{{ $agreementTemplate->place }}, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</p>
                                        </div>
                                    </div>
                                @else
                                    <p class="font-bold text-center text-slate-800 dark:text-white">SURAT PERNYATAAN KESANGGUPAN MEMATUHI PERATURAN & BIAYA PENDIDIKAN</p>
                                    <p>Saya yang bertanda tangan di bawah ini selaku Orang Tua / Wali murid dari calon siswa:</p>
                                    <div class="pl-4 space-y-1 font-semibold">
                                        <p>Nama Calon Siswa : {{ $registration->candidate_name }}</p>
                                        <p>Unit & Program : {{ $registration->unit->name }} - {{ $registration->grade->name }}</p>
                                    </div>
                                    <p>Menyatakan dengan sesungguhnya dan penuh kesadaran bahwa:</p>
                                    <ol class="list-decimal pl-4 space-y-2">
                                        <li><strong>Tata Tertib Sekolah:</strong> Sanggup mematuhi dan mendidik anak kami agar mematuhi seluruh peraturan, disiplin, dan tata tertib akademik maupun non-akademik di lingkungan Yayasan Sekolah Anak Saleh.</li>
                                        <li><strong>Komitmen Pembiayaan:</strong> Menyanggupi pelunasan seluruh biaya administrasi masuk awal (Uang Gedung, Seragam, Uang Kegiatan) sesuai ketentuan unit program yang dipilih, serta membayar SPP bulanan paling lambat tanggal 10 setiap bulannya.</li>
                                        <li><strong>Partisipasi Program:</strong> Bersedia berpartisipasi aktif dalam kegiatan komite sekolah dan mendukung penuh program pembiasaan ibadah harian anak di rumah.</li>
                                    </ol>
                                    <p>Demikian surat pernyataan ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
                                @endif
                            </div>

                            <!-- Scroll warning badge -->
                            <div id="scroll-warning-badge" class="mt-3 py-2 px-3.5 rounded-xl border bg-amber-50 border-amber-200 dark:bg-amber-950/20 dark:border-amber-900/30 text-[11px] text-amber-700 dark:text-amber-400 flex items-center justify-between gap-2.5 transition duration-300">
                                <span class="flex items-center gap-2 font-semibold">
                                    <i data-lucide="info" class="w-3.5 h-3.5 animate-bounce"></i>
                                    <span>Mohon scroll dokumen di atas sampai akhir untuk mengaktifkan persetujuan.</span>
                                </span>
                                <span class="text-[8px] font-bold uppercase bg-amber-100 dark:bg-amber-900/40 px-1.5 py-0.5 rounded shadow-sm">Belum Dibaca</span>
                            </div>
                        </div>

                        <div id="agreement-fields-container" class="space-y-6">
                            <div class="space-y-3">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="agree_rules" class="rounded text-brand-emerald focus:ring-brand-emerald mt-0.5" required>
                                    <span class="text-xs text-slate-650 dark:text-slate-400">
                                        {{ $agreementTemplate->rules_consent_label ?? 'Saya menyetujui seluruh tata tertib dan peraturan akademik Sekolah Anak Saleh.' }}
                                    </span>
                                </label>
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="agree_fees" class="rounded text-brand-emerald focus:ring-brand-emerald mt-0.5" required>
                                    <span class="text-xs text-slate-650 dark:text-slate-400">
                                        {{ $agreementTemplate->fees_consent_label ?? 'Saya menyanggupi pemenuhan seluruh rincian biaya pendidikan dan administrasi masuk yayasan.' }}
                                    </span>
                                </label>
                            </div>

                            <div class="space-y-2">
                                <label for="signature_name" class="block text-xs font-bold text-slate-650 dark:text-slate-350">Nama Lengkap Penandatangan (Orang Tua / Wali)</label>
                                <input type="text" id="signature_name" name="signature_name" value="{{ $registration->father_name ?? ($registration->mother_name ?? '') }}" class="w-full max-w-md rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:text-white" placeholder="Ketik nama lengkap Anda di sini..." required>
                            </div>

                            <div class="pt-4 flex justify-end">
                                <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl font-bold text-xs shadow-md transition flex items-center gap-1.5">
                                    <i data-lucide="check-square" class="w-4 h-4"></i> Setujui & Tandatangani Pernyataan Kesanggupan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            @elseif (in_array($registration->registration_status, ['agreement_signed', 'completed']))
                <!-- 3. State: Agreement Signed / Completed (Readonly signed status) -->
                <div class="space-y-6 text-center py-8 max-w-md mx-auto">
                    <div class="h-16 w-16 bg-green-50 dark:bg-green-950/20 text-green-600 rounded-3xl flex items-center justify-center mx-auto shadow-inner">
                        <i data-lucide="award" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-800 dark:text-white">Pernyataan Kesanggupan Disepakati</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Terima kasih. Anda telah menyetujui Pernyataan Kesanggupan Tata Tertib dan Biaya Masuk Yayasan.
                    </p>
                    <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-4 rounded-xl text-left text-xs space-y-2">
                        <p class="font-bold text-slate-800 dark:text-white text-center border-b border-slate-100 dark:border-slate-800 pb-2">Status Persetujuan Digital</p>
                        <p><strong>Penandatangan:</strong> {{ $registration->signature_name ?? ($registration->father_name ?? ($registration->mother_name ?? $registration->candidate_name)) }} (Orang Tua / Wali)</p>
                        <p><strong>Status:</strong> <span class="text-green-600 font-bold">DISAPAKATI / VALID</span></p>
                        <p><strong>Tanggal:</strong> {{ ($registration->signed_at ?? $registration->updated_at)->format('d M Y H:i') }} WIB</p>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('dashboard.result', $registration->id) }}" class="inline-flex items-center gap-2 bg-brand-emerald hover-emerald text-white px-6 py-2.5 rounded-xl text-xs font-bold shadow-md transition">
                            <span>Lanjutkan ke Administrasi</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>

            @else
                <!-- 4. State: Lock (Belum Terverifikasi) -->
                <div class="text-center py-8 space-y-3">
                    <span class="inline-flex items-center justify-center h-12 w-12 bg-slate-100 dark:bg-slate-800 text-slate-450 rounded-full">
                        <i data-lucide="lock" class="w-6 h-6"></i>
                    </span>
                    <h3 class="font-bold text-slate-800 dark:text-white text-sm">Belum Dibuka</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-450 max-w-sm mx-auto leading-relaxed">
                        Tahapan tes observasi dan penandatanganan kesanggupan hanya akan aktif setelah berkas pendaftaran Anda lolos verifikasi sukses di menu <strong>Verification</strong>.
                    </p>
                </div>
            @endif

        </div>
    </div>
</div>

<script>
     document.addEventListener('DOMContentLoaded', function() {
         const signatureInput = document.getElementById('signature_name');
         
         if (signatureInput) {
             // Function to synchronize signature element with dynamic-signature-name
             const syncParentNames = function(value) {
                 const cleanValue = value.trim();
                 const parentNameElements = document.querySelectorAll('.dynamic-signature-name');
                 parentNameElements.forEach(el => {
                     el.textContent = cleanValue ? cleanValue : '____________________';
                 });
             };
 
             // Run initial sync on load in case field is pre-filled
             syncParentNames(signatureInput.value);
 
             // Listen for changes as user types
             signatureInput.addEventListener('input', function() {
                 syncParentNames(this.value);
             });
         }

         // Scroll to Agree Logic
         const scrollBox = document.getElementById('agreement-scrollbox');
         const fieldsContainer = document.getElementById('agreement-fields-container');
         const warningBadge = document.getElementById('scroll-warning-badge');
         
         if (scrollBox && fieldsContainer && warningBadge) {
             const checkboxes = fieldsContainer.querySelectorAll('input[type="checkbox"]');
             const submitBtn = fieldsContainer.querySelector('button[type="submit"]');

             const disableFields = () => {
                 fieldsContainer.classList.add('opacity-40', 'pointer-events-none');
                 checkboxes.forEach(cb => cb.disabled = true);
                 if (signatureInput) signatureInput.disabled = true;
                 if (submitBtn) {
                     submitBtn.disabled = true;
                     submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                 }
             };

             const enableFields = () => {
                 fieldsContainer.classList.remove('opacity-40', 'pointer-events-none');
                 checkboxes.forEach(cb => cb.disabled = false);
                 if (signatureInput) signatureInput.disabled = false;
                 if (submitBtn) {
                     submitBtn.disabled = false;
                     submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                 }

                 // Update badge to success state
                 warningBadge.className = "mt-3 py-2 px-3.5 rounded-xl border bg-emerald-50 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-900/30 text-[11px] text-emerald-700 dark:text-emerald-400 flex items-center justify-between gap-2.5 transition duration-300";
                 warningBadge.innerHTML = `
                     <span class="flex items-center gap-2 font-semibold">
                         <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                         <span>Terima kasih, dokumen selesai dibaca. Silakan isi form persetujuan di bawah.</span>
                     </span>
                     <span class="text-[8px] font-bold uppercase bg-emerald-100 dark:bg-emerald-900/40 px-1.5 py-0.5 rounded shadow-sm">Selesai Baca</span>
                 `;
                 if (window.lucide) {
                     lucide.createIcons();
                 }
             };

             const checkScroll = () => {
                 // Determine if box has scrollbar
                 const isScrollable = scrollBox.scrollHeight > scrollBox.clientHeight;
                 // If not scrollable (fits on screen) or scrolled near the bottom (within 15px)
                 if (!isScrollable || (scrollBox.scrollTop + scrollBox.clientHeight >= scrollBox.scrollHeight - 15)) {
                     enableFields();
                     scrollBox.removeEventListener('scroll', handleScroll);
                 }
             };

             const handleScroll = () => {
                 checkScroll();
             };

             disableFields();
             
             // Attach scroll listener
             scrollBox.addEventListener('scroll', handleScroll);
             
             // Initial check (after layout calculation)
             setTimeout(checkScroll, 250);
             
             // Recheck on window resize
             window.addEventListener('resize', checkScroll);
         }
     });
 </script>
@endsection
