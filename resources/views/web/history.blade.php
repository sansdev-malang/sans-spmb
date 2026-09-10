@extends('layouts.portal')

@section('title', 'Status Akhir Pendaftaran - Portal SPMB')

@section('content')
<div class="max-w-7xl mx-auto px-4 pt-4 pb-16 sm:px-6 lg:px-8 space-y-8">

    <!-- PAGE HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200/80 dark:border-slate-800 pb-5">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-brand-emerald text-white shadow-sm flex items-center justify-center">
                    <i data-lucide="history" class="w-5 h-5 text-brand-yellow"></i>
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Status Akhir Pendaftaran</h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                Rekam jejak komprehensif perjalanan pendaftaran ananda dari registrasi akun hingga tahap penerimaan resmi.
            </p>
        </div>
    </div>

    <!-- ACCOUNT SUMMARY CARD -->
    <div class="bg-gradient-to-br from-white via-slate-50 to-emerald-50/30 dark:from-slate-900 dark:via-slate-900 dark:to-emerald-950/20 rounded-3xl p-5 sm:p-7 border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden">
        <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-brand-emerald/5 dark:bg-emerald-500/5 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            <div class="flex items-start sm:items-center gap-4">
                <div class="h-14 w-14 sm:h-16 sm:w-16 rounded-2xl bg-brand-emerald text-white flex items-center justify-center text-xl sm:text-2xl font-black uppercase shadow-md flex-shrink-0 ring-4 ring-emerald-100/60 dark:ring-emerald-950">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                </div>
                <div class="space-y-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white truncate">{{ $user->name }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300/40">
                            Akun Orang Tua / Wali
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                        <span class="flex items-center gap-1">
                            <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i> {{ $user->email }}
                        </span>
                        @if(!empty($user->phone))
                            <span class="flex items-center gap-1">
                                <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400"></i> {{ $user->phone }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Account Meta Badges -->
            <div class="grid grid-cols-2 sm:grid-cols-2 gap-3 sm:gap-4 border-t lg:border-t-0 lg:border-l border-slate-200/80 dark:border-slate-800 pt-4 lg:pt-0 lg:pl-6">
                <div class="bg-white dark:bg-slate-950/60 rounded-2xl p-3.5 border border-slate-200 dark:border-slate-800">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Tanggal Registrasi Akun</span>
                    <span class="text-xs font-black text-slate-900 dark:text-slate-200 flex items-center gap-1.5 mt-1">
                        <i data-lucide="calendar-check" class="w-3.5 h-3.5 text-brand-emerald"></i>
                        {{ $user->created_at ? $user->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') : '-' }} WIB
                    </span>
                </div>
                <div class="bg-white dark:bg-slate-950/60 rounded-2xl p-3.5 border border-slate-200 dark:border-slate-800">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Total Pendaftaran Aktif</span>
                    <span class="text-xs font-black text-slate-900 dark:text-slate-200 flex items-center gap-1.5 mt-1">
                        <i data-lucide="users" class="w-3.5 h-3.5 text-brand-emerald"></i>
                        {{ $registrations->count() }} Calon Murid
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if($registrations->isEmpty())
        <!-- EMPTY STATE -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-12 text-center border border-slate-200/80 dark:border-slate-800 space-y-4 max-w-lg mx-auto shadow-sm">
            <div class="h-16 w-16 bg-slate-100 dark:bg-slate-800 text-slate-400 rounded-2xl flex items-center justify-center mx-auto">
                <i data-lucide="file-x" class="w-8 h-8"></i>
            </div>
            <div class="space-y-1">
                <h3 class="font-extrabold text-base text-slate-900 dark:text-white">Belum Ada Riwayat Pendaftaran</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Akun Anda belum memiliki data pendaftaran murid baru yang aktif atau telah diselesaikan pembayarannya.
                </p>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 bg-brand-emerald hover-emerald text-white px-6 py-2.5 rounded-xl font-bold text-xs shadow-md transition">
                <i data-lucide="user-plus" class="w-4 h-4"></i> Daftarkan Sekarang
            </a>
        </div>
    @else

        <!-- MULTI-CANDIDATE SELECTOR / TABS -->
        @if($registrations->count() > 1)
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="user-check" class="w-4 h-4 text-brand-emerald"></i> Pilih Calon Murid Yang Ditinjau:
                    </span>
                    <span class="text-[11px] text-slate-400 font-semibold">{{ $registrations->count() }} Pendaftaran Ditemukan</span>
                </div>
                <div class="flex items-center gap-3 overflow-x-auto pb-2 scrollbar-thin">
                    @foreach($registrations as $regItem)
                        @php
                            $isActiveTab = ($selectedRegistration && $selectedRegistration->id === $regItem->id);
                            $regStatus = $regItem->registration_status;
                        @endphp
                        <a href="{{ route('dashboard.history', ['id' => $regItem->id]) }}" 
                           class="flex items-center gap-3 px-4 py-3 rounded-2xl border transition-all duration-200 flex-shrink-0 shadow-xs {{ $isActiveTab ? 'bg-brand-emerald text-white border-brand-emerald shadow-md ring-2 ring-emerald-200 dark:ring-emerald-900' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200/80 dark:border-slate-800 hover:border-brand-emerald/50' }}">
                            <div class="h-9 w-9 rounded-xl flex items-center justify-center font-black text-xs {{ $isActiveTab ? 'bg-white/20 text-white' : 'bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald' }}">
                                {{ strtoupper(substr(trim($regItem->candidate_name ?? 'A'), 0, 1)) }}
                            </div>
                            <div class="flex flex-col text-left">
                                <span class="font-bold text-xs leading-snug {{ $isActiveTab ? 'text-white' : 'text-slate-900 dark:text-white' }}">{{ $regItem->candidate_name }}</span>
                                <div class="flex items-center gap-1.5 text-[10px] mt-0.5 {{ $isActiveTab ? 'text-emerald-100' : 'text-slate-400' }}">
                                    <span>{{ $regItem->unit?->code }}</span>
                                    <span>•</span>
                                    <span>{{ $regItem->id_label }}</span>
                                </div>
                            </div>
                            @if($regStatus === 'completed')
                                <span class="ml-2 text-[9px] px-2 py-0.5 rounded-full font-black uppercase {{ $isActiveTab ? 'bg-emerald-800 text-emerald-100' : 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300' }}">
                                    Lunas
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @php
            $reg = $selectedRegistration ?? $registrations->first();
            $status = $reg->registration_status;
            $regFee = $reg->getRegistrationFee();
            $regPayment = $reg->payments()->where('payment_type', 'registration_fee')->where('status', 'success')->latest()->first();
            $isRegPaid = ($regPayment !== null);
            
            // Final Fees calculation
            $grossTuition = (float) $reg->gross_fee;
            $totalDiscount = (float) $reg->total_discount;
            $netTuition = (float) $reg->net_fee;
            $totalPaidFinal = (float) $reg->total_paid_final_fee;
            $remainingTuition = (float) $reg->remaining_balance;
            $isTuitionLunas = ($remainingTuition <= 0 && $status === 'completed');

            $finalPayments = $reg->payments()
                ->where('payment_type', 'final_fee')
                ->where('status', 'success')
                ->with('items')
                ->orderBy('created_at', 'asc')
                ->get();
        @endphp

        <!-- ACTIVE CANDIDATE RECAP CARD -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden space-y-6">

            <!-- Candidate Banner Header -->
            <div class="bg-slate-50/90 dark:bg-slate-950/80 p-5 sm:p-7 border-b border-slate-200/80 dark:border-slate-800 space-y-5">
                <!-- Top Row: Avatar + Name + Registration Number & Status -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="h-14 w-14 sm:h-16 sm:w-16 bg-brand-emerald text-white rounded-2xl flex items-center justify-center text-xl sm:text-2xl font-black shadow-md flex-shrink-0 ring-4 ring-emerald-100/60 dark:ring-emerald-950">
                            {{ strtoupper(substr(trim($reg->candidate_name ?? 'A'), 0, 1)) }}
                        </div>
                        <div class="space-y-1 min-w-0">
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block leading-none">Calon Murid Baru</span>
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <h3 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">{{ $reg->candidate_name }}</h3>
                                <span class="text-xs font-mono font-extrabold px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/80 inline-flex items-center gap-1.5 shadow-2xs">
                                    <i data-lucide="tag" class="w-3.5 h-3.5 text-brand-emerald"></i>
                                    {{ $reg->id_label }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Status Pill & Timestamp -->
                    <div class="flex flex-col sm:items-end gap-1.5 shrink-0 self-start sm:self-auto">
                        <div>
                            @if($status === 'completed')
                                <span class="inline-flex items-center gap-1.5 bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 text-xs font-black px-3.5 py-1.5 rounded-full border border-emerald-300/50 shadow-2xs">
                                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i> RESMI DITERIMA
                                </span>
                            @elseif($status === 'agreement_signed')
                                <span class="inline-flex items-center gap-1.5 bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 text-xs font-black px-3.5 py-1.5 rounded-full border border-amber-300/50 shadow-2xs">
                                    <i data-lucide="clock" class="w-4 h-4 text-amber-600"></i> PELUNASAN ADMINISTRASI
                                </span>
                            @elseif($status === 'taaruf_completed')
                                <span class="inline-flex items-center gap-1.5 bg-indigo-100 text-indigo-800 dark:bg-indigo-950/80 dark:text-indigo-300 text-xs font-black px-3.5 py-1.5 rounded-full border border-indigo-300/50 shadow-2xs">
                                    <i data-lucide="calendar-check" class="w-4 h-4 text-indigo-600"></i> TA'ARUF SELESAI
                                </span>
                            @elseif($status === 'verified')
                                <span class="inline-flex items-center gap-1.5 bg-teal-100 text-teal-800 dark:bg-teal-950/80 dark:text-teal-300 text-xs font-black px-3.5 py-1.5 rounded-full border border-teal-300/50 shadow-2xs">
                                    <i data-lucide="shield-check" class="w-4 h-4 text-teal-600"></i> BERKAS TERVERIFIKASI
                                </span>
                            @elseif($status === 'submitted')
                                <span class="inline-flex items-center gap-1.5 bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300 text-xs font-black px-3.5 py-1.5 rounded-full border border-blue-300/50 shadow-2xs">
                                    <i data-lucide="hourglass" class="w-4 h-4 text-blue-600 animate-spin"></i> PROSES VERIFIKASI
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 text-xs font-black px-3.5 py-1.5 rounded-full border border-slate-200 dark:border-slate-700 shadow-2xs">
                                    <i data-lucide="edit-3" class="w-4 h-4 text-slate-500"></i> DRAF PENDAFTARAN
                                </span>
                            @endif
                        </div>
                        <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                            Didaftarkan: {{ $reg->created_at ? $reg->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') : '-' }} WIB
                        </span>
                    </div>
                </div>

                <!-- Bottom Row: 3-Card Grid for Comprehensive Candidate Meta -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                    <!-- 1. Unit Sekolah -->
                    <div class="flex items-center gap-3 bg-white dark:bg-slate-900 px-4 py-3 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald dark:text-emerald-400 flex items-center justify-center shrink-0 font-bold border border-emerald-200/60 dark:border-emerald-800/60">
                            <i data-lucide="school" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider block leading-none">Unit Sekolah</span>
                            <span class="font-black text-brand-emerald dark:text-emerald-400 truncate block text-xs sm:text-sm mt-1">
                                {{ $reg->unit?->name ?? '-' }}
                            </span>
                        </div>
                    </div>

                    <!-- 2. Jenjang / Kelas & Kategori Murid -->
                    <div class="flex items-center gap-3 bg-white dark:bg-slate-900 px-4 py-3 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald dark:text-emerald-400 flex items-center justify-center shrink-0 font-bold border border-emerald-200/60 dark:border-emerald-800/60">
                            <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider block leading-none">Jenjang & Kelas</span>
                            <span class="font-bold text-slate-900 dark:text-white truncate block text-xs sm:text-sm mt-1">
                                {{ $reg->grade?->name ?? ($reg->admission_level ?? '-') }} ({{ $reg->classProgram?->name ?? 'Reguler' }})
                                @if($reg->extraServices && $reg->extraServices->isNotEmpty())
                                    <span class="text-amber-600 dark:text-amber-400 font-bold text-xs">• {{ $reg->extraServices->pluck('name')->join(', ') }}</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- 3. Jalur, Gelombang & Tahun Pelajaran -->
                    <div class="flex items-center gap-3 bg-white dark:bg-slate-900 px-4 py-3 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-2xs">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald dark:text-emerald-400 flex items-center justify-center shrink-0 font-bold border border-emerald-200/60 dark:border-emerald-800/60">
                            <i data-lucide="layers" class="w-4 h-4"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-1.5">
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider block leading-none">Jalur & Gelombang</span>
                                @if($reg->period?->year || $reg->period?->name)
                                    <span class="text-[9px] font-extrabold text-brand-emerald bg-emerald-50 dark:bg-emerald-950/80 dark:text-emerald-300 px-2 py-0.5 rounded-md border border-emerald-200/80 dark:border-emerald-800/80 leading-none shrink-0 shadow-2xs">
                                        TP {{ $reg->period->year ?? $reg->period->name }}
                                    </span>
                                @endif
                            </div>
                            <span class="font-bold text-slate-900 dark:text-white truncate block text-xs sm:text-sm mt-1">
                                {{ $reg->type?->name ?? 'Reguler' }} • {{ $reg->wave?->name ?? 'Gelombang 1' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 7-STAGE TIMELINE TRACKER -->
            <div class="px-5 sm:px-8 py-2 space-y-6">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h4 class="font-extrabold text-slate-900 dark:text-white text-sm uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="git-commit" class="w-4 h-4 text-brand-emerald"></i>
                        <span>Rekam Jejak & Alur Lengkap Pendaftaran</span>
                    </h4>
                </div>

                <div class="relative space-y-6 sm:space-y-8">
                    <!-- Continuous Vertical Line (Precision Centered at Center of Circle) -->
                    <div class="absolute top-5 bottom-8 left-4 sm:left-5 w-0.5 -translate-x-1/2 bg-emerald-200 dark:bg-emerald-900/60 pointer-events-none"></div>

                    <!-- STAGE 1: REGISTRASI & BIAYA AWAL PENDAFTARAN -->
                    <div class="relative flex items-start gap-3 sm:gap-5 group">
                        <!-- Node Circle -->
                        <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-brand-emerald text-white flex items-center justify-center font-black text-xs sm:text-sm shadow-md ring-4 ring-white dark:ring-slate-900 shrink-0 z-10 select-none">
                            1
                        </div>
                        <div class="flex-1 min-w-0 bg-slate-50/80 dark:bg-slate-950/60 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-800 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="space-y-0.5">
                                    <h5 class="font-extrabold text-xs sm:text-sm text-slate-900 dark:text-white flex items-center gap-1.5 flex-wrap">
                                        <span>Registrasi & Biaya Awal Pendaftaran</span>
                                        @if($isRegPaid)
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300 font-bold uppercase">Lunas</span>
                                        @else
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 font-bold uppercase">Belum Lunas</span>
                                        @endif
                                    </h5>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Pendaftaran murid baru dan penyelesaian setoran biaya awal pendaftaran.</p>
                                </div>
                                <span class="text-[10px] font-bold text-slate-400 shrink-0">
                                    {{ $reg->created_at ? $reg->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') : '-' }} WIB
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1 text-xs">
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Biaya Awal Pendaftaran</span>
                                    <span class="font-black text-slate-900 dark:text-white mt-1 block">Rp {{ number_format($regFee->amount ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Metode Pembayaran</span>
                                    <span class="font-bold text-slate-900 dark:text-white uppercase mt-1 block">{{ $regPayment->payment_method ?? ($regPayment->payment_channel ?? 'Winpay') }}</span>
                                </div>
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                                    <div>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kwitansi Pendaftaran</span>
                                        <span class="font-bold text-slate-900 dark:text-slate-300 mt-1 block">{{ $regPayment ? 'Tersedia' : 'Belum Ada' }}</span>
                                    </div>
                                    @if($regPayment)
                                        <a href="{{ route('dashboard.payment.receipt', $regPayment->id) }}" class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition" title="Unduh Kwitansi">
                                            <i data-lucide="download" class="w-4 h-4"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STAGE 2: PENGISIAN BIODATA & BERKAS -->
                    <div class="relative flex items-start gap-3 sm:gap-5 group">
                        <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full {{ in_array($status, ['submitted', 'verified', 'taaruf_completed', 'agreement_signed', 'completed']) ? 'bg-brand-emerald text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-500' }} flex items-center justify-center font-black text-xs sm:text-sm shadow-md ring-4 ring-white dark:ring-slate-900 shrink-0 z-10 select-none">
                            2
                        </div>
                        <div class="flex-1 min-w-0 bg-slate-50/80 dark:bg-slate-950/60 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-800 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="space-y-0.5">
                                    <h5 class="font-extrabold text-xs sm:text-sm text-slate-900 dark:text-white flex items-center gap-1.5 flex-wrap">
                                        <span>Pengisian Biodata & Berkas Persyaratan</span>
                                        @if(in_array($status, ['submitted', 'verified', 'taaruf_completed', 'agreement_signed', 'completed']))
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300 font-bold uppercase">Lengkap & Terkirim</span>
                                        @else
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 font-bold uppercase">Dalam Pengisian</span>
                                        @endif
                                    </h5>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Kelengkapan data diri ananda, orang tua/wali, dan unggah dokumen persyaratan.</p>
                                </div>
                                @if($reg->submitted_at)
                                    <span class="text-[10px] font-bold text-slate-400 shrink-0">
                                        {{ \Carbon\Carbon::parse($reg->submitted_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                                    </span>
                                @elseif(in_array($status, ['submitted', 'verified', 'taaruf_completed', 'agreement_signed', 'completed']))
                                    <span class="text-[10px] font-bold text-slate-400 shrink-0">
                                        {{ $reg->updated_at ? $reg->updated_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : 'Terkirim' }}
                                    </span>
                                @endif
                            </div>

                            <!-- Structured Mini-Cards Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 pt-1 text-xs">
                                <!-- 1. Nama Lengkap -->
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 flex items-start gap-2.5 shadow-2xs">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 font-bold border border-emerald-200/50 dark:border-emerald-800/50">
                                        <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block leading-none">Nama Lengkap</span>
                                        <span class="font-extrabold text-slate-900 dark:text-white truncate block mt-1">{{ $reg->candidate_name }}</span>
                                    </div>
                                </div>

                                <!-- 2. NIK / No. KK -->
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 flex items-start gap-2.5 shadow-2xs">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 font-bold border border-emerald-200/50 dark:border-emerald-800/50">
                                        <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block leading-none">NIK / Identitas</span>
                                        <span class="font-mono font-bold text-slate-900 dark:text-white truncate block mt-1">
                                            {{ $reg->nik ?: ($reg->family_card_no ?: '-') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- 3. Tempat, Tanggal Lahir -->
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 flex items-start gap-2.5 shadow-2xs">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 font-bold border border-emerald-200/50 dark:border-emerald-800/50">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block leading-none">Tempat, Tgl Lahir</span>
                                        <span class="font-bold text-slate-900 dark:text-white truncate block mt-1">
                                            {{ $reg->birth_place ?: '-' }}, {{ $reg->birth_date ? $reg->birth_date->translatedFormat('d F Y') : '-' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- 4. Asal Sekolah -->
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 flex items-start gap-2.5 shadow-2xs">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 font-bold border border-emerald-200/50 dark:border-emerald-800/50">
                                        <i data-lucide="school" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block leading-none">Asal Sekolah</span>
                                        <span class="font-bold text-slate-900 dark:text-white truncate block mt-1">
                                            {{ $reg->previous_school ?: '-' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- 5. Orang Tua / Kontak -->
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 flex items-start gap-2.5 shadow-2xs">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 font-bold border border-emerald-200/50 dark:border-emerald-800/50">
                                        <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block leading-none">Orang Tua / Wali</span>
                                        <span class="font-bold text-slate-900 dark:text-white truncate block mt-1">
                                            {{ $reg->father_name ?: ($reg->mother_name ?: ($reg->user?->name ?? '-')) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- 6. Status Dokumen Persyaratan -->
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 flex items-start gap-2.5 shadow-2xs">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 font-bold border border-emerald-200/50 dark:border-emerald-800/50">
                                        <i data-lucide="file-check-2" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block leading-none">Dokumen Persyaratan</span>
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400 truncate block mt-1 flex items-center gap-1">
                                            <i data-lucide="check-circle" class="w-3 h-3 text-emerald-500"></i> Lengkap Terunggah
                                        </span>
                                    </div>
                                </div>

                                <!-- 7. Alamat Domisili (Spans Full Width) -->
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 sm:col-span-2 lg:col-span-3 flex items-start gap-2.5 shadow-2xs">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-brand-emerald dark:text-emerald-400 flex items-center justify-center shrink-0 mt-0.5 font-bold border border-emerald-200/50 dark:border-emerald-800/50">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block leading-none">Alamat Domisili</span>
                                        <span class="font-bold text-slate-900 dark:text-slate-200 block mt-1 leading-relaxed text-xs">
                                            {{ $reg->getFullCandidateAddress() ?: ($reg->address ?: '-') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STAGE 3: VERIFIKASI BERKAS OLEH PANITIA -->
                    <div class="relative flex items-start gap-3 sm:gap-5 group">
                        <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full {{ in_array($status, ['verified', 'taaruf_completed', 'agreement_signed', 'completed']) ? 'bg-brand-emerald text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-500' }} flex items-center justify-center font-black text-xs sm:text-sm shadow-md ring-4 ring-white dark:ring-slate-900 shrink-0 z-10 select-none">
                            3
                        </div>
                        <div class="flex-1 min-w-0 bg-slate-50/80 dark:bg-slate-950/60 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-800 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="space-y-0.5">
                                    <h5 class="font-extrabold text-xs sm:text-sm text-slate-900 dark:text-white flex items-center gap-1.5 flex-wrap">
                                        <span>Verifikasi Berkas oleh Panitia SPMB</span>
                                        @if(in_array($status, ['verified', 'taaruf_completed', 'agreement_signed', 'completed']))
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-teal-100 dark:bg-teal-950 text-teal-800 dark:text-teal-300 font-bold uppercase">Terverifikasi</span>
                                        @elseif($status === 'submitted')
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300 font-bold uppercase">Menunggu Pemeriksaan</span>
                                        @elseif($status === 'failed')
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-rose-100 dark:bg-rose-950 text-rose-800 dark:text-rose-300 font-bold uppercase">Perlu Perbaikan</span>
                                        @else
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold uppercase">Belum Diajukan</span>
                                        @endif
                                    </h5>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Pemeriksaan keabsahan dokumen persyaratan oleh tim verifikator panitia.</p>
                                </div>
                                @if(in_array($status, ['verified', 'taaruf_completed', 'agreement_signed', 'completed']))
                                    <span class="text-[10px] font-bold text-slate-400 shrink-0">
                                        {{ $reg->verified_at ? \Carbon\Carbon::parse($reg->verified_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : 'Selesai Diverifikasi' }}
                                    </span>
                                @endif
                            </div>

                            @if(!empty($reg->admin_notes))
                                <div class="p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 rounded-xl text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2 shadow-2xs">
                                    <i data-lucide="message-square" class="w-4 h-4 shrink-0 mt-0.5 text-amber-600"></i>
                                    <div>
                                        <strong class="block font-bold">Catatan Panitia:</strong>
                                        <span>{{ $reg->admin_notes }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- STAGE 4: OBSERVASI & TA'ARUF -->
                    <div class="relative flex items-start gap-3 sm:gap-5 group">
                        <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full {{ in_array($status, ['taaruf_completed', 'agreement_signed', 'completed']) ? 'bg-brand-emerald text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-500' }} flex items-center justify-center font-black text-xs sm:text-sm shadow-md ring-4 ring-white dark:ring-slate-900 shrink-0 z-10 select-none">
                            4
                        </div>
                        <div class="flex-1 min-w-0 bg-slate-50/80 dark:bg-slate-950/60 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-800 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="space-y-0.5">
                                    <h5 class="font-extrabold text-xs sm:text-sm text-slate-900 dark:text-white flex items-center gap-1.5 flex-wrap">
                                        <span>Assessment / Ta'aruf</span>
                                        @if(in_array($status, ['taaruf_completed', 'agreement_signed', 'completed']))
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-800 dark:text-indigo-300 font-bold uppercase">Selesai & Lulus</span>
                                        @elseif(!empty($reg->observation_date))
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300 font-bold uppercase">Terjadwal</span>
                                        @else
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold uppercase">Menunggu Jadwal</span>
                                        @endif
                                    </h5>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Sesi assessment kesiapan belajar ananda serta ta'aruf dan penyelarasan visi orang tua/wali.</p>
                                </div>
                            </div>

                            @if(!empty($reg->observation_date))
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1 text-xs">
                                    <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Tanggal Assessment / Ta'aruf</span>
                                        <span class="font-bold text-slate-900 dark:text-white flex items-center gap-1 mt-1">
                                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-brand-emerald"></i>
                                            {{ \Carbon\Carbon::parse($reg->observation_date)->translatedFormat('d F Y') }}
                                        </span>
                                    </div>
                                    <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Waktu / Sesi</span>
                                        <span class="font-bold text-slate-900 dark:text-white flex items-center gap-1 mt-1">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 text-brand-emerald"></i>
                                            {{ $reg->observation_time ?: '-' }}
                                        </span>
                                    </div>
                                    <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800">
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Ruangan / Tempat</span>
                                        <span class="font-bold text-slate-900 dark:text-white flex items-center gap-1 mt-1">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-brand-emerald"></i>
                                            {{ $reg->observation_room ?: ($reg->observation_location ?: 'Kampus Sekolah') }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- STAGE 5: SURAT PERNYATAAN & KERINGANAN/CICILAN -->
                    <div class="relative flex items-start gap-3 sm:gap-5 group">
                        <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full {{ in_array($status, ['agreement_signed', 'completed']) ? 'bg-brand-emerald text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-500' }} flex items-center justify-center font-black text-xs sm:text-sm shadow-md ring-4 ring-white dark:ring-slate-900 shrink-0 z-10 select-none">
                            5
                        </div>
                        <div class="flex-1 min-w-0 bg-slate-50/80 dark:bg-slate-950/60 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-800 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="space-y-0.5">
                                    <h5 class="font-extrabold text-xs sm:text-sm text-slate-900 dark:text-white flex items-center gap-1.5 flex-wrap">
                                        <span>Surat Pernyataan Kesanggupan Tata Tertib & Biaya</span>
                                        @if(in_array($status, ['agreement_signed', 'completed']))
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300 font-bold uppercase">Telah Disetujui</span>
                                        @else
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold uppercase">Belum Ditandatangani</span>
                                        @endif
                                    </h5>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Persetujuan kesanggupan tata tertib sekolah dan komitmen nominal pembiayaan masuk awal murid baru.</p>
                                </div>
                                <span class="text-[10px] font-bold text-slate-400 shrink-0">
                                    {{ $reg->signed_at ? \Carbon\Carbon::parse($reg->signed_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}
                                </span>
                            </div>

                            @if($totalDiscount > 0)
                                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/50 rounded-xl text-xs">
                                    <div class="flex items-center gap-1.5 font-bold text-emerald-800 dark:text-emerald-300">
                                        <i data-lucide="tag" class="w-3.5 h-3.5 text-emerald-600"></i>
                                        <span>Disetujui Keringanan Biaya: Rp {{ number_format($totalDiscount, 0, ',', '.') }} ({{ $reg->discount_notes ?: 'Keringanan Yayasan' }})</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- STAGE 6: ADMINISTRASI DAFTAR ULANG & TRANSAKSI -->
                    <div class="relative flex items-start gap-3 sm:gap-5 group">
                        <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full {{ ($status === 'completed') ? 'bg-brand-emerald text-white' : (in_array($status, ['agreement_signed']) ? 'bg-amber-500 text-white animate-pulse' : 'bg-slate-200 dark:bg-slate-800 text-slate-500') }} flex items-center justify-center font-black text-xs sm:text-sm shadow-md ring-4 ring-white dark:ring-slate-900 shrink-0 z-10 select-none">
                            6
                        </div>
                        <div class="flex-1 min-w-0 bg-slate-50/80 dark:bg-slate-950/60 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-800 space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="space-y-0.5">
                                    <h5 class="font-extrabold text-xs sm:text-sm text-slate-900 dark:text-white flex items-center gap-1.5 flex-wrap">
                                        <span>Administrasi Masuk Awal & Riwayat Setoran</span>
                                        @if($status === 'completed')
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300 font-bold uppercase">Lunas Sepenuhnya</span>
                                        @elseif($totalPaidFinal > 0)
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 font-bold uppercase">Dicicil Sebagian</span>
                                        @else
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 font-bold uppercase">Belum Lunas</span>
                                        @endif
                                    </h5>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Penyelesaian pelunasan komponen pembiayaan masuk awal murid baru.</p>
                                </div>
                            </div>

                            <!-- Financial Ledger Overview -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 shadow-2xs">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Tarif Normal</span>
                                    <span class="font-extrabold text-slate-900 dark:text-slate-200 mt-1 block">Rp {{ number_format($grossTuition, 0, ',', '.') }}</span>
                                </div>
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 shadow-2xs">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Keringanan Diskon</span>
                                    <span class="font-extrabold text-rose-600 dark:text-rose-400 mt-1 block">- Rp {{ number_format($totalDiscount, 0, ',', '.') }}</span>
                                </div>
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 shadow-2xs">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Terbayar</span>
                                    <span class="font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 block">Rp {{ number_format($totalPaidFinal, 0, ',', '.') }}</span>
                                </div>
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 shadow-2xs">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Sisa Tagihan</span>
                                    <span class="font-extrabold {{ $remainingTuition > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-400' }} mt-1 block">Rp {{ number_format($remainingTuition, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            @php
                                $feeDetails = $reg->getFinalFeeDetails();
                                $feeItems = $feeDetails['items'] ?? [];
                            @endphp

                            <!-- Rincian Komponen Pembiayaan Masuk Awal -->
                            @if(!empty($feeItems))
                                <div class="space-y-2 pt-2">
                                    <span class="text-[11px] font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider block">
                                        Rincian Komponen Biaya Masuk Awal:
                                    </span>
                                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 overflow-hidden divide-y divide-slate-100 dark:divide-slate-800 shadow-2xs">
                                        @foreach($feeItems as $fItem)
                                            @php
                                                $fGross = (float) ($fItem['amount'] ?? 0);
                                                $fDiscount = $reg->getItemDiscountAmount($fItem['name'], $fItem['id'] ?? null);
                                                $fNet = max(0, $fGross - $fDiscount);
                                                $fPaid = $reg->getItemPaidAmount($fItem['name'], $fItem['id'] ?? null);
                                                $fRemaining = max(0, $fNet - $fPaid);
                                                $isFLunas = ($fRemaining <= 0 || $status === 'completed');
                                            @endphp
                                            <div class="p-3 sm:px-4 sm:py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 text-xs">
                                                <div class="space-y-0.5 min-w-0">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <strong class="text-slate-900 dark:text-white font-bold">{{ $fItem['name'] }}</strong>
                                                        @if($fDiscount > 0)
                                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 border border-rose-200/60 dark:border-rose-900">
                                                                Diskon Rp {{ number_format($fDiscount, 0, ',', '.') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center gap-3 text-[11px] text-slate-400">
                                                        <span>Tarif: Rp {{ number_format($fGross, 0, ',', '.') }}</span>
                                                        <span>•</span>
                                                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Terbayar: Rp {{ number_format($fPaid, 0, ',', '.') }}</span>
                                                        @if($fRemaining > 0 && $status !== 'completed')
                                                            <span>•</span>
                                                            <span class="text-amber-600 dark:text-amber-400 font-semibold">Sisa: Rp {{ number_format($fRemaining, 0, ',', '.') }}</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="shrink-0 self-start sm:self-auto">
                                                    @if($isFLunas)
                                                        <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2.5 py-1 rounded-lg bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800">
                                                            <i data-lucide="check-circle" class="w-3 h-3 text-green-600 dark:text-green-400"></i> Lunas
                                                        </span>
                                                    @elseif($fPaid > 0)
                                                        <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2.5 py-1 rounded-lg bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                                            <i data-lucide="clock" class="w-3 h-3 text-blue-600 dark:text-blue-400"></i> Dicicil (Rp {{ number_format($fPaid, 0, ',', '.') }})
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 text-[10px] font-extrabold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                            Belum Bayar
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Transaction Receipts List -->
                            @if($finalPayments->isNotEmpty())
                                <div class="space-y-2 pt-2">
                                    <span class="text-[11px] font-extrabold text-slate-700 dark:text-slate-300 uppercase tracking-wider block">
                                        Riwayat Pembayaran Masuk ({{ $finalPayments->count() }}x Transaksi):
                                    </span>
                                    <div class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/80 dark:border-slate-800 overflow-hidden shadow-2xs">
                                        @foreach($finalPayments as $index => $fp)
                                            @php
                                                $paidItemsList = [];
                                                if ($fp->items && $fp->items->isNotEmpty()) {
                                                    foreach ($fp->items as $pItem) {
                                                        $paidItemsList[] = $pItem->fee_name . ' (Rp ' . number_format($pItem->amount, 0, ',', '.') . ')';
                                                    }
                                                } elseif (isset($fp->payment_info['selected_items']) && is_array($fp->payment_info['selected_items'])) {
                                                    foreach ($fp->payment_info['selected_items'] as $si) {
                                                        $paidItemsList[] = ($si['name'] ?? 'Komponen') . ' (Rp ' . number_format($si['amount'] ?? 0, 0, ',', '.') . ')';
                                                    }
                                                }
                                                $nominalPokok = (float)$fp->amount - (float)($fp->admin_fee ?? 0);
                                            @endphp
                                            <div class="p-3.5 sm:px-4 sm:py-3.5 flex flex-col sm:flex-row sm:items-start justify-between gap-3 text-xs">
                                                <div class="flex items-start gap-3 min-w-0">
                                                    <span class="h-7 w-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/80 text-brand-emerald dark:text-emerald-400 font-extrabold flex items-center justify-center text-[10px] shrink-0 mt-0.5 border border-emerald-200/60 dark:border-emerald-800/60">
                                                        #{{ $index + 1 }}
                                                    </span>
                                                    <div class="space-y-1 min-w-0">
                                                        <div class="flex items-center gap-2 flex-wrap">
                                                            <strong class="text-slate-900 dark:text-white font-extrabold text-sm">Rp {{ number_format($fp->amount, 0, ',', '.') }}</strong>
                                                            <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold uppercase border border-slate-200 dark:border-slate-700">
                                                                {{ $fp->payment_method ?? ($fp->payment_channel ?? 'VA') }}
                                                            </span>
                                                            <span class="text-[9px] px-1.5 py-0.5 rounded bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300 font-bold uppercase">
                                                                Berhasil
                                                            </span>
                                                        </div>

                                                        <!-- Breakdown details of this transaction -->
                                                        <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-[11px] text-slate-500 dark:text-slate-400">
                                                            <span>Pokok: <strong class="text-slate-700 dark:text-slate-300">Rp {{ number_format($nominalPokok, 0, ',', '.') }}</strong></span>
                                                            @if($fp->admin_fee > 0)
                                                                <span>•</span>
                                                                <span>Biaya Transaksi: <strong class="text-slate-700 dark:text-slate-300">Rp {{ number_format($fp->admin_fee, 0, ',', '.') }}</strong></span>
                                                            @endif
                                                            <span>•</span>
                                                            <span>{{ $fp->created_at ? $fp->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') : '-' }} WIB</span>
                                                        </div>

                                                        @if(!empty($paidItemsList))
                                                            <div class="pt-0.5 flex items-center gap-1.5 flex-wrap text-[11px]">
                                                                <span class="text-slate-400 font-medium">Alokasi Item:</span>
                                                                @foreach($paidItemsList as $piText)
                                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50/80 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 font-semibold text-[10px] border border-emerald-200/50 dark:border-emerald-900/50">
                                                                        <i data-lucide="check" class="w-2.5 h-2.5"></i> {{ $piText }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <a href="{{ route('dashboard.payment.receipt', $fp->id) }}" class="self-start sm:self-center px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-50 dark:hover:bg-slate-800 transition flex items-center gap-1.5 shrink-0 shadow-2xs">
                                                    <i data-lucide="download" class="w-3.5 h-3.5 text-brand-emerald"></i> Kwitansi #{{ $index + 1 }}
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Status Administrasi Banner -->
                            @if($status === 'completed' || $remainingTuition <= 0)
                                <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/80 dark:border-emerald-800/80 rounded-xl flex items-center justify-between gap-3 text-xs">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                                            <i data-lucide="check-check" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <span class="font-extrabold text-emerald-900 dark:text-emerald-200 block">Kewajiban Administrasi Telah Selesai (Lunas Sepenuhnya)</span>
                                            <span class="text-[11px] text-emerald-700 dark:text-emerald-400">Seluruh komponen pembiayaan masuk awal telah berhasil diselesaikan.</span>
                                        </div>
                                    </div>
                                </div>
                            @elseif($remainingTuition > 0)
                                <div class="p-3.5 bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/80 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                                            <i data-lucide="clock" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <span class="font-extrabold text-amber-900 dark:text-amber-200 block">Sisa Tagihan yang Belum Terbayar: Rp {{ number_format($remainingTuition, 0, ',', '.') }}</span>
                                            <span class="text-[11px] text-amber-700 dark:text-amber-400">Silakan selesaikan pembayaran angsuran/pelunasan sebelum batas waktu berakhir.</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('dashboard.result', $reg->id) }}" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-[11px] font-bold transition shrink-0 shadow-2xs self-start sm:self-auto flex items-center gap-1.5">
                                        <i data-lucide="credit-card" class="w-3.5 h-3.5"></i> Lanjut Bayar
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- STAGE 7: STATUS RESMI DITERIMA -->
                    <div class="relative flex items-start gap-3 sm:gap-5 group">
                        <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full {{ ($status === 'completed') ? 'bg-brand-emerald text-brand-yellow ring-4 ring-emerald-100 dark:ring-emerald-950' : 'bg-slate-200 dark:bg-slate-800 text-slate-500 ring-4 ring-white dark:ring-slate-900' }} flex items-center justify-center font-black text-xs sm:text-sm shadow-md shrink-0 z-10 select-none">
                            <i data-lucide="check" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                        </div>
                        <div class="flex-1 min-w-0 {{ $status === 'completed' ? 'bg-gradient-to-r from-emerald-50 via-emerald-100/40 to-emerald-50 dark:from-emerald-950/30 dark:via-emerald-900/10 dark:to-emerald-950/30 border-emerald-300/60 dark:border-emerald-800' : 'bg-slate-50/80 dark:bg-slate-950/60 border-slate-200/80 dark:border-slate-800' }} rounded-2xl p-4 sm:p-5 border space-y-2">
                            <div class="flex items-center gap-2">
                                <h5 class="font-black text-xs sm:text-sm {{ $status === 'completed' ? 'text-emerald-900 dark:text-emerald-200' : 'text-slate-900 dark:text-white' }}">
                                    Status Akhir: {{ $status === 'completed' ? 'Alhamdulillah, RESMI DITERIMA' : 'Dalam Proses Daftar Ulang' }}
                                </h5>
                            </div>
                            <p class="text-[11px] {{ $status === 'completed' ? 'text-emerald-800/80 dark:text-emerald-300/80' : 'text-slate-500 dark:text-slate-400' }} leading-relaxed">
                                @if($status === 'completed')
                                    Selamat! Ananda <strong>{{ $reg->candidate_name }}</strong> telah resmi menjadi bagian dari keluarga besar {{ $reg->unit?->name ?? 'Sekolah Anak Saleh' }}. Seluruh tahapan pendaftaran dan administrasi telah selesai dengan lengkap.
                                @else
                                    Selesaikan seluruh tahapan yang masih berjalan agar ananda resmi terdaftar dan diterima sebagai murid baru.
                                @endif
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- CARD FOOTER ACTIONS -->
            <div class="bg-slate-50/80 dark:bg-slate-950/80 p-4 sm:p-6 border-t border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                <span class="text-xs text-slate-500 dark:text-slate-400 text-center sm:text-left">
                    Butuh bantuan atau informasi perubahan data? Hubungi panitia SPMB unit.
                </span>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    @if($status === 'completed' || $status === 'agreement_signed')
                        <a href="{{ route('dashboard.result', $reg->id) }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-brand-emerald hover-emerald text-white font-bold text-xs shadow-sm transition flex items-center justify-center gap-1.5">
                            <i data-lucide="award" class="w-4 h-4 text-brand-yellow"></i> Buka Halaman Administrasi
                        </a>
                    @else
                        <a href="{{ route('dashboard.detail', $reg->id) }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-brand-emerald hover-emerald text-white font-bold text-xs shadow-sm transition flex items-center justify-center gap-1.5">
                            <i data-lucide="arrow-right" class="w-4 h-4"></i> Lanjut Tahapan
                        </a>
                    @endif
                </div>
            </div>

        </div>

    @endif

</div>
@endsection
