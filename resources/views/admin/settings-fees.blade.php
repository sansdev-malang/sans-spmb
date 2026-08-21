@extends('layouts.admin')

@section('title', 'Setting Biaya - Admin Panel')
@section('page_title', 'Setting Biaya')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h1 class="text-xl font-extrabold text-slate-800">Manajemen Biaya Pendaftaran (SPMB)</h1>
        <p class="text-xs text-slate-500 mt-1">Mengatur jenis-jenis kategori biaya dan nominal biaya tambahan pendaftaran calon siswa baru.</p>
    </div>

    <!-- Tab Navigation Pills -->
    <div class="flex flex-wrap gap-2 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
        <button onclick="switchFeeTab('jenis_biaya')" id="feeTabBtn-jenis_biaya" class="fee-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow">
            <i data-lucide="tag" class="w-4 h-4"></i> Jenis Biaya
        </button>
        <button onclick="switchFeeTab('biaya_tambahan')" id="feeTabBtn-biaya_tambahan" class="fee-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
            <i data-lucide="coins" class="w-4 h-4"></i> Biaya Tambahan
        </button>
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
                            <th class="py-4 px-6 text-center">Digunakan Transaksi</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($categories as $cat)
                            <tr class="hover:bg-slate-50/30 transition">
                                <td class="py-4 px-6 font-extrabold text-slate-800">{{ $cat->name }}</td>
                                <td class="py-4 px-6 text-center text-xs font-semibold {{ $cat->is_used ? 'text-slate-600 font-bold' : 'text-slate-400' }}">
                                    {{ $cat->is_used ? 'Ya (Sistem)' : 'Tidak' }}
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <button onclick="openFeeModal('jenis_biaya', '{{ $cat->name }}', '{{ $cat->is_used }}', '{{ route('admin.spmb-settings.fees.categories.update', $cat->id) }}')" class="text-xs text-brand-emerald font-bold hover:underline">Edit</button>
                                    <button onclick="deleteFeeItem('jenis_biaya', '{{ $cat->name }}', '{{ $cat->is_used }}', '{{ route('admin.spmb-settings.fees.categories.delete', $cat->id) }}')" class="text-xs text-red-600 font-bold hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 px-6 text-center text-slate-400">Belum ada data jenis biaya.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 2: Biaya Tambahan (Nominal Biaya Pendaftaran) -->
        <div id="feeTabContent-biaya_tambahan" class="fee-tab-content p-8 space-y-6 hidden">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-extrabold text-base text-slate-800">Daftar Nominal Biaya Tambahan</h3>
                    <p class="text-[11px] text-slate-400">Atur besaran nominal biaya formulir pendaftaran.</p>
                </div>
                <button onclick="openFeeModal('biaya_tambahan', '', '', '{{ route('admin.spmb-settings.fees.admin-fees.store') }}', '')" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Biaya Tambahan
                </button>
            </div>
            
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Nama Biaya</th>
                            <th class="py-4 px-6 text-center">Nominal (Rp)</th>
                            <th class="py-4 px-6 text-center">Payment Gateway</th>
                            <th class="py-4 px-6 text-center">Digunakan Transaksi</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($fees as $fee)
                            <tr class="hover:bg-slate-50/30 transition">
                                <td class="py-4 px-6 font-extrabold text-slate-800">{{ $fee->name }}</td>
                                <td class="py-4 px-6 text-center font-semibold text-slate-700">Rp {{ number_format($fee->amount, 0, ',', '.') }}</td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase {{ $fee->payment_gateway === 'bni' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ $fee->payment_gateway }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center text-xs font-semibold {{ $fee->is_used ? 'text-slate-600 font-bold' : 'text-slate-400' }}">
                                    {{ $fee->is_used ? 'Ya (Terpakai)' : 'Tidak' }}
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <button onclick="openFeeModal('biaya_tambahan', '{{ $fee->name }}', '{{ $fee->is_used }}', '{{ route('admin.spmb-settings.fees.admin-fees.update', $fee->id) }}', '{{ $fee->amount }}', '{{ $fee->payment_gateway }}')" class="text-xs text-brand-emerald font-bold hover:underline">Edit</button>
                                    <button onclick="deleteFeeItem('biaya_tambahan', '{{ $fee->name }}', '{{ $fee->is_used }}', '{{ route('admin.spmb-settings.fees.admin-fees.delete', $fee->id) }}')" class="text-xs text-red-600 font-bold hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 px-6 text-center text-slate-400">Belum ada data nominal biaya tambahan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

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
            
            @if($errors->any() && session('failed_modal'))
                <div class="text-xs text-red-600 bg-red-50 p-3.5 rounded-xl border border-red-200 font-semibold mb-3 space-y-1">
                    @foreach($errors->all() as $error)
                        <p>⚠️ {{ $error }}</p>
                    @endforeach
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
                    <input type="number" id="feeAmountInput" name="amount" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm" placeholder="Contoh: 350000">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Payment Gateway*</label>
                    <select id="feeGatewayInput" name="payment_gateway" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
                        <option value="winpay">Winpay</option>
                        <option value="bni">BNI SNAP</option>
                    </select>
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
    // Tab Switching
    function switchFeeTab(tabId) {
        document.querySelectorAll('.fee-tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('feeTabContent-' + tabId).classList.remove('hidden');

        document.querySelectorAll('.fee-tab-btn').forEach(btn => {
            btn.className = "fee-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50";
        });
        
        const activeBtn = document.getElementById('feeTabBtn-' + tabId);
        activeBtn.className = "fee-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow";
        
        localStorage.setItem('spmb_fees_active_tab', tabId);
    }

    document.addEventListener("DOMContentLoaded", function() {
        const savedTab = localStorage.getItem('spmb_fees_active_tab') || 'jenis_biaya';
        // Normalize if old memory was biaya_admin
        const activeTab = savedTab === 'biaya_admin' ? 'biaya_tambahan' : savedTab;
        switchFeeTab(activeTab);
    });

    // Modal Control
    function openFeeModal(moduleType, val = '', isLocked = false, actionUrl = '', amount = '', gateway = 'winpay') {
        const form = document.getElementById('feeCrudForm');
        form.action = actionUrl;
        
        const mainInput = document.getElementById('feeMainInput');
        mainInput.value = val;
        mainInput.disabled = (isLocked === 'true' || isLocked === true || isLocked === '1');

        const amountInput = document.getElementById('feeAmountInput');
        const gatewayInput = document.getElementById('feeGatewayInput');
        const amountWrapper = document.getElementById('feeAmountWrapper');
        
        const titleEl = document.getElementById('feeModalTitle');
        const labelEl = document.getElementById('feeInputLabel');

        if (moduleType === 'jenis_biaya') {
            titleEl.innerText = val ? 'Edit Jenis Biaya' : 'Tambah Jenis Biaya';
            labelEl.innerText = 'Nama Jenis Biaya*';
            mainInput.placeholder = 'Contoh: Uang Gedung';
            
            amountWrapper.classList.add('hidden');
            amountInput.required = false;
        } else {
            titleEl.innerText = val ? 'Edit Biaya Tambahan' : 'Tambah Biaya Tambahan';
            labelEl.innerText = 'Nama Biaya Pendaftaran*';
            mainInput.placeholder = 'Contoh: Biaya Pendaftaran TK B';
            
            amountWrapper.classList.remove('hidden');
            amountInput.value = amount;
            amountInput.required = true;
            amountInput.disabled = (isLocked === 'true' || isLocked === true || isLocked === '1');
            
            gatewayInput.value = gateway;
            gatewayInput.required = true;
            // Gateway can be changed even if locked/used based on updated controller logic
        }

        document.getElementById('feeCrudModal').classList.remove('hidden');
    }

    // Close Modal
    function closeFeeModal() {
        document.getElementById('feeCrudModal').classList.add('hidden');
    }

    // Delete Operations
    function deleteFeeItem(type, name, isUsed, deleteUrl) {
        let label = (type === 'jenis_biaya') ? 'Jenis Biaya' : 'Biaya Tambahan';
        if (isUsed === 'true' || isUsed === true || isUsed === '1') {
            showToast(`Peringatan: Tidak dapat menghapus ${label} "${name}" karena sudah terpakai dalam data transaksi pembayaran aktif!`, 'error');
        } else {
            if (confirm(`Apakah Anda yakin ingin menghapus ${label} "${name}"?`)) {
                const deleteForm = document.getElementById('feeDeleteForm');
                deleteForm.action = deleteUrl;
                deleteForm.submit();
            }
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
                switchFeeTab('biaya_tambahan');
                openFeeModal('biaya_tambahan', '{{ old('name') }}', false, '{{ route('admin.spmb-settings.fees.admin-fees.store') }}', '{{ old('amount') }}', '{{ old('payment_gateway') }}');
            } else if (failed.startsWith('biaya_admin_edit_')) {
                switchFeeTab('biaya_tambahan');
                let id = failed.replace('biaya_admin_edit_', '');
                openFeeModal('biaya_tambahan', '{{ old('name') }}', false, '/admin/spmb-settings/fees/admin-fees/' + id, '{{ old('amount') }}', '{{ old('payment_gateway') }}');
            }
        });
    @endif
</script>
@endsection
