@extends('layouts.portal')

@section('title', 'Hasil Seleksi & Administrasi Akhir - Portal SPMB')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8 space-y-6">

    <!-- Top Navigation Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="text-xs font-bold text-brand-emerald dark:text-emerald-400 hover:underline flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Kembali ke Beranda Dasbor
        </a>
        <span class="text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-500 px-3 py-1.5 rounded-full font-bold uppercase tracking-wider">
            No. Registrasi: SANS-{{ substr($registration->period->year ?? '2026', 0, 4) }}-{{ str_pad($registration->id, 4, '0', STR_PAD_LEFT) }}
        </span>
    </div>

    <!-- MAIN CARD -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-150/80 dark:border-slate-800 overflow-hidden">
        
        <!-- CARD HEADER -->
        <div class="bg-brand-emerald text-white px-6 py-5 flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-lg flex items-center gap-2">
                    <i data-lucide="award" class="w-5 h-5 text-brand-yellow"></i>
                    Hasil Seleksi & Administrasi Akhir
                </h2>
                <p class="text-xs text-brand-yellow font-medium mt-0.5">
                    Pengumuman kelulusan resmi dan rincian pembiayaan pendidikan Sekolah Anak Saleh.
                </p>
            </div>
            @if($registration->registration_status === 'completed')
                <span class="bg-green-700 text-white font-bold text-[10px] uppercase tracking-widest px-3.5 py-1.5 rounded-full border border-green-500 shadow-sm flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-300 animate-ping"></span> Lunas & Resmi
                </span>
            @else
                <span class="bg-amber-600 text-white font-bold text-[10px] uppercase tracking-widest px-3.5 py-1.5 rounded-full border border-amber-455 shadow-sm">
                    Menunggu Pelunasan
                </span>
            @endif
        </div>

        <div class="p-8 space-y-8">
            
            <!-- ANNOUNCEMENT BANNER -->
            <div class="bg-gradient-to-r from-emerald-50 to-emerald-100/50 dark:from-emerald-950/10 dark:to-emerald-900/5 border border-emerald-200/60 dark:border-emerald-900/50 rounded-2xl p-6 flex flex-col sm:flex-row gap-5 items-center text-center sm:text-left">
                <div class="h-16 w-16 bg-brand-emerald text-white rounded-2xl flex items-center justify-center shadow-md flex-shrink-0">
                    <i data-lucide="party-popper" class="w-8 h-8 text-brand-yellow"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-lg font-black text-slate-850 dark:text-white">Alhamdulillah, Dinyatakan LULUS & DITERIMA</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Selamat kepada ananda <strong class="text-slate-800 dark:text-slate-200">{{ $registration->candidate_name }}</strong> yang telah lolos seluruh tahapan observasi kesiapan belajar dan berkas pendaftaran.
                    </p>
                </div>
            </div>

            <!-- STUDENT PROFILE META -->
            <div class="bg-slate-50 dark:bg-slate-955 rounded-2xl p-5 border border-slate-100 dark:border-slate-850 grid grid-cols-2 sm:grid-cols-5 gap-4 text-xs">
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Nama Calon Siswa</span>
                    <span class="font-extrabold text-slate-800 dark:text-slate-200 mt-1 block">{{ $registration->candidate_name }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Tingkat / Unit</span>
                    <span class="font-extrabold text-slate-800 dark:text-slate-200 mt-1 block">{{ $registration->admission_level }} - {{ $registration->unit->name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Program Kelas</span>
                    <span class="font-extrabold text-slate-800 dark:text-slate-200 mt-1 block">{{ $registration->classProgram->name ?? 'Reguler' }}</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Layanan Tambahan</span>
                    <span class="font-extrabold text-brand-emerald dark:text-emerald-450 mt-1 block">
                        {{ $registration->extraServices->count() > 0 ? $registration->extraServices->pluck('name')->implode(', ') : 'Tidak Ada' }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Tahun Pelajaran</span>
                    <span class="font-extrabold text-slate-800 dark:text-slate-200 mt-1 block">{{ $registration->period->year ?? '2026/2027' }}</span>
                </div>
            </div>

            <!-- TUITION FEES COMPONENT BREAKDOWN -->
            <div class="space-y-4">
                <h4 class="font-extrabold text-slate-850 dark:text-white text-xs uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center gap-1.5">
                    <i data-lucide="receipt" class="w-4 h-4 text-brand-emerald"></i> Rincian Biaya Pendidikan Masuk Awal
                </h4>

                <div class="bg-white dark:bg-slate-900 border border-slate-150/80 dark:border-slate-800 rounded-2xl overflow-hidden shadow-inner">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950 text-[10px] text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100 dark:border-slate-850">
                                <th class="p-4">Komponen Pembiayaan</th>
                                <th class="p-4 text-right">Nominal</th>
                                <th class="p-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
                            @if(isset($feeDetails['items']) && is_array($feeDetails['items']))
                                @foreach($feeDetails['items'] as $item)
                                    <tr class="text-slate-655 dark:text-slate-355">
                                        <td class="p-4 font-medium">{{ $item['name'] }}</td>
                                        <td class="p-4 text-right font-bold text-slate-800 dark:text-slate-250">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                                        <td class="p-4 text-center">
                                            @if($registration->registration_status === 'completed')
                                                <span class="text-[9px] bg-green-50 dark:bg-green-950/20 text-green-600 px-2 py-0.5 rounded font-bold uppercase tracking-wider">Lunas</span>
                                            @else
                                                <span class="text-[9px] bg-amber-50 dark:bg-amber-955/20 text-amber-600 px-2 py-0.5 rounded font-bold uppercase tracking-wider">Tanggungan</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="text-slate-655 dark:text-slate-355">
                                    <td class="p-4 font-medium">Uang Gedung (Sarana & Prasarana)</td>
                                    <td class="p-4 text-right font-bold text-slate-800 dark:text-slate-250">Rp {{ number_format($feeDetails['uang_gedung'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="p-4 text-center">
                                        @if($registration->registration_status === 'completed')
                                            <span class="text-[9px] bg-green-50 dark:bg-green-950/20 text-green-600 px-2 py-0.5 rounded font-bold uppercase tracking-wider">Lunas</span>
                                        @else
                                            <span class="text-[9px] bg-amber-50 dark:bg-amber-955/20 text-amber-600 px-2 py-0.5 rounded font-bold uppercase tracking-wider">Tanggungan</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="text-slate-655 dark:text-slate-355">
                                    <td class="p-4 font-medium">Paket Seragam Sekolah</td>
                                    <td class="p-4 text-right font-bold text-slate-800 dark:text-slate-250">Rp {{ number_format($feeDetails['seragam'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="p-4 text-center">
                                        @if($registration->registration_status === 'completed')
                                            <span class="text-[9px] bg-green-50 dark:bg-green-950/20 text-green-600 px-2 py-0.5 rounded font-bold uppercase tracking-wider">Lunas</span>
                                        @else
                                            <span class="text-[9px] bg-amber-50 dark:bg-amber-955/20 text-amber-600 px-2 py-0.5 rounded font-bold uppercase tracking-wider">Tanggungan</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="text-slate-655 dark:text-slate-355">
                                    <td class="p-4 font-medium">Biaya SPP Bulanan (Juli)</td>
                                    <td class="p-4 text-right font-bold text-slate-800 dark:text-slate-250">Rp {{ number_format($feeDetails['spp'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="p-4 text-center">
                                        @if($registration->registration_status === 'completed')
                                            <span class="text-[9px] bg-green-50 dark:bg-green-950/20 text-green-600 px-2 py-0.5 rounded font-bold uppercase tracking-wider">Lunas</span>
                                        @else
                                            <span class="text-[9px] bg-amber-50 dark:bg-amber-955/20 text-amber-600 px-2 py-0.5 rounded font-bold uppercase tracking-wider">Tanggungan</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr class="text-slate-655 dark:text-slate-355">
                                    <td class="p-4 font-medium">Uang Program Kegiatan Kesiswaan</td>
                                    <td class="p-4 text-right font-bold text-slate-800 dark:text-slate-250">Rp {{ number_format($feeDetails['kegiatan'] ?? 0, 0, ',', '.') }}</td>
                                    <td class="p-4 text-center">
                                        @if($registration->registration_status === 'completed')
                                            <span class="text-[9px] bg-green-50 dark:bg-green-950/20 text-green-600 px-2 py-0.5 rounded font-bold uppercase tracking-wider">Lunas</span>
                                        @else
                                            <span class="text-[9px] bg-amber-50 dark:bg-amber-955/20 text-amber-600 px-2 py-0.5 rounded font-bold uppercase tracking-wider">Tanggungan</span>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            <tr class="bg-slate-50/50 dark:bg-slate-950/30 text-xs font-black text-slate-800 dark:text-white uppercase border-t border-slate-150 dark:border-slate-800">
                                <td class="p-4">Total Biaya Masuk Awal</td>
                                <td class="p-4 text-right text-brand-emerald dark:text-emerald-400 text-sm">Rp {{ number_format($feeDetails['total'], 0, ',', '.') }}</td>
                                <td class="p-4 text-center">
                                    @if($registration->registration_status === 'completed')
                                        <span class="text-[10px] bg-green-500 text-white px-3 py-1 rounded font-bold uppercase tracking-wider shadow-sm">Lunas</span>
                                    @else
                                        <span class="text-[10px] bg-amber-500 text-white px-3 py-1 rounded font-bold uppercase tracking-wider shadow-sm">Belum Lunas</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- INSTRUCTIONS BOX -->
            <div class="bg-slate-50 dark:bg-slate-955 rounded-2xl p-6 border border-slate-100 dark:border-slate-800 space-y-3.5 text-xs text-slate-600 dark:text-slate-400">
                <h5 class="font-extrabold text-slate-800 dark:text-white flex items-center gap-1.5 uppercase tracking-wider text-[10px]">
                    <i data-lucide="info" class="w-4 h-4 text-brand-emerald"></i> Informasi Penting & Prosedur Daftar Ulang
                </h5>
                <ul class="list-disc pl-4 space-y-2.5 leading-relaxed">
                    @if($registration->registration_status !== 'completed')
                        <li><strong>Batas Pelunasan:</strong> Pembayaran wajib dilunasi paling lambat 7 hari kerja sejak tanggal persetujuan komitmen biaya ini disubmit.</li>
                        <li><strong>Metode Pembayaran:</strong> Klik tombol <strong>Lanjut ke Pembayaran Online</strong> di bawah untuk memilih metode transfer Virtual Account Bank (BNI) atau pemindaian kode QRIS secara instan.</li>
                        <li><strong>Daftar Ulang:</strong> Setelah pembayaran dikonfirmasi lunas oleh sistem, calon siswa secara resmi terdaftar dan Anda dapat mencetak Surat Keterangan Penerimaan (SKP) langsung dari halaman ini.</li>
                    @else
                        <li><strong>Status Resmi:</strong> Selamat, ananda telah resmi menjadi bagian dari keluarga besar Sekolah Anak Saleh.</li>
                        <li><strong>Surat Keputusan Penerimaan (SKP):</strong> Anda dapat mengunduh dan mencetak surat kelulusan resmi menggunakan tombol cetak di bawah ini.</li>
                        <li><strong>Bukti Pembayaran:</strong> Silakan simpan / cetak kwitansi lunas elektronik sebagai tanda bukti setoran awal Anda yang sah.</li>
                    @endif
                </ul>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="pt-4 flex flex-col sm:flex-row justify-center items-center gap-4">
                @if($registration->registration_status === 'completed')
                    <!-- Completed buttons -->
                    <button onclick="window.print()" class="w-full sm:w-auto bg-brand-emerald hover-emerald text-white px-8 py-3.5 rounded-xl font-bold text-xs shadow-md transition flex items-center justify-center gap-2">
                        <i data-lucide="printer" class="w-4.5 h-4.5"></i> Cetak Surat Kelulusan
                    </button>
                    @php
                        $successPayment = $registration->payments()->where('status', 'success')->latest()->first();
                    @endphp
                    @if($successPayment)
                        <a href="{{ route('dashboard.payment.receipt', $successPayment->id) }}" target="_blank" class="w-full sm:w-auto border border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-850 text-slate-700 dark:text-slate-300 px-8 py-3.5 rounded-xl font-bold text-xs shadow-sm transition flex items-center justify-center gap-2">
                            <i data-lucide="file-text" class="w-4.5 h-4.5 text-brand-emerald"></i> Cetak Kwitansi Pembayaran
                        </a>
                    @endif
                @else
                    <!-- Unpaid buttons -->
                    <a href="{{ route('dashboard.payment', $registration->id) }}" class="w-full sm:w-auto bg-brand-emerald hover-emerald text-white px-8 py-3.5 rounded-xl font-bold text-xs shadow-md transition flex items-center justify-center gap-2">
                        <i data-lucide="credit-card" class="w-4.5 h-4.5 text-brand-yellow animate-pulse"></i> Lanjut ke Pembayaran Online
                    </a>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
