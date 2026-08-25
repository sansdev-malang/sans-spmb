@extends('layouts.portal')

@section('title', 'Formulir Pendaftaran - Portal SPMB')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
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

                    @foreach($steps as $index => $s)
                        @php
                            $isActive = (!$s->is_completed && ($index === 0 || $steps[$index - 1]->is_completed)) || ($completedCount === $totalCount && $index === $totalCount - 1);
                            $isCompleted = $s->is_completed;
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
                $previousStepCompleted = true;
            @endphp
            @foreach($steps as $index => $step)
                @if($previousStepCompleted || $registration->registration_status !== 'draft')
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
                            <form id="form-step-{{ $step->id }}" action="{{ route('dashboard.step.save', [$registration->id, $step->id]) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-sm {{ ($step->is_completed && !$hasInvalidFields) ? 'hidden' : '' }}">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($step->fields as $field)
                                        @php
                                            $val = $registration->getFieldValue($field->field_name);
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
                                        @endphp
                                        <div class="{{ $isFullWidth ? 'md:col-span-2' : '' }}">
                                            <label class="block text-xs font-semibold text-slate-600 mb-1">
                                                {{ $fieldLabel }}{{ $field->is_required ? '*' : '' }}
                                            </label>
                                            
                                            @if($field->field_name === 'extra_services')
                                                 @php
                                                     $activeServices = \App\Models\SpmbExtraService::where('is_active', true)->get();
                                                     $selectedServiceIds = $registration->extraServices->pluck('id')->toArray();
                                                     $currentSelectedId = count($selectedServiceIds) > 0 ? $selectedServiceIds[0] : '';
                                                 @endphp
                                                 <div class="flex flex-wrap gap-2.5 mt-1 bg-slate-50 p-3 rounded-xl border border-slate-200">
                                                     <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-xl border border-slate-200 hover:border-brand-emerald cursor-pointer transition select-none">
                                                         <input type="radio" name="extra_services" value="" {{ empty($currentSelectedId) ? 'checked' : '' }} class="w-4 h-4 text-brand-emerald border-slate-300 focus:ring-brand-emerald">
                                                         <span class="text-xs font-bold text-slate-700">Tidak Ada</span>
                                                     </label>
                                                     @foreach($activeServices as $service)
                                                         <label class="flex items-center gap-2 bg-white px-3 py-2 rounded-xl border border-slate-200 hover:border-brand-emerald cursor-pointer transition select-none">
                                                             <input type="radio" name="extra_services" value="{{ $service->id }}" {{ $currentSelectedId == $service->id ? 'checked' : '' }} class="w-4 h-4 text-brand-emerald border-slate-300 focus:ring-brand-emerald">
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
                                                @if(!empty($val))
                                                    <div class="mb-2 p-2 bg-slate-50 border border-slate-200 rounded-lg flex items-center justify-between text-xs">
                                                        <a href="{{ Storage::url($val) }}" target="_blank" class="text-brand-emerald font-bold hover:underline">📄 Lihat Berkas Saat Ini</a>
                                                        <span class="text-[10px] text-slate-400">Unggah file baru untuk mengganti</span>
                                                    </div>
                                                @endif
                                                <input type="file" name="{{ $field->field_name }}" class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-850 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs" {{ ($field->is_required && empty($val)) ? 'required' : '' }}>
                                                <span class="text-[9px] text-slate-450 block mt-1">Format file yang diperbolehkan: PDF, JPG, JPEG, PNG (Maks. 2 MB)</span>
                                            @else
                                                <input type="{{ $field->type }}" name="{{ $field->field_name }}" value="{{ $val }}" class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-850 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs" {{ $field->is_required ? 'required' : '' }}>
                                            @endif

                                            @error($field->field_name)
                                                <span class="text-red-600 text-xs mt-1 block font-bold">{{ $message }}</span>
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
                                @if($index === 0)
                                    <div>
                                        <strong class="text-slate-500 font-semibold">Unit Sekolah:</strong>
                                        <span class="text-slate-800 font-bold ml-1">{{ $registration->unit->name ?? '-' }}</span>
                                    </div>
                                @endif
                                @foreach($step->fields as $field)
                                    @php
                                        $val = $registration->getFieldValue($field->field_name);
                                    @endphp
                                    <div>
                                        <strong class="text-slate-500 font-semibold">{{ $field->label }}:</strong> 
                                        @if($field->type === 'file' && !empty($val))
                                            <a href="{{ Storage::url($val) }}" target="_blank" class="text-brand-emerald font-bold hover:underline">Lihat Berkas 📄</a>
                                        @elseif($field->field_name === 'spmb_period_id')
                                            <span class="text-slate-800 font-bold ml-1">{{ $registration->period?->year ?? '-' }}</span>
                                        @elseif($field->field_name === 'spmb_wave_id')
                                            <span class="text-slate-800 font-bold ml-1">{{ $registration->wave?->name ?? '-' }}</span>
                                        @elseif($field->field_name === 'spmb_type_id')
                                            <span class="text-slate-800 font-bold ml-1">{{ $registration->type?->name ?? '-' }}</span>
                                        @elseif($field->field_name === 'spmb_class_program_id')
                                            <span class="text-slate-800 font-bold ml-1">{{ $registration->classProgram?->name ?? '-' }}</span>
                                        @elseif($field->field_name === 'extra_services')
                                            <span class="text-slate-800 font-bold ml-1">{{ $val ?? 'Tidak Ada' }}</span>
                                        @else
                                            <span class="text-slate-800 font-bold ml-1">{{ $val ?? '-' }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
                @php
                    $previousStepCompleted = $step->is_completed;
                @endphp
            @endforeach
        </div>
    </div>
</div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const invalidFields = @json($registration->invalid_fields ?? []);
            
            // Auto-highlight all invalid fields on load
            if (invalidFields && invalidFields.length > 0) {
                invalidFields.forEach(field => {
                    highlightFieldInput(field);
                });
            }

            // Handle specific highlight parameter from URL
            const urlParams = new URLSearchParams(window.location.search);
            const highlightField = urlParams.get('highlight');
            const targetStep = urlParams.get('step');

            if (targetStep) {
                // Open the targeted step form
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
                // Search inputs, select, textarea, file types
                const targetInput = document.querySelector(`[name="${fieldName}"], [name="${fieldName}[]"], [name^="${fieldName}"]`);
                if (targetInput) {
                    const formGroup = targetInput.closest('.grid > div') || targetInput.closest('div');
                    if (formGroup) {
                        formGroup.classList.add('ring-4', 'ring-red-500/20', 'border', 'border-red-400', 'p-4', 'rounded-2xl', 'bg-red-50/10', 'transition-all');
                        
                        // Prevent duplicate warning labels
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
