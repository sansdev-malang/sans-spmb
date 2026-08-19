@extends('layouts.admin')

@section('title', 'Setting UI Portal - Admin Panel')
@section('page_title', 'Setting UI Portal')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h1 class="text-xl font-extrabold text-slate-800">Pengaturan Tampilan Portal Pendaftaran (UI Portal)</h1>
        <p class="text-xs text-slate-500 mt-1">Mengustomisasi teks, skema warna, dan logo di halaman depan portal pendaftaran calon siswa.</p>
    </div>



    <!-- Form Configuration -->
    <form onsubmit="saveUiSettings(event)" class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        <div class="p-8 space-y-6">
            
            <!-- Section 1: Hero Banner Text -->
            <div class="space-y-4">
                <h3 class="text-sm font-extrabold text-brand-emerald border-b border-slate-100 pb-2">1. Konten Hero Banner</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Judul Utama Hero (Hero Title)</label>
                        <input type="text" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm"
                            value="Membentuk Generasi Amanah, Cerdas, dan Mandiri">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Sub-judul Deskripsi (Hero Description)</label>
                        <textarea rows="3" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">Sekolah Anak Saleh memadukan kurikulum Islam komprehensif dengan pendekatan modern yang ramah anak, mempersiapkan buah hati Anda untuk masa depan yang gemilang.</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Branding & Appearance -->
            <div class="space-y-4 pt-4">
                <h3 class="text-sm font-extrabold text-brand-emerald border-b border-slate-100 pb-2">2. Warna & Branding Tampilan</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Warna Primer Portal</label>
                        <div class="flex gap-2 items-center">
                            <input type="color" class="h-10 w-12 rounded border border-slate-300 cursor-pointer" value="#0f5132">
                            <span class="text-xs font-mono text-slate-500">#0f5132 (Emerald)</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Warna Sekunder Portal</label>
                        <div class="flex gap-2 items-center">
                            <input type="color" class="h-10 w-12 rounded border border-slate-300 cursor-pointer" value="#ffc107">
                            <span class="text-xs font-mono text-slate-500">#ffc107 (Yellow)</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Mode Layout Standar</label>
                        <select class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
                            <option value="light">Light Mode (Terang)</option>
                            <option value="dark">Dark Mode (Gelap)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 3: Logo & Asset Upload -->
            <div class="space-y-4 pt-4">
                <h3 class="text-sm font-extrabold text-brand-emerald border-b border-slate-100 pb-2">3. Logo Sekolah & Gambar Banner</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="border border-dashed border-slate-300 rounded-2xl p-5 flex flex-col items-center justify-center text-center gap-2">
                        <i data-lucide="school" class="w-8 h-8 text-brand-emerald"></i>
                        <div class="text-xs font-bold text-slate-700">Logo Instansi Sekolah</div>
                        <p class="text-[10px] text-slate-400">PNG atau SVG, maks 1MB (rekomendasi rasio 1:1)</p>
                        <button type="button" class="mt-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-[10px] font-bold transition flex items-center gap-1">
                            <i data-lucide="upload" class="w-3 h-3"></i> Pilih File Logo
                        </button>
                    </div>
                    <div class="border border-dashed border-slate-300 rounded-2xl p-5 flex flex-col items-center justify-center text-center gap-2">
                        <i data-lucide="image" class="w-8 h-8 text-brand-emerald"></i>
                        <div class="text-xs font-bold text-slate-700">Banner Background Hero</div>
                        <p class="text-[10px] text-slate-400">JPG atau WebP, maks 2MB (rekomendasi resolusi 1920x1080)</p>
                        <button type="button" class="mt-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-[10px] font-bold transition flex items-center gap-1">
                            <i data-lucide="upload" class="w-3 h-3"></i> Pilih Gambar Banner
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Submit Panel -->
        <div class="bg-slate-50 border-t border-slate-100 px-8 py-4 flex justify-between items-center">
            <span class="text-xs text-slate-400 font-semibold">Semua perubahan hanya berlaku pada domain calon pendaftar.</span>
            <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl text-xs font-bold shadow-md transition">
                Simpan Perubahan Tampilan
            </button>
        </div>
    </form>
</div>

<script>
    function saveUiSettings(e) {
        e.preventDefault();
        showToast('Pengaturan tampilan UI pendaftaran berhasil disimpan!', 'success');
    }
</script>
@endsection
