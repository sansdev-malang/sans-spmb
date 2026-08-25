@extends('layouts.admin')

@section('title', 'QR Code SPMB - Admin Panel')
@section('page_title', 'QR Code SPMB')

@section('content')
<div class="w-full space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800">QR Code Pendaftaran SPMB</h1>
            <p class="text-xs text-slate-500 mt-1">Buat, uji, dan unduh kode QR pendaftaran sekolah untuk mempermudah calon orang tua melakukan registrasi online.</p>
        </div>
    </div>

    <!-- Main QR Card -->
    <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden p-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- Left configuration form -->
            <div class="md:col-span-2 space-y-6">
                <div>
                    <h3 class="font-extrabold text-base text-slate-800">Konfigurasi Tautan QR Code</h3>
                    <p class="text-xs text-slate-400">Masukkan tautan formulir atau landing page registrasi Anda untuk mengubah isi kode QR secara instan.</p>
                </div>
                
                <form method="POST" action="{{ route('admin.spmb-settings.qrcode.save') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-650 uppercase tracking-wider mb-2">URL Tujuan Pendaftaran*</label>
                        <input type="url" name="qrcode_url" id="qrcodeUrlInput" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm"
                            value="{{ $qrcodeUrl }}">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                            Perbarui & Simpan QR Code
                        </button>
                    </div>
                </form>

                <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-xs text-amber-800 leading-relaxed space-y-2">
                    <span class="font-bold flex items-center gap-1"><i data-lucide="info" class="w-4 h-4"></i> Petunjuk Penggunaan:</span>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Gunakan URL lengkap dengan protokol `https://` atau `http://`.</li>
                        <li>Cetak kode QR hasil unduhan ke dalam brosur, pamflet, atau spanduk pendaftaran sekolah.</li>
                        <li>Calon orang tua murid cukup memindai (scan) kode QR tersebut menggunakan kamera ponsel untuk langsung menuju ke halaman formulir registrasi.</li>
                    </ul>
                </div>
            </div>

            <!-- Right QR visual and copy links -->
            <div class="border border-slate-100 rounded-2xl p-6 flex flex-col items-center justify-center text-center gap-4 bg-slate-50/50">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hasil Gambar QR</span>
                
                <div class="bg-white p-3 border border-slate-200 rounded-2xl shadow-sm">
                    <img id="qrCodeImage" src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($qrcodeUrl) }}" alt="SANS SPMB QR Code" class="h-36 w-36">
                </div>

                <!-- Display URL under QR Code to be copied -->
                <div class="w-full space-y-1.5">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Salin Tautan</label>
                    <div class="flex items-center border border-slate-300 rounded-xl overflow-hidden bg-white">
                        <input type="text" readonly id="displayUrlInput" value="{{ $qrcodeUrl }}" class="w-full border-none bg-transparent px-3 py-2 text-[11px] font-mono text-slate-650 focus:ring-0 focus:outline-none">
                        <button onclick="copyToClipboard()" type="button" class="bg-slate-100 hover:bg-slate-200 px-3 py-2 text-[10px] font-bold text-slate-700 transition border-l border-slate-300">
                            Salin
                        </button>
                    </div>
                </div>

                <a id="downloadQrButton" href="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($qrcodeUrl) }}" target="_blank" download="sans-spmb-qrcode.png"
                    class="w-full bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm text-center">
                    📥 Unduh Gambar QR
                </a>
            </div>

        </div>
    </div>
</div>

<script>
    // Real-time preview as user types
    document.getElementById('qrcodeUrlInput').addEventListener('input', function() {
        const url = this.value;
        const encoded = encodeURIComponent(url);

        document.getElementById('qrCodeImage').src = `https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${encoded}`;
        document.getElementById('displayUrlInput').value = url;
        document.getElementById('downloadQrButton').href = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encoded}`;
    });

    function copyToClipboard() {
        const input = document.getElementById('displayUrlInput');
        input.select();
        input.setSelectionRange(0, 99999); // for mobile
        navigator.clipboard.writeText(input.value);
        showToast('Tautan disalin ke papan klip!', 'success');
    }
</script>
@endsection
