@extends('layouts.admin')

@section('title', 'Pengaturan Kelas - Admin Panel')
@section('page_title', 'Pengaturan Kelas')

@section('content')
<div class="space-y-6">
    <!-- Header Summary Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800">Manajemen Kuota Kelas & Gelombang</h1>
            <p class="text-xs text-slate-500 mt-1">Mengatur pembagian kapasitas dan kuota calon siswa untuk setiap jenjang kelas.</p>
        </div>
        <div>
            <button class="bg-brand-emerald hover-emerald text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-sm transition">
                ➕ Tambah Kelas Baru
            </button>
        </div>
    </div>

    <!-- Class Grid Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Class 1 -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="bg-brand-emerald text-white p-5">
                <span class="text-[9px] font-black uppercase tracking-wider bg-emerald-950 px-2 py-0.5 rounded text-brand-yellow">PG</span>
                <h3 class="font-extrabold text-base mt-2">Play Group (Kelompok Bermain)</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400 font-semibold uppercase">Kuota Pendaftaran</span>
                    <span class="font-extrabold text-slate-700">25 Kursi</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400 font-semibold uppercase">Pendaftar Terisi</span>
                    <span class="font-extrabold text-brand-emerald">18 Calon Siswa</span>
                </div>
                <!-- Progress bar -->
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-brand-emerald h-full rounded-full" style="width: 72%"></div>
                </div>
                <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                    <button class="border border-slate-200 hover:bg-slate-50 text-slate-600 px-3 py-1.5 rounded-lg text-[10px] font-bold transition">Ubah Kuota</button>
                </div>
            </div>
        </div>

        <!-- Class 2 -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="bg-brand-emerald text-white p-5">
                <span class="text-[9px] font-black uppercase tracking-wider bg-emerald-950 px-2 py-0.5 rounded text-brand-yellow">TK A</span>
                <h3 class="font-extrabold text-base mt-2">TK A (Taman Kanak-Kanak)</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400 font-semibold uppercase">Kuota Pendaftaran</span>
                    <span class="font-extrabold text-slate-700">20 Kursi</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400 font-semibold uppercase">Pendaftar Terisi</span>
                    <span class="font-extrabold text-brand-emerald">15 Calon Siswa</span>
                </div>
                <!-- Progress bar -->
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-brand-emerald h-full rounded-full" style="width: 75%"></div>
                </div>
                <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                    <button class="border border-slate-200 hover:bg-slate-50 text-slate-600 px-3 py-1.5 rounded-lg text-[10px] font-bold transition">Ubah Kuota</button>
                </div>
            </div>
        </div>

        <!-- Class 3 -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="bg-brand-emerald text-white p-5">
                <span class="text-[9px] font-black uppercase tracking-wider bg-emerald-950 px-2 py-0.5 rounded text-brand-yellow">TK B</span>
                <h3 class="font-extrabold text-base mt-2">TK B (Taman Kanak-Kanak)</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400 font-semibold uppercase">Kuota Pendaftaran</span>
                    <span class="font-extrabold text-slate-700">25 Kursi</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-400 font-semibold uppercase">Pendaftar Terisi</span>
                    <span class="font-extrabold text-brand-emerald">12 Calon Siswa</span>
                </div>
                <!-- Progress bar -->
                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                    <div class="bg-brand-emerald h-full rounded-full" style="width: 48%"></div>
                </div>
                <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                    <button class="border border-slate-200 hover:bg-slate-50 text-slate-600 px-3 py-1.5 rounded-lg text-[10px] font-bold transition">Ubah Kuota</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
