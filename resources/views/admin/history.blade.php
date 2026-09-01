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
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="showFeatureComingSoon('Ekspor Riwayat Pendaftar (Excel)')" class="bg-brand-emerald hover-emerald text-white px-3.5 py-2 rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-2 cursor-pointer">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-200"></i>
                <span>Ekspor Excel</span>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-amber-400 text-amber-950 shadow-2xs">Soon</span>
            </button>
            <button type="button" onclick="showFeatureComingSoon('Cetak Riwayat Pendaftar (PDF)')" class="border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4 text-slate-400"></i>
                <span>Cetak PDF</span>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300">Soon</span>
            </button>
        </div>
    </div>

    <!-- Candidate List Table -->
    <div id="history-card" class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden" hx-boost="true" hx-target="#history-card" hx-select="#history-card">
        
        <!-- Search & Filter Form -->
        <form id="historyFilterForm" action="{{ route('admin.history') }}" method="GET" hx-boost="false" class="p-6 bg-slate-50/50 border-b border-slate-100 space-y-4">
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
                        <option value="agreement_signed" {{ request('status') === 'agreement_signed' ? 'selected' : '' }}>Administrasi</option>
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
                    @php
                        $feeCategories = \App\Models\SpmbFeeCategory::with(['fees', 'units'])->get();
                    @endphp
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
                                $currentStageText = 'Administrasi';
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
                                    $finalFeeCalc = app(\App\Http\Controllers\Web\WebDashboardController::class)->getFinalFeeDetails($cand);
                                    $tab3FeeItems = $cand->final_fee_snapshot['items'] ?? $finalFeeCalc['items'] ?? [];
                                    if ($cand->extraServices && $cand->extraServices->isNotEmpty() && !empty($finalFeeCalc['items'])) {
                                        $existingNames = array_map('strtoupper', array_column($tab3FeeItems, 'name'));
                                        foreach ($finalFeeCalc['items'] as $calcItem) {
                                            if (!in_array(strtoupper($calcItem['name']), $existingNames)) {
                                                $tab3FeeItems[] = $calcItem;
                                            }
                                        }
                                    }
                                    $calcGross = array_sum(array_column($tab3FeeItems, 'amount'));
                                    $calcDisc = (float) ($cand->discount_amount ?? 0);
                                    $calcNet = max(0, $calcGross - $calcDisc);

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

                                        // Keringanan & Cicilan
                                        'id' => $cand->id,
                                        'discount_amount' => (float) ($cand->discount_amount ?? 0),
                                        'discount_notes' => $cand->discount_notes ?? '',
                                        'installment_mode' => $cand->installment_mode ?? 'none',
                                        'installment_allowed_fee_ids' => $cand->installment_allowed_fee_ids ?? [],
                                        'min_installment_amount' => (float) ($cand->min_installment_amount ?? 0),
                                        'gross_fee' => (float) $calcGross,
                                        'net_fee' => (float) $calcNet,
                                        'total_paid' => (float) ($cand->total_paid_final_fee ?? 0),
                                        'remaining_balance' => (float) max(0, $calcNet - ($cand->total_paid_final_fee ?? 0)),
                                        'fee_items' => $tab3FeeItems,
                                        'save_installment_url' => route('admin.candidates.installment-settings', $cand->id),
                                        'unit_name' => $cand->unit->name ?? 'Anak Saleh',
                                        'registration_fee_nominal' => (float) ($cand->unit->registration_fee ?? (app(\App\Http\Controllers\Web\WebDashboardController::class)->getRegistrationFee($cand)->amount ?? 350000)),

                                        // Riwayat Transaksi Payments
                                        'payments' => $cand->payments->map(function($p) use ($cand) {
                                            $catName = $p->payment_type === 'registration_fee' ? 'Formulir Pendaftaran' : 'Biaya Administrasi Masuk';
                                            $feeName = $p->payment_type === 'registration_fee' 
                                                ? ('Formulir Pendaftaran ' . ($cand->unit->name ?? ''))
                                                : (!empty($p->payment_info['selected_items']) 
                                                    ? collect($p->payment_info['selected_items'])->pluck('name')->join(', ')
                                                    : 'Biaya Masuk Siswa Baru');

                                            return [
                                                'id' => $p->id,
                                                'order_id' => $p->invoice_number ?: ('PAY-' . $p->id),
                                                'invoice_number' => $p->invoice_number ?: ('PAY-' . $p->id),
                                                'payment_type' => $p->payment_type,
                                                'category_name' => $catName,
                                                'fee_name' => $feeName,
                                                'amount' => (float) $p->amount,
                                                'status' => strtolower($p->status),
                                                'payment_channel' => $p->payment_channel ?: ($p->payment_method ?: 'Online'),
                                                'payment_method' => $p->payment_method ?: '-',
                                                'va_number' => $p->va_number ?: '-',
                                                'created_at_formatted' => $p->created_at ? $p->created_at->format('d M Y, H:i') . ' WIB' : '-',
                                                'paid_at_formatted' => $p->paid_at ? $p->paid_at->format('d M Y, H:i') . ' WIB' : ($p->status === 'success' && $p->updated_at ? $p->updated_at->format('d M Y, H:i') . ' WIB' : '-'),
                                                'selected_items' => $p->payment_info['selected_items'] ?? [],
                                            ];
                                        })->values()->all(),

                                        // Pengelompokan Kategori Tarif & Biaya Dinamis Sesuai Unit & Layanan Siswa & Persetujuan Pernyataan
                                        ...((function() use ($cand, $feeCategories) {
                                            $candUnitId = $cand->spmb_unit_id;
                                            $candPayments = $cand->payments ?? collect();
                                            $candSuccessPayments = $candPayments->whereIn('status', ['success', 'settled']);
                                            $regPayment = $candSuccessPayments->where('payment_type', 'registration_fee')->sortByDesc('id')->first()
                                                ?? $candPayments->where('payment_type', 'registration_fee')->sortByDesc('id')->first();
                                            $finalSuccessPayments = $candSuccessPayments->where('payment_type', 'final_fee');
                                            $isAllFinalPaid = ($cand->remaining_balance <= 0 && $cand->total_paid_final_fee > 0);

                                            $hasAgreed = !is_null($cand->signed_at) 
                                                || in_array($cand->registration_status, ['agreement_signed', 'completed', 'registered', 're_registration']) 
                                                || !empty($cand->final_fee_snapshot);

                                            $categoriesResult = [];
                                            $totalGross = 0;
                                            $totalPaid = 0;
                                            $paidCount = 0;

                                            foreach ($feeCategories as $cat) {
                                                $catName = $cat->name;
                                                $isFormulir = (stripos($catName, 'Formulir') !== false);

                                                // Biaya Administrasi & Biaya Tambahan hanya muncul jika pendaftar sudah menyetujui surat pernyataan
                                                if (!$hasAgreed && !$isFormulir) {
                                                    continue;
                                                }

                                                // Filter fees strictly belonging to this candidate's unit
                                                $unitFees = $cat->fees->filter(function($f) use ($candUnitId) {
                                                    return $f->is_active && ($f->spmb_unit_id == $candUnitId);
                                                });

                                                // Filter Biaya Tambahan: check against both name and code of candidate's extraServices
                                                if (stripos($catName, 'Tambahan') !== false) {
                                                    $extraServices = $cand->extraServices ?? collect();
                                                    if ($extraServices->isEmpty()) {
                                                        continue; // Skip Biaya Tambahan category entirely if candidate chose none
                                                    }
                                                    $unitFees = $unitFees->filter(function($f) use ($extraServices) {
                                                        $feeNameClean = strtolower(trim($f->name));
                                                        return $extraServices->contains(function($es) use ($feeNameClean) {
                                                            $esNameClean = strtolower(trim($es->name ?? ''));
                                                            $esCodeClean = strtolower(trim($es->code ?? ''));

                                                            return ($feeNameClean === $esNameClean)
                                                                || ($feeNameClean === $esCodeClean)
                                                                || (!empty($esCodeClean) && str_contains($feeNameClean, $esCodeClean))
                                                                || (!empty($feeNameClean) && str_contains($esNameClean, $feeNameClean));
                                                        });
                                                    });
                                                }

                                                if ($unitFees->isEmpty()) {
                                                    continue;
                                                }

                                                $items = [];
                                                foreach ($unitFees as $fee) {
                                                    $feeName = $fee->name;
                                                    $feeAmount = (float) $fee->amount;
                                                    
                                                    $isPaid = false;
                                                    $status = 'unpaid';
                                                    $invoiceNo = '-';
                                                    $method = '-';
                                                    $paidTime = '-';
                                                    $amountPaid = $feeAmount;

                                                    if ($isFormulir) {
                                                        if ($cand->form_paid || ($regPayment && in_array($regPayment->status, ['success', 'settled']))) {
                                                            $isPaid = true;
                                                            $status = 'paid';
                                                            $invoiceNo = $regPayment->invoice_number ?: ($regPayment->order_id ?: ('PAY-' . $regPayment->id));
                                                            $method = $regPayment->payment_channel ?: ($regPayment->payment_method ?: 'Online');
                                                            $paidTime = $regPayment->paid_at ? $regPayment->paid_at->format('d M Y, H:i') . ' WIB' : ($regPayment->created_at ? $regPayment->created_at->format('d M Y, H:i') . ' WIB' : '-');
                                                            $amountPaid = (float) ($regPayment->amount ?: $feeAmount);
                                                        } elseif ($regPayment && $regPayment->status === 'pending') {
                                                            $status = 'pending';
                                                            $invoiceNo = $regPayment->invoice_number ?: ($regPayment->order_id ?: ('PAY-' . $regPayment->id));
                                                            $method = $regPayment->payment_channel ?: ($regPayment->payment_method ?: 'Online');
                                                            $paidTime = $regPayment->created_at ? $regPayment->created_at->format('d M Y, H:i') . ' WIB' : '-';
                                                            $amountPaid = (float) $regPayment->amount;
                                                        }
                                                    } else {
                                                        if ($isAllFinalPaid) {
                                                            $isPaid = true;
                                                            $status = 'paid';
                                                            $latestPay = $finalSuccessPayments->sortByDesc('id')->first();
                                                            if ($latestPay) {
                                                                $invoiceNo = $latestPay->invoice_number ?: ($latestPay->order_id ?: ('PAY-' . $latestPay->id));
                                                                $method = $latestPay->payment_channel ?: ($latestPay->payment_method ?: 'Online');
                                                                $paidTime = $latestPay->paid_at ? $latestPay->paid_at->format('d M Y, H:i') . ' WIB' : ($latestPay->created_at ? $latestPay->created_at->format('d M Y, H:i') . ' WIB' : '-');
                                                            }
                                                        } else {
                                                            $matchedPay = $finalSuccessPayments->first(function($p) use ($feeName) {
                                                                if (!isset($p->payment_info['selected_items'])) return false;
                                                                $itemsList = collect($p->payment_info['selected_items']);
                                                                return $itemsList->contains(function($si) use ($feeName) {
                                                                    $siName = strtolower(trim($si['name'] ?? ''));
                                                                    $fName = strtolower(trim($feeName));
                                                                    return $siName === $fName || str_contains($siName, $fName) || str_contains($fName, $siName);
                                                                });
                                                            });

                                                            if ($matchedPay) {
                                                                $isPaid = true;
                                                                $status = 'paid';
                                                                $invoiceNo = $matchedPay->invoice_number ?: ($matchedPay->order_id ?: ('PAY-' . $matchedPay->id));
                                                                $method = $matchedPay->payment_channel ?: ($matchedPay->payment_method ?: 'Online');
                                                                $paidTime = $matchedPay->paid_at ? $matchedPay->paid_at->format('d M Y, H:i') . ' WIB' : ($matchedPay->created_at ? $matchedPay->created_at->format('d M Y, H:i') . ' WIB' : '-');
                                                            }
                                                        }
                                                    }

                                                    $totalGross += $feeAmount;
                                                    if ($isPaid) {
                                                        $totalPaid += $feeAmount;
                                                        $paidCount++;
                                                    }

                                                    $items[] = [
                                                        'fee_id' => $fee->id,
                                                        'name' => $feeName,
                                                        'amount' => $feeAmount,
                                                        'is_paid' => $isPaid,
                                                        'status' => $status,
                                                        'invoice_no' => $invoiceNo,
                                                        'payment_method' => $method,
                                                        'paid_time' => $paidTime,
                                                        'amount_paid' => $amountPaid,
                                                    ];
                                                }

                                                $categoriesResult[] = [
                                                    'category_id' => $cat->id,
                                                    'category_name' => strtoupper($catName),
                                                    'items' => $items,
                                                ];
                                            }

                                            $discount = (float) ($cand->discount_amount ?? 0);
                                            $totalNet = max(0, $totalGross - $discount);
                                            $remBalance = max(0, $totalNet - $totalPaid);

                                            return [
                                                'has_agreed_statement' => $hasAgreed,
                                                'signed_at_formatted' => $cand->signed_at ? $cand->signed_at->format('d M Y, H:i') . ' WIB' : null,
                                                'signature_name' => $cand->signature_name,
                                                'fee_categories' => $categoriesResult,
                                                'all_gross_fee' => $totalGross,
                                                'all_net_fee' => $totalNet,
                                                'all_total_paid' => $totalPaid,
                                                'all_remaining_balance' => $remBalance,
                                                'all_paid_items_count' => $paidCount,
                                            ];
                                        })()),
                                    ];
                                @endphp
                                <div class="flex items-center justify-center">
                                    <button type="button" 
                                        id="cand-btn-{{ $cand->id }}"
                                        onclick="openCandidateDetailModal({{ json_encode($candJson) }})" 
                                        class="bg-brand-emerald hover-emerald text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i> Detail
                                    </button>
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
<div id="detailModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6 overflow-hidden">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-4xl w-full h-[88vh] max-h-[88vh] overflow-hidden shadow-2xl border border-slate-100 dark:border-slate-800 flex flex-col transition-all duration-200">
        <!-- Modal Header -->
        <div class="text-white px-6 py-4 flex items-center justify-between flex-shrink-0 border-b border-emerald-900/40 shadow-sm" style="background-color: #064e3b;">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-2xl bg-white/15 flex items-center justify-center text-amber-300 flex-shrink-0">
                    <i data-lucide="user" class="w-5 h-5 text-amber-300"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 id="modal-header-cand-name" class="font-extrabold text-base text-white">Detail Calon Siswa</h3>
                        <span id="det-status-chip" class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-white/20 text-white border border-white/20">SUBMITTED</span>
                    </div>
                    <p id="det-id-label" class="text-xs text-emerald-200 font-mono mt-0.5">ID: SANS-YYYY-XXXX</p>
                </div>
            </div>
            <button type="button" onclick="closeDetailModal()" class="text-white hover:text-emerald-100 bg-white/15 hover:bg-white/25 p-2 rounded-xl transition flex items-center justify-center cursor-pointer shadow-sm">
                <i data-lucide="x" class="w-5 h-5 text-white"></i>
            </button>
        </div>

        <!-- Pinned Candidate Progress Timeline (Above Tabs) -->
        <div id="det-timeline-container" class="bg-slate-50/80 dark:bg-slate-900/90 border-b border-slate-200 dark:border-slate-800 px-6 py-3 flex-shrink-0"></div>

        <!-- Sticky Tab Navigation Bar (4 Tabs) -->
        <div class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 px-6 py-2.5 flex items-center gap-2 flex-shrink-0 overflow-x-auto select-none">
            <button type="button" id="tab-btn-biodata" onclick="switchCandidateTab('biodata')" class="cand-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 border border-emerald-600 text-emerald-700 dark:text-emerald-300 bg-emerald-50/70 dark:bg-emerald-950/60 shadow-sm whitespace-nowrap">
                <i data-lucide="user-check" class="w-4 h-4"></i> Biodata & Orang Tua
            </button>
            <button type="button" id="tab-btn-documents" onclick="switchCandidateTab('documents')" class="cand-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 border border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 whitespace-nowrap">
                <i data-lucide="folder" class="w-4 h-4"></i> Berkas Lampiran
            </button>
            <button type="button" id="tab-btn-installment" onclick="switchCandidateTab('installment')" class="cand-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 border border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 whitespace-nowrap">
                <i data-lucide="sliders-horizontal" class="w-4 h-4"></i> Kebijakan Cicilan & Diskon
                <span id="tab-installment-badge" class="hidden px-1.5 py-0.5 rounded-md text-[9px] font-extrabold bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300"></span>
            </button>
            <button type="button" id="tab-btn-payments" onclick="switchCandidateTab('payments')" class="cand-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 border border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 whitespace-nowrap">
                <i data-lucide="credit-card" class="w-4 h-4"></i> Data Pembayaran
                <span id="tab-payments-badge" class="px-1.5 py-0.5 rounded-md text-[9px] font-extrabold bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">Belum Bayar</span>
            </button>
        </div>

        <!-- Modal Body (Scrollable Tab Panes with Consistent Height) -->
        <div id="modalDetailBody" class="p-6 overflow-y-auto flex-1 min-h-0 text-xs text-slate-700 dark:text-slate-300 space-y-6">
            
            <!-- TAB PANE 1: BIODATA & ORANG TUA -->
            <div id="tab-pane-biodata" class="cand-tab-pane space-y-6">
                <!-- Grid: SPMB Admission Stats -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Periode</span>
                        <span id="det-period" class="font-bold text-slate-700 dark:text-slate-200 text-xs">2024-2025</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Gelombang</span>
                        <span id="det-wave" class="font-bold text-slate-700 dark:text-slate-200 text-xs">Gelombang 1</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Jalur Masuk</span>
                        <span id="det-type" class="font-bold text-slate-700 dark:text-slate-200 text-xs">Reguler</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Status Berkas</span>
                        <span id="det-status" class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-extrabold uppercase bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200">SUBMITTED</span>
                    </div>
                </div>

                <!-- Segment 1: Personal Information -->
                <div class="space-y-4 bg-white dark:bg-slate-800/80 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                    <h4 class="font-extrabold text-xs text-brand-emerald dark:text-emerald-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 dark:border-slate-700/80 pb-3">
                        <i data-lucide="info" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i> Informasi Pribadi Calon Siswa
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Nama Lengkap</span>
                            <span id="det-name" class="font-bold text-slate-800 dark:text-slate-100 text-xs">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Nama Panggilan</span>
                            <span id="det-nickname" class="font-semibold text-slate-800 dark:text-slate-200 text-xs">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">NIK Siswa</span>
                            <span id="det-nik" class="font-mono font-bold text-slate-800 dark:text-slate-100 text-xs">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Nomor Kartu Keluarga (KK)</span>
                            <span id="det-family-card-no" class="font-mono text-slate-800 dark:text-slate-100 font-bold text-brand-emerald dark:text-emerald-400 text-xs">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Jenis Kelamin</span>
                            <span id="det-gender" class="font-semibold text-slate-800 dark:text-slate-200 text-xs">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Tempat, Tanggal Lahir</span>
                            <span id="det-birth" class="font-semibold text-slate-800 dark:text-slate-200 text-xs">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Agama</span>
                            <span id="det-religion" class="font-semibold text-slate-800 dark:text-slate-200 text-xs">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Asal Sekolah</span>
                            <span id="det-previous-school" class="font-semibold text-slate-800 dark:text-slate-200 text-xs">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Program Kelas</span>
                            <span id="det-program" class="font-bold text-brand-emerald dark:text-emerald-400 text-xs">-</span>
                        </div>
                        <div class="sm:col-span-2 md:col-span-3 bg-slate-50 dark:bg-slate-900/60 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700">
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Layanan Tambahan (Non-Formal)</span>
                            <span id="det-extras" class="font-bold text-slate-800 dark:text-slate-100 text-xs">-</span>
                        </div>
                    </div>
                </div>

                <!-- Segment 2: Tempat Tinggal -->
                <div class="space-y-4 bg-white dark:bg-slate-800/80 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                    <h4 class="font-extrabold text-xs text-brand-emerald dark:text-emerald-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 dark:border-slate-700/80 pb-3">
                        <i data-lucide="map-pin" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i> Domisili & Tempat Tinggal
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-5">
                        <div class="sm:col-span-2">
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Alamat Jalan</span>
                            <span id="det-address" class="font-semibold text-slate-800 dark:text-slate-100 text-xs">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Nomor Rumah</span>
                            <span id="det-house-no" class="font-semibold text-slate-800 dark:text-slate-100 text-xs">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">RT / RW</span>
                            <span id="det-rt-rw" class="font-semibold text-slate-800 dark:text-slate-100 text-xs">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Kelurahan / Desa</span>
                            <span id="det-kelurahan" class="font-semibold text-slate-800 dark:text-slate-100 text-xs">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Kecamatan</span>
                            <span id="det-kecamatan" class="font-semibold text-slate-800 dark:text-slate-100 text-xs">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Kabupaten / Kota</span>
                            <span id="det-city" class="font-semibold text-slate-800 dark:text-slate-100 text-xs">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Provinsi</span>
                            <span id="det-province" class="font-semibold text-slate-800 dark:text-slate-100 text-xs">-</span>
                        </div>
                    </div>
                </div>

                <!-- Segment 3: Data Orang Tua & Wali -->
                <div class="space-y-4 bg-white dark:bg-slate-800/80 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                    <h4 class="font-extrabold text-xs text-brand-emerald dark:text-emerald-400 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 dark:border-slate-700/80 pb-3">
                        <i data-lucide="users" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i> Data Orang Tua & Wali
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Ayah -->
                        <div class="bg-slate-50 dark:bg-slate-900/60 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-3">
                            <span class="text-[11px] font-extrabold text-brand-emerald dark:text-emerald-400 uppercase tracking-wider block border-b border-slate-200/60 dark:border-slate-700 pb-2">Data Ayah Kandung</span>
                            <div class="grid grid-cols-2 gap-3.5 text-xs">
                                <div><span class="text-slate-400 dark:text-slate-400 font-bold block text-[10px]">Nama:</span> <span id="det-father-name" class="font-semibold text-slate-800 dark:text-slate-100">-</span></div>
                                <div><span class="text-slate-400 dark:text-slate-400 font-bold block text-[10px]">NIK:</span> <span id="det-father-nik" class="font-mono text-slate-800 dark:text-slate-100">-</span></div>
                                <div><span class="text-slate-400 dark:text-slate-400 font-bold block text-[10px]">No. HP:</span> <span id="det-father-phone" class="font-mono text-slate-800 dark:text-slate-100">-</span></div>
                                <div><span class="text-slate-400 dark:text-slate-400 font-bold block text-[10px]">Alamat:</span> <span id="det-father-addr" class="text-slate-800 dark:text-slate-200">-</span></div>
                            </div>
                        </div>
                        <!-- Ibu -->
                        <div class="bg-slate-50 dark:bg-slate-900/60 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-3">
                            <span class="text-[11px] font-extrabold text-brand-emerald dark:text-emerald-400 uppercase tracking-wider block border-b border-slate-200/60 dark:border-slate-700 pb-2">Data Ibu Kandung</span>
                            <div class="grid grid-cols-2 gap-3.5 text-xs">
                                <div><span class="text-slate-400 dark:text-slate-400 font-bold block text-[10px]">Nama:</span> <span id="det-mother-name" class="font-semibold text-slate-800 dark:text-slate-100">-</span></div>
                                <div><span class="text-slate-400 dark:text-slate-400 font-bold block text-[10px]">NIK:</span> <span id="det-mother-nik" class="font-mono text-slate-800 dark:text-slate-100">-</span></div>
                                <div><span class="text-slate-400 dark:text-slate-400 font-bold block text-[10px]">No. HP:</span> <span id="det-mother-phone" class="font-mono text-slate-800 dark:text-slate-100">-</span></div>
                                <div><span class="text-slate-400 dark:text-slate-400 font-bold block text-[10px]">Alamat:</span> <span id="det-mother-addr" class="text-slate-800 dark:text-slate-200">-</span></div>
                            </div>
                        </div>
                    </div>
                    <!-- Wali -->
                    <div class="bg-slate-50 dark:bg-slate-900/60 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-3 mt-4">
                        <span class="text-[11px] font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider block border-b border-slate-200/60 dark:border-slate-700 pb-2">Data Wali (Jika Ada)</span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 text-xs">
                            <div><span class="text-slate-400 dark:text-slate-400 font-bold block text-[10px]">Nama Wali:</span> <span id="det-guardian-name" class="font-semibold text-slate-800 dark:text-slate-100">-</span></div>
                            <div><span class="text-slate-400 dark:text-slate-400 font-bold block text-[10px]">NIK Wali:</span> <span id="det-guardian-nik" class="font-mono text-slate-800 dark:text-slate-100">-</span></div>
                            <div><span class="text-slate-400 dark:text-slate-400 font-bold block text-[10px]">No. HP:</span> <span id="det-guardian-phone" class="font-mono text-slate-800 dark:text-slate-100">-</span></div>
                            <div><span class="text-slate-400 dark:text-slate-400 font-bold block text-[10px]">Alamat:</span> <span id="det-guardian-addr" class="text-slate-800 dark:text-slate-200">-</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB PANE 2: BERKAS & DOKUMEN -->
            <div id="tab-pane-documents" class="cand-tab-pane hidden space-y-4">
                <div class="p-4.5 bg-emerald-50/70 dark:bg-emerald-950/40 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 flex items-center justify-between">
                    <div>
                        <h4 class="font-extrabold text-xs text-emerald-900 dark:text-emerald-200">Data Berkas & Dokumen Pendaftaran</h4>
                        <p class="text-[11px] text-emerald-700 dark:text-emerald-400 mt-0.5">Seluruh dokumen pendukung yang diunggah oleh wali murid saat melengkapi formulir.</p>
                    </div>
                    <span class="px-3 py-1 bg-white dark:bg-slate-800 text-emerald-800 dark:text-emerald-300 rounded-xl font-extrabold text-xs border border-emerald-200 dark:border-slate-700 shadow-sm">
                        Total 6 Berkas
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3.5">
                    <!-- Foto -->
                    <div id="det-photo-box" class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 hover:border-emerald-400 dark:hover:border-emerald-500 transition flex flex-col justify-between space-y-3 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="h-10 w-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="image" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 block">Pas Foto Murid</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-400">Formal Siswa</span>
                            </div>
                        </div>
                        <a id="det-photo-link" href="#" target="_blank" class="w-full text-center bg-brand-emerald hover-emerald text-white py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center justify-center gap-1.5">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Buka Foto
                        </a>
                    </div>

                    <!-- Akta -->
                    <div id="det-cert-box" class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 hover:border-emerald-400 dark:hover:border-emerald-500 transition flex flex-col justify-between space-y-3 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="h-10 w-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="file-digit" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 block">Akta Kelahiran</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-400">Scan Asli</span>
                            </div>
                        </div>
                        <a id="det-cert-link" href="#" target="_blank" class="w-full text-center bg-brand-emerald hover-emerald text-white py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center justify-center gap-1.5">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Buka Akta
                        </a>
                    </div>

                    <!-- KK -->
                    <div id="det-card-box" class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 hover:border-emerald-400 dark:hover:border-emerald-500 transition flex flex-col justify-between space-y-3 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="h-10 w-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="file-text" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 block">Kartu Keluarga</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-400">Scan Asli</span>
                            </div>
                        </div>
                        <a id="det-card-link" href="#" target="_blank" class="w-full text-center bg-brand-emerald hover-emerald text-white py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center justify-center gap-1.5">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Buka Kartu Keluarga
                        </a>
                    </div>

                    <!-- Ijazah -->
                    <div id="det-diploma-box" class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 hover:border-emerald-400 dark:hover:border-emerald-500 transition flex flex-col justify-between space-y-3 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="h-10 w-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="award" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 block">Ijazah Terakhir</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-400">Dokumen Kelulusan</span>
                            </div>
                        </div>
                        <a id="det-diploma-link" href="#" target="_blank" class="w-full text-center bg-brand-emerald hover-emerald text-white py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center justify-center gap-1.5">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Buka Ijazah
                        </a>
                    </div>

                    <!-- NISN -->
                    <div id="det-nisn-box" class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 hover:border-emerald-400 dark:hover:border-emerald-500 transition flex flex-col justify-between space-y-3 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="h-10 w-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="credit-card" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 block">NISN / Kartu Pelajar</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-400">Identitas Siswa</span>
                            </div>
                        </div>
                        <a id="det-nisn-link" href="#" target="_blank" class="w-full text-center bg-brand-emerald hover-emerald text-white py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center justify-center gap-1.5">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Buka Kartu Pelajar
                        </a>
                    </div>

                    <!-- Assesmen Khusus -->
                    <div id="det-special-box" class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 hover:border-emerald-400 dark:hover:border-emerald-500 transition flex flex-col justify-between space-y-3 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="h-10 w-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="file-heart" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 block">Assesmen Khusus</span>
                                <span class="text-[10px] text-slate-400 dark:text-slate-400">Kebutuhan Khusus</span>
                            </div>
                        </div>
                        <a id="det-special-link" href="#" target="_blank" class="w-full text-center bg-brand-emerald hover-emerald text-white py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center justify-center gap-1.5">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Buka Assesmen
                        </a>
                    </div>
                </div>
            </div>

            <!-- TAB PANE 3: KERINGANAN & KEBIJAKAN CICILAN -->
            <div id="tab-pane-installment" class="cand-tab-pane hidden space-y-5">
                <!-- Already Paid Notice (Shown only if candidate is already fully paid) -->
                <div id="modal_already_paid_notice" class="hidden p-4 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-700 text-emerald-800 dark:text-emerald-200 rounded-2xl text-xs flex items-center gap-3 shadow-sm font-semibold">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
                    <div>
                        <span class="font-extrabold block text-[13px] text-emerald-900 dark:text-emerald-100">Tagihan Calon Siswa Telah Lunas</span>
                        <span class="text-[11px] text-emerald-700 dark:text-emerald-300 font-normal">Seluruh kewajiban biaya masuk telah diselesaikan (Rp 0 sisa tagihan). Pengaturan cicilan sudah tidak berlaku lagi.</span>
                    </div>
                </div>

                <div class="p-4.5 bg-emerald-50/70 dark:bg-emerald-950/40 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 flex items-center justify-between">
                    <div>
                        <h4 class="font-extrabold text-xs text-emerald-950 dark:text-emerald-200 flex items-center gap-1.5">
                            <i data-lucide="sliders-horizontal" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i> Persetujuan Keringanan Biaya & Kebijakan Cicilan
                        </h4>
                        <p class="text-[11px] text-emerald-700 dark:text-emerald-400 mt-0.5">Tentukan potongan diskon khusus serta izin pembayaran bertahap (cicilan) untuk calon siswa ini.</p>
                    </div>
                    <span id="det-installment-status-badge" class="px-3 py-1 rounded-xl text-[10px] font-extrabold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-emerald-200 dark:border-slate-700 shadow-sm">
                        Standar (Lunas)
                    </span>
                </div>

                <!-- In-Modal Success Alert Banner -->
                <div id="modal_installment_success_alert" class="hidden p-4 bg-emerald-600 dark:bg-emerald-700 text-white rounded-2xl text-xs font-bold flex items-center justify-between shadow-md transition-all duration-300">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="check" class="w-4 h-4 text-white"></i>
                        </div>
                        <div>
                            <span class="block text-white font-black text-xs">Perubahan Berhasil Disimpan!</span>
                            <span class="block text-emerald-100 font-normal text-[11px] mt-0.5">Kebijakan diskon & cicilan untuk calon siswa ini telah berhasil diperbarui di sistem.</span>
                        </div>
                    </div>
                    <button type="button" onclick="document.getElementById('modal_installment_success_alert').classList.add('hidden')" class="text-emerald-200 hover:text-white p-1 rounded-lg hover:bg-white/10 transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form id="det-installment-form" method="POST" action="" hx-boost="false" onsubmit="window.saveInstallmentSettings(event); return false;" class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-5">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Potongan Diskon -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Potongan Biaya (Diskon / Keringanan)
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400 dark:text-slate-500 font-mono">Rp</span>
                                <input type="number" name="discount_amount" id="modal_discount_amount" min="0" step="50000"
                                    oninput="recalcModalInstallment()"
                                    class="w-full pl-10 pr-3 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold font-mono text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    placeholder="0">
                            </div>
                        </div>

                        <!-- Catatan Alasan -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Alasan / Catatan Persetujuan
                            </label>
                            <input type="text" name="discount_notes" id="modal_discount_notes"
                                class="w-full px-3 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                placeholder="Misal: Disetujui Yayasan (Anak Guru/Prestasi)">
                        </div>
                    </div>

                    <!-- Mode Cicilan -->
                    <div class="space-y-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            Kebijakan Pembayaran Masuk
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                            <label class="flex items-center gap-2.5 p-3.5 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-emerald-500 dark:hover:border-emerald-500 transition">
                                <input type="radio" name="installment_mode" value="none" id="mode_none" onchange="onModalModeChange()" class="text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                <div>
                                    <span class="font-bold text-slate-800 dark:text-slate-100 block text-xs">Wajib Lunas Sekaligus</span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-400">Tidak ada fasilitas cicilan</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-2.5 p-3.5 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-emerald-500 dark:hover:border-emerald-500 transition">
                                <input type="radio" name="installment_mode" value="all" id="mode_all" onchange="onModalModeChange()" class="text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                <div>
                                    <span class="font-bold text-slate-800 dark:text-slate-100 block text-xs">Cicil Semua (Global)</span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-400">Seluruh tagihan boleh dicicil</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-2.5 p-3.5 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-emerald-500 dark:hover:border-emerald-500 transition">
                                <input type="radio" name="installment_mode" value="selective" id="mode_selective" onchange="onModalModeChange()" class="text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                <div>
                                    <span class="font-bold text-slate-800 dark:text-slate-100 block text-xs">Cicil Komponen Tertentu</span>
                                    <span class="text-[10px] text-slate-400 dark:text-slate-400">Pilih komponen tertentu</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Selective Fees Checklist Container -->
                    <div id="modal_selective_fees_container" class="hidden space-y-3 p-5 bg-white dark:bg-slate-900 rounded-2xl border border-emerald-200 dark:border-emerald-800/60 shadow-sm">
                        <span class="text-[11px] font-extrabold text-emerald-800 dark:text-emerald-300 block mb-1">
                            Pilih Komponen Biaya Yang Boleh Dicicil:
                        </span>
                        <div id="modal_selective_fees_list" class="space-y-2">
                            <!-- Injected via JavaScript -->
                        </div>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 italic pt-1">
                            * Komponen yang tidak dicentang otomatis berstatus <strong>Wajib Lunas Awal</strong> (harus dilunasi pada pembayaran pertama).
                        </p>
                    </div>

                    <!-- Batas Minimal Sekali Cicil -->
                    <div id="modal_min_installment_box" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Batas Minimal Cicilan per Transaksi
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400 dark:text-slate-500 font-mono">Rp</span>
                                <input type="number" name="min_installment_amount" id="modal_min_installment_amount" min="0" step="50000"
                                    oninput="recalcModalInstallment()"
                                    class="w-full pl-10 pr-3 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold font-mono text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    placeholder="500000">
                            </div>
                        </div>
                    </div>

                    <!-- Live Calculation Summary Box -->
                    <div class="p-5 bg-emerald-50/80 dark:bg-slate-900/90 rounded-2xl border border-emerald-200 dark:border-slate-700 space-y-2.5 text-xs shadow-sm">
                        <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                            <span>Total Biaya Awal (Kotor):</span>
                            <span id="modal_calc_gross" class="font-mono font-bold text-slate-800 dark:text-slate-100">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between text-rose-600 dark:text-rose-400">
                            <span>Potongan Diskon:</span>
                            <span id="modal_calc_discount" class="font-mono font-bold">- Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between font-extrabold text-slate-900 dark:text-white border-t border-emerald-200 dark:border-slate-700 pt-2">
                            <span>Total Tagihan Bersih (Net):</span>
                            <span id="modal_calc_net" class="font-mono text-emerald-700 dark:text-emerald-400 font-black">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 border-t border-dashed border-emerald-200 dark:border-slate-700 pt-2">
                            <span>Minimal Pembayaran Transaksi Pertama:</span>
                            <span id="modal_calc_min_first" class="font-mono font-extrabold text-slate-800 dark:text-emerald-300">Rp 0</span>
                        </div>
                    </div>

                    <div class="flex justify-end pt-1">
                        <button type="button" id="btn-save-installment" onclick="window.saveInstallmentSettings(event)" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i> Simpan Kebijakan Keringanan & Cicilan
                        </button>
                    </div>
                </form>
            </div>

            <!-- TAB PANE 4: DATA & RIWAYAT PEMBAYARAN -->
            <div id="tab-pane-payments" class="cand-tab-pane hidden space-y-6">
                <!-- Top Summary Banner -->
                <div class="p-5 bg-gradient-to-r from-slate-900 via-slate-850 to-emerald-950 text-white rounded-2xl border border-emerald-800/40 shadow-sm relative overflow-hidden">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                        <div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-400 block mb-1">
                                Status Pelunasan Administrasi Masuk
                            </span>
                            <div class="flex items-center gap-2.5">
                                <h3 id="pay-status-headline" class="text-base font-black text-white">Belum Ada Pembayaran</h3>
                                <span id="pay-status-pill" class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    Belum Bayar
                                </span>
                            </div>
                        </div>
                        <div class="text-left sm:text-right">
                            <span class="text-[10px] text-slate-400 block">Total Bersih Tagihan:</span>
                            <span id="pay-total-net-display" class="text-lg font-black text-emerald-300 font-mono">Rp 0</span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-4 space-y-1.5 relative z-10">
                        <div class="flex justify-between text-[11px] font-bold">
                            <span class="text-slate-300">Progres Pembayaran:</span>
                            <span id="pay-progress-percent" class="text-emerald-300 font-mono">0%</span>
                        </div>
                        <div class="w-full h-2.5 bg-white/10 rounded-full overflow-hidden p-0.5">
                            <div id="pay-progress-bar" class="h-full bg-gradient-to-r from-emerald-400 to-teal-300 rounded-full transition-all duration-500" style="width: 0%;"></div>
                        </div>
                    </div>
                </div>

                <!-- 3 Metric Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Total Tagihan Awal</span>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span id="pay-gross-amount" class="text-sm font-bold font-mono text-slate-800 dark:text-slate-100">Rp 0</span>
                        </div>
                        <span id="pay-discount-sub" class="text-[10px] text-rose-500 font-medium block mt-0.5">Diskon: Rp 0</span>
                    </div>
                    <div class="p-4 bg-emerald-50/70 dark:bg-emerald-950/40 rounded-2xl border border-emerald-200 dark:border-emerald-800/50">
                        <span class="text-[10px] font-bold text-emerald-800 dark:text-emerald-300 uppercase block">Sudah Dibayar (Masuk)</span>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span id="pay-paid-amount" class="text-sm font-black font-mono text-emerald-700 dark:text-emerald-300">Rp 0</span>
                        </div>
                        <span id="pay-success-count-sub" class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium block mt-0.5">0 Transaksi Berhasil</span>
                    </div>
                    <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase block">Sisa Tagihan (Tanggungan)</span>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span id="pay-remaining-amount" class="text-sm font-bold font-mono text-slate-800 dark:text-slate-100">Rp 0</span>
                        </div>
                        <span id="pay-remaining-status-sub" class="text-[10px] text-slate-400 dark:text-slate-400 font-medium block mt-0.5">Wajib Dilunasi</span>
                    </div>
                </div>

                <!-- (Card Utama) TRANSAKSI PEMBAYARAN -->
                <div class="space-y-4 bg-white dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                        <h4 class="font-extrabold text-xs text-brand-emerald dark:text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                            <i data-lucide="receipt" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i> TRANSAKSI PEMBAYARAN
                        </h4>
                        <span id="pay-installment-policy-pill" class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                            Kebijakan: Non-Cicil
                        </span>
                    </div>

                    <!-- Dynamic Category Cards Container: (card 1) FORMULIR PENDAFTARAN, (card 2) BIAYA ADMINISTRASI, (card 3) BIAYA TAMBAHAN, dst. -->
                    <div id="pay-dynamic-categories-container" class="space-y-4 pt-1">
                        <!-- Injected via JavaScript -->
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="bg-slate-50 dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 px-6 py-4 flex justify-between items-center flex-shrink-0">
            <div>
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase block">Tanggal Masuk Formulir</span>
                <span id="det-created" class="text-xs font-semibold text-slate-600 dark:text-slate-300">20 Aug 2026, 03:00 WIB</span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closeDetailModal()" class="bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 px-5 py-2 rounded-xl text-xs font-bold transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    window.renderModalTimeline = function(cand) {
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
            activeStep = 5; // Administrasi
        } else if (status === 'completed') {
            activeStep = 6; // Kelulusan & Selesai
        }
        
        const steps = [
            { title: 'Pembayaran', desc: 'Biaya Pendaftaran' },
            { title: 'Formulir', desc: 'Biodata & Berkas' },
            { title: 'Verifikasi', desc: 'Review Panitia' },
            { title: 'Ta\'aruf', desc: 'Observasi & Tes' },
            { title: 'Persetujuan', desc: 'Tanda Tangan Biaya' },
            { title: 'Administrasi', desc: 'Pelunasan / Cicilan' },
            { title: 'Lulus & Selesai', desc: 'Resmi Diterima' }
        ];
        
        let html = `
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <h4 class="font-extrabold text-[11px] text-brand-emerald dark:text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="activity" class="w-3.5 h-3.5"></i>
                        Status Progres Pendaftaran Calon Siswa
                    </h4>
                </div>
                <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-4 md:gap-2 pt-1 pb-0.5">
                    <!-- Progress Line (Desktop) -->
                    <div class="hidden md:block absolute left-8 right-8 top-[15px] h-1 bg-slate-200 dark:bg-slate-800 -z-0">
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
                    iconHtml = `<span class="text-xs font-black text-slate-900">${index + 1}</span>`;
                }
            } else {
                state = 'pending';
                iconHtml = `<span class="text-xs font-bold text-slate-400 dark:text-slate-600">${index + 1}</span>`;
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
                        <span class="text-xs ${labelClass}">${step.title}</span>
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
    };

    window.openCandidateDetailModal = function(cand) {
        const setText = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.innerText = val !== undefined && val !== null && val !== '' ? val : '-';
        };

        setText('det-id-label', 'ID: ' + (cand.id_label || '-'));
        setText('det-status-chip', cand.status || '-');
        setText('modal-header-cand-name', cand.name || 'Detail Calon Siswa');
        setText('det-period', cand.period);
        setText('det-wave', cand.wave);
        setText('det-type', cand.type);
        
        // Render Timeline Progress
        const timelineContainer = document.getElementById('det-timeline-container');
        if (timelineContainer) {
            timelineContainer.innerHTML = renderModalTimeline(cand);
        }
        
        // Status Badge Style
        const statusEl = document.getElementById('det-status');
        if (statusEl) {
            statusEl.innerText = cand.status || '-';
            statusEl.className = "inline-block mt-0.5 px-2 py-0.5 rounded text-xs font-bold uppercase";
            if (cand.status === 'VERIFIED') {
                statusEl.classList.add('bg-green-50', 'text-green-700', 'border', 'border-green-200');
            } else if (cand.status === 'SUBMITTED') {
                statusEl.classList.add('bg-blue-50', 'text-blue-700', 'border', 'border-blue-200');
            } else {
                statusEl.classList.add('bg-slate-100', 'text-slate-600', 'border', 'border-slate-300');
            }
        }

        setText('det-name', cand.name);
        setText('det-nickname', cand.nickname);
        setText('det-nik', cand.nik);
        setText('det-family-card-no', cand.family_card_no);
        setText('det-gender', cand.gender);
        setText('det-birth', (cand.birth_place || '') + (cand.birth_date ? (', ' + cand.birth_date) : ''));
        setText('det-religion', cand.religion);
        setText('det-previous-school', cand.previous_school);
        setText('det-program', cand.class_program || 'Reguler');
        setText('det-extras', cand.extra_services);
        
        // Tempat Tinggal
        setText('det-address', cand.address);
        setText('det-house-no', cand.house_number);
        setText('det-rt-rw', (cand.rt !== '-' || cand.rw !== '-') ? (cand.rt + ' / ' + cand.rw) : '-');
        setText('det-kelurahan', cand.kelurahan);
        setText('det-kecamatan', cand.kecamatan);
        setText('det-city', cand.city);
        setText('det-province', cand.province);

        // Orang Tua
        setText('det-father-name', cand.father_name);
        setText('det-father-nik', cand.father_nik);
        setText('det-father-phone', cand.father_phone);
        setText('det-father-addr', cand.father_address);

        setText('det-mother-name', cand.mother_name);
        setText('det-mother-nik', cand.mother_nik);
        setText('det-mother-phone', cand.mother_phone);
        setText('det-mother-addr', cand.mother_address);

        // Wali
        setText('det-guardian-name', cand.guardian_name);
        setText('det-guardian-nik', cand.guardian_nik);
        setText('det-guardian-phone', cand.guardian_phone);
        setText('det-guardian-addr', cand.guardian_address);
        
        setText('det-created', cand.created_at_label);

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

        // Setup Installment & Discount Settings in Modal
        window.currentCandData = cand;
        const formEl = document.getElementById('det-installment-form');
        if (formEl && cand.save_installment_url) {
            formEl.action = cand.save_installment_url;
        }

        // Set values
        const discountInput = document.getElementById('modal_discount_amount');
        if (discountInput) discountInput.value = cand.discount_amount ? cand.discount_amount : '';
        const discountNotesInput = document.getElementById('modal_discount_notes');
        if (discountNotesInput) discountNotesInput.value = cand.discount_notes || '';
        const minInstallmentInput = document.getElementById('modal_min_installment_amount');
        if (minInstallmentInput) minInstallmentInput.value = cand.min_installment_amount ? cand.min_installment_amount : '';

        // Set radio mode
        const mode = cand.installment_mode || 'none';
        const modeRadio = document.querySelector(`input[name="installment_mode"][value="${mode}"]`);
        if (modeRadio) modeRadio.checked = true;

        // Render selective fee checklist
        const feeListContainer = document.getElementById('modal_selective_fees_list');
        if (feeListContainer) {
            feeListContainer.innerHTML = '';
            const feeItems = cand.fee_items || [];
            const allowedIds = cand.installment_allowed_fee_ids || [];

            if (feeItems.length === 0) {
                feeListContainer.innerHTML = '<span class="text-slate-400 italic text-[11px]">Belum ada rincian komponen biaya untuk unit ini.</span>';
            } else {
                feeItems.forEach((item, idx) => {
                    const feeId = item.id || item.name;
                    const feeName = item.name;
                    const feeAmt = Number(item.amount) || 0;
                    
                    // Check if selected
                    let isChecked = false;
                    if (allowedIds.includes(feeId) || allowedIds.includes(feeName) || allowedIds.some(x => String(x).toLowerCase() === feeName.toLowerCase())) {
                        isChecked = true;
                    }

                    const itemHtml = `
                        <label class="flex items-center justify-between p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700/60 cursor-pointer transition text-xs">
                            <div class="flex items-center gap-2.5">
                                <input type="checkbox" name="installment_allowed_fee_ids[]" value="${feeId}" 
                                    ${isChecked ? 'checked' : ''} 
                                    onchange="recalcModalInstallment()"
                                    class="selective-fee-cb rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                                <span class="font-bold text-slate-800 dark:text-slate-100">${feeName}</span>
                            </div>
                            <span class="font-mono font-bold text-slate-600 dark:text-slate-300 text-[11px]">Rp ${feeAmt.toLocaleString('id-ID')}</span>
                        </label>
                    `;
                    feeListContainer.insertAdjacentHTML('beforeend', itemHtml);
                });
            }
        }

        // Set header elements
        document.getElementById('modal-header-cand-name').innerText = cand.name || 'Detail Calon Siswa';
        document.getElementById('det-status-chip').innerText = cand.status || '-';
        
        // Update Tab 3 pill badge (Policy & Discount)
        const tabInstallmentBadge = document.getElementById('tab-installment-badge');
        if (tabInstallmentBadge) {
            if (cand.discount_amount > 0) {
                tabInstallmentBadge.innerText = 'Diskon Rp ' + (cand.discount_amount/1000).toLocaleString('id-ID') + 'k';
                tabInstallmentBadge.className = 'px-1.5 py-0.5 rounded-md text-[9px] font-extrabold bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60';
                tabInstallmentBadge.classList.remove('hidden');
            } else if (cand.installment_mode === 'all') {
                tabInstallmentBadge.innerText = 'Cicil Global';
                tabInstallmentBadge.className = 'px-1.5 py-0.5 rounded-md text-[9px] font-extrabold bg-blue-100 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60';
                tabInstallmentBadge.classList.remove('hidden');
            } else if (cand.installment_mode === 'selective') {
                tabInstallmentBadge.innerText = 'Cicil Selektif';
                tabInstallmentBadge.className = 'px-1.5 py-0.5 rounded-md text-[9px] font-extrabold bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60';
                tabInstallmentBadge.classList.remove('hidden');
            } else {
                tabInstallmentBadge.classList.add('hidden');
            }
        }

        // Show/hide already paid alert banner on Tab 3
        const alreadyPaidNotice = document.getElementById('modal_already_paid_notice');
        if (alreadyPaidNotice) {
            if (cand.total_paid > 0 && cand.remaining_balance <= 0) {
                alreadyPaidNotice.classList.remove('hidden');
            } else {
                alreadyPaidNotice.classList.add('hidden');
            }
        }

        // ==========================================
        // TAB 4: DATA & RIWAYAT PEMBAYARAN POPULATION
        // ==========================================
        window.updateModalPaymentTab(cand);

        // ==============================================================
        // RENDER KARTU DINAMIS PER KATEGORI DARI "TARIF & BIAYA"
        // ==============================================================
        const dynamicCatContainer = document.getElementById('pay-dynamic-categories-container');
        if (dynamicCatContainer) {
            dynamicCatContainer.innerHTML = '';
            const feeCategories = cand.fee_categories || [];

            if (feeCategories.length === 0) {
                dynamicCatContainer.innerHTML = '<p class="text-xs text-slate-400 italic py-4 text-center">Belum ada rincian kategori tarif & biaya untuk unit ini.</p>';
            } else {
                feeCategories.forEach((cat, idx) => {
                    let itemsHtml = '';

                    cat.items.forEach(item => {
                        let statusBadge = '';
                        if (item.is_paid) {
                            statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 flex items-center gap-1"><i data-lucide="check" class="w-3 h-3"></i> Lunas</span>';
                        } else if (item.status === 'pending') {
                            statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 flex items-center gap-1"><i data-lucide="clock" class="w-3 h-3"></i> Menunggu</span>';
                        } else {
                            statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60">Belum Dibayar</span>';
                        }

                        itemsHtml += `
                            <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 space-y-2.5 shadow-2xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-extrabold text-xs text-slate-800 dark:text-slate-100">
                                        ${item.name}
                                    </span>
                                    <div>
                                        ${statusBadge}
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs pt-1 border-t border-slate-100 dark:border-slate-800">
                                    <div>
                                        <span class="text-slate-400 font-bold block text-[10px] uppercase">No. Invoice</span>
                                        <span class="font-mono font-bold ${item.invoice_no !== '-' ? 'text-brand-emerald dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500'}">${item.invoice_no}</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 font-bold block text-[10px] uppercase">Metode Pembayaran</span>
                                        <span class="font-bold text-slate-700 dark:text-slate-200">${item.payment_method}</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 font-bold block text-[10px] uppercase">Nominal</span>
                                        <span class="font-mono font-extrabold text-slate-900 dark:text-white">Rp ${item.amount.toLocaleString('id-ID')}</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 font-bold block text-[10px] uppercase">Waktu Pembayaran</span>
                                        <span class="text-slate-600 dark:text-slate-300 font-medium">${item.paid_time}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    const catCard = `
                        <div class="p-4.5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/50 space-y-3 shadow-2xs">
                            <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-slate-700/80 pb-2.5">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-brand-emerald/10 dark:bg-emerald-950/70 text-brand-emerald dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                                        ${cat.category_name}
                                    </span>
                                </div>
                                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400">
                                    ${cat.items.length} Komponen Biaya
                                </span>
                            </div>
                            <div class="space-y-2.5">
                                ${itemsHtml}
                            </div>
                        </div>
                    `;

                    dynamicCatContainer.insertAdjacentHTML('beforeend', catCard);
                });

                if (!cand.has_agreed_statement) {
                    const noticeHtml = `
                        <div class="p-4 rounded-2xl border border-amber-200/80 dark:border-amber-900/60 bg-amber-50/70 dark:bg-amber-950/20 text-amber-800 dark:text-amber-300 flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center shrink-0 text-amber-600 dark:text-amber-400 mt-0.5">
                                <i data-lucide="info" class="w-4 h-4"></i>
                            </div>
                            <div class="text-xs space-y-0.5">
                                <div class="font-extrabold text-amber-900 dark:text-amber-200">Surat Pernyataan Belum Disetujui</div>
                                <p class="text-amber-700/90 dark:text-amber-400/90 leading-relaxed text-[11px]">
                                    Tagihan <strong>Biaya Administrasi</strong> dan <strong>Biaya Tambahan</strong> akan otomatis aktif dan diterbitkan setelah orang tua/wali calon siswa menandatangani / menyetujui Surat Pernyataan Kesanggupan Tata Tertib & Biaya Pendidikan.
                                </p>
                            </div>
                        </div>
                    `;
                    dynamicCatContainer.insertAdjacentHTML('beforeend', noticeHtml);
                    if (window.lucide) lucide.createIcons();
                }
            }
        }

        // Trigger mode UI change & calculation
        onModalModeChange();

        // Default to first tab
        switchCandidateTab('biodata');

        // Prevent background page scrolling
        document.body.style.overflow = 'hidden';

        document.getElementById('detailModal').classList.remove('hidden');
        
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    window.switchCandidateTab = function(tabId) {
        // Tab buttons
        document.querySelectorAll('.cand-tab-btn').forEach(btn => {
            btn.classList.remove('border-emerald-600', 'text-emerald-700', 'dark:text-emerald-300', 'bg-emerald-50/70', 'dark:bg-emerald-950/60', 'shadow-sm');
            btn.classList.add('border-transparent', 'text-slate-500', 'dark:text-slate-400', 'hover:text-slate-700', 'dark:hover:text-slate-200', 'hover:bg-slate-100', 'dark:hover:bg-slate-800');
        });
        
        // Tab panes
        document.querySelectorAll('.cand-tab-pane').forEach(pane => {
            pane.classList.add('hidden');
        });

        const activeBtn = document.getElementById('tab-btn-' + tabId);
        const activePane = document.getElementById('tab-pane-' + tabId);

        if (activeBtn) {
            activeBtn.classList.remove('border-transparent', 'text-slate-500', 'dark:text-slate-400', 'hover:text-slate-700', 'dark:hover:text-slate-200', 'hover:bg-slate-100', 'dark:hover:bg-slate-800');
            activeBtn.classList.add('border-emerald-600', 'text-emerald-700', 'dark:text-emerald-300', 'bg-emerald-50/70', 'dark:bg-emerald-950/60', 'shadow-sm');
        }
        if (activePane) {
            activePane.classList.remove('hidden');
        }

        // Scroll to top of modal body on tab switch
        const modalBody = document.getElementById('modalDetailBody');
        if (modalBody) {
            modalBody.scrollTop = 0;
        }
        
        if (window.lucide) {
            lucide.createIcons();
        }
    };

    window.onModalModeChange = function() {
        const modeRadio = document.querySelector('input[name="installment_mode"]:checked');
        const mode = modeRadio ? modeRadio.value : 'none';
        
        const selectiveBox = document.getElementById('modal_selective_fees_container');
        const minInstallmentBox = document.getElementById('modal_min_installment_box');
        const badge = document.getElementById('det-installment-status-badge');

        if (mode === 'none') {
            if (selectiveBox) selectiveBox.classList.add('hidden');
            if (minInstallmentBox) minInstallmentBox.classList.add('hidden');
            if (badge) {
                badge.innerText = 'Wajib Lunas';
                badge.className = 'px-3 py-1 rounded-xl text-[10px] font-extrabold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-emerald-200 dark:border-slate-700 shadow-sm';
            }
        } else if (mode === 'all') {
            if (selectiveBox) selectiveBox.classList.add('hidden');
            if (minInstallmentBox) minInstallmentBox.classList.remove('hidden');
            if (badge) {
                badge.innerText = 'Cicil Global';
                badge.className = 'px-3 py-1 rounded-xl text-[10px] font-extrabold bg-blue-50 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shadow-sm';
            }
        } else if (mode === 'selective') {
            if (selectiveBox) selectiveBox.classList.remove('hidden');
            if (minInstallmentBox) minInstallmentBox.classList.remove('hidden');
            if (badge) {
                badge.innerText = 'Cicil Selektif';
                badge.className = 'px-3 py-1 rounded-xl text-[10px] font-extrabold bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shadow-sm';
            }
        }

        window.recalcModalInstallment();
    };

    window.recalcModalInstallment = function() {
        const candData = window.currentCandData;
        if (!candData) return;

        const grossFee = Number(candData.gross_fee) || 0;
        const discountInput = Number(document.getElementById('modal_discount_amount').value) || 0;
        const netFee = Math.max(0, grossFee - discountInput);

        const modeRadio = document.querySelector('input[name="installment_mode"]:checked');
        const mode = modeRadio ? modeRadio.value : 'none';

        const minInstallmentInput = Number(document.getElementById('modal_min_installment_amount').value) || 0;

        let minFirstPayment = netFee;

        if (mode === 'none') {
            minFirstPayment = netFee;
        } else if (mode === 'all') {
            const minPart = minInstallmentInput > 0 ? minInstallmentInput : 500000;
            minFirstPayment = Math.min(netFee, Math.max(1, minPart));
        } else if (mode === 'selective') {
            // Sum unchecked mandatory fees
            const feeItems = candData.fee_items || [];
            const checkboxes = document.querySelectorAll('.selective-fee-cb');
            const checkedValues = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);

            let mandatoryTotal = 0;
            feeItems.forEach(item => {
                const feeId = String(item.id || item.name);
                if (!checkedValues.includes(feeId) && !checkedValues.includes(item.name)) {
                    mandatoryTotal += Number(item.amount) || 0;
                }
            });

            const installmentRemaining = Math.max(0, netFee - mandatoryTotal);
            const installmentPart = Math.min(installmentRemaining, minInstallmentInput);
            minFirstPayment = Math.min(netFee, mandatoryTotal + installmentPart);
        }

        document.getElementById('modal_calc_gross').innerText = 'Rp ' + grossFee.toLocaleString('id-ID');
        document.getElementById('modal_calc_discount').innerText = '- Rp ' + discountInput.toLocaleString('id-ID');
        document.getElementById('modal_calc_net').innerText = 'Rp ' + netFee.toLocaleString('id-ID');
        document.getElementById('modal_calc_min_first').innerText = 'Rp ' + minFirstPayment.toLocaleString('id-ID');
    };

    window.closeDetailModal = function() {
        const modal = document.getElementById('detailModal');
        if (modal) modal.classList.add('hidden');
        // Restore background page scrolling
        document.body.style.overflow = '';
    };

    // Modal click listener
    const modalEl = document.getElementById('detailModal');
    if (modalEl && !modalEl.dataset.clickBound) {
        modalEl.dataset.clickBound = 'true';
        modalEl.addEventListener('click', function(e) {
            if (e.target === this) {
                window.closeDetailModal();
            }
        });
    }

    // Escape key listener to close detail modal
    if (!window._histModalEscapeBound) {
        window._histModalEscapeBound = true;
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const detailModal = document.getElementById('detailModal');
                if (detailModal && !detailModal.classList.contains('hidden')) {
                    window.closeDetailModal();
                }
            }
        });
    }

    // Update Tab 4 (Data & Riwayat Pembayaran) dynamically
    window.updateModalPaymentTab = function(cand) {
        if (!cand) return;
        const totalGross = Number(cand.all_gross_fee !== undefined ? cand.all_gross_fee : (cand.gross_fee || 0));
        const discount = Number(cand.discount_amount || 0);
        const totalNet = Number(cand.all_net_fee !== undefined ? cand.all_net_fee : (cand.net_fee !== undefined ? cand.net_fee : Math.max(0, totalGross - discount)));
        const totalPaid = Number(cand.all_total_paid !== undefined ? cand.all_total_paid : (cand.total_paid || 0));
        const remaining = Number(cand.all_remaining_balance !== undefined ? cand.all_remaining_balance : (cand.remaining_balance !== undefined ? cand.remaining_balance : Math.max(0, totalNet - totalPaid)));
        const percent = totalNet > 0 ? Math.min(100, Math.round((totalPaid / totalNet) * 100)) : (totalPaid > 0 ? 100 : 0);
        const paidItemsCount = cand.all_paid_items_count !== undefined ? cand.all_paid_items_count : ((cand.payments || []).filter(p => p.status === 'success' || p.status === 'settled').length);

        // Fill Metrics
        const netEl = document.getElementById('pay-total-net-display');
        if (netEl) netEl.innerText = 'Rp ' + totalNet.toLocaleString('id-ID');
        const grossEl = document.getElementById('pay-gross-amount');
        if (grossEl) grossEl.innerText = 'Rp ' + totalGross.toLocaleString('id-ID');
        const discEl = document.getElementById('pay-discount-sub');
        if (discEl) discEl.innerText = discount > 0 ? ('Potongan Diskon: - Rp ' + discount.toLocaleString('id-ID')) : 'Diskon: Rp 0';
        const paidEl = document.getElementById('pay-paid-amount');
        if (paidEl) paidEl.innerText = 'Rp ' + totalPaid.toLocaleString('id-ID');
        const remEl = document.getElementById('pay-remaining-amount');
        if (remEl) remEl.innerText = 'Rp ' + remaining.toLocaleString('id-ID');
        const countEl = document.getElementById('pay-success-count-sub');
        if (countEl) countEl.innerText = paidItemsCount + ' Komponen Terbayar';

        // Progress Bar
        const progressBar = document.getElementById('pay-progress-bar');
        const progressPercent = document.getElementById('pay-progress-percent');
        if (progressPercent) progressPercent.innerText = percent + '%';
        if (progressBar) progressBar.style.width = percent + '%';

        // Status Headlines & Badges
        const headlineEl = document.getElementById('pay-status-headline');
        const statusPill = document.getElementById('pay-status-pill');
        const remStatusSub = document.getElementById('pay-remaining-status-sub');
        const tabPaymentsBadge = document.getElementById('tab-payments-badge');

        if (remaining <= 0 && totalPaid > 0) {
            if (headlineEl) headlineEl.innerText = 'Tagihan Lunas Sepenuhnya (100%)';
            if (statusPill) {
                statusPill.innerText = '100% LUNAS';
                statusPill.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/30';
            }
            if (remStatusSub) {
                remStatusSub.innerText = 'Tidak Ada Sisa Tanggungan';
                remStatusSub.className = 'text-[10px] text-emerald-600 dark:text-emerald-400 font-bold block mt-0.5';
            }
            if (tabPaymentsBadge) {
                tabPaymentsBadge.innerText = '100%';
                tabPaymentsBadge.className = 'px-1.5 py-0.5 rounded-md text-[9px] font-extrabold bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60';
            }
        } else if (totalPaid > 0) {
            if (headlineEl) headlineEl.innerText = 'Pembayaran Bertahap (' + percent + '%)';
            if (statusPill) {
                statusPill.innerText = percent + '% TERBAYAR';
                statusPill.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-blue-500/20 text-blue-300 border border-blue-500/30';
            }
            if (remStatusSub) {
                remStatusSub.innerText = 'Sisa: Rp ' + remaining.toLocaleString('id-ID');
                remStatusSub.className = 'text-[10px] text-amber-600 dark:text-amber-400 font-bold block mt-0.5';
            }
            if (tabPaymentsBadge) {
                tabPaymentsBadge.innerText = percent + '%';
                tabPaymentsBadge.className = 'px-1.5 py-0.5 rounded-md text-[9px] font-extrabold bg-blue-100 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60';
            }
        } else {
            if (headlineEl) headlineEl.innerText = 'Belum Ada Pembayaran Masuk';
            if (statusPill) {
                statusPill.innerText = '0% (BELUM BAYAR)';
                statusPill.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-amber-500/20 text-amber-300 border border-amber-500/30';
            }
            if (remStatusSub) {
                remStatusSub.innerText = 'Wajib Dilunasi';
                remStatusSub.className = 'text-[10px] text-slate-400 dark:text-slate-400 font-medium block mt-0.5';
            }
            if (tabPaymentsBadge) {
                tabPaymentsBadge.innerText = '0%';
                tabPaymentsBadge.className = 'px-1.5 py-0.5 rounded-md text-[9px] font-extrabold bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700';
            }
        }

        // Policy Pill
        const policyPill = document.getElementById('pay-installment-policy-pill');
        if (policyPill) {
            if (cand.installment_mode === 'all') {
                policyPill.innerText = 'Kebijakan: Cicil Semua Komponen';
            } else if (cand.installment_mode === 'selective') {
                policyPill.innerText = 'Kebijakan: Cicil Selektif';
            } else {
                policyPill.innerText = 'Kebijakan: Non-Cicil (Wajib Lunas Sekaligus)';
            }
        }

        // Show/hide already paid alert banner on Tab 3
        const alreadyPaidNotice = document.getElementById('modal_already_paid_notice');
        if (alreadyPaidNotice) {
            if (totalPaid > 0 && remaining <= 0) {
                alreadyPaidNotice.classList.remove('hidden');
            } else {
                alreadyPaidNotice.classList.add('hidden');
            }
        }
    };

    // Handle AJAX Save for Installment & Discount Settings
    window.saveInstallmentSettings = function(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const form = document.getElementById('det-installment-form');
        if (!form) return;

        const actionUrl = form.action || (window.currentCandData ? window.currentCandData.save_installment_url : '');
        
        if (!actionUrl) {
            alert('Gagal menyimpan: URL aksi tidak ditemukan.');
            return;
        }

        const formData = new FormData(form);
        const submitBtn = document.getElementById('btn-save-installment') || form.querySelector('button[type="submit"]');
        const originalContent = submitBtn ? submitBtn.innerHTML : '';
        
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menyimpan...</span>';
        }

        fetch(actionUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP error ' + res.status);
            return res.json();
        })
        .then(data => {
            if (data.success) {
                if (window.currentCandData && data.data) {
                    const gross = Number(window.currentCandData.all_gross_fee !== undefined ? window.currentCandData.all_gross_fee : window.currentCandData.gross_fee) || Number(data.data.gross_fee) || 0;
                    const disc = Number(data.data.discount_amount) || 0;
                    const net = Math.max(0, gross - disc);
                    const paid = Number(window.currentCandData.all_total_paid !== undefined ? window.currentCandData.all_total_paid : window.currentCandData.total_paid) || Number(data.data.total_paid) || 0;
                    const rem = Math.max(0, net - paid);

                    window.currentCandData.discount_amount = disc;
                    window.currentCandData.discount_notes = data.data.discount_notes;
                    window.currentCandData.installment_mode = data.data.installment_mode;
                    window.currentCandData.installment_allowed_fee_ids = data.data.installment_allowed_fee_ids;
                    window.currentCandData.min_installment_amount = data.data.min_installment_amount;
                    window.currentCandData.gross_fee = gross;
                    window.currentCandData.all_gross_fee = gross;
                    window.currentCandData.net_fee = net;
                    window.currentCandData.all_net_fee = net;
                    window.currentCandData.total_paid = paid;
                    window.currentCandData.all_total_paid = paid;
                    window.currentCandData.remaining_balance = rem;
                    window.currentCandData.all_remaining_balance = rem;
                }

                // 1. Live update Tab 3 pill badge
                const tabInstallmentBadge = document.getElementById('tab-installment-badge');
                if (tabInstallmentBadge && data.data) {
                    if (data.data.discount_amount > 0) {
                        tabInstallmentBadge.innerText = 'Diskon Rp ' + (data.data.discount_amount/1000).toLocaleString('id-ID') + 'k';
                        tabInstallmentBadge.className = 'px-1.5 py-0.5 rounded-md text-[9px] font-extrabold bg-rose-100 dark:bg-rose-950/70 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800/60';
                        tabInstallmentBadge.classList.remove('hidden');
                    } else if (data.data.installment_mode === 'all') {
                        tabInstallmentBadge.innerText = 'Cicil Global';
                        tabInstallmentBadge.className = 'px-1.5 py-0.5 rounded-md text-[9px] font-extrabold bg-blue-100 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60';
                        tabInstallmentBadge.classList.remove('hidden');
                    } else if (data.data.installment_mode === 'selective') {
                        tabInstallmentBadge.innerText = 'Cicil Selektif';
                        tabInstallmentBadge.className = 'px-1.5 py-0.5 rounded-md text-[9px] font-extrabold bg-emerald-100 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60';
                        tabInstallmentBadge.classList.remove('hidden');
                    } else {
                        tabInstallmentBadge.classList.add('hidden');
                    }
                }

                // 2. Live recalculate Tab 3 summary box & mode UI
                window.recalcModalInstallment();
                window.onModalModeChange();

                // 3. Live update Tab 4 Data & Riwayat Pembayaran metrics
                window.updateModalPaymentTab(window.currentCandData);

                // 4. Update the background table button onclick so next click uses updated data immediately
                if (data.data && data.data.id) {
                    const candBtn = document.getElementById('cand-btn-' + data.data.id);
                    if (candBtn && window.currentCandData) {
                        const updatedDataCopy = JSON.parse(JSON.stringify(window.currentCandData));
                        candBtn.onclick = function() {
                            window.openCandidateDetailModal(updatedDataCopy);
                        };
                    }
                }

                // 5. Show in-modal alert banner
                const modalAlert = document.getElementById('modal_installment_success_alert');
                if (modalAlert) {
                    modalAlert.classList.remove('hidden');
                    // Scroll to alert inside modal
                    const modalBody = document.getElementById('modalDetailBody');
                    if (modalBody) modalBody.scrollTo({ top: 0, behavior: 'smooth' });
                }

                // 6. Show prominent top-center floating toast
                const toast = document.createElement('div');
                toast.className = 'fixed top-8 left-1/2 -translate-x-1/2 z-[99999] bg-emerald-800 text-white px-6 py-3.5 rounded-2xl shadow-2xl flex items-center gap-3 border border-emerald-400/50 text-xs font-bold transition-all duration-300 transform -translate-y-4 opacity-0';
                toast.innerHTML = `
                    <div class="w-7 h-7 rounded-xl bg-emerald-500/40 flex items-center justify-center flex-shrink-0 text-emerald-200">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-200"></i>
                    </div>
                    <div>
                        <div class="font-extrabold text-white text-xs">Berhasil Disimpan!</div>
                        <div class="text-[11px] text-emerald-200 font-medium">${data.message || 'Pengaturan keringanan & cicilan berhasil diperbarui.'}</div>
                    </div>
                `;
                document.body.appendChild(toast);
                if (window.lucide) lucide.createIcons();

                // Trigger animation
                requestAnimationFrame(() => {
                    toast.classList.remove('-translate-y-4', 'opacity-0');
                    toast.classList.add('translate-y-0', 'opacity-100');
                });

                setTimeout(() => {
                    toast.classList.remove('translate-y-0', 'opacity-100');
                    toast.classList.add('-translate-y-4', 'opacity-0');
                    setTimeout(() => toast.remove(), 400);
                }, 3500);

                // 7. Temporary button success state
                if (submitBtn) {
                    submitBtn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
                    submitBtn.classList.add('bg-emerald-700');
                    submitBtn.innerHTML = '<span class="inline-flex items-center gap-1.5"><i data-lucide="check" class="w-4 h-4 text-white"></i> Tersimpan!</span>';
                    if (window.lucide) lucide.createIcons();
                }

                // 8. Trigger partial HTMX refresh on history table
                const filterForm = document.getElementById('historyFilterForm');
                if (filterForm && window.htmx) {
                    htmx.trigger(filterForm, 'submit');
                }
            } else {
                alert('Gagal: ' + (data.message || 'Terjadi kesalahan saat menyimpan'));
            }
        })
        .catch(err => {
            console.error('Save installment error:', err);
            alert('Gagal menyimpan: ' + err.message);
        })
        .finally(() => {
            setTimeout(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('bg-emerald-700');
                    submitBtn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
                    submitBtn.innerHTML = originalContent;
                    if (window.lucide) lucide.createIcons();
                }
            }, 1500);
        });
    };
})();
</script>
@endsection
