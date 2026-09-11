<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentTitle ?? 'Laporan Rincian Tagihan & DSP Murid SPMB' }}</title>
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
            border-spacing: 5px;
            margin-bottom: 10px;
        }
        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 5px 6px;
            text-align: center;
            vertical-align: middle;
        }
        .kpi-card.indigo { background-color: #eef2ff; border-color: #c7d2fe; }
        .kpi-card.slate { background-color: #f8fafc; border-color: #e2e8f0; }
        .kpi-card.rose { background-color: #fff1f2; border-color: #fecdd3; }
        .kpi-card.emerald { background-color: #f0fdf4; border-color: #bbf7d0; }
        .kpi-card.amber { background-color: #fffbeb; border-color: #fde68a; }
        
        .kpi-label {
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            display: block;
        }
        .kpi-value {
            font-size: 11.5px;
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
            padding: 4px 3px;
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
            padding: 3.5px 3px;
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
            padding: 4px 3px;
        }

        /* Itemized Component Pill List */
        .item-list {
            margin: 0;
            padding: 0;
            list-style: none;
            font-size: 7px;
        }
        .item-list li {
            margin-bottom: 1.5px;
            line-height: 1.25;
        }
        .item-name {
            font-weight: 600;
            color: #1e293b;
        }
        .item-paid-badge {
            font-size: 6.5px;
            color: #059669;
            font-weight: 700;
        }
        .item-unpaid-badge {
            font-size: 6.5px;
            color: #d97706;
            font-weight: 700;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 1px 3px;
            border-radius: 3px;
            font-size: 6.5px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-info { background-color: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
        .badge-purple { background-color: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; }
        .badge-amber { background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-slate { background-color: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

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
                <div class="doc-badge">RINCIAN KEUANGAN OPERASIONAL</div>
                <div><strong>Tgl Cetak:</strong> {{ $printedAt }}</div>
                <div><strong>Dicetak Oleh:</strong> {{ $printedBy }}</div>
                <div><strong>Filter Unit:</strong> {{ $unitFilterLabel }}</div>
            </td>
        </tr>
    </table>

    <!-- Title Section -->
    <div class="report-title-section">
        <h2 class="report-title">LAPORAN RINCIAN TAGIHAN & PEMBAYARAN DSP MURID</h2>
        <div class="report-subtitle">
            TAHUN AJARAN {{ $periodName }} &bull; DETAIL ITEM KOMPONEN BIAYA, DISKON, DAN STATUS ANGSURAN
        </div>
    </div>

    <!-- 5 Financial KPI Summary Cards -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-card indigo" style="width: 20%;">
                <span class="kpi-label">Total Murid DSP</span>
                <span class="kpi-value" style="color: #3730a3;">{{ $stats['candidate_count'] }}</span>
                <span class="kpi-sub">{{ $stats['lunas_count'] }} Lunas Sepenuhnya</span>
            </td>
            <td class="kpi-card slate" style="width: 20%;">
                <span class="kpi-label">Tagihan Bruto (Tarif)</span>
                <span class="kpi-value" style="color: #1e293b;">Rp {{ number_format($stats['gross_revenue'], 0, ',', '.') }}</span>
                <span class="kpi-sub">Total Komponen Biaya</span>
            </td>
            <td class="kpi-card rose" style="width: 20%;">
                <span class="kpi-label">Total Diskon</span>
                <span class="kpi-value" style="color: #9f1239;">- Rp {{ number_format($stats['discount_sum'], 0, ',', '.') }}</span>
                <span class="kpi-sub">{{ $stats['diskon_count'] }} Murid Disetujui</span>
            </td>
            <td class="kpi-card emerald" style="width: 20%;">
                <span class="kpi-label">Kas Masuk (Realisasi)</span>
                <span class="kpi-value" style="color: #065f46;">Rp {{ number_format($stats['paid_sum'], 0, ',', '.') }}</span>
                <span class="kpi-sub">{{ $stats['net_revenue'] > 0 ? round(($stats['paid_sum'] / $stats['net_revenue']) * 100, 1) : 0 }}% dari Netto</span>
            </td>
            <td class="kpi-card amber" style="width: 20%;">
                <span class="kpi-label">Sisa Piutang DSP</span>
                <span class="kpi-value" style="color: #92400e;">Rp {{ number_format($stats['remaining_sum'], 0, ',', '.') }}</span>
                <span class="kpi-sub">Tunggakan Belum Lunas</span>
            </td>
        </tr>
    </table>

    <!-- Main Itemized Candidates Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 2.5%;">No</th>
                <th style="width: 8%;">No. Registrasi</th>
                <th style="width: 14%;">Nama Calon Murid</th>
                <th style="width: 5.5%;">Unit</th>
                <th style="width: 7%;">Gelombang</th>
                <th style="width: 22%;">Rincian Komponen Biaya DSP</th>
                <th class="text-right" style="width: 8%;">Bruto (Rp)</th>
                <th class="text-right" style="width: 7%;">Diskon</th>
                <th class="text-right" style="width: 8%;">Netto (Rp)</th>
                <th class="text-right" style="width: 8%;">Terbayar</th>
                <th class="text-right" style="width: 8%;">Sisa Piutang</th>
                <th class="text-center" style="width: 7%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sumGross = 0;
                $sumDiscount = 0;
                $sumNet = 0;
                $sumPaid = 0;
                $sumRemaining = 0;
            @endphp
            @forelse($registrations as $index => $c)
                @php
                    $gross = $c->getGrossFee();
                    $discount = $c->total_discount;
                    $net = $c->net_fee;
                    $paid = $c->total_paid_final_fee;
                    $remaining = $c->remaining_balance;

                    $sumGross += $gross;
                    $sumDiscount += $discount;
                    $sumNet += $net;
                    $sumPaid += $paid;
                    $sumRemaining += $remaining;

                    $statusText = 'Belum Bayar';
                    $badgeClass = 'badge-amber';
                    if ($c->is_dispensation) {
                        $statusText = 'Dispensasi';
                        $badgeClass = 'badge-purple';
                    } elseif ($remaining <= 0 && $net > 0 && $paid > 0) {
                        $statusText = 'LUNAS';
                        $badgeClass = 'badge-success';
                    } elseif ($paid > 0 && $remaining > 0) {
                        $statusText = 'SEBAGIAN';
                        $badgeClass = 'badge-info';
                    }

                    $feeData = $c->getFinalFeeDetails();
                    $feeItems = $feeData['items'] ?? [];
                @endphp
                <tr>
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-weight: bold; color: #0f172a;">{{ $c->id_label ?? '-' }}</td>
                    <td>
                        <strong style="color: #0f172a;">{{ $c->candidate_name ?? '-' }}</strong>
                        <div style="font-size: 6.5px; color: #64748b; margin-top: 1px;">
                            WA: {{ $c->parent_phone ?: ($c->father_phone ?: ($c->mother_phone ?: '-')) }}
                        </div>
                    </td>
                    <td><strong>{{ strtoupper($c->unit->code ?? ($c->unit->name ?? '-')) }}</strong></td>
                    <td>{{ $c->wave->name ?? '-' }}</td>
                    <td>
                        @if(empty($feeItems))
                            <span style="color: #94a3b8; font-style: italic;">- Belum ada rincian komponen -</span>
                        @else
                            <ul class="item-list">
                                @foreach($feeItems as $item)
                                    @php
                                        $itemPaid = $c->getItemPaidAmount($item['name'], $item['id'] ?? null);
                                        $isItemPaid = ($itemPaid >= $item['amount'] && $item['amount'] > 0);
                                    @endphp
                                    <li>
                                        &bull; <span class="item-name">{{ $item['name'] }}:</span>
                                        <span>Rp {{ number_format($item['amount'], 0, ',', '.') }}</span>
                                        @if($itemPaid > 0)
                                            <span class="{{ $isItemPaid ? 'item-paid-badge' : 'item-unpaid-badge' }}">
                                                ({{ $isItemPaid ? 'Lunas' : 'Dicicil Rp ' . number_format($itemPaid, 0, ',', '.') }})
                                            </span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($gross, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: {{ $discount > 0 ? '#b91c1c' : '#64748b' }};">
                        {{ $discount > 0 ? '- Rp ' . number_format($discount, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right" style="font-weight: bold; color: #0f172a;">Rp {{ number_format($net, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold; color: #059669;">Rp {{ number_format($paid, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold; color: {{ $remaining > 0 ? '#d97706' : '#64748b' }};">
                        Rp {{ number_format($remaining, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">{{ $statusText }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 15px; color: #94a3b8; font-style: italic;">
                        Tidak ada data tagihan & DSP yang sesuai dengan kriteria filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($registrations->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right" style="font-size: 7.5px;">TOTAL REKAPITULASI ({{ $registrations->count() }} Murid):</td>
                    <td class="text-right">Rp {{ number_format($sumGross, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #b91c1c;">- Rp {{ number_format($sumDiscount, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($sumNet, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #059669;">Rp {{ number_format($sumPaid, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #d97706;">Rp {{ number_format($sumRemaining, 0, ',', '.') }}</td>
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
                    <strong>Kepala Unit / Bagian Keuangan Yayasan</strong>
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
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}  |  Laporan Rincian Tagihan & DSP Sekolah Anak Saleh Malang";
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
