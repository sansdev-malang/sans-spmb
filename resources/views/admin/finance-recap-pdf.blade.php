<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentTitle ?? 'Laporan Rekapitulasi Keuangan per Unit & Gelombang' }}</title>
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
        .kpi-card.emerald { background-color: #f0fdf4; border-color: #bbf7d0; }
        .kpi-card.slate { background-color: #f8fafc; border-color: #e2e8f0; }
        .kpi-card.indigo { background-color: #eef2ff; border-color: #c7d2fe; }
        .kpi-card.rose { background-color: #fff1f2; border-color: #fecdd3; }
        
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

        .section-heading {
            font-size: 9px;
            font-weight: 800;
            color: #064e3b;
            text-transform: uppercase;
            margin: 10px 0 4px 0;
            border-bottom: 1.5px solid #059669;
            padding-bottom: 2px;
        }

        /* Main Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
            margin-bottom: 10px;
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
        .data-table th.text-center, .data-table td.text-center { text-align: center; }
        .data-table th.text-right, .data-table td.text-right { text-align: right; }
        .data-table td {
            padding: 3.5px 4px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            color: #334155;
        }
        .data-table tbody tr:nth-child(even) { background-color: #f8fafc; }
        .data-table tfoot td {
            background-color: #f1f5f9;
            font-weight: 800;
            border-top: 2px solid #cbd5e1;
            padding: 4px 4px;
        }

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
            <td style="width: 60%;">
                <h1 class="kop-brand">YAYASAN PENDIDIKAN ANAK SALEH MALANG</h1>
                <div class="kop-subbrand">BAGIAN KEUANGAN & PANITIA PENERIMAAN MURID BARU (SPMB)</div>
                <div class="kop-address">
                    Jl. Candi Panggung Indah No. 1-3, Mojolangu, Kec. Lowokwaru, Kota Malang, Jawa Timur | Telp: (0341) 404888
                </div>
            </td>
            <td style="width: 40%;" class="meta-box">
                <div class="doc-badge">REKAPITULASI REALISASI & UNIT</div>
                <div><strong>Tgl Cetak:</strong> {{ $printedAt }}</div>
                <div><strong>Dicetak Oleh:</strong> {{ $printedBy }}</div>
                <div><strong>Filter Unit:</strong> {{ $unitFilterLabel }}</div>
            </td>
        </tr>
    </table>

    <!-- Title Section -->
    <div class="report-title-section">
        <h2 class="report-title">LAPORAN REKAPITULASI KEUANGAN PER UNIT & GELOMBANG</h2>
        <div class="report-subtitle">
            TAHUN AJARAN {{ $periodName }} &bull; REKAP TARGET TAGIHAN, KAS POKOK BERSIH MASUK, DAN SISA PIUTANG
        </div>
    </div>

    <!-- 4 KPI Summary Cards -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-card emerald" style="width: 25%;">
                <span class="kpi-label">Kas Pokok Bersih Masuk</span>
                <span class="kpi-value" style="color: #065f46;">Rp {{ number_format($totalNetCashIn, 0, ',', '.') }}</span>
                <span class="kpi-sub">Total Penerimaan Bersih Sekolah</span>
            </td>
            <td class="kpi-card slate" style="width: 25%;">
                <span class="kpi-label">Target Tagihan Netto (DSP)</span>
                <span class="kpi-value" style="color: #1e293b;">Rp {{ number_format($totalNetDSP, 0, ',', '.') }}</span>
                <span class="kpi-sub">Diskon Disetujui: Rp {{ number_format($totalDiscount, 0, ',', '.') }}</span>
            </td>
            <td class="kpi-card indigo" style="width: 25%;">
                <span class="kpi-label">Kolektibilitas DSP</span>
                <span class="kpi-value" style="color: #3730a3;">{{ $collectionRate }}%</span>
                <span class="kpi-sub">{{ $lunasCount }} Lunas dari {{ $totalDSPCandidatesCount }} Murid</span>
            </td>
            <td class="kpi-card rose" style="width: 25%;">
                <span class="kpi-label">Sisa Piutang DSP</span>
                <span class="kpi-value" style="color: #9f1239;">Rp {{ number_format($totalRemainingDSP, 0, ',', '.') }}</span>
                <span class="kpi-sub">{{ $sebagianCount + $belumBayarCount }} Murid Memiliki Tunggakan</span>
            </td>
        </tr>
    </table>

    <!-- Section 1: Breakdown per Unit -->
    <div class="section-heading">1. Rekapitulasi Realisasi Keuangan per Unit Sekolah</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 18%;">Unit Sekolah</th>
                <th class="text-center" style="width: 8%;">Murid DSP</th>
                <th class="text-right" style="width: 12%;">Target DSP Netto</th>
                <th class="text-right" style="width: 12%;">Pokok DSP Masuk</th>
                <th class="text-right" style="width: 12%;">Sisa Piutang</th>
                <th class="text-right" style="width: 10%;">Pokok Formulir</th>
                <th class="text-right" style="width: 12%;">Total Kas Bersih</th>
                <th class="text-right" style="width: 9%;">Mutasi Bruto</th>
                <th class="text-center" style="width: 7%;">Capaian</th>
            </tr>
        </thead>
        <tbody>
            @forelse($unitBreakdown as $ub)
                <tr>
                    <td><strong>{{ $ub['unit']->name }}</strong></td>
                    <td class="text-center font-bold">{{ $ub['count'] }} anak</td>
                    <td class="text-right">Rp {{ number_format($ub['net'], 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold; color: #059669;">Rp {{ number_format($ub['paid'], 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold; color: {{ $ub['remaining'] > 0 ? '#b91c1c' : '#64748b' }};">
                        Rp {{ number_format($ub['remaining'], 0, ',', '.') }}
                    </td>
                    <td class="text-right">Rp {{ number_format($ub['form_fee_net'], 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: 800; color: #065f46; background-color: #f0fdf4;">
                        Rp {{ number_format($ub['total_net_cash'], 0, ',', '.') }}
                    </td>
                    <td class="text-right" style="color: #64748b;">Rp {{ number_format($ub['gross_mutasi'], 0, ',', '.') }}</td>
                    <td class="text-center font-bold">{{ $ub['percentage'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 10px; color: #94a3b8; font-style: italic;">Tidak ada data unit tersedia.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td class="font-bold">TOTAL KESELURUHAN:</td>
                <td class="text-center">{{ $totalDSPCandidatesCount }} anak</td>
                <td class="text-right">Rp {{ number_format($totalNetDSP, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #059669;">Rp {{ number_format($dspFeeNet, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #b91c1c;">Rp {{ number_format($totalRemainingDSP, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($formFeeNet, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #065f46; background-color: #dcfce7;">Rp {{ number_format($totalNetCashIn, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalGrossRevenue, 0, ',', '.') }}</td>
                <td class="text-center">{{ $collectionRate }}%</td>
            </tr>
        </tfoot>
    </table>

    <!-- Section 2: Breakdown per Gelombang -->
    <div class="section-heading">2. Realisasi Penerimaan per Gelombang Pendaftaran</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Gelombang Pendaftaran</th>
                <th class="text-center" style="width: 12%;">Murid Tagihan</th>
                <th class="text-right" style="width: 18%;">Target Tagihan Netto</th>
                <th class="text-right" style="width: 18%;">Pokok Terbayar</th>
                <th class="text-right" style="width: 18%;">Sisa Piutang</th>
                <th class="text-center" style="width: 9%;">% Capaian</th>
            </tr>
        </thead>
        <tbody>
            @forelse($waveBreakdown as $wb)
                <tr>
                    <td><strong>{{ $wb['wave']->name }}</strong></td>
                    <td class="text-center font-bold">{{ $wb['count'] }} anak</td>
                    <td class="text-right">Rp {{ number_format($wb['net'], 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold; color: #059669;">Rp {{ number_format($wb['paid'], 0, ',', '.') }}</td>
                    <td class="text-right" style="font-weight: bold; color: {{ $wb['remaining'] > 0 ? '#b91c1c' : '#64748b' }};">
                        Rp {{ number_format($wb['remaining'], 0, ',', '.') }}
                    </td>
                    <td class="text-center font-bold">{{ $wb['percentage'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 10px; color: #94a3b8; font-style: italic;">Tidak ada data gelombang tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signatures Block -->
    <table class="signature-table">
        <tr>
            <td style="text-align: left; padding-left: 20px;">
                <div class="signature-title">
                    Mengetahui,<br>
                    <strong>Ketua Yayasan / Kepala Keuangan</strong>
                </div>
                <div class="signature-name">( __________________________ )</div>
                <div class="signature-role">NIP / NIY. .....................................</div>
            </td>
            <td style="text-align: right; padding-right: 20px;">
                <div class="signature-title">
                    Kota Malang, {{ now()->translatedFormat('d F Y') }}<br>
                    <strong>Bendahara & Panitia SPMB</strong>
                </div>
                <div class="signature-name">( {{ $printedBy }} )</div>
                <div class="signature-role">Staf Administrasi Keuangan</div>
            </td>
        </tr>
    </table>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}  |  Laporan Rekapitulasi Keuangan SPMB Yayasan Pendidikan Anak Saleh";
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
