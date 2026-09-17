@extends('layouts.admin')

@section('title', 'Master Unit & Tingkatan SPMB - Admin SANS')
@section('page_title', 'Master Unit & Tingkatan')

@section('content')
<div id="spmb-units-container" hx-boost="true" hx-target="#spmb-units-container" hx-select="#spmb-units-container" class="w-full space-y-6">
    
    <!-- Top Header with Unit Filter -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                <i data-lucide="building-2" class="w-6 h-6 text-brand-emerald"></i>
                Master Unit & Tingkatan
            </h1>
            <p class="text-xs text-slate-500 mt-1">Kelola data master unit sekolah dan tingkatan kelas untuk penerimaan murid baru.</p>
        </div>

        <!-- Unit Filter Switcher -->
        <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200/80 p-1.5 rounded-2xl shadow-xs self-start md:self-auto overflow-x-auto">
            <span class="text-xs font-extrabold text-slate-500 flex items-center gap-1.5 px-2 whitespace-nowrap">
                <i data-lucide="filter" class="w-3.5 h-3.5 text-brand-emerald"></i>
                Unit:
            </span>
            <button type="button" onclick="filterByUnit('')" id="unitFilterBtn-all" class="unit-filter-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ ($selectedUnitId ?? '') === '' ? 'bg-brand-emerald text-white shadow-xs' : 'bg-white text-slate-650 hover:bg-slate-100 border border-slate-200/60' }} cursor-pointer">
                <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                Semua Unit
            </button>
            @foreach($units as $u)
                <button type="button" onclick="filterByUnit('{{ $u->id }}')" id="unitFilterBtn-{{ $u->id }}" class="unit-filter-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ ($selectedUnitId ?? '') == $u->id ? 'bg-brand-emerald text-white shadow-xs' : 'bg-white text-slate-650 hover:bg-slate-100 border border-slate-200/60' }} cursor-pointer">
                    <span>{{ strtoupper($u->code ?? $u->name) }}</span>
                </button>
            @endforeach
        </div>
    </div>

    @php
        $activeTab = $activeTab ?? request()->input('tab', 'unit');
    @endphp
    <!-- Tab Navigation -->
    <div class="flex flex-wrap gap-2 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
            <button id="tabBtn-unit" onclick="switchTab('unit')" class="tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'unit' ? 'bg-brand-emerald text-white shadow' : 'text-slate-600 hover:bg-slate-50' }} cursor-pointer">
                <i data-lucide="building-2" class="w-4 h-4"></i> Unit Sekolah
            </button>
            <button id="tabBtn-grade" onclick="switchTab('grade')" class="tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'grade' ? 'bg-brand-emerald text-white shadow' : 'text-slate-600 hover:bg-slate-50' }} cursor-pointer">
                <i data-lucide="layers" class="w-4 h-4"></i> Tingkatan Kelas
            </button>
            <button id="tabBtn-extra" onclick="switchTab('extra')" class="tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'extra' ? 'bg-brand-emerald text-white shadow' : 'text-slate-600 hover:bg-slate-50' }} cursor-pointer">
                <i data-lucide="sparkles" class="w-4 h-4"></i> Layanan Non-Formal
            </button>
    </div>

    <!-- Main Card Container -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

        <!-- Tab: Unit -->
        <div id="tabContent-unit" class="tab-content p-8 space-y-6 {{ $activeTab === 'unit' ? '' : 'hidden' }}">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-extrabold text-base text-slate-800">Unit Sekolah</h3>
                    <p class="text-[11px] text-slate-400">Kelola unit sekolah yang tersedia untuk pendaftaran (mis. SANS PAUD, SANS SD).</p>
                </div>
                <button onclick="openUnitModal('', '', '', '', '', '1', true, '{{ route('admin.spmb-settings.units.store') }}')" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1 cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Unit
                </button>
            </div>
            
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Nama Unit</th>
                            <th class="py-4 px-6">Kode Unit</th>
                            <th class="py-4 px-6">No. WhatsApp Admin</th>
                            <th class="py-4 px-6">Group WA SPMB</th>
                            <th class="py-4 px-6 text-center">Status</th>
                            <th class="py-4 px-6 text-center">Digunakan Transaksi</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-slate-100">
                        @forelse($units as $unit)
                            <tr class="unit-item-row hover:bg-slate-50/30 transition" data-unit-id="{{ $unit->id }}">
                                <td class="py-4 px-6 font-extrabold text-slate-800">{{ $unit->name }}</td>
                                <td class="py-4 px-6 text-slate-600 font-semibold">{{ $unit->code ?? '-' }}</td>
                                <td class="py-4 px-6">
                                    @if(!empty($unit->whatsapp_number))
                                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                            <span>{{ $unit->whatsapp_number }}</span>
                                        </div>
                                        @if(!empty($unit->admin_contact_name))
                                            <span class="text-[10px] text-slate-400 block mt-0.5">{{ $unit->admin_contact_name }}</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum diatur</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if(!empty($unit->spmb_group_url))
                                        <a href="{{ $unit->spmb_group_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition" title="Buka Link Group WA">
                                            <i data-lucide="users" class="w-3.5 h-3.5 text-emerald-600"></i>
                                            <span>Tersedia</span>
                                            <i data-lucide="external-link" class="w-3 h-3 text-emerald-500"></i>
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum diatur</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide border {{ $unit->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $unit->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        {{ $unit->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex min-w-20 justify-center px-2.5 py-1 rounded-lg text-[10px] font-bold {{ $unit->registrations_count > 0 ? 'bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-400' }}">
                                        {{ $unit->registrations_count }} Pendaftar
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button onclick="openUnitModal('{{ addslashes($unit->name) }}', '{{ addslashes($unit->code) }}', '{{ addslashes($unit->whatsapp_number ?? '') }}', '{{ addslashes($unit->admin_contact_name ?? '') }}', '{{ addslashes($unit->spmb_group_url ?? '') }}', '{{ $unit->is_active }}', false, '{{ route('admin.spmb-settings.units.update', $unit->id) }}')" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-brand-emerald cursor-pointer" title="Edit Unit">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        @if($unit->registrations_count > 0)
                                            <button type="button" onclick="showToast('Peringatan: Tidak dapat menghapus Unit karena sudah digunakan oleh pendaftar!', 'error')" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-300 cursor-not-allowed" title="Hapus Unit">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        @else
                                            <button type="button" onclick="confirmDelete('{{ route('admin.spmb-settings.units.delete', $unit->id) }}', 'Apakah Anda yakin ingin menghapus Unit ini? Data yang terhapus tidak dapat dikembalikan.')" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 cursor-pointer" title="Hapus Unit">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 text-xs">Belum ada unit yang ditambahkan.</td>
                            </tr>
                        @endforelse
                        <tr id="emptyUnitRow-filtered" class="hidden">
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">Tidak ada unit sekolah yang sesuai dengan filter.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Grade (Tingkatan) -->
        <div id="tabContent-grade" class="tab-content p-8 space-y-6 {{ $activeTab === 'grade' ? '' : 'hidden' }}">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-extrabold text-base text-slate-800">Tingkatan Kelas</h3>
                    <p class="text-[11px] text-slate-400">Kelola tingkatan kelas dan batas usia/umur untuk setiap Unit (mis. TK A, TK B, Kelas 1).</p>
                </div>
                <button onclick="openGradeModal('', window.currentUnitFilter || '', '', '0', '', '0', '', '1', true, '{{ route('admin.spmb-settings.grades.store') }}')" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1 cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Tingkatan
                </button>
            </div>
            
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Tingkatan (Grade)</th>
                            <th class="py-4 px-6">Unit Asal</th>
                            <th class="py-4 px-6">Batas Usia / Umur</th>
                            <th class="py-4 px-6 text-center">Status</th>
                            <th class="py-4 px-6 text-center">Digunakan Transaksi</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-slate-100">
                        @forelse($grades as $grade)
                            <tr class="grade-item-row hover:bg-slate-50/30 transition" data-unit-id="{{ $grade->spmb_unit_id }}">
                                <td class="py-4 px-6 font-extrabold text-slate-800">{{ $grade->name }}</td>
                                <td class="py-4 px-6 text-slate-600 font-semibold">{{ $grade->unit->name ?? '-' }}</td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold {{ $grade->min_age_years !== null || $grade->max_age_years !== null ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                        <i data-lucide="clock" class="w-3 h-3 text-brand-emerald"></i>
                                        {{ $grade->age_range_label }}
                                    </span>
                                    @if(!empty($grade->age_notes))
                                        <p class="text-[10px] text-slate-400 mt-0.5">{{ $grade->age_notes }}</p>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide border {{ $grade->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $grade->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        {{ $grade->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex min-w-20 justify-center px-2.5 py-1 rounded-lg text-[10px] font-bold {{ $grade->registrations_count > 0 ? 'bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-400' }}">
                                        {{ $grade->registrations_count }} Pendaftar
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button onclick="openGradeModal('{{ addslashes($grade->name) }}', '{{ $grade->spmb_unit_id }}', '{{ $grade->min_age_years ?? '' }}', '{{ $grade->min_age_months ?? 0 }}', '{{ $grade->max_age_years ?? '' }}', '{{ $grade->max_age_months ?? 0 }}', '{{ addslashes($grade->age_notes ?? '') }}', '{{ $grade->is_active }}', false, '{{ route('admin.spmb-settings.grades.update', $grade->id) }}')" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-brand-emerald cursor-pointer" title="Edit Tingkatan">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        @if($grade->registrations_count > 0)
                                            <button type="button" onclick="showToast('Peringatan: Tidak dapat menghapus Tingkatan karena sudah digunakan oleh pendaftar!', 'error')" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-300 cursor-not-allowed" title="Hapus Tingkatan">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        @else
                                            <button type="button" onclick="confirmDelete('{{ route('admin.spmb-settings.grades.delete', $grade->id) }}', 'Apakah Anda yakin ingin menghapus Tingkatan ini? Data yang terhapus tidak dapat dikembalikan.')" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 cursor-pointer" title="Hapus Tingkatan">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 text-xs">Belum ada tingkatan yang ditambahkan.</td>
                            </tr>
                        @endforelse
                        <tr id="emptyGradeRow-filtered" class="hidden">
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">Tidak ada tingkatan kelas untuk unit yang dipilih.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
            
            <!-- Tab: Extra Services (Layanan Non-Formal) -->
            <div id="tabContent-extra" class="tab-content p-8 space-y-6 {{ $activeTab === 'extra' ? '' : 'hidden' }}">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-extrabold text-base text-slate-800">Layanan Non-Formal</h3>
                        <p class="text-[11px] text-slate-400">Kelola layanan tambahan opsional seperti TPA/Daycare dan TPQ.</p>
                    </div>
                    <button
                        onclick="openExtraModal('', '', window.currentUnitFilter || '', '1', true, '{{ route('admin.spmb-settings.extra-services.store') }}')"
                        class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1 cursor-pointer"
                    >
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        Tambah Layanan
                    </button>
                </div>
                
                <div class="overflow-x-auto border border-slate-100 rounded-xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                                <th class="py-4 px-6">Nama Layanan</th>
                                <th class="py-4 px-6">Kode Layanan</th>
                                <th class="py-4 px-6">Unit Asal</th>
                                <th class="py-4 px-6 text-center">Status</th>
                                <th class="py-4 px-6 text-center">Jumlah Murid</th>
                                <th class="py-4 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs text-slate-650 divide-y divide-slate-50">
                            @forelse($extraServices as $service)
                                <tr class="extra-item-row hover:bg-slate-50/30 transition" data-unit-id="{{ $service->spmb_unit_id ?? 'all' }}">
                                    <td class="py-4 px-6 font-bold text-slate-800">
                                        {{ $service->name }}
                                    </td>
                                    <td class="py-4 px-6 font-mono font-bold text-brand-emerald">
                                        {{ $service->code }}
                                    </td>
                                    <td class="py-4 px-6 font-semibold text-slate-600">
                                        @if($service->unit)
                                            <span class="text-slate-800 font-bold">{{ $service->unit->name }}</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-500 border border-slate-200">Semua Unit</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide border {{ $service->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-slate-500 border-slate-200' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $service->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            {{ $service->is_active ? 'Aktif' : 'Non-Aktif' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="inline-flex min-w-20 justify-center px-2.5 py-1 rounded-lg text-[10px] font-bold {{ $service->registrations_count > 0 ? 'bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-400' }}">
                                            {{ $service->registrations_count }} Pendaftar
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button onclick="openExtraModal('{{ addslashes($service->name) }}', '{{ addslashes($service->code) }}', '{{ $service->spmb_unit_id }}', '{{ $service->is_active }}', false, '{{ route('admin.spmb-settings.extra-services.update', $service->id) }}')" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-brand-emerald cursor-pointer" title="Edit Layanan">
                                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                                            </button>
                                            @if($service->registrations_count > 0)
                                                <button type="button" onclick="showToast('Peringatan: Tidak dapat menghapus Layanan karena sudah digunakan oleh pendaftar!', 'error')" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-300 cursor-not-allowed" title="Hapus Layanan">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            @else
                                                <button type="button" onclick="confirmDelete('{{ route('admin.spmb-settings.extra-services.delete', $service->id) }}', 'Apakah Anda yakin ingin menghapus Layanan ini? Data yang terhapus tidak dapat dikembalikan.')" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 cursor-pointer" title="Hapus Layanan">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 text-xs">Belum ada layanan non-formal yang ditambahkan.</td>
                                </tr>
                            @endforelse
                            <tr id="emptyExtraRow-filtered" class="hidden">
                                <td colspan="6" class="py-8 text-center text-slate-400 text-xs">Tidak ada layanan non-formal untuk unit yang dipilih.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal for Extra Service (Layanan Non-Formal) -->
        <div id="extraModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 opacity-0 pointer-events-none transition-all duration-100">
            <div class="bg-white w-full max-w-md rounded-3xl shadow-xl transform scale-95 transition-all duration-100" id="extraModalBody">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="text-lg font-extrabold text-slate-800" id="extraModalTitle">Tambah Layanan Non-Formal</h2>
                    <button onclick="closeExtraModal()" type="button" class="p-2 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-slate-650 transition">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form id="extraForm" method="POST" action="" hx-boost="false">
                    @csrf
                    <div id="extraMethod"></div>
                    @if($errors->any() && session('failed_modal') && str_starts_with(session('failed_modal'), 'extra_'))
                        <div class="spmb-unit-errors mx-6 mt-4 text-xs text-red-650 bg-red-50 p-3.5 rounded-xl border border-red-205 font-semibold space-y-1">
                            @foreach($errors->all() as $error)
                                <p>⚠️ {{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nama Layanan</label>
                            <input type="text" id="extraNameInput" name="name" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: Taman Penitipan Anak (TPA)">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Kode Layanan</label>
                            <input type="text" id="extraCodeInput" name="code" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: TPA">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Unit Asal (Terkait Unit Sekolah)</label>
                            <select id="extraUnitInput" name="spmb_unit_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold">
                                <option value="">Semua Unit (Umum)</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="extraActiveInput" name="is_active" value="1" class="w-4 h-4 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                            <label for="extraActiveInput" class="text-sm font-bold text-slate-700">Layanan Aktif</label>
                        </div>
                    </div>
                    <div class="p-6 border-t border-slate-100 bg-slate-50 rounded-b-3xl flex justify-end gap-3">
                        <button type="button" onclick="closeExtraModal()" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-200 transition">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-brand-emerald hover-emerald transition shadow-sm" id="extraSubmitBtn">Simpan Layanan</button>
                    </div>
                </form>
            </div>
        </div>

    <!-- Modal for Unit -->
    <div id="unitModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 opacity-0 pointer-events-none transition-all duration-100">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-xl transform scale-95 transition-all duration-100" id="unitModalBody">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-lg font-extrabold text-slate-800" id="unitModalTitle">Tambah Unit Pendaftaran</h2>
                <button onclick="closeUnitModal()" type="button" class="p-2 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-slate-600 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="unitForm" method="POST" action="" hx-boost="false">
                @csrf
                <div id="unitMethod"></div>
                @if($errors->any() && session('failed_modal') && str_starts_with(session('failed_modal'), 'unit_'))
                    <div class="spmb-unit-errors mx-6 mt-4 text-xs text-red-655 bg-red-50 p-3.5 rounded-xl border border-red-205 font-semibold space-y-1">
                        @foreach($errors->all() as $error)
                            <p>⚠️ {{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nama Unit Sekolah</label>
                        <input type="text" id="unitNameInput" name="name" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: SANS SD">
                    </div>
                    <div id="unitCodeGroup">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2 flex items-center justify-between">
                            <span>Kode Unit</span>
                            <span id="unitCodeLockBadge" class="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200 normal-case hidden flex items-center gap-1">
                                <i data-lucide="lock" class="w-3 h-3"></i> Terkunci sistem
                            </span>
                        </label>
                        <input type="text" id="unitCodeInput" name="code" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: SD (Opsional)">
                        <span id="unitCodeHelper" class="text-[10px] text-slate-400 mt-1 hidden block">Kode unit (PAUD / SD / SMP) dikunci untuk menjaga kestabilan alur formulir dan integrasi sistem.</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">No. WhatsApp Admin Unit</label>
                        <input type="text" id="unitWhatsappInput" name="whatsapp_number" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: 081234567890">
                        <span class="text-[10px] text-slate-400 mt-1 block">Nomor ini akan dihubungi oleh orang tua calon murid unit ini.</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nama Kontak / Petugas (Opsional)</label>
                        <input type="text" id="unitAdminContactInput" name="admin_contact_name" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: Kak Nisa - Admin PAUD">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Link WhatsApp Group SPMB (Unit)</label>
                        <input type="url" id="unitGroupUrlInput" name="spmb_group_url" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: https://chat.whatsapp.com/XXXXX">
                        <span class="text-[10px] text-slate-400 mt-1 block">Tautan group WhatsApp resmi untuk informasi seputar SPMB unit ini (ditampilkan pada tahap Ta'aruf).</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="unitActiveInput" name="is_active" value="1" class="w-4 h-4 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                        <label for="unitActiveInput" class="text-sm font-bold text-slate-700">Unit Aktif</label>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-100 bg-slate-50 rounded-b-3xl flex justify-end gap-3">
                    <button type="button" onclick="closeUnitModal()" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-brand-emerald hover-emerald transition shadow-sm" id="unitSubmitBtn">Simpan Unit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for Grade -->
    <div id="gradeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 opacity-0 pointer-events-none transition-all duration-100">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-xl transform scale-95 transition-all duration-100" id="gradeModalBody">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-lg font-extrabold text-slate-800" id="gradeModalTitle">Tambah Tingkatan Kelas</h2>
                <button onclick="closeGradeModal()" type="button" class="p-2 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-slate-600 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="gradeForm" method="POST" action="" hx-boost="false">
                @csrf
                <div id="gradeMethod"></div>
                @if($errors->any() && session('failed_modal') && str_starts_with(session('failed_modal'), 'grade_'))
                    <div class="spmb-unit-errors mx-6 mt-4 text-xs text-red-655 bg-red-50 p-3.5 rounded-xl border border-red-205 font-semibold space-y-1">
                        @foreach($errors->all() as $error)
                            <p>⚠️ {{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Tingkatan (Grade)</label>
                        <input type="text" id="gradeNameInput" name="name" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: TK A, Kelas 1">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Unit Terkait</label>
                        <select id="gradeUnitInput" name="spmb_unit_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold">
                            <option value="">-- Pilih Unit --</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Batas Usia Minimal & Maksimal -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-3">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                            <i data-lucide="clock" class="w-4 h-4 text-brand-emerald"></i>
                            <span>Konfigurasi Batas Usia / Umur</span>
                        </div>
                        <p class="text-[10.5px] text-slate-400 leading-relaxed">Dihitung relatif per 1 Juli tahun ajaran aktif. Kosongkan jika tanpa batasan usia.</p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Usia Minimal</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="number" id="gradeMinAgeYearsInput" name="min_age_years" min="0" max="25" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-semibold" placeholder="Tahun (e.g. 4)">
                                    <span class="text-[11px] font-bold text-slate-400">Thn</span>
                                    <input type="number" id="gradeMinAgeMonthsInput" name="min_age_months" min="0" max="11" class="w-16 bg-white border border-slate-300 rounded-xl px-2 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-semibold" placeholder="Bln" value="0">
                                    <span class="text-[11px] font-bold text-slate-400">Bln</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Usia Maksimal</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="number" id="gradeMaxAgeYearsInput" name="max_age_years" min="0" max="25" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-semibold" placeholder="Tahun (e.g. 5)">
                                    <span class="text-[11px] font-bold text-slate-400">Thn</span>
                                    <input type="number" id="gradeMaxAgeMonthsInput" name="max_age_months" min="0" max="11" class="w-16 bg-white border border-slate-300 rounded-xl px-2 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-semibold" placeholder="Bln" value="0">
                                    <span class="text-[11px] font-bold text-slate-400">Bln</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Catatan Batas Usia (Opsional)</label>
                            <input type="text" id="gradeAgeNotesInput" name="age_notes" class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-semibold" placeholder="Contoh: Minimal 4 tahun per 1 Juli">
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="gradeActiveInput" name="is_active" value="1" class="w-4 h-4 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                        <label for="gradeActiveInput" class="text-sm font-bold text-slate-700">Tingkatan Aktif</label>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-100 bg-slate-50 rounded-b-3xl flex justify-end gap-3">
                    <button type="button" onclick="closeGradeModal()" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-brand-emerald hover-emerald transition shadow-sm" id="gradeSubmitBtn">Simpan Tingkatan</button>
                </div>
            </form>
        </div>
    </div>
    @if(session('success'))
        <script>
            if (typeof showToast === 'function') {
                showToast("{{ session('success') }}", 'success');
            }
        </script>
    @endif
    @if(session('error'))
        <script>
            if (typeof showToast === 'function') {
                showToast("{{ session('error') }}", 'error');
            }
        </script>
    @endif

    <script>
        window.currentUnitFilter = "{{ $selectedUnitId ?? '' }}";

        // Dynamic Unit Filtering
        window.filterByUnit = function(unitId) {
            window.currentUnitFilter = unitId ? unitId.toString() : '';

            // Update active button classes
            document.querySelectorAll('.unit-filter-btn').forEach(btn => {
                btn.className = "unit-filter-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap bg-white text-slate-650 hover:bg-slate-100 border border-slate-200/60 cursor-pointer";
            });

            const activeBtnId = window.currentUnitFilter ? 'unitFilterBtn-' + window.currentUnitFilter : 'unitFilterBtn-all';
            const activeBtn = document.getElementById(activeBtnId);
            if (activeBtn) {
                activeBtn.className = "unit-filter-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap bg-brand-emerald text-white shadow-xs cursor-pointer";
            }

            // 1. Filter Tab Unit
            const unitRows = document.querySelectorAll('.unit-item-row');
            let visibleUnitCount = 0;
            unitRows.forEach(row => {
                const uId = (row.dataset.unitId || '').toString().trim();
                if (!window.currentUnitFilter || uId === window.currentUnitFilter) {
                    row.style.display = '';
                    visibleUnitCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            const emptyUnitFiltered = document.getElementById('emptyUnitRow-filtered');
            if (emptyUnitFiltered) {
                if (unitRows.length > 0 && visibleUnitCount === 0) emptyUnitFiltered.classList.remove('hidden');
                else emptyUnitFiltered.classList.add('hidden');
            }

            // 2. Filter Tab Grade
            const gradeRows = document.querySelectorAll('.grade-item-row');
            let visibleGradeCount = 0;
            gradeRows.forEach(row => {
                const uId = (row.dataset.unitId || '').toString().trim();
                if (!window.currentUnitFilter || uId === window.currentUnitFilter) {
                    row.style.display = '';
                    visibleGradeCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            const emptyGradeFiltered = document.getElementById('emptyGradeRow-filtered');
            if (emptyGradeFiltered) {
                if (gradeRows.length > 0 && visibleGradeCount === 0) emptyGradeFiltered.classList.remove('hidden');
                else emptyGradeFiltered.classList.add('hidden');
            }

            // 3. Filter Tab Extra Services
            const extraRows = document.querySelectorAll('.extra-item-row');
            let visibleExtraCount = 0;
            extraRows.forEach(row => {
                const uId = (row.dataset.unitId || '').toString().trim();
                if (!window.currentUnitFilter || uId === 'all' || uId === window.currentUnitFilter) {
                    row.style.display = '';
                    visibleExtraCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            const emptyExtraFiltered = document.getElementById('emptyExtraRow-filtered');
            if (emptyExtraFiltered) {
                if (extraRows.length > 0 && visibleExtraCount === 0) emptyExtraFiltered.classList.remove('hidden');
                else emptyExtraFiltered.classList.add('hidden');
            }

            // Update URL and storage
            const url = new URL(window.location.href);
            if (window.currentUnitFilter) {
                url.searchParams.set('unit_id', window.currentUnitFilter);
            } else {
                url.searchParams.delete('unit_id');
            }
            window.history.replaceState({ path: url.toString() }, '', url.toString());
            localStorage.setItem('spmb_units_active_unit', window.currentUnitFilter);

            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
            }
        };

        // Tab Switching
        window.switchTab = function(tabId) {
            const panel = document.getElementById('tabContent-' + tabId);
            if (!panel) return;

            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            panel.classList.remove('hidden');

            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.className = "tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50 cursor-pointer";
            });
            
            const activeBtn = document.getElementById('tabBtn-' + tabId);
            if (activeBtn) {
                activeBtn.className = "tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow cursor-pointer";
            }
            
            // Update URL query parameter to sync with server
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabId);
            if (window.currentUnitFilter) {
                url.searchParams.set('unit_id', window.currentUnitFilter);
            }
            window.history.replaceState({ path: url.toString() }, '', url.toString());
            localStorage.setItem('spmb_units_active_tab', tabId);
        };

        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            const urlUnit = urlParams.get('unit_id');
            const savedUnit = localStorage.getItem('spmb_units_active_unit');
            
            if (urlUnit !== null) {
                window.currentUnitFilter = urlUnit;
            } else if (savedUnit !== null && savedUnit !== '') {
                window.currentUnitFilter = savedUnit;
            }

            if (window.currentUnitFilter) {
                window.filterByUnit(window.currentUnitFilter);
            }
        });

        // Clear Validation Errors on modal show/hide
        window.clearModalErrors = function() {
            document.querySelectorAll('.spmb-unit-errors').forEach(el => {
                el.classList.add('hidden');
            });
        };

        // Modal Unit
        window.openUnitModal = function(name = '', code = '', whatsapp = '', contactName = '', groupUrl = '', isActive = '1', isCreate = true, actionUrl = '') {
            window.clearModalErrors();
            
            const modal = document.getElementById('unitModal');
            const modalBody = document.getElementById('unitModalBody');
            const form = document.getElementById('unitForm');
            const methodDiv = document.getElementById('unitMethod');
            
            document.getElementById('unitModalTitle').innerText = isCreate ? 'Tambah Unit Pendaftaran' : 'Edit Unit Pendaftaran';
            document.getElementById('unitSubmitBtn').innerText = isCreate ? 'Simpan Unit' : 'Perbarui Unit';
            
            form.setAttribute('action', actionUrl);
            document.getElementById('unitNameInput').value = name;
            document.getElementById('unitCodeInput').value = code;
            document.getElementById('unitWhatsappInput').value = whatsapp;
            document.getElementById('unitAdminContactInput').value = contactName;
            document.getElementById('unitGroupUrlInput').value = groupUrl;
            document.getElementById('unitActiveInput').checked = (isActive == '1' || isActive == true || isActive == 'true');
            
            if (!isCreate) {
                methodDiv.innerHTML = '<input type="hidden" name="_method" value="PUT">';
                const codeInput = document.getElementById('unitCodeInput');
                if (codeInput) {
                    codeInput.readOnly = true;
                    codeInput.classList.add('bg-slate-100', 'text-slate-500', 'cursor-not-allowed');
                    codeInput.classList.remove('bg-slate-50', 'text-slate-800');
                }
                const lockBadge = document.getElementById('unitCodeLockBadge');
                if (lockBadge) lockBadge.classList.remove('hidden');
                const helperText = document.getElementById('unitCodeHelper');
                if (helperText) helperText.classList.remove('hidden');
            } else {
                methodDiv.innerHTML = '';
                const codeInput = document.getElementById('unitCodeInput');
                if (codeInput) {
                    codeInput.readOnly = false;
                    codeInput.classList.remove('bg-slate-100', 'text-slate-500', 'cursor-not-allowed');
                    codeInput.classList.add('bg-slate-50', 'text-slate-800');
                }
                const lockBadge = document.getElementById('unitCodeLockBadge');
                if (lockBadge) lockBadge.classList.add('hidden');
                const helperText = document.getElementById('unitCodeHelper');
                if (helperText) helperText.classList.add('hidden');
            }
            
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalBody.classList.remove('scale-95');
            modalBody.classList.add('scale-100');
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
            }
        };

        window.closeUnitModal = function() {
            window.clearModalErrors();
            const modal = document.getElementById('unitModal');
            const modalBody = document.getElementById('unitModalBody');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalBody.classList.remove('scale-100');
            modalBody.classList.add('scale-95');
        };

        // Modal Grade
        window.openGradeModal = function(name = '', unitId = '', minAgeYears = '', minAgeMonths = '0', maxAgeYears = '', maxAgeMonths = '0', ageNotes = '', isActive = '1', isCreate = true, actionUrl = '') {
            window.clearModalErrors();
            
            const modal = document.getElementById('gradeModal');
            const modalBody = document.getElementById('gradeModalBody');
            const form = document.getElementById('gradeForm');
            const methodDiv = document.getElementById('gradeMethod');
            
            document.getElementById('gradeModalTitle').innerText = isCreate ? 'Tambah Tingkatan Kelas' : 'Edit Tingkatan Kelas';
            document.getElementById('gradeSubmitBtn').innerText = isCreate ? 'Simpan Tingkatan' : 'Perbarui Tingkatan';
            
            form.setAttribute('action', actionUrl);
            document.getElementById('gradeNameInput').value = name;
            document.getElementById('gradeUnitInput').value = unitId || window.currentUnitFilter || '';
            document.getElementById('gradeMinAgeYearsInput').value = minAgeYears;
            document.getElementById('gradeMinAgeMonthsInput').value = (minAgeMonths !== '' && minAgeMonths !== null) ? minAgeMonths : '0';
            document.getElementById('gradeMaxAgeYearsInput').value = maxAgeYears;
            document.getElementById('gradeMaxAgeMonthsInput').value = (maxAgeMonths !== '' && maxAgeMonths !== null) ? maxAgeMonths : '0';
            document.getElementById('gradeAgeNotesInput').value = ageNotes;
            document.getElementById('gradeActiveInput').checked = (isActive == '1' || isActive == true || isActive == 'true');
            
            if (!isCreate) {
                methodDiv.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            } else {
                methodDiv.innerHTML = '';
            }
            
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalBody.classList.remove('scale-95');
            modalBody.classList.add('scale-100');
        };

        window.closeGradeModal = function() {
            window.clearModalErrors();
            const modal = document.getElementById('gradeModal');
            const modalBody = document.getElementById('gradeModalBody');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalBody.classList.remove('scale-100');
            modalBody.classList.add('scale-95');
        };

        // Modal Extra Service
        window.openExtraModal = function(name = '', code = '', unitId = '', isActive = '1', isCreate = true, actionUrl = '') {
            window.clearModalErrors();
            
            const modal = document.getElementById('extraModal');
            const modalBody = document.getElementById('extraModalBody');
            const form = document.getElementById('extraForm');
            const methodDiv = document.getElementById('extraMethod');
            
            document.getElementById('extraModalTitle').innerText = isCreate ? 'Tambah Layanan Non-Formal' : 'Edit Layanan Non-Formal';
            document.getElementById('extraSubmitBtn').innerText = isCreate ? 'Simpan Layanan' : 'Perbarui Layanan';
            
            form.setAttribute('action', actionUrl);
            document.getElementById('extraNameInput').value = name;
            document.getElementById('extraCodeInput').value = code;
            document.getElementById('extraUnitInput').value = unitId || window.currentUnitFilter || '';
            document.getElementById('extraActiveInput').checked = (isActive == '1' || isActive == true || isActive == 'true');
            
            if (!isCreate) {
                methodDiv.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            } else {
                methodDiv.innerHTML = '';
            }
            
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalBody.classList.remove('scale-95');
            modalBody.classList.add('scale-100');
        };

        window.closeExtraModal = function() {
            window.clearModalErrors();
            const modal = document.getElementById('extraModal');
            const modalBody = document.getElementById('extraModalBody');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalBody.classList.remove('scale-100');
            modalBody.classList.add('scale-95');
        };

        // Click outside handlers to close modals
        document.getElementById('unitModal').addEventListener('click', function(e) {
            if (e.target === this) window.closeUnitModal();
        });
        document.getElementById('gradeModal').addEventListener('click', function(e) {
            if (e.target === this) window.closeGradeModal();
        });
        document.getElementById('extraModal').addEventListener('click', function(e) {
            if (e.target === this) window.closeExtraModal();
        });

        // Auto-reopen modal if validation failed on redirect
        @if(session('failed_modal'))
            document.addEventListener("DOMContentLoaded", function() {
                let failed = "{{ session('failed_modal') }}";
                if (failed.startsWith('unit_create')) {
                    window.switchTab('unit');
                    window.openUnitModal('{{ old('name') }}', '{{ old('code') }}', '{{ old('whatsapp_number') }}', '{{ old('admin_contact_name') }}', '{{ old('is_active') ? 1 : 0 }}', true, '{{ route('admin.spmb-settings.units.store') }}');
                } else if (failed.startsWith('unit_edit_')) {
                    window.switchTab('unit');
                    let id = failed.replace('unit_edit_', '');
                    window.openUnitModal('{{ old('name') }}', '{{ old('code') }}', '{{ old('whatsapp_number') }}', '{{ old('admin_contact_name') }}', '{{ old('is_active') ? 1 : 0 }}', false, '/admin/spmb-settings/units/' + id);
                } else if (failed.startsWith('grade_create')) {
                    window.switchTab('grade');
                    window.openGradeModal('{{ old('name') }}', '{{ old('spmb_unit_id') }}', '{{ old('min_age_years') }}', '{{ old('min_age_months', 0) }}', '{{ old('max_age_years') }}', '{{ old('max_age_months', 0) }}', '{{ old('age_notes') }}', '{{ old('is_active') ? 1 : 0 }}', true, '{{ route('admin.spmb-settings.grades.store') }}');
                } else if (failed.startsWith('grade_edit_')) {
                    window.switchTab('grade');
                    let id = failed.replace('grade_edit_', '');
                    window.openGradeModal('{{ old('name') }}', '{{ old('spmb_unit_id') }}', '{{ old('min_age_years') }}', '{{ old('min_age_months', 0) }}', '{{ old('max_age_years') }}', '{{ old('max_age_months', 0) }}', '{{ old('age_notes') }}', '{{ old('is_active') ? 1 : 0 }}', false, '/admin/spmb-settings/grades/' + id);
                } else if (failed.startsWith('extra_create')) {
                    window.switchTab('extra');
                    window.openExtraModal('{{ old('name') }}', '{{ old('code') }}', '{{ old('spmb_unit_id') }}', '{{ old('is_active') ? 1 : 0 }}', true, '{{ route('admin.spmb-settings.extra-services.store') }}');
                } else if (failed.startsWith('extra_edit_')) {
                    window.switchTab('extra');
                    let id = failed.replace('extra_edit_', '');
                    window.openExtraModal('{{ old('name') }}', '{{ old('code') }}', '{{ old('spmb_unit_id') }}', '{{ old('is_active') ? 1 : 0 }}', false, '/admin/spmb-settings/extra-services/' + id);
                }

                // Show errors inside the reopened modal
                document.querySelectorAll('.spmb-unit-errors').forEach(el => {
                    el.classList.remove('hidden');
                });
            });
        @endif

        // Escape key listener to close modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const uModal = document.getElementById('unitModal');
                if (uModal && !uModal.classList.contains('pointer-events-none')) window.closeUnitModal();
                
                const gModal = document.getElementById('gradeModal');
                if (gModal && !gModal.classList.contains('pointer-events-none')) window.closeGradeModal();
                
                const eModal = document.getElementById('extraModal');
                if (eModal && !eModal.classList.contains('pointer-events-none')) window.closeExtraModal();
            }
        });
    </script>
</div>
@endsection
