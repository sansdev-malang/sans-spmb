@extends('layouts.admin')

@section('title', 'Riwayat Pendaftaran (Log) - Admin Panel')
@section('page_title', 'Riwayat Pendaftaran (Log)')

@section('content')
<div class="space-y-6">
    <!-- Header Summary Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800">Riwayat Pendaftaran Lengkap (Log)</h1>
            <p class="text-xs text-slate-500 mt-1">Menampilkan seluruh riwayat calon pendaftar (termasuk draf/belum bayar) yang tercatat dalam sistem pendaftaran Sekolah Anak Saleh.</p>
        </div>
        <div class="flex gap-2">
            <button class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                📥 Ekspor Excel
            </button>
            <button class="border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition">
                🖨️ Cetak PDF
            </button>
        </div>
    </div>

    <!-- Candidate List Table -->
    <div id="history-card" class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden" hx-boost="true" hx-target="#history-card" hx-select="#history-card">
        
        <!-- Search & Filter Form -->
        <form action="{{ route('admin.history') }}" method="GET" hx-boost="false" class="p-6 bg-slate-50/50 border-b border-slate-100 space-y-4">
            @if(request('unit_id'))
                <input type="hidden" name="unit_id" value="{{ request('unit_id') }}">
            @endif
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    <!-- Search Input Container -->
                    <div class="relative w-full md:w-80 flex items-center">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, WhatsApp, NIK..." 
                               class="w-full pl-9 pr-20 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-emerald transition">
                        
                        <!-- Clear (X) Button -->
                        @if(request('search'))
                            <button type="button" onclick="this.form.querySelector('input[name=search]').value = ''; this.form.submit();" 
                                    class="absolute right-12 inset-y-0 pr-1 flex items-center text-slate-400 hover:text-slate-600 transition"
                                    title="Hapus Pencarian">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        @endif

                        <!-- Integrated Search Button -->
                        <button type="submit" class="absolute right-1.5 top-1.5 bottom-1.5 px-3 bg-brand-emerald hover-emerald text-white rounded-lg text-[10px] font-bold shadow-sm transition">
                            Cari
                        </button>
                    </div>
                    
                    @if(auth()->user()->isSuperAdmin())
                        <!-- Filter Level / Unit -->
                        <select name="unit_id" onchange="this.form.submit()" class="py-2.5 px-3 text-xs rounded-xl border border-slate-200 bg-white font-bold text-slate-650 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                            <option value="">Semua Jenjang</option>
                            @foreach(\App\Models\SpmbUnit::where('is_active', true)->get() as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>{{ strtoupper($unit->code) }}</option>
                            @endforeach
                        </select>
                    @endif

                    <!-- Filter Stage / Status -->
                    <select name="status" onchange="this.form.submit()" class="py-2.5 px-3 text-xs rounded-xl border border-slate-200 bg-white font-bold text-slate-650 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                        <option value="">Semua Tahapan</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Pembayaran / Pengisian</option>
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Verifikasi Berkas</option>
                        <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Observasi / Ta'aruf</option>
                        <option value="taaruf_completed" {{ request('status') === 'taaruf_completed' ? 'selected' : '' }}>Persetujuan Pernyataan</option>
                        <option value="agreement_signed" {{ request('status') === 'agreement_signed' ? 'selected' : '' }}>Administrasi Akhir</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai & Lulus</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Perbaikan Berkas</option>
                    </select>

                    <!-- Per Page Select -->
                    <select name="per_page" onchange="this.form.submit()" class="py-2.5 px-3 text-xs rounded-xl border border-slate-200 bg-white font-bold text-slate-650 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 Baris</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 Baris</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 Baris</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 Baris</option>
                    </select>

                    <!-- Advanced Filter Toggle Button -->
                    <button type="button" onclick="document.getElementById('adv-filters').classList.toggle('hidden')" 
                            class="flex items-center gap-1.5 py-2.5 px-3.5 text-xs rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-bold text-slate-600 transition">
                        <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>
                        Filter Lanjutan
                    </button>
                </div>
            </div>

            <!-- Slide-down Advanced Filters Panel -->
            <div id="adv-filters" class="{{ (request('start_date') || request('end_date') || request('gender') || request('wave_id') || request('type_id') || request('class_program_id') || request('doc_status')) ? '' : 'hidden' }} border-t border-slate-100 pt-4 space-y-4 transition-all duration-300">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Date Range: Start -->
                    <div class="space-y-1">
                        <label class="text-[9px] font-extrabold uppercase text-slate-400 block">Tgl Mulai Daftar</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" 
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                    </div>
                    <!-- Date Range: End -->
                    <div class="space-y-1">
                        <label class="text-[9px] font-extrabold uppercase text-slate-400 block">Tgl Selesai Daftar</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" 
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                    </div>
                    <!-- Filter: Gender -->
                    <div class="space-y-1">
                        <label class="text-[9px] font-extrabold uppercase text-slate-400 block">Jenis Kelamin</label>
                        <select name="gender" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                            <option value="">Semua</option>
                            <option value="Laki-laki" {{ request('gender') === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ request('gender') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <!-- Filter: Wave -->
                    <div class="space-y-1">
                        <label class="text-[9px] font-extrabold uppercase text-slate-400 block">Gelombang</label>
                        <select name="wave_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                            <option value="">Semua Gelombang</option>
                            @foreach(\App\Models\SpmbWave::all() as $wave)
                                <option value="{{ $wave->id }}" {{ request('wave_id') == $wave->id ? 'selected' : '' }}>{{ $wave->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Filter: Type -->
                    <div class="space-y-1">
                        <label class="text-[9px] font-extrabold uppercase text-slate-400 block">Jalur Pendaftaran</label>
                        <select name="type_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                            <option value="">Semua Jalur</option>
                            @foreach(\App\Models\SpmbType::all() as $type)
                                <option value="{{ $type->id }}" {{ request('type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Filter: Class Program -->
                    <div class="space-y-1">
                        <label class="text-[9px] font-extrabold uppercase text-slate-400 block">Program Kelas</label>
                        <select name="class_program_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                            <option value="">Semua Program</option>
                            @foreach(\App\Models\SpmbClassProgram::all() as $program)
                                <option value="{{ $program->id }}" {{ request('class_program_id') == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Filter: Document Status -->
                    <div class="space-y-1">
                        <label class="text-[9px] font-extrabold uppercase text-slate-400 block">Status Berkas (KK/Akte)</label>
                        <select name="doc_status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                            <option value="">Semua Status</option>
                            <option value="complete" {{ request('doc_status') === 'complete' ? 'selected' : '' }}>Lengkap</option>
                            <option value="incomplete" {{ request('doc_status') === 'incomplete' ? 'selected' : '' }}>Belum Lengkap</option>
                        </select>
                    </div>
                </div>
                <!-- Action Buttons in Advanced Filter -->
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="resetAdvancedFilters(this.form)" class="text-xs font-bold text-slate-500 hover:text-slate-700 px-4 py-2 rounded-xl transition">
                        Reset Filter
                    </button>
                    <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>

        <script>
            function resetAdvancedFilters(form) {
                form.querySelector('input[name=start_date]').value = '';
                form.querySelector('input[name=end_date]').value = '';
                form.querySelector('select[name=gender]').value = '';
                form.querySelector('select[name=wave_id]').value = '';
                form.querySelector('select[name=type_id]').value = '';
                form.querySelector('select[name=class_program_id]').value = '';
                form.querySelector('select[name=doc_status]').value = '';
                form.submit();
            }
        </script>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                        <th class="py-4 px-6 text-center w-12">No.</th>
                        <th class="py-4 px-6">ID Pendaftaran</th>
                        <th class="py-4 px-6">Nama Lengkap / Kontak</th>
                        <th class="py-4 px-6">Tingkat</th>
                        <th class="py-4 px-6">Tahapan Pendaftaran</th>
                        <th class="py-4 px-6">Tanggal Pendaftaran</th>
                        <th class="py-4 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    @forelse($candidates as $cand)
                        @php
                            $formPaid = $cand->payments->where('payment_type', 'registration_fee')->where('status', 'success')->isNotEmpty();
                            $status = strtolower($cand->registration_status);
                            
                            $currentStageText = '';
                            $currentStageColor = '';
                            
                            if ($status === 'draft') {
                                if (!$formPaid) {
                                    $currentStageText = 'Pembayaran Formulir';
                                    $currentStageColor = 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-900';
                                } else {
                                    $currentStageText = 'Pengisian Formulir';
                                    $currentStageColor = 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/20 dark:text-blue-400 dark:border-blue-900';
                                }
                            } elseif ($status === 'failed') {
                                $currentStageText = 'Perbaikan Berkas';
                                $currentStageColor = 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/20 dark:text-red-400 dark:border-red-900';
                            } elseif ($status === 'submitted') {
                                $currentStageText = 'Verifikasi Berkas';
                                $currentStageColor = 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/20 dark:text-purple-400 dark:border-purple-900';
                            } elseif ($status === 'verified') {
                                $currentStageText = 'Observasi / Ta\'aruf';
                                $currentStageColor = 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/20 dark:text-indigo-400 dark:border-indigo-900';
                            } elseif ($status === 'taaruf_completed') {
                                $currentStageText = 'Persetujuan Pernyataan';
                                $currentStageColor = 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-950/20 dark:text-orange-400 dark:border-orange-900';
                            } elseif ($status === 'agreement_signed') {
                                $currentStageText = 'Administrasi Akhir';
                                $currentStageColor = 'bg-pink-50 text-pink-700 border-pink-200 dark:bg-pink-950/20 dark:text-pink-400 dark:border-pink-900';
                            } elseif ($status === 'completed') {
                                $currentStageText = 'Selesai & Lulus';
                                $currentStageColor = 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900';
                            } else {
                                $currentStageText = str_replace('_', ' ', $status);
                                $currentStageColor = 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-350 dark:border-slate-700';
                            }

                            // Format Phone for WA
                            $phone = $cand->parent_phone;
                            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                            if (str_starts_with($cleanPhone, '0')) {
                                $cleanPhone = '62' . substr($cleanPhone, 1);
                            }

                            $waText = '';
                            if ($status === 'draft') {
                                if (!$formPaid) {
                                    $waText = "Halo Ayah/Bunda, kami dari Panitia SPMB Sekolah Anak Saleh melihat Anda telah mendaftarkan ananda " . $cand->candidate_name . " di sistem kami. Apakah ada kendala dalam proses pembayaran biaya pendaftaran formulir? Hubungi kami jika memerlukan bantuan.";
                                } else {
                                    $waText = "Halo Ayah/Bunda, pendaftaran ananda " . $cand->candidate_name . " di Sekolah Anak Saleh saat ini berstatus pengisian formulir. Silakan lengkapi biodata dan berkas persyaratan agar dapat kami verifikasi berkasnya.";
                                }
                            } elseif ($status === 'failed') {
                                $waText = "Halo Ayah/Bunda, berkas pendaftaran ananda " . $cand->candidate_name . " di Sekolah Anak Saleh masih terdapat kekurangan atau ketidaksesuaian. Silakan login ke portal pendaftaran untuk melihat catatan perbaikan berkas dan mengirim ulang.";
                            } elseif ($status === 'submitted') {
                                $waText = "Halo Ayah/Bunda, berkas pendaftaran ananda " . $cand->candidate_name . " telah kami terima dan sedang dalam antrean proses verifikasi oleh panitia. Mohon ditunggu perkembangan selanjutnya.";
                            } elseif ($status === 'verified') {
                                $waText = "Halo Ayah/Bunda, berkas pendaftaran ananda " . $cand->candidate_name . " telah berhasil diverifikasi. Tahap selanjutnya adalah proses Ta'aruf/Observasi. Silakan cek menu Ta'Aruf di portal untuk melihat jadwal.";
                            } elseif ($status === 'taaruf_completed') {
                                $waText = "Halo Ayah/Bunda, tahapan observasi/wawancara ananda " . $cand->candidate_name . " telah selesai dilaksanakan. Silakan masuk ke portal pendaftaran untuk menandatangani dokumen persetujuan biaya dan pernyataan.";
                            } elseif ($status === 'agreement_signed') {
                                $waText = "Halo Ayah/Bunda, dokumen persetujuan biaya pendaftaran ananda " . $cand->candidate_name . " sudah ditandatangani. Silakan selesaikan kewajiban administrasi sekolah agar status pendaftaran ananda dapat dinyatakan resmi lunas.";
                            } elseif ($status === 'completed') {
                                $waText = "Halo Ayah/Bunda, selamat! Seluruh proses administrasi dan pendaftaran ananda " . $cand->candidate_name . " di Sekolah Anak Saleh telah selesai. Selamat bergabung di keluarga besar kami.";
                            }

                            $waUrl = "https://wa.me/" . $cleanPhone . "?text=" . urlencode($waText);
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-6 text-center text-slate-500 font-bold text-xs">
                                {{ ($candidates->currentPage() - 1) * $candidates->perPage() + $loop->iteration }}
                            </td>
                            <td class="py-4 px-6 font-mono text-xs text-slate-500">
                                SANS-{{ substr($cand->period->year ?? '2026', 0, 4) }}-{{ str_pad($cand->id, 4, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800">{{ $cand->candidate_name }}</div>
                                <div class="text-[10px] text-slate-400">WA: {{ $cand->parent_phone ?: '-' }}</div>
                            </td>
                            <td class="py-4 px-6 font-semibold text-brand-emerald">
                                {{ $cand->admission_level }}
                                <div class="mt-0.5">
                                    @if($cand->classProgram && $cand->classProgram->name === 'Inklusi')
                                        <span class="bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded text-[9px] font-bold border border-indigo-200">Inklusi</span>
                                    @else
                                        <span class="bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded text-[9px] font-bold border border-slate-200">Reguler</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2 py-1.5 rounded-xl text-[10px] font-extrabold border {{ $currentStageColor }}">
                                    {{ $currentStageText }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-slate-500 text-xs font-semibold">
                                {{ $cand->created_at->format('d M Y, H:i') }} WIB
                            </td>
                            <td class="py-4 px-6 text-center">
                                @php
                                    // Prepare JSON data to pass to Javascript modal
                                    $candJson = [
                                        'id_label' => 'SANS-' . substr($cand->period->year ?? '2026', 0, 4) . '-' . str_pad($cand->id, 4, '0', STR_PAD_LEFT),
                                        'name' => $cand->candidate_name,
                                        'nickname' => $cand->nickname ?? '-',
                                        'nik' => $cand->nik ?? '-',
                                        'family_card_no' => $cand->getFieldValue('family_card_no') ?? '-',
                                        'gender' => $cand->gender === 'male' ? 'Laki-laki' : 'Perempuan',
                                        'birth_place' => $cand->birth_place ?? '-',
                                        'birth_date' => $cand->birth_date ? $cand->birth_date->format('d F Y') : '-',
                                        'religion' => $cand->religion ?? '-',
                                        'previous_school' => $cand->previous_school ?? 'Tidak ada',
                                        'admission_level' => $cand->admission_level ?? '-',
                                        'class_program' => $cand->classProgram->name ?? 'Reguler',
                                        
                                        // Tempat Tinggal
                                        'address' => $cand->getFieldValue('address') ?? '-',
                                        'house_number' => $cand->getFieldValue('house_number') ?? '-',
                                        'rt' => $cand->getFieldValue('rt') ?? '-',
                                        'rw' => $cand->getFieldValue('rw') ?? '-',
                                        'kelurahan' => $cand->getFieldValue('kelurahan') ?? '-',
                                        'kecamatan' => $cand->getFieldValue('kecamatan') ?? '-',
                                        'city' => $cand->getFieldValue('city') ?? '-',
                                        'province' => $cand->getFieldValue('province') ?? '-',

                                        // Data Orang Tua
                                        'father_name' => $cand->father_name ?? '-',
                                        'father_nik' => $cand->getFieldValue('father_nik') ?? '-',
                                        'father_address' => $cand->getFieldValue('father_address') ?? '-',
                                        'father_phone' => $cand->getFieldValue('father_phone') ?? $cand->parent_phone ?? '-',
                                        'mother_name' => $cand->mother_name ?? '-',
                                        'mother_nik' => $cand->getFieldValue('mother_nik') ?? '-',
                                        'mother_address' => $cand->getFieldValue('mother_address') ?? '-',
                                        'mother_phone' => $cand->getFieldValue('mother_phone') ?? '-',
                                        'parent_phone' => $cand->parent_phone ?? '-',

                                        // Data Wali
                                        'guardian_name' => $cand->getFieldValue('guardian_name') ?? '-',
                                        'guardian_nik' => $cand->getFieldValue('guardian_nik') ?? '-',
                                        'guardian_address' => $cand->getFieldValue('guardian_address') ?? '-',
                                        'guardian_phone' => $cand->getFieldValue('guardian_phone') ?? '-',

                                        // Lampiran
                                        'student_photo' => $cand->getFieldValue('student_photo_path') ? asset('storage/' . $cand->getFieldValue('student_photo_path')) : null,
                                        'birth_certificate' => $cand->birth_certificate_path ? asset('storage/' . $cand->birth_certificate_path) : null,
                                        'family_card' => $cand->family_card_path ? asset('storage/' . $cand->family_card_path) : null,
                                        'diploma_certificate' => $cand->getFieldValue('diploma_certificate_path') ? asset('storage/' . $cand->getFieldValue('diploma_certificate_path')) : null,
                                        'student_card' => $cand->getFieldValue('student_card_path') ? asset('storage/' . $cand->getFieldValue('student_card_path')) : null,
                                        'special_needs' => $cand->getFieldValue('special_needs_assessment_path') ? asset('storage/' . $cand->getFieldValue('special_needs_assessment_path')) : null,
                                        'payment_receipt' => $cand->getFieldValue('payment_receipt_path') ? asset('storage/' . $cand->getFieldValue('payment_receipt_path')) : null,

                                        'created_at_label' => $cand->created_at->format('d M Y, H:i') . ' WIB',
                                        'status' => strtoupper($cand->registration_status),
                                        'payment_status' => strtoupper($cand->payment_status),
                                        'period' => $cand->period->year ?? '-',
                                        'wave' => $cand->wave->name ?? '-',
                                        'type' => $cand->type->name ?? '-',
                                        'extra_services' => $cand->extraServices->pluck('name')->join(', ') ?: '-',
                                        'form_paid' => $formPaid,
                                        'stage_text' => $currentStageText,
                                    ];
                                @endphp
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" 
                                        onclick="openCandidateDetailModal({{ json_encode($candJson) }})" 
                                        class="bg-brand-emerald hover-emerald text-white px-2.5 py-1.5 rounded-xl text-[10px] font-bold shadow-sm transition flex items-center gap-1">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i> Detail
                                    </button>
                                    @if(!empty($cleanPhone))
                                        <a href="{{ $waUrl }}" target="_blank" 
                                           class="bg-emerald-500 hover:bg-emerald-600 text-white px-2.5 py-1.5 rounded-xl text-[10px] font-bold shadow-sm transition flex items-center gap-1"
                                           title="Hubungi Wali via WhatsApp">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> Hubungi
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-6 text-center text-slate-400">
                                Belum ada calon siswa yang melengkapi biodata.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($candidates->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                {{ $candidates->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Candidate Detail Modal Overlay -->
<div id="detailModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden shadow-2xl border border-slate-100 flex flex-col">
        <!-- Modal Header -->
        <div class="bg-brand-emerald text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
            <div>
                <h3 class="font-extrabold text-base flex items-center gap-2">
                    <i data-lucide="user" class="w-5 h-5 text-brand-yellow"></i>
                    Detail Data Pendaftar
                </h3>
                <p id="det-id-label" class="text-[10px] text-emerald-100 font-mono mt-0.5">ID: SANS-YYYY-XXXX</p>
            </div>
            <button onclick="closeDetailModal()" class="text-white hover:text-brand-yellow font-bold text-lg">&times;</button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-6 space-y-6 overflow-y-auto flex-grow text-xs text-slate-700">
            
            <!-- Progress Timeline -->
            <div id="det-timeline-container"></div>
            
            <!-- Grid: SPMB Admission Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-150">
                <div>
                    <span class="text-[9px] font-bold text-slate-400 uppercase block">Periode</span>
                    <span id="det-period" class="font-bold text-slate-700">2024-2025</span>
                </div>
                <div>
                    <span class="text-[9px] font-bold text-slate-400 uppercase block">Gelombang</span>
                    <span id="det-wave" class="font-bold text-slate-700">Gelombang 1</span>
                </div>
                <div>
                    <span class="text-[9px] font-bold text-slate-400 uppercase block">Jalur Masuk</span>
                    <span id="det-type" class="font-bold text-slate-700">Reguler</span>
                </div>
                <div>
                    <span class="text-[9px] font-bold text-slate-400 uppercase block">Status Berkas</span>
                    <span id="det-status" class="inline-block mt-0.5 px-2 py-0.5 rounded text-[9px] font-bold uppercase">SUBMITTED</span>
                </div>
            </div>

            <!-- Segment 1: Personal Information -->
            <div class="space-y-3">
                <h4 class="font-extrabold text-sm text-brand-emerald border-b border-slate-100 pb-1.5 flex items-center gap-1.5">
                    <i data-lucide="info" class="w-4 h-4"></i> Informasi Calon Siswa
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Nama Lengkap</span>
                        <span id="det-name" class="font-semibold text-slate-800">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Nama Panggilan</span>
                        <span id="det-nickname" class="font-semibold text-slate-800">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">NIK Siswa</span>
                        <span id="det-nik" class="font-mono text-slate-800">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Nomor Kartu Keluarga (KK)</span>
                        <span id="det-family-card-no" class="font-mono text-slate-800 font-bold text-brand-emerald">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Jenis Kelamin</span>
                        <span id="det-gender" class="font-semibold text-slate-800">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Tempat, Tanggal Lahir</span>
                        <span id="det-birth" class="font-semibold text-slate-800">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Agama</span>
                        <span id="det-religion" class="font-semibold text-slate-800">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Asal Sekolah</span>
                        <span id="det-previous-school" class="font-semibold text-slate-800">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Program Kelas</span>
                        <span id="det-program" class="font-bold text-brand-emerald">-</span>
                    </div>
                    <div class="md:col-span-3 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Layanan Tambahan (Non-Formal)</span>
                        <span id="det-extras" class="font-bold text-slate-800">-</span>
                    </div>
                </div>
            </div>

            <!-- Segment 2: Tempat Tinggal -->
            <div class="space-y-3 pt-2">
                <h4 class="font-extrabold text-sm text-brand-emerald border-b border-slate-100 pb-1.5 flex items-center gap-1.5">
                    <i data-lucide="map-pin" class="w-4 h-4"></i> Tempat Tinggal
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Alamat</span>
                        <span id="det-address" class="font-semibold text-slate-800">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Nomor Rumah</span>
                        <span id="det-house-no" class="font-semibold text-slate-800">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">RT / RW</span>
                        <span id="det-rt-rw" class="font-semibold text-slate-800">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Kelurahan / Desa</span>
                        <span id="det-kelurahan" class="font-semibold text-slate-800">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Kecamatan</span>
                        <span id="det-kecamatan" class="font-semibold text-slate-800">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Kabupaten / Kota</span>
                        <span id="det-city" class="font-semibold text-slate-800">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Provinsi</span>
                        <span id="det-province" class="font-semibold text-slate-800">-</span>
                    </div>
                </div>
            </div>

            <!-- Segment 3: Data Orang Tua -->
            <div class="space-y-3 pt-2">
                <h4 class="font-extrabold text-sm text-brand-emerald border-b border-slate-100 pb-1.5 flex items-center gap-1.5">
                    <i data-lucide="users" class="w-4 h-4"></i> Data Orang Tua
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Ayah -->
                    <div class="bg-slate-50/70 p-3.5 rounded-xl border border-slate-200/80 space-y-2">
                        <span class="text-xs font-extrabold text-brand-emerald uppercase block">Data Ayah Kandung</span>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div><span class="text-slate-400 font-bold block">Nama:</span> <span id="det-father-name" class="font-semibold text-slate-800">-</span></div>
                            <div><span class="text-slate-400 font-bold block">NIK:</span> <span id="det-father-nik" class="font-mono text-slate-800">-</span></div>
                            <div><span class="text-slate-400 font-bold block">No. HP:</span> <span id="det-father-phone" class="font-mono text-slate-800">-</span></div>
                            <div><span class="text-slate-400 font-bold block">Alamat:</span> <span id="det-father-addr" class="text-slate-800">-</span></div>
                        </div>
                    </div>
                    <!-- Ibu -->
                    <div class="bg-slate-50/70 p-3.5 rounded-xl border border-slate-200/80 space-y-2">
                        <span class="text-xs font-extrabold text-brand-emerald uppercase block">Data Ibu Kandung</span>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div><span class="text-slate-400 font-bold block">Nama:</span> <span id="det-mother-name" class="font-semibold text-slate-800">-</span></div>
                            <div><span class="text-slate-400 font-bold block">NIK:</span> <span id="det-mother-nik" class="font-mono text-slate-800">-</span></div>
                            <div><span class="text-slate-400 font-bold block">No. HP:</span> <span id="det-mother-phone" class="font-mono text-slate-800">-</span></div>
                            <div><span class="text-slate-400 font-bold block">Alamat:</span> <span id="det-mother-addr" class="text-slate-800">-</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segment 4: Data Wali -->
            <div class="space-y-3 pt-2">
                <h4 class="font-extrabold text-sm text-brand-emerald border-b border-slate-100 pb-1.5 flex items-center gap-1.5">
                    <i data-lucide="user-check" class="w-4 h-4"></i> Data Wali (Jika Bukan Orang Tua Kandung)
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
                    <div>
                        <span class="font-bold text-slate-400 uppercase block">Nama Wali</span>
                        <span id="det-guardian-name" class="font-semibold text-slate-800">-</span>
                    </div>
                    <div>
                        <span class="font-bold text-slate-400 uppercase block">NIK Wali</span>
                        <span id="det-guardian-nik" class="font-mono text-slate-800">-</span>
                    </div>
                    <div>
                        <span class="font-bold text-slate-400 uppercase block">No. HP Wali</span>
                        <span id="det-guardian-phone" class="font-mono text-slate-800">-</span>
                    </div>
                    <div>
                        <span class="font-bold text-slate-400 uppercase block">Alamat Wali</span>
                        <span id="det-guardian-addr" class="text-slate-800">-</span>
                    </div>
                </div>
            </div>

            <!-- Segment 5: Uploaded Documents -->
            <div class="space-y-3 pt-2">
                <h4 class="font-extrabold text-sm text-brand-emerald border-b border-slate-100 pb-1.5 flex items-center gap-1.5">
                    <i data-lucide="file-text" class="w-4 h-4"></i> Data Lampiran Dokumen
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <!-- Foto -->
                    <div id="det-photo-box" class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="image" class="w-5 h-5 text-brand-emerald"></i>
                            <div><span class="text-xs font-bold text-slate-700 block">Pas Foto Murid</span><span class="text-[9px] text-slate-400">Formal</span></div>
                        </div>
                        <a id="det-photo-link" href="#" target="_blank" class="bg-brand-emerald hover-emerald text-white px-2.5 py-1 rounded text-xs font-bold transition">Buka</a>
                    </div>
                    <!-- Akta -->
                    <div id="det-cert-box" class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="file-digit" class="w-5 h-5 text-brand-emerald"></i>
                            <div><span class="text-xs font-bold text-slate-700 block">Akta Kelahiran</span><span class="text-[9px] text-slate-400">Scan Asli</span></div>
                        </div>
                        <a id="det-cert-link" href="#" target="_blank" class="bg-brand-emerald hover-emerald text-white px-2.5 py-1 rounded text-xs font-bold transition">Buka</a>
                    </div>
                    <!-- KK -->
                    <div id="det-card-box" class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="file-digit" class="w-5 h-5 text-brand-emerald"></i>
                            <div><span class="text-xs font-bold text-slate-700 block">Kartu Keluarga</span><span class="text-[9px] text-slate-400">Scan Asli</span></div>
                        </div>
                        <a id="det-card-link" href="#" target="_blank" class="bg-brand-emerald hover-emerald text-white px-2.5 py-1 rounded text-xs font-bold transition">Buka</a>
                    </div>
                    <!-- Ijazah -->
                    <div id="det-diploma-box" class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="award" class="w-5 h-5 text-brand-emerald"></i>
                            <div><span class="text-xs font-bold text-slate-700 block">Ijazah Terakhir</span><span class="text-[9px] text-slate-400">Dokumen</span></div>
                        </div>
                        <a id="det-diploma-link" href="#" target="_blank" class="bg-brand-emerald hover-emerald text-white px-2.5 py-1 rounded text-xs font-bold transition">Buka</a>
                    </div>
                    <!-- NISN -->
                    <div id="det-nisn-box" class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="credit-card" class="w-5 h-5 text-brand-emerald"></i>
                            <div><span class="text-xs font-bold text-slate-700 block">NISN / Kartu Pelajar</span><span class="text-[9px] text-slate-400">Opsional</span></div>
                        </div>
                        <a id="det-nisn-link" href="#" target="_blank" class="bg-brand-emerald hover-emerald text-white px-2.5 py-1 rounded text-xs font-bold transition">Buka</a>
                    </div>
                    <!-- Assesmen Khusus -->
                    <div id="det-special-box" class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="file-heart" class="w-5 h-5 text-brand-emerald"></i>
                            <div><span class="text-xs font-bold text-slate-700 block">Assesmen Kebutuhan Khusus</span><span class="text-[9px] text-slate-400">Jika Ada</span></div>
                        </div>
                        <a id="det-special-link" href="#" target="_blank" class="bg-brand-emerald hover-emerald text-white px-2.5 py-1 rounded text-xs font-bold transition">Buka</a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex justify-between items-center flex-shrink-0">
            <div>
                <span class="text-[9px] font-bold text-slate-400 uppercase block">Tanggal Masuk Formulir</span>
                <span id="det-created" class="text-[10px] font-semibold text-slate-600">20 Aug 2026, 03:00 WIB</span>
            </div>
            <button onclick="closeDetailModal()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition">
                Tutup Detail
            </button>
        </div>
    </div>
</div>

<script>
    function renderModalTimeline(cand) {
        const status = cand.status.toLowerCase();
        const formPaid = cand.form_paid;
        
        let activeStep = 0;
        let isFailed = false;
        
        if (status === 'draft') {
            if (!formPaid) {
                activeStep = 0; // Pembayaran Formulir
            } else {
                activeStep = 1; // Pengisian Formulir
            }
        } else if (status === 'failed') {
            activeStep = 1; // Pengisian Formulir (needs correction)
            isFailed = true;
        } else if (status === 'submitted') {
            activeStep = 2; // Verifikasi Berkas
        } else if (status === 'verified') {
            activeStep = 3; // Observasi / Ta'aruf
        } else if (status === 'taaruf_completed') {
            activeStep = 4; // Persetujuan Pernyataan
        } else if (status === 'agreement_signed') {
            activeStep = 5; // Administrasi Akhir
        } else if (status === 'completed') {
            activeStep = 6; // Kelulusan & Selesai
        }
        
        const steps = [
            { title: 'Pembayaran', desc: 'Biaya Pendaftaran' },
            { title: 'Formulir', desc: 'Biodata & Berkas' },
            { title: 'Verifikasi', desc: 'Review Panitia' },
            { title: 'Ta\'aruf', desc: 'Observasi & Tes' },
            { title: 'Persetujuan', desc: 'Tanda Tangan Biaya' },
            { title: 'Daftar Ulang', desc: 'Administrasi Akhir' },
            { title: 'Lulus & Selesai', desc: 'Resmi Diterima' }
        ];
        
        let html = `
            <div class="space-y-3 bg-slate-50/70 dark:bg-slate-900/30 p-5 rounded-2xl border border-slate-100 dark:border-slate-800">
                <h4 class="font-extrabold text-[10px] text-brand-emerald dark:text-emerald-400 uppercase tracking-wider flex items-center gap-1.5 mb-4">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                    Status Progres Pendaftaran Calon Siswa
                </h4>
                <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-6 py-2">
                    <!-- Progress Line (Desktop) -->
                    <div class="hidden md:block absolute left-8 right-8 top-[15px] h-1 bg-slate-200 dark:bg-slate-800 -z-10">
                        <div class="h-full bg-brand-emerald dark:bg-emerald-500 transition-all duration-500" style="width: ${(activeStep / (steps.length - 1)) * 100}%"></div>
                    </div>
        `;
        
        steps.forEach((step, index) => {
            let state = 'pending';
            let iconHtml = '';
            
            if (index < activeStep) {
                state = 'completed';
                iconHtml = '<i data-lucide="check" class="w-3.5 h-3.5 text-white"></i>';
            } else if (index === activeStep) {
                if (isFailed) {
                    state = 'failed';
                    iconHtml = '<i data-lucide="x" class="w-3.5 h-3.5 text-white"></i>';
                } else if (status === 'completed') {
                    state = 'completed';
                    iconHtml = '<i data-lucide="check" class="w-3.5 h-3.5 text-white"></i>';
                } else {
                    state = 'active';
                    iconHtml = `<span class="text-[10px] font-black text-slate-900">${index + 1}</span>`;
                }
            } else {
                state = 'pending';
                iconHtml = `<span class="text-[10px] font-bold text-slate-400 dark:text-slate-600">${index + 1}</span>`;
            }
            
            let circleClass = '';
            let labelClass = '';
            
            if (state === 'completed') {
                circleClass = 'bg-brand-emerald border-brand-emerald dark:bg-emerald-600 dark:border-emerald-600';
                labelClass = 'text-brand-emerald font-extrabold dark:text-emerald-400';
            } else if (state === 'active') {
                circleClass = 'bg-brand-yellow border-brand-yellow font-black ring-4 ring-amber-100 dark:ring-amber-950/40';
                labelClass = 'text-slate-800 font-extrabold dark:text-white';
            } else if (state === 'failed') {
                circleClass = 'bg-red-500 border-red-500 font-bold ring-4 ring-red-100 dark:ring-red-950/40';
                labelClass = 'text-red-500 font-extrabold dark:text-red-400';
            } else {
                circleClass = 'bg-white border-slate-200 dark:bg-slate-900 dark:border-slate-800';
                labelClass = 'text-slate-400 dark:text-slate-500 font-semibold';
            }
            
            html += `
                <div class="flex md:flex-col items-center gap-3 md:gap-2 flex-1 w-full text-left md:text-center relative">
                    <!-- Step Circle -->
                    <div class="h-8 w-8 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all duration-300 z-10 ${circleClass}">
                        ${iconHtml}
                    </div>
                    <!-- Label Text -->
                    <div class="flex flex-col md:items-center leading-none">
                        <span class="text-[10px] ${labelClass}">${step.title}</span>
                        <span class="text-[8px] text-slate-400 dark:text-slate-500 hidden md:block mt-1 max-w-[100px] leading-tight">${step.desc}</span>
                    </div>
                </div>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
        
        return html;
    }

    function openCandidateDetailModal(cand) {
        document.getElementById('det-id-label').innerText = 'ID: ' + cand.id_label;
        document.getElementById('det-period').innerText = cand.period;
        document.getElementById('det-wave').innerText = cand.wave;
        document.getElementById('det-type').innerText = cand.type;
        
        // Render Timeline Progress
        document.getElementById('det-timeline-container').innerHTML = renderModalTimeline(cand);
        
        // Status Badge Style
        const statusEl = document.getElementById('det-status');
        statusEl.innerText = cand.status;
        statusEl.className = "inline-block mt-0.5 px-2 py-0.5 rounded text-[9px] font-bold uppercase";
        if (cand.status === 'VERIFIED') {
            statusEl.classList.add('bg-green-50', 'text-green-700', 'border', 'border-green-200');
        } else if (cand.status === 'SUBMITTED') {
            statusEl.classList.add('bg-blue-50', 'text-blue-700', 'border', 'border-blue-200');
        } else {
            statusEl.classList.add('bg-slate-100', 'text-slate-600', 'border', 'border-slate-300');
        }

        document.getElementById('det-name').innerText = cand.name || '-';
        document.getElementById('det-nickname').innerText = cand.nickname || '-';
        document.getElementById('det-nik').innerText = cand.nik || '-';
        document.getElementById('det-family-card-no').innerText = cand.family_card_no || '-';
        document.getElementById('det-gender').innerText = cand.gender || '-';
        document.getElementById('det-birth').innerText = cand.birth_place + ', ' + cand.birth_date;
        document.getElementById('det-religion').innerText = cand.religion || '-';
        document.getElementById('det-previous-school').innerText = cand.previous_school || '-';
        document.getElementById('det-program').innerText = cand.class_program || 'Reguler';
        document.getElementById('det-extras').innerText = cand.extra_services || '-';
        
        // Tempat Tinggal
        document.getElementById('det-address').innerText = cand.address || '-';
        document.getElementById('det-house-no').innerText = cand.house_number || '-';
        document.getElementById('det-rt-rw').innerText = (cand.rt !== '-' || cand.rw !== '-') ? (cand.rt + ' / ' + cand.rw) : '-';
        document.getElementById('det-kelurahan').innerText = cand.kelurahan || '-';
        document.getElementById('det-kecamatan').innerText = cand.kecamatan || '-';
        document.getElementById('det-city').innerText = cand.city || '-';
        document.getElementById('det-province').innerText = cand.province || '-';

        // Orang Tua
        document.getElementById('det-father-name').innerText = cand.father_name || '-';
        document.getElementById('det-father-nik').innerText = cand.father_nik || '-';
        document.getElementById('det-father-phone').innerText = cand.father_phone || '-';
        document.getElementById('det-father-addr').innerText = cand.father_address || '-';

        document.getElementById('det-mother-name').innerText = cand.mother_name || '-';
        document.getElementById('det-mother-nik').innerText = cand.mother_nik || '-';
        document.getElementById('det-mother-phone').innerText = cand.mother_phone || '-';
        document.getElementById('det-mother-addr').innerText = cand.mother_address || '-';

        // Wali
        document.getElementById('det-guardian-name').innerText = cand.guardian_name || '-';
        document.getElementById('det-guardian-nik').innerText = cand.guardian_nik || '-';
        document.getElementById('det-guardian-phone').innerText = cand.guardian_phone || '-';
        document.getElementById('det-guardian-addr').innerText = cand.guardian_address || '-';
        
        document.getElementById('det-created').innerText = cand.created_at_label;

        // Lampiran Helper
        function setupFileLink(boxId, linkId, url) {
            const box = document.getElementById(boxId);
            const link = document.getElementById(linkId);
            if (!box || !link) return;
            if (url) {
                box.classList.remove('opacity-50');
                link.href = url;
                link.style.display = 'inline-block';
            } else {
                box.classList.add('opacity-50');
                link.style.display = 'none';
            }
        }

        setupFileLink('det-photo-box', 'det-photo-link', cand.student_photo);
        setupFileLink('det-cert-box', 'det-cert-link', cand.birth_certificate);
        setupFileLink('det-card-box', 'det-card-link', cand.family_card);
        setupFileLink('det-diploma-box', 'det-diploma-link', cand.diploma_certificate);
        setupFileLink('det-nisn-box', 'det-nisn-link', cand.student_card);
        setupFileLink('det-special-box', 'det-special-link', cand.special_needs);

        document.getElementById('detailModal').classList.remove('hidden');
        
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
    }

    document.getElementById('detailModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDetailModal();
        }
    });

    // Escape key listener to close detail modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const detailModal = document.getElementById('detailModal');
            if (detailModal && !detailModal.classList.contains('hidden')) {
                closeDetailModal();
            }
        }
    });
</script>
@endsection
