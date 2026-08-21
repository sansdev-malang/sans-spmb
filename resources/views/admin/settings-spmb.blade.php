@extends('layouts.admin')

@section('title', 'Setting SPMB - Admin Panel')
@section('page_title', 'Setting SPMB')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h1 class="text-xl font-extrabold text-slate-800">Setting Master Penerimaan (SPMB)</h1>
        <p class="text-xs text-slate-500 mt-1">Kelola data periode akademik, gelombang masuk, kategori jenis pendaftaran, dan download QR Code pendaftaran.</p>
    </div>



    <!-- Tab Navigation Pills -->
    <div class="flex flex-wrap gap-2 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
        <button onclick="switchTab('periode')" id="tabBtn-periode" class="tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow">
            <i data-lucide="calendar" class="w-4 h-4"></i> Periode
        </button>
        <button onclick="switchTab('gelombang')" id="tabBtn-gelombang" class="tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
            <i data-lucide="waves" class="w-4 h-4"></i> Gelombang
        </button>
        <button onclick="switchTab('jenis')" id="tabBtn-jenis" class="tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
            <i data-lucide="tag" class="w-4 h-4"></i> Jenis Pendaftaran
        </button>

        <button onclick="switchTab('qrcode')" id="tabBtn-qrcode" class="tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
            <i data-lucide="qr-code" class="w-4 h-4"></i> QR Code SPMB
        </button>
    </div>

    <!-- Tab Contents -->
    <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        
        <!-- Tab 1: Periode -->
        <div id="tabContent-periode" class="tab-content p-8 space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-extrabold text-base text-slate-800">Tahun Pelajaran (Periode Akademik)</h3>
                    <p class="text-[11px] text-slate-400">Atur periode ajaran baru yang sedang dibuka.</p>
                </div>
                <button onclick="openModal('periode', '', '', '{{ route('admin.spmb-settings.periods.store') }}')" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Periode
                </button>
            </div>
            
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Tahun Pelajaran (Periode)</th>
                            <th class="py-4 px-6 text-center">Digunakan Transaksi</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($periods as $period)
                            <tr class="hover:bg-slate-50/30 transition">
                                <td class="py-4 px-6 font-extrabold text-slate-800">{{ $period->year }}</td>
                                <td class="py-4 px-6 text-center text-xs font-semibold {{ $period->registrations_count > 0 ? 'text-slate-600 font-bold' : 'text-slate-400' }}">
                                    {{ $period->registrations_count > 0 ? 'Ya (' . $period->registrations_count . ' Siswa)' : 'Tidak' }}
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <button onclick="openModal('periode', '{{ $period->year }}', '{{ $period->registrations_count > 0 }}', '{{ route('admin.spmb-settings.periods.update', $period->id) }}')" class="text-xs text-brand-emerald font-bold hover:underline">Edit</button>
                                    <button onclick="deleteItem('periode', '{{ $period->year }}', '{{ $period->registrations_count > 0 }}', '{{ route('admin.spmb-settings.periods.delete', $period->id) }}')" class="text-xs text-red-600 font-bold hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 px-6 text-center text-slate-400">Belum ada data periode akademik.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 2: Gelombang -->
        <div id="tabContent-gelombang" class="tab-content p-8 space-y-6 hidden">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-extrabold text-base text-slate-800">Gelombang Masuk Pendaftaran</h3>
                    <p class="text-[11px] text-slate-400">Kelola kuota gelombang penerimaan baru (Biaya disetel pada menu Keuangan).</p>
                </div>
                <button onclick="openModal('gelombang', '', '', '{{ route('admin.spmb-settings.waves.store') }}')" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Gelombang
                </button>
            </div>
            
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Nama Gelombang</th>
                            <th class="py-4 px-6 text-center">Digunakan Transaksi</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($waves as $wave)
                            <tr class="hover:bg-slate-50/30 transition">
                                <td class="py-4 px-6 font-extrabold text-slate-800">{{ $wave->name }}</td>
                                <td class="py-4 px-6 text-center text-xs font-semibold {{ $wave->registrations_count > 0 ? 'text-slate-600 font-bold' : 'text-slate-400' }}">
                                    {{ $wave->registrations_count > 0 ? 'Ya (' . $wave->registrations_count . ' Siswa)' : 'Tidak' }}
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <button onclick="openModal('gelombang', '{{ $wave->name }}', '{{ $wave->registrations_count > 0 }}', '{{ route('admin.spmb-settings.waves.update', $wave->id) }}')" class="text-xs text-brand-emerald font-bold hover:underline">Edit</button>
                                    <button onclick="deleteItem('gelombang', '{{ $wave->name }}', '{{ $wave->registrations_count > 0 }}', '{{ route('admin.spmb-settings.waves.delete', $wave->id) }}')" class="text-xs text-red-600 font-bold hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 px-6 text-center text-slate-400">Belum ada data gelombang pendaftaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 3: Jenis Pendaftaran -->
        <div id="tabContent-jenis" class="tab-content p-8 space-y-6 hidden">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-extrabold text-base text-slate-800">Kategori Jenis Pendaftaran</h3>
                    <p class="text-[11px] text-slate-400">Kelola jenis penerimaan (contoh: Siswa Baru, Pindahan Mutasi, dll).</p>
                </div>
                <button onclick="openModal('jenis', '', '', '{{ route('admin.spmb-settings.types.store') }}')" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Kategori
                </button>
            </div>
            
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Kategori Jenis</th>
                            <th class="py-4 px-6 text-center">Digunakan Transaksi</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($types as $type)
                            <tr class="hover:bg-slate-50/30 transition">
                                <td class="py-4 px-6 font-extrabold text-slate-800">{{ $type->name }}</td>
                                <td class="py-4 px-6 text-center text-xs font-semibold {{ $type->registrations_count > 0 ? 'text-slate-600 font-bold' : 'text-slate-400' }}">
                                    {{ $type->registrations_count > 0 ? 'Ya (' . $type->registrations_count . ' Siswa)' : 'Tidak' }}
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <button onclick="openModal('jenis', '{{ $type->name }}', '{{ $type->registrations_count > 0 }}', '{{ route('admin.spmb-settings.types.update', $type->id) }}')" class="text-xs text-brand-emerald font-bold hover:underline">Edit</button>
                                    <button onclick="deleteItem('jenis', '{{ $type->name }}', '{{ $type->registrations_count > 0 }}', '{{ route('admin.spmb-settings.types.delete', $type->id) }}')" class="text-xs text-red-600 font-bold hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-8 px-6 text-center text-slate-400">Belum ada data jenis pendaftaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 4: QR Code SPMB -->
        <div id="tabContent-qrcode" class="tab-content p-8 space-y-6 hidden">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Left configuration form -->
                <div class="md:col-span-2 space-y-4">
                    <div>
                        <h3 class="font-extrabold text-base text-slate-800">Tautan QR Code Pendaftaran</h3>
                        <p class="text-[11px] text-slate-400">Masukkan tautan landing page registrasi Anda untuk merubah hasil kode QR secara otomatis.</p>
                    </div>
                    
                    <form onsubmit="handleQrUpdate(event)" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">URL Tujuan Pendaftaran*</label>
                            <input type="url" id="qrcodeUrlInput" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm"
                                value="https://sans-spmb.test/register">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                                Perbarui QR Code
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right QR visual and copy links -->
                <div class="border border-slate-100 rounded-2xl p-6 flex flex-col items-center justify-center text-center gap-4 bg-slate-50/50">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hasil Gambar QR</span>
                    
                    <div class="bg-white p-3 border border-slate-200 rounded-2xl shadow-sm">
                        <img id="qrCodeImage" src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://sans-spmb.test/register" alt="SANS SPMB QR Code" class="h-36 w-36">
                    </div>

                    <!-- Display URL under QR Code to be copied -->
                    <div class="w-full space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Salin Tautan</label>
                        <div class="flex items-center border border-slate-300 rounded-xl overflow-hidden bg-white">
                            <input type="text" readonly id="displayUrlInput" value="https://sans-spmb.test/register" class="w-full border-none bg-transparent px-3 py-2 text-[11px] font-mono text-slate-600 focus:ring-0 focus:outline-none">
                            <button onclick="copyToClipboard()" type="button" class="bg-slate-100 hover:bg-slate-200 px-3 py-2 text-[10px] font-bold text-slate-700 transition border-l border-slate-300">
                                Salin
                            </button>
                        </div>
                    </div>

                    <a id="downloadQrButton" href="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=https://sans-spmb.test/register" target="_blank" download="sans-spmb-qrcode.png"
                        class="w-full bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm text-center">
                        📥 Unduh Gambar QR
                    </a>
                </div>

            </div>
        </div>
    </div>


