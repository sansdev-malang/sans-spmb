@extends('layouts.portal')

@section('title', 'Dashboard Calon Siswa - Portal SPMB')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
    
    <!-- Top Alert Messages -->
    @if (session('success'))
        <div class="mb-6 bg-green-50 text-green-700 p-4 rounded-xl text-sm border border-green-200 font-semibold shadow-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 bg-red-50 text-red-700 p-4 rounded-xl text-sm border border-red-200 font-semibold shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols: Main Activities & Widgets -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Committee Box -->
            <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-100 flex gap-4 items-start">
                <div class="h-10 w-10 bg-emerald-100 text-brand-emerald rounded-xl flex items-center justify-center flex-shrink-0 font-bold">
                    i
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">Pesan dari Panitia SPMB</h3>
                    <p class="text-sm text-slate-600 mt-1 leading-relaxed">
                        "{{ $committeeMessage }}"
                    </p>
                </div>
            </div>

            <!-- Dashboard Welcome & Overview Widgets -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Widget 1: Progress Formulir -->
                <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-100 flex items-center gap-4">
                    <div class="h-12 w-12 bg-emerald-50 rounded-xl flex items-center justify-center text-brand-emerald">
                        <i data-lucide="file-check" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Formulir Pendaftaran</span>
                        <span class="text-lg font-black text-slate-800">{{ $stepsCompleted }} / {{ $stepsCount }} Tahap</span>
                        <p class="text-[10px] text-slate-500 mt-1">
                            @if($stepsCompleted == $stepsCount)
                                Formulir pendaftaran terisi lengkap.
                            @else
                                Selesaikan {{ $stepsCount - $stepsCompleted }} tahapan formulir lagi.
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Widget 2: Status Pembayaran -->
                <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-100 flex items-center gap-4">
                    <div class="h-12 w-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
                        <i data-lucide="credit-card" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Status Biaya Seleksi</span>
                        @if($registration->payment_status === 'paid')
                            <span class="text-sm bg-green-100 text-green-700 px-2.5 py-0.5 rounded-full font-bold uppercase">Lunas</span>
                        @elseif($registration->payment_status === 'pending')
                            <span class="text-sm bg-amber-100 text-amber-700 px-2.5 py-0.5 rounded-full font-bold uppercase">Pending</span>
                        @else
                            <span class="text-sm bg-red-100 text-red-700 px-2.5 py-0.5 rounded-full font-bold uppercase">Belum Lunas</span>
                        @endif
                        <p class="text-[10px] text-slate-500 mt-1">Biaya Administrasi: Rp 350.000</p>
                    </div>
                </div>
            </div>

            <!-- Welcome Intro Card -->
            <div class="bg-white rounded-2xl p-8 shadow-md border border-slate-100 space-y-4">
                <h2 class="text-xl font-extrabold text-slate-800">Selamat Datang di Portal Penerimaan Siswa Baru!</h2>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Melalui portal ini, Anda dapat memantau seluruh rangkaian proses seleksi penerimaan siswa baru Sekolah Anak Saleh. Silakan ikuti instruksi pengisian formulir pendaftaran, lakukan pelunasan biaya seleksi, dan tunggu hasil verifikasi dari tim panitia kami.
                </p>
                <div class="pt-2 flex gap-3">
                    <a href="{{ route('dashboard.form', $registration->id) }}" class="bg-brand-emerald hover-emerald text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5">
                        <i data-lucide="file-edit" class="w-4 h-4"></i> Mulai Isi Formulir
                    </a>
                    <a href="{{ route('dashboard.payment', $registration->id) }}" class="border border-slate-300 hover:bg-slate-50 text-slate-700 px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                        <i data-lucide="credit-card" class="w-4 h-4"></i> Rincian Biaya
                    </a>
                </div>
            </div>

        </div>

        <!-- Right 1 Col: Profile & Timeline Progress Tracker -->
        <div class="space-y-6">
            
            <!-- Registration Profile Box -->
            <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-100 text-center">
                <div class="h-16 w-16 bg-brand-emerald text-brand-yellow rounded-2xl flex items-center justify-center font-bold text-3xl mx-auto shadow-md">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <h3 class="font-extrabold text-slate-800 mt-4">{{ auth()->user()->name }}</h3>
                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block mt-1">Akun Calon Wali Murid</span>
                
                @if($registration->candidate_name)
                    <div class="mt-4 border-t border-slate-100 pt-4 text-xs text-slate-500 text-left space-y-1">
                        <p><strong>Nama Calon Siswa:</strong> {{ $registration->candidate_name }}</p>
                        <p><strong>Tingkat:</strong> {{ $registration->admission_level ?? '-' }}</p>
                        <p><strong>No. Registrasi:</strong> SANS-2026-{{ str_pad($registration->id, 4, '0', STR_PAD_LEFT) }}</p>
                    </div>
                @endif
            </div>

            <!-- Steps Timeline Tracker -->
            <div class="bg-white rounded-2xl p-6 shadow-md border border-slate-100 space-y-6">
                <h3 class="font-extrabold text-slate-800 text-sm uppercase tracking-wider border-b border-slate-100 pb-3">Progres Penerimaan</h3>
                
                <div class="relative pl-6 border-l border-slate-200 ml-3 space-y-8">
                    @foreach($timeline as $key => $step)
                        <div class="relative">
                            <!-- Bullet point icon -->
                            <div class="absolute -left-[30px] top-0.5 h-4 w-4 rounded-full flex items-center justify-center 
                                @if($step['status'] === 'completed') bg-green-500 text-white 
                                @elseif($step['status'] === 'in_progress') bg-brand-yellow text-slate-900 ring-4 ring-yellow-50 
                                @elseif($step['status'] === 'failed') bg-red-500 text-white
                                @else bg-slate-200 text-slate-400 @endif font-bold text-[8px]">
                                @if($step['status'] === 'completed') ✓ @endif
                            </div>
                            <!-- Step texts -->
                            <div>
                                <h4 class="font-bold text-xs 
                                    @if($step['status'] === 'completed') text-slate-800 
                                    @elseif($step['status'] === 'in_progress') text-brand-emerald 
                                    @elseif($step['status'] === 'failed') text-red-600
                                    @else text-slate-400 @endif">
                                    {{ $step['label'] }}
                                </h4>
                                <p class="text-[10px] text-slate-400 mt-0.5">{{ $step['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
