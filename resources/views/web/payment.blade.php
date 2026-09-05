@extends('layouts.portal')

@section('title', 'Pembayaran Biaya Pendaftaran - Portal SPMB')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
    
    @php
        $formPaid = $registration->payments()->where('payment_type', 'registration_fee')->where('status', 'success')->exists();
        $successPayment = $registration->payments()->where('status', 'success')->latest()->first();
        $latestPayment = $registration->payments()->latest()->first();
        $latestFailedPayment = ($latestPayment && $latestPayment->status === 'failed') ? $latestPayment : null;
        $isPaymentPending = ($registration->payment_status === 'pending' && $activePayment && $activePayment->status === 'pending');
    @endphp

    <!-- Check if candidate is still in draft and paid -->
    @if ($registration->registration_status === 'draft' && $formPaid)
        <div class="bg-white rounded-2xl shadow-md border border-slate-100 p-8 text-center space-y-4">
            <span class="inline-flex items-center justify-center h-14 w-14 bg-green-50 text-green-600 rounded-full border border-green-100 shadow-sm">
                <i data-lucide="check" class="w-6 h-6"></i>
            </span>
            <h2 class="text-lg font-extrabold text-slate-800">Pembayaran Formulir Lunas</h2>
            <p class="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                Pembayaran biaya formulir pendaftaran Anda telah berhasil terkonfirmasi. Silakan isi dan lengkapi data calon siswa Anda pada menu <strong>Formulir</strong>.
            </p>
            <div class="pt-4 flex flex-col sm:flex-row justify-center items-center gap-3">
                <a href="{{ route('dashboard.form', $registration->id) }}" class="w-full sm:w-auto bg-brand-emerald hover-emerald text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-md transition text-center">
                    Lengkapi Formulir Sekarang
                </a>
                @if($successPayment)
                    <a href="{{ route('dashboard.payment.receipt', $successPayment->id) }}" class="download-pdf-btn w-full sm:w-auto border border-slate-200 hover:bg-slate-50 text-slate-650 px-5 py-2.5 rounded-xl text-xs font-bold shadow-sm transition text-center flex items-center justify-center gap-1.5">
                        <i data-lucide="download" class="w-4 h-4 icon-download"></i> <span class="btn-text">Unduh Bukti Pembayaran</span>
                    </a>
                @endif
            </div>
        </div>
    @else
        <!-- PAYMENT FLOW INTERFACE -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-md border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div class="bg-brand-emerald text-white p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3.5">
                <div class="flex-1 min-w-0">
                    @if($registration->registration_status === 'draft')
                        <h2 class="font-extrabold text-base sm:text-lg flex items-center gap-2">
                            <i data-lucide="file-text" class="w-5 h-5 text-brand-yellow shrink-0"></i>
                            <span>Biaya Formulir Pendaftaran</span>
                        </h2>
                        <p class="text-xs text-brand-yellow/90 font-medium mt-1 leading-relaxed">Selesaikan pembayaran biaya pendaftaran untuk membuka akses pengisian formulir pendaftaran.</p>
                    @else
                        <h2 class="font-extrabold text-base sm:text-lg flex items-center gap-2">
                            <i data-lucide="credit-card" class="w-5 h-5 text-brand-yellow shrink-0"></i>
                            <span>Biaya Seleksi & Administrasi</span>
                        </h2>
                        <p class="text-xs text-brand-yellow/90 font-medium mt-1 leading-relaxed">Selesaikan pembayaran biaya seleksi administrasi untuk menjadwalkan tes observasi.</p>
                    @endif
                </div>
                
                <div class="shrink-0 self-start sm:self-center">
                    @if($registration->payment_status === 'paid')
                        <span class="inline-flex items-center gap-1 bg-green-700 text-white font-black text-xs uppercase tracking-wider px-3 py-1 rounded-full border border-green-500 shadow-xs whitespace-nowrap">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Lunas
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 bg-red-700 text-white font-black text-xs uppercase tracking-wider px-3 py-1 rounded-full border border-red-500 shadow-xs whitespace-nowrap">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i> Belum Lunas
                        </span>
                    @endif
                </div>
            </div>

            <div class="p-5 sm:p-6 space-y-6">
                <!-- Invoice details card -->
                <div class="bg-slate-50 dark:bg-slate-950/60 p-5 sm:p-6 rounded-2xl border border-slate-200/70 dark:border-slate-800 space-y-4">
                    <!-- Top Info: Jenis Pembayaran & Pendaftar -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3.5 border-b border-slate-200/60 dark:border-slate-800">
                        <div>
                            <span class="text-xs text-slate-400 font-bold block uppercase tracking-wider mb-0.5">Jenis Pembayaran</span>
                            <div class="text-sm font-extrabold text-slate-850 dark:text-white flex items-center gap-1.5">
                                <i data-lucide="receipt" class="w-4 h-4 text-brand-emerald"></i>
                                {{ $feeName }}
                            </div>
                        </div>
                        <div class="sm:text-right">
                            <span class="text-xs text-slate-400 font-bold block uppercase tracking-wider mb-0.5">Pendaftar & Unit</span>
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-200">
                                <span>{{ $registration->candidate_name }}</span>
                                <span class="text-slate-300 dark:text-slate-700 font-normal mx-1.5">•</span>
                                <span class="text-brand-emerald dark:text-emerald-450 font-bold">{{ $registration->unit->name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Info: Nominal Tagihan -->
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-extrabold text-slate-500 uppercase tracking-wider">Total Tagihan</span>
                        <span id="displayInvoiceAmount" class="text-xl sm:text-2xl font-black text-brand-emerald dark:text-emerald-450 font-mono">
                            Rp {{ number_format($feeAmount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                @if(isset($discountAmount) && ($discountAmount > 0 || ($installmentMode ?? 'none') !== 'none'))
                    <!-- Keringanan & Cicilan Banner -->
                    <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900 rounded-2xl flex items-start gap-3">
                        <div class="h-8 w-8 rounded-xl bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-extrabold text-xs text-emerald-900 dark:text-emerald-300">
                                @if($discountAmount > 0 && ($installmentMode ?? 'none') !== 'none')
                                    Persetujuan Keringanan & Kebijakan Cicilan
                                @elseif($discountAmount > 0)
                                    Persetujuan Keringanan Biaya (Diskon)
                                @else
                                    Kebijakan Cicilan Pembayaran
                                @endif
                            </h4>
                            <p class="text-xs text-emerald-750 dark:text-emerald-400 leading-relaxed">
                                @if($discountAmount > 0 && ($installmentMode ?? 'none') !== 'none')
                                    Alhamdulillah! Anda disetujui memperoleh <strong>Keringanan Potongan Biaya sebesar Rp {{ number_format($discountAmount, 0, ',', '.') }}</strong> ({{ $discountNotes ?: 'Keringanan Yayasan' }}) dan diizinkan melakukan <strong>pembayaran bertahap (cicilan)</strong>.
                                @elseif($discountAmount > 0)
                                    Alhamdulillah! Anda disetujui memperoleh <strong>Keringanan Potongan Biaya sebesar Rp {{ number_format($discountAmount, 0, ',', '.') }}</strong> ({{ $discountNotes ?: 'Keringanan Yayasan' }}).
                                @elseif(($installmentMode ?? 'none') !== 'none')
                                    Alhamdulillah! Anda disetujui untuk melakukan <strong>pembayaran bertahap (cicilan)</strong> untuk biaya administrasi akhir ini.
                                @endif
                            </p>
                        </div>
                    </div>
                @endif


                <!-- 1. Success Message if already paid -->
                @if ($registration->payment_status === 'paid')
                    <div class="border border-green-200 bg-green-50/10 rounded-xl p-6 text-center space-y-3">
                        <span class="inline-flex items-center justify-center h-12 w-12 bg-green-100 text-green-700 rounded-full">
                            <i data-lucide="check" class="w-6 h-6"></i>
                        </span>
                        @if($registration->registration_status === 'completed')
                            <h3 class="font-extrabold text-green-800 text-sm">Pembayaran Administrasi Akhir Lunas</h3>
                            <p class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
                                Alhamdulillah! Pembayaran biaya administrasi akhir Anda telah lunas terkonfirmasi oleh sistem. Selamat bergabung di Sekolah Anak Saleh! Silakan cek menu <strong>Final Result</strong> untuk mengunduh Surat Keterangan Penerimaan.
                            </p>
                        @else
                            <h3 class="font-extrabold text-green-800 text-sm">Pembayaran Formulir Lunas</h3>
                            <p class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
                                Terima kasih! Pembayaran biaya pendaftaran formulir Anda telah lunas terkonfirmasi oleh sistem. Silakan isi dan lengkapi formulir pendaftaran Anda di menu <strong>Formulir</strong>.
                            </p>
                        @endif
                        
                        @if($successPayment)
                            <div class="pt-2">
                                <a href="{{ route('dashboard.payment.receipt', $successPayment->id) }}" class="download-pdf-btn inline-flex items-center gap-1.5 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                                    <i data-lucide="download" class="w-4 h-4 text-brand-emerald icon-download"></i> <span class="btn-text">Unduh Bukti Pembayaran</span>
                                </a>
                            </div>
                        @endif
                    </div>
                @endif

                @if($latestFailedPayment && !$isPaymentPending && $registration->payment_status !== 'paid')
                    <!-- Notifikasi Pembuatan Tagihan Belum Berhasil -->
                    <div class="p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900 rounded-2xl flex items-start gap-3">
                        <div class="h-8 w-8 rounded-xl bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i>
                        </div>
                        <div class="space-y-1">
                            <h4 class="font-extrabold text-xs text-amber-900 dark:text-amber-300">
                                Pembuatan Tagihan Belum Berhasil
                            </h4>
                            <p class="text-xs text-amber-750 dark:text-amber-400 leading-relaxed">
                                {{ $latestFailedPayment->payment_info['failure_reason'] ?? 'Pembuatan tagihan untuk metode pembayaran yang dipilih belum dapat diselesaikan oleh sistem perbankan. Silakan pilih kanal pembayaran lain (seperti QRIS atau E-Wallet) di bawah ini lalu klik Bayar Sekarang.' }}
                            </p>
                        </div>
                    </div>
                @endif

                <!-- 2. Form Select payment method if unpaid or partially paid (or failed pending) -->
                @if (!$isPaymentPending && $registration->payment_status !== 'paid')
                    <form action="{{ route('dashboard.charge', $registration->id) }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="items" value="{{ request()->query('items') }}">

                        @if(isset($feeDetails) && $feeDetails)
                            <!-- Rincian Komponen Biaya Akhir (Read-Only Review) -->
                            <div class="bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-5 space-y-4 text-xs">
                                <div class="flex items-center justify-between border-b border-slate-200/50 dark:border-slate-800 pb-3">
                                    <h4 class="font-extrabold text-slate-850 dark:text-white uppercase tracking-wider text-xs flex items-center gap-1.5">
                                        <i data-lucide="list-checks" class="w-4 h-4 text-brand-emerald"></i> Rincian Komponen Biaya Transaksi
                                    </h4>
                                    @if(($installmentMode ?? 'none') === 'selective')
                                        <span class="text-[9px] font-bold text-slate-400">Mode: Cicilan Komponen Tertentu</span>
                                    @elseif(($installmentMode ?? 'none') === 'all')
                                        <span class="text-[9px] font-bold text-slate-400">Mode: Cicilan Global</span>
                                    @endif
                                </div>

                                <div class="space-y-2.5">
                                    @if(isset($feeDetails['items']) && is_array($feeDetails['items']) && count($feeDetails['items']) > 0)
                                        @foreach($feeDetails['items'] as $item)
                                            @php
                                                $itemGross = (float) ($item['amount'] ?? 0);
                                                $itemDiscount = $registration->getItemDiscountAmount($item['name'], $item['id'] ?? null);
                                                $itemNet = max(0, $itemGross - $itemDiscount);
                                                $itemRemaining = (float) ($item['remaining_amount'] ?? $itemNet);
                                                $itemPayAmount = (float) ($item['amount_to_pay'] ?? $itemRemaining);
                                                $isPartial = ($itemPayAmount < $itemRemaining);
                                                $remainingAfter = max(0, $itemRemaining - $itemPayAmount);
                                            @endphp
                                            <input type="hidden" name="item_amounts[{{ $item['name'] }}]" value="{{ $itemPayAmount }}">
                                            
                                            <div class="border border-slate-200/80 dark:border-slate-800 rounded-xl p-3.5 bg-white dark:bg-slate-950 space-y-1 shadow-sm transition">
                                                <div class="flex items-center justify-between gap-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-extrabold text-xs text-slate-850 dark:text-white">{{ $item['name'] }}</span>
                                                        @if($itemDiscount > 0)
                                                            <span class="px-1.5 py-0.5 rounded bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 font-bold text-[9px] border border-rose-200/60 dark:border-rose-900">
                                                                🏷️ Diskon Rp {{ number_format($itemDiscount, 0, ',', '.') }}
                                                            </span>
                                                        @endif
                                                        @if(($installmentMode ?? 'none') === 'selective' && !empty($item['is_installment_allowed']))
                                                            <span class="px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 font-bold text-[9px] border border-blue-200/60 dark:border-blue-900">
                                                                🔓 Boleh Dicicil
                                                            </span>
                                                        @elseif(($installmentMode ?? 'none') === 'all')
                                                            <span class="px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 font-bold text-[9px] border border-emerald-200/60 dark:border-emerald-900">
                                                                ✓ Bisa Dicicil
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <span class="font-extrabold text-xs text-slate-850 dark:text-slate-200 font-mono">
                                                        Rp {{ number_format($itemPayAmount, 0, ',', '.') }}
                                                    </span>
                                                </div>

                                                <div class="flex items-center justify-between text-xs text-slate-400">
                                                    @if($isPartial)
                                                        <span class="text-blue-600 dark:text-blue-400 font-medium">
                                                            Cicilan Tahap Ini (Sisa setelah bayar: Rp {{ number_format($remainingAfter, 0, ',', '.') }})
                                                        </span>
                                                    @else
                                                        <span class="text-emerald-600 dark:text-emerald-400 font-medium">
                                                            Pelunasan Komponen
                                                        </span>
                                                    @endif
                                                    <span>Total Komponen: Rp {{ number_format($itemNet, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                                @php
                                    $isInstallmentActive = (($installmentMode ?? 'none') !== 'none');
                                @endphp

                                @if(isset($discountAmount) && $discountAmount > 0)
                                    <div class="flex justify-between items-center text-rose-600 dark:text-rose-400 border-t border-slate-200/40 dark:border-slate-800 pt-2">
                                        <span>Potongan Keringanan (Diskon)</span>
                                        <span class="font-bold">- Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
                                    </div>
                                @endif

                                @if($isInstallmentActive && isset($totalPaid) && $totalPaid > 0)
                                    <div class="flex justify-between items-center text-emerald-600 dark:text-emerald-400 border-t border-slate-200/40 dark:border-slate-800 pt-1.5">
                                        <span>Telah Dibayar Sebelumnya</span>
                                        <span class="font-bold">Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-slate-800 dark:text-white font-extrabold">
                                        <span>Sisa Tanggungan</span>
                                        <span class="font-mono text-emerald-600 dark:text-emerald-400">Rp {{ number_format($remainingBalance, 0, ',', '.') }}</span>
                                    </div>
                                @endif

                                <!-- Dynamic Admin Fee row -->
                                <div id="adminFeeRow" class="flex justify-between items-center text-slate-650 dark:text-slate-400 border-t border-slate-200/40 dark:border-slate-800 pt-2">
                                    <span>Biaya Transaksi (Admin)</span>
                                    <span id="displayAdminFee" class="font-bold text-slate-800 dark:text-slate-200">Rp 0</span>
                                </div>

                                <div class="border-t border-slate-200/50 dark:border-slate-800 pt-3 flex justify-between items-center text-xs font-black text-slate-800 dark:text-white uppercase">
                                    <span>Total Pembayaran Transaksi Ini</span>
                                    <span id="displayGrandTotal" class="text-brand-emerald dark:text-emerald-400 text-sm font-extrabold">Rp {{ number_format($feeAmount, 0, ',', '.') }}</span>
                                </div>

                                <p class="text-xs text-slate-400 italic leading-relaxed mt-1 select-none">Note: Biaya transaksi dibebankan kepada wali murid sesuai instruksi yayasan.</p>
                            </div>
                        @else
                            <!-- Formulir Pendaftaran (Draft) -->
                            <div class="bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl p-5 space-y-3 text-xs">
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                    <span>Biaya Formulir Pendaftaran</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($feeAmount, 0, ',', '.') }}</span>
                                </div>
                                <input type="hidden" class="item-amount-input" value="{{ $feeAmount }}" data-max="{{ $feeAmount }}" data-min="{{ $feeAmount }}">
                                <div class="flex justify-between items-center text-slate-650 dark:text-slate-400 border-t border-slate-200/40 dark:border-slate-800 pt-2">
                                    <span>Biaya Transaksi (Admin)</span>
                                    <span id="displayAdminFee" class="font-bold text-slate-800 dark:text-slate-200">Rp 0</span>
                                </div>
                                <div class="border-t border-slate-200/50 dark:border-slate-800 pt-3 flex justify-between items-center text-xs font-black text-slate-800 dark:text-white uppercase">
                                    <span>Total Pembayaran</span>
                                    <span id="displayGrandTotal" class="text-brand-emerald dark:text-emerald-400 text-sm font-extrabold">Rp {{ number_format($feeAmount, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="space-y-2.5">
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Pilih Metode Pembayaran</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 sm:gap-2.5">
                                 @forelse($channels as $channel)
                                    <label class="border border-slate-200 dark:border-slate-800 hover:border-brand-emerald dark:hover:border-emerald-600 bg-white dark:bg-slate-900 has-[:checked]:border-brand-emerald has-[:checked]:ring-1 has-[:checked]:ring-brand-emerald/30 has-[:checked]:bg-emerald-50/15 dark:has-[:checked]:bg-emerald-950/20 rounded-xl px-2.5 py-1.5 h-11 sm:h-12 flex items-center gap-2 cursor-pointer transition shadow-xs hover:shadow-sm relative select-none">
                                        <input type="radio" name="payment_method" value="{{ $channel->code }}" data-type="{{ $channel->type }}" data-gateway="{{ $channel->gateway->code ?? '' }}" data-fee-type="{{ $channel->fee_type ?? 'flat' }}" data-fee-value="{{ $channel->fee_value ?? 4500 }}" class="text-brand-emerald focus:ring-brand-emerald h-3.5 w-3.5 shrink-0" {{ $loop->first ? 'checked' : '' }}>
                                        
                                        <!-- Logo Container -->
                                        <div class="flex-1 h-full flex items-center justify-center overflow-hidden">
                                            @if($channel->getLogoUrl())
                                                <img src="{{ $channel->getLogoUrl() }}" alt="{{ $channel->name }}" title="{{ $channel->name }}" class="max-h-6 sm:max-h-7 max-w-full object-contain">
                                            @else
                                                <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200 text-center truncate leading-tight">
                                                    {{ $channel->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </label>
                                @empty
                                    <div class="col-span-full py-6 text-center text-xs text-slate-400 font-bold">
                                        Tidak ada metode pembayaran aktif yang tersedia saat ini.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end items-center gap-3">
                            @if($registration->registration_status === 'draft')
                                <a href="{{ route('dashboard') }}" class="border border-slate-250 hover:bg-slate-50 text-slate-600 px-6 py-3 rounded-xl font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                                    Batal / Kembali
                                </a>
                            @else
                                <a href="{{ route('dashboard.result', $registration->id) }}" class="border border-slate-250 hover:bg-slate-50 text-slate-600 px-6 py-3 rounded-xl font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                                    Batal / Kembali
                                </a>
                            @endif
                            <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl font-bold text-xs shadow-md transition flex items-center gap-1.5">
                                <i data-lucide="credit-card" class="w-4 h-4"></i>
                                Bayar Sekarang
                            </button>
                        </div>
                    </form>
                @endif

                <!-- 3. Display VA / QRIS Details when status is pending -->
                @if ($isPaymentPending)
                    <div class="border border-slate-200 rounded-xl p-6 space-y-6">
                        <div class="text-center">
                            <span class="text-xs text-slate-400 font-bold block uppercase tracking-wider">Metode Pembayaran</span>
                            <span class="text-lg font-black text-slate-800">{{ $activePayment->payment_method }}</span>
                        </div>

                        @if (str_contains(strtoupper($activePayment->payment_method), 'QRIS'))
                            <!-- QRIS Display -->
                            <div class="flex flex-col items-center justify-center gap-3">
                                <div class="bg-white p-3 border border-slate-200 rounded-xl shadow-inner flex items-center justify-center">
                                    @if(!empty($activePayment->payment_info['qrUrl']))
                                        <img src="{{ $activePayment->payment_info['qrUrl'] }}" alt="QRIS Code" class="h-44 w-44 object-contain">
                                    @else
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($activePayment->payment_info['qrContent'] ?? $activePayment->payment_info['qrisString'] ?? 'MOCK_QRIS_STRING') }}" alt="QRIS Code" class="h-44 w-44">
                                    @endif
                                </div>
                                <p class="text-xs text-slate-400 font-medium">Scan QRIS menggunakan Mobile Banking atau e-Wallet pilihan Anda.</p>
                                @if(!empty($activePayment->payment_info['qrUrl']))
                                    <a href="{{ $activePayment->payment_info['qrUrl'] }}" download="QRIS-SPMB-SekolahAnakSaleh.png" target="_blank" class="bg-brand-emerald hover:bg-emerald-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5 mt-2">
                                        <i data-lucide="download" class="w-4 h-4"></i> Unduh/Lihat QRIS (PNG)
                                    </a>
                                @else
                                    <a href="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($activePayment->payment_info['qrContent'] ?? $activePayment->payment_info['qrisString'] ?? 'MOCK_QRIS_STRING') }}" download="QRIS-SPMB-SekolahAnakSaleh.png" target="_blank" class="bg-brand-emerald hover:bg-emerald-600 text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5 mt-2">
                                        <i data-lucide="download" class="w-4 h-4"></i> Unduh Kode QRIS (PNG)
                                    </a>
                                @endif
                            </div>
                        @elseif (!empty($activePayment->payment_info['webRedirectUrl']) || !empty($activePayment->payment_info['paymentUrl']))
                            <!-- E-Wallet Display -->
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-6 text-center space-y-4">
                                <span class="text-xs text-slate-400 font-semibold uppercase block">Pembayaran Dompet Digital (E-Wallet)</span>
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <p class="text-xs text-slate-600 font-medium max-w-sm">Klik tombol di bawah ini untuk melanjutkan pembayaran melalui aplikasi atau web {{ $activePayment->payment_method }}:</p>
                                    <a href="{{ $activePayment->payment_info['webRedirectUrl'] ?? $activePayment->payment_info['paymentUrl'] }}" target="_blank" class="bg-brand-emerald hover:bg-emerald-600 text-white px-6 py-3 rounded-xl text-xs font-bold shadow-md transition flex items-center gap-2">
                                        <i data-lucide="external-link" class="w-4 h-4"></i> Buka Pembayaran {{ $activePayment->payment_method }}
                                    </a>
                                </div>
                            </div>
                        @else
                            <!-- VA Number display -->
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 text-center">
                                <span class="text-xs text-slate-400 font-semibold uppercase block">Nomor Virtual Account (VA)</span>
                                <span class="text-2xl font-black text-brand-emerald tracking-wider font-mono block mt-1 select-all">
                                    {{ $activePayment->payment_info['virtualAccountNo'] ?? $activePayment->payment_info['virtualAccount'] ?? '88990012345678' }}
                                </span>
                                <span class="text-xs text-slate-400 mt-2 block font-semibold">BANK PARTNER: {{ $activePayment->payment_method }}</span>
                            </div>
                        @endif
                        
                        <!-- Invoice Details -->
                        <div class="bg-slate-50 dark:bg-slate-900 border border-slate-150 dark:border-slate-800 rounded-xl p-3.5 flex justify-between items-center text-xs">
                            <span class="font-medium text-slate-500 dark:text-slate-400">No. Invoice:</span>
                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200 select-all">{{ $activePayment->invoice_number }}</span>
                        </div>

                        <div class="bg-slate-50 p-4 rounded-xl text-xs text-slate-500 leading-relaxed space-y-1">
                            <p><strong>Instruksi Pembayaran:</strong></p>
                            <ol class="list-decimal pl-4 space-y-1">
                                <li>Salin nomor Virtual Account / Scan QRIS di atas.</li>
                                <li>Buka aplikasi Mobile Banking atau kunjungi ATM terdekat.</li>
                                <li>Pilih Transfer / Pembayaran Virtual Account, masukkan nomor VA, dan konfirmasi nominal pembayaran.</li>
                                <li>Status di dashboard akan berubah otomatis setelah pembayaran sukses.</li>
                            </ol>
                        </div>

                        <!-- Ganti Metode Pembayaran / Batal Button -->
                        <div class="border-t border-slate-200 pt-4 flex justify-center">
                            <button type="button" onclick="openCancelModal()" class="border border-red-200 hover:bg-red-50 text-red-600 px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                <i data-lucide="x-circle" class="w-4 h-4"></i> Batalkan / Ganti Metode Pembayaran
                            </button>
                        </div>

                        <!-- Cancel Payment Confirmation Modal -->
                        <div id="cancelPaymentModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" onclick="closeCancelModal()"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                <div class="inline-block align-middle bg-white dark:bg-slate-900 rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-100 dark:border-slate-800">
                                    <div class="bg-white dark:bg-slate-900 px-6 pt-6 pb-4 sm:p-8 sm:pb-4">
                                        <div class="sm:flex sm:items-start gap-4 text-left">
                                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-2xl bg-red-50 dark:bg-red-950/20 text-red-600 sm:mx-0">
                                                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                                            </div>
                                            <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                                <h3 class="text-base font-extrabold text-slate-800 dark:text-white" id="modal-title">
                                                    Batalkan Pembayaran
                                                </h3>
                                                <div class="mt-2">
                                                    <p class="text-xs text-slate-500 leading-relaxed">
                                                        Apakah Anda yakin ingin membatalkan transaksi pembayaran aktif ini? Nomor Virtual Account atau kode QRIS yang sudah dibuat tidak akan dapat digunakan lagi, dan Anda harus memilih metode pembayaran baru.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-slate-50 dark:bg-slate-900 px-6 py-4 sm:px-8 sm:py-5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                                        <button type="button" onclick="closeCancelModal()" class="w-full sm:w-auto border border-slate-200 dark:border-slate-700 text-slate-650 dark:text-slate-400 px-5 py-2.5 rounded-xl text-xs font-bold transition hover:bg-slate-100 dark:hover:bg-slate-800">
                                            Tidak, Kembali
                                        </button>
                                        <form id="confirmCancelForm" action="{{ route('dashboard.cancel-payment', $activePayment->id) }}" method="POST" class="w-full sm:w-auto">
                                            @csrf
                                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                                                Ya, Batalkan Pembayaran
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- DEVELOPER ONLY: Webhook simulation button -->
                        @php
                            $gatewayMode = \App\Models\Setting::get($feeGateway . '_mode', 'simulator');
                        @endphp
                        @if($gatewayMode === 'simulator')
                            <div class="border-t border-dashed border-slate-200 pt-6 mt-4 bg-sky-50/50 p-4 rounded-xl border border-sky-100 flex flex-col items-center justify-center gap-2">
                                <span class="text-xs text-sky-800 font-extrabold uppercase tracking-widest bg-sky-100 px-2.5 py-1 rounded-full">Developer Simulator Utility</span>
                                <p class="text-xs text-sky-700 text-center leading-relaxed max-w-md">
                                    @if(str_contains($feeGateway, 'bni'))
                                        Simulasikan callback pembayaran sukses BNI Simulator untuk memperbarui status transaksi ini langsung dari browser secara instan.
                                    @else
                                        Simulasikan callback pembayaran sukses Winpay Simulator untuk memperbarui status transaksi ini langsung dari browser secara instan.
                                    @endif
                                </p>
                                <form action="{{ route('dashboard.simulate-payment', $activePayment->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2.5 rounded-lg text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                                        <i data-lucide="play" class="w-4 h-4"></i>
                                        Simulasikan Pembayaran Sukses ({{ str_contains($feeGateway, 'bni') ? 'BNI Simulator' : 'Winpay Simulator' }})
                                    </button>
                                </form>
                            </div>
                        @endif

                    </div>
                @endif

            </div>
        </div>
    @endif
</div>

<script>
    const currentBaseAmount = {{ (float) $feeAmount }};

    function updateSummary() {
        const adminFeeEl = document.getElementById('displayAdminFee');
        const grandTotalEl = document.getElementById('displayGrandTotal');
        const invoiceAmountEl = document.getElementById('displayInvoiceAmount');

        const selectedRadio = document.querySelector('input[name="payment_method"]:checked');
        if (!selectedRadio) return;

        const feeType = selectedRadio.getAttribute('data-fee-type') || 'flat';
        const feeVal = parseFloat(selectedRadio.getAttribute('data-fee-value')) || 0;

        let adminFee = 0;
        if (feeType === 'percent') {
            adminFee = Math.round(currentBaseAmount * (feeVal / 100));
        } else {
            adminFee = Math.round(feeVal);
        }

        const grandTotal = currentBaseAmount + adminFee;

        if (adminFeeEl) {
            adminFeeEl.innerText = 'Rp ' + adminFee.toLocaleString('id-ID');
        }
        if (grandTotalEl) {
            grandTotalEl.innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
        }
        if (invoiceAmountEl) {
            invoiceAmountEl.innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Attach event listeners to radio buttons
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', updateSummary);
        });

        // Initialize display
        updateSummary();

        // Handle PDF download button loader animation
        document.querySelectorAll('.download-pdf-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                const btnText = this.querySelector('.btn-text');
                const icon = this.querySelector('.icon-download');
                
                // Save original HTML state
                const originalText = btnText ? btnText.innerText : 'Unduh Bukti Pembayaran';
                
                // Disable and show loading animation
                this.classList.add('opacity-75', 'cursor-wait');
                this.style.pointerEvents = 'none';
                if (btnText) btnText.innerText = 'Mengunduh PDF...';
                if (icon) {
                    icon.outerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-brand-emerald inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
                }
                
                // Trigger file download
                window.location.href = url;
                
                // Restore button state after 3.5 seconds
                setTimeout(() => {
                    this.classList.remove('opacity-75', 'cursor-wait');
                    this.style.pointerEvents = '';
                    if (btnText) btnText.innerText = originalText;
                    
                    const tempSvg = this.querySelector('svg.animate-spin');
                    if (tempSvg) {
                        const newIcon = document.createElement('i');
                        newIcon.setAttribute('data-lucide', 'download');
                        newIcon.className = 'w-4 h-4 icon-download' + (this.classList.contains('inline-flex') ? ' text-brand-emerald' : '');
                        tempSvg.parentNode.replaceChild(newIcon, tempSvg);
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    }
                }, 3500);
            });
        });
    });

    function openCancelModal() {
        const modal = document.getElementById('cancelPaymentModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeCancelModal() {
        const modal = document.getElementById('cancelPaymentModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }
</script>
@endsection
