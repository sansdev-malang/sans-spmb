@extends('layouts.admin')

@section('title', 'Rekapitulasi Pendaftaran Calon Murid - Admin Panel')
@section('page_title', 'Rekap Pendaftaran')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-3">
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 dark:text-white">Rekapitulasi Pendaftaran Calon Murid</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Analisis alur konversi calon murid, matriks distribusi per jenjang unit, dan segmentasi pendaftar.</p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @if($selectedPeriod)
                <div class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-brand-emerald"></i>
                    <span>TA {{ $selectedPeriod->year }}</span>
                </div>
            @endif

            <button type="button" id="btn-export-reg-excel" onclick="exportRegistrationExcel(this)" class="h-9 px-3.5 bg-brand-emerald hover-emerald text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-2 cursor-pointer">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-200"></i>
                <span>Ekspor Excel</span>
            </button>
            <button type="button" id="btn-export-reg-pdf" onclick="exportRegistrationPdf(this)" class="h-9 px-3.5 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold shadow-2xs transition flex items-center gap-2 cursor-pointer">
                <i data-lucide="file-text" class="w-4 h-4 text-rose-500 dark:text-rose-400"></i>
                <span>Ekspor PDF</span>
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-4">
        <form id="registrationFilterForm" method="GET" action="{{ route('admin.reports.registrations') }}" class="flex flex-wrap items-center gap-3">
            <!-- Unit Filter -->
            <div class="flex items-center gap-2">
                <label for="filter_unit_id" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Unit:</label>
                <select name="unit_id" id="filter_unit_id" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 rounded-xl pl-3.5 pr-9 py-2 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                    <option value="">Semua Unit Sekolah</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}{{ !$u->is_active ? ' (Nonaktif)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Wave Filter -->
            <div class="flex items-center gap-2">
                <label for="filter_wave_id" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Gelombang:</label>
                <select name="wave_id" id="filter_wave_id" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 rounded-xl pl-3.5 pr-9 py-2 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                    <option value="">Semua Gelombang</option>
                    @foreach($waves as $w)
                        <option value="{{ $w->id }}" {{ request('wave_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}{{ !$w->is_active ? ' (Ditutup)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Type Filter -->
            <div class="flex items-center gap-2">
                <label for="filter_type_id" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Jalur:</label>
                <select name="type_id" id="filter_type_id" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 rounded-xl pl-3.5 pr-9 py-2 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                    <option value="">Semua Jalur</option>
                    @foreach($types as $t)
                        <option value="{{ $t->id }}" {{ request('type_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Class Program Filter -->
            <div class="flex items-center gap-2">
                <label for="filter_class_program_id" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Program:</label>
                <select name="class_program_id" id="filter_class_program_id" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 rounded-xl pl-3.5 pr-9 py-2 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                    <option value="">Semua Program Kelas</option>
                    @foreach($classPrograms as $cp)
                        <option value="{{ $cp->id }}" {{ request('class_program_id') == $cp->id ? 'selected' : '' }}>{{ $cp->name }}</option>
                    @endforeach
                </select>
            </div>

            @if(request()->anyFilled(['unit_id', 'wave_id', 'type_id', 'class_program_id', 'start_date', 'end_date']))
                <a href="{{ route('admin.reports.registrations') }}" class="h-9 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-semibold flex items-center gap-1.5 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Reset Filter
                </a>
            @endif
        </form>
    </div>

    <!-- 1. Interactive Visual Conversion Pipeline Stepper -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                    <i data-lucide="git-merge" class="w-4 h-4 text-brand-emerald"></i>
                    Alur Pipeline & Corong Konversi Penerimaan Murid (Conversion Funnel)
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Pantau rasio kelulusan dan perpindahan calon murid pada setiap tahapan SPMB.</p>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-bold text-slate-500">Konversi Final:</span>
                <span class="px-2.5 py-1 rounded-xl text-xs font-black bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300">
                    {{ $overallConversionRate }}%
                </span>
            </div>
        </div>

        <!-- Funnel Stages Flow Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 pt-2">
            <!-- Stage 1: Akun Terdaftar -->
            <div class="bg-blue-50 dark:bg-blue-800/60 p-4 rounded-xl border border-blue-100 dark:border-blue-700/60 relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">1. Akun Terdaftar</span>
                        <div class="w-6 h-6 rounded-md bg-blue-100 dark:bg-blue-950/80 text-blue-600 flex items-center justify-center">
                            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-slate-800 dark:text-white mt-2">{{ $totalRegistered }}</div>
                    <span class="text-[10px] text-slate-400 font-semibold block mt-0.5">100% Calon Murid</span>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-200/60 dark:border-slate-700 text-[10px] text-slate-500">
                    Draft: <span class="font-bold text-slate-700 dark:text-slate-300">{{ $totalDraft }} anak</span>
                </div>
            </div>

            <!-- Stage 2: Formulir Lengkap -->
            <div class="bg-sky-50 dark:bg-sky-800/60 p-4 rounded-xl border border-sky-100 dark:border-sky-700/60 relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-sky-600 dark:text-sky-400 uppercase tracking-wider">2. Formulir Terisi</span>
                        <div class="w-6 h-6 rounded-md bg-sky-100 dark:bg-sky-950/80 text-sky-600 flex items-center justify-center">
                            <i data-lucide="file-check" class="w-3.5 h-3.5"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-sky-600 dark:text-sky-400 mt-2">{{ $totalSubmitted + $totalVerified }}</div>
                    <span class="text-[10px] text-sky-600 font-bold block mt-0.5">{{ $rateDraftToSubmitted }}% dari akun</span>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-200/60 dark:border-slate-700 text-[10px] text-slate-500">
                    Menunggu Verif: <span class="font-bold text-amber-600">{{ $totalSubmitted }} anak</span>
                </div>
            </div>

            <!-- Stage 3: Berkas Valid -->
            <div class="bg-indigo-50 dark:bg-indigo-800/60 p-4 rounded-xl border border-indigo-100 dark:border-indigo-700/60 relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">3. Berkas Valid</span>
                        <div class="w-6 h-6 rounded-md bg-indigo-100 dark:bg-indigo-950/80 text-indigo-600 flex items-center justify-center">
                            <i data-lucide="check-square" class="w-3.5 h-3.5"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-2">{{ $totalVerified }}</div>
                    <span class="text-[10px] text-indigo-600 font-bold block mt-0.5">{{ $rateSubmittedToVerified }}% lolos berkas</span>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-200/60 dark:border-slate-700 text-[10px] text-slate-500">
                    Ditolak: <span class="font-bold text-rose-600">{{ $totalRejected }} anak</span>
                </div>
            </div>

            <!-- Stage 4: Lulus Observasi -->
            <div class="bg-amber-50 dark:bg-amber-800/60 p-4 rounded-xl border border-amber-100 dark:border-amber-700/60 relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">4. Lulus Ta'aruf</span>
                        <div class="w-6 h-6 rounded-md bg-amber-100 dark:bg-amber-950/80 text-amber-600 flex items-center justify-center">
                            <i data-lucide="calendar-check" class="w-3.5 h-3.5"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-2">{{ $totalTaarufCompleted }}</div>
                    <span class="text-[10px] text-amber-600 font-bold block mt-0.5">{{ $rateVerifiedToTaaruf }}% dari berkas</span>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-200/60 dark:border-slate-700 text-[10px] text-slate-500">
                    Terjadwal: <span class="font-bold text-blue-600">{{ $totalTaarufScheduled }} anak</span>
                </div>
            </div>

            <!-- Stage 5: Akad Santri -->
            <div class="bg-teal-50 dark:bg-teal-800/60 p-4 rounded-xl border border-teal-100 dark:border-teal-700/60 relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-teal-600 dark:text-teal-400 uppercase tracking-wider">5. Akad Santri</span>
                        <div class="w-6 h-6 rounded-md bg-teal-100 dark:bg-teal-950/80 text-teal-600 flex items-center justify-center">
                            <i data-lucide="file-signature" class="w-3.5 h-3.5"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-teal-600 dark:text-teal-400 mt-2">{{ $totalAgreementSigned }}</div>
                    <span class="text-[10px] text-teal-600 font-bold block mt-0.5">Komitmen Terisi</span>
                </div>
                <div class="mt-3 pt-2 border-t border-slate-200/60 dark:border-slate-700 text-[10px] text-slate-500">
                    Menunggu DSP: <span class="font-bold text-amber-600">{{ $pendingDspPaymentCount }} anak</span>
                </div>
            </div>

            <!-- Stage 6: Resmi Diterima -->
            <div class="bg-emerald-50/60 dark:bg-emerald-950/30 p-4 rounded-xl border border-emerald-200 dark:border-emerald-800/60 relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">6. Resmi Diterima</span>
                        <div class="w-6 h-6 rounded-md bg-emerald-200 dark:bg-emerald-900/80 text-emerald-800 flex items-center justify-center">
                            <i data-lucide="award" class="w-3.5 h-3.5"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2">{{ $totalCompleted }}</div>
                    <span class="text-[10px] text-emerald-700 dark:text-emerald-300 font-bold block mt-0.5">{{ $overallConversionRate }}% Konversi Final</span>
                </div>
                <div class="mt-3 pt-2 border-t border-emerald-200/60 dark:border-emerald-800/60 text-[10px] text-emerald-700 dark:text-emerald-300 font-semibold">
                    Lunas Masuk Sekolah
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Operational Actionable Bottlenecks Insight Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Perlu Verifikasi -->
        <a href="{{ route('admin.verification') }}" class="bg-white dark:bg-slate-900 p-4 rounded-2xl shadow-sm border border-amber-100 dark:border-amber-950/50 hover:border-amber-300 transition block group">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Perlu Verifikasi</span>
                <div class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-950/80 text-amber-600 flex items-center justify-center group-hover:scale-110 transition">
                    <i data-lucide="inbox" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-xl font-extrabold text-slate-800 dark:text-white mt-1">{{ $pendingVerificationCount }} <span class="text-xs font-normal text-slate-400">berkas</span></div>
            <span class="text-[10px] text-slate-400 font-medium mt-0.5 block group-hover:text-amber-600 transition">Klik untuk buka verifikasi &rarr;</span>
        </a>

        <!-- Card 2: Belum Terjadwal Taaruf -->
        <a href="{{ route('admin.taaruf') }}" class="bg-white dark:bg-slate-900 p-4 rounded-2xl shadow-sm border border-blue-100 dark:border-blue-950/50 hover:border-blue-300 transition block group">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">Belum Jadwal Observasi</span>
                <div class="w-7 h-7 rounded-lg bg-blue-50 dark:bg-blue-950/80 text-blue-600 flex items-center justify-center group-hover:scale-110 transition">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-xl font-extrabold text-slate-800 dark:text-white mt-1">{{ $pendingTaarufScheduleCount }} <span class="text-xs font-normal text-slate-400">anak</span></div>
            <span class="text-[10px] text-slate-400 font-medium mt-0.5 block group-hover:text-blue-600 transition">Atur jadwal observasi &rarr;</span>
        </a>

        <!-- Card 3: Menunggu Pelunasan DSP -->
        <a href="{{ route('admin.finance.reports', ['tab' => 'receivables']) }}" class="bg-white dark:bg-slate-900 p-4 rounded-2xl shadow-sm border border-purple-100 dark:border-purple-950/50 hover:border-purple-300 transition block group">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-purple-600 dark:text-purple-400 uppercase tracking-wider">Menunggu Pelunasan DSP</span>
                <div class="w-7 h-7 rounded-lg bg-purple-50 dark:bg-purple-950/80 text-purple-600 flex items-center justify-center group-hover:scale-110 transition">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-xl font-extrabold text-slate-800 dark:text-white mt-1">{{ $pendingDspPaymentCount }} <span class="text-xs font-normal text-slate-400">anak</span></div>
            <span class="text-[10px] text-slate-400 font-medium mt-0.5 block group-hover:text-purple-600 transition">Buka buku piutang &rarr;</span>
        </a>

        <!-- Card 4: Berkas Ditolak / Gagal -->
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl shadow-sm border border-rose-100 dark:border-rose-950/50">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Ditolak / Gagal</span>
                <div class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/80 text-rose-600 flex items-center justify-center">
                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-xl font-extrabold text-rose-600 dark:text-rose-400 mt-1">{{ $totalRejected }} <span class="text-xs font-normal text-slate-400">anak</span></div>
            <span class="text-[10px] text-slate-400 font-medium mt-0.5 block">Berkas tidak valid / gugur</span>
        </div>
    </div>

    <!-- 3. Matriks Rekapitulasi per Unit Sekolah & Rincian Jenjang Kelas -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                    <i data-lucide="building-2" class="w-4 h-4 text-brand-emerald"></i>
                    Matriks Distribusi & Konversi per Unit Sekolah & Jenjang Kelas
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Rincian komprehensif perkembangan pendaftaran dari akun terdaftar hingga resmi diterima di tiap kelas.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="toggleAllGrades()" id="btn-toggle-grades" class="h-8 px-3 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-200 transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="unfold-vertical" class="w-3.5 h-3.5"></i>
                    <span id="toggle-grades-text">Buka Rincian Kelas</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50 dark:bg-slate-800/50">
                        <th class="py-3 px-3">Unit Sekolah & Jenjang Kelas</th>
                        <th class="py-3 px-3 text-center">Pendaftar (Akun)</th>
                        <th class="py-3 px-3 text-center">Formulir Terisi</th>
                        <th class="py-3 px-3 text-center">Berkas Valid</th>
                        <th class="py-3 px-3 text-center">Lulus Ta'aruf</th>
                        <th class="py-3 px-3 text-center font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50/40 dark:bg-emerald-950/20">Resmi Diterima</th>
                        <th class="py-3 px-3 text-right">% Konversi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                    @forelse($unitStats as $us)
                        <!-- Unit Summary Row -->
                        <tr class="bg-slate-50/80 dark:bg-slate-800/60 font-bold hover:bg-slate-100/80 dark:hover:bg-slate-800 transition">
                            <td class="py-3 px-3 text-slate-900 dark:text-white">
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="toggleUnitGrades({{ $us['unit']->id }})" class="p-1 rounded hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer" title="Tampilkan/Sembunyikan Kelas">
                                        <i data-lucide="chevron-down" id="icon-unit-{{ $us['unit']->id }}" class="w-4 h-4 text-brand-emerald transition-transform"></i>
                                    </button>
                                    <span>{{ $us['unit']->name }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300">
                                        {{ count($us['grades']) }} Kelas
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-center font-bold text-slate-900 dark:text-white">{{ $us['registered'] }} anak</td>
                            <td class="py-3 px-3 text-center font-semibold text-sky-600">{{ $us['submitted'] + $us['verified'] }} anak</td>
                            <td class="py-3 px-3 text-center font-semibold text-indigo-600">{{ $us['verified'] }} anak</td>
                            <td class="py-3 px-3 text-center font-semibold text-amber-600">{{ $us['taaruf'] }} anak</td>
                            <td class="py-3 px-3 text-center font-black text-emerald-600 dark:text-emerald-400 bg-emerald-50/40 dark:bg-emerald-950/20">{{ $us['completed'] }} anak</td>
                            <td class="py-3 px-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-16 bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-brand-emerald h-full rounded-full" style="width: {{ min(100, $us['percentage']) }}%;"></div>
                                    </div>
                                    <span class="font-extrabold text-slate-900 dark:text-white">{{ $us['percentage'] }}%</span>
                                </div>
                            </td>
                        </tr>

                        <!-- Detailed Grades Child Rows -->
                        @foreach($us['grades'] as $gs)
                            <tr class="grade-row-unit-{{ $us['unit']->id }} hover:bg-slate-50 dark:hover:bg-slate-800/40 transition text-slate-600 dark:text-slate-400">
                                <td class="py-2.5 px-3 pl-10 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $gs['grade']->name }}</span>
                                    </div>
                                </td>
                                <td class="py-2.5 px-3 text-center font-medium">{{ $gs['registered'] }}</td>
                                <td class="py-2.5 px-3 text-center font-medium text-sky-600">{{ $gs['verified'] }}</td>
                                <td class="py-2.5 px-3 text-center font-medium text-indigo-600">{{ $gs['verified'] }}</td>
                                <td class="py-2.5 px-3 text-center font-medium text-amber-600">{{ $gs['taaruf'] }}</td>
                                <td class="py-2.5 px-3 text-center font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50/20 dark:bg-emerald-950/10">{{ $gs['completed'] }}</td>
                                <td class="py-2.5 px-3 text-right font-medium text-slate-500">{{ $gs['percentage'] }}%</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Tidak ada data unit tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-black text-slate-900 dark:text-white">
                        <td class="py-3 px-3 uppercase">TOTAL KESELURUHAN</td>
                        <td class="py-3 px-3 text-center font-black">{{ $totalRegistered }} anak</td>
                        <td class="py-3 px-3 text-center text-sky-600 font-black">{{ $totalSubmitted + $totalVerified }} anak</td>
                        <td class="py-3 px-3 text-center text-indigo-600 font-black">{{ $totalVerified }} anak</td>
                        <td class="py-3 px-3 text-center text-amber-600 font-black">{{ $totalTaarufCompleted }} anak</td>
                        <td class="py-3 px-3 text-center text-emerald-600 dark:text-emerald-400 bg-emerald-100/40 dark:bg-emerald-950/40 font-black">{{ $totalCompleted }} anak</td>
                        <td class="py-3 px-3 text-right text-emerald-600 dark:text-emerald-400 font-black">{{ $overallConversionRate }}%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- 4. Segmentations & Characteristics Grid (3 Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 1. Gender Composition -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
            <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-brand-emerald"></i>
                Komposisi Gender Calon Murid
            </h2>
            <div class="space-y-4 pt-1">
                <!-- Laki-laki -->
                <div>
                    <div class="flex justify-between text-xs font-bold mb-1.5">
                        <span class="text-sky-600 dark:text-sky-400 flex items-center gap-1.5">
                            <i data-lucide="user" class="w-3.5 h-3.5"></i> Laki-laki
                        </span>
                        <span class="text-slate-800 dark:text-slate-200">
                            {{ $maleCount }} anak ({{ $totalRegistered > 0 ? round(($maleCount / $totalRegistered) * 100, 1) : 0 }}%)
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                        <div class="bg-sky-500 h-full rounded-full transition-all duration-500" style="width: {{ $totalRegistered > 0 ? ($maleCount / $totalRegistered) * 100 : 0 }}%;"></div>
                    </div>
                </div>

                <!-- Perempuan -->
                <div>
                    <div class="flex justify-between text-xs font-bold mb-1.5">
                        <span class="text-pink-600 dark:text-pink-400 flex items-center gap-1.5">
                            <i data-lucide="user" class="w-3.5 h-3.5"></i> Perempuan
                        </span>
                        <span class="text-slate-800 dark:text-slate-200">
                            {{ $femaleCount }} anak ({{ $totalRegistered > 0 ? round(($femaleCount / $totalRegistered) * 100, 1) : 0 }}%)
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                        <div class="bg-pink-500 h-full rounded-full transition-all duration-500" style="width: {{ $totalRegistered > 0 ? ($femaleCount / $totalRegistered) * 100 : 0 }}%;"></div>
                    </div>
                </div>

                <!-- Belum Diisi -->
                @if($unknownGenderCount > 0)
                <div>
                    <div class="flex justify-between text-xs font-bold mb-1.5">
                        <span class="text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                            <i data-lucide="help-circle" class="w-3.5 h-3.5"></i> Belum Mengisi Profil
                        </span>
                        <span class="text-slate-500 dark:text-slate-400">
                            {{ $unknownGenderCount }} anak ({{ $totalRegistered > 0 ? round(($unknownGenderCount / $totalRegistered) * 100, 1) : 0 }}%)
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                        <div class="bg-slate-300 dark:bg-slate-700 h-full rounded-full transition-all duration-500" style="width: {{ $totalRegistered > 0 ? ($unknownGenderCount / $totalRegistered) * 100 : 0 }}%;"></div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- 2. Jalur Masuk Distribution -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
            <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="compass" class="w-4 h-4 text-brand-emerald"></i>
                Distribusi Jalur Pendaftaran
            </h2>
            <div class="space-y-3 pt-1">
                @forelse($typeStats as $ts)
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1">
                            <span class="text-slate-700 dark:text-slate-300">{{ $ts['type']->name }}</span>
                            <span class="text-slate-900 dark:text-white font-extrabold">{{ $ts['registered'] }} anak ({{ $ts['percentage'] }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-brand-emerald h-full rounded-full" style="width: {{ $ts['percentage'] }}%;"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-xs text-slate-400 text-center py-4">Belum ada data jalur pendaftaran.</div>
                @endforelse
            </div>
        </div>

        <!-- 3. Program Kelas & Gelombang -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
            <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="layers" class="w-4 h-4 text-brand-emerald"></i>
                Distribusi Gelombang & Program
            </h2>
            <div class="space-y-3 pt-1">
                @forelse($waveStats as $ws)
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $ws['wave']->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $ws['wave']->name }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-extrabold text-slate-900 dark:text-white">{{ $ws['registered'] }} anak</span>
                            <span class="text-[10px] text-slate-400 block">{{ $ws['percentage'] }}% porsi</span>
                        </div>
                    </div>
                @empty
                    <div class="text-xs text-slate-400 text-center py-4">Belum ada data gelombang.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    let gradesExpanded = false;

    window.toggleUnitGrades = function(unitId) {
        const rows = document.querySelectorAll('.grade-row-unit-' + unitId);
        const icon = document.getElementById('icon-unit-' + unitId);
        rows.forEach(row => {
            row.classList.toggle('hidden');
        });
        if (icon) {
            icon.classList.toggle('rotate-180');
        }
    };

    window.toggleAllGrades = function() {
        gradesExpanded = !gradesExpanded;
        const allGradeRows = document.querySelectorAll('[class*="grade-row-unit-"]');
        const allIcons = document.querySelectorAll('[id*="icon-unit-"]');
        const textBtn = document.getElementById('toggle-grades-text');

        allGradeRows.forEach(row => {
            if (gradesExpanded) {
                row.classList.remove('hidden');
            } else {
                row.classList.add('hidden');
            }
        });

        allIcons.forEach(icon => {
            if (gradesExpanded) {
                icon.classList.add('rotate-180');
            } else {
                icon.classList.remove('rotate-180');
            }
        });

        if (textBtn) {
            textBtn.innerText = gradesExpanded ? 'Tutup Rincian Kelas' : 'Buka Rincian Kelas';
        }
    };

    window.exportRegistrationExcel = function(btn) {
        const baseUrl = "{{ route('admin.reports.registrations.export') }}";
        const form = document.getElementById('registrationFilterForm');
        const params = new URLSearchParams(window.location.search);

        if (form) {
            const formData = new FormData(form);
            for (const [key, value] of formData.entries()) {
                if (value !== null && value !== undefined && value.toString().trim() !== '' && key !== '_token') {
                    params.set(key, value.toString().trim());
                } else {
                    params.delete(key);
                }
            }
        }

        if (btn) {
            const origHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>Mengekspor...</span>';
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                if (window.lucide) window.lucide.createIcons();
            }, 3000);
        }

        const queryString = params.toString();
        window.location.href = baseUrl + (queryString ? '?' + queryString : '');
    };

    window.exportRegistrationPdf = function(btn) {
        const baseUrl = "{{ route('admin.reports.registrations.export-pdf') }}";
        const form = document.getElementById('registrationFilterForm');
        const params = new URLSearchParams(window.location.search);

        if (form) {
            const formData = new FormData(form);
            for (const [key, value] of formData.entries()) {
                if (value !== null && value !== undefined && value.toString().trim() !== '' && key !== '_token') {
                    params.set(key, value.toString().trim());
                } else {
                    params.delete(key);
                }
            }
        }

        if (btn) {
            const origHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 text-slate-600 dark:text-slate-300 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>Mengekspor PDF...</span>';
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                if (window.lucide) window.lucide.createIcons();
            }, 3000);
        }

        const queryString = params.toString();
        window.location.href = baseUrl + (queryString ? '?' + queryString : '');
    };
</script>
@endsection
