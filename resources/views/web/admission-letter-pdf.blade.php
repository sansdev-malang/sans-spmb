<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keputusan Penerimaan - {{ $registration->candidate_name }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 12px;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 40px;
            background-color: #ffffff;
            position: relative;
        }
        /* Kop Surat Header */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px double #059669;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .kop-table td {
            vertical-align: middle;
        }
        .school-title {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .school-subtitle {
            font-size: 11px;
            color: #059669;
            font-weight: bold;
            text-transform: uppercase;
            margin: 2px 0 0 0;
        }
        .school-address {
            font-size: 9px;
            color: #64748b;
            margin: 4px 0 0 0;
        }
        /* Document Title */
        .doc-title-section {
            text-align: center;
            margin-bottom: 30px;
        }
        .doc-title {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 0;
            letter-spacing: 1px;
        }
        .doc-number {
            font-size: 10px;
            color: #64748b;
            margin: 5px 0 0 0;
            font-family: monospace;
        }
        /* Content Paragraphs */
        .greeting {
            font-weight: bold;
            margin-bottom: 12px;
            color: #0f172a;
        }
        .opening-text {
            text-align: justify;
            margin-bottom: 20px;
            color: #334155;
        }
        /* Student Details Table */
        .student-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }
        .student-table td {
            padding: 10px 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        .student-table tr:last-child td {
            border-bottom: none;
        }
        .label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            width: 35%;
        }
        .value {
            font-size: 12px;
            color: #0f172a;
            font-weight: bold;
        }
        /* Status Banner */
        .status-box {
            background-color: #ecfdf5;
            border: 1px solid #10b981;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            margin-bottom: 25px;
        }
        .status-text {
            color: #047857;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 0;
        }
        /* Footer Text */
        .closing-text {
            text-align: justify;
            margin-bottom: 35px;
            color: #334155;
        }
        /* Signature Area */
        .sig-section {
            width: 100%;
            margin-top: 30px;
        }
        .sig-table {
            width: 100%;
            border-collapse: collapse;
        }
        .sig-table td {
            width: 50%;
            vertical-align: top;
        }
        .sig-date {
            margin-bottom: 5px;
            color: #475569;
        }
        .sig-title {
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 50px;
        }
        .sig-name {
            font-weight: bold;
            color: #0f172a;
            text-decoration: underline;
        }
        .sig-nip {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
        .watermark {
            position: absolute;
            bottom: 40px;
            left: 40px;
            opacity: 0.05;
            font-size: 80px;
            font-weight: bold;
            color: #059669;
            transform: rotate(-30deg);
            z-index: -1;
            pointer-events: none;
        }
    </style>
</head>
<body>
    @php
        $months = [
            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
            'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
            'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
            'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
        ];
        
        $dateStr = now()->format('d F Y');
        foreach ($months as $eng => $ind) {
            $dateStr = str_replace($eng, $ind, $dateStr);
        }
        
        $regDateStr = $registration->created_at->format('d F Y');
        foreach ($months as $eng => $ind) {
            $regDateStr = str_replace($eng, $ind, $regDateStr);
        }
    @endphp

    <div class="container">
        <!-- Watermark -->
        <div class="watermark">SANS</div>

        <!-- Kop Surat -->
        <table class="kop-table">
            <tr>
                <td style="width: 15%; text-align: left;">
                    <!-- Place for logo placeholder or plain badge -->
                    <div style="background-color: #059669; color: white; width: 45px; height: 45px; line-height: 45px; text-align: center; border-radius: 10px; font-weight: bold; font-size: 18px;">
                        AS
                    </div>
                </td>
                <td style="width: 85%; text-align: left; padding-left: 10px;">
                    <div class="school-title">Yayasan Anak Saleh Malang</div>
                    <div class="school-subtitle">{{ $registration->unit->name ?? 'Sekolah Anak Saleh' }}</div>
                    <div class="school-address">Jl. Candi Panggung No. 1A, Malang | Telp: (0341) 404040 | Email: info@anaksaleh.sch.id</div>
                </td>
            </tr>
        </table>

        <!-- Title Section -->
        <div class="doc-title-section">
            <h1 class="doc-title">Surat Keterangan Penerimaan</h1>
            <p class="doc-number">Nomor: SKP/SANS-{{ substr($registration->period->year ?? '2026', 0, 4) }}/{{ str_pad($registration->id, 4, '0', STR_PAD_LEFT) }}</p>
        </div>

        <!-- Content -->
        <p class="greeting">Bismillahirrahmanirrahim,</p>
        <p class="opening-text">
            Berdasarkan hasil rangkaian Evaluasi Kesiapan Belajar (Tes Observasi) dan penyelesaian kelengkapan administrasi daftar ulang pada Sistem Penerimaan Murid Baru (SPMB) Sekolah Anak Saleh Tahun Pelajaran {{ $registration->period->year ?? '2026/2027' }}, Panitia SPMB dengan ini menetapkan bahwa:
        </p>

        <!-- Student Table -->
        <table class="student-table">
            <tr>
                <td class="label">Nama Calon Siswa</td>
                <td class="value">{{ $registration->candidate_name }}</td>
            </tr>
            <tr>
                <td class="label">No. Registrasi</td>
                <td class="value">SANS-{{ substr($registration->period->year ?? '2026', 0, 4) }}-{{ str_pad($registration->id, 4, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <td class="label">Unit Pendidikan</td>
                <td class="value">{{ $registration->unit->name ?? '-' }} ({{ $registration->admission_level }})</td>
            </tr>
            <tr>
                <td class="label">Program Kelas</td>
                <td class="value">{{ $registration->classProgram->name ?? 'Reguler' }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Registrasi</td>
                <td class="value">{{ $regDateStr }}</td>
            </tr>
        </table>

        <!-- Status Declaration -->
        <div class="status-box">
            <h2 class="status-text">Lulus & Diterima</h2>
        </div>

        <p class="closing-text">
            Sebagai siswa resmi di {{ $registration->unit->name ?? 'Sekolah Anak Saleh' }}. Selamat bergabung menjadi bagian dari keluarga besar Sekolah Anak Saleh. Semoga ananda senantiasa dirahmat Allah SWT dan dapat tumbuh kembang secara optimal untuk menjadi generasi shalih, cerdas, dan mandiri.
        </p>

        <!-- Signatures -->
        <div class="sig-section">
            <table class="sig-table">
                <tr>
                    <td>
                        <!-- Optional left block (e.g. barcode) -->
                        <div style="margin-top: 25px;">
                            <!-- Placeholder QR / Barcode -->
                            <div style="border: 1px solid #cbd5e1; width: 65px; height: 65px; text-align: center; line-height: 65px; font-size: 8px; color: #94a3b8; border-radius: 6px; font-family: monospace;">
                                VERIFIED
                            </div>
                        </div>
                    </td>
                    <td style="text-align: right;">
                        <div class="sig-date">Malang, {{ $dateStr }}</div>
                        <div class="sig-title">Ketua Panitia SPMB,</div>
                        <div class="sig-name">Hj. Lilik Handayani, S.Pd</div>
                        <div class="sig-nip">NIP. 19780512 200501 2 003</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
