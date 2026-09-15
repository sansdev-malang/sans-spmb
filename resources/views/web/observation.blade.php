@extends('layouts.portal')

@section('title', 'Assessment / Ta\'aruf - Portal SPMB')

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
    .agreement-body ol ol {
        counter-reset: list-1 !important;
        padding-left: 0.5rem !important;
        margin-top: 0.35rem !important;
        margin-bottom: 0.5rem !important;
    }
    .agreement-body ol ol ol {
        counter-reset: list-2 !important;
        padding-left: 0.5rem !important;
    }
    .agreement-body ul {
        list-style-type: none !important;
        padding-left: 0.5rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }
    .agreement-body li {
        list-style-type: none !important;
        position: relative !important;
        padding-left: 1.75rem !important;
        margin-bottom: 0.45rem !important;
        line-height: 1.65 !important;
        color: #334155 !important;
    }

    /* Top-level list items (Level 0) -> 1., 2., 3. */
    .agreement-body > ol > li,
    .agreement-body ol:not(ol ol) > li:not([class*="ql-indent"]):not(ol ol li) {
        counter-increment: list-0 !important;
        counter-set: list-1 0 list-2 0 !important;
        counter-reset: list-1 !important;
    }
    .agreement-body > ol > li::before,
    .agreement-body ol:not(ol ol) > li:not([class*="ql-indent"]):not(ol ol li)::before {
        content: counter(list-0, decimal) ". " !important;
        position: absolute !important;
        left: 0 !important;
        font-weight: bold !important;
        color: #334155 !important;
    }

    /* Nested level-1 list items (<ol><ol> > <li> OR Quill .ql-indent-1) -> a., b., c., d. */
    .agreement-body ol ol > li:not(ol ol ol li):not([class*="ql-indent"]),
    .agreement-body li.ql-indent-1 {
        counter-increment: list-1 !important;
        counter-set: list-2 0 !important;
        counter-reset: list-2 !important;
        padding-left: 1.75rem !important;
        margin-left: 1.25rem !important;
    }
    .agreement-body ol ol > li:not(ol ol ol li):not([class*="ql-indent"])::before,
    .agreement-body li.ql-indent-1::before {
        content: counter(list-1, lower-alpha) ". " !important;
        position: absolute !important;
        left: 0 !important;
        font-weight: 600 !important;
        color: #475569 !important;
    }

    /* Nested level-2 list items (<ol><ol><ol> > <li> OR Quill .ql-indent-2) -> (1), (2), (3) */
    .agreement-body ol ol ol > li:not([class*="ql-indent"]),
    .agreement-body li.ql-indent-2 {
        counter-increment: list-2 !important;
        padding-left: 1.75rem !important;
        margin-left: 2.5rem !important;
    }
    .agreement-body ol ol ol > li:not([class*="ql-indent"])::before,
    .agreement-body li.ql-indent-2::before {
        content: "(" counter(list-2, decimal) ") " !important;
        position: absolute !important;
        left: 0 !important;
        font-weight: normal !important;
        color: #475569 !important;
    }

    /* Bullet items inside ul */
    .agreement-body ul > li::before {
        content: "•" !important;
        position: absolute !important;
        left: 0.25rem !important;
        font-weight: bold !important;
        color: #059669 !important;
        font-size: 1.2em !important;
    }

    .dark .agreement-body li {
        color: #cbd5e1 !important;
    }
    .dark .agreement-body > ol > li::before,
    .dark .agreement-body ol:not(ol ol) > li:not([class*="ql-indent"]):not(ol ol li)::before {
        color: #f1f5f9 !important;
    }
    .dark .agreement-body ol ol > li:not(ol ol ol li):not([class*="ql-indent"])::before,
    .dark .agreement-body li.ql-indent-1::before {
        color: #cbd5e1 !important;
    }
    .dark .agreement-body ol ol ol > li:not([class*="ql-indent"])::before,
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

