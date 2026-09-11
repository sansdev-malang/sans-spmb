@extends('layouts.portal')

@section('title', 'Verifikasi Berkas - Portal SPMB')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-md border border-slate-100 dark:border-slate-800 overflow-hidden">
        <!-- Verification Card Header -->
        <div class="bg-brand-emerald text-white p-5 sm:p-6 space-y-3 sm:space-y-4">
            <div class="flex items-center justify-between gap-2.5 w-full">
                <h2 class="font-extrabold text-sm sm:text-lg text-white flex items-center gap-2 leading-tight min-w-0">
                    <i data-lucide="shield-check" class="w-4 h-4 sm:w-5 sm:h-5 text-brand-yellow shrink-0"></i>
                    <span class="truncate sm:whitespace-normal">Verifikasi Berkas & Dokumen</span>
                </h2>
                
                <div class="shrink-0 self-center sm:self-start pt-0">
                    @if(in_array($registration->registration_status, ['verified', 'taaruf_completed', 'agreement_signed', 'completed']))
                        <span class="inline-flex items-center gap-1 bg-green-700 text-white font-black text-[9px] sm:text-[10px] uppercase tracking-wider px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border border-green-500 shadow-xs whitespace-nowrap">
                            <i data-lucide="check-circle" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i> Terverifikasi
                        </span>
                    @elseif($registration->registration_status === 'failed')
                        <span class="inline-flex items-center gap-1 bg-red-750 text-white font-black text-[9px] sm:text-[10px] uppercase tracking-wider px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border border-red-500 shadow-xs whitespace-nowrap">
                            <i data-lucide="alert-circle" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i> Gagal
                        </span>
                    @elseif($registration->registration_status === 'submitted')
                        <span class="inline-flex items-center gap-1 bg-amber-600 text-white font-black text-[9px] sm:text-[10px] uppercase tracking-wider px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border border-amber-500 shadow-xs whitespace-nowrap">
                            <i data-lucide="clock" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i> Ditinjau
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 bg-slate-700 text-white font-black text-[9px] sm:text-[10px] uppercase tracking-wider px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border border-slate-500 shadow-xs whitespace-nowrap">
                            <i data-lucide="help-circle" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i> Belum Lengkap
                        </span>
                    @endif
                </div>
            </div>

            <!-- Full-width subtitle -->
            <p class="text-xs text-brand-yellow/90 font-medium leading-relaxed w-full">Pantau status peninjauan berkas persyaratan pendaftaran oleh panitia SPMB.</p>

            <!-- Integrated Candidate Context Info -->
            <div class="bg-black/20 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/15 shadow-sm space-y-3.5">
                <!-- Top Row: Avatar + Name + Registration Number on the Far Right -->
                <div class="flex items-center justify-between gap-3 pb-3 border-b border-white/10">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="h-11 w-11 sm:h-12 sm:w-12 rounded-xl bg-white/20 text-white font-black text-base sm:text-lg flex items-center justify-center border border-white/25 shadow-inner shrink-0">
                            {{ strtoupper(substr(trim($registration->candidate_name ?? 'A'), 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <span class="text-[10px] font-bold text-white/60 uppercase tracking-wider block leading-tight">Calon Murid</span>
                            <h4 class="font-black text-sm sm:text-lg text-white tracking-tight leading-snug truncate">
                                {{ $registration->candidate_name ?? 'Calon Murid' }}
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

        <div class="p-6 space-y-6">
            
            <!-- Committee Box (Only shown when reviewing or revision needed, hidden when verified to prevent redundancy) -->
            @if(!in_array($registration->registration_status, ['verified', 'taaruf_completed', 'agreement_signed', 'completed']))
                <div class="bg-slate-50 dark:bg-slate-850 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 sm:p-6 flex gap-4 items-start">
                    <div class="h-10 w-10 bg-emerald-100 dark:bg-emerald-950/60 text-brand-emerald dark:text-emerald-400 rounded-xl flex items-center justify-center flex-shrink-0 font-bold border border-emerald-200/80 dark:border-emerald-800/60 shadow-xs">
                        <i data-lucide="message-square-quote" class="w-5 h-5"></i>
                    </div>
                    <div class="w-full min-w-0">
                        <h3 class="font-extrabold text-slate-800 dark:text-white text-sm sm:text-base">Catatan Panitia SPMB</h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1.5 leading-relaxed">
                            "{!! str_replace('Menu Formulir', '<a href="' . route('dashboard.form', $registration->id) . '" class="text-brand-emerald font-extrabold underline hover:text-emerald-700">Menu Formulir</a>', e($committeeMessage)) !!}"
                        </p>

                        @if($registration->registration_status === 'failed' && !empty($registration->invalid_fields) && is_array($registration->invalid_fields))
                            <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700/60">
                                <p class="text-[10px] font-extrabold text-red-650 dark:text-red-400 mb-2.5 uppercase tracking-wider">Kolom Data yang Perlu Diperbaiki:</p>
                                @php
                                    $dbFields = \App\Models\SpmbFormField::all()->keyBy('field_name');
                                    $fieldMeta = [
                                        // Step 1: Program & Layanan
                                        'spmb_period_id' => ['label' => 'Tahun Ajaran', 'step_id' => 1],
                                        'spmb_wave_id' => ['label' => 'Gelombang Pendaftaran', 'step_id' => 1],
                                        'spmb_type_id' => ['label' => 'Jalur Pendaftaran', 'step_id' => 1],
                                        'spmb_class_program_id' => ['label' => 'Kategori Murid', 'step_id' => 1],
                                        'extra_services' => ['label' => 'Layanan Tambahan', 'step_id' => 1],

                                        // Step 2: Informasi Calon Murid
                                        'candidate_name' => ['label' => 'Nama Lengkap Calon Murid', 'step_id' => 2],
                                        'nickname' => ['label' => 'Nama Panggilan', 'step_id' => 2],
                                        'nik' => ['label' => 'NIK Anak', 'step_id' => 2],
                                        'family_card_no' => ['label' => 'Nomor Kartu Keluarga (KK)', 'step_id' => 2],
                                        'gender' => ['label' => 'Jenis Kelamin', 'step_id' => 2],
                                        'religion' => ['label' => 'Agama', 'step_id' => 2],
                                        'birth_place' => ['label' => 'Tempat & Tanggal Lahir', 'step_id' => 2],
                                        'birth_date' => ['label' => 'Tanggal Lahir', 'step_id' => 2],
                                        'previous_school' => ['label' => 'Asal Sekolah', 'step_id' => 2],
                                        'admission_level' => ['label' => 'Tingkat Pendaftaran', 'step_id' => 2],

                                        // Step 3: Tempat Tinggal
                                        'province' => ['label' => 'Provinsi', 'step_id' => 3],
                                        'city' => ['label' => 'Kabupaten / Kota', 'step_id' => 3],
                                        'kecamatan' => ['label' => 'Kecamatan', 'step_id' => 3],
                                        'kelurahan' => ['label' => 'Kelurahan / Desa', 'step_id' => 3],
                                        'address' => ['label' => 'Alamat Domisili', 'step_id' => 3],
                                        'house_number' => ['label' => 'Nomor Rumah', 'step_id' => 3],
                                        'rt' => ['label' => 'RT', 'step_id' => 3],
                                        'rw' => ['label' => 'RW', 'step_id' => 3],

                                        // Step 4: Data Orang Tua
                                        'father_name' => ['label' => 'Nama Ayah Kandung', 'step_id' => 4],
                                        'father_nik' => ['label' => 'NIK Ayah', 'step_id' => 4],
                                        'father_job' => ['label' => 'Pekerjaan Ayah', 'step_id' => 4],
                                        'father_phone' => ['label' => 'No. WhatsApp Ayah', 'step_id' => 4],
                                        'parent_phone' => ['label' => 'No. WhatsApp Orang Tua', 'step_id' => 4],
                                        'father_address' => ['label' => 'Alamat Ayah', 'step_id' => 4],
                                        'mother_name' => ['label' => 'Nama Ibu Kandung', 'step_id' => 4],
                                        'mother_nik' => ['label' => 'NIK Ibu', 'step_id' => 4],
                                        'mother_job' => ['label' => 'Pekerjaan Ibu', 'step_id' => 4],
                                        'mother_phone' => ['label' => 'No. WhatsApp Ibu', 'step_id' => 4],
                                        'mother_address' => ['label' => 'Alamat Ibu', 'step_id' => 4],

                                        // Step 5: Data Wali (Opsional)
                                        'guardian_name' => ['label' => 'Nama Wali', 'step_id' => 5],
                                        'guardian_nik' => ['label' => 'NIK Wali', 'step_id' => 5],
                                        'guardian_job' => ['label' => 'Pekerjaan Wali', 'step_id' => 5],
                                        'guardian_phone' => ['label' => 'No. WhatsApp Wali', 'step_id' => 5],
                                        'guardian_address' => ['label' => 'Alamat Wali', 'step_id' => 5],

                                        // Step 6: Data Lampiran
                                        'student_photo_path' => ['label' => 'Pas Foto Formal Calon Murid', 'step_id' => 6],
                                        'birth_certificate_path' => ['label' => 'Scan Akta Kelahiran', 'step_id' => 6],
                                        'family_card_path' => ['label' => 'Scan Kartu Keluarga', 'step_id' => 6],
                                        'diploma_certificate_path' => ['label' => 'Ijazah / Surat Keterangan Lulus', 'step_id' => 6],
                                        'student_card_path' => ['label' => 'NISN / KIA / Kartu Pelajar', 'step_id' => 6],
                                        'special_needs_assessment_path' => ['label' => 'Asesmen Kebutuhan Khusus', 'step_id' => 6],
                                    ];
                                @endphp
                                <ul class="space-y-1.5">
                                    @foreach($registration->invalid_fields as $invalidField)
                                        @php
                                            $dbField = $dbFields->get($invalidField);
                                            $stepId = $dbField ? $dbField->form_step_id : ($fieldMeta[$invalidField]['step_id'] ?? 2);
                                            $label = $dbField ? $dbField->label : ($fieldMeta[$invalidField]['label'] ?? $invalidField);
                                            if ($invalidField === 'family_card_path') $label = 'Scan Kartu Keluarga';
                                            if ($invalidField === 'birth_certificate_path') $label = 'Scan Akta Kelahiran';
                                        @endphp
                                        <li class="flex items-center justify-between gap-4 text-xs font-semibold text-red-750 dark:text-red-300">
                                            <span class="flex items-center gap-1.5">
                                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                                {{ $label }}
                                            </span>
                                            <a href="{{ route('dashboard.form', $registration->id) }}?highlight={{ $invalidField }}&step={{ $stepId }}" 
                                               class="bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/50 dark:hover:bg-rose-900/60 text-rose-700 dark:text-rose-300 px-2.5 py-1 rounded-lg text-[10px] font-bold shadow-xs transition flex items-center gap-0.5">
                                                Perbaiki Data →
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Verification Status Details -->
            <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden text-xs bg-white dark:bg-slate-900 shadow-xs">
                <div class="bg-slate-50 dark:bg-slate-850 p-4 border-b border-slate-200/80 dark:border-slate-800 font-extrabold text-slate-800 dark:text-white flex justify-between items-center">
                    <span class="flex items-center gap-1.5"><i data-lucide="folder-check" class="w-4 h-4 text-brand-emerald"></i> Dokumen Persyaratan</span>
                    <span class="text-[11px] text-slate-500 uppercase tracking-wider font-bold hidden sm:inline">Status Berkas</span>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @php
                        $docList = isset($documentFields) && count($documentFields) > 0 
                            ? $documentFields 
                            : \App\Models\SpmbFormField::where('form_step_id', 6)->orWhere('type', 'file')->orderBy('order', 'asc')->get();
                    @endphp

                    @forelse($docList as $docField)
                        @php
                            $val = $registration->getFieldValue($docField->field_name);
                            $isRejected = is_array($registration->invalid_fields) && in_array($docField->field_name, $registration->invalid_fields);
                        @endphp
                        <div class="p-4 flex items-center justify-between gap-3 hover:bg-slate-50/50 dark:hover:bg-slate-850/40 transition">
                            <div class="space-y-0.5 min-w-0">
                                <div>
                                    <span class="font-extrabold text-slate-800 dark:text-white text-xs sm:text-sm block truncate">{{ $docField->label }}</span>
                                </div>
                                @if($val)
                                    <a href="{{ Storage::url($val) }}" target="_blank" class="text-brand-emerald dark:text-emerald-400 font-bold hover:underline inline-flex items-center gap-1 text-[11px]">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Buka Berkas
                                    </a>
                                @else
                                    <span class="text-slate-400 text-[11px] italic">Belum diunggah</span>
                                @endif
                            </div>
                            <div class="shrink-0 self-center">
                                @if($val)
                                    @if($registration->registration_status === 'submitted')
                                        <span class="bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 px-3 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 shadow-xs whitespace-nowrap">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span> Menunggu Verifikasi
                                        </span>
                                    @elseif($registration->registration_status === 'failed')
                                        @if($isRejected)
                                            <span class="bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800/60 px-3 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 shadow-xs whitespace-nowrap">
                                                <span class="h-1.5 w-1.5 rounded-full bg-rose-600 animate-pulse"></span> Perlu Perbaikan (Ditolak)
                                            </span>
                                        @else
                                            <span class="bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800/60 px-3 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 shadow-xs whitespace-nowrap">
                                                <span class="h-1.5 w-1.5 rounded-full bg-green-600"></span> Terverifikasi
                                            </span>
                                        @endif
                                    @else
                                        <span class="bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800/60 px-3 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 shadow-xs whitespace-nowrap">
                                            <span class="h-1.5 w-1.5 rounded-full bg-green-600"></span> Terverifikasi
                                        </span>
                                    @endif
                                @else
                                    <span class="bg-slate-100 dark:bg-slate-800 text-slate-500 px-3 py-1 rounded-full text-[11px] font-bold inline-flex items-center gap-1 shadow-xs whitespace-nowrap">
                                        Tidak Diunggah
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-slate-400 text-xs italic">
                            Belum ada konfigurasi dokumen persyaratan.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Next steps instruction -->
            @if($registration->registration_status === 'verified')
                <div class="bg-emerald-50/20 border border-brand-emerald/30 p-5 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-brand-emerald"></i>
                            Langkah Selanjutnya
                        </h4>
                        <p class="text-xs text-slate-650 leading-relaxed max-w-xl">
                            Dokumen pendaftaran Anda telah lengkap diverifikasi dengan benar. Tahapan sesi Assessment / Ta'aruf kini telah aktif. Silakan lanjut ke tahapan <strong>Assessment / Ta'aruf</strong> untuk melihat ketentuan pelaksanaan di unit sekolah.
                        </p>
                    </div>
                    <a href="{{ route('dashboard.observation', $registration->id) }}" class="w-full sm:w-auto whitespace-nowrap bg-brand-emerald hover-emerald text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-md transition flex items-center justify-center gap-2 flex-shrink-0">
                        <span>Lanjutkan ke Assessment / Ta'aruf</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            @elseif(in_array($registration->registration_status, ['taaruf_completed', 'agreement_signed', 'completed']))
                <div class="bg-emerald-50/20 border border-brand-emerald/30 p-5 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-brand-emerald"></i>
                            Tahapan Selesai
                        </h4>
                        <p class="text-xs text-slate-650 leading-relaxed max-w-xl">
                            Seluruh berkas persyaratan telah terverifikasi dan sesi Assessment / Ta'aruf telah diselesaikan. Silakan lanjut ke tahapan <strong>Administrasi</strong> untuk melihat rincian pembiayaan dan status penerimaan.
                        </p>
                    </div>
                    <a href="{{ route('dashboard.result', $registration->id) }}" class="w-full sm:w-auto whitespace-nowrap bg-brand-emerald hover-emerald text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-md transition flex items-center justify-center gap-2 flex-shrink-0">
                        <span>Lanjutkan ke Administrasi</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
