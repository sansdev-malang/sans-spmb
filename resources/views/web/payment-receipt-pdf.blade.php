<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ !empty($isSettlement) ? 'Kwitansi Utama Pelunasan SPMB' : ('Kwitansi Pembayaran #' . $payment->invoice_number) }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            font-size: 11px;
            line-height: 1.5;
            margin: 0;
            padding: 10px;
        }
        .container {
            max-width: 550px;
            margin: 0 auto;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 25px;
            background-color: #ffffff;
        }
        .header {
            border-bottom: 2px solid #059669;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .header-table {
            width: 100%;
        }
        .school-name {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }
        .school-subtitle {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        .status-badge {
            background-color: #d1fae5;
            color: #065f46;
            font-size: 9px;
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
        }
        .title-section {
            text-align: center;
            margin-bottom: 20px;
        }
        .title {
            font-size: 13px;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 0;
            font-weight: bold;
        }
        .invoice-no {
            font-size: 11px;
            color: #64748b;
            font-weight: bold;
            margin: 4px 0 0 0;
        }
        .details-table {
            width: 100%;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .details-table td {
            padding: 5px 0;
            vertical-align: top;
        }
        .label {
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: bold;
            display: block;
        }
        .value {
            font-size: 11px;
            color: #1e293b;
            font-weight: bold;
            margin-top: 2px;
            display: block;
        }
        .items-title {
            font-size: 10px;
            color: #0f172a;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            padding: 6px 0;
            text-align: left;
        }
        .items-table td {
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .installment-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px;
            margin: 8px 0 15px 0;
        }
        .installment-title {
            font-size: 9px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .installment-item {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .installment-item td {
            padding: 4px 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        .installment-item tr:last-child td {
            border-bottom: none;
        }
        .total-row td {
            border-top: 2px solid #059669;
            padding-top: 10px;
            font-weight: bold;
        }
        .total-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #0f172a;
        }
        .total-value {
            font-size: 14px;
            color: #059669;
            text-align: right;
        }
        .footer-note {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            line-height: 1.4;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    @php
        $isFormPayment = ($payment->payment_type === 'registration_fee');
        $allFinalPayments = $registration->payments()
            ->where('status', 'success')
            ->where('payment_type', 'final_fee')
            ->orderBy('created_at')
            ->get();

        // Extract items for current payment
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

    <div class="container">
        <!-- Header -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <td>
                        <h1 class="school-name">{{ \App\Models\Setting::get('school_name', 'Sekolah Anak Saleh') }}</h1>
                        <div class="school-subtitle">Sistem Penerimaan Murid Baru (SPMB)</div>
                    </td>
                    <td style="text-align: right; vertical-align: middle;">
                        @if(!empty($isSettlement) || $isFullySettled)
                            <span class="status-badge">LUNAS SEPENUHNYA</span>
                        @elseif($isFormPayment || !$hasAnyInstallmentItem)
                            <span class="status-badge">LUNAS</span>
                        @else
                            <span class="status-badge" style="background-color: #dbeafe; color: #1e40af;">ANGSURAN KE-{{ $primaryItemInstallmentNo }}</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Title Section -->
        <div class="title-section">
            @if(!empty($isSettlement))
                <h2 class="title">Kwitansi Utama Pelunasan Biaya SPMB</h2>
                <p class="invoice-no">
                    @if(!empty($filterItemName))
                        Komponen: {{ $filterItemName }} • 
                    @endif
                    No. Pendaftaran: SANS-{{ str_pad($registration->id, 4, '0', STR_PAD_LEFT) }}
                </p>
            @elseif($isFormPayment)
                <h2 class="title">Bukti Pembayaran Biaya Pendaftaran</h2>
                <p class="invoice-no">Nomor Transaksi: {{ $payment->invoice_number }}</p>
            @elseif($hasAnyInstallmentItem)
                <h2 class="title">Bukti Pembayaran Angsuran Ke-{{ $primaryItemInstallmentNo }}</h2>
                <p class="invoice-no">Nomor Transaksi: {{ $payment->invoice_number }}</p>
            @else
                <h2 class="title">Bukti Pembayaran Resmi</h2>
                <p class="invoice-no">Nomor Transaksi: {{ $payment->invoice_number }}</p>
            @endif
        </div>

        <!-- Details Metadata -->
        <table class="details-table">
            <tr>
                <td style="width: 50%;">
                    <span class="label">{{ !empty($isSettlement) ? 'Tanggal Pelunasan' : 'Tanggal Transaksi' }}</span>
                    <span class="value">
                        @if(!empty($isSettlement) && $allFinalPayments->isNotEmpty())
                            {{ $allFinalPayments->last()->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                        @else
                            {{ $payment->updated_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                        @endif
                    </span>
                </td>
                <td style="width: 50%;">
                    <span class="label">Metode Pembayaran</span>
                    <span class="value">
                        @if(!empty($isSettlement))
                            {{ $allFinalPayments->pluck('payment_method')->unique()->implode(', ') ?: 'Online Payment' }}
                        @else
                            {{ $payment->payment_method }}
                        @endif
                    </span>
                </td>
            </tr>
            <tr>
                <td style="width: 50%;">
                    <span class="label">Nama Calon Siswa</span>
                    <span class="value">{{ $registration->candidate_name ?? 'Calon Siswa' }}</span>
                </td>
                <td style="width: 50%;">
                    <span class="label">Unit Pendidikan</span>
                    <span class="value">
                        {{ $registration->unit->name ?? '-' }}@if(!empty($registration->grade->name)) ({{ $registration->grade->name }})@endif
                    </span>
                </td>
            </tr>
            <tr>
                <td style="width: 50%;">
                    <span class="label">Tahun Ajaran & Gelombang</span>
                    <span class="value">
                        {{ $registration->period->year ?? '-' }} - {{ $registration->wave->name ?? '-' }}
                    </span>
                </td>
                <td style="width: 50%;">
                    <span class="label">Jalur & Program Kelas</span>
                    <span class="value">
                        {{ $registration->type->name ?? '-' }}@if(!empty($registration->classProgram->name)) ({{ $registration->classProgram->name }})@endif
                    </span>
                </td>
            </tr>
            @if($registration->extraServices->count() > 0)
                <tr>
                    <td colspan="2" style="padding-top: 8px;">
                        <span class="label">Layanan Tambahan</span>
                        <span class="value" style="color: #059669;">
                            {{ $registration->extraServices->pluck('name')->implode(', ') }}
                        </span>
                    </td>
                </tr>
            @endif
        </table>

        <!-- PRICING & PAYMENT HISTORY BREAKDOWN -->
        @if(!empty($isSettlement))
            {{-- KWITANSI UTAMA: MENAMPILKAN SEMUA CICILAN YANG TELAH DILAKUKAN HINGGA LUNAS --}}
            <div class="items-title">Rincian Komponen Biaya & Riwayat Pelunasan Cicilan</div>

            @php
                $targetItems = [];
                if (isset($feeDetails['items']) && is_array($feeDetails['items'])) {
                    foreach ($feeDetails['items'] as $it) {
                        if (empty($filterItemName) || strcasecmp(trim($it['name']), trim($filterItemName)) === 0) {
                            $targetItems[] = $it;
                        }
                    }
                }
                $grandTotalSettled = 0;
            @endphp

            @foreach($targetItems as $tItem)
                @php
                    $tGross = (float) ($tItem['amount'] ?? 0);
                    $tDiscount = $registration->getItemDiscountAmount($tItem['name'], $tItem['id'] ?? null);
                    $tNet = max(0, $tGross - $tDiscount);
                    $tPaid = $registration->getItemPaidAmount($tItem['name'], $tItem['id'] ?? null);
                    $grandTotalSettled += $tPaid;

                    // Collect installments for this specific component
                    $itemInstallments = [];
                    foreach ($allFinalPayments as $p) {
                        $foundAmt = 0;
                        if ($p->items && $p->items->isNotEmpty()) {
                            foreach ($p->items as $pItem) {
                                if ((!empty($tItem['id']) && (int)$pItem->spmb_fee_id === (int)$tItem['id']) || strcasecmp(trim($pItem->fee_name), trim($tItem['name'])) === 0) {
                                    $foundAmt += (float) $pItem->amount;
                                }
                            }
                        } elseif (isset($p->payment_info['selected_items']) && is_array($p->payment_info['selected_items'])) {
                            foreach ($p->payment_info['selected_items'] as $si) {
                                if ((!empty($tItem['id']) && isset($si['id']) && (int)$si['id'] === (int)$tItem['id']) || strcasecmp(trim($si['name'] ?? ''), trim($tItem['name'])) === 0) {
                                    $foundAmt += (float) ($si['amount'] ?? 0);
                                }
                            }
                        }
                        if ($foundAmt > 0) {
                            $itemInstallments[] = [
                                'invoice' => $p->invoice_number,
                                'amount' => $foundAmt,
                                'date' => $p->created_at ? $p->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') : '-',
                                'method' => $p->payment_method ?? 'VA'
                            ];
                        }
                    }
                @endphp

                <table class="items-table" style="margin-bottom: 5px;">
                    <thead>
                        <tr>
                            <th>Deskripsi Komponen</th>
                            <th style="text-align: right; width: 130px;">Tarif Pokok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong>{{ $tItem['name'] }}</strong>
                                @if($tDiscount > 0)
                                    <div style="font-size: 8px; color: #e11d48;">Diskon Khusus: - Rp {{ number_format($tDiscount, 0, ',', '.') }} (Tarif Asli: Rp {{ number_format($tGross, 0, ',', '.') }})</div>
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: bold; color: #1e293b;">
                                Rp {{ number_format($tNet, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                @if(!empty($itemInstallments))
                    <div class="installment-box">
                        <div class="installment-title">
                            Riwayat Pembayaran Angsuran ({{ count($itemInstallments) }}x Pembayaran):
                        </div>
                        <table class="installment-item">
                            @foreach($itemInstallments as $idx => $inst)
                                <tr>
                                    <td style="width: 25%; font-weight: bold; color: #059669;">
                                        Cicilan #{{ $idx + 1 }}
                                    </td>
                                    <td style="color: #64748b;">
                                        {{ $inst['invoice'] }} ({{ $inst['date'] }} WIB via {{ $inst['method'] }})
                                    </td>
                                    <td style="text-align: right; font-weight: bold; color: #1e293b; width: 100px;">
                                        Rp {{ number_format($inst['amount'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endif
            @endforeach

            <table class="items-table" style="margin-top: 10px;">
                <tbody>
                    <tr class="total-row">
                        <td class="total-label">Total Terbayar Lunas</td>
                        <td class="total-value">
                            Rp {{ number_format($grandTotalSettled, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr style="font-size: 9px; color: #059669;">
                        <td style="padding-top: 8px; border-bottom: none; font-weight: bold;">
                            Status Pelunasan Administrasi
                        </td>
                        <td style="text-align: right; font-weight: bold; padding-top: 8px; border-bottom: none; color: #059669;">
                            LUNAS SEPENUHNYA (100%)
                        </td>
                    </tr>
                </tbody>
            </table>

        @else
            {{-- KWITANSI TRANSAKSI TUNGGAL --}}
            <div class="items-title">Rincian Pembayaran Transaksi Ini</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Deskripsi Biaya</th>
                        <th style="text-align: right; width: 120px;">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!empty($annotatedItems))
                        @foreach($annotatedItems as $it)
                            <tr>
                                <td>
                                    {{ $it['name'] }}
                                    @if($it['is_installment'])
                                        <span style="font-size: 8px; color: #2563eb; font-weight: normal;">(Setoran Angsuran #{{ $it['installment_no'] }})</span>
                                    @endif
                                </td>
                                <td style="text-align: right; font-weight: bold; color: #1e293b;">
                                    Rp {{ number_format($it['amount'], 0, ',', '.') }}
                                </td>
                            </tr>
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
                            }
                            $feeName = $fee ? $fee->name : 'Formulir Pendaftaran';
                        @endphp
                        <tr>
                            <td>
                                {{ $payment->payment_type === 'registration_fee' ? $feeName : 'Biaya Pokok Seleksi & Administrasi' }}
                            </td>
                            <td style="text-align: right; font-weight: bold; color: #1e293b;">
                                Rp {{ number_format($payment->base_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                    @if($payment->admin_fee > 0)
                        <tr>
                            <td>Biaya Administrasi Transaksi</td>
                            <td style="text-align: right; font-weight: bold; color: #1e293b;">
                                Rp {{ number_format($payment->admin_fee, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                    <tr class="total-row">
                        <td class="total-label">Total Terbayar Transaksi Ini</td>
                        <td class="total-value">
                            Rp {{ number_format($payment->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @if($payment->payment_type === 'final_fee' && $hasAnyInstallmentItem)
                        @foreach($annotatedItems as $aItem)
                            @if($aItem['is_installment'])
                                <tr style="font-size: 9px; color: #64748b;">
                                    <td style="padding-top: 8px; border-bottom: none;">
                                        @if($aItem['item_remaining'] > 0)
                                            Sisa Tagihan {{ $aItem['name'] }} Belum Lunas
                                        @else
                                            Status Pelunasan {{ $aItem['name'] }}
                                        @endif
                                    </td>
                                    <td style="text-align: right; font-weight: bold; padding-top: 8px; border-bottom: none; color: {{ $aItem['item_remaining'] > 0 ? '#d97706' : '#059669' }};">
                                        @if($aItem['item_remaining'] > 0)
                                            Rp {{ number_format($aItem['item_remaining'], 0, ',', '.') }}
                                        @else
                                            LUNAS SEPENUHNYA (100%)
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                </tbody>
            </table>
        @endif

        <div class="footer-note">
            *Kwitansi ini diterbitkan secara sah dan otomatis oleh sistem SPMB Online Sekolah Anak Saleh. Tidak memerlukan tanda tangan basah. Harap simpan bukti pembayaran ini dengan baik.
        </div>
    </div>
</body>
</html>
