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
        @if($isSuperAdmin)
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
        @else
        <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 py-2 px-3.5 rounded-2xl shadow-xs self-start md:self-auto">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span class="text-xs font-extrabold text-emerald-800">
                Unit Pengelola: {{ $units->first()->name ?? 'Unit Saya' }}
            </span>
        </div>
        @endif
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
                @if($isSuperAdmin)
                <button onclick="openUnitModal('', '', '', '', '', '1', true, '{{ route('admin.spmb-settings.units.store') }}')" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1 cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Unit
                </button>
                @endif
            </div>
            
            <div class="overflow-x-auto border border-slate-100 rounded-2xl shadow-xs">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] text-slate-500 font-bold uppercase tracking-wider bg-slate-50/80">
                            <th class="py-3.5 px-6 whitespace-nowrap">Nama Unit</th>
                            <th class="py-3.5 px-6 whitespace-nowrap">Kode Unit</th>
                            <th class="py-3.5 px-6 whitespace-nowrap">No. WhatsApp Admin</th>
                            <th class="py-3.5 px-6 whitespace-nowrap">Group WA SPMB</th>
                            <th class="py-3.5 px-6 text-center whitespace-nowrap">Status</th>
                            <th class="py-3.5 px-6 text-center whitespace-nowrap">Digunakan Transaksi</th>
                            <th class="py-3.5 px-6 text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-slate-100/80">
                        @forelse($units as $unit)
                            <tr class="unit-item-row hover:bg-slate-50/40 transition" data-unit-id="{{ $unit->id }}">
                                <td class="py-4 px-6 align-middle whitespace-nowrap">
                                    <div class="flex items-center gap-1">
                                        <span class="w-2.5 h-2.5 rounded-full {{ $unit->is_active ? 'bg-emerald-500 ring-4 ring-emerald-50' : 'bg-slate-300' }} shrink-0"></span>
                                        <span class="font-extrabold text-xs text-slate-800 tracking-tight">{{ $unit->name }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6 align-middle whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200/60 font-mono">
                                        {{ $unit->code ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 align-middle whitespace-nowrap">
                                    @if(!empty($unit->whatsapp_number))
                                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                            <span>{{ $unit->whatsapp_number }}</span>
                                        </div>
                                        @if(!empty($unit->admin_contact_name))
                                            <span class="text-[10.5px] text-slate-400 block mt-0.5">{{ $unit->admin_contact_name }}</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum diatur</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 align-middle whitespace-nowrap">
                                    @if(!empty($unit->spmb_group_url))
                                        <a href="{{ $unit->spmb_group_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200/80 transition" title="Buka Link Group WA">
                                            <i data-lucide="users" class="w-3.5 h-3.5 text-emerald-600"></i>
                                            <span>Tersedia</span>
                                            <i data-lucide="external-link" class="w-3 h-3 text-emerald-500"></i>
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Belum diatur</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 align-middle text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10.5px] font-extrabold uppercase tracking-wide border {{ $unit->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $unit->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        {{ $unit->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 align-middle text-center whitespace-nowrap">
                                    <span class="inline-flex min-w-24 justify-center items-center px-3 py-1.5 rounded-xl text-xs font-bold {{ $unit->registrations_count > 0 ? 'bg-slate-100 text-slate-700 border border-slate-200/60' : 'bg-slate-50 text-slate-400' }}">
                                        {{ $unit->registrations_count }} Pendaftar
                                    </span>
                                </td>
                                <td class="py-4 px-6 align-middle text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button onclick="openUnitModal('{{ addslashes($unit->name) }}', '{{ addslashes($unit->code) }}', '{{ addslashes($unit->whatsapp_number ?? '') }}', '{{ addslashes($unit->admin_contact_name ?? '') }}', '{{ addslashes($unit->spmb_group_url ?? '') }}', '{{ $unit->is_active }}', false, '{{ route('admin.spmb-settings.units.update', $unit->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-brand-emerald text-xs font-bold text-brand-emerald transition hover:bg-emerald-800 hover:border-emerald-800 hover:text-white cursor-pointer" title="Edit Unit">
                                            <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                            <span>Edit</span>
                                        </button>
                                        @if($isSuperAdmin)
                                            @if($unit->registrations_count > 0)
                                                <button type="button" onclick="showToast('Peringatan: Tidak dapat menghapus Unit karena sudah digunakan oleh pendaftar!', 'error')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white cursor-pointer" title="Hapus Unit">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            @else
                                                <button type="button" onclick="confirmDelete('{{ route('admin.spmb-settings.units.delete', $unit->id) }}', 'Apakah Anda yakin ingin menghapus Unit ini? Data yang terhapus tidak dapat dikembalikan.')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white cursor-pointer" title="Hapus Unit">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            @endif
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
                <button onclick="openGradeModal('', window.currentUnitFilter || '', '', '', '0', '', '0', '', '1', true, '{{ route('admin.spmb-settings.grades.store') }}', [], [], [], [])" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1 cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Tingkatan
                </button>
            </div>
            
            <div class="overflow-x-auto border border-slate-100 rounded-2xl shadow-xs">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] text-slate-500 font-bold uppercase tracking-wider bg-slate-50/80">
                            <th class="py-3.5 px-6 whitespace-nowrap">Tingkatan (Grade)</th>
                            <th class="py-3.5 px-6 whitespace-nowrap">Sub-Unit</th>
                            <th class="py-3.5 px-6 whitespace-nowrap">Unit Asal</th>
                            <th class="py-3.5 px-6 whitespace-nowrap">Kriteria Keterbukaan</th>
                            <th class="py-3.5 px-6 whitespace-nowrap">Batas Usia / Umur</th>
                            <th class="py-3.5 px-6 text-center whitespace-nowrap">Status</th>
                            <th class="py-3.5 px-6 text-center whitespace-nowrap">Digunakan Transaksi</th>
                            <th class="py-3.5 px-6 text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-slate-100/80">
                        @forelse($grades as $grade)
                            <tr class="grade-item-row hover:bg-slate-50/40 transition" data-unit-id="{{ $grade->spmb_unit_id }}">
                                <td class="py-4 px-6 align-middle whitespace-nowrap">
                                    <div class="flex items-center gap-1">
                                        <span class="w-2.5 h-2.5 rounded-full {{ $grade->is_active ? 'bg-emerald-500 ring-4 ring-emerald-50' : 'bg-slate-300' }} shrink-0"></span>
                                        <span class="font-extrabold text-xs text-slate-800 tracking-tight">{{ $grade->name }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6 align-middle whitespace-nowrap">
                                    @if($grade->sub_unit)
                                        @php
                                            $subLower = strtolower($grade->sub_unit);
                                            $subBadgeClass = 'bg-slate-100 text-slate-700 border-slate-200';
                                            if (str_contains($subLower, 'playgroup') || str_contains($subLower, 'kb')) {
                                                $subBadgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
                                            } elseif (str_contains($subLower, 'tk') || str_contains($subLower, 'kanak')) {
                                                $subBadgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                            } elseif (str_contains($subLower, 'daycare') || str_contains($subLower, 'tpa')) {
                                                $subBadgeClass = 'bg-amber-50 text-amber-800 border-amber-200';
                                            }
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border {{ $subBadgeClass }}">
                                            {{ $grade->sub_unit }}
                                        </span>
                                    @else
                                        <span class="text-slate-300 dark:text-slate-600 text-xs font-medium">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 align-middle whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200/60">
                                        {{ $grade->unit->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 align-middle">
                                    <div class="space-y-1.5 min-w-[210px] max-w-[280px]">
                                        {{-- Jalur --}}
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase w-13 shrink-0">Jalur:</span>
                                            @if(empty($grade->applicable_types))
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80 whitespace-nowrap">Semua Jalur</span>
                                            @else
                                                @php
                                                    $targetTypes = $types->whereIn('id', (array)$grade->applicable_types)->pluck('name')->implode(', ');
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200/80 whitespace-nowrap truncate max-w-[150px]" title="{{ $targetTypes }}">
                                                    {{ \Illuminate\Support\Str::limit($targetTypes, 22) }}
                                                </span>
                                            @endif
                                        </div>
                                        {{-- Kategori --}}
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-bold text-slate-400 uppercase w-13 shrink-0">Kategori:</span>
                                            @if(empty($grade->applicable_class_programs))
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200/80 whitespace-nowrap">Semua Kategori</span>
                                            @else
                                                @php
                                                    $targetProgs = $classPrograms->whereIn('id', (array)$grade->applicable_class_programs)->pluck('name')->implode(', ');
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200/80 whitespace-nowrap truncate max-w-[150px]" title="{{ $targetProgs }}">
                                                    {{ \Illuminate\Support\Str::limit($targetProgs, 22) }}
                                                </span>
                                            @endif
                                        </div>
                                        {{-- Gelombang / Periode jika di-filter khusus --}}
                                        @if(!empty($grade->applicable_waves) || !empty($grade->applicable_periods))
                                            <div class="flex items-center gap-1.5 flex-wrap pt-0.5">
                                                @if(!empty($grade->applicable_waves))
                                                    @php
                                                        $targetWaves = $waves->whereIn('id', (array)$grade->applicable_waves)->pluck('name')->implode(', ');
                                                    @endphp
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 whitespace-nowrap" title="Gelombang: {{ $targetWaves }}">
                                                        Gel: {{ \Illuminate\Support\Str::limit($targetWaves, 15) }}
                                                    </span>
                                                @endif
                                                @if(!empty($grade->applicable_periods))
                                                    @php
                                                        $targetPeriods = $periods->whereIn('id', (array)$grade->applicable_periods)->pluck('year')->implode(', ');
                                                    @endphp
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 whitespace-nowrap" title="Tahun Ajaran: {{ $targetPeriods }}">
                                                        {{ \Illuminate\Support\Str::limit($targetPeriods, 15) }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 px-6 align-middle whitespace-nowrap">
                                    <div class="inline-flex flex-col gap-1 items-start">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap {{ $grade->min_age_years !== null || $grade->max_age_years !== null ? 'bg-emerald-50 text-emerald-800 border border-emerald-200/80 shadow-2xs' : 'bg-slate-100 text-slate-500' }}">
                                            <i data-lucide="clock" class="w-3.5 h-3.5 text-brand-emerald shrink-0"></i>
                                            <span>{{ $grade->age_range_label }}</span>
                                        </span>
                                        @if(!empty($grade->age_notes))
                                            <span class="text-[10.5px] text-slate-400 font-normal max-w-[200px] truncate block" title="{{ $grade->age_notes }}">{{ $grade->age_notes }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 px-6 align-middle text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10.5px] font-extrabold uppercase tracking-wide border {{ $grade->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $grade->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        {{ $grade->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 align-middle text-center whitespace-nowrap">
                                    <span class="inline-flex min-w-24 justify-center items-center px-3 py-1.5 rounded-xl text-xs font-bold {{ $grade->registrations_count > 0 ? 'bg-slate-100 text-slate-700 border border-slate-200/60' : 'bg-slate-50 text-slate-400' }}">
                                        {{ $grade->registrations_count }} Pendaftar
                                    </span>
                                </td>
                                <td class="py-4 px-6 align-middle text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button onclick="openGradeModal('{{ addslashes($grade->name) }}', '{{ $grade->spmb_unit_id }}', '{{ addslashes($grade->sub_unit ?? '') }}', '{{ $grade->min_age_years ?? '' }}', '{{ $grade->min_age_months ?? 0 }}', '{{ $grade->max_age_years ?? '' }}', '{{ $grade->max_age_months ?? 0 }}', '{{ addslashes($grade->age_notes ?? '') }}', '{{ $grade->is_active }}', false, '{{ route('admin.spmb-settings.grades.update', $grade->id) }}', {{ json_encode($grade->applicable_types ?? []) }}, {{ json_encode($grade->applicable_class_programs ?? []) }}, {{ json_encode($grade->applicable_waves ?? []) }}, {{ json_encode($grade->applicable_periods ?? []) }})" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-brand-emerald text-xs font-bold text-brand-emerald transition hover:bg-emerald-800 hover:border-emerald-800 hover:text-white cursor-pointer" title="Edit Tingkatan">
                                            <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                            <span>Edit</span>
                                        </button>
                                        @if($grade->registrations_count > 0)
                                            <button type="button" onclick="showToast('Peringatan: Tidak dapat menghapus Tingkatan karena sudah digunakan oleh pendaftar!', 'error')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white cursor-pointer" title="Hapus Tingkatan">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Hapus</span>
                                            </button>
                                        @else
                                            <button type="button" onclick="confirmDelete('{{ route('admin.spmb-settings.grades.delete', $grade->id) }}', 'Apakah Anda yakin ingin menghapus Tingkatan ini? Data yang terhapus tidak dapat dikembalikan.')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white cursor-pointer" title="Hapus Tingkatan">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Hapus</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400 text-xs">Belum ada tingkatan yang ditambahkan.</td>
                            </tr>
                        @endforelse
                        <tr id="emptyGradeRow-filtered" class="hidden">
                            <td colspan="8" class="py-8 text-center text-slate-400 text-xs">Tidak ada tingkatan kelas untuk unit yang dipilih.</td>
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
                        onclick="openExtraModal('', '', window.currentUnitFilter || '', '1', true, '{{ route('admin.spmb-settings.extra-services.store') }}', [], [], [], [], [])"
                        class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1 cursor-pointer"
                    >
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        Tambah Layanan
                    </button>
                </div>
                
                <div class="overflow-x-auto border border-slate-100 rounded-2xl shadow-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] text-slate-500 font-bold uppercase tracking-wider bg-slate-50/80">
                                <th class="py-3.5 px-6 whitespace-nowrap">Nama Layanan</th>
                                <th class="py-3.5 px-6 whitespace-nowrap">Kode Layanan</th>
                                <th class="py-3.5 px-6 whitespace-nowrap">Unit Asal</th>
                                <th class="py-3.5 px-6 whitespace-nowrap">Kriteria Keterbukaan</th>
                                <th class="py-3.5 px-6 text-center whitespace-nowrap">Status</th>
                                <th class="py-3.5 px-6 text-center whitespace-nowrap">Jumlah Murid</th>
                                <th class="py-3.5 px-6 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs divide-y divide-slate-100/80">
                            @forelse($extraServices as $service)
                                <tr class="extra-item-row hover:bg-slate-50/40 transition" data-unit-id="{{ $service->spmb_unit_id ?? 'all' }}">
                                    <td class="py-4 px-6 align-middle whitespace-nowrap">
                                        <div class="flex items-center gap-1">
                                            <span class="w-2.5 h-2.5 rounded-full {{ $service->is_active ? 'bg-emerald-500 ring-4 ring-emerald-50' : 'bg-slate-300' }} shrink-0"></span>
                                            <span class="font-extrabold text-xs text-slate-800 tracking-tight">{{ $service->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 align-middle whitespace-nowrap font-mono font-bold text-brand-emerald">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 font-mono">
                                            {{ $service->code }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 align-middle whitespace-nowrap">
                                        @if($service->unit)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200/60">{{ $service->unit->name }}</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10.5px] font-bold uppercase bg-slate-100 text-slate-500 border border-slate-200">Semua Unit</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 align-middle">
                                        <div class="space-y-1.5 min-w-[210px] max-w-[280px]">
                                            {{-- Jalur --}}
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase w-13 shrink-0">Jalur:</span>
                                                @if(empty($service->applicable_types))
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80 whitespace-nowrap">Semua Jalur</span>
                                                @else
                                                    @php
                                                        $targetTypes = $types->whereIn('id', (array)$service->applicable_types)->pluck('name')->implode(', ');
                                                    @endphp
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200/80 whitespace-nowrap truncate max-w-[150px]" title="{{ $targetTypes }}">
                                                        {{ \Illuminate\Support\Str::limit($targetTypes, 22) }}
                                                    </span>
                                                @endif
                                            </div>
                                            {{-- Kategori --}}
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase w-13 shrink-0">Kategori:</span>
                                                @if(empty($service->applicable_class_programs))
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200/80 whitespace-nowrap">Semua Kategori</span>
                                                @else
                                                    @php
                                                        $targetProgs = $classPrograms->whereIn('id', (array)$service->applicable_class_programs)->pluck('name')->implode(', ');
                                                    @endphp
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200/80 whitespace-nowrap truncate max-w-[150px]" title="{{ $targetProgs }}">
                                                        {{ \Illuminate\Support\Str::limit($targetProgs, 22) }}
                                                    </span>
                                                @endif
                                            </div>
                                            {{-- Tingkatan / Gelombang / Periode jika di-filter khusus --}}
                                            @if(!empty($service->applicable_grades) || !empty($service->applicable_waves) || !empty($service->applicable_periods))
                                                <div class="flex items-center gap-1.5 flex-wrap pt-0.5">
                                                    @if(!empty($service->applicable_grades))
                                                        @php
                                                            $targetGrades = $grades->whereIn('id', (array)$service->applicable_grades)->pluck('name')->implode(', ');
                                                        @endphp
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 whitespace-nowrap" title="Tingkatan: {{ $targetGrades }}">
                                                            Kelas: {{ \Illuminate\Support\Str::limit($targetGrades, 15) }}
                                                        </span>
                                                    @endif
                                                    @if(!empty($service->applicable_waves))
                                                        @php
                                                            $targetWaves = $waves->whereIn('id', (array)$service->applicable_waves)->pluck('name')->implode(', ');
                                                        @endphp
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 whitespace-nowrap" title="Gelombang: {{ $targetWaves }}">
                                                            Gel: {{ \Illuminate\Support\Str::limit($targetWaves, 15) }}
                                                        </span>
                                                    @endif
                                                    @if(!empty($service->applicable_periods))
                                                        @php
                                                            $targetPeriods = $periods->whereIn('id', (array)$service->applicable_periods)->pluck('year')->implode(', ');
                                                        @endphp
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 whitespace-nowrap" title="Tahun Ajaran: {{ $targetPeriods }}">
                                                            {{ \Illuminate\Support\Str::limit($targetPeriods, 15) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 align-middle text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10.5px] font-extrabold uppercase tracking-wide border {{ $service->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-slate-500 border-slate-200' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $service->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            {{ $service->is_active ? 'Aktif' : 'Non-Aktif' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 align-middle text-center whitespace-nowrap">
                                        <span class="inline-flex min-w-24 justify-center items-center px-3 py-1.5 rounded-xl text-xs font-bold {{ $service->registrations_count > 0 ? 'bg-slate-100 text-slate-700 border border-slate-200/60' : 'bg-slate-50 text-slate-400' }}">
                                            {{ $service->registrations_count }} Pendaftar
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 align-middle text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button onclick="openExtraModal('{{ addslashes($service->name) }}', '{{ addslashes($service->code) }}', '{{ $service->spmb_unit_id }}', '{{ $service->is_active }}', false, '{{ route('admin.spmb-settings.extra-services.update', $service->id) }}', {{ json_encode($service->applicable_types ?? []) }}, {{ json_encode($service->applicable_class_programs ?? []) }}, {{ json_encode($service->applicable_waves ?? []) }}, {{ json_encode($service->applicable_periods ?? []) }}, {{ json_encode($service->applicable_grades ?? []) }})" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-brand-emerald text-xs font-bold text-brand-emerald transition hover:bg-emerald-800 hover:border-emerald-800 hover:text-white cursor-pointer" title="Edit Layanan">
                                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                                <span>Edit</span>
                                            </button>
                                            @if($service->registrations_count > 0)
                                                <button type="button" onclick="showToast('Peringatan: Tidak dapat menghapus Layanan karena sudah digunakan oleh pendaftar!', 'error')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white cursor-pointer" title="Hapus Layanan">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            @else
                                                <button type="button" onclick="confirmDelete('{{ route('admin.spmb-settings.extra-services.delete', $service->id) }}', 'Apakah Anda yakin ingin menghapus Layanan ini? Data yang terhapus tidak dapat dikembalikan.')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white cursor-pointer" title="Hapus Layanan">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400 text-xs">Belum ada layanan non-formal yang ditambahkan.</td>
                                </tr>
                            @endforelse
                            <tr id="emptyExtraRow-filtered" class="hidden">
                                <td colspan="7" class="py-8 text-center text-slate-400 text-xs">Tidak ada layanan non-formal untuk unit yang dipilih.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal for Extra Service (Layanan Non-Formal) -->
        <div id="extraModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/50 backdrop-blur-xs opacity-0 pointer-events-none transition-all duration-150 overflow-y-auto overscroll-contain">
            <div class="bg-white dark:bg-slate-900 w-full max-w-xl flex flex-col rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-800 transform scale-95 transition-all duration-150 max-h-[calc(100dvh-1.5rem)] sm:max-h-[90vh] my-auto overflow-hidden" id="extraModalBody">
                <div class="px-5 sm:px-6 py-4 sm:py-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/30 shrink-0">
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-800 dark:text-white" id="extraModalTitle">Tambah Layanan Non-Formal</h2>
                    <button onclick="closeExtraModal()" type="button" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 transition">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form id="extraForm" method="POST" action="" hx-boost="false" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                    @csrf
                    <div id="extraMethod"></div>
                    @if($errors->any() && session('failed_modal') && str_starts_with(session('failed_modal'), 'extra_'))
                        <div class="spmb-unit-errors mx-5 sm:mx-6 mt-4 text-xs text-red-650 bg-red-50 dark:bg-red-950/40 p-3.5 rounded-xl border border-red-200 dark:border-red-900/40 font-semibold space-y-1">
                            @foreach($errors->all() as $error)
                                <p>⚠️ {{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                    <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1 min-h-0 overscroll-contain touch-pan-y" style="-webkit-overflow-scrolling: touch;">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">Nama Layanan</label>
                            <input type="text" id="extraNameInput" name="name" required class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: Taman Penitipan Anak (TPA)">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">Kode Layanan</label>
                            <input type="text" id="extraCodeInput" name="code" required class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: TPA">
                        </div>
                        @if($isSuperAdmin)
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">Unit Asal (Terkait Unit Sekolah)</label>
                            <select id="extraUnitInput" name="spmb_unit_id" onchange="filterExtraTargetingByUnit(this.value)" class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold">
                                <option value="">Semua Unit (Umum)</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" id="extraUnitInput" name="spmb_unit_id" value="{{ auth()->user()->spmb_unit_id }}">
                        @endif

                        <!-- Kriteria Keterbukaan Layanan (Jalur, Kategori, Tingkatan, Gelombang, Periode) -->
                        <div class="p-4 bg-slate-50/80 dark:bg-slate-800/40 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 space-y-4">
                            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                                <i data-lucide="sliders-horizontal" class="w-4 h-4 text-brand-emerald"></i>
                                <span>Kriteria Keterbukaan Layanan</span>
                            </div>
                            <p class="text-[10.5px] text-slate-400 dark:text-slate-500 leading-relaxed">
                                Tentukan kapan layanan ini dapat dipilih oleh calon murid. Jika <strong>tidak dicentang</strong> atau <strong>Pilih Semua</strong>, layanan ini akan otomatis berlaku untuk <strong>semua</strong> jalur, kategori, tingkatan, gelombang, atau tahun ajaran.
                            </p>

                            <!-- 1. Target Jalur Pendaftaran -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Jalur Pendaftaran</label>
                                    <label class="flex items-center gap-1.5 text-[10.5px] font-bold text-brand-emerald cursor-pointer hover:underline">
                                        <input type="checkbox" id="checkAllExtraTypes" onchange="toggleAllExtraTypes(this)" class="rounded text-brand-emerald focus:ring-brand-emerald w-3.5 h-3.5">
                                        Pilih Semua Jalur
                                    </label>
                                </div>
                                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 max-h-32 overflow-y-auto">
                                    <div id="extraTypeCheckboxesList" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($types as $type)
                                            @php
                                                $typeUnitIds = $type->units->pluck('id')->toArray();
                                            @endphp
                                            <label class="extra-type-item flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer p-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition" data-unit-ids="{{ implode(',', $typeUnitIds) }}">
                                                <input type="checkbox" name="applicable_types[]" value="{{ $type->id }}" class="extra-type-checkbox rounded text-brand-emerald focus:ring-brand-emerald w-4 h-4 border-slate-300" onchange="updateCheckAllExtraTypesState()">
                                                <span class="truncate">{{ $type->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Target Kategori Murid (Program Kelas) -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Kategori Murid</label>
                                    <label class="flex items-center gap-1.5 text-[10.5px] font-bold text-brand-emerald cursor-pointer hover:underline">
                                        <input type="checkbox" id="checkAllExtraClassPrograms" onchange="toggleAllExtraClassPrograms(this)" class="rounded text-brand-emerald focus:ring-brand-emerald w-3.5 h-3.5">
                                        Pilih Semua Kategori
                                    </label>
                                </div>
                                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 max-h-32 overflow-y-auto">
                                    <div id="extraClassProgramCheckboxesList" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($classPrograms as $prog)
                                            @php
                                                $progUnitIds = $prog->units->pluck('id')->toArray();
                                            @endphp
                                            <label class="extra-program-item flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer p-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition" data-unit-ids="{{ implode(',', $progUnitIds) }}">
                                                <input type="checkbox" name="applicable_class_programs[]" value="{{ $prog->id }}" class="extra-program-checkbox rounded text-brand-emerald focus:ring-brand-emerald w-4 h-4 border-slate-300" onchange="updateCheckAllExtraClassProgramsState()">
                                                <span class="truncate">{{ $prog->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Target Tingkatan Kelas -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Tingkatan Kelas</label>
                                    <label class="flex items-center gap-1.5 text-[10.5px] font-bold text-brand-emerald cursor-pointer hover:underline">
                                        <input type="checkbox" id="checkAllExtraGrades" onchange="toggleAllExtraGrades(this)" class="rounded text-brand-emerald focus:ring-brand-emerald w-3.5 h-3.5">
                                        Pilih Semua Tingkatan
                                    </label>
                                </div>
                                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 max-h-36 overflow-y-auto">
                                    <div id="extraGradeCheckboxesList" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($grades as $gradeItem)
                                            <label class="extra-grade-item flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer p-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition" data-unit-id="{{ $gradeItem->spmb_unit_id }}">
                                                <input type="checkbox" name="applicable_grades[]" value="{{ $gradeItem->id }}" class="extra-grade-checkbox rounded text-brand-emerald focus:ring-brand-emerald w-4 h-4 border-slate-300" onchange="updateCheckAllExtraGradesState()">
                                                <span class="truncate">{{ $gradeItem->name }} <span class="text-[10px] text-slate-400">({{ $gradeItem->unit->name ?? 'Unit' }})</span></span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Target Gelombang Pendaftaran -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Gelombang Pendaftaran</label>
                                    <label class="flex items-center gap-1.5 text-[10.5px] font-bold text-brand-emerald cursor-pointer hover:underline">
                                        <input type="checkbox" id="checkAllExtraWaves" onchange="toggleAllExtraWaves(this)" class="rounded text-brand-emerald focus:ring-brand-emerald w-3.5 h-3.5">
                                        Pilih Semua Gelombang
                                    </label>
                                </div>
                                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 max-h-32 overflow-y-auto">
                                    <div id="extraWaveCheckboxesList" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($waves as $wave)
                                            <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer p-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                                <input type="checkbox" name="applicable_waves[]" value="{{ $wave->id }}" class="extra-wave-checkbox rounded text-brand-emerald focus:ring-brand-emerald w-4 h-4 border-slate-300" onchange="updateCheckAllExtraWavesState()">
                                                <span class="truncate">{{ $wave->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Target Tahun Ajaran / Periode -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Tahun Ajaran / Periode</label>
                                    <label class="flex items-center gap-1.5 text-[10.5px] font-bold text-brand-emerald cursor-pointer hover:underline">
                                        <input type="checkbox" id="checkAllExtraPeriods" onchange="toggleAllExtraPeriods(this)" class="rounded text-brand-emerald focus:ring-brand-emerald w-3.5 h-3.5">
                                        Pilih Semua Periode
                                    </label>
                                </div>
                                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 max-h-32 overflow-y-auto">
                                    <div id="extraPeriodCheckboxesList" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($periods as $per)
                                            <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer p-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                                <input type="checkbox" name="applicable_periods[]" value="{{ $per->id }}" class="extra-period-checkbox rounded text-brand-emerald focus:ring-brand-emerald w-4 h-4 border-slate-300" onchange="updateCheckAllExtraPeriodsState()">
                                                <span class="truncate">{{ $per->year }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <input type="checkbox" id="extraActiveInput" name="is_active" value="1" class="w-4 h-4 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                            <label for="extraActiveInput" class="text-sm font-bold text-slate-700 dark:text-slate-300">Layanan Aktif</label>
                        </div>
                    </div>
                    <div class="px-5 sm:px-6 py-3.5 sm:py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40 rounded-b-3xl flex justify-end gap-3 shrink-0">
                        <button type="button" onclick="closeExtraModal()" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-brand-emerald hover-emerald transition shadow-sm" id="extraSubmitBtn">Simpan Layanan</button>
                    </div>
                </form>
            </div>
        </div>

    <!-- Modal for Unit -->
    <div id="unitModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/50 backdrop-blur-xs opacity-0 pointer-events-none transition-all duration-150 overflow-y-auto overscroll-contain">
        <div class="bg-white dark:bg-slate-900 w-full max-w-md flex flex-col rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-800 transform scale-95 transition-all duration-150 max-h-[calc(100dvh-1.5rem)] sm:max-h-[90vh] my-auto overflow-hidden" id="unitModalBody">
            <div class="px-5 sm:px-6 py-4 sm:py-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/30 shrink-0">
                <h2 class="text-base sm:text-lg font-extrabold text-slate-800 dark:text-white" id="unitModalTitle">Tambah Unit Pendaftaran</h2>
                <button onclick="closeUnitModal()" type="button" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="unitForm" method="POST" action="" hx-boost="false" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                @csrf
                <div id="unitMethod"></div>
                @if($errors->any() && session('failed_modal') && str_starts_with(session('failed_modal'), 'unit_'))
                    <div class="spmb-unit-errors mx-5 sm:mx-6 mt-4 text-xs text-red-655 bg-red-50 dark:bg-red-950/40 p-3.5 rounded-xl border border-red-200 dark:border-red-900/40 font-semibold space-y-1">
                        @foreach($errors->all() as $error)
                            <p>⚠️ {{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1 min-h-0 overscroll-contain touch-pan-y" style="-webkit-overflow-scrolling: touch;">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">Nama Unit Sekolah</label>
                        <input type="text" id="unitNameInput" name="name" required class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: SANS SD">
                    </div>
                    <div id="unitCodeGroup">
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">Kode Unit</label>
                        <input type="text" id="unitCodeInput" name="code" class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: SD (Opsional)">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">No. WhatsApp Admin Unit</label>
                        <input type="text" id="unitWhatsappInput" name="whatsapp_number" class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: 081234567890">
                        <span class="text-[10px] text-slate-400 mt-1 block">Nomor ini akan dihubungi oleh orang tua calon murid unit ini.</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">Nama Kontak / Petugas (Opsional)</label>
                        <input type="text" id="unitAdminContactInput" name="admin_contact_name" class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: Kak Nisa - Admin PAUD">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">Link WhatsApp Group SPMB (Unit)</label>
                        <input type="url" id="unitGroupUrlInput" name="spmb_group_url" class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: https://chat.whatsapp.com/XXXXX">
                        <span class="text-[10px] text-slate-400 mt-1 block">Tautan group WhatsApp resmi untuk informasi seputar SPMB unit ini (ditampilkan pada tahap Ta'aruf).</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="unitActiveInput" name="is_active" value="1" class="w-4 h-4 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                        <label for="unitActiveInput" class="text-sm font-bold text-slate-700 dark:text-slate-300">Unit Aktif</label>
                    </div>
                </div>
                <div class="px-5 sm:px-6 py-3.5 sm:py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40 rounded-b-3xl flex justify-end gap-3 shrink-0">
                    <button type="button" onclick="closeUnitModal()" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-brand-emerald hover-emerald transition shadow-sm" id="unitSubmitBtn">Simpan Unit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for Grade -->
    <div id="gradeModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/50 backdrop-blur-xs opacity-0 pointer-events-none transition-all duration-150 overflow-y-auto overscroll-contain">
        <div class="bg-white dark:bg-slate-900 w-full max-w-xl flex flex-col rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-800 transform scale-95 transition-all duration-150 max-h-[calc(100dvh-1.5rem)] sm:max-h-[90vh] my-auto overflow-hidden" id="gradeModalBody">
            <div class="px-5 sm:px-6 py-4 sm:py-5 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/30 shrink-0">
                <h2 class="text-base sm:text-lg font-extrabold text-slate-800 dark:text-white" id="gradeModalTitle">Tambah Tingkatan Kelas</h2>
                <button onclick="closeGradeModal()" type="button" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="gradeForm" method="POST" action="" hx-boost="false" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                @csrf
                <div id="gradeMethod"></div>
                @if($errors->any() && session('failed_modal') && str_starts_with(session('failed_modal'), 'grade_'))
                    <div class="spmb-unit-errors mx-5 sm:mx-6 mt-4 text-xs text-red-655 bg-red-50 dark:bg-red-950/40 p-3.5 rounded-xl border border-red-200 dark:border-red-900/40 font-semibold space-y-1">
                        @foreach($errors->all() as $error)
                            <p>⚠️ {{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1 min-h-0 overscroll-contain touch-pan-y" style="-webkit-overflow-scrolling: touch;">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">Tingkatan (Grade)</label>
                        <input type="text" id="gradeNameInput" name="name" required class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: TK A, Kelas 1">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">Sub-Unit (Opsional)</label>
                        <input type="text" id="gradeSubUnitInput" name="sub_unit" list="subUnitSuggestions" class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: Playgroup, TK, Daycare (Kosongkan jika bukan sub-unit)">
                        <datalist id="subUnitSuggestions">
                            <option value="Playgroup">
                            <option value="TK">
                            <option value="Daycare">
                        </datalist>
                        <span class="text-[10px] text-slate-400 mt-1 block">Digunakan khusus unit multi-layanan seperti PAUD (Playgroup, TK, Daycare) untuk membedakan kategori layanan.</span>
                    </div>
                    @if($isSuperAdmin)
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">Unit Terkait</label>
                        <select id="gradeUnitInput" name="spmb_unit_id" required onchange="filterGradeTargetingByUnit(this.value)" class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold">
                            <option value="">-- Pilih Unit --</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <input type="hidden" id="gradeUnitInput" name="spmb_unit_id" value="{{ auth()->user()->spmb_unit_id }}">
                    @endif

                    <!-- Kriteria Keterbukaan Kelas (Jalur, Kategori, Gelombang, Periode) -->
                    <div class="p-4 bg-slate-50/80 dark:bg-slate-800/40 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 space-y-4">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                            <i data-lucide="sliders-horizontal" class="w-4 h-4 text-brand-emerald"></i>
                            <span>Kriteria Keterbukaan Tingkatan</span>
                        </div>
                        <p class="text-[10.5px] text-slate-400 dark:text-slate-500 leading-relaxed">
                            Tentukan kapan tingkatan ini dapat dipilih oleh calon murid. Jika <strong>tidak dicentang</strong> atau <strong>Pilih Semua</strong>, tingkatan ini akan otomatis berlaku untuk <strong>semua</strong> jalur, kategori, gelombang, atau tahun ajaran.
                        </p>

                        <!-- 1. Target Jalur Pendaftaran -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Jalur Pendaftaran</label>
                                <label class="flex items-center gap-1.5 text-[10.5px] font-bold text-brand-emerald cursor-pointer hover:underline">
                                    <input type="checkbox" id="checkAllGradeTypes" onchange="toggleAllGradeTypes(this)" class="rounded text-brand-emerald focus:ring-brand-emerald w-3.5 h-3.5">
                                    Pilih Semua Jalur
                                </label>
                            </div>
                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 max-h-32 overflow-y-auto">
                                <div id="gradeTypeCheckboxesList" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($types as $type)
                                        @php
                                            $typeUnitIds = $type->units->pluck('id')->toArray();
                                        @endphp
                                        <label class="grade-type-item flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer p-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition" data-unit-ids="{{ implode(',', $typeUnitIds) }}">
                                            <input type="checkbox" name="applicable_types[]" value="{{ $type->id }}" class="grade-type-checkbox rounded text-brand-emerald focus:ring-brand-emerald w-4 h-4 border-slate-300" onchange="updateCheckAllGradeTypesState()">
                                            <span class="truncate">{{ $type->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- 2. Target Kategori Murid (Program Kelas) -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Kategori Murid</label>
                                <label class="flex items-center gap-1.5 text-[10.5px] font-bold text-brand-emerald cursor-pointer hover:underline">
                                    <input type="checkbox" id="checkAllGradeClassPrograms" onchange="toggleAllGradeClassPrograms(this)" class="rounded text-brand-emerald focus:ring-brand-emerald w-3.5 h-3.5">
                                    Pilih Semua Kategori
                                </label>
                            </div>
                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 max-h-32 overflow-y-auto">
                                <div id="gradeClassProgramCheckboxesList" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($classPrograms as $prog)
                                        @php
                                            $progUnitIds = $prog->units->pluck('id')->toArray();
                                        @endphp
                                        <label class="grade-program-item flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer p-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition" data-unit-ids="{{ implode(',', $progUnitIds) }}">
                                            <input type="checkbox" name="applicable_class_programs[]" value="{{ $prog->id }}" class="grade-program-checkbox rounded text-brand-emerald focus:ring-brand-emerald w-4 h-4 border-slate-300" onchange="updateCheckAllGradeClassProgramsState()">
                                            <span class="truncate">{{ $prog->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- 3. Target Gelombang Pendaftaran -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Gelombang Pendaftaran</label>
                                <label class="flex items-center gap-1.5 text-[10.5px] font-bold text-brand-emerald cursor-pointer hover:underline">
                                    <input type="checkbox" id="checkAllGradeWaves" onchange="toggleAllGradeWaves(this)" class="rounded text-brand-emerald focus:ring-brand-emerald w-3.5 h-3.5">
                                    Pilih Semua Gelombang
                                </label>
                            </div>
                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 max-h-32 overflow-y-auto">
                                <div id="gradeWaveCheckboxesList" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($waves as $wave)
                                        <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer p-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                            <input type="checkbox" name="applicable_waves[]" value="{{ $wave->id }}" class="grade-wave-checkbox rounded text-brand-emerald focus:ring-brand-emerald w-4 h-4 border-slate-300" onchange="updateCheckAllGradeWavesState()">
                                            <span class="truncate">{{ $wave->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- 4. Target Tahun Ajaran / Periode -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Tahun Ajaran / Periode</label>
                                <label class="flex items-center gap-1.5 text-[10.5px] font-bold text-brand-emerald cursor-pointer hover:underline">
                                    <input type="checkbox" id="checkAllGradePeriods" onchange="toggleAllGradePeriods(this)" class="rounded text-brand-emerald focus:ring-brand-emerald w-3.5 h-3.5">
                                    Pilih Semua Periode
                                </label>
                            </div>
                            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 max-h-32 overflow-y-auto">
                                <div id="gradePeriodCheckboxesList" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($periods as $per)
                                        <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer p-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                            <input type="checkbox" name="applicable_periods[]" value="{{ $per->id }}" class="grade-period-checkbox rounded text-brand-emerald focus:ring-brand-emerald w-4 h-4 border-slate-300" onchange="updateCheckAllGradePeriodsState()">
                                            <span class="truncate">{{ $per->year }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Batas Usia Minimal & Maksimal -->
                    <div class="p-4 bg-slate-50/80 dark:bg-slate-800/40 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 space-y-3.5">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-700 dark:text-slate-300">
                            <i data-lucide="clock" class="w-4 h-4 text-brand-emerald"></i>
                            <span>Konfigurasi Batas Usia / Umur</span>
                        </div>
                        <p class="text-[10.5px] text-slate-400 dark:text-slate-500 leading-relaxed">Dihitung relatif per 1 Juli tahun ajaran aktif. Kosongkan jika tanpa batasan usia.</p>
                        
                        <div class="space-y-3 pt-0.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1.5">Usia Minimal</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="relative flex items-center">
                                        <input type="number" id="gradeMinAgeYearsInput" name="min_age_years" min="0" max="25" class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 rounded-xl pl-3.5 pr-14 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-semibold" placeholder="0">
                                        <span class="absolute right-3 text-[11px] font-bold text-slate-400 pointer-events-none select-none">Tahun</span>
                                    </div>
                                    <div class="relative flex items-center">
                                        <input type="number" id="gradeMinAgeMonthsInput" name="min_age_months" min="0" max="11" class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 rounded-xl pl-3.5 pr-14 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-semibold" placeholder="0" value="0">
                                        <span class="absolute right-3 text-[11px] font-bold text-slate-400 pointer-events-none select-none">Bulan</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1.5">Usia Maksimal</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="relative flex items-center">
                                        <input type="number" id="gradeMaxAgeYearsInput" name="max_age_years" min="0" max="25" class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 rounded-xl pl-3.5 pr-14 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-semibold" placeholder="0">
                                        <span class="absolute right-3 text-[11px] font-bold text-slate-400 pointer-events-none select-none">Tahun</span>
                                    </div>
                                    <div class="relative flex items-center">
                                        <input type="number" id="gradeMaxAgeMonthsInput" name="max_age_months" min="0" max="11" class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 rounded-xl pl-3.5 pr-14 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-semibold" placeholder="0" value="0">
                                        <span class="absolute right-3 text-[11px] font-bold text-slate-400 pointer-events-none select-none">Bulan</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1.5">Catatan Batas Usia (Opsional)</label>
                            <input type="text" id="gradeAgeNotesInput" name="age_notes" class="w-full bg-slate-50 dark:bg-slate-800/60 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-semibold" placeholder="Contoh: Minimal 4 tahun per 1 Juli">
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="gradeActiveInput" name="is_active" value="1" class="w-4 h-4 text-brand-emerald rounded border-slate-300 focus:ring-brand-emerald">
                        <label for="gradeActiveInput" class="text-sm font-bold text-slate-700 dark:text-slate-300">Tingkatan Aktif</label>
                    </div>
                </div>
                <div class="px-5 sm:px-6 py-3.5 sm:py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/40 rounded-b-3xl flex justify-end gap-3 shrink-0">
                    <button type="button" onclick="closeGradeModal()" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition">Batal</button>
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
            
            const codeGroup = document.getElementById('unitCodeGroup');
            if (!isCreate) {
                methodDiv.innerHTML = '<input type="hidden" name="_method" value="PUT">';
                if (codeGroup) {
                    codeGroup.classList.add('hidden');
                }
            } else {
                methodDiv.innerHTML = '';
                if (codeGroup) {
                    codeGroup.classList.remove('hidden');
                }
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

        // Grade Dynamic Targeting Checkbox Helpers
        window.filterGradeTargetingByUnit = function(unitId) {
            const uId = unitId ? unitId.toString().trim() : '';
            // Filter Types
            document.querySelectorAll('.grade-type-item').forEach(item => {
                const itemUnits = (item.dataset.unitIds || '').split(',').filter(Boolean);
                if (!uId || itemUnits.length === 0 || itemUnits.includes(uId)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
            // Filter Class Programs
            document.querySelectorAll('.grade-program-item').forEach(item => {
                const itemUnits = (item.dataset.unitIds || '').split(',').filter(Boolean);
                if (!uId || itemUnits.length === 0 || itemUnits.includes(uId)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        };

        window.toggleAllGradeTypes = function(master) {
            document.querySelectorAll('.grade-type-checkbox').forEach(cb => {
                // only toggle visible ones
                const parent = cb.closest('.grade-type-item');
                if (!parent || parent.style.display !== 'none') {
                    cb.checked = master.checked;
                }
            });
        };
        window.updateCheckAllGradeTypesState = function() {
            const visibleCbs = Array.from(document.querySelectorAll('.grade-type-checkbox')).filter(cb => {
                const parent = cb.closest('.grade-type-item');
                return !parent || parent.style.display !== 'none';
            });
            const allChecked = visibleCbs.length > 0 && visibleCbs.every(cb => cb.checked);
            const master = document.getElementById('checkAllGradeTypes');
            if (master) master.checked = allChecked;
        };

        window.toggleAllGradeClassPrograms = function(master) {
            document.querySelectorAll('.grade-program-checkbox').forEach(cb => {
                const parent = cb.closest('.grade-program-item');
                if (!parent || parent.style.display !== 'none') {
                    cb.checked = master.checked;
                }
            });
        };
        window.updateCheckAllGradeClassProgramsState = function() {
            const visibleCbs = Array.from(document.querySelectorAll('.grade-program-checkbox')).filter(cb => {
                const parent = cb.closest('.grade-program-item');
                return !parent || parent.style.display !== 'none';
            });
            const allChecked = visibleCbs.length > 0 && visibleCbs.every(cb => cb.checked);
            const master = document.getElementById('checkAllGradeClassPrograms');
            if (master) master.checked = allChecked;
        };

        window.toggleAllGradeWaves = function(master) {
            document.querySelectorAll('.grade-wave-checkbox').forEach(cb => {
                cb.checked = master.checked;
            });
        };
        window.updateCheckAllGradeWavesState = function() {
            const cbs = Array.from(document.querySelectorAll('.grade-wave-checkbox'));
            const allChecked = cbs.length > 0 && cbs.every(cb => cb.checked);
            const master = document.getElementById('checkAllGradeWaves');
            if (master) master.checked = allChecked;
        };

        window.toggleAllGradePeriods = function(master) {
            document.querySelectorAll('.grade-period-checkbox').forEach(cb => {
                cb.checked = master.checked;
            });
        };
        window.updateCheckAllGradePeriodsState = function() {
            const cbs = Array.from(document.querySelectorAll('.grade-period-checkbox'));
            const allChecked = cbs.length > 0 && cbs.every(cb => cb.checked);
            const master = document.getElementById('checkAllGradePeriods');
            if (master) master.checked = allChecked;
        };

        // Modal Grade
        window.openGradeModal = function(name = '', unitId = '', subUnit = '', minAgeYears = '', minAgeMonths = '0', maxAgeYears = '', maxAgeMonths = '0', ageNotes = '', isActive = '1', isCreate = true, actionUrl = '', applicableTypes = [], applicableClassPrograms = [], applicableWaves = [], applicablePeriods = []) {
            window.clearModalErrors();
            
            const modal = document.getElementById('gradeModal');
            const modalBody = document.getElementById('gradeModalBody');
            const form = document.getElementById('gradeForm');
            const methodDiv = document.getElementById('gradeMethod');
            
            document.getElementById('gradeModalTitle').innerText = isCreate ? 'Tambah Tingkatan Kelas' : 'Edit Tingkatan Kelas';
            document.getElementById('gradeSubmitBtn').innerText = isCreate ? 'Simpan Tingkatan' : 'Perbarui Tingkatan';
            
            form.setAttribute('action', actionUrl);
            document.getElementById('gradeNameInput').value = name;
            document.getElementById('gradeSubUnitInput').value = subUnit || '';
            const targetUnitId = unitId || window.currentUnitFilter || '';
            document.getElementById('gradeUnitInput').value = targetUnitId;
            document.getElementById('gradeMinAgeYearsInput').value = minAgeYears;
            document.getElementById('gradeMinAgeMonthsInput').value = (minAgeMonths !== '' && minAgeMonths !== null) ? minAgeMonths : '0';
            document.getElementById('gradeMaxAgeYearsInput').value = maxAgeYears;
            document.getElementById('gradeMaxAgeMonthsInput').value = (maxAgeMonths !== '' && maxAgeMonths !== null) ? maxAgeMonths : '0';
            document.getElementById('gradeAgeNotesInput').value = ageNotes;
            document.getElementById('gradeActiveInput').checked = (isActive == '1' || isActive == true || isActive == 'true');
            
            // Filter targeting options by unit
            window.filterGradeTargetingByUnit(targetUnitId);

            // Populate checkboxes
            // 1. Jalur Pendaftaran (If null/empty, default to all checked)
            const isAllTypes = !applicableTypes || applicableTypes.length === 0;
            const typesArr = (applicableTypes || []).map(x => parseInt(x));
            document.querySelectorAll('.grade-type-checkbox').forEach(cb => {
                cb.checked = isAllTypes || typesArr.includes(parseInt(cb.value));
            });
            window.updateCheckAllGradeTypesState();

            // 2. Kategori Murid (If null/empty, default to all checked)
            const isAllProgs = !applicableClassPrograms || applicableClassPrograms.length === 0;
            const progsArr = (applicableClassPrograms || []).map(x => parseInt(x));
            document.querySelectorAll('.grade-program-checkbox').forEach(cb => {
                cb.checked = isAllProgs || progsArr.includes(parseInt(cb.value));
            });
            window.updateCheckAllGradeClassProgramsState();

            // 3. Gelombang Pendaftaran (If null/empty, default to all checked)
            const isAllWaves = !applicableWaves || applicableWaves.length === 0;
            const wavesArr = (applicableWaves || []).map(x => parseInt(x));
            document.querySelectorAll('.grade-wave-checkbox').forEach(cb => {
                cb.checked = isAllWaves || wavesArr.includes(parseInt(cb.value));
            });
            window.updateCheckAllGradeWavesState();

            // 4. Tahun Ajaran / Periode (If null/empty, default to all checked)
            const isAllPeriods = !applicablePeriods || applicablePeriods.length === 0;
            const periodsArr = (applicablePeriods || []).map(x => parseInt(x));
            document.querySelectorAll('.grade-period-checkbox').forEach(cb => {
                cb.checked = isAllPeriods || periodsArr.includes(parseInt(cb.value));
            });
            window.updateCheckAllGradePeriodsState();

            if (!isCreate) {
                methodDiv.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            } else {
                methodDiv.innerHTML = '';
            }
            
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalBody.classList.remove('scale-95');
            modalBody.classList.add('scale-100');
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
            }
        };

        window.closeGradeModal = function() {
            window.clearModalErrors();
            const modal = document.getElementById('gradeModal');
            const modalBody = document.getElementById('gradeModalBody');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modalBody.classList.remove('scale-100');
            modalBody.classList.add('scale-95');
        };

        // Extra Services Dynamic Targeting Checkbox Helpers
        window.filterExtraTargetingByUnit = function(unitId) {
            const uId = unitId ? unitId.toString().trim() : '';
            // Filter Types
            document.querySelectorAll('.extra-type-item').forEach(item => {
                const itemUnits = (item.dataset.unitIds || '').split(',').filter(Boolean);
                if (!uId || itemUnits.length === 0 || itemUnits.includes(uId)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
            // Filter Class Programs
            document.querySelectorAll('.extra-program-item').forEach(item => {
                const itemUnits = (item.dataset.unitIds || '').split(',').filter(Boolean);
                if (!uId || itemUnits.length === 0 || itemUnits.includes(uId)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
            // Filter Grades
            document.querySelectorAll('.extra-grade-item').forEach(item => {
                const gUnit = (item.dataset.unitId || '').toString().trim();
                if (!uId || gUnit === uId) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        };

        window.toggleAllExtraTypes = function(master) {
            document.querySelectorAll('.extra-type-checkbox').forEach(cb => {
                const parent = cb.closest('.extra-type-item');
                if (!parent || parent.style.display !== 'none') {
                    cb.checked = master.checked;
                }
            });
        };
        window.updateCheckAllExtraTypesState = function() {
            const visibleCbs = Array.from(document.querySelectorAll('.extra-type-checkbox')).filter(cb => {
                const parent = cb.closest('.extra-type-item');
                return !parent || parent.style.display !== 'none';
            });
            const allChecked = visibleCbs.length > 0 && visibleCbs.every(cb => cb.checked);
            const master = document.getElementById('checkAllExtraTypes');
            if (master) master.checked = allChecked;
        };

        window.toggleAllExtraClassPrograms = function(master) {
            document.querySelectorAll('.extra-program-checkbox').forEach(cb => {
                const parent = cb.closest('.extra-program-item');
                if (!parent || parent.style.display !== 'none') {
                    cb.checked = master.checked;
                }
            });
        };
        window.updateCheckAllExtraClassProgramsState = function() {
            const visibleCbs = Array.from(document.querySelectorAll('.extra-program-checkbox')).filter(cb => {
                const parent = cb.closest('.extra-program-item');
                return !parent || parent.style.display !== 'none';
            });
            const allChecked = visibleCbs.length > 0 && visibleCbs.every(cb => cb.checked);
            const master = document.getElementById('checkAllExtraClassPrograms');
            if (master) master.checked = allChecked;
        };

        window.toggleAllExtraGrades = function(master) {
            document.querySelectorAll('.extra-grade-checkbox').forEach(cb => {
                const parent = cb.closest('.extra-grade-item');
                if (!parent || parent.style.display !== 'none') {
                    cb.checked = master.checked;
                }
            });
        };
        window.updateCheckAllExtraGradesState = function() {
            const visibleCbs = Array.from(document.querySelectorAll('.extra-grade-checkbox')).filter(cb => {
                const parent = cb.closest('.extra-grade-item');
                return !parent || parent.style.display !== 'none';
            });
            const allChecked = visibleCbs.length > 0 && visibleCbs.every(cb => cb.checked);
            const master = document.getElementById('checkAllExtraGrades');
            if (master) master.checked = allChecked;
        };

        window.toggleAllExtraWaves = function(master) {
            document.querySelectorAll('.extra-wave-checkbox').forEach(cb => {
                cb.checked = master.checked;
            });
        };
        window.updateCheckAllExtraWavesState = function() {
            const cbs = Array.from(document.querySelectorAll('.extra-wave-checkbox'));
            const allChecked = cbs.length > 0 && cbs.every(cb => cb.checked);
            const master = document.getElementById('checkAllExtraWaves');
            if (master) master.checked = allChecked;
        };

        window.toggleAllExtraPeriods = function(master) {
            document.querySelectorAll('.extra-period-checkbox').forEach(cb => {
                cb.checked = master.checked;
            });
        };
        window.updateCheckAllExtraPeriodsState = function() {
            const cbs = Array.from(document.querySelectorAll('.extra-period-checkbox'));
            const allChecked = cbs.length > 0 && cbs.every(cb => cb.checked);
            const master = document.getElementById('checkAllExtraPeriods');
            if (master) master.checked = allChecked;
        };

        // Modal Extra Service
        window.openExtraModal = function(name = '', code = '', unitId = '', isActive = '1', isCreate = true, actionUrl = '', applicableTypes = [], applicableClassPrograms = [], applicableWaves = [], applicablePeriods = [], applicableGrades = []) {
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
            const targetUnitId = unitId || window.currentUnitFilter || '';
            document.getElementById('extraUnitInput').value = targetUnitId;
            document.getElementById('extraActiveInput').checked = (isActive == '1' || isActive == true || isActive == 'true');
            
            // Filter targeting options by unit
            window.filterExtraTargetingByUnit(targetUnitId);

            // Populate checkboxes
            // 1. Jalur Pendaftaran (If null/empty, default to all checked)
            const isAllTypes = !applicableTypes || applicableTypes.length === 0;
            const typesArr = (applicableTypes || []).map(x => parseInt(x));
            document.querySelectorAll('.extra-type-checkbox').forEach(cb => {
                cb.checked = isAllTypes || typesArr.includes(parseInt(cb.value));
            });
            window.updateCheckAllExtraTypesState();

            // 2. Kategori Murid (If null/empty, default to all checked)
            const isAllProgs = !applicableClassPrograms || applicableClassPrograms.length === 0;
            const progsArr = (applicableClassPrograms || []).map(x => parseInt(x));
            document.querySelectorAll('.extra-program-checkbox').forEach(cb => {
                cb.checked = isAllProgs || progsArr.includes(parseInt(cb.value));
            });
            window.updateCheckAllExtraClassProgramsState();

            // 3. Tingkatan Kelas (If null/empty, default to all checked)
            const isAllGrades = !applicableGrades || applicableGrades.length === 0;
            const gradesArr = (applicableGrades || []).map(x => parseInt(x));
            document.querySelectorAll('.extra-grade-checkbox').forEach(cb => {
                cb.checked = isAllGrades || gradesArr.includes(parseInt(cb.value));
            });
            window.updateCheckAllExtraGradesState();

            // 4. Gelombang Pendaftaran (If null/empty, default to all checked)
            const isAllWaves = !applicableWaves || applicableWaves.length === 0;
            const wavesArr = (applicableWaves || []).map(x => parseInt(x));
            document.querySelectorAll('.extra-wave-checkbox').forEach(cb => {
                cb.checked = isAllWaves || wavesArr.includes(parseInt(cb.value));
            });
            window.updateCheckAllExtraWavesState();

            // 5. Tahun Ajaran / Periode (If null/empty, default to all checked)
            const isAllPeriods = !applicablePeriods || applicablePeriods.length === 0;
            const periodsArr = (applicablePeriods || []).map(x => parseInt(x));
            document.querySelectorAll('.extra-period-checkbox').forEach(cb => {
                cb.checked = isAllPeriods || periodsArr.includes(parseInt(cb.value));
            });
            window.updateCheckAllExtraPeriodsState();

            if (!isCreate) {
                methodDiv.innerHTML = '<input type="hidden" name="_method" value="PUT">';
            } else {
                methodDiv.innerHTML = '';
            }
            
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modalBody.classList.remove('scale-95');
            modalBody.classList.add('scale-100');
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
            }
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
                    window.openGradeModal('{{ old('name') }}', '{{ old('spmb_unit_id') }}', '{{ old('sub_unit') }}', '{{ old('min_age_years') }}', '{{ old('min_age_months', 0) }}', '{{ old('max_age_years') }}', '{{ old('max_age_months', 0) }}', '{{ old('age_notes') }}', '{{ old('is_active') ? 1 : 0 }}', true, '{{ route('admin.spmb-settings.grades.store') }}', {{ json_encode(old('applicable_types', [])) }}, {{ json_encode(old('applicable_class_programs', [])) }}, {{ json_encode(old('applicable_waves', [])) }}, {{ json_encode(old('applicable_periods', [])) }});
                } else if (failed.startsWith('grade_edit_')) {
                    window.switchTab('grade');
                    let id = failed.replace('grade_edit_', '');
                    window.openGradeModal('{{ old('name') }}', '{{ old('spmb_unit_id') }}', '{{ old('sub_unit') }}', '{{ old('min_age_years') }}', '{{ old('min_age_months', 0) }}', '{{ old('max_age_years') }}', '{{ old('max_age_months', 0) }}', '{{ old('age_notes') }}', '{{ old('is_active') ? 1 : 0 }}', false, '/admin/spmb-settings/grades/' + id, {{ json_encode(old('applicable_types', [])) }}, {{ json_encode(old('applicable_class_programs', [])) }}, {{ json_encode(old('applicable_waves', [])) }}, {{ json_encode(old('applicable_periods', [])) }});
                } else if (failed.startsWith('extra_create')) {
                    window.switchTab('extra');
                    window.openExtraModal('{{ old('name') }}', '{{ old('code') }}', '{{ old('spmb_unit_id') }}', '{{ old('is_active') ? 1 : 0 }}', true, '{{ route('admin.spmb-settings.extra-services.store') }}', {{ json_encode(old('applicable_types', [])) }}, {{ json_encode(old('applicable_class_programs', [])) }}, {{ json_encode(old('applicable_waves', [])) }}, {{ json_encode(old('applicable_periods', [])) }}, {{ json_encode(old('applicable_grades', [])) }});
                } else if (failed.startsWith('extra_edit_')) {
                    window.switchTab('extra');
                    let id = failed.replace('extra_edit_', '');
                    window.openExtraModal('{{ old('name') }}', '{{ old('code') }}', '{{ old('spmb_unit_id') }}', '{{ old('is_active') ? 1 : 0 }}', false, '/admin/spmb-settings/extra-services/' + id, {{ json_encode(old('applicable_types', [])) }}, {{ json_encode(old('applicable_class_programs', [])) }}, {{ json_encode(old('applicable_waves', [])) }}, {{ json_encode(old('applicable_periods', [])) }}, {{ json_encode(old('applicable_grades', [])) }});
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
