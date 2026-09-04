@extends('layouts.portal')

@section('title', 'Hasil Seleksi & Administrasi Akhir - Portal SPMB')

@section('content')
<style>
    /* Styling for dynamic rich text instructions from Quill */
    .instructions-body ul {
        list-style-type: disc !important;
        padding-left: 1.25rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }
    .instructions-body ol {
        list-style-type: decimal !important;
        padding-left: 1.25rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }
    .instructions-body li {
        margin-bottom: 0.625rem !important;
        line-height: 1.625 !important;
    }
</style>

<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
    @php
        $userAllRegs = auth()->check() ? auth()->user()->registrations()->with(['unit', 'grade', 'classProgram'])->where('registration_status', '!=', 'draft')->orWhereHas('payments', function($q) { $q->where('payment_type', 'registration_fee')->where('status', 'success'); })->latest()->get() : collect();
        $otherRegs = $userAllRegs->where('id', '!=', $registration->id);
    @endphp

    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-150/80 dark:border-slate-800 overflow-hidden">
        
        <!-- CARD HEADER -->
        <div class="bg-brand-emerald text-white p-5 sm:p-6 space-y-3 sm:space-y-4">
            <div class="flex items-center justify-between gap-2.5 w-full">
                <h2 class="font-extrabold text-sm sm:text-lg text-white flex items-center gap-2 leading-tight min-w-0">
                    <i data-lucide="award" class="w-4 h-4 sm:w-5 sm:h-5 text-brand-yellow shrink-0"></i>
                    <span class="truncate sm:whitespace-normal">Hasil Seleksi & Administrasi</span>
                </h2>
                
                <div class="shrink-0 self-center sm:self-start pt-0">
                    @if($registration->registration_status === 'completed')
                        <span class="inline-flex items-center gap-1 bg-green-700 text-white font-black text-[9px] sm:text-[10px] uppercase tracking-wider px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border border-green-500 shadow-xs whitespace-nowrap">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-300 animate-ping"></span> Lunas &amp; Resmi
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 bg-amber-600 text-white font-black text-[9px] sm:text-[10px] uppercase tracking-wider px-2 sm:px-3 py-0.5 sm:py-1 rounded-full border border-amber-500 shadow-xs whitespace-nowrap">
                            <i data-lucide="clock" class="w-3 h-3 sm:w-3.5 sm:h-3.5"></i> <span class="hidden sm:inline">Menunggu </span>Pelunasan
                        </span>
                    @endif
                </div>
            </div>

            <!-- Full-width subtitle -->
            <p class="text-xs text-brand-yellow/90 font-medium leading-relaxed w-full">Pengumuman kelulusan resmi dan rincian pembiayaan pendidikan.</p>

            <!-- Integrated Candidate Context Info -->
            <div class="bg-black/15 backdrop-blur-md rounded-2xl p-3 sm:p-4 border border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <!-- Left: Avatar + Candidate Details -->
                <div class="flex items-start sm:items-center gap-3 min-w-0">
                    <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl sm:rounded-2xl bg-white/20 text-white font-black text-sm sm:text-base flex items-center justify-center border border-white/20 shadow-inner shrink-0 mt-0.5 sm:mt-0">
                        {{ strtoupper(substr(trim($registration->candidate_name ?? 'A'), 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1 space-y-0.5">
                        <div class="flex items-center justify-between sm:justify-start gap-2">
                            <h4 class="font-extrabold text-sm sm:text-base text-white tracking-tight truncate">
                                {{ $registration->candidate_name ?? 'Calon Siswa' }}
                            </h4>
                            @if($registration->id_label)
                                <span class="sm:hidden text-[10px] font-mono font-bold text-emerald-200 bg-white/15 px-2 py-0.5 rounded-lg border border-white/20 inline-flex items-center gap-1 shadow-xs whitespace-nowrap shrink-0">
                                    <i data-lucide="tag" class="w-3 h-3 text-emerald-300"></i> {{ $registration->id_label }}
                                </span>
                            @endif
                        </div>
                        
                        <p class="text-xs text-emerald-100 font-semibold truncate">
                            <span class="text-emerald-300 font-bold">{{ $registration->unit?->name }}</span> • {{ $registration->grade?->name }} ({{ $registration->classProgram?->name ?? 'Reguler' }})
                            @if($registration->extraServices && $registration->extraServices->isNotEmpty())
                                <span class="text-brand-yellow font-bold">• {{ $registration->extraServices->pluck('name')->join(', ') }}</span>
                            @endif
                        </p>
                        
                        <p class="text-[11px] text-white/75 truncate">
                            Jalur {{ $registration->type?->name ?? '-' }} • {{ $registration->wave?->name ?? '-' }}
                            @if($registration->period?->year)
                                <span class="text-white/50">(TP {{ $registration->period->year }})</span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Right: ID Label Badge (Desktop) & Child Switcher -->
                <div class="flex items-center sm:justify-end gap-2 shrink-0 {{ $otherRegs->isNotEmpty() ? 'border-t sm:border-t-0 pt-2 sm:pt-0 border-white/10' : '' }}">
                    @if($registration->id_label)
                        <span class="hidden sm:inline-flex text-[11px] font-mono font-bold text-emerald-200 bg-white/15 px-2.5 py-1 rounded-xl border border-white/20 items-center gap-1.5 shadow-xs whitespace-nowrap">
                            <i data-lucide="tag" class="w-3.5 h-3.5 text-emerald-300"></i> {{ $registration->id_label }}
                        </span>
                    @endif

                    @if($otherRegs->isNotEmpty())
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @foreach($otherRegs as $other)
                                <a href="{{ route('dashboard.result', $other->id) }}" 
                                   class="inline-flex items-center gap-1.5 px-2 py-1 rounded-xl bg-white/15 hover:bg-white/25 text-white text-[11px] font-bold transition border border-white/20 shadow-xs"
                                   title="Beralih ke {{ $other->candidate_name }}">
                                   <span>👦 {{ $other->candidate_name }}</span>
                                   <span class="text-[9px] px-1.5 py-0.5 bg-emerald-950/80 rounded-md text-emerald-300 font-extrabold">{{ $other->unit?->code }}</span>
                                   <i data-lucide="arrow-right" class="w-3 h-3 text-emerald-300"></i>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="p-4 sm:p-8 space-y-5 sm:space-y-8">
            
            <!-- ANNOUNCEMENT BANNER -->
            <div class="bg-gradient-to-r from-emerald-50 to-emerald-100/50 dark:from-emerald-950/10 dark:to-emerald-900/5 border border-emerald-200/60 dark:border-emerald-900/50 rounded-2xl p-4 sm:p-6 flex flex-col sm:flex-row gap-4 sm:gap-5 items-center text-center sm:text-left">
                <div class="h-14 w-14 sm:h-16 sm:w-16 bg-brand-emerald text-white rounded-2xl flex items-center justify-center shadow-md flex-shrink-0">
                    <i data-lucide="party-popper" class="w-7 h-7 sm:w-8 sm:h-8 text-brand-yellow"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-base sm:text-lg font-black text-slate-850 dark:text-white">Alhamdulillah, Dinyatakan LULUS & DITERIMA</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Selamat kepada ananda <strong class="text-slate-800 dark:text-slate-200">{{ $registration->candidate_name }}</strong> yang telah lolos seluruh tahapan observasi kesiapan belajar dan berkas pendaftaran.
                    </p>
                </div>
            </div>

            <!-- TUITION FEES COMPONENT BREAKDOWN -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h4 class="font-bold text-slate-700 dark:text-slate-300 text-[10px] sm:text-xs uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="receipt" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-brand-emerald"></i>
                        <span>Rincian Biaya Masuk Awal</span>
                    </h4>
                    @if(isset($discountAmount) && ($discountAmount > 0 || ($installmentMode ?? 'none') !== 'none'))
                        <span class="self-start sm:self-auto px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-300/40">
                            Disetujui Keringanan / Cicilan
                        </span>
                    @endif
                </div>

                @if(isset($discountAmount) && ($discountAmount > 0 || ($installmentMode ?? 'none') !== 'none'))
                    <!-- Keringanan Notice Banner -->
                    <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900 rounded-2xl flex items-start gap-3">
                        <div class="h-8 w-8 rounded-xl bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-extrabold text-xs text-emerald-900 dark:text-emerald-300">
                                @if($discountAmount > 0 && ($installmentMode ?? 'none') !== 'none')
                                    Pemberitahuan Persetujuan Keringanan & Kebijakan Cicilan
                                @elseif($discountAmount > 0)
                                    Pemberitahuan Persetujuan Keringanan Biaya (Diskon)
                                @else
                                    Kebijakan Cicilan Pembayaran
                                @endif
                            </h4>
                            <p class="text-[11px] text-emerald-750 dark:text-emerald-400 leading-relaxed">
                                @if($discountAmount > 0 && ($installmentMode ?? 'none') !== 'none')
                                    Alhamdulillah! Ananda disetujui memperoleh <strong>Keringanan Potongan Biaya sebesar Rp {{ number_format($discountAmount, 0, ',', '.') }}</strong> ({{ $discountNotes ?: 'Keringanan Yayasan' }}) dan diizinkan melakukan <strong>pembayaran bertahap (cicilan)</strong>.
                                @elseif($discountAmount > 0)
                                    Alhamdulillah! Ananda disetujui memperoleh <strong>Keringanan Potongan Biaya sebesar Rp {{ number_format($discountAmount, 0, ',', '.') }}</strong> ({{ $discountNotes ?: 'Keringanan Yayasan' }}).
                                @elseif(($installmentMode ?? 'none') !== 'none')
                                    Alhamdulillah! Anda disetujui untuk melakukan <strong>pembayaran bertahap (cicilan)</strong> untuk biaya masuk ini.
                                @endif
                            </p>
                        </div>
                    </div>
                @endif

                <div class="bg-white dark:bg-slate-900 border border-slate-150/80 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs sm:shadow-inner">
                    <table class="w-full text-left text-xs border-collapse block sm:table">
                        <thead class="hidden sm:table-header-group">
                            <tr class="bg-slate-50 dark:bg-slate-950 text-[10px] text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-850">
                                @if($registration->registration_status !== 'completed')
                                    <th class="p-4 text-center w-12 select-none">Pilih</th>
                                @endif
                                <th class="p-4">Komponen Pembiayaan</th>
                                <th class="p-4 text-right">Nominal</th>
                                <th class="p-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="block sm:table-row-group p-3 sm:p-0 space-y-3 sm:space-y-0 sm:divide-y sm:divide-slate-100 sm:dark:divide-slate-850">
                            @if(isset($feeDetails['items']) && is_array($feeDetails['items']))
                                @php
                                    $successfulPayments = $registration->payments()
                                        ->where('status', 'success')
                                        ->where('payment_type', 'final_fee')
                                        ->with('items')
                                        ->get();
                                @endphp
                                @foreach($feeDetails['items'] as $item)
                                    @php
                                        $itemId = $item['id'] ?? null;
                                        $itemName = $item['name'];
                                        
                                        // Collect all payment receipts for this specific item in chronological order
                                        $itemPayments = [];
                                        if (isset($successfulPayments)) {
                                            foreach ($successfulPayments->sortBy('created_at') as $p) {
                                                $foundAmount = 0;
                                                if ($p->items && $p->items->isNotEmpty()) {
                                                    foreach ($p->items as $pItem) {
                                                        if (($itemId && (int)$pItem->spmb_fee_id === (int)$itemId) || strcasecmp(trim($pItem->fee_name), trim($itemName)) === 0) {
                                                            $foundAmount += (float) $pItem->amount;
                                                        }
                                                    }
                                                } elseif (isset($p->payment_info['selected_items']) && is_array($p->payment_info['selected_items'])) {
                                                    foreach ($p->payment_info['selected_items'] as $si) {
                                                        if (($itemId && isset($si['id']) && (int)$si['id'] === (int)$itemId) || strcasecmp(trim($si['name'] ?? ''), trim($itemName)) === 0) {
                                                            $foundAmount += (float) ($si['amount'] ?? 0);
                                                        }
                                                    }
                                                }
                                                if ($foundAmount > 0) {
                                                    $itemPayments[] = [
                                                        'payment' => $p,
                                                        'amount' => $foundAmount,
                                                        'date' => $p->created_at ? $p->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') : '-',
                                                        'method' => $p->payment_method ?? 'VA'
                                                    ];
                                                }
                                            }
                                        }

                                        $itemGross = (float) ($item['amount'] ?? 0);
                                        $itemDiscount = $registration->getItemDiscountAmount($item['name'], $item['id'] ?? null);
                                        $itemNet = max(0, $itemGross - $itemDiscount);
                                        $itemPaid = $registration->getItemPaidAmount($item['name'], $item['id'] ?? null);
                                        $itemRemaining = max(0, $itemNet - $itemPaid);
                                        $isItemLunas = ($itemRemaining <= 0);
                                        $canCicil = (!$isItemLunas) && (!empty($item['is_installment_allowed']) || ($installmentMode ?? 'none') === 'all');
                                        $minItemInstallment = min($itemRemaining, (float) ($registration->min_installment_amount ?: 500000));
                                    @endphp
                                    <tr class="block sm:table-row bg-slate-50/50 dark:bg-slate-950/30 sm:bg-transparent border border-slate-200/80 dark:border-slate-800 sm:border-0 rounded-2xl sm:rounded-none p-3.5 sm:p-0 shadow-xs sm:shadow-none text-slate-650 dark:text-slate-350 {{ $isItemLunas ? 'bg-slate-50/30 dark:bg-slate-950/10' : '' }}">
                                        @if($registration->registration_status !== 'completed')
                                            <td class="float-left sm:float-none p-0 sm:p-4 text-center align-top sm:pt-4.5 mr-2.5 sm:mr-0 mt-0.5 sm:mt-0">
                                                @if($isItemLunas)
                                                    <span class="inline-flex items-center justify-center text-green-600 dark:text-green-400">
                                                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                                                    </span>
                                                @else
                                                    <input type="checkbox" 
                                                        class="fee-checkbox rounded text-brand-emerald focus:ring-brand-emerald cursor-pointer w-4 h-4" 
                                                        id="checkbox-item-{{ $loop->index }}"
                                                        data-index="{{ $loop->index }}" 
                                                        data-gateways="{{ json_encode($item['gateways'] ?? ['winpay']) }}" 
                                                        data-amount="{{ $itemRemaining }}"
                                                        data-is-cicil="{{ $canCicil ? '1' : '0' }}"
                                                        data-max="{{ $itemRemaining }}"
                                                        data-min="{{ $minItemInstallment }}"
                                                        data-name="{{ $item['name'] }}"
                                                        checked 
                                                        onchange="onFeeCheckboxChange({{ $loop->index }})">
                                                @endif
                                            </td>
                                        @endif
                                        <td class="block sm:table-cell p-0 sm:p-4 font-medium {{ $isItemLunas ? 'text-slate-400' : '' }}">
                                            <div class="flex flex-col gap-0.5">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div class="flex items-center gap-2 flex-wrap {{ $isItemLunas ? 'line-through' : '' }}">
                                                        <span class="font-extrabold text-xs text-slate-850 dark:text-white">{{ $item['name'] }}</span>
                                                        @if($itemDiscount > 0)
                                                            <span class="px-1.5 py-0.5 rounded bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 font-bold text-[9px] border border-rose-200/60 dark:border-rose-900">
                                                                🏷️ Diskon Rp {{ number_format($itemDiscount, 0, ',', '.') }}
                                                            </span>
                                                        @endif
                                                        @if(($installmentMode ?? 'none') === 'selective' && !empty($item['is_installment_allowed']))
                                                            <span class="px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 font-bold text-[9px] border border-blue-200/60 dark:border-blue-900">
                                                                🔓 Boleh Dicicil
                                                            </span>
                                                        @elseif(($installmentMode ?? 'none') === 'all')
                                                            <span class="px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold text-[9px] border border-emerald-200/60 dark:border-emerald-900">
                                                                ✓ Bisa Dicicil
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <!-- Mobile Status Badge in Card Top-Right -->
                                                    <div class="sm:hidden shrink-0">
                                                        @if($isItemLunas || $registration->registration_status === 'completed')
                                                            <span class="text-[9px] bg-green-100 dark:bg-green-950/40 text-green-700 dark:text-green-300 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider border border-green-300/40">Lunas</span>
                                                        @elseif($itemPaid > 0)
                                                            <span class="text-[9px] bg-blue-100 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider border border-blue-300/40">Dicicil</span>
                                                        @else
                                                            <span class="text-[9px] bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider border border-amber-300/40">Tanggungan</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                @if($itemDiscount > 0)
                                                    <div class="text-[10px] text-rose-600 dark:text-rose-400 font-semibold flex items-center gap-1 mt-0.5">
                                                        <i data-lucide="tag" class="w-3 h-3"></i> Diskon Khusus: - Rp {{ number_format($itemDiscount, 0, ',', '.') }} (Tarif Asli: Rp {{ number_format($itemGross, 0, ',', '.') }})
                                                    </div>
                                                @endif

                                                @if($isItemLunas)
                                                    @if(count($itemPayments) > 1)
                                                        <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1 mt-0.5">
                                                            <i data-lucide="check-check" class="w-3.5 h-3.5 text-emerald-500"></i> Terbayar Lunas (Total: Rp {{ number_format($itemNet, 0, ',', '.') }} melalui {{ count($itemPayments) }}x pembayaran)
                                                        </div>
                                                        <div class="mt-2 bg-emerald-50/40 dark:bg-emerald-950/20 p-2.5 rounded-xl border border-emerald-200/50 dark:border-emerald-900/40 space-y-1.5">
                                                            <div class="text-[10px] font-bold text-slate-700 dark:text-slate-300 flex items-center justify-between">
                                                                <span class="flex items-center gap-1.5">
                                                                    <i data-lucide="history" class="w-3.5 h-3.5 text-brand-emerald"></i> Riwayat Angsuran Cicilan:
                                                                </span>
                                                            </div>
                                                            <div class="space-y-1 pt-0.5">
                                                                @foreach($itemPayments as $idx => $ip)
                                                                    <div class="flex items-center justify-between gap-2 text-[10px] bg-white dark:bg-slate-800 px-2.5 py-1.5 rounded-lg border border-emerald-100 dark:border-slate-700/60">
                                                                        <div class="flex items-center gap-2 flex-wrap">
                                                                            <span class="px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold text-[9px]">Cicilan #{{ $idx + 1 }}</span>
                                                                            <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono">Rp {{ number_format($ip['amount'], 0, ',', '.') }}</span>
                                                                            <span class="text-slate-400 text-[9px]">({{ $ip['date'] }})</span>
                                                                        </div>
                                                                        <a href="{{ route('dashboard.payment.receipt', $ip['payment']->id) }}" target="_blank" download class="download-link-animate inline-flex items-center gap-1 text-[9px] font-bold text-brand-emerald hover:underline bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded border border-emerald-200/60 dark:border-emerald-800 whitespace-nowrap" title="Unduh Kwitansi Cicilan ke-{{ $idx + 1 }}">
                                                                            <i data-lucide="download" class="w-2.5 h-2.5"></i> Kwitansi #{{ $idx + 1 }}
                                                                        </a>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @elseif(count($itemPayments) === 1)
                                                        <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center justify-between gap-1 mt-0.5">
                                                            <span class="flex items-center gap-1"><i data-lucide="check-check" class="w-3.5 h-3.5 text-emerald-500"></i> Terbayar Lunas via {{ $itemPayments[0]['method'] }} ({{ $itemPayments[0]['date'] }} WIB)</span>
                                                            <a href="{{ route('dashboard.payment.receipt', $itemPayments[0]['payment']->id) }}" target="_blank" download class="sm:hidden download-link-animate inline-flex items-center gap-1 text-[9px] font-bold text-brand-emerald hover:underline bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded border border-emerald-200/60 dark:border-emerald-800 whitespace-nowrap">
                                                                <i data-lucide="download" class="w-2.5 h-2.5"></i> Kwitansi
                                                            </a>
                                                        </div>
                                                    @endif
                                                @elseif(!$isItemLunas && $itemPaid > 0)
                                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-medium flex flex-wrap items-center gap-1.5 mt-1">
                                                        <span>Tarif Pokok: <strong class="font-mono text-slate-700 dark:text-slate-200">Rp {{ number_format($itemNet, 0, ',', '.') }}</strong></span>
                                                        <span>•</span>
                                                        <span>Telah Dicicil: <strong class="text-emerald-600 dark:text-emerald-400 font-bold font-mono">Rp {{ number_format($itemPaid, 0, ',', '.') }}</strong> ({{ count($itemPayments) }}x)</span>
                                                        <span>•</span>
                                                        <span>Sisa: <strong class="text-amber-600 dark:text-amber-400 font-bold font-mono">Rp {{ number_format($itemRemaining, 0, ',', '.') }}</strong></span>
                                                    </div>

                                                    {{-- List Riwayat Cicilan yang Sudah Dibayarkan dengan Tombol Kwitansi Masing-Masing --}}
                                                    @if(!empty($itemPayments))
                                                        <div class="mt-2.5 bg-slate-50/90 dark:bg-slate-900/60 p-2.5 rounded-xl border border-slate-200/70 dark:border-slate-800 space-y-1.5">
                                                            <div class="text-[10px] font-bold text-slate-700 dark:text-slate-300 flex items-center justify-between">
                                                                <span class="flex items-center gap-1.5">
                                                                    <i data-lucide="receipt-text" class="w-3.5 h-3.5 text-brand-emerald"></i> Riwayat Kwitansi Pembayaran Cicilan ({{ count($itemPayments) }}x):
                                                                </span>
                                                            </div>
                                                            <div class="space-y-1 pt-0.5">
                                                                @foreach($itemPayments as $idx => $ip)
                                                                    <div class="flex items-center justify-between gap-2 text-[10px] bg-white dark:bg-slate-800 px-2.5 py-1.5 rounded-lg border border-slate-200/50 dark:border-slate-700/60 shadow-xs">
                                                                        <div class="flex items-center gap-2 flex-wrap">
                                                                            <span class="px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold text-[9px]">Cicilan #{{ $idx + 1 }}</span>
                                                                            <span class="font-bold text-emerald-600 dark:text-emerald-400 font-mono">Rp {{ number_format($ip['amount'], 0, ',', '.') }}</span>
                                                                            <span class="text-slate-400 text-[9px]">via {{ $ip['method'] }} • {{ $ip['date'] }} WIB</span>
                                                                        </div>
                                                                        <a href="{{ route('dashboard.payment.receipt', $ip['payment']->id) }}" target="_blank" download class="download-link-animate inline-flex items-center gap-1 text-[9px] font-bold text-brand-emerald hover:underline bg-emerald-50 dark:bg-emerald-950/40 px-2.5 py-1 rounded-md border border-emerald-200/60 dark:border-emerald-800 whitespace-nowrap transition" title="Unduh Kwitansi Cicilan ke-{{ $idx + 1 }}">
                                                                            <i data-lucide="download" class="w-2.5 h-2.5"></i> Kwitansi Cicilan #{{ $idx + 1 }}
                                                                        </a>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endif

                                                {{-- Accordion Input Cicilan pada Item yang Boleh Dicicil --}}
                                                @if(!$isItemLunas && $canCicil)
                                                    <div class="mt-2" id="cicil-control-wrapper-{{ $loop->index }}">
                                                        <button type="button" onclick="toggleResultItemAccordion({{ $loop->index }})" class="inline-flex items-center gap-1.5 text-[11px] font-bold text-brand-emerald hover:underline select-none">
                                                            <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>
                                                            <span id="btn-accordion-text-{{ $loop->index }}">Cicil Sebagian (Atur Nominal)</span>
                                                            <i data-lucide="chevron-down" id="chevron-{{ $loop->index }}" class="w-3 h-3 transition-transform duration-200"></i>
                                                        </button>

                                                        <div id="accordion-input-box-{{ $loop->index }}" class="hidden mt-2.5 p-3.5 sm:p-4 bg-emerald-50/70 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200/80 dark:border-emerald-900/60 space-y-2.5 max-w-lg shadow-xs">
                                                            <!-- Header: Label + Boundary Info Badge -->
                                                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                                                <label for="input-amount-{{ $loop->index }}" class="text-[11px] font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                                                    <i data-lucide="coins" class="w-3.5 h-3.5 text-brand-emerald"></i>
                                                                    <span>Nominal Dicicil Tahap Ini</span>
                                                                </label>
                                                                <span class="text-[10px] text-emerald-800 dark:text-emerald-300 bg-white/90 dark:bg-slate-900/80 font-mono font-bold px-2 py-0.5 rounded-lg border border-emerald-200 dark:border-emerald-800 shadow-xs">
                                                                    Min: <strong class="text-emerald-600 dark:text-emerald-400">Rp {{ number_format($minItemInstallment, 0, ',', '.') }}</strong>
                                                                </span>
                                                            </div>

                                                            <!-- Full-Width Input Field -->
                                                            <div class="relative w-full">
                                                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-xs font-bold text-slate-400 dark:text-slate-500 font-mono select-none">Rp</span>
                                                                <input type="text"
                                                                    inputmode="numeric" 
                                                                    id="input-amount-{{ $loop->index }}"
                                                                    class="result-item-amount-input w-full pl-10 pr-3.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-xs sm:text-sm font-bold font-mono text-slate-850 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-xs transition"
                                                                    placeholder="Contoh: {{ number_format($minItemInstallment, 0, ',', '.') }}"
                                                                    value=""
                                                                    data-applied-amount="{{ $itemRemaining }}"
                                                                    data-index="{{ $loop->index }}"
                                                                    data-max="{{ $itemRemaining }}"
                                                                    data-min="{{ $minItemInstallment }}"
                                                                    oninput="formatRupiahInput(this)"
                                                                    onkeydown="if(event.key === 'Enter'){ event.preventDefault(); applyResultItemInstallment({{ $loop->index }}); }">
                                                            </div>

                                                            <!-- Action Buttons Row: Terapkan (Primary) & Bayar Penuh (Secondary) -->
                                                            <div class="flex items-center gap-2 pt-0.5">
                                                                <button type="button" onclick="applyResultItemInstallment({{ $loop->index }})" class="flex-1 py-2 px-3.5 bg-brand-emerald hover-emerald text-white rounded-xl text-xs font-bold transition shadow-xs flex items-center justify-center gap-1.5 select-none active:scale-[0.98]">
                                                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                                    <span>Terapkan</span>
                                                                </button>
                                                                <button type="button" onclick="resetResultItemFull({{ $loop->index }})" class="py-2 px-3 bg-white dark:bg-slate-850 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700/80 rounded-xl text-xs font-bold transition select-none whitespace-nowrap active:scale-[0.98] shadow-xs" title="Batalkan cicilan dan bayar penuh">
                                                                    Bayar Penuh
                                                                </button>
                                                            </div>

                                                            <p id="error-msg-{{ $loop->index }}" class="hidden text-[10px] text-red-600 dark:text-red-400 font-semibold leading-tight pt-0.5"></p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="block sm:table-cell p-0 pt-2 sm:p-4 text-right font-bold {{ $isItemLunas ? 'text-slate-400' : 'text-slate-800 dark:text-slate-200' }} align-top sm:pt-4.5 border-t border-slate-100 dark:border-slate-800/60 sm:border-0 mt-2 sm:mt-0">
                                            <div class="flex items-center justify-between sm:justify-end gap-2">
                                                <span class="sm:hidden text-[10px] text-slate-400 font-bold uppercase tracking-wider">Nominal:</span>
                                                <div>
                                                    <span id="display-item-amount-{{ $loop->index }}" class="font-mono">
                                                        Rp {{ number_format($isItemLunas ? $itemNet : $itemRemaining, 0, ',', '.') }}
                                                    </span>
                                                    @if(!$isItemLunas && $itemPaid > 0)
                                                        <span class="block text-[10px] text-slate-400 font-normal mt-0.5">Sisa Tagihan</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="hidden sm:table-cell p-4 text-center align-top pt-4.5">
                                            @if($isItemLunas || $registration->registration_status === 'completed')
                                                <div class="flex flex-col items-center justify-center gap-1.5">
                                                    <span class="text-[9px] bg-green-50 dark:bg-green-950/20 text-green-600 px-2 py-0.5 rounded font-bold uppercase tracking-wider select-none">Lunas</span>
                                                    @if(!empty($itemPayments))
                                                        @php
                                                            $isMultipleInstallments = count($itemPayments) > 1;
                                                            $btnLabel = $isMultipleInstallments ? 'Kwitansi Utama' : 'Kwitansi';
                                                            $btnTitle = $isMultipleInstallments ? 'Unduh Kwitansi Utama / Pelunasan' : 'Unduh Kwitansi';
                                                            $receiptUrl = route('dashboard.payment.receipt', end($itemPayments)['payment']->id);
                                                            if ($isMultipleInstallments) {
                                                                $receiptUrl .= '?type=settlement&item_name=' . urlencode($item['name']);
                                                            }
                                                        @endphp
                                                        <a href="{{ $receiptUrl }}" target="_blank" download class="download-link-animate inline-flex items-center gap-1 text-[9px] font-bold text-brand-emerald hover:underline bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded border border-emerald-200/60 dark:border-emerald-800 whitespace-nowrap" title="{{ $btnTitle }}">
                                                            <i data-lucide="download" class="w-2.5 h-2.5 text-brand-emerald"></i> {{ $btnLabel }}
                                                        </a>
                                                    @endif
                                                </div>
                                            @elseif($itemPaid > 0)
                                                <div class="flex flex-col items-center justify-center gap-1">
                                                    <span class="text-[9px] bg-blue-50 dark:bg-blue-950/20 text-blue-600 px-2 py-0.5 rounded font-bold uppercase tracking-wider select-none">Dicicil</span>
                                                    <span class="text-[9px] text-slate-400 font-medium">Belum Lunas</span>
                                                </div>
                                            @else
                                                <span class="text-[9px] bg-amber-50 dark:bg-amber-955/20 text-amber-600 px-2 py-0.5 rounded font-bold uppercase tracking-wider select-none">Tanggungan</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif

                            @php
                                $isInstallmentActive = (($installmentMode ?? 'none') !== 'none');
                            @endphp

                            @if(isset($discountAmount) && $discountAmount > 0)
                                <tr class="block sm:table-row text-rose-600 dark:text-rose-400 bg-rose-50/30 dark:bg-rose-950/20 border border-rose-200/50 dark:border-rose-900/40 sm:border-0 rounded-2xl sm:rounded-none p-3.5 sm:p-0">
                                    @if($registration->registration_status !== 'completed')
                                        <td class="hidden sm:table-cell"></td>
                                    @endif
                                    <td class="block sm:table-cell p-0 sm:p-4 font-bold">
                                        <div class="flex items-center justify-between sm:justify-start gap-2">
                                            <span>Potongan Keringanan (Diskon)</span>
                                            <span class="sm:hidden text-[9px] bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 px-2 py-0.5 rounded font-bold">Diskon</span>
                                        </div>
                                    </td>
                                    <td class="block sm:table-cell p-0 pt-1.5 sm:p-4 text-right font-mono font-bold border-t border-rose-100 dark:border-rose-900/30 sm:border-0 mt-1.5 sm:mt-0">
                                        <div class="flex items-center justify-between sm:justify-end gap-2">
                                            <span class="sm:hidden text-[10px] text-rose-400 font-bold uppercase tracking-wider">Potongan:</span>
                                            <span>- Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
                                        </div>
                                    </td>
                                    <td class="hidden sm:table-cell p-4 text-center"><span class="text-[9px] bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 px-2 py-0.5 rounded font-bold">Diskon</span></td>
                                </tr>
                            @endif

                            {{-- Baris 'Telah Terbayar' hanya muncul jika skema cicilan adalah Cicilan Global (all) --}}
                            @if(($installmentMode ?? 'none') === 'all' && isset($totalPaid) && $totalPaid > 0)
                                <tr class="block sm:table-row text-emerald-600 dark:text-emerald-400 bg-emerald-50/30 dark:bg-emerald-950/20 border border-emerald-200/50 dark:border-emerald-900/40 sm:border-0 rounded-2xl sm:rounded-none p-3.5 sm:p-0">
                                    @if($registration->registration_status !== 'completed')
                                        <td class="hidden sm:table-cell"></td>
                                    @endif
                                    <td class="block sm:table-cell p-0 sm:p-4 font-bold">
                                        <div class="flex items-center justify-between sm:justify-start gap-2">
                                            <span>Telah Terbayar (Cicilan Global)</span>
                                            <span class="sm:hidden text-[9px] bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 px-2 py-0.5 rounded font-bold">Terbayar</span>
                                        </div>
                                    </td>
                                    <td class="block sm:table-cell p-0 pt-1.5 sm:p-4 text-right font-mono font-bold border-t border-emerald-100 dark:border-emerald-900/30 sm:border-0 mt-1.5 sm:mt-0">
                                        <div class="flex items-center justify-between sm:justify-end gap-2">
                                            <span class="sm:hidden text-[10px] text-emerald-500 font-bold uppercase tracking-wider">Terbayar:</span>
                                            <span>Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
                                        </div>
                                    </td>
                                    <td class="hidden sm:table-cell p-4 text-center"><span class="text-[9px] bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 px-2 py-0.5 rounded font-bold">Terbayar</span></td>
                                </tr>
                            @endif

                            {{-- Baris Sisa Tanggungan Keseluruhan --}}
                            <tr class="block sm:table-row bg-slate-100/80 dark:bg-slate-850 sm:bg-slate-50/50 sm:dark:bg-slate-950/30 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase border border-slate-200 dark:border-slate-800 sm:border-0 sm:border-t rounded-2xl sm:rounded-none p-3.5 sm:p-0 shadow-xs sm:shadow-none">
                                @if($registration->registration_status !== 'completed')
                                    <td class="hidden sm:table-cell"></td>
                                @endif
                                <td class="block sm:table-cell p-0 sm:p-4 font-extrabold">
                                    <div class="flex items-center justify-between sm:justify-start gap-2">
                                        <span>{{ ($isInstallmentActive && isset($totalPaid) && $totalPaid > 0) || (isset($discountAmount) && $discountAmount > 0) ? 'Sisa Tanggungan Keseluruhan' : 'Total Tanggungan' }}</span>
                                        @if($registration->registration_status === 'completed' || (isset($remainingBalance) && $remainingBalance <= 0))
                                            <span class="sm:hidden text-[9px] bg-green-500 text-white px-2.5 py-0.5 rounded font-bold uppercase tracking-wider shadow-xs">Lunas</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="block sm:table-cell p-0 pt-1.5 sm:p-4 text-right font-mono font-bold text-slate-850 dark:text-white border-t border-slate-200/60 dark:border-slate-800 sm:border-0 mt-1.5 sm:mt-0">
                                    <div class="flex items-center justify-between sm:justify-end gap-2">
                                        <span class="sm:hidden text-[10px] text-slate-400 font-bold uppercase tracking-wider">Total:</span>
                                        <span>Rp {{ number_format($remainingBalance ?? $netFee ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                </td>
                                <td class="hidden sm:table-cell p-4 text-center">
                                    @if($registration->registration_status === 'completed' || (isset($remainingBalance) && $remainingBalance <= 0))
                                        <span class="text-[10px] bg-green-500 text-white px-3 py-1 rounded font-bold uppercase tracking-wider shadow-sm">Lunas</span>
                                    @endif
                                </td>
                            </tr>

                            {{-- Baris Total Pembayaran Transaksi Ini (Hanya muncul jika belum lunas) --}}
                            @if($registration->registration_status !== 'completed' && (isset($remainingBalance) && $remainingBalance > 0))
                                <tr class="block sm:table-row bg-emerald-50 dark:bg-emerald-950/40 sm:bg-emerald-50/50 sm:dark:bg-emerald-950/20 text-xs font-black text-slate-850 dark:text-white uppercase border border-emerald-300/70 dark:border-emerald-800 sm:border-0 sm:border-t sm:border-emerald-100 sm:dark:border-emerald-900/40 rounded-2xl sm:rounded-none p-3.5 sm:p-0 shadow-xs sm:shadow-none">
                                    <td class="hidden sm:table-cell"></td>
                                    <td class="block sm:table-cell p-0 sm:p-4 text-brand-emerald dark:text-emerald-400">
                                        Total Pembayaran Transaksi Ini
                                    </td>
                                    <td class="block sm:table-cell p-0 pt-1.5 sm:p-4 text-right text-brand-emerald dark:text-emerald-400 text-sm font-extrabold font-mono border-t border-emerald-200/60 dark:border-emerald-900/40 sm:border-0 mt-1.5 sm:mt-0">
                                        <div class="flex items-center justify-between sm:justify-end gap-2">
                                            <span class="sm:hidden text-[10px] text-emerald-600 dark:text-emerald-400 font-bold uppercase tracking-wider">Bayar Sekarang:</span>
                                            <span id="total-amount-display">
                                                Rp {{ number_format($remainingBalance ?? $netFee ?? 0, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="hidden sm:table-cell p-4 text-center"></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div id="gateway-conflict-warning" class="hidden text-[11px] text-red-800 dark:text-red-300 font-extrabold bg-red-50 dark:bg-red-955/20 border border-red-200/50 dark:border-red-900/50 rounded-xl p-4 mt-4 flex items-center gap-2.5 shadow-sm leading-relaxed">
                    <i data-lucide="alert-triangle" class="w-4.5 h-4.5 text-red-600 flex-shrink-0 animate-bounce"></i>
                    <span>Komponen biaya yang dipilih tidak dapat dibayar bersamaan karena menggunakan metode pembayaran berbeda (misal BNI Snap saja & Winpay saja). Silakan centang item satu per satu.</span>
                </div>
            </div>

            <!-- INSTRUCTIONS BOX -->
            <div class="bg-slate-50 dark:bg-slate-955 rounded-2xl p-4 sm:p-6 border border-slate-100 dark:border-slate-800 space-y-3 sm:space-y-3.5 text-xs text-slate-600 dark:text-slate-400">
                <h5 class="font-extrabold text-slate-800 dark:text-white flex items-center gap-1.5 uppercase tracking-wider text-[10px]">
                    <i data-lucide="info" class="w-4 h-4 text-brand-emerald"></i> Informasi Penting & Prosedur Daftar Ulang
                </h5>
                <div class="instructions-body text-slate-650 dark:text-slate-350">
                    @if($registration->registration_status !== 'completed')
                        {!! $registration->unit?->re_registration_instructions_unpaid 
                            ?: \App\Models\Setting::get('re_registration_instructions_unpaid', '<ul><li><strong>Pembayaran Fleksibel:</strong> Anda dapat mencentang satu atau beberapa komponen biaya di atas untuk diangsur/dilunasi terlebih dahulu sesuai kelonggaran finansial Anda.</li><li><strong>Batas Pelunasan:</strong> Seluruh biaya administrasi wajib dilunasi sepenuhnya sebelum tahun ajaran baru dimulai.</li><li><strong>Metode Pembayaran:</strong> Klik tombol <strong>Lanjut Bayar</strong> di bawah untuk memilih metode transfer Virtual Account Bank (BNI) atau pemindaian kode QRIS secara instan.</li><li><strong>Daftar Ulang Resmi:</strong> Setelah seluruh komponen biaya di atas terkonfirmasi <strong>Lunas</strong> oleh sistem, calon siswa secara resmi terdaftar dan Anda dapat mencetak Surat Keterangan Penerimaan (SKP) langsung dari halaman ini.</li></ul>') !!}
                    @else
                        {!! $registration->unit?->re_registration_instructions_completed 
                            ?: \App\Models\Setting::get('re_registration_instructions_completed', '<ul><li><strong>Status Resmi:</strong> Selamat, ananda telah resmi menjadi bagian dari keluarga besar Sekolah Anak Saleh.</li><li><strong>Surat Keputusan Penerimaan (SKP):</strong> Anda dapat mengunduh dan mencetak surat kelulusan resmi menggunakan tombol cetak di bawah ini.</li><li><strong>Bukti Pembayaran:</strong> Silakan simpan / cetak kwitansi lunas elektronik sebagai tanda bukti setoran awal Anda yang sah.</li></ul>') !!}
                    @endif
                </div>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="pt-4 flex flex-col sm:flex-row justify-center items-center gap-4">
                @if($registration->registration_status === 'completed')
                    <!-- Completed buttons -->
                    <a href="{{ route('dashboard.admission-letter.download', $registration->id) }}" class="download-link-animate w-full sm:w-auto bg-brand-emerald hover-emerald text-white px-8 py-3.5 rounded-xl font-bold text-xs shadow-md transition flex items-center justify-center gap-2">
                        <i data-lucide="download" class="w-4.5 h-4.5"></i> Unduh Surat Kelulusan
                    </a>
                    @if(isset($successfulPayments) && $successfulPayments->count() > 1)
                        <button onclick="openReceiptsModal()" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-850 text-slate-700 dark:text-slate-300 px-8 py-3.5 rounded-xl font-bold text-xs shadow-sm transition flex items-center justify-center gap-2">
                            <i data-lucide="file-text" class="w-4.5 h-4.5 text-brand-emerald"></i> Unduh Kwitansi Pembayaran
                        </button>
                    @elseif(isset($successfulPayments) && $successfulPayments->count() === 1)
                        <a href="{{ route('dashboard.payment.receipt', $successfulPayments->first()->id) }}" class="download-link-animate w-full sm:w-auto border border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-850 text-slate-700 dark:text-slate-300 px-8 py-3.5 rounded-xl font-bold text-xs shadow-sm transition flex items-center justify-center gap-2">
                            <i data-lucide="file-text" class="w-4.5 h-4.5 text-brand-emerald"></i> Unduh Kwitansi Pembayaran
                        </a>
                    @endif
                @else
                    <!-- Unpaid buttons -->
                    <a href="{{ route('dashboard.payment', $registration->id) }}" id="payment-btn" data-base-url="{{ route('dashboard.payment', $registration->id) }}" class="w-full sm:w-auto bg-brand-emerald hover-emerald text-white px-8 py-3.5 rounded-xl font-bold text-xs shadow-md transition flex items-center justify-center gap-2">
                        <span class="btn-label-text flex items-center gap-2">
                            <i data-lucide="credit-card" class="w-4.5 h-4.5 text-brand-yellow animate-pulse"></i> Lanjut Bayar
                        </span>
                    </a>
                @endif
            </div>

        </div>
    </div>
</div>

<script>
    function formatRupiahInput(el) {
        let raw = el.value.replace(/\D/g, '');
        if (raw === '') {
            el.value = '';
            return;
        }
        let num = parseInt(raw, 10);
        el.value = num.toLocaleString('id-ID');
    }

    function toggleResultItemAccordion(idx) {
        const box = document.getElementById('accordion-input-box-' + idx);
        const chevron = document.getElementById('chevron-' + idx);
        const btnText = document.getElementById('btn-accordion-text-' + idx);
        const input = document.getElementById('input-amount-' + idx);
        const errorEl = document.getElementById('error-msg-' + idx);
        if (!box) return;

        if (box.classList.contains('hidden')) {
            box.classList.remove('hidden');
            if (chevron) chevron.classList.add('rotate-180');
            if (btnText) btnText.textContent = 'Tutup Pengaturan';
            if (errorEl) errorEl.classList.add('hidden');
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            if (input) {
                input.classList.remove('border-red-500', 'focus:ring-red-500');
                const appliedAmt = Number(input.getAttribute('data-applied-amount')) || 0;
                const maxVal = Number(input.getAttribute('data-max')) || 0;
                // If user has applied a specific partial amount, show it formatted. If default full, keep empty with placeholder
                if (appliedAmt < maxVal && appliedAmt > 0) {
                    input.value = appliedAmt.toLocaleString('id-ID');
                } else {
                    input.value = '';
                }
                input.focus();
            }
        } else {
            box.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
            if (input) {
                const appliedAmt = Number(input.getAttribute('data-applied-amount')) || 0;
                const maxVal = Number(input.getAttribute('data-max')) || 0;
                if (btnText) {
                    btnText.textContent = (appliedAmt < maxVal && appliedAmt > 0) 
                        ? 'Dicicil: Rp ' + appliedAmt.toLocaleString('id-ID') + ' (Ubah)' 
                        : 'Cicil Sebagian (Atur Nominal)';
                }
            }
        }
    }

    function resetResultItemFull(idx) {
        const input = document.getElementById('input-amount-' + idx);
        const display = document.getElementById('display-item-amount-' + idx);
        const errorEl = document.getElementById('error-msg-' + idx);
        const box = document.getElementById('accordion-input-box-' + idx);
        const chevron = document.getElementById('chevron-' + idx);
        const btnText = document.getElementById('btn-accordion-text-' + idx);
        const cb = document.getElementById('checkbox-item-' + idx);

        if (!input) return;

        const maxVal = Number(input.getAttribute('data-max')) || 0;

        // Reset applied amount to full remaining balance
        input.setAttribute('data-applied-amount', maxVal);
        input.value = '';

        if (errorEl) errorEl.classList.add('hidden');
        input.classList.remove('border-red-500', 'focus:ring-red-500');

        // Update display on table row to full amount
        if (display) {
            display.textContent = 'Rp ' + maxVal.toLocaleString('id-ID');
        }

        // Reset label and close accordion
        if (box) box.classList.add('hidden');
        if (chevron) chevron.classList.remove('rotate-180');
        if (btnText) {
            btnText.textContent = 'Cicil Sebagian (Atur Nominal)';
        }

        // Ensure checkbox remains checked
        if (cb && !cb.checked) {
            cb.checked = true;
        }

        // Recalculate grand total
        calculateTotal();
    }

    function applyResultItemInstallment(idx) {
        const input = document.getElementById('input-amount-' + idx);
        const display = document.getElementById('display-item-amount-' + idx);
        const cb = document.getElementById('checkbox-item-' + idx);
        const errorEl = document.getElementById('error-msg-' + idx);
        const box = document.getElementById('accordion-input-box-' + idx);
        const chevron = document.getElementById('chevron-' + idx);
        const btnText = document.getElementById('btn-accordion-text-' + idx);

        if (!input) return;

        let raw = input.value.replace(/\D/g, '');
        let val = parseInt(raw, 10) || 0;
        const minVal = Number(input.getAttribute('data-min')) || 0;
        const maxVal = Number(input.getAttribute('data-max')) || 0;

        if (val === 0 || isNaN(val)) {
            if (errorEl) {
                errorEl.textContent = 'Silakan ketik nominal cicilan terlebih dahulu.';
                errorEl.classList.remove('hidden');
            }
            input.classList.add('border-red-500', 'focus:ring-red-500');
            input.focus();
            return; // Do NOT close accordion
        }

        if (val < minVal) {
            if (errorEl) {
                errorEl.textContent = 'Nominal minimal cicilan adalah Rp ' + minVal.toLocaleString('id-ID');
                errorEl.classList.remove('hidden');
            }
            input.classList.add('border-red-500', 'focus:ring-red-500');
            input.focus();
            return; // Do NOT close accordion
        }

        if (val > maxVal) {
            if (errorEl) {
                errorEl.textContent = 'Nominal melebihi sisa tanggungan (Maksimal Rp ' + maxVal.toLocaleString('id-ID') + ')';
                errorEl.classList.remove('hidden');
            }
            input.classList.add('border-red-500', 'focus:ring-red-500');
            input.focus();
            return; // Do NOT close accordion
        }

        // Valid
        if (errorEl) errorEl.classList.add('hidden');
        input.classList.remove('border-red-500', 'focus:ring-red-500');

        // Save applied amount & format
        input.setAttribute('data-applied-amount', val);
        input.value = val.toLocaleString('id-ID');

        // Update display on table row
        if (display) {
            display.textContent = 'Rp ' + val.toLocaleString('id-ID');
        }

        // Ensure checkbox is checked
        if (cb && !cb.checked) {
            cb.checked = true;
        }

        // Update button label and close accordion
        if (box) box.classList.add('hidden');
        if (chevron) chevron.classList.remove('rotate-180');
        if (btnText) {
            btnText.textContent = (val < maxVal) 
                ? 'Dicicil: Rp ' + val.toLocaleString('id-ID') + ' (Ubah)' 
                : 'Cicil Sebagian (Atur Nominal)';
        }

        // Calculate new total
        calculateTotal();
    }

    function onFeeCheckboxChange(idx) {
        const cb = document.getElementById('checkbox-item-' + idx);
        const box = document.getElementById('accordion-input-box-' + idx);
        const chevron = document.getElementById('chevron-' + idx);
        const btnText = document.getElementById('btn-accordion-text-' + idx);
        const input = document.getElementById('input-amount-' + idx);

        if (cb && !cb.checked) {
            if (box) box.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
            if (btnText) btnText.textContent = 'Cicil Sebagian (Atur Nominal)';
        }

        calculateTotal();
    }

    function calculateTotal() {
        const checkboxes = document.querySelectorAll('.fee-checkbox');
        const totalDisplay = document.getElementById('total-amount-display');
        const paymentBtn = document.getElementById('payment-btn');
        const totalBadge = document.getElementById('total-status-badge');
        if (!paymentBtn) return;

        const baseUrl = paymentBtn.getAttribute('data-base-url');
        let total = 0;
        const queryPairs = [];
        let commonGateways = null;
        let checkedCount = 0;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                checkedCount++;
                const idx = cb.getAttribute('data-index');
                const input = document.getElementById('input-amount-' + idx);
                let itemAmount = 0;
                if (input) {
                    itemAmount = Number(input.getAttribute('data-applied-amount')) || Number(cb.getAttribute('data-amount')) || 0;
                } else {
                    itemAmount = Number(cb.getAttribute('data-amount')) || 0;
                }

                total += itemAmount;
                queryPairs.push(idx + ':' + itemAmount);

                const itemGateways = JSON.parse(cb.getAttribute('data-gateways') || '[]');
                if (commonGateways === null) {
                    commonGateways = [...itemGateways];
                } else {
                    commonGateways = commonGateways.filter(gw => itemGateways.includes(gw));
                }
            }
        });

        // Update total display in footer
        if (totalDisplay) {
            totalDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        // Update dynamic URL and button text
        paymentBtn.href = baseUrl + '?items=' + queryPairs.join(',');
        const btnLabel = paymentBtn.querySelector('.btn-label-text');
        if (btnLabel) {
            btnLabel.innerHTML = '<i data-lucide="credit-card" class="w-4.5 h-4.5 text-brand-yellow animate-pulse"></i> Lanjut Bayar (Rp ' + total.toLocaleString('id-ID') + ')';
        }

        const warningBox = document.getElementById('gateway-conflict-warning');
        const hasConflict = checkedCount > 0 && commonGateways && commonGateways.length === 0;

        if (hasConflict) {
            if (warningBox) warningBox.classList.remove('hidden');
            paymentBtn.classList.add('opacity-50', 'pointer-events-none');
        } else {
            if (warningBox) warningBox.classList.add('hidden');
            if (checkedCount === 0) {
                paymentBtn.classList.add('opacity-50', 'pointer-events-none');
                if (btnLabel) {
                    btnLabel.innerHTML = '<i data-lucide="credit-card" class="w-4.5 h-4.5 text-brand-yellow"></i> Pilih Komponen Biaya';
                }
            } else {
                paymentBtn.classList.remove('opacity-50', 'pointer-events-none');
            }
        }

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initial calculation on render
        calculateTotal();

        // Handle loading animations for download buttons (placed outside check block so it runs unconditionally)
        document.querySelectorAll('.download-link-animate').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const originalHref = this.getAttribute('href');
                const originalContent = this.innerHTML;
                
                // Generate a unique token
                const token = 'dt_' + Date.now();
                const downloadUrl = originalHref + (originalHref.includes('?') ? '&' : '?') + 'download_token=' + token;
                
                // Show spinner animation
                this.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="animate-spin h-3.5 w-3.5 text-brand-emerald" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Mohon Tunggu...</span>';
                this.style.pointerEvents = 'none';
                
                // Start the download
                window.location.href = downloadUrl;
                
                // Poll for the cookie
                const cookieName = 'download_status_' + token;
                const checkInterval = setInterval(() => {
                    const cookies = document.cookie.split(';');
                    let cookieFound = false;
                    for (let i = 0; i < cookies.length; i++) {
                        const c = cookies[i].trim();
                        if (c.indexOf(cookieName + '=') === 0) {
                            cookieFound = true;
                            // Delete the cookie
                            document.cookie = cookieName + '=; Max-Age=-99999999; path=/;';
                            break;
                        }
                    }
                    
                    if (cookieFound) {
                        clearInterval(checkInterval);
                        this.innerHTML = originalContent;
                        this.style.pointerEvents = '';
                    }
                }, 150);
                
                // Safety timeout fallback (15 seconds) in case of network/render errors
                setTimeout(() => {
                    clearInterval(checkInterval);
                    if (this.style.pointerEvents === 'none') {
                        this.innerHTML = originalContent;
                        this.style.pointerEvents = '';
                    }
                }, 15000);
            });
        });
    });
</script>

<!-- Receipts Modal -->
@if(isset($successfulPayments) && $successfulPayments->count() > 1)
    <div id="receiptsModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeReceiptsModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-middle bg-white dark:bg-slate-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100 dark:border-slate-800">
                <div class="bg-white dark:bg-slate-900 px-6 pt-6 pb-4 sm:p-8">
                    <h3 class="text-base font-extrabold text-slate-800 dark:text-white mb-4 flex items-center gap-2" id="modal-title">
                        <i data-lucide="file-text" class="w-5 h-5 text-brand-emerald"></i>
                        Pilih Kwitansi Pembayaran
                    </h3>
                    <div class="space-y-3">
                        @foreach($successfulPayments as $index => $p)
                            @php
                                $itemNames = ($p->items && $p->items->isNotEmpty())
                                    ? $p->items->pluck('fee_name')->implode(', ')
                                    : collect($p->payment_info['selected_items'] ?? [])->pluck('name')->implode(', ');
                            @endphp
                            <div class="border border-slate-150 dark:border-slate-800 rounded-2xl p-4 flex justify-between items-center bg-slate-50/50 dark:bg-slate-950/20">
                                <div class="space-y-1">
                                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Kwitansi #{{ $index + 1 }}</span>
                                    <span class="text-xs font-bold text-slate-800 dark:text-white block leading-tight">{{ $itemNames ?: 'Biaya Administrasi Akhir' }}</span>
                                    <span class="text-[10px] text-brand-emerald font-extrabold block">Rp {{ number_format($p->amount, 0, ',', '.') }}</span>
                                </div>
                                <a href="{{ route('dashboard.payment.receipt', $p->id) }}" class="download-link-animate bg-white hover:bg-slate-50 border border-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-200 px-3 py-2 rounded-xl text-[10px] font-bold shadow-sm transition flex items-center gap-1">
                                    <i data-lucide="download" class="w-3.5 h-3.5 text-brand-emerald"></i> Unduh
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-900 px-6 py-4 sm:px-8 sm:py-5 flex justify-end border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="closeReceiptsModal()" class="border border-slate-200 dark:border-slate-700 text-slate-650 dark:text-slate-400 px-5 py-2.5 rounded-xl text-xs font-bold transition hover:bg-slate-100 dark:hover:bg-slate-800">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
        function openReceiptsModal() {
            document.getElementById('receiptsModal').classList.remove('hidden');
        }
        function closeReceiptsModal() {
            document.getElementById('receiptsModal').classList.add('hidden');
        }
    </script>
@endif
@endsection
