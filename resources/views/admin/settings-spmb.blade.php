@extends('layouts.admin')

@section('title', 'Jalur & Gelombang - Admin Panel')
@section('page_title', 'Jalur & Gelombang')

@section('content')
<div id="spmb-master-container" hx-boost="true" hx-target="#spmb-master-container" hx-select="#spmb-master-container" class="w-full space-y-6">
    <!-- Header with Unit Filter Selector -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800">Master Jalur & Gelombang</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola kamus data periode akademik, gelombang masuk, kategori jenis pendaftaran, dan kategori murid.</p>
        </div>

        @if($isSuperAdmin)
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500 whitespace-nowrap"><i data-lucide="filter" class="w-3.5 h-3.5 inline text-brand-emerald"></i> Filter Unit:</span>
                <select onchange="window.location.href='{{ route('admin.spmb-settings') }}?tab={{ $activeTab }}&unit_id=' + this.value" 
                        class="py-2 px-3.5 text-xs rounded-xl border border-slate-200 bg-slate-50 font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-emerald cursor-pointer">
                    <option value="" {{ empty($selectedUnitId) ? 'selected' : '' }}>Semua Unit (Yayasan)</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ ($selectedUnitId == $unit->id) ? 'selected' : '' }}>
                            {{ $unit->name }} ({{ strtoupper($unit->code) }})
                        </option>
                    @endforeach
                </select>
            </div>
        @elseif(!empty($selectedUnitId))
            @php $userUnit = $units->firstWhere('id', $selectedUnitId); @endphp
            @if($userUnit)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i data-lucide="building" class="w-3.5 h-3.5"></i> Unit: {{ $userUnit->name }}
                </span>
            @endif
        @endif
    </div>

    <!-- Activation Context Notice -->
    <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 flex items-start gap-3 text-xs text-slate-600">
        <div class="h-6 w-6 rounded-lg bg-emerald-100 text-brand-emerald flex items-center justify-center flex-shrink-0 mt-0.5">
            <i data-lucide="info" class="w-4 h-4"></i>
        </div>
        <div class="flex-1 leading-relaxed">
            @if($isSuperAdmin)
                <span class="font-bold text-slate-800">Petunjuk Status & Visual Redup:</span>
                Komponen dengan baris <strong class="text-slate-500">agak redup (muted)</strong> menandakan gelombang/jalur/periode tersebut saat ini sedang <strong>dinonaktifkan / ditutup</strong> untuk pendaftaran online. Anda dapat membuka atau menutup status pendaftaran per unit secara independen melalui menu <a href="{{ route('admin.spmb-settings.registration', ['unit_id' => $selectedUnitId ?: ($units->first()->id ?? 1)]) }}" class="font-bold text-brand-emerald underline hover:text-emerald-700">Aktivasi SPMB</a>.
            @else
                <span class="font-bold text-slate-800">Mode Informasi (Read-Only):</span>
                Master Jalur, Gelombang, Periode, dan Kategori Murid dikelola secara terpusat oleh Yayasan / Super Admin. Anda dapat melihat informasi dan status keaktifan untuk unit Anda di halaman ini. Untuk membuka atau menutup jalur & gelombang pada unit Anda, silakan gunakan menu <a href="{{ route('admin.spmb-settings.registration') }}" class="font-bold text-brand-emerald underline hover:text-emerald-700">Aktivasi SPMB</a>.
            @endif
        </div>
    </div>

    <!-- Tab Navigation Pills -->
    <div class="flex flex-wrap items-center justify-between gap-2 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex flex-wrap items-center gap-2">
            <button onclick="switchTab('periode')" id="tabBtn-periode" class="tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'periode' ? 'bg-brand-emerald text-white shadow' : 'text-slate-600 hover:bg-slate-50' }}">
                <i data-lucide="calendar" class="w-4 h-4"></i> Periode
            </button>
            <button onclick="switchTab('gelombang')" id="tabBtn-gelombang" class="tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'gelombang' ? 'bg-brand-emerald text-white shadow' : 'text-slate-600 hover:bg-slate-50' }}">
                <i data-lucide="waves" class="w-4 h-4"></i> Gelombang
            </button>
            <button onclick="switchTab('jenis')" id="tabBtn-jenis" class="tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'jenis' ? 'bg-brand-emerald text-white shadow' : 'text-slate-600 hover:bg-slate-50' }}">
                <i data-lucide="tag" class="w-4 h-4"></i> Jenis Pendaftaran
            </button>
            <button onclick="switchTab('program')" id="tabBtn-program" class="tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'program' ? 'bg-brand-emerald text-white shadow' : 'text-slate-600 hover:bg-slate-50' }}">
                <i data-lucide="layers" class="w-4 h-4"></i> Kategori Murid
            </button>
        </div>

        @if($isSuperAdmin)
            @php
                $trashPeriodCount = $periods->where('is_testing', true)->count();
                $trashWaveCount = $waves->where('is_testing', true)->count();
                $trashTypeCount = $types->where('is_testing', true)->count();
                $trashProgramCount = $classPrograms->where('is_testing', true)->count();
                $isAnyTrashTab = in_array($activeTab, ['test_periode', 'test_gelombang', 'test_jenis', 'test_program']);
            @endphp
            <!-- Tong Sampah Dropdown di Pojok Kanan (Khusus Super Admin) -->
            <div class="relative inline-block text-left" id="masterTrashDropdownWrapper">
                <button type="button" onclick="toggleMasterTrashDropdown()" id="masterTrashDropdownBtn" class="px-3.5 py-2 {{ $isAnyTrashTab ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/80' }} rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-xs cursor-pointer">
                    <i data-lucide="trash-2" class="w-4 h-4 {{ $isAnyTrashTab ? 'text-white' : 'text-amber-600' }}"></i>
                    <span>Tong Sampah</span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 {{ $isAnyTrashTab ? 'text-white/80' : 'text-slate-400' }}"></i>
                </button>
                <div id="masterTrashDropdownMenu" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-30 transition-all">
                    <div class="px-3.5 py-2 text-[10px] font-extrabold uppercase tracking-wider text-amber-800 bg-amber-50/70 border-b border-amber-100 mb-1">
                        Terkunci
                    </div>
                    <a href="javascript:void(0)" onclick="switchTab('test_periode'); closeMasterTrashDropdown();" id="trashSubTab-test_periode" class="trash-subtab-btn w-full text-left px-3.5 py-2 text-xs font-bold transition flex items-center justify-between gap-2 cursor-pointer {{ $activeTab === 'test_periode' ? 'bg-amber-100 text-amber-900 font-extrabold border-l-4 border-amber-600' : 'text-slate-700 hover:bg-amber-50 hover:text-amber-800' }}">
                        <div class="flex items-center gap-2">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-amber-600"></i>
                            <span>Periode Akademik</span>
                        </div>
                        @if($trashPeriodCount > 0)
                            <span class="dropdown-item-count px-1.5 py-0.5 text-[9px] font-extrabold rounded-full {{ $activeTab === 'test_periode' ? 'bg-amber-200 text-amber-900' : 'bg-amber-100 text-amber-800' }}">{{ $trashPeriodCount }} Data</span>
                        @else
                            <span class="dropdown-item-count text-[9px] font-semibold text-slate-400">0 Data</span>
                        @endif
                    </a>
                    <a href="javascript:void(0)" onclick="switchTab('test_gelombang'); closeMasterTrashDropdown();" id="trashSubTab-test_gelombang" class="trash-subtab-btn w-full text-left px-3.5 py-2 text-xs font-bold transition flex items-center justify-between gap-2 cursor-pointer {{ $activeTab === 'test_gelombang' ? 'bg-amber-100 text-amber-900 font-extrabold border-l-4 border-amber-600' : 'text-slate-700 hover:bg-amber-50 hover:text-amber-800' }}">
                        <div class="flex items-center gap-2">
                            <i data-lucide="waves" class="w-3.5 h-3.5 text-amber-600"></i>
                            <span>Gelombang</span>
                        </div>
                        @if($trashWaveCount > 0)
                            <span class="dropdown-item-count px-1.5 py-0.5 text-[9px] font-extrabold rounded-full {{ $activeTab === 'test_gelombang' ? 'bg-amber-200 text-amber-900' : 'bg-amber-100 text-amber-800' }}">{{ $trashWaveCount }} Data</span>
                        @else
                            <span class="dropdown-item-count text-[9px] font-semibold text-slate-400">0 Data</span>
                        @endif
                    </a>
                    <a href="javascript:void(0)" onclick="switchTab('test_jenis'); closeMasterTrashDropdown();" id="trashSubTab-test_jenis" class="trash-subtab-btn w-full text-left px-3.5 py-2 text-xs font-bold transition flex items-center justify-between gap-2 cursor-pointer {{ $activeTab === 'test_jenis' ? 'bg-amber-100 text-amber-900 font-extrabold border-l-4 border-amber-600' : 'text-slate-700 hover:bg-amber-50 hover:text-amber-800' }}">
                        <div class="flex items-center gap-2">
                            <i data-lucide="tag" class="w-3.5 h-3.5 text-amber-600"></i>
                            <span>Jenis Pendaftaran</span>
                        </div>
                        @if($trashTypeCount > 0)
                            <span class="dropdown-item-count px-1.5 py-0.5 text-[9px] font-extrabold rounded-full {{ $activeTab === 'test_jenis' ? 'bg-amber-200 text-amber-900' : 'bg-amber-100 text-amber-800' }}">{{ $trashTypeCount }} Data</span>
                        @else
                            <span class="dropdown-item-count text-[9px] font-semibold text-slate-400">0 Data</span>
                        @endif
                    </a>
                    <a href="javascript:void(0)" onclick="switchTab('test_program'); closeMasterTrashDropdown();" id="trashSubTab-test_program" class="trash-subtab-btn w-full text-left px-3.5 py-2 text-xs font-bold transition flex items-center justify-between gap-2 cursor-pointer {{ $activeTab === 'test_program' ? 'bg-amber-100 text-amber-900 font-extrabold border-l-4 border-amber-600' : 'text-slate-700 hover:bg-amber-50 hover:text-amber-800' }}">
                        <div class="flex items-center gap-2">
                            <i data-lucide="layers" class="w-3.5 h-3.5 text-amber-600"></i>
                            <span>Kategori Murid</span>
                        </div>
                        @if($trashProgramCount > 0)
                            <span class="dropdown-item-count px-1.5 py-0.5 text-[9px] font-extrabold rounded-full {{ $activeTab === 'test_program' ? 'bg-amber-200 text-amber-900' : 'bg-amber-100 text-amber-800' }}">{{ $trashProgramCount }} Data</span>
                        @else
                            <span class="dropdown-item-count text-[9px] font-semibold text-slate-400">0 Data</span>
                        @endif
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Tab Contents -->
    <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        
        <!-- Tab 1: Periode (Live) -->
        <div id="tabContent-periode" class="tab-content p-8 space-y-6 {{ $activeTab === 'periode' ? '' : 'hidden' }}">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-extrabold text-base text-slate-800">Tahun Pelajaran (Periode Akademik)</h3>
                    <p class="text-[11px] text-slate-400">Daftar periode ajaran baru. Tentukan 1 <strong>Tahun Default</strong> agar seluruh halaman admin otomatis terbuka pada tahun tersebut.</p>
                </div>
                @if($isSuperAdmin)
                    <button onclick="openModal('periode', '', '', '{{ route('admin.spmb-settings.periods.store') }}')" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1 cursor-pointer">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Periode
                    </button>
                @endif
            </div>
            
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Tahun Pelajaran (Periode)</th>
                            <th class="py-4 px-6 text-center">Status Aktivasi</th>
                            <th class="py-4 px-6 text-center">Tahun Default</th>
                            <th class="py-4 px-6 text-center">Digunakan Transaksi</th>
                            @if($isSuperAdmin)
                                <th class="py-4 px-6 text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($periods->where('is_testing', false) as $period)
                            @php
                                $isRowActive = (bool) ($period->is_active_for_selected ?? true);
                            @endphp
                            <tr class="transition {{ $isRowActive ? 'hover:bg-slate-50/30' : 'opacity-60 bg-slate-50/40 text-slate-400' }}">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-extrabold {{ $isRowActive ? 'text-slate-800' : 'text-slate-500' }}">{{ $period->year }}</span>
                                        @if($period->is_current_default ?? ($period->id == ($defaultPeriodId ?? null) || $period->is_default))
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-brand-emerald border border-emerald-200 shadow-2xs">
                                                <i data-lucide="check-circle-2" class="w-3 h-3 text-brand-emerald"></i> Default Sistem
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if(!empty($selectedUnitId))
                                        @if($isRowActive)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Buka / Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-500 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Tutup / Nonaktif
                                            </span>
                                        @endif
                                    @else
                                        @if($period->active_units && $period->active_units->isNotEmpty())
                                            <div class="flex flex-wrap items-center justify-center gap-1">
                                                @foreach($period->active_units as $au)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-emerald-50 text-brand-emerald border border-emerald-200" title="Aktif di {{ $au->name }}">
                                                        {{ strtoupper($au->code ?: $au->name) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-400 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Tutup di Semua Unit
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if($period->is_current_default ?? ($period->id == ($defaultPeriodId ?? null) || $period->is_default))
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-500 text-white shadow-sm shadow-emerald-200">
                                            <i data-lucide="star" class="w-3.5 h-3.5 fill-white text-white"></i> Default Utama
                                        </span>
                                    @elseif($isSuperAdmin)
                                        <form action="{{ route('admin.spmb-settings.periods.default', $period->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-brand-emerald hover:text-white text-slate-600 transition shadow-2xs cursor-pointer group" title="Jadikan sebagai Tahun Pelajaran default untuk seluruh admin panel">
                                                <i data-lucide="star" class="w-3.5 h-3.5 text-slate-400 group-hover:text-white"></i> Jadikan Default
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400 font-semibold">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex min-w-20 justify-center px-2.5 py-1 rounded-lg text-[10px] font-bold {{ $period->registrations_count > 0 ? 'bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-400' }}">
                                        {{ $period->registrations_count }} Pendaftar
                                    </span>
                                </td>
                                @if($isSuperAdmin)
                                    <td class="py-4 px-6 align-middle text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" onclick="openModal('periode', '{{ $period->year }}', '{{ $period->registrations_count > 0 }}', '{{ route('admin.spmb-settings.periods.update', $period->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-brand-emerald text-xs font-bold text-brand-emerald transition hover:bg-emerald-800 hover:border-emerald-800 hover:text-white cursor-pointer" title="Edit Periode">
                                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                                <span>Edit</span>
                                            </button>
                                            <button type="button" onclick="confirmTrashMaster('Periode', '{{ $period->year }}', '{{ route('admin.spmb-settings.periods.trash', $period->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-amber-300 text-xs font-bold text-amber-800 bg-amber-50 hover:bg-amber-600 hover:border-amber-600 hover:text-white transition shadow-2xs cursor-pointer" title="Pindahkan ke Tong Sampah">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Tong Sampah</span>
                                            </button>
                                            @if($period->registrations_count > 0)
                                                <button type="button" onclick="showToast('Peringatan: Tidak dapat menghapus Periode karena sudah digunakan oleh pendaftar!', 'error')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white cursor-pointer" title="Hapus Periode">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            @else
                                                <button type="button" onclick="deleteItem('periode', '{{ $period->year }}', '{{ $period->registrations_count > 0 }}', '{{ route('admin.spmb-settings.periods.delete', $period->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white cursor-pointer" title="Hapus Periode">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSuperAdmin ? 5 : 4 }}" class="py-8 px-6 text-center text-slate-400">Belum ada data periode akademik aktif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 2: Gelombang (Live) -->
        <div id="tabContent-gelombang" class="tab-content p-8 space-y-6 {{ $activeTab === 'gelombang' ? '' : 'hidden' }}">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-extrabold text-base text-slate-800">Gelombang Masuk Pendaftaran</h3>
                    <p class="text-[11px] text-slate-400">Daftar gelombang penerimaan murid baru (Biaya disetel pada menu Keuangan).</p>
                </div>
                @if($isSuperAdmin)
                    <button onclick="openModal('gelombang', '', '', '{{ route('admin.spmb-settings.waves.store') }}')" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1 cursor-pointer">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Gelombang
                    </button>
                @endif
            </div>
            
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Nama Gelombang</th>
                            <th class="py-4 px-6 text-center">Status Aktivasi</th>
                            <th class="py-4 px-6 text-center">Digunakan Transaksi</th>
                            @if($isSuperAdmin)
                                <th class="py-4 px-6 text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($waves->where('is_testing', false) as $wave)
                            @php
                                $isRowActive = (bool) ($wave->is_active_for_selected ?? true);
                            @endphp
                            <tr class="transition {{ $isRowActive ? 'hover:bg-slate-50/30' : 'opacity-60 bg-slate-50/40 text-slate-400' }}">
                                <td class="py-4 px-6">
                                    <div class="font-extrabold {{ $isRowActive ? 'text-slate-800' : 'text-slate-500' }} flex items-center gap-2">
                                        <span>{{ $wave->name }}</span>
                                        @if(!$isRowActive)
                                            <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full border border-slate-200">Nonaktif</span>
                                        @endif
                                    </div>
                                    @if($wave->description)
                                        <div class="text-[11px] text-slate-400 font-medium mt-0.5">{{ $wave->description }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if(!empty($selectedUnitId))
                                        @if($isRowActive)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Buka / Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-500 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Tutup / Nonaktif
                                            </span>
                                        @endif
                                    @else
                                        @if($wave->active_units && $wave->active_units->isNotEmpty())
                                            <div class="flex flex-wrap items-center justify-center gap-1">
                                                @foreach($wave->active_units as $au)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-emerald-50 text-brand-emerald border border-emerald-200" title="Aktif di {{ $au->name }}">
                                                        {{ strtoupper($au->code ?: $au->name) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-400 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Tutup di Semua Unit
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex min-w-20 justify-center px-2.5 py-1 rounded-lg text-[10px] font-bold {{ $wave->registrations_count > 0 ? 'bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-400' }}">
                                        {{ $wave->registrations_count }} Pendaftar
                                    </span>
                                </td>
                                @if($isSuperAdmin)
                                    <td class="py-4 px-6 align-middle text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" onclick="openModal('gelombang', '{{ addslashes($wave->name) }}', '{{ $wave->registrations_count > 0 }}', '{{ route('admin.spmb-settings.waves.update', $wave->id) }}', '1', '{{ addslashes($wave->description) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-brand-emerald text-xs font-bold text-brand-emerald transition hover:bg-emerald-800 hover:border-emerald-800 hover:text-white cursor-pointer" title="Edit Gelombang">
                                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                                <span>Edit</span>
                                            </button>
                                            <button type="button" onclick="confirmTrashMaster('Gelombang', '{{ addslashes($wave->name) }}', '{{ route('admin.spmb-settings.waves.trash', $wave->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-amber-300 text-xs font-bold text-amber-800 bg-amber-50 hover:bg-amber-600 hover:border-amber-600 hover:text-white transition shadow-2xs cursor-pointer" title="Pindahkan ke Tong Sampah">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Tong Sampah</span>
                                            </button>
                                            @if($wave->registrations_count > 0)
                                                <button type="button" onclick="showToast('Peringatan: Tidak dapat menghapus Gelombang karena sudah digunakan oleh pendaftar!', 'error')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white cursor-pointer" title="Hapus Gelombang">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            @else
                                                <button type="button" onclick="deleteItem('gelombang', '{{ addslashes($wave->name) }}', '{{ $wave->registrations_count > 0 }}', '{{ route('admin.spmb-settings.waves.delete', $wave->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white cursor-pointer" title="Hapus Gelombang">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSuperAdmin ? 4 : 3 }}" class="py-8 px-6 text-center text-slate-400">Belum ada data gelombang pendaftaran aktif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 3: Jenis Pendaftaran (Live) -->
        <div id="tabContent-jenis" class="tab-content p-8 space-y-6 {{ $activeTab === 'jenis' ? '' : 'hidden' }}">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-extrabold text-base text-slate-800">Kategori Jenis Pendaftaran</h3>
                    <p class="text-[11px] text-slate-400">Kelola jenis penerimaan (contoh: Murid Baru, Pindahan Mutasi, dll).</p>
                </div>
                @if($isSuperAdmin)
                    <button onclick="openModal('jenis', '', '', '{{ route('admin.spmb-settings.types.store') }}')" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1 cursor-pointer">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Kategori
                    </button>
                @endif
            </div>
            
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Kategori Jenis</th>
                            <th class="py-4 px-6 text-center">Status Aktivasi</th>
                            <th class="py-4 px-6 text-center">Digunakan Transaksi</th>
                            @if($isSuperAdmin)
                                <th class="py-4 px-6 text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($types->where('is_testing', false) as $type)
                            @php
                                $isRowActive = (bool) ($type->is_active_for_selected ?? true);
                            @endphp
                            <tr class="transition {{ $isRowActive ? 'hover:bg-slate-50/30' : 'opacity-60 bg-slate-50/40 text-slate-400' }}">
                                <td class="py-4 px-6">
                                    <div class="font-extrabold {{ $isRowActive ? 'text-slate-800' : 'text-slate-500' }} flex items-center gap-2">
                                        <span>{{ $type->name }}</span>
                                        @if(!$isRowActive)
                                            <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full border border-slate-200">Nonaktif</span>
                                        @endif
                                    </div>
                                    @if($type->description)
                                        <div class="text-[11px] text-slate-400 font-medium mt-0.5">{{ $type->description }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if(!empty($selectedUnitId))
                                        @if($isRowActive)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Buka / Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-500 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Tutup / Nonaktif
                                            </span>
                                        @endif
                                    @else
                                        @if($type->active_units && $type->active_units->isNotEmpty())
                                            <div class="flex flex-wrap items-center justify-center gap-1">
                                                @foreach($type->active_units as $au)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-emerald-50 text-brand-emerald border border-emerald-200" title="Aktif di {{ $au->name }}">
                                                        {{ strtoupper($au->code ?: $au->name) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-400 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Tutup di Semua Unit
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex min-w-20 justify-center px-2.5 py-1 rounded-lg text-[10px] font-bold {{ $type->registrations_count > 0 ? 'bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-400' }}">
                                        {{ $type->registrations_count }} Pendaftar
                                    </span>
                                </td>
                                @if($isSuperAdmin)
                                    <td class="py-4 px-6 align-middle text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" onclick="openModal('jenis', '{{ addslashes($type->name) }}', '{{ $type->registrations_count > 0 }}', '{{ route('admin.spmb-settings.types.update', $type->id) }}', '1', '{{ addslashes($type->description) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-brand-emerald text-xs font-bold text-brand-emerald transition hover:bg-emerald-800 hover:border-emerald-800 hover:text-white cursor-pointer" title="Edit Jenis Pendaftaran">
                                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                                <span>Edit</span>
                                            </button>
                                            <button type="button" onclick="confirmTrashMaster('Jenis Pendaftaran', '{{ addslashes($type->name) }}', '{{ route('admin.spmb-settings.types.trash', $type->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-amber-300 text-xs font-bold text-amber-800 bg-amber-50 hover:bg-amber-600 hover:border-amber-600 hover:text-white transition shadow-2xs cursor-pointer" title="Pindahkan ke Tong Sampah">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Tong Sampah</span>
                                            </button>
                                            @if($type->registrations_count > 0)
                                                <button type="button" onclick="showToast('Peringatan: Tidak dapat menghapus Jenis Pendaftaran karena sudah digunakan oleh pendaftar!', 'error')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white cursor-pointer" title="Hapus Jenis Pendaftaran">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            @else
                                                <button type="button" onclick="deleteItem('jenis', '{{ addslashes($type->name) }}', '{{ $type->registrations_count > 0 }}', '{{ route('admin.spmb-settings.types.delete', $type->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white cursor-pointer" title="Hapus Jenis Pendaftaran">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSuperAdmin ? 4 : 3 }}" class="py-8 px-6 text-center text-slate-400">Belum ada data jenis pendaftaran aktif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 4: Kategori Murid (Live) -->
        <div id="tabContent-program" class="tab-content p-8 space-y-6 {{ $activeTab === 'program' ? '' : 'hidden' }}">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-extrabold text-base text-slate-800">Kategori Murid</h3>
                    <p class="text-[11px] text-slate-400">Kelola kategori penerimaan murid (contoh: Reguler, Inklusi).</p>
                </div>
                @if($isSuperAdmin)
                    <button onclick="openModal('program', '', '', '{{ route('admin.spmb-settings.class-programs.store') }}')" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1 cursor-pointer">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Kategori Murid
                    </button>
                @endif
            </div>
            
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Nama Kategori Murid</th>
                            <th class="py-4 px-6 text-center">Status Aktivasi</th>
                            <th class="py-4 px-6 text-center">Digunakan Transaksi</th>
                            @if($isSuperAdmin)
                                <th class="py-4 px-6 text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($classPrograms->where('is_testing', false) as $program)
                            @php
                                $isRowActive = (bool) ($program->is_active_for_selected ?? true);
                            @endphp
                            <tr class="transition {{ $isRowActive ? 'hover:bg-slate-50/30' : 'opacity-60 bg-slate-50/40 text-slate-400' }}">
                                <td class="py-4 px-6">
                                    <div class="font-extrabold {{ $isRowActive ? 'text-slate-800' : 'text-slate-500' }} flex items-center gap-2">
                                        <span>{{ $program->name }}</span>
                                        @if(!$isRowActive)
                                            <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full border border-slate-200">Nonaktif</span>
                                        @endif
                                    </div>
                                    @if($program->description)
                                        <div class="text-[11px] text-slate-400 font-medium mt-0.5">{{ $program->description }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if(!empty($selectedUnitId))
                                        @if($isRowActive)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Buka / Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-500 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Tutup / Nonaktif
                                            </span>
                                        @endif
                                    @else
                                        @if($program->active_units && $program->active_units->isNotEmpty())
                                            <div class="flex flex-wrap items-center justify-center gap-1">
                                                @foreach($program->active_units as $au)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black bg-emerald-50 text-brand-emerald border border-emerald-200" title="Aktif di {{ $au->name }}">
                                                        {{ strtoupper($au->code ?: $au->name) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-400 border border-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Tutup di Semua Unit
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex min-w-20 justify-center px-2.5 py-1 rounded-lg text-[10px] font-bold {{ $program->registrations_count > 0 ? 'bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-400' }}">
                                        {{ $program->registrations_count }} Pendaftar
                                    </span>
                                </td>
                                @if($isSuperAdmin)
                                    <td class="py-4 px-6 align-middle text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" onclick="openModal('program', '{{ addslashes($program->name) }}', '{{ $program->registrations_count > 0 }}', '{{ route('admin.spmb-settings.class-programs.update', $program->id) }}', '{{ $program->is_active }}', '{{ addslashes($program->description) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-brand-emerald text-xs font-bold text-brand-emerald transition hover:bg-emerald-800 hover:border-emerald-800 hover:text-white cursor-pointer" title="Edit Kategori Murid">
                                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                                <span>Edit</span>
                                            </button>
                                            <button type="button" onclick="confirmTrashMaster('Kategori Murid', '{{ addslashes($program->name) }}', '{{ route('admin.spmb-settings.class-programs.trash', $program->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-amber-300 text-xs font-bold text-amber-800 bg-amber-50 hover:bg-amber-600 hover:border-amber-600 hover:text-white transition shadow-2xs cursor-pointer" title="Pindahkan ke Tong Sampah">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Tong Sampah</span>
                                            </button>
                                            @if($program->registrations_count > 0)
                                                <button type="button" onclick="showToast('Peringatan: Tidak dapat menghapus Kategori Murid karena sudah digunakan oleh pendaftar!', 'error')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white cursor-pointer" title="Hapus Kategori Murid">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            @else
                                                <button type="button" onclick="deleteItem('program', '{{ addslashes($program->name) }}', '{{ $program->registrations_count > 0 }}', '{{ route('admin.spmb-settings.class-programs.delete', $program->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white cursor-pointer" title="Hapus Kategori Murid">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    <span>Hapus</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSuperAdmin ? 4 : 3 }}" class="py-8 px-6 text-center text-slate-400">Belum ada data kategori murid aktif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($isSuperAdmin)
            <!-- Tab 5: Tong Sampah Periode -->
            <div id="tabContent-test_periode" class="tab-content p-8 space-y-6 {{ $activeTab === 'test_periode' ? '' : 'hidden' }}">
                <div class="bg-amber-50/80 border border-amber-200 rounded-2xl p-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="archive" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm text-amber-950">Tong Sampah: Periode Akademik</h3>
                            <p class="text-[11px] text-amber-800">Menampilkan data periode yang telah diarsipkan / berstatus testing. Data ini diisolasi dan tidak muncul di formulir pendaftaran publik.</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-100 rounded-xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                                <th class="py-4 px-6">Tahun Pelajaran (Periode)</th>
                                <th class="py-4 px-6 text-center">Status</th>
                                <th class="py-4 px-6 text-center">Pendaftar Terkait</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            @forelse($periods->where('is_testing', true) as $period)
                                <tr class="transition hover:bg-slate-50/30">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-extrabold text-slate-700">{{ $period->year }}</span>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                                <i data-lucide="lock" class="w-3 h-3 text-amber-600"></i> Terkunci
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Di Tong Sampah
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="inline-flex min-w-20 justify-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700">
                                            {{ $period->registrations_count }} Pendaftar
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 align-middle text-right whitespace-nowrap">
                                        <button type="button" onclick="confirmRestoreMaster('Periode', '{{ $period->year }}', '{{ route('admin.spmb-settings.periods.restore', $period->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-emerald-300 text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-600 hover:border-emerald-600 hover:text-white transition shadow-2xs cursor-pointer" title="Pulihkan Periode">
                                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                            <span>Pulihkan</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 px-6 text-center text-slate-400 text-xs">Tong sampah periode kosong.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 6: Tong Sampah Gelombang -->
            <div id="tabContent-test_gelombang" class="tab-content p-8 space-y-6 {{ $activeTab === 'test_gelombang' ? '' : 'hidden' }}">
                <div class="bg-amber-50/80 border border-amber-200 rounded-2xl p-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="archive" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm text-amber-950">Tong Sampah: Gelombang Pendaftaran</h3>
                            <p class="text-[11px] text-amber-800">Menampilkan data gelombang yang telah diarsipkan / berstatus testing. Data ini diisolasi dan tidak muncul di formulir pendaftaran publik.</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-100 rounded-xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                                <th class="py-4 px-6">Nama Gelombang</th>
                                <th class="py-4 px-6 text-center">Status</th>
                                <th class="py-4 px-6 text-center">Pendaftar Terkait</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            @forelse($waves->where('is_testing', true) as $wave)
                                <tr class="transition hover:bg-slate-50/30">
                                    <td class="py-4 px-6">
                                        <div class="font-extrabold text-slate-700 flex items-center gap-2">
                                            <span>{{ $wave->name }}</span>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                                <i data-lucide="lock" class="w-3 h-3 text-amber-600"></i> Terkunci
                                            </span>
                                        </div>
                                        @if($wave->description)
                                            <div class="text-[11px] text-slate-400 font-medium mt-0.5">{{ $wave->description }}</div>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Di Tong Sampah
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="inline-flex min-w-20 justify-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700">
                                            {{ $wave->registrations_count }} Pendaftar
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 align-middle text-right whitespace-nowrap">
                                        <button type="button" onclick="confirmRestoreMaster('Gelombang', '{{ addslashes($wave->name) }}', '{{ route('admin.spmb-settings.waves.restore', $wave->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-emerald-300 text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-600 hover:border-emerald-600 hover:text-white transition shadow-2xs cursor-pointer" title="Pulihkan Gelombang">
                                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                            <span>Pulihkan</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 px-6 text-center text-slate-400 text-xs">Tong sampah gelombang kosong.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 7: Tong Sampah Jenis Pendaftaran -->
            <div id="tabContent-test_jenis" class="tab-content p-8 space-y-6 {{ $activeTab === 'test_jenis' ? '' : 'hidden' }}">
                <div class="bg-amber-50/80 border border-amber-200 rounded-2xl p-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="archive" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm text-amber-950">Tong Sampah: Jenis Pendaftaran</h3>
                            <p class="text-[11px] text-amber-800">Menampilkan data jenis pendaftaran yang telah diarsipkan / berstatus testing. Data ini diisolasi dan tidak muncul di formulir pendaftaran publik.</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-100 rounded-xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                                <th class="py-4 px-6">Kategori Jenis</th>
                                <th class="py-4 px-6 text-center">Status</th>
                                <th class="py-4 px-6 text-center">Pendaftar Terkait</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            @forelse($types->where('is_testing', true) as $type)
                                <tr class="transition hover:bg-slate-50/30">
                                    <td class="py-4 px-6">
                                        <div class="font-extrabold text-slate-700 flex items-center gap-2">
                                            <span>{{ $type->name }}</span>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                                <i data-lucide="lock" class="w-3 h-3 text-amber-600"></i> Terkunci
                                            </span>
                                        </div>
                                        @if($type->description)
                                            <div class="text-[11px] text-slate-400 font-medium mt-0.5">{{ $type->description }}</div>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Di Tong Sampah
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="inline-flex min-w-20 justify-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700">
                                            {{ $type->registrations_count }} Pendaftar
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 align-middle text-right whitespace-nowrap">
                                        <button type="button" onclick="confirmRestoreMaster('Jenis Pendaftaran', '{{ addslashes($type->name) }}', '{{ route('admin.spmb-settings.types.restore', $type->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-emerald-300 text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-600 hover:border-emerald-600 hover:text-white transition shadow-2xs cursor-pointer" title="Pulihkan Jenis Pendaftaran">
                                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                            <span>Pulihkan</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 px-6 text-center text-slate-400 text-xs">Tong sampah jenis pendaftaran kosong.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 8: Tong Sampah Kategori Murid -->
            <div id="tabContent-test_program" class="tab-content p-8 space-y-6 {{ $activeTab === 'test_program' ? '' : 'hidden' }}">
                <div class="bg-amber-50/80 border border-amber-200 rounded-2xl p-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="archive" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm text-amber-950">Tong Sampah: Kategori Murid</h3>
                            <p class="text-[11px] text-amber-800">Menampilkan data kategori murid yang telah diarsipkan / berstatus testing. Data ini diisolasi dan tidak muncul di formulir pendaftaran publik.</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-100 rounded-xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                                <th class="py-4 px-6">Nama Kategori Murid</th>
                                <th class="py-4 px-6 text-center">Status</th>
                                <th class="py-4 px-6 text-center">Pendaftar Terkait</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            @forelse($classPrograms->where('is_testing', true) as $program)
                                <tr class="transition hover:bg-slate-50/30">
                                    <td class="py-4 px-6">
                                        <div class="font-extrabold text-slate-700 flex items-center gap-2">
                                            <span>{{ $program->name }}</span>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                                <i data-lucide="lock" class="w-3 h-3 text-amber-600"></i> Terkunci
                                            </span>
                                        </div>
                                        @if($program->description)
                                            <div class="text-[11px] text-slate-400 font-medium mt-0.5">{{ $program->description }}</div>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Di Tong Sampah
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="inline-flex min-w-20 justify-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700">
                                            {{ $program->registrations_count }} Pendaftar
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 align-middle text-right whitespace-nowrap">
                                        <button type="button" onclick="confirmRestoreMaster('Kategori Murid', '{{ addslashes($program->name) }}', '{{ route('admin.spmb-settings.class-programs.restore', $program->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-emerald-300 text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-600 hover:border-emerald-600 hover:text-white transition shadow-2xs cursor-pointer" title="Pulihkan Kategori Murid">
                                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                            <span>Pulihkan</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 px-6 text-center text-slate-400 text-xs">Tong sampah kategori murid kosong.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

</div>

@if($isSuperAdmin)
    <!-- Unified CRUD Modal -->
    <div id="crudModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-2xl max-w-md w-full mx-4 shadow-2xl border border-slate-100 overflow-hidden">
            <div class="bg-brand-emerald text-white px-6 py-4">
                <h3 id="crudModalTitle" class="font-extrabold text-lg">Tambah</h3>
                <p class="text-xs text-emerald-100 mt-0.5">Kelola data konfigurasi setting master.</p>
            </div>
            <form id="crudForm" method="POST" hx-boost="false" class="p-6 space-y-4">
                @csrf
                
                @if($errors->any() && session('failed_modal'))
                    <div id="spmbErrorWrapper" class="text-xs text-red-650 bg-red-50 p-3.5 rounded-xl border border-red-200 font-semibold mb-3 space-y-1">
                        @foreach($errors->all() as $error)
                            <p>⚠️ {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div>
                    <label id="crudInputLabel" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Input</label>
                    <input type="text" id="crudMainInput" name="value" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
                </div>

                <div id="descriptionInputBox">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Deskripsi / Keterangan</label>
                    <textarea id="crudDescriptionInput" name="description" rows="3" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm" placeholder="Masukkan deskripsi singkat (opsional)..."></textarea>
                </div>
                
                <div id="statusToggleBox" class="hidden flex items-center gap-2 pt-2">
                    <input type="checkbox" id="crudStatusInput" name="is_active" value="1" class="rounded border-slate-300 text-brand-emerald focus:ring-brand-emerald">
                    <label for="crudStatusInput" class="text-xs font-semibold text-slate-700">Aktifkan Kategori Murid</label>
                </div>
                
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" onclick="closeModal()" class="border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                        Kembali
                    </button>
                    <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2 rounded-xl text-xs font-bold transition shadow-md cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Konfirmasi Pindahkan ke Tong Sampah -->
    <div id="trashMasterModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-2xl max-w-md w-full mx-4 shadow-2xl border border-slate-100 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0">
                        <i data-lucide="archive" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-slate-800">Pindahkan ke Tong Sampah?</h3>
                        <p class="text-xs text-slate-500">Arsipkan data master tanpa menghapus database.</p>
                    </div>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed mb-6">
                    Apakah Anda yakin ingin memindahkan <span id="trashMasterType" class="font-bold text-slate-800"></span> "<strong id="trashMasterName" class="text-amber-800 font-extrabold"></strong>" ke <strong>Tong Sampah</strong>? Data ini tidak akan muncul pada pilihan pendaftaran publik dan formulir aktif.
                </p>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeTrashMasterModal()" class="px-4 py-2 rounded-xl text-xs font-bold border border-slate-200 text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                        Batal
                    </button>
                    <form id="trashMasterForm" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white transition shadow-sm cursor-pointer">
                            Pindahkan ke Tong Sampah
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Pulihkan dari Tong Sampah -->
    <div id="restoreMasterModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-2xl max-w-md w-full mx-4 shadow-2xl border border-slate-100 overflow-hidden">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">
                        <i data-lucide="rotate-ccw" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-slate-800">Pulihkan Data Master?</h3>
                        <p class="text-xs text-slate-500">Kembalikan data ke tab utama / aktif.</p>
                    </div>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed mb-6">
                    Apakah Anda yakin ingin memulihkan <span id="restoreMasterType" class="font-bold text-slate-800"></span> "<strong id="restoreMasterName" class="text-emerald-800 font-extrabold"></strong>" dari Tong Sampah ke daftar utama aktif?
                </p>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeRestoreMasterModal()" class="px-4 py-2 rounded-xl text-xs font-bold border border-slate-200 text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                        Batal
                    </button>
                    <form id="restoreMasterForm" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm cursor-pointer">
                            Pulihkan Data
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Delete Form -->
    <form id="deleteForm" method="POST" hx-boost="false" class="hidden">
        @csrf
        @method('DELETE')
    </form>
@endif

<script>
    // Master Dropdown Toggle
    function toggleMasterTrashDropdown() {
        const menu = document.getElementById('masterTrashDropdownMenu');
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }

    function closeMasterTrashDropdown() {
        const menu = document.getElementById('masterTrashDropdownMenu');
        if (menu) {
            menu.classList.add('hidden');
        }
    }

    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('masterTrashDropdownWrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            closeMasterTrashDropdown();
        }
    });

    // Tab Switching
    function switchTab(tabId) {
        const panel = document.getElementById('tabContent-' + tabId);
        if (!panel) return;

        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        // Show selected tab
        panel.classList.remove('hidden');

        // Reset main tabs
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.className = "tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50";
        });
        
        const activeBtn = document.getElementById('tabBtn-' + tabId);
        if (activeBtn) {
            activeBtn.className = "tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow";
        }

        // Handle dropdown subtabs active state
        const isTrash = tabId.startsWith('test_');
        const trashDropdownBtn = document.getElementById('masterTrashDropdownBtn');
        if (trashDropdownBtn) {
            if (isTrash) {
                trashDropdownBtn.className = "px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-xs cursor-pointer";
                const trashIcon = trashDropdownBtn.querySelector('i[data-lucide="trash-2"]');
                if (trashIcon) trashIcon.className = "w-4 h-4 text-white";
                const chevronIcon = trashDropdownBtn.querySelector('i[data-lucide="chevron-down"]');
                if (chevronIcon) chevronIcon.className = "w-3.5 h-3.5 text-white/80";
            } else {
                trashDropdownBtn.className = "px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/80 rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-xs cursor-pointer";
                const trashIcon = trashDropdownBtn.querySelector('i[data-lucide="trash-2"]');
                if (trashIcon) trashIcon.className = "w-4 h-4 text-amber-600";
                const chevronIcon = trashDropdownBtn.querySelector('i[data-lucide="chevron-down"]');
                if (chevronIcon) chevronIcon.className = "w-3.5 h-3.5 text-slate-400";
            }
        }

        document.querySelectorAll('.trash-subtab-btn').forEach(subBtn => {
            subBtn.className = "trash-subtab-btn w-full text-left px-3.5 py-2 text-xs font-bold transition flex items-center justify-between gap-2 cursor-pointer text-slate-700 hover:bg-amber-50 hover:text-amber-800";
            const badge = subBtn.querySelector('.dropdown-item-count') || subBtn.querySelector('span:last-child');
            if (badge) {
                if (!badge.innerText.startsWith('0')) {
                    badge.className = "dropdown-item-count px-1.5 py-0.5 text-[9px] font-extrabold rounded-full bg-amber-100 text-amber-800";
                } else {
                    badge.className = "dropdown-item-count text-[9px] font-semibold text-slate-400";
                }
            }
        });

        const activeSubBtn = document.getElementById('trashSubTab-' + tabId);
        if (activeSubBtn) {
            activeSubBtn.className = "trash-subtab-btn w-full text-left px-3.5 py-2 text-xs font-bold transition flex items-center justify-between gap-2 cursor-pointer bg-amber-100 text-amber-900 font-extrabold border-l-4 border-amber-600";
            const badge = activeSubBtn.querySelector('.dropdown-item-count') || activeSubBtn.querySelector('span:last-child');
            if (badge) {
                if (!badge.innerText.startsWith('0')) {
                    badge.className = "dropdown-item-count px-1.5 py-0.5 text-[9px] font-extrabold rounded-full bg-amber-200 text-amber-900";
                } else {
                    badge.className = "dropdown-item-count text-[9px] font-semibold text-slate-400";
                }
            }
        }
        
        // Update URL query parameter while preserving unit_id
        const unitId = "{{ $selectedUnitId ?? '' }}";
        const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?tab=' + tabId + (unitId ? '&unit_id=' + unitId : '');
        window.history.replaceState({ path: newUrl }, '', newUrl);

        // Save to localStorage as fallback
        localStorage.setItem('spmb_active_tab', tabId);

        // Re-render lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

@if($isSuperAdmin)
    // Trash Master Modal Control
    function confirmTrashMaster(type, name, actionUrl) {
        document.getElementById('trashMasterType').innerText = type;
        document.getElementById('trashMasterName').innerText = name;
        document.getElementById('trashMasterForm').setAttribute('action', actionUrl);
        document.getElementById('trashMasterModal').classList.remove('hidden');
    }

    function closeTrashMasterModal() {
        document.getElementById('trashMasterModal').classList.add('hidden');
    }

    // Restore Master Modal Control
    function confirmRestoreMaster(type, name, actionUrl) {
        document.getElementById('restoreMasterType').innerText = type;
        document.getElementById('restoreMasterName').innerText = name;
        document.getElementById('restoreMasterForm').setAttribute('action', actionUrl);
        document.getElementById('restoreMasterModal').classList.remove('hidden');
    }

    function closeRestoreMasterModal() {
        document.getElementById('restoreMasterModal').classList.add('hidden');
    }

    // CRUD Modal Control
    function openModal(moduleType, val = '', isLocked = false, actionUrl = '', isActive = '1', desc = '') {
        const errorWrapper = document.getElementById('spmbErrorWrapper');
        if (errorWrapper) {
            errorWrapper.classList.add('hidden');
        }

        const form = document.getElementById('crudForm');
        form.setAttribute('action', actionUrl);
        
        const mainInput = document.getElementById('crudMainInput');
        mainInput.value = val;
        mainInput.disabled = false;

        const descBox = document.getElementById('descriptionInputBox');
        const descInput = document.getElementById('crudDescriptionInput');
        descInput.value = desc;

        const titleEl = document.getElementById('crudModalTitle');
        const labelEl = document.getElementById('crudInputLabel');
        const toggleBox = document.getElementById('statusToggleBox');
        
        // Status toggle check
        const statusInput = document.getElementById('crudStatusInput');
        statusInput.checked = (isActive === '1' || isActive === 'true' || isActive === true || isActive === 1);

        if (moduleType === 'periode') {
            descBox.classList.add('hidden');
            descInput.disabled = true;
        } else {
            descBox.classList.remove('hidden');
            descInput.disabled = false;
        }

        if (moduleType === 'program' && val) {
            toggleBox.classList.remove('hidden');
        } else {
            toggleBox.classList.add('hidden');
        }

        if (moduleType === 'periode') {
            mainInput.name = 'year';
            titleEl.innerText = val ? 'Edit Periode Akademik' : 'Tambah Periode Akademik';
            labelEl.innerText = 'Tahun Pelajaran*';
            mainInput.placeholder = 'Contoh: 2026-2027';
        } else if (moduleType === 'gelombang') {
            mainInput.name = 'name';
            titleEl.innerText = val ? 'Edit Gelombang' : 'Tambah Gelombang';
            labelEl.innerText = 'Nama Gelombang*';
            mainInput.placeholder = 'Contoh: Gelombang 3';
        } else if (moduleType === 'program') {
            mainInput.name = 'name';
            titleEl.innerText = val ? 'Edit Kategori Murid' : 'Tambah Kategori Murid';
            labelEl.innerText = 'Nama Kategori Murid*';
            mainInput.placeholder = 'Contoh: Inklusi';
        } else {
            mainInput.name = 'name';
            titleEl.innerText = val ? 'Edit Jenis Pendaftaran' : 'Tambah Jenis Pendaftaran';
            labelEl.innerText = 'Nama Jenis Pendaftaran*';
            mainInput.placeholder = 'Contoh: Mutasi Kelas 2';
        }

        document.getElementById('crudModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('crudModal').classList.add('hidden');
        const errorWrapper = document.getElementById('spmbErrorWrapper');
        if (errorWrapper) {
            errorWrapper.classList.add('hidden');
        }
    }

    const crudModalEl = document.getElementById('crudModal');
    if (crudModalEl) {
        crudModalEl.addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    }

    const trashModalEl = document.getElementById('trashMasterModal');
    if (trashModalEl) {
        trashModalEl.addEventListener('click', function(e) {
            if (e.target === this) closeTrashMasterModal();
        });
    }

    const restoreModalEl = document.getElementById('restoreMasterModal');
    if (restoreModalEl) {
        restoreModalEl.addEventListener('click', function(e) {
            if (e.target === this) closeRestoreMasterModal();
        });
    }

    // Delete Operations
    function deleteItem(type, name, isUsed, deleteUrl) {
        let label = (type === 'periode') ? 'Periode' : (type === 'gelombang' ? 'Gelombang' : (type === 'program' ? 'Kategori Murid' : 'Jenis Pendaftaran'));
        if (isUsed === 'true' || isUsed === true || isUsed > 0) {
            showToast(`Peringatan: Tidak dapat menghapus ${label} "${name}" karena data sudah dipakai dalam transaksi aktif! Anda hanya dapat mengubah datanya.`, 'error');
        } else {
            confirmDelete(deleteUrl, `Apakah Anda yakin ingin menghapus ${label} "${name}"?`);
        }
    }

    // Auto-reopen modal if validation failed on redirect
    @if(session('failed_modal'))
        document.addEventListener("DOMContentLoaded", function() {
            let failed = "{{ session('failed_modal') }}";
            if (failed.startsWith('periode_create')) {
                switchTab('periode');
                openModal('periode', '{{ old('year') }}', false, '{{ route('admin.spmb-settings.periods.store') }}');
            } else if (failed.startsWith('periode_edit_')) {
                switchTab('periode');
                let id = failed.replace('periode_edit_', '');
                openModal('periode', '{{ old('year') }}', false, '/admin/spmb-settings/periods/' + id);
            } else if (failed.startsWith('gelombang_create')) {
                switchTab('gelombang');
                openModal('gelombang', '{{ old('name') }}', false, '{{ route('admin.spmb-settings.waves.store') }}', '1', '{{ old('description') }}');
            } else if (failed.startsWith('gelombang_edit_')) {
                switchTab('gelombang');
                let id = failed.replace('gelombang_edit_', '');
                openModal('gelombang', '{{ old('name') }}', false, '/admin/spmb-settings/waves/' + id, '1', '{{ old('description') }}');
            } else if (failed.startsWith('jenis_create')) {
                switchTab('jenis');
                openModal('jenis', '{{ old('name') }}', false, '{{ route('admin.spmb-settings.types.store') }}', '1', '{{ old('description') }}');
            } else if (failed.startsWith('jenis_edit_')) {
                switchTab('jenis');
                let id = failed.replace('jenis_edit_', '');
                openModal('jenis', '{{ old('name') }}', false, '/admin/spmb-settings/types/' + id, '1', '{{ old('description') }}');
            } else if (failed.startsWith('program_create')) {
                switchTab('program');
                openModal('program', '{{ old('name') }}', false, '{{ route('admin.spmb-settings.class-programs.store') }}', '1', '{{ old('description') }}');
            } else if (failed.startsWith('program_edit_')) {
                switchTab('program');
                let id = failed.replace('program_edit_', '');
                openModal('program', '{{ old('name') }}', false, '/admin/spmb-settings/class-programs/' + id, '{{ old('is_active') ? 1 : 0 }}', '{{ old('description') }}');
            }

            const errorWrapper = document.getElementById('spmbErrorWrapper');
            if (errorWrapper) {
                errorWrapper.classList.remove('hidden');
            }
        });
    @endif

    // Escape key listener to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('crudModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeModal();
            }
            const trashModal = document.getElementById('trashMasterModal');
            if (trashModal && !trashModal.classList.contains('hidden')) {
                closeTrashMasterModal();
            }
            const restoreModal = document.getElementById('restoreMasterModal');
            if (restoreModal && !restoreModal.classList.contains('hidden')) {
                closeRestoreMasterModal();
            }
        }
    });
@endif
</script>
@endsection
