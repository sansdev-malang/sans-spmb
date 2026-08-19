@extends('layouts.portal')

@section('title', 'Hasil Seleksi Kelulusan Akhir - Portal SPMB')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        <div class="bg-brand-emerald text-white px-6 py-5">
            <h2 class="font-extrabold text-lg flex items-center gap-2">
                <i data-lucide="award" class="w-5 h-5 text-brand-yellow"></i>
                Pengumuman Hasil Seleksi SPMB
            </h2>
            <p class="text-xs text-brand-yellow font-medium mt-0.5">Hasil seleksi kelulusan akhir calon siswa baru Sekolah Anak Saleh.</p>
        </div>

        <div class="p-6">
            @if ($registration->registration_status === 'verified')
                <!-- Simulation result showing success as verified -->
                <div class="border border-green-200 bg-green-50/15 rounded-2xl p-6 text-center space-y-4">
                    <span class="inline-flex items-center justify-center h-16 w-16 bg-green-100 text-green-700 rounded-full border border-green-200 shadow-md">
                        <i data-lucide="party-popper" class="w-8 h-8"></i>
                    </span>
                    
                    <div class="space-y-1">
                        <h3 class="text-xl font-black text-green-800">Selamat! Anda Dinyatakan LULUS</h3>
                        <p class="text-xs text-slate-500 font-medium">Ananda <strong>{{ $registration->candidate_name }}</strong> dinyatakan memenuhi syarat penerimaan siswa baru.</p>
                    </div>

                    <div class="bg-white border border-green-200 p-4 rounded-xl max-w-sm mx-auto text-xs text-slate-600 text-left space-y-1 shadow-sm">
                        <p><strong>Nama Lengkap:</strong> {{ $registration->candidate_name }}</p>
                        <p><strong>No. Pendaftaran:</strong> SANS-2026-{{ str_pad($registration->id, 4, '0', STR_PAD_LEFT) }}</p>
                        <p><strong>Tingkat:</strong> {{ $registration->admission_level ?? '-' }}</p>
                        <p><strong>Status Seleksi:</strong> LULUS SELEKSI UTAMA</p>
                    </div>

                    <div class="pt-2 flex justify-center gap-3">
                        <a href="#" onclick="alert('Mengunduh kartu tanda kelulusan...')" class="bg-brand-emerald hover-emerald text-white px-5 py-2.5 rounded-lg font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                            <i data-lucide="download" class="w-4 h-4"></i> Cetak Bukti Kelulusan
                        </a>
                        <a href="#" onclick="alert('Membuka formulir daftar ulang...')" class="bg-brand-yellow hover:bg-yellow-400 text-slate-900 px-5 py-2.5 rounded-lg font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                            <i data-lucide="check-square" class="w-4 h-4"></i> Isi Formulir Daftar Ulang
                        </a>
                    </div>
                </div>
            @elseif ($registration->registration_status === 'failed')
                <div class="border border-red-200 bg-red-50/15 rounded-2xl p-6 text-center space-y-4">
                    <span class="inline-flex items-center justify-center h-16 w-16 bg-red-100 text-red-600 rounded-full border border-red-200 shadow-md">
                        <i data-lucide="frown" class="w-8 h-8"></i>
                    </span>
                    
                    <div class="space-y-1">
                        <h3 class="text-xl font-black text-red-800">Mohon Maaf</h3>
                        <p class="text-xs text-slate-500 font-medium">Berdasarkan hasil rapat pleno panitia seleksi penerimaan, ananda dinyatakan belum lulus.</p>
                    </div>

                    <p class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
                        Terima kasih atas partisipasi Anda dalam rangkaian proses seleksi Sekolah Anak Saleh. Semoga sukses di institusi pendidikan lainnya.
                    </p>
                </div>
            @else
                <div class="text-center py-8 space-y-3">
                    <span class="inline-flex items-center justify-center h-12 w-12 bg-slate-100 text-slate-400 rounded-full">
                        <i data-lucide="clock" class="w-6 h-6"></i>
                    </span>
                    <h3 class="font-bold text-slate-800 text-sm">Belum Pengumuman</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
                        Hasil pengumuman kelulusan akhir akan diumumkan setelah seluruh tahapan seleksi berkas dan tes observasi selesai dinilai oleh panitia.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
