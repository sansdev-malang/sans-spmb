@extends('layouts.portal')

@section('title', 'Pembayaran Biaya Pendaftaran - Portal SPMB')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
    
    <!-- Check if candidate is still in draft -->
    @if ($registration->registration_status === 'draft')
        <div class="bg-white rounded-2xl shadow-md border border-slate-100 p-8 text-center space-y-4">
            <span class="inline-flex items-center justify-center h-14 w-14 bg-red-50 text-red-600 rounded-full border border-red-100 shadow-sm">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </span>
            <h2 class="text-lg font-extrabold text-slate-800">Formulir Pendaftaran Belum Lengkap</h2>
            <p class="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                Anda harus mengisi dan melengkapi seluruh tahapan data pendaftaran serta dokumen persyaratan terlebih dahulu pada menu <strong>Formulir</strong> sebelum dapat melakukan pembayaran biaya seleksi.
            </p>
            <div class="pt-4">
                <a href="{{ route('dashboard.form') }}" class="bg-brand-emerald hover-emerald text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-md transition">
                    Lengkapi Formulir Sekarang
                </a>
            </div>
        </div>
    @else
        <!-- PAYMENT FLOW INTERFACE (After forms submitted) -->
        <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
            <div class="bg-brand-emerald text-white px-6 py-5 flex justify-between items-center">
                <div>
                    <h2 class="font-extrabold text-lg flex items-center gap-2">
                        <i data-lucide="credit-card" class="w-5 h-5 text-brand-yellow"></i>
                        Biaya Seleksi & Administrasi
                    </h2>
                    <p class="text-xs text-brand-yellow font-medium mt-0.5">Selesaikan pembayaran biaya seleksi administrasi untuk menjadwalkan tes observasi.</p>
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
                <div class="bg-slate-50 p-5 rounded-xl border border-slate-100 flex justify-between items-center">
                    <div>
                        <span class="text-xs text-slate-400 font-semibold block uppercase">Jumlah Tagihan</span>
                        <span class="text-2xl font-black text-slate-800">Rp 350.000</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-slate-400 font-semibold block uppercase">Pendaftar</span>
                        <span class="text-sm font-bold text-slate-700">{{ $registration->candidate_name }}</span>
                    </div>
                </div>

                <!-- 1. Success Message if already paid -->
                @if ($registration->payment_status === 'paid')
                    <div class="border border-green-200 bg-green-50/10 rounded-xl p-6 text-center space-y-3">
                        <span class="inline-flex items-center justify-center h-12 w-12 bg-green-100 text-green-700 rounded-full">
                            <i data-lucide="check" class="w-6 h-6"></i>
                        </span>
                        <h3 class="font-extrabold text-green-800 text-sm">Pembayaran Lunas Sukses</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto leading-relaxed">
                            Terima kasih! Pembayaran biaya seleksi administrasi Anda telah lunas dikonfirmasi oleh sistem. Silakan periksa status dokumen Anda di menu <strong>Verification</strong>.
                        </p>
                    </div>
                @endif

                <!-- 2. Form Select payment method if unpaid -->
                @if ($registration->payment_status === 'unpaid')
                    <form action="{{ route('dashboard.charge') }}" method="POST" class="space-y-4">
                        @csrf
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Pilih Metode Pembayaran</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            @foreach($channels as $channel)
                                <label class="border border-slate-200 rounded-xl p-4 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-brand-emerald hover:bg-emerald-50/10 transition relative">
                                    <input type="radio" name="payment_method" value="{{ $channel->code }}" class="absolute top-3 right-3 text-brand-emerald focus:ring-brand-emerald" {{ $loop->first ? 'checked' : '' }}>
                                    <span class="text-sm font-bold text-slate-800 text-center leading-tight">{{ $channel->name }}</span>
                                    <span class="text-[9px] text-slate-400 font-semibold uppercase tracking-wider">{{ $channel->type }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl font-bold text-xs shadow-md transition">
                                Inisiasi Pembayaran Sekarang
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

                        @if ($activePayment->payment_method === 'QRIS')
                            <!-- QRIS Display -->
                            <div class="flex flex-col items-center justify-center gap-3">
                                <div class="bg-white p-3 border border-slate-200 rounded-xl shadow-inner">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($activePayment->payment_info['qrContent'] ?? 'MOCK_QRIS_STRING') }}" alt="QRIS Code" class="h-44 w-44">
                                </div>
                                <p class="text-xs text-slate-400 font-medium">Scan QRIS menggunakan Mobile Banking atau e-Wallet pilihan Anda.</p>
                                <a href="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($activePayment->payment_info['qrContent'] ?? 'MOCK_QRIS_STRING') }}" download="QRIS-SPMB-SekolahAnakSaleh.png" target="_blank" class="bg-brand-emerald hover-emerald text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5 mt-2">
                                    <i data-lucide="download" class="w-4 h-4"></i> Unduh Kode QRIS (PNG)
                                </a>
                            </div>
                        @else
                            <!-- VA Number display -->
                            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 text-center">
                                <span class="text-xs text-slate-400 font-semibold uppercase block">Nomor Virtual Account (VA)</span>
                                <span class="text-2xl font-black text-brand-emerald tracking-wider font-mono block mt-1 select-all">
                                    {{ $activePayment->payment_info['virtualAccountNo'] ?? '88990012345678' }}
                                </span>
                                <span class="text-[10px] text-slate-400 mt-2 block font-semibold">BANK PARTNER: {{ $activePayment->payment_method }}</span>
                            </div>
                        @endif

                        <div class="bg-slate-50 p-4 rounded-xl text-xs text-slate-500 leading-relaxed space-y-1">
                            <p><strong>Instruksi Pembayaran:</strong></p>
                            <ol class="list-decimal pl-4 space-y-1">
                                <li>Salin nomor Virtual Account / Scan QRIS di atas.</li>
                                <li>Buka aplikasi Mobile Banking atau kunjungi ATM terdekat.</li>
                                <li>Pilih Transfer / Pembayaran Virtual Account, masukkan nomor VA, dan konfirmasi nominal tagihan.</li>
                                <li>Status di dashboard akan berubah otomatis setelah pembayaran sukses.</li>
                            </ol>
                        </div>

                        <!-- Ganti Metode Pembayaran / Batal Button -->
                        <div class="border-t border-slate-200 pt-4 flex justify-center">
                            <form action="{{ route('dashboard.cancel-payment', $activePayment->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi pembayaran ini dan mengganti metode pembayaran?')">
                                @csrf
                                <button type="submit" class="border border-red-200 hover:bg-red-50 text-red-600 px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                    <i data-lucide="x-circle" class="w-4 h-4"></i> Batalkan / Ganti Metode Pembayaran
                                </button>
                            </form>
                        </div>

                        <!-- DEVELOPER ONLY: Webhook simulation button -->
                        <div class="border-t border-dashed border-slate-200 pt-6 mt-4 bg-sky-50/50 p-4 rounded-xl border border-sky-100 flex flex-col items-center justify-center gap-2">
                            <span class="text-[10px] text-sky-800 font-extrabold uppercase tracking-widest bg-sky-100 px-2.5 py-1 rounded-full">Developer Sandbox Utility</span>
                            <p class="text-xs text-sky-700 text-center leading-relaxed max-w-md">
                                Simulasikan webhook callback sukses dari Winpay untuk memperbarui status transaksi ini langsung dari browser secara instan.
                            </p>
                            <form action="{{ route('dashboard.simulate-payment', $activePayment->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white px-5 py-2.5 rounded-lg text-xs font-bold shadow-sm transition">
                                    Simulasikan Pembayaran Sukses (Webhook Callback)
                                </button>
                            </form>
                        </div>

                    </div>
                @endif

            </div>
        </div>
    @endif
</div>
@endsection
