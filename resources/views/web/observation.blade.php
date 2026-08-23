@extends('layouts.portal')

@section('title', 'Tes Observasi & Kesanggupan - Portal SPMB')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-150/80 dark:border-slate-800 overflow-hidden">
        
        <!-- Header -->
        <div class="bg-brand-emerald text-white px-6 py-5">
            <h2 class="font-extrabold text-lg flex items-center gap-2">
                <i data-lucide="video" class="w-5 h-5 text-brand-yellow"></i>
                Observasi & Pernyataan Kesanggupan
            </h2>
            <p class="text-xs text-brand-yellow font-medium mt-0.5">Ujian wawancara ta'aruf serta persetujuan surat komitmen biaya pendidikan.</p>
        </div>

        <div class="p-8">
            
            @if ($registration->registration_status === 'verified')
                <!-- 1. State: Verified (Jadwal Observasi Zoom Aktif) -->
                <div class="space-y-6">
                    <div class="border border-brand-emerald/30 bg-emerald-50/10 rounded-2xl p-6 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 bg-brand-emerald text-white rounded-xl flex items-center justify-center font-bold text-lg">
                                📅
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 dark:text-white">{{ $observationDetails['title'] ?? 'Tes Observasi secara daring' }}</h3>
                                <p class="text-xs text-slate-500 font-semibold">{{ $observationDetails['datetime'] ?? 'Sabtu, 26 Okt 2024. 08:00 - 10:00 WIB' }}</p>
                            </div>
                        </div>
                        <div class="pt-4 flex flex-wrap gap-3">
                            <a href="{{ $observationDetails['zoom_link'] ?? '#' }}" target="_blank" class="bg-brand-emerald hover-emerald text-white px-5 py-2.5 rounded-lg font-bold text-xs shadow-sm transition flex items-center gap-2">
                                <i data-lucide="video" class="w-4 h-4"></i> Masuk Zoom Meeting
                            </a>
                            <a href="{{ $observationDetails['guide_link'] ?? '#' }}" target="_blank" class="border border-slate-350 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 px-5 py-2.5 rounded-lg font-bold text-xs transition inline-flex items-center gap-1.5">
                                <i data-lucide="download" class="w-4 h-4"></i> Unduh Panduan
                            </a>
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-5 rounded-xl text-xs text-slate-500 leading-relaxed space-y-2">
                        <p class="font-bold text-slate-700 dark:text-slate-300">Ketentuan Tes Observasi Online / Ta'aruf:</p>
                        <ul class="list-disc pl-4 space-y-1">
                            <li>Harap standby di Zoom Meeting 10 menit sebelum jadwal observasi dimulai.</li>
                            <li>Pastikan koneksi internet stabil dan kamera (webcam) aktif sepanjang proses tes.</li>
                            <li>Calon siswa didampingi oleh minimal satu orang tua/wali kandung selama sesi wawancara.</li>
                            <li>Siapkan dokumen asli Kartu Keluarga (KK) dan Akta Lahir untuk dicocokkan panitia saat observasi visual.</li>
                        </ul>
                    </div>
                </div>

            @elseif ($registration->registration_status === 'taaruf_completed')
                <!-- 2. State: Ta'aruf Completed (Form Pernyataan Kesanggupan Wali Murid) -->
                <div class="space-y-6">
                    <div class="border border-green-200 bg-green-50/10 rounded-2xl p-6 flex gap-3.5 items-start">
                        <span class="inline-flex items-center justify-center h-10 w-10 bg-green-100 text-green-700 rounded-xl flex-shrink-0">
                            <i data-lucide="check" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <h3 class="font-extrabold text-slate-800 dark:text-white text-sm">Observasi / Ta'aruf Selesai</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                Ananda telah menyelesaikan rangkaian ujian observasi kesiapan belajar. Selanjutnya, silakan baca dan setujui Surat Pernyataan Kesanggupan berikut ini untuk melanjutkan ke tahap administrasi keuangan.
                            </p>
                        </div>
                    </div>

                    <form action="{{ route('dashboard.agreement.submit', $registration->id) }}" method="POST" class="space-y-6 border-t border-slate-100 dark:border-slate-800 pt-6">
                        @csrf
                        
                        <div class="space-y-4">
                            <h4 class="font-extrabold text-sm text-slate-800 dark:text-white">Surat Pernyataan Kesanggupan Orang Tua / Wali</h4>
                            
                            <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 text-xs text-slate-650 dark:text-slate-350 space-y-3.5 max-h-72 overflow-y-auto leading-relaxed">
                                <p class="font-bold text-center text-slate-800 dark:text-white">SURAT PERNYATAAN KESANGGUPAN MEMATUHI PERATURAN & BIAYA PENDIDIKAN</p>
                                <p>Saya yang bertanda tangan di bawah ini selaku Orang Tua / Wali murid dari calon siswa:</p>
                                <div class="pl-4 space-y-1 font-semibold">
                                    <p>Nama Calon Siswa : {{ $registration->candidate_name }}</p>
                                    <p>Unit & Program : {{ $registration->unit->name }} - {{ $registration->grade->name }}</p>
                                </div>
                                <p>Menyatakan dengan sesungguhnya dan penuh kesadaran bahwa:</p>
                                <ol class="list-decimal pl-4 space-y-2">
                                    <li><strong>Tata Tertib Sekolah:</strong> Sanggup mematuhi dan mendidik anak kami agar mematuhi seluruh peraturan, disiplin, dan tata tertib akademik maupun non-akademik di lingkungan Yayasan Sekolah Anak Saleh.</li>
                                    <li><strong>Komitmen Pembiayaan:</strong> Menyanggupi pelunasan seluruh biaya administrasi masuk awal (Uang Gedung, Seragam, Uang Kegiatan) sesuai ketentuan unit program yang dipilih, serta membayar SPP bulanan paling lambat tanggal 10 setiap bulannya.</li>
                                    <li><strong>Partisipasi Program:</strong> Bersedia berpartisipasi aktif dalam kegiatan komite sekolah dan mendukung penuh program pembiasaan ibadah harian anak di rumah.</li>
                                </ol>
                                <p>Demikian surat pernyataan ini dibuat untuk dipergunakan sebagaimana mestinya.</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="agree_rules" class="rounded text-brand-emerald focus:ring-brand-emerald mt-0.5" required>
                                <span class="text-xs text-slate-600 dark:text-slate-400">Saya menyetujui seluruh tata tertib dan peraturan akademik Sekolah Anak Saleh.</span>
                            </label>
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="agree_fees" class="rounded text-brand-emerald focus:ring-brand-emerald mt-0.5" required>
                                <span class="text-xs text-slate-600 dark:text-slate-400">Saya menyanggupi pemenuhan seluruh rincian biaya pendidikan dan administrasi masuk yayasan.</span>
                            </label>
                        </div>

                        <div class="space-y-2">
                            <label for="signature_name" class="block text-xs font-bold text-slate-650 dark:text-slate-350">Nama Lengkap Penandatangan (Orang Tua / Wali)</label>
                            <input type="text" id="signature_name" name="signature_name" class="w-full max-w-md rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:text-white" placeholder="Ketik nama lengkap Anda di sini..." required>
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl font-bold text-xs shadow-md transition flex items-center gap-1.5">
                                <i data-lucide="check-square" class="w-4 h-4"></i> Setujui & Tandatangani Surat Pernyataan
                            </button>
                        </div>
                    </form>
                </div>

            @elseif (in_array($registration->registration_status, ['agreement_signed', 'completed']))
                <!-- 3. State: Agreement Signed / Completed (Readonly signed status) -->
                <div class="space-y-6 text-center py-8 max-w-md mx-auto">
                    <div class="h-16 w-16 bg-green-50 dark:bg-green-950/20 text-green-600 rounded-3xl flex items-center justify-center mx-auto shadow-inner">
                        <i data-lucide="award" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-800 dark:text-white">Pernyataan Kesanggupan Ditandatangani</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Terima kasih. Anda telah menandatangani Surat Pernyataan Kesanggupan Tata Tertib dan Biaya Masuk Yayasan.
                    </p>
                    <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-4 rounded-xl text-left text-xs space-y-2">
                        <p class="font-bold text-slate-800 dark:text-white text-center border-b border-slate-100 dark:border-slate-800 pb-2">Status Persetujuan Digital</p>
                        <p><strong>Penandatangan:</strong> {{ $registration->candidate_name }} (Orang Tua / Wali)</p>
                        <p><strong>Status:</strong> <span class="text-green-600 font-bold">DISAPAKATI / VALID</span></p>
                        <p><strong>Tanggal:</strong> {{ $registration->updated_at->format('d M Y H:i') }} WIB</p>
                    </div>
                </div>

            @else
                <!-- 4. State: Lock (Belum Terverifikasi) -->
                <div class="text-center py-8 space-y-3">
                    <span class="inline-flex items-center justify-center h-12 w-12 bg-slate-100 dark:bg-slate-800 text-slate-450 rounded-full">
                        <i data-lucide="lock" class="w-6 h-6"></i>
                    </span>
                    <h3 class="font-bold text-slate-800 dark:text-white text-sm">Belum Dibuka</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-450 max-w-sm mx-auto leading-relaxed">
                        Tahapan tes observasi dan penandatanganan kesanggupan hanya akan aktif setelah berkas pendaftaran Anda lolos verifikasi sukses di menu <strong>Verification</strong>.
                    </p>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
