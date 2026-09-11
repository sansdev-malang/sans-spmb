<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentTitle ?? 'Laporan Riwayat Transaksi Masuk SPMB' }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 10mm 12mm 12mm 12mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5px;
            color: #1e293b;
            line-height: 1.3;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        
        /* Kop Surat Header */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #059669;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .kop-table td {
            vertical-align: middle;
        }
        .kop-brand {
            font-size: 14px;
            font-weight: 800;
            color: #064e3b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .kop-subbrand {
            font-size: 9.5px;
            font-weight: 700;
            color: #059669;
            text-transform: uppercase;
            margin: 2px 0 0 0;
        }
        .kop-address {
            font-size: 7.5px;
            color: #64748b;
            margin: 2px 0 0 0;
        }
        .meta-box {
            text-align: right;
            font-size: 8px;
            color: #475569;
        }
        .meta-box .doc-badge {
            display: inline-block;
            background-color: #ecfdf5;
            color: #047857;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid #a7f3d0;
            font-size: 8.5px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        
        /* Document Title Section */
        .report-title-section {
            text-align: center;
            margin-bottom: 10px;
        }
        .report-title {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .report-subtitle {
            font-size: 8.5px;
            color: #059669;
            font-weight: 700;
            margin-top: 2px;
        }

        /* KPI Summary Grid Table */
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 10px;
        }
        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 6px 8px;
            text-align: center;
            vertical-align: middle;
        }
        .kpi-card.indigo { background-color: #eef2ff; border-color: #c7d2fe; }
        .kpi-card.emerald { background-color: #f0fdf4; border-color: #bbf7d0; }
        .kpi-card.amber { background-color: #fffbeb; border-color: #fde68a; }
        .kpi-card.rose { background-color: #fff1f2; border-color: #fecdd3; }
        
        .kpi-label {
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            display: block;
        }
        .kpi-value {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            display: block;
            margin-top: 1px;
        }
        .kpi-sub {
            font-size: 7px;
            font-weight: 600;
            color: #64748b;
            display: block;
        }

        /* Main Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #059669;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            padding: 4px 4px;
            border: 1px solid #047857;
            text-align: left;
            font-size: 7px;
        }
        .data-table th.text-center, .data-table td.text-center {
            text-align: center;
        }
        .data-table th.text-right, .data-table td.text-right {
            text-align: right;
        }
        .data-table td {
            padding: 4px 4px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
            color: #334155;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .data-table tfoot td {
            background-color: #f1f5f9;
            font-weight: 800;
            border-top: 2px solid #cbd5e1;
            padding: 4px 4px;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 6.5px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-pending { background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-failed { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

        /* Signatures Section */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .signature-table td {
            width: 50%;
            vertical-align: top;
            font-size: 8.5px;
        }
        .signature-title {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 45px;
        }
        .signature-name {
            font-weight: 800;
            color: #0f172a;
            text-decoration: underline;
        }
        .signature-role {
            font-size: 7.5px;
            color: #64748b;
            margin-top: 1px;
        }
    </style>
</head>
<body>

    <!-- Kop Surat Header -->
    <table class="kop-table">
        <tr>
            <td style="width: 70px;">
                @php
                    $logoSrc = \App\Models\Setting::getLogoBase64() ?? ($logoUrl ?? null);
                @endphp
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" style="height: 48px; width: auto;" alt="Logo">
                @else
                    <div style="width: 48px; height: 48px; background-color: #047857; color: white; text-align: center; line-height: 48px; font-weight: bold; border-radius: 6px;">SAS</div>
                @endif
            </td>
            <td>
                <h1 class="kop-brand">YAYASAN PENDIDIKAN ANAK SALEH MALANG</h1>
                <div class="kop-subbrand">PANITIA SISTEM PENERIMAAN MURID BARU (SPMB)</div>
                <div class="kop-address">Jl. Candi Panggung Indah No. 1-3, Mojolangu, Kecamatan Lowokwaru, Kota Malang, Jawa Timur</div>
            </td>
            <td class="meta-box" style="width: 220px;">
                <div class="doc-badge">JURNAL MUTASI KAS MASUK</div>
                <div><strong>Tgl Cetak:</strong> {{ $printedAt }}</div>
                <div><strong>Dicetak Oleh:</strong> {{ $printedBy }}</div>
                <div><strong>Filter Unit:</strong> {{ $unitFilterLabel }}</div>
            </td>
        </tr>
    </table>

    <!-- Title Section -->
    <div class="report-title-section">
        <h2 class="report-title">LAPORAN MUTASI & RIWAYAT TRANSAKSI MASUK</h2>
        <div class="report-subtitle">
            TAHUN AJARAN {{ $periodName }} &bull; REKAPITULASI TRANSAKSI GATEWAY & KAS PENDAFTARAN
        </div>
    </div>

    <!-- 4 KPI Summary Cards -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-card indigo" style="width: 25%;">
                <span class="kpi-label">Total Transaksi</span>
                <span class="kpi-value" style="color: #3730a3;">{{ $stats['total_count'] }}</span>
                <span class="kpi-sub">Seluruh Mutasi Tercatat</span>
            </td>
            <td class="kpi-card emerald" style="width: 25%;">
                <span class="kpi-label">Kas Masuk Berhasil (Success)</span>
                <span class="kpi-value" style="color: #065f46;">Rp {{ number_format($stats['success_amount'], 0, ',', '.') }}</span>
                <span class="kpi-sub">{{ $stats['success_count'] }} Transaksi Lunas</span>
            </td>
            <td class="kpi-card amber" style="width: 25%;">
                <span class="kpi-label">Menunggu Pembayaran (Pending)</span>
                <span class="kpi-value" style="color: #92400e;">Rp {{ number_format($stats['pending_amount'], 0, ',', '.') }}</span>
                <span class="kpi-sub">{{ $stats['pending_count'] }} Transaksi Aktif</span>
            </td>
            <td class="kpi-card rose" style="width: 25%;">
                <span class="kpi-label">Dibatalkan / Kedaluwarsa</span>
                <span class="kpi-value" style="color: #9f1239;">{{ $stats['spam_count'] }} Transaksi</span>
                <span class="kpi-sub">Rp {{ number_format($stats['spam_amount'], 0, ',', '.') }}</span>
            </td>
        </tr>
    </table>

    <!-- Main Transactions Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 3%;">No</th>
                <th style="width: 14%;">No. Invoice & ID Trx</th>
                <th style="width: 11%;">Waktu Transaksi</th>
                <th style="width: 17%;">Calon Murid & No. Reg</th>
                <th style="width: 10%;">Unit & Jenjang</th>
                <th style="width: 18%;">Jenis & Item Pembayaran</th>
                <th style="width: 12%;">Metode & VA</th>
                <th class="text-right" style="width: 9%;">Nominal (Rp)</th>
                <th class="text-center" style="width: 6%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sumAmount = 0;
            @endphp
            @forelse($payments as $index => $pay)
                @php
                    $callbackPayload = $pay->payment_info['callback_payload'] ?? [];
                    $numericWinpayId = $callbackPayload['originalReferenceNo'] 
                        ?? ($callbackPayload['referenceNo'] 
                            ?? ($callbackPayload['paymentRequestId'] 
                                ?? (!empty($pay->reference_id) && is_numeric($pay->reference_id) ? $pay->reference_id : null)));
                    $contractId = $pay->reference_id 
                        ?? ($pay->payment_info['referenceId'] 
                            ?? ($pay->payment_info['contractId'] 
                                ?? ($pay->payment_info['additionalInfo']['contractId'] ?? null)));
                    $displayWinpayId = $numericWinpayId ?: ($contractId ?: '-');

                    $settledTimeRaw = $pay->payment_info['settled_at'] 
                        ?? ($callbackPayload['paidTime'] 
                            ?? ($callbackPayload['trxDateTime'] ?? null));

                    $displayTime = null;
                    if ($pay->status === 'success' && $settledTimeRaw) {
                        try {
                            $displayTime = \Carbon\Carbon::parse($settledTimeRaw)->timezone('Asia/Jakarta')->format('d/m/Y H:i');
                        } catch (\Throwable $e) {
                            $displayTime = null;
                        }
                    }
                    if (!$displayTime) {
                        $displayTime = $pay->created_at ? $pay->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') : '-';
                    }

                    $reg = $pay->registration;
                    $candName = $reg?->candidate_name ?? 'Draft / Belum Isi';
                    $regId = $reg?->id_label ?? '-';
                    $unitName = $reg?->unit?->name ?? '-';
                    $gradeName = $reg?->grade?->name ?? ($reg?->admission_level ?? '-');

                    if ($pay->payment_type === 'registration_fee') {
                        $fee = $reg ? $reg->getRegistrationFee() : null;
                        $feeTitle = 'Formulir: ' . ($fee ? $fee->name : 'Pendaftaran');
                    } else {
                        $itemNames = [];
                        if ($pay->items && $pay->items->isNotEmpty()) {
                            $itemNames = $pay->items->pluck('fee_name')->toArray();
                        } elseif (isset($pay->payment_info['selected_items']) && is_array($pay->payment_info['selected_items'])) {
                            $itemNames = array_column($pay->payment_info['selected_items'], 'name');
                        }
                        $feeTitle = !empty($itemNames) ? implode(', ', $itemNames) : 'Pelunasan Biaya Administrasi DSP';
                    }

                    $vaNumber = $pay->payment_info['virtualAccountNo'] ?? null;
                    
                    if ($pay->status === 'success') {
                        $sumAmount += $pay->amount;
                        $badgeClass = 'badge-success';
                    } elseif ($pay->status === 'pending') {
                        $badgeClass = 'badge-pending';
                    } else {
                        $badgeClass = 'badge-failed';
                    }
                @endphp
                <tr>
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td>
                        <strong style="font-family: monospace; color: #0f172a;">{{ $pay->invoice_number }}</strong>
                        @if($displayWinpayId !== '-')
                            <div style="font-size: 6.5px; color: #64748b; font-family: monospace; margin-top: 1px;">
                                Ref: {{ $displayWinpayId }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $displayTime }}</strong>
                        <div style="font-size: 6.5px; color: #64748b;">WIB</div>
                    </td>
                    <td>
                        <strong style="color: #0f172a;">{{ $candName }}</strong>
                        <div style="font-size: 6.5px; color: #64748b; font-family: monospace;">{{ $regId }}</div>
                    </td>
                    <td>
                        <strong>{{ $unitName }}</strong>
                        <div style="font-size: 6.5px; color: #64748b;">{{ $gradeName }}</div>
                    </td>
                    <td>
                        <span style="font-weight: 600; color: #0f172a;">{{ $feeTitle }}</span>
                    </td>
                    <td>
                        <strong>{{ $pay->payment_method ?: 'Gateway' }}</strong>
                        @if($vaNumber)
                            <div style="font-size: 6.5px; font-family: monospace; color: #475569;">VA: {{ $vaNumber }}</div>
                        @endif
                    </td>
                    <td class="text-right" style="font-weight: 800; color: {{ $pay->status === 'success' ? '#059669' : '#0f172a' }};">
                        Rp {{ number_format($pay->amount, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">{{ strtoupper($pay->status) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 15px; color: #94a3b8; font-style: italic;">
                        Tidak ada riwayat transaksi pembayaran yang sesuai dengan kriteria filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($payments->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="7" class="text-right" style="font-size: 7.5px;">TOTAL KAS MASUK BERHASIL (SUCCESS):</td>
                    <td class="text-right" style="color: #059669; font-size: 8px;">
                        Rp {{ number_format($sumAmount, 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Signatures Block -->
    <table class="signature-table">
        <tr>
            <td style="text-align: left; padding-left: 20px;">
                <div class="signature-title">
                    Mengetahui,<br>
                    <strong>Kepala Bagian Keuangan Yayasan</strong>
                </div>
                <div class="signature-name">( __________________________ )</div>
                <div class="signature-role">NIP / NIY. .....................................</div>
            </td>
            <td style="text-align: right; padding-right: 20px;">
                <div class="signature-title">
                    Kota Malang, {{ now()->translatedFormat('d F Y') }}<br>
                    <strong>Petugas Kasir & Administrasi SPMB</strong>
                </div>
                <div class="signature-name">( {{ $printedBy }} )</div>
                <div class="signature-role">Staf Administrasi Keuangan</div>
            </td>
        </tr>
    </table>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}  |  Laporan Riwayat Transaksi Masuk SPMB Sekolah Anak Saleh";
            $font = $fontMetrics->getFont("Helvetica", "normal");
            $size = 7;
            $color = array(0.5, 0.5, 0.5);
            $word_space = 0.0;
            $char_space = 0.0;
            $angle = 0.0;
            $pdf->page_text(340, 570, $text, $font, $size, $color, $word_space, $char_space, $angle);
        }
    </script>
</body>
</html>
