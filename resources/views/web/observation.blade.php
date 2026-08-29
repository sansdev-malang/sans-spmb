@extends('layouts.portal')

@section('title', 'Tes Observasi & Kesanggupan - Portal SPMB')

@section('content')
<style>
    /* Styling to make sure WYSIWYG rich text content renders beautifully and matches Word document margins */
    .agreement-body ol {
        list-style-type: none !important;
        padding-left: 0 !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
        counter-reset: list-0 !important;
    }
    .agreement-body ul {
        list-style-type: disc !important;
        padding-left: 1.5rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }
    .agreement-body li {
        list-style-type: none !important;
        position: relative !important;
        padding-left: 1.5rem !important;
        margin-bottom: 0.4rem !important;
        line-height: 1.65 !important;
        color: #334155 !important;
    }
    .agreement-body li:not([class*="ql-indent"]) {
        counter-increment: list-0 !important;
        counter-reset: list-1 list-2 list-3 list-4 list-5 list-6 list-7 list-8 list-9 !important;
    }
    .agreement-body li:not([class*="ql-indent"])::before {
        content: counter(list-0, decimal) ". " !important;
        position: absolute !important;
        left: 0 !important;
        font-weight: bold !important;
        color: #334155 !important;
    }
    .dark .agreement-body li {
        color: #cbd5e1 !important;
    }
    .dark .agreement-body li:not([class*="ql-indent"])::before {
        color: #cbd5e1 !important;
    }
    /* Render lower-alpha prefixes for ql-indent-1 level list items */
    .agreement-body li.ql-indent-1 {
        list-style-type: none !important;
        position: relative !important;
        padding-left: 1.5rem !important;
        margin-left: 1.5rem !important;
        counter-increment: list-1 !important;
        counter-reset: list-2 list-3 list-4 list-5 list-6 list-7 list-8 list-9 !important;
    }
    .agreement-body li.ql-indent-1::before {
        content: counter(list-1, lower-alpha) ". " !important;
        position: absolute !important;
        left: 0 !important;
        font-weight: normal !important;
        color: #475569 !important;
    }
    .dark .agreement-body li.ql-indent-1::before {
        color: #94a3b8 !important;
    }
    /* Render level-2 nested list items further to the right */
    .agreement-body li.ql-indent-2 {
        list-style-type: none !important;
        position: relative !important;
        padding-left: 1.5rem !important;
        margin-left: 3rem !important;
        counter-increment: list-2 !important;
        counter-reset: list-3 list-4 list-5 list-6 list-7 list-8 list-9 !important;
    }
    .agreement-body li.ql-indent-2::before {
        content: "(" counter(list-2, decimal) ") " !important;
        position: absolute !important;
        left: 0 !important;
        font-weight: normal !important;
        color: #475569 !important;
    }
    .dark .agreement-body li.ql-indent-2::before {
        color: #94a3b8 !important;
    }
    .agreement-body p {
        margin-top: 0.75rem !important;
        margin-bottom: 0.75rem !important;
        line-height: 1.65 !important;
        color: #334155 !important;
    }
    .dark .agreement-body p {
        color: #cbd5e1 !important;
    }
    .dark .agreement-body strong {
        color: #f8fafc !important;
    }
    .metadata-row {
        display: grid !important;
        grid-template-columns: 165px 10px 1fr !important;
        column-gap: 8px !important;
        margin-top: 4px !important;
        margin-bottom: 4px !important;
        line-height: 1.65 !important;
    }
    /* Responsive mobile formatting */
    @media (max-width: 640px) {
        .metadata-row {
            grid-template-columns: 1fr !important;
            row-gap: 1px !important;
            margin-top: 8px !important;
            margin-bottom: 8px !important;
        }
        .metadata-row div:nth-child(2) {
            display: none !important;
        }
    }
