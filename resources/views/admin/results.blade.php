@extends('layouts.admin')

@section('title', 'Hasil Seleksi & Pengumuman Kelulusan - Admin Panel')
@section('page_title', 'Pengumuman Kelulusan')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div>
                    <h1 class="text-xl font-extrabold text-slate-800 dark:text-white">Hasil Seleksi & Pengumuman Kelulusan</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Penetapan status kelulusan calon siswa pasca observasi Ta'aruf dan penerbitan Surat Keputusan Diterima.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Peserta Observasi</span>
                <span class="text-xl font-black text-slate-800 dark:text-white block mt-1">{{ $stats['total'] }}</span>
            </div>
            <div class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                <i data-lucide="users" class="w-4 h-4"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Lulus / Diterima</span>
                <span class="text-xl font-black text-emerald-600 dark:text-emerald-400 block mt-1">{{ $stats['passed'] }}</span>
            </div>
            <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Menunggu Sidang</span>
                <span class="text-xl font-black text-amber-600 dark:text-amber-400 block mt-1">{{ $stats['pending'] }}</span>
            </div>
            <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <i data-lucide="clock" class="w-4 h-4"></i>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Tidak Lolos</span>
                <span class="text-xl font-black text-rose-600 dark:text-rose-400 block mt-1">{{ $stats['rejected'] }}</span>
            </div>
            <div class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                <i data-lucide="x-circle" class="w-4 h-4"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-4">
        <form method="GET" action="{{ route('admin.results') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[240px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa, no daftar, no HP..." class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 rounded-xl px-3.5 py-2 focus:outline-none focus:ring-1 focus:ring-brand-emerald">
            </div>

            <div class="flex items-center gap-2">
                <select name="unit_id" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                    <option value="">Semua Unit</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="passed" {{ request('status') == 'passed' ? 'selected' : '' }}>Lulus / Diterima</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu Sidang</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Tidak Lolos</option>
                </select>
            </div>

            <button type="submit" class="h-9 px-4 bg-brand-emerald hover:bg-brand-dark text-white rounded-xl text-xs font-bold transition">
                Cari
            </button>

            @if(request()->anyFilled(['search', 'unit_id', 'status']))
                <a href="{{ route('admin.results') }}" class="h-9 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-semibold flex items-center gap-1 hover:bg-slate-200 transition">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Candidates Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="px-5 py-3.5">Calon Siswa</th>
                        <th class="px-5 py-3.5">Unit / Jenjang</th>
                        <th class="px-5 py-3.5">Jalur & Gelombang</th>
                        <th class="px-5 py-3.5">Status Seleksi</th>
                        <th class="px-5 py-3.5">Catatan Panitia</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                    @forelse($candidates as $c)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $c->candidate_name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">SPMB-{{ str_pad($c->id, 5, '0', STR_PAD_LEFT) }} • {{ $c->parent_phone ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $c->unit->name ?? '-' }}</span>
                                <div class="text-[10px] text-slate-400">{{ $c->grade->name ?? '-' }} ({{ $c->classProgram->name ?? 'Reguler' }})</div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div>{{ $c->wave->name ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $c->type->name ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                @if(in_array($c->registration_status, ['taaruf_completed', 'agreement_signed', 'completed']))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">
                                        <i data-lucide="check-circle-2" class="w-3 h-3"></i> Lulus / Diterima
                                    </span>
                                @elseif($c->registration_status === 'rejected')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400">
                                        <i data-lucide="x-circle" class="w-3 h-3"></i> Tidak Lolos
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400">
                                        <i data-lucide="clock" class="w-3 h-3"></i> Menunggu Sidang
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-slate-500 max-w-[200px] truncate" title="{{ $c->committee_notes }}">
                                {{ $c->committee_notes ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Download Admission Letter -->
                                    @if(in_array($c->registration_status, ['taaruf_completed', 'agreement_signed', 'completed']))
                                        <a href="{{ route('dashboard.admission-letter.download', $c->id) }}" target="_blank" class="p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Unduh Surat Diterima (SKL)">
                                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-brand-emerald"></i>
                                        </a>
                                    @endif

                                    <!-- Quick Action Status Form -->
                                    <form method="POST" action="{{ route('admin.results.status', $c->id) }}" class="inline-flex items-center gap-1">
                                        @csrf
                                        @if(!in_array($c->registration_status, ['taaruf_completed', 'agreement_signed', 'completed']))
                                            <input type="hidden" name="status" value="passed">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-bold shadow-xs transition flex items-center gap-1" title="Nyatakan Lulus">
                                                <i data-lucide="check" class="w-3 h-3"></i> Lulus
                                            </button>
                                        @else
                                            <input type="hidden" name="status" value="pending">
                                            <button type="submit" class="px-2 py-1 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 text-[10px] font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Batalkan Keputusan">
                                                Reset
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                Tidak ada data peserta observasi / seleksi yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($candidates->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $candidates->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
