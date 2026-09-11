@extends('layouts.admin')

@section('title', 'Laporan Keuangan & Piutang SPMB - Admin Panel')
@section('page_title', 'Laporan Keuangan')

@section('content')
<div class="space-y-6">
    <!-- Printable Official Header (Hidden on screen, Visible on print) -->
    <div class="hidden print:block mb-6 border-b-2 border-slate-800 pb-4">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo" class="h-16 w-auto object-contain">
                @endif
                <div>
                    <h1 class="text-xl font-black uppercase tracking-wider text-slate-900">{{ $schoolName }}</h1>
                    <p class="text-xs font-bold text-slate-600">Laporan Rekapitulasi Keuangan & Piutang Penerimaan Peserta Didik Baru (SPMB)</p>
                    <p class="text-[11px] text-slate-500">Tahun Ajaran: {{ $selectedPeriod->year ?? 'Semua Periode' }} | Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Screen Header Card (Hidden on Print) -->
    <div class="print:hidden bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                <i data-lucide="line-chart" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 dark:text-white">Laporan & Rekapitulasi Keuangan SPMB</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Rekapitulasi kas bersih sekolah, rincian biaya admin gateway, target tagihan, dan buku piutang calon murid.</p>
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <button type="button" onclick="window.print()" class="h-9 px-4 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-2 cursor-pointer">
                <i data-lucide="printer" class="w-4 h-4 text-slate-500"></i>
                <span>Cetak / PDF</span>
            </button>
            <a href="{{ route('admin.finance.reports.export', request()->query()) }}" class="h-9 px-4 bg-brand-emerald hover-emerald text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-2 cursor-pointer">
                <i data-lucide="download" class="w-4 h-4 text-emerald-100"></i>
                <span>Ekspor CSV / Excel</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar (Hidden on Print) -->
    <div class="print:hidden bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-4">
        <form method="GET" action="{{ route('admin.finance.reports') }}" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="tab" value="{{ $activeTab }}">

            <!-- Unit Filter -->
            <div class="flex items-center gap-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Unit:</label>
                <select name="unit_id" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                    <option value="">Semua Unit Sekolah</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}{{ !$u->is_active ? ' (Nonaktif)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Wave Filter -->
            <div class="flex items-center gap-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Gelombang:</label>
                <select name="wave_id" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                    <option value="">Semua Gelombang</option>
                    @foreach($waves as $w)
                        <option value="{{ $w->id }}" {{ request('wave_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}{{ !$w->is_active ? ' (Ditutup)' : '' }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Type Filter -->
            <div class="flex items-center gap-2">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Jalur:</label>
                <select name="type_id" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                    <option value="">Semua Jalur</option>
                    @foreach($types as $t)
                        <option value="{{ $t->id }}" {{ request('type_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            @if(request()->anyFilled(['unit_id', 'wave_id', 'type_id', 'start_date', 'end_date', 'search', 'receivable_status']))
                <a href="{{ route('admin.finance.reports', ['tab' => $activeTab]) }}" class="h-9 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-semibold flex items-center gap-1.5 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Reset Filter
                </a>
            @endif
        </form>
    </div>

    <!-- 4 Main Executive Financial KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Kas Pokok Bersih Sekolah -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-emerald-100 dark:border-emerald-950/40 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Kas Bersih Sekolah</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-2">
                Rp {{ number_format($totalNetCashIn, 0, ',', '.') }}
            </div>
            <div class="mt-2.5 pt-2.5 border-t border-emerald-50 dark:border-slate-800 flex flex-col gap-1 text-[10px] text-slate-500 dark:text-slate-400">
                <div class="flex justify-between items-center">
                    <span>Pokok {{ $registrationFeeLabel }} ({{ $formFeeTrxCount }} trx):</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300">Rp {{ number_format($formFeeNet, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span>Pokok {{ $finalFeeLabel }} ({{ $dspFeeTrxCount }} trx):</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300">Rp {{ number_format($dspFeeNet, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-[9px] pt-1 text-slate-400 border-t border-slate-100 dark:border-slate-800">
                    <span>Total Mutasi Bruto:</span>
                    <span class="font-semibold text-slate-600 dark:text-slate-400">Rp {{ number_format($totalGrossRevenue, 0, ',', '.') }} <span class="text-amber-500 font-bold">(MDR: -Rp {{ number_format($totalAdminFee, 0, ',', '.') }})</span></span>
                </div>
            </div>
        </div>

        <!-- Card 2: Target Tagihan Bersih (DSP) -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Target Tagihan ({{ $finalFeeLabel }})</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-xl font-black text-slate-800 dark:text-white mt-2">
                Rp {{ number_format($totalNetDSP, 0, ',', '.') }}
            </div>
            <div class="mt-2.5 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex flex-col gap-1 text-[10px] text-slate-500 dark:text-slate-400">
                <div class="flex justify-between items-center">
                    <span>Tagihan Bruto Awal:</span>
                    <span class="font-semibold text-slate-600 dark:text-slate-400">Rp {{ number_format($totalGrossDSP, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-amber-600 dark:text-amber-400">
                    <span>Total Diskon Disetujui:</span>
                    <span class="font-bold">- Rp {{ number_format($totalDiscount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Rasio Capaian & Pelunasan -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kolektibilitas {{ $finalFeeLabel }}</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i data-lucide="percent" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-xl font-black text-indigo-600 dark:text-indigo-400">{{ $collectionRate }}%</span>
                <span class="text-[11px] text-slate-400 font-semibold">Tercapai</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden mt-2.5">
                <div class="bg-indigo-600 h-full rounded-full transition-all duration-500" style="width: {{ min(100, $collectionRate) }}%;"></div>
            </div>
            <div class="mt-2 flex items-center justify-between text-[10px] text-slate-500 font-medium">
                <span class="text-emerald-600 font-bold">{{ $lunasCount }} Lunas</span>
                <span class="text-amber-600 font-bold">{{ $sebagianCount }} Cicil</span>
                <span class="text-rose-500 font-bold">{{ $belumBayarCount }} Belum</span>
            </div>
        </div>

        <!-- Card 4: Sisa Piutang / Tunggakan -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-rose-100 dark:border-rose-950/40">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Sisa Piutang ({{ $finalFeeLabel }})</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-2">
                Rp {{ number_format($totalRemainingDSP, 0, ',', '.') }}
            </div>
            <div class="mt-2.5 pt-2.5 border-t border-rose-50 dark:border-slate-800 flex justify-between items-center text-[10px]">
                <span class="text-slate-500 dark:text-slate-400">Jumlah Murid Belum Lunas:</span>
                <span class="font-extrabold text-rose-600 dark:text-rose-400">{{ $sebagianCount + $belumBayarCount }} Murid</span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs (Hidden on Print) -->
    <div class="print:hidden flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-2">
        <a href="{{ route('admin.finance.reports', array_merge(request()->except(['page']), ['tab' => 'recap'])) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all duration-150 {{ $activeTab === 'recap' ? 'bg-brand-emerald text-white shadow-xs' : 'bg-white dark:bg-slate-850 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <i data-lucide="layout-grid" class="w-4 h-4"></i>
            <span>1. Rekapitulasi Realisasi & Unit</span>
        </a>

        <a href="{{ route('admin.finance.reports', array_merge(request()->except(['page']), ['tab' => 'cashflow'])) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all duration-150 {{ $activeTab === 'cashflow' ? 'bg-brand-emerald text-white shadow-xs' : 'bg-white dark:bg-slate-850 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <i data-lucide="credit-card" class="w-4 h-4"></i>
            <span>2. Arus Kas & Kanal Pembayaran</span>
        </a>

        <a href="{{ route('admin.finance.reports', array_merge(request()->except(['page']), ['tab' => 'receivables'])) }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all duration-150 {{ $activeTab === 'receivables' ? 'bg-brand-emerald text-white shadow-xs' : 'bg-white dark:bg-slate-850 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <i data-lucide="user-x" class="w-4 h-4"></i>
            <span>3. Buku Rekapitulasi Piutang Murid</span>
            <span class="px-1.5 py-0.2 rounded-md text-[10px] font-extrabold {{ $activeTab === 'receivables' ? 'bg-white/20 text-white' : 'bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400' }}">
                {{ $sebagianCount + $belumBayarCount }}
            </span>
        </a>
    </div>

    <!-- TAB 1: REKAPITULASI REALISASI PER UNIT & GELOMBANG -->
    @if($activeTab === 'recap' || request()->header('X-Print') || true)
    <div class="{{ $activeTab !== 'recap' ? 'hidden print:block' : '' }} space-y-6">
        <!-- Breakdown per Unit Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                        <i data-lucide="building-2" class="w-4 h-4 text-brand-emerald"></i>
                        Rekapitulasi Keuangan per Unit Sekolah
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Rincian perbandingan target tagihan, kas pokok bersih masuk sekolah, dan sisa piutang di tiap unit.</p>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50 dark:bg-slate-800/50">
                            <th class="py-3 px-3">Unit Sekolah</th>
                            <th class="py-3 px-3 text-center">Murid Tagihan</th>
                            <th class="py-3 px-3 text-right">Target {{ $finalFeeLabel }}</th>
                            <th class="py-3 px-3 text-right">Pokok {{ $finalFeeLabel }}</th>
                            <th class="py-3 px-3 text-right">Sisa Piutang</th>
                            <th class="py-3 px-3 text-right">Pokok {{ $registrationFeeLabel }}</th>
                            <th class="py-3 px-3 text-right font-black text-emerald-600 dark:text-emerald-400 bg-emerald-50/50 dark:bg-emerald-950/30">Kas Bersih Masuk</th>
                            <th class="py-3 px-3 text-right text-slate-400">Mutasi Bruto</th>
                            <th class="py-3 px-3 text-right">% Capaian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($unitBreakdown as $ub)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-3 font-bold text-slate-900 dark:text-white">
                                    {{ $ub['unit']->name }}
                                </td>
                                <td class="py-3 px-3 text-center font-semibold">{{ $ub['count'] }} anak</td>
                                <td class="py-3 px-3 text-right font-semibold">Rp {{ number_format($ub['net'], 0, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($ub['paid'], 0, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($ub['remaining'], 0, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right text-slate-500 font-semibold">Rp {{ number_format($ub['form_fee_net'], 0, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right font-black text-emerald-600 dark:text-emerald-400 bg-emerald-50/30 dark:bg-emerald-950/20">
                                    Rp {{ number_format($ub['total_net_cash'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right text-slate-500">
                                    Rp {{ number_format($ub['gross_mutasi'], 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right font-extrabold text-slate-900 dark:text-white">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="w-16 bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                            <div class="bg-brand-emerald h-full rounded-full" style="width: {{ min(100, $ub['percentage']) }}%;"></div>
                                        </div>
                                        <span class="text-xs">{{ $ub['percentage'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-6 text-center text-slate-400">Tidak ada data unit tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-black text-slate-900 dark:text-white">
                            <td class="py-3 px-3 uppercase">TOTAL KESELURUHAN</td>
                            <td class="py-3 px-3 text-center">{{ $totalDSPCandidatesCount }} anak</td>
                            <td class="py-3 px-3 text-right">Rp {{ number_format($totalNetDSP, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-emerald-600 dark:text-emerald-400">Rp {{ number_format($dspFeeNet, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-rose-600 dark:text-rose-400">Rp {{ number_format($totalRemainingDSP, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-slate-600 dark:text-slate-300">Rp {{ number_format($formFeeNet, 0, ',', '.') }}</td>
                            <td class="py-3 px-3 text-right text-emerald-600 dark:text-emerald-400 bg-emerald-100/40 dark:bg-emerald-950/40">
                                Rp {{ number_format($totalNetCashIn, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right text-slate-600 dark:text-slate-300">
                                Rp {{ number_format($totalGrossRevenue, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right">{{ $collectionRate }}%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Breakdown per Gelombang Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
            <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="layers" class="w-4 h-4 text-brand-emerald"></i>
                Realisasi Penerimaan per Gelombang Pendaftaran
            </h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50 dark:bg-slate-800/50">
                            <th class="py-3 px-3">Gelombang</th>
                            <th class="py-3 px-3 text-center">Murid Tagihan</th>
                            <th class="py-3 px-3 text-right">Target {{ $finalFeeLabel }}</th>
                            <th class="py-3 px-3 text-right">Pokok {{ $finalFeeLabel }} Masuk</th>
                            <th class="py-3 px-3 text-right">Sisa Piutang</th>
                            <th class="py-3 px-3 text-right">% Capaian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($waveBreakdown as $wb)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-3 font-bold text-slate-900 dark:text-white">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $wb['wave']->name }}</span>
                                        @if($wb['wave']->is_active)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">Aktif</span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">Ditutup</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-center font-semibold">{{ $wb['count'] }} anak</td>
                                <td class="py-3 px-3 text-right font-semibold">Rp {{ number_format($wb['net'], 0, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($wb['paid'], 0, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($wb['remaining'], 0, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right font-extrabold text-slate-900 dark:text-white">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="w-16 bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                            <div class="bg-brand-emerald h-full rounded-full" style="width: {{ min(100, $wb['percentage']) }}%;"></div>
                                        </div>
                                        <span>{{ $wb['percentage'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400">Tidak ada data gelombang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- TAB 2: ARUS KAS & KANAL PEMBAYARAN -->
    @if($activeTab === 'cashflow')
    <div class="space-y-6">
        <!-- Payment Channels Breakdown Grid -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                        <i data-lucide="credit-card" class="w-4 h-4 text-brand-emerald"></i>
                        Distribusi Penerimaan per Kanal / Saluran Pembayaran (Winpay & Gateway)
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Analisis total transaksi, volume mutasi bruto, biaya admin MDR, dan kas pokok bersih per metode pembayaran.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($channelStats as $ch)
                    @php
                        $chPercentage = $totalGrossRevenue > 0 ? round(($ch['gross'] / $totalGrossRevenue) * 100, 1) : 0;
                    @endphp
                    <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-xl border border-slate-100 dark:border-slate-700/60 flex flex-col justify-between">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                @if(!empty($ch['logo']))
                                    <div class="h-8 w-14 bg-white dark:bg-slate-900 rounded-lg p-1 border border-slate-200 dark:border-slate-700 flex items-center justify-center">
                                        <img src="{{ $ch['logo'] }}" alt="{{ $ch['name'] }}" class="h-6 w-auto max-w-full object-contain">
                                    </div>
                                @else
                                    <div class="h-8 w-8 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold text-xs">
                                        {{ substr($ch['name'], 0, 2) }}
                                    </div>
                                @endif
                                <div>
                                    <span class="text-xs font-bold text-slate-900 dark:text-white block">{{ $ch['name'] }}</span>
                                    <span class="text-[10px] text-slate-400 font-medium">{{ $ch['count'] }} Transaksi Sukses</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300">
                                {{ $chPercentage }}%
                            </span>
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-200/60 dark:border-slate-700 space-y-1 text-xs">
                            <div class="flex justify-between items-center text-slate-500 dark:text-slate-400 text-[11px]">
                                <span>Mutasi Bruto (Wali):</span>
                                <span class="font-bold text-slate-700 dark:text-slate-300">Rp {{ number_format($ch['gross'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-amber-600 dark:text-amber-400 text-[10px]">
                                <span>Biaya Admin Gateway (MDR):</span>
                                <span class="font-semibold">- Rp {{ number_format($ch['admin_fee'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center font-extrabold text-emerald-600 dark:text-emerald-400 pt-1 border-t border-dashed border-slate-200 dark:border-slate-700">
                                <span>Kas Pokok Bersih:</span>
                                <span>Rp {{ number_format($ch['net'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-8 text-center text-slate-400 text-xs">
                        Belum ada transaksi pembayaran yang berhasil masuk pada periode ini.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Transactions Audit Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
            <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="history" class="w-4 h-4 text-brand-emerald"></i>
                5 Transaksi Pembayaran Masuk Terakhir
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-2.5 px-3">Waktu Bayar</th>
                            <th class="py-2.5 px-3">No. Invoice</th>
                            <th class="py-2.5 px-3">Nama Murid / Unit</th>
                            <th class="py-2.5 px-3">Tipe</th>
                            <th class="py-2.5 px-3">Kanal</th>
                            <th class="py-2.5 px-3 text-right">Pokok Bersih</th>
                            <th class="py-2.5 px-3 text-right">Fee Admin</th>
                            <th class="py-2.5 px-3 text-right font-black text-slate-900 dark:text-white">Total Mutasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($recentTransactions as $rt)
                            @php
                                $rtNet = $rt->base_amount ?: ($rt->amount - ($rt->admin_fee ?: 0));
                            @endphp
                            <tr>
                                <td class="py-3 px-3 text-slate-500">{{ $rt->updated_at ? $rt->updated_at->translatedFormat('d M Y, H:i') : $rt->created_at->translatedFormat('d M Y, H:i') }}</td>
                                <td class="py-3 px-3 font-mono font-bold text-slate-900 dark:text-white">{{ $rt->invoice_number ?: ($rt->reference_id ?: 'TRX-' . $rt->id) }}</td>
                                <td class="py-3 px-3">
                                    <span class="font-bold text-slate-900 dark:text-white block">{{ $rt->registration->candidate_name ?? '-' }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $rt->registration->unit->name ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    @if($rt->payment_type === 'registration_fee')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300">{{ $registrationFeeLabel }}</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">{{ $finalFeeLabel }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 font-semibold">{{ $rt->channel_display_name }}</td>
                                <td class="py-3 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($rtNet, 0, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right text-amber-600 dark:text-amber-400 font-medium">Rp {{ number_format($rt->admin_fee ?: 0, 0, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right font-black text-slate-900 dark:text-white">Rp {{ number_format($rt->amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-6 text-center text-slate-400">Belum ada transaksi masuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- TAB 3: BUKU REKAPITULASI PIUTANG MURID -->
    @if($activeTab === 'receivables')
    <div class="space-y-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                        <i data-lucide="user-x" class="w-4 h-4 text-rose-600"></i>
                        Buku Rekapitulasi Piutang & Sisa Tagihan Murid
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Daftar calon murid yang belum melunasi biaya masuk (DSP) lengkap dengan kontak wali untuk penagihan.</p>
                </div>
                
                <!-- Quick Sub-status Filter -->
                <div class="flex items-center gap-2 text-xs font-bold">
                    <a href="{{ route('admin.finance.reports', array_merge(request()->except(['page', 'receivable_status']), ['tab' => 'receivables'])) }}" 
                       class="px-3 py-1.5 rounded-xl border text-xs font-bold transition {{ !request()->filled('receivable_status') ? 'bg-slate-800 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">
                        Semua Piutang ({{ $sebagianCount + $belumBayarCount }})
                    </a>
                    <a href="{{ route('admin.finance.reports', array_merge(request()->except(['page']), ['tab' => 'receivables', 'receivable_status' => 'unpaid'])) }}" 
                       class="px-3 py-1.5 rounded-xl border text-xs font-bold transition {{ request('receivable_status') === 'unpaid' ? 'bg-rose-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">
                        Belum Bayar ({{ $belumBayarCount }})
                    </a>
                    <a href="{{ route('admin.finance.reports', array_merge(request()->except(['page']), ['tab' => 'receivables', 'receivable_status' => 'partial'])) }}" 
                       class="px-3 py-1.5 rounded-xl border text-xs font-bold transition {{ request('receivable_status') === 'partial' ? 'bg-amber-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">
                        Cicilan Sebagian ({{ $sebagianCount }})
                    </a>
                </div>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('admin.finance.reports') }}" class="flex items-center gap-3 pt-2">
                <input type="hidden" name="tab" value="receivables">
                @if(request('unit_id')) <input type="hidden" name="unit_id" value="{{ request('unit_id') }}"> @endif
                @if(request('wave_id')) <input type="hidden" name="wave_id" value="{{ request('wave_id') }}"> @endif
                @if(request('receivable_status')) <input type="hidden" name="receivable_status" value="{{ request('receivable_status') }}"> @endif

                <div class="relative flex-grow">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari berdasarkan nama calon murid, NIK, ID registrasi, atau nomor HP wali..." class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-200 rounded-xl pl-10 pr-4 py-2.5 focus:outline-none focus:ring-1 focus:ring-brand-emerald">
                </div>
                <button type="submit" class="h-9 px-4 bg-brand-emerald hover-emerald text-white rounded-xl text-xs font-bold shadow-xs transition">
                    Cari
                </button>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50 dark:bg-slate-800/50">
                            <th class="py-3 px-3">No. ID SPMB</th>
                            <th class="py-3 px-3">Nama Calon Murid</th>
                            <th class="py-3 px-3">Unit / Jenjang</th>
                            <th class="py-3 px-3">Wali / No. WhatsApp</th>
                            <th class="py-3 px-3 text-right">Tagihan Bersih</th>
                            <th class="py-3 px-3 text-right">Sudah Dibayar</th>
                            <th class="py-3 px-3 text-right font-black text-rose-600 dark:text-rose-400">Sisa Piutang</th>
                            <th class="py-3 px-3 text-center">Aksi / Penagihan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                        @forelse($paginatedReceivables as $rc)
                            @php
                                $phoneClean = preg_replace('/[^0-9]/', '', $rc->parent_phone ?? '');
                                if (str_starts_with($phoneClean, '0')) {
                                    $phoneClean = '62' . substr($phoneClean, 1);
                                }
                                $waMsg = urlencode("Assalamu'alaikum Wr. Wb. Yth. Bapak/Ibu Wali dari {$rc->candidate_name} ({$rc->id_label}), kami dari Panitia SPMB {$schoolName} mengingatkan bahwa sisa tagihan administrasi biaya masuk ananda adalah sebesar Rp " . number_format($rc->remaining_balance, 0, ',', '.') . ". Mohon konfirmasi pelunasan melalui portal pendaftaran. Terima kasih.");
                            @endphp
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-3 font-mono font-bold text-slate-900 dark:text-white">{{ $rc->id_label }}</td>
                                <td class="py-3 px-3">
                                    <span class="font-bold text-slate-900 dark:text-white block">{{ $rc->candidate_name }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $rc->wave->name ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-semibold text-slate-800 dark:text-slate-200 block">{{ $rc->unit->name ?? '-' }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $rc->grade->name ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-medium text-slate-800 dark:text-slate-200 block">{{ $rc->father_name ?: ($rc->mother_name ?: ($rc->guardian_name ?: '-')) }}</span>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ $rc->parent_phone ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-3 text-right font-semibold">Rp {{ number_format($rc->net_fee, 0, ',', '.') }}</td>
                                <td class="py-3 px-3 text-right text-emerald-600 dark:text-emerald-400 font-bold">
                                    Rp {{ number_format($rc->total_paid_final_fee, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right text-rose-600 dark:text-rose-400 font-black">
                                    Rp {{ number_format($rc->remaining_balance, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if(!empty($phoneClean))
                                            <a href="https://wa.me/{{ $phoneClean }}?text={{ $waMsg }}" target="_blank" class="h-7 px-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-[10px] font-bold inline-flex items-center gap-1 shadow-2xs transition" title="Kirim Pengingat Tagihan via WhatsApp">
                                                <i data-lucide="message-circle" class="w-3 h-3"></i> WA
                                            </a>
                                        @endif
                                        <a href="{{ route('admin.payments.data', ['search' => $rc->candidate_name]) }}" class="h-7 px-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-[10px] font-bold inline-flex items-center gap-1 transition" title="Buka Detail Tagihan">
                                            <i data-lucide="external-link" class="w-3 h-3"></i> Detail
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400 text-xs">
                                    Tidak ada catatan sisa piutang yang ditemukan dengan filter saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($paginatedReceivables->hasPages())
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $paginatedReceivables->links() }}
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Printable Official Signature Area (Only visible on print) -->
    <div class="hidden print:block mt-12 pt-6">
        <div class="grid grid-cols-2 gap-8 text-center text-xs text-slate-800">
            <div>
                <p>Mengetahui,</p>
                <p class="font-bold">Ketua Panitia SPMB</p>
                <div class="h-20"></div>
                <p class="font-bold underline">( .................................................... )</p>
            </div>
            <div>
                <p>Malang, {{ now()->translatedFormat('d F Y') }}</p>
                <p class="font-bold">Bendahara Yayasan / SPMB</p>
                <div class="h-20"></div>
                <p class="font-bold underline">( .................................................... )</p>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    /* Hide layout chrome */
    #sidebar-left,
    header,
    nav,
    .sidebar-collapse-btn,
    .print\\:hidden {
        display: none !important;
    }
    
    body {
        background: white !important;
        color: black !important;
        font-size: 10pt;
    }
    
    main {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }

    table {
        border-collapse: collapse !important;
        width: 100% !important;
    }

    th, td {
        border: 1px solid #cbd5e1 !important;
        padding: 6px 8px !important;
        color: black !important;
    }

    .shadow-sm, .shadow-md, .shadow-xs {
        box-shadow: none !important;
    }

    .bg-slate-50, .bg-slate-900, .bg-white {
        background: transparent !important;
    }
}
</style>
@endsection
