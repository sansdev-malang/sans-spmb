<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Pembayaran #{{ $payment->invoice_number }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            emerald: '#059669',
                            yellow: '#f59e0b',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
                color: black !important;
            }
            .print-border {
                border: 1px solid #e2e8f0 !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen py-10 antialiased">

    <div class="max-w-xl mx-auto px-4">
        
        <!-- Action Buttons (No Print) -->
        <div class="no-print flex justify-between items-center mb-6">
            <a href="{{ route('dashboard.payment', $registration->id) }}" class="flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Pembayaran
            </a>
            <button onclick="window.print()" class="flex items-center gap-1.5 bg-brand-emerald hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-md transition">
                <i data-lucide="printer" class="w-4 h-4"></i> Cetak Kwitansi
            </button>
        </div>

        <!-- Receipt Card Container -->
        <div class="bg-white print-border rounded-3xl shadow-lg border border-slate-150 overflow-hidden relative p-8 md:p-10 text-left">
            
            @php
                $isFormPayment = ($payment->payment_type === 'registration_fee');
                $allFinalPayments = $registration->payments()
                    ->where('status', 'success')
                    ->where('payment_type', 'final_fee')
                    ->orderBy('created_at')
                    ->get();

                // Extract items in current payment
                $currentPaymentItems = [];
                if ($payment->items && $payment->items->isNotEmpty()) {
                    foreach ($payment->items as $it) {
                        $currentPaymentItems[] = [
                            'name' => $it->fee_name,
                            'amount' => (float) $it->amount,
                        ];
                    }
                } elseif (isset($payment->payment_info['selected_items']) && is_array($payment->payment_info['selected_items'])) {
                    foreach ($payment->payment_info['selected_items'] as $it) {
                        $currentPaymentItems[] = [
                            'name' => $it['name'] ?? '',
                            'amount' => (float) ($it['amount'] ?? 0),
                        ];
                    }
                }

                // Check whether any item in this payment is part of an installment plan
                $hasAnyInstallmentItem = false;
                $primaryItemInstallmentNo = 1;
                $annotatedItems = [];

                foreach ($currentPaymentItems as $cItem) {
                    $cName = $cItem['name'];
                    $itemGross = 0;
                    $itemDiscount = $registration->getItemDiscountAmount($cName);
                    if (isset($feeDetails['items']) && is_array($feeDetails['items'])) {
                        foreach ($feeDetails['items'] as $fdItem) {
                            if (strcasecmp(trim($fdItem['name']), trim($cName)) === 0) {
                                $itemGross = (float)($fdItem['amount'] ?? 0);
                                break;
                            }
                        }
                    }
                    $itemNet = max(0, $itemGross - $itemDiscount);

                    $itemHistory = [];
                    $cumulativePaidUpToThis = 0;
                    $isPaidAfterThis = false;

                    foreach ($allFinalPayments as $p) {
                        $foundAmt = 0;
                        if ($p->items && $p->items->isNotEmpty()) {
                            foreach ($p->items as $pi) {
                                if (strcasecmp(trim($pi->fee_name), trim($cName)) === 0) {
                                    $foundAmt += (float) $pi->amount;
                                }
                            }
                        } elseif (isset($p->payment_info['selected_items']) && is_array($p->payment_info['selected_items'])) {
                            foreach ($p->payment_info['selected_items'] as $si) {
                                if (strcasecmp(trim($si['name'] ?? ''), trim($cName)) === 0) {
                                    $foundAmt += (float) ($si['amount'] ?? 0);
                                }
                            }
                        }
                        if ($foundAmt > 0) {
                            $itemHistory[] = $p->id;
                            if (!$isPaidAfterThis) {
                                $cumulativePaidUpToThis += $foundAmt;
                            }
                            if ($p->id === $payment->id) {
                                $isPaidAfterThis = true;
                            }
                        }
                    }

                    $isItemInstallment = count($itemHistory) > 1;
                    $itemInstIndex = array_search($payment->id, $itemHistory);
                    $itemInstNo = ($itemInstIndex !== false) ? ($itemInstIndex + 1) : 1;
                    $itemRemainingAfterThis = max(0, $itemNet - $cumulativePaidUpToThis);

                    if ($isItemInstallment) {
                        $hasAnyInstallmentItem = true;
                        $primaryItemInstallmentNo = $itemInstNo;
                    }

                    $annotatedItems[] = [
                        'name' => $cName,
                        'amount' => $cItem['amount'],
                        'is_installment' => $isItemInstallment,
                        'installment_no' => $itemInstNo,
                        'item_net' => $itemNet,
                        'item_remaining' => $itemRemainingAfterThis,
                    ];
                }

                $isFullySettled = ($registration->registration_status === 'completed' || $registration->remaining_balance <= 0);
            @endphp
            <!-- Receipt Header / Branding -->
            <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pb-6 border-b border-slate-100">
                <div>
                    <h1 class="text-lg font-extrabold text-slate-900 leading-tight">
                        {{ \App\Models\Setting::get('school_name', 'Sekolah Anak Saleh') }}
                    </h1>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">
                        Sistem Penerimaan Murid Baru (SPMB)
                    </p>
                </div>
                <div class="text-right">
                    @if($hasAnyInstallmentItem)
                        <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i> ANGSURAN KE-{{ $primaryItemInstallmentNo }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 bg-green-100 text-green-700 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> LUNAS
                        </span>
                    @endif
                </div>
            </div>

            <!-- Receipt Title -->
            <div class="py-6 text-center">
                @if($isFormPayment)
                    <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest">Bukti Pembayaran Biaya Pendaftaran</h2>
                @elseif($hasAnyInstallmentItem)
                    <h2 class="text-sm font-black text-blue-600 uppercase tracking-widest">Bukti Pembayaran Angsuran Ke-{{ $primaryItemInstallmentNo }}</h2>
                @else
                    <h2 class="text-sm font-black text-slate-400 uppercase tracking-widest">Bukti Pembayaran Resmi</h2>
                @endif
                <p class="text-xs text-slate-500 font-bold mt-1">Nomor: {{ $payment->invoice_number }}</p>
            </div>

            <!-- Invoice Details Metadata -->
            <div class="grid grid-cols-2 gap-y-4 gap-x-2 text-xs border-b border-dashed border-slate-200 pb-6 mb-6">
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Tanggal Transaksi</span>
                    <span class="font-bold text-slate-800 mt-0.5 block">
                        {{ $payment->updated_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Metode Pembayaran</span>
                    <span class="font-bold text-slate-800 mt-0.5 block">
                        {{ $payment->payment_method }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Nama Calon Siswa</span>
                    <span class="font-bold text-slate-850 mt-0.5 block">
                        {{ $registration->candidate_name ?? 'Calon Siswa' }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Unit Pendidikan</span>
                    <span class="font-bold text-slate-800 mt-0.5 block">
                        {{ $registration->unit->name ?? '-' }}@if(!empty($registration->grade->name)) ({{ $registration->grade->name }})@endif
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Tahun Ajaran & Gelombang</span>
                    <span class="font-bold text-slate-800 mt-0.5 block">
                        {{ $registration->period->year ?? '-' }} - {{ $registration->wave->name ?? '-' }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Jalur & Program Kelas</span>
                    <span class="font-bold text-slate-800 mt-0.5 block">
                        {{ $registration->type->name ?? '-' }}@if(!empty($registration->classProgram->name)) ({{ $registration->classProgram->name }})@endif
                    </span>
                </div>
                @if($registration->extraServices->count() > 0)
                    <div class="col-span-2 border-t border-slate-100/80 pt-2.5 mt-0.5">
                        <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Layanan Tambahan</span>
                        <span class="font-extrabold text-brand-emerald mt-0.5 block">
                            {{ $registration->extraServices->pluck('name')->implode(', ') }}
                        </span>
                    </div>
                @endif
            </div>

            <!-- Pricing Details Table -->
            <div class="space-y-3 mb-8">
                <span class="text-[10px] text-slate-400 font-black uppercase tracking-wider block mb-2">Rincian Pembayaran</span>
                
                @if(!empty($annotatedItems))
                    @foreach($annotatedItems as $it)
                        <div class="flex justify-between items-center text-xs text-slate-600">
                            <span>
                                {{ $it['name'] }}
                                @if($it['is_installment'])
                                    <span class="text-[10px] text-blue-600 font-semibold">(Setoran Angsuran #{{ $it['installment_no'] }})</span>
                                @endif
                            </span>
                            <span class="font-bold text-slate-800">
                                Rp {{ number_format($it['amount'], 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                @else
                    @php
                        $feeCategory = \App\Models\SpmbFeeCategory::where('name', 'Formulir Pendaftaran')->first();
                        $fee = null;
                        if ($feeCategory && $registration) {
                            if ($registration->spmb_unit_id) {
                                $fee = \App\Models\SpmbFee::where('spmb_fee_category_id', $feeCategory->id)
                                    ->where('spmb_unit_id', $registration->spmb_unit_id)
                                    ->where('is_active', true)
                                    ->first()
                                    ?? \App\Models\SpmbFee::where('spmb_fee_category_id', $feeCategory->id)
                                    ->where('spmb_unit_id', $registration->spmb_unit_id)
                                    ->first();
                            }
                            if (!$fee) {
                                $admissionLevel = $registration->admission_level ?: ($registration->grade->name ?? '');
                                $fee = \App\Models\SpmbFee::where('spmb_fee_category_id', $feeCategory->id)
                                    ->where(function($q) use ($admissionLevel) {
                                        if ($admissionLevel) {
                                            $q->where('name', 'like', '%' . $admissionLevel . '%')
                                              ->orWhere('name', 'Formulir Pendaftaran');
                                        } else {
                                            $q->where('name', 'Formulir Pendaftaran');
                                        }
                                    })->first();
                            }
                        }
                        $feeName = $fee ? $fee->name : 'Formulir Pendaftaran';
                    @endphp
                    <div class="flex justify-between items-center text-xs text-slate-600">
                        <span>
                            {{ $payment->payment_type === 'registration_fee' ? $feeName : 'Biaya Pokok Seleksi & Administrasi' }}
                        </span>
                        <span class="font-bold text-slate-800">
                            Rp {{ number_format($payment->base_amount, 0, ',', '.') }}
                        </span>
                    </div>
                @endif

                <!-- Admin Fee -->
                <div class="flex justify-between items-center text-xs text-slate-600">
                    <span>Biaya Administrasi Transaksi</span>
                    <span class="font-bold text-slate-800">
                        Rp {{ number_format($payment->admin_fee, 0, ',', '.') }}
                    </span>
                </div>

                <!-- Total Row -->
                <div class="border-t border-slate-200 pt-3 flex justify-between items-center">
                    <span class="text-xs font-black text-slate-900 uppercase">Total Terbayar</span>
                    <span class="text-sm font-black text-brand-emerald">
                        Rp {{ number_format($payment->amount, 0, ',', '.') }}
                    </span>
                </div>
                @if($payment->payment_type === 'final_fee' && $hasAnyInstallmentItem)
                    @foreach($annotatedItems as $aItem)
                        @if($aItem['is_installment'])
                            <div class="pt-2 border-t border-dashed border-slate-200 flex justify-between items-center text-xs">
                                <span class="text-slate-500 font-medium">
                                    @if($aItem['item_remaining'] > 0)
                                        Sisa Tagihan {{ $aItem['name'] }} Belum Lunas
                                    @else
                                        Status Pelunasan {{ $aItem['name'] }}
                                    @endif
                                </span>
                                <span class="font-bold {{ $aItem['item_remaining'] > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                                    @if($aItem['item_remaining'] > 0)
                                        Rp {{ number_format($aItem['item_remaining'], 0, ',', '.') }}
                                    @else
                                        LUNAS SEPENUHNYA (100%)
                                    @endif
                                </span>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            <!-- Receipt Bottom Note -->
            <div class="bg-slate-50 rounded-2xl p-4 text-center border border-slate-100">
                <p class="text-[10px] text-slate-400 leading-normal font-semibold">
                    *Kwitansi ini diterbitkan secara sah dan otomatis oleh sistem SPMB Online Sekolah Anak Saleh. Tidak memerlukan tanda tangan basah. Harap simpan bukti pembayaran ini dengan baik.
                </p>
            </div>
            
        </div>
        
    </div>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
