@extends('layouts.portal')

@section('title', 'Verifikasi Berkas - Portal SPMB')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        <div class="bg-brand-emerald text-white px-6 py-5 flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-lg flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-5 h-5 text-brand-yellow"></i>
                    Status Peninjauan & Verifikasi Dokumen
                </h2>
                <p class="text-xs text-brand-yellow font-medium mt-0.5">Pantau status peninjauan berkas persyaratan pendaftaran oleh panitia SPMB.</p>
            </div>
            
            @if(in_array($registration->registration_status, ['verified', 'taaruf_completed', 'agreement_signed', 'completed']))
                <span class="bg-green-700 text-white font-bold text-[10px] uppercase tracking-widest px-3 py-1 rounded-full border border-green-500 shadow-sm">
                    Terverifikasi
                </span>
            @elseif($registration->registration_status === 'failed')
                <span class="bg-red-750 text-white font-bold text-[10px] uppercase tracking-widest px-3 py-1 rounded-full border border-red-500 shadow-sm">
                    Gagal
                </span>
            @elseif($registration->registration_status === 'submitted')
                <span class="bg-amber-600 text-white font-bold text-[10px] uppercase tracking-widest px-3 py-1 rounded-full border border-amber-500 shadow-sm">
                    Ditinjau
                </span>
            @else
                <span class="bg-slate-700 text-white font-bold text-[10px] uppercase tracking-widest px-3 py-1 rounded-full border border-slate-500 shadow-sm">
                    Belum Lengkap
                </span>
            @endif
        </div>

        <div class="p-6 space-y-6">
            
            <!-- Committee Box -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 flex gap-4 items-start">
                <div class="h-10 w-10 bg-emerald-100 text-brand-emerald rounded-xl flex items-center justify-center flex-shrink-0 font-bold">
                    i
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Umpan Balik Panitia</h3>
                    <p class="text-sm text-slate-600 mt-1 leading-relaxed">
                        "{{ $committeeMessage }}"
                    </p>
                </div>
            </div>

            <!-- Verification Status Details -->
            <div class="border border-slate-200 rounded-xl overflow-hidden text-xs">
                <div class="bg-slate-50 p-4 border-b border-slate-200 font-bold text-slate-800 flex justify-between">
                    <span>Dokumen Persyaratan</span>
                    <span>Status Berkas</span>
                </div>
                <div class="divide-y divide-slate-100">
                    <div class="p-4 flex justify-between items-center">
                        <span class="font-semibold text-slate-700">Scan Akta Kelahiran</span>
                        @if($registration->birth_certificate_path)
                            <a href="{{ Storage::url($registration->birth_certificate_path) }}" target="_blank" class="text-brand-emerald font-bold hover:underline flex items-center gap-1">
                                📄 Terunggah (Buka Berkas)
                            </a>
                        @else
                            <span class="text-slate-400">Belum diunggah</span>
                        @endif
                    </div>
                    <div class="p-4 flex justify-between items-center">
                        <span class="font-semibold text-slate-700">Scan Kartu Keluarga</span>
                        @if($registration->family_card_path)
                            <a href="{{ Storage::url($registration->family_card_path) }}" target="_blank" class="text-brand-emerald font-bold hover:underline flex items-center gap-1">
                                📄 Terunggah (Buka Berkas)
                            </a>
                        @else
                            <span class="text-slate-400">Belum diunggah</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Next steps instruction -->
            @if(in_array($registration->registration_status, ['verified', 'taaruf_completed', 'agreement_signed', 'completed']))
                <div class="bg-emerald-50/10 border border-brand-emerald/30 p-5 rounded-xl space-y-2">
                    <h4 class="font-bold text-slate-800 dark:text-white text-xs uppercase tracking-wider">Langkah Selanjutnya</h4>
                    <p class="text-xs text-slate-650 dark:text-slate-400 leading-relaxed">
                        Dokumen pendaftaran Anda telah lengkap diverifikasi dengan benar. Jadwal ujian kesiapan belajar (Tes Observasi) kini telah aktif. Silakan buka menu <strong>Observation</strong> untuk detail jadwal pelaksanaan dan tautan video conference.
                    </p>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
