<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $documentTitle ?? 'Laporan Rekapitulasi Calon Murid SPMB' }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 10mm 12mm 12mm 12mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #1e293b;
            line-height: 1.35;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        
        /* Kop Surat Header */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #059669;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .kop-table td {
            vertical-align: middle;
        }
        .kop-brand {
            font-size: 15px;
            font-weight: 800;
            color: #064e3b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .kop-subbrand {
            font-size: 10px;
            font-weight: 700;
            color: #059669;
            text-transform: uppercase;
            margin: 2px 0 0 0;
        }
        .kop-address {
            font-size: 8px;
            color: #64748b;
            margin: 2px 0 0 0;
        }
        .meta-box {
            text-align: right;
            font-size: 8.5px;
            color: #475569;
        }
        .meta-box .doc-badge {
            display: inline-block;
            background-color: #ecfdf5;
            color: #047857;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 4px;
            border: 1px solid #a7f3d0;
            font-size: 9px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        
        /* Document Title Section */
        .report-title-section {
            text-align: center;
            margin-bottom: 12px;
        }
        .report-title {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .report-subtitle {
            font-size: 9.5px;
            color: #059669;
            font-weight: 700;
            margin-top: 3px;
        }

        /* KPI Summary Grid Table */
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 12px;
        }
        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 10px;
            text-align: center;
            vertical-align: middle;
        }
        .kpi-card.emerald {
            background-color: #f0fdf4;
            border-color: #bbf7d0;
        }
        .kpi-card.blue {
            background-color: #eff6ff;
            border-color: #bfdbfe;
        }
        .kpi-card.rose {
            background-color: #fff1f2;
            border-color: #fecdd3;
        }
        .kpi-card.amber {
            background-color: #fffbeb;
            border-color: #fde68a;
        }
        .kpi-label {
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.5px;
            display: block;
        }
        .kpi-value {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            display: block;
            margin-top: 1px;
        }
        .kpi-sub {
            font-size: 7.5px;
            font-weight: 600;
            color: #64748b;
            display: block;
        }

        /* Distribution Summary Section */
        .dist-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .dist-table td {
            vertical-align: top;
            padding: 0 4px;
        }
        .dist-box {
            background-color: #fafafa;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 6px 8px;
        }
        .dist-title {
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            color: #064e3b;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 3px;
            margin-bottom: 4px;
        }
        .dist-item {
            font-size: 8px;
            margin-bottom: 2px;
            color: #334155;
        }
        .dist-item-badge {
            font-weight: 800;
            float: right;
            color: #047857;
        }

        /* Main Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin-bottom: 14px;
        }
        .data-table th {
            background-color: #059669;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 5px 3px;
            border: 1px solid #047857;
            text-align: left;
            font-size: 7.5px;
        }
        .data-table th.text-center, .data-table td.text-center {
            text-align: center;
        }
        .data-table th.text-right, .data-table td.text-right {
            text-align: right;
        }
        .data-table td {
            padding: 4px 3px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
            color: #334155;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 1.5px 4px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        .badge-info {
            background-color: #e0e7ff;
            color: #4338ca;
            border: 1px solid #c7d2fe;
        }
        .badge-purple {
            background-color: #f3e8ff;
            color: #7e22ce;
            border: 1px solid #e9d5ff;
        }
        .badge-amber {
            background-color: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        .badge-slate {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        /* Signatures Section */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .signature-table td {
            width: 50%;
            vertical-align: top;
            font-size: 9px;
        }
        .signature-title {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 50px;
        }
        .signature-name {
            font-weight: 800;
            color: #0f172a;
            text-decoration: underline;
        }
        .signature-role {
            font-size: 8px;
            color: #64748b;
            margin-top: 1px;
        }

        /* Footer */
        .page-footer {
            position: fixed;
            bottom: -8mm;
            left: 0;
            right: 0;
            height: 6mm;
            text-align: right;
            font-size: 7.5px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 2px;
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
                <div class="doc-badge">DOKUMEN REKAPITULASI RESMI</div>
                <div><strong>Tgl Cetak:</strong> {{ $printedAt }}</div>
                <div><strong>Dicetak Oleh:</strong> {{ $printedBy }}</div>
                <div><strong>Filter Unit:</strong> {{ $unitFilterLabel }}</div>
            </td>
        </tr>
    </table>

    <!-- Title Section -->
    <div class="report-title-section">
        <h2 class="report-title">LAPORAN REKAPITULASI DATA CALON MURID BARU</h2>
        <div class="report-subtitle">
            TAHUN AJARAN {{ $periodName }} &bull; STATUS: CALON MURID AKTIF (ENROLLMENT FEE LUNAS)
        </div>
    </div>

    <!-- Executive KPI Dashboard Cards -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-card emerald" style="width: 20%;">
                <span class="kpi-label">Total Pendaftar Aktif</span>
                <span class="kpi-value" style="color: #065f46;">{{ $stats['total'] }}</span>
                <span class="kpi-sub">Calon Murid</span>
            </td>
            <td class="kpi-card blue" style="width: 20%;">
                <span class="kpi-label">Laki-Laki (L)</span>
                <span class="kpi-value" style="color: #1e40af;">{{ $stats['male'] }}</span>
                <span class="kpi-sub">{{ $stats['total'] > 0 ? round(($stats['male'] / $stats['total']) * 100, 1) : 0 }}% dari Total</span>
            </td>
            <td class="kpi-card rose" style="width: 20%;">
                <span class="kpi-label">Perempuan (P)</span>
                <span class="kpi-value" style="color: #9f1239;">{{ $stats['female'] }}</span>
                <span class="kpi-sub">{{ $stats['total'] > 0 ? round(($stats['female'] / $stats['total']) * 100, 1) : 0 }}% dari Total</span>
            </td>
            <td class="kpi-card emerald" style="width: 20%;">
                <span class="kpi-label">Status {{ $regFeeLabel }}</span>
                <span class="kpi-value" style="color: #065f46;">100% LUNAS</span>
                <span class="kpi-sub">{{ $stats['total'] }} Siswa Terverifikasi</span>
            </td>
            <td class="kpi-card amber" style="width: 20%;">
                <span class="kpi-label">Diterima / Selesai</span>
                <span class="kpi-value" style="color: #92400e;">{{ $stageCounts['completed'] ?? 0 }}</span>
                <span class="kpi-sub">Murid Resmi Diterima</span>
            </td>
        </tr>
    </table>

    <!-- Distribution Summary Boxes (Gelombang & Jalur) -->
    <table class="dist-table">
        <tr>
            <td style="width: 33.3%;">
                <div class="dist-box">
                    <div class="dist-title">Distribusi Gelombang</div>
                    @forelse($waveStats as $ws)
                        <div class="dist-item">
                            <span>{{ $ws['name'] }}</span>
                            <span class="dist-item-badge">{{ $ws['count'] }} Murid ({{ $stats['total'] > 0 ? round(($ws['count'] / $stats['total']) * 100) : 0 }}%)</span>
                        </div>
                    @empty
                        <div class="dist-item" style="color: #94a3b8;">- Tidak ada data gelombang -</div>
                    @endforelse
                </div>
            </td>
            <td style="width: 33.3%;">
                <div class="dist-box">
                    <div class="dist-title">Distribusi Jalur Pendaftaran</div>
                    @forelse($typeStats as $ts)
                        <div class="dist-item">
                            <span>{{ $ts['name'] }}</span>
                            <span class="dist-item-badge">{{ $ts['count'] }} Murid ({{ $stats['total'] > 0 ? round(($ts['count'] / $stats['total']) * 100) : 0 }}%)</span>
                        </div>
                    @empty
                        <div class="dist-item" style="color: #94a3b8;">- Tidak ada data jalur -</div>
                    @endforelse
                </div>
            </td>
            <td style="width: 33.3%;">
                <div class="dist-box">
                    <div class="dist-title">Distribusi Tahapan Seleksi SPMB</div>
                    <div class="dist-item">
                        <span>Observasi / Ta'aruf:</span>
                        <span class="dist-item-badge">{{ ($stageCounts['submitted'] ?? 0) + ($stageCounts['verified'] ?? 0) }} Murid</span>
                    </div>
                    <div class="dist-item">
                        <span>Persetujuan & Administrasi:</span>
                        <span class="dist-item-badge">{{ ($stageCounts['taaruf_completed'] ?? 0) + ($stageCounts['agreement_signed'] ?? 0) }} Murid</span>
                    </div>
                    <div class="dist-item">
                        <span>Lulus & Diterima (Selesai):</span>
                        <span class="dist-item-badge">{{ $stageCounts['completed'] ?? 0 }} Murid</span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Main Candidates Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 3%;">No</th>
                <th style="width: 9%;">No. Registrasi</th>
                <th style="width: 7%;">Tgl Daftar</th>
                <th style="width: 16%;">Nama Lengkap Calon Murid</th>
                <th class="text-center" style="width: 4%;">L/P</th>
                <th style="width: 7%;">Unit</th>
                <th style="width: 9%;">Gelombang</th>
                <th style="width: 9%;">Jalur</th>
                <th class="text-center" style="width: 10%;">Status Tahapan</th>
                <th class="text-center" style="width: 8%;">{{ $regFeeLabel }}</th>
                <th class="text-center" style="width: 9%;">Biaya Masuk</th>
                <th style="width: 9%;">WhatsApp Ortu</th>
            </tr>
        </thead>
        <tbody>
            @forelse($candidates as $index => $c)
                @php
                    $genderCode = strtoupper(substr($c->gender ?? '-', 0, 1));
                    $isDSPStage = in_array($c->registration_status, ['taaruf_completed', 'agreement_signed', 'completed']);
                    $net = $isDSPStage ? $c->net_fee : 0;
                    $paidDSP = $isDSPStage ? $c->total_paid_final_fee : 0;
                    $remaining = $isDSPStage ? $c->remaining_balance : 0;

                    $dspStatusText = '-';
                    $dspBadgeClass = 'badge-slate';
                    if ($isDSPStage) {
                        if ($c->is_dispensation) {
                            $dspStatusText = 'Dispensasi';
                            $dspBadgeClass = 'badge-purple';
                        } elseif ($remaining <= 0 && $net > 0 && $paidDSP > 0) {
                            $dspStatusText = 'LUNAS';
                            $dspBadgeClass = 'badge-success';
                        } elseif ($paidDSP > 0 && $remaining > 0) {
                            $dspStatusText = 'Sebagian';
                            $dspBadgeClass = 'badge-info';
                        } else {
                            $dspStatusText = 'Belum Bayar';
                            $dspBadgeClass = 'badge-amber';
                        }
                    }

                    $stageBadgeClass = match($c->registration_status) {
                        'completed' => 'badge-success',
                        'agreement_signed' => 'badge-purple',
                        'taaruf_completed' => 'badge-amber',
                        'verified' => 'badge-info',
                        'submitted' => 'badge-info',
                        default => 'badge-slate'
                    };

                    $stageLabel = match($c->registration_status) {
                        'completed' => 'DITERIMA',
                        'agreement_signed' => 'ADMINISTRASI',
                        'taaruf_completed' => 'PERSETUJUAN',
                        'verified' => "TA'ARUF",
                        'submitted' => 'VERIFIKASI',
                        'draft' => 'FORMULIR',
                        default => strtoupper($c->registration_status ?? '-')
                    };
                @endphp
                <tr>
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-weight: bold; color: #0f172a;">{{ $c->id_label ?? '-' }}</td>
                    <td>{{ $c->created_at ? $c->created_at->format('d/m/Y') : '-' }}</td>
                    <td>
                        <strong style="color: #0f172a;">{{ $c->candidate_name ?? '-' }}</strong>
                        @if($c->nickname)
                            <span style="color: #64748b; font-size: 7px;">({{ $c->nickname }})</span>
                        @endif
                    </td>
                    <td class="text-center font-bold" style="color: {{ $genderCode === 'L' ? '#1d4ed8' : '#be123c' }};">
                        {{ $genderCode }}
                    </td>
                    <td><strong>{{ strtoupper($c->unit->code ?? ($c->unit->name ?? '-')) }}</strong></td>
                    <td>{{ $c->wave->name ?? '-' }}</td>
                    <td>{{ $c->type->name ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge {{ $stageBadgeClass }}">{{ $stageLabel }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-success">LUNAS</span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $dspBadgeClass }}">{{ $dspStatusText }}</span>
                    </td>
                    <td style="font-family: monospace; font-size: 7.5px;">{{ $c->parent_phone ?: ($c->father_phone ?: ($c->mother_phone ?: '-')) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 15px; color: #94a3b8; font-style: italic;">
                        Tidak ada data calon murid aktif yang sesuai dengan kriteria filter.
                    </td>
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
                    <strong>Kepala Unit / Ketua SPMB</strong>
                </div>
                <div class="signature-name">( __________________________ )</div>
                <div class="signature-role">NIP / NIY. .....................................</div>
            </td>
            <td style="text-align: right; padding-right: 20px;">
                <div class="signature-title">
                    Kota Malang, {{ now()->translatedFormat('d F Y') }}<br>
                    <strong>Koordinator Administrasi SPMB</strong>
                </div>
                <div class="signature-name">( {{ $printedBy }} )</div>
                <div class="signature-role">Petugas Administrasi & Pendaftaran</div>
            </td>
        </tr>
    </table>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}  |  Laporan Resmi SPMB Sekolah Anak Saleh Malang";
            $font = $fontMetrics->getFont("Helvetica", "normal");
            $size = 7.5;
            $color = array(0.5, 0.5, 0.5);
            $word_space = 0.0;
            $char_space = 0.0;
            $angle = 0.0;
            $pdf->page_text(340, 570, $text, $font, $size, $color, $word_space, $char_space, $angle);
        }
    </script>
</body>
</html>
