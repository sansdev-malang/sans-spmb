@extends('layouts.admin')

@section('title', 'Demografi & Asal Sekolah Calon Murid - Admin Panel')
@section('page_title', 'Demografi & Asal Sekolah')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div>
                    <h1 class="text-xl font-extrabold text-slate-800 dark:text-white">Demografi & Asal Sekolah Calon Murid</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Analisis persebaran sekolah asal, peta domisili pendaftar, dan efektivitas saluran informasi.</p>
                </div>
            </div>
        </div>

        @if($selectedPeriod)
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200">
                <i data-lucide="calendar" class="w-3.5 h-3.5 text-brand-emerald"></i>
                <span>Tahun Ajaran {{ $selectedPeriod->year }}</span>
            </div>
        @endif
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-4">
        <form method="GET" action="{{ route('admin.reports.demographics') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label for="demographics_unit_id" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Unit Sekolah:</label>
                <select name="unit_id" id="demographics_unit_id" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 rounded-xl pl-3.5 pr-9 py-2 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                    <option value="">Semua Unit Sekolah</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}{{ !$u->is_active ? ' (Nonaktif)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            @if(request()->filled('unit_id'))
                <a href="{{ route('admin.reports.demographics') }}" class="h-8 px-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-semibold flex items-center gap-1 hover:bg-slate-200 transition">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Reset
                </a>
            @endif
        </form>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-4 space-y-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Dianalisis</span>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl font-black text-slate-800 dark:text-white">{{ number_format($totalCandidates, 0, ',', '.') }}</span>
                <span class="text-xs font-bold text-slate-400">anak</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Pendaftar terdata di sistem</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-4 space-y-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Sekolah / Lembaga Asal</span>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ count($schoolCounts) }}</span>
                <span class="text-xs font-bold text-slate-400">sekolah</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">{{ $totalFilledSchools }} pendaftar mengisi</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-4 space-y-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kota / Kabupaten</span>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ count($cityCounts) }}</span>
                <span class="text-xs font-bold text-slate-400">wilayah</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Persebaran domisili kota</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-4 space-y-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kecamatan Lokal</span>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ count($districtCounts) }}</span>
                <span class="text-xs font-bold text-slate-400">kecamatan</span>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Jangkauan zona pendaftar</p>
        </div>
    </div>

    <!-- Main Content 2-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left: Top 15 Origin Schools -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                        <i data-lucide="school" class="w-4 h-4 text-brand-emerald"></i>
                        Top Asal Sekolah / Lembaga Asal
                    </h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Peringkat sekolah asal berdasarkan jumlah calon murid yang mendaftar.</p>
                </div>
                <span class="text-[10px] font-bold px-2 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald">
                    Top 15
                </span>
            </div>

            <div class="space-y-3">
                @php $rank = 1; @endphp
                @forelse($topSchools as $school => $cnt)
                    @php
                        $pct = $totalCandidates > 0 ? round(($cnt / $totalCandidates) * 100, 1) : 0;
                    @endphp
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-6 h-6 rounded-lg {{ $rank === 1 ? 'bg-amber-400 text-amber-950 font-black shadow-sm' : ($rank === 2 ? 'bg-slate-300 text-slate-900 font-black' : ($rank === 3 ? 'bg-amber-700 text-white font-bold' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold')) }} flex items-center justify-center text-xs shrink-0">
                                    {{ $rank++ }}
                                </span>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate" title="{{ $school }}">
                                    {{ $school }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs font-extrabold text-brand-emerald bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-md">
                                    {{ $cnt }} anak
                                </span>
                                <span class="text-[11px] font-bold text-slate-400 w-10 text-right">
                                    {{ $pct }}%
                                </span>
                            </div>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-brand-emerald h-full rounded-full transition-all duration-500" style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 text-slate-400">
                        <i data-lucide="school" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                        <p class="text-xs font-medium">Belum ada data sekolah asal yang diinput pendaftar.</p>
                    </div>
                @endforelse

                @if($unfilledSchools > 0)
                    <div class="pt-2 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 px-1">
                        <span>Belum mengisi sekolah asal / pendaftar baru:</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $unfilledSchools }} anak ({{ $totalCandidates > 0 ? round(($unfilledSchools / $totalCandidates) * 100, 1) : 0 }}%)</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right: Location & Marketing Channels -->
        <div class="space-y-6">
            <!-- 1. City / District Distribution -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-indigo-500"></i>
                            Persebaran Kota / Kabupaten Domisili
                        </h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Wilayah tempat tinggal calon murid berdasarkan isian alamat.</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($topCities as $city => $cnt)
                        @php
                            $pctCity = $totalCandidates > 0 ? round(($cnt / $totalCandidates) * 100, 1) : 0;
                        @endphp
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                    {{ $city }}
                                </span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $cnt }} anak <span class="text-slate-400 font-normal">({{ $pctCity }}%)</span></span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-indigo-500 h-full rounded-full transition-all duration-500" style="width: {{ $pctCity }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">Belum ada data kota/kabupaten yang tercatat.</p>
                    @endforelse

                    @if($unfilledCities > 0)
                        <div class="pt-2 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800">
                            <span>Belum melengkapi data kota:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ $unfilledCities }} anak</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 2. Local District / Kecamatan Breakdown -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                            <i data-lucide="compass" class="w-4 h-4 text-amber-500"></i>
                            Persebaran Kecamatan (Zona Lokal)
                        </h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Peta kecamatan tempat tinggal untuk analisis zonasi pendaftar.</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($topDistricts as $dist => $cnt)
                        @php
                            $pctDist = $totalCandidates > 0 ? round(($cnt / $totalCandidates) * 100, 1) : 0;
                        @endphp
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    Kec. {{ $dist }}
                                </span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $cnt }} anak <span class="text-slate-400 font-normal">({{ $pctDist }}%)</span></span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-amber-500 h-full rounded-full transition-all duration-500" style="width: {{ $pctDist }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">Belum ada data kecamatan yang tercatat.</p>
                    @endforelse

                    @if($unfilledDistricts > 0)
                        <div class="pt-2 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-800">
                            <span>Belum melengkapi data kecamatan:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ $unfilledDistricts }} anak</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 3. Marketing Channels / Saluran Informasi -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                            <i data-lucide="megaphone" class="w-4 h-4 text-emerald-500"></i>
                            Saluran Informasi Pendaftaran
                        </h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Sumber referensi bagaimana orang tua mengetahui info SPMB.</p>
                    </div>
                </div>

                @if($totalSurveyResponses > 0)
                    <div class="space-y-3">
                        @foreach($sourceCounts as $src => $cnt)
                            @php
                                $pctSrc = round(($cnt / $totalSurveyResponses) * 100, 1);
                            @endphp
                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-slate-700 dark:text-slate-200">{{ $src }}</span>
                                    <span class="font-extrabold text-emerald-600 dark:text-emerald-400">{{ $cnt }} responden ({{ $pctSrc }}%)</span>
                                </div>
                                <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-emerald-500 h-full rounded-full transition-all duration-500" style="width: {{ $pctSrc }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 rounded-xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-900/50 flex items-start gap-3 text-xs">
                        <i data-lucide="info" class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
                        <div class="space-y-1 text-slate-600 dark:text-slate-300">
                            <p class="font-bold text-amber-900 dark:text-amber-300">Belum ada data survei saluran informasi</p>
                            <p class="text-[11px] leading-relaxed">
                                Form pendaftaran saat ini belum menyertakan pertanyaan survei saluran info (*Medsos, Brosur, Rekomendasi Teman/Alumni, dll*). Anda dapat menambahkan field survei ini sewaktu-waktu melalui menu <a href="{{ route('admin.spmb-settings.form') }}" class="text-brand-emerald dark:text-emerald-400 font-bold underline hover:opacity-80">Setting Formulir</a> agar efektivitas promosi terekam otomatis.
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 4. Dedicated Referral Recap Section (Klaim Keringanan SPP Rp 300.000,- TA 2026/2027) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4 text-purple-600"></i>
                    Rekapitulasi Rekomendasi / Rujukan Murid (TA 2026/2027)
                </h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                    Daftar murid / wali murid aktif (TA 2026/2027) yang merekomendasikan calon murid baru.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-xl text-xs font-extrabold bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                    Total: {{ count($referralList) }} Rekomendasi
                </span>
            </div>
        </div>

        @if(count($referralList) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4 text-center w-10">No.</th>
                            <th class="py-3 px-4">Calon Murid Baru</th>
                            <th class="py-3 px-4">Unit Tujuan</th>
                            <th class="py-3 px-4">Murid Perujuk (TA 2026/2027)</th>
                            <th class="py-3 px-4">Kelas Murid Perujuk</th>
                            <th class="py-3 px-4">Kontak Ortu Perujuk</th>
                            <th class="py-3 px-4 text-center">Status Pendaftar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($referralList as $index => $ref)
                            @php
                                $cand = $ref['candidate'];
                                $cleanPhone = preg_replace('/[^0-9]/', '', $ref['parent_phone']);
                                if (str_starts_with($cleanPhone, '0')) {
                                    $cleanPhone = '62' . substr($cleanPhone, 1);
                                }
                                $waUrl = !empty($cleanPhone) ? "https://wa.me/{$cleanPhone}" : null;
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                <td class="py-3 px-4 text-center text-slate-400 font-bold">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800 dark:text-white">{{ $cand->candidate_name }}</div>
                                    <div class="font-mono text-[10px] text-slate-400">SANS-{{ substr($cand->period->year ?? '2026', 0, 4) }}-{{ str_pad($cand->id, 4, '0', STR_PAD_LEFT) }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $cand->unit->name ?? '-' }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ $cand->grade->name ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-extrabold text-purple-700 dark:text-purple-300">{{ $ref['student_name'] }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        {{ $ref['student_class'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if(!empty($ref['parent_phone']) && $ref['parent_phone'] !== '-')
                                        <a href="{{ $waUrl }}" target="_blank" class="inline-flex items-center gap-1 font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                                            <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                                            <span>{{ $ref['parent_phone'] }}</span>
                                        </a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold uppercase bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ str_replace('_', ' ', $cand->registration_status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 text-center py-6 text-xs text-slate-400">
                <i data-lucide="inbox" class="w-8 h-8 text-slate-300 dark:text-slate-600 mx-auto mb-2"></i>
                <p>Belum ada pendaftar yang memasukkan data referral atau rujukan dari murid aktif.</p>
            </div>
        @endif
    </div>
</div>
@endsection

