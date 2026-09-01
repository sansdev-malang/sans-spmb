@extends('layouts.portal')

@section('title', 'Status Pendaftaran - Portal SPMB')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8 space-y-6">

    <!-- Top Navigation Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="text-xs font-bold text-brand-emerald dark:text-emerald-400 hover:underline flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Kembali ke Daftar Calon Siswa
        </a>
        <span class="text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-500 px-3 py-1.5 rounded-full font-bold uppercase tracking-wider">
            No. Registrasi: SANS-{{ substr($registration->period->year ?? '2026', 0, 4) }}-{{ str_pad($registration->id, 4, '0', STR_PAD_LEFT) }}
        </span>
    </div>

    <!-- Header Title -->
    <div class="border-b border-slate-100 dark:border-slate-800 pb-4">
        <h1 class="text-2xl font-extrabold text-slate-850 dark:text-white">Status Pendaftaran</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pantau tahapan penerimaan ananda dengan mudah secara real-time.</p>
    </div>

    <!-- Stepper Horizontal Timeline Progress Card (Mockup Style - Full Width) -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-150/80 dark:border-slate-800 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 class="font-extrabold text-slate-850 dark:text-white text-xs uppercase tracking-wider">Progres Tahapan SPMB</h3>
            <span class="text-[10px] font-bold text-brand-emerald dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 px-2.5 py-1 rounded-full uppercase">
                Tahap: {{ str_replace('_', ' ', $registration->registration_status) }}
            </span>
        </div>
        
        <!-- Horizontal stepper scrollable on mobile -->
        <div class="flex items-center justify-between overflow-x-auto gap-4 py-2 select-none">
            @foreach($timeline as $key => $step)
                <div class="flex flex-col items-center text-center min-w-[90px] flex-grow relative">
                    <!-- Bullet Indicator -->
                    <div class="h-8 w-8 rounded-2xl flex items-center justify-center font-bold text-xs shadow-sm transition-all duration-300
                        @if($step['status'] === 'completed') bg-green-500 text-white ring-4 ring-green-50 dark:ring-green-950/20
                        @elseif($step['status'] === 'in_progress') bg-brand-yellow text-slate-900 ring-4 ring-yellow-50 dark:ring-yellow-950/20 font-black scale-110
                        @elseif($step['status'] === 'failed') bg-rose-500 text-white
                        @else bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 @endif">
                        @if($step['status'] === 'completed')
                            <i data-lucide="check" class="w-4 h-4"></i>
                        @elseif($step['status'] === 'in_progress')
                            <i data-lucide="loader" class="w-4 h-4 animate-spin"></i>
                        @elseif($step['status'] === 'failed')
                            <i data-lucide="x" class="w-4 h-4"></i>
                        @else
                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                        @endif
                    </div>
                    
                    <!-- Step Label -->
                    <span class="text-[9px] font-bold mt-2 tracking-wide block text-center leading-tight uppercase
                        @if($step['status'] === 'completed') text-slate-800 dark:text-slate-200
                        @elseif($step['status'] === 'in_progress') text-brand-emerald dark:text-emerald-400 font-extrabold
                        @elseif($step['status'] === 'failed') text-rose-600
                        @else text-slate-400 dark:text-slate-600 @endif">
                        {{ $step['label'] }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Main Layout Grid: Left/Center (Active Stage) & Right Sidebar (Info + Status Tahapan + Bantuan) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Kolom Kiri/Tengah: Main Area (Active Stage Content Card) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Committee Announcement Banner (Mockup Status Card Header) -->
            <div class="bg-gradient-to-r from-emerald-800 to-emerald-900 dark:from-slate-900 dark:to-slate-950 text-white rounded-3xl p-6 shadow-sm border border-transparent dark:border-slate-800 flex gap-4 items-start relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-emerald-700/20 dark:bg-slate-800/10 rounded-full blur-2xl"></div>
                <div class="h-10 w-10 bg-brand-yellow/10 text-brand-yellow rounded-xl flex items-center justify-center flex-shrink-0 font-extrabold text-base border border-brand-yellow/20">
                    <i data-lucide="info" class="w-5 h-5"></i>
                </div>
                <div class="relative z-10 space-y-1">
                    <h3 class="font-black text-xs uppercase tracking-widest text-brand-yellow">Pemberitahuan Terkini</h3>
                    <p class="text-xs text-slate-100/90 leading-relaxed font-medium">
                        "{!! str_replace('Menu Formulir', '<a href="' . route('dashboard.form', $registration->id) . '" class="text-brand-yellow font-black underline hover:text-amber-250">Menu Formulir</a>', e($committeeMessage)) !!}"
                    </p>
                </div>
            </div>

            <!-- Active Step Content Area (Mockup Status Card Main Area) -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 shadow-sm border border-slate-150/80 dark:border-slate-800 space-y-6 text-center">
                
                @if($registration->registration_status === 'draft')
                    @php
                        $formPaid = $registration->payments()->where('payment_type', 'registration_fee')->where('status', 'success')->exists();
                    @endphp
                    
                    @if(!$formPaid)
                        @if ($registration->payment_status === 'pending' && $activePayment)
                            <!-- Case 1.1: Pending Payment Details for Registration Fee -->
                            <div class="space-y-6 text-left max-w-xl mx-auto">
                                <div class="text-center py-2">
                                    <div class="h-12 w-12 bg-amber-50 dark:bg-amber-950/20 text-amber-600 rounded-2xl flex items-center justify-center mx-auto shadow-inner mb-3">
                                        <i data-lucide="clock" class="w-6 h-6 animate-pulse"></i>
                                    </div>
                                    <h3 class="text-sm font-extrabold text-slate-850 dark:text-white">Menunggu Pembayaran Formulir</h3>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-normal">
                                        Selesaikan pembayaran tagihan formulir pendaftaran sebelum masa berlaku invoice berakhir.
                                    </p>
                                </div>

                                <div class="border border-slate-200 dark:border-slate-800 rounded-2xl p-6 space-y-6 bg-slate-50/50 dark:bg-slate-950/10">
                                    <div class="text-center border-b border-slate-100 dark:border-slate-800 pb-4 flex justify-between items-center text-xs">
                                        <div>
                                            <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Metode</span>
                                            <span class="font-extrabold text-slate-800 dark:text-white">{{ $activePayment->payment_method }}</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Nominal</span>
                                            <span class="font-black text-brand-emerald dark:text-emerald-400">Rp {{ number_format($activePayment->amount, 0, ',', '.') }}</span>
                                        </div>
                                    </div>

                                    @if ($activePayment->payment_method === 'QRIS')
                                        <div class="flex flex-col items-center justify-center gap-3">
                                            <div class="bg-white p-3 border border-slate-200 dark:border-slate-800 rounded-xl shadow-inner flex items-center justify-center">
                                                @if(!empty($activePayment->payment_info['qrUrl']))
                                                    <img src="{{ $activePayment->payment_info['qrUrl'] }}" alt="QRIS Code" class="h-44 w-44 object-contain">
                                                @else
                                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($activePayment->payment_info['qrContent'] ?? 'MOCK_QRIS_STRING') }}" alt="QRIS Code" class="h-44 w-44">
                                                @endif
                                            </div>
                                            <p class="text-xs text-slate-400 font-medium">Scan QRIS menggunakan Mobile Banking atau e-Wallet pilihan Anda.</p>
                                        </div>
                                    @else
                                        <div class="bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-5 text-center">
                                            <span class="text-xs text-slate-400 font-semibold uppercase block">Nomor Virtual Account (VA)</span>
                                            <span class="text-2xl font-black text-brand-emerald tracking-wider font-mono block mt-1 select-all">
                                                {{ $activePayment->payment_info['virtualAccountNo'] ?? $activePayment->payment_info['virtualAccount'] ?? '88990012345678' }}
                                            </span>
                                        </div>
                                    @endif

                                    <div class="bg-slate-100 dark:bg-slate-950 p-4 rounded-xl text-xs text-slate-500 leading-relaxed space-y-1">
                                        <p class="font-bold text-slate-700 dark:text-slate-350">Instruksi Pembayaran:</p>
                                        <ol class="list-decimal pl-4 space-y-1">
                                            <li>Salin nomor Virtual Account / Scan QRIS di atas.</li>
                                            <li>Buka aplikasi M-Banking Anda atau pergi ke ATM terdekat.</li>
                                            <li>Pilih Pembayaran Virtual Account, masukkan nomor VA, dan konfirmasi nominal tagihan.</li>
                                        </ol>
                                    </div>

                                    <div class="pt-4 flex justify-center border-t border-slate-100 dark:border-slate-800">
                                        <form action="{{ route('dashboard.cancel-payment', $activePayment->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi pembayaran ini?')">
                                            @csrf
                                            <button type="submit" class="border border-red-200 dark:border-red-900/50 hover:bg-red-50 dark:hover:bg-red-950/20 text-red-650 px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                                <i data-lucide="x-circle" class="w-4.5 h-4.5"></i> Batalkan & Pilih Metode Lain
                                            </button>
                                        </form>
                                    </div>

                                    <!-- DEVELOPER ONLY: Webhook simulation button -->
                                    @php
                                        $gwCode = 'winpay';
                                        if ($activePayment && str_contains(strtolower($activePayment->payment_method), 'bni')) {
                                            $gwCode = 'bni';
                                        }
                                        $gwMode = \App\Models\Setting::get($gwCode . '_mode', 'simulator');
                                    @endphp
                                    @if($gwMode === 'simulator')
                                        <div class="border-t border-dashed border-slate-200 dark:border-slate-800 pt-6 mt-4 bg-sky-50/10 dark:bg-sky-950/15 p-4 rounded-xl border border-sky-100 dark:border-sky-900/50 flex flex-col items-center justify-center gap-2">
                                            <div class="flex items-center gap-1.5 text-sky-700 dark:text-sky-400">
                                                <i data-lucide="settings" class="w-4 h-4 animate-spin"></i>
                                                <span class="text-[10px] font-extrabold uppercase tracking-wider">Mode Simulator (Developer)</span>
                                            </div>
                                            <p class="text-[10px] text-slate-400 text-center leading-normal">
                                                Mensimulasikan callback lunas dari gateway pembayaran Winpay/BNI secara instan.
                                            </p>
                                            <form action="{{ route('dashboard.simulate-payment', $activePayment->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="bg-sky-600 hover:bg-sky-750 text-white px-4 py-2 rounded-xl text-[10px] font-black shadow-sm transition">
                                                    Simulasikan Pembayaran Sukses (Lunas)
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <!-- Case 1.2: Pembayaran Biaya Pendaftaran Form Selection -->
                            <form action="{{ route('dashboard.charge', $registration->id) }}" method="POST" class="space-y-4 text-left max-w-xl mx-auto">
                                @csrf
                                <div class="text-center py-2 mb-4">
                                    <div class="h-16 w-16 bg-amber-50 dark:bg-amber-950/20 text-amber-600 rounded-3xl flex items-center justify-center mx-auto shadow-inner mb-3">
                                        <i data-lucide="wallet" class="w-8 h-8"></i>
                                    </div>
                                    <h3 class="text-base font-extrabold text-slate-850 dark:text-white">Pembayaran Biaya Pendaftaran</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                        Selesaikan pembayaran biaya formulir sebesar <strong class="text-brand-emerald dark:text-emerald-400">Rp {{ number_format($feeAmount, 0, ',', '.') }}</strong> untuk membuka & mengisi formulir pendaftaran.
                                    </p>
                                </div>
                                
                                <label class="block text-xs font-bold text-slate-650 dark:text-slate-350 uppercase tracking-wider mb-2">Pilih Metode Pembayaran</label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    @if($feeGateway === 'bni' || $feeGateway === 'BNI SNAP')
                                        <label class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-brand-emerald hover:bg-emerald-50/10 transition relative">
                                            <input type="radio" name="payment_method" value="BNI" class="absolute top-3 right-3 text-brand-emerald focus:ring-brand-emerald" checked>
                                            
                                            <!-- Logo Placeholder -->
                                            <div class="h-8 w-16 flex items-center justify-center p-0.5 select-none shrink-0 mb-1">
                                                <div class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded font-black text-[9px] text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                                    BNI
                                                </div>
                                            </div>

                                            <span class="text-sm font-bold text-slate-800 dark:text-white text-center leading-tight">BNI VA</span>
                                            <span class="text-[9px] text-slate-450 uppercase font-semibold">VIRTUAL ACCOUNT</span>
                                        </label>
                                        <label class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-brand-emerald hover:bg-emerald-50/10 transition relative">
                                            <input type="radio" name="payment_method" value="QRIS" class="absolute top-3 right-3 text-brand-emerald focus:ring-brand-emerald">
                                            
                                            <!-- Logo Placeholder -->
                                            <div class="h-8 w-16 flex items-center justify-center p-0.5 select-none shrink-0 mb-1">
                                                <div class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded font-black text-[9px] text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                                    QRIS
                                                </div>
                                            </div>

                                            <span class="text-sm font-bold text-slate-800 dark:text-white text-center leading-tight">QRIS</span>
                                            <span class="text-[9px] text-slate-450 uppercase font-semibold">QR CODE SCAN</span>
                                        </label>
                                    @else
                                        @foreach($channels as $channel)
                                            <label class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-brand-emerald hover:bg-emerald-50/10 transition relative">
                                                <input type="radio" name="payment_method" value="{{ $channel->code }}" class="absolute top-3 right-3 text-brand-emerald focus:ring-brand-emerald" {{ $loop->first ? 'checked' : '' }}>
                                                
                                                <!-- Logo Container -->
                                                <div class="h-8 w-16 flex items-center justify-center p-0.5 select-none shrink-0 mb-1">
                                                    @if($channel->getLogoUrl())
                                                        <img src="{{ $channel->getLogoUrl() }}" alt="{{ $channel->name }}" class="max-h-full max-w-full object-contain">
                                                    @else
                                                        <div class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded font-black text-[9px] text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                                            {{ substr($channel->code, 0, 3) }}
                                                        </div>
                                                    @endif
                                                </div>

                                                <span class="text-sm font-bold text-slate-800 dark:text-white text-center leading-tight">{{ $channel->name }}</span>
                                                <span class="text-[9px] text-slate-450 uppercase font-semibold">{{ $channel->type }}</span>
                                            </label>
                                        @endforeach
                                    @endif
                                </div>
                                <!-- Payment Summary breakdown -->
                                <div id="paymentSummaryCardForm1" class="bg-slate-50 dark:bg-slate-955 border border-slate-200 dark:border-slate-800 rounded-xl p-5 space-y-3 text-xs mt-4">
                                    <h4 class="font-extrabold text-slate-800 dark:text-white uppercase tracking-wider text-[10px] pb-1 border-b border-slate-200/50 dark:border-slate-800">Rincian Pembayaran</h4>
                                    <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                        <span>Tagihan Pokok</span>
                                        <span class="font-bold text-slate-800 dark:text-white">Rp {{ number_format($feeAmount, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                        <span>Biaya Transaksi / Admin</span>
                                        <span id="displayAdminFeeForm1" class="font-bold text-slate-800 dark:text-white">Rp 0</span>
                                    </div>
                                    <div class="border-t border-slate-200/50 dark:border-slate-800 pt-3 flex justify-between items-center text-xs font-black text-slate-800 dark:text-white uppercase">
                                        <span>Total Pembayaran</span>
                                        <span id="displayGrandTotalForm1" class="text-brand-emerald dark:text-emerald-400 text-sm font-extrabold">Rp {{ number_format($feeAmount, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <div class="pt-6 flex justify-center">
                                    <button type="submit" class="bg-brand-emerald hover-emerald text-white px-8 py-3 rounded-xl font-bold text-xs shadow-md transition flex items-center gap-1.5">
                                        <i data-lucide="credit-card" class="w-4 h-4 text-brand-yellow"></i> Bayar Biaya Formulir
                                    </button>
                                </div>
                            </form>
                        @endif
                    @else
                        <!-- Case 2: Formulir Terbuka -->
                        <div class="text-center py-6 space-y-4 max-w-md mx-auto">
                            <div class="h-16 w-16 bg-emerald-50 dark:bg-emerald-950/20 text-brand-emerald dark:text-emerald-400 rounded-3xl flex items-center justify-center mx-auto shadow-inner">
                                <i data-lucide="file-edit" class="w-8 h-8"></i>
                            </div>
                            <h3 class="text-base font-extrabold text-slate-805 dark:text-white">Pembayaran Sukses! Formulir Terbuka</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                Terima kasih. Pembayaran formulir Anda telah lunas diverifikasi. Silakan isi dan lengkapi seluruh tahapan formulir data anak, orang tua, dan unggah berkas persyaratan.
                            </p>
                            <div class="pt-4">
                                <a href="{{ route('dashboard.form', $registration->id) }}" class="bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl text-xs font-bold shadow-md transition inline-flex items-center gap-1.5">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i> Isi Formulir Sekarang
                                </a>
                            </div>
                        </div>
                    @endif

                @elseif($registration->registration_status === 'submitted')
                    <!-- Case 3: Verifikasi Berkas -->
                    <div class="text-center py-6 space-y-4 max-w-md mx-auto">
                        <div class="h-16 w-16 bg-blue-50 dark:bg-blue-950/20 text-blue-600 rounded-3xl flex items-center justify-center mx-auto shadow-inner">
                            <i data-lucide="hourglass" class="w-8 h-8 animate-pulse"></i>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-850 dark:text-white">Berkas Sedang Diverifikasi</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Formulir pendaftaran ananda telah sukses dikirim ke panitia SPMB Sekolah Anak Saleh. Berkas Anda sedang diperiksa secara berkala. Mohon pantau halaman ini untuk melihat hasil verifikasi panitia.
                        </p>
                        <div class="pt-4">
                            <a href="{{ route('dashboard.verification', $registration->id) }}" class="bg-slate-800 hover:bg-slate-900 text-white px-6 py-3 rounded-xl text-xs font-bold shadow-md transition inline-flex items-center gap-1.5">
                                <i data-lucide="search" class="w-4 h-4"></i> Cek Berkas Dikirim
                            </a>
                        </div>
                    </div>

                @elseif($registration->registration_status === 'verified')
                    <!-- Case 4: Sesi Ta'aruf -->
                    <div class="text-center py-6 space-y-4 max-w-md mx-auto">
                        <div class="h-16 w-16 bg-brand-yellow/10 text-amber-600 rounded-3xl flex items-center justify-center mx-auto shadow-inner">
                            <i data-lucide="users" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-850 dark:text-white">Sesi Ta'aruf Offline Aktif</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Alhamdulillah, berkas persyaratan dinyatakan lolos verifikasi. Silakan persiapkan kehadiran tatap muka ananda dan orang tua di unit sekolah untuk mengikuti wawancara Ta'aruf.
                        </p>
                        <div class="pt-4">
                            <a href="{{ route('dashboard.observation', $registration->id) }}" class="bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl text-xs font-bold shadow-md transition inline-flex items-center gap-1.5">
                                <i data-lucide="calendar" class="w-4 h-4"></i> Lihat Informasi Ta'aruf & Unit
                            </a>
                        </div>
                    </div>
 
                @elseif($registration->registration_status === 'taaruf_completed')
                    <!-- Case 5: Pernyataan Kesanggupan -->
                    <div class="text-center py-6 space-y-4 max-w-md mx-auto">
                        <div class="h-16 w-16 bg-purple-50 dark:bg-purple-950/20 text-purple-600 rounded-3xl flex items-center justify-center mx-auto shadow-inner">
                            <i data-lucide="file-signature" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-850 dark:text-white">Persetujuan Pernyataan Kesanggupan</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Sesi Ta'aruf offline selesai dilakukan. Silakan mengisi dan menyepakati formulir komitmen biaya pendidikan serta tata tertib yayasan sebelum mencetak tagihan administrasi akhir.
                        </p>
                        <div class="pt-4">
                            <a href="{{ route('dashboard.observation', $registration->id) }}" class="bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl text-xs font-bold shadow-md transition inline-flex items-center gap-1.5">
                                <i data-lucide="pen-tool" class="w-4 h-4"></i> Isi Pernyataan Kesanggupan
                            </a>
                        </div>
                    </div>

                @elseif($registration->registration_status === 'agreement_signed')
                    @if ($registration->payment_status === 'pending' && $activePayment)
                        <!-- Case 6.1: Pending Payment for Final Admission Fee -->
                        <div class="space-y-6 text-left max-w-xl mx-auto">
                            <div class="text-center py-2">
                                <div class="h-12 w-12 bg-amber-50 dark:bg-amber-950/20 text-amber-600 rounded-2xl flex items-center justify-center mx-auto shadow-inner mb-3">
                                    <i data-lucide="clock" class="w-6 h-6 animate-pulse"></i>
                                </div>
                                <h3 class="text-sm font-extrabold text-slate-850 dark:text-white">Menunggu Pelunasan Administrasi Akhir</h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 leading-normal">
                                    Selesaikan pelunasan biaya masuk yayasan agar pendaftaran ananda resmi diselesaikan.
                                </p>
                            </div>

                            <div class="border border-slate-200 dark:border-slate-800 rounded-2xl p-6 space-y-6 bg-slate-50/50 dark:bg-slate-950/10">
                                <div class="text-center border-b border-slate-100 dark:border-slate-800 pb-4 flex justify-between items-center text-xs">
                                    <div>
                                        <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Metode</span>
                                        <span class="font-extrabold text-slate-800 dark:text-white">{{ $activePayment->payment_method }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Total</span>
                                        <span class="font-black text-brand-emerald dark:text-emerald-400">Rp {{ number_format($activePayment->amount, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                @if ($activePayment->payment_method === 'QRIS')
                                    <div class="flex flex-col items-center justify-center gap-3">
                                        <div class="bg-white p-3 border border-slate-200 dark:border-slate-800 rounded-xl shadow-inner flex items-center justify-center">
                                            @if(!empty($activePayment->payment_info['qrUrl']))
                                                <img src="{{ $activePayment->payment_info['qrUrl'] }}" alt="QRIS Code" class="h-44 w-44 object-contain">
                                            @else
                                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($activePayment->payment_info['qrContent'] ?? 'MOCK_QRIS_STRING') }}" alt="QRIS Code" class="h-44 w-44">
                                            @endif
                                        </div>
                                        <p class="text-xs text-slate-400 font-medium">Scan QRIS menggunakan Mobile Banking atau e-Wallet pilihan Anda.</p>
                                    </div>
                                @elseif (!empty($activePayment->payment_info['webRedirectUrl']) || !empty($activePayment->payment_info['paymentUrl']))
                                    <div class="bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-6 text-center space-y-4">
                                        <span class="text-xs text-slate-400 font-semibold uppercase block">Pembayaran Dompet Digital (E-Wallet)</span>
                                        <div class="flex flex-col items-center justify-center gap-3">
                                            <p class="text-xs text-slate-600 dark:text-slate-350 font-medium max-w-sm">Klik tombol di bawah ini untuk melanjutkan pembayaran via {{ $activePayment->payment_method }}:</p>
                                            <a href="{{ $activePayment->payment_info['webRedirectUrl'] ?? $activePayment->payment_info['paymentUrl'] }}" target="_blank" class="bg-brand-emerald hover:bg-emerald-600 text-white px-6 py-3 rounded-xl text-xs font-bold shadow-md transition flex items-center gap-2">
                                                <i data-lucide="external-link" class="w-4 h-4"></i> Buka Pembayaran {{ $activePayment->payment_method }}
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-5 text-center">
                                        <span class="text-xs text-slate-400 font-semibold uppercase block">Nomor Virtual Account (VA)</span>
                                        <span class="text-2xl font-black text-brand-emerald tracking-wider font-mono block mt-1 select-all">
                                            {{ $activePayment->payment_info['virtualAccountNo'] ?? $activePayment->payment_info['virtualAccount'] ?? '88990012345678' }}
                                        </span>
                                    </div>
                                @endif

                                <div class="bg-slate-100 dark:bg-slate-950 p-4 rounded-xl text-xs text-slate-500 leading-relaxed space-y-1">
                                    <p class="font-bold text-slate-700 dark:text-slate-350">Instruksi Pembayaran:</p>
                                    <ol class="list-decimal pl-4 space-y-1">
                                        <li>Salin nomor Virtual Account / Scan QRIS di atas.</li>
                                        <li>Lakukan transfer sesuai total tagihan.</li>
                                    </ol>
                                </div>

                                <div class="pt-4 flex justify-center border-t border-slate-100 dark:border-slate-800">
                                    <form action="{{ route('dashboard.cancel-payment', $activePayment->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi pembayaran ini?')">
                                        @csrf
                                        <button type="submit" class="border border-red-200 dark:border-red-900/50 hover:bg-red-50 dark:hover:bg-red-950/20 text-red-650 px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                            <i data-lucide="x-circle" class="w-4.5 h-4.5"></i> Batalkan & Pilih Metode Lain
                                        </button>
                                    </form>
                                </div>

                                <!-- DEVELOPER ONLY: Webhook simulation button -->
                                @php
                                    $gwCode = 'winpay';
                                    if ($activePayment && str_contains(strtolower($activePayment->payment_method), 'bni')) {
                                        $gwCode = 'bni';
                                    }
                                    $gwMode = \App\Models\Setting::get($gwCode . '_mode', 'simulator');
                                @endphp
                                @if($gwMode === 'simulator')
                                    <div class="border-t border-dashed border-slate-200 dark:border-slate-800 pt-6 mt-4 bg-sky-50/10 dark:bg-sky-950/15 p-4 rounded-xl border border-sky-100 dark:border-sky-900/50 flex flex-col items-center justify-center gap-2">
                                        <div class="flex items-center gap-1.5 text-sky-700 dark:text-sky-400">
                                            <i data-lucide="settings" class="w-4 h-4 animate-spin"></i>
                                            <span class="text-[10px] font-extrabold uppercase tracking-wider">Mode Simulator (Developer)</span>
                                        </div>
                                        <form action="{{ route('dashboard.simulate-payment', $activePayment->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="bg-sky-600 hover:bg-sky-750 text-white px-4 py-2 rounded-xl text-[10px] font-black shadow-sm transition">
                                                Simulasikan Pembayaran Sukses (Lunas)
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <!-- Case 6.2: Final Fee Payment Form Selection -->
                        @php
                            $finalDetails = $registration->final_fee_snapshot ?? app(\App\Http\Controllers\Web\WebDashboardController::class)->getFinalFeeDetails($registration);
                        @endphp
                        <form action="{{ route('dashboard.charge', $registration->id) }}" method="POST" class="space-y-4 text-left max-w-xl mx-auto">
                            @csrf
                            <div class="text-center py-2 mb-4">
                                <div class="h-16 w-16 bg-amber-50 dark:bg-amber-950/20 text-amber-600 rounded-3xl flex items-center justify-center mx-auto shadow-inner mb-3">
                                    <i data-lucide="receipt" class="w-8 h-8"></i>
                                </div>
                                <h3 class="text-base font-extrabold text-slate-850 dark:text-white">Pembayaran Administrasi Akhir</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                    Komitmen biaya disetujui. Selesaikan pelunasan administrasi sebesar <strong class="text-brand-emerald dark:text-emerald-400">Rp {{ number_format($finalDetails['total'], 0, ',', '.') }}</strong> untuk menyelesaikan penerimaan siswa baru.
                                </p>
                            </div>

                            <!-- Rincian Biaya Table -->
                            <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 space-y-2.5 text-xs mb-6">
                                @if(isset($finalDetails['items']) && is_array($finalDetails['items']))
                                    @foreach($finalDetails['items'] as $item)
                                        <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                            <span>{{ $item['name'] }}</span>
                                            <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($item['amount'], 0, ',', '.') }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                        <span>Uang Gedung</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($finalDetails['uang_gedung'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                        <span>Uang Seragam</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($finalDetails['seragam'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                        <span>SPP Mulai Juli</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($finalDetails['spp'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                        <span>Uang Kegiatan</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($finalDetails['kegiatan'] ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                            </div>
                            
                            <label class="block text-xs font-bold text-slate-650 dark:text-slate-350 uppercase tracking-wider mb-2">Pilih Metode Pembayaran</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @if($feeGateway === 'bni' || $feeGateway === 'BNI SNAP')
                                    <label class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-brand-emerald hover:bg-emerald-50/10 transition relative">
                                        <input type="radio" name="payment_method" value="BNI" class="absolute top-3 right-3 text-brand-emerald focus:ring-brand-emerald" checked>
                                        
                                        <!-- Logo Placeholder -->
                                        <div class="h-8 w-16 flex items-center justify-center p-0.5 select-none shrink-0 mb-1">
                                            <div class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded font-black text-[9px] text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                                BNI
                                            </div>
                                        </div>

                                        <span class="text-sm font-bold text-slate-800 dark:text-white text-center leading-tight">BNI VA</span>
                                        <span class="text-[9px] text-slate-450 uppercase font-semibold">VIRTUAL ACCOUNT</span>
                                    </label>
                                    <label class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-brand-emerald hover:bg-emerald-50/10 transition relative">
                                        <input type="radio" name="payment_method" value="QRIS" class="absolute top-3 right-3 text-brand-emerald focus:ring-brand-emerald">
                                        
                                        <!-- Logo Placeholder -->
                                        <div class="h-8 w-16 flex items-center justify-center p-0.5 select-none shrink-0 mb-1">
                                            <div class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded font-black text-[9px] text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                                QRIS
                                            </div>
                                        </div>

                                        <span class="text-sm font-bold text-slate-800 dark:text-white text-center leading-tight">QRIS</span>
                                        <span class="text-[9px] text-slate-450 uppercase font-semibold">QR CODE SCAN</span>
                                    </label>
                                @else
                                    @foreach($channels as $channel)
                                        <label class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-brand-emerald hover:bg-emerald-50/10 transition relative">
                                            <input type="radio" name="payment_method" value="{{ $channel->code }}" class="absolute top-3 right-3 text-brand-emerald focus:ring-brand-emerald" {{ $loop->first ? 'checked' : '' }}>
                                            
                                            <!-- Logo Container -->
                                            <div class="h-8 w-16 flex items-center justify-center p-0.5 select-none shrink-0 mb-1">
                                                @if($channel->getLogoUrl())
                                                    <img src="{{ $channel->getLogoUrl() }}" alt="{{ $channel->name }}" class="max-h-full max-w-full object-contain">
                                                @else
                                                    <div class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded font-black text-[9px] text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                                        {{ substr($channel->code, 0, 3) }}
                                                    </div>
                                                @endif
                                            </div>

                                            <span class="text-sm font-bold text-slate-800 dark:text-white text-center leading-tight">{{ $channel->name }}</span>
                                            <span class="text-[9px] text-slate-450 uppercase font-semibold">{{ $channel->type }}</span>
                                        </label>
                                    @endforeach
                                @endif
                            </div>
                            <!-- Payment Summary breakdown -->
                            <div id="paymentSummaryCardForm2" class="bg-slate-50 dark:bg-slate-955 border border-slate-200 dark:border-slate-800 rounded-xl p-5 space-y-3 text-xs mt-4">
                                <h4 class="font-extrabold text-slate-800 dark:text-white uppercase tracking-wider text-[10px] pb-1 border-b border-slate-200/50 dark:border-slate-800">Rincian Pembayaran</h4>
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                    <span>Tagihan Pokok</span>
                                    <span class="font-bold text-slate-800 dark:text-white">Rp {{ number_format($finalDetails['total'] ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                    <span>Biaya Transaksi / Admin</span>
                                    <span id="displayAdminFeeForm2" class="font-bold text-slate-800 dark:text-white">Rp 0</span>
                                </div>
                                <div class="border-t border-slate-200/50 dark:border-slate-800 pt-3 flex justify-between items-center text-xs font-black text-slate-800 dark:text-white uppercase">
                                    <span>Total Pembayaran</span>
                                    <span id="displayGrandTotalForm2" class="text-brand-emerald dark:text-emerald-400 text-sm font-extrabold">Rp {{ number_format($finalDetails['total'] ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <div class="pt-6 flex justify-center">
                                <button type="submit" class="bg-brand-emerald hover-emerald text-white px-8 py-3 rounded-xl font-bold text-xs shadow-md transition flex items-center gap-1.5">
                                    <i data-lucide="credit-card" class="w-4 h-4 text-brand-yellow"></i> Bayar Administrasi Akhir
                                </button>
                            </div>
                        </form>
                    @endif

                @elseif($registration->registration_status === 'completed')
                    <!-- Case 7: Lulus & Selesai -->
                    <div class="text-center py-6 space-y-4 max-w-md mx-auto">
                        <div class="h-16 w-16 bg-green-50 dark:bg-green-950/20 text-green-600 rounded-3xl flex items-center justify-center mx-auto shadow-inner animate-bounce">
                            <i data-lucide="award" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-850 dark:text-white">Alhamdulillah, Selamat Bergabung!</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            Seluruh tahapan pendaftaran dan pembayaran administrasi ananda telah selesai diselesaikan. Selamat bergabung di Sekolah Anak Saleh!
                        </p>
                        <div class="pt-4 flex gap-3 justify-center">
                            <a href="{{ route('dashboard.result', $registration->id) }}" class="bg-brand-emerald hover-emerald text-white px-5 py-3 rounded-xl text-xs font-bold shadow-md transition inline-flex items-center gap-1.5">
                                <i data-lucide="award" class="w-4 h-4"></i> Surat Kelulusan & Kartu
                            </a>
                        </div>
                    </div>

                @elseif($registration->registration_status === 'failed')
                    <!-- Case 8: Berkas Ditolak / Perlu Perbaikan -->
                    <div class="text-center py-6 space-y-4 max-w-xl mx-auto">
                        <div class="h-16 w-16 bg-red-50 dark:bg-red-955/20 text-rose-600 rounded-3xl flex items-center justify-center mx-auto shadow-inner animate-pulse">
                            <i data-lucide="alert-triangle" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-850 dark:text-white">Berkas Pendaftaran Perlu Perbaikan</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed max-w-md mx-auto">
                            Terdapat data atau dokumen persyaratan yang tidak sesuai kriteria verifikasi panitia. Mohon periksa kembali dan perbaiki kolom-kolom berikut:
                        </p>

                        <!-- List of invalid fields -->
                        @if(!empty($registration->invalid_fields) && is_array($registration->invalid_fields))
                            <div class="bg-red-50/50 dark:bg-red-950/10 border border-red-100 dark:border-red-900/50 rounded-2xl p-4 text-left space-y-2.5 max-w-md mx-auto">
                                @php
                                    $fieldMeta = [
                                        'spmb_period_id' => ['label' => 'Tahun Ajaran', 'step_id' => 1],
                                        'spmb_wave_id' => ['label' => 'Gelombang Pendaftaran', 'step_id' => 1],
                                        'spmb_type_id' => ['label' => 'Jalur Pendaftaran', 'step_id' => 1],
                                        'spmb_class_program_id' => ['label' => 'Program Kelas', 'step_id' => 1],
                                        'candidate_name' => ['label' => 'Nama Lengkap Calon Siswa', 'step_id' => 2],
                                        'nickname' => ['label' => 'Nama Panggilan', 'step_id' => 2],
                                        'nik' => ['label' => 'NIK Anak', 'step_id' => 2],
                                        'gender' => ['label' => 'Jenis Kelamin', 'step_id' => 2],
                                        'religion' => ['label' => 'Agama', 'step_id' => 2],
                                        'birth_place' => ['label' => 'Tempat & Tanggal Lahir', 'step_id' => 2],
                                        'previous_school' => ['label' => 'Asal Sekolah', 'step_id' => 2],
                                        'admission_level' => ['label' => 'Tingkat Pendaftaran', 'step_id' => 2],
                                        'extra_services' => ['label' => 'Layanan Tambahan', 'step_id' => 2],
                                        'father_name' => ['label' => 'Nama Ayah Kandung', 'step_id' => 3],
                                        'mother_name' => ['label' => 'Nama Ibu Kandung', 'step_id' => 3],
                                        'parent_phone' => ['label' => 'No. HP Wali (WhatsApp)', 'step_id' => 3],
                                        'birth_certificate_path' => ['label' => 'Scan Akta Kelahiran', 'step_id' => 4],
                                        'family_card_path' => ['label' => 'Scan Kartu Keluarga', 'step_id' => 4],
                                    ];
                                @endphp
                                <ul class="space-y-1.5">
                                    @foreach($registration->invalid_fields as $invalidField)
                                        @php
                                            $meta = $fieldMeta[$invalidField] ?? ['label' => $invalidField, 'step_id' => 2];
                                        @endphp
                                        <li class="flex items-center justify-between gap-4 text-xs font-semibold text-red-700 dark:text-rose-400">
                                            <span class="flex items-center gap-1.5">
                                                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                                {{ $meta['label'] }}
                                            </span>
                                            <a href="{{ route('dashboard.form', $registration->id) }}?highlight={{ $invalidField }}&step={{ $meta['step_id'] }}" 
                                               class="bg-red-100 hover:bg-red-200 dark:bg-rose-950/30 dark:hover:bg-rose-900/50 text-red-800 dark:text-rose-350 px-2.5 py-1 rounded-lg text-[10px] font-bold shadow-sm transition">
                                                Perbaiki →
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="pt-4">
                            <a href="{{ route('dashboard.form', $registration->id) }}" class="bg-red-650 hover:bg-red-750 text-white px-6 py-3 rounded-xl text-xs font-bold shadow-md transition inline-flex items-center gap-1.5">
                                <i data-lucide="edit-3" class="w-4 h-4"></i> Perbaiki Formulir Pendaftaran
                            </a>
                        </div>
                    </div>
                @endif

            </div>

        </div>

        <!-- Kolom Kanan: Sidebar (Info Pendaftaran, Rincian Administrasi & Bantuan) -->
        <div class="space-y-6">
            
            <!-- Card 1: Info Calon Siswa (Mini Profile) -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-150/80 dark:border-slate-800">
                <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-4 mb-4">
                    <div class="h-10 w-10 bg-brand-emerald text-brand-yellow rounded-xl flex items-center justify-center font-black text-lg">
                        {{ substr($registration->candidate_name ?? 'A', 0, 1) }}
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-800 dark:text-white text-xs leading-tight text-left">{{ $registration->candidate_name ?? 'Draft Calon Siswa' }}</h3>
                        <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider block mt-0.5 text-left">
                            {{ $registration->unit->name ?? '-' }}@if(!empty($registration->grade->name)) • {{ $registration->grade->name }}@endif
                        </span>
                    </div>
                </div>
                <div class="text-[10px] space-y-2.5 text-slate-600 dark:text-slate-400">
                    <div class="flex justify-between items-center">
                        <span>Tahun Pelajaran</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $registration->period->name ?? '2026/2027' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span>Jalur Masuk</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $registration->type->name ?? 'Reguler' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span>Program Kelas</span>
                        <span class="font-bold text-brand-emerald dark:text-emerald-400">{{ $registration->classProgram->name ?? ($registration->getFieldValue('class_program') ?: '-') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span>Tanggal Registrasi</span>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $registration->created_at->format('d M Y') }}</span>
                    </div>
                    @if($registration->extraServices->count() > 0)
                        <div class="flex justify-between items-center border-t border-slate-100 dark:border-slate-800 pt-2 mt-2">
                            <span>Layanan Tambahan</span>
                            <span class="font-bold text-brand-emerald dark:text-emerald-400">{{ $registration->extraServices->pluck('name')->implode(', ') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            @php
                $formPaid = $registration->payments()->where('payment_type', 'registration_fee')->where('status', 'success')->exists();
                $status = $registration->registration_status;
                $isAccepted = in_array($status, ['accepted', 'agreement_signed', 'completed']);
            @endphp

            @if($isAccepted)
                <!-- Card 2: Rincian Administrasi (Hanya muncul jika dinyatakan diterima) -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-150/80 dark:border-slate-800 space-y-4 text-left">
                    <h4 class="font-extrabold text-slate-850 dark:text-white text-xs uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center gap-1.5">
                        <i data-lucide="receipt" class="w-4 h-4 text-brand-emerald"></i> Rincian Administrasi
                    </h4>
                    
                    @php
                        $finalFees = $registration->final_fee_snapshot ?? app(\App\Http\Controllers\Web\WebDashboardController::class)->getFinalFeeDetails($registration);
                    @endphp

                    <!-- Final Fee Breakdowns -->
                    <div class="space-y-2.5 text-[11px] text-slate-655 dark:text-slate-355">
                        @if(isset($finalFees['items']) && is_array($finalFees['items']))
                            @foreach($finalFees['items'] as $item)
                                <div class="flex justify-between items-center">
                                    <span>{{ $item['name'] }}</span>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($item['amount'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        @else
                            <div class="flex justify-between items-center">
                                <span>Uang Gedung</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($finalFees['uang_gedung'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Biaya Seragam</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($finalFees['seragam'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>SPP Mulai Juli</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($finalFees['spp'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Uang Kegiatan</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($finalFees['kegiatan'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="border-t border-slate-100 dark:border-slate-800 pt-2.5 flex justify-between items-center font-extrabold text-slate-800 dark:text-white uppercase">
                            <span>Total Administrasi</span>
                            <span class="text-brand-emerald dark:text-emerald-400">Rp {{ number_format($finalFees['total'], 0, ',', '.') }}</span>
                        </div>
                        <div class="pt-2">
                            @if($registration->payment_status === 'paid')
                                <span class="w-full text-center py-1 bg-green-50 dark:bg-green-950/20 text-green-600 border border-green-200 dark:border-green-900/50 rounded-lg text-[9px] font-bold block uppercase tracking-wider">
                                    Lunas
                                </span>
                            @else
                                <span class="w-full text-center py-1 bg-amber-50 dark:bg-amber-950/20 text-amber-600 border border-amber-200 dark:border-amber-900/50 rounded-lg text-[9px] font-bold block uppercase tracking-wider">
                                    Belum Lunas
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <!-- Card 2: Status Tahapan Pendaftaran (Riwayat Aktivitas) -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-150/80 dark:border-slate-800 space-y-4 text-left">
                    <h4 class="font-extrabold text-slate-850 dark:text-white text-xs uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 pb-3 flex items-center gap-1.5">
                        <i data-lucide="activity" class="w-4 h-4 text-brand-emerald"></i> Status Tahapan
                    </h4>
                    <div class="space-y-4 text-xs">
                        <!-- Step 1: Pembayaran Formulir -->
                        <div class="flex items-start gap-2.5">
                            @if($formPaid)
                                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 flex items-center justify-center text-green-600 dark:text-green-450 mt-0.5">
                                    <i data-lucide="check" class="w-3 h-3"></i>
                                </span>
                                <div>
                                    <span class="font-bold text-slate-700 dark:text-slate-200 block">Pembayaran Formulir</span>
                                    <span class="text-[10px] text-green-600 font-semibold block uppercase">Lunas</span>
                                </div>
                            @else
                                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 flex items-center justify-center text-amber-600 dark:text-amber-450 mt-0.5 animate-pulse">
                                    <i data-lucide="loader" class="w-3 h-3"></i>
                                </span>
                                <div>
                                    <span class="font-bold text-slate-600 dark:text-slate-300 block">Pembayaran Formulir</span>
                                    <span class="text-[10px] text-amber-600 font-semibold block uppercase">Belum Dibayar</span>
                                </div>
                            @endif
                        </div>

                        <!-- Step 2: Pengisian Formulir -->
                        <div class="flex items-start gap-2.5">
                            @if($status !== 'draft')
                                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 flex items-center justify-center text-green-600 dark:text-green-450 mt-0.5">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                </span>
                                <div>
                                    <span class="font-bold text-slate-700 dark:text-slate-200 block">Pengisian Formulir</span>
                                    <span class="text-[10px] text-green-600 font-semibold block uppercase">Selesai</span>
                                </div>
                            @else
                                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 mt-0.5">
                                    <i data-lucide="circle" class="w-3 h-3"></i>
                                </span>
                                <div>
                                    <span class="font-bold text-slate-500 dark:text-slate-400 block">Pengisian Formulir</span>
                                    <span class="text-[10px] text-slate-400 block uppercase">Belum Lengkap</span>
                                </div>
                            @endif
                        </div>

                        <!-- Step 3: Verifikasi Berkas -->
                        <div class="flex items-start gap-2.5">
                            @if(in_array($status, ['submitted']))
                                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 flex items-center justify-center text-amber-600 dark:text-amber-450 mt-0.5">
                                    <i data-lucide="loader" class="w-3 h-3 animate-spin"></i>
                                </span>
                                <div>
                                    <span class="font-bold text-slate-700 dark:text-slate-200 block">Verifikasi Berkas</span>
                                    <span class="text-[10px] text-amber-600 font-semibold block uppercase">Sedang Diperiksa</span>
                                </div>
                            @elseif(in_array($status, ['verified', 'accepted', 'agreement_signed', 'completed']))
                                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 flex items-center justify-center text-green-600 dark:text-green-450 mt-0.5">
                                    <i data-lucide="check" class="w-3 h-3"></i>
                                </span>
                                <div>
                                    <span class="font-bold text-slate-700 dark:text-slate-200 block">Verifikasi Berkas</span>
                                    <span class="text-[10px] text-green-600 font-semibold block uppercase">Diverifikasi</span>
                                </div>
                            @else
                                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 mt-0.5">
                                    <i data-lucide="circle" class="w-3 h-3"></i>
                                </span>
                                <div>
                                    <span class="font-bold text-slate-500 dark:text-slate-400 block">Verifikasi Berkas</span>
                                    <span class="text-[10px] text-slate-400 block uppercase">Belum Dikirim</span>
                                </div>
                            @endif
                        </div>

                        <!-- Step 4: Ta'aruf (Observasi) -->
                        <div class="flex items-start gap-2.5">
                            @if(in_array($status, ['verified']))
                                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 flex items-center justify-center text-amber-600 dark:text-amber-450 mt-0.5">
                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                </span>
                                <div>
                                    <span class="font-bold text-slate-700 dark:text-slate-200 block">Observasi Ta'aruf</span>
                                    <span class="text-[10px] text-amber-600 font-semibold block uppercase">Dijadwalkan</span>
                                </div>
                            @elseif(in_array($status, ['accepted', 'agreement_signed', 'completed']))
                                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 flex items-center justify-center text-green-600 dark:text-green-450 mt-0.5">
                                    <i data-lucide="check" class="w-3 h-3"></i>
                                </span>
                                <div>
                                    <span class="font-bold text-slate-700 dark:text-slate-200 block">Observasi Ta'aruf</span>
                                    <span class="text-[10px] text-green-600 font-semibold block uppercase">Selesai</span>
                                </div>
                            @else
                                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 mt-0.5">
                                    <i data-lucide="circle" class="w-3 h-3"></i>
                                </span>
                                <div>
                                    <span class="font-bold text-slate-500 dark:text-slate-400 block">Observasi Ta'aruf</span>
                                    <span class="text-[10px] text-slate-400 block uppercase">Menunggu Jadwal</span>
                                </div>
                            @endif
                        </div>

                        <!-- Step 5: Kelulusan & Diterima -->
                        <div class="flex items-start gap-2.5">
                            @if(in_array($status, ['accepted', 'agreement_signed', 'completed']))
                                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 flex items-center justify-center text-green-600 dark:text-green-450 mt-0.5">
                                    <i data-lucide="award" class="w-3 h-3"></i>
                                </span>
                                <div>
                                    <span class="font-bold text-slate-700 dark:text-slate-200 block">Kelulusan & Hasil</span>
                                    <span class="text-[10px] text-green-600 font-semibold block uppercase">Diterima</span>
                                </div>
                            @else
                                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 mt-0.5">
                                    <i data-lucide="circle" class="w-3.5 h-3.5"></i>
                                </span>
                                <div>
                                    <span class="font-bold text-slate-500 dark:text-slate-400 block">Kelulusan & Hasil</span>
                                    <span class="text-[10px] text-slate-400 block uppercase">Belum Diumumkan</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Card 3: Bantuan / WhatsApp Contact (Dynamic per Unit) -->
            @php
                $currentUnit = $registration->unit;
                $unitWaUrl = $currentUnit ? $currentUnit->getWhatsappUrl($registration->candidate_name, $registration->registration_number) : 'https://wa.me/6281234567890';
                $unitWaNumber = $currentUnit?->whatsapp_number ?: \App\Models\Setting::get('spmb_whatsapp_general', '0812-3456-7890');
                $unitAdminLabel = $currentUnit?->admin_contact_name ?: ('Admin ' . ($currentUnit?->name ?? 'SPMB'));
                $otherUnits = \App\Models\SpmbUnit::where('is_active', true)->get();
            @endphp
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-150/80 dark:border-slate-800 space-y-4 text-left">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h4 class="font-extrabold text-slate-850 dark:text-white text-xs uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="help-circle" class="w-4 h-4 text-brand-emerald"></i> Butuh Bantuan?
                    </h4>
                    @if($otherUnits->count() > 1)
                        <button type="button" onclick="document.getElementById('allUnitsWaModal').classList.remove('hidden')" class="text-[10px] text-brand-emerald dark:text-emerald-400 font-bold hover:underline flex items-center gap-0.5">
                            Kontak Unit Lain <i data-lucide="chevron-right" class="w-3 h-3"></i>
                        </button>
                    @endif
                </div>

                <p class="text-[10px] text-slate-500 dark:text-slate-450 leading-relaxed">
                    Mengalami kendala saat pengisian formulir, dokumen, atau pembayaran untuk unit <strong class="text-slate-700 dark:text-slate-300">{{ $currentUnit->name ?? 'Sekolah Anak Saleh' }}</strong>?
                </p>

                <div class="p-3 bg-slate-50 dark:bg-slate-850 rounded-2xl border border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-brand-emerald flex items-center justify-center flex-shrink-0">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                        </div>
                        <div class="truncate">
                            <span class="text-[11px] font-bold text-slate-800 dark:text-white block truncate">{{ $unitAdminLabel }}</span>
                            <span class="text-[10px] text-slate-400 font-mono block">{{ $unitWaNumber }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <a href="{{ $unitWaUrl }}" target="_blank" class="w-full py-3 bg-emerald-50 dark:bg-emerald-950/30 hover:bg-brand-emerald hover:text-white dark:hover:bg-brand-emerald text-brand-emerald dark:text-emerald-400 text-xs font-bold rounded-xl transition flex items-center justify-center gap-2 border border-emerald-200 dark:border-emerald-900/50 shadow-sm group">
                        <i data-lucide="message-square" class="w-4 h-4 group-hover:scale-110 transition-transform"></i> Hubungi WhatsApp Admin
                    </a>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- Modal Kontak WhatsApp Seluruh Unit -->
<div id="allUnitsWaModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4 transition-all">
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-100 dark:border-slate-800 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-brand-emerald flex items-center justify-center">
                    <i data-lucide="phone-call" class="w-4 h-4"></i>
                </div>
                <h3 class="font-extrabold text-slate-800 dark:text-white text-sm">Kontak Admin SPMB Seluruh Unit</h3>
            </div>
            <button type="button" onclick="document.getElementById('allUnitsWaModal').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <p class="text-xs text-slate-500 dark:text-slate-400">
            Pilih unit sekolah untuk terhubung langsung dengan Panitia SPMB via WhatsApp:
        </p>

        <div class="space-y-2.5 max-h-80 overflow-y-auto pr-1">
            @foreach($otherUnits as $u)
                <div class="p-3.5 bg-slate-50 dark:bg-slate-850 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/20 rounded-2xl border border-slate-200/70 dark:border-slate-800 flex items-center justify-between gap-3 transition">
                    <div>
                        <span class="text-xs font-extrabold text-slate-800 dark:text-white block">{{ $u->name }}</span>
                        <div class="flex items-center gap-2 mt-0.5 text-[10px] text-slate-500">
                            <span>{{ $u->admin_contact_name ?: 'Admin Unit' }}</span>
                            <span>•</span>
                            <span class="font-mono font-semibold">{{ $u->whatsapp_number ?: '-' }}</span>
                        </div>
                    </div>
                    <a href="{{ $u->getWhatsappUrl($registration->candidate_name ?? null, $registration->registration_number ?? null) }}" target="_blank" class="px-3 py-2 bg-brand-emerald hover-emerald text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap shadow-sm">
                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> Chat
                    </a>
                </div>
            @endforeach
        </div>

        <div class="pt-2 text-center">
            <button type="button" onclick="document.getElementById('allUnitsWaModal').classList.add('hidden')" class="w-full py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const feeBniVa = {{ \App\Models\Setting::get('fee_bni_va', 1500) }};
        const feeBniQris = {{ \App\Models\Setting::get('fee_bni_qris', 0.7) }} / 100;
        const feeWinpayVa = {{ \App\Models\Setting::get('fee_winpay_va', 4500) }};

        // Form 1 Calculation (Registration Fee)
        const baseAmount1 = {{ $feeAmount ?? 350000 }};
        const adminFeeEl1 = document.getElementById('displayAdminFeeForm1');
        const grandTotalEl1 = document.getElementById('displayGrandTotalForm1');

        function updateForm1Summary() {
            const form1 = document.getElementById('paymentSummaryCardForm1');
            if (!form1) return;
            const selectedRadio = form1.closest('form').querySelector('input[name="payment_method"]:checked');
            if (!selectedRadio) return;

            const method = selectedRadio.value;
            let adminFee = 0;
            if (method === 'BNI') {
                adminFee = feeBniVa;
            } else if (method === 'QRIS') {
                adminFee = Math.round(baseAmount1 * feeBniQris);
            } else {
                adminFee = feeWinpayVa;
            }

            const grandTotal = baseAmount1 + adminFee;

            if (adminFeeEl1) adminFeeEl1.innerText = 'Rp ' + adminFee.toLocaleString('id-ID');
            if (grandTotalEl1) grandTotalEl1.innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
        }

        // Form 2 Calculation (Final Fee)
        const baseAmount2 = {{ $finalDetails['total'] ?? 0 }};
        const adminFeeEl2 = document.getElementById('displayAdminFeeForm2');
        const grandTotalEl2 = document.getElementById('displayGrandTotalForm2');

        function updateForm2Summary() {
            const form2 = document.getElementById('paymentSummaryCardForm2');
            if (!form2) return;
            const selectedRadio = form2.closest('form').querySelector('input[name="payment_method"]:checked');
            if (!selectedRadio) return;

            const method = selectedRadio.value;
            let adminFee = 0;
            if (method === 'BNI') {
                adminFee = feeBniVa;
            } else if (method === 'QRIS') {
                adminFee = Math.round(baseAmount2 * feeBniQris);
            } else {
                adminFee = feeWinpayVa;
            }

            const grandTotal = baseAmount2 + adminFee;

            if (adminFeeEl2) adminFeeEl2.innerText = 'Rp ' + adminFee.toLocaleString('id-ID');
            if (grandTotalEl2) grandTotalEl2.innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
        }

        // Attach event listeners for Form 1
        const form1Card = document.getElementById('paymentSummaryCardForm1');
        if (form1Card) {
            form1Card.closest('form').querySelectorAll('input[name="payment_method"]').forEach(radio => {
                radio.addEventListener('change', updateForm1Summary);
            });
            updateForm1Summary();
        }

        // Attach event listeners for Form 2
        const form2Card = document.getElementById('paymentSummaryCardForm2');
        if (form2Card) {
            form2Card.closest('form').querySelectorAll('input[name="payment_method"]').forEach(radio => {
                radio.addEventListener('change', updateForm2Summary);
            });
            updateForm2Summary();
        }
    });
</script>
@endsection
