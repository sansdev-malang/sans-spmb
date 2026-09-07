@extends('layouts.admin')

@section('title', 'Rekapitulasi Pendaftaran & Kuota - Admin Panel')
@section('page_title', 'Rekap Pendaftaran')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-slate-800 dark:text-white">Rekapitulasi Pendaftaran & Kuota Kelas</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Statistik alur konversi calon siswa, distribusi gender, dan ketercapaian kuota per unit.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-4">
        <form method="GET" action="{{ route('admin.reports.registrations') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Unit:</label>
                <select name="unit_id" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                    <option value="">Semua Unit Sekolah</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Gelombang:</label>
                <select name="wave_id" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                    <option value="">Semua Gelombang</option>
                    @foreach($waves as $w)
                        <option value="{{ $w->id }}" {{ request('wave_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>

            @if(request()->anyFilled(['unit_id', 'wave_id']))
                <a href="{{ route('admin.reports.registrations') }}" class="h-8 px-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-semibold flex items-center gap-1 hover:bg-slate-200 transition">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Conversion Funnel Grid (4 Steps) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Terdaftar -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">1. Terdaftar</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-slate-800 dark:text-white mt-2">{{ $totalRegistered }}</div>
            <span class="text-[10px] text-slate-400 font-semibold mt-1 block">Total akun calon siswa</span>
        </div>

        <!-- 2. Terverifikasi -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">2. Berkas Valid</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i data-lucide="check-square" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-2">{{ $totalVerified }}</div>
            <span class="text-[10px] text-indigo-500 font-bold mt-1 block">
                {{ $totalRegistered > 0 ? round(($totalVerified / $totalRegistered) * 100, 1) : 0 }}% dari pendaftar
            </span>
        </div>

        <!-- 3. Selesai Ta'aruf -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">3. Lulus Observasi</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <i data-lucide="calendar-check" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-2">{{ $totalTaaruf }}</div>
            <span class="text-[10px] text-amber-500 font-bold mt-1 block">
                {{ $totalVerified > 0 ? round(($totalTaaruf / $totalVerified) * 100, 1) : 0 }}% dari berkas valid
            </span>
        </div>

        <!-- 4. Lunas / Selesai -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">4. Resmi Diterima</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i data-lucide="award" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-2">{{ $totalCompleted }}</div>
            <span class="text-[10px] text-emerald-600 font-bold mt-1 block">
                {{ $totalRegistered > 0 ? round(($totalCompleted / $totalRegistered) * 100, 1) : 0 }}% konversi final
            </span>
        </div>
    </div>

    <!-- Gender & Unit Quota Matrix -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Gender Distribution -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
            <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-brand-emerald"></i>
                Komposisi Gender Siswa
            </h2>
            <div class="space-y-3 pt-2">
                <div>
                    <div class="flex justify-between text-xs font-bold mb-1">
                        <span class="text-sky-600 dark:text-sky-400 flex items-center gap-1.5">
                            <i data-lucide="user" class="w-3.5 h-3.5"></i> Laki-laki
                        </span>
                        <span class="text-slate-700 dark:text-slate-300">
                            {{ $maleCount }} anak ({{ $totalRegistered > 0 ? round(($maleCount / $totalRegistered) * 100, 1) : 0 }}%)
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                        <div class="bg-sky-500 h-full rounded-full" style="width: {{ $totalRegistered > 0 ? ($maleCount / $totalRegistered) * 100 : 0 }}%;"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-xs font-bold mb-1">
                        <span class="text-pink-600 dark:text-pink-400 flex items-center gap-1.5">
                            <i data-lucide="user" class="w-3.5 h-3.5"></i> Perempuan
                        </span>
                        <span class="text-slate-700 dark:text-slate-300">
                            {{ $femaleCount }} anak ({{ $totalRegistered > 0 ? round(($femaleCount / $totalRegistered) * 100, 1) : 0 }}%)
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                        <div class="bg-pink-500 h-full rounded-full" style="width: {{ $totalRegistered > 0 ? ($femaleCount / $totalRegistered) * 100 : 0 }}%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unit & Jenjang Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 lg:col-span-2 space-y-4">
            <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="building" class="w-4 h-4 text-brand-emerald"></i>
                Distribusi Pendaftar per Unit Sekolah & Jenjang
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-2.5">Unit Sekolah</th>
                            <th class="py-2.5">Terdaftar</th>
                            <th class="py-2.5">Berkas Valid</th>
                            <th class="py-2.5">Resmi Diterima</th>
                            <th class="py-2.5 text-right">Rincian Jenjang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                        @foreach($unitStats as $us)
                            <tr>
                                <td class="py-3 font-bold text-slate-900 dark:text-white">{{ $us['unit']->name }}</td>
                                <td class="py-3 font-semibold">{{ $us['registered'] }} anak</td>
                                <td class="py-3 text-indigo-600 font-semibold">{{ $us['verified'] }} anak</td>
                                <td class="py-3 text-emerald-600 font-bold">{{ $us['completed'] }} anak</td>
                                <td class="py-3 text-right">
                                    <div class="flex flex-wrap gap-1 justify-end">
                                        @foreach($us['grades'] as $gs)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                {{ $gs['grade']->name }}: {{ $gs['registered'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
