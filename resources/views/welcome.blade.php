@extends('layouts.portal')

@section('title', 'Sekolah Anak Saleh - Penerimaan Siswa Baru')

@section('content')
<!-- Hero Section -->
<div class="relative bg-brand-emerald text-white overflow-hidden py-24 md:py-32">
    <!-- background pattern decorative -->
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#ffc107_1px,transparent_1px)] [background-size:16px_16px]"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <!-- Hero Texts -->
        <div class="space-y-6">
            <span class="bg-brand-yellow text-slate-900 font-extrabold text-[10px] uppercase tracking-widest px-3.5 py-1.5 rounded-full shadow-sm">
                Tahun Ajaran 2026/2027
            </span>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-black leading-tight tracking-tight">
                Membentuk Generasi <span class="text-brand-yellow">Amanah, Cerdas</span>, dan <span class="text-brand-yellow">Mandiri</span>
            </h1>
            <p class="text-slate-200 text-sm md:text-base leading-relaxed max-w-xl">
                Sekolah Anak Saleh memadukan kurikulum Islam komprehensif dengan pendekatan modern yang ramah anak, mempersiapkan buah hati Anda untuk masa depan yang gemilang dan berakhlak mulia.
            </p>
            <div class="flex flex-wrap gap-4 pt-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="bg-brand-yellow text-slate-900 hover:bg-yellow-400 px-6 py-3.5 rounded-xl font-bold text-sm shadow transition duration-150">
                        Masuk ke Dashboard Anda
                    </a>
                @else
                    <a href="{{ route('register') }}" class="bg-brand-yellow text-slate-900 hover:bg-yellow-400 px-6 py-3.5 rounded-xl font-bold text-sm shadow transition duration-150">
                        Daftar Sekarang
                    </a>
                    <a href="{{ route('login') }}" class="border border-white hover:bg-white/10 text-white px-6 py-3.5 rounded-xl font-bold text-sm transition duration-150">
                        Masuk Dashboard
                    </a>
                @endauth
            </div>
        </div>
        
        <!-- Hero Graphics placeholder/mockup -->
        <div class="hidden lg:flex justify-center relative">
            <div class="h-96 w-96 bg-brand-yellow/10 border-4 border-dashed border-brand-yellow/30 rounded-full absolute -top-8 -left-8 animate-pulse"></div>
            <div class="relative bg-emerald-950 p-6 rounded-3xl shadow-2xl border border-emerald-800 w-full max-w-md overflow-hidden">
                <div class="flex justify-between items-center pb-4 border-b border-emerald-900">
                    <span class="text-xs font-bold tracking-widest text-brand-yellow">STATISTIK SPMB</span>
                    <span class="h-2 w-2 rounded-full bg-green-500 animate-ping"></span>
                </div>
                <div class="py-6 space-y-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Pendaftaran Gelombang 1</span>
                        <span class="text-white font-bold">Sedang Berlangsung</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Kuota Tersisa</span>
                        <span class="text-brand-yellow font-black">15 Kursi</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Biaya Formulir</span>
                        <span class="text-white font-bold">Rp 350.000</span>
                    </div>
                </div>
                <div class="bg-emerald-900/50 p-4 rounded-2xl border border-emerald-800">
                    <p class="text-[11px] text-brand-yellow font-extrabold uppercase tracking-widest mb-1">Jadwal Observasi Terdekat</p>
                    <p class="text-xs text-white leading-relaxed">
                        Sabtu, 26 Oktober 2024. Pukul 08:00 - 10:00 WIB (Online via Zoom).
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mengapa Memilih Kami Section -->
<div class="max-w-7xl mx-auto px-4 py-20 sm:px-6 lg:px-8">
    <div class="text-center max-w-3xl mx-auto space-y-3 mb-16">
        <span class="text-brand-emerald font-extrabold text-[10px] uppercase tracking-widest bg-emerald-100 px-3.5 py-1 rounded-full">Keunggulan</span>
        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">Mengapa Memilih Sekolah Anak Saleh?</h2>
        <p class="text-sm text-slate-500 leading-relaxed">Pendidikan terbaik dengan mengedepankan pembiasaan akhlakul karimah dan kesiapan kognitif anak.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Card 1 -->
        <div class="bg-white p-8 rounded-2xl shadow-md border border-slate-100 space-y-4 hover:shadow-lg transition">
            <div class="h-12 w-12 bg-emerald-100 text-brand-emerald rounded-2xl flex items-center justify-center font-bold text-xl shadow-sm">
                🕌
            </div>
            <h3 class="font-extrabold text-lg text-slate-800">Kurikulum Islam Terpadu</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Memadukan ilmu agama yang mendalam dengan kurikulum nasional yang progresif, membekali anak dengan fondasi akhlak yang kuat.
            </p>
        </div>
        <!-- Card 2 -->
        <div class="bg-white p-8 rounded-2xl shadow-md border border-slate-100 space-y-4 hover:shadow-lg transition">
            <div class="h-12 w-12 bg-emerald-100 text-brand-emerald rounded-2xl flex items-center justify-center font-bold text-xl shadow-sm">
                🏫
            </div>
            <h3 class="font-extrabold text-lg text-slate-800">Fasilitas Lengkap</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Lingkungan belajar yang aman, nyaman, dan dilengkapi dengan teknologi terkini untuk mendukung eksplorasi kreativitas siswa.
            </p>
        </div>
        <!-- Card 3 -->
        <div class="bg-white p-8 rounded-2xl shadow-md border border-slate-100 space-y-4 hover:shadow-lg transition">
            <div class="h-12 w-12 bg-emerald-100 text-brand-emerald rounded-2xl flex items-center justify-center font-bold text-xl shadow-sm">
                🌟
            </div>
            <h3 class="font-extrabold text-lg text-slate-800">Karakter Saleh</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Pembiasaan adab dan akhlak mulia setiap hari, membentuk kemandirian dan rasa peduli terhadap sesama dan lingkungan.
            </p>
        </div>
    </div>
