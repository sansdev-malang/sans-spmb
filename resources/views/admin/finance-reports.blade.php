@extends('layouts.admin')

@section('title', 'Laporan Keuangan & Piutang SPMB - Admin Panel')
@section('page_title', 'Laporan Keuangan')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-2.5">
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 dark:text-white">Laporan & Rekapitulasi Keuangan SPMB</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Ringkasan arus kas masuk, realisasi pembayaran DSP, diskon, dan sisa piutang pendaftaran.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.finance.reports.export', request()->query()) }}" class="h-9 px-4 bg-brand-emerald hover-emerald text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-2">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Ekspor CSV / Excel</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-4">
        <form method="GET" action="{{ route('admin.finance.reports') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Unit:</label>
                <select name="unit_id" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                    <option value="">Semua Unit Sekolah</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Gelombang:</label>
                <div class="relative">
                    <select name="wave_id" onchange="this.form.submit()" class="appearance-none bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-left text-xs font-semibold text-slate-700 dark:text-slate-200 rounded-xl pl-3 pr-8 py-2 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                        <option value="">Semua Gelombang</option>
                        @foreach($waves as $w)
                            <option value="{{ $w->id }}" {{ request('wave_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if(request()->anyFilled(['unit_id', 'wave_id']))
                <a href="{{ route('admin.finance.reports') }}" class="h-8 px-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-semibold flex items-center gap-1 hover:bg-slate-200 transition">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Reset
                </a>
            @endif
        </form>
    </div>

    <!-- 4 Main Financial Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Tagihan Bruto</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-xl font-black text-slate-800 dark:text-white mt-2">
                Rp {{ number_format($totalGrossDSP, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-slate-400 font-semibold mt-1 block">Tarif Awal Sebelum Diskon</span>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Diskon / Keringanan</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <i data-lucide="tag" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-xl font-black text-amber-600 dark:text-amber-400 mt-2">
                Rp {{ number_format($totalDiscount, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-slate-400 font-semibold mt-1 block">Keringanan yang disetujui</span>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Kas Masuk</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i data-lucide="arrow-down-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-2">
                Rp {{ number_format($totalPaidDSP, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-emerald-600 dark:text-emerald-500 font-bold mt-1 block">+ Rp {{ number_format($totalFormFeePaid, 0, ',', '.') }} (Formulir)</span>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sisa Piutang / Tunggakan</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-2">
                Rp {{ number_format($totalRemainingDSP, 0, ',', '.') }}
            </div>
            <span class="text-[10px] text-rose-500 font-bold mt-1 block">{{ $sebagianCount + $belumBayarCount }} Siswa Belum Lunas</span>
        </div>
    </div>

    <!-- Breakdown Tables Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Breakdown per Unit -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
            <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="building-2" class="w-4 h-4 text-brand-emerald"></i>
                Realisasi Penerimaan per Unit Sekolah
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-2.5">Unit</th>
                            <th class="py-2.5">Siswa</th>
                            <th class="py-2.5">Kas Masuk</th>
                            <th class="py-2.5">Sisa Piutang</th>
                            <th class="py-2.5 text-right">% Capaian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                        @foreach($unitBreakdown as $ub)
                            <tr>
                                <td class="py-3 font-bold text-slate-900 dark:text-white">{{ $ub['unit']->name }}</td>
                                <td class="py-3">{{ $ub['count'] }} anak</td>
                                <td class="py-3 text-emerald-600 font-bold">Rp {{ number_format($ub['paid'], 0, ',', '.') }}</td>
                                <td class="py-3 text-rose-600 font-bold">Rp {{ number_format($ub['remaining'], 0, ',', '.') }}</td>
                                <td class="py-3 text-right font-extrabold text-slate-900 dark:text-white">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="w-16 bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                            <div class="bg-brand-emerald h-full rounded-full" style="width: {{ $ub['percentage'] }}%;"></div>
                                        </div>
                                        <span>{{ $ub['percentage'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Breakdown per Gelombang -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
            <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="layers" class="w-4 h-4 text-brand-emerald"></i>
                Realisasi Penerimaan per Gelombang
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-2.5">Gelombang</th>
                            <th class="py-2.5">Siswa</th>
                            <th class="py-2.5">Kas Masuk</th>
                            <th class="py-2.5">Sisa Piutang</th>
                            <th class="py-2.5 text-right">% Capaian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                        @foreach($waveBreakdown as $wb)
                            <tr>
                                <td class="py-3 font-bold text-slate-900 dark:text-white">{{ $wb['wave']->name }}</td>
                                <td class="py-3">{{ $wb['count'] }} anak</td>
                                <td class="py-3 text-emerald-600 font-bold">Rp {{ number_format($wb['paid'], 0, ',', '.') }}</td>
                                <td class="py-3 text-rose-600 font-bold">Rp {{ number_format($wb['remaining'], 0, ',', '.') }}</td>
                                <td class="py-3 text-right font-extrabold text-slate-900 dark:text-white">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="w-16 bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                            <div class="bg-brand-emerald h-full rounded-full" style="width: {{ $wb['percentage'] }}%;"></div>
                                        </div>
                                        <span>{{ $wb['percentage'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Receivables List -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="user-x" class="w-4 h-4 text-rose-600"></i>
                Daftar Siswa dengan Sisa Tagihan Terbanyak (Piutang)
            </h2>
            <a href="{{ route('admin.payments.data', ['status' => 'sebagian']) }}" class="text-xs font-bold text-brand-emerald hover:underline">
                Kelola Tagihan & Cicilan →
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-2.5">No. Daftar</th>
                        <th class="py-2.5">Nama Siswa</th>
                        <th class="py-2.5">Unit / Jenjang</th>
                        <th class="py-2.5">Wali / No. HP</th>
                        <th class="py-2.5">Tagihan Bersih</th>
                        <th class="py-2.5">Sudah Dibayar</th>
                        <th class="py-2.5 text-right">Sisa Piutang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                    @forelse($receivableCandidates->take(10) as $rc)
                        <tr>
                            <td class="py-3 font-mono font-bold text-slate-900 dark:text-white">SPMB-{{ str_pad($rc->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td class="py-3 font-bold text-slate-900 dark:text-white">{{ $rc->candidate_name }}</td>
                            <td class="py-3">{{ $rc->unit->name ?? '-' }} ({{ $rc->grade->name ?? '-' }})</td>
                            <td class="py-3">{{ $rc->father_name ?? $rc->mother_name ?? '-' }} ({{ $rc->parent_phone ?? '-' }})</td>
                            <td class="py-3">Rp {{ number_format($rc->net_fee, 0, ',', '.') }}</td>
                            <td class="py-3 text-emerald-600 font-semibold">Rp {{ number_format($rc->total_paid_final_fee, 0, ',', '.') }}</td>
                            <td class="py-3 text-right text-rose-600 font-black">Rp {{ number_format($rc->remaining_balance, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Tidak ada siswa dengan sisa piutang pada kriteria filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
