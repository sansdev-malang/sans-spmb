@extends('layouts.admin')

@section('title', 'Demografi & Asal Sekolah Calon Siswa - Admin Panel')
@section('page_title', 'Demografi & Asal Sekolah')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div>
                    <h1 class="text-xl font-extrabold text-slate-800 dark:text-white">Demografi & Asal Sekolah Calon Siswa</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Analisis persebaran asal sekolah/TK, wilayah domisili, dan efektivitas saluran informasi pendaftaran.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-4">
        <form method="GET" action="{{ route('admin.reports.demographics') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Unit:</label>
                <select name="unit_id" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                    <option value="">Semua Unit Sekolah</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            @if(request()->filled('unit_id'))
                <a href="{{ route('admin.reports.demographics') }}" class="h-8 px-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-semibold flex items-center gap-1 hover:bg-slate-200 transition">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Reset
                </a>
            @endif
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top 15 Origin Schools -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                    <i data-lucide="school" class="w-4 h-4 text-brand-emerald"></i>
                    Top Asal Sekolah / Lembaga Asal
                </h2>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Ranking Pendaftar</span>
            </div>

            <div class="space-y-2.5">
                @php $rank = 1; @endphp
                @forelse($topSchools as $school => $cnt)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-lg {{ $rank <= 3 ? 'bg-amber-400 text-amber-950 font-black' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold' }} flex items-center justify-center text-xs">
                                {{ $rank++ }}
                            </span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100">{{ $school }}</span>
                        </div>
                        <span class="text-xs font-extrabold text-brand-emerald bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-1 rounded-lg">
                            {{ $cnt }} anak
                        </span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">Belum ada data sekolah asal yang tercatat.</p>
                @endforelse
            </div>
        </div>

        <!-- Right Side: Location & Marketing Channels -->
        <div class="space-y-6">
            <!-- City / District Distribution -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
                <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-4 h-4 text-brand-emerald"></i>
                    Persebaran Wilayah / Domisili
                </h2>
                <div class="space-y-2.5">
                    @forelse($topCities as $city => $cnt)
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-brand-emerald"></span>
                                {{ $city }}
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $cnt }} pendaftar</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada data wilayah yang tercatat.</p>
                    @endforelse
                </div>
            </div>

            <!-- Marketing Information Sources -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
                <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                    <i data-lucide="megaphone" class="w-4 h-4 text-brand-emerald"></i>
                    Saluran Informasi Pendaftaran
                </h2>
                <div class="space-y-2.5">
                    @forelse($sourceCounts as $src => $cnt)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 text-xs">
                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ $src }}</span>
                            <span class="font-extrabold text-indigo-600 dark:text-indigo-400">{{ $cnt }} responden</span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">Belum ada data sumber informasi yang tercatat.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
