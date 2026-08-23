@extends('layouts.admin')

@section('title', 'Verifikasi Data Calon Siswa - Portal SPMB')
@section('page_title', 'Verifikasi Data')

@section('content')
<div class="space-y-8">
    
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-extrabold leading-7 text-slate-900 sm:text-3xl sm:truncate dark:text-white">
                Verifikasi Data Calon Siswa
            </h2>
            <p class="text-xs text-brand-emerald font-semibold uppercase tracking-wider mt-1">
                Sekolah Anak Saleh • Panel Administrasi
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 gap-3">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-xl shadow-sm text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-750 transition">
                <i data-lucide="layout-dashboard" class="w-4 h-4 mr-1 text-slate-500"></i> Dashboard Utama
            </a>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-xl shadow-sm text-xs font-bold text-white bg-brand-emerald hover-emerald transition">
                <i data-lucide="monitor" class="w-4 h-4 mr-1 text-brand-yellow"></i> Portal Pendaftar
            </a>
        </div>
    </div>



    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <!-- Stat Item -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800">
            <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Total Pendaftar</span>
            <span class="text-2xl font-black text-slate-800 dark:text-white block mt-1">{{ $stats['total'] }}</span>
        </div>
        <!-- Stat Item -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 border-l-4 border-l-yellow-500">
            <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Perlu Review</span>
            <span class="text-2xl font-black text-yellow-600 block mt-1">{{ $stats['submitted'] }}</span>
        </div>
        <!-- Stat Item -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 border-l-4 border-l-green-500">
            <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Terverifikasi</span>
            <span class="text-2xl font-black text-green-600 block mt-1">{{ $stats['verified'] }}</span>
        </div>
        <!-- Stat Item -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 border-l-4 border-l-red-500">
            <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Ditolak / Gagal</span>
            <span class="text-2xl font-black text-red-600 block mt-1">{{ $stats['failed'] }}</span>
        </div>
        <!-- Stat Item -->
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 border-l-4 border-l-emerald-500">
            <span class="text-[10px] text-slate-400 font-bold block uppercase tracking-wider">Lunas Biaya</span>
            <span class="text-2xl font-black text-brand-emerald dark:text-emerald-400 block mt-1">{{ $stats['paid'] }}</span>
        </div>
    </div>

    <!-- Candidate List Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-md border border-slate-100 dark:border-slate-800 overflow-hidden">
        <div class="bg-slate-50 dark:bg-slate-950 border-b border-slate-100 dark:border-slate-800 px-6 py-4 flex justify-between items-center">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Daftar Pendaftaran Calon Siswa</span>
            
            <!-- Quick Filter Links -->
            <div class="flex flex-wrap gap-2 text-[10px] font-bold">
                <a href="{{ route('admin.verification') }}" class="px-2.5 py-1 rounded-full {{ !request()->has('status') ? 'bg-brand-emerald text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-650 dark:text-slate-350' }}">Semua</a>
                <a href="{{ route('admin.verification', ['status' => 'submitted']) }}" class="px-2.5 py-1 rounded-full {{ request()->status === 'submitted' ? 'bg-brand-emerald text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-650 dark:text-slate-350' }}">Perlu Review</a>
                <a href="{{ route('admin.verification', ['status' => 'verified']) }}" class="px-2.5 py-1 rounded-full {{ request()->status === 'verified' ? 'bg-brand-emerald text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-650 dark:text-slate-350' }}">Terverifikasi</a>
                <a href="{{ route('admin.verification', ['status' => 'taaruf_completed']) }}" class="px-2.5 py-1 rounded-full {{ request()->status === 'taaruf_completed' ? 'bg-brand-emerald text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-650 dark:text-slate-350' }}">Ta'aruf Selesai</a>
                <a href="{{ route('admin.verification', ['status' => 'agreement_signed']) }}" class="px-2.5 py-1 rounded-full {{ request()->status === 'agreement_signed' ? 'bg-brand-emerald text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-650 dark:text-slate-350' }}">Persetujuan</a>
                <a href="{{ route('admin.verification', ['status' => 'completed']) }}" class="px-2.5 py-1 rounded-full {{ request()->status === 'completed' ? 'bg-brand-emerald text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-650 dark:text-slate-350' }}">Lulus</a>
                <a href="{{ route('admin.verification', ['status' => 'failed']) }}" class="px-2.5 py-1 rounded-full {{ request()->status === 'failed' ? 'bg-brand-emerald text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-650 dark:text-slate-350' }}">Ditolak</a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50 dark:bg-slate-950/20">
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
                                'gender' => $reg->gender === 'male' ? 'Laki-laki' : ($reg->gender === 'female' ? 'Perempuan' : '-'),
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
                            <td class="py-4 px-6 font-mono text-xs text-slate-500">
                                SANS-{{ substr($reg->period->year ?? '2026', 0, 4) }}-{{ str_pad($reg->id, 4, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800 dark:text-slate-200">{{ $reg->candidate_name ?? 'Draft' }}</div>
                                <div class="text-[10px] text-slate-400">Ortu: {{ $reg->user->name }} ({{ $reg->parent_phone ?? '-' }})</div>
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
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                    @if($reg->payment_status === 'paid') bg-green-50 text-green-700 border border-green-200
                                    @elseif($reg->payment_status === 'pending') bg-yellow-50 text-yellow-700 border border-yellow-200
                                    @else bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 @endif">
                                    {{ $reg->payment_status }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
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
                                    <!-- Detail Modal Trigger -->
                                    <button type="button" 
                                        onclick="openCandidateDetailModal({{ json_encode($candJson) }})" 
                                        class="bg-brand-emerald hover-emerald text-white px-2.5 py-1.5 rounded-lg text-[10px] font-bold shadow-sm transition flex items-center gap-1">
                                        <i data-lucide="eye" class="w-3 h-3"></i> Detail
                                    </button>
 
                                    @if ($reg->registration_status === 'submitted')
                                        <!-- Quick Verify Form -->
                                        <form action="{{ route('admin.registrations.verify', $reg->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="bg-green-650 hover:bg-green-750 text-white font-bold text-[10px] px-2.5 py-1.5 rounded-lg shadow-sm transition">
                                                Setujui
                                            </button>
                                        </form>
 
                                        <!-- Reject Trigger (shows custom reason input dialog) -->
                                        <button onclick="toggleRejectModal({{ $reg->id }}, '{{ $reg->candidate_name }}')" class="bg-rose-600 hover:bg-rose-700 text-white font-bold text-[10px] px-2.5 py-1.5 rounded-lg shadow-sm transition">
                                            Tolak
                                        </button>
                                    @endif
 
                                    @if ($reg->registration_status === 'verified')
                                        <!-- Selesaikan Ta'aruf -->
                                        <form action="{{ route('admin.registrations.complete-taaruf', $reg->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-[10px] px-2.5 py-1.5 rounded-lg shadow-sm transition flex items-center gap-1">
                                                Selesaikan Ta'aruf
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 px-6 text-center text-slate-400">
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

<!-- Modal 1: Reject Dialog -->
<div id="rejectModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden">
        <div class="bg-red-600 text-white px-6 py-4">
            <h3 class="font-extrabold text-lg">Tolak Pendaftaran</h3>
            <p class="text-xs text-red-100 mt-0.5">Berikan alasan mengapa berkas pendaftaran calon siswa ditolak.</p>
        </div>
        <form id="rejectForm" method="POST" class="p-6 space-y-4 text-left">
            @csrf
            <div>
                <span class="text-xs text-slate-400 font-bold block uppercase mb-1">Calon Siswa</span>
                <span id="rejectCandidateName" class="font-extrabold text-slate-800 dark:text-white text-sm"></span>
            </div>
            <div>
                <label for="reason" class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">Alasan Penolakan*</label>
                <textarea id="reason" name="reason" required rows="4"
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500 text-sm"
                    placeholder="Contoh: Berkas Akta Kelahiran buram dan tidak terbaca. Harap unggah ulang berkas yang lebih jelas."></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeRejectModal()" class="border border-slate-300 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 px-4 py-2 rounded-xl text-xs font-bold transition">
                    Kembali
                </button>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-xl text-xs font-bold transition shadow-md">
                    Kirim Alasan Penolakan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Candidate Detail Modal Overlay -->
<div id="detailModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden shadow-2xl border border-slate-100 dark:border-slate-800 flex flex-col">
        <!-- Modal Header -->
        <div class="bg-brand-emerald text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
            <div>
                <h3 class="font-extrabold text-base flex items-center gap-2">
                    <i data-lucide="user" class="w-5 h-5 text-brand-yellow"></i>
                    Detail Data Pendaftar
                </h3>
                <p id="det-id-label" class="text-[10px] text-emerald-100 font-mono mt-0.5">ID: SANS-YYYY-XXXX</p>
            </div>
            <button onclick="closeDetailModal()" class="text-white hover:text-brand-yellow font-bold text-lg">&times;</button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-6 space-y-6 overflow-y-auto flex-grow text-xs text-slate-700 dark:text-slate-300 text-left">
            
            <!-- Grid: SPMB Admission Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-150 dark:border-slate-800">
                <div>
                    <span class="text-[9px] font-bold text-slate-400 uppercase block">Periode</span>
                    <span id="det-period" class="font-bold text-slate-700 dark:text-slate-300">-</span>
                </div>
                <div>
                    <span class="text-[9px] font-bold text-slate-400 uppercase block">Gelombang</span>
                    <span id="det-wave" class="font-bold text-slate-700 dark:text-slate-300">-</span>
                </div>
                <div>
                    <span class="text-[9px] font-bold text-slate-400 uppercase block">Jalur Masuk</span>
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
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Nama Lengkap</span>
                        <span id="det-name" class="font-semibold text-slate-800 dark:text-slate-200">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Nama Panggilan</span>
                        <span id="det-nickname" class="font-semibold text-slate-800 dark:text-slate-200">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">NIK (Nomor Induk Kependudukan)</span>
                        <span id="det-nik" class="font-mono text-slate-800 dark:text-slate-200">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Jenis Kelamin</span>
                        <span id="det-gender" class="font-semibold text-slate-800 dark:text-slate-200">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Tempat, Tanggal Lahir</span>
                        <span id="det-birth" class="font-semibold text-slate-800 dark:text-slate-200">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Agama</span>
                        <span id="det-religion" class="font-semibold text-slate-800 dark:text-slate-200">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Asal Sekolah (TK/PAUD)</span>
                        <span id="det-previous-school" class="font-semibold text-slate-800 dark:text-slate-200">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Tingkat Pendaftaran</span>
                        <span id="det-level" class="font-bold text-slate-800 dark:text-slate-200">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Program Kelas</span>
                        <span id="det-program" class="font-bold text-brand-emerald dark:text-emerald-400">-</span>
                    </div>
                    <div class="md:col-span-2 bg-slate-50 dark:bg-slate-950 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800">
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Layanan Tambahan (Non-Formal)</span>
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
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Nama Ayah Kandung</span>
                        <span id="det-father" class="font-semibold text-slate-800 dark:text-slate-200">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Nama Ibu Kandung</span>
                        <span id="det-mother" class="font-semibold text-slate-800 dark:text-slate-200">-</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">No. HP Wali (WhatsApp)</span>
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
                    <div id="det-cert-box" class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="file-digit" class="w-6 h-6 text-brand-emerald"></i>
                            <div>
                                <span class="text-[10px] font-bold text-slate-700 dark:text-slate-350 block">Akta Kelahiran</span>
                                <span class="text-[9px] text-slate-400">PDF/Gambar Asli</span>
                            </div>
                        </div>
                        <a id="det-cert-link" href="#" target="_blank" class="bg-brand-emerald hover-emerald text-white px-2.5 py-1 rounded text-[9px] font-bold transition font-sans">
                            Buka File
                        </a>
                    </div>
                    <div id="det-card-box" class="p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="file-digit" class="w-6 h-6 text-brand-emerald"></i>
                            <div>
                                <span class="text-[10px] font-bold text-slate-700 dark:text-slate-350 block">Kartu Keluarga</span>
                                <span class="text-[9px] text-slate-400">PDF/Gambar Asli</span>
                            </div>
                        </div>
                        <a id="det-card-link" href="#" target="_blank" class="bg-brand-emerald hover-emerald text-white px-2.5 py-1 rounded text-[9px] font-bold transition font-sans">
                            Buka File
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="bg-slate-50 dark:bg-slate-950 border-t border-slate-100 dark:border-slate-800 px-6 py-4 flex justify-between items-center flex-shrink-0">
            <div>
                <span class="text-[9px] font-bold text-slate-400 uppercase block">Tanggal Masuk Formulir</span>
                <span id="det-created" class="text-[10px] font-semibold text-slate-650 dark:text-slate-350">20 Aug 2026, 03:00 WIB</span>
            </div>
            <button onclick="closeDetailModal()" class="bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-xl text-xs font-bold transition">
                Tutup Detail
            </button>
        </div>
    </div>
</div>

<script>
    function toggleRejectModal(id, name) {
        const modal = document.getElementById('rejectModal');
        const form = document.getElementById('rejectForm');
        const nameSpan = document.getElementById('rejectCandidateName');
        
        nameSpan.innerText = name;
        form.action = `/admin/registrations/${id}/reject`;
        
        modal.classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }

    // Detailed Candidate Modal
    function openCandidateDetailModal(cand) {
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

        document.getElementById('detailModal').classList.remove('hidden');
        
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
    }
</script>
@endsection
