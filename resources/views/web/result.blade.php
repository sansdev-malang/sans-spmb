@extends('layouts.portal')

@section('title', 'Hasil Seleksi & Administrasi Akhir - Portal SPMB')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8 space-y-6">
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

    <!-- MAIN CARD -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-150/80 dark:border-slate-800 overflow-hidden">
        
        <!-- CARD HEADER -->
        <div class="bg-brand-emerald text-white px-6 py-5 flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-lg flex items-center gap-2">
                    <i data-lucide="award" class="w-5 h-5 text-brand-yellow"></i>
                    Hasil Seleksi & Administrasi Akhir
                </h2>
                <div class="flex flex-wrap items-center gap-2 mt-1">
                    <p class="text-xs text-brand-yellow/90 font-medium">
                        Pengumuman kelulusan resmi dan rincian pembiayaan pendidikan.
                    </p>
                </div>
            </div>
            @if($registration->registration_status === 'completed')
                <span class="bg-green-700 text-white font-bold text-[10px] uppercase tracking-widest px-3.5 py-1.5 rounded-full border border-green-500 shadow-sm flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-300 animate-ping"></span> Lunas & Resmi
                </span>
            @else
                <span class="bg-amber-600 text-white font-bold text-[10px] uppercase tracking-widest px-3.5 py-1.5 rounded-full border border-amber-455 shadow-sm">
                    Menunggu Pelunasan
                </span>
            @endif
        </div>

        <div class="p-8 space-y-8">
            
            <!-- ANNOUNCEMENT BANNER -->
            <div class="bg-gradient-to-r from-emerald-50 to-emerald-100/50 dark:from-emerald-950/10 dark:to-emerald-900/5 border border-emerald-200/60 dark:border-emerald-900/50 rounded-2xl p-6 flex flex-col sm:flex-row gap-5 items-center text-center sm:text-left">
                <div class="h-16 w-16 bg-brand-emerald text-white rounded-2xl flex items-center justify-center shadow-md flex-shrink-0">
                    <i data-lucide="party-popper" class="w-8 h-8 text-brand-yellow"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-lg font-black text-slate-850 dark:text-white">Alhamdulillah, Dinyatakan LULUS & DITERIMA</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Selamat kepada ananda <strong class="text-slate-800 dark:text-slate-200">{{ $registration->candidate_name }}</strong> yang telah lolos seluruh tahapan observasi kesiapan belajar dan berkas pendaftaran.
                    </p>
                </div>
            </div>

            <!-- STUDENT PROFILE META -->
            <div class="bg-slate-50 dark:bg-slate-955 rounded-2xl p-6 border border-slate-100 dark:border-slate-850 grid grid-cols-2 sm:grid-cols-3 gap-x-8 gap-y-5 text-xs">
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">No. Registrasi</span>
                    <span class="font-extrabold text-brand-emerald dark:text-emerald-450 mt-1 block">SANS-{{ substr($registration->period->year ?? '2026', 0, 4) }}-{{ str_pad($registration->id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Nama Calon Siswa</span>
                    <span class="font-extrabold text-slate-800 dark:text-slate-200 mt-1 block">{{ $registration->candidate_name }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Tingkat / Unit</span>
                    <span class="font-extrabold text-slate-800 dark:text-slate-200 mt-1 block">{{ $registration->admission_level }} - {{ $registration->unit->name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Program Kelas</span>
                    <span class="font-extrabold text-slate-800 dark:text-slate-200 mt-1 block">{{ $registration->classProgram->name ?? 'Reguler' }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Layanan Tambahan</span>
                    @if($registration->extraServices->count() > 0)
                        <ul class="list-disc pl-3.5 mt-1 font-extrabold text-brand-emerald dark:text-emerald-450 space-y-0.5">
                            @foreach($registration->extraServices as $service)
                                <li>{{ $service->name }}</li>
                            @endforeach
                        </ul>
                    @else
                        <span class="font-extrabold text-slate-500 dark:text-slate-400 mt-1 block">Tidak Ada</span>
                    @endif
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Tahun Pelajaran</span>
                    <span class="font-extrabold text-slate-800 dark:text-slate-200 mt-1 block">{{ $registration->period->year ?? '2027-2028' }}</span>
                </div>
            </div>

            <!-- TUITION FEES COMPONENT BREAKDOWN -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h4 class="font-extrabold text-slate-850 dark:text-white text-xs uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="receipt" class="w-4 h-4 text-brand-emerald"></i> Rincian Biaya Pendidikan Masuk Awal
                    </h4>
                    @if(isset($discountAmount) && ($discountAmount > 0 || ($installmentMode ?? 'none') !== 'none'))
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-300/40">
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

                <div class="bg-white dark:bg-slate-900 border border-slate-150/80 dark:border-slate-800 rounded-2xl overflow-hidden shadow-inner">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950 text-[10px] text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-850">
                                @if($registration->registration_status !== 'completed')
                                    <th class="p-4 text-center w-12 select-none">Pilih</th>
                                @endif
                                <th class="p-4">Komponen Pembiayaan</th>
                                <th class="p-4 text-right">Nominal</th>
                                <th class="p-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
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
                                        $isPaid = isset($paidItemNames) && in_array($itemName, $paidItemNames);
                                        
                                        // Find payment receipt for this specific item
                                        $itemPayment = null;
                                        if (isset($successfulPayments)) {
                                            foreach ($successfulPayments as $p) {
                                                if ($p->items && $p->items->isNotEmpty()) {
                                                    foreach ($p->items as $pItem) {
                                                        if (($itemId && (int)$pItem->spmb_fee_id === (int)$itemId) || strcasecmp(trim($pItem->fee_name), trim($itemName)) === 0) {
                                                            $itemPayment = $p;
                                                            break 2;
                                                        }
                                                    }
                                                } elseif (isset($p->payment_info['selected_items']) && is_array($p->payment_info['selected_items'])) {
                                                    foreach ($p->payment_info['selected_items'] as $si) {
                                                        if (($itemId && isset($si['id']) && (int)$si['id'] === (int)$itemId) || strcasecmp(trim($si['name'] ?? ''), trim($itemName)) === 0) {
                                                            $itemPayment = $p;
                                                            break 2;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    @endphp
                                    @php
                                        $itemGross = (float) ($item['amount'] ?? 0);
                                        $itemDiscount = $registration->getItemDiscountAmount($item['name'], $item['id'] ?? null);
                                        $itemNet = max(0, $itemGross - $itemDiscount);
                                        $itemPaid = $registration->getItemPaidAmount($item['name'], $item['id'] ?? null);
                                        $itemRemaining = max(0, $itemNet - $itemPaid);
                                        $isItemLunas = ($isPaid || $itemRemaining <= 0);
                                        $canCicil = (!$isItemLunas) && (!empty($item['is_installment_allowed']) || ($installmentMode ?? 'none') === 'all');
                                        $minItemInstallment = min($itemRemaining, (float) ($registration->min_installment_amount ?: 500000));
                                    @endphp
                                    <tr class="text-slate-650 dark:text-slate-350 {{ $isItemLunas ? 'bg-slate-50/40 dark:bg-slate-950/10' : '' }}">
                                        @if($registration->registration_status !== 'completed')
                                            <td class="p-4 text-center align-top pt-4.5">
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
                                        <td class="p-4 font-medium {{ $isItemLunas ? 'text-slate-400' : '' }}">
                                            <div class="flex flex-col gap-0.5">
                                                <div class="flex items-center gap-2 {{ $isItemLunas ? 'line-through' : '' }}">
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

                                                @if($itemDiscount > 0)
                                                    <div class="text-[10px] text-rose-600 dark:text-rose-400 font-semibold flex items-center gap-1 mt-0.5">
                                                        <i data-lucide="tag" class="w-3 h-3"></i> Diskon Khusus: - Rp {{ number_format($itemDiscount, 0, ',', '.') }} (Tarif Asli: Rp {{ number_format($itemGross, 0, ',', '.') }})
                                                    </div>
                                                @endif

                                                @if($isItemLunas && $itemPayment)
                                                    <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1 mt-0.5">
                                                        <i data-lucide="check-check" class="w-3.5 h-3.5 text-emerald-500"></i> Terbayar Lunas via {{ $itemPayment->channel_display_name }} ({{ $itemPayment->created_at ? $itemPayment->created_at->format('d M Y') : '' }})
                                                    </div>
                                                @elseif(!$isItemLunas && $itemPaid > 0)
                                                    <div class="text-[10px] text-slate-400 font-medium">
                                                        Total: Rp {{ number_format($itemNet, 0, ',', '.') }} • Telah Dicicil: <span class="text-emerald-600 font-bold">Rp {{ number_format($itemPaid, 0, ',', '.') }}</span>
                                                    </div>
                                                @endif

                                                {{-- Accordion Input Cicilan pada Item yang Boleh Dicicil --}}
                                                @if(!$isItemLunas && $canCicil)
                                                    <div class="mt-2" id="cicil-control-wrapper-{{ $loop->index }}">
                                                        <button type="button" onclick="toggleResultItemAccordion({{ $loop->index }})" class="inline-flex items-center gap-1.5 text-[11px] font-bold text-brand-emerald hover:underline select-none">
                                                            <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>
                                                            <span id="btn-accordion-text-{{ $loop->index }}">Cicil Sebagian (Atur Nominal)</span>
                                                            <i data-lucide="chevron-down" id="chevron-{{ $loop->index }}" class="w-3 h-3 transition-transform duration-200"></i>
                                                        </button>

                                                        <div id="accordion-input-box-{{ $loop->index }}" class="hidden mt-2 p-3 bg-emerald-50/60 dark:bg-emerald-950/20 rounded-xl border border-emerald-200/60 dark:border-emerald-900/40 space-y-2 max-w-md">
                                                            <div class="flex items-center justify-between">
                                                                <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Nominal Bayar Tahap Ini</span>
                                                                <span class="text-[9px] text-slate-500 font-mono">Batas Min: <strong class="text-emerald-700 dark:text-emerald-400 font-mono">Rp {{ number_format($minItemInstallment, 0, ',', '.') }}</strong></span>
                                                            </div>
                                                            <div class="flex items-center gap-2">
                                                                <div class="relative flex-1">
                                                                    <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-[11px] font-bold text-slate-400 font-mono">Rp</span>
                                                                    <input type="text"
                                                                        inputmode="numeric" 
                                                                        id="input-amount-{{ $loop->index }}"
                                                                        class="result-item-amount-input w-full pl-8 pr-2.5 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-bold font-mono text-slate-850 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition"
                                                                        placeholder="Contoh: {{ number_format($minItemInstallment, 0, ',', '.') }}"
                                                                        value=""
                                                                        data-applied-amount="{{ $itemRemaining }}"
                                                                        data-index="{{ $loop->index }}"
                                                                        data-max="{{ $itemRemaining }}"
                                                                        data-min="{{ $minItemInstallment }}"
                                                                        oninput="formatRupiahInput(this)"
                                                                        onkeydown="if(event.key === 'Enter'){ event.preventDefault(); applyResultItemInstallment({{ $loop->index }}); }">
                                                                </div>
                                                                <button type="button" onclick="applyResultItemInstallment({{ $loop->index }})" class="px-4 py-1.5 bg-brand-emerald hover-emerald text-white rounded-lg text-xs font-bold transition shadow-sm select-none">
                                                                    OK
                                                                </button>
                                                                <button type="button" onclick="resetResultItemFull({{ $loop->index }})" class="px-2.5 py-1.5 bg-slate-200/80 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-bold transition shadow-sm select-none whitespace-nowrap" title="Batalkan cicilan dan bayar penuh">
                                                                    Bayar Penuh
                                                                </button>
                                                            </div>
                                                            <p id="error-msg-{{ $loop->index }}" class="hidden text-[10px] text-red-600 font-semibold leading-tight"></p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="p-4 text-right font-bold {{ $isItemLunas ? 'text-slate-400' : 'text-slate-800 dark:text-slate-200' }} align-top pt-4.5">
                                            <span id="display-item-amount-{{ $loop->index }}" class="font-mono">
                                                Rp {{ number_format($isItemLunas ? $itemNet : $itemRemaining, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="p-4 text-center align-top pt-4.5">
                                            @if($isItemLunas || $registration->registration_status === 'completed')
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <span class="text-[9px] bg-green-50 dark:bg-green-950/20 text-green-600 px-2 py-0.5 rounded font-bold uppercase tracking-wider select-none">Lunas</span>
                                                    @if($itemPayment)
                                                        <a href="{{ route('dashboard.payment.receipt', $itemPayment->id) }}" target="_blank" download class="download-link-animate inline-flex items-center gap-0.5 text-[9px] font-bold text-brand-emerald hover:underline" title="Unduh Kwitansi">
                                                            <i data-lucide="download" class="w-3 h-3 text-brand-emerald"></i> Unduh
                                                        </a>
                                                    @endif
                                                </div>
                                            @elseif($itemPaid > 0)
                                                <span class="text-[9px] bg-blue-50 dark:bg-blue-950/20 text-blue-600 px-2 py-0.5 rounded font-bold uppercase tracking-wider select-none">Dicicil</span>
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
                                <tr class="text-rose-600 dark:text-rose-400 bg-rose-50/20">
                                    @if($registration->registration_status !== 'completed')
                                        <td></td>
                                    @endif
                                    <td class="p-4 font-bold">Potongan Keringanan (Diskon)</td>
                                    <td class="p-4 text-right font-mono font-bold">- Rp {{ number_format($discountAmount, 0, ',', '.') }}</td>
                                    <td class="p-4 text-center"><span class="text-[9px] bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 px-2 py-0.5 rounded font-bold">Diskon</span></td>
                                </tr>
                            @endif

                            {{-- Baris 'Telah Terbayar' hanya muncul jika skema cicilan adalah Cicilan Global (all) --}}
                            @if(($installmentMode ?? 'none') === 'all' && isset($totalPaid) && $totalPaid > 0)
                                <tr class="text-emerald-600 dark:text-emerald-400 bg-emerald-50/20">
                                    @if($registration->registration_status !== 'completed')
                                        <td></td>
                                    @endif
                                    <td class="p-4 font-bold">Telah Terbayar (Cicilan Global)</td>
                                    <td class="p-4 text-right font-mono font-bold">Rp {{ number_format($totalPaid, 0, ',', '.') }}</td>
                                    <td class="p-4 text-center"><span class="text-[9px] bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 px-2 py-0.5 rounded font-bold">Terbayar</span></td>
                                </tr>
                            @endif

                            {{-- Baris Sisa Tanggungan Keseluruhan --}}
                            <tr class="bg-slate-50/50 dark:bg-slate-950/30 text-xs font-bold text-slate-700 dark:text-slate-300 uppercase border-t border-slate-200 dark:border-slate-800">
                                @if($registration->registration_status !== 'completed')
                                    <td></td>
                                @endif
                                <td class="p-4 font-extrabold">
                                    {{ ($isInstallmentActive && isset($totalPaid) && $totalPaid > 0) || (isset($discountAmount) && $discountAmount > 0) ? 'Sisa Tanggungan Keseluruhan' : 'Total Tanggungan' }}
                                </td>
                                <td class="p-4 text-right font-mono font-bold text-slate-850 dark:text-white">
                                    Rp {{ number_format($remainingBalance ?? $netFee ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="p-4 text-center">
                                    @if($registration->registration_status === 'completed' || (isset($remainingBalance) && $remainingBalance <= 0))
                                        <span class="text-[10px] bg-green-500 text-white px-3 py-1 rounded font-bold uppercase tracking-wider shadow-sm">Lunas</span>
                                    @endif
                                </td>
                            </tr>

                            {{-- Baris Total Pembayaran Transaksi Ini (Hanya muncul jika belum lunas) --}}
                            @if($registration->registration_status !== 'completed' && (isset($remainingBalance) && $remainingBalance > 0))
                                <tr class="bg-emerald-50/50 dark:bg-emerald-950/20 text-xs font-black text-slate-850 dark:text-white uppercase border-t border-emerald-100 dark:border-emerald-900/40">
                                    <td></td>
                                    <td class="p-4 text-brand-emerald dark:text-emerald-400">
                                        Total Pembayaran Transaksi Ini
                                    </td>
                                    <td class="p-4 text-right text-brand-emerald dark:text-emerald-400 text-sm font-extrabold font-mono" id="total-amount-display">
                                        Rp {{ number_format($remainingBalance ?? $netFee ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="p-4 text-center"></td>
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
            <div class="bg-slate-50 dark:bg-slate-955 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 space-y-3.5 text-xs text-slate-600 dark:text-slate-400">
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
