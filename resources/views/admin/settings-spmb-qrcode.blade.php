@extends('layouts.admin')

@section('title', 'QR Code SPMB - Admin Panel')
@section('page_title', 'QR Code SPMB')

@section('content')
<div id="qrcode-settings-container" hx-boost="true" hx-target="#qrcode-settings-container" hx-select="#qrcode-settings-container" class="w-full space-y-6">
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
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-extrabold text-base text-slate-800">Tautan QR Code Pendaftaran</h3>
                        <p class="text-xs text-slate-400">
                            @if($isSuperAdmin)
                                Masukkan tautan formulir atau landing page registrasi Anda untuk mengubah isi kode QR secara instan.
                            @else
                                Tautan QR Code pendaftaran resmi portal SPMB.
                            @endif
                        </p>
                    </div>
                </div>
                
                @if($errors->any())
                    <div class="text-xs text-red-650 bg-red-50 p-3.5 rounded-xl border border-red-200 font-semibold space-y-1">
                        @foreach($errors->all() as $error)
                            <p>⚠️ {{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                
                <form method="POST" action="{{ route('admin.spmb-settings.qrcode.save') }}" hx-boost="false" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-650 uppercase tracking-wider mb-2">URL Tujuan Pendaftaran*</label>
                        <input type="url" name="qrcode_url" id="qrcodeUrlInput" required 
                            {{ !$isSuperAdmin ? 'disabled' : '' }} 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm {{ !$isSuperAdmin ? 'cursor-not-allowed opacity-75' : '' }}"
                            value="{{ old('qrcode_url', $qrcodeUrl) }}">
                    </div>
                    @if($isSuperAdmin)
                        <div class="flex justify-end">
                            <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                                Perbarui & Simpan QR Code
                            </button>
                        </div>
                    @endif
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
                
                <div class="bg-white p-3 border border-slate-200 rounded-2xl shadow-sm relative flex items-center justify-center">
                    <img id="qrCodeImage" src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&ecc=H&data={{ urlencode($qrcodeUrl) }}" alt="SANS SPMB QR Code" class="h-44 w-44 object-contain">
                    <!-- Center Branding Logo Overlay -->
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="w-20 h-20">
                            <img id="qrCenterLogo" src="{{ $logoUrl }}" alt="School Logo" class="max-h-full max-w-full object-contain rounded">
                        </div>
                    </div>
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

                <button type="button" onclick="downloadQrWithLogo()" id="downloadQrButton"
                    class="w-full bg-brand-emerald hover-emerald text-white px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-sm text-center flex items-center justify-center gap-2 cursor-pointer">
                    <i data-lucide="download" class="w-4 h-4"></i> Unduh QR Code
                </button>
            </div>

        </div>
    </div>
    @if(session('success'))
        <script>
            if (typeof showToast === 'function') {
                showToast("{{ session('success') }}", 'success');
            }
        </script>
    @endif
    @if(session('error'))
        <script>
            if (typeof showToast === 'function') {
                showToast("{{ session('error') }}", 'error');
            }
        </script>
    @endif
</div>

<script>
    // Real-time preview as user types
    document.getElementById('qrcodeUrlInput').addEventListener('input', function() {
        const url = this.value;
        const encoded = encodeURIComponent(url);

        document.getElementById('qrCodeImage').src = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&ecc=H&data=${encoded}`;
        document.getElementById('displayUrlInput').value = url;
    });

    function copyToClipboard() {
        const input = document.getElementById('displayUrlInput');
        input.select();
        input.setSelectionRange(0, 99999); // for mobile
        navigator.clipboard.writeText(input.value);
        showToast('Tautan disalin ke papan klip!', 'success');
    }

    function downloadQrWithLogo() {
        const logoUrl = "{{ $logoUrl }}";
        const url = document.getElementById('qrcodeUrlInput').value || "{{ $qrcodeUrl }}";
        const encoded = encodeURIComponent(url);
        const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=600x600&ecc=H&data=${encoded}`;

        const downloadBtn = document.getElementById('downloadQrButton');
        const originalHtml = downloadBtn.innerHTML;
        downloadBtn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Memproses QR...';
        if (window.lucide) lucide.createIcons();

        const canvas = document.createElement('canvas');
        const size = 600;
        canvas.width = size;
        canvas.height = size;
        const ctx = canvas.getContext('2d');

        const qrImg = new Image();
        qrImg.crossOrigin = "Anonymous";
        qrImg.src = qrApiUrl;

        qrImg.onload = function() {
            ctx.fillStyle = "#FFFFFF";
            ctx.fillRect(0, 0, size, size);
            ctx.drawImage(qrImg, 0, 0, size, size);

            const logoImg = new Image();
            logoImg.crossOrigin = "Anonymous";
            logoImg.src = logoUrl;

            logoImg.onload = function() {
                const boxSize = size * 0.22;
                const x = (size - boxSize) / 2;
                const y = (size - boxSize) / 2;
                const radius = 16;

                ctx.save();
                ctx.shadowColor = 'rgba(0, 0, 0, 0.12)';
                ctx.shadowBlur = 10;
                ctx.fillStyle = '#FFFFFF';

                ctx.beginPath();
                ctx.moveTo(x + radius, y);
                ctx.arcTo(x + boxSize, y, x + boxSize, y + boxSize, radius);
                ctx.arcTo(x + boxSize, y + boxSize, x, y + boxSize, radius);
                ctx.arcTo(x, y + boxSize, x, y, radius);
                ctx.arcTo(x, y, x + boxSize, y, radius);
                ctx.closePath();
                ctx.fill();
                ctx.restore();

                const pad = 10;
                const logoDrawSize = boxSize - (pad * 2);
                ctx.drawImage(logoImg, x + pad, y + pad, logoDrawSize, logoDrawSize);

                const link = document.createElement('a');
                link.download = 'sans-spmb-qrcode-logo.png';
                link.href = canvas.toDataURL('image/png');
                link.click();

                downloadBtn.innerHTML = originalHtml;
                if (window.lucide) lucide.createIcons();
            };

            logoImg.onerror = function() {
                const link = document.createElement('a');
                link.download = 'sans-spmb-qrcode.png';
                link.href = canvas.toDataURL('image/png');
                link.click();

                downloadBtn.innerHTML = originalHtml;
                if (window.lucide) lucide.createIcons();
            };
        };

        qrImg.onerror = function() {
            if (typeof showToast === 'function') {
                showToast('Gagal memuat gambar QR code', 'error');
            }
            downloadBtn.innerHTML = originalHtml;
            if (window.lucide) lucide.createIcons();
        };
    }
</script>
@endsection
