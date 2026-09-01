@extends('layouts.admin')

@section('title', 'Verifikasi Data Calon Siswa - Portal SPMB')
@section('page_title', 'Verifikasi Data')

@section('content')
<div class="space-y-8">
    
    <!-- Header Summary Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800">Verifikasi Data Calon Siswa</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola review berkas, status pembayaran, dan validasi data calon siswa dari satu tempat.</p>
        </div>
    </div>



    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <!-- Stat Item -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800">
            <span class="text-xs text-slate-400 font-bold block uppercase tracking-wider">Total Pendaftar</span>
            <span class="text-2xl font-black text-slate-800 dark:text-white block mt-1">{{ $stats['total'] }}</span>
        </div>
        <!-- Stat Item -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 border-l-4 border-l-yellow-500">
            <span class="text-xs text-slate-400 font-bold block uppercase tracking-wider">Perlu Review</span>
            <span class="text-2xl font-black text-yellow-600 block mt-1">{{ $stats['submitted'] }}</span>
        </div>
        <!-- Stat Item -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 border-l-4 border-l-green-500">
            <span class="text-xs text-slate-400 font-bold block uppercase tracking-wider">Terverifikasi</span>
            <span class="text-2xl font-black text-green-600 block mt-1">{{ $stats['verified'] }}</span>
        </div>
        <!-- Stat Item -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 border-l-4 border-l-red-500">
            <span class="text-xs text-slate-400 font-bold block uppercase tracking-wider">Ditolak / Gagal</span>
            <span class="text-2xl font-black text-red-600 block mt-1">{{ $stats['failed'] }}</span>
        </div>
        <!-- Stat Item -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 border-l-4 border-l-emerald-500">
            <span class="text-xs text-slate-400 font-bold block uppercase tracking-wider">Lunas Biaya</span>
            <span class="text-2xl font-black text-brand-emerald dark:text-emerald-400 block mt-1">{{ $stats['paid'] }}</span>
        </div>
    </div>

    <!-- Candidate List Table -->
    <div id="candidate-card" class="bg-white dark:bg-slate-900 rounded-2xl shadow-md border border-slate-100 dark:border-slate-800 overflow-hidden" hx-boost="true" hx-target="#candidate-card" hx-select="#candidate-card">
        <div class="bg-slate-50 dark:bg-slate-950 border-b border-slate-100 dark:border-slate-800 px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Daftar Pendaftaran Calon Siswa</span>
            
            <!-- Quick Filter Links -->
            <div class="flex flex-wrap gap-2 text-xs font-bold">
                <a href="{{ route('admin.verification', request()->except(['status', 'page'])) }}" class="px-2.5 py-1 rounded-full {{ !request()->has('status') ? 'bg-brand-emerald text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-650 dark:text-slate-350' }}">Semua</a>
                @foreach(['submitted' => 'Perlu Review', 'verified' => 'Terverifikasi', 'taaruf_completed' => 'Ta\'aruf Selesai', 'agreement_signed' => 'Persetujuan', 'completed' => 'Lulus', 'failed' => 'Ditolak'] as $statusVal => $statusLabel)
                    <a href="{{ route('admin.verification', array_merge(request()->except(['page']), ['status' => $statusVal])) }}" class="px-2.5 py-1 rounded-full {{ request()->status === $statusVal ? 'bg-brand-emerald text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-650 dark:text-slate-350' }}">{{ $statusLabel }}</a>
                @endforeach
            </div>
        </div>

        <!-- Search & Filter Form -->
        <form action="{{ route('admin.verification') }}" method="GET" class="p-6 bg-slate-50/50 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row gap-4 items-center justify-between">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <!-- Search Input Container -->
                <div class="relative w-full md:w-80 flex items-center">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, WhatsApp, NIK..." 
                           class="w-full pl-9 pr-20 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:text-white transition">
                    
                    <!-- Clear (X) Button -->
                    @if(request('search'))
                        <button type="button" onclick="this.form.querySelector('input[name=search]').value = ''; htmx.trigger(this.form, 'submit');" 
                                class="absolute right-12 inset-y-0 pr-1 flex items-center text-slate-400 hover:text-slate-600 transition"
                                title="Hapus Pencarian">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    @endif

                    <!-- Integrated Search Button -->
                    <button type="submit" class="absolute right-1.5 top-1.5 bottom-1.5 px-3 bg-brand-emerald hover-emerald text-white rounded-lg text-xs font-bold shadow-sm transition">
                        Cari
                    </button>
                </div>
                
                @if(auth()->user()->isSuperAdmin())
                    <!-- Filter Level / Unit -->
                    <select name="unit_id" onchange="htmx.trigger(this.form, 'submit')" class="py-2.5 px-5.5 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-bold text-slate-650 dark:text-slate-350 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                        <option value="">Semua Jenjang</option>
                        @foreach(\App\Models\SpmbUnit::where('is_active', true)->get() as $unit)
                            <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>{{ strtoupper($unit->code) }}</option>
                        @endforeach
                    </select>
                @endif

                <!-- Per Page Select -->
                <select name="per_page" onchange="htmx.trigger(this.form, 'submit')" class="py-2.5 px-4.5 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-bold text-slate-650 dark:text-slate-350 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 Baris</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 Baris</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 Baris</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 Baris</option>
                </select>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 text-xs text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50 dark:bg-slate-950/20">
                        <th class="py-4 px-6 text-center w-12">No.</th>
                        <th class="py-4 px-6">ID / No. Reg</th>
                        <th class="py-4 px-6">Calon Siswa</th>
                        <th class="py-4 px-6">Tingkat</th>
                        <th class="py-4 px-6">Berkas Upload</th>
                        <th class="py-4 px-6 text-center">Status Bayar</th>
                        <th class="py-4 px-6 text-center">Status Berkas</th>
                        <th class="py-4 px-6 text-right">Aksi Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($registrations as $reg)
                        @php
                            $candJson = [
                                'id_label' => 'SANS-' . substr($reg->period->year ?? '2026', 0, 4) . '-' . str_pad($reg->id, 4, '0', STR_PAD_LEFT),
                                'name' => $reg->candidate_name ?? 'Draft / Belum Isi',
                                'nickname' => $reg->nickname ?? '-',
                                'nik' => $reg->nik ?? '-',
                                'gender' => in_array($reg->gender, ['Laki-laki', 'male']) ? 'Laki-laki' : (in_array($reg->gender, ['Perempuan', 'female']) ? 'Perempuan' : ($reg->gender ?? '-')),
                                'birth_place' => $reg->birth_place ?? '-',
                                'birth_date' => $reg->birth_date ? $reg->birth_date->format('d F Y') : '-',
                                'religion' => $reg->religion ?? '-',
                                'previous_school' => $reg->previous_school ?? 'Tidak ada',
                                'admission_level' => $reg->admission_level ?? '-',
                                'class_program' => $reg->classProgram->name ?? 'Reguler',
                                'father_name' => $reg->father_name ?? '-',
                                'mother_name' => $reg->mother_name ?? '-',
                                'parent_phone' => $reg->parent_phone ?? '-',
                                'birth_certificate' => $reg->birth_certificate_path ? asset('storage/' . $reg->birth_certificate_path) : null,
                                'family_card' => $reg->family_card_path ? asset('storage/' . $reg->family_card_path) : null,
                                'created_at_label' => $reg->created_at->format('d M Y, H:i') . ' WIB',
                                'status' => strtoupper($reg->registration_status),
                                'payment_status' => strtoupper($reg->payment_status),
                                'period' => $reg->period->year ?? '-',
                                'wave' => $reg->wave->name ?? '-',
                                'type' => $reg->type->name ?? '-',
                                'extra_services' => $reg->extraServices->pluck('name')->join(', ') ?: '-',
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-850/50 transition">
                            <td class="py-4 px-6 text-center text-slate-500 font-bold text-xs">
                                {{ ($registrations->currentPage() - 1) * $registrations->perPage() + $loop->iteration }}
                            </td>
                            <td class="py-4 px-6 font-mono text-xs text-slate-500">
                                SANS-{{ substr($reg->period->year ?? '2026', 0, 4) }}-{{ str_pad($reg->id, 4, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800 dark:text-slate-200">{{ $reg->candidate_name ?? 'Draft' }}</div>
                                <div class="text-xs text-slate-400">Ortu: {{ $reg->user->name }} ({{ $reg->parent_phone ?? '-' }})</div>
                            </td>
                            <td class="py-4 px-6 font-semibold text-slate-600 dark:text-slate-400">
                                {{ $reg->admission_level ?? '-' }}
                                <div class="mt-0.5">
                                    @if($reg->classProgram && $reg->classProgram->name === 'Inklusi')
                                        <span class="bg-indigo-50 text-indigo-600 px-1.5 py-0.5 rounded text-[9px] font-bold border border-indigo-200">Inklusi</span>
                                    @else
                                        <span class="bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded text-[9px] font-bold border border-slate-200">Reguler</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-6 space-y-1">
                                @if($reg->birth_certificate_path)
                                    <a href="{{ Storage::url($reg->birth_certificate_path) }}" target="_blank" class="text-xs text-brand-emerald font-semibold hover:underline block flex items-center gap-1">
                                        <i data-lucide="file-digit" class="w-3.5 h-3.5"></i> Akte Kelahiran
                                    </a>
                                @endif
                                @if($reg->family_card_path)
                                    <a href="{{ Storage::url($reg->family_card_path) }}" target="_blank" class="text-xs text-brand-emerald font-semibold hover:underline block flex items-center gap-1">
                                        <i data-lucide="file-digit" class="w-3.5 h-3.5"></i> Kartu Keluarga
                                    </a>
                                @endif
                                @if(!$reg->birth_certificate_path && !$reg->family_card_path)
                                    <span class="text-xs text-slate-400">Belum diunggah</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                    @if($reg->payment_status === 'paid') bg-green-50 text-green-700 border border-green-200
                                    @elseif($reg->payment_status === 'pending') bg-yellow-50 text-yellow-700 border border-yellow-200
                                    @else bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 @endif">
                                    {{ $reg->payment_status }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                    @if(in_array($reg->registration_status, ['verified', 'completed'])) bg-green-50 text-green-700 border border-green-200
                                    @elseif($reg->registration_status === 'submitted') bg-blue-50 text-blue-700 border border-blue-200
                                    @elseif($reg->registration_status === 'taaruf_completed') bg-indigo-50 text-indigo-700 border border-indigo-200
                                    @elseif($reg->registration_status === 'agreement_signed') bg-purple-50 text-purple-700 border border-purple-200
                                    @elseif($reg->registration_status === 'failed') bg-red-50 text-red-700 border border-red-200
                                    @else bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 @endif">
                                    {{ str_replace('_', ' ', $reg->registration_status) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex justify-end items-center gap-1.5 flex-wrap">
                                    @if ($reg->registration_status === 'submitted')
                                        <!-- Verifikasi Modal Trigger -->
                                        <button type="button" 
                                            onclick="openCandidateDetailModal({{ json_encode($candJson) }}, true, {{ $reg->id }})" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-2.5 py-1.5 rounded-lg text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-brand-yellow"></i> Verifikasi Data
                                        </button>
                                    @else
                                        <!-- Detail Modal Trigger -->
                                        <button type="button" 
                                            onclick="openCandidateDetailModal({{ json_encode($candJson) }}, false)" 
                                            class="bg-slate-600 hover:bg-slate-700 text-white px-2.5 py-1.5 rounded-lg text-xs font-bold shadow-sm transition flex items-center gap-1">
                                            <i data-lucide="eye" class="w-3 h-3"></i> Detail
                                        </button>
                                    @endif
 
                                    @if ($reg->registration_status === 'verified')
                                        <!-- Selesaikan Ta'aruf -->
                                        <form action="{{ route('admin.registrations.complete-taaruf', $reg->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-2.5 py-1.5 rounded-lg shadow-sm transition flex items-center gap-1">
                                                Selesaikan Ta'aruf
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 px-6 text-center text-slate-400">
                                Tidak ada data pendaftaran yang sesuai filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($registrations->hasPages())
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-950 border-t border-slate-100 dark:border-slate-800">
                {{ $registrations->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Candidate Detail & Verification Modal Overlay -->
<div id="detailModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden shadow-2xl border border-slate-100 dark:border-slate-800 flex flex-col">
        
        <form id="verifyForm" method="POST" action="" class="flex flex-col h-full overflow-hidden">
            @csrf
            <!-- Hidden inputs -->
            <input type="hidden" id="invalid_fields_input" name="invalid_fields" value="[]">

            <!-- Modal Header -->
            <div class="bg-brand-emerald text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
                <div>
                    <h3 class="font-extrabold text-base flex items-center gap-2">
                        <i data-lucide="user" class="w-5 h-5 text-brand-yellow"></i>
                        <span id="det-title-label">Detail Data Pendaftar</span>
                    </h3>
                    <p id="det-id-label" class="text-xs text-emerald-100 font-mono mt-0.5">ID: SANS-YYYY-XXXX</p>
                </div>
                <button type="button" onclick="closeDetailModal()" class="text-white hover:text-brand-yellow font-bold text-lg">&times;</button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-6 space-y-6 overflow-y-auto flex-grow text-xs text-slate-700 dark:text-slate-300 text-left">
                
                <!-- Grid: SPMB Admission Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-150 dark:border-slate-800">
                    <div class="verify-field-container p-1 rounded-lg border border-transparent">
                        <div class="flex items-center justify-between mb-0.5">
                            <span class="text-[9px] font-bold text-slate-400 uppercase block">Periode</span>
                            <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check hidden">
                                <input type="checkbox" data-field="spmb_period_id" data-label="Tahun Ajaran" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                                <span>OK</span>
                            </label>
                        </div>
                        <span id="det-period" class="font-bold text-slate-700 dark:text-slate-300">-</span>
                    </div>
                    <div class="verify-field-container p-1 rounded-lg border border-transparent">
                        <div class="flex items-center justify-between mb-0.5">
                            <span class="text-[9px] font-bold text-slate-400 uppercase block">Gelombang</span>
                            <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check hidden">
                                <input type="checkbox" data-field="spmb_wave_id" data-label="Gelombang" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                                <span>OK</span>
                            </label>
                        </div>
                        <span id="det-wave" class="font-bold text-slate-700 dark:text-slate-300">-</span>
                    </div>
                    <div class="verify-field-container p-1 rounded-lg border border-transparent">
                        <div class="flex items-center justify-between mb-0.5">
                            <span class="text-[9px] font-bold text-slate-400 uppercase block">Jalur Masuk</span>
                            <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check hidden">
                                <input type="checkbox" data-field="spmb_type_id" data-label="Jalur Pendaftaran" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                                <span>OK</span>
                            </label>
                        </div>
                        <span id="det-type" class="font-bold text-slate-700 dark:text-slate-300">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Status Berkas</span>
                        <span id="det-status" class="inline-block mt-0.5 px-2 py-0.5 rounded text-[9px] font-bold uppercase">SUBMITTED</span>
                    </div>
                </div>

                <!-- Segment 1: Personal Information -->
                <div class="space-y-3">
                    <h4 class="font-extrabold text-sm text-brand-emerald dark:text-emerald-400 border-b border-slate-100 dark:border-slate-800 pb-1.5 flex items-center gap-1.5">
                        <i data-lucide="info" class="w-4 h-4"></i> Biodata Calon Siswa
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="verify-field-container p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/20">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase block">Nama Lengkap</span>
                                <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check hidden">
                                    <input type="checkbox" data-field="candidate_name" data-label="Nama Lengkap" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                                    <span>OK</span>
                                </label>
                            </div>
                            <span id="det-name" class="font-semibold text-slate-800 dark:text-slate-200">-</span>
                        </div>
                        <div class="verify-field-container p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/20">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase block">Nama Panggilan</span>
                                <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check hidden">
                                    <input type="checkbox" data-field="nickname" data-label="Nama Panggilan" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                                    <span>OK</span>
                                </label>
                            </div>
                            <span id="det-nickname" class="font-semibold text-slate-800 dark:text-slate-200">-</span>
                        </div>
                        <div class="verify-field-container p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/20">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase block">NIK (Nomor Induk Kependudukan)</span>
                                <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check hidden">
                                    <input type="checkbox" data-field="nik" data-label="NIK" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                                    <span>OK</span>
                                </label>
                            </div>
                            <span id="det-nik" class="font-mono text-slate-800 dark:text-slate-200">-</span>
                        </div>
                        <div class="verify-field-container p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/20">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase block">Jenis Kelamin</span>
                                <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check hidden">
                                    <input type="checkbox" data-field="gender" data-label="Jenis Kelamin" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                                    <span>OK</span>
                                </label>
                            </div>
                            <span id="det-gender" class="font-semibold text-slate-800 dark:text-slate-200">-</span>
                        </div>
                        <div class="verify-field-container p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/20">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase block">Tempat, Tanggal Lahir</span>
                                <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check hidden">
                                    <input type="checkbox" data-field="birth_place" data-label="Tempat/Tanggal Lahir" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                                    <span>OK</span>
                                </label>
                            </div>
                            <span id="det-birth" class="font-semibold text-slate-800 dark:text-slate-200">-</span>
                        </div>
                        <div class="verify-field-container p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/20">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase block">Agama</span>
                                <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check hidden">
                                    <input type="checkbox" data-field="religion" data-label="Agama" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                                    <span>OK</span>
                                </label>
                            </div>
                            <span id="det-religion" class="font-semibold text-slate-800 dark:text-slate-200">-</span>
                        </div>
                        <div class="verify-field-container p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/20">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase block">Asal Sekolah (TK/PAUD)</span>
                                <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check hidden">
                                    <input type="checkbox" data-field="previous_school" data-label="Asal Sekolah" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald font-sans">
                                    <span>OK</span>
                                </label>
                            </div>
                            <span id="det-previous-school" class="font-semibold text-slate-800 dark:text-slate-200">-</span>
                        </div>
                        <div class="verify-field-container p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/20">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase block">Tingkat Pendaftaran</span>
                                <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check hidden">
                                    <input type="checkbox" data-field="admission_level" data-label="Tingkat Pendaftaran" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                                    <span>OK</span>
                                </label>
                            </div>
                            <span id="det-level" class="font-bold text-slate-800 dark:text-slate-200">-</span>
                        </div>
                        <div class="verify-field-container p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/20">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase block">Program Kelas</span>
                                <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check hidden">
                                    <input type="checkbox" data-field="spmb_class_program_id" data-label="Program Kelas" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                                    <span>OK</span>
                                </label>
                            </div>
                            <span id="det-program" class="font-bold text-brand-emerald dark:text-emerald-400">-</span>
                        </div>
                        <div class="md:col-span-2 bg-slate-50 dark:bg-slate-950/20 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 verify-field-container">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase block">Layanan Tambahan (Non-Formal)</span>
                                <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check hidden">
                                    <input type="checkbox" data-field="extra_services" data-label="Layanan Tambahan" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                                    <span>OK</span>
                                </label>
                            </div>
                            <span id="det-extras" class="font-bold text-slate-800 dark:text-slate-200">-</span>
                        </div>
                    </div>
                </div>

                <!-- Segment 2: Parent Information -->
                <div class="space-y-3 pt-2">
                    <h4 class="font-extrabold text-sm text-brand-emerald dark:text-emerald-400 border-b border-slate-100 dark:border-slate-800 pb-1.5 flex items-center gap-1.5">
                        <i data-lucide="users" class="w-4 h-4"></i> Data Orang Tua / Wali
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="verify-field-container p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/20">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase block">Nama Ayah Kandung</span>
                                <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check hidden">
                                    <input type="checkbox" data-field="father_name" data-label="Nama Ayah" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                                    <span>OK</span>
                                </label>
                            </div>
                            <span id="det-father" class="font-semibold text-slate-800 dark:text-slate-200">-</span>
                        </div>
                        <div class="verify-field-container p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/20">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase block">Nama Ibu Kandung</span>
                                <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check hidden">
                                    <input type="checkbox" data-field="mother_name" data-label="Nama Ibu" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                                    <span>OK</span>
                                </label>
                            </div>
                            <span id="det-mother" class="font-semibold text-slate-800 dark:text-slate-200">-</span>
                        </div>
                        <div class="verify-field-container p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/20">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[9px] font-bold text-slate-400 uppercase block">No. HP Wali (WhatsApp)</span>
                                <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check hidden">
                                    <input type="checkbox" data-field="parent_phone" data-label="No. HP Wali" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                                    <span>OK</span>
                                </label>
                            </div>
                            <span id="det-phone" class="font-mono text-slate-800 dark:text-slate-200">-</span>
                        </div>
                    </div>
                </div>

                <!-- Segment 3: Uploaded Documents -->
                <div class="space-y-3 pt-2">
                    <h4 class="font-extrabold text-sm text-brand-emerald dark:text-emerald-400 border-b border-slate-100 dark:border-slate-800 pb-1.5 flex items-center gap-1.5">
                        <i data-lucide="file-text" class="w-4 h-4"></i> Dokumen Persyaratan
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div id="det-cert-box" class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 flex items-center justify-between verify-field-container">
                            <div class="flex items-center gap-2">
                                <i data-lucide="file-digit" class="w-6 h-6 text-brand-emerald"></i>
                                <div>
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-350 block">Akta Kelahiran</span>
                                        <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check hidden">
                                            <input type="checkbox" data-field="birth_certificate_path" data-label="Scan Akta Kelahiran" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                                            <span>OK</span>
                                        </label>
                                    </div>
                                    <span class="text-[9px] text-slate-400">PDF/Gambar Asli</span>
                                </div>
                            </div>
                            <a id="det-cert-link" href="#" target="_blank" class="bg-brand-emerald hover-emerald text-white px-2.5 py-1 rounded text-[9px] font-bold transition font-sans">
                                Buka File
                            </a>
                        </div>
                        <div id="det-card-box" class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 flex items-center justify-between verify-field-container">
                            <div class="flex items-center gap-2">
                                <i data-lucide="file-digit" class="w-6 h-6 text-brand-emerald"></i>
                                <div>
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-350 block">Kartu Keluarga</span>
                                        <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check hidden">
                                            <input type="checkbox" data-field="family_card_path" data-label="Scan Kartu Keluarga" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald font-sans">
                                            <span>OK</span>
                                        </label>
                                    </div>
                                    <span class="text-[9px] text-slate-400">PDF/Gambar Asli</span>
                                </div>
                            </div>
                            <a id="det-card-link" href="#" target="_blank" class="bg-brand-emerald hover-emerald text-white px-2.5 py-1 rounded text-[9px] font-bold transition font-sans">
                                Buka File
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Textarea for Verification Message Notes (Dynamic Rejection Message) -->
                <div id="verification-notes-block" class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-2 hidden">
                    <label id="verification-notes-label" class="block text-xs font-bold text-slate-655 dark:text-slate-400 uppercase tracking-wider">Pesan Catatan Verifikasi</label>
                    <textarea id="verification-notes" name="notes" rows="4"
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-850 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs leading-relaxed"
                        placeholder="Masukkan catatan tambahan..."></textarea>
                </div>

            </div>            <!-- Modal Footer -->
            <div class="bg-slate-50 dark:bg-slate-950 border-t border-slate-100 dark:border-slate-800 px-6 py-4 flex justify-between items-center flex-shrink-0">
                <div>
                    <span class="text-[9px] font-bold text-slate-400 uppercase block">Tanggal Masuk Formulir</span>
                    <span id="det-created" class="text-xs font-semibold text-slate-650 dark:text-slate-350">20 Aug 2026, 03:00 WIB</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="closeDetailModal()" class="bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-xl text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" id="btn-reject" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-md hidden">
                        Tolak & Minta Perbaikan
                    </button>
                    <button type="submit" id="btn-approve" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-md hidden">
                        Setujui & Verifikasi Berkas
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Detailed Candidate Modal
    function openCandidateDetailModal(cand, isVerificationMode = false, regId = null) {
        document.getElementById('det-id-label').innerText = 'ID: ' + cand.id_label;
        document.getElementById('det-period').innerText = cand.period;
        document.getElementById('det-wave').innerText = cand.wave;
        document.getElementById('det-type').innerText = cand.type;
        
        // Status Badge Style
        const statusEl = document.getElementById('det-status');
        statusEl.innerText = cand.status;
        statusEl.className = "inline-block mt-0.5 px-2 py-0.5 rounded text-[9px] font-bold uppercase";
        if (cand.status === 'VERIFIED') {
            statusEl.classList.add('bg-green-50', 'text-green-700', 'border', 'border-green-200');
        } else if (cand.status === 'SUBMITTED') {
            statusEl.classList.add('bg-blue-50', 'text-blue-700', 'border', 'border-blue-200');
        } else {
            statusEl.classList.add('bg-slate-100', 'text-slate-600', 'border', 'border-slate-300');
        }

        document.getElementById('det-name').innerText = cand.name;
        document.getElementById('det-nickname').innerText = cand.nickname;
        document.getElementById('det-nik').innerText = cand.nik;
        document.getElementById('det-gender').innerText = cand.gender;
        document.getElementById('det-birth').innerText = cand.birth_place + ', ' + cand.birth_date;
        document.getElementById('det-religion').innerText = cand.religion;
        document.getElementById('det-previous-school').innerText = cand.previous_school;
        document.getElementById('det-level').innerText = cand.admission_level;
        document.getElementById('det-program').innerText = cand.class_program || 'Reguler';
        document.getElementById('det-extras').innerText = cand.extra_services;
        
        document.getElementById('det-father').innerText = cand.father_name;
        document.getElementById('det-mother').innerText = cand.mother_name;
        document.getElementById('det-phone').innerText = cand.parent_phone;
        
        document.getElementById('det-created').innerText = cand.created_at_label;

        // Akta File Box Link
        const certBox = document.getElementById('det-cert-box');
        const certLink = document.getElementById('det-cert-link');
        if (cand.birth_certificate) {
            certBox.classList.remove('opacity-50');
            certLink.href = cand.birth_certificate;
            certLink.style.display = 'inline-block';
        } else {
            certBox.classList.add('opacity-50');
            certLink.style.display = 'none';
        }

        // KK File Box Link
        const cardBox = document.getElementById('det-card-box');
        const cardLink = document.getElementById('det-card-link');
        if (cand.family_card) {
            cardBox.classList.remove('opacity-50');
            cardLink.href = cand.family_card;
            cardLink.style.display = 'inline-block';
        } else {
            cardBox.classList.add('opacity-50');
            cardLink.style.display = 'none';
        }

        // Toggle verification elements
        const verifyForm = document.getElementById('verifyForm');
        const titleLabel = document.getElementById('det-title-label');
        const btnApprove = document.getElementById('btn-approve');
        const btnReject = document.getElementById('btn-reject');
        const notesBlock = document.getElementById('verification-notes-block');
        const verificationChecks = document.querySelectorAll('.verification-check');
        const fieldContainers = document.querySelectorAll('.verify-field-container');

        // Reset check boxes state & layout styling classes
        document.querySelectorAll('.verification-check input[type="checkbox"]').forEach(cb => {
            cb.checked = true;
        });
        fieldContainers.forEach(container => {
            container.classList.remove('bg-red-50', 'dark:bg-red-955/20', 'border-red-200');
        });

        if (isVerificationMode && regId) {
            verifyForm.dataset.regId = regId;
            titleLabel.innerText = 'Proses Verifikasi Data & Berkas Calon Siswa';
            btnApprove.classList.remove('hidden');
            btnReject.classList.remove('hidden');
            notesBlock.classList.remove('hidden');
            verificationChecks.forEach(el => el.classList.remove('hidden'));

            // Bind separate actions to buttons dynamically using formaction
            btnApprove.setAttribute('formaction', `/admin/registrations/${regId}/verify`);
            btnReject.setAttribute('formaction', `/admin/registrations/${regId}/reject`);

            updateVerificationSummary();
        } else {
            titleLabel.innerText = 'Detail Data Pendaftar';
            btnApprove.classList.add('hidden');
            btnReject.classList.add('hidden');
            notesBlock.classList.add('hidden');
            verificationChecks.forEach(el => el.classList.add('hidden'));
        }

        document.getElementById('detailModal').classList.remove('hidden');
        
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
    }

    // Close modal by clicking outside
    document.getElementById('detailModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDetailModal();
        }
    });

    // Attach event listeners to validation checkboxes
    document.querySelectorAll('.verification-check input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', function() {
            const container = this.closest('.verify-field-container');
            if (container) {
                if (this.checked) {
                    container.classList.remove('bg-red-50', 'dark:bg-red-955/20', 'border-red-200');
                } else {
                    container.classList.add('bg-red-50', 'dark:bg-red-955/20', 'border-red-200');
                }
            }
            updateVerificationSummary();
        });
    });

    function updateVerificationSummary() {
        const unchecked = [];
        const labels = [];
        document.querySelectorAll('.verification-check input[type="checkbox"]').forEach(cb => {
            if (!cb.checked) {
                unchecked.push(cb.getAttribute('data-field'));
                labels.push(cb.getAttribute('data-label'));
            }
        });

        const notesTextarea = document.getElementById('verification-notes');
        const notesLabel = document.getElementById('verification-notes-label');
        const invalidFieldsInput = document.getElementById('invalid_fields_input');
        const candidateName = document.getElementById('det-name').innerText;
        const btnApprove = document.getElementById('btn-approve');
        const btnReject = document.getElementById('btn-reject');

        invalidFieldsInput.value = JSON.stringify(unchecked);

        if (unchecked.length > 0) {
            notesLabel.innerText = 'Alasan Penolakan / Perbaikan Berkas';
            
            // Auto-generate helper rejection text
            let compiledMsg = `Mohon maaf, berkas pendaftaran ananda ${candidateName} perlu diperbaiki pada bagian:\n`;
            labels.forEach(lbl => {
                compiledMsg += `- ${lbl}\n`;
            });
            compiledMsg += `\nSilakan perbaiki data tersebut melalui portal pendaftar Menu Formulir agar dapat kami verifikasi kembali.`;
            notesTextarea.value = compiledMsg;

            // Prevent approval when fields are unchecked (failed validation)
            if (btnApprove) btnApprove.classList.add('hidden');
            if (btnReject) btnReject.classList.remove('hidden');
        } else {
            notesLabel.innerText = 'Catatan Penutup Verifikasi (Opsional)';
            notesTextarea.value = `Alhamdulillah, berkas pendaftaran ananda ${candidateName} telah kami terima dan diverifikasi. Silakan persiapkan untuk mengikuti Tes Observasi.`;

            // Only allow approval when all fields are checked OK
            if (btnApprove) btnApprove.classList.remove('hidden');
            if (btnReject) btnReject.classList.add('hidden');
        }
    }
</script>
@endsection