</div>

<!-- Unified CRUD Modal -->
<div id="crudModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center">
    <div class="bg-white rounded-2xl max-w-md w-full mx-4 shadow-2xl border border-slate-100 overflow-hidden">
        <div class="bg-brand-emerald text-white px-6 py-4">
            <h3 id="crudModalTitle" class="font-extrabold text-lg">Tambah</h3>
            <p class="text-xs text-emerald-100 mt-0.5">Kelola data konfigurasi setting master.</p>
        </div>
        <form id="crudForm" method="POST" class="p-6 space-y-4">
            @csrf
            
            @if($errors->any() && session('failed_modal'))
                <div class="text-xs text-red-600 bg-red-50 p-3.5 rounded-xl border border-red-200 font-semibold mb-3 space-y-1">
                    @foreach($errors->all() as $error)
                        <p>⚠️ {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div>
                <label id="crudInputLabel" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Input</label>
                <input type="text" id="crudMainInput" name="value" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
            </div>
            
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeModal()" class="border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition">
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
<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    // Tab Switching
    function switchTab(tabId) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        // Show selected tab
        document.getElementById('tabContent-' + tabId).classList.remove('hidden');

        // Style tabs
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.className = "tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50";
        });
        
        const activeBtn = document.getElementById('tabBtn-' + tabId);
        activeBtn.className = "tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow";
        
        // Save to localStorage
        localStorage.setItem('spmb_active_tab', tabId);
    }

    // On Load Restore Active Tab
    document.addEventListener("DOMContentLoaded", function() {
        const savedTab = localStorage.getItem('spmb_active_tab') || 'periode';
        switchTab(savedTab);
    });

    // Modal Control
    function openModal(moduleType, val = '', isLocked = false, actionUrl = '') {
        const form = document.getElementById('crudForm');
        form.action = actionUrl;
        
        const mainInput = document.getElementById('crudMainInput');
        mainInput.value = val;
        mainInput.disabled = isLocked; // lock key items from renaming if used in db

        const titleEl = document.getElementById('crudModalTitle');
        const labelEl = document.getElementById('crudInputLabel');

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
    }

    // Delete Operations
    function deleteItem(type, name, isUsed, deleteUrl) {
        let label = (type === 'periode') ? 'Periode' : (type === 'gelombang' ? 'Gelombang' : 'Jenis Pendaftaran');
        if (isUsed === 'true' || isUsed === true || isUsed > 0) {
            showToast(`Peringatan: Tidak dapat menghapus ${label} "${name}" karena data sudah dipakai dalam transaksi aktif! Anda hanya dapat mengubah datanya.`, 'error');
        } else {
            if (confirm(`Apakah Anda yakin ingin menghapus ${label} "${name}"?`)) {
                const deleteForm = document.getElementById('deleteForm');
                deleteForm.action = deleteUrl;
                deleteForm.submit();
            }
        }
    }

    // QR Code actions (keeps visual UI helper)
    function handleQrUpdate(e) {
        e.preventDefault();
        const url = document.getElementById('qrcodeUrlInput').value;
        const encoded = encodeURIComponent(url);

        document.getElementById('qrCodeImage').src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encoded}`;
        document.getElementById('displayUrlInput').value = url;
        document.getElementById('downloadQrButton').href = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encoded}`;
    }

    function copyToClipboard() {
        const input = document.getElementById('displayUrlInput');
        input.select();
        input.setSelectionRange(0, 99999); // for mobile
        navigator.clipboard.writeText(input.value);
        showToast('Tautan disalin ke papan klip!', 'success');
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
                openModal('gelombang', '{{ old('name') }}', false, '{{ route('admin.spmb-settings.waves.store') }}');
            } else if (failed.startsWith('gelombang_edit_')) {
                switchTab('gelombang');
                let id = failed.replace('gelombang_edit_', '');
                openModal('gelombang', '{{ old('name') }}', false, '/admin/spmb-settings/waves/' + id);
            } else if (failed.startsWith('jenis_create')) {
                switchTab('jenis');
                openModal('jenis', '{{ old('name') }}', false, '{{ route('admin.spmb-settings.types.store') }}');
            } else if (failed.startsWith('jenis_edit_')) {
                switchTab('jenis');
                let id = failed.replace('jenis_edit_', '');
                openModal('jenis', '{{ old('name') }}', false, '/admin/spmb-settings/types/' + id);
            }
        });
    @endif
</script>
@endsection
