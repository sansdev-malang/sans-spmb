@extends('layouts.portal')

@section('title', 'Formulir Pendaftaran - Portal SPMB')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-md border border-slate-100 dark:border-slate-800 overflow-hidden">
        <!-- Form Card Header -->
        <div class="bg-brand-emerald text-white p-5 sm:p-6 space-y-3 sm:space-y-4">
            <div class="flex items-center justify-between gap-2.5 w-full">
                <h2 class="font-extrabold text-sm sm:text-lg text-white flex items-center gap-2 leading-tight min-w-0">
                    <i data-lucide="file-edit" class="w-4 h-4 sm:w-5 sm:h-5 text-brand-yellow shrink-0"></i>
                    <span class="truncate sm:whitespace-normal">Isi Formulir & Unggah Dokumen</span>
                </h2>
                
                <div class="shrink-0 self-center sm:self-start pt-0">
                    @if(in_array($registration->registration_status, ['submitted', 'verified', 'taaruf_completed', 'agreement_signed', 'completed']))
                        <span class="inline-flex items-center gap-1 bg-green-700 text-white font-black text-[9px] sm:text-[10px] uppercase tracking-wider px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border border-green-500 shadow-xs whitespace-nowrap">
                            <i data-lucide="check-circle" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i> Terkirim
                        </span>
                    @elseif($registration->registration_status === 'failed')
                        <span class="inline-flex items-center gap-1 bg-red-750 text-white font-black text-[9px] sm:text-[10px] uppercase tracking-wider px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border border-red-500 shadow-xs whitespace-nowrap">
                            <i data-lucide="alert-circle" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i> Perlu Revisi
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 bg-brand-yellow text-brand-emerald font-black text-[9px] sm:text-[10px] uppercase tracking-wider px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border border-yellow-300 shadow-xs whitespace-nowrap">
                            <i data-lucide="edit-3" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i> Pengisian
                        </span>
                    @endif
                </div>
            </div>

            <!-- Full-width subtitle -->
            <p class="text-xs text-brand-yellow/90 font-medium leading-relaxed w-full">Lengkapi biodata calon siswa, data orang tua, dan unggah dokumen persyaratan.</p>

            <!-- Integrated Candidate Context Info -->
            <div class="bg-black/20 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/15 shadow-sm space-y-3.5">
                <!-- Top Row: Avatar + Name + Registration Number on the Far Right -->
                <div class="flex items-center justify-between gap-3 pb-3 border-b border-white/10">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="h-11 w-11 sm:h-12 sm:w-12 rounded-xl bg-white/20 text-white font-black text-base sm:text-lg flex items-center justify-center border border-white/25 shadow-inner shrink-0">
                            {{ strtoupper(substr(trim($registration->candidate_name ?? 'A'), 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-white/60 uppercase tracking-wider block leading-tight">Calon Siswa</span>
                            <h4 class="font-black text-sm sm:text-lg text-white tracking-tight leading-snug truncate">
                                {{ $registration->candidate_name ?? 'Calon Siswa' }}
                            </h4>
                        </div>
                    </div>

                    <!-- No. Registrasi Badge di Pojok Kanan -->
                    @if($registration->id_label)
                        <div class="shrink-0 text-right">
                            <span class="text-[10px] font-bold text-white/60 uppercase tracking-wider hidden sm:inline-block mr-1.5">No. Registrasi:</span>
                            <span class="text-xs sm:text-sm font-mono font-extrabold text-emerald-200 bg-white/15 px-3 py-1.5 rounded-xl border border-white/20 inline-flex items-center gap-1.5 shadow-xs whitespace-nowrap">
                                <i data-lucide="tag" class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-emerald-300"></i> {{ $registration->id_label }}
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Bottom Row: Structured List / Grid of Metadata -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 sm:gap-4">
                    <!-- 1. Unit Sekolah -->
                    <div class="flex items-center gap-2.5 bg-white/10 sm:bg-white/5 px-3 py-2 rounded-xl border border-white/10">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0">
                            <i data-lucide="school" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[10px] text-white/60 font-semibold block leading-none">Unit Sekolah</span>
                            <span class="font-bold text-emerald-300 truncate block text-xs mt-1">{{ $registration->unit?->name ?? '-' }}</span>
                        </div>
                    </div>

                    <!-- 2. Tingkat & Kategori Murid -->
                    <div class="flex items-center gap-2.5 bg-white/10 sm:bg-white/5 px-3 py-2 rounded-xl border border-white/10">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center shrink-0">
                            <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[10px] text-white/60 font-semibold block leading-none">Kelas & Kategori</span>
                            <span class="font-bold text-white truncate block text-xs mt-1">{{ $registration->grade?->name ?? '-' }} ({{ $registration->classProgram?->name ?? 'Reguler' }})</span>
                        </div>
                    </div>

                    <!-- 3. Jalur & Gelombang -->
                    <div class="flex items-center gap-2.5 bg-white/10 sm:bg-white/5 px-3 py-2 rounded-xl border border-white/10">
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

        <style>
            @keyframes pulse-glow {
                0%, 100% {
                    box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2);
                }
                50% {
                    box-shadow: 0 0 0 6px rgba(5, 150, 105, 0.4);
                }
            }
            .animate-pulse-glow {
                animation: pulse-glow 2s infinite;
            }
        </style>

        <div class="p-6 sm:p-8 space-y-8">

            <!-- Horizontal Step Progress Timeline -->
            <div class="mb-6 sm:mb-12 mt-2 px-2 max-w-2xl mx-auto">
                <div class="relative flex items-center justify-between w-full">
                    <!-- Line connector background -->
                    <div class="absolute left-0 right-0 top-1/2 -translate-y-1/2 h-1 bg-slate-100 dark:bg-slate-800 rounded-full z-0"></div>
                    <!-- Line connector active progress -->
                    @php
                        $completedCount = $steps->where('is_completed', true)->count();
                        $totalCount = $steps->count();
                        $progressPercent = $totalCount > 1 ? ($completedCount / ($totalCount - 1)) * 100 : 0;
                        if ($progressPercent > 100) $progressPercent = 100;
                    @endphp
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-brand-emerald rounded-full z-0 transition-all duration-500 ease-out" style="width: {{ $progressPercent }}%;"></div>

                    @php
                        $canAccessStep = true;
                    @endphp
                    @foreach($steps as $index => $s)
                        @php
                            $isCompleted = $s->is_completed;
                            $isActive = (!$isCompleted && $canAccessStep);
                            if (!$isCompleted) {
                                $canAccessStep = false;
                            }
                        @endphp
                        <div class="relative flex flex-col items-center z-10">
                            <!-- Step Circle Indicator -->
                            <div class="h-9 w-9 rounded-full flex items-center justify-center font-extrabold text-xs transition-all duration-300 shadow-sm
                                {{ $isCompleted 
                                    ? 'bg-brand-emerald text-white ring-4 ring-emerald-50 dark:ring-emerald-950/20' 
                                    : ($isActive 
                                        ? 'bg-white border-2 border-brand-emerald text-brand-emerald ring-4 ring-emerald-100 dark:ring-emerald-900/30 animate-pulse-glow' 
                                        : 'bg-white border-2 border-slate-200 text-slate-400') }}">
                                @if($isCompleted)
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </div>
                            
                            <!-- Step Title Label (hidden on mobile to prevent overlapping) -->
                            <span class="hidden sm:block absolute top-11 text-[9px] sm:text-[10px] font-bold text-center whitespace-nowrap transition-colors duration-300 mt-0.5
                                {{ $isActive 
                                    ? 'text-brand-emerald dark:text-emerald-450' 
                                    : ($isCompleted ? 'text-slate-700 dark:text-slate-350' : 'text-slate-400') }}">
                                {{ $s->title }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            <!-- Checking if all steps are completed but registration is still draft or failed -->
            @if (in_array($registration->registration_status, ['draft', 'failed']) && $allStepsCompleted)
                @if ($registration->registration_status === 'failed')
                    <div class="bg-amber-500/10 dark:bg-amber-950/30 border border-amber-400/50 dark:border-amber-700/60 p-5 rounded-2xl flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-start gap-3.5">
                            <div class="h-10 w-10 rounded-2xl bg-amber-500/20 text-amber-500 dark:text-amber-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-sm text-slate-800 dark:text-amber-200">Perlu Perbaikan Formulir & Berkas</h3>
                                <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                    @if($registration->committee_notes && !str_contains($registration->committee_notes, 'berhasil'))
                                        <span class="font-bold text-amber-700 dark:text-amber-300">Catatan Panitia:</span> {{ $registration->committee_notes }}
                                    @else
                                        Panitia SPMB meminta perbaikan pada berkas atau data pendaftaran Anda. Silakan ubah bagian data yang ditandai merah di bawah, lalu kirim ulang formulir.
                                    @endif
                                </p>
                            </div>
                        </div>
                        <form action="{{ route('dashboard.form.submit', $registration->id) }}" method="POST" class="flex-shrink-0 w-full md:w-auto form-final-submit" data-action-type="revision">
                            @csrf
                            <button type="submit" class="w-full md:w-auto bg-amber-600 hover:bg-amber-700 dark:bg-amber-500 dark:hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-md transition flex items-center justify-center gap-1.5 whitespace-nowrap cursor-pointer">
                                <i data-lucide="send" class="w-4 h-4"></i> Kirim Ulang Pendaftaran
                            </button>
                        </form>
                    </div>
                @else
                    <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-brand-emerald/30 dark:border-emerald-800/60 p-5 rounded-2xl flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-start gap-3.5">
                            <div class="h-10 w-10 rounded-2xl bg-emerald-100 dark:bg-emerald-900/40 text-brand-emerald dark:text-emerald-400 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-sm text-slate-800 dark:text-white">Semua Data Selesai Diisi!</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-300 mt-1">Data Anda sudah tersimpan lengkap sebagai draf. Silakan kirimkan pendaftaran Anda untuk diverifikasi panitia.</p>
                            </div>
                        </div>
                        <form action="{{ route('dashboard.form.submit', $registration->id) }}" method="POST" class="flex-shrink-0 w-full md:w-auto form-final-submit" data-action-type="new">
                            @csrf
                            <button type="submit" class="w-full md:w-auto bg-brand-emerald hover-emerald text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-md transition flex items-center justify-center gap-1.5 whitespace-nowrap cursor-pointer">
                                <i data-lucide="send" class="w-4 h-4"></i> Kirim Pendaftaran Sekarang
                            </button>
                        </form>
                    </div>
                @endif
            @endif

            <!-- Checking if the form is locked (submitted or verified) -->
            @if ($registration->registration_status === 'submitted')
                <div class="bg-amber-50/70 dark:bg-amber-950/20 border border-amber-200/80 dark:border-amber-800/50 rounded-2xl p-5 text-center space-y-2.5">
                    <span class="inline-flex items-center justify-center h-10 w-10 bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-300 rounded-2xl shadow-xs">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </span>
                    <h3 class="font-extrabold text-slate-800 dark:text-white text-sm">Formulir Sedang Dalam Verifikasi</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 max-w-md mx-auto leading-relaxed">
                        Formulir pendaftaran Anda telah berhasil dikirim dan saat ini sedang dalam proses peninjauan berkas oleh Panitia SPMB. Formulir dikunci agar data tidak berubah selama verifikasi.
                    </p>
                    <div class="pt-1">
                        <a href="{{ route('dashboard.verification', $registration->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-emerald text-white text-xs font-bold rounded-xl hover-emerald shadow-xs transition">
                            <i data-lucide="shield-check" class="w-4 h-4 text-brand-yellow"></i>
                            <span>Pantau Status Verifikasi Berkas</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            @elseif (in_array($registration->registration_status, ['verified', 'taaruf_completed', 'agreement_signed', 'completed']))
                <div class="bg-emerald-50/70 dark:bg-emerald-950/20 border border-emerald-200/80 dark:border-emerald-800/50 rounded-2xl p-5 text-center space-y-2.5">
                    <span class="inline-flex items-center justify-center h-10 w-10 bg-emerald-100 dark:bg-emerald-900/40 text-brand-emerald dark:text-emerald-300 rounded-2xl shadow-xs">
                        <i data-lucide="check-check" class="w-5 h-5"></i>
                    </span>
                    <h3 class="font-extrabold text-slate-800 dark:text-white text-sm">Formulir & Berkas Telah Terverifikasi</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 max-w-md mx-auto leading-relaxed">
                        Alhamdulillah, seluruh data formulir dan berkas persyaratan ananda telah diverifikasi & disetujui oleh Panitia SPMB. Formulir dikunci untuk arsip resmi pendaftaran.
                    </p>
                    @if($registration->registration_status === 'verified')
                        <div class="pt-1 flex justify-center">
                            <a href="{{ route('dashboard.observation', $registration->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-emerald hover-emerald text-white text-xs font-bold rounded-xl shadow-md transition">
                                <span>Lanjut ke Ta'aruf & Observasi</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    @elseif(in_array($registration->registration_status, ['taaruf_completed', 'agreement_signed', 'completed']))
                        <div class="pt-1 flex justify-center">
                            <a href="{{ route('dashboard.result', $registration->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-emerald hover-emerald text-white text-xs font-bold rounded-xl shadow-md transition">
                                <span>Lanjut ke Administrasi</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            @php
                $canRenderStep = true;
                $candidateFullAddressGlobal = $registration->getFullCandidateAddress();
            @endphp
            <input type="hidden" id="candidate_full_address_data" value="{{ $candidateFullAddressGlobal }}">
            @foreach($steps as $index => $step)
                @if($canRenderStep || $registration->registration_status !== 'draft')
                    @php
                        $stepFieldNames = $step->fields->pluck('field_name')->toArray();
                        $hasInvalidFields = false;
                        if (is_array($registration->invalid_fields)) {
                            $hasInvalidFields = count(array_intersect($stepFieldNames, $registration->invalid_fields)) > 0;
                        }
                        $stepHasErrors = $errors->any() && count(array_intersect($stepFieldNames, array_keys($errors->messages()))) > 0;
                        $isCurrentActive = (!$step->is_completed && $registration->registration_status === 'draft');
                        $isAccordionItem = $step->is_completed && !$hasInvalidFields && !$stepHasErrors;
                    @endphp
                    <div class="border rounded-2xl transition-all duration-200 overflow-hidden {{ $hasInvalidFields ? 'border-red-400 dark:border-red-600 bg-red-50/5 dark:bg-red-950/20 ring-2 ring-red-200 dark:ring-red-900/40' : ($isCurrentActive ? 'border-brand-emerald bg-white dark:bg-slate-900 ring-4 ring-emerald-500/10 shadow-sm' : 'border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-slate-300 dark:hover:border-slate-700 shadow-xs') }}">
                        
                        @php
                            $hasActiveExtraServices = \App\Models\SpmbExtraService::where('is_active', true)
                                ->where(function($q) use ($registration) {
                                    $q->whereNull('spmb_unit_id')
                                      ->orWhere('spmb_unit_id', $registration->spmb_unit_id);
                                })
                                ->exists();

                            $stepDescriptions = [
                                1 => $hasActiveExtraServices
                                    ? 'Pilih kategori program belajar ananda serta layanan tambahan non-formal (jika tersedia).'
                                    : 'Pilih kategori program belajar untuk ananda.',
                                2 => 'Lengkapi identitas kependudukan, data kelahiran, dan riwayat sekolah calon murid.',
                                3 => 'Masukkan alamat domisili tempat tinggal calon murid saat ini secara lengkap dan akurat.',
                                4 => 'Isi data identitas orang tua kandung beserta nomor WhatsApp aktif untuk koordinasi resmi panitia.',
                                5 => 'Isi data berikut jika calon murid tinggal bersama wali (opsional, dapat dikosongkan jika bersama orang tua).',
                                6 => 'Unggah dokumen persyaratan pendaftaran seperti Akta Kelahiran, KK, dan Pas Foto (maks. 2MB per berkas).',
                            ];
                            $stepDesc = $stepDescriptions[$step->id] ?? $stepDescriptions[$index + 1] ?? 'Lengkapi formulir berikut dengan data yang benar.';
                        @endphp

                        @if ($isAccordionItem)
                            <!-- Accordion Header for Completed Step -->
                            <div onclick="toggleStepAccordion({{ $step->id }})" class="p-4 sm:p-5 flex items-center justify-between cursor-pointer select-none group transition bg-white dark:bg-slate-900 hover:bg-slate-50/70 dark:hover:bg-slate-800/60">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="h-8 w-8 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-brand-emerald dark:text-emerald-400 flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-xs">
                                        <i data-lucide="check" class="w-4 h-4 text-brand-emerald dark:text-emerald-400"></i>
                                    </div>
                                    <span class="font-extrabold text-sm text-slate-800 dark:text-white tracking-tight truncate group-hover:text-brand-emerald transition-colors">
                                        {{ $step->title }}
                                    </span>
                                </div>
                                
                                <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                                    <span class="text-[11px] bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60 px-2.5 py-0.5 rounded-full font-bold inline-flex items-center gap-1 shadow-xs">
                                        Tersimpan
                                    </span>
                                    @if ($registration->registration_status === 'draft' || $registration->registration_status === 'failed')
                                        <button type="button" onclick="event.stopPropagation(); openStepEdit({{ $step->id }});" class="text-xs text-brand-emerald dark:text-emerald-400 font-bold hover:underline px-2 py-1 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition">
                                            Ubah Data
                                        </button>
                                    @endif
                                    <div id="chevron-box-{{ $step->id }}" class="text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300 transition-transform duration-200 p-1">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Active Step Header / Error Header -->
                            <div class="p-5 pb-1">
                                <div class="flex justify-between items-start">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2.5">
                                            <span class="h-7 w-7 rounded-xl bg-brand-emerald text-white text-xs flex items-center justify-center font-black shadow-xs shrink-0">{{ $index + 1 }}</span>
                                            <h3 class="font-extrabold text-slate-800 dark:text-white text-sm sm:text-base tracking-tight">
                                                {{ $step->title }}
                                            </h3>
                                        </div>
                                        <p class="text-xs text-slate-400 dark:text-slate-400 font-medium pl-9.5 leading-relaxed">
                                            {{ $stepDesc }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        @if ($hasInvalidFields)
                                            <span class="text-[10px] bg-rose-100 text-rose-700 px-2.5 py-1 rounded-full font-bold flex items-center gap-1 shadow-sm border border-rose-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500 animate-pulse"></span> Perlu Perbaikan
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Form Block -->
                        @if ($registration->registration_status === 'draft' || $registration->registration_status === 'failed')
                            <form id="form-step-{{ $step->id }}" action="{{ route('dashboard.step.save', [$registration->id, $step->id]) }}" method="POST" enctype="multipart/form-data" class="form-step-ajax space-y-4 text-sm px-5 pb-5 pt-2 {{ $isAccordionItem ? 'pt-3 border-t border-slate-100 dark:border-slate-800 hidden' : '' }}" data-step-title="{{ $step->title }}" data-is-last="{{ $index === $steps->count() - 1 ? '1' : '0' }}">
                                @csrf

                                @if($stepHasErrors)
                                    <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800/60 text-red-700 dark:text-red-300 text-xs space-y-1.5 shadow-sm">
                                        <div class="flex items-center gap-2 font-bold text-red-800 dark:text-red-200">
                                            <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                                            <span>Terdapat data/berkas yang belum lengkap pada tahapan ini:</span>
                                        </div>
                                        <ul class="list-disc list-inside text-[11px] font-semibold pl-1 space-y-0.5">
                                            @foreach($stepFieldNames as $sfn)
                                                @if($errors->has($sfn))
                                                    @foreach($errors->get($sfn) as $msg)
                                                        <li>{{ $msg }}</li>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                                    @foreach($step->fields as $field)
                                        @php
                                            $val = $registration->getFieldValue($field->field_name);
                                            
                                            $isHiddenField = in_array($field->field_name, ['spmb_period_id', 'spmb_wave_id', 'spmb_type_id']);
                                        @endphp
                                        
                                        @if($isHiddenField)
                                            <input type="hidden" name="{{ $field->field_name }}" value="{{ $val }}">
                                            @continue
                                        @endif
                                        
                                        @php
                                            $uCode = strtoupper($registration->unit->code ?? '');
                                            $admLevel = strtoupper(trim($registration->admission_level ?? ''));
                                            $isKb = ($admLevel === 'KB');
                                            $isTkA = in_array($admLevel, ['TK A', 'TKA', 'TK-A']);
                                            $isTkB = in_array($admLevel, ['TK B', 'TKB', 'TK-B']);

                                            // Determine internal school name for previous_school
                                            $internalSchoolName = null;
                                            if ($uCode === 'PAUD') {
                                                if ($isTkA) {
                                                    $internalSchoolName = 'KB Anak Saleh';
                                                } elseif ($isTkB) {
                                                    $internalSchoolName = 'TK A Anak Saleh';
                                                } else {
                                                    $internalSchoolName = 'KB Anak Saleh';
                                                }
                                            } elseif ($uCode === 'SD') {
                                                $internalSchoolName = 'PAUD Terpadu Anak Saleh';
                                            } elseif ($uCode === 'SMP') {
                                                $internalSchoolName = 'SD Anak Saleh';
                                            } else {
                                                $internalSchoolName = 'Sekolah Anak Saleh';
                                            }
                                        @endphp

                                        @if($field->field_name === 'previous_school' && $uCode === 'PAUD' && $isKb)
                                            @continue
                                        @endif

                                        @if($field->field_name === 'extra_services')
                                            @php
                                                $activeServices = \App\Models\SpmbExtraService::where('is_active', true)
                                                     ->where(function($q) use ($registration) {
                                                         $q->whereNull('spmb_unit_id')
                                                           ->orWhere('spmb_unit_id', $registration->spmb_unit_id);
                                                     })
                                                     ->get();
                                            @endphp
                                            @if($activeServices->isEmpty())
                                                @continue
                                            @endif
                                            @php
                                                $selectedServiceIds = $registration->extraServices->pluck('id')->toArray();
                                            @endphp
                                        @endif
                                        
                                        @php
                                            $fieldLabel = $field->label;
                                            if ($field->field_name === 'spmb_class_program_id') {
                                                $fieldLabel = 'Kategori Murid';
                                            } elseif ($field->field_name === 'previous_school') {
                                                $fieldLabel = 'Asal Sekolah';
                                            }
                                            $isFullWidth = ($field->type === 'textarea') || ($field->type !== 'file' && strlen($fieldLabel) > 30) || $field->field_name === 'extra_services' || $field->field_name === 'previous_school' || in_array($field->field_name, ['father_address', 'mother_address', 'guardian_address']);
                                            $hasFieldError = $errors->has($field->field_name);
                                        @endphp

                                        @if($step->id == 4 && $field->field_name === 'father_name')
                                            <div class="md:col-span-2 flex items-center gap-2.5 pt-1 pb-2 border-b border-slate-200/70 dark:border-slate-800">
                                                <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-brand-emerald flex items-center justify-center text-xs shadow-xs">
                                                    👨
                                                </div>
                                                <div>
                                                    <h4 class="font-extrabold text-xs sm:text-sm text-slate-800 dark:text-white tracking-tight">Data Ayah Kandung</h4>
                                                    <p class="text-[10.5px] text-slate-400 font-medium">Informasi identitas dan kontak ayah kandung calon murid</p>
                                                </div>
                                            </div>
                                        @elseif($step->id == 4 && $field->field_name === 'mother_name')
                                            <div class="md:col-span-2 flex items-center gap-2.5 pt-4 pb-2 border-b border-slate-200/70 dark:border-slate-800 mt-2">
                                                <div class="w-7 h-7 rounded-lg bg-pink-100 dark:bg-pink-950/60 text-pink-600 flex items-center justify-center text-xs shadow-xs">
                                                    👩
                                                </div>
                                                <div>
                                                    <h4 class="font-extrabold text-xs sm:text-sm text-slate-800 dark:text-white tracking-tight">Data Ibu Kandung</h4>
                                                    <p class="text-[10.5px] text-slate-400 font-medium">Informasi identitas dan kontak ibu kandung calon murid</p>
                                                </div>
                                            </div>
                                        @elseif($step->id == 5 && $field->field_name === 'guardian_name')
                                            <div class="md:col-span-2 flex items-center gap-2.5 pt-1 pb-2 border-b border-slate-200/70 dark:border-slate-800">
                                                <div class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-600 flex items-center justify-center text-xs shadow-xs">
                                                    🤝
                                                </div>
                                                <div>
                                                    <h4 class="font-extrabold text-xs sm:text-sm text-slate-800 dark:text-white tracking-tight">Data Wali Murid (Opsional)</h4>
                                                    <p class="text-[10.5px] text-slate-400 font-medium">Isi jika calon murid tinggal bersama wali, kosongkan jika bersama orang tua</p>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="{{ $isFullWidth ? 'md:col-span-2' : '' }} flex flex-col justify-start">
                                            <div class="flex items-center justify-between mb-2">
                                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-200">
                                                    {{ $fieldLabel }}{{ $field->is_required ? '*' : '' }}
                                                </label>
                                                @if($field->field_name === 'guardian_address')
                                                    <button type="button" 
                                                            id="guardian_address_reset_btn_{{ $step->id }}" 
                                                            onclick="resetGuardianAddress('{{ $step->id }}')" 
                                                            class="{{ empty($val) ? 'hidden' : '' }} text-[11px] font-bold text-rose-500 hover:text-rose-600 dark:text-rose-400 inline-flex items-center gap-1 transition-colors px-2 py-0.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/30">
                                                        <i data-lucide="rotate-ccw" class="w-3 h-3"></i> Kosongkan Pilihan
                                                    </button>
                                                @endif
                                            </div>
                                            
                                            @if($field->field_name === 'extra_services')
                                                 <div class="flex flex-wrap gap-2.5 mt-1 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                                                     @foreach($activeServices as $service)
                                                         <label class="flex items-center gap-2 bg-white px-3.5 py-2.5 rounded-xl border border-slate-200 hover:border-brand-emerald cursor-pointer transition select-none">
                                                             <input type="checkbox" name="extra_services[]" value="{{ $service->id }}" {{ in_array($service->id, $selectedServiceIds) ? 'checked' : '' }} class="w-4 h-4 text-brand-emerald border-slate-300 rounded focus:ring-brand-emerald">
                                                             <span class="text-xs font-bold text-slate-700">
                                                                 {{ $service->name }}
                                                             </span>
                                                         </label>
                                                     @endforeach
                                                 </div>
                                            @elseif($field->field_name === 'previous_school')
                                                @php
                                                    $isInternal = (!empty($val) && $val === $internalSchoolName);
                                                    $isOther = (!empty($val) && $val !== $internalSchoolName);
                                                @endphp
                                                <div class="space-y-2.5 mt-1" id="prevSchoolWrapper_{{ $step->id }}">
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                                        <!-- Option 1: Internal School -->
                                                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 cursor-pointer hover:border-brand-emerald dark:hover:border-emerald-500 transition select-none shadow-xs group">
                                                            <input type="radio" name="prev_school_choice_{{ $step->id }}" value="{{ $internalSchoolName }}" {{ $isInternal ? 'checked' : '' }} onchange="handlePrevSchoolChoice(this, '{{ $step->id }}')" class="w-4 h-4 text-brand-emerald border-slate-300 dark:border-slate-700 focus:ring-brand-emerald">
                                                            <div class="flex items-center gap-2">
                                                                <span class="w-2 h-2 rounded-full bg-brand-emerald shrink-0"></span>
                                                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 group-hover:text-brand-emerald transition-colors">
                                                                    {{ $internalSchoolName }}
                                                                </span>
                                                            </div>
                                                        </label>

                                                        <!-- Option 2: Lainnya (Sekolah Luar) -->
                                                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 cursor-pointer hover:border-brand-emerald dark:hover:border-emerald-500 transition select-none shadow-xs group">
                                                            <input type="radio" name="prev_school_choice_{{ $step->id }}" value="__OTHER__" {{ $isOther ? 'checked' : '' }} onchange="handlePrevSchoolChoice(this, '{{ $step->id }}')" class="w-4 h-4 text-brand-emerald border-slate-300 dark:border-slate-700 focus:ring-brand-emerald">
                                                            <div class="flex items-center gap-2">
                                                                <span class="w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-600 shrink-0"></span>
                                                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 group-hover:text-brand-emerald transition-colors">
                                                                    Lainnya (Sekolah Luar)
                                                                </span>
                                                            </div>
                                                        </label>
                                                    </div>

                                                    <!-- Custom Input for previous_school if Lainnya is selected -->
                                                    <div id="prevSchoolCustomInputBox_{{ $step->id }}" class="{{ $isOther ? '' : 'hidden' }} space-y-1 transition-all duration-200">
                                                        <input type="text" id="prevSchoolCustomInput_{{ $step->id }}" placeholder="Ketik nama lengkap sekolah asal..." value="{{ $isOther ? $val : '' }}" oninput="syncPrevSchoolValue(this.value, '{{ $step->id }}')" class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-850 rounded-xl px-4 py-3 text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs shadow-xs">
                                                        <p class="text-[10px] text-slate-400 dark:text-slate-500 italic pl-1">Masukkan nama sekolah asal sebelumnya (contoh: TK Aisyiyah 1, SDN Klojen 1, dll).</p>
                                                    </div>

                                                    <input type="hidden" name="previous_school" id="realPreviousSchoolInput_{{ $step->id }}" value="{{ $val }}">
                                                </div>
                                            @elseif(in_array($field->field_name, ['father_address', 'mother_address']))
                                                @php
                                                    $parentType = str_replace('_address', '', $field->field_name); // 'father', 'mother'
                                                    $candidateFullAddress = $registration->getFullCandidateAddress();
                                                    $isSameAddress = empty($val) || ($val === $candidateFullAddress);
                                                @endphp
                                                <div class="space-y-2.5 mt-1" id="{{ $field->field_name }}_wrapper_{{ $step->id }}">
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                                        <!-- Option 1: Sama dengan Calon Murid -->
                                                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 cursor-pointer hover:border-brand-emerald dark:hover:border-emerald-500 transition select-none shadow-xs group">
                                                            <input type="radio" name="{{ $field->field_name }}_choice_{{ $step->id }}" value="same" {{ $isSameAddress ? 'checked' : '' }} onchange="handleParentAddressChoice('{{ $parentType }}', this.value, '{{ $step->id }}')" class="w-4 h-4 text-brand-emerald border-slate-300 dark:border-slate-700 focus:ring-brand-emerald">
                                                            <div class="flex items-center gap-2">
                                                                <span class="w-2 h-2 rounded-full bg-brand-emerald shrink-0"></span>
                                                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 group-hover:text-brand-emerald transition-colors">
                                                                    Sama dengan Calon Murid
                                                                </span>
                                                            </div>
                                                        </label>

                                                        <!-- Option 2: Alamat Berbeda (Ketik Sendiri) -->
                                                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 cursor-pointer hover:border-brand-emerald dark:hover:border-emerald-500 transition select-none shadow-xs group">
                                                            <input type="radio" name="{{ $field->field_name }}_choice_{{ $step->id }}" value="custom" {{ !$isSameAddress ? 'checked' : '' }} onchange="handleParentAddressChoice('{{ $parentType }}', this.value, '{{ $step->id }}')" class="w-4 h-4 text-brand-emerald border-slate-300 dark:border-slate-700 focus:ring-brand-emerald">
                                                            <div class="flex items-center gap-2">
                                                                <span class="w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-600 shrink-0"></span>
                                                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 group-hover:text-brand-emerald transition-colors">
                                                                    Alamat Berbeda (Ketik Sendiri)
                                                                </span>
                                                            </div>
                                                        </label>
                                                    </div>

                                                    <!-- Info Box when Same is selected -->
                                                    <div id="{{ $parentType }}_address_same_box_{{ $step->id }}" class="{{ $isSameAddress ? '' : 'hidden' }} p-3.5 bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/50 rounded-xl flex items-start gap-3 text-xs text-slate-600 dark:text-slate-300 transition-all shadow-xs">
                                                        <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-brand-emerald flex items-center justify-center shrink-0 mt-0.5">
                                                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                                                        </div>
                                                        <div>
                                                            <span class="font-bold text-slate-800 dark:text-white text-xs block">Alamat disamakan dengan domisili tempat tinggal calon murid:</span>
                                                            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block leading-relaxed" id="{{ $parentType }}_address_preview_{{ $step->id }}">{{ $candidateFullAddress ?: '(Sesuai alamat calon murid pada Tahap 3)' }}</span>
                                                        </div>
                                                    </div>

                                                    <!-- Custom Input Box when Custom is selected -->
                                                    <div id="{{ $parentType }}_address_custom_box_{{ $step->id }}" class="{{ $isSameAddress ? 'hidden' : '' }} space-y-1 transition-all duration-200">
                                                        <textarea id="{{ $parentType }}_address_custom_input_{{ $step->id }}" rows="2" placeholder="Masukkan alamat lengkap domisili {{ strtolower($fieldLabel) }}..." oninput="syncParentAddressValue('{{ $parentType }}', this.value, '{{ $step->id }}')" class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-850 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs shadow-xs">{{ !$isSameAddress ? $val : '' }}</textarea>
                                                        <p class="text-[10px] text-slate-400 dark:text-slate-500 italic pl-1">Isi jika domisili berbeda dengan alamat tempat tinggal calon murid.</p>
                                                    </div>

                                                    <!-- Hidden input for submission -->
                                                    <input type="hidden" name="{{ $field->field_name }}" id="real_{{ $parentType }}_address_{{ $step->id }}" value="{{ $isSameAddress ? $candidateFullAddress : $val }}">
                                                </div>
                                            @elseif($field->field_name === 'guardian_address')
                                                @php
                                                    $candidateFullAddress = $registration->getFullCandidateAddress();
                                                    $guardianChoice = '';
                                                    if (!empty($val)) {
                                                        if ($val === $candidateFullAddress) {
                                                            $guardianChoice = 'same';
                                                        } else {
                                                            $guardianChoice = 'custom';
                                                        }
                                                    }
                                                @endphp
                                                <div class="space-y-2.5 mt-1" id="guardian_address_wrapper_{{ $step->id }}">
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                                        <!-- Option 1: Sama dengan Calon Murid -->
                                                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 cursor-pointer hover:border-brand-emerald dark:hover:border-emerald-500 transition select-none shadow-xs group">
                                                            <input type="radio" 
                                                                   name="guardian_address_choice_{{ $step->id }}" 
                                                                   value="same" 
                                                                   data-was-checked="{{ $guardianChoice === 'same' ? 'true' : 'false' }}"
                                                                   {{ $guardianChoice === 'same' ? 'checked' : '' }} 
                                                                   onclick="handleParentAddressRadioClick(this, 'guardian', '{{ $step->id }}')" 
                                                                   class="w-4 h-4 text-brand-emerald border-slate-300 dark:border-slate-700 focus:ring-brand-emerald">
                                                            <div class="flex items-center gap-2">
                                                                <span class="w-2 h-2 rounded-full bg-brand-emerald shrink-0"></span>
                                                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 group-hover:text-brand-emerald transition-colors">
                                                                    Sama dengan Calon Murid
                                                                </span>
                                                            </div>
                                                        </label>

                                                        <!-- Option 2: Alamat Berbeda (Ketik Sendiri) -->
                                                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 cursor-pointer hover:border-brand-emerald dark:hover:border-emerald-500 transition select-none shadow-xs group">
                                                            <input type="radio" 
                                                                   name="guardian_address_choice_{{ $step->id }}" 
                                                                   value="custom" 
                                                                   data-was-checked="{{ $guardianChoice === 'custom' ? 'true' : 'false' }}"
                                                                   {{ $guardianChoice === 'custom' ? 'checked' : '' }} 
                                                                   onclick="handleParentAddressRadioClick(this, 'guardian', '{{ $step->id }}')" 
                                                                   class="w-4 h-4 text-brand-emerald border-slate-300 dark:border-slate-700 focus:ring-brand-emerald">
                                                            <div class="flex items-center gap-2">
                                                                <span class="w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-600 shrink-0"></span>
                                                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 group-hover:text-brand-emerald transition-colors">
                                                                    Alamat Berbeda (Ketik Sendiri)
                                                                </span>
                                                            </div>
                                                        </label>
                                                    </div>

                                                    <!-- Info Box when Same is selected -->
                                                    <div id="guardian_address_same_box_{{ $step->id }}" class="{{ $guardianChoice === 'same' ? '' : 'hidden' }} p-3.5 bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/50 rounded-xl flex items-start gap-3 text-xs text-slate-600 dark:text-slate-300 transition-all shadow-xs">
                                                        <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-brand-emerald flex items-center justify-center shrink-0 mt-0.5">
                                                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                                                        </div>
                                                        <div>
                                                            <span class="font-bold text-slate-800 dark:text-white text-xs block">Alamat disamakan dengan domisili tempat tinggal calon murid:</span>
                                                            <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 block leading-relaxed" id="guardian_address_preview_{{ $step->id }}">{{ $candidateFullAddress ?: '(Sesuai alamat calon murid pada Tahap 3)' }}</span>
                                                        </div>
                                                    </div>

                                                    <!-- Custom Input Box when Custom is selected -->
                                                    <div id="guardian_address_custom_box_{{ $step->id }}" class="{{ $guardianChoice === 'custom' ? '' : 'hidden' }} space-y-1 transition-all duration-200">
                                                        <textarea id="guardian_address_custom_input_{{ $step->id }}" rows="2" placeholder="Masukkan alamat lengkap domisili wali..." oninput="syncParentAddressValue('guardian', this.value, '{{ $step->id }}')" class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-850 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs shadow-xs">{{ $guardianChoice === 'custom' ? $val : '' }}</textarea>
                                                        <p class="text-[10px] text-slate-400 dark:text-slate-500 italic pl-1">Isi jika domisili wali berbeda dengan alamat tempat tinggal calon murid.</p>
                                                    </div>

                                                    <!-- Hidden input for submission -->
                                                    <input type="hidden" name="guardian_address" id="real_guardian_address_{{ $step->id }}" value="{{ $guardianChoice === 'same' ? $candidateFullAddress : ($guardianChoice === 'custom' ? $val : '') }}">
                                                </div>
                                            @elseif(in_array($field->field_name, ['province', 'city', 'kecamatan', 'kelurahan']))
                                                <div class="relative">
                                                    <select id="wilayah_{{ $field->field_name }}_{{ $step->id }}"
                                                            name="{{ $field->field_name }}"
                                                            data-step-id="{{ $step->id }}"
                                                            data-field="{{ $field->field_name }}"
                                                            data-current-val="{{ $val }}"
                                                            class="wilayah-select wilayah-{{ $field->field_name }} w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-850 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs transition duration-150 disabled:bg-slate-100 dark:disabled:bg-slate-900 disabled:text-slate-400 dark:disabled:text-slate-500 disabled:cursor-not-allowed shadow-xs"
                                                            {{ $field->is_required ? 'required' : '' }}
                                                            {{ ($field->field_name !== 'province' && empty($val)) ? 'disabled' : '' }}>
                                                        <option value="">-- Pilih {{ $fieldLabel }} --</option>
                                                        @if(!empty($val))
                                                            <option value="{{ $val }}" selected>{{ $val }}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            @elseif($field->type === 'select')
                                                @php
                                                    $ops = [];
                                                    if ($field->field_name === 'spmb_period_id') {
                                                        $ops = \App\Models\SpmbPeriod::where('is_active', true)->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->year]);
                                                    } elseif ($field->field_name === 'spmb_wave_id') {
                                                        $ops = \App\Models\SpmbWave::where('is_active', true)->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->name]);
                                                    } elseif ($field->field_name === 'spmb_type_id') {
                                                        $ops = \App\Models\SpmbType::where('is_active', true)->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->name]);
                                                    } elseif ($field->field_name === 'spmb_class_program_id') {
                                                        $ops = \App\Models\SpmbClassProgram::where('is_active', true)->get()->map(fn($item) => ['value' => $item->id, 'label' => $item->name]);
                                                    } elseif ($field->field_name === 'admission_level') {
                                                        $unitId = $registration->spmb_unit_id ?: 1;
                                                        $ops = \App\Models\SpmbGrade::where('spmb_unit_id', $unitId)
                                                            ->where('is_active', true)
                                                            ->get()
                                                            ->map(fn($item) => ['value' => $item->name, 'label' => $item->name]);
                                                    } elseif (!empty($field->options)) {
                                                        $ops = collect(explode(',', $field->options))->map(fn($o) => ['value' => trim($o), 'label' => trim($o)]);
                                                    }
                                                @endphp
                                                <select name="{{ $field->field_name }}" class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-850 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs" {{ $field->is_required ? 'required' : '' }}>
                                                    <option value="">-- Pilih {{ $fieldLabel }} --</option>
                                                    @foreach($ops as $op)
                                                        <option value="{{ $op['value'] }}" {{ $val == $op['value'] ? 'selected' : '' }}>{{ $op['label'] }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($field->type === 'textarea')
                                                <textarea name="{{ $field->field_name }}" rows="3" class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-850 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs" {{ $field->is_required ? 'required' : '' }}>{{ $val }}</textarea>
                                            @elseif($field->type === 'file')
                                                @php
                                                    $uniqueId = 'file_' . $field->id . '_' . $field->field_name;
                                                    $hasExisting = !empty($val);
                                                @endphp
                                                <div class="space-y-2 h-full flex flex-col mt-0.5" id="wrapper-{{ $uniqueId }}">
                                                    <!-- Drag & Drop Dropzone Box -->
                                                    <div id="dropzone-{{ $uniqueId }}" 
                                                         class="dropzone-box group relative border-2 border-dashed {{ $hasFieldError ? 'border-red-400 bg-red-50/40 ring-4 ring-red-500/20' : 'border-slate-300 dark:border-slate-750 hover:border-brand-emerald dark:hover:border-emerald-500 bg-slate-50/70 hover:bg-emerald-50/30 dark:bg-slate-900/40 dark:hover:bg-slate-900/80' }} rounded-2xl p-4 sm:p-5 transition-all duration-200 cursor-pointer text-center flex-1 flex flex-col items-center justify-center min-h-[125px] sm:min-h-[140px]"
                                                         data-input-id="input-{{ $uniqueId }}"
                                                         data-unique-id="{{ $uniqueId }}">
                                                        
                                                        <!-- Hidden Real File Input (without native required to prevent silent browser freeze) -->
                                                        <input type="file" 
                                                               id="input-{{ $uniqueId }}" 
                                                               name="{{ $field->field_name }}" 
                                                               class="hidden file-input-element" 
                                                               data-unique-id="{{ $uniqueId }}"
                                                               data-has-existing="{{ $hasExisting ? '1' : '0' }}"
                                                               data-is-required="{{ $field->is_required ? '1' : '0' }}"
                                                               data-label="{{ $fieldLabel }}"
                                                               accept=".pdf,.jpg,.jpeg,.png">

                                                        <!-- Empty / Prompt State -->
                                                        <div id="prompt-{{ $uniqueId }}" class="{{ $hasExisting ? 'hidden' : 'block' }} pointer-events-none w-full">
                                                            <div class="w-10 h-10 sm:w-11 sm:h-11 mx-auto mb-1.5 sm:mb-2 rounded-2xl {{ $hasFieldError ? 'bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400' : 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400' }} flex items-center justify-center group-hover:scale-110 transition-transform duration-200 shadow-sm">
                                                                <i data-lucide="{{ $hasFieldError ? 'alert-triangle' : 'cloud-upload' }}" class="w-5 h-5 sm:w-5.5 sm:h-5.5"></i>
                                                            </div>
                                                            <p class="text-[11.5px] sm:text-xs font-bold {{ $hasFieldError ? 'text-red-700 dark:text-red-300' : 'text-slate-700 dark:text-slate-200' }}">
                                                                <span class="{{ $hasFieldError ? 'text-red-700 dark:text-red-300 underline font-black' : 'text-brand-emerald dark:text-emerald-400 underline decoration-dashed font-extrabold' }} underline-offset-4">Klik untuk memilih</span> <span class="hidden sm:inline">atau seret file ke sini</span><span class="sm:hidden">berkas</span>
                                                            </p>
                                                            <p class="text-[9.5px] sm:text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">
                                                                Format: PDF, JPG, JPEG, PNG (Maks. 2 MB)
                                                            </p>
                                                        </div>

                                                        <!-- Existing File State (from Database) -->
                                                        @if($hasExisting)
                                                            <div id="existing-{{ $uniqueId }}" class="p-3 bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-750 flex items-center justify-between gap-2 text-left shadow-sm w-full max-w-full overflow-hidden">
                                                                <div class="flex items-center gap-2.5 min-w-0 flex-1 overflow-hidden">
                                                                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-brand-emerald flex items-center justify-center shrink-0">
                                                                        <i data-lucide="file-check-2" class="w-5 h-5"></i>
                                                                    </div>
                                                                    <div class="min-w-0 flex-1 overflow-hidden">
                                                                        <span class="text-xs font-bold text-slate-800 dark:text-white block truncate">Berkas Tersimpan</span>
                                                                        <span class="text-[10px] text-slate-400 font-mono block truncate mt-0.5 w-full" title="{{ basename($val) }}">{{ basename($val) }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="flex items-center gap-1.5 shrink-0 ml-1" onclick="event.stopPropagation()">
                                                                    <a href="{{ Storage::url($val) }}" target="_blank" class="px-2.5 py-1.5 bg-slate-100 hover:bg-brand-emerald hover:text-white text-slate-700 dark:bg-slate-800 dark:text-slate-300 rounded-lg text-xs font-bold transition flex items-center gap-1">
                                                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i> Lihat
                                                                    </a>
                                                                    <button type="button" onclick="document.getElementById('input-{{ $uniqueId }}').click()" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-300 rounded-lg text-xs font-bold transition">
                                                                        Ganti
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <!-- New Selected File Preview Box (Dynamic) -->
                                                        <div id="preview-{{ $uniqueId }}" class="hidden p-3 bg-emerald-50/80 dark:bg-emerald-950/40 rounded-xl border border-brand-emerald/40 text-left shadow-sm w-full max-w-full overflow-hidden">
                                                            <div class="flex items-center justify-between gap-2.5 w-full min-w-0">
                                                                <div class="flex items-center gap-2.5 min-w-0 flex-1 overflow-hidden">
                                                                    <!-- Thumbnail image or Document icon -->
                                                                    <div id="thumb-wrap-{{ $uniqueId }}" class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-700 flex items-center justify-center shrink-0 overflow-hidden shadow-xs">
                                                                        <img id="thumb-img-{{ $uniqueId }}" src="" class="hidden w-full h-full object-cover">
                                                                        <i id="doc-icon-{{ $uniqueId }}" data-lucide="file-text" class="w-5 h-5 sm:w-6 sm:h-6 text-brand-emerald"></i>
                                                                    </div>
                                                                    <div class="min-w-0 flex-1 overflow-hidden">
                                                                        <span id="file-name-{{ $uniqueId }}" class="text-xs font-bold text-slate-800 dark:text-white block truncate w-full">nama_file.pdf</span>
                                                                        <div class="flex items-center gap-1.5 mt-0.5">
                                                                            <span id="file-size-{{ $uniqueId }}" class="text-[10px] text-slate-500 font-mono shrink-0">1.2 MB</span>
                                                                            <span id="file-badge-{{ $uniqueId }}" class="text-[8.5px] bg-brand-emerald text-white px-1.5 py-0.2 rounded font-black uppercase shrink-0">PDF</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <button type="button" 
                                                                        onclick="event.stopPropagation(); window.resetFileInput('{{ $uniqueId }}', {{ $hasExisting ? 'true' : 'false' }})" 
                                                                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 dark:text-rose-400 flex items-center justify-center transition shrink-0 ml-1" 
                                                                        title="Batalkan File Ini">
                                                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                                                </button>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            @else
                                                <input type="{{ $field->type }}" name="{{ $field->field_name }}" value="{{ $val }}" class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-850 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs" {{ $field->is_required ? 'required' : '' }}>
                                            @endif

                                            @error($field->field_name)
                                                <span class="text-red-600 text-xs mt-1 block font-bold flex items-center gap-1">
                                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> {{ $message }}
                                                </span>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                                <div class="flex justify-between items-center pt-4 border-t border-slate-100 dark:border-slate-800">
                                    @if ($step->is_completed)
                                        <button type="button" onclick="cancelStepEdit({{ $step->id }})" class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 px-4 py-2 rounded-lg font-bold text-xs transition cursor-pointer">
                                            Batal
                                        </button>
                                    @else
                                        <div></div>
                                    @endif
                                    <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2 rounded-lg font-bold text-xs shadow-sm flex items-center gap-1.5 cursor-pointer">
                                        @if ($step->is_completed || $registration->registration_status === 'failed')
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                            <span>Simpan Perubahan</span>
                                        @elseif ($index === $steps->count() - 1)
                                            <i data-lucide="send" class="w-4 h-4"></i>
                                            <span>Simpan & Kirim Pendaftaran</span>
                                        @else
                                            <span>Simpan & Lanjut</span>
                                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                        @endif
                                    </button>
                                </div>
                            </form>
                        @endif

                        <!-- Readonly Block (Accordion Content) -->
                        @if ($step->is_completed)
                            <div id="readonly-step-{{ $step->id }}" class="hidden px-5 pb-5 pt-1 border-t border-slate-100 dark:border-slate-800 {{ $hasInvalidFields ? '!hidden' : '' }}">
                                @if($step->id == 4)
                                    <!-- Step 4: Data Orang Tua (Terpisah: Kartu Ayah & Kartu Ibu) -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                                        <!-- Card 1: 👨 Data Ayah Kandung -->
                                        <div class="bg-slate-50/80 dark:bg-slate-850/60 p-4 sm:p-5 rounded-2xl border border-slate-200/70 dark:border-slate-800 space-y-3.5 shadow-xs">
                                            <div class="flex items-center gap-2 pb-2.5 border-b border-slate-200/60 dark:border-slate-800 text-slate-800 dark:text-white font-extrabold text-xs sm:text-sm">
                                                <span class="w-6 h-6 rounded-lg bg-emerald-100 dark:bg-emerald-950/80 text-brand-emerald dark:text-emerald-400 flex items-center justify-center text-xs">👨</span>
                                                <span>Data Ayah Kandung</span>
                                            </div>
                                            <div class="space-y-2.5 text-xs">
                                                <div class="space-y-0.5">
                                                    <span class="text-slate-400 dark:text-slate-500 font-semibold block text-[11px]">Nama Ayah Kandung</span>
                                                    <span class="text-slate-800 dark:text-slate-200 font-bold">{{ $registration->father_name ?? '-' }}</span>
                                                </div>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                                    <div class="space-y-0.5">
                                                        <span class="text-slate-400 dark:text-slate-500 font-semibold block text-[11px]">NIK Ayah Kandung</span>
                                                        <span class="text-slate-800 dark:text-slate-200 font-bold font-mono">{{ $registration->father_nik ?? '-' }}</span>
                                                    </div>
                                                    <div class="space-y-0.5">
                                                        <span class="text-slate-400 dark:text-slate-500 font-semibold block text-[11px]">Pekerjaan Ayah</span>
                                                        <span class="text-slate-800 dark:text-slate-200 font-bold">{{ $registration->father_job ?? '-' }}</span>
                                                    </div>
                                                </div>
                                                <div class="space-y-0.5">
                                                    <span class="text-slate-400 dark:text-slate-500 font-semibold block text-[11px]">Handphone / WhatsApp Ayah</span>
                                                    <span class="text-slate-800 dark:text-slate-200 font-bold font-mono">{{ $registration->father_phone ?? '-' }}</span>
                                                </div>
                                                <div class="space-y-0.5 pt-1.5 border-t border-slate-200/40 dark:border-slate-800">
                                                    <span class="text-slate-400 dark:text-slate-500 font-semibold block text-[11px]">Alamat Ayah</span>
                                                    <span class="text-slate-800 dark:text-slate-200 font-bold leading-relaxed">{{ $registration->father_address ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Card 2: 👩 Data Ibu Kandung -->
                                        <div class="bg-slate-50/80 dark:bg-slate-850/60 p-4 sm:p-5 rounded-2xl border border-slate-200/70 dark:border-slate-800 space-y-3.5 shadow-xs">
                                            <div class="flex items-center gap-2 pb-2.5 border-b border-slate-200/60 dark:border-slate-800 text-slate-800 dark:text-white font-extrabold text-xs sm:text-sm">
                                                <span class="w-6 h-6 rounded-lg bg-pink-100 dark:bg-pink-950/80 text-pink-600 dark:text-pink-400 flex items-center justify-center text-xs">👩</span>
                                                <span>Data Ibu Kandung</span>
                                            </div>
                                            <div class="space-y-2.5 text-xs">
                                                <div class="space-y-0.5">
                                                    <span class="text-slate-400 dark:text-slate-500 font-semibold block text-[11px]">Nama Ibu Kandung</span>
                                                    <span class="text-slate-800 dark:text-slate-200 font-bold">{{ $registration->mother_name ?? '-' }}</span>
                                                </div>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                                    <div class="space-y-0.5">
                                                        <span class="text-slate-400 dark:text-slate-500 font-semibold block text-[11px]">NIK Ibu Kandung</span>
                                                        <span class="text-slate-800 dark:text-slate-200 font-bold font-mono">{{ $registration->mother_nik ?? '-' }}</span>
                                                    </div>
                                                    <div class="space-y-0.5">
                                                        <span class="text-slate-400 dark:text-slate-500 font-semibold block text-[11px]">Pekerjaan Ibu</span>
                                                        <span class="text-slate-800 dark:text-slate-200 font-bold">{{ $registration->mother_job ?? '-' }}</span>
                                                    </div>
                                                </div>
                                                <div class="space-y-0.5">
                                                    <span class="text-slate-400 dark:text-slate-500 font-semibold block text-[11px]">Handphone / WhatsApp Ibu</span>
                                                    <span class="text-slate-800 dark:text-slate-200 font-bold font-mono">{{ $registration->mother_phone ?? '-' }}</span>
                                                </div>
                                                <div class="space-y-0.5 pt-1.5 border-t border-slate-200/40 dark:border-slate-800">
                                                    <span class="text-slate-400 dark:text-slate-500 font-semibold block text-[11px]">Alamat Ibu</span>
                                                    <span class="text-slate-800 dark:text-slate-200 font-bold leading-relaxed">{{ $registration->mother_address ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($step->id == 5)
                                    <!-- Step 5: Data Wali (Opsional) -->
                                    @if(!empty($registration->guardian_name))
                                        <div class="bg-slate-50/80 dark:bg-slate-850/60 p-4 sm:p-5 rounded-2xl border border-slate-200/70 dark:border-slate-800 space-y-3.5 mt-3 shadow-xs">
                                            <div class="flex items-center gap-2 pb-2.5 border-b border-slate-200/60 dark:border-slate-800 text-slate-800 dark:text-white font-extrabold text-xs sm:text-sm">
                                                <span class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-950/80 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs">🤝</span>
                                                <span>Data Wali Murid</span>
                                            </div>
                                            <div class="space-y-2.5 text-xs">
                                                <div class="space-y-0.5">
                                                    <span class="text-slate-400 dark:text-slate-500 font-semibold block text-[11px]">Nama Wali</span>
                                                    <span class="text-slate-800 dark:text-slate-200 font-bold">{{ $registration->guardian_name }}</span>
                                                </div>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                                    <div class="space-y-0.5">
                                                        <span class="text-slate-400 dark:text-slate-500 font-semibold block text-[11px]">NIK Wali</span>
                                                        <span class="text-slate-800 dark:text-slate-200 font-bold font-mono">{{ $registration->guardian_nik ?? '-' }}</span>
                                                    </div>
                                                    <div class="space-y-0.5">
                                                        <span class="text-slate-400 dark:text-slate-500 font-semibold block text-[11px]">Pekerjaan Wali</span>
                                                        <span class="text-slate-800 dark:text-slate-200 font-bold">{{ $registration->guardian_job ?? '-' }}</span>
                                                    </div>
                                                </div>
                                                <div class="space-y-0.5">
                                                    <span class="text-slate-400 dark:text-slate-500 font-semibold block text-[11px]">Handphone / WhatsApp Wali</span>
                                                    <span class="text-slate-800 dark:text-slate-200 font-bold font-mono">{{ $registration->guardian_phone ?? '-' }}</span>
                                                </div>
                                                <div class="space-y-0.5 pt-1.5 border-t border-slate-200/40 dark:border-slate-800">
                                                    <span class="text-slate-400 dark:text-slate-500 font-semibold block text-[11px]">Alamat Wali</span>
                                                    <span class="text-slate-800 dark:text-slate-200 font-bold leading-relaxed">{{ $registration->guardian_address ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="mt-3 p-4 bg-slate-50/80 dark:bg-slate-850/60 rounded-2xl border border-slate-200/70 dark:border-slate-800 flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                                            <i data-lucide="info" class="w-4 h-4 text-slate-400 shrink-0"></i>
                                            <span>Tidak mengisi data wali (calon murid tinggal bersama orang tua kandung).</span>
                                        </div>
                                    @endif
                                @elseif($step->id == 6)
                                    <!-- Step 6: Data Lampiran -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 mt-3">
                                        @foreach($step->fields as $field)
                                            @php
                                                $val = $registration->getFieldValue($field->field_name);
                                            @endphp
                                            <div class="p-3.5 bg-slate-50/80 dark:bg-slate-850/60 rounded-xl border border-slate-200/70 dark:border-slate-800 space-y-2 flex flex-col justify-between">
                                                <div>
                                                    <span class="text-slate-400 dark:text-slate-500 font-semibold block text-[11px] truncate" title="{{ $field->label }}">{{ $field->label }}</span>
                                                </div>
                                                @if(!empty($val))
                                                    <div class="flex items-center justify-between pt-1 border-t border-slate-200/40 dark:border-slate-800">
                                                        <a href="{{ Storage::url($val) }}" target="_blank" class="inline-flex items-center gap-1.5 text-brand-emerald dark:text-emerald-400 font-bold hover:underline text-xs">
                                                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Lihat Berkas
                                                        </a>
                                                    </div>
                                                @else
                                                    <span class="text-slate-400 italic text-[11px] pt-1">Tidak diunggah</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <!-- Step 1, 2, 3: Other Steps -->
                                    <div class="text-xs text-slate-600 dark:text-slate-300 grid grid-cols-1 md:grid-cols-2 gap-3.5 mt-3 bg-slate-50/80 dark:bg-slate-850/60 p-4 rounded-xl border border-slate-200/70 dark:border-slate-800">
                                        @foreach($step->fields as $field)
                                            @php
                                                $isHiddenField = in_array($field->field_name, ['spmb_period_id', 'spmb_wave_id', 'spmb_type_id']);
                                            @endphp
                                            @if($isHiddenField)
                                                @continue
                                            @endif
                                            @if($field->field_name === 'extra_services')
                                                @php
                                                    $services = $registration->extraServices;
                                                    $hasActiveServices = \App\Models\SpmbExtraService::where('is_active', true)
                                                         ->where(function($q) use ($registration) {
                                                             $q->whereNull('spmb_unit_id')
                                                               ->orWhere('spmb_unit_id', $registration->spmb_unit_id);
                                                         })
                                                         ->exists();
                                                @endphp
                                                @if(!$hasActiveServices && $services->isEmpty())
                                                    @continue
                                                @endif
                                            @endif
                                            @if($field->field_name === 'previous_school')
                                                @php
                                                    $uCode = strtoupper($registration->unit->code ?? '');
                                                    $admLevel = strtoupper(trim($registration->admission_level ?? ''));
                                                    $isKb = ($admLevel === 'KB');
                                                @endphp
                                                @if($uCode === 'PAUD' && $isKb)
                                                    @continue
                                                @endif
                                            @endif
                                            @php
                                                $val = $registration->getFieldValue($field->field_name);
                                                $isFullWidthSummary = in_array($field->field_name, ['address', 'extra_services']);
                                            @endphp
                                            <div class="space-y-0.5 {{ $isFullWidthSummary ? 'md:col-span-2' : '' }}">
                                                <strong class="text-slate-400 dark:text-slate-500 font-semibold block text-[11px]">{{ $field->label }}:</strong> 
                                                @if($field->type === 'file' && !empty($val))
                                                    <a href="{{ Storage::url($val) }}" target="_blank" class="text-brand-emerald dark:text-emerald-400 font-bold hover:underline">Lihat Berkas 📄</a>
                                                @elseif($field->field_name === 'spmb_class_program_id')
                                                    <span class="text-slate-800 dark:text-slate-200 font-bold">{{ $registration->classProgram?->name ?? '-' }}</span>
                                                @elseif($field->field_name === 'extra_services')
                                                    @php
                                                        $services = $registration->extraServices;
                                                    @endphp
                                                    @if($services->isEmpty())
                                                        <span class="text-slate-800 dark:text-slate-200 font-bold">Tidak Ada</span>
                                                    @else
                                                        <div class="flex flex-wrap gap-1.5 mt-1">
                                                            @foreach($services as $s)
                                                                <span class="inline-flex items-center gap-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-2.5 py-1 rounded-lg text-slate-800 dark:text-slate-200 font-bold text-xs">
                                                                    <span class="w-1.5 h-1.5 rounded-full bg-brand-emerald"></span>
                                                                    {{ $s->name }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-slate-800 dark:text-slate-200 font-bold">{{ $val ?? '-' }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
                @php
                    if (!$step->is_completed) {
                        $canRenderStep = false;
                    }
                @endphp
            @endforeach
        </div>
    </div>
</div>
    <!-- Upload & Submission Progress Modal Overlay -->
    <div id="uploadProgressModal" class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-md hidden flex items-center justify-center p-4 transition-all duration-300">
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-slate-100 dark:border-slate-800 text-center space-y-5 animate-scale-in">
            
            <!-- Animated Graphic -->
            <div class="relative w-20 h-20 mx-auto">
                <div id="uploadPingAnim" class="absolute inset-0 rounded-full bg-emerald-500/20 dark:bg-emerald-400/20 animate-ping"></div>
                <div id="uploadIconCircle" class="relative w-20 h-20 rounded-full bg-emerald-600 text-white flex items-center justify-center shadow-xl shadow-emerald-600/30" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%) !important;">
                    <div id="modalIconContainer" class="flex items-center justify-center text-white w-full h-full" style="color: #ffffff !important;">
                        <svg class="w-10 h-10 text-white animate-bounce" style="stroke: #ffffff; color: #ffffff;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/>
                            <path d="M12 12v9"/>
                            <path d="m16 16-4-4-4 4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div>
                <h3 id="uploadModalTitle" class="text-base sm:text-lg font-extrabold text-slate-800 dark:text-white">Mengirimkan Pendaftaran...</h3>
                <p id="uploadModalSubtitle" class="text-xs text-slate-500 dark:text-slate-400 mt-1">Harap tidak menutup atau merefresh halaman saat proses berlangsung.</p>
            </div>

            <!-- Progress Bar Section -->
            <div class="space-y-2 bg-slate-50 dark:bg-slate-850 p-4 rounded-2xl border border-slate-200/70 dark:border-slate-800">
                <div class="flex justify-between items-center text-xs font-bold">
                    <span id="uploadModalStatus" class="text-brand-emerald flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-brand-emerald animate-ping"></span> Memproses data...
                    </span>
                    <span id="uploadModalPercent" class="text-slate-800 dark:text-white font-mono text-sm font-black">0%</span>
                </div>
                <div class="h-3 w-full bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden p-0.5">
                    <div id="uploadModalBar" class="h-full bg-gradient-to-r from-brand-emerald via-emerald-400 to-teal-400 rounded-full transition-all duration-150 ease-out shadow-sm" style="width: 0%;"></div>
                </div>
                <div class="flex justify-between items-center text-[10px] text-slate-400 font-mono">
                    <span id="uploadFileNameDetail">Memproses berkas</span>
                    <span id="uploadModalBytes">0 KB / 0 KB</span>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Helper functions for modal & formatting
            function formatBytes(bytes, decimals = 2) {
                if (!bytes || bytes === 0) return '0 Bytes';
                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            }

            const progressModal = document.getElementById('uploadProgressModal');
            const progressBar = document.getElementById('uploadModalBar');
            const progressPercent = document.getElementById('uploadModalPercent');
            const progressBytes = document.getElementById('uploadModalBytes');
            const progressStatus = document.getElementById('uploadModalStatus');
            const progressTitle = document.getElementById('uploadModalTitle');
            const progressSubtitle = document.getElementById('uploadModalSubtitle');
            const progressDetail = document.getElementById('uploadFileNameDetail');
            const uploadPingAnim = document.getElementById('uploadPingAnim');
            const modalIconContainer = document.getElementById('modalIconContainer');

            function showProgressModal({ title, subtitle, statusText, detailText, bytesText = '', iconType = 'upload' }) {
                if (progressTitle) progressTitle.innerText = title;
                if (progressSubtitle) progressSubtitle.innerText = subtitle;
                if (progressStatus) progressStatus.innerHTML = '<span class="w-2 h-2 rounded-full bg-brand-emerald animate-ping"></span> ' + statusText;
                if (progressDetail) progressDetail.innerText = detailText;
                if (progressBytes) progressBytes.innerText = bytesText;
                if (progressBar) progressBar.style.width = '0%';
                if (progressPercent) progressPercent.innerText = '0%';

                if (modalIconContainer) {
                    if (iconType === 'send') {
                        modalIconContainer.innerHTML = `
                            <svg class="w-10 h-10 text-white animate-bounce" style="stroke: #ffffff; color: #ffffff;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>`;
                    } else if (iconType === 'save') {
                        modalIconContainer.innerHTML = `
                            <svg class="w-10 h-10 text-white animate-bounce" style="stroke: #ffffff; color: #ffffff;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>`;
                    } else {
                        modalIconContainer.innerHTML = `
                            <svg class="w-10 h-10 text-white animate-bounce" style="stroke: #ffffff; color: #ffffff;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/>
                                <path d="M12 12v9"/>
                                <path d="m16 16-4-4-4 4"/>
                            </svg>`;
                    }
                }

                if (uploadPingAnim) uploadPingAnim.classList.remove('hidden');
                if (progressModal) progressModal.classList.remove('hidden');
            }

            function updateProgress(percentVal, statusText = null, detailText = null, bytesText = null) {
                if (progressBar) progressBar.style.width = percentVal + '%';
                if (progressPercent) progressPercent.innerText = percentVal + '%';
                if (statusText && progressStatus) progressStatus.innerHTML = statusText;
                if (detailText && progressDetail) progressDetail.innerText = detailText;
                if (bytesText && progressBytes) progressBytes.innerText = bytesText;
            }

            function completeProgress(successTitle, successStatus, redirectUrl, toastMsg = null) {
                updateProgress(100);
                if (progressTitle) progressTitle.innerText = successTitle;
                if (progressStatus) progressStatus.innerHTML = '✅ ' + successStatus;
                if (uploadPingAnim) uploadPingAnim.classList.add('hidden');
                if (modalIconContainer) {
                    modalIconContainer.innerHTML = `
                        <svg class="w-10 h-10 text-white" style="stroke: #ffffff; color: #ffffff;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>`;
                }

                if (toastMsg) {
                    try {
                        sessionStorage.setItem('pendingToast', JSON.stringify({ message: toastMsg, type: 'success' }));
                    } catch(e) {}
                }

                setTimeout(() => {
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    } else {
                        window.location.reload();
                    }
                }, 700);
            }

            function hideProgressModal() {
                if (progressModal) progressModal.classList.add('hidden');
            }

            // 1. Drag & Drop and File Selection Handlers
            const dropzoneBoxes = document.querySelectorAll('.dropzone-box');
            
            dropzoneBoxes.forEach(box => {
                const inputId = box.getAttribute('data-input-id');
                const uniqueId = box.getAttribute('data-unique-id');
                const input = document.getElementById(inputId);

                if (!input) return;

                // Click to select
                box.addEventListener('click', (e) => {
                    if (e.target.closest('button') || e.target.closest('a')) return;
                    input.click();
                });

                // Drag and Drop Events
                ['dragenter', 'dragover'].forEach(eventName => {
                    box.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        box.classList.add('border-brand-emerald', 'bg-emerald-50/60', 'ring-4', 'ring-emerald-500/20', 'scale-[1.01]');
                    });
                });

                ['dragleave', 'dragend', 'drop'].forEach(eventName => {
                    box.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        box.classList.remove('border-brand-emerald', 'bg-emerald-50/60', 'ring-4', 'ring-emerald-500/20', 'scale-[1.01]');
                    });
                });

                box.addEventListener('drop', (e) => {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    if (files && files.length > 0) {
                        input.files = files;
                        handleFileSelection(input, files[0], uniqueId);
                    }
                });

                input.addEventListener('change', (e) => {
                    if (input.files && input.files[0]) {
                        handleFileSelection(input, input.files[0], uniqueId);
                    }
                });
            });

            function handleFileSelection(input, file, uniqueId) {
                // Size validation: max 2MB
                if (file.size > 2 * 1024 * 1024) {
                    alert('Ukuran file "' + file.name + '" terlalu besar (' + formatBytes(file.size) + '). Maksimal ukuran file adalah 2 MB.');
                    input.value = '';
                    return;
                }

                // Extension validation
                const ext = file.name.split('.').pop().toLowerCase();
                const allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                if (!allowed.includes(ext)) {
                    alert('Format file tidak didukung. Harap unggah file PDF, JPG, JPEG, atau PNG.');
                    input.value = '';
                    return;
                }

                const promptBox = document.getElementById('prompt-' + uniqueId);
                const existingBox = document.getElementById('existing-' + uniqueId);
                const previewBox = document.getElementById('preview-' + uniqueId);
                const dropzone = document.getElementById('dropzone-' + uniqueId);

                // Clear any previous error highlighting
                if (dropzone) {
                    dropzone.classList.remove('border-red-400', 'bg-red-50/40', 'ring-4', 'ring-red-500/20');
                    const errNode = dropzone.parentElement.querySelector('.client-file-error');
                    if (errNode) errNode.remove();
                }

                const fileNameEl = document.getElementById('file-name-' + uniqueId);
                const fileSizeEl = document.getElementById('file-size-' + uniqueId);
                const fileBadgeEl = document.getElementById('file-badge-' + uniqueId);
                const thumbImg = document.getElementById('thumb-img-' + uniqueId);
                const docIcon = document.getElementById('doc-icon-' + uniqueId);

                if (fileNameEl) {
                    fileNameEl.innerText = file.name;
                    fileNameEl.title = file.name;
                }
                if (fileSizeEl) fileSizeEl.innerText = formatBytes(file.size);
                if (fileBadgeEl) fileBadgeEl.innerText = ext.toUpperCase();

                // Thumbnail preview if image
                if (['jpg', 'jpeg', 'png'].includes(ext)) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (thumbImg) {
                            thumbImg.src = e.target.result;
                            thumbImg.classList.remove('hidden');
                        }
                        if (docIcon) docIcon.classList.add('hidden');
                    };
                    reader.readAsDataURL(file);
                } else {
                    if (thumbImg) thumbImg.classList.add('hidden');
                    if (docIcon) docIcon.classList.remove('hidden');
                }

                if (promptBox) promptBox.classList.add('hidden');
                if (existingBox) existingBox.classList.add('hidden');
                if (previewBox) previewBox.classList.remove('hidden');

                if (window.lucide) {
                    lucide.createIcons();
                }
            }

            window.resetFileInput = function(uniqueId, hasExisting) {
                const input = document.getElementById('input-' + uniqueId);
                if (input) input.value = '';

                const promptBox = document.getElementById('prompt-' + uniqueId);
                const existingBox = document.getElementById('existing-' + uniqueId);
                const previewBox = document.getElementById('preview-' + uniqueId);
                const dropzone = document.getElementById('dropzone-' + uniqueId);

                if (dropzone) {
                    dropzone.classList.remove('border-red-400', 'bg-red-50/40', 'ring-4', 'ring-red-500/20');
                    const errNode = dropzone.parentElement.querySelector('.client-file-error');
                    if (errNode) errNode.remove();
                }

                if (previewBox) previewBox.classList.add('hidden');

                if (hasExisting && existingBox) {
                    existingBox.classList.remove('hidden');
                    if (promptBox) promptBox.classList.add('hidden');
                } else if (promptBox) {
                    promptBox.classList.remove('hidden');
                }
            };

            // 2. Final Registration Submit Handler (from Banner "Kirim Pendaftaran Sekarang" / "Kirim Ulang Pendaftaran")
            const finalSubmitForms = document.querySelectorAll('.form-final-submit, form[action*="/form/submit"]');
            finalSubmitForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const isRevision = form.getAttribute('data-action-type') === 'revision';
                    const defaultMsg = isRevision ? 'Formulir perbaikan berhasil dikirim kembali!' : 'Formulir pendaftaran berhasil dikirim!';

                    showProgressModal({
                        title: isRevision ? 'Mengirimkan Perbaikan Pendaftaran...' : 'Mengirimkan Pendaftaran...',
                        subtitle: 'Data & berkas pendaftaran Anda sedang dikirimkan ke Panitia SPMB.',
                        statusText: 'Mengirimkan formulir pendaftaran...',
                        detailText: 'Sinkronisasi berkas & data',
                        bytesText: '100% Siap',
                        iconType: 'send'
                    });

                    // Simulated smooth progress while network request runs
                    let currentProg = 20;
                    updateProgress(currentProg, '<span class="w-2 h-2 rounded-full bg-brand-emerald animate-ping"></span> Mengirimkan formulir...');
                    
                    const progInterval = setInterval(() => {
                        if (currentProg < 88) {
                            currentProg += Math.floor(Math.random() * 12) + 6;
                            if (currentProg > 88) currentProg = 88;
                            updateProgress(currentProg, '<span class="w-2 h-2 rounded-full bg-brand-emerald animate-ping"></span> Memproses pengiriman ke panitia...');
                        }
                    }, 140);

                    const formData = new FormData(form);
                    const xhr = new XMLHttpRequest();

                    xhr.addEventListener('load', function() {
                        clearInterval(progInterval);
                        if (xhr.status >= 200 && xhr.status < 300) {
                            let redirectUrl = null;
                            let resMsg = defaultMsg;
                            try {
                                const res = JSON.parse(xhr.responseText);
                                redirectUrl = res.redirect;
                                if (res.message) resMsg = res.message;
                            } catch (e) {}

                            completeProgress(
                                isRevision ? 'Perbaikan Berhasil Dikirim!' : 'Pendaftaran Berhasil Dikirim!',
                                'Data pendaftaran tersimpan & sedang ditinjau panitia',
                                redirectUrl,
                                resMsg
                            );
                        } else {
                            hideProgressModal();
                            try {
                                const errRes = JSON.parse(xhr.responseText);
                                if (errRes.message) {
                                    alert('Gagal: ' + errRes.message);
                                } else {
                                    alert('Gagal mengirimkan formulir pendaftaran. Silakan coba kembali.');
                                }
                            } catch(e) {
                                alert('Gagal mengirimkan formulir pendaftaran.');
                            }
                        }
                    });

                    xhr.addEventListener('error', function() {
                        clearInterval(progInterval);
                        hideProgressModal();
                        alert('Koneksi terputus saat mengirimkan pendaftaran. Silakan periksa jaringan internet Anda.');
                    });

                    xhr.open(form.method || 'POST', form.action, true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.send(formData);
                });
            });

            // 3. Step Forms Submission with Upload Progress & Smooth Transition
            const stepForms = document.querySelectorAll('form.form-step-ajax, form[id^="form-step-"], form[enctype="multipart/form-data"]');

            stepForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    // Client-Side Validation for Required File Inputs
                    let missingRequiredFile = false;
                    let firstMissingEl = null;

                    const fileInputs = form.querySelectorAll('.file-input-element');
                    fileInputs.forEach(fi => {
                        const isRequired = fi.getAttribute('data-is-required') === '1';
                        const hasExisting = fi.getAttribute('data-has-existing') === '1';
                        const hasNewFile = (fi.files && fi.files.length > 0);
                        const uniqueId = fi.getAttribute('data-unique-id');
                        const label = fi.getAttribute('data-label') || 'Berkas';
                        const dropzone = document.getElementById('dropzone-' + uniqueId);

                        if (isRequired && !hasExisting && !hasNewFile) {
                            missingRequiredFile = true;
                            if (!firstMissingEl) firstMissingEl = dropzone || fi;

                            if (dropzone) {
                                dropzone.classList.add('border-red-400', 'bg-red-50/40', 'ring-4', 'ring-red-500/20');
                                let errNode = dropzone.parentElement.querySelector('.client-file-error');
                                if (!errNode) {
                                    errNode = document.createElement('div');
                                    errNode.className = 'client-file-error text-red-600 text-xs mt-1.5 font-bold flex items-center gap-1';
                                    errNode.innerHTML = `<i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> ${label} wajib diunggah!`;
                                    dropzone.parentElement.appendChild(errNode);
                                }
                            }
                        } else {
                            if (dropzone) {
                                dropzone.classList.remove('border-red-400', 'bg-red-50/40', 'ring-4', 'ring-red-500/20');
                                const errNode = dropzone.parentElement.querySelector('.client-file-error');
                                if (errNode) errNode.remove();
                            }
                        }
                    });

                    if (missingRequiredFile) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (typeof showToast === 'function') {
                            showToast('Mohon lengkapi semua berkas lampiran yang wajib diunggah!', 'error');
                        } else {
                            alert('Mohon lengkapi semua berkas lampiran yang wajib diunggah!');
                        }
                        if (firstMissingEl) {
                            firstMissingEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        if (window.lucide) lucide.createIcons();
                        return false;
                    }

                    e.preventDefault();

                    let hasSelectedNewFiles = false;
                    let newFileCount = 0;
                    let totalFileSize = 0;
                    fileInputs.forEach(fi => {
                        if (fi.files && fi.files.length > 0) {
                            hasSelectedNewFiles = true;
                            newFileCount += fi.files.length;
                            totalFileSize += fi.files[0].size;
                        }
                    });

                    const isLastStep = form.getAttribute('data-is-last') === '1';
                    const stepTitle = form.getAttribute('data-step-title') || 'Formulir';
                    const defaultMsg = 'Langkah "' + stepTitle + '" berhasil disimpan.';

                    if (hasSelectedNewFiles) {
                        showProgressModal({
                            title: 'Mengunggah Dokumen Lampiran...',
                            subtitle: 'Harap tidak menutup atau merefresh halaman saat proses upload berlangsung.',
                            statusText: 'Mengunggah berkas lampiran...',
                            detailText: newFileCount + ' Berkas Baru Dipilih',
                            bytesText: '0 KB / ' + formatBytes(totalFileSize),
                            iconType: 'upload'
                        });
                    } else {
                        showProgressModal({
                            title: isLastStep ? 'Simpan & Kirim Pendaftaran...' : 'Menyimpan ' + stepTitle + '...',
                            subtitle: 'Data isian formulir Anda sedang dikirim dan disimpan ke server.',
                            statusText: 'Menyimpan data formulir...',
                            detailText: 'Menyimpan isian data',
                            bytesText: 'Data Formulir',
                            iconType: isLastStep ? 'send' : 'save'
                        });
                    }

                    const formData = new FormData(form);
                    const xhr = new XMLHttpRequest();
                    let progInterval = null;

                    if (hasSelectedNewFiles) {
                        // Real upload progress tracking
                        xhr.upload.addEventListener('progress', function(event) {
                            if (event.lengthComputable) {
                                const percentComplete = Math.round((event.loaded / event.total) * 100);
                                updateProgress(
                                    percentComplete,
                                    percentComplete >= 100 
                                        ? '⚡ Menyimpan berkas ke server...' 
                                        : '<span class="w-2 h-2 rounded-full bg-brand-emerald animate-ping"></span> Mengunggah berkas lampiran...',
                                    newFileCount + ' Berkas Sedang Diunggah',
                                    formatBytes(event.loaded) + ' / ' + formatBytes(event.total)
                                );
                            }
                        });
                    } else {
                        // Simulated progress animation for instant feel
                        let currentProg = 25;
                        updateProgress(currentProg, '<span class="w-2 h-2 rounded-full bg-brand-emerald animate-ping"></span> Mengirim data ke server...');
                        progInterval = setInterval(() => {
                            if (currentProg < 88) {
                                currentProg += Math.floor(Math.random() * 15) + 8;
                                if (currentProg > 88) currentProg = 88;
                                updateProgress(currentProg, '<span class="w-2 h-2 rounded-full bg-brand-emerald animate-ping"></span> Menyimpan data...');
                            }
                        }, 120);
                    }

                    xhr.addEventListener('load', function() {
                        if (progInterval) clearInterval(progInterval);
                        if (xhr.status >= 200 && xhr.status < 300) {
                            let redirectUrl = null;
                            let resMsg = defaultMsg;
                            try {
                                const res = JSON.parse(xhr.responseText);
                                redirectUrl = res.redirect;
                                if (res.message) resMsg = res.message;
                            } catch (e) {}

                            completeProgress(
                                hasSelectedNewFiles ? 'Upload Berkas Selesai!' : (isLastStep ? 'Pendaftaran Berhasil Dikirim!' : 'Data Berhasil Disimpan!'),
                                'Perubahan telah berhasil disimpan',
                                redirectUrl,
                                resMsg
                            );
                        } else {
                            hideProgressModal();
                            try {
                                const errRes = JSON.parse(xhr.responseText);
                                if (errRes.errors) {
                                    const firstKey = Object.keys(errRes.errors)[0];
                                    alert('Gagal menyimpan: ' + errRes.errors[firstKey][0]);
                                } else if (errRes.message) {
                                    alert('Gagal: ' + errRes.message);
                                } else {
                                    alert('Gagal menyimpan data formulir. Silakan coba kembali.');
                                }
                            } catch (e) {
                                alert('Terjadi kesalahan saat menyimpan formulir.');
                            }
                        }
                    });

                    xhr.addEventListener('error', function() {
                        if (progInterval) clearInterval(progInterval);
                        hideProgressModal();
                        alert('Koneksi terputus saat menyimpan formulir. Periksa internet Anda dan coba lagi.');
                    });

                    xhr.open(form.method || 'POST', form.action, true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.send(formData);
                });
            });

            // 4. Highlight Invalid Fields on Verification Return
            const invalidFields = @json($registration->invalid_fields ?? []);
            if (invalidFields && invalidFields.length > 0) {
                invalidFields.forEach(field => {
                    highlightFieldInput(field);
                });
            }

            const urlParams = new URLSearchParams(window.location.search);
            const highlightField = urlParams.get('highlight');
            const targetStep = urlParams.get('step');

            if (targetStep) {
                const formStep = document.getElementById('form-step-' + targetStep);
                const readonlyStep = document.getElementById('readonly-step-' + targetStep);
                if (formStep && readonlyStep) {
                    readonlyStep.classList.add('hidden');
                    formStep.classList.remove('hidden');
                }
            }

            if (highlightField) {
                highlightFieldInput(highlightField, true);
            }

            function highlightFieldInput(fieldName, scrollToIt = false) {
                const targetInput = document.querySelector(`[name="${fieldName}"], [name="${fieldName}[]"], [name^="${fieldName}"]`);
                if (targetInput) {
                    const formGroup = targetInput.closest('.grid > div') || targetInput.closest('div');
                    if (formGroup) {
                        formGroup.classList.add('ring-4', 'ring-red-500/20', 'border', 'border-red-400', 'p-4', 'rounded-2xl', 'bg-red-50/10', 'transition-all');
                        if (!formGroup.querySelector('.warning-label-rej')) {
                            const warningNode = document.createElement('div');
                            warningNode.className = 'text-[10px] text-red-600 font-bold mt-1.5 flex items-center gap-1 warning-label-rej';
                            warningNode.innerHTML = '⚠️ Perlu Perbaikan (Data Ditolak Panitia)';
                            formGroup.appendChild(warningNode);
                        }

                        if (scrollToIt) {
                            setTimeout(() => {
                                formGroup.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }, 500);
                        }
                    }
                }
            }

            // Initialize Wilayah Dropdowns for any active/rendered steps
            document.querySelectorAll('.wilayah-select').forEach(select => {
                const stepId = select.getAttribute('data-step-id');
                if (stepId && window.WilayahEngine) {
                    window.WilayahEngine.initStep(stepId);
                }
            });
        });

        // ==========================================
        // Wilayah Indonesia Cascading Dropdowns Engine
        // ==========================================
        const WilayahEngine = {
            apiBase: 'https://www.emsifa.com/api-wilayah-indonesia/api',
            apiFallback: 'https://emsifa.github.io/api-wilayah-indonesia/api',
            cache: {
                provinces: null,
                regencies: {},
                districts: {},
                villages: {}
            },
            knownVillagePatches: {
                '3573050': [
                    { id: '3573050011', district_id: '3573050', name: 'TUNGGULWULUNG' }
                ],
                '3573020': [
                    { id: '3573020004', district_id: '3573020', name: 'SUKUN' }
                ]
            },
            initializedSteps: new Set(),

            formatName(str) {
                if (!str) return '';
                return str.toLowerCase().split(' ').map(word => {
                    if (word === 'dki') return 'DKI';
                    if (word === 'di') return 'DI';
                    return word.charAt(0).toUpperCase() + word.slice(1);
                }).join(' ');
            },

            async fetchJson(endpoint) {
                try {
                    const res = await fetch(`${this.apiBase}/${endpoint}`);
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return await res.json();
                } catch (e) {
                    try {
                        const fbRes = await fetch(`${this.apiFallback}/${endpoint}`);
                        if (!fbRes.ok) throw new Error('HTTP ' + fbRes.status);
                        return await fbRes.json();
                    } catch (err) {
                        console.error('Wilayah API error:', endpoint, err);
                        return null;
                    }
                }
            },

            async getProvinces() {
                if (this.cache.provinces) return this.cache.provinces;
                let data = await this.fetchJson('provinces.json');
                if (data && Array.isArray(data)) {
                    data = [...data].sort((a, b) => a.name.localeCompare(b.name, 'id'));
                    this.cache.provinces = data;
                }
                return data || [];
            },

            async getRegencies(provinceId) {
                if (!provinceId) return [];
                if (this.cache.regencies[provinceId]) return this.cache.regencies[provinceId];
                let data = await this.fetchJson(`regencies/${provinceId}.json`);
                if (data && Array.isArray(data)) {
                    data = [...data].sort((a, b) => a.name.localeCompare(b.name, 'id'));
                    this.cache.regencies[provinceId] = data;
                }
                return data || [];
            },

            async getDistricts(regencyId) {
                if (!regencyId) return [];
                if (this.cache.districts[regencyId]) return this.cache.districts[regencyId];
                let data = await this.fetchJson(`districts/${regencyId}.json`);
                if (data && Array.isArray(data)) {
                    data = [...data].sort((a, b) => a.name.localeCompare(b.name, 'id'));
                    this.cache.districts[regencyId] = data;
                }
                return data || [];
            },

            async getVillages(districtId) {
                if (!districtId) return [];
                if (this.cache.villages[districtId]) return this.cache.villages[districtId];
                let data = await this.fetchJson(`villages/${districtId}.json`);
                data = (data && Array.isArray(data)) ? [...data] : [];

                // Inject known missing entries (e.g. Tunggulwulung in Lowokwaru, Sukun in Sukun)
                if (this.knownVillagePatches[districtId]) {
                    this.knownVillagePatches[districtId].forEach(patch => {
                        if (!data.some(item => item.name.toUpperCase().replace(/\s+/g, '') === patch.name.toUpperCase().replace(/\s+/g, ''))) {
                            data.push(patch);
                        }
                    });
                }

                data.sort((a, b) => a.name.localeCompare(b.name, 'id'));
                this.cache.villages[districtId] = data;
                return data;
            },

            resetSelect(selectEl, placeholder) {
                if (!selectEl) return;
                selectEl.innerHTML = `<option value="">${placeholder}</option>`;
                selectEl.disabled = true;
            },

            initStep(stepId) {
                if (this.initializedSteps.has(stepId)) return;
                const provSelect = document.getElementById(`wilayah_province_${stepId}`);
                const citySelect = document.getElementById(`wilayah_city_${stepId}`);
                const kecSelect = document.getElementById(`wilayah_kecamatan_${stepId}`);
                const kelSelect = document.getElementById(`wilayah_kelurahan_${stepId}`);

                if (!provSelect) return;
                this.initializedSteps.add(stepId);

                const targetProv = (provSelect.getAttribute('data-current-val') || '').trim();
                const targetCity = (citySelect?.getAttribute('data-current-val') || '').trim();
                const targetKec = (kecSelect?.getAttribute('data-current-val') || '').trim();
                const targetKel = (kelSelect?.getAttribute('data-current-val') || '').trim();

                // 1. Fetch Provinces
                provSelect.innerHTML = '<option value="">⏳ Memuat daftar Provinsi...</option>';
                this.getProvinces().then(provinces => {
                    provSelect.innerHTML = '<option value="">-- Pilih Provinsi --</option>';
                    let matchedProvId = null;

                    provinces.forEach(p => {
                        const formatted = this.formatName(p.name);
                        const opt = document.createElement('option');
                        opt.value = formatted;
                        opt.textContent = formatted;
                        opt.dataset.id = p.id;

                        if (targetProv && (targetProv.toLowerCase() === formatted.toLowerCase() || targetProv.toLowerCase() === p.name.toLowerCase())) {
                            opt.selected = true;
                            matchedProvId = p.id;
                        }
                        provSelect.appendChild(opt);
                    });

                    if (matchedProvId && citySelect) {
                        this.loadCities(stepId, matchedProvId, targetCity, targetKec, targetKel);
                    }
                });

                // Event listener: Province Change
                provSelect.addEventListener('change', () => {
                    const selectedOpt = provSelect.options[provSelect.selectedIndex];
                    const provId = selectedOpt?.dataset?.id;

                    this.resetSelect(citySelect, '-- Pilih Kabupaten / Kota --');
                    this.resetSelect(kecSelect, '-- Pilih Kecamatan --');
                    this.resetSelect(kelSelect, '-- Pilih Kelurahan / Desa --');

                    if (provId) {
                        this.loadCities(stepId, provId, '', '', '');
                    }
                });

                // Event listener: City Change
                if (citySelect) {
                    citySelect.addEventListener('change', () => {
                        const selectedOpt = citySelect.options[citySelect.selectedIndex];
                        const cityId = selectedOpt?.dataset?.id;

                        this.resetSelect(kecSelect, '-- Pilih Kecamatan --');
                        this.resetSelect(kelSelect, '-- Pilih Kelurahan / Desa --');

                        if (cityId) {
                            this.loadDistricts(stepId, cityId, '', '');
                        }
                    });
                }

                // Event listener: Kecamatan Change
                if (kecSelect) {
                    kecSelect.addEventListener('change', () => {
                        const selectedOpt = kecSelect.options[kecSelect.selectedIndex];
                        const kecId = selectedOpt?.dataset?.id;

                        this.resetSelect(kelSelect, '-- Pilih Kelurahan / Desa --');

                        if (kecId) {
                            this.loadVillages(stepId, kecId, '');
                        }
                    });
                }
            },

            async loadCities(stepId, provId, targetCity, targetKec, targetKel) {
                const citySelect = document.getElementById(`wilayah_city_${stepId}`);
                if (!citySelect) return;

                citySelect.disabled = false;
                citySelect.innerHTML = '<option value="">⏳ Memuat data Kabupaten / Kota...</option>';

                const regencies = await this.getRegencies(provId);
                citySelect.innerHTML = '<option value="">-- Pilih Kabupaten / Kota --</option>';

                let matchedCityId = null;
                regencies.forEach(r => {
                    const formatted = this.formatName(r.name);
                    const opt = document.createElement('option');
                    opt.value = formatted;
                    opt.textContent = formatted;
                    opt.dataset.id = r.id;

                    if (targetCity && (targetCity.toLowerCase() === formatted.toLowerCase() || targetCity.toLowerCase() === r.name.toLowerCase() || targetCity.toLowerCase().includes(formatted.toLowerCase()))) {
                        opt.selected = true;
                        matchedCityId = r.id;
                    }
                    citySelect.appendChild(opt);
                });

                if (matchedCityId) {
                    this.loadDistricts(stepId, matchedCityId, targetKec, targetKel);
                }
            },

            async loadDistricts(stepId, regencyId, targetKec, targetKel) {
                const kecSelect = document.getElementById(`wilayah_kecamatan_${stepId}`);
                if (!kecSelect) return;

                kecSelect.disabled = false;
                kecSelect.innerHTML = '<option value="">⏳ Memuat data Kecamatan...</option>';

                const districts = await this.getDistricts(regencyId);
                kecSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';

                let matchedKecId = null;
                districts.forEach(d => {
                    const formatted = this.formatName(d.name);
                    const opt = document.createElement('option');
                    opt.value = formatted;
                    opt.textContent = formatted;
                    opt.dataset.id = d.id;

                    if (targetKec && (targetKec.toLowerCase() === formatted.toLowerCase() || targetKec.toLowerCase() === d.name.toLowerCase())) {
                        opt.selected = true;
                        matchedKecId = d.id;
                    }
                    kecSelect.appendChild(opt);
                });

                if (matchedKecId) {
                    this.loadVillages(stepId, matchedKecId, targetKel);
                }
            },

            async loadVillages(stepId, districtId, targetKel) {
                const kelSelect = document.getElementById(`wilayah_kelurahan_${stepId}`);
                if (!kelSelect) return;

                kelSelect.disabled = false;
                kelSelect.innerHTML = '<option value="">⏳ Memuat data Kelurahan / Desa...</option>';

                const villages = await this.getVillages(districtId);
                kelSelect.innerHTML = '<option value="">-- Pilih Kelurahan / Desa --</option>';

                villages.forEach(v => {
                    const formatted = this.formatName(v.name);
                    const opt = document.createElement('option');
                    opt.value = formatted;
                    opt.textContent = formatted;
                    opt.dataset.id = v.id;

                    if (targetKel && (targetKel.toLowerCase() === formatted.toLowerCase() || targetKel.toLowerCase() === v.name.toLowerCase())) {
                        opt.selected = true;
                    }
                    kelSelect.appendChild(opt);
                });
            }
        };
        window.WilayahEngine = WilayahEngine;

        // Global Accordion Handlers for Completed Steps
        window.toggleStepAccordion = function(stepId) {
            const readonlyEl = document.getElementById('readonly-step-' + stepId);
            const chevronBox = document.getElementById('chevron-box-' + stepId);
            const formEl = document.getElementById('form-step-' + stepId);
            
            // If form edit is currently open, close form and show summary
            if (formEl && !formEl.classList.contains('hidden')) {
                formEl.classList.add('hidden');
                if (readonlyEl) {
                    readonlyEl.classList.remove('hidden');
                    if (chevronBox) chevronBox.classList.add('rotate-180');
                }
                return;
            }
            
            if (readonlyEl) {
                const isHidden = readonlyEl.classList.contains('hidden');
                if (isHidden) {
                    readonlyEl.classList.remove('hidden');
                    if (chevronBox) chevronBox.classList.add('rotate-180');
                } else {
                    readonlyEl.classList.add('hidden');
                    if (chevronBox) chevronBox.classList.remove('rotate-180');
                }
            }
        };

        window.openStepEdit = function(stepId) {
            const readonlyEl = document.getElementById('readonly-step-' + stepId);
            const formEl = document.getElementById('form-step-' + stepId);
            const chevronBox = document.getElementById('chevron-box-' + stepId);
            
            if (readonlyEl) readonlyEl.classList.add('hidden');
            if (formEl) formEl.classList.remove('hidden');
            if (chevronBox) chevronBox.classList.remove('rotate-180');

            if (window.WilayahEngine) {
                window.WilayahEngine.initStep(stepId);
            }
        };

        window.cancelStepEdit = function(stepId) {
            const readonlyEl = document.getElementById('readonly-step-' + stepId);
            const formEl = document.getElementById('form-step-' + stepId);
            const chevronBox = document.getElementById('chevron-box-' + stepId);
            
            if (formEl) formEl.classList.add('hidden');
            if (readonlyEl) {
                readonlyEl.classList.add('hidden');
                if (chevronBox) chevronBox.classList.remove('rotate-180');
            }
        };

        window.handlePrevSchoolChoice = function(radio, stepId) {
            const customBox = document.getElementById('prevSchoolCustomInputBox_' + stepId);
            const customInput = document.getElementById('prevSchoolCustomInput_' + stepId);
            const realInput = document.getElementById('realPreviousSchoolInput_' + stepId);

            if (radio.value === '__OTHER__') {
                if (customBox) customBox.classList.remove('hidden');
                if (customInput) {
                    customInput.focus();
                    if (realInput) realInput.value = customInput.value.trim();
                }
            } else {
                if (customBox) customBox.classList.add('hidden');
                if (realInput) realInput.value = radio.value;
            }
        };

        window.syncPrevSchoolValue = function(val, stepId) {
            const realInput = document.getElementById('realPreviousSchoolInput_' + stepId);
            if (realInput) {
                realInput.value = val.trim();
            }
        };

        window.handleParentAddressRadioClick = function(radio, parentType, stepId) {
            const groupName = radio.name;
            const wasChecked = radio.dataset.wasChecked === 'true';

            if (wasChecked) {
                radio.checked = false;
                document.querySelectorAll(`input[name="${groupName}"]`).forEach(r => r.dataset.wasChecked = 'false');
                handleParentAddressChoice(parentType, 'none', stepId);
            } else {
                document.querySelectorAll(`input[name="${groupName}"]`).forEach(r => r.dataset.wasChecked = 'false');
                radio.dataset.wasChecked = 'true';
                handleParentAddressChoice(parentType, radio.value, stepId);
            }
        };

        window.resetGuardianAddress = function(stepId) {
            document.querySelectorAll(`input[name="guardian_address_choice_${stepId}"]`).forEach(r => {
                r.checked = false;
                r.dataset.wasChecked = 'false';
            });
            const customInput = document.getElementById('guardian_address_custom_input_' + stepId);
            if (customInput) customInput.value = '';
            handleParentAddressChoice('guardian', 'none', stepId);
        };

        window.handleParentAddressChoice = function(parentType, choice, stepId) {
            const sameBox = document.getElementById(parentType + '_address_same_box_' + stepId);
            const customBox = document.getElementById(parentType + '_address_custom_box_' + stepId);
            const customInput = document.getElementById(parentType + '_address_custom_input_' + stepId);
            const realInput = document.getElementById('real_' + parentType + '_address_' + stepId);
            const resetBtn = document.getElementById(parentType + '_address_reset_btn_' + stepId);
            const candidateAddress = document.getElementById('candidate_full_address_data')?.value || '';

            if (choice === 'same') {
                if (sameBox) sameBox.classList.remove('hidden');
                if (customBox) customBox.classList.add('hidden');
                if (realInput) realInput.value = candidateAddress;
                if (resetBtn) resetBtn.classList.remove('hidden');
            } else if (choice === 'custom') {
                if (sameBox) sameBox.classList.add('hidden');
                if (customBox) customBox.classList.remove('hidden');
                if (customInput) {
                    customInput.focus();
                    if (realInput) realInput.value = customInput.value.trim();
                }
                if (resetBtn) resetBtn.classList.remove('hidden');
            } else {
                // choice === 'none' (Kosongkan / Bersama Orang Tua)
                if (sameBox) sameBox.classList.add('hidden');
                if (customBox) customBox.classList.add('hidden');
                if (realInput) realInput.value = '';
                if (customInput) customInput.value = '';
                if (resetBtn) resetBtn.classList.add('hidden');
            }
        };

        window.syncParentAddressValue = function(parentType, val, stepId) {
            const realInput = document.getElementById('real_' + parentType + '_address_' + stepId);
            if (realInput) {
                realInput.value = val.trim();
            }
        };
    </script>
@endsection
