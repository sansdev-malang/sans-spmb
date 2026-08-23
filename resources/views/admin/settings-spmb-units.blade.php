@extends('layouts.admin')

@section('title', 'Master Unit & Tingkatan SPMB - Admin SANS')
@section('page_title', 'Master Unit & Tingkatan')

@section('content')
<div class="p-8">
    
    <!-- Top Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">Master Unit & Tingkatan</h1>
            <p class="text-xs text-slate-500 mt-1">Kelola data master unit sekolah dan tingkatan kelas untuk penerimaan siswa baru.</p>
        </div>
    </div>



    <!-- Main Card Container -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        
        <!-- Tab Navigation -->
        <div class="border-b border-slate-100 p-4 flex gap-2 overflow-x-auto">
            <button id="tabBtn-unit" onclick="switchTab('unit')" class="tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow">
                <i data-lucide="building-2" class="w-4 h-4"></i> Unit Sekolah
            </button>
            <button id="tabBtn-grade" onclick="switchTab('grade')" class="tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
                <i data-lucide="layers" class="w-4 h-4"></i> Tingkatan Kelas
            </button>
            <button id="tabBtn-extra" onclick="switchTab('extra')" class="tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
                <i data-lucide="sparkles" class="w-4 h-4"></i> Layanan Non-Formal
            </button>
        </div>

        <!-- Tab: Unit -->
        <div id="tabContent-unit" class="tab-content p-8 space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-extrabold text-base text-slate-800">Unit Sekolah</h3>
                    <p class="text-[11px] text-slate-400">Kelola unit sekolah yang tersedia untuk pendaftaran (mis. SANS PAUD, SANS SD).</p>
                </div>
                <button onclick="openUnitModal('', '', '1', true, '{{ route('admin.spmb-settings.units.store') }}')" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Unit
                </button>
            </div>
            
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Nama Unit</th>
                            <th class="py-4 px-6">Kode Unit</th>
                            <th class="py-4 px-6 text-center">Status</th>
                            <th class="py-4 px-6 text-center">Digunakan Transaksi</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($units as $unit)
                            <tr class="hover:bg-slate-50/30 transition">
                                <td class="py-4 px-6 font-extrabold text-slate-800">{{ $unit->name }}</td>
                                <td class="py-4 px-6 text-slate-600">{{ $unit->code ?? '-' }}</td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $unit->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $unit->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center text-xs font-semibold {{ $unit->registrations_count > 0 ? 'text-slate-600 font-bold' : 'text-slate-400' }}">
                                    {{ $unit->registrations_count }} Pendaftar
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center justify-end gap-2">
                                        <button onclick="openUnitModal('{{ $unit->name }}', '{{ $unit->code }}', '{{ $unit->is_active }}', false, '{{ route('admin.spmb-settings.units.update', $unit->id) }}')" class="p-2 text-slate-400 hover:text-brand-emerald bg-slate-50 hover:bg-emerald-50 rounded-lg transition" title="Edit Unit">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        @if($unit->registrations_count > 0)
                                            <button type="button" onclick="showToast('Peringatan: Tidak dapat menghapus Unit karena sudah digunakan oleh pendaftar!', 'error')" class="p-2 text-slate-300 bg-slate-50 rounded-lg cursor-not-allowed" title="Hapus Unit">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        @else
                                            <button type="button" onclick="confirmDelete('{{ route('admin.spmb-settings.units.delete', $unit->id) }}', 'Apakah Anda yakin ingin menghapus Unit ini? Data yang terhapus tidak dapat dikembalikan.')" class="p-2 text-slate-400 hover:text-rose-600 bg-slate-50 hover:bg-rose-50 rounded-lg transition" title="Hapus Unit">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 text-xs">Belum ada unit yang ditambahkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Grade (Tingkatan) -->
        <div id="tabContent-grade" class="tab-content p-8 space-y-6 hidden">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-extrabold text-base text-slate-800">Tingkatan Kelas</h3>
                    <p class="text-[11px] text-slate-400">Kelola tingkatan kelas untuk setiap Unit (mis. TK A, TK B, Kelas 1).</p>
                </div>
                <button onclick="openGradeModal('', '', '1', true, '{{ route('admin.spmb-settings.grades.store') }}')" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Tingkatan
                </button>
            </div>
            
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Tingkatan (Grade)</th>
                            <th class="py-4 px-6">Unit Asal</th>
                            <th class="py-4 px-6 text-center">Status</th>
                            <th class="py-4 px-6 text-center">Digunakan Transaksi</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($grades as $grade)
                            <tr class="hover:bg-slate-50/30 transition">
                                <td class="py-4 px-6 font-extrabold text-slate-800">{{ $grade->name }}</td>
                                <td class="py-4 px-6 text-slate-600">{{ $grade->unit->name ?? '-' }}</td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $grade->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $grade->is_active ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center text-xs font-semibold {{ $grade->registrations_count > 0 ? 'text-slate-600 font-bold' : 'text-slate-400' }}">
                                    {{ $grade->registrations_count }} Pendaftar
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center justify-end gap-2">
                                        <button onclick="openGradeModal('{{ $grade->name }}', '{{ $grade->spmb_unit_id }}', '{{ $grade->is_active }}', false, '{{ route('admin.spmb-settings.grades.update', $grade->id) }}')" class="p-2 text-slate-400 hover:text-brand-emerald bg-slate-50 hover:bg-emerald-50 rounded-lg transition" title="Edit Tingkatan">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        @if($grade->registrations_count > 0)
                                            <button type="button" onclick="showToast('Peringatan: Tidak dapat menghapus Tingkatan karena sudah digunakan oleh pendaftar!', 'error')" class="p-2 text-slate-300 bg-slate-50 rounded-lg cursor-not-allowed" title="Hapus Tingkatan">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        @else
                                            <button type="button" onclick="confirmDelete('{{ route('admin.spmb-settings.grades.delete', $grade->id) }}', 'Apakah Anda yakin ingin menghapus Tingkatan ini? Data yang terhapus tidak dapat dikembalikan.')" class="p-2 text-slate-400 hover:text-rose-600 bg-slate-50 hover:bg-rose-50 rounded-lg transition" title="Hapus Tingkatan">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 text-xs">Belum ada tingkatan yang ditambahkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
            
            <!-- Tab: Extra Services (Layanan Non-Formal) -->
            <div id="tabContent-extra" class="tab-content p-8 space-y-6 hidden">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-extrabold text-base text-slate-800">Layanan Non-Formal</h3>
                        <p class="text-[11px] text-slate-400">Kelola layanan tambahan opsional seperti TPA/Daycare dan TPQ.</p>
                    </div>
                    <button onclick="openExtraModal('', '', '0', '1', true, '{{ route('admin.spmb-settings.extra-services.store') }}')" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Layanan
                    </button>
                </div>
                
                <div class="overflow-x-auto border border-slate-100 rounded-xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                                <th class="py-4 px-6">Nama Layanan</th>
                                <th class="py-4 px-6">Kode Layanan</th>
                                <th class="py-4 px-6 text-center">Status</th>
                                <th class="py-4 px-6 text-center">Jumlah Siswa</th>
                                <th class="py-4 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs text-slate-650 divide-y divide-slate-50">
                            @forelse($extraServices as $service)
                                <tr class="hover:bg-slate-50/30 transition">
                                    <td class="py-4 px-6 font-bold text-slate-800">
                                        {{ $service->name }}
                                    </td>
                                    <td class="py-4 px-6 font-mono font-bold text-brand-emerald">
                                        {{ $service->code }}
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border {{ $service->is_active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-slate-100 text-slate-500 border-slate-200' }}">
                                            {{ $service->is_active ? 'Aktif' : 'Non-Aktif' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-center text-xs font-semibold {{ $service->registrations_count > 0 ? 'text-slate-600 font-bold' : 'text-slate-400' }}">
                                        {{ $service->registrations_count }} Pendaftar
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center justify-center gap-2">
                                            <button onclick="openExtraModal('{{ addslashes($service->name) }}', '{{ addslashes($service->code) }}', '{{ $service->is_active }}', false, '{{ route('admin.spmb-settings.extra-services.update', $service->id) }}')" class="p-2 text-slate-400 hover:text-brand-emerald bg-slate-50 hover:bg-emerald-50 rounded-lg transition" title="Edit Layanan">
                                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                                            </button>
                                            @if($service->registrations_count > 0)
                                                <button type="button" onclick="showToast('Peringatan: Tidak dapat menghapus Layanan karena sudah digunakan oleh pendaftar!', 'error')" class="p-2 text-slate-300 bg-slate-50 rounded-lg cursor-not-allowed" title="Hapus Layanan">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            @else
                                                <button type="button" onclick="confirmDelete('{{ route('admin.spmb-settings.extra-services.delete', $service->id) }}', 'Apakah Anda yakin ingin menghapus Layanan ini? Data yang terhapus tidak dapat dikembalikan.')" class="p-2 text-slate-400 hover:text-rose-600 bg-slate-50 hover:bg-rose-50 rounded-lg transition" title="Hapus Layanan">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400 text-xs">Belum ada layanan non-formal yang ditambahkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal for Extra Service (Layanan Non-Formal) -->
        <div id="extraModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="bg-white w-full max-w-md rounded-3xl shadow-xl transform scale-95 transition-transform duration-300" id="extraModalBody">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="text-lg font-extrabold text-slate-800" id="extraModalTitle">Tambah Layanan Non-Formal</h2>
                    <button onclick="closeExtraModal()" type="button" class="p-2 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-slate-650 transition">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form id="extraForm" method="POST" action="">
                    @csrf
                    <div id="extraMethod"></div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nama Layanan</label>
                            <input type="text" id="extraNameInput" name="name" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: Taman Penitipan Anak (TPA)">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Kode Layanan</label>
                            <input type="text" id="extraCodeInput" name="code" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: TPA">
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
    <div id="unitModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-xl transform scale-95 transition-transform duration-300" id="unitModalBody">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-lg font-extrabold text-slate-800" id="unitModalTitle">Tambah Unit Pendaftaran</h2>
                <button onclick="closeUnitModal()" type="button" class="p-2 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-slate-600 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="unitForm" method="POST" action="">
                @csrf
                <div id="unitMethod"></div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nama Unit Sekolah</label>
                        <input type="text" id="unitNameInput" name="name" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: SANS SD">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Kode Unit</label>
                        <input type="text" id="unitCodeInput" name="code" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Misal: SD (Opsional)">
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
    <div id="gradeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-xl transform scale-95 transition-transform duration-300" id="gradeModalBody">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <h2 class="text-lg font-extrabold text-slate-800" id="gradeModalTitle">Tambah Tingkatan Kelas</h2>
                <button onclick="closeGradeModal()" type="button" class="p-2 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-slate-600 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form id="gradeForm" method="POST" action="">
                @csrf
                <div id="gradeMethod"></div>
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

</div>

<script>
    // Tab Switching
    function switchTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('tabContent-' + tabId).classList.remove('hidden');

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.className = "tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50";
        });
        
        const activeBtn = document.getElementById('tabBtn-' + tabId);
        activeBtn.className = "tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow";
        
        localStorage.setItem('spmb_units_active_tab', tabId);
    }

    document.addEventListener("DOMContentLoaded", function() {
        const savedTab = localStorage.getItem('spmb_units_active_tab') || 'unit';
        switchTab(savedTab);
    });

    // Modal Unit
    function openUnitModal(name = '', code = '', isActive = '1', isCreate = true, actionUrl = '') {
        const modal = document.getElementById('unitModal');
        const modalBody = document.getElementById('unitModalBody');
        const form = document.getElementById('unitForm');
        const methodDiv = document.getElementById('unitMethod');
        
        document.getElementById('unitModalTitle').innerText = isCreate ? 'Tambah Unit Pendaftaran' : 'Edit Unit Pendaftaran';
        document.getElementById('unitSubmitBtn').innerText = isCreate ? 'Simpan Unit' : 'Perbarui Unit';
        
        form.action = actionUrl;
        document.getElementById('unitNameInput').value = name;
        document.getElementById('unitCodeInput').value = code;
        document.getElementById('unitActiveInput').checked = (isActive == '1' || isActive == true);
        
        if (!isCreate) {
            methodDiv.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        } else {
            methodDiv.innerHTML = '';
        }
        
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modalBody.classList.remove('scale-95');
        modalBody.classList.add('scale-100');
    }

    function closeUnitModal() {
        const modal = document.getElementById('unitModal');
        const modalBody = document.getElementById('unitModalBody');
        modal.classList.add('opacity-0', 'pointer-events-none');
        modalBody.classList.remove('scale-100');
        modalBody.classList.add('scale-95');
    }

    // Modal Grade
    function openGradeModal(name = '', unitId = '', isActive = '1', isCreate = true, actionUrl = '') {
        const modal = document.getElementById('gradeModal');
        const modalBody = document.getElementById('gradeModalBody');
        const form = document.getElementById('gradeForm');
        const methodDiv = document.getElementById('gradeMethod');
        
        document.getElementById('gradeModalTitle').innerText = isCreate ? 'Tambah Tingkatan Kelas' : 'Edit Tingkatan Kelas';
        document.getElementById('gradeSubmitBtn').innerText = isCreate ? 'Simpan Tingkatan' : 'Perbarui Tingkatan';
        
        form.action = actionUrl;
        document.getElementById('gradeNameInput').value = name;
        document.getElementById('gradeUnitInput').value = unitId;
        document.getElementById('gradeActiveInput').checked = (isActive == '1' || isActive == true);
        
        if (!isCreate) {
            methodDiv.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        } else {
            methodDiv.innerHTML = '';
        }
        
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modalBody.classList.remove('scale-95');
        modalBody.classList.add('scale-100');
    }

    function closeGradeModal() {
        const modal = document.getElementById('gradeModal');
        const modalBody = document.getElementById('gradeModalBody');
        modal.classList.add('opacity-0', 'pointer-events-none');
        modalBody.classList.remove('scale-100');
        modalBody.classList.add('scale-95');
    }

    // Modal Extra Service
    function openExtraModal(name = '', code = '', isActive = '1', isCreate = true, actionUrl = '') {
        const modal = document.getElementById('extraModal');
        const modalBody = document.getElementById('extraModalBody');
        const form = document.getElementById('extraForm');
        const methodDiv = document.getElementById('extraMethod');
        
        document.getElementById('extraModalTitle').innerText = isCreate ? 'Tambah Layanan Non-Formal' : 'Edit Layanan Non-Formal';
        document.getElementById('extraSubmitBtn').innerText = isCreate ? 'Simpan Layanan' : 'Perbarui Layanan';
        
        form.action = actionUrl;
        document.getElementById('extraNameInput').value = name;
        document.getElementById('extraCodeInput').value = code;
        document.getElementById('extraActiveInput').checked = (isActive == '1' || isActive == true || isActive == 'true');
        
        if (!isCreate) {
            methodDiv.innerHTML = '<input type="hidden" name="_method" value="PUT">';
        } else {
            methodDiv.innerHTML = '';
        }
        
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modalBody.classList.remove('scale-95');
        modalBody.classList.add('scale-100');
    }

    function closeExtraModal() {
        const modal = document.getElementById('extraModal');
        const modalBody = document.getElementById('extraModalBody');
        modal.classList.add('opacity-0', 'pointer-events-none');
        modalBody.classList.remove('scale-100');
        modalBody.classList.add('scale-95');
    }
</script>
@endsection
