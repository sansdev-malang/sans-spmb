@extends('layouts.admin')

@section('title', 'Data Pendaftar - Admin Panel')
@section('page_title', 'Data Pendaftar')

@section('content')
<div class="space-y-6">
    <!-- Header Summary Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800">Daftar Lengkap Pendaftar</h1>
            <p class="text-xs text-slate-500 mt-1">Menampilkan biodata lengkap calon pendaftar yang sedang masuk dalam sistem pendaftaran Sekolah Anak Saleh.</p>
        </div>
        <div class="flex gap-2">
            <button class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                📥 Ekspor Excel
            </button>
            <button class="border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition">
                🖨️ Cetak PDF
            </button>
        </div>
    </div>

    <!-- Candidate List Table -->
    <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                        <th class="py-4 px-6">ID Pendaftaran</th>
                        <th class="py-4 px-6">Nama Lengkap / NIK</th>
                        <th class="py-4 px-6">Tempat & Tanggal Lahir</th>
                        <th class="py-4 px-6">Tingkat</th>
                        <th class="py-4 px-6">Tanggal Pendaftaran</th>
                        <th class="py-4 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    @forelse($candidates as $cand)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-6 font-mono text-xs text-slate-500">
                                SANS-2026-{{ str_pad($cand->id, 4, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800">{{ $cand->candidate_name }}</div>
                                <div class="text-[10px] text-slate-400">NIK: {{ $cand->nik }}</div>
                            </td>
                            <td class="py-4 px-6 text-slate-600 font-medium">
                                {{ $cand->birth_place }}, {{ $cand->birth_date ? $cand->birth_date->format('d M Y') : '-' }}
                            </td>
                            <td class="py-4 px-6 font-semibold text-brand-emerald">
                                {{ $cand->admission_level }}
                            </td>
                            <td class="py-4 px-6 text-slate-500 text-xs font-semibold">
                                {{ $cand->created_at->format('d M Y, H:i') }} WIB
                            </td>
                            <td class="py-4 px-6 text-center">
                                @php
                                    // Prepare JSON data to pass to Javascript modal
                                    $candJson = [
                                        'id_label' => 'SANS-2026-' . str_pad($cand->id, 4, '0', STR_PAD_LEFT),
                                        'name' => $cand->candidate_name,
                                        'nickname' => $cand->nickname ?? '-',
                                        'nik' => $cand->nik,
                                        'gender' => $cand->gender === 'male' ? 'Laki-laki' : 'Perempuan',
                                        'birth_place' => $cand->birth_place,
                                        'birth_date' => $cand->birth_date ? $cand->birth_date->format('d F Y') : '-',
                                        'religion' => $cand->religion,
                                        'previous_school' => $cand->previous_school ?? 'Tidak ada',
                                        'admission_level' => $cand->admission_level,
                                        'father_name' => $cand->father_name ?? '-',
                                        'mother_name' => $cand->mother_name ?? '-',
                                        'parent_phone' => $cand->parent_phone ?? '-',
                                        'birth_certificate' => $cand->birth_certificate_path ? asset('storage/' . $cand->birth_certificate_path) : null,
                                        'family_card' => $cand->family_card_path ? asset('storage/' . $cand->family_card_path) : null,
                                        'created_at_label' => $cand->created_at->format('d M Y, H:i') . ' WIB',
                                        'status' => strtoupper($cand->registration_status),
                                        'payment_status' => strtoupper($cand->payment_status),
                                        'period' => $cand->period->year ?? '-',
                                        'wave' => $cand->wave->name ?? '-',
                                        'type' => $cand->type->name ?? '-',
                                    ];
                                @endphp
                                <button type="button" 
                                    onclick="openCandidateDetailModal({{ json_encode($candJson) }})" 
                                    class="bg-brand-emerald hover-emerald text-white px-3 py-1.5 rounded-lg text-[10px] font-bold shadow-sm transition flex items-center gap-1 mx-auto">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i> Lihat Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-6 text-center text-slate-400">
                                Belum ada calon siswa yang melengkapi biodata.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($candidates->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                {{ $candidates->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Candidate Detail Modal Overlay -->
<div id="detailModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden shadow-2xl border border-slate-100 flex flex-col">
        <!-- Modal Header -->
        <div class="bg-brand-emerald text-white px-6 py-4 flex items-center justify-between flex-shrink-0">
            <div>
                <h3 class="font-extrabold text-base flex items-center gap-2">
                    <i data-lucide="user" class="w-5 h-5 text-brand-yellow"></i>
                    Detail Data Pendaftar
                </h3>
                <p id="det-id-label" class="text-[10px] text-emerald-100 font-mono mt-0.5">ID: SANS-2026-0000</p>
            </div>
            <button onclick="closeDetailModal()" class="text-white hover:text-brand-yellow font-bold text-lg">&times;</button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-6 space-y-6 overflow-y-auto flex-grow text-xs text-slate-700">
            
            <!-- Grid: SPMB Admission Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-150">
                <div>
                    <span class="text-[9px] font-bold text-slate-400 uppercase block">Periode</span>
                    <span id="det-period" class="font-bold text-slate-700">2024-2025</span>
                </div>
                <div>
                    <span class="text-[9px] font-bold text-slate-400 uppercase block">Gelombang</span>
                    <span id="det-wave" class="font-bold text-slate-700">Gelombang 1</span>
                </div>
                <div>
                    <span class="text-[9px] font-bold text-slate-400 uppercase block">Jalur Masuk</span>
                    <span id="det-type" class="font-bold text-slate-700">Reguler</span>
                </div>
                <div>
                    <span class="text-[9px] font-bold text-slate-400 uppercase block">Status Berkas</span>
                    <span id="det-status" class="inline-block mt-0.5 px-2 py-0.5 rounded text-[9px] font-bold uppercase">SUBMITTED</span>
                </div>
            </div>

            <!-- Segment 1: Personal Information -->
            <div class="space-y-3">
                <h4 class="font-extrabold text-sm text-brand-emerald border-b border-slate-100 pb-1.5 flex items-center gap-1.5">
                    <i data-lucide="info" class="w-4 h-4"></i> Biodata Calon Siswa
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Nama Lengkap</span>
                        <span id="det-name" class="font-semibold text-slate-800">Ahmad Raihan</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Nama Panggilan</span>
                        <span id="det-nickname" class="font-semibold text-slate-800">Raihan</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">NIK (Nomor Induk Kependudukan)</span>
                        <span id="det-nik" class="font-mono text-slate-800">3578091234560002</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Jenis Kelamin</span>
                        <span id="det-gender" class="font-semibold text-slate-800">Laki-laki</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Tempat, Tanggal Lahir</span>
                        <span id="det-birth" class="font-semibold text-slate-800">Malang, 12 Oktober 2018</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Agama</span>
                        <span id="det-religion" class="font-semibold text-slate-800">Islam</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Asal Sekolah (TK/PAUD)</span>
                        <span id="det-previous-school" class="font-semibold text-slate-800">TK Anak Saleh</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Tingkat Pendaftaran</span>
                        <span id="det-level" class="font-bold text-slate-800">SD Kelas 1</span>
                    </div>
                </div>
            </div>

            <!-- Segment 2: Parent Information -->
            <div class="space-y-3 pt-2">
                <h4 class="font-extrabold text-sm text-brand-emerald border-b border-slate-100 pb-1.5 flex items-center gap-1.5">
                    <i data-lucide="users" class="w-4 h-4"></i> Data Orang Tua / Wali
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Nama Ayah Kandung</span>
                        <span id="det-father" class="font-semibold text-slate-800">Budi Santoso</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Nama Ibu Kandung</span>
                        <span id="det-mother" class="font-semibold text-slate-800">Siti Aminah</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">No. HP Wali (WhatsApp)</span>
                        <span id="det-phone" class="font-mono text-slate-800">081234567890</span>
                    </div>
                </div>
            </div>

            <!-- Segment 3: Uploaded Documents -->
            <div class="space-y-3 pt-2">
                <h4 class="font-extrabold text-sm text-brand-emerald border-b border-slate-100 pb-1.5 flex items-center gap-1.5">
                    <i data-lucide="file-text" class="w-4 h-4"></i> Dokumen Persyaratan
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div id="det-cert-box" class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="file-digit" class="w-6 h-6 text-brand-emerald"></i>
                            <div>
                                <span class="text-[10px] font-bold text-slate-700 block">Akta Kelahiran</span>
                                <span class="text-[9px] text-slate-400">PDF/Gambar Asli</span>
                            </div>
                        </div>
                        <a id="det-cert-link" href="#" target="_blank" class="bg-brand-emerald hover-emerald text-white px-2.5 py-1 rounded text-[9px] font-bold transition">
                            Buka File
                        </a>
                    </div>
                    <div id="det-card-box" class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i data-lucide="file-digit" class="w-6 h-6 text-brand-emerald"></i>
                            <div>
                                <span class="text-[10px] font-bold text-slate-700 block">Kartu Keluarga</span>
                                <span class="text-[9px] text-slate-400">PDF/Gambar Asli</span>
                            </div>
                        </div>
                        <a id="det-card-link" href="#" target="_blank" class="bg-brand-emerald hover-emerald text-white px-2.5 py-1 rounded text-[9px] font-bold transition">
                            Buka File
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div class="bg-slate-50 border-t border-slate-100 px-6 py-4 flex justify-between items-center flex-shrink-0">
            <div>
                <span class="text-[9px] font-bold text-slate-400 uppercase block">Tanggal Masuk Formulir</span>
                <span id="det-created" class="text-[10px] font-semibold text-slate-600">20 Aug 2026, 03:00 WIB</span>
            </div>
            <button onclick="closeDetailModal()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition">
                Tutup Detail
            </button>
        </div>
    </div>
</div>

<script>
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
