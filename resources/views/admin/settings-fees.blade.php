@extends('layouts.admin')

@section('title', 'Setting Biaya - Admin Panel')
@section('page_title', 'Setting Biaya')

@section('content')
<div class="w-full space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h1 class="text-xl font-extrabold text-slate-800">Manajemen Biaya Pendaftaran (SPMB)</h1>
        <p class="text-xs text-slate-500 mt-1">Mengatur jenis-jenis kategori biaya dan nominal biaya pendaftaran calon siswa baru.</p>
    </div>

    <!-- Tab Navigation Pills -->
    <div class="flex flex-wrap gap-2 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
        <button onclick="switchFeeTab('jenis_biaya')" id="feeTabBtn-jenis_biaya" class="fee-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow">
            <i data-lucide="tag" class="w-4 h-4"></i> Jenis Biaya
        </button>
        @foreach($categories as $cat)
            <button onclick="switchFeeTab('cat_{{ $cat->id }}')" id="feeTabBtn-cat_{{ $cat->id }}" class="fee-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
                <i data-lucide="coins" class="w-4 h-4"></i> {{ $cat->name }}
            </button>
        @endforeach
    </div>

    <!-- Tab Contents -->
    <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        
        <!-- Tab 1: Jenis Biaya -->
        <div id="feeTabContent-jenis_biaya" class="fee-tab-content p-8 space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-extrabold text-base text-slate-800">Kategori Jenis Biaya</h3>
                    <p class="text-[11px] text-slate-400">Kelola kelompok jenis pembayaran masuk.</p>
                </div>
                <button onclick="openFeeModal('jenis_biaya', '', '', '{{ route('admin.spmb-settings.fees.categories.store') }}')" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Jenis Biaya
                </button>
            </div>
            
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Jenis Biaya</th>
                            @if(auth()->user()->isSuperAdmin())
                                <th class="py-4 px-6 text-center">Unit Pengguna</th>
                            @endif
                            <th class="py-4 px-6 text-center">Digunakan Transaksi</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($categories as $cat)
                            <tr class="hover:bg-slate-50/30 transition">
                                <td class="py-4 px-6 font-extrabold text-slate-800">{{ $cat->name }}</td>
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
                                <td class="py-4 px-6 text-right space-x-2">
                                    <button onclick="openFeeModal('jenis_biaya', '{{ addslashes($cat->name) }}', '{{ $cat->is_used }}', '{{ route('admin.spmb-settings.fees.categories.update', $cat->id) }}', '', 'winpay', '', '', [{{ implode(',', $cat->units->pluck('id')->toArray()) }}])" class="text-xs text-brand-emerald font-bold hover:underline">Edit</button>
                                    @if(!$cat->is_used)
                                        <button onclick="deleteFeeItem('jenis_biaya', '{{ addslashes($cat->name) }}', '{{ $cat->is_used }}', '{{ route('admin.spmb-settings.fees.categories.delete', $cat->id) }}')" class="text-xs text-red-600 font-bold hover:underline">Hapus</button>
                                    @else
                                        <span class="text-xs text-slate-350 cursor-not-allowed font-bold" title="Kategori sedang digunakan">Hapus</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 px-6 text-center text-slate-400">Belum ada data jenis biaya.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Dynamic Category Tabs -->
        @foreach($categories as $cat)
            <div id="feeTabContent-cat_{{ $cat->id }}" class="fee-tab-content p-8 space-y-6 hidden">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-extrabold text-base text-slate-800">Daftar Nominal {{ $cat->name }}</h3>
                        <p class="text-[11px] text-slate-400">Atur besaran nominal untuk kategori {{ $cat->name }}.</p>
                    </div>
                    <button onclick="openFeeModal('biaya_tambahan', '', false, '{{ route('admin.spmb-settings.fees.admin-fees.store') }}', '', 'winpay', '{{ $cat->id }}', '')" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah {{ $cat->name }}
                    </button>
                </div>
                
                <div class="overflow-x-auto border border-slate-100 rounded-xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                                <th class="py-4 px-6">Nama Biaya</th>
                                <th class="py-4 px-6 text-center">Unit Sekolah</th>
                                <th class="py-4 px-6 text-center">Nominal (Rp)</th>
                                <th class="py-4 px-6 text-center">Payment Gateway</th>
                                <th class="py-4 px-6 text-center">Digunakan Transaksi</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            @forelse($fees->where('spmb_fee_category_id', $cat->id) as $fee)
                                <tr class="hover:bg-slate-50/30 transition">
                                    <td class="py-4 px-6 font-extrabold text-slate-800">{{ $fee->name }}</td>
                                    <td class="py-4 px-6 text-center font-semibold text-slate-500 text-xs">
                                        {{ $fee->unit->code ?? 'Global' }}
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
                                    <td class="py-4 px-6 text-right space-x-2">
                                        <button onclick="openFeeModal('biaya_tambahan', '{{ addslashes($fee->name) }}', {{ $fee->is_used ? 'true' : 'false' }}, '{{ route('admin.spmb-settings.fees.admin-fees.update', $fee->id) }}', '{{ $fee->amount }}', '{{ is_array($fee->payment_gateway) ? implode(',', $fee->payment_gateway) : $fee->payment_gateway }}', '{{ $cat->id }}', '{{ $fee->spmb_unit_id }}')" class="text-xs text-brand-emerald font-bold hover:underline">Edit</button>
                                        <button onclick="deleteFeeItem('biaya_tambahan', '{{ addslashes($fee->name) }}', {{ $fee->is_used ? 'true' : 'false' }}, '{{ route('admin.spmb-settings.fees.admin-fees.delete', $fee->id) }}')" class="text-xs text-red-600 font-bold hover:underline">Hapus</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 px-6 text-center text-slate-400 text-xs">Belum ada data nominal untuk kategori ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

    </div>
