@extends('layouts.admin')

@section('title', 'Verifikasi Pendaftaran Calon Murid - Portal SPMB')
@section('page_title', 'Verifikasi Pendaftaran')

@section('content')
<div class="space-y-8">
    
    <!-- Header Summary Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 dark:text-white">Verifikasi Pendaftaran Calon Murid</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola review formulir, status berkas, dan validasi data calon murid dari satu tempat.</p>
        </div>
    </div>

    <!-- Candidate List Table -->
    <div id="candidate-card" class="bg-white dark:bg-slate-900 rounded-2xl shadow-md border border-slate-100 dark:border-slate-800 overflow-hidden" hx-boost="true" hx-target="#candidate-card" hx-select="#candidate-card">
        <div class="bg-slate-50 dark:bg-slate-950 border-b border-slate-100 dark:border-slate-800 px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Daftar Pendaftaran Calon Murid</span>
            
            <!-- Quick Filter Tabs with Counter Badges -->
            <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
                <!-- Tab: Semua -->
                <a href="{{ route('admin.verification', request()->except(['status', 'page'])) }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all duration-150 {{ !request()->filled('status') ? 'bg-brand-emerald text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-650 dark:text-slate-350 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700/80' }}">
                    <span>Semua</span>
                    <span class="px-1.5 py-0.2 rounded-md text-[10px] font-extrabold {{ !request()->filled('status') ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300' }}">
                        {{ $tabCounts['all'] ?? 0 }}
                    </span>
                </a>

                @php
                    $tabDefinitions = [
                        'submitted' => ['label' => 'Perlu Review', 'badge_inactive' => 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400'],
                        'verified' => ['label' => 'Terverifikasi', 'badge_inactive' => 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400'],
                        'taaruf_completed' => ['label' => 'Ta\'aruf Selesai', 'badge_inactive' => 'bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400'],
                        'agreement_signed' => ['label' => 'Persetujuan', 'badge_inactive' => 'bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-400'],
                        'completed' => ['label' => 'Lulus', 'badge_inactive' => 'bg-green-100 dark:bg-green-950/60 text-green-700 dark:text-green-400'],
                        'failed' => ['label' => 'Ditolak', 'badge_inactive' => 'bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400'],
                    ];
                @endphp

                @foreach($tabDefinitions as $statusVal => $meta)
                    @php
                        $isActive = request('status') === $statusVal;
                        $count = $tabCounts[$statusVal] ?? 0;
                    @endphp
                    <a href="{{ route('admin.verification', array_merge(request()->except(['page']), ['status' => $statusVal])) }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all duration-150 {{ $isActive ? 'bg-brand-emerald text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-650 dark:text-slate-350 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700/80' }}">
                        <span>{{ $meta['label'] }}</span>
                        <span class="px-1.5 py-0.2 rounded-md text-[10px] font-extrabold {{ $isActive ? 'bg-white/20 text-white' : ($count > 0 ? $meta['badge_inactive'] : 'bg-slate-100 dark:bg-slate-700 text-slate-400') }}">
                            {{ $count }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Search & Filter Form -->
        <form action="{{ route('admin.verification') }}" method="GET" class="p-6 bg-slate-50/50 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <!-- Search Input Container -->
                <div class="relative w-full md:w-80 flex items-center">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}" hidden class="hidden">
                    @endif
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
                        <th class="py-4 px-6">No. Registrasi</th>
                        <th class="py-4 px-6">Calon Murid</th>
                        <th class="py-4 px-6">Tingkat</th>
                        <th class="py-4 px-6">Berkas Upload</th>
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
                                'family_card_no' => $reg->getFieldValue('family_card_no') ?? '-',
                                'gender' => in_array($reg->gender, ['Laki-laki', 'male']) ? 'Laki-laki' : (in_array($reg->gender, ['Perempuan', 'female']) ? 'Perempuan' : ($reg->gender ?? '-')),
                                'birth_place' => $reg->birth_place ?? '-',
                                'birth_date' => $reg->birth_date ? $reg->birth_date->format('d F Y') : '-',
                                'religion' => $reg->religion ?? '-',
                                'previous_school' => $reg->previous_school ?? 'Tidak ada',
                                'admission_level' => $reg->admission_level ?? '-',
                                'class_program' => $reg->classProgram->name ?? 'Reguler',
                                
                                // Tempat Tinggal
                                'address' => $reg->getFieldValue('address') ?? '-',
                                'house_number' => $reg->getFieldValue('house_number') ?? '-',
                                'rt' => $reg->getFieldValue('rt') ?? '-',
                                'rw' => $reg->getFieldValue('rw') ?? '-',
                                'kelurahan' => $reg->getFieldValue('kelurahan') ?? '-',
                                'kecamatan' => $reg->getFieldValue('kecamatan') ?? '-',
                                'city' => $reg->getFieldValue('city') ?? '-',
                                'province' => $reg->getFieldValue('province') ?? '-',

                                // Data Orang Tua
                                'father_name' => $reg->father_name ?? '-',
                                'father_nik' => $reg->getFieldValue('father_nik') ?? '-',
                                'father_address' => $reg->getFieldValue('father_address') ?? '-',
                                'father_phone' => $reg->getFieldValue('father_phone') ?? $reg->parent_phone ?? '-',
                                'mother_name' => $reg->mother_name ?? '-',
                                'mother_nik' => $reg->getFieldValue('mother_nik') ?? '-',
                                'mother_address' => $reg->getFieldValue('mother_address') ?? '-',
                                'mother_phone' => $reg->getFieldValue('mother_phone') ?? '-',
                                'parent_phone' => $reg->parent_phone ?? '-',

                                // Data Wali
                                'guardian_name' => $reg->getFieldValue('guardian_name') ?? '-',
                                'guardian_nik' => $reg->getFieldValue('guardian_nik') ?? '-',
                                'guardian_address' => $reg->getFieldValue('guardian_address') ?? '-',
                                'guardian_phone' => $reg->getFieldValue('guardian_phone') ?? '-',

                                // Lampiran Dinamis
                                'documents' => $documentFields->map(function($df) use ($reg) {
                                    $val = $reg->getFieldValue($df->field_name);
                                    return [
                                        'field_name' => $df->field_name,
                                        'label' => $df->label,
                                        'is_required' => (bool)$df->is_required,
                                        'url' => $val ? Storage::url($val) : null,
                                    ];
                                })->values()->all(),

                                'student_photo' => $reg->getFieldValue('student_photo_path') ? asset('storage/' . $reg->getFieldValue('student_photo_path')) : null,
                                'birth_certificate' => $reg->birth_certificate_path ? asset('storage/' . $reg->birth_certificate_path) : null,
                                'family_card' => $reg->family_card_path ? asset('storage/' . $reg->family_card_path) : null,
                                'diploma_certificate' => $reg->getFieldValue('diploma_certificate_path') ? asset('storage/' . $reg->getFieldValue('diploma_certificate_path')) : null,
                                'student_card' => $reg->getFieldValue('student_card_path') ? asset('storage/' . $reg->getFieldValue('student_card_path')) : null,
                                'special_needs' => $reg->getFieldValue('special_needs_assessment_path') ? asset('storage/' . $reg->getFieldValue('special_needs_assessment_path')) : null,
                                'payment_receipt' => $reg->getFieldValue('payment_receipt_path') ? asset('storage/' . $reg->getFieldValue('payment_receipt_path')) : null,

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
                                @php
                                    $parentName = $reg->father_name 
                                        ?: ($reg->mother_name 
                                        ?: ($reg->guardian_name 
                                        ?: ($reg->user->name ?? '-')));

                                    $parentContact = $reg->parent_phone 
                                        ?: ($reg->father_phone 
                                        ?: ($reg->mother_phone 
                                        ?: ($reg->guardian_phone 
                                        ?: ($reg->getFieldValue('father_phone') 
                                        ?: ($reg->getFieldValue('mother_phone') 
                                        ?: ($reg->getFieldValue('guardian_phone') ?: null))))));
                                @endphp
                                <div class="text-xs text-slate-400 flex items-center flex-wrap gap-x-2 gap-y-0.5 mt-0.5">
                                    <span>Ortu: {{ $parentName }}</span>
                                    @if($parentContact)
                                        <span class="inline-flex items-center gap-1 text-slate-500 dark:text-slate-400">
                                            <i data-lucide="phone" class="w-3 h-3 text-emerald-500"></i>
                                            <span>{{ $parentContact }}</span>
                                        </span>
                                    @endif
                                </div>
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
                                @php
                                    $uploadedDocs = [];
                                    foreach ($documentFields as $df) {
                                        $fVal = $reg->getFieldValue($df->field_name);
                                        if ($fVal) {
                                            $uploadedDocs[] = [
                                                'label' => $df->label,
                                                'url' => Storage::url($fVal),
                                            ];
                                        }
                                    }
                                @endphp
                                @if(count($uploadedDocs) > 0)
                                    @foreach($uploadedDocs as $doc)
                                        <a href="{{ $doc['url'] }}" target="_blank" hx-boost="false" class="text-xs text-brand-emerald font-semibold hover:underline flex items-center gap-1 truncate max-w-[220px]" title="{{ $doc['label'] }}">
                                            <i data-lucide="file-digit" class="w-3.5 h-3.5 shrink-0"></i> 
                                            <span class="truncate">{{ $doc['label'] }}</span>
                                        </a>
                                    @endforeach
                                @else
                                    <span class="text-xs text-slate-400">Belum diunggah</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                    @if(in_array($reg->registration_status, ['verified', 'completed'])) bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-400 dark:border-emerald-800
                                    @elseif($reg->registration_status === 'submitted') bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/50 dark:text-amber-400 dark:border-amber-800
                                    @elseif($reg->registration_status === 'taaruf_completed') bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-950/50 dark:text-indigo-400 dark:border-indigo-800
                                    @elseif($reg->registration_status === 'agreement_signed') bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-950/50 dark:text-purple-400 dark:border-purple-800
                                    @elseif($reg->registration_status === 'failed') bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-950/50 dark:text-rose-400 dark:border-rose-800
                                    @elseif($reg->registration_status === 'draft') bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700
                                    @else bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 @endif">
                                    {{ $reg->registration_status === 'draft' ? 'Draft Formulir' : str_replace('_', ' ', $reg->registration_status) }}
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
                                        @if(!$reg->observation_date)
                                            <!-- Belum dijadwalkan: Tampilkan tombol Atur Jadwal Ta'aruf -->
                                            <a href="{{ route('admin.taaruf', ['unit_id' => $reg->spmb_unit_id, 'search' => $reg->candidate_name]) }}" hx-boost="false" class="bg-brand-emerald hover-emerald text-white font-bold text-xs px-2.5 py-1.5 rounded-lg shadow-sm transition flex items-center gap-1" title="Atur Jadwal Ta'aruf">
                                                <i data-lucide="calendar-plus" class="w-3.5 h-3.5"></i>
                                                <span>Jadwal Ta'aruf</span>
                                            </a>
                                        @else
                                            <!-- Sudah dijadwalkan: Tampilkan tombol Selesaikan Ta'aruf -->
                                            <form action="{{ route('admin.registrations.complete-taaruf', $reg->id) }}" method="POST" hx-boost="false" class="inline" onsubmit="return confirm('Selesaikan sesi Ta\'aruf ananda {{ addslashes($reg->candidate_name) }}?');">
                                                @csrf
                                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-2.5 py-1.5 rounded-lg shadow-sm transition flex items-center gap-1" title="Selesaikan Sesi Ta'aruf">
                                                    <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                                                    <span>Selesaikan Ta'aruf</span>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 px-6 text-center">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700/60 flex items-center justify-center text-slate-400 dark:text-slate-500 mb-3 shadow-2xs">
                                        <i data-lucide="clipboard-check" class="w-8 h-8 text-slate-400 dark:text-slate-400"></i>
                                    </div>
                                    <h4 class="text-sm font-extrabold text-slate-700 dark:text-slate-200 mb-1">
                                        Belum Ada Antrean Verifikasi
                                    </h4>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 leading-relaxed">
                                        Saat ini belum ada formulir pendaftaran yang masuk untuk diverifikasi, atau tidak ada data yang cocok dengan kriteria filter aktif.
                                    </p>
                                    @if(request()->has('search') || request()->has('status') || request()->has('unit_id'))
                                        <a href="{{ route('admin.verification') }}" class="mt-4 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold transition">
                                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                            <span>Reset Filter</span>
                                        </a>
                                    @endif
                                </div>
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
        
        <form id="verifyForm" method="POST" action="" hx-boost="false" class="flex flex-col h-full overflow-hidden">
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
                        <i data-lucide="info" class="w-4 h-4"></i> Biodata Calon Murid
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
                                <span class="text-[9px] font-bold text-slate-400 uppercase block">Kategori Murid</span>
                                <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check hidden">
                                    <input type="checkbox" data-field="spmb_class_program_id" data-label="Kategori Murid" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
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
                    <div id="det-documents-container" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Dynamically rendered via openCandidateDetailModal -->
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

        // Render dynamic documents
        const docsContainer = document.getElementById('det-documents-container');
        docsContainer.innerHTML = '';

        if (cand.documents && cand.documents.length > 0) {
            cand.documents.forEach(doc => {
                const box = document.createElement('div');
                box.className = `p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 flex items-center justify-between verify-field-container ${doc.url ? '' : 'opacity-60'}`;
                
                let actionHtml = '';
                if (doc.url) {
                    actionHtml = `<a href="${doc.url}" target="_blank" class="bg-brand-emerald hover-emerald text-white px-2.5 py-1 rounded text-[9px] font-bold transition font-sans shrink-0">Buka File</a>`;
                } else {
                    actionHtml = `<span class="text-[9px] font-semibold text-slate-400 italic shrink-0">Belum diunggah</span>`;
                }

                box.innerHTML = `
                    <div class="flex items-center gap-2.5 min-w-0 pr-2">
                        <i data-lucide="file-digit" class="w-6 h-6 text-brand-emerald shrink-0"></i>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate" title="${doc.label}">${doc.label}</span>
                                <label class="inline-flex items-center gap-1 cursor-pointer text-[9px] font-bold text-slate-400 hover:text-red-500 verification-check ${isVerificationMode ? '' : 'hidden'}">
                                    <input type="checkbox" data-field="${doc.field_name}" data-label="${doc.label}" checked class="w-3.5 h-3.5 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald font-sans">
                                    <span>OK</span>
                                </label>
                            </div>
                            <span class="text-[9px] text-slate-400 block truncate">${doc.url ? 'File Terlampir' : 'Berkas Belum Diunggah'}</span>
                        </div>
                    </div>
                    ${actionHtml}
                `;
                docsContainer.appendChild(box);
            });
        } else {
            docsContainer.innerHTML = '<p class="text-xs text-slate-400 italic col-span-2">Tidak ada dokumen persyaratan yang dikonfigurasi.</p>';
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
            titleLabel.innerText = 'Proses Verifikasi Data & Berkas Calon Murid';
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

    // Close on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDetailModal();
        }
    });

    // Attach delegated event listener to validation checkboxes
    document.addEventListener('change', function(e) {
        if (e.target && e.target.matches('.verification-check input[type="checkbox"]')) {
            const container = e.target.closest('.verify-field-container');
            if (container) {
                if (e.target.checked) {
                    container.classList.remove('bg-red-50', 'dark:bg-red-955/20', 'border-red-200');
                } else {
                    container.classList.add('bg-red-50', 'dark:bg-red-955/20', 'border-red-200');
                }
            }
            updateVerificationSummary();
        }
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
