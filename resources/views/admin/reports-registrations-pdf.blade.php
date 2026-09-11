<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentTitle ?? 'Laporan Rekapitulasi & Alur Konversi Pendaftaran Calon Murid' }}</title>
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
            padding: 6px 8px;
            text-align: center;
            vertical-align: top;
        }
        .kpi-card.emerald {
            background-color: #ecfdf5;
            border-color: #a7f3d0;
        }
        .kpi-card.blue {
            background-color: #eff6ff;
            border-color: #bfdbfe;
        }
        .kpi-card.indigo {
            background-color: #eef2ff;
            border-color: #c7d2fe;
        }
        .kpi-card.amber {
            background-color: #fffbeb;
            border-color: #fde68a;
        }
        .kpi-title {
            font-size: 7px;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 3px;
        }
        .kpi-value {
            font-size: 12px;
            font-weight: 900;
            color: #0f172a;
        }
        .kpi-desc {
            font-size: 6.5px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Data Tables */
        .section-heading {
            font-size: 9.5px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            margin: 8px 0 4px 0;
            border-left: 3px solid #059669;
            padding-left: 5px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8px;
        }
        .data-table th {
            background-color: #047857;
            color: #ffffff;
            font-weight: 800;
            text-transform: uppercase;
            padding: 5px 6px;
            border: 1px solid #065f46;
            text-align: left;
        }
        .data-table th.center, .data-table td.center {
            text-align: center;
        }
        .data-table th.right, .data-table td.right {
            text-align: right;
        }
        .data-table td {
            padding: 4.5px 6px;
            border: 1px solid #cbd5e1;
            color: #334155;
        }
        .data-table tr.unit-header {
            background-color: #f1f5f9;
            font-weight: 800;
            color: #0f172a;
        }
        .data-table tr.grade-row {
            background-color: #ffffff;
        }
        .data-table tr:nth-child(even):not(.unit-header) {
            background-color: #f8fafc;
        }
        .data-table tr.total-row td {
            background-color: #e2e8f0;
            font-weight: 900;
            color: #0f172a;
            border-top: 2px solid #64748b;
            font-size: 8.5px;
        }

        /* Status Badges */
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 0.5px solid #6ee7b7;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
            border: 0.5px solid #fcd34d;
        }
        .badge-info {
            background-color: #e0e7ff;
            color: #3730a3;
            border: 0.5px solid #a5b4fc;
        }

        /* Side by Side Grid for Segmentations */
        .two-col-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .two-col-table td {
            vertical-align: top;
            padding: 0 4px;
            width: 33.33%;
        }

        /* Signature Area */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            page-break-inside: avoid;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 8px;
            color: #1e293b;
        }
        .signature-space {
            height: 45px;
        }
        .signature-name {
            font-weight: 800;
            text-decoration: underline;
            text-transform: uppercase;
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
                <p class="kop-subbrand">PANITIA SISTEM PENERIMAAN MURID BARU (SPMB)</p>
                <p class="kop-address">Jl. Candi Panggung Indah No. 1-3, Mojolangu, Kecamatan Lowokwaru, Kota Malang, Jawa Timur</p>
            </td>
            <td class="meta-box" style="width: 200px;">
                <span class="doc-badge">Dokumen Resmi SPMB</span>
                <div><strong>Tahun Ajaran:</strong> {{ $periodName }}</div>
                <div><strong>Filter Unit:</strong> {{ $unitFilterLabel }}</div>
                <div><strong>Waktu Cetak:</strong> {{ $printedAt }}</div>
                <div><strong>Dicetak Oleh:</strong> {{ $printedBy }}</div>
            </td>
        </tr>
    </table>

    <!-- Title Section -->
    <div class="report-title-section">
        <h2 class="report-title">Laporan Rekapitulasi & Alur Konversi Pendaftaran Calon Murid</h2>
        <p class="report-subtitle">Tahun Ajaran {{ $periodName }} &bull; Analisis Pipeline Konversi & Distribusi per Unit Sekolah</p>
    </div>

    <!-- 5 KPI Funnel Cards -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-card blue">
                <div class="kpi-title">1. Total Akun Terdaftar</div>
                <div class="kpi-value" style="color: #1d4ed8;">{{ $totalRegistered }} <span style="font-size: 8px; font-weight: normal;">Anak</span></div>
                <div class="kpi-desc">100% Pendaftar</div>
            </td>
            <td class="kpi-card">
                <div class="kpi-title">2. Formulir Masuk</div>
                <div class="kpi-value">{{ $totalSubmitted + $totalVerified }} <span style="font-size: 8px; font-weight: normal;">Anak</span></div>
                <div class="kpi-desc">{{ $rateDraftToSubmitted }}% dari akun</div>
            </td>
            <td class="kpi-card indigo">
                <div class="kpi-title">3. Berkas Terverifikasi</div>
                <div class="kpi-value" style="color: #4338ca;">{{ $totalVerified }} <span style="font-size: 8px; font-weight: normal;">Anak</span></div>
                <div class="kpi-desc">{{ $rateSubmittedToVerified }}% lolos berkas</div>
            </td>
            <td class="kpi-card amber">
                <div class="kpi-title">4. Lulus Observasi</div>
                <div class="kpi-value" style="color: #b45309;">{{ $totalTaarufCompleted }} <span style="font-size: 8px; font-weight: normal;">Anak</span></div>
                <div class="kpi-desc">{{ $rateVerifiedToTaaruf }}% dari berkas valid</div>
            </td>
            <td class="kpi-card emerald">
                <div class="kpi-title">5. Resmi Diterima (Lunas DSP)</div>
                <div class="kpi-value" style="color: #047857;">{{ $totalCompleted }} <span style="font-size: 8px; font-weight: normal;">Anak</span></div>
                <div class="kpi-desc" style="color: #047857; font-weight: bold;">{{ $overallConversionRate }}% Konversi Final</div>
            </td>
        </tr>
    </table>

    <!-- Section 1: Matriks Distribusi per Unit Sekolah & Jenjang Kelas -->
    <div class="section-heading">I. Matriks Rekapitulasi per Unit Sekolah & Jenjang Kelas</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="center">No.</th>
                <th>Unit Sekolah / Jenjang Kelas</th>
                <th class="center" style="width: 70px;">Pendaftar (Akun)</th>
                <th class="center" style="width: 65px;">Berkas Valid</th>
                <th class="center" style="width: 70px;">Lulus Observasi</th>
                <th class="center" style="width: 70px;">Akad Tertanda</th>
                <th class="center" style="width: 70px;">Resmi Diterima</th>
                <th class="right" style="width: 65px;">% Konversi</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($unitStats as $us)
                <tr class="unit-header">
                    <td class="center">{{ $no++ }}</td>
                    <td><strong>{{ $us['unit']->name }}</strong></td>
                    <td class="center font-bold">{{ $us['registered'] }} anak</td>
                    <td class="center font-bold">{{ $us['verified'] }} anak</td>
                    <td class="center font-bold">{{ $us['taaruf'] }} anak</td>
                    <td class="center font-bold">{{ $us['completed'] }} anak</td>
                    <td class="center font-bold" style="color: #047857;">{{ $us['completed'] }} anak</td>
                    <td class="right font-bold" style="color: #047857;">{{ $us['percentage'] }}%</td>
                </tr>
                @foreach($us['grades'] as $gs)
                    <tr class="grade-row">
                        <td></td>
                        <td style="padding-left: 18px; color: #475569;">&bull; {{ $gs['grade']->name }}</td>
                        <td class="center">{{ $gs['registered'] }}</td>
                        <td class="center">{{ $gs['verified'] }}</td>
                        <td class="center">{{ $gs['taaruf'] }}</td>
                        <td class="center">{{ $gs['completed'] }}</td>
                        <td class="center" style="font-weight: bold; color: #047857;">{{ $gs['completed'] }}</td>
                        <td class="right">{{ $gs['percentage'] }}%</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="8" class="center" style="padding: 10px;">Tidak ada data pendaftaran ditemukan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="right">TOTAL KESELURUHAN</td>
                <td class="center">{{ $totalRegistered }} anak</td>
                <td class="center">{{ $totalVerified }} anak</td>
                <td class="center">{{ $totalTaarufCompleted }} anak</td>
                <td class="center">{{ $totalAgreementSigned }} anak</td>
                <td class="center" style="color: #047857;">{{ $totalCompleted }} anak</td>
                <td class="right" style="color: #047857;">{{ $overallConversionRate }}%</td>
            </tr>
        </tfoot>
    </table>

    <!-- Section 2: Segmentasi Pendaftar (3 Columns) -->
    <div class="section-heading">II. Analisis Karakteristik & Segmentasi Calon Murid</div>
    <table class="two-col-table">
        <tr>
            <!-- Column 1: Gender Distribution -->
            <td>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th colspan="3">Komposisi Jenis Kelamin</th>
                        </tr>
                        <tr>
                            <th>Kategori</th>
                            <th class="center" style="width: 45px;">Jumlah</th>
                            <th class="right" style="width: 40px;">Porsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Laki-laki</td>
                            <td class="center">{{ $maleCount }} anak</td>
                            <td class="right font-bold">{{ $totalRegistered > 0 ? round(($maleCount / $totalRegistered) * 100, 1) : 0 }}%</td>
                        </tr>
                        <tr>
                            <td>Perempuan</td>
                            <td class="center">{{ $femaleCount }} anak</td>
                            <td class="right font-bold">{{ $totalRegistered > 0 ? round(($femaleCount / $totalRegistered) * 100, 1) : 0 }}%</td>
                        </tr>
                        @if($unknownGenderCount > 0)
                        <tr>
                            <td style="color: #94a3b8;">Belum Mengisi Profil</td>
                            <td class="center" style="color: #94a3b8;">{{ $unknownGenderCount }} anak</td>
                            <td class="right" style="color: #94a3b8;">{{ $totalRegistered > 0 ? round(($unknownGenderCount / $totalRegistered) * 100, 1) : 0 }}%</td>
                        </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td>Total</td>
                            <td class="center">{{ $totalRegistered }}</td>
                            <td class="right">100%</td>
                        </tr>
                    </tfoot>
                </table>
            </td>

            <!-- Column 2: Jalur Masuk Distribution -->
            <td>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th colspan="3">Distribusi Jalur Pendaftaran</th>
                        </tr>
                        <tr>
                            <th>Jalur Masuk</th>
                            <th class="center" style="width: 45px;">Jumlah</th>
                            <th class="right" style="width: 40px;">Porsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($typeStats as $ts)
                            <tr>
                                <td>{{ $ts['type']->name }}</td>
                                <td class="center">{{ $ts['registered'] }} anak</td>
                                <td class="right font-bold">{{ $ts['percentage'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="center">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td>Total</td>
                            <td class="center">{{ $totalRegistered }}</td>
                            <td class="right">100%</td>
                        </tr>
                    </tfoot>
                </table>
            </td>

            <!-- Column 3: Gelombang Pendaftaran -->
            <td>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th colspan="3">Distribusi Gelombang Pendaftaran</th>
                        </tr>
                        <tr>
                            <th>Gelombang</th>
                            <th class="center" style="width: 45px;">Jumlah</th>
                            <th class="right" style="width: 40px;">Porsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($waveStats as $ws)
                            <tr>
                                <td>{{ $ws['wave']->name }}</td>
                                <td class="center">{{ $ws['registered'] }} anak</td>
                                <td class="right font-bold">{{ $ws['percentage'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="center">Tidak ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td>Total</td>
                            <td class="center">{{ $totalRegistered }}</td>
                            <td class="right">100%</td>
                        </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
    </table>

    <!-- Signature Area -->
    <table class="signature-table">
        <tr>
            <td>
                <p>Mengetahui,</p>
                <p><strong>Ketua Panitia SPMB</strong></p>
                <div class="signature-space"></div>
                <p class="signature-name">( .................................................... )</p>
                <p style="font-size: 7px; color: #64748b;">NIP. ........................................</p>
            </td>
            <td>
                <p>Malang, {{ now()->translatedFormat('d F Y') }}</p>
                <p><strong>Sekretaris Panitia SPMB / Kepala Sekolah</strong></p>
                <div class="signature-space"></div>
                <p class="signature-name">( .................................................... )</p>
                <p style="font-size: 7px; color: #64748b;">NIP. ........................................</p>
            </td>
        </tr>
    </table>

</body>
</html>
