<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi Pembayaran #{{ $payment->invoice_number }}</title>
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
            padding: 3px 10px;
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
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0;
            font-weight: bold;
        }
        .invoice-no {
            font-size: 11px;
            color: #475569;
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
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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
        .total-row td {
            border-top: 2px solid #e2e8f0;
            padding-top: 10px;
            font-weight: bold;
        }
        .total-label {
            font-size: 11px;
            text-transform: uppercase;
            color: #0f172a;
        }
        .total-value {
            font-size: 13px;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <table class="header-table">
                <tr>
                    <td>
                        <h1 class="school-name">{{ \App\Models\Setting::get('school_name', 'Sekolah Anak Saleh') }}</h1>
                        <div class="school-subtitle">Sistem Penerimaan Murid Baru (SPMB)</div>
                    </td>
                    <td style="text-align: right; vertical-align: middle;">
                        <span class="status-badge">LUNAS</span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="title-section">
            <h2 class="title">Bukti Pembayaran Resmi</h2>
            <p class="invoice-no">Nomor: {{ $payment->invoice_number }}</p>
        </div>

        <table class="details-table">
            <tr>
                <td style="width: 50%;">
                    <span class="label">Tanggal Transaksi</span>
                    <span class="value">{{ $payment->updated_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</span>
                </td>
                <td style="width: 50%;">
                    <span class="label">Metode Pembayaran</span>
                    <span class="value">{{ $payment->payment_method }}</span>
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

        <div class="items-title">Rincian Pembayaran</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Deskripsi Biaya</th>
                    <th style="text-align: right; width: 120px;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @if($payment->items && $payment->items->isNotEmpty())
                    @foreach($payment->items as $item)
                        <tr>
                            <td>{{ $item->fee_name }}</td>
                            <td style="text-align: right; font-weight: bold; color: #1e293b;">
                                Rp {{ number_format($item->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                @elseif($payment->payment_type === 'final_fee' && isset($payment->payment_info['selected_items']) && is_array($payment->payment_info['selected_items']))
                    @foreach($payment->payment_info['selected_items'] as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td style="text-align: right; font-weight: bold; color: #1e293b;">
                                Rp {{ number_format($item['amount'], 0, ',', '.') }}
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
                    <tr>
                        <td>
                            {{ $payment->payment_type === 'registration_fee' ? $feeName : 'Biaya Pokok Seleksi & Administrasi' }}
                        </td>
                        <td style="text-align: right; font-weight: bold; color: #1e293b;">
                            Rp {{ number_format($payment->base_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td>Biaya Administrasi Transaksi</td>
                    <td style="text-align: right; font-weight: bold; color: #1e293b;">
                        Rp {{ number_format($payment->admin_fee, 0, ',', '.') }}
                    </td>
                </tr>
                <tr class="total-row">
                    <td class="total-label">Total Terbayar</td>
                    <td class="total-value">
                        Rp {{ number_format($payment->amount, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="footer-note">
            *Kwitansi ini diterbitkan secara sah dan otomatis oleh sistem SPMB Online Sekolah Anak Saleh. Tidak memerlukan tanda tangan basah. Harap simpan bukti pembayaran ini dengan baik.
        </div>
    </div>
</body>
</html>
