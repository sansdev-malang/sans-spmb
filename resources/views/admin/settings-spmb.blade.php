@extends('layouts.admin')

@section('title', 'Jalur & Gelombang - Admin Panel')
@section('page_title', 'Jalur & Gelombang')

@section('content')
<div class="w-full space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h1 class="text-xl font-extrabold text-slate-800">Master Jalur & Gelombang</h1>
        <p class="text-xs text-slate-500 mt-1">Kelola data periode akademik, gelombang masuk, kategori jenis pendaftaran, dan program kelas.</p>
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
        <button onclick="switchTab('program')" id="tabBtn-program" class="tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
            <i data-lucide="layers" class="w-4 h-4"></i> Program Kelas
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
                                <td class="py-4 px-6">
                                    <div class="font-extrabold text-slate-800">{{ $wave->name }}</div>
                                    @if($wave->description)
                                        <div class="text-[11px] text-slate-400 font-medium mt-0.5">{{ $wave->description }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center text-xs font-semibold {{ $wave->registrations_count > 0 ? 'text-slate-600 font-bold' : 'text-slate-400' }}">
                                    {{ $wave->registrations_count > 0 ? 'Ya (' . $wave->registrations_count . ' Siswa)' : 'Tidak' }}
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <button onclick="openModal('gelombang', '{{ addslashes($wave->name) }}', '{{ $wave->registrations_count > 0 }}', '{{ route('admin.spmb-settings.waves.update', $wave->id) }}', '1', '{{ addslashes($wave->description) }}')" class="text-xs text-brand-emerald font-bold hover:underline">Edit</button>
                                    <button onclick="deleteItem('gelombang', '{{ addslashes($wave->name) }}', '{{ $wave->registrations_count > 0 }}', '{{ route('admin.spmb-settings.waves.delete', $wave->id) }}')" class="text-xs text-red-600 font-bold hover:underline">Hapus</button>
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
                                <td class="py-4 px-6">
                                    <div class="font-extrabold text-slate-800">{{ $type->name }}</div>
                                    @if($type->description)
                                        <div class="text-[11px] text-slate-400 font-medium mt-0.5">{{ $type->description }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center text-xs font-semibold {{ $type->registrations_count > 0 ? 'text-slate-600 font-bold' : 'text-slate-400' }}">
                                    {{ $type->registrations_count > 0 ? 'Ya (' . $type->registrations_count . ' Siswa)' : 'Tidak' }}
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <button onclick="openModal('jenis', '{{ addslashes($type->name) }}', '{{ $type->registrations_count > 0 }}', '{{ route('admin.spmb-settings.types.update', $type->id) }}', '1', '{{ addslashes($type->description) }}')" class="text-xs text-brand-emerald font-bold hover:underline">Edit</button>
                                    <button onclick="deleteItem('jenis', '{{ addslashes($type->name) }}', '{{ $type->registrations_count > 0 }}', '{{ route('admin.spmb-settings.types.delete', $type->id) }}')" class="text-xs text-red-600 font-bold hover:underline">Hapus</button>
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

        <!-- Tab: Program Kelas -->
        <div id="tabContent-program" class="tab-content p-8 space-y-6 hidden">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-extrabold text-base text-slate-800">Program Kelas (Kategori Siswa)</h3>
                    <p class="text-[11px] text-slate-400">Kelola program/kategori penerimaan (contoh: Reguler, Inklusi).</p>
                </div>
                <button onclick="openModal('program', '', '', '{{ route('admin.spmb-settings.class-programs.store') }}')" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Program
                </button>
            </div>
            
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Nama Program</th>
                            <th class="py-4 px-6 text-center">Status</th>
                            <th class="py-4 px-6 text-center">Digunakan Transaksi</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($classPrograms as $program)
                            <tr class="hover:bg-slate-50/30 transition">
                                <td class="py-4 px-6">
                                    <div class="font-extrabold text-slate-800">{{ $program->name }}</div>
                                    @if($program->description)
                                        <div class="text-[11px] text-slate-400 font-medium mt-0.5">{{ $program->description }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if($program->is_active)
                                        <span class="bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded-lg text-[10px] font-bold">Aktif</span>
                                    @else
                                        <span class="bg-slate-100 text-slate-500 border border-slate-200 px-2 py-0.5 rounded-lg text-[10px] font-bold">Non-aktif</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center text-xs font-semibold {{ $program->registrations_count > 0 ? 'text-slate-600 font-bold' : 'text-slate-400' }}">
                                    {{ $program->registrations_count > 0 ? 'Ya (' . $program->registrations_count . ' Siswa)' : 'Tidak' }}
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <button onclick="openModal('program', '{{ addslashes($program->name) }}', '{{ $program->registrations_count > 0 }}', '{{ route('admin.spmb-settings.class-programs.update', $program->id) }}', '{{ $program->is_active }}', '{{ addslashes($program->description) }}')" class="text-xs text-brand-emerald font-bold hover:underline">Edit</button>
                                    <button onclick="deleteItem('program', '{{ addslashes($program->name) }}', '{{ $program->registrations_count > 0 }}', '{{ route('admin.spmb-settings.class-programs.delete', $program->id) }}')" class="text-xs text-red-600 font-bold hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 px-6 text-center text-slate-400">Belum ada data program kelas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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

            <div id="descriptionInputBox">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Deskripsi / Keterangan</label>
                <textarea id="crudDescriptionInput" name="description" rows="3" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm" placeholder="Masukkan deskripsi singkat (opsional)..."></textarea>
            </div>
            
            <div id="statusToggleBox" class="hidden flex items-center gap-2 pt-2">
                <input type="checkbox" id="crudStatusInput" name="is_active" value="1" class="rounded border-slate-300 text-brand-emerald focus:ring-brand-emerald">
                <label for="crudStatusInput" class="text-xs font-semibold text-slate-700">Aktifkan Program Kelas</label>
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
    function openModal(moduleType, val = '', isLocked = false, actionUrl = '', isActive = '1', desc = '') {
        const form = document.getElementById('crudForm');
        form.action = actionUrl;
        
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
            titleEl.innerText = val ? 'Edit Program Kelas' : 'Tambah Program Kelas';
            labelEl.innerText = 'Nama Program Kelas*';
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
    }

    // Delete Operations
    function deleteItem(type, name, isUsed, deleteUrl) {
        let label = (type === 'periode') ? 'Periode' : (type === 'gelombang' ? 'Gelombang' : (type === 'program' ? 'Program Kelas' : 'Jenis Pendaftaran'));
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
        });
    @endif
</script>
@endsection