</div>

<!-- Alur Pendaftaran Section -->
<div class="bg-slate-50 py-20 border-t border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto space-y-3 mb-16">
            <span class="text-brand-emerald font-extrabold text-[10px] uppercase tracking-widest bg-emerald-100 px-3.5 py-1 rounded-full">Prosedur</span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">5 Langkah Mudah Pendaftaran</h2>
            <p class="text-sm text-slate-500 leading-relaxed">Perjalanan menuju pendidikan berkualitas buah hati dimulai dari sini.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <!-- Step 1 -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 text-center relative space-y-3">
                <span class="absolute top-4 left-4 text-xs font-black text-slate-200">01</span>
                <span class="text-xl block">👤</span>
                <h4 class="font-extrabold text-xs text-slate-800">Buat Akun</h4>
                <p class="text-[10px] text-slate-400 leading-relaxed">Daftarkan email aktif untuk mengakses dashboard PPDB.</p>
            </div>
            <!-- Step 2 -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 text-center relative space-y-3">
                <span class="absolute top-4 left-4 text-xs font-black text-slate-200">02</span>
                <span class="text-xl block">📝</span>
                <h4 class="font-extrabold text-xs text-slate-800">Isi Formulir</h4>
                <p class="text-[10px] text-slate-400 leading-relaxed">Lengkapi data calon siswa dan informasi orang tua.</p>
            </div>
            <!-- Step 3 -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 text-center relative space-y-3 border-brand-emerald ring-2 ring-emerald-50">
                <span class="absolute top-4 left-4 text-xs font-black text-brand-emerald/20">03</span>
                <span class="text-xl block">💳</span>
                <h4 class="font-extrabold text-xs text-brand-emerald">Biaya Seleksi</h4>
                <p class="text-[10px] text-slate-400 leading-relaxed">Lakukan pembayaran (VA/QRIS) via Winpay untuk mengaktifkan jadwal tes.</p>
            </div>
            <!-- Step 4 -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 text-center relative space-y-3">
                <span class="absolute top-4 left-4 text-xs font-black text-slate-200">04</span>
                <span class="text-xl block">🔍</span>
                <h4 class="font-extrabold text-xs text-slate-800">Observasi</h4>
                <p class="text-[10px] text-slate-400 leading-relaxed">Ikuti tes kesiapan belajar dan wawancara orang tua secara online.</p>
            </div>
            <!-- Step 5 -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 text-center relative space-y-3">
                <span class="absolute top-4 left-4 text-xs font-black text-slate-200">05</span>
                <span class="text-xl block">🎉</span>
                <h4 class="font-extrabold text-xs text-slate-800">Daftar Ulang</h4>
                <p class="text-[10px] text-slate-400 leading-relaxed">Pengumuman hasil akhir dan penyelesaian administrasi.</p>
            </div>
        </div>
    </div>
</div>
@endsection
