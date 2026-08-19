@extends('layouts.portal')

@section('title', 'Tes Observasi Calon Siswa - Portal SPMB')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        <div class="bg-brand-emerald text-white px-6 py-5">
            <h2 class="font-extrabold text-lg flex items-center gap-2">
                <i data-lucide="video" class="w-5 h-5 text-brand-yellow"></i>
                Jadwal & Tautan Tes Observasi
            </h2>
            <p class="text-xs text-brand-yellow font-medium mt-0.5">Ujian wawancara dan observasi kesiapan belajar siswa secara online/virtual.</p>
        </div>

        <div class="p-6">
            @if ($registration->registration_status === 'verified' && $observationDetails)
                <div class="space-y-6">
                    <div class="border border-brand-emerald/30 bg-emerald-50/10 rounded-2xl p-6 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 bg-brand-emerald text-white rounded-xl flex items-center justify-center font-bold text-lg">
                                📅
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800">{{ $observationDetails['title'] }}</h3>
                                <p class="text-xs text-slate-500 font-semibold">{{ $observationDetails['datetime'] }}</p>
                            </div>
                        </div>
                        <div class="pt-4 flex flex-wrap gap-3">
                            <a href="{{ $observationDetails['zoom_link'] }}" target="_blank" class="bg-brand-emerald hover-emerald text-white px-5 py-2.5 rounded-lg font-bold text-xs shadow-sm transition flex items-center gap-2">
                                🎥 Masuk Zoom Meeting
                            </a>
                            <a href="{{ $observationDetails['guide_link'] }}" target="_blank" class="border border-slate-300 hover:bg-slate-50 text-slate-700 px-5 py-2.5 rounded-lg font-bold text-xs transition">
                                📖 Unduh Panduan Observasi
                            </a>
                        </div>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 p-5 rounded-xl text-xs text-slate-500 leading-relaxed space-y-2">
                        <p><strong>Ketentuan Tes Observasi Online:</strong></p>
                        <ul class="list-disc pl-4 space-y-1">
                            <li>Harap standby di Zoom Meeting 10 menit sebelum jadwal observasi dimulai.</li>
                            <li>Pastikan koneksi internet stabil dan kamera (webcam) aktif sepanjang proses tes.</li>
                            <li>Calon siswa didampingi oleh minimal satu orang tua/wali kandung selama sesi wawancara.</li>
                            <li>Siapkan dokumen asli Kartu Keluarga (KK) dan Akta Lahir untuk dicocokkan panitia saat observasi visual.</li>
                        </ul>
                    </div>
                </div>
            @else
                <div class="text-center py-8 space-y-3">
                    <span class="inline-flex items-center justify-center h-12 w-12 bg-slate-100 text-slate-400 rounded-full">
                        <i data-lucide="lock" class="w-6 h-6"></i>
                    </span>
                    <h3 class="font-bold text-slate-800 text-sm">Belum Dijadwalkan</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
                        Jadwal observasi dan link Zoom hanya akan diterbitkan setelah berkas pendaftaran Anda lolos verifikasi sukses di menu <strong>Verification</strong>.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