</div>

<!-- Unified Fee CRUD Modal -->
<div id="feeCrudModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-2xl max-w-md w-full mx-4 shadow-2xl border border-slate-100 overflow-hidden">
        <div class="bg-brand-emerald text-white px-6 py-4">
            <h3 id="feeModalTitle" class="font-extrabold text-lg">Tambah</h3>
            <p class="text-xs text-emerald-100 mt-0.5">Kelola data konfigurasi setting biaya.</p>
        </div>
        <form id="feeCrudForm" method="POST" class="p-6 space-y-4">
            @csrf
            
            <input type="hidden" id="feeCategoryInput" name="spmb_fee_category_id">

            @if($errors->any() && session('failed_modal'))
                <div class="text-xs text-red-600 bg-red-50 p-3.5 rounded-xl border border-red-200 font-semibold mb-3 space-y-1">
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
                        <div class="grid grid-cols-1 gap-2">
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
                        <div class="grid grid-cols-1 gap-2">
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

            <div>
                <label id="feeInputLabel" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nama*</label>
                <input type="text" id="feeMainInput" name="name" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
            </div>
            
            <!-- Amount Input (Only visible for Biaya Tambahan) -->
            <div id="feeAmountWrapper" class="hidden space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nominal (Rupiah)*</label>
                    <input type="text" id="feeAmountInput" name="amount" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm" placeholder="Contoh: 350.000">
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
            
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeFeeModal()" class="border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition">
                    Kembali
                </button>
                <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2 rounded-xl text-xs font-bold transition shadow-md">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="feeDeleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    // Format as thousands
    function formatRupiah(value) {
        if (!value) return '';
        let str = value.toString();
        // If database float representation (ends with .00), strip it
        if (str.endsWith('.00')) {
            str = str.substring(0, str.length - 3);
        }
        let clean = str.replace(/\D/g, '');
        return clean.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    document.addEventListener("DOMContentLoaded", function() {
        const amountInput = document.getElementById('feeAmountInput');
        if (amountInput) {
            amountInput.addEventListener('input', function(e) {
                let formatted = formatRupiah(e.target.value);
                e.target.value = formatted;
            });
        }

        const form = document.getElementById('feeCrudForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const amountInput = document.getElementById('feeAmountInput');
                if (amountInput && amountInput.value) {
                    // Remove all dot separators before sending to backend
                    amountInput.value = amountInput.value.replace(/\./g, '');
                }
            });
        }
    });

    // Checkbox controls
    function toggleAllUnits(source) {
        document.querySelectorAll('.unit-checkbox').forEach(cb => {
            cb.checked = source.checked;
        });
    }

    function updateCheckAllState() {
        const checkboxes = document.querySelectorAll('.unit-checkbox');
        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        const checkAll = document.getElementById('checkAllUnits');
        if (checkAll) {
            checkAll.checked = checkedCount === checkboxes.length;
        }
    }

    function toggleAllFeeUnits(source) {
        document.querySelectorAll('.fee-unit-checkbox').forEach(cb => {
            cb.checked = source.checked;
        });
    }

    function updateCheckAllFeeState() {
        const checkboxes = document.querySelectorAll('.fee-unit-checkbox');
        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        const checkAll = document.getElementById('checkAllFeeUnits');
        if (checkAll) {
            checkAll.checked = checkedCount === checkboxes.length;
        }
    }

    // Tab Switching
    function switchFeeTab(tabId) {
        document.querySelectorAll('.fee-tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('feeTabContent-' + tabId).classList.remove('hidden');

        document.querySelectorAll('.fee-tab-btn').forEach(btn => {
            btn.className = "fee-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50";
        });
        
        const activeBtn = document.getElementById('feeTabBtn-' + tabId);
        if (activeBtn) {
            activeBtn.className = "fee-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow";
        }
        
        localStorage.setItem('spmb_fees_active_tab', tabId);
    }

    document.addEventListener("DOMContentLoaded", function() {
        const savedTab = localStorage.getItem('spmb_fees_active_tab') || 'jenis_biaya';
        switchFeeTab(savedTab);
    });

    // Modal Control
    function openFeeModal(moduleType, val = '', isLocked = false, actionUrl = '', amount = '', gateway = 'winpay', categoryId = '', unitId = '', categoryUnits = []) {
        const form = document.getElementById('feeCrudForm');
        form.action = actionUrl;
        
        const mainInput = document.getElementById('feeMainInput');
        mainInput.value = val;
        mainInput.disabled = false;

        const categoryInput = document.getElementById('feeCategoryInput');
        categoryInput.value = categoryId;

        const amountInput = document.getElementById('feeAmountInput');
        const amountWrapper = document.getElementById('feeAmountWrapper');
        const unitWrapper = document.getElementById('feeUnitWrapper');
        const categoryUnitsWrapper = document.getElementById('categoryUnitsWrapper');
        
        const titleEl = document.getElementById('feeModalTitle');
        const labelEl = document.getElementById('feeInputLabel');

        // Reset category checkboxes
        document.querySelectorAll('.unit-checkbox').forEach(cb => cb.checked = false);
        const checkAll = document.getElementById('checkAllUnits');
        if (checkAll) checkAll.checked = false;

        // Reset nominal fee checkboxes
        document.querySelectorAll('.fee-unit-checkbox').forEach(cb => cb.checked = false);
        const checkAllFee = document.getElementById('checkAllFeeUnits');
        if (checkAllFee) checkAllFee.checked = false;

        // Reset gateway checkboxes
        document.querySelectorAll('.fee-gateway-checkbox').forEach(cb => cb.checked = false);

        if (moduleType === 'jenis_biaya') {
            titleEl.innerText = val ? 'Edit Jenis Biaya' : 'Tambah Jenis Biaya';
            labelEl.innerText = 'Nama Jenis Biaya*';
            mainInput.placeholder = 'Contoh: Uang SPP';
            
            amountWrapper.classList.add('hidden');
            amountInput.required = false;
            if (unitWrapper) unitWrapper.classList.add('hidden');

            if (categoryUnitsWrapper) {
                categoryUnitsWrapper.classList.remove('hidden');
                if (categoryUnits && categoryUnits.length > 0) {
                    categoryUnits.forEach(uId => {
                        const cb = document.querySelector(`.unit-checkbox[value="${uId}"]`);
                        if (cb) cb.checked = true;
                    });
                    updateCheckAllState();
                }
            }
        } else {
            titleEl.innerText = val ? 'Edit Nominal Biaya' : 'Tambah Nominal Biaya';
            labelEl.innerText = 'Nama Biaya*';
            mainInput.placeholder = 'Contoh: Biaya Pendaftaran TK B';
            
            amountWrapper.classList.remove('hidden');
            amountInput.value = formatRupiah(amount);
            amountInput.required = true;
            amountInput.disabled = false; // Always allow editing to fix typos
            
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
                document.querySelectorAll('.fee-unit-checkbox').forEach(cb => {
                    cb.checked = (cb.value == unitId);
                });
                updateCheckAllFeeState();
            }

            if (categoryUnitsWrapper) categoryUnitsWrapper.classList.add('hidden');
        }

        document.getElementById('feeCrudModal').classList.remove('hidden');
    }

    // Close Modal
    function closeFeeModal() {
        document.getElementById('feeCrudModal').classList.add('hidden');
    }

    // Delete Operations
    function deleteFeeItem(type, name, isUsed, deleteUrl) {
        let label = (type === 'jenis_biaya') ? 'Jenis Biaya' : 'Nominal Biaya';
        if (isUsed === 'true' || isUsed === true || isUsed === '1') {
            showToast(`Peringatan: Tidak dapat menghapus ${label} "${name}" karena sudah terpakai dalam data transaksi pembayaran aktif!`, 'error');
        } else {
            confirmDelete(deleteUrl, `Apakah Anda yakin ingin menghapus ${label} "${name}"?`);
        }
    }

    // Auto-reopen modal if validation failed on redirect
    @if(session('failed_modal'))
        document.addEventListener("DOMContentLoaded", function() {
            let failed = "{{ session('failed_modal') }}";
            if (failed.startsWith('jenis_biaya_create')) {
                switchFeeTab('jenis_biaya');
                openFeeModal('jenis_biaya', '{{ old('name') }}', false, '{{ route('admin.spmb-settings.fees.categories.store') }}');
            } else if (failed.startsWith('jenis_biaya_edit_')) {
                switchFeeTab('jenis_biaya');
                let id = failed.replace('jenis_biaya_edit_', '');
                openFeeModal('jenis_biaya', '{{ old('name') }}', false, '/admin/spmb-settings/fees/categories/' + id);
            } else if (failed.startsWith('biaya_admin_create')) {
                const oldCatId = "{{ old('spmb_fee_category_id') }}";
                const oldUnitId = "{{ old('spmb_unit_id') }}";
                if (oldCatId) {
                    switchFeeTab('cat_' + oldCatId);
                    openFeeModal('biaya_tambahan', '{{ old('name') }}', false, '{{ route('admin.spmb-settings.fees.admin-fees.store') }}', '{{ old('amount') }}', '{{ is_array(old('payment_gateway')) ? implode(',', old('payment_gateway')) : old('payment_gateway') }}', oldCatId, oldUnitId);
                }
            } else if (failed.startsWith('biaya_admin_edit_')) {
                const oldCatId = "{{ old('spmb_fee_category_id') }}";
                const oldUnitId = "{{ old('spmb_unit_id') }}";
                let id = failed.replace('biaya_admin_edit_', '');
                if (oldCatId) {
                    switchFeeTab('cat_' + oldCatId);
                    openFeeModal('biaya_tambahan', '{{ old('name') }}', false, '/admin/spmb-settings/fees/admin-fees/' + id, '{{ old('amount') }}', '{{ is_array(old('payment_gateway')) ? implode(',', old('payment_gateway')) : old('payment_gateway') }}', oldCatId, oldUnitId);
                }
            }
        });
    @endif
</script>
@endsection
