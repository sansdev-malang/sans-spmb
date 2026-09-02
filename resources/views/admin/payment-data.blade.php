@extends('layouts.admin')

@section('title', 'Data Pembayaran & Billing Siswa - Admin Panel')
@section('page_title', 'Data Pembayaran')

@section('content')
<div class="space-y-6">
    <!-- Header Summary Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="wallet-cards" class="w-6 h-6 text-brand-emerald"></i>
                Data Pembayaran & Billing Calon Siswa
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Pusat pengelolaan tagihan pendaftaran, rincian biaya masuk, persetujuan keringanan/diskon, dan kebijakan cicilan calon siswa.
            </p>
        </div>
        <div class="flex gap-2 items-center">
            <button type="button" onclick="location.reload()" class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition" title="Refresh Data">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
            </button>
            <button type="button" onclick="showFeatureComingSoon('Ekspor Data Pembayaran (CSV)')" class="bg-brand-emerald hover-emerald text-white px-3.5 py-2.5 rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-2 cursor-pointer">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-200"></i>
                <span>Ekspor CSV</span>
                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-amber-400 text-amber-950 shadow-2xs">Soon</span>
            </button>
        </div>
    </div>

    <!-- Financial Stats Grid (5 Cards) -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <!-- Card 1: Candidate Count -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 flex items-center justify-between col-span-2 sm:col-span-1">
            <div>
                <span class="text-[11px] text-slate-400 font-bold block uppercase tracking-wider">Total Siswa</span>
                <span class="text-2xl font-black text-slate-800 dark:text-white block mt-1">{{ $stats['candidate_count'] }}</span>
                <span class="text-[10px] text-emerald-600 font-extrabold mt-0.5 block">{{ $stats['lunas_count'] }} Lunas</span>
            </div>
            <div class="h-10 w-10 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Card 2: Gross Revenue -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-[11px] text-slate-400 font-bold block uppercase tracking-wider">Tagihan (Bruto)</span>
                <span class="text-sm font-black text-slate-800 dark:text-slate-100 block mt-1">Rp {{ number_format($stats['gross_revenue'], 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400 font-semibold mt-0.5 block">Tarif Awal</span>
            </div>
            <div class="h-10 w-10 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="receipt" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Card 3: Total Discount Approved -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-[11px] text-slate-400 font-bold block uppercase tracking-wider">Total Diskon</span>
                <span class="text-sm font-black text-rose-600 dark:text-rose-400 block mt-1">- Rp {{ number_format($stats['discount_sum'], 0, ',', '.') }}</span>
                <span class="text-[10px] text-rose-500 font-semibold mt-0.5 block">Keringanan</span>
            </div>
            <div class="h-10 w-10 bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="tag" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Card 4: Realized Paid Revenue -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-[11px] text-slate-400 font-bold block uppercase tracking-wider">Kas Terbayar</span>
                <span class="text-sm font-black text-emerald-600 dark:text-emerald-400 block mt-1">Rp {{ number_format($stats['paid_sum'], 0, ',', '.') }}</span>
                <span class="text-[10px] text-emerald-600 font-semibold mt-0.5 block">Realisasi</span>
            </div>
            <div class="h-10 w-10 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="coins" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Card 5: Remaining Receivable -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-[11px] text-slate-400 font-bold block uppercase tracking-wider">Sisa Tunggakan</span>
                <span class="text-sm font-black text-amber-600 dark:text-amber-400 block mt-1">Rp {{ number_format($stats['remaining_sum'], 0, ',', '.') }}</span>
                <span class="text-[10px] text-amber-500 font-semibold mt-0.5 block">Belum Lunas</span>
            </div>
            <div class="h-10 w-10 bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Candidate Billing Table Card -->
    <div id="payments-card" class="bg-white dark:bg-slate-900 rounded-2xl shadow-md border border-slate-100 dark:border-slate-800 overflow-hidden">
        
        <!-- Search & Filter Form -->
        <form action="{{ route('admin.payments.data') }}" method="GET" class="bg-slate-50/60 dark:bg-slate-800/40 p-4 sm:p-5 border-b border-slate-200/80 dark:border-slate-700/80">
            @if(request('unit_id'))
                <input type="hidden" name="unit_id" value="{{ request('unit_id') }}">
            @endif

            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex flex-1 flex-col gap-3 md:flex-row md:items-center">
                    <div class="relative flex-1 min-w-0">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400 pointer-events-none">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama calon siswa, ID (SANS-2027-0092), No. WA orang tua..." 
                               class="w-full h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 pl-10 pr-20 text-xs text-slate-700 dark:text-slate-200 placeholder:text-slate-400 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-brand-emerald focus:border-transparent">

                        @if(request('search'))
                            <button type="button" onclick="this.form.querySelector('input[name=search]').value = ''; this.form.submit();" 
                                    class="absolute inset-y-0 right-12 flex items-center pr-1 text-slate-400 transition hover:text-slate-600"
                                    title="Hapus Pencarian">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        @endif

                        <button type="submit" class="absolute inset-y-1.5 right-1.5 flex items-center justify-center rounded-lg bg-brand-emerald px-3.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-600">
                            Cari
                        </button>
                    </div>

                    <div class="flex items-center gap-3">
                        <select name="per_page" onchange="this.form.submit()" class="h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 text-xs font-bold text-slate-600 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 Baris</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 Baris</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 Baris</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 Baris</option>
                        </select>

                        <button type="button" onclick="document.getElementById('adv-filters').classList.toggle('hidden')" 
                                class="inline-flex h-11 items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 text-xs font-bold text-slate-600 dark:text-slate-300 transition hover:bg-slate-50 dark:hover:bg-slate-800">
                            <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>
                            Filter Kebijakan
                        </button>
                    </div>
                </div>
            </div>

            <div id="adv-filters" class="{{ (request('discount_mode') || request('installment_mode')) ? '' : 'hidden' }} mt-4 border-t border-slate-200 dark:border-slate-700 pt-4 transition-all duration-300">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1">
                        <label class="block text-[10px] font-extrabold uppercase tracking-[0.18em] text-slate-400">Mode Diskon</label>
                        <select name="discount_mode" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-xs text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                            <option value="">Semua Mode Diskon</option>
                            <option value="none" {{ request('discount_mode') === 'none' ? 'selected' : '' }}>Tanpa Diskon (Standar)</option>
                            <option value="global" {{ request('discount_mode') === 'global' ? 'selected' : '' }}>Diskon Global (Total Tagihan)</option>
                            <option value="selective" {{ request('discount_mode') === 'selective' ? 'selected' : '' }}>Diskon Selektif (Per Komponen)</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[10px] font-extrabold uppercase tracking-[0.18em] text-slate-400">Kebijakan Cicilan</label>
                        <select name="installment_mode" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-xs text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                            <option value="">Semua Kebijakan Cicilan</option>
                            <option value="none" {{ request('installment_mode') === 'none' ? 'selected' : '' }}>Wajib Lunas Sekaligus</option>
                            <option value="all" {{ request('installment_mode') === 'all' ? 'selected' : '' }}>Cicil Semua (Global)</option>
                            <option value="selective" {{ request('installment_mode') === 'selective' ? 'selected' : '' }}>Cicil Komponen Tertentu (Selektif)</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2 border-t border-slate-200 dark:border-slate-700 pt-3">
                    <button type="button" onclick="this.form.querySelector('select[name=discount_mode]').value = ''; this.form.querySelector('select[name=installment_mode]').value = ''; this.form.submit();" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Reset Filter
                    </button>
                    <button type="submit" class="rounded-xl bg-brand-emerald px-5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-600">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>

        <!-- Unit Tabs for SuperAdmin -->
        @if(auth()->check() && auth()->user()->isSuperAdmin())
            <div class="border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 pb-2 pt-3 sm:px-5">
                <div class="flex items-center gap-2 overflow-x-auto whitespace-nowrap rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-100/80 dark:bg-slate-800 p-1.5">
                    <a href="{{ route('admin.payments.data', request()->except(['page', 'unit_id'])) }}" 
                       class="inline-flex shrink-0 items-center justify-center min-w-[140px] rounded-xl px-4 py-2.5 text-[11px] font-extrabold uppercase tracking-[0.08em] transition-all duration-200 {{ !request()->filled('unit_id') ? 'bg-gradient-to-r from-emerald-600 to-emerald-500 text-white shadow-sm' : 'bg-transparent text-slate-500 hover:bg-white dark:hover:bg-slate-700 hover:text-slate-700 dark:hover:text-slate-200' }}">
                        Semua Unit
                    </a>

                    @foreach(\App\Models\SpmbUnit::where('is_active', true)->get() as $unit)
                        <a href="{{ route('admin.payments.data', array_merge(request()->except(['page']), ['unit_id' => $unit->id])) }}" 
                           class="inline-flex shrink-0 items-center justify-center min-w-[140px] rounded-xl px-4 py-2.5 text-[11px] font-extrabold uppercase tracking-[0.08em] transition-all duration-200 {{ request('unit_id') == $unit->id ? 'bg-gradient-to-r from-emerald-600 to-emerald-500 text-white shadow-sm' : 'bg-transparent text-slate-500 hover:bg-white dark:hover:bg-slate-700 hover:text-slate-700 dark:hover:text-slate-200' }}">
                            {{ strtoupper($unit->name) }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Candidate Billing Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full border-separate border-spacing-0 text-left">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/80 text-[10px] font-extrabold uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">
                        <th class="border-b border-slate-200 dark:border-slate-700 px-4 py-3.5 text-center">No.</th>
                        <th class="border-b border-slate-200 dark:border-slate-700 px-4 py-3.5">ID & Calon Siswa</th>
                        <th class="border-b border-slate-200 dark:border-slate-700 px-4 py-3.5">Rincian Komponen Biaya</th>
                        <th class="border-b border-slate-200 dark:border-slate-700 px-4 py-3.5">Diskon / Keringanan</th>
                        <th class="border-b border-slate-200 dark:border-slate-700 px-4 py-3.5">Kebijakan Cicilan</th>
                        <th class="border-b border-slate-200 dark:border-slate-700 px-4 py-3.5">Tagihan & Realisasi</th>
                        <th class="border-b border-slate-200 dark:border-slate-700 px-4 py-3.5">Sisa Tagihan</th>
                        <th class="border-b border-slate-200 dark:border-slate-700 px-4 py-3.5 text-center">Status</th>
                        <th class="border-b border-slate-200 dark:border-slate-700 px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-xs text-slate-600 dark:text-slate-300">
                    @forelse($registrations as $cand)
                        @php
                            $feeDetails = $cand->getFinalFeeDetails();
                            $items = array_map(function($it) use ($cand) {
                                $it['paid_amount'] = $cand->getItemPaidAmount($it['name']);
                                $it['discount_amount'] = $cand->getItemDiscountAmount($it['name'], $it['id'] ?? null);
                                $it['net_amount'] = max(0, $it['amount'] - $it['discount_amount']);
                                $it['is_fully_paid'] = ($it['paid_amount'] >= $it['net_amount'] && $it['paid_amount'] > 0);
                                return $it;
                            }, $feeDetails['items'] ?? []);
                            $feeDetails['items'] = $items;
                            $gross = $cand->gross_fee;
                            $discount = $cand->total_discount;
                            $net = $cand->net_fee;
                            $paid = $cand->total_paid_final_fee;
                            $remaining = $cand->remaining_balance;
                            $percent = $net > 0 ? min(100, round(($paid / $net) * 100)) : 100;
                            $isLunas = ($remaining <= 0 && $net > 0 && $paid > 0);
                            $successfulPayments = $cand->payments->whereIn('status', ['success', 'settled'])->values();
                        @endphp
                        <tr id="cand-row-{{ $cand->id }}" class="group transition-colors duration-150 hover:bg-emerald-50/20 dark:hover:bg-slate-800/50">
                            <!-- No -->
                            <td class="border-b border-slate-200 dark:border-slate-700 px-4 py-4 text-center font-bold text-slate-400">
                                {{ ($registrations->currentPage() - 1) * $registrations->perPage() + $loop->iteration }}
                            </td>

                            <!-- ID & Calon Siswa -->
                            <td class="border-b border-slate-200 dark:border-slate-700 px-4 py-4">
                                <div class="flex items-start gap-2.5">
                                    <div class="h-8 w-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald flex items-center justify-center font-bold text-xs flex-shrink-0 mt-0.5 border border-emerald-200 dark:border-emerald-800/60">
                                        {{ strtoupper(substr($cand->candidate_name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="font-extrabold text-slate-800 dark:text-white block text-[13px] leading-snug">
                                            {{ $cand->candidate_name ?? 'Draft / Belum Isi' }}
                                        </span>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="font-mono text-[11px] font-bold text-emerald-700 dark:text-emerald-400 select-all">
                                                {{ $cand->id_label }}
                                            </span>
                                            <span class="text-slate-300 dark:text-slate-600">•</span>
                                            <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                                {{ $cand->unit->name ?? 'PAUD/TK' }}
                                                @if(!empty($cand->admission_level))
                                                    ({{ $cand->admission_level }})
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Rincian Komponen Biaya -->
                            <td class="border-b border-slate-200 dark:border-slate-700 px-4 py-4 max-w-[240px]">
                                <div class="space-y-1">
                                    @forelse($items as $it)
                                        @php
                                            $itDiscount = $cand->getItemDiscountAmount($it['name'], $it['id'] ?? null);
                                            $itNet = max(0, $it['amount'] - $itDiscount);
                                        @endphp
                                        <div class="flex items-center justify-between gap-2 text-[11px] font-medium bg-slate-50 dark:bg-slate-800/80 px-2 py-1 rounded-lg border border-slate-100 dark:border-slate-700">
                                            <span class="truncate font-semibold text-slate-700 dark:text-slate-300" title="{{ $it['name'] }}">
                                                {{ $it['name'] }}
                                            </span>
                                            <span class="font-mono text-slate-600 dark:text-slate-300 flex-shrink-0 font-bold">
                                                Rp {{ number_format($itNet, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @empty
                                        <span class="text-[11px] text-slate-400 italic">Belum ada komponen biaya</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Diskon / Keringanan -->
                            <td class="border-b border-slate-200 dark:border-slate-700 px-4 py-4">
                                @if($discount > 0)
                                    <div>
                                        <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-extrabold bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60">
                                            <i data-lucide="tag" class="w-3 h-3"></i>
                                            - Rp {{ number_format($discount, 0, ',', '.') }}
                                        </div>
                                        <span class="block text-[10px] text-slate-400 font-semibold mt-1">
                                            {{ $cand->discount_mode === 'selective' ? 'Diskon Selektif' : 'Diskon Global' }}
                                        </span>
                                        @if(!empty($cand->discount_notes))
                                            <span class="block text-[10px] text-slate-500 italic mt-0.5 truncate max-w-[140px]" title="{{ $cand->discount_notes }}">
                                                "{{ $cand->discount_notes }}"
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-400">
                                        <i data-lucide="minus" class="w-3 h-3"></i> Rp 0
                                    </span>
                                @endif
                            </td>

                            <!-- Kebijakan Cicilan -->
                            <td class="border-b border-slate-200 dark:border-slate-700 px-4 py-4">
                                @if($cand->installment_mode === 'all')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-extrabold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                                        <i data-lucide="layers" class="w-3 h-3"></i> Cicil Semua
                                    </span>
                                    <span class="block text-[10px] text-slate-400 font-semibold mt-1">
                                        Min: Rp {{ number_format($cand->min_installment_amount ?: 500000, 0, ',', '.') }}
                                    </span>
                                @elseif($cand->installment_mode === 'selective')
                                    @php
                                        $allowedCount = count($cand->installment_allowed_fee_ids ?? []);
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-extrabold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60">
                                        <i data-lucide="check-square" class="w-3 h-3"></i> Cicil Selektif ({{ $allowedCount }})
                                    </span>
                                    <span class="block text-[10px] text-slate-400 font-semibold mt-1">
                                        Min: Rp {{ number_format($cand->min_installment_amount ?: 0, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-extrabold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                        <i data-lucide="shield" class="w-3 h-3"></i> Wajib Lunas
                                    </span>
                                @endif
                            </td>

                            <!-- Tagihan & Realisasi -->
                            <td class="border-b border-slate-200 dark:border-slate-700 px-4 py-4">
                                <div>
                                    <div class="flex justify-between text-[11px] font-bold">
                                        <span class="text-slate-400">Total:</span>
                                        <span class="font-mono text-slate-800 dark:text-slate-200">Rp {{ number_format($net, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between text-[11px] font-bold mt-0.5">
                                        <span class="text-emerald-600">Terbayar:</span>
                                        <span class="font-mono text-emerald-600 dark:text-emerald-400">Rp {{ number_format($paid, 0, ',', '.') }}</span>
                                    </div>
                                    <!-- Progress Bar -->
                                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden mt-1.5">
                                        <div class="bg-brand-emerald h-full rounded-full transition-all duration-300" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Sisa Tagihan -->
                            <td class="border-b border-slate-200 dark:border-slate-700 px-4 py-4">
                                @if($remaining <= 0 && $net > 0)
                                    <span class="font-mono text-xs font-black text-emerald-600 dark:text-emerald-400">Rp 0</span>
                                    <span class="block text-[10px] text-emerald-500 font-bold mt-0.5">Lunas</span>
                                @else
                                    <span class="font-mono text-xs font-black text-amber-600 dark:text-amber-400">
                                        Rp {{ number_format($remaining, 0, ',', '.') }}
                                    </span>
                                    <span class="block text-[10px] text-slate-400 font-semibold mt-0.5">Tersisa</span>
                                @endif
                            </td>

                            <!-- Status Badge -->
                            <td class="border-b border-slate-200 dark:border-slate-700 px-4 py-4 text-center">
                                @if($isLunas)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 shadow-2xs">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i> LUNAS
                                    </span>
                                @elseif($paid > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-blue-100 dark:bg-blue-950/80 text-blue-800 dark:text-blue-300 border border-blue-300 dark:border-blue-700 shadow-2xs">
                                        <i data-lucide="clock-3" class="w-3 h-3"></i> DICICIL ({{ $percent }}%)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700 shadow-2xs">
                                        <i data-lucide="alert-circle" class="w-3 h-3"></i> BELUM BAYAR
                                    </span>
                                @endif
                            </td>

                            <!-- Action Buttons -->
                            <td class="border-b border-slate-200 dark:border-slate-700 px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Button Setting Diskon & Cicilan -->
                                    <button type="button" 
                                            onclick='openPolicyModal(@json($cand), @json($feeDetails))'
                                            class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-600 hover:text-white dark:hover:bg-emerald-600 border border-emerald-200 dark:border-emerald-800/60 transition shadow-2xs"
                                            title="Atur Keringanan & Kebijakan Cicilan">
                                        <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                                    </button>

                                    <!-- Button Riwayat Transaksi -->
                                    <button type="button" 
                                            onclick='openTransactionsModal(@json($cand), @json($successfulPayments))'
                                            class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-700 hover:text-white dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition shadow-2xs"
                                            title="Lihat Riwayat Transaksi & Kwitansi">
                                        <i data-lucide="receipt" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                        <i data-lucide="inbox" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-xs font-semibold">Tidak ada data pembayaran calon siswa yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($registrations->hasPages())
            <div class="border-t border-slate-200 dark:border-slate-700 px-6 py-4 bg-slate-50/50 dark:bg-slate-800/50">
                {{ $registrations->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: PENGATURAN KERINGANAN (DISKON) & KEBIJAKAN CICILAN CALON SISWA  -->
<!-- ========================================================================= -->
<div id="policy-modal" onclick="if(event.target === this) closePolicyModal()" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-3xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-emerald-800 to-emerald-950 p-6 text-white flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                <div class="h-11 w-11 rounded-2xl bg-white/10 flex items-center justify-center text-brand-yellow flex-shrink-0 border border-white/15">
                    <i data-lucide="sliders-horizontal" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 id="modal-cand-name" class="text-base font-extrabold text-white"></h3>
                    <div class="flex items-center gap-2 mt-0.5 text-xs text-emerald-200">
                        <span id="modal-cand-id" class="font-mono font-bold select-all"></span>
                        <span>•</span>
                        <span id="modal-cand-unit"></span>
                    </div>
                </div>
            </div>
            <button type="button" onclick="closePolicyModal()" class="h-9 w-9 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- In-Modal Success Alert -->
        <div id="modal_policy_success_alert" class="hidden p-4 bg-emerald-600 text-white text-xs font-bold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2.5">
                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                <span>Pengaturan keringanan & cicilan berhasil disimpan!</span>
            </div>
            <button type="button" onclick="document.getElementById('modal_policy_success_alert').classList.add('hidden')" class="text-emerald-200 hover:text-white">
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>

        <!-- In-Modal Fully Paid Alert / Lock Banner -->
        <div id="modal_already_paid_banner" class="hidden p-4 bg-emerald-50 dark:bg-emerald-950/80 border-b border-emerald-300 dark:border-emerald-700 flex items-center gap-3 text-xs font-semibold text-emerald-900 dark:text-emerald-200 shadow-inner">
            <div class="h-9 w-9 rounded-xl bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 flex items-center justify-center flex-shrink-0">
                <i data-lucide="shield-check" class="w-5 h-5 text-emerald-600"></i>
            </div>
            <div>
                <span class="font-extrabold block text-xs text-emerald-900 dark:text-emerald-100">Tagihan Calon Siswa Telah Lunas Sepenuhnya</span>
                <span class="text-[11px] text-emerald-700 dark:text-emerald-300 font-normal">Seluruh komponen biaya telah dibayar (Rp 0 sisa tagihan). Kebijakan biaya terkunci dan tidak dapat diubah lagi.</span>
            </div>
        </div>

        <!-- Form Body -->
        <form id="policy-form" onsubmit="submitPolicyForm(event)" class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
            @csrf
            <input type="hidden" id="modal_registration_id" name="registration_id" value="">

            <!-- SECTION 1: PERSETUJUAN DISKON / KERINGANAN -->
            <div class="space-y-3.5 p-5 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-white flex items-center gap-1.5">
                            <i data-lucide="tag" class="w-4 h-4 text-emerald-600 dark:text-emerald-400"></i>
                            1. Persetujuan Keringanan (Diskon)
                        </h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Tentukan jenis dan nominal potongan biaya untuk calon siswa ini.</p>
                    </div>
                </div>

                <!-- Radio Mode Diskon -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                    <label class="flex items-center gap-2.5 p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-emerald-500 transition">
                        <input type="radio" name="discount_mode" value="none" id="disc_mode_none" onchange="onDiscountModeChange()" class="text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                        <div>
                            <span class="font-bold text-slate-800 dark:text-slate-100 block text-xs">Tanpa Diskon</span>
                            <span class="text-[10px] text-slate-400">Tarif normal</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-emerald-500 transition">
                        <input type="radio" name="discount_mode" value="global" id="disc_mode_global" onchange="onDiscountModeChange()" class="text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                        <div>
                            <span class="font-bold text-slate-800 dark:text-slate-100 block text-xs">Diskon Global</span>
                            <span class="text-[10px] text-slate-400">Potongan total tagihan</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-emerald-500 transition">
                        <input type="radio" name="discount_mode" value="selective" id="disc_mode_selective" onchange="onDiscountModeChange()" class="text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                        <div>
                            <span class="font-bold text-slate-800 dark:text-slate-100 block text-xs">Diskon Selektif</span>
                            <span class="text-[10px] text-slate-400">Per komponen biaya</span>
                        </div>
                    </label>
                </div>

                <!-- Input Nominal Diskon Global -->
                <div id="modal_global_discount_container" class="hidden pt-2 border-t border-slate-200 dark:border-slate-700 space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">
                        Nominal Potongan Total (Global)
                    </label>
                    <div class="relative max-w-sm">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400 font-mono">Rp</span>
                        <input type="number" name="discount_amount" id="modal_discount_amount" min="0" step="10000"
                               class="w-full pl-10 pr-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold font-mono text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500"
                               placeholder="0" oninput="recalcLiveSimulation()">
                    </div>
                </div>

                <!-- Tabel Input Diskon Selektif Per Item -->
                <div id="modal_selective_discount_container" class="hidden pt-2 border-t border-slate-200 dark:border-slate-700 space-y-2">
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">
                        Atur Nominal Potongan per Komponen Biaya:
                    </label>
                    <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden bg-white dark:bg-slate-900">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100/70 dark:bg-slate-800/70 text-slate-500 text-[10px] font-extrabold uppercase">
                                <tr>
                                    <th class="py-2.5 px-3">Komponen Biaya</th>
                                    <th class="py-2.5 px-3 text-right">Tarif Pokok</th>
                                    <th class="py-2.5 px-3 text-right">Potongan (Rp)</th>
                                    <th class="py-2.5 px-3 text-right">Biaya Netto</th>
                                </tr>
                            </thead>
                            <tbody id="selective_discount_table_body" class="divide-y divide-slate-100 dark:divide-slate-800">
                                <!-- Injected via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Catatan / Alasan Keringanan -->
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700 space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">
                        Keterangan / Alasan Keringanan (Opsional)
                    </label>
                    <input type="text" name="discount_notes" id="modal_discount_notes"
                           class="w-full px-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500"
                           placeholder="Contoh: Diskon saudara kandung / Prestasi Tahfidz / Keringanan Yayasan">
                </div>
            </div>

            <!-- SECTION 2: KEBIJAKAN CICILAN -->
            <div class="space-y-3.5 p-5 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200 dark:border-slate-700">
                <div>
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-white flex items-center gap-1.5">
                        <i data-lucide="layers" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                        2. Kebijakan Cicilan Pembayaran
                    </h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Atur apakah siswa diperbolehkan mencicil dan komponen mana saja yang dapat dicicil.</p>
                </div>

                <!-- Radio Mode Cicilan -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                    <label class="flex items-center gap-2.5 p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-indigo-500 transition">
                        <input type="radio" name="installment_mode" value="none" id="inst_mode_none" onchange="onInstallmentModeChange()" class="text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <div>
                            <span class="font-bold text-slate-800 dark:text-slate-100 block text-xs">Harus Lunas</span>
                            <span class="text-[10px] text-slate-400">Tidak boleh mencicil</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-indigo-500 transition">
                        <input type="radio" name="installment_mode" value="all" id="inst_mode_all" onchange="onInstallmentModeChange()" class="text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <div>
                            <span class="font-bold text-slate-800 dark:text-slate-100 block text-xs">Cicil Bebas (Global)</span>
                            <span class="text-[10px] text-slate-400">Semua biaya bisa dicicil</span>
                        </div>
                    </label>
                    <label class="flex items-center gap-2.5 p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 cursor-pointer hover:border-indigo-500 transition">
                        <input type="radio" name="installment_mode" value="selective" id="inst_mode_selective" onchange="onInstallmentModeChange()" class="text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <div>
                            <span class="font-bold text-slate-800 dark:text-slate-100 block text-xs">Cicil Selektif</span>
                            <span class="text-[10px] text-slate-400">Pilih komponen tertentu</span>
                        </div>
                    </label>
                </div>

                <!-- Checklist Komponen yang Boleh Dicicil -->
                <div id="modal_selective_installment_container" class="hidden pt-2 border-t border-slate-200 dark:border-slate-700 space-y-2">
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">
                        Pilih Komponen Biaya yang Diperbolehkan Dicicil:
                    </label>
                    <div id="selective_installment_items_list" class="space-y-1.5 p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700">
                        <!-- Injected via JavaScript -->
                    </div>
                </div>

                <!-- Batas Minimal Cicilan -->
                <div id="modal_min_installment_container" class="hidden pt-2 border-t border-slate-200 dark:border-slate-700 space-y-1.5">
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">
                        Batas Minimal Cicilan per Transaksi
                    </label>
                    <div class="relative max-w-sm">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-xs font-bold text-slate-400 font-mono">Rp</span>
                        <input type="number" name="min_installment_amount" id="modal_min_installment_amount" min="0" step="50000"
                               class="w-full pl-10 pr-3 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold font-mono text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500"
                               placeholder="1000000">
                    </div>
                </div>
            </div>

            <!-- SECTION 3: LIVE SIMULATION CALCULATION CARD -->
            <div class="p-4.5 bg-emerald-950 text-white rounded-2xl space-y-3 shadow-md">
                <span class="text-[10px] font-black uppercase tracking-widest text-emerald-300 block">
                    ✨ Simulasi Ringkasan Keuangan Siswa
                </span>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div>
                        <span class="text-[10px] text-slate-300 block">Total Kotor (Bruto)</span>
                        <span id="sim_gross" class="font-mono font-black text-white text-xs">Rp 0</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-rose-300 block">Total Diskon</span>
                        <span id="sim_discount" class="font-mono font-black text-rose-300 text-xs">- Rp 0</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-emerald-300 block">Tagihan Bersih (Netto)</span>
                        <span id="sim_net" class="font-mono font-black text-emerald-300 text-xs">Rp 0</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-amber-300 block">Sisa Tagihan</span>
                        <span id="sim_remaining" class="font-mono font-black text-amber-300 text-xs">Rp 0</span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="closePolicyModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Batal
                </button>
                <button type="submit" id="btn_save_policy" class="px-6 py-2.5 rounded-xl bg-brand-emerald text-white text-xs font-black shadow-md hover:bg-emerald-600 transition flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Kebijakan Biaya</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: RIWAYAT TRANSAKSI & BUKTI KWITANSI PEMBAYARAN SISWA             -->
<!-- ========================================================================= -->
<div id="transactions-modal" onclick="if(event.target === this) closeTransactionsModal()" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200 dark:border-slate-800 shadow-2xl w-full max-w-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Header -->
        <div class="bg-slate-900 p-6 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center text-brand-yellow">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 id="tx-modal-name" class="text-base font-extrabold text-white"></h3>
                    <span id="tx-modal-id" class="text-xs text-slate-400 font-mono"></span>
                </div>
            </div>
            <button type="button" onclick="closeTransactionsModal()" class="h-8 w-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
            <div id="tx-modal-list" class="space-y-3">
                <!-- Injected via JavaScript -->
            </div>
        </div>

        <!-- Footer -->
        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-800 flex justify-end">
            <button type="button" onclick="closeTransactionsModal()" class="px-5 py-2 rounded-xl bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-xs font-bold text-slate-700 dark:text-slate-200 transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT CONTROLLERS FOR DATA PEMBAYARAN & MODALS                      -->
<!-- ========================================================================= -->
<script>
    let currentCandidate = null;
    let currentFeeDetails = null;

    // Global listener to close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePolicyModal();
            closeTransactionsModal();
        }
    });

    function openPolicyModal(cand, feeDetails) {
        document.body.classList.add('overflow-hidden');
        currentCandidate = cand;
        currentFeeDetails = feeDetails;

        const isFullyPaid = (cand.remaining_balance <= 0 && cand.total_paid_final_fee > 0);

        document.getElementById('modal-cand-name').textContent = cand.candidate_name || 'Calon Siswa';
        document.getElementById('modal-cand-id').textContent = cand.id_label || ('SANS-2027-' + String(cand.id).padStart(4, '0'));
        document.getElementById('modal-cand-unit').textContent = (cand.unit?.name || 'Unit') + (cand.admission_level ? ' (' + cand.admission_level + ')' : '');
        document.getElementById('modal_registration_id').value = cand.id;

        // Fully Paid Notice & Button Lock
        document.getElementById('modal_already_paid_banner').classList.toggle('hidden', !isFullyPaid);
        document.getElementById('btn_save_policy').classList.toggle('hidden', isFullyPaid);

        // 1. Setup Diskon Mode
        const discMode = cand.discount_mode || (cand.discount_amount > 0 ? 'global' : 'none');
        if (discMode === 'selective') {
            document.getElementById('disc_mode_selective').checked = true;
        } else if (discMode === 'global') {
            document.getElementById('disc_mode_global').checked = true;
        } else {
            document.getElementById('disc_mode_none').checked = true;
        }
        document.getElementById('modal_discount_amount').value = cand.discount_amount || 0;
        document.getElementById('modal_discount_notes').value = cand.discount_notes || '';

        // If candidate is fully paid, disable all discount controls
        document.getElementById('disc_mode_none').disabled = isFullyPaid;
        document.getElementById('disc_mode_global').disabled = isFullyPaid;
        document.getElementById('disc_mode_selective').disabled = isFullyPaid;
        document.getElementById('modal_discount_amount').disabled = isFullyPaid;
        document.getElementById('modal_discount_notes').disabled = isFullyPaid;

        // 2. Setup Cicilan Mode
        const instMode = cand.installment_mode || 'none';
        if (instMode === 'all') {
            document.getElementById('inst_mode_all').checked = true;
        } else if (instMode === 'selective') {
            document.getElementById('inst_mode_selective').checked = true;
        } else {
            document.getElementById('inst_mode_none').checked = true;
        }
        document.getElementById('modal_min_installment_amount').value = cand.min_installment_amount || 1000000;

        document.getElementById('inst_mode_none').disabled = isFullyPaid;
        document.getElementById('inst_mode_all').disabled = isFullyPaid;
        document.getElementById('inst_mode_selective').disabled = isFullyPaid;
        document.getElementById('modal_min_installment_amount').disabled = isFullyPaid;

        // 3. Render Selective Discount Table & Selective Installment Checklist
        renderSelectiveDiscountItems(feeDetails.items || [], cand.item_discounts || {}, isFullyPaid);
        renderSelectiveInstallmentItems(feeDetails.items || [], cand.installment_allowed_fee_ids || [], isFullyPaid);

        // 4. Update visibility
        onDiscountModeChange();
        onInstallmentModeChange();
        recalcLiveSimulation();

        // 5. Open Modal
        document.getElementById('policy-modal').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function closePolicyModal() {
        document.getElementById('policy-modal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function onDiscountModeChange() {
        const isSelective = document.getElementById('disc_mode_selective').checked;
        const isGlobal = document.getElementById('disc_mode_global').checked;

        document.getElementById('modal_selective_discount_container').classList.toggle('hidden', !isSelective);
        document.getElementById('modal_global_discount_container').classList.toggle('hidden', !isGlobal);
        recalcLiveSimulation();
    }

    function onInstallmentModeChange() {
        const isSelective = document.getElementById('inst_mode_selective').checked;
        const isAll = document.getElementById('inst_mode_all').checked;

        document.getElementById('modal_selective_installment_container').classList.toggle('hidden', !isSelective);
        document.getElementById('modal_min_installment_container').classList.toggle('hidden', (!isSelective && !isAll));
    }

    function renderSelectiveDiscountItems(items, itemDiscounts, isFullyPaid) {
        const tbody = document.getElementById('selective_discount_table_body');
        tbody.innerHTML = '';

        if (!items || !items.length) {
            tbody.innerHTML = `<tr><td colspan="4" class="py-4 text-center text-slate-400">Tidak ada komponen biaya.</td></tr>`;
            return;
        }

        items.forEach((it, idx) => {
            const discVal = Number(itemDiscounts[it.name] || (it.id ? itemDiscounts[it.id] : 0) || 0);
            const paid = Number(it.paid_amount || 0);
            const isItemPaid = it.is_fully_paid || (paid >= it.amount && it.amount > 0);
            const netAmount = Math.max(0, it.amount - discVal);
            const maxDiscount = Math.max(0, it.amount - paid);

            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition';
            tr.innerHTML = `
                <td class="py-2.5 px-3 font-extrabold text-slate-800 dark:text-slate-100">
                    <div class="flex items-center gap-2">
                        <span>${it.name}</span>
                        ${isItemPaid ? `
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                                ✓ Lunas
                            </span>
                        ` : (paid > 0 ? `
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold text-amber-700 bg-amber-50 border border-amber-200">
                                Terbayar Rp ${paid.toLocaleString('id-ID')}
                            </span>
                        ` : '')}
                    </div>
                </td>
                <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-600 dark:text-slate-300">
                    Rp ${Number(it.amount).toLocaleString('id-ID')}
                </td>
                <td class="py-2.5 px-3 text-right">
                    <div class="relative inline-block w-32">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-2 text-[10px] font-bold text-slate-400 font-mono">Rp</span>
                        <input type="number" 
                               name="item_discounts[${it.name}]" 
                               value="${isItemPaid ? 0 : discVal}"
                               min="0" 
                               max="${maxDiscount}"
                               step="10000"
                               ${(isFullyPaid || isItemPaid) ? 'disabled' : ''}
                               data-gross="${it.amount}"
                               data-paid="${paid}"
                               class="selective-disc-input w-full pl-7 pr-2 py-1 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-bold font-mono text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 text-right ${(isFullyPaid || isItemPaid) ? 'opacity-50 cursor-not-allowed bg-slate-100 dark:bg-slate-800' : ''}"
                               placeholder="0"
                               oninput="onSelectiveDiscountInput(this)">
                    </div>
                </td>
                <td class="py-2.5 px-3 text-right font-mono font-black text-emerald-600 dark:text-emerald-400 item-net-display">
                    Rp ${Number(netAmount).toLocaleString('id-ID')}
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderSelectiveInstallmentItems(items, allowedIds, isFullyPaid) {
        const container = document.getElementById('selective_installment_items_list');
        container.innerHTML = '';

        if (!items || !items.length) {
            container.innerHTML = `<p class="text-slate-400 text-xs">Tidak ada komponen biaya.</p>`;
            return;
        }

        items.forEach((it, idx) => {
            const isChecked = Array.isArray(allowedIds) && (allowedIds.includes(it.id) || allowedIds.includes(String(it.id)) || allowedIds.includes(it.name));
            const paid = Number(it.paid_amount || 0);
            const isItemPaid = it.is_fully_paid || (paid >= it.amount && it.amount > 0);

            const div = document.createElement('label');
            div.className = `flex items-center justify-between p-2 rounded-lg border border-slate-200 dark:border-slate-700/60 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition cursor-pointer ${(isFullyPaid || isItemPaid) ? 'opacity-50 cursor-not-allowed bg-slate-50 dark:bg-slate-800' : ''}`;
            div.innerHTML = `
                <div class="flex items-center gap-2.5">
                    <input type="checkbox" 
                           name="installment_fee_ids[]" 
                           value="${it.id || it.name}" 
                           ${isChecked ? 'checked' : ''}
                           ${(isFullyPaid || isItemPaid) ? 'disabled' : ''}
                           class="text-indigo-600 focus:ring-indigo-500 rounded w-4 h-4">
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-100">${it.name}</span>
                    ${isItemPaid ? `
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                            ✓ Lunas
                        </span>
                    ` : ''}
                </div>
                <span class="text-xs font-mono font-bold text-slate-500">
                    Rp ${Number(it.amount).toLocaleString('id-ID')}
                </span>
            `;
            container.appendChild(div);
        });
    }

    function onSelectiveDiscountInput(input) {
        const gross = Number(input.dataset.gross || 0);
        const paid = Number(input.dataset.paid || 0);
        const maxDisc = Math.max(0, gross - paid);
        let val = Number(input.value || 0);

        if (val < 0) val = 0;
        if (val > maxDisc) val = maxDisc;
        input.value = val;

        const net = Math.max(0, gross - val);
        const tr = input.closest('tr');
        if (tr) {
            const netDisplay = tr.querySelector('.item-net-display');
            if (netDisplay) netDisplay.textContent = 'Rp ' + Number(net).toLocaleString('id-ID');
        }

        recalcLiveSimulation();
    }

    function recalcLiveSimulation() {
        if (!currentCandidate || !currentFeeDetails) return;

        const items = currentFeeDetails.items || [];
        const gross = items.reduce((acc, it) => acc + Number(it.amount || 0), 0);
        let totalDisc = 0;

        const discMode = document.querySelector('input[name="discount_mode"]:checked')?.value || 'none';

        if (discMode === 'global') {
            totalDisc = Number(document.getElementById('modal_discount_amount').value || 0);
        } else if (discMode === 'selective') {
            document.querySelectorAll('.selective-disc-input').forEach(inp => {
                totalDisc += Number(inp.value || 0);
            });
        }

        const net = Math.max(0, gross - totalDisc);
        const paid = currentCandidate ? (currentCandidate.total_paid_final_fee || 0) : 0;
        const remaining = Math.max(0, net - paid);

        document.getElementById('sim_gross').textContent = 'Rp ' + Number(gross).toLocaleString('id-ID');
        document.getElementById('sim_discount').textContent = '- Rp ' + Number(totalDisc).toLocaleString('id-ID');
        document.getElementById('sim_net').textContent = 'Rp ' + Number(net).toLocaleString('id-ID');
        document.getElementById('sim_remaining').textContent = 'Rp ' + Number(remaining).toLocaleString('id-ID');
    }

    async function submitPolicyForm(event) {
        event.preventDefault();
        const form = document.getElementById('policy-form');
        const regId = document.getElementById('modal_registration_id').value;
        const saveBtn = document.getElementById('btn_save_policy');

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Menyimpan...';
        if (window.lucide) lucide.createIcons();

        const formData = new FormData(form);

        try {
            const res = await fetch(`/admin/candidates/${regId}/installment-settings`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await res.json();
            if (data.success) {
                document.getElementById('modal_policy_success_alert').classList.remove('hidden');
                setTimeout(() => {
                    location.reload();
                }, 800);
            } else {
                alert('Gagal menyimpan pengaturan: ' + (data.message || 'Terjadi kesalahan.'));
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan jaringan.');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4"></i> Simpan Kebijakan Biaya';
            if (window.lucide) lucide.createIcons();
        }
    }

    function openTransactionsModal(cand, payments) {
        document.body.classList.add('overflow-hidden');
        document.getElementById('tx-modal-name').textContent = cand.candidate_name || 'Calon Siswa';
        document.getElementById('tx-modal-id').textContent = cand.id_label || ('SANS-2027-' + String(cand.id).padStart(4, '0'));

        const listContainer = document.getElementById('tx-modal-list');
        listContainer.innerHTML = '';

        // Filter only SUCCESS / SETTLED payments for official accounting report
        const successPayments = (payments || []).filter(p => p.status === 'success' || p.status === 'settled');

        if (!successPayments.length) {
            listContainer.innerHTML = `
                <div class="p-10 text-center text-slate-400 dark:text-slate-500">
                    <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i data-lucide="receipt" class="w-7 h-7"></i>
                    </div>
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Belum Ada Pembayaran Masuk</p>
                    <p class="text-xs text-slate-400 mt-1">Calon siswa ini belum memiliki transaksi pembayaran yang berstatus lunas/berhasil.</p>
                </div>
            `;
        } else {
            successPayments.forEach(p => {
                const isRegFee = (p.payment_type === 'registration_fee');

                // Dynamic Category Badges from master SpmbFeeCategory
                const catList = (p.category_names && p.category_names.length > 0) 
                    ? p.category_names 
                    : [isRegFee ? 'Formulir Pendaftaran' : 'Biaya Administrasi'];

                const categoryBadgesHtml = catList.map(catName => {
                    let badgeStyle = 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800';
                    const lower = catName.toLowerCase();
                    if (lower.includes('formulir') || lower.includes('pendaftaran') || lower.includes('registrasi')) {
                        badgeStyle = 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800';
                    } else if (lower.includes('tambahan') || lower.includes('non-formal') || lower.includes('tpa') || lower.includes('tpq')) {
                        badgeStyle = 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800';
                    }
                    return `<span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase ${badgeStyle}">${catName}</span>`;
                }).join(' ');

                // Parsing selected items
                let items = [];
                let info = p.payment_info;
                if (typeof info === 'string') {
                    try { info = JSON.parse(info); } catch(e) { info = {}; }
                }
                info = info || {};

                if (isRegFee) {
                    items.push({
                        name: cand.registration_fee_name || info.fee_name || 'Formulir Pendaftaran',
                        amount: Number(p.base_amount || (p.amount - (p.admin_fee || 0)))
                    });
                } else {
                    if (Array.isArray(info.selected_items) && info.selected_items.length > 0) {
                        items = info.selected_items.map(it => ({
                            name: it.name || 'Komponen Biaya',
                            amount: Number(it.amount || 0)
                        }));
                    } else {
                        items.push({
                            name: 'Pelunasan / Cicilan Biaya Masuk',
                            amount: Number(p.base_amount || (p.amount - (p.admin_fee || 0)))
                        });
                    }
                }

                const baseAmount = Number(p.base_amount || (p.amount - (p.admin_fee || 0)));
                const adminFee = Number(p.admin_fee || (p.amount - baseAmount));
                const totalAmount = Number(p.amount);

                const formattedDate = new Date(p.created_at).toLocaleString('id-ID', {
                    day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
                });

                const itemsHtml = items.map(it => `
                    <div class="flex items-center justify-between py-1.5 text-xs">
                        <span class="font-bold text-slate-700 dark:text-slate-200 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-emerald flex-shrink-0"></span>
                            <span>${it.name}</span>
                        </span>
                        <span class="font-mono font-extrabold text-slate-800 dark:text-slate-100">
                            Rp ${it.amount.toLocaleString('id-ID')}
                        </span>
                    </div>
                `).join('');

                const row = document.createElement('div');
                row.className = 'p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 space-y-3 shadow-xs';
                row.innerHTML = `
                    <!-- Header: Invoice, Categories, Status -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-mono font-black text-xs text-slate-900 dark:text-white select-all">${p.invoice_number}</span>
                                ${categoryBadgesHtml}
                            </div>
                            <span class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3 text-slate-400"></i> ${formattedDate} WIB
                            </span>
                        </div>
                        <span class="self-start sm:self-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase border bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-300 dark:border-emerald-700 flex items-center gap-1.5 shadow-2xs">
                            <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                            <span>LUNAS / BERHASIL</span>
                        </span>
                    </div>

                    <!-- Items Detail -->
                    <div class="p-3 bg-white dark:bg-slate-900/90 rounded-xl border border-slate-200/80 dark:border-slate-700/80 space-y-1">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">
                            Rincian Komponen Biaya:
                        </span>
                        <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            ${itemsHtml}
                        </div>
                    </div>

                    <!-- Financial Summary Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 p-3 rounded-xl bg-slate-100/70 dark:bg-slate-900/40 text-xs border border-slate-200/50 dark:border-slate-800">
                        <div>
                            <span class="text-[10px] text-slate-400 block font-medium">Metode Pembayaran</span>
                            <span class="font-bold text-slate-800 dark:text-slate-100 text-xs uppercase block truncate">
                                ${p.payment_method || 'Online'}
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block font-medium">Nominal Pokok</span>
                            <span class="font-mono font-bold text-slate-700 dark:text-slate-200 text-xs block">
                                Rp ${baseAmount.toLocaleString('id-ID')}
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block font-medium">Biaya Admin PG</span>
                            <span class="font-mono font-bold text-slate-500 dark:text-slate-400 text-xs block">
                                + Rp ${adminFee.toLocaleString('id-ID')}
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold block">Total Transaksi</span>
                            <span class="font-mono font-black text-slate-900 dark:text-white text-xs block">
                                Rp ${totalAmount.toLocaleString('id-ID')}
                            </span>
                        </div>
                    </div>

                    <!-- Footer Action: Receipt Button -->
                    <div class="flex items-center justify-between pt-1 text-xs">
                        <span class="text-[11px] text-emerald-700 dark:text-emerald-400 font-bold flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                            Kas Berhasil Tercatat
                        </span>
                        <a href="/admin/payments/receipt/${p.id}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-sm transition">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span>Unduh Kwitansi PDF</span>
                        </a>
                    </div>
                `;
                listContainer.appendChild(row);
            });
        }

        document.getElementById('transactions-modal').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function closeTransactionsModal() {
        document.getElementById('transactions-modal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
</script>
@endsection
