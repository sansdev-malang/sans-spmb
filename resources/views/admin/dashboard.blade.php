@extends('layouts.admin')

@section('title', 'Admin Dashboard - Portal SPMB')
@section('page_title', 'Dashboard Utama')

@section('content')
<div class="space-y-8">
    
    <!-- Top Greeting Header -->
    <div class="bg-gradient-to-r from-emerald-800 to-emerald-950 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-16 -top-16 w-44 h-44 rounded-full bg-white/10 blur-2xl"></div>
        <div class="absolute -left-10 -bottom-10 w-36 h-36 rounded-full bg-emerald-400/20 blur-xl"></div>
        
        <div class="space-y-2 relative z-10">
            <h2 class="text-2xl font-black tracking-wide">Selamat Datang di Portal Panitia SPMB</h2>
            <p class="text-xs text-brand-yellow font-bold uppercase tracking-widest">
                Sekolah Anak Saleh • 
                @if(auth()->user()->isSuperAdmin())
                    Pusat Kendali Administrasi
                @else
                    Pusat Kendali {{ auth()->user()->spmbUnit->name ?? 'Unit' }}
                @endif
            </p>
            <p class="text-xs text-emerald-100 max-w-xl font-medium leading-relaxed mt-2">
                @if(auth()->user()->isSuperAdmin())
                    Berikut adalah rangkuman performa statistik pendaftaran calon siswa baru, penerimaan kas, dan rincian biaya transaksi payment gateway secara real-time. Kelola verifikasi berkas secara berkala.
                @else
                    Berikut adalah rangkuman performa statistik pendaftaran calon siswa baru, penerimaan kas, dan rincian transaksi pada unit {{ auth()->user()->spmbUnit->name ?? 'Unit' }} secara real-time.
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2 relative z-10 flex-shrink-0">
            <a href="{{ route('admin.verification') }}" class="bg-brand-yellow hover:bg-yellow-500 text-slate-900 px-5 py-3 rounded-2xl text-xs font-black shadow-md transition flex items-center gap-1.5">
                <i data-lucide="file-check-corner" class="w-4 h-4 text-slate-900"></i> Verifikasi Pendaftaran
            </a>
            <a href="{{ route('admin.payments.data') }}" class="bg-white/15 hover:bg-white/25 text-white border border-white/20 px-4 py-3 rounded-2xl text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                <i data-lucide="wallet-cards" class="w-4 h-4 text-brand-yellow"></i> Data Pembayaran
            </a>
        </div>
    </div>

    <!-- Core Operational Stats Grid: 4 Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 dashboard-stat-grid">
        <!-- Stat 1: Total Pendaftar -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold block uppercase tracking-wider">Total Pendaftar</span>
                <span class="text-2xl font-black text-slate-800 dark:text-white block mt-1 stat-counter" data-target="{{ $totalCandidates }}">{{ number_format($totalCandidates, 0, ',', '.') }}</span>
                <span class="text-[11px] text-slate-400 font-medium block mt-0.5">Calon Siswa Terdata</span>
            </div>
            <div class="h-11 w-11 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Stat 2: Berkas Masuk -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold block uppercase tracking-wider">Berkas Masuk</span>
                <span class="text-2xl font-black text-amber-600 dark:text-amber-400 block mt-1 stat-counter" data-target="{{ $submittedCandidates }}">{{ number_format($submittedCandidates, 0, ',', '.') }}</span>
                <span class="text-[11px] text-amber-600/80 font-medium block mt-0.5">Menunggu Verifikasi</span>
            </div>
            <div class="h-11 w-11 bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 rounded-2xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="file-text" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Stat 3: Terverifikasi / Lolos -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold block uppercase tracking-wider">Terverifikasi</span>
                <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 block mt-1 stat-counter" data-target="{{ $verifiedCandidates }}">{{ number_format($verifiedCandidates, 0, ',', '.') }}</span>
                <span class="text-[11px] text-emerald-600/80 font-medium block mt-0.5">Lolos Berkas & Lanjutan</span>
            </div>
            <div class="h-11 w-11 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
            </div>
        </div>

        <!-- Stat 4: Transaksi Berhasil -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-400 font-bold block uppercase tracking-wider">Transaksi Sukses</span>
                <span class="text-2xl font-black text-blue-600 dark:text-blue-400 block mt-1 stat-counter" data-target="{{ $paidTransactions }}">{{ number_format($paidTransactions, 0, ',', '.') }}</span>
                <span class="text-[11px] text-blue-600/80 font-medium block mt-0.5">Pembayaran Gateway</span>
            </div>
            <div class="h-11 w-11 bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="wallet" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- FINANCIAL & TRANSACTION INTELLIGENCE SECTION                              -->
    <!-- ========================================================================= -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-6 dashboard-animate-card">
        
        <!-- Financial Section Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="h-9 w-9 bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald rounded-xl flex items-center justify-center">
                        <i data-lucide="circle-dollar-sign" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-800 dark:text-white">
                            Ikhtisar Keuangan & Biaya Transaksi Gateway
                        </h3>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                            Rangkuman penerimaan kas bersih sekolah, potongan biaya admin payment gateway (MDR), dan realisasi tagihan biaya masuk (DSP).
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.payments.data') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-emerald hover:text-emerald-700 dark:hover:text-emerald-400 transition bg-emerald-50 dark:bg-emerald-950/50 hover:bg-emerald-100 px-3.5 py-2 rounded-xl border border-emerald-200 dark:border-emerald-800/60">
                    <i data-lucide="receipt-text" class="w-4 h-4"></i> Kelola Billing & Diskon →
                </a>
            </div>
        </div>

        <!-- 4 Financial Summary KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Financial Card 1: Penerimaan Bersih Sekolah (Net Revenue) -->
            <div class="bg-gradient-to-br from-emerald-50/70 via-white to-white dark:from-emerald-950/30 dark:via-slate-900 dark:to-slate-900 p-5 rounded-2xl border border-emerald-200/80 dark:border-emerald-800/40 shadow-xs relative overflow-hidden flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300">
                        <i data-lucide="shield-check" class="w-3 h-3"></i> Kas Bersih Sekolah
                    </span>
                    <div class="h-8 w-8 rounded-lg bg-emerald-100 dark:bg-emerald-900/60 text-brand-emerald flex items-center justify-center">
                        <i data-lucide="landmark" class="w-4 h-4"></i>
                    </div>
                </div>
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold block">Total Penerimaan Bersih</span>
                    <span class="text-xl sm:text-2xl font-black text-emerald-700 dark:text-emerald-400 block mt-0.5 stat-counter" data-target="{{ $totalNetRevenue }}" data-prefix="Rp " data-format="currency">
                        Rp {{ number_format($totalNetRevenue, 0, ',', '.') }}
                    </span>
                </div>
                <div class="pt-2 border-t border-emerald-100/80 dark:border-emerald-900/40 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between font-medium">
                    <span>Dana pokok masuk</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">100% Kas Sekolah</span>
                </div>
            </div>

            <!-- Financial Card 2: Biaya Admin Payment Gateway (MDR / Admin Fee) -->
            <div class="bg-gradient-to-br from-amber-50/70 via-white to-white dark:from-amber-950/30 dark:via-slate-900 dark:to-slate-900 p-5 rounded-2xl border border-amber-200/80 dark:border-amber-800/40 shadow-xs relative overflow-hidden flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-amber-100 dark:bg-amber-900/60 text-amber-900 dark:text-amber-300">
                        <i data-lucide="percent" class="w-3 h-3"></i> Biaya Admin Gateway (MDR)
                    </span>
                    <div class="h-8 w-8 rounded-lg bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 flex items-center justify-center">
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                    </div>
                </div>
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold block">Total Potongan Admin PG</span>
                    <span class="text-xl sm:text-2xl font-black text-amber-700 dark:text-amber-400 block mt-0.5 stat-counter" data-target="{{ $totalAdminFee }}" data-prefix="Rp " data-format="currency">
                        Rp {{ number_format($totalAdminFee, 0, ',', '.') }}
                    </span>
                </div>
                <div class="pt-2 border-t border-amber-100/80 dark:border-amber-900/40 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between font-medium">
                    <span>Fee Winpay / Bank VA / QRIS</span>
                    <span class="font-bold text-amber-700 dark:text-amber-400">{{ $paidTransactions }} Transaksi</span>
                </div>
            </div>

            <!-- Financial Card 3: Total Transaksi Bruto (Gross Paid by Guardians) -->
            <div class="bg-gradient-to-br from-blue-50/70 via-white to-white dark:from-blue-950/30 dark:via-slate-900 dark:to-slate-900 p-5 rounded-2xl border border-blue-200/80 dark:border-blue-800/40 shadow-xs relative overflow-hidden flex flex-col justify-between space-y-3">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-blue-100 dark:bg-blue-900/60 text-blue-900 dark:text-blue-300">
                        <i data-lucide="coins" class="w-3 h-3"></i> Total Bruto Masuk
                    </span>
                    <div class="h-8 w-8 rounded-lg bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 flex items-center justify-center">
                        <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                    </div>
                </div>
                <div>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold block">Total Bayar Seluruh Wali</span>
                    <span class="text-xl sm:text-2xl font-black text-blue-700 dark:text-blue-400 block mt-0.5 stat-counter" data-target="{{ $totalGrossRevenue }}" data-prefix="Rp " data-format="currency">
                        Rp {{ number_format($totalGrossRevenue, 0, ',', '.') }}
                    </span>
                </div>
                <div class="pt-2 border-t border-blue-100/80 dark:border-blue-900/40 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between font-medium">
                    <span>Pokok + Biaya Admin PG</span>
                    <span class="font-bold text-blue-700 dark:text-blue-400">Total Mutasi</span>
                </div>
            </div>

            <!-- Financial Card 4: Komposisi Formulir vs DSP (Biaya Masuk) -->
            <div class="bg-gradient-to-br from-purple-50/70 via-white to-white dark:from-purple-950/30 dark:via-slate-900 dark:to-slate-900 p-5 rounded-2xl border border-purple-200/80 dark:border-purple-800/40 shadow-xs relative overflow-hidden flex flex-col justify-between space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-purple-100 dark:bg-purple-900/60 text-purple-900 dark:text-purple-300">
                        <i data-lucide="layers" class="w-3 h-3"></i> Pos Penerimaan Kas
                    </span>
                    <div class="h-8 w-8 rounded-lg bg-purple-100 dark:bg-purple-900/60 text-purple-700 dark:text-purple-300 flex items-center justify-center">
                        <i data-lucide="pie-chart" class="w-4 h-4"></i>
                    </div>
                </div>
                
                <div class="space-y-1.5 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Formulir ({{ $formFeeTrxCount }})</span>
                        <span class="font-extrabold text-purple-700 dark:text-purple-400">Rp {{ number_format($formFeeNet, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-[10px] text-slate-400">
                        <span>Biaya Admin PG</span>
                        <span>Rp {{ number_format($formFeeAdmin, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between items-center pt-1 border-t border-purple-100 dark:border-purple-900/40">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Biaya Masuk/DSP ({{ $dspFeeTrxCount }})</span>
                        <span class="font-extrabold text-emerald-700 dark:text-emerald-400">Rp {{ number_format($dspFeeNet, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-[10px] text-slate-400">
                        <span>Biaya Admin PG</span>
                        <span>Rp {{ number_format($dspFeeAdmin, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- 2-Column Interactive Row: Recent Transactions Stream (Left) & Channel Breakdown + DSP Receivables (Right) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-2">
            
            <!-- Left Column (2 Cols): Live Recent Transactions Stream Widget -->
            <div class="lg:col-span-2 bg-slate-50/60 dark:bg-slate-800/40 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200/80 dark:border-slate-800">
                    <h4 class="text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wide flex items-center gap-2">
                        <i data-lucide="receipt" class="w-4 h-4 text-brand-emerald"></i>
                        Mutasi Transaksi Pembayaran Terbaru
                    </h4>
                    <span class="text-[11px] text-slate-400 font-medium">Real-time Stream</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200/80 dark:border-slate-800 text-[11px] text-slate-400 font-bold uppercase tracking-wider bg-white/60 dark:bg-slate-900/60">
                                <th class="py-2.5 px-3">Calon Siswa & Unit</th>
                                <th class="py-2.5 px-3">Kanal Bayar</th>
                                <th class="py-2.5 px-3 text-right">Pokok Bersih</th>
                                <th class="py-2.5 px-3 text-right">Fee PG</th>
                                <th class="py-2.5 px-3 text-right">Total Bayar</th>
                                <th class="py-2.5 px-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($recentPayments as $payment)
                                @php
                                    $net = $payment->base_amount ?? ($payment->amount - ($payment->admin_fee ?? 0));
                                    $fee = $payment->admin_fee ?? 0;
                                    $isRegFee = ($payment->payment_type === 'registration_fee');
                                @endphp
                                <tr class="hover:bg-white/80 dark:hover:bg-slate-800/70 transition">
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-slate-800 dark:text-slate-100 flex items-center gap-1.5">
                                            {{ $payment->registration->candidate_name ?? 'Calon Siswa #' . $payment->registration_id }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                            <span class="font-semibold text-slate-500 dark:text-slate-400">{{ strtoupper($payment->registration->unit->name ?? '-') }}</span>
                                            <span>•</span>
                                            <span class="px-1.5 py-0.2 rounded {{ $isRegFee ? 'bg-purple-50 text-purple-600 dark:bg-purple-950/60 dark:text-purple-300' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-300' }} font-bold text-[9px]">
                                                {{ $isRegFee ? 'Formulir' : 'Biaya Masuk (DSP)' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        @php
                                            $paymentLogo = $payment->getLogoUrl();
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-[10px]">
                                            @if($paymentLogo)
                                                <span class="w-10 h-8 flex items-center justify-center flex-shrink-0 bg-white dark:bg-slate-700/80 rounded px-0.5 py-0.5 border border-slate-200/60 dark:border-slate-700">
                                                    <img src="{{ $paymentLogo }}" alt="{{ $payment->channel_display_name }}" class="h-8 w-8 object-contain">
                                                </span>
                                            @else
                                                <i data-lucide="credit-card" class="w-3 h-3 text-slate-400"></i>
                                            @endif
                                            <span>{{ $payment->channel_display_name }}</span>
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-bold text-emerald-600 dark:text-emerald-400">
                                        Rp {{ number_format($net, 0, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-medium text-amber-600 dark:text-amber-400 text-[11px]">
                                        Rp {{ number_format($fee, 0, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-black text-slate-800 dark:text-slate-200">
                                        Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        @if($payment->status === 'success')
                                            <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 font-extrabold text-[10px]">
                                                <i data-lucide="check" class="w-3 h-3"></i> Lunas
                                            </span>
                                        @elseif($payment->status === 'pending')
                                            <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 font-extrabold text-[10px]">
                                                <i data-lucide="clock" class="w-3 h-3"></i> Pending
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-extrabold text-[10px]">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 font-semibold text-xs">
                                        Belum ada data transaksi pembayaran yang tercatat pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-2 flex justify-between items-center text-xs">
                    <span class="text-slate-400">Menampilkan {{ count($recentPayments) }} mutasi transaksi terbaru</span>
                    <a href="{{ route('admin.payments.data') }}" class="font-bold text-brand-emerald hover:underline flex items-center gap-1">
                        Buka Riwayat Billing Lengkap <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>

            <!-- Right Column (1 Col): Payment Channels & DSP Receivable Status -->
            <div class="space-y-4">
                
                <!-- Card 1: Distribusi Kanal Pembayaran & Total Admin Fee -->
                <div class="bg-slate-50/60 dark:bg-slate-800/40 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200/80 dark:border-slate-800">
                        <h4 class="text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wide flex items-center gap-1.5">
                            <i data-lucide="credit-card" class="w-4 h-4 text-brand-emerald"></i>
                            Kanal Pembayaran Digunakan
                        </h4>
                    </div>

                    <div class="space-y-2.5 pt-1">
                        @forelse($channelStats as $stat)
                            @php
                                $channelPct = $paidTransactions > 0 ? round(($stat['count'] / $paidTransactions) * 100) : 0;
                            @endphp
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
                                    <span class="flex items-center gap-1.5">
                                        @if(!empty($stat['logo_url']))
                                            <span class="w-10 h-8 flex items-center justify-center flex-shrink-0 bg-white dark:bg-slate-700/80 rounded px-0.5 py-0.5 border border-slate-200/60 dark:border-slate-700">
                                                <img src="{{ $stat['logo_url'] }}" alt="{{ $stat['channel'] }}" class="w-8 h-8 object-contain">
                                            </span>
                                        @else
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        @endif
                                        <span>{{ $stat['channel'] }}</span>
                                    </span>
                                    <span class="text-[11px] text-slate-500">{{ $stat['count'] }} trx ({{ $channelPct }}%)</span>
                                </div>
                                <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-brand-emerald h-full rounded-full progress-bar" data-width="{{ $channelPct }}" style="width: {{ $channelPct }}%"></div>
                                </div>
                                <div class="flex justify-between text-[10px] text-slate-400 pt-0.5">
                                    <span>Pokok: Rp {{ number_format($stat['net'], 0, ',', '.') }}</span>
                                    <span class="text-amber-600 dark:text-amber-400 font-semibold">Fee PG: Rp {{ number_format($stat['admin_fee'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-4 font-semibold">Belum ada kanal pembayaran terpakai.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Card 2: Monitoring Piutang DSP (Dana Sumbangan Pendidikan) -->
                <div class="bg-gradient-to-br from-indigo-50/50 via-slate-50/60 to-white dark:from-indigo-950/20 dark:via-slate-800/40 dark:to-slate-900 rounded-2xl border border-indigo-100 dark:border-indigo-900/40 p-5 space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-indigo-100/80 dark:border-indigo-900/40">
                        <h4 class="text-xs font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wide flex items-center gap-1.5">
                            <i data-lucide="trending-up" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
                            Realisasi Biaya Masuk (DSP)
                        </h4>
                    </div>

                    <div class="space-y-2 text-xs">
                        @php
                            $dspProgress = $totalDSPNetBilled > 0 ? round(($totalDSPPaid / $totalDSPNetBilled) * 100) : 0;
                        @endphp
                        <div class="flex justify-between items-center text-[11px]">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Target Tagihan Bersih:</span>
                            <span class="font-extrabold text-slate-800 dark:text-slate-200">Rp {{ number_format($totalDSPNetBilled, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-[11px]">
                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Kas DSP Terkumpul:</span>
                            <span class="font-black text-emerald-700 dark:text-emerald-400">Rp {{ number_format($totalDSPPaid, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-[11px]">
                            <span class="text-amber-600 dark:text-amber-400 font-semibold">Sisa Piutang DSP:</span>
                            <span class="font-black text-amber-600 dark:text-amber-400">Rp {{ number_format($totalDSPRemaining, 0, ',', '.') }}</span>
                        </div>

                        <div class="pt-1.5 space-y-1">
                            <div class="flex justify-between text-[10px] font-bold text-slate-500">
                                <span>Persentase Terkumpul</span>
                                <span>{{ $dspProgress }}%</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                                <div class="bg-indigo-600 h-full rounded-full progress-bar" data-width="{{ $dspProgress }}" style="width: {{ $dspProgress }}%"></div>
                            </div>
                        </div>

                        @if($totalDSPDiscount > 0)
                            <div class="pt-1 text-[10px] text-rose-500 font-semibold flex justify-between items-center">
                                <span>Keringanan/Diskon Disetujui:</span>
                                <span>- Rp {{ number_format($totalDSPDiscount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Super Admin Unit Financial Breakdown (Optional consolidated breakdown across units) -->
    @if(auth()->user()->isSuperAdmin() && count($unitFinanceSummary) > 0)
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 sm:p-8 space-y-4 dashboard-animate-card">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-extrabold text-slate-800 dark:text-white uppercase tracking-wide flex items-center gap-2">
                    <i data-lucide="building-2" class="w-4 h-4 text-brand-emerald"></i>
                    Perbandingan Finansial & Penerimaan Kas per Unit Sekolah
                </h3>
                <span class="text-xs text-slate-400 font-semibold">Konsolidasi Multi-Unit</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
                @foreach($unitFinanceSummary as $unitStat)
                    <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-100 dark:border-slate-800 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-slate-800 dark:text-white text-sm">{{ $unitStat['unit']->name }}</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300">
                                {{ $unitStat['paid_trx'] }} Lunas
                            </span>
                        </div>
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Pendaftar:</span>
                                <span class="font-bold text-slate-700 dark:text-slate-200">{{ $unitStat['reg_count'] }} Siswa</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Kas Bersih:</span>
                                <span class="font-black text-emerald-600 dark:text-emerald-400">Rp {{ number_format($unitStat['net_revenue'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-[11px]">
                                <span class="text-slate-400">Fee PG:</span>
                                <span class="font-bold text-amber-600 dark:text-amber-400">Rp {{ number_format($unitStat['admin_fee'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-[11px] pt-1 border-t border-slate-200 dark:border-slate-700">
                                <span class="text-slate-400">Total Bruto:</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($unitStat['gross_revenue'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Charts & Graphics Sections (3 Columns) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Graph 1: Target Pendaftaran Level (Bar Progress) -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 space-y-4 dashboard-animate-card">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-extrabold text-slate-800 dark:text-white uppercase tracking-wide flex items-center gap-1.5">
                    <i data-lucide="graduation-cap" class="w-4 h-4 text-brand-emerald"></i>
                    Pendaftar per Tingkat Kelas
                </h3>
            </div>
            <div class="space-y-4 pt-2">
                @forelse($levelStats as $stat)
                    @php
                        $percentage = $totalCandidates > 0 ? round(($stat->count / $totalCandidates) * 100) : 0;
                    @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
                            <span>{{ $stat->level_name }}</span>
                            <span>{{ $stat->count }} Siswa ({{ $percentage }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-brand-emerald h-full rounded-full progress-bar" data-width="{{ $percentage }}" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6 font-semibold">Belum ada tingkat kelas terdaftar.</p>
                @endforelse
            </div>
        </div>

        <!-- Graph 2: Form Status Metrics (Alur Tahapan SPMB Dinamis) -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 space-y-4 dashboard-animate-card">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-extrabold text-slate-800 dark:text-white uppercase tracking-wide flex items-center gap-1.5">
                    <i data-lucide="git-branch" class="w-4 h-4 text-brand-emerald"></i>
                    Status Alur Pendaftaran
                </h3>
            </div>
            <div class="space-y-3 pt-2">
                @forelse($pipelineStages as $stage)
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
                            <span class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full {{ $stage['dot'] }}"></span>
                                {{ $stage['label'] }}
                            </span>
                            <span>{{ $stage['count'] }} Siswa ({{ $stage['percentage'] }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                            <div class="{{ $stage['color'] }} h-full rounded-full progress-bar" data-width="{{ $stage['percentage'] }}" style="width: {{ $stage['percentage'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6 font-semibold">Belum ada data alur pendaftaran.</p>
                @endforelse
            </div>
        </div>

        <!-- Graph 3: Payment Status Metrics -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 space-y-4 dashboard-animate-card">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-extrabold text-slate-800 dark:text-white uppercase tracking-wide flex items-center gap-1.5">
                    <i data-lucide="bar-chart-3" class="w-4 h-4 text-brand-emerald"></i>
                    Status Pembayaran Formulir
                </h3>
            </div>
            <div class="space-y-4 pt-2">
                @php
                    $paymentLabels = [
                        'Paid' => ['label' => 'Lunas (Paid)', 'dot' => 'bg-emerald-500', 'bar' => 'bg-emerald-500'],
                        'Pending' => ['label' => 'Pending', 'dot' => 'bg-yellow-500', 'bar' => 'bg-yellow-500'],
                        'Unpaid' => ['label' => 'Belum Bayar (Unpaid)', 'dot' => 'bg-slate-300', 'bar' => 'bg-slate-400'],
                    ];
                @endphp
                @foreach($paymentStats as $key => $count)
                    @php
                        $cfg = $paymentLabels[$key] ?? ['label' => $key, 'dot' => 'bg-slate-400', 'bar' => 'bg-slate-400'];
                        $percentage = $totalCandidates > 0 ? round(($count / $totalCandidates) * 100) : 0;
                    @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
                            <span class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full {{ $cfg['dot'] }}"></span>
                                {{ $cfg['label'] }}
                            </span>
                            <span>{{ $count }} Transaksi ({{ $percentage }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                            <div class="{{ $cfg['bar'] }} h-full rounded-full progress-bar" data-width="{{ $percentage }}" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Bottom Dashboard Row: Recent Registrations & System Status / Activity Logs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Column 1 & 2: Recent Registrations -->
        <div class="md:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 space-y-4 dashboard-animate-card">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-extrabold text-slate-800 dark:text-white uppercase tracking-wide flex items-center gap-1.5">
                    <i data-lucide="history" class="w-4 h-4 text-brand-emerald"></i>
                    5 Pendaftaran Terbaru
                </h3>
                <a href="{{ route('admin.verification') }}" class="text-xs font-bold text-brand-emerald hover:underline flex items-center gap-1">
                    Lihat Semua <i data-lucide="chevron-right" class="w-3 h-3"></i>
                </a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-xs text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50 dark:bg-slate-800/50">
                            <th class="py-3 px-4 text-center w-10">No.</th>
                            <th class="py-3 px-4">Calon Siswa</th>
                            <th class="py-3 px-4">Unit</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-slate-100 dark:divide-slate-800 dashboard-table-body">
                        @forelse($recentRegistrations as $reg)
                            <tr class="hover:bg-slate-50/30 dark:hover:bg-slate-800/40 transition table-row-reveal">
                                <td class="py-3 px-4 text-center text-slate-400 font-bold">{{ $loop->iteration }}</td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800 dark:text-slate-100">{{ $reg->candidate_name }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5">Wali: {{ $reg->father_name ?? $reg->mother_name ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-650 dark:text-slate-300">{{ strtoupper($reg->unit->name ?? '-') }}</td>
                                <td class="py-3 px-4 text-center">
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700',
                                            'submitted' => 'bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-900/50',
                                            'verified' => 'bg-green-50 dark:bg-green-950/60 text-green-600 dark:text-green-400 border-green-100 dark:border-green-900/50',
                                            'taaruf_completed' => 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border-indigo-100 dark:border-indigo-900/50',
                                            'agreement_signed' => 'bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 border-purple-100 dark:border-purple-900/50',
                                            'completed' => 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/50',
                                            'failed' => 'bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400 border-red-100 dark:border-red-900/50',
                                        ];
                                        $color = $statusColors[$reg->registration_status] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                                    @endphp
                                    <span class="inline-block px-2.5 py-0.5 rounded-full border {{ $color }} text-xs font-extrabold uppercase">
                                        {{ $reg->registration_status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('admin.verification') }}?search={{ urlencode($reg->candidate_name) }}" class="inline-block bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 px-3 py-1 rounded-lg font-bold transition">
                                        Periksa
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400 font-semibold">Belum ada pendaftaran masuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Column 3: System Info & Recent Activity Logs -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm p-6 space-y-4 dashboard-animate-card">
            @if(auth()->user()->isSuperAdmin())
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-xs font-extrabold text-slate-800 dark:text-white uppercase tracking-wide flex items-center gap-1.5">
                        <i data-lucide="shield-alert" class="w-4 h-4 text-brand-emerald"></i>
                        Status & Aktivitas Sistem
                    </h3>
                </div>
                
                <!-- System Indicators -->
                <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                    <div class="p-3 bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-xl mini-card-reveal">
                        <span class="text-xs font-bold text-slate-400 block uppercase">Wali Terdaftar</span>
                        <span class="font-extrabold text-slate-800 dark:text-white text-sm mt-0.5 block">{{ $totalGuardiansCount }} Akun</span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-xl mini-card-reveal">
                        <span class="text-xs font-bold text-slate-400 block uppercase">Gelombang Aktif</span>
                        @php
                            $activeWaveNames = $activeWaves->pluck('name')->implode(', ');
                        @endphp
                        <span class="font-extrabold text-brand-emerald text-xs mt-0.5 block truncate" title="{{ $activeWaveNames ?: 'Tidak ada' }}">
                            {{ $activeWaveNames ?: 'Tidak ada' }}
                        </span>
                    </div>
                </div>

                <!-- Recent Mini Logs -->
                <div class="space-y-3 pt-2">
                    <span class="text-xs font-black text-slate-400 uppercase tracking-wider block">Log Aktivitas Terbaru</span>
                    <div class="space-y-2.5">
                        @forelse($recentLogs as $log)
                            <div class="text-[11px] leading-relaxed border-l-2 border-brand-emerald pl-2 py-0.5 log-item-reveal">
                                <div class="flex justify-between text-xs text-slate-400 font-semibold">
                                    <span class="font-bold text-slate-600 dark:text-slate-300 truncate max-w-[80px]" title="{{ $log->user_name }}">{{ $log->user_name }}</span>
                                    <span>{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-slate-650 dark:text-slate-400 font-medium mt-0.5">{{ $log->description }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 font-semibold text-center py-4">Belum ada log terekam.</p>
                        @endforelse
                    </div>
                    <a href="{{ route('admin.activity-logs') }}" class="text-xs font-bold text-brand-emerald hover:underline block pt-2">
                        Lihat Semua Log →
                    </a>
                </div>
            @else
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-xs font-extrabold text-slate-800 dark:text-white uppercase tracking-wide flex items-center gap-1.5">
                        <i data-lucide="info" class="w-4 h-4 text-brand-emerald"></i>
                        Informasi Unit
                    </h3>
                </div>
                
                <div class="space-y-4 pt-2">
                    <div class="p-4 bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-2xl space-y-2.5 mini-card-reveal">
                        <div>
                            <span class="text-xs font-bold text-slate-400 block uppercase">Unit Operasional</span>
                            <span class="font-extrabold text-slate-800 dark:text-white text-sm mt-0.5 block">{{ auth()->user()->spmbUnit->name ?? 'Unit' }}</span>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-slate-400 block uppercase">Alamat / Keterangan</span>
                            <span class="text-xs text-slate-600 dark:text-slate-300 font-semibold mt-0.5 block leading-relaxed">
                                {{ auth()->user()->spmbUnit->address ?? 'Pusat Kendali Administrasi Unit Sekolah Anak Saleh.' }}
                            </span>
                        </div>
                    </div>

                    <div class="p-4 bg-emerald-50/50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/60 rounded-2xl text-xs text-brand-emerald leading-relaxed mini-card-reveal">
                        <div class="flex gap-2 items-start">
                            <i data-lucide="check-circle-2" class="w-4.5 h-4.5 text-brand-emerald flex-shrink-0 mt-0.5"></i>
                            <div>
                                <span class="font-bold block text-slate-800 dark:text-white">Status Gelombang</span>
                                @php
                                    $unitWaveNames = $activeWaves->pluck('name')->implode(', ');
                                @endphp
                                <span class="text-[11px] text-slate-500 dark:text-slate-400 font-medium block mt-0.5">
                                    Gelombang registrasi yang aktif saat ini: 
                                    <strong class="text-brand-emerald">{{ $unitWaveNames ?: 'Tidak ada gelombang aktif' }}</strong>.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.2/anime.min.js" referrerpolicy="no-referrer"></script>
<script>
    (function () {
        let hasAnimated = false;

        const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(Math.round(value));

        const animateCounter = (element) => {
            const target = parseFloat(element.dataset.target || '0');
            const prefix = element.dataset.prefix || '';
            const isCurrency = element.dataset.format === 'currency';

            anime({
                targets: { value: 0 },
                value: target,
                duration: 1400,
                easing: 'easeOutExpo',
                round: 1,
                update: function(anim) {
                    const current = anim.animations[0].currentValue;
                    element.textContent = prefix + formatNumber(current);
                }
            });
        };

        const initDashboardAnimations = () => {
            if (typeof anime === 'undefined' || hasAnimated) {
                return;
            }

            const root = document.querySelector('.space-y-8');
            if (!root) {
                return;
            }

            hasAnimated = true;

            anime({
                targets: '.space-y-8 > *',
                opacity: [0, 1],
                translateY: [18, 0],
                delay: anime.stagger(110),
                duration: 750,
                easing: 'easeOutQuad'
            });

            anime({
                targets: '.space-y-8 .bg-gradient-to-r',
                translateY: [-12, 0],
                opacity: [0, 1],
                duration: 900,
                easing: 'easeOutCubic'
            });

            anime({
                targets: '.dashboard-stat-grid > div',
                scale: [0.96, 1],
                opacity: [0, 1],
                delay: anime.stagger(70, { start: 150 }),
                duration: 650,
                easing: 'easeOutQuad'
            });

            anime({
                targets: '.dashboard-animate-card',
                opacity: [0, 1],
                translateY: [20, 0],
                delay: anime.stagger(90, { start: 180 }),
                duration: 700,
                easing: 'easeOutQuad'
            });

            document.querySelectorAll('.stat-counter').forEach(animateCounter);

            anime({
                targets: '.progress-bar',
                width: function(el) {
                    return el.dataset.width + '%';
                },
                delay: anime.stagger(70, { start: 350 }),
                duration: 1200,
                easing: 'easeOutCubic'
            });

            anime({
                targets: '.table-row-reveal',
                opacity: [0, 1],
                translateX: [-12, 0],
                delay: anime.stagger(45, { start: 400 }),
                duration: 650,
                easing: 'easeOutQuad'
            });

            anime({
                targets: '.mini-card-reveal',
                opacity: [0, 1],
                translateY: [14, 0],
                delay: anime.stagger(70, { start: 420 }),
                duration: 650,
                easing: 'easeOutQuad'
            });

            anime({
                targets: '.log-item-reveal',
                opacity: [0, 1],
                translateX: [10, 0],
                delay: anime.stagger(85, { start: 500 }),
                duration: 600,
                easing: 'easeOutQuad'
            });
        };

        document.addEventListener('DOMContentLoaded', initDashboardAnimations);
        document.body.addEventListener('htmx:afterSwap', function (event) {
            if (event.target && event.target.querySelector && event.target.querySelector('.space-y-8')) {
                hasAnimated = false;
                requestAnimationFrame(initDashboardAnimations);
            }
        });

    })();
</script>
@endsection
