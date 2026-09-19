@extends('layouts.admin')

@section('title', 'Setting Biaya - Admin Panel')
@section('page_title', 'Setting Biaya')

@section('content')
<div id="spmb-fees-container" hx-boost="true" hx-target="#spmb-fees-container" hx-select="#spmb-fees-container" class="w-full space-y-6">
    <!-- Header with Unit & Period Filter -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                <i data-lucide="coins" class="w-6 h-6 text-brand-emerald"></i>
                Manajemen Biaya Pendaftaran (SPMB)
            </h1>
            <p class="text-xs text-slate-500 mt-1">Mengatur jenis-jenis kategori biaya dan nominal biaya pendaftaran calon murid baru per tahun ajaran.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Period Filter Switcher -->
            <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200/80 p-1.5 rounded-2xl shadow-xs overflow-x-auto">
                <span class="text-xs font-extrabold text-slate-500 flex items-center gap-1.5 px-2 whitespace-nowrap">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-indigo-500"></i>
                    Tahun Ajaran:
                </span>
                <button type="button" onclick="filterFeesByPeriod('')" id="periodFilterBtn-all" class="period-filter-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ ($selectedPeriodId ?? '') === '' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-650 hover:bg-slate-100 border border-slate-200/60' }} cursor-pointer">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                    Semua TA
                </button>
                @foreach($periods as $period)
                    <button type="button" onclick="filterFeesByPeriod('{{ $period->id }}')" id="periodFilterBtn-{{ $period->id }}" class="period-filter-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ ($selectedPeriodId ?? '') == $period->id ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-650 hover:bg-slate-100 border border-slate-200/60' }} cursor-pointer">
                        <span>{{ $period->year ? 'TA ' . $period->year : ($period->name ?? 'TA ' . $period->id) }}</span>
                    </button>
                @endforeach
            </div>

            <!-- Unit Filter Switcher (Khusus SuperAdmin) -->
            @if(auth()->user()->isSuperAdmin())
                <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200/80 p-1.5 rounded-2xl shadow-xs overflow-x-auto">
                    <span class="text-xs font-extrabold text-slate-500 flex items-center gap-1.5 px-2 whitespace-nowrap">
                        <i data-lucide="filter" class="w-3.5 h-3.5 text-brand-emerald"></i>
                        Unit:
                    </span>
                    <button type="button" onclick="filterFeesByUnit('')" id="unitFilterBtn-all" class="unit-filter-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ ($selectedUnitId ?? '') === '' ? 'bg-brand-emerald text-white shadow-xs' : 'bg-white text-slate-650 hover:bg-slate-100 border border-slate-200/60' }} cursor-pointer">
                        <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                        Semua Unit
                    </button>
                    @foreach($units as $unit)
                        <button type="button" onclick="filterFeesByUnit('{{ $unit->id }}')" id="unitFilterBtn-{{ $unit->id }}" class="unit-filter-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap {{ ($selectedUnitId ?? '') == $unit->id ? 'bg-brand-emerald text-white shadow-xs' : 'bg-white text-slate-650 hover:bg-slate-100 border border-slate-200/60' }} cursor-pointer">
                            <span>{{ strtoupper($unit->code) }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Tab Navigation Pills -->
    <div class="flex flex-wrap gap-2 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
        <button onclick="switchFeeTab('jenis_biaya')" id="feeTabBtn-jenis_biaya" class="fee-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'jenis_biaya' ? 'bg-brand-emerald text-white shadow' : 'text-slate-600 hover:bg-slate-50' }} cursor-pointer">
            <i data-lucide="tag" class="w-4 h-4"></i> Jenis Biaya
        </button>
        @foreach($categories as $cat)
            <button onclick="switchFeeTab('cat_{{ $cat->id }}')" id="feeTabBtn-cat_{{ $cat->id }}" class="fee-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'cat_' . $cat->id ? 'bg-brand-emerald text-white shadow' : 'text-slate-600 hover:bg-slate-50' }} cursor-pointer">
                <i data-lucide="coins" class="w-4 h-4"></i> {{ $cat->name }}
            </button>
        @endforeach
    </div>

    <!-- Tab Contents -->
    <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        
        <!-- Tab 1: Jenis Biaya -->
        <div id="feeTabContent-jenis_biaya" class="fee-tab-content p-8 space-y-6 {{ $activeTab === 'jenis_biaya' ? '' : 'hidden' }}">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-extrabold text-base text-slate-800">Kategori Jenis Biaya</h3>
                    <p class="text-[11px] text-slate-400">Kelola kelompok jenis pembayaran masuk dan target tahun ajarannya.</p>
                </div>
                <button onclick="openFeeModal('jenis_biaya', '', '', '{{ route('admin.spmb-settings.fees.categories.store') }}', '', 'winpay', '', '', [], 'tuition_fee')" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1 cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Jenis Biaya
                </button>
            </div>
            
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Jenis Biaya</th>
                            <th class="py-4 px-6 text-center">Fungsi / Tipe Biaya</th>
                            <th class="py-4 px-6 text-center">Tahun Ajaran</th>
                            @if(auth()->user()->isSuperAdmin())
                                <th class="py-4 px-6 text-center">Unit Pengguna</th>
                            @endif
                            <th class="py-4 px-6 text-center">Digunakan Transaksi</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-slate-100">
                        @forelse($categories as $cat)
                            <tr class="category-item-row hover:bg-slate-50/30 transition" data-unit-ids="{{ implode(',', $cat->units->pluck('id')->toArray()) }}" data-period-ids="{{ implode(',', (array)$cat->applicable_periods) }}">
                                <td class="py-4 px-6 font-extrabold text-slate-800">{{ $cat->name }}</td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $cat->type_badge_class }}">
                                        {{ $cat->type_label }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if(empty($cat->applicable_periods))
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-red-50 text-red-600 border border-red-200">
                                            Non-Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200" title="{{ $cat->target_periods_text }}">
                                            <i data-lucide="calendar" class="w-3 h-3"></i>
                                            {{ \Illuminate\Support\Str::limit($cat->target_periods_text, 25) }}
                                        </span>
                                    @endif
                                </td>
                                @if(auth()->user()->isSuperAdmin())
                                    <td class="py-4 px-6 text-center text-xs font-semibold text-slate-500">
                                        @if($cat->units->count() === $units->count())
                                            <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold text-[10px]">Semua Unit</span>
                                        @else
                                            {{ implode(', ', $cat->units->pluck('code')->toArray()) }}
                                        @endif
                                    </td>
                                @endif
                                <td class="py-4 px-6 text-center text-xs font-semibold {{ $cat->is_used ? 'text-slate-600 font-bold' : 'text-slate-400' }}">
                                    {{ $cat->is_used ? 'Ya' : 'Tidak' }}
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" onclick="editCategoryItem({{ json_encode([
                                            'name' => $cat->name,
                                            'is_used' => (bool)$cat->is_used,
                                            'update_url' => route('admin.spmb-settings.fees.categories.update', $cat->id),
                                            'units' => $cat->units->pluck('id')->toArray(),
                                            'category_type' => $cat->category_type,
                                            'applicable_periods' => $cat->applicable_periods ?? [],
                                        ]) }})" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-brand-emerald text-xs font-bold text-brand-emerald transition hover:bg-emerald-800 hover:border-emerald-800 hover:text-white cursor-pointer">
                                            <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                            <span>Edit</span>
                                        </button>
                                    @if(!$cat->is_used)
                                        <button type="button" onclick="deleteFeeItem('jenis_biaya', {{ json_encode($cat->name) }}, {{ $cat->is_used ? 'true' : 'false' }}, '{{ route('admin.spmb-settings.fees.categories.delete', $cat->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white cursor-pointer">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            <span>Hapus</span>
                                        </button>
                                    @else
                                        <span class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 border border-slate-200 text-xs font-bold text-slate-400 cursor-not-allowed" title="Kategori sedang digunakan">
                                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                            <span>Hapus</span>
                                        </span>
                                    @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 px-6 text-center text-slate-400">Belum ada data jenis biaya.</td>
                            </tr>
                        @endforelse
                        <tr id="emptyCatRow-filtered" class="hidden">
                            <td colspan="6" class="py-8 px-6 text-center text-slate-400 text-xs">Tidak ada jenis biaya untuk filter yang dipilih.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Dynamic Category Tabs -->
        @foreach($categories as $cat)
            @php
                $catFees = $fees->where('spmb_fee_category_id', $cat->id);
            @endphp
            <div id="feeTabContent-cat_{{ $cat->id }}" class="fee-tab-content p-8 space-y-6 {{ $activeTab === 'cat_' . $cat->id ? '' : 'hidden' }}">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-extrabold text-base text-slate-800">Daftar Nominal {{ $cat->name }}</h3>
                        <p class="text-[11px] text-slate-400">Atur besaran nominal untuk kategori {{ $cat->name }}.</p>
                    </div>
                    <button onclick="openFeeModal('biaya_tambahan', '', false, '{{ route('admin.spmb-settings.fees.admin-fees.store') }}', '', 'winpay', '{{ $cat->id }}', window.currentUnitFilter || '')" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1 cursor-pointer">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah {{ $cat->name }}
                    </button>
                </div>
                
                <div class="overflow-x-auto border border-slate-100 rounded-xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                                <th class="py-4 px-6">Nama Biaya</th>
                                <th class="py-4 px-6 text-center">Unit Sekolah</th>
                                <th class="py-4 px-6 text-left">Target Kelas & Kategori</th>
                                <th class="py-4 px-6 text-center">Nominal (Rp)</th>
                                <th class="py-4 px-6 text-center">Payment Gateway</th>
                                <th class="py-4 px-6 text-center">Digunakan Transaksi</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs divide-y divide-slate-100">
                            @forelse($catFees as $fee)
                                <tr class="fee-item-row hover:bg-slate-50/30 transition" data-unit-id="{{ $fee->spmb_unit_id }}" data-category-id="{{ $cat->id }}" data-period-ids="{{ implode(',', (array)$fee->applicable_periods) }}">
                                    <td class="py-4 px-6 font-extrabold text-slate-800">{{ $fee->name }}</td>
                                    <td class="py-4 px-6 text-center font-semibold text-slate-500 text-xs">
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold {{ $fee->unit ? 'bg-slate-100 text-slate-700' : 'bg-emerald-50 text-emerald-700' }}">
                                            {{ $fee->unit->code ?? 'Global' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-left">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="text-[10px] font-bold text-slate-400">Tahun Ajaran:</span>
                                                @if(empty($fee->applicable_periods))
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-red-50 text-red-600 border border-red-200">
                                                        Tidak Ada (Non-Aktif)
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200" title="{{ $fee->target_periods_text }}">
                                                        <i data-lucide="calendar" class="w-3 h-3"></i>
                                                        {{ \Illuminate\Support\Str::limit($fee->target_periods_text, 25) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="text-[10px] font-bold text-slate-400">Kelas:</span>
                                                @if(empty($fee->applicable_grades))
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        Semua Kelas
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200" title="{{ $fee->target_grades_text }}">
                                                        {{ \Illuminate\Support\Str::limit($fee->target_grades_text, 25) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="text-[10px] font-bold text-slate-400">Kategori:</span>
                                                @if(empty($fee->applicable_class_programs))
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                                        Semua Kategori
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200" title="{{ $fee->target_class_programs_text }}">
                                                        {{ \Illuminate\Support\Str::limit($fee->target_class_programs_text, 25) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="text-[10px] font-bold text-slate-400">Jalur:</span>
                                                @if(empty($fee->applicable_types))
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                        Semua Jalur
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200" title="{{ $fee->target_types_text }}">
                                                        {{ \Illuminate\Support\Str::limit($fee->target_types_text, 25) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="text-[10px] font-bold text-slate-400">Gender:</span>
                                                @if(empty($fee->applicable_gender) || $fee->applicable_gender === 'all')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                                        Semua Gender
                                                    </span>
                                                @elseif(in_array(strtolower($fee->applicable_gender), ['male', 'laki-laki', 'l', 'putra']))
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                        👦 Laki-laki
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-pink-50 text-pink-700 border border-pink-200">
                                                        👧 Perempuan
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center font-semibold text-slate-700">Rp {{ number_format($fee->amount, 0, ',', '.') }}</td>
                                    <td class="py-4 px-6 text-center whitespace-nowrap">
                                        @php
                                            $gatewaysArray = is_array($fee->payment_gateway) ? $fee->payment_gateway : [$fee->payment_gateway];
                                        @endphp
                                        <div class="flex flex-wrap gap-1 justify-center">
                                            @foreach($gatewaysArray as $gwCode)
                                                @php
                                                    $gw = $gateways->where('code', $gwCode)->first();
                                                    $colorClass = 'bg-blue-100 text-blue-700';
                                                    if ($gwCode === 'bni') {
                                                        $colorClass = 'bg-orange-100 text-orange-700';
                                                    } elseif ($gwCode === 'winpay') {
                                                        $colorClass = 'bg-emerald-50 text-brand-emerald';
                                                    }
                                                @endphp
                                                <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase {{ $colorClass }}">
                                                    {{ $gw ? $gw->name : strtoupper($gwCode) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center text-xs font-semibold {{ $fee->is_used ? 'text-slate-600 font-bold' : 'text-slate-400' }}">
                                        {{ $fee->is_used ? 'Ya (Terpakai)' : 'Tidak' }}
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" onclick="editFeeItem({{ json_encode([
                                                'name' => $fee->name,
                                                'is_used' => (bool)$fee->is_used,
                                                'update_url' => route('admin.spmb-settings.fees.admin-fees.update', $fee->id),
                                                'amount' => $fee->amount,
                                                'payment_gateway' => is_array($fee->payment_gateway) ? implode(',', $fee->payment_gateway) : $fee->payment_gateway,
                                                'category_id' => $cat->id,
                                                'unit_id' => $fee->spmb_unit_id,
                                                'applicable_periods' => $fee->applicable_periods ?? [],
                                                'applicable_grades' => $fee->applicable_grades ?? [],
                                                'applicable_class_programs' => $fee->applicable_class_programs ?? [],
                                                'applicable_types' => $fee->applicable_types ?? [],
                                                'applicable_gender' => $fee->applicable_gender ?? 'all',
                                            ]) }})" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-brand-emerald text-xs font-bold text-brand-emerald transition hover:bg-emerald-800 hover:border-emerald-800 hover:text-white cursor-pointer">
                                                <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                                <span>Edit</span>
                                            </button>
                                            <button type="button" onclick="deleteFeeItem('biaya_tambahan', {{ json_encode($fee->name) }}, {{ $fee->is_used ? 'true' : 'false' }}, '{{ route('admin.spmb-settings.fees.admin-fees.delete', $fee->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white cursor-pointer">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 px-6 text-center text-slate-400 text-xs">Belum ada data nominal untuk kategori ini.</td>
                                </tr>
                            @endforelse
                            <tr id="emptyFeeRow-{{ $cat->id }}-filtered" class="hidden">
                                <td colspan="7" class="py-8 px-6 text-center text-slate-400 text-xs">Tidak ada data biaya untuk unit yang dipilih pada kategori ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

    </div>
</div>

<!-- Unified Fee CRUD Modal -->
<div id="feeCrudModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-xl w-full mx-auto shadow-2xl border border-slate-100 overflow-hidden max-h-[92vh] flex flex-col">
        <div class="bg-brand-emerald text-white px-6 py-4 flex-shrink-0 flex items-center justify-between">
            <div>
                <h3 id="feeModalTitle" class="font-extrabold text-lg">Tambah</h3>
                <p class="text-xs text-emerald-100 mt-0.5">Kelola data konfigurasi setting biaya.</p>
            </div>
            <button type="button" onclick="closeFeeModal()" class="text-emerald-100 hover:text-white transition p-1 rounded-lg hover:bg-emerald-700/50">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="feeCrudForm" method="POST" class="p-6 space-y-4 overflow-y-auto" hx-boost="false">
            @csrf
            
            <input type="hidden" id="feeCategoryInput" name="spmb_fee_category_id">

            @if($errors->any() && session('failed_modal'))
                <div id="feeErrorWrapper" class="text-xs text-red-650 bg-red-50 p-3.5 rounded-xl border border-red-200 font-semibold mb-3 space-y-1">
                    @foreach($errors->all() as $error)
                        <p>⚠️ {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Unit Checkboxes for Jenis Biaya (Super Admin only) -->
            @if(auth()->user()->isSuperAdmin())
                <div id="categoryUnitsWrapper" class="hidden">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Unit Sekolah Pengguna*</label>
                    <div class="bg-slate-50 border border-slate-300 rounded-xl p-4 space-y-2">
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                            <input type="checkbox" id="checkAllUnits" onchange="toggleAllUnits(this)" class="rounded text-brand-emerald focus:ring-brand-emerald">
                            Pilih Semua Unit
                        </label>
                        <hr class="border-slate-200 my-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($units as $unit)
                                <label class="flex items-center gap-2 text-xs font-semibold text-slate-650 cursor-pointer">
                                    <input type="checkbox" name="spmb_units[]" value="{{ $unit->id }}" class="unit-checkbox rounded text-brand-emerald focus:ring-brand-emerald" onchange="updateCheckAllState()">
                                    {{ $unit->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Unit Input (Only visible for Super Admin when managing fees) -->
            @if(auth()->user()->isSuperAdmin())
                <div id="feeUnitWrapper" class="hidden">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Unit Sekolah*</label>
                    <div class="bg-slate-50 border border-slate-300 rounded-xl p-4 space-y-2">
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                            <input type="checkbox" id="checkAllFeeUnits" onchange="toggleAllFeeUnits(this)" class="rounded text-brand-emerald focus:ring-brand-emerald">
                            Pilih Semua Unit
                        </label>
                        <hr class="border-slate-200 my-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($units as $unit)
                                <label class="flex items-center gap-2 text-xs font-semibold text-slate-650 cursor-pointer">
                                    <input type="checkbox" name="spmb_units[]" value="{{ $unit->id }}" class="fee-unit-checkbox rounded text-brand-emerald focus:ring-brand-emerald" onchange="updateCheckAllFeeState()">
                                    {{ $unit->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Category Type Dropdown (Only for Jenis Biaya) -->
            <div id="categoryTypeWrapper" class="hidden">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Fungsi / Tipe Biaya*</label>
                <select id="categoryTypeSelect" name="category_type" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-semibold">
                    <option value="registration_fee">Biaya Pendaftaran Awal (Dibayar sebelum isi formulir pendaftaran)</option>
                    <option value="tuition_fee" selected>Biaya Masuk / Administrasi Akhir (Daftar ulang setelah lolos observasi)</option>
                    <option value="extra_service">Layanan Tambahan (Biaya opsional fasilitas di formulir, misal TPA/TPQ)</option>
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Sistem menggunakan tipe ini untuk menentukan alur penagihan yang tepat secara otomatis.</p>
            </div>

            <!-- Category Periods Checkboxes (Only for Jenis Biaya) -->
            <div id="categoryPeriodsWrapper" class="hidden space-y-2">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Tahun Ajaran Target*</label>
                    <label class="flex items-center gap-1.5 text-[11px] font-bold text-brand-emerald cursor-pointer hover:underline">
                        <input type="checkbox" id="checkAllCategoryPeriods" onchange="toggleAllCategoryPeriods(this)" class="rounded text-brand-emerald focus:ring-brand-emerald w-3.5 h-3.5">
                        Pilih Semua TA
                    </label>
                </div>
                <div class="bg-slate-50 border border-slate-300 rounded-xl p-3">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach($periods as $period)
                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer p-1 rounded-lg hover:bg-slate-100 transition">
                                <input type="checkbox" name="applicable_periods[]" value="{{ $period->id }}" class="category-period-checkbox rounded text-brand-emerald focus:ring-brand-emerald w-4 h-4 border-slate-300" onchange="updateCheckAllCategoryPeriodsState()">
                                <span>{{ $period->year ? 'TA ' . $period->year : ($period->name ?? 'TA ' . $period->id) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <p class="text-[11px] text-slate-400">Jenis biaya hanya akan aktif pada Tahun Ajaran yang dicentang.</p>
            </div>

            <div>
                <label id="feeInputLabel" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nama*</label>
                <input type="text" id="feeMainInput" name="name" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
            </div>
            
            <!-- Amount Input & Targeting (Only visible for Fee Items) -->
            <div id="feeAmountWrapper" class="hidden space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-605 uppercase tracking-wider mb-2">Nominal (Rupiah)*</label>
                    <input type="text" id="feeAmountInput" name="amount" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm" placeholder="Contoh: 350.000">
                    <p id="feeAmountWarning" class="text-[11px] text-amber-600 font-semibold mt-1.5 hidden flex items-center gap-1">
                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5 flex-shrink-0"></i>
                        Nominal biaya ini dikunci karena sudah memiliki transaksi pembayaran.
                    </p>
                </div>

                <!-- Fee Targeting Section (Periods, Grades, Class Programs, Registration Types) -->
                <div id="feeTargetingWrapper" class="space-y-4 pt-1">
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-3.5 text-[11px] text-amber-800 flex items-start gap-2.5">
                        <i data-lucide="info" class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5"></i>
                        <span class="leading-relaxed"><strong>Petunjuk Pengaturan:</strong> Tentukan tahun ajaran, kelas, & kategori murid yang dibebankan biaya ini. Wajib mencentang minimal satu Tahun Ajaran agar biaya dapat aktif.</span>
                    </div>

                    <!-- 0. Target Tahun Ajaran (Periods) -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Target Tahun Ajaran (Periode)*</label>
                            <label class="flex items-center gap-1.5 text-[11px] font-bold text-brand-emerald cursor-pointer hover:underline">
                                <input type="checkbox" id="checkAllFeePeriods" onchange="toggleAllFeePeriods(this)" class="rounded text-brand-emerald focus:ring-brand-emerald w-3.5 h-3.5">
                                Pilih Semua TA
                            </label>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                            <div id="feePeriodCheckboxesList" class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach($periods as $period)
                                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer p-1.5 rounded-lg hover:bg-slate-100 transition">
                                        <input type="checkbox" name="applicable_periods[]" value="{{ $period->id }}" class="fee-period-checkbox rounded text-brand-emerald focus:ring-brand-emerald w-4 h-4 border-slate-300" onchange="updateCheckAllFeePeriodsState()">
                                        <span class="truncate">{{ $period->year ? 'TA ' . $period->year : ($period->name ?? 'TA ' . $period->id) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-400">Nominal biaya hanya akan aktif dan ditagihkan pada Tahun Ajaran yang dicentang.</p>
                    </div>

                    <!-- 1. Target Kelas (Grades) -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Target Kelas (Grades)</label>
                            <label class="flex items-center gap-1.5 text-[11px] font-bold text-brand-emerald cursor-pointer hover:underline">
                                <input type="checkbox" id="checkAllGrades" onchange="toggleAllGrades(this)" class="rounded text-brand-emerald focus:ring-brand-emerald w-3.5 h-3.5">
                                Pilih Semua Kelas
                            </label>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 max-h-36 overflow-y-auto">
                            <div id="gradeCheckboxesList" class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach($grades as $grade)
                                    <label class="grade-item-wrapper flex items-center gap-2 text-xs font-medium text-slate-700 cursor-pointer p-1.5 rounded-lg hover:bg-slate-100 transition" data-unit-id="{{ $grade->spmb_unit_id }}">
                                        <input type="checkbox" name="applicable_grades[]" value="{{ $grade->id }}" class="fee-grade-checkbox rounded text-brand-emerald focus:ring-brand-emerald w-4 h-4 border-slate-300" onchange="updateCheckAllGradesState()">
                                        <span class="truncate">{{ $grade->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- 2. Target Kategori Murid (Program Kelas) -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Target Kategori Murid</label>
                            <label class="flex items-center gap-1.5 text-[11px] font-bold text-brand-emerald cursor-pointer hover:underline">
                                <input type="checkbox" id="checkAllClassPrograms" onchange="toggleAllClassPrograms(this)" class="rounded text-brand-emerald focus:ring-brand-emerald w-3.5 h-3.5">
                                Pilih Semua Kategori
                            </label>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                            <div id="classProgramCheckboxesList" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($classPrograms as $prog)
                                    @php
                                        $progUnitIds = $prog->units->pluck('id')->toArray();
                                    @endphp
                                    <label class="program-item-wrapper flex items-center gap-2 text-xs font-medium text-slate-700 cursor-pointer p-1.5 rounded-lg hover:bg-slate-100 transition" data-unit-ids="{{ implode(',', $progUnitIds) }}">
                                        <input type="checkbox" name="applicable_class_programs[]" value="{{ $prog->id }}" class="fee-program-checkbox rounded text-brand-emerald focus:ring-brand-emerald w-4 h-4 border-slate-300" onchange="updateCheckAllClassProgramsState()">
                                        <span class="truncate">{{ $prog->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- 3. Target Jalur Pendaftaran (Tipe Masuk) -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Target Jalur Pendaftaran</label>
                            <label class="flex items-center gap-1.5 text-[11px] font-bold text-brand-emerald cursor-pointer hover:underline">
                                <input type="checkbox" id="checkAllTypes" onchange="toggleAllTypes(this)" class="rounded text-brand-emerald focus:ring-brand-emerald w-3.5 h-3.5">
                                Pilih Semua Jalur
                            </label>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                            <div id="typeCheckboxesList" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($types as $type)
                                    @php
                                        $typeUnitIds = $type->units->pluck('id')->toArray();
                                    @endphp
                                    <label class="type-item-wrapper flex items-center gap-2 text-xs font-medium text-slate-700 cursor-pointer p-1.5 rounded-lg hover:bg-slate-100 transition" data-unit-ids="{{ implode(',', $typeUnitIds) }}">
                                        <input type="checkbox" name="applicable_types[]" value="{{ $type->id }}" class="fee-type-checkbox rounded text-brand-emerald focus:ring-brand-emerald w-4 h-4 border-slate-300" onchange="updateCheckAllTypesState()">
                                        <span class="truncate">{{ $type->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- 4. Target Jenis Kelamin (Gender) -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider block">Target Jenis Kelamin</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="gender-radio-label flex items-center justify-center gap-1.5 p-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-700 cursor-pointer hover:bg-slate-100 transition">
                                <input type="radio" name="applicable_gender" value="all" class="fee-gender-radio text-brand-emerald focus:ring-brand-emerald w-4 h-4" checked>
                                <span>Semua Gender</span>
                            </label>
                            <label class="gender-radio-label flex items-center justify-center gap-1.5 p-2.5 rounded-xl border border-blue-200 bg-blue-50/50 text-xs font-semibold text-blue-800 cursor-pointer hover:bg-blue-100 transition">
                                <input type="radio" name="applicable_gender" value="male" class="fee-gender-radio text-brand-emerald focus:ring-brand-emerald w-4 h-4">
                                <span>👦 Laki-laki</span>
                            </label>
                            <label class="gender-radio-label flex items-center justify-center gap-1.5 p-2.5 rounded-xl border border-pink-200 bg-pink-50/50 text-xs font-semibold text-pink-800 cursor-pointer hover:bg-pink-100 transition">
                                <input type="radio" name="applicable_gender" value="female" class="fee-gender-radio text-brand-emerald focus:ring-brand-emerald w-4 h-4">
                                <span>👧 Perempuan</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Payment Gateway*</label>
                    <div class="space-y-2 bg-slate-50 border border-slate-300 rounded-xl p-3">
                        @foreach($gateways->where('is_active', true) as $gw)
                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer hover:text-slate-900">
                                <input type="checkbox" name="payment_gateway[]" value="{{ $gw->code }}" class="fee-gateway-checkbox rounded text-brand-emerald focus:ring-brand-emerald w-4 h-4 border-slate-300">
                                <span>{{ $gw->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 mt-4">
                <button type="button" onclick="closeFeeModal()" class="border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition">
                    Kembali
                </button>
                <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2 rounded-xl text-xs font-bold transition shadow-md">
                    Simpan Perubahan
                </button>
            </div>
        </form>
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
</div>

<!-- Hidden Delete Form -->
<form id="feeDeleteForm" method="POST" class="hidden" hx-boost="false">
    @csrf
    @method('DELETE')
</form>

<script>
    var currentUserUnitId = "{{ auth()->user()->spmb_unit_id ?? '' }}";
    var isSuperAdmin = {{ auth()->user()->isSuperAdmin() ? 'true' : 'false' }};
    window.currentUnitFilter = "{{ $selectedUnitId ?? '' }}";
    window.currentPeriodFilter = "{{ $selectedPeriodId ?? '' }}";

    // Format as thousands
    window.formatRupiah = function(value) {
        if (!value) return '';
        let str = value.toString();
        if (str.endsWith('.00')) {
            str = str.substring(0, str.length - 3);
        }
        let clean = str.replace(/\D/g, '');
        return clean.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    };

    document.addEventListener("DOMContentLoaded", function() {
        const amountInput = document.getElementById('feeAmountInput');
        if (amountInput) {
            amountInput.addEventListener('input', function(e) {
                let formatted = window.formatRupiah(e.target.value);
                e.target.value = formatted;
            });
        }

        const form = document.getElementById('feeCrudForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const amountInput = document.getElementById('feeAmountInput');
                if (amountInput && amountInput.value) {
                    amountInput.value = amountInput.value.replace(/\./g, '');
                }
            });
        }
    });

    // Checkbox controls for Category Units (Super Admin)
    window.toggleAllUnits = function(source) {
        document.querySelectorAll('.unit-checkbox').forEach(cb => {
            cb.checked = source.checked;
        });
    };

    window.updateCheckAllState = function() {
        const checkboxes = document.querySelectorAll('.unit-checkbox');
        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        const checkAll = document.getElementById('checkAllUnits');
        if (checkAll) {
            checkAll.checked = (checkboxes.length > 0 && checkedCount === checkboxes.length);
        }
    };

    // Category Periods Checkbox controls
    window.toggleAllCategoryPeriods = function(source) {
        document.querySelectorAll('.category-period-checkbox').forEach(cb => {
            cb.checked = source.checked;
        });
    };

    window.updateCheckAllCategoryPeriodsState = function() {
        const checkboxes = document.querySelectorAll('.category-period-checkbox');
        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        const checkAll = document.getElementById('checkAllCategoryPeriods');
        if (checkAll) {
            checkAll.checked = (checkboxes.length > 0 && checkedCount === checkboxes.length);
        }
    };

    // Fee Units Checkbox controls (Super Admin)
    window.getSelectedFeeUnits = function() {
        const checkedBoxes = document.querySelectorAll('.fee-unit-checkbox:checked');
        return Array.from(checkedBoxes).map(cb => cb.value);
    };

    window.toggleAllFeeUnits = function(source) {
        document.querySelectorAll('.fee-unit-checkbox').forEach(cb => {
            cb.checked = source.checked;
        });
        window.filterTargetingCheckboxesByUnit(window.getSelectedFeeUnits());
    };

    window.updateCheckAllFeeState = function() {
        const checkboxes = document.querySelectorAll('.fee-unit-checkbox');
        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        const checkAll = document.getElementById('checkAllFeeUnits');
        if (checkAll) {
            checkAll.checked = (checkboxes.length > 0 && checkedCount === checkboxes.length);
        }
        window.filterTargetingCheckboxesByUnit(window.getSelectedFeeUnits());
    };

    // Fee Periods Checkbox controls
    window.toggleAllFeePeriods = function(source) {
        document.querySelectorAll('.fee-period-checkbox').forEach(cb => {
            cb.checked = source.checked;
        });
    };

    window.updateCheckAllFeePeriodsState = function() {
        const checkboxes = document.querySelectorAll('.fee-period-checkbox');
        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        const checkAll = document.getElementById('checkAllFeePeriods');
        if (checkAll) {
            checkAll.checked = (checkboxes.length > 0 && checkedCount === checkboxes.length);
        }
    };

    // Targeting Checkboxes Controls
    window.toggleAllGrades = function(source) {
        document.querySelectorAll('.grade-item-wrapper').forEach(wrapper => {
            if (wrapper.style.display !== 'none') {
                const cb = wrapper.querySelector('.fee-grade-checkbox');
                if (cb) cb.checked = source.checked;
            }
        });
    };

    window.updateCheckAllGradesState = function() {
        const visibleWrappers = Array.from(document.querySelectorAll('.grade-item-wrapper')).filter(w => w.style.display !== 'none');
        if (visibleWrappers.length === 0) return;
        const checkedCount = visibleWrappers.filter(w => {
            const cb = w.querySelector('.fee-grade-checkbox');
            return cb && cb.checked;
        }).length;
        const checkAll = document.getElementById('checkAllGrades');
        if (checkAll) {
            checkAll.checked = checkedCount === visibleWrappers.length;
        }
    };

    window.toggleAllClassPrograms = function(source) {
        document.querySelectorAll('.program-item-wrapper').forEach(wrapper => {
            if (wrapper.style.display !== 'none') {
                const cb = wrapper.querySelector('.fee-program-checkbox');
                if (cb) cb.checked = source.checked;
            }
        });
    };

    window.updateCheckAllClassProgramsState = function() {
        const visibleWrappers = Array.from(document.querySelectorAll('.program-item-wrapper')).filter(w => w.style.display !== 'none');
        if (visibleWrappers.length === 0) return;
        const checkedCount = visibleWrappers.filter(w => {
            const cb = w.querySelector('.fee-program-checkbox');
            return cb && cb.checked;
        }).length;
        const checkAll = document.getElementById('checkAllClassPrograms');
        if (checkAll) {
            checkAll.checked = checkedCount === visibleWrappers.length;
        }
    };

    window.toggleAllTypes = function(source) {
        document.querySelectorAll('.type-item-wrapper').forEach(wrapper => {
            if (wrapper.style.display !== 'none') {
                const cb = wrapper.querySelector('.fee-type-checkbox');
                if (cb) cb.checked = source.checked;
            }
        });
    };

    window.updateCheckAllTypesState = function() {
        const visibleWrappers = Array.from(document.querySelectorAll('.type-item-wrapper')).filter(w => w.style.display !== 'none');
        if (visibleWrappers.length === 0) return;
        const checkedCount = visibleWrappers.filter(w => {
            const cb = w.querySelector('.fee-type-checkbox');
            return cb && cb.checked;
        }).length;
        const checkAll = document.getElementById('checkAllTypes');
        if (checkAll) {
            checkAll.checked = checkedCount === visibleWrappers.length;
        }
    };

    // Dynamic Filter for Checklist based on Selected Unit
    window.filterTargetingCheckboxesByUnit = function(activeUnits) {
        let unitIds = [];
        if (Array.isArray(activeUnits)) {
            unitIds = activeUnits.map(id => id.toString().trim()).filter(id => id !== '');
        } else if (activeUnits) {
            let str = activeUnits.toString();
            unitIds = str.includes(',') ? str.split(',').map(s => s.trim()).filter(s => s !== '') : [str.trim()];
        }

        const isGlobalOrAll = (unitIds.length === 0);

        // Filter Grades
        document.querySelectorAll('.grade-item-wrapper').forEach(wrapper => {
            const uId = (wrapper.dataset.unitId || '').toString();
            if (isGlobalOrAll || unitIds.includes(uId)) {
                wrapper.style.display = 'flex';
            } else {
                wrapper.style.display = 'none';
                const cb = wrapper.querySelector('.fee-grade-checkbox');
                if (cb) cb.checked = false;
            }
        });
        window.updateCheckAllGradesState();

        // Filter Class Programs
        document.querySelectorAll('.program-item-wrapper').forEach(wrapper => {
            const uIds = (wrapper.dataset.unitIds || '').split(',').map(s => s.trim());
            if (isGlobalOrAll || uIds.length === 0 || uIds.some(id => unitIds.includes(id))) {
                wrapper.style.display = 'flex';
            } else {
                wrapper.style.display = 'none';
                const cb = wrapper.querySelector('.fee-program-checkbox');
                if (cb) cb.checked = false;
            }
        });
        window.updateCheckAllClassProgramsState();

        // Filter Registration Types
        document.querySelectorAll('.type-item-wrapper').forEach(wrapper => {
            const uIds = (wrapper.dataset.unitIds || '').split(',').map(s => s.trim());
            if (isGlobalOrAll || uIds.length === 0 || uIds.some(id => unitIds.includes(id))) {
                wrapper.style.display = 'flex';
            } else {
                wrapper.style.display = 'none';
                const cb = wrapper.querySelector('.fee-type-checkbox');
                if (cb) cb.checked = false;
            }
        });
        window.updateCheckAllTypesState();
    };

    // Combined Filter for Unit and Period
    window.applyFeeFilters = function() {
        // 1. Update Unit filter button styles
        document.querySelectorAll('.unit-filter-btn').forEach(btn => {
            btn.className = "unit-filter-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap bg-white text-slate-650 hover:bg-slate-100 border border-slate-200/60 cursor-pointer";
        });
        const activeUnitBtnId = window.currentUnitFilter ? 'unitFilterBtn-' + window.currentUnitFilter : 'unitFilterBtn-all';
        const activeUnitBtn = document.getElementById(activeUnitBtnId);
        if (activeUnitBtn) {
            activeUnitBtn.className = "unit-filter-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap bg-brand-emerald text-white shadow-xs cursor-pointer";
        }

        // 2. Update Period filter button styles
        document.querySelectorAll('.period-filter-btn').forEach(btn => {
            btn.className = "period-filter-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap bg-white text-slate-650 hover:bg-slate-100 border border-slate-200/60 cursor-pointer";
        });
        const activePeriodBtnId = window.currentPeriodFilter ? 'periodFilterBtn-' + window.currentPeriodFilter : 'periodFilterBtn-all';
        const activePeriodBtn = document.getElementById(activePeriodBtnId);
        if (activePeriodBtn) {
            activePeriodBtn.className = "period-filter-btn px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 whitespace-nowrap bg-indigo-600 text-white shadow-xs cursor-pointer";
        }

        // 3. Filter Category Rows in Tab 1 (Jenis Biaya)
        const catRows = document.querySelectorAll('.category-item-row');
        let visibleCatCount = 0;
        catRows.forEach(row => {
            const uIds = (row.dataset.unitIds || '').split(',').map(s => s.trim()).filter(Boolean);
            const pIds = (row.dataset.periodIds || '').split(',').map(s => s.trim()).filter(Boolean);

            const matchUnit = !window.currentUnitFilter || uIds.length === 0 || uIds.includes(window.currentUnitFilter);
            const matchPeriod = !window.currentPeriodFilter || pIds.includes(window.currentPeriodFilter);

            if (matchUnit && matchPeriod) {
                row.style.display = '';
                visibleCatCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const emptyCatFiltered = document.getElementById('emptyCatRow-filtered');
        if (emptyCatFiltered) {
            if (catRows.length > 0 && visibleCatCount === 0) {
                emptyCatFiltered.classList.remove('hidden');
            } else {
                emptyCatFiltered.classList.add('hidden');
            }
        }

        // 4. Filter Fee Rows in Category Tabs
        document.querySelectorAll('.fee-tab-content').forEach(tabContent => {
            const feeRows = tabContent.querySelectorAll('.fee-item-row');
            if (feeRows.length === 0) return;

            let visibleFeeCount = 0;
            let catId = null;
            feeRows.forEach(row => {
                catId = row.dataset.categoryId;
                const uId = (row.dataset.unitId || '').toString().trim();
                const pIds = (row.dataset.periodIds || '').split(',').map(s => s.trim()).filter(Boolean);

                const matchUnit = !window.currentUnitFilter || uId === '' || uId === window.currentUnitFilter;
                const matchPeriod = !window.currentPeriodFilter || pIds.includes(window.currentPeriodFilter);

                if (matchUnit && matchPeriod) {
                    row.style.display = '';
                    visibleFeeCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (catId) {
                const filteredEmpty = document.getElementById(`emptyFeeRow-${catId}-filtered`);
                if (filteredEmpty) {
                    if (feeRows.length > 0 && visibleFeeCount === 0) {
                        filteredEmpty.classList.remove('hidden');
                    } else {
                        filteredEmpty.classList.add('hidden');
                    }
                }
            }
        });

        // 5. Update URL search params
        const url = new URL(window.location.href);
        if (window.currentUnitFilter) {
            url.searchParams.set('unit_id', window.currentUnitFilter);
        } else {
            url.searchParams.delete('unit_id');
        }
        if (window.currentPeriodFilter) {
            url.searchParams.set('period_id', window.currentPeriodFilter);
        } else {
            url.searchParams.delete('period_id');
        }
        window.history.replaceState({ path: url.toString() }, '', url.toString());

        localStorage.setItem('spmb_fees_active_unit', window.currentUnitFilter);
        localStorage.setItem('spmb_fees_active_period', window.currentPeriodFilter);

        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }
    };

    // Filter by Unit
    window.filterFeesByUnit = function(unitId) {
        window.currentUnitFilter = unitId ? unitId.toString() : '';
        window.applyFeeFilters();
    };

    // Filter by Period
    window.filterFeesByPeriod = function(periodId) {
        window.currentPeriodFilter = periodId ? periodId.toString() : '';
        window.applyFeeFilters();
    };

    // Tab Switching
    window.switchFeeTab = function(tabId) {
        const panel = document.getElementById('feeTabContent-' + tabId);
        if (!panel) return;

        document.querySelectorAll('.fee-tab-content').forEach(el => el.classList.add('hidden'));
        panel.classList.remove('hidden');

        document.querySelectorAll('.fee-tab-btn').forEach(btn => {
            btn.className = "fee-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50 cursor-pointer";
        });
        
        const activeBtn = document.getElementById('feeTabBtn-' + tabId);
        if (activeBtn) {
            activeBtn.className = "fee-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow cursor-pointer";
        }
        
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabId);
        if (window.currentUnitFilter) {
            url.searchParams.set('unit_id', window.currentUnitFilter);
        }
        if (window.currentPeriodFilter) {
            url.searchParams.set('period_id', window.currentPeriodFilter);
        }
        window.history.replaceState({ path: url.toString() }, '', url.toString());
        localStorage.setItem('spmb_fees_active_tab', tabId);
    };

    // Unified Fee Modal Control
    window.openFeeModal = function(moduleType, val = '', isLocked = false, actionUrl = '', amount = '', gateway = 'winpay', categoryId = '', unitId = '', categoryUnits = [], categoryType = 'tuition_fee', applicableGrades = [], applicableClassPrograms = [], applicableTypes = [], applicableGender = 'all', applicablePeriods = []) {
        const errorWrapper = document.getElementById('feeErrorWrapper');
        if (errorWrapper) {
            errorWrapper.classList.add('hidden');
        }

        const form = document.getElementById('feeCrudForm');
        form.setAttribute('action', actionUrl);
        
        const mainInput = document.getElementById('feeMainInput');
        mainInput.value = val;
        mainInput.disabled = false;

        const categoryInput = document.getElementById('feeCategoryInput');
        categoryInput.value = categoryId;

        const amountInput = document.getElementById('feeAmountInput');
        const amountWrapper = document.getElementById('feeAmountWrapper');
        const targetingWrapper = document.getElementById('feeTargetingWrapper');
        const unitWrapper = document.getElementById('feeUnitWrapper');
        const categoryUnitsWrapper = document.getElementById('categoryUnitsWrapper');
        const categoryPeriodsWrapper = document.getElementById('categoryPeriodsWrapper');
        const catTypeWrapper = document.getElementById('categoryTypeWrapper');
        const catTypeSelect = document.getElementById('categoryTypeSelect');
        
        const titleEl = document.getElementById('feeModalTitle');
        const labelEl = document.getElementById('feeInputLabel');

        if (amountInput) {
            amountInput.readOnly = false;
            amountInput.classList.remove('bg-slate-200', 'cursor-not-allowed', 'text-slate-500');
            amountInput.classList.add('bg-slate-50', 'text-slate-800');
        }
        const warningEl = document.getElementById('feeAmountWarning');
        if (warningEl) warningEl.classList.add('hidden');

        // Reset category checkboxes
        document.querySelectorAll('.unit-checkbox').forEach(cb => cb.checked = false);
        const checkAll = document.getElementById('checkAllUnits');
        if (checkAll) checkAll.checked = false;

        // Reset category periods checkboxes
        document.querySelectorAll('.category-period-checkbox').forEach(cb => cb.checked = false);
        const checkAllCatP = document.getElementById('checkAllCategoryPeriods');
        if (checkAllCatP) checkAllCatP.checked = false;

        // Reset nominal fee unit checkboxes
        document.querySelectorAll('.fee-unit-checkbox').forEach(cb => cb.checked = false);
        const checkAllFee = document.getElementById('checkAllFeeUnits');
        if (checkAllFee) checkAllFee.checked = false;

        // Reset fee periods checkboxes
        document.querySelectorAll('.fee-period-checkbox').forEach(cb => cb.checked = false);
        const checkAllFeeP = document.getElementById('checkAllFeePeriods');
        if (checkAllFeeP) checkAllFeeP.checked = false;

        // Reset gateway checkboxes
        document.querySelectorAll('.fee-gateway-checkbox').forEach(cb => cb.checked = false);

        // Reset targeting checkboxes
        document.querySelectorAll('.fee-grade-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.fee-program-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.fee-type-checkbox').forEach(cb => cb.checked = false);
        const checkAllG = document.getElementById('checkAllGrades');
        if (checkAllG) checkAllG.checked = false;
        const checkAllP = document.getElementById('checkAllClassPrograms');
        if (checkAllP) checkAllP.checked = false;
        const checkAllT = document.getElementById('checkAllTypes');
        if (checkAllT) checkAllT.checked = false;

        // Determine target periods (if editing, use provided; if creating, precheck all or current filtered period)
        let targetPeriodsArr = [];
        if (Array.isArray(applicablePeriods) && applicablePeriods.length > 0) {
            targetPeriodsArr = applicablePeriods.map(x => x.toString());
        } else if (applicablePeriods && typeof applicablePeriods === 'string' && applicablePeriods !== '') {
            targetPeriodsArr = [applicablePeriods.toString()];
        } else if (!val) {
            // New item creation default: if period filter active use it, otherwise check all periods
            if (window.currentPeriodFilter) {
                targetPeriodsArr = [window.currentPeriodFilter.toString()];
            } else {
                const targetSelector = (moduleType === 'jenis_biaya') ? '.category-period-checkbox' : '.fee-period-checkbox';
                targetPeriodsArr = Array.from(document.querySelectorAll(targetSelector)).map(cb => cb.value.toString());
            }
        }

        if (moduleType === 'jenis_biaya') {
            titleEl.innerText = val ? 'Edit Jenis Biaya' : 'Tambah Jenis Biaya';
            labelEl.innerText = 'Nama Jenis Biaya*';
            mainInput.placeholder = 'Contoh: Uang SPP';
            
            amountWrapper.classList.add('hidden');
            amountInput.required = false;
            if (targetingWrapper) targetingWrapper.classList.add('hidden');
            if (unitWrapper) unitWrapper.classList.add('hidden');

            if (catTypeWrapper) {
                catTypeWrapper.classList.remove('hidden');
                if (catTypeSelect) catTypeSelect.value = categoryType || 'tuition_fee';
            }

            if (categoryPeriodsWrapper) {
                categoryPeriodsWrapper.classList.remove('hidden');
                document.querySelectorAll('.category-period-checkbox').forEach(cb => {
                    cb.checked = targetPeriodsArr.includes(cb.value.toString());
                });
                window.updateCheckAllCategoryPeriodsState();
            }

            if (categoryUnitsWrapper) {
                categoryUnitsWrapper.classList.remove('hidden');
                let targetUnits = (categoryUnits && categoryUnits.length > 0) ? categoryUnits : (window.currentUnitFilter ? [window.currentUnitFilter] : []);
                if (targetUnits.length > 0) {
                    targetUnits.forEach(uId => {
                        const cb = document.querySelector(`.unit-checkbox[value="${uId}"]`);
                        if (cb) cb.checked = true;
                    });
                    window.updateCheckAllState();
                }
            }
        } else {
            titleEl.innerText = val ? 'Edit Nominal Biaya' : 'Tambah Nominal Biaya';
            labelEl.innerText = 'Nama Biaya*';
            mainInput.placeholder = 'Contoh: Biaya Pendaftaran TK B';
            
            if (catTypeWrapper) catTypeWrapper.classList.add('hidden');
            if (categoryPeriodsWrapper) categoryPeriodsWrapper.classList.add('hidden');

            amountWrapper.classList.remove('hidden');
            if (targetingWrapper) targetingWrapper.classList.remove('hidden');
            amountInput.value = window.formatRupiah(amount);
            amountInput.required = true;
            
            const isLockedBool = (isLocked === 'true' || isLocked === true || isLocked === '1');
            amountInput.readOnly = isLockedBool;
            if (isLockedBool) {
                amountInput.classList.add('bg-slate-200', 'cursor-not-allowed', 'text-slate-500');
                amountInput.classList.remove('bg-slate-50', 'text-slate-800');
                if (warningEl) warningEl.classList.remove('hidden');
            } else {
                amountInput.classList.remove('bg-slate-200', 'cursor-not-allowed', 'text-slate-500');
                amountInput.classList.add('bg-slate-50', 'text-slate-800');
                if (warningEl) warningEl.classList.add('hidden');
            }
            
            if (gateway) {
                let selectedGateways = [];
                try {
                    if (gateway.startsWith('[')) {
                        selectedGateways = JSON.parse(gateway);
                    } else {
                        selectedGateways = gateway.split(',');
                    }
                } catch(e) {
                    selectedGateways = gateway.split(',');
                }

                selectedGateways.forEach(code => {
                    let checkCode = code.trim();
                    const cb = document.querySelector(`.fee-gateway-checkbox[value="${checkCode}"]`);
                    if (cb) cb.checked = true;
                });
            }

            if (unitWrapper) {
                unitWrapper.classList.remove('hidden');
                let effectiveUnitId = unitId || window.currentUnitFilter || (currentUserUnitId || '');
                let selectedUnits = [];
                if (effectiveUnitId) {
                    let unitIdStr = effectiveUnitId.toString();
                    if (unitIdStr.includes(',')) {
                        selectedUnits = unitIdStr.split(',').map(id => id.trim());
                    } else {
                        selectedUnits = [unitIdStr];
                    }
                }
                document.querySelectorAll('.fee-unit-checkbox').forEach(cb => {
                    cb.checked = selectedUnits.includes(cb.value.toString());
                });
                window.updateCheckAllFeeState();
            }

            // Populate Period targeting for nominal fee
            document.querySelectorAll('.fee-period-checkbox').forEach(cb => {
                cb.checked = targetPeriodsArr.includes(cb.value.toString());
            });
            window.updateCheckAllFeePeriodsState();

            // Populate targeting selections
            let targetGradesArr = [];
            if (Array.isArray(applicableGrades)) targetGradesArr = applicableGrades.map(x => x.toString());
            else if (applicableGrades) targetGradesArr = [applicableGrades.toString()];

            document.querySelectorAll('.fee-grade-checkbox').forEach(cb => {
                cb.checked = targetGradesArr.includes(cb.value.toString());
            });

            let targetProgsArr = [];
            if (Array.isArray(applicableClassPrograms)) targetProgsArr = applicableClassPrograms.map(x => x.toString());
            else if (applicableClassPrograms) targetProgsArr = [applicableClassPrograms.toString()];

            document.querySelectorAll('.fee-program-checkbox').forEach(cb => {
                cb.checked = targetProgsArr.includes(cb.value.toString());
            });

            let targetTypesArr = [];
            if (Array.isArray(applicableTypes)) targetTypesArr = applicableTypes.map(x => x.toString());
            else if (applicableTypes) targetTypesArr = [applicableTypes.toString()];

            document.querySelectorAll('.fee-type-checkbox').forEach(cb => {
                cb.checked = targetTypesArr.includes(cb.value.toString());
            });

            // Populate Gender radio
            const effGender = (applicableGender && applicableGender !== '') ? applicableGender.toLowerCase() : 'all';
            document.querySelectorAll('.fee-gender-radio').forEach(radio => {
                radio.checked = (radio.value === effGender);
            });

            const effectiveUnit = unitId || (isSuperAdmin ? window.getSelectedFeeUnits() : currentUserUnitId);
            window.filterTargetingCheckboxesByUnit(effectiveUnit);

            if (categoryUnitsWrapper) categoryUnitsWrapper.classList.add('hidden');
        }

        document.getElementById('feeCrudModal').classList.remove('hidden');
    };

    // Close Modal
    window.closeFeeModal = function() {
        document.getElementById('feeCrudModal').classList.add('hidden');
        const errorWrapper = document.getElementById('feeErrorWrapper');
        if (errorWrapper) {
            errorWrapper.classList.add('hidden');
        }
    };

    document.getElementById('feeCrudModal').addEventListener('click', function(e) {
        if (e.target === this) window.closeFeeModal();
    });

    // Delete Operations
    window.deleteFeeItem = function(type, name, isUsed, deleteUrl) {
        let label = (type === 'jenis_biaya') ? 'Jenis Biaya' : 'Nominal Biaya';
        if (isUsed === 'true' || isUsed === true || isUsed === '1') {
            showToast(`Peringatan: Tidak dapat menghapus ${label} "${name}" karena sudah terpakai dalam data transaksi pembayaran aktif!`, 'error');
        } else {
            confirmDelete(deleteUrl, `Apakah Anda yakin ingin menghapus ${label} "${name}"?`);
        }
    };

    // Edit Helpers for clean data passing
    window.editFeeItem = function(data) {
        openFeeModal('biaya_tambahan', data.name, data.is_used, data.update_url, data.amount, data.payment_gateway, data.category_id, data.unit_id, [], 'tuition_fee', data.applicable_grades, data.applicable_class_programs, data.applicable_types, data.applicable_gender, data.applicable_periods);
    };

    window.editCategoryItem = function(data) {
        openFeeModal('jenis_biaya', data.name, data.is_used, data.update_url, '', 'winpay', '', '', data.units, data.category_type, [], [], [], 'all', data.applicable_periods);
    };

    // Auto-reopen modal if validation failed on redirect
    @if(session('failed_modal'))
        document.addEventListener("DOMContentLoaded", function() {
            let failed = {!! json_encode(session('failed_modal')) !!};
            if (failed.startsWith('jenis_biaya_create')) {
                window.switchFeeTab('jenis_biaya');
                window.openFeeModal('jenis_biaya', {!! json_encode(old('name', '')) !!}, false, {!! json_encode(route('admin.spmb-settings.fees.categories.store')) !!}, '', 'winpay', '', '', {!! json_encode(is_array(old('spmb_units')) ? old('spmb_units') : []) !!}, {!! json_encode(old('category_type', 'tuition_fee')) !!}, [], [], [], 'all', {!! json_encode(old('applicable_periods', [])) !!});
            } else if (failed.startsWith('jenis_biaya_edit_')) {
                window.switchFeeTab('jenis_biaya');
                let id = failed.replace('jenis_biaya_edit_', '');
                window.openFeeModal('jenis_biaya', {!! json_encode(old('name', '')) !!}, false, '/admin/spmb-settings/fees/categories/' + id, '', 'winpay', '', '', {!! json_encode(is_array(old('spmb_units')) ? old('spmb_units') : []) !!}, {!! json_encode(old('category_type', 'tuition_fee')) !!}, [], [], [], 'all', {!! json_encode(old('applicable_periods', [])) !!});
            } else if (failed.startsWith('biaya_admin_create')) {
                const oldCatId = {!! json_encode(old('spmb_fee_category_id', '')) !!};
                if (oldCatId) {
                    window.switchFeeTab('cat_' + oldCatId);
                    window.openFeeModal('biaya_tambahan', {!! json_encode(old('name', '')) !!}, false, {!! json_encode(route('admin.spmb-settings.fees.admin-fees.store')) !!}, {!! json_encode(old('amount', '')) !!}, {!! json_encode(is_array(old('payment_gateway')) ? implode(',', old('payment_gateway')) : old('payment_gateway', 'winpay')) !!}, oldCatId, {!! json_encode(is_array(old('spmb_units')) ? implode(',', old('spmb_units')) : old('spmb_units', '')) !!}, [], 'tuition_fee', {!! json_encode(old('applicable_grades', [])) !!}, {!! json_encode(old('applicable_class_programs', [])) !!}, {!! json_encode(old('applicable_types', [])) !!}, {!! json_encode(old('applicable_gender', 'all')) !!}, {!! json_encode(old('applicable_periods', [])) !!});
                }
            } else if (failed.startsWith('biaya_admin_edit_')) {
                const oldCatId = {!! json_encode(old('spmb_fee_category_id', '')) !!};
                let id = failed.replace('biaya_admin_edit_', '');
                if (oldCatId) {
                    window.switchFeeTab('cat_' + oldCatId);
                    window.openFeeModal('biaya_tambahan', {!! json_encode(old('name', '')) !!}, false, '/admin/spmb-settings/fees/admin-fees/' + id, {!! json_encode(old('amount', '')) !!}, {!! json_encode(is_array(old('payment_gateway')) ? implode(',', old('payment_gateway')) : old('payment_gateway', 'winpay')) !!}, oldCatId, {!! json_encode(is_array(old('spmb_units')) ? implode(',', old('spmb_units')) : old('spmb_units', '')) !!}, [], 'tuition_fee', {!! json_encode(old('applicable_grades', [])) !!}, {!! json_encode(old('applicable_class_programs', [])) !!}, {!! json_encode(old('applicable_types', [])) !!}, {!! json_encode(old('applicable_gender', 'all')) !!}, {!! json_encode(old('applicable_periods', [])) !!});
                }
            }

            const errorWrapper = document.getElementById('feeErrorWrapper');
            if (errorWrapper) {
                errorWrapper.classList.remove('hidden');
            }
        });
    @endif

    // Initial Filter sync on DOM ready
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        const urlUnit = urlParams.get('unit_id');
        const urlPeriod = urlParams.get('period_id');
        const savedUnit = localStorage.getItem('spmb_fees_active_unit');
        const savedPeriod = localStorage.getItem('spmb_fees_active_period');
        
        if (urlUnit !== null) {
            window.currentUnitFilter = urlUnit;
        } else if (savedUnit !== null && savedUnit !== '') {
            window.currentUnitFilter = savedUnit;
        }

        if (urlPeriod !== null) {
            window.currentPeriodFilter = urlPeriod;
        } else if (savedPeriod !== null && savedPeriod !== '') {
            window.currentPeriodFilter = savedPeriod;
        }

        window.applyFeeFilters();
    });

    // Escape key listener to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('feeCrudModal');
            if (modal && !modal.classList.contains('hidden')) {
                window.closeFeeModal();
            }
        }
    });
</script>
@endsection
