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

        <div class="p-6 space-y-8">
            <!-- Checking if all steps are completed but registration is still draft -->
            @if ($registration->registration_status === 'draft' && $allStepsCompleted)
                <div class="bg-emerald-50 border border-brand-emerald/30 p-5 rounded-2xl flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-800">Semua Data Selesai Diisi!</h3>
                        <p class="text-xs text-slate-500 mt-1">Data Anda sudah tersimpan sebagai draf. Silakan kirimkan pendaftaran Anda untuk memproses tagihan pembayaran.</p>
                    </div>
                    <form action="{{ route('dashboard.form.submit', $registration->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5 whitespace-nowrap">
                            <i data-lucide="send" class="w-4 h-4"></i> Kirim Pendaftaran Sekarang
                        </button>
                    </form>
                </div>
            @endif

            <!-- Checking if the form is locked (already submitted) -->
            @if ($registration->registration_status !== 'draft')
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
                    <div class="border border-slate-200 rounded-xl p-5 {{ (!$step->is_completed && $registration->registration_status === 'draft') ? 'border-brand-emerald bg-emerald-50/10' : '' }}">
                        <div class="flex justify-between items-center mb-4">
                            <span class="font-bold text-slate-800 flex items-center gap-2">
                                <span class="h-6 w-6 rounded-full bg-brand-emerald text-white text-xs flex items-center justify-center font-bold">{{ $index + 1 }}</span>
                                {{ $step->title }}
                            </span>
                            <div class="flex items-center gap-2">
                                @if ($step->is_completed)
                                    <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-semibold">Tersimpan</span>
                                    @if ($registration->registration_status === 'draft')
                                        <button type="button" onclick="document.getElementById('readonly-step-{{ $step->id }}').classList.add('hidden'); document.getElementById('form-step-{{ $step->id }}').classList.remove('hidden');" class="text-[10px] text-brand-emerald font-bold hover:underline">
                                            Ubah Data
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Form Block -->
                        @if ($registration->registration_status === 'draft')
                            <form id="form-step-{{ $step->id }}" action="{{ route('dashboard.step.save', [$registration->id, $step->id]) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-sm {{ $step->is_completed ? 'hidden' : '' }}">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($step->fields as $field)
                                        @php
                                            $val = $registration->getFieldValue($field->field_name);
                                            $isFullWidth = in_array($field->type, ['textarea', 'file']) || strlen($field->label) > 30;
                                        @endphp
                                        <div class="{{ $isFullWidth ? 'md:col-span-2' : '' }}">
                                            <label class="block text-xs font-semibold text-slate-600 mb-1">
                                                {{ $field->label }}{{ $field->is_required ? '*' : '' }}
                                            </label>
                                            
                                            @if($field->type === 'select')
                                                <select name="{{ $field->field_name }}" {{ $field->is_required ? 'required' : '' }} class="w-full border {{ $errors->has($field->field_name) ? 'border-red-500 focus:ring-red-500' : 'border-slate-300 focus:ring-brand-emerald' }} rounded-lg px-3 py-2 text-slate-800 bg-white">
                                                    @foreach(explode(',', $field->options) as $opt)
                                                        <option value="{{ trim($opt) }}" {{ $val == trim($opt) ? 'selected' : '' }}>{{ trim($opt) }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($field->type === 'textarea')
                                                <textarea name="{{ $field->field_name }}" {{ $field->is_required ? 'required' : '' }} class="w-full border {{ $errors->has($field->field_name) ? 'border-red-500 focus:ring-red-500' : 'border-slate-300 focus:ring-brand-emerald' }} rounded-lg px-3 py-2 text-slate-800" rows="3">{{ $val }}</textarea>
                                            @elseif($field->type === 'file')
                                                <input type="file" name="{{ $field->field_name }}" {{ ($field->is_required && empty($val)) ? 'required' : '' }} class="w-full border {{ $errors->has($field->field_name) ? 'border-red-500 focus:ring-red-500' : 'border-slate-300 focus:ring-brand-emerald' }} rounded-lg px-3 py-2 text-slate-800 bg-white">
                                                @if(!empty($val))
                                                    <div class="mt-1 text-[10px] text-brand-emerald font-semibold">
                                                        📄 File terunggah: <a href="{{ Storage::url($val) }}" target="_blank" class="underline hover:text-emerald-700">Buka Berkas</a>
                                                    </div>
                                                @endif
                                            @else
                                                <input type="{{ $field->type === 'number' ? 'number' : ($field->type === 'date' ? 'date' : ($field->type === 'email' ? 'email' : 'text')) }}" name="{{ $field->field_name }}" value="{{ $val }}" {{ $field->is_required ? 'required' : '' }} class="w-full border {{ $errors->has($field->field_name) ? 'border-red-500 focus:ring-red-500' : 'border-slate-300 focus:ring-brand-emerald' }} rounded-lg px-3 py-2 text-slate-800" placeholder="{{ $field->label }}">
                                            @endif

                                            @error($field->field_name)
                                                <span class="text-red-600 text-xs mt-1 block font-bold">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                                <div class="pt-3 flex justify-end">
                                    <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2 rounded-lg font-bold text-xs shadow-sm">
                                        {{ ($index === $steps->count() - 1) ? 'Kirim Pendaftaran' : 'Simpan & Lanjut' }}
                                    </button>
                                </div>
                            </form>
                        @endif

                        <!-- Readonly Block -->
                        @if ($step->is_completed)
                            <div id="readonly-step-{{ $step->id }}" class="text-xs text-slate-600 grid grid-cols-1 md:grid-cols-2 gap-3 mt-2 bg-slate-50 p-4 rounded-lg border border-slate-100">
                                @foreach($step->fields as $field)
                                    @php
                                        $val = $registration->getFieldValue($field->field_name);
                                    @endphp
                                    <div>
                                        <strong class="text-slate-500 font-semibold">{{ $field->label }}:</strong> 
                                        @if($field->type === 'file' && !empty($val))
                                            <a href="{{ Storage::url($val) }}" target="_blank" class="text-brand-emerald font-bold hover:underline">Lihat Berkas 📄</a>
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
@endsection
