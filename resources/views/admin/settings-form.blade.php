@extends('layouts.admin')

@section('title', 'Setting Formulir - Admin Panel')
@section('page_title', 'Setting Formulir')

@section('content')
<div class="w-full space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                <i data-lucide="settings-2" class="w-5 h-5 text-brand-emerald"></i>
                Pengaturan Tahapan & Kolom Formulir (Form Settings)
            </h1>
            <p class="text-xs text-slate-500 mt-1">Kelola tahapan wizard pendaftaran calon siswa beserta pertanyaan kolom input secara dinamis.</p>
        </div>
    </div>

    <!-- Tab Navigation Pills -->
    <div class="flex flex-wrap gap-2 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
        <button onclick="switchFormTab('crud_steps')" id="formTabBtn-crud_steps" class="form-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow">
            <i data-lucide="list-ordered" class="w-4 h-4"></i> Manajemen Tahapan (Steps)
        </button>
        @foreach($steps as $step)
            <button onclick="switchFormTab('step_{{ $step->id }}')" id="formTabBtn-step_{{ $step->id }}" class="form-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
                <i data-lucide="folder" class="w-4 h-4"></i> {{ $step->title }}
            </button>
        @endforeach
    </div>

    <!-- Tab Contents Container -->
    <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        
        <!-- Tab 1: CRUD Steps -->
        <div id="formTabContent-crud_steps" class="form-tab-content p-8 space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-extrabold text-base text-slate-800">Daftar Langkah Formulir</h3>
                    <p class="text-[11px] text-slate-400">Urutkan dan kelola nama kelompok tahapan formulir pendaftaran.</p>
                </div>
                <button onclick="openAddStepModal()" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Langkah
                </button>
            </div>
            
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Urutan</th>
                            <th class="py-4 px-6">Nama Tahapan</th>
                            <th class="py-4 px-6 text-center">Jumlah Kolom</th>
                            <th class="py-4 px-6 text-center">Status Keaktifan</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($steps as $step)
                            <tr class="hover:bg-slate-50/30 transition">
                                <td class="py-4 px-6 font-bold text-slate-700">Langkah #{{ $step->order }}</td>
                                <td class="py-4 px-6 font-extrabold text-slate-800">{{ $step->title }}</td>
                                <td class="py-4 px-6 text-center font-semibold text-slate-600">{{ $step->fields->count() }} Input</td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $step->is_active ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-slate-100 text-slate-400' }}">
                                        {{ $step->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <button onclick="openEditStepModal({{ json_encode($step) }})" class="text-xs text-brand-emerald font-bold hover:underline">Edit</button>
                                    <button onclick="deleteStepItem('{{ $step->title }}', '{{ route('admin.spmb-settings.form.steps.delete', $step->id) }}')" class="text-xs text-red-600 font-bold hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 px-6 text-center text-slate-400">Belum ada langkah formulir dikonfigurasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 2 to N: Fields for each Step -->
        @foreach($steps as $step)
            <div id="formTabContent-step_{{ $step->id }}" class="form-tab-content p-8 space-y-6 hidden">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-extrabold text-base text-slate-800">Daftar Pertanyaan: {{ $step->title }}</h3>
                        <p class="text-[11px] text-slate-400">Kelola isian kolom formulir di tahapan ini.</p>
                    </div>
                    <button onclick="openAddFieldModal({{ $step->id }})" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Kolom Input
                    </button>
                </div>
                
                <div class="overflow-x-auto border border-slate-100 rounded-xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                                <th class="py-4 px-6">Urutan</th>
                                <th class="py-4 px-6">Label Kolom</th>
                                <th class="py-4 px-6">Key Database</th>
                                <th class="py-4 px-6">Tipe Form</th>
                                <th class="py-4 px-6 text-center">Wajib Diisi</th>
                                <th class="py-4 px-6">Pilihan Pilihan (Dropdown)</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            @forelse($step->fields as $field)
                                <tr class="hover:bg-slate-50/30 transition">
                                    <td class="py-4 px-6 font-bold text-slate-700">#{{ $field->order }}</td>
                                    <td class="py-4 px-6 font-extrabold text-slate-800">{{ $field->label }}</td>
                                    <td class="py-4 px-6 font-mono text-xs text-slate-500">{{ $field->field_name }}</td>
                                    <td class="py-4 px-6 font-semibold text-brand-emerald text-xs uppercase">{{ $field->type }}</td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase {{ $field->is_required ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $field->is_required ? 'Wajib' : 'Opsional' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-xs text-slate-500 max-w-xs truncate">
                                        {{ $field->options ?? '-' }}
                                    </td>
                                    <td class="py-4 px-6 text-right space-x-2">
                                        <button onclick="openEditFieldModal({{ json_encode($field) }})" class="text-xs text-brand-emerald font-bold hover:underline">Edit</button>
                                        <button onclick="deleteFieldItem('{{ $field->label }}', '{{ route('admin.spmb-settings.form.fields.delete', $field->id) }}')" class="text-xs text-red-600 font-bold hover:underline">Hapus</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 px-6 text-center text-slate-400">Belum ada kolom input formulir di tahapan ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

    </div>
</div>

<!-- Modal A: Add Step Modal -->
<div id="addStepModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden">
        <div class="bg-brand-emerald text-white px-6 py-4 flex justify-between items-center">
            <h3 class="font-extrabold text-base flex items-center gap-1.5">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Tahapan Formulir
            </h3>
            <button onclick="closeAddStepModal()" class="text-white hover:text-brand-yellow font-bold text-lg">&times;</button>
        </div>
        <form action="{{ route('admin.spmb-settings.form.steps.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nama Tahapan*</label>
                <input type="text" name="title" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm" placeholder="Contoh: Dokumen Pendukung">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Urutan Tampil (Order)*</label>
                <input type="number" name="order" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm" value="{{ $steps->count() + 1 }}">
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeAddStepModal()" class="border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition">Kembali</button>
                <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2 rounded-xl text-xs font-bold transition shadow-md">Simpan Tahapan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal B: Edit Step Modal -->
<div id="editStepModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden">
        <div class="bg-brand-emerald text-white px-6 py-4 flex justify-between items-center">
            <h3 class="font-extrabold text-base flex items-center gap-1.5">
                <i data-lucide="edit-3" class="w-4 h-4"></i> Edit Tahapan Formulir
            </h3>
            <button onclick="closeEditStepModal()" class="text-white hover:text-brand-yellow font-bold text-lg">&times;</button>
        </div>
        <form id="editStepForm" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nama Tahapan*</label>
                <input type="text" id="edit-step-title" name="title" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Urutan Tampil (Order)*</label>
                <input type="number" id="edit-step-order" name="order" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeEditStepModal()" class="border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition">Kembali</button>
                <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2 rounded-xl text-xs font-bold transition shadow-md">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal C: Add Field Modal -->
<div id="addFieldModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden">
        <div class="bg-brand-emerald text-white px-6 py-4 flex justify-between items-center">
            <h3 class="font-extrabold text-base flex items-center gap-1.5">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Kolom Input
            </h3>
            <button onclick="closeAddFieldModal()" class="text-white hover:text-brand-yellow font-bold text-lg">&times;</button>
        </div>
        <form action="{{ route('admin.spmb-settings.form.fields.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" id="add-field-step-id" name="form_step_id">
            
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Label Input (Dibaca Pendaftar)*</label>
                <input type="text" name="label" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm" placeholder="Contoh: Golongan Darah">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Key Database / Nama Kolom (Unik)*</label>
                <input type="text" name="field_name" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm" placeholder="Contoh: blood_type">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Jenis Form (Tipe)*</label>
                <select name="type" id="add-field-type" onchange="toggleOptionsInput('add')" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-bold">
                    <option value="text">Input Text (Teks Biasa)</option>
                    <option value="number">Input Number (Angka NIK/Telp)</option>
                    <option value="email">Input Email</option>
                    <option value="date">Input Date (Tanggal)</option>
                    <option value="select">Select Dropdown (Pilihan)</option>
                    <option value="textarea">Textarea (Teks Panjang/Alamat)</option>
                    <option value="file">File Upload (Unggah Berkas)</option>
                </select>
            </div>
            
            <!-- Dynamic options fields list for select dropdown types -->
            <div id="add-options-wrapper" class="hidden">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Pilihan-Pilihan Dropdown (Pisahkan Dengan Koma)*</label>
                <input type="text" name="options" id="add-field-options" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm" placeholder="A,B,AB,O">
            </div>

            <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200 bg-slate-50">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Wajib Diisi (Mandatory)?</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_required" value="1" checked class="sr-only peer">
                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked-emerald"></div>
                </label>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Urutan Tampil (Order)*</label>
                <input type="number" name="order" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm" value="1">
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeAddFieldModal()" class="border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition">Kembali</button>
                <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2 rounded-xl text-xs font-bold transition shadow-md">Simpan Kolom</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal D: Edit Field Modal -->
<div id="editFieldModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden">
        <div class="bg-brand-emerald text-white px-6 py-4 flex justify-between items-center">
            <h3 class="font-extrabold text-base flex items-center gap-1.5">
                <i data-lucide="edit-3" class="w-4 h-4"></i> Edit Kolom Input
            </h3>
            <button onclick="closeEditFieldModal()" class="text-white hover:text-brand-yellow font-bold text-lg">&times;</button>
        </div>
        <form id="editFieldForm" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Label Input (Dibaca Pendaftar)*</label>
                <input type="text" id="edit-field-label" name="label" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Key Database / Nama Kolom (Unik)*</label>
                <input type="text" id="edit-field-name" name="field_name" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Jenis Form (Tipe)*</label>
                <select name="type" id="edit-field-type" onchange="toggleOptionsInput('edit')" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-bold">
                    <option value="text">Input Text (Teks Biasa)</option>
                    <option value="number">Input Number (Angka NIK/Telp)</option>
                    <option value="email">Input Email</option>
                    <option value="date">Input Date (Tanggal)</option>
                    <option value="select">Select Dropdown (Pilihan)</option>
                    <option value="textarea">Textarea (Teks Panjang/Alamat)</option>
                    <option value="file">File Upload (Unggah Berkas)</option>
                </select>
            </div>
            
            <div id="edit-options-wrapper" class="hidden">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Pilihan-Pilihan Dropdown (Pisahkan Dengan Koma)*</label>
                <input type="text" name="options" id="edit-field-options" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
            </div>

            <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200 bg-slate-50">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Wajib Diisi (Mandatory)?</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_required" id="edit-field-required" value="1" class="sr-only peer">
                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked-emerald"></div>
                </label>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Urutan Tampil (Order)*</label>
                <input type="number" id="edit-field-order" name="order" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
            </div>

            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeEditFieldModal()" class="border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition">Kembali</button>
                <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2 rounded-xl text-xs font-bold transition shadow-md">Simpan Perubahan</button>
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
    // Tab switching memory
    function switchFormTab(tabId) {
        document.querySelectorAll('.form-tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('formTabContent-' + tabId).classList.remove('hidden');

        document.querySelectorAll('.form-tab-btn').forEach(btn => {
            btn.className = "form-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50";
        });
        
        const activeBtn = document.getElementById('formTabBtn-' + tabId);
        activeBtn.className = "form-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow";
        
        localStorage.setItem('spmb_form_active_tab', tabId);
    }

    document.addEventListener("DOMContentLoaded", function() {
        const savedTab = localStorage.getItem('spmb_form_active_tab') || 'crud_steps';
        switchFormTab(savedTab);
    });

    // Step Modals
    function openAddStepModal() {
        document.getElementById('addStepModal').classList.remove('hidden');
    }
    function closeAddStepModal() {
        document.getElementById('addStepModal').classList.add('hidden');
    }

    function openEditStepModal(step) {
        document.getElementById('edit-step-title').value = step.title;
        document.getElementById('edit-step-order').value = step.order;
        document.getElementById('editStepForm').action = '/admin/spmb-settings/form/steps/' + step.id;
        document.getElementById('editStepModal').classList.remove('hidden');
    }
    function closeEditStepModal() {
        document.getElementById('editStepModal').classList.add('hidden');
    }

    function deleteStepItem(name, url) {
        confirmDelete(url, `Apakah Anda yakin ingin menghapus tahapan "${name}"? Seluruh kolom input di dalam tahapan ini juga akan ikut terhapus.`);
    }

    // Field Modals
    function openAddFieldModal(stepId) {
        document.getElementById('add-field-step-id').value = stepId;
        document.getElementById('addFieldModal').classList.remove('hidden');
        toggleOptionsInput('add');
    }
    function closeAddFieldModal() {
        document.getElementById('addFieldModal').classList.add('hidden');
    }

    function openEditFieldModal(field) {
        document.getElementById('edit-field-label').value = field.label;
        document.getElementById('edit-field-name').value = field.field_name;
        document.getElementById('edit-field-type').value = field.type;
        document.getElementById('edit-field-options').value = field.options || '';
        document.getElementById('edit-field-required').checked = (field.is_required === 1 || field.is_required === '1' || field.is_required === true);
        document.getElementById('edit-field-order').value = field.order;
        document.getElementById('editFieldForm').action = '/admin/spmb-settings/form/fields/' + field.id;
        
        document.getElementById('editFieldModal').classList.remove('hidden');
        toggleOptionsInput('edit');
    }
    function closeEditFieldModal() {
        document.getElementById('editFieldModal').classList.add('hidden');
    }

    function deleteFieldItem(name, url) {
        confirmDelete(url, `Apakah Anda yakin ingin menghapus kolom input "${name}"?`);
    }

    // Toggle options field visibility for 'select' type
    function toggleOptionsInput(prefix) {
        const typeEl = document.getElementById(prefix + '-field-type');
        const wrapperEl = document.getElementById(prefix + '-options-wrapper');
        const optionsInput = document.getElementById(prefix + '-field-options');
        
        if (typeEl && typeEl.value === 'select') {
            wrapperEl.classList.remove('hidden');
            optionsInput.required = true;
        } else {
            wrapperEl.classList.add('hidden');
            optionsInput.required = false;
        }
    }
</script>
@endsection
