@extends('layouts.admin')

@section('title', 'CRUD Payment Gateway - Portal SPMB')
@section('page_title', 'Payment Gateways')

@section('content')
<div id="gateway-settings-container" class="space-y-8">
    
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-extrabold leading-7 text-slate-900 sm:text-3xl sm:truncate dark:text-white">
                Kelola Payment Gateways
            </h2>
            <p class="text-xs text-brand-emerald font-semibold uppercase tracking-wider mt-1">
                Sekolah Anak Saleh • Integrasi Pembayaran
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 gap-3">
            <button onclick="openCreateModal()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-xl shadow-sm text-xs font-bold text-white bg-brand-emerald hover-emerald transition">
                <i data-lucide="plus-circle" class="w-4 h-4 mr-1.5 text-brand-yellow"></i> Tambah Gateway Baru
            </button>
        </div>
    </div>



    <!-- Table List -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-md border border-slate-100 dark:border-slate-800 overflow-hidden">
        <div class="bg-slate-50 dark:bg-slate-950 border-b border-slate-100 dark:border-slate-800 px-6 py-4">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Daftar Gateway Pembayaran Terdaftar</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50 dark:bg-slate-950/20">
                        <th class="py-4 px-6">Nama Gateway</th>
                        <th class="py-4 px-6">Kode Unik</th>
                        <th class="py-4 px-6">Parameter Keys</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($gateways as $gateway)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-850/50 transition">
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800 dark:text-slate-200">{{ $gateway->name }}</div>
                            </td>
                            <td class="py-4 px-6 font-mono text-xs text-brand-emerald dark:text-emerald-400">
                                {{ $gateway->code }}
                            </td>
                            <td class="py-4 px-6 space-y-1">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($gateway->settings_schema as $field)
                                        <span class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 px-2 py-0.5 rounded text-[9px] font-semibold font-mono">
                                            {{ $field['key'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                    @if($gateway->is_active) bg-green-50 text-green-700 border border-green-200 @else bg-slate-100 text-slate-500 @endif">
                                    {{ $gateway->is_active ? 'AKTIF' : 'NON-AKTIF' }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex justify-end items-center gap-1.5">
                                    <!-- Config link -->
                                    @if($gateway->is_active)
                                        <a href="{{ route('admin.payment-gateways.settings', $gateway->code) }}" class="bg-brand-emerald hover-emerald text-white px-2.5 py-1.5 rounded-lg text-[10px] font-bold shadow-sm transition flex items-center gap-1">
                                            <i data-lucide="settings" class="w-3 h-3 text-brand-yellow"></i> Konfigurasi
                                        </a>
                                    @else
                                        <button type="button" disabled class="bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 px-2.5 py-1.5 rounded-lg text-[10px] font-bold cursor-not-allowed flex items-center gap-1 border border-slate-200 dark:border-slate-700" title="Aktifkan gateway terlebih dahulu untuk melakukan konfigurasi">
                                            <i data-lucide="settings" class="w-3 h-3 text-slate-400 dark:text-slate-600"></i> Konfigurasi
                                        </button>
                                    @endif

                                    <!-- Edit Trigger -->
                                    <button onclick="openEditModal({{ json_encode($gateway) }})" class="bg-blue-600 hover:bg-blue-700 text-white px-2.5 py-1.5 rounded-lg text-[10px] font-bold shadow-sm transition">
                                        Edit
                                    </button>

                                    <!-- Delete Trigger -->
                                    @if(!in_array($gateway->code, ['winpay', 'bni']))
                                        <button type="button" onclick="confirmDelete('{{ route('admin.payment-gateways.destroy', $gateway->id) }}', 'Apakah Anda yakin ingin menghapus gateway ini?')" class="bg-rose-600 hover:bg-rose-700 text-white px-2.5 py-1.5 rounded-lg text-[10px] font-bold shadow-sm transition">
                                            Hapus
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 px-6 text-center text-slate-400">
                                Belum ada payment gateway tambahan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($gateways->hasPages())
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-950 border-t border-slate-100 dark:border-slate-800">
                {{ $gateways->links() }}
            </div>
        @endif
    </div>

    <!-- Info Box Schema Guide -->
    <div class="bg-sky-50 dark:bg-slate-950 border border-sky-100 dark:border-sky-900/50 rounded-2xl p-6 text-xs text-slate-650 dark:text-slate-400 space-y-2">
        <h4 class="font-extrabold text-sky-850 dark:text-sky-400 flex items-center gap-1.5">
            <i data-lucide="info" class="w-4 h-4"></i> Panduan Skema Parameter
        </h4>
        <p class="leading-relaxed">
            Format Skema Parameter menggunakan raw JSON Array. Parameter yang didefinisikan di sini akan secara otomatis di-render sebagai kolom input konfigurasi bagi masing-masing lingkungan (Simulator, Sandbox, Production) di halaman detail konfigurasi gateway terkait.
        </p>
        <p class="font-bold text-slate-700 dark:text-slate-350">Contoh format JSON Skema:</p>
        <pre class="bg-white dark:bg-slate-950 p-4 border border-slate-200 dark:border-slate-800 rounded-xl overflow-x-auto font-mono text-[10px] text-slate-700 dark:text-slate-300">
[
  {"key": "merchant_id", "label": "Merchant ID", "type": "text"},
  {"key": "client_key", "label": "Client Key", "type": "text"},
  {"key": "private_key", "label": "Private Key (RSA)", "type": "textarea"},
  {"key": "public_key", "label": "Public Key (RSA)", "type": "textarea"}
]</pre>
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
</div>

<!-- Modal Create -->
<div id="createModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/40 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden text-left transform scale-95 transition-all duration-100" id="createModalBody">
        <div class="bg-brand-emerald text-white px-6 py-4">
            <h3 class="font-extrabold text-lg">Tambah Payment Gateway</h3>
            <p class="text-xs text-emerald-100 mt-0.5">Daftarkan jenis/channel gateway pembayaran baru.</p>
        </div>
        <form action="{{ route('admin.payment-gateways.store') }}" method="POST" hx-boost="false" class="p-6 space-y-4">
            @csrf
            @if($errors->any() && session('failed_modal') && session('failed_modal') === 'gateway_create')
                <div class="spmb-gateway-errors mx-6 mt-4 text-xs text-red-650 bg-red-50 p-3.5 rounded-xl border border-red-200 font-semibold space-y-1">
                    @foreach($errors->all() as $error)
                        <p>⚠️ {{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <div>
                <label for="create_name" class="block text-xs font-bold text-slate-650 dark:text-slate-350 uppercase tracking-wider mb-1.5">Nama Gateway*</label>
                <input type="text" id="create_name" name="name" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm" placeholder="Contoh: DOKU Checkout, Midtrans SNAP">
            </div>
            <div>
                <label for="create_code" class="block text-xs font-bold text-slate-650 dark:text-slate-350 uppercase tracking-wider mb-1.5">Kode Unik*</label>
                <input type="text" id="create_code" name="code" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-mono" placeholder="doku (hanya huruf kecil/angka)">
            </div>
            <div>
                <label class="flex items-center gap-2 cursor-pointer mt-2 select-none">
                    <input type="checkbox" name="is_active" value="1" checked class="text-brand-emerald focus:ring-brand-emerald rounded border-slate-300 dark:border-slate-800">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase">Aktifkan Gateway</span>
                </label>
            </div>
            <div>
                <label for="create_schema" class="block text-xs font-bold text-slate-650 dark:text-slate-350 uppercase tracking-wider mb-1.5 font-sans">JSON Skema Parameter*</label>
                <textarea id="create_schema" name="settings_schema_raw" required rows="6"
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono"
                    placeholder='[&#10;  {"key": "merchant_id", "label": "Merchant ID", "type": "text"}&#10;]'></textarea>
                @error('settings_schema_raw')
                    <span class="text-xs text-red-500 font-semibold block mt-1">{{ $message }}</span>
                @enderror
            </div>
            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeCreateModal()" class="border border-slate-300 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 px-4 py-2 rounded-xl text-xs font-bold transition">
                    Batal
                </button>
                <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2 rounded-xl text-xs font-bold transition shadow-md">
                    Simpan Gateway
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/40 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden text-left transform scale-95 transition-all duration-100" id="editModalBody">
        <div class="bg-blue-600 text-white px-6 py-4">
            <h3 class="font-extrabold text-lg">Edit Payment Gateway</h3>
            <p class="text-xs text-blue-100 mt-0.5">Ubah konfigurasi detail pendaftaran gateway pembayaran.</p>
        </div>
        <form id="editForm" method="POST" hx-boost="false" class="p-6 space-y-4">
            @csrf
            @if($errors->any() && session('failed_modal') && str_starts_with(session('failed_modal'), 'gateway_edit_'))
                <div class="spmb-gateway-errors mx-6 mt-4 text-xs text-red-655 bg-red-50 p-3.5 rounded-xl border border-red-200 font-semibold space-y-1">
                    @foreach($errors->all() as $error)
                        <p>⚠️ {{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <div>
                <label for="edit_name" class="block text-xs font-bold text-slate-650 dark:text-slate-350 uppercase tracking-wider mb-1.5">Nama Gateway*</label>
                <input type="text" id="edit_name" name="name" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label for="edit_code" class="block text-xs font-bold text-slate-650 dark:text-slate-350 uppercase tracking-wider mb-1.5">Kode Unik*</label>
                <input type="text" id="edit_code" name="code" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-mono">
            </div>
            <div>
                <label class="flex items-center gap-2 cursor-pointer mt-2 select-none">
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1" class="text-blue-600 focus:ring-blue-500 rounded border-slate-300 dark:border-slate-800">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase">Aktifkan Gateway</span>
                </label>
            </div>
            <div>
                <label for="edit_schema" class="block text-xs font-bold text-slate-650 dark:text-slate-350 uppercase tracking-wider mb-1.5 font-sans">JSON Skema Parameter*</label>
                <textarea id="edit_schema" name="settings_schema_raw" required rows="6"
                    class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs font-mono"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeEditModal()" class="border border-slate-300 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 px-4 py-2 rounded-xl text-xs font-bold transition">
                    Batal
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-xs font-bold transition shadow-md">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Clear validation errors
    function clearGatewayErrors() {
        document.querySelectorAll('.spmb-gateway-errors').forEach(el => {
            el.classList.add('hidden');
        });
    }

    function openCreateModal() {
        clearGatewayErrors();
        document.getElementById('createModal').classList.remove('hidden');
        setTimeout(() => {
            const body = document.getElementById('createModalBody');
            if (body) body.classList.replace('scale-95', 'scale-100');
        }, 10);
    }
    function closeCreateModal() {
        clearGatewayErrors();
        const body = document.getElementById('createModalBody');
        if (body) body.classList.replace('scale-100', 'scale-95');
        setTimeout(() => {
            document.getElementById('createModal').classList.add('hidden');
        }, 100);
    }

    function openEditModal(gateway) {
        clearGatewayErrors();
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editForm');
        
        document.getElementById('edit_name').value = gateway.name;
        document.getElementById('edit_code').value = gateway.code;
        document.getElementById('edit_is_active').checked = gateway.is_active;
        document.getElementById('edit_schema').value = typeof gateway.settings_schema === 'string' 
            ? gateway.settings_schema 
            : JSON.stringify(gateway.settings_schema, null, 2);
        
        form.setAttribute('action', `/admin/payment-gateways/${gateway.id}/update`);
        modal.classList.remove('hidden');
        setTimeout(() => {
            const body = document.getElementById('editModalBody');
            if (body) body.classList.replace('scale-95', 'scale-100');
        }, 10);
    }
    
    function closeEditModal() {
        clearGatewayErrors();
        const body = document.getElementById('editModalBody');
        if (body) body.classList.replace('scale-100', 'scale-95');
        setTimeout(() => {
            document.getElementById('editModal').classList.add('hidden');
        }, 100);
    }

    // Click outside handlers to close modals
    document.getElementById('createModal').addEventListener('click', function(e) {
        if (e.target === this) closeCreateModal();
    });
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });

    // Escape key listener to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const createModal = document.getElementById('createModal');
            if (createModal && !createModal.classList.contains('hidden')) closeCreateModal();
            
            const editModal = document.getElementById('editModal');
            if (editModal && !editModal.classList.contains('hidden')) closeEditModal();
        }
    });

    // Auto-reopen modal if validation failed on redirect
    @if(session('failed_modal'))
        document.addEventListener("DOMContentLoaded", function() {
            let failed = "{{ session('failed_modal') }}";
            if (failed.startsWith('gateway_create')) {
                openCreateModal();
                // Repopulate form inputs with old values
                document.getElementById('create_name').value = "{{ old('name') }}";
                document.getElementById('create_code').value = "{{ old('code') }}";
                document.getElementById('create_schema').value = `{!! old('settings_schema_raw') !!}`;
            } else if (failed.startsWith('gateway_edit_')) {
                let id = failed.replace('gateway_edit_', '');
                openEditModal({
                    id: id,
                    name: "{{ old('name') }}",
                    code: "{{ old('code') }}",
                    is_active: {{ old('is_active') ? 'true' : 'false' }},
                    settings_schema: `{!! old('settings_schema_raw') !!}`
                });
            }

            // Unhide the failed errors block in the reopened modal
            document.querySelectorAll('.spmb-gateway-errors').forEach(el => {
                el.classList.remove('hidden');
            });
        });
    @endif
</script>
@endsection
