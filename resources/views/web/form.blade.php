@extends('layouts.portal')

@section('title', 'Formulir Pendaftaran - Portal SPMB')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
    @php
        $userAllRegs = auth()->check() ? auth()->user()->registrations()->with(['unit', 'grade', 'classProgram'])->where('registration_status', '!=', 'draft')->orWhereHas('payments', function($q) { $q->where('payment_type', 'registration_fee')->where('status', 'success'); })->latest()->get() : collect();
        $otherRegs = $userAllRegs->where('id', '!=', $registration->id);
    @endphp

    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-md border border-slate-100 dark:border-slate-800 overflow-hidden">
        <!-- Form Card Header -->
        <div class="bg-brand-emerald text-white p-5 sm:p-6 space-y-3 sm:space-y-4">
            <div class="flex items-start justify-between gap-3 w-full">
                <h2 class="font-extrabold text-base sm:text-lg text-white flex items-start sm:items-center gap-2 leading-snug min-w-0">
                    <i data-lucide="file-edit" class="w-5 h-5 text-brand-yellow shrink-0 mt-0.5 sm:mt-0"></i>
                    <span>Isi Formulir & Unggah Dokumen</span>
                </h2>
                
                <div class="shrink-0 self-start pt-0.5">
                    @if(in_array($registration->registration_status, ['submitted', 'verified', 'taaruf_completed', 'agreement_signed', 'completed']))
                        <span class="inline-flex items-center gap-1 bg-green-700 text-white font-black text-[10px] uppercase tracking-wider px-2.5 sm:px-3 py-1 rounded-full border border-green-500 shadow-xs whitespace-nowrap">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Terkirim
                        </span>
                    @elseif($registration->registration_status === 'failed')
                        <span class="inline-flex items-center gap-1 bg-red-750 text-white font-black text-[10px] uppercase tracking-wider px-2.5 sm:px-3 py-1 rounded-full border border-red-500 shadow-xs whitespace-nowrap">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i> Perlu Revisi
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 bg-amber-600 text-white font-black text-[10px] uppercase tracking-wider px-2.5 sm:px-3 py-1 rounded-full border border-amber-500 shadow-xs whitespace-nowrap">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Belum Lengkap
                        </span>
                    @endif
                </div>
            </div>

            <!-- Full-width subtitle -->
            <p class="text-xs text-brand-yellow/90 font-medium leading-relaxed w-full">Silakan isi seluruh tahapan pendaftaran secara bertahap hingga formulir siap dikirim.</p>

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
                                <a href="{{ route('dashboard.form', $other->id) }}" 
                                   class="inline-flex items-center gap-1.5 px-2 py-1 rounded-xl bg-white/15 hover:bg-white/25 text-white text-[11px] font-bold transition border border-white/20 shadow-xs"
                                   title="Beralih ke formulir {{ $other->candidate_name }}">
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
                <div class="bg-emerald-50 border border-brand-emerald/30 p-5 rounded-2xl flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-800">{{ $registration->registration_status === 'failed' ? 'Formulir Selesai Diperbaiki!' : 'Semua Data Selesai Diisi!' }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ $registration->registration_status === 'failed' ? 'Silakan kirimkan kembali pendaftaran Anda untuk verifikasi ulang berkas.' : 'Data Anda sudah tersimpan sebagai draf. Silakan kirimkan pendaftaran Anda.' }}</p>
                    </div>
                    <form action="{{ route('dashboard.form.submit', $registration->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5 whitespace-nowrap">
                            <i data-lucide="send" class="w-4 h-4"></i> {{ $registration->registration_status === 'failed' ? 'Kirim Ulang Pendaftaran' : 'Kirim Pendaftaran Sekarang' }}
                        </button>
                    </form>
                </div>
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
            @endphp
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
                    <div class="border rounded-2xl transition-all duration-200 overflow-hidden {{ $hasInvalidFields ? 'border-red-400 bg-red-50/5 ring-2 ring-red-200' : ($isCurrentActive ? 'border-brand-emerald bg-white ring-4 ring-emerald-500/10 shadow-sm' : 'border-slate-200/80 bg-white hover:border-slate-300 shadow-xs') }}">
                        
                        @if ($isAccordionItem)
                            <!-- Accordion Header for Completed Step -->
                            <div onclick="toggleStepAccordion({{ $step->id }})" class="p-4 sm:p-5 flex items-center justify-between cursor-pointer select-none group transition bg-white hover:bg-slate-50/70">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="h-8 w-8 rounded-xl bg-emerald-100 text-brand-emerald flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-xs">
                                        <i data-lucide="check" class="w-4 h-4 text-brand-emerald"></i>
                                    </div>
                                    <span class="font-extrabold text-sm text-slate-800 tracking-tight truncate group-hover:text-brand-emerald transition-colors">
                                        {{ $step->title }}
                                    </span>
                                </div>
                                
                                <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                                    <span class="text-[11px] bg-emerald-50 text-emerald-700 border border-emerald-200/80 px-2.5 py-0.5 rounded-full font-bold inline-flex items-center gap-1 shadow-xs">
                                        Tersimpan
                                    </span>
                                    @if ($registration->registration_status === 'draft' || $registration->registration_status === 'failed')
                                        <button type="button" onclick="event.stopPropagation(); openStepEdit({{ $step->id }});" class="text-xs text-brand-emerald font-bold hover:underline px-2 py-1 rounded-lg hover:bg-emerald-50 transition">
                                            Ubah Data
                                        </button>
                                    @endif
                                    <div id="chevron-box-{{ $step->id }}" class="text-slate-400 group-hover:text-slate-600 transition-transform duration-200 p-1">
                                        <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Active Step Header / Error Header -->
                            <div class="p-5 pb-0 flex justify-between items-center mb-4">
                                <span class="font-extrabold text-slate-800 flex items-center gap-2.5 text-sm sm:text-base">
                                    <span class="h-7 w-7 rounded-xl bg-brand-emerald text-white text-xs flex items-center justify-center font-black shadow-xs">{{ $index + 1 }}</span>
                                    {{ $step->title }}
                                </span>
                                <div class="flex items-center gap-2">
                                    @if ($hasInvalidFields)
                                        <span class="text-[10px] bg-rose-100 text-rose-700 px-2.5 py-1 rounded-full font-bold flex items-center gap-1 shadow-sm border border-rose-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500 animate-pulse"></span> Perlu Perbaikan
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Form Block -->
                        @if ($registration->registration_status === 'draft' || $registration->registration_status === 'failed')
                            <form id="form-step-{{ $step->id }}" action="{{ route('dashboard.step.save', [$registration->id, $step->id]) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-sm p-5 {{ $isAccordionItem ? 'pt-3 border-t border-slate-100 hidden' : '' }}">
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

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                                            $fieldLabel = $field->label;
                                            if ($field->field_name === 'previous_school' && isset($registration->unit)) {
                                                $uCode = strtoupper($registration->unit->code);
                                                if ($uCode === 'PAUD') {
                                                    $fieldLabel = 'Asal Sekolah / Kelompok Bermain (Jika Ada)';
                                                } elseif ($uCode === 'SD') {
                                                    $fieldLabel = 'Asal Sekolah (TK/RA/PAUD)';
                                                } elseif ($uCode === 'SMP') {
                                                    $fieldLabel = 'Asal Sekolah (SD/MI)';
                                                }
                                            }
                                            $isFullWidth = in_array($field->type, ['textarea', 'file']) || strlen($fieldLabel) > 30;
                                            $hasFieldError = $errors->has($field->field_name);
                                        @endphp
                                        <div class="{{ $isFullWidth ? 'md:col-span-2' : '' }}">
                                            <label class="block text-xs font-semibold text-slate-600 mb-1">
                                                {{ $fieldLabel }}{{ $field->is_required ? '*' : '' }}
                                            </label>
                                            
                                            @if($field->field_name === 'extra_services')
                                                 @php
                                                     $activeServices = \App\Models\SpmbExtraService::where('is_active', true)
                                                          ->where(function($q) use ($registration) {
                                                              $q->whereNull('spmb_unit_id')
                                                                ->orWhere('spmb_unit_id', $registration->spmb_unit_id);
                                                          })
                                                          ->get();
                                                     $selectedServiceIds = $registration->extraServices->pluck('id')->toArray();
                                                 @endphp
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
                                                            ->map(function($item) {
                                                                $val = $item->name === 'KB' ? 'Play Group' : $item->name;
                                                                return ['value' => $val, 'label' => $val];
                                                            });
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
                                                <div class="space-y-2" id="wrapper-{{ $uniqueId }}">
                                                    <!-- Drag & Drop Dropzone Box -->
                                                    <div id="dropzone-{{ $uniqueId }}" 
                                                         class="dropzone-box group relative border-2 border-dashed {{ $hasFieldError ? 'border-red-400 bg-red-50/40 ring-4 ring-red-500/20' : 'border-slate-300 dark:border-slate-750 hover:border-brand-emerald dark:hover:border-emerald-500 bg-slate-50/70 hover:bg-emerald-50/30 dark:bg-slate-900/40 dark:hover:bg-slate-900/80' }} rounded-2xl p-4 sm:p-5 transition-all duration-200 cursor-pointer text-center"
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
                                                        <div id="prompt-{{ $uniqueId }}" class="{{ $hasExisting ? 'hidden' : 'block' }} pointer-events-none">
                                                            <div class="w-12 h-12 mx-auto mb-2 rounded-2xl {{ $hasFieldError ? 'bg-red-100 dark:bg-red-950/60 text-red-600 dark:text-red-400' : 'bg-emerald-50 dark:bg-emerald-950/50 text-brand-emerald dark:text-emerald-400' }} flex items-center justify-center group-hover:scale-110 transition-transform duration-200 shadow-sm">
                                                                <i data-lucide="{{ $hasFieldError ? 'alert-triangle' : 'cloud-upload' }}" class="w-6 h-6"></i>
                                                            </div>
                                                            <p class="text-xs font-bold {{ $hasFieldError ? 'text-red-700 dark:text-red-300' : 'text-slate-700 dark:text-slate-200' }}">
                                                                <span class="{{ $hasFieldError ? 'text-red-700 dark:text-red-300 underline font-black' : 'text-brand-emerald dark:text-emerald-400 underline decoration-dashed font-extrabold' }} underline-offset-4">Klik untuk memilih</span> atau seret file ke sini
                                                            </p>
                                                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">
                                                                Format yang didukung: PDF, JPG, JPEG, PNG (Maks. 2 MB)
                                                            </p>
                                                        </div>

                                                        <!-- Existing File State (from Database) -->
                                                        @if($hasExisting)
                                                            <div id="existing-{{ $uniqueId }}" class="p-3 bg-white dark:bg-slate-850 rounded-xl border border-slate-200 dark:border-slate-750 flex items-center justify-between gap-3 text-left shadow-sm">
                                                                <div class="flex items-center gap-3 overflow-hidden">
                                                                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-brand-emerald flex items-center justify-center flex-shrink-0">
                                                                        <i data-lucide="file-check-2" class="w-5 h-5"></i>
                                                                    </div>
                                                                    <div class="truncate">
                                                                        <div class="flex items-center gap-2">
                                                                            <span class="text-xs font-bold text-slate-800 dark:text-white">Berkas Tersimpan</span>
                                                                            <span class="text-[9px] bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 px-2 py-0.5 rounded-full font-extrabold">Tersedia</span>
                                                                        </div>
                                                                        <span class="text-[10px] text-slate-400 font-mono block truncate mt-0.5">{{ basename($val) }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="flex items-center gap-2 flex-shrink-0" onclick="event.stopPropagation()">
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
                                                        <div id="preview-{{ $uniqueId }}" class="hidden p-3 bg-emerald-50/80 dark:bg-emerald-950/40 rounded-xl border border-brand-emerald/40 text-left shadow-sm">
                                                            <div class="flex items-center justify-between gap-3">
                                                                <div class="flex items-center gap-3 overflow-hidden">
                                                                    <!-- Thumbnail image or Document icon -->
                                                                    <div id="thumb-wrap-{{ $uniqueId }}" class="w-11 h-11 rounded-xl bg-white dark:bg-slate-850 border border-slate-200 dark:border-slate-700 flex items-center justify-center flex-shrink-0 overflow-hidden shadow-inner">
                                                                        <img id="thumb-img-{{ $uniqueId }}" src="" class="hidden w-full h-full object-cover">
                                                                        <i id="doc-icon-{{ $uniqueId }}" data-lucide="file-text" class="w-6 h-6 text-brand-emerald"></i>
                                                                    </div>
                                                                    <div class="truncate">
                                                                        <span id="file-name-{{ $uniqueId }}" class="text-xs font-extrabold text-slate-800 dark:text-white block truncate">nama_file.pdf</span>
                                                                        <div class="flex items-center gap-2 mt-1">
                                                                            <span id="file-size-{{ $uniqueId }}" class="text-[10px] text-slate-500 font-mono">1.2 MB</span>
                                                                            <span id="file-badge-{{ $uniqueId }}" class="text-[9px] bg-brand-emerald text-white px-1.5 py-0.2 rounded font-black uppercase">PDF</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <button type="button" 
                                                                        onclick="event.stopPropagation(); window.resetFileInput('{{ $uniqueId }}', {{ $hasExisting ? 'true' : 'false' }})" 
                                                                        class="w-8 h-8 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 dark:text-rose-400 flex items-center justify-center transition flex-shrink-0" 
                                                                        title="Batalkan File Ini">
                                                                    <i data-lucide="x" class="w-4 h-4"></i>
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
                                <div class="flex justify-between items-center pt-4 border-t border-slate-100">
                                    @if ($step->is_completed)
                                        <button type="button" onclick="cancelStepEdit({{ $step->id }})" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-lg font-bold text-xs transition">
                                            Batal
                                        </button>
                                    @else
                                        <div></div>
                                    @endif
                                    <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2 rounded-lg font-bold text-xs shadow-sm">
                                        {{ ($index === $steps->count() - 1) ? 'Kirim Pendaftaran' : 'Simpan & Lanjut' }}
                                    </button>
                                </div>
                            </form>
                        @endif

                        <!-- Readonly Block (Accordion Content) -->
                        @if ($step->is_completed)
                            <div id="readonly-step-{{ $step->id }}" class="hidden px-5 pb-5 pt-1 border-t border-slate-100 {{ $hasInvalidFields ? '!hidden' : '' }}">
                                <div class="text-xs text-slate-600 grid grid-cols-1 md:grid-cols-2 gap-3.5 mt-3 bg-slate-50 p-4 rounded-xl border border-slate-200/60">
                                
                                @foreach($step->fields as $field)
                                    @php
                                        $isHiddenField = in_array($field->field_name, ['spmb_period_id', 'spmb_wave_id', 'spmb_type_id']);
                                    @endphp
                                    @if($isHiddenField)
                                        @continue
                                    @endif
                                    @php
                                        $val = $registration->getFieldValue($field->field_name);
                                    @endphp
                                    <div class="space-y-0.5">
                                        <strong class="text-slate-500 font-semibold block">{{ $field->label }}:</strong> 
                                        @if($field->type === 'file' && !empty($val))
                                            <a href="{{ Storage::url($val) }}" target="_blank" class="text-brand-emerald font-bold hover:underline">Lihat Berkas 📄</a>
                                        @elseif($field->field_name === 'spmb_class_program_id')
                                            <span class="text-slate-800 font-bold">{{ $registration->classProgram?->name ?? '-' }}</span>
                                        @elseif($field->field_name === 'extra_services')
                                            @php
                                                $services = $registration->extraServices;
                                            @endphp
                                            @if($services->isEmpty())
                                                <span class="text-slate-800 font-bold">Tidak Ada</span>
                                            @else
                                                <div class="space-y-1 mt-1 pl-1">
                                                    @foreach($services as $s)
                                                        <div class="flex items-center gap-1.5 text-slate-850 font-bold">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-brand-emerald"></span>
                                                            <span>{{ $s->name }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-slate-800 font-bold">{{ $val ?? '-' }}</span>
                                        @endif
                                    </div>
                                @endforeach
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
    <!-- Upload Progress Modal Overlay -->
    <div id="uploadProgressModal" class="fixed inset-0 z-50 bg-slate-900/80 backdrop-blur-md hidden flex items-center justify-center p-4 transition-all duration-300">
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-slate-100 dark:border-slate-800 text-center space-y-5 animate-scale-in">
            
            <!-- Animated Graphic -->
            <div class="relative w-20 h-20 mx-auto">
                <div id="uploadPingAnim" class="absolute inset-0 rounded-full bg-brand-emerald/20 animate-ping"></div>
                <div id="uploadIconCircle" class="relative w-20 h-20 rounded-full bg-gradient-to-tr from-brand-emerald to-emerald-400 text-white flex items-center justify-center shadow-lg shadow-emerald-500/30">
                    <i id="uploadModalIcon" data-lucide="cloud-upload" class="w-10 h-10 animate-bounce"></i>
                </div>
            </div>

            <div>
                <h3 id="uploadModalTitle" class="text-base sm:text-lg font-extrabold text-slate-800 dark:text-white">Mengunggah Dokumen Lampiran...</h3>
                <p id="uploadModalSubtitle" class="text-xs text-slate-500 dark:text-slate-400 mt-1">Harap tidak menutup atau merefresh halaman saat proses upload berlangsung.</p>
            </div>

            <!-- Progress Bar Section -->
            <div class="space-y-2 bg-slate-50 dark:bg-slate-850 p-4 rounded-2xl border border-slate-200/70 dark:border-slate-800">
                <div class="flex justify-between items-center text-xs font-bold">
                    <span id="uploadModalStatus" class="text-brand-emerald flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-brand-emerald animate-ping"></span> Mengunggah berkas...
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

            function formatBytes(bytes, decimals = 2) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            }

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

                if (fileNameEl) fileNameEl.innerText = file.name;
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

            // 2. AJAX Form Submission with Real-time Upload Progress Percentage & Client-Side Validation
            const formsWithFiles = document.querySelectorAll('form[enctype="multipart/form-data"]');
            const progressModal = document.getElementById('uploadProgressModal');
            const progressBar = document.getElementById('uploadModalBar');
            const progressPercent = document.getElementById('uploadModalPercent');
            const progressBytes = document.getElementById('uploadModalBytes');
            const progressStatus = document.getElementById('uploadModalStatus');
            const progressTitle = document.getElementById('uploadModalTitle');
            const uploadIcon = document.getElementById('uploadModalIcon');
            const uploadPingAnim = document.getElementById('uploadPingAnim');

            formsWithFiles.forEach(form => {
                form.addEventListener('submit', function(e) {
                    // 1. Client-Side Validation for Required File Inputs
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

                    let hasSelectedNewFiles = false;
                    fileInputs.forEach(fi => {
                        if (fi.files && fi.files.length > 0) {
                            hasSelectedNewFiles = true;
                        }
                    });

                    // If uploading files, show upload progress modal and track percentage
                    if (hasSelectedNewFiles) {
                        e.preventDefault();

                        const formData = new FormData(form);
                        const xhr = new XMLHttpRequest();

                        // Reset Modal State
                        progressBar.style.width = '0%';
                        progressPercent.innerText = '0%';
                        progressBytes.innerText = '0 MB / 0 MB';
                        progressStatus.innerHTML = '<span class="w-2 h-2 rounded-full bg-brand-emerald animate-ping"></span> Mengunggah berkas lampiran...';
                        progressTitle.innerText = 'Mengunggah Dokumen Lampiran...';
                        progressModal.classList.remove('hidden');
                        if (uploadPingAnim) uploadPingAnim.classList.remove('hidden');

                        // Real-time Upload Progress Event
                        xhr.upload.addEventListener('progress', function(event) {
                            if (event.lengthComputable) {
                                const percentComplete = Math.round((event.loaded / event.total) * 100);
                                progressBar.style.width = percentComplete + '%';
                                progressPercent.innerText = percentComplete + '%';
                                progressBytes.innerText = formatBytes(event.loaded) + ' / ' + formatBytes(event.total);

                                if (percentComplete >= 100) {
                                    progressStatus.innerHTML = '⚡ Menyimpan berkas ke server...';
                                }
                            }
                        });

                        xhr.addEventListener('load', function() {
                            if (xhr.status >= 200 && xhr.status < 300) {
                                progressBar.style.width = '100%';
                                progressPercent.innerText = '100%';
                                progressStatus.innerHTML = '✅ Berhasil disimpan!';
                                progressTitle.innerText = 'Upload Berkas Selesai!';
                                if (uploadPingAnim) uploadPingAnim.classList.add('hidden');

                                setTimeout(() => {
                                    try {
                                        const res = JSON.parse(xhr.responseText);
                                        if (res.redirect) {
                                            window.location.href = res.redirect;
                                        } else {
                                            window.location.reload();
                                        }
                                    } catch (err) {
                                        window.location.reload();
                                    }
                                }, 600);
                            } else {
                                progressModal.classList.add('hidden');
                                try {
                                    const errRes = JSON.parse(xhr.responseText);
                                    if (errRes.errors) {
                                        const firstKey = Object.keys(errRes.errors)[0];
                                        alert('Gagal menyimpan: ' + errRes.errors[firstKey][0]);
                                    } else {
                                        alert('Gagal mengunggah berkas. Silakan coba kembali.');
                                    }
                                } catch (e) {
                                    alert('Terjadi kesalahan saat mengunggah berkas.');
                                }
                            }
                        });

                        xhr.addEventListener('error', function() {
                            progressModal.classList.add('hidden');
                            alert('Koneksi terputus saat mengunggah berkas. Periksa internet Anda dan coba lagi.');
                        });

                        xhr.open(form.method || 'POST', form.action, true);
                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                        xhr.setRequestHeader('Accept', 'application/json');
                        xhr.send(formData);
                    }
                });
            });

            // 3. Highlight Invalid Fields on Verification Return
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
        });

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
    </script>
@endsection
