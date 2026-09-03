@extends('layouts.portal')

@section('title', 'Formulir Pendaftaran - Portal SPMB')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
    @include('web.partials.candidate-context-bar')
    <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        <div class="bg-brand-emerald text-white px-6 py-5">
            <h2 class="font-extrabold text-lg flex items-center gap-2">
                <i data-lucide="file-edit" class="w-5 h-5 text-brand-yellow"></i>
                Isi Formulir & Unggah Dokumen
            </h2>
            <p class="text-xs text-brand-yellow font-medium mt-0.5">Silakan isi seluruh tahapan pendaftaran secara bertahap hingga tombol Kirim aktif.</p>
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

        <div class="p-6 space-y-8">
            <!-- Informasi Pendaftaran Terpilih (Premium Design) -->
            <div class="relative overflow-hidden bg-gradient-to-r from-slate-900 to-brand-emerald text-white rounded-3xl p-6 shadow-md border border-emerald-800/10">
                <!-- Background patterns -->
                <div class="absolute right-0 top-0 w-36 h-36 bg-emerald-500/10 rounded-full blur-2xl"></div>
                <div class="absolute left-1/3 bottom-0 w-28 h-28 bg-teal-500/10 rounded-full blur-2xl"></div>
                
                <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 text-emerald-400 font-bold text-[10px] uppercase tracking-widest">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse-glow"></span>
                            <span>Informasi Pendaftaran Terpilih</span>
                        </div>
                        <h4 class="text-base font-extrabold tracking-tight text-white">
                            {{ $registration->candidate_name ?? 'Calon Siswa' }}
                        </h4>
                    </div>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 md:gap-6 bg-white/5 backdrop-blur-md rounded-2xl p-4 border border-white/10 text-xs">
                        <div>
                            <span class="text-white/50 font-bold uppercase block text-[8px] tracking-widest">Unit Sekolah</span>
                            <span class="font-extrabold text-emerald-300 mt-0.5 block">{{ $registration->unit->name ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-white/50 font-bold uppercase block text-[8px] tracking-widest">Tahun Pelajaran</span>
                            <span class="font-extrabold text-white mt-0.5 block">{{ $registration->period->year ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-white/50 font-bold uppercase block text-[8px] tracking-widest">Jalur</span>
                            <span class="font-extrabold text-white mt-0.5 block">{{ $registration->type->name ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-white/50 font-bold uppercase block text-[8px] tracking-widest">Gelombang</span>
                            <span class="font-extrabold text-white mt-0.5 block">{{ $registration->wave->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Horizontal Step Progress Timeline -->
            <div class="mb-12 mt-2 px-2 max-w-2xl mx-auto">
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
                            
                            <!-- Step Title Label -->
                            <span class="absolute top-11 text-[9px] sm:text-[10px] font-bold text-center whitespace-nowrap transition-colors duration-300 mt-0.5
                                {{ $isActive 
                                    ? 'text-brand-emerald dark:text-emerald-450' 
                                    : ($isCompleted ? 'text-slate-700 dark:text-slate-350' : 'text-slate-400') }}">
                                {{ $s->title }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Extra space to prevent labels overlap -->
            <div class="mb-4 sm:hidden"></div>
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

            <!-- Checking if the form is locked (already submitted and verified) -->
            @if ($registration->registration_status !== 'draft' && $registration->registration_status !== 'failed')
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 text-center space-y-2">
                    <span class="inline-flex items-center justify-center h-10 w-10 bg-emerald-100 text-brand-emerald rounded-full">
                        <i data-lucide="lock" class="w-5 h-5"></i>
                    </span>
                    <h3 class="font-bold text-slate-800 text-sm">Formulir Dikunci</h3>
                    <p class="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                        Formulir pendaftaran Anda telah dikirim dan saat ini sedang berada dalam tahap verifikasi berkas oleh Panitia. Anda tidak dapat melakukan pengubahan data secara mandiri.
                    </p>
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
                    @endphp
                    <div class="border rounded-xl p-5 {{ $hasInvalidFields ? 'border-red-400 bg-red-50/5 ring-2 ring-red-200' : ((!$step->is_completed && $registration->registration_status === 'draft') ? 'border-brand-emerald bg-emerald-50/10' : 'border-slate-200') }}">
                        <div class="flex justify-between items-center mb-4">
                            <span class="font-bold text-slate-800 flex items-center gap-2">
                                <span class="h-6 w-6 rounded-full bg-brand-emerald text-white text-xs flex items-center justify-center font-bold">{{ $index + 1 }}</span>
                                {{ $step->title }}
                            </span>
                            <div class="flex items-center gap-2">
                                @if ($hasInvalidFields)
                                    <span class="text-[10px] bg-rose-100 text-rose-700 px-2.5 py-1 rounded-full font-bold flex items-center gap-1 shadow-sm">
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500 animate-pulse"></span> Perlu Perbaikan
                                    </span>
                                @elseif ($step->is_completed)
                                    <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-semibold">Tersimpan</span>
                                @endif
                                @if ($step->is_completed)
                                    @if ($registration->registration_status === 'draft' || $registration->registration_status === 'failed')
                                        <button type="button" onclick="document.getElementById('readonly-step-{{ $step->id }}').classList.add('hidden'); document.getElementById('form-step-{{ $step->id }}').classList.remove('hidden');" class="text-[10px] text-brand-emerald font-bold hover:underline">
                                            Ubah Data
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Form Block -->
                        @if ($registration->registration_status === 'draft' || $registration->registration_status === 'failed')
                            @php
                                $stepFieldNames = $step->fields->pluck('field_name')->toArray();
                                $stepHasErrors = $errors->any() && count(array_intersect($stepFieldNames, array_keys($errors->messages()))) > 0;
                            @endphp
                            <form id="form-step-{{ $step->id }}" action="{{ route('dashboard.step.save', [$registration->id, $step->id]) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-sm {{ ($step->is_completed && !$hasInvalidFields && !$stepHasErrors) ? 'hidden' : '' }}">
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
                                        <button type="button" onclick="document.getElementById('form-step-{{ $step->id }}').classList.add('hidden'); document.getElementById('readonly-step-{{ $step->id }}').classList.remove('hidden');" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg font-bold text-xs transition">
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

                        <!-- Readonly Block -->
                        @if ($step->is_completed)
                            <div id="readonly-step-{{ $step->id }}" class="text-xs text-slate-600 grid grid-cols-1 md:grid-cols-2 gap-3 mt-2 bg-slate-50 p-4 rounded-lg border border-slate-100 {{ $hasInvalidFields ? 'hidden' : '' }}">
                                
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
    </script>
@endsection
