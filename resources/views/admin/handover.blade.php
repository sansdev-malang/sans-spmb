@extends('layouts.admin')

@section('title', 'Handover Data Siswa Baru ke Unit - Admin Panel')
@section('page_title', 'Handover Siswa Unit')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                    <i data-lucide="share-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-slate-800 dark:text-white">Handover Data Siswa Baru ke Unit Sekolah</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Ekspor data master siswa & orang tua yang telah menyelesaikan daftar ulang untuk diimpor ke sistem akademik unit (SANS PAUD / SD / SMP).</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.handover.export', request()->query()) }}" class="h-9 px-4 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-2">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Ekspor Format Unit (.csv)</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Siap Di-Handover</span>
                <span class="text-xl font-black text-slate-800 dark:text-white block mt-1">{{ $allReady->count() }} siswa</span>
            </div>
            <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <i data-lucide="users" class="w-4 h-4"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Daftar Ulang Lunas</span>
                <span class="text-xl font-black text-emerald-600 dark:text-emerald-400 block mt-1">{{ $totalLunas }} siswa</span>
            </div>
            <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kelengkapan Dokumen</span>
                <span class="text-xl font-black text-teal-600 dark:text-teal-400 block mt-1">100% Terverifikasi</span>
            </div>
            <div class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                <i data-lucide="file-check-2" class="w-4 h-4"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-4">
        <form method="GET" action="{{ route('admin.handover') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[240px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa, NISN, NIK, nama orang tua..." class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 rounded-xl px-3.5 py-2 focus:outline-none focus:ring-1 focus:ring-brand-emerald">
            </div>

            <div class="flex items-center gap-2">
                <select name="unit_id" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                    <option value="">Semua Unit Sekolah</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="h-9 px-4 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition">
                Cari
            </button>

            @if(request()->anyFilled(['search', 'unit_id']))
                <a href="{{ route('admin.handover') }}" class="h-9 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-semibold flex items-center gap-1 hover:bg-slate-200 transition">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Ready Candidates Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="px-5 py-3.5">Calon Siswa Baru</th>
                        <th class="px-5 py-3.5">Unit & Jenjang</th>
                        <th class="px-5 py-3.5">Identitas (NISN/NIK)</th>
                        <th class="px-5 py-3.5">Orang Tua / Kontak</th>
                        <th class="px-5 py-3.5">Status DSP</th>
                        <th class="px-5 py-3.5 text-right">Status SPMB</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                    @forelse($readyCandidates as $c)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $c->candidate_name }}</div>
                                <div class="text-[10px] text-slate-400">
                                    {{ $c->gender === 'L' ? 'Laki-laki' : 'Perempuan' }} • {{ $c->birth_place ?? '-' }}, {{ $c->birth_date ? \Carbon\Carbon::parse($c->birth_date)->format('d/m/Y') : '-' }}
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-teal-700 dark:text-teal-400">{{ $c->unit->name ?? '-' }}</span>
                                <div class="text-[10px] text-slate-400">{{ $c->grade->name ?? '-' }} ({{ $c->classProgram->name ?? 'Reguler' }})</div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div>NISN: <span class="font-mono font-semibold">{{ $c->nisn ?? '-' }}</span></div>
                                <div class="text-[10px] text-slate-400">NIK: {{ $c->nik ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div>{{ $c->father_name ?? $c->mother_name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $c->parent_phone ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($c->remaining_balance <= 0 && $c->net_fee > 0 && $c->total_paid_final_fee > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
                                        <i data-lucide="check" class="w-3 h-3"></i> Lunas
                                    </span>
                                @elseif($c->total_paid_final_fee > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400">
                                        Cicilan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400">
                                        Belum Lunas
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ strtoupper($c->registration_status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                Belum ada data siswa baru yang siap di-handover pada kriteria filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($readyCandidates->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $readyCandidates->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
