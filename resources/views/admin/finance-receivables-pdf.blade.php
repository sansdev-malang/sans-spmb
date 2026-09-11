<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentTitle ?? 'Buku Rekapitulasi Piutang & Tunggakan Murid SPMB' }}</title>
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
            background-color: #fff1f2;
            color: #9f1239;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid #fecdd3;
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
            color: #9f1239;
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
        .kpi-card.rose { background-color: #fff1f2; border-color: #fecdd3; }
        .kpi-card.slate { background-color: #f8fafc; border-color: #e2e8f0; }
        .kpi-card.amber { background-color: #fffbeb; border-color: #fde68a; }
        .kpi-card.emerald { background-color: #f0fdf4; border-color: #bbf7d0; }
        
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
            margin-bottom: 10px;
        }
        .data-table th {
            background-color: #be123c;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            padding: 4px 4px;
            border: 1px solid #9f1239;
            text-align: left;
            font-size: 7px;
        }
        .data-table th.text-center, .data-table td.text-center { text-align: center; }
        .data-table th.text-right, .data-table td.text-right { text-align: right; }
        .data-table td {
            padding: 4px 4px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            color: #334155;
        }
        .data-table tbody tr:nth-child(even) { background-color: #fff1f2/20; }
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
        .badge-amber { background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-info { background-color: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
        .badge-purple { background-color: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; }

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
                <div class="doc-badge">BUKU REKAPITULASI PIUTANG</div>
                <div><strong>Tgl Cetak:</strong> {{ $printedAt }}</div>
                <div><strong>Dicetak Oleh:</strong> {{ $printedBy }}</div>
                <div><strong>Filter Unit:</strong> {{ $unitFilterLabel }}</div>
            </td>
        </tr>
    </table>

    <!-- Title Section -->
    <div class="report-title-section">
        <h2 class="report-title">BUKU REKAPITULASI PIUTANG & TUNGGAKAN BIAYA MASUK MURID</h2>
        <div class="report-subtitle">
            TAHUN AJARAN {{ $periodName }} &bull; DAFTAR CALON MURID DENGAN KEWAJIBAN BIAYA MASUK BELUM LUNAS
        </div>
    </div>

    <!-- 4 KPI Summary Cards -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-card rose" style="width: 25%;">
                <span class="kpi-label">Total Sisa Piutang DSP</span>
                <span class="kpi-value" style="color: #9f1239;">Rp {{ number_format($totalRemainingDSP, 0, ',', '.') }}</span>
                <span class="kpi-sub">Kewajiban Belum Diterima</span>
            </td>
            <td class="kpi-card slate" style="width: 25%;">
                <span class="kpi-label">Jumlah Murid Piutang</span>
                <span class="kpi-value" style="color: #1e293b;">{{ $receivables->count() }} Murid</span>
                <span class="kpi-sub">Total Memiliki Sisa Tagihan</span>
            </td>
            <td class="kpi-card amber" style="width: 25%;">
                <span class="kpi-label">Belum Bayar Sama Sekali</span>
                <span class="kpi-value" style="color: #92400e;">{{ $belumBayarCount }} Murid</span>
                <span class="kpi-sub">0% Terbayar</span>
            </td>
            <td class="kpi-card emerald" style="width: 25%;">
                <span class="kpi-label">Sedang Mencicil (Sebagian)</span>
                <span class="kpi-value" style="color: #065f46;">{{ $sebagianCount }} Murid</span>
                <span class="kpi-sub">Sudah Masuk Angsuran Awal</span>
            </td>
        </tr>
    </table>

    <!-- Main Receivables Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 3%;">No</th>
                <th style="width: 10%;">No. Registrasi</th>
                <th style="width: 18%;">Nama Calon Murid</th>
                <th style="width: 15%;">Orang Tua & WhatsApp</th>
                <th style="width: 12%;">Unit & Jenjang</th>
                <th style="width: 8%;">Gelombang</th>
                <th class="text-right" style="width: 11%;">Tagihan Netto</th>
                <th class="text-right" style="width: 11%;">Kas Masuk</th>
                <th class="text-right" style="width: 12%;">Sisa Piutang</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sumNet = 0;
                $sumPaid = 0;
                $sumRemaining = 0;
            @endphp
            @forelse($receivables as $index => $c)
                @php
                    $net = $c->net_fee;
                    $paid = $c->total_paid_final_fee;
                    $remaining = $c->remaining_balance;

                    $sumNet += $net;
                    $sumPaid += $paid;
                    $sumRemaining += $remaining;

                    $parentName = $c->guardian_name ?: ($c->father_name ?: ($c->mother_name ?: '-'));
                    $parentPhone = $c->parent_phone ?: ($c->father_phone ?: ($c->mother_phone ?: '-'));
                @endphp
                <tr>
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-weight: bold; color: #0f172a;">{{ $c->id_label ?? '-' }}</td>
                    <td>
                        <strong style="color: #0f172a;">{{ $c->candidate_name ?? '-' }}</strong>
                        @if($c->is_dispensation)
                            <div style="font-size: 6.5px; color: #7e22ce; font-weight: bold;">(Dispensasi)</div>
                        @endif
                    </td>
                    <td>
                        <div>{{ $parentName }}</div>
                        <div style="font-size: 6.5px; color: #64748b; font-family: monospace;">{{ $parentPhone }}</div>
                    </td>
                    <td>
                        <strong>{{ $c->unit->name ?? '-' }}</strong>
                        <div style="font-size: 6.5px; color: #64748b;">{{ $c->grade->name ?? ($c->admission_level ?? '-') }}</div>
                    </td>
                    <td>{{ $c->wave->name ?? '-' }}</td>
                    <td class="text-right">Rp {{ number_format($net, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold; color: #059669;">Rp {{ number_format($paid, 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: 800; color: #be123c;">Rp {{ number_format($remaining, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 15px; color: #94a3b8; font-style: italic;">
                        Alhamdulillah, tidak ada data piutang / seluruh calon murid telah melunasi biaya masuk.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($receivables->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right" style="font-size: 7.5px;">TOTAL PIUTANG ({{ $receivables->count() }} Murid):</td>
                    <td class="text-right">Rp {{ number_format($sumNet, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #059669;">Rp {{ number_format($sumPaid, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #be123c; font-size: 8px;">Rp {{ number_format($sumRemaining, 0, ',', '.') }}</td>
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
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}  |  Buku Rekapitulasi Piutang SPMB Yayasan Pendidikan Anak Saleh";
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