</style>

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
                <!-- 1. State: Verified (Informasi Ta'aruf Offline) -->
                <div class="space-y-6">
                    <div class="border border-brand-emerald/30 bg-emerald-50/10 rounded-2xl p-6 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 bg-brand-emerald text-white rounded-xl flex items-center justify-center font-bold text-lg">
                                🤝
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 dark:text-white">Undangan Sesi Ta'aruf Offline</h3>
                                <p class="text-xs text-slate-500 font-semibold">Tatap Muka di Unit Sekolah Anak Saleh</p>
                            </div>
                        </div>
                        
                        <div class="bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div>
                                    <span class="text-slate-400 block">Unit Sekolah</span>
                                    <span class="font-bold text-slate-800 dark:text-white">{{ $registration->unit->name }} ({{ $registration->unit->code }})</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block">Tingkat Kelas</span>
                                    <span class="font-bold text-slate-800 dark:text-white">{{ $registration->grade->name }} ({{ $registration->classProgram->name ?? 'Reguler' }})</span>
                                </div>
                                <div class="sm:col-span-2">
                                    <span class="text-slate-400 block">No. HP Wali Terdaftar (Penerima Undangan WA)</span>
                                    <span class="font-bold text-slate-800 dark:text-white flex items-center gap-1">
                                        {{ $registration->parent_phone }}
                                        <span class="bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded text-[9px] font-bold">Aktif WhatsApp</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <p class="text-xs text-slate-650 dark:text-slate-400 leading-relaxed">
                            Jadwal tanggal dan waktu spesifik untuk sesi Ta'aruf tatap muka akan dikirimkan langsung oleh panitia unit bersangkutan melalui pesan resmi WhatsApp ke nomor di atas. Mohon pastikan nomor Anda selalu aktif.
                        </p>
                    </div>

                    <!-- Info Tahap Selanjutnya -->
                    <div class="bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200/60 dark:border-blue-900/50 p-5 rounded-2xl flex items-start gap-3">
                        <i data-lucide="info" class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0"></i>
                        <div class="space-y-1">
                            <h4 class="font-extrabold text-xs text-blue-800 dark:text-blue-300 uppercase tracking-wider">Tahap Selanjutnya: Administrasi Keuangan</h4>
                            <p class="text-xs text-slate-650 dark:text-slate-400 leading-relaxed">
                                Setelah rangkaian wawancara ta'aruf tatap muka selesai dilaksanakan dan status kelulusan disetujui oleh panitia unit, Anda akan diarahkan untuk menyetujui Surat Pernyataan Kesanggupan Orang Tua secara digital dan dapat melanjutkan ke tahap pelunasan biaya masuk sekolah pada menu <strong>Administrasi</strong>.
                            </p>
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-5 rounded-xl text-xs text-slate-500 leading-relaxed space-y-2">
                        <p class="font-bold text-slate-700 dark:text-slate-300">Ketentuan Kehadiran Sesi Ta'aruf Offline:</p>
                        <ul class="list-disc pl-4 space-y-1">
                            <li>Wali murid (Ayah dan Ibu) wajib hadir mendampingi calon siswa ke unit sekolah sesuai undangan.</li>
                            <li>Harap hadir 10 menit sebelum waktu undangan untuk registrasi kehadiran fisik.</li>
                            <li>Berpakaian rapi, sopan, dan Islami sesuai ketentuan lingkungan Sekolah Anak Saleh.</li>
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
                                                   <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 text-xs text-slate-650 dark:text-slate-350 space-y-3.5 max-h-[500px] overflow-y-auto leading-relaxed">
                                @if($agreementTemplate)
                                    <div class="flex flex-col items-end mb-5 select-none">
                                        <div class="border border-brand-emerald/20 bg-brand-emerald/5 dark:border-emerald-950/40 dark:bg-emerald-950/10 p-2.5 rounded-xl flex flex-col items-center text-center max-w-[280px]">
                                            <span class="bg-brand-emerald/10 text-brand-emerald dark:bg-emerald-950/30 dark:text-emerald-400 px-2.5 py-0.5 rounded-lg font-bold text-[8px] uppercase tracking-wider border border-brand-emerald/15">
                                                Untuk Kalangan Sendiri
                                            </span>
                                            <span class="text-[8px] text-slate-450 dark:text-slate-400 font-semibold mt-1.5 leading-normal">
                                                Dilarang memfoto, mengcopy, dan menyebarluaskan dokumen ini
                                            </span>
                                        </div>
                                    </div>
                                    <p class="font-bold text-center text-slate-800 dark:text-white uppercase tracking-wide border-b border-slate-200 dark:border-slate-800 pb-2 mb-3 whitespace-pre-line">{{ $agreementTemplate->title }}</p>
                                    <div class="space-y-3 agreement-body">
                                        {!! $agreementTemplate->content !!}
                                    </div>

                                    <!-- Dynamic Signature Footer Mockup -->
                                    <div class="border-t border-slate-200 dark:border-slate-800 pt-6 mt-6 select-none text-[10px] text-slate-655 dark:text-slate-400">
                                        <!-- Date row -->
                                        <div class="flex justify-end pr-4">
                                            <p class="font-bold text-slate-750 dark:text-slate-300">{{ $agreementTemplate->place }}, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</p>
                                        </div>
                                    </div>
                                @else
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
                                @endif
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="agree_rules" class="rounded text-brand-emerald focus:ring-brand-emerald mt-0.5" required>
                                <span class="text-xs text-slate-600 dark:text-slate-400">
                                    {{ $agreementTemplate->rules_consent_label ?? 'Saya menyetujui seluruh tata tertib dan peraturan akademik Sekolah Anak Saleh.' }}
                                </span>
                            </label>
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="agree_fees" class="rounded text-brand-emerald focus:ring-brand-emerald mt-0.5" required>
                                <span class="text-xs text-slate-600 dark:text-slate-400">
                                    {{ $agreementTemplate->fees_consent_label ?? 'Saya menyanggupi pemenuhan seluruh rincian biaya pendidikan dan administrasi masuk yayasan.' }}
                                </span>
                            </label>
                        </div>

                        <div class="space-y-2">
                            <label for="signature_name" class="block text-xs font-bold text-slate-650 dark:text-slate-350">Nama Lengkap Penandatangan (Orang Tua / Wali)</label>
                            <input type="text" id="signature_name" name="signature_name" value="{{ $registration->father_name ?? ($registration->mother_name ?? '') }}" class="w-full max-w-md rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:text-white" placeholder="Ketik nama lengkap Anda di sini..." required>
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
                        <p><strong>Penandatangan:</strong> {{ $registration->signature_name ?? ($registration->father_name ?? ($registration->mother_name ?? $registration->candidate_name)) }} (Orang Tua / Wali)</p>
                        <p><strong>Status:</strong> <span class="text-green-600 font-bold">DISAPAKATI / VALID</span></p>
                        <p><strong>Tanggal:</strong> {{ ($registration->signed_at ?? $registration->updated_at)->format('d M Y H:i') }} WIB</p>
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

<script>
     document.addEventListener('DOMContentLoaded', function() {
         const signatureInput = document.getElementById('signature_name');
         
         if (signatureInput) {
             // Function to synchronize signature element with dynamic-signature-name
             const syncParentNames = function(value) {
                 const cleanValue = value.trim();
                 const parentNameElements = document.querySelectorAll('.dynamic-signature-name');
                 parentNameElements.forEach(el => {
                     el.textContent = cleanValue ? cleanValue : '____________________';
                 });
             };
 
             // Run initial sync on load in case field is pre-filled
             syncParentNames(signatureInput.value);
 
             // Listen for changes as user types
             signatureInput.addEventListener('input', function() {
                 syncParentNames(this.value);
             });
         }
     });
 </script>
@endsection
