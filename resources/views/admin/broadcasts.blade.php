@extends('layouts.admin')

@section('title', 'Broadcast WhatsApp & Pengingat - Admin Panel')
@section('page_title', 'Broadcast & Pengingat')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i data-lucide="message-square" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-slate-800 dark:text-white">Broadcast WhatsApp & Pengingat Cerdas</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Kirimkan pesan personalisasi, notifikasi kelengkapan berkas, jadwal observasi, dan pengingat pelunasan DSP ke wali murid.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Target & Template Editor -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Target Selection Form -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
            <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="filter" class="w-4 h-4 text-brand-emerald"></i>
                Pilih Target Penerima
            </h2>

            <form method="GET" action="{{ route('admin.broadcasts') }}" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1.5">Kelompok Sasaran:</label>
                    <select name="target" onchange="this.form.submit()" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-800 dark:text-slate-100 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                        <option value="all" {{ $targetAudience === 'all' ? 'selected' : '' }}>Semua Calon Siswa</option>
                        <option value="unverified" {{ $targetAudience === 'unverified' ? 'selected' : '' }}>Berkas Belum Diverifikasi</option>
                        <option value="taaruf_schedule" {{ $targetAudience === 'taaruf_schedule' ? 'selected' : '' }}>Pengingat Jadwal Observasi / Ta'aruf</option>
                        <option value="has_receivable" {{ $targetAudience === 'has_receivable' ? 'selected' : '' }}>Pengingat Pelunasan DSP / Sisa Tagihan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1.5">Unit Sekolah:</label>
                    <select name="unit_id" onchange="this.form.submit()" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-800 dark:text-slate-100 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                        <option value="">Semua Unit</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1.5">Gelombang:</label>
                    <select name="wave_id" onchange="this.form.submit()" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-800 dark:text-slate-100 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-brand-emerald cursor-pointer">
                        <option value="">Semua Gelombang</option>
                        @foreach($waves as $w)
                            <option value="{{ $w->id }}" {{ request('wave_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-2">
                    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/40 flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300">Total Sasaran:</span>
                        <span class="text-sm font-black text-emerald-700 dark:text-emerald-400">{{ $allTargetCandidates->count() }} orang</span>
                    </div>
                </div>
            </form>
        </div>

        <!-- Template WhatsApp Message Preview -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 lg:col-span-2 space-y-4">
            <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4 text-brand-emerald"></i>
                Format Template Pesan WhatsApp
            </h2>

            <div class="space-y-3">
                <textarea id="broadcastTemplate" rows="8" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-100 rounded-xl p-3.5 focus:outline-none focus:ring-1 focus:ring-brand-emerald font-mono leading-relaxed">{{ $currentMessage }}</textarea>

                <div class="flex flex-wrap gap-2 text-[10px] text-slate-500">
                    <span class="font-bold text-slate-700 dark:text-slate-300">Tag Placeholder Otomatis:</span>
                    <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-brand-emerald font-bold cursor-pointer" onclick="insertTag('@{{nama_siswa}}')">@{{nama_siswa}}</code>
                    <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-brand-emerald font-bold cursor-pointer" onclick="insertTag('@{{no_pendaftaran}}')">@{{no_pendaftaran}}</code>
                    <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-brand-emerald font-bold cursor-pointer" onclick="insertTag('@{{unit}}')">@{{unit}}</code>
                    <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-brand-emerald font-bold cursor-pointer" onclick="insertTag('@{{sisa_tagihan}}')">@{{sisa_tagihan}}</code>
                    <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-brand-emerald font-bold cursor-pointer" onclick="insertTag('@{{jadwal_taaruf}}')">@{{jadwal_taaruf}}</code>
                </div>
            </div>
        </div>
    </div>

    <!-- Target Recipient List -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="text-sm font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="phone-call" class="w-4 h-4 text-brand-emerald"></i>
                Daftar Kontak Sasaran Broadcast ({{ $allTargetCandidates->count() }} Siswa)
            </h2>
            <button type="button" onclick="copyAllPhones()" class="h-8 px-3 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition flex items-center gap-1.5">
                <i data-lucide="copy" class="w-3.5 h-3.5"></i> Salin Semua Nomor HP
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                        <th class="py-2.5">Calon Siswa</th>
                        <th class="py-2.5">Unit / Jenjang</th>
                        <th class="py-2.5">Wali Murid</th>
                        <th class="py-2.5">No. WhatsApp</th>
                        <th class="py-2.5">Status SPMB</th>
                        <th class="py-2.5 text-right">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-medium">
                    @forelse($allTargetCandidates as $c)
                        @php
                            $phone = preg_replace('/[^0-9]/', '', $c->parent_phone ?? '');
                            if (str_starts_with($phone, '0')) {
                                $phone = '62' . substr($phone, 1);
                            }
                            $customMsg = str_replace(
                                ['{{nama_siswa}}', '{{no_pendaftaran}}', '{{unit}}', '{{sisa_tagihan}}', '{{jadwal_taaruf}}', '{{portal_url}}'],
                                [
                                    $c->candidate_name,
                                    'SPMB-' . str_pad($c->id, 5, '0', STR_PAD_LEFT),
                                    $c->unit->name ?? '-',
                                    number_format($c->remaining_balance, 0, ',', '.'),
                                    $c->observation_date ? (\Carbon\Carbon::parse($c->observation_date)->translatedFormat('l, d F Y') . ' pukul ' . ($c->observation_time ?? '08:00 WIB')) : 'Menunggu Jadwal',
                                    url('/dashboard')
                                ],
                                $currentMessage
                            );
                            $waUrl = !empty($phone) ? 'https://wa.me/' . $phone . '?text=' . urlencode($customMsg) : '#';
                        @endphp
                        <tr>
                            <td class="py-3 font-bold text-slate-900 dark:text-white">
                                {{ $c->candidate_name }}
                                <div class="text-[10px] text-slate-400 font-mono">SPMB-{{ str_pad($c->id, 5, '0', STR_PAD_LEFT) }}</div>
                            </td>
                            <td class="py-3">{{ $c->unit->name ?? '-' }} ({{ $c->grade->name ?? '-' }})</td>
                            <td class="py-3">{{ $c->father_name ?? $c->mother_name ?? '-' }}</td>
                            <td class="py-3 font-mono font-bold text-slate-800 dark:text-slate-200 phone-cell" data-phone="{{ $phone }}">
                                {{ $c->parent_phone ?? '-' }}
                            </td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ strtoupper($c->registration_status) }}
                                </span>
                            </td>
                            <td class="py-3 text-right">
                                @if(!empty($phone))
                                    <a href="{{ $waUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                                        <i data-lucide="send" class="w-3.5 h-3.5"></i> Kirim WA
                                    </a>
                                @else
                                    <span class="text-slate-400 text-[10px]">No HP Kosong</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Tidak ada calon siswa pada kelompok sasaran ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function insertTag(tag) {
        const textarea = document.getElementById('broadcastTemplate');
        textarea.value += ' ' + tag;
        textarea.focus();
    }

    function copyAllPhones() {
        const cells = document.querySelectorAll('.phone-cell');
        const phones = [];
        cells.forEach(c => {
            const p = c.getAttribute('data-phone');
            if (p && p.length > 5) phones.push(p);
        });

        if (phones.length === 0) {
            alert('Tidak ada nomor telepon yang dapat disalin.');
            return;
        }

        navigator.clipboard.writeText(phones.join(', ')).then(() => {
            alert('Berhasil menyalin ' + phones.length + ' nomor WhatsApp ke clipboard!');
        });
    }
</script>
@endsection
