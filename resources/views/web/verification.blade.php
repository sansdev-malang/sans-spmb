@extends('layouts.portal')

@section('title', 'Verifikasi Berkas - Portal SPMB')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        <div class="bg-brand-emerald text-white px-6 py-5 flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-lg flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-5 h-5 text-brand-yellow"></i>
                    Status Peninjauan & Verifikasi Dokumen
                </h2>
                <p class="text-xs text-brand-yellow font-medium mt-0.5">Pantau status peninjauan berkas persyaratan pendaftaran oleh panitia SPMB.</p>
            </div>
            
            @if(in_array($registration->registration_status, ['verified', 'taaruf_completed', 'agreement_signed', 'completed']))
                <span class="bg-green-700 text-white font-bold text-[10px] uppercase tracking-widest px-3 py-1 rounded-full border border-green-500 shadow-sm">
                    Terverifikasi
                </span>
            @elseif($registration->registration_status === 'failed')
                <span class="bg-red-750 text-white font-bold text-[10px] uppercase tracking-widest px-3 py-1 rounded-full border border-red-500 shadow-sm">
                    Gagal
                </span>
            @elseif($registration->registration_status === 'submitted')
                <span class="bg-amber-600 text-white font-bold text-[10px] uppercase tracking-widest px-3 py-1 rounded-full border border-amber-500 shadow-sm">
                    Ditinjau
                </span>
            @else
                <span class="bg-slate-700 text-white font-bold text-[10px] uppercase tracking-widest px-3 py-1 rounded-full border border-slate-500 shadow-sm">
                    Belum Lengkap
                </span>
            @endif
        </div>

        <div class="p-6 space-y-6">
            
            <!-- Committee Box -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 flex gap-4 items-start">
                <div class="h-10 w-10 bg-emerald-100 text-brand-emerald rounded-xl flex items-center justify-center flex-shrink-0 font-bold">
                    i
                </div>
                <div class="w-full">
                    <h3 class="font-bold text-slate-800">Umpan Balik Panitia</h3>
                    <p class="text-sm text-slate-650 mt-1 leading-relaxed">
                        "{!! str_replace('Menu Formulir', '<a href="' . route('dashboard.form', $registration->id) . '" class="text-brand-emerald font-extrabold underline hover:text-emerald-700">Menu Formulir</a>', e($committeeMessage)) !!}"
                    </p>

                    @if($registration->registration_status === 'failed' && !empty($registration->invalid_fields) && is_array($registration->invalid_fields))
                        <div class="mt-4 pt-4 border-t border-slate-200">
                            <p class="text-[10px] font-extrabold text-red-650 mb-2.5 uppercase tracking-wider">Kolom Data yang Perlu Diperbaiki:</p>
                            @php
                                $fieldMeta = [
                                    'spmb_period_id' => ['label' => 'Tahun Ajaran', 'step_id' => 1],
                                    'spmb_wave_id' => ['label' => 'Gelombang Pendaftaran', 'step_id' => 1],
                                    'spmb_type_id' => ['label' => 'Jalur Pendaftaran', 'step_id' => 1],
                                    'spmb_class_program_id' => ['label' => 'Program Kelas', 'step_id' => 1],
                                    'candidate_name' => ['label' => 'Nama Lengkap Calon Siswa', 'step_id' => 2],
                                    'nickname' => ['label' => 'Nama Panggilan', 'step_id' => 2],
                                    'nik' => ['label' => 'NIK Anak', 'step_id' => 2],
                                    'gender' => ['label' => 'Jenis Kelamin', 'step_id' => 2],
                                    'religion' => ['label' => 'Agama', 'step_id' => 2],
                                    'birth_place' => ['label' => 'Tempat & Tanggal Lahir', 'step_id' => 2],
                                    'previous_school' => ['label' => 'Asal Sekolah', 'step_id' => 2],
                                    'admission_level' => ['label' => 'Tingkat Pendaftaran', 'step_id' => 2],
                                    'extra_services' => ['label' => 'Layanan Tambahan', 'step_id' => 2],
                                    'father_name' => ['label' => 'Nama Ayah Kandung', 'step_id' => 3],
                                    'mother_name' => ['label' => 'Nama Ibu Kandung', 'step_id' => 3],
                                    'parent_phone' => ['label' => 'No. HP Wali (WhatsApp)', 'step_id' => 3],
                                    'birth_certificate_path' => ['label' => 'Scan Akta Kelahiran', 'step_id' => 4],
                                    'family_card_path' => ['label' => 'Scan Kartu Keluarga', 'step_id' => 4],
                                ];
                            @endphp
                            <ul class="space-y-1.5">
                                @foreach($registration->invalid_fields as $invalidField)
                                    @php
                                        $meta = $fieldMeta[$invalidField] ?? ['label' => $invalidField, 'step_id' => 2];
                                    @endphp
                                    <li class="flex items-center justify-between gap-4 text-xs font-semibold text-red-750">
                                        <span class="flex items-center gap-1.5">
                                            <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                            {{ $meta['label'] }}
                                        </span>
                                        <a href="{{ route('dashboard.form', $registration->id) }}?highlight={{ $invalidField }}&step={{ $meta['step_id'] }}" 
                                           class="bg-rose-50 hover:bg-rose-100 text-rose-700 px-2.5 py-1 rounded-lg text-[10px] font-bold shadow-sm transition flex items-center gap-0.5">
                                            Perbaiki Data →
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Verification Status Details -->
            <div class="border border-slate-200 rounded-xl overflow-hidden text-xs">
                <div class="bg-slate-50 p-4 border-b border-slate-200 font-bold text-slate-800 flex justify-between">
                    <span>Dokumen Persyaratan</span>
                    <span>Status Berkas</span>
                </div>
                <div class="divide-y divide-slate-100">
                    <div class="p-4 flex justify-between items-center">
                        <span class="font-semibold text-slate-700">Scan Akta Kelahiran</span>
                        <div class="flex items-center gap-3">
                            @if($registration->birth_certificate_path)
                                <a href="{{ Storage::url($registration->birth_certificate_path) }}" target="_blank" class="text-brand-emerald font-bold hover:underline flex items-center gap-1">
                                    📄 Buka Berkas
                                </a>
                                @if($registration->registration_status === 'failed')
                                    @if(is_array($registration->invalid_fields) && in_array('birth_certificate_path', $registration->invalid_fields))
                                        <span class="bg-red-50 text-red-700 border border-red-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold flex items-center gap-1">
                                            <span class="h-1.5 w-1.5 rounded-full bg-rose-650 animate-pulse"></span> Perlu Perbaikan (Ditolak)
                                        </span>
                                    @else
                                        <span class="bg-green-50 text-green-700 border border-green-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold flex items-center gap-1">
                                            <span class="h-1.5 w-1.5 rounded-full bg-green-600"></span> OK
                                        </span>
                                    @endif
                                @else
                                    <span class="bg-green-50 text-green-700 border border-green-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold flex items-center gap-1">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-600"></span> OK
                                    </span>
                                @endif
                            @else
                                <span class="text-slate-400">Belum diunggah</span>
                            @endif
                        </div>
                    </div>
                    <div class="p-4 flex justify-between items-center">
                        <span class="font-semibold text-slate-700">Scan Kartu Keluarga</span>
                        <div class="flex items-center gap-3">
                            @if($registration->family_card_path)
                                <a href="{{ Storage::url($registration->family_card_path) }}" target="_blank" class="text-brand-emerald font-bold hover:underline flex items-center gap-1">
                                    📄 Buka Berkas
                                </a>
                                @if($registration->registration_status === 'failed')
                                    @if(is_array($registration->invalid_fields) && in_array('family_card_path', $registration->invalid_fields))
                                        <span class="bg-red-50 text-red-700 border border-red-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold flex items-center gap-1">
                                            <span class="h-1.5 w-1.5 rounded-full bg-rose-650 animate-pulse"></span> Perlu Perbaikan (Ditolak)
                                        </span>
                                    @else
                                        <span class="bg-green-50 text-green-700 border border-green-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold flex items-center gap-1">
                                            <span class="h-1.5 w-1.5 rounded-full bg-green-600"></span> OK
                                        </span>
                                    @endif
                                @else
                                    <span class="bg-green-50 text-green-700 border border-green-200 px-2.5 py-0.5 rounded-full text-[10px] font-bold flex items-center gap-1">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-600"></span> OK
                                    </span>
                                @endif
                            @else
                                <span class="text-slate-400">Belum diunggah</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next steps instruction -->
            @if(in_array($registration->registration_status, ['verified', 'taaruf_completed', 'agreement_signed', 'completed']))
                <div class="bg-emerald-50/10 border border-brand-emerald/30 p-5 rounded-xl space-y-2">
                    <h4 class="font-bold text-slate-800 dark:text-white text-xs uppercase tracking-wider">Langkah Selanjutnya</h4>
                    <p class="text-xs text-slate-650 dark:text-slate-400 leading-relaxed">
                        Dokumen pendaftaran Anda telah lengkap diverifikasi dengan benar. Tahapan sesi Ta'aruf kini telah aktif. Silakan buka menu <strong>Ta'aruf</strong> untuk melihat ketentuan kehadiran tatap muka di unit sekolah.
                    </p>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
