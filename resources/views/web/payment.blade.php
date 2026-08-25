@extends('layouts.portal')

@section('title', 'Pembayaran Biaya Pendaftaran - Portal SPMB')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
    
    @php
        $formPaid = $registration->payments()->where('payment_type', 'registration_fee')->where('status', 'success')->exists();
        $successPayment = $registration->payments()->where('status', 'success')->latest()->first();
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
        <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
            <div class="bg-brand-emerald text-white px-6 py-5 flex justify-between items-center">
                <div>
                    @if($registration->registration_status === 'draft')
                        <h2 class="font-extrabold text-lg flex items-center gap-2">
                            <i data-lucide="file-text" class="w-5 h-5 text-brand-yellow"></i>
                            Biaya Formulir Pendaftaran
                        </h2>
                        <p class="text-xs text-brand-yellow font-medium mt-0.5">Selesaikan pembayaran biaya pendaftaran untuk membuka akses pengisian formulir pendaftaran.</p>
                    @else
                        <h2 class="font-extrabold text-lg flex items-center gap-2">
                            <i data-lucide="credit-card" class="w-5 h-5 text-brand-yellow"></i>
                            Biaya Seleksi & Administrasi
                        </h2>
                        <p class="text-xs text-brand-yellow font-medium mt-0.5">Selesaikan pembayaran biaya seleksi administrasi untuk menjadwalkan tes observasi.</p>
                    @endif
                </div>
                
                @if($registration->payment_status === 'paid')
                    <span class="bg-green-700 text-white font-bold text-[10px] uppercase tracking-widest px-3 py-1 rounded-full border border-green-500 shadow-sm">
                        Lunas
                    </span>
                @else
                    <span class="bg-red-700 text-white font-bold text-[10px] uppercase tracking-widest px-3 py-1 rounded-full border border-red-500 shadow-sm">
                        Belum Lunas
                    </span>
                @endif
            </div>

            <div class="p-6 space-y-6">
                <!-- Invoice details card -->
                <div class="bg-slate-50 dark:bg-slate-900 p-5 rounded-xl border border-slate-100 dark:border-slate-800 flex justify-between items-center">
                    <div>
                        <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Jenis Pembayaran</span>
                        <span class="text-sm font-extrabold text-slate-800 dark:text-white block mt-0.5 mb-2.5">{{ $feeName }}</span>
                        
                        <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Nominal Pembayaran</span>
                        <span id="displayInvoiceAmount" class="text-xl font-black text-brand-emerald dark:text-emerald-450 block">Rp {{ number_format($feeAmount, 0, ',', '.') }}</span>
                    </div>
                    <div class="text-right flex flex-col justify-between self-stretch">
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Pendaftar</span>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ $registration->candidate_name }}</span>
                        </div>
                        <div class="mt-2.5">
                            <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Unit Pendidikan</span>
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-350">{{ $registration->unit->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                @if(isset($feeDetails) && $feeDetails)
                    <!-- Rincian Komponen Biaya Akhir -->
                    <div class="bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl p-5 space-y-3 text-xs">
                        <h4 class="font-extrabold text-slate-850 dark:text-white uppercase tracking-wider text-[10px] border-b border-slate-200/50 dark:border-slate-800 pb-2 flex items-center gap-1.5">
                            <i data-lucide="list-checks" class="w-3.5 h-3.5 text-brand-emerald"></i> Rincian Pembayaran
                        </h4>
                        @if(isset($feeDetails['items']) && is_array($feeDetails['items']))
                            @foreach($feeDetails['items'] as $item)
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                    <span>{{ $item['name'] }}</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($item['amount'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        @else
                            <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                <span>Uang Gedung (Yayasan)</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($feeDetails['uang_gedung'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                <span>Biaya Seragam Sekolah</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($feeDetails['seragam'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                <span>SPP Bulanan (Mulai Juli)</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($feeDetails['spp'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                <span>Uang Kegiatan / Program</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($feeDetails['kegiatan'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        <!-- Dynamic Admin Fee row (shown if unpaid status to update via JS) -->
                        @if($registration->payment_status === 'unpaid')
                            <div id="adminFeeRow" class="flex justify-between items-center text-slate-650 dark:text-slate-400">
                                <span>Biaya Transaksi / Admin</span>
                                <span id="displayAdminFee" class="font-bold text-slate-800 dark:text-slate-200">Rp 0</span>
                            </div>
                        @elseif($registration->payment_status === 'pending' && $activePayment)
                            <div class="flex justify-between items-center text-slate-650 dark:text-slate-400">
                                <span>Biaya Transaksi / Admin</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($activePayment->admin_fee, 0, ',', '.') }}</span>
                            </div>
                        @elseif($registration->payment_status === 'paid' && isset($successPayment))
                            <div class="flex justify-between items-center text-slate-650 dark:text-slate-400">
                                <span>Biaya Transaksi / Admin</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($successPayment->admin_fee, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        <div class="border-t border-slate-200/50 dark:border-slate-800 pt-3 flex justify-between items-center text-xs font-black text-slate-800 dark:text-white uppercase">
                            <span>Total Pembayaran</span>
                            @if($registration->payment_status === 'pending' && $activePayment)
                                <span class="text-brand-emerald dark:text-emerald-400 text-sm font-extrabold">Rp {{ number_format($activePayment->amount, 0, ',', '.') }}</span>
                            @elseif($registration->payment_status === 'paid' && isset($successPayment))
                                <span class="text-brand-emerald dark:text-emerald-400 text-sm font-extrabold">Rp {{ number_format($successPayment->amount, 0, ',', '.') }}</span>
                            @else
                                <span id="displayGrandTotal" class="text-brand-emerald dark:text-emerald-400 text-sm font-extrabold">Rp {{ number_format($feeDetails['total'], 0, ',', '.') }}</span>
                            @endif
                        </div>

                        @if($registration->payment_status === 'unpaid' || $registration->payment_status === 'partially_paid')
                            <p class="text-[10px] text-slate-400 italic leading-relaxed mt-1 select-none">Note: Biaya transaksi dibebankan kepada wali murid sesuai instruksi yayasan.</p>
                        @endif
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

                <!-- 2. Form Select payment method if unpaid or partially paid -->
                @if ($registration->payment_status === 'unpaid' || $registration->payment_status === 'partially_paid')
                    <form action="{{ route('dashboard.charge', $registration->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="items" value="{{ request()->query('items') }}">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Pilih Metode Pembayaran</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            @forelse($channels as $channel)
                                <label class="border border-slate-200 rounded-xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-brand-emerald hover:bg-emerald-50/10 transition relative">
                                    <input type="radio" name="payment_method" value="{{ $channel->code }}" data-type="{{ $channel->type }}" data-gateway="{{ $channel->gateway->code ?? '' }}" class="absolute top-3 right-3 text-brand-emerald focus:ring-brand-emerald" {{ $loop->first ? 'checked' : '' }}>
                                    <span class="text-sm font-bold text-slate-800 text-center leading-tight">{{ $channel->name }}</span>
                                    <span class="text-[9px] text-slate-400 font-semibold uppercase tracking-wider">{{ $channel->type }}</span>
                                </label>
                            @empty
                                <div class="col-span-full py-6 text-center text-xs text-slate-400 font-bold">
                                    Tidak ada metode pembayaran aktif yang tersedia saat ini.
                                </div>
                            @endforelse
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
                @if ($registration->payment_status === 'pending' && $activePayment)
                    <div class="border border-slate-200 rounded-xl p-6 space-y-6">
                        <div class="text-center">
                            <span class="text-xs text-slate-400 font-bold block uppercase tracking-wider">Metode Pembayaran</span>
                            <span class="text-lg font-black text-slate-800">{{ $activePayment->payment_method }}</span>
                        </div>

                        @if (str_contains(strtoupper($activePayment->payment_method), 'QRIS'))
                            <!-- QRIS Display -->
                            <div class="flex flex-col items-center justify-center gap-3">
                                <div class="bg-white p-3 border border-slate-200 rounded-xl shadow-inner">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($activePayment->payment_info['qrContent'] ?? $activePayment->payment_info['qrisString'] ?? 'MOCK_QRIS_STRING') }}" alt="QRIS Code" class="h-44 w-44">
                                </div>
                                <p class="text-xs text-slate-400 font-medium">Scan QRIS menggunakan Mobile Banking atau e-Wallet pilihan Anda.</p>
                                <a href="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($activePayment->payment_info['qrContent'] ?? $activePayment->payment_info['qrisString'] ?? 'MOCK_QRIS_STRING') }}" download="QRIS-SPMB-SekolahAnakSaleh.png" target="_blank" class="bg-brand-emerald hover-emerald text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5 mt-2">
                                    <i data-lucide="download" class="w-4 h-4"></i> Unduh Kode QRIS (PNG)
                                </a>
                            </div>
                        @else
                            <!-- VA Number display -->
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 text-center">
                                <span class="text-xs text-slate-400 font-semibold uppercase block">Nomor Virtual Account (VA)</span>
                                <span class="text-2xl font-black text-brand-emerald tracking-wider font-mono block mt-1 select-all">
                                    {{ $activePayment->payment_info['virtualAccountNo'] ?? $activePayment->payment_info['virtualAccount'] ?? '88990012345678' }}
                                </span>
                                <span class="text-[10px] text-slate-400 mt-2 block font-semibold">BANK PARTNER: {{ $activePayment->payment_method }}</span>
                            </div>
                        @endif
                        
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
                                <span class="text-[10px] text-sky-800 font-extrabold uppercase tracking-widest bg-sky-100 px-2.5 py-1 rounded-full">Developer Simulator Utility</span>
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
    document.addEventListener("DOMContentLoaded", function() {
        const baseAmount = {{ $feeAmount }};
        const feeBniVa = {{ \App\Models\Setting::get('fee_bni_va', 1500) }};
        const feeBniQris = {{ \App\Models\Setting::get('fee_bni_qris', 0.7) }} / 100;
        const feeWinpayVa = {{ \App\Models\Setting::get('fee_winpay_va', 4500) }};

        const adminFeeEl = document.getElementById('displayAdminFee');
        const grandTotalEl = document.getElementById('displayGrandTotal');

        const gateway = "{{ $feeGateway }}";

        function updateSummary() {
            const selectedRadio = document.querySelector('input[name="payment_method"]:checked');
            if (!selectedRadio) return;

            const method = selectedRadio.value;
            const channelType = selectedRadio.getAttribute('data-type');
            const channelGateway = selectedRadio.getAttribute('data-gateway');
            let adminFee = 0;

            if (channelGateway === 'bni') {
                if (channelType === 'qris') {
                    adminFee = Math.round(baseAmount * feeBniQris);
                } else {
                    adminFee = feeBniVa;
                }
            } else {
                // Winpay gateway (always use Winpay VA setting flat fee for all its channels)
                adminFee = feeWinpayVa;
            }

            const grandTotal = baseAmount + adminFee;

            if (adminFeeEl) {
                adminFeeEl.innerText = 'Rp ' + adminFee.toLocaleString('id-ID');
            }
            if (grandTotalEl) {
                grandTotalEl.innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
            }
            const invoiceAmountEl = document.getElementById('displayInvoiceAmount');
            if (invoiceAmountEl) {
                invoiceAmountEl.innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
            }
        }

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