<div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8">
    <div class="bg-white dark:bg-slate-900 rounded-2xl sm:rounded-3xl shadow-sm border border-slate-150/80 dark:border-slate-800 overflow-hidden">
        
        <!-- Header -->
        <div class="bg-brand-emerald text-white p-4 sm:p-6 space-y-3 sm:space-y-4">
            <div class="flex items-center justify-between gap-2.5 w-full">
                <h2 class="font-extrabold text-sm sm:text-lg text-white flex items-center gap-2 leading-tight min-w-0">
                    <i data-lucide="users" class="w-4 h-4 sm:w-5 sm:h-5 text-brand-yellow shrink-0"></i>
                    <span class="truncate sm:whitespace-normal">Assessment / Ta'aruf</span>
                </h2>
                
                <div class="shrink-0 self-center sm:self-start pt-0">
                    @if(in_array($registration->registration_status, ['taaruf_completed', 'agreement_signed', 'completed']))
                        <span class="inline-flex items-center gap-1 bg-green-700 text-white font-black text-[9px] sm:text-[10px] uppercase tracking-wider px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border border-green-500 shadow-xs whitespace-nowrap">
                            <i data-lucide="check-circle" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i> Selesai
                        </span>
                    @elseif($registration->registration_status === 'verified')
                        @if(!empty($registration->observation_date))
                            <span class="inline-flex items-center gap-1 bg-emerald-600 text-white font-black text-[9px] sm:text-[10px] uppercase tracking-wider px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border border-emerald-400 shadow-xs whitespace-nowrap">
                                <i data-lucide="calendar-check" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i> Jadwal Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 bg-amber-600 text-white font-black text-[9px] sm:text-[10px] uppercase tracking-wider px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border border-amber-500 shadow-xs whitespace-nowrap">
                                <i data-lucide="clock" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i> Menunggu Jadwal
                            </span>
                        @endif
                    @else
                        <span class="inline-flex items-center gap-1 bg-slate-700 text-white font-black text-[9px] sm:text-[10px] uppercase tracking-wider px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border border-slate-500 shadow-xs whitespace-nowrap">
                            <i data-lucide="lock" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i> Belum Aktif
                        </span>
                    @endif
                </div>
            </div>

            <!-- Full-width subtitle -->
            <p class="text-xs text-brand-yellow/90 font-medium leading-relaxed w-full">Assessment / Ta'aruf serta persetujuan komitmen biaya pendidikan.</p>

            <!-- Integrated Candidate Context Info -->
            <div class="bg-black/20 backdrop-blur-md rounded-2xl p-3.5 sm:p-5 border border-white/15 shadow-sm space-y-3">
                <!-- Top Row: Avatar + Name + Registration Number on the Far Right -->
                <div class="flex items-center justify-between gap-2.5 pb-2.5 sm:pb-3 border-b border-white/10">
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                        <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-xl bg-white/20 text-white font-black text-sm sm:text-lg flex items-center justify-center border border-white/25 shadow-inner shrink-0">
                            {{ strtoupper(substr(trim($registration->candidate_name ?? 'A'), 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <span class="text-[9px] sm:text-[10px] font-bold text-white/60 uppercase tracking-wider block leading-tight">Calon Murid</span>
                            <h4 class="font-black text-xs sm:text-lg text-white tracking-tight leading-snug truncate">
                                {{ $registration->candidate_name ?? 'Calon Murid' }}
                            </h4>
                        </div>
                    </div>

                    <!-- No. Registrasi Badge di Pojok Kanan -->
                    @if($registration->id_label)
                        <div class="shrink-0 text-right">
                            <span class="text-[10px] font-bold text-white/60 uppercase tracking-wider hidden sm:inline-block mr-1.5">No. Registrasi:</span>
                            <span class="text-[10px] sm:text-sm font-mono font-extrabold text-emerald-200 bg-white/15 px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg sm:rounded-xl border border-white/20 inline-flex items-center gap-1 sm:gap-1.5 shadow-xs whitespace-nowrap">
                                <i data-lucide="tag" class="w-2.5 h-2.5 sm:w-3.5 sm:h-3.5 text-emerald-300"></i> {{ $registration->id_label }}
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Bottom Row: Structured List / Grid of Metadata -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3.5">
                    <!-- 1. Unit Sekolah -->
                    <div class="flex items-center gap-2.5 bg-white/10 sm:bg-white/5 px-2.5 sm:px-3 py-2 rounded-xl border border-white/10">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0">
                            <i data-lucide="school" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[10px] text-white/60 font-semibold block leading-none">Unit Sekolah</span>
                            <span class="font-bold text-emerald-300 truncate block text-xs mt-1">{{ $registration->unit?->name ?? '-' }}</span>
                        </div>
                    </div>

                    <!-- 2. Tingkat & Kategori Murid -->
                    <div class="flex items-center gap-2.5 bg-white/10 sm:bg-white/5 px-2.5 sm:px-3 py-2 rounded-xl border border-white/10">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0">
                            <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[10px] text-white/60 font-semibold block leading-none">Kelas & Kategori</span>
                            <span class="font-bold text-white truncate block text-xs mt-1">{{ $registration->grade?->name ?? '-' }} ({{ $registration->classProgram?->name ?? 'Reguler' }})</span>
                        </div>
                    </div>

                    <!-- 3. Jalur & Gelombang -->
                    <div class="flex items-center gap-2.5 bg-white/10 sm:bg-white/5 px-2.5 sm:px-3 py-2 rounded-xl border border-white/10">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0">
                            <i data-lucide="layers" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-1.5">
                                <span class="text-[10px] text-white/60 font-semibold block leading-none">Jalur & Gelombang</span>
                                @if($registration->period?->year)
                                    <span class="text-[9px] font-extrabold text-emerald-200 bg-white/15 px-1.5 py-0.5 rounded border border-white/20 leading-none shrink-0 shadow-2xs">
                                        TP {{ $registration->period->year }}
                                    </span>
                                @endif
                            </div>
                            <span class="font-bold text-white/95 truncate block text-xs mt-1">
                                {{ $registration->type?->name ?? '-' }} • {{ $registration->wave?->name ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-3.5 sm:p-6 md:p-8">
            
            @if ($registration->registration_status === 'verified')
                @php
                    $isScheduled = !empty($registration->observation_date);
                    $unitTitle = $registration->unit?->taaruf_title ?? 'Jadwal dan Sesi Assessment / Ta\'aruf';
                    $defaultLoc = $registration->unit?->taaruf_default_location ?: ($registration->unit?->name ?? 'Sekolah Dasar Anak Saleh');
                    $defaultRoom = $registration->unit?->taaruf_default_room;
                    
                    $isPaud = stripos($registration->unit?->code ?? '', 'PAUD') !== false || stripos($registration->unit?->name ?? '', 'PAUD') !== false || stripos($registration->unit?->name ?? '', 'TK') !== false || stripos($registration->unit?->name ?? '', 'KB') !== false;
                    $fallbackAddress = $isPaud 
                        ? 'Jl. Candi Panggung Indah No. 1-3, Mojolangu, Kecamatan Lowokwaru, Kota Malang, Jawa Timur' 
                        : 'Jl. Arumba No.31, Tunggulwulung, Kec. Lowokwaru, Kota Malang, Jawa Timur';
                    $defaultAddress = $registration->unit?->taaruf_default_address ?: $fallbackAddress;
                    
                    $instructions = $registration->unit?->taaruf_instructions;
                    $requiredItems = $registration->unit?->taaruf_required_items;
                @endphp
                <!-- 1. State: Verified (Informasi & Jadwal Ta'aruf) -->
                <div class="space-y-6">

                    @if($registration->unit && !empty($registration->unit->spmb_group_url))
                        @php
                            $waJoined = (bool) ($registration->additional_info['wa_group_joined'] ?? false);
                            $waJoinedAt = !empty($registration->additional_info['wa_group_joined_at']) ? \Carbon\Carbon::parse($registration->additional_info['wa_group_joined_at'])->translatedFormat('d M Y, H:i') : null;
                        @endphp
                        <div id="wa-group-card-{{ $registration->id }}" class="p-4 sm:p-5 rounded-2xl sm:rounded-3xl border transition-all duration-300 {{ $waJoined ? 'bg-emerald-50/80 dark:bg-emerald-950/30 border-emerald-300/80 dark:border-emerald-800' : 'bg-gradient-to-r from-emerald-500/10 via-teal-500/5 to-emerald-500/10 border-emerald-400/50 dark:border-emerald-700/60' }}">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
                                <div class="flex items-start sm:items-center gap-3 min-w-0">
                                    <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-2xl flex items-center justify-center shrink-0 shadow-sm {{ $waJoined ? 'bg-emerald-500 text-white' : 'bg-[#25D366] text-white' }}">
                                        <i data-lucide="{{ $waJoined ? 'check-circle-2' : 'message-circle' }}" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h4 class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white leading-snug">
                                                {{ $waJoined ? '✅ Anda Telah Bergabung ke Group WhatsApp SPMB (' . ($registration->unit->name ?? '') . ')' : 'Group WhatsApp Informasi SPMB ' . ($registration->unit->name ?? '') }}
                                            </h4>
                                            @if($waJoined)
                                                <span class="text-[9px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300 font-extrabold uppercase">Terhubung</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed mt-0.5">
                                            {{ $waJoined 
                                                ? 'Terima kasih telah bergabung. Pembaruan informasi seputar tahapan SPMB akan dibagikan secara berkala melalui grup ini dan portal pendaftaran.' 
                                                : 'Silakan bergabung ke group WhatsApp resmi untuk mendapatkan pembaruan informasi seputar tahapan SPMB Sekolah Anak Saleh.' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 shrink-0 w-full sm:w-auto">
                                    @if(!$waJoined)
                                        <button type="button" 
                                                onclick="handleJoinWaGroup({{ $registration->id }}, '{{ $registration->unit->spmb_group_url }}', '{{ addslashes($registration->unit->name) }}')" 
                                                class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-[#25D366] hover:bg-[#1EBE5D] shadow-md shadow-emerald-600/20 transition-all cursor-pointer">
                                            <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                            <span>Gabung Group WhatsApp</span>
                                        </button>
                                    @else
                                        <a href="{{ $registration->unit->spmb_group_url }}" target="_blank" rel="noopener noreferrer" 
                                           class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-700 hover:bg-emerald-50 dark:hover:bg-slate-800 shadow-xs transition">
                                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                            <span>Buka Tautan Group</span>
                                        </a>
                                        <button type="button" 
                                                onclick="toggleWaGroupStatus({{ $registration->id }}, false, '{{ $registration->unit->spmb_group_url }}', '{{ addslashes($registration->unit->name) }}')" 
                                                class="text-[10px] text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 underline text-center px-1.5 py-1"
                                                title="Klik jika Anda belum bergabung atau ingin mengubah status">
                                            Ubah status
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if($isScheduled)
                        <!-- Kartu Jadwal Resmi Terjadwal -->
                        <div class="border-2 border-brand-emerald/40 bg-gradient-to-b from-emerald-50/40 to-white dark:from-emerald-950/20 dark:to-slate-900 rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 space-y-5 sm:space-y-6 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-emerald-100 dark:border-emerald-900/60 pb-4 sm:pb-5">
                                <div class="flex items-center gap-3 sm:gap-3.5">
                                    <div class="h-10 w-10 sm:h-12 sm:w-12 bg-gradient-to-tr from-brand-emerald to-emerald-400 text-white rounded-xl sm:rounded-2xl flex items-center justify-center font-bold text-lg sm:text-xl shadow-md shadow-emerald-500/20 flex-shrink-0">
                                        📅
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-black text-slate-800 dark:text-white text-sm sm:text-lg leading-snug">{{ $unitTitle }}</h3>
                                        </div>
                                        <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 font-semibold mt-0.5">Undangan Resmi Pelaksanaan di {{ $registration->unit->name }}</p>
                                    </div>
                                </div>
                                <span class="self-start sm:self-auto inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 rounded-full text-[11px] sm:text-xs font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                    <span class="w-2 h-2 rounded-full bg-brand-emerald animate-pulse"></span>
                                    Terjadwal Resmi
                                </span>
                            </div>

                            <!-- Rincian Waktu & Lokasi Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-1 shadow-sm">
                                    <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Hari & Tanggal Pelaksanaan</span>
                                    <div class="font-extrabold text-slate-800 dark:text-white text-xs sm:text-base flex items-center gap-2">
                                        <i data-lucide="calendar" class="w-4 h-4 text-brand-emerald shrink-0"></i>
                                        <span>{{ $registration->observation_date->translatedFormat('l, d F Y') }}</span>
                                    </div>
                                </div>

                                <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-1 shadow-sm">
                                    <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Waktu / Sesi</span>
                                    <div class="font-extrabold text-slate-800 dark:text-white text-xs sm:text-base flex items-center gap-2">
                                        <i data-lucide="clock" class="w-4 h-4 text-brand-emerald shrink-0"></i>
                                        <span>{{ $registration->observation_time }}</span>
                                    </div>
                                </div>

                                <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-2 shadow-sm sm:col-span-2">
                                    <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Lokasi & Ruangan Pelaksanaan</span>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <div class="font-bold text-slate-800 dark:text-white text-xs sm:text-sm flex items-center gap-2">
                                            <i data-lucide="map-pin" class="w-4 h-4 text-rose-500 flex-shrink-0"></i>
                                            <span>{{ $registration->observation_location ?: $defaultLoc }}</span>
                                        </div>
                                        @if($registration->observation_room || $defaultRoom)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 font-bold text-xs shadow-2xs">
                                                <i data-lucide="door-open" class="w-3.5 h-3.5 text-brand-emerald"></i>
                                                <span>{{ $registration->observation_room ?: $defaultRoom }}</span>
                                            </span>
                                        @endif
                                    </div>
                                    @if($registration->observation_address || $defaultAddress)
                                        <div class="pt-2 mt-1 border-t border-slate-100 dark:border-slate-800/80 text-xs text-slate-600 dark:text-slate-400 flex items-start gap-2">
                                            <i data-lucide="navigation" class="w-3.5 h-3.5 text-slate-400 mt-0.5 flex-shrink-0"></i>
                                            <span class="leading-relaxed"><strong class="text-slate-700 dark:text-slate-300 font-semibold">Alamat:</strong> {{ $registration->observation_address ?: $defaultAddress }}</span>
                                        </div>
                                    @endif
                                </div>

                                @if($registration->observation_interviewer)
                                    <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-1 shadow-sm sm:col-span-2">
                                        <span class="text-[10px] sm:text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Pewawancara / Tim Penguji</span>
                                        <div class="font-bold text-slate-800 dark:text-white text-xs sm:text-sm flex items-center gap-2">
                                            <i data-lucide="user-check" class="w-4 h-4 text-blue-500"></i>
                                            <span>{{ $registration->observation_interviewer }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if($registration->observation_notes)
                                <div class="p-3.5 sm:p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 text-xs space-y-1">
                                    <div class="font-bold text-amber-800 dark:text-amber-300 flex items-center gap-1.5">
                                        <i data-lucide="info" class="w-4 h-4 text-amber-600"></i>
                                        <span>Catatan Khusus Panitia:</span>
                                    </div>
                                    <p class="text-slate-700 dark:text-slate-300 leading-relaxed pl-5 whitespace-pre-line">{{ $registration->observation_notes }}</p>
                                </div>
                            @endif

                            <!-- KARTU KONFIRMASI KEHADIRAN (RSVP) OLEH WALI MURID (Hanya jika hasil observasi belum diunggah) -->
                            @if(empty($registration->observation_result_path) && !in_array($registration->registration_status, ['taaruf_completed', 'agreement_signed', 'completed']))
                                <div id="attendance-card" class="p-4 sm:p-5 rounded-2xl sm:rounded-3xl border transition-all duration-300 {{ $registration->observation_attendance_status === 'confirmed_present' ? 'bg-emerald-50/80 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800' : ($registration->observation_attendance_status === 'reschedule_requested' ? 'bg-amber-50/80 dark:bg-amber-950/40 border-amber-300 dark:border-amber-800' : 'bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800') }}">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div class="flex items-start gap-3 sm:gap-3.5 min-w-0">
                                            <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-2xl flex items-center justify-center shrink-0 shadow-sm {{ $registration->observation_attendance_status === 'confirmed_present' ? 'bg-emerald-500 text-white' : ($registration->observation_attendance_status === 'reschedule_requested' ? 'bg-amber-500 text-white' : 'bg-brand-emerald text-white') }}">
                                                <i data-lucide="{{ $registration->observation_attendance_status === 'confirmed_present' ? 'user-check' : ($registration->observation_attendance_status === 'reschedule_requested' ? 'calendar-clock' : 'check-circle-2') }}" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                                            </div>
                                            <div class="min-w-0 space-y-0.5">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h4 class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-white leading-snug">
                                                        @if($registration->observation_attendance_status === 'confirmed_present')
                                                            ✅ Kehadiran Dikonfirmasi Hadir
                                                        @elseif($registration->observation_attendance_status === 'reschedule_requested')
                                                            🟡 Permohonan Reschedule Terkirim
                                                        @else
                                                            Konfirmasi Kehadiran Sesi Ta'aruf
                                                        @endif
                                                    </h4>
                                                    @if($registration->observation_attendance_status === 'confirmed_present')
                                                        <span class="text-[9px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300 font-extrabold uppercase">Siap Hadir</span>
                                                    @elseif($registration->observation_attendance_status === 'reschedule_requested')
                                                        <span class="text-[9px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300 font-extrabold uppercase">Menunggu Tinjauan</span>
                                                    @endif
                                                </div>
                                                <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed">
                                                    @if($registration->observation_attendance_status === 'confirmed_present')
                                                        Alhamdulillah, Anda telah mengonfirmasi kehadiran ananda sesuai jadwal yang ditetapkan. Kami menanti kehadiran ananda di kampus Sekolah Anak Saleh.
                                                        @if($registration->observation_attendance_confirmed_at)
                                                            <span class="block text-[10px] text-emerald-700 dark:text-emerald-400 font-bold mt-0.5">Dikonfirmasi pada: {{ \Carbon\Carbon::parse($registration->observation_attendance_confirmed_at)->translatedFormat('d F Y, H:i') }} WIB</span>
                                                        @endif
                                                    @elseif($registration->observation_attendance_status === 'reschedule_requested')
                                                        Permohonan penjadwalan ulang sedang ditinjau oleh panitia unit {{ $registration->unit->name }}. Panitia akan memperbarui jadwal dan menghubungi Anda.
                                                        @if($registration->observation_attendance_notes)
                                                            <span class="block text-[11px] font-semibold text-amber-800 dark:text-amber-300 italic mt-0.5">Catatan/Alasan: "{{ $registration->observation_attendance_notes }}"</span>
                                                        @endif
                                                    @else
                                                        Mohon lakukan konfirmasi kesiapan kehadiran ananda untuk mempermudah alokasi waktu penguji dan persiapan ruang observasi.
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 shrink-0 w-full sm:w-auto">
                                            @if($registration->observation_attendance_status === 'confirmed_present')
                                                <button type="button" 
                                                        onclick="toggleRescheduleForm(true)" 
                                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold text-amber-700 dark:text-amber-300 bg-amber-100 hover:bg-amber-200 dark:bg-amber-950/60 dark:hover:bg-amber-900/60 transition cursor-pointer">
                                                    <i data-lucide="calendar-sync" class="w-3.5 h-3.5"></i>
                                                    <span>Ajukan Reschedule</span>
                                                </button>
                                            @elseif($registration->observation_attendance_status === 'reschedule_requested')
                                                <button type="button" 
                                                        onclick="toggleRescheduleForm(true)" 
                                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold text-amber-800 dark:text-amber-200 bg-amber-200/80 hover:bg-amber-300 dark:bg-amber-900/80 dark:hover:bg-amber-800 transition cursor-pointer">
                                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                                    <span>Ubah Alasan</span>
                                                </button>
                                                <button type="button" 
                                                        onclick="submitAttendance('confirmed_present')" 
                                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-400 hover:text-emerald-700 dark:hover:text-emerald-400 bg-white/80 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700 hover:border-emerald-300 transition cursor-pointer"
                                                        title="Batalkan permohonan reschedule dan konfirmasi tetap siap hadir pada jadwal semula">
                                                    <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                                    <span>Tetap Hadir</span>
                                                </button>
                                            @else
                                                <button type="button" 
                                                        onclick="submitAttendance('confirmed_present')" 
                                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-brand-emerald hover-emerald shadow-md shadow-emerald-600/20 transition cursor-pointer">
                                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                    <span>Konfirmasi Hadir</span>
                                                </button>
                                                <button type="button" 
                                                        onclick="toggleRescheduleForm(true)" 
                                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                                                    <i data-lucide="calendar-sync" class="w-3.5 h-3.5"></i>
                                                    <span>Minta Reschedule</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Form Reschedule (Collapsed by default) -->
                                    <div id="reschedule-form-container" class="hidden mt-4 pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                                Alasan / Pilihan Waktu Penjadwalan Ulang
                                            </label>
                                            <textarea id="rescheduleNotesInput" 
                                                      rows="2" 
                                                      placeholder="Misal: Mohon maaf, berhalangan hadir pada tanggal tersebut karena ada keperluan mendesak. Kami mohon agar jadwal dapat dialihkan ke sesi / tanggal berikutnya..." 
                                                      class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500">{{ $registration->observation_attendance_notes ?? '' }}</textarea>
                                        </div>
                                        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2">
                                            <button type="button" onclick="toggleRescheduleForm(false)" class="w-full sm:w-auto px-4 py-2.5 bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition text-center">
                                                Batal
                                            </button>
                                            <button type="button" onclick="submitAttendance('reschedule_requested')" class="w-full sm:w-auto px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center justify-center gap-1.5">
                                                <i data-lucide="send" class="w-3.5 h-3.5"></i>
                                                <span>{{ $registration->observation_attendance_status === 'reschedule_requested' ? 'Simpan Perubahan Alasan' : 'Kirim Permohonan Reschedule' }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- KARTU HASIL OBSERVASI (JIKA SUDAH DIUNGGAH OLEH ADMIN) -->
                            @if(!empty($registration->observation_result_path))
                                <div class="p-4 sm:p-5 rounded-2xl sm:rounded-3xl bg-gradient-to-r from-indigo-50/90 via-purple-50/50 to-indigo-50/90 dark:from-indigo-950/40 dark:via-purple-950/20 dark:to-indigo-950/40 border-2 border-indigo-300 dark:border-indigo-800 shadow-sm space-y-3">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
                                        <div class="flex items-start sm:items-center gap-3 sm:gap-3.5 min-w-0">
                                            <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-indigo-600/20 font-bold">
                                                <i data-lucide="file-check-2" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h4 class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-white leading-snug">
                                                        📋 Hasil Observasi / Ta'aruf
                                                    </h4>
                                                    <span class="text-[9px] px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300 font-extrabold uppercase">Tersedia</span>
                                                </div>
                                                <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed mt-0.5">
                                                    Dokumen evaluasi &amp; hasil observasi kesiapan belajar ananda telah diunggah oleh panitia unit.
                                                    @if($registration->observation_result_uploaded_at)
                                                        <span class="text-[10px] text-slate-400 block mt-0.5">Diperbarui: {{ \Carbon\Carbon::parse($registration->observation_result_uploaded_at)->translatedFormat('d F Y, H:i') }} WIB</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        <div class="shrink-0 w-full sm:w-auto">
                                            <a href="{{ route('dashboard.registration.download-result', $registration->id) }}" 
                                               class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition cursor-pointer">
                                                <i data-lucide="download" class="w-4 h-4"></i>
                                                <span>Unduh Dokumen Hasil</span>
                                            </a>
                                        </div>
                                    </div>

                                    @if($registration->observation_result_notes)
                                        <div class="p-3 bg-white/80 dark:bg-slate-900/80 rounded-xl sm:rounded-2xl border border-indigo-200/80 dark:border-indigo-900/60 text-xs text-slate-700 dark:text-slate-300">
                                            <strong class="font-bold text-indigo-700 dark:text-indigo-400 block mb-0.5">Catatan Hasil Observasi:</strong>
                                            <p class="leading-relaxed italic">"{{ $registration->observation_result_notes }}"</p>
                                        </div>
                                    @endif
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
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Alur setelah pelaksanaan sesi Assessment / Ta'aruf</p>
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
                                            Setelah sesi Assessment / Ta'aruf selesai, panitia akan mengunggah berkas hasil observasi.
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
                        <div class="border border-brand-emerald/30 bg-emerald-50/10 rounded-2xl sm:rounded-3xl p-4 sm:p-6 md:p-8 space-y-4 sm:space-y-5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-emerald-100 dark:border-emerald-900/60 pb-4 sm:pb-5">
                                <div class="flex items-center gap-3 sm:gap-3.5">
                                    <div class="h-10 w-10 sm:h-12 sm:w-12 bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-xl sm:rounded-2xl flex items-center justify-center font-bold text-lg sm:text-xl flex-shrink-0">
                                        <i data-lucide="hourglass" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-black text-slate-800 dark:text-white text-sm sm:text-base leading-snug">{{ $unitTitle }}</h3>
                                        <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 font-semibold mt-0.5">Mohon senantiasa memantau halaman ini secara berkala untuk pembaruan jadwal resmi</p>
                                    </div>
                                </div>
                                <span class="self-start sm:self-auto inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    Menunggu Alokasi Jadwal
                                </span>
                            </div>

                            <div class="bg-white dark:bg-slate-950 p-3.5 sm:p-4 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-2.5 sm:space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3 text-xs">
                                    <div>
                                        <span class="text-slate-400 text-[11px] block">Unit Sekolah</span>
                                        <span class="font-bold text-slate-800 dark:text-white">{{ $registration->unit->name }} ({{ $registration->unit->code }})</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 text-[11px] block">Tingkat Kelas</span>
                                        <span class="font-bold text-slate-800 dark:text-white">{{ $registration->grade->name }} ({{ $registration->classProgram->name ?? 'Reguler' }})</span>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <span class="text-slate-400 text-[11px] block">No. WhatsApp Orang Tua</span>
                                        <span class="font-bold text-slate-800 dark:text-white">
                                            {{ $registration->parent_phone ?? $registration->father_phone ?? $registration->mother_phone ?? $registration->guardian_phone ?? $registration->user?->phone ?? '-' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-slate-650 dark:text-slate-400 leading-relaxed">
                                Berkas pendaftaran ananda telah berhasil diverifikasi oleh panitia. Saat ini, rincian tanggal, sesi waktu, dan ruangan pelaksanaan <strong>Assessment / Ta'aruf</strong> sedang dalam proses alokasi oleh panitia unit <strong>{{ $registration->unit->name }}</strong>. Mohon senantiasa memantau halaman ini secara berkala karena jadwal resmi akan langsung terbit dan diperbarui secara otomatis di sini.
                            </p>
                        </div>
                    @endif

                    <!-- Ketentuan & Perlengkapan Bawaan Spesifik Unit -->
                    @if($instructions || $requiredItems)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($instructions)
                                <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-4 sm:p-5 rounded-2xl text-xs space-y-2">
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
                                <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-4 sm:p-5 rounded-2xl text-xs space-y-2">
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
                    <div class="border border-green-200 bg-green-50/10 rounded-2xl p-4 sm:p-6 flex gap-3.5 items-start">
                        <span class="inline-flex items-center justify-center h-9 w-9 sm:h-10 sm:w-10 bg-green-100 text-green-700 rounded-xl flex-shrink-0">
                            <i data-lucide="check" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <h3 class="font-extrabold text-slate-800 dark:text-white text-sm">Assessment / Ta'aruf Selesai</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                Ananda telah menyelesaikan rangkaian sesi Assessment / Ta'aruf kesiapan belajar. Selanjutnya, silakan baca dan setujui Pernyataan Kesanggupan berikut ini untuk melanjutkan ke tahap administrasi keuangan.
                            </p>
                        </div>
                    </div>

                    <!-- KARTU HASIL OBSERVASI PADA STATE SELESAI -->
                    @if(!empty($registration->observation_result_path))
                        <div class="p-4 sm:p-5 rounded-2xl sm:rounded-3xl bg-gradient-to-r from-indigo-50/90 via-purple-50/50 to-indigo-50/90 dark:from-indigo-950/40 dark:via-purple-950/20 dark:to-indigo-950/40 border-2 border-indigo-300 dark:border-indigo-800 shadow-sm space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
                                <div class="flex items-start sm:items-center gap-3 sm:gap-3.5 min-w-0">
                                    <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-indigo-600/20 font-bold">
                                        <i data-lucide="file-check-2" class="w-5 h-5 sm:w-6 sm:h-6"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h4 class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-white leading-snug">
                                                📋 Berkas Laporan Hasil Observasi / Ta'aruf
                                            </h4>
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300 font-extrabold uppercase">Tersedia</span>
                                        </div>
                                        <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed mt-0.5">
                                            Dokumen evaluasi &amp; hasil observasi kesiapan belajar ananda telah diunggah oleh panitia unit.
                                            @if($registration->observation_result_uploaded_at)
                                                <span class="text-[10px] text-slate-400 block mt-0.5">Diperbarui: {{ \Carbon\Carbon::parse($registration->observation_result_uploaded_at)->translatedFormat('d F Y, H:i') }} WIB</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="shrink-0 w-full sm:w-auto">
                                    <a href="{{ route('dashboard.registration.download-result', $registration->id) }}" 
                                       class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition cursor-pointer">
                                        <i data-lucide="download" class="w-4 h-4"></i>
                                        <span>Unduh Dokumen Hasil</span>
                                    </a>
                                </div>
                            </div>

                            @if($registration->observation_result_notes)
                                <div class="p-3 bg-white/80 dark:bg-slate-900/80 rounded-xl sm:rounded-2xl border border-indigo-200/80 dark:border-indigo-900/60 text-xs text-slate-700 dark:text-slate-300">
                                    <strong class="font-bold text-indigo-700 dark:text-indigo-400 block mb-0.5">Catatan Hasil Observasi:</strong>
                                    <p class="leading-relaxed italic">"{{ $registration->observation_result_notes }}"</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <form action="{{ route('dashboard.agreement.submit', $registration->id) }}" method="POST" class="space-y-6 border-t border-slate-100 dark:border-slate-800 pt-6">
                        @csrf
                        
                        <div class="space-y-4">
                            <h4 class="font-extrabold text-sm text-slate-800 dark:text-white">Pernyataan Kesanggupan Orang Tua / Wali</h4>
                            <div id="agreement-scrollbox" class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl p-3.5 sm:p-5 text-xs text-slate-650 dark:text-slate-350 space-y-3.5 max-h-[380px] sm:max-h-[500px] overflow-y-auto leading-relaxed">
                                @if($agreementTemplate)
                                    <div class="flex flex-col items-end mb-5 select-none">
                                        <div class="bg-amber-50/90 dark:bg-amber-950/60 border border-amber-300 dark:border-amber-700/80 p-2.5 sm:p-3 rounded-2xl flex flex-col items-center text-center max-w-[290px] shadow-sm">
                                            <span class="inline-flex items-center gap-1.5 bg-amber-500 text-white dark:bg-amber-600 px-2.5 py-0.5 rounded-lg font-black text-[9px] uppercase tracking-wider shadow-xs">
                                                <i data-lucide="shield-alert" class="w-3 h-3 flex-shrink-0"></i>
                                                UNTUK KALANGAN SENDIRI
                                            </span>
                                            <span class="text-[9px] text-amber-900 dark:text-amber-200 font-bold mt-1.5 leading-snug">
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
                                    <div class="flex flex-col items-end mb-5 select-none">
                                        <div class="bg-amber-50/90 dark:bg-amber-950/60 border border-amber-300 dark:border-amber-700/80 p-2.5 sm:p-3 rounded-2xl flex flex-col items-center text-center max-w-[290px] shadow-sm">
                                            <span class="inline-flex items-center gap-1.5 bg-amber-500 text-white dark:bg-amber-600 px-2.5 py-0.5 rounded-lg font-black text-[9px] uppercase tracking-wider shadow-xs">
                                                <i data-lucide="shield-alert" class="w-3 h-3 flex-shrink-0"></i>
                                                UNTUK KALANGAN SENDIRI
                                            </span>
                                            <span class="text-[9px] text-amber-900 dark:text-amber-200 font-bold mt-1.5 leading-snug">
                                                Dilarang memfoto, mengcopy, dan menyebarluaskan dokumen ini
                                            </span>
                                        </div>
                                    </div>
                                    <p class="font-bold text-center text-slate-800 dark:text-white uppercase tracking-wide border-b border-slate-200 dark:border-slate-800 pb-2 mb-3">SURAT PERNYATAAN KESANGGUPAN MEMATUHI PERATURAN & BIAYA PENDIDIKAN</p>
                                    <p>Saya yang bertanda tangan di bawah ini selaku Orang Tua / Wali murid dari calon murid:</p>
                                    <div class="pl-4 space-y-1 font-semibold">
                                        <p>Nama Calon Murid : {{ $registration->candidate_name }}</p>
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
                                <button type="submit" class="w-full sm:w-auto bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl font-bold text-xs shadow-md transition flex items-center justify-center gap-1.5">
                                    <i data-lucide="check-square" class="w-4 h-4"></i> Setujui & Tandatangani Pernyataan Kesanggupan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            @elseif (in_array($registration->registration_status, ['agreement_signed', 'completed']))
                <!-- 3. State: Agreement Signed / Completed (Readonly signed status) -->
                <div class="space-y-6 text-center py-6 sm:py-8 max-w-md mx-auto">
                    <div class="h-14 w-14 sm:h-16 sm:w-16 bg-green-50 dark:bg-green-950/20 text-green-600 rounded-2xl sm:rounded-3xl flex items-center justify-center mx-auto shadow-inner">
                        <i data-lucide="award" class="w-7 h-7 sm:w-8 sm:h-8"></i>
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-extrabold text-slate-800 dark:text-white">Pernyataan Kesanggupan Disepakati</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Terima kasih. Anda telah menyetujui Pernyataan Kesanggupan Tata Tertib dan Biaya Masuk Yayasan.
                        </p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-4 rounded-xl text-left text-xs space-y-2">
                        <p class="font-bold text-slate-800 dark:text-white text-center border-b border-slate-100 dark:border-slate-800 pb-2">Status Persetujuan Digital</p>
                        <p><strong>Penandatangan:</strong> {{ $registration->signature_name ?? ($registration->father_name ?? ($registration->mother_name ?? $registration->candidate_name)) }} (Orang Tua / Wali)</p>
                        <p><strong>Status:</strong> <span class="text-green-600 font-bold">DISETUJUI / VALID</span></p>
                        <p><strong>Tanggal:</strong> {{ ($registration->signed_at ?? $registration->updated_at)->format('d M Y H:i') }} WIB</p>
                    </div>
                    <div class="pt-2">
                        <a href="{{ route('dashboard.result', $registration->id) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl text-xs font-bold shadow-md transition">
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
                        Tahapan sesi Assessment / Ta'aruf dan penandatanganan kesanggupan hanya akan aktif setelah berkas pendaftaran Anda lolos verifikasi sukses di menu <strong>Verifikasi Data</strong>.
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
      // WhatsApp Group Tracker Functions
      window.handleJoinWaGroup = async function(regId, groupUrl, unitName) {
          if (groupUrl) {
              window.open(groupUrl, '_blank', 'noopener,noreferrer');
          }
          
          try {
              const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
              const response = await fetch(`/dashboard/registration/${regId}/join-wa-group`, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': csrfToken,
                      'Accept': 'application/json'
                  },
                  body: JSON.stringify({ status: true })
              });
              const data = await response.json();
              if (data.success) {
                  updateWaGroupCardUI(regId, true, groupUrl, unitName);
              }
          } catch (e) {
              console.error('Failed to record WA group join:', e);
          }
      };

      window.toggleWaGroupStatus = async function(regId, newStatus, groupUrl, unitName) {
          try {
              const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
              const response = await fetch(`/dashboard/registration/${regId}/join-wa-group`, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': csrfToken,
                      'Accept': 'application/json'
                  },
                  body: JSON.stringify({ status: newStatus })
              });
              const data = await response.json();
              if (data.success) {
                  updateWaGroupCardUI(regId, newStatus, groupUrl, unitName);
              }
          } catch (e) {
              console.error('Failed to toggle WA group status:', e);
          }
      };

      function updateWaGroupCardUI(regId, joined, groupUrl, unitName) {
          const card = document.getElementById(`wa-group-card-${regId}`);
          if (!card) return;

          if (joined) {
              card.className = "p-4 sm:p-5 rounded-3xl border transition-all duration-300 bg-emerald-50/80 dark:bg-emerald-950/30 border-emerald-300/80 dark:border-emerald-800";
              card.innerHTML = `
                  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
                      <div class="flex items-start sm:items-center gap-3 min-w-0">
                          <div class="h-11 w-11 sm:h-12 sm:w-12 rounded-2xl flex items-center justify-center shrink-0 shadow-sm bg-emerald-500 text-white">
                              <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                          </div>
                          <div class="min-w-0">
                              <div class="flex items-center gap-2 flex-wrap">
                                  <h4 class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white">
                                      ✅ Anda Telah Bergabung ke Group WhatsApp SPMB ${unitName ? '(' + unitName + ')' : ''}
                                  </h4>
                                  <span class="text-[9px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300 font-extrabold uppercase">Terhubung</span>
                              </div>
                              <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed mt-0.5">
                                  Terima kasih telah bergabung. Pembaruan informasi seputar tahapan SPMB akan dibagikan secara berkala melalui grup ini dan portal pendaftaran.
                              </p>
                          </div>
                      </div>
                      <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                          <a href="${groupUrl}" target="_blank" rel="noopener noreferrer" 
                             class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-white dark:bg-slate-900 border border-emerald-300 dark:border-emerald-700 hover:bg-emerald-50 dark:hover:bg-slate-800 shadow-xs transition">
                              <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                              <span>Buka Tautan Group</span>
                          </a>
                          <button type="button" 
                                  onclick="toggleWaGroupStatus(${regId}, false, '${groupUrl}', '${unitName}')" 
                                  class="text-[10px] text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 underline px-1.5 py-1"
                                  title="Klik jika Anda belum bergabung atau ingin mengubah status">
                              Ubah status
                          </button>
                      </div>
                  </div>
              `;
          } else {
              card.className = "p-4 sm:p-5 rounded-3xl border transition-all duration-300 bg-gradient-to-r from-emerald-500/10 via-teal-500/5 to-emerald-500/10 border-emerald-400/50 dark:border-emerald-700/60";
              card.innerHTML = `
                  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5">
                      <div class="flex items-start sm:items-center gap-3 min-w-0">
                          <div class="h-11 w-11 sm:h-12 sm:w-12 rounded-2xl flex items-center justify-center shrink-0 shadow-sm bg-[#25D366] text-white">
                              <i data-lucide="message-circle" class="w-6 h-6"></i>
                          </div>
                          <div class="min-w-0">
                              <h4 class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white">
                                  Group WhatsApp Informasi SPMB ${unitName}
                              </h4>
                              <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed mt-0.5">
                                  Silakan bergabung ke group WhatsApp resmi untuk mendapatkan pembaruan informasi seputar tahapan SPMB Sekolah Anak Saleh.
                              </p>
                          </div>
                      </div>
                      <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                          <button type="button" 
                                  onclick="handleJoinWaGroup(${regId}, '${groupUrl}', '${unitName}')" 
                                  class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-[#25D366] hover:bg-[#1EBE5D] shadow-md shadow-emerald-600/20 transition-all cursor-pointer">
                              <i data-lucide="send" class="w-3.5 h-3.5"></i>
                              <span>Gabung Group WhatsApp</span>
                          </button>
                      </div>
                  </div>
              `;
          }

          if (typeof lucide !== 'undefined') {
              lucide.createIcons();
          }
      }

      // Attendance RSVP Functions
      window.toggleRescheduleForm = function(show) {
          const container = document.getElementById('reschedule-form-container');
          if (container) {
              if (show) {
                  container.classList.remove('hidden');
                  const input = document.getElementById('rescheduleNotesInput');
                  if (input) input.focus();
              } else {
                  container.classList.add('hidden');
              }
          }
      };

      window.submitAttendance = async function(status) {
          let notes = null;
          if (status === 'reschedule_requested') {
              const notesInput = document.getElementById('rescheduleNotesInput');
              notes = notesInput ? notesInput.value.trim() : '';
              if (!notes) {
                  alert('Mohon tuliskan alasan atau preferensi waktu penjadwalan ulang terlebih dahulu.');
                  if (notesInput) notesInput.focus();
                  return;
              }
          }

          try {
              const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
              const response = await fetch('{{ route('dashboard.registration.confirm-attendance', $registration->id) }}', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': csrfToken,
                      'Accept': 'application/json'
                  },
                  body: JSON.stringify({
                      attendance_status: status,
                      attendance_notes: notes
                  })
              });

              if (!response.ok) {
                  const errData = await response.json().catch(() => ({}));
                  throw new Error(errData.message || 'Gagal menyimpan konfirmasi kehadiran.');
              }

              const data = await response.json();
              if (window.showToast) {
                  showToast(data.message || 'Konfirmasi kehadiran berhasil disimpan.', 'success');
              }
              setTimeout(() => {
                  window.location.reload();
              }, 400);
          } catch (e) {
              console.error('Error submitting attendance RSVP:', e);
              if (window.showToast) {
                  showToast(e.message || 'Gagal menyimpan konfirmasi kehadiran.', 'error');
              } else {
                  alert(e.message || 'Gagal menyimpan konfirmasi kehadiran.');
              }
          }
      };
 </script>
@endsection
