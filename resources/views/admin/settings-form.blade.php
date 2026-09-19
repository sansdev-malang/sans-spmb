@extends('layouts.admin')

@section('title', 'Setting Formulir - Admin Panel')
@section('page_title', 'Setting Formulir')

@section('content')
<div class="w-full space-y-6">
    <!-- Header -->
    <!-- Header with Unit Filter -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                Pengaturan Tahapan & Kolom Formulir (Form Settings)
            </h1>
            <p class="text-xs text-slate-500 mt-1">Kelola tahapan wizard pendaftaran calon murid beserta pertanyaan kolom input secara dinamis.</p>
        </div>
        <!-- Unit Filter -->
        @if(auth()->user()->isUnitAdmin())
            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200/65 px-3 py-2 rounded-2xl shadow-inner">
                <i data-lucide="shield" class="w-4 h-4 text-brand-emerald"></i>
                <span class="text-xs font-extrabold text-slate-700">Unit: {{ auth()->user()->spmbUnit?->name ?? 'Unit Sekolah' }}</span>
                <span class="text-[10px] font-extrabold px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-lg">Khusus Unit</span>
            </div>
        @else
            <div class="flex items-center gap-2.5 bg-slate-50 border border-slate-200/65 p-2.5 rounded-2xl shadow-inner">
                <span class="text-xs font-extrabold text-slate-650 flex items-center gap-1.5 pl-1.5 whitespace-nowrap">
                    <i data-lucide="filter" class="w-4 h-4 text-brand-emerald"></i>
                    Unit Sekolah:
                </span>
                <select onchange="const bar = document.getElementById('top-loading-bar'); if(bar){ bar.style.opacity = '1'; bar.style.width = '60%'; setTimeout(() => { if(bar.style.opacity === '1') bar.style.width = '90%'; }, 500); }; window.location.href = '{{ route('admin.spmb-settings.form') }}?tab={{ $activeTab }}&unit_id=' + this.value" class="bg-white border border-slate-300 rounded-xl px-3.5 py-1.5 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald cursor-pointer">
                    <option value="" {{ $selectedUnitId === '' ? 'selected' : '' }}>-- Semua Unit (Global) --</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    <!-- Documentation & Key Guide Card (Collapsible) -->
    <div x-data="{ openGuide: false }" class="bg-gradient-to-r from-emerald-50/80 via-teal-50/50 to-indigo-50/80 border border-emerald-200/80 rounded-2xl p-5 shadow-xs transition-all">
        <div class="flex items-center justify-between cursor-pointer select-none" @click="openGuide = !openGuide">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-brand-emerald text-white flex items-center justify-center flex-shrink-0 shadow-xs">
                    <i data-lucide="book-open" class="w-5 h-5 text-brand-yellow"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-800 flex items-center gap-2">
                        Panduan & Referensi Penamaan Kolom (Key Database)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">Tips Admin</span>
                    </h3>
                    <p class="text-xs text-slate-600 mt-0.5">Klik untuk melihat penjelasan cara kerja Key Database, daftar kolom sistem, dan cara menambah kolom baru secara bebas.</p>
                </div>
            </div>
            <button type="button" class="p-2 text-slate-500 hover:text-brand-emerald rounded-xl hover:bg-white/60 transition">
                <i data-lucide="chevron-down" class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': openGuide }"></i>
            </button>
        </div>

        <div x-show="openGuide" x-collapse x-cloak class="mt-5 pt-5 border-t border-emerald-200/60 space-y-4 text-xs text-slate-700">
            <!-- Section 1: Cara Kerja -->
            <div class="bg-white/90 rounded-xl p-4 border border-emerald-100 space-y-2">
                <h4 class="font-extrabold text-slate-900 flex items-center gap-1.5 text-xs">
                    <i data-lucide="sparkles" class="w-4 h-4 text-brand-emerald"></i>
                    1. Apakah Saya Bebas Membuat Nama Kolom Baru?
                </h4>
                <p class="leading-relaxed text-slate-600">
                    <strong>YA, SANGAT BEBAS!</strong> Sistem formulir SPMB dibuat secara fleksibel (*Dynamic Schema*). Saat Anda mengetik <strong>Label Input</strong> (misal: <em>"Golongan Darah"</em>), sistem akan <strong>otomatis membuatkan Key Database</strong> seperti <code class="bg-slate-100 px-1.5 py-0.5 rounded text-brand-emerald font-mono font-bold">golongan_darah</code>. Anda tidak perlu mengubah database atau koding apa pun. Semua jawaban pendaftar akan otomatis tersimpan dengan rapi.
                </p>
            </div>

            <!-- Section 2: Aturan Format -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white/90 rounded-xl p-4 border border-emerald-100 space-y-2">
                    <h4 class="font-extrabold text-slate-900 flex items-center gap-1.5 text-xs">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                        2. Aturan Format Key Database (Nama Kolom)
                    </h4>
                    <ul class="space-y-1.5 text-[11px] text-slate-600 list-disc list-inside">
                        <li>Gunakan <strong>huruf kecil</strong> semua (contoh: <code class="font-mono text-slate-800">anak_ke</code>).</li>
                        <li>Gunakan <strong>garis bawah (underscore <code>_</code>)</strong> sebagai pemisah kata, bukan spasi.</li>
                        <li>Jangan gunakan karakter khusus seperti <code class="text-rose-600">-, /, @, !, ?, .</code></li>
                        <li>Pastikan nama kolom <strong>unik</strong> dan belum dipakai di tahapan lain.</li>
                    </ul>
                </div>

                <div class="bg-white/90 rounded-xl p-4 border border-emerald-100 space-y-2">
                    <h4 class="font-extrabold text-slate-900 flex items-center gap-1.5 text-xs">
                        <i data-lucide="layers" class="w-4 h-4 text-indigo-600"></i>
                        3. Contoh Kolom Tambahan Kustom yang Sering Digunakan
                    </h4>
                    <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-600 font-mono">
                        <div class="bg-slate-50 p-2 rounded-lg border border-slate-200/60">
                            <span class="text-brand-emerald font-bold">anak_ke</span> (Number)<br>
                            <span class="text-brand-emerald font-bold">jumlah_saudara</span> (Number)<br>
                            <span class="text-brand-emerald font-bold">golongan_darah</span> (Select)
                        </div>
                        <div class="bg-slate-50 p-2 rounded-lg border border-slate-200/60">
                            <span class="text-brand-emerald font-bold">tinggi_badan</span> (Number)<br>
                            <span class="text-brand-emerald font-bold">berat_badan</span> (Number)<br>
                            <span class="text-brand-emerald font-bold">riwayat_alergi</span> (Text)
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Kolom Bawaan Sistem -->
            <div class="bg-white/90 rounded-xl p-4 border border-emerald-100 space-y-2">
                <h4 class="font-extrabold text-slate-900 flex items-center gap-1.5 text-xs">
                    <i data-lucide="database" class="w-4 h-4 text-blue-600"></i>
                    4. Daftar Kolom Bawaan Sistem (Physical Columns)
                </h4>
                <p class="text-[11px] text-slate-500">Jika Anda ingin menanyakan data profil standar, gunakan nama kolom bawaan di bawah ini agar terhubung langsung dengan kartu pendaftar dan laporan rekap:</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 text-[11px]">
                    <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200">
                        <strong class="text-slate-800 block mb-1">Calon Murid:</strong>
                        <code class="text-[10px] text-slate-600 block">candidate_name</code>
                        <code class="text-[10px] text-slate-600 block">nickname</code>
                        <code class="text-[10px] text-slate-600 block">nik</code>
                        <code class="text-[10px] text-slate-600 block">family_card_no</code>
                        <code class="text-[10px] text-slate-600 block">gender</code>
                        <code class="text-[10px] text-slate-600 block">birth_place</code>
                        <code class="text-[10px] text-slate-600 block">birth_date</code>
                        <code class="text-[10px] text-slate-600 block">religion</code>
                        <code class="text-[10px] text-slate-600 block">previous_school</code>
                    </div>
                    <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200">
                        <strong class="text-slate-800 block mb-1">Alamat:</strong>
                        <code class="text-[10px] text-slate-600 block">address</code>
                        <code class="text-[10px] text-slate-600 block">house_number</code>
                        <code class="text-[10px] text-slate-600 block">rt</code> / <code class="text-[10px] text-slate-600">rw</code>
                        <code class="text-[10px] text-slate-600 block">kelurahan</code>
                        <code class="text-[10px] text-slate-600 block">kecamatan</code>
                        <code class="text-[10px] text-slate-600 block">city</code> / <code class="text-[10px] text-slate-600">province</code>
                    </div>
                    <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200">
                        <strong class="text-slate-800 block mb-1">Orang Tua / Wali:</strong>
                        <code class="text-[10px] text-slate-600 block">father_name, father_nik</code>
                        <code class="text-[10px] text-slate-600 block">father_job, father_phone</code>
                        <code class="text-[10px] text-slate-600 block">mother_name, mother_nik</code>
                        <code class="text-[10px] text-slate-600 block">mother_job, mother_phone</code>
                        <code class="text-[10px] text-slate-600 block">guardian_name, guardian_phone</code>
                    </div>
                    <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-200">
                        <strong class="text-slate-800 block mb-1">Berkas Unggahan:</strong>
                        <code class="text-[10px] text-slate-600 block">student_photo_path</code>
                        <code class="text-[10px] text-slate-600 block">birth_certificate_path</code>
                        <code class="text-[10px] text-slate-600 block">family_card_path</code>
                        <code class="text-[10px] text-slate-600 block">diploma_certificate_path</code>
                        <code class="text-[10px] text-slate-600 block">student_card_path</code>
                        <code class="text-[10px] text-slate-600 block">special_needs_assessment_path</code>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation Pills -->
    <div class="flex flex-wrap items-center justify-between gap-2 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
        <div class="flex flex-wrap items-center gap-2">
            @if(!auth()->user()->isUnitAdmin())
                <button onclick="switchFormTab('crud_steps')" id="formTabBtn-crud_steps" class="form-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'crud_steps' ? 'bg-brand-emerald text-white shadow' : 'text-slate-600 hover:bg-slate-50' }} cursor-pointer">
                    <i data-lucide="list-ordered" class="w-4 h-4"></i> Manajemen Tahapan (Steps)
                </button>
            @endif
            @foreach($steps as $step)
                <button onclick="switchFormTab('step_{{ $step->id }}')" id="formTabBtn-step_{{ $step->id }}" class="form-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'step_' . $step->id ? 'bg-brand-emerald text-white shadow' : 'text-slate-600 hover:bg-slate-50' }} cursor-pointer">
                    <i data-lucide="folder" class="w-4 h-4"></i> {{ $step->title }}
                </button>
            @endforeach
        </div>

        @if($isSuperAdmin)
            @php
                $trashFieldCount = $trashedFields->count();
                $isAnyTrashTab = ($activeTab === 'test_fields');
            @endphp
            <!-- Tong Sampah Dropdown di Pojok Kanan (Khusus Super Admin) -->
            <div class="relative inline-block text-left" id="formTrashDropdownWrapper">
                <button type="button" onclick="toggleFormTrashDropdown()" id="formTrashDropdownBtn" class="px-3.5 py-2 {{ $isAnyTrashTab ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/80' }} rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-xs cursor-pointer">
                    <i data-lucide="trash-2" class="w-4 h-4 {{ $isAnyTrashTab ? 'text-white' : 'text-amber-600' }}"></i>
                    <span>Tong Sampah</span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 {{ $isAnyTrashTab ? 'text-white/80' : 'text-slate-400' }}"></i>
                </button>
                <div id="formTrashDropdownMenu" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-30 transition-all">
                    <div class="px-3.5 py-2 text-[10px] font-extrabold uppercase tracking-wider text-amber-800 bg-amber-50/70 border-b border-amber-100 mb-1">
                        Terkunci
                    </div>
                    <a href="javascript:void(0)" onclick="switchFormTab('test_fields'); closeFormTrashDropdown();" id="trashSubTab-test_fields" class="trash-subtab-btn w-full text-left px-3.5 py-2 text-xs font-bold transition flex items-center justify-between gap-2 cursor-pointer {{ $activeTab === 'test_fields' ? 'bg-amber-100 text-amber-900 font-extrabold border-l-4 border-amber-600' : 'text-slate-700 hover:bg-amber-50 hover:text-amber-800' }}">
                        <div class="flex items-center gap-2">
                            <i data-lucide="form-input" class="w-3.5 h-3.5 text-amber-600"></i>
                            <span>Kolom Input Formulir</span>
                        </div>
                        @if($trashFieldCount > 0)
                            <span class="dropdown-item-count px-1.5 py-0.5 text-[9px] font-extrabold rounded-full {{ $activeTab === 'test_fields' ? 'bg-amber-200 text-amber-900' : 'bg-amber-100 text-amber-800' }}">{{ $trashFieldCount }} Data</span>
                        @else
                            <span class="dropdown-item-count text-[9px] font-semibold text-slate-400">0 Data</span>
                        @endif
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Tab Contents Container -->
    <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        
        @if(!auth()->user()->isUnitAdmin())
        <!-- Tab 1: CRUD Steps (Super Admin Only) -->
        <div id="formTabContent-crud_steps" class="form-tab-content p-8 space-y-6 {{ $activeTab === 'crud_steps' ? '' : 'hidden' }}">
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
                            <th class="py-4 px-6">Berlaku Untuk</th>
                            <th class="py-4 px-6 text-center">Jumlah Kolom</th>
                            <th class="py-4 px-6 text-center">Status Keaktifan</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($steps as $step)
                            <tr class="hover:bg-slate-50/30 transition">
                                <td class="py-4 px-6 text-xs font-bold text-slate-700">Langkah #{{ $step->order }}</td>
                                <td class="py-4 px-6 text-xs font-extrabold text-slate-800">{{ $step->title }}</td>
                                <td class="py-4 px-6 space-x-1 space-y-1">
                                    @forelse($step->units as $u)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-100">
                                            {{ $u->code }}
                                        </span>
                                    @empty
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-extrabold bg-slate-50 text-slate-550 border border-slate-200">
                                            Global (Semua)
                                        </span>
                                    @endforelse
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex min-w-16 justify-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ $step->fields->count() }} Input
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide border {{ $step->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $step->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        {{ $step->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 align-middle text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" onclick="openEditStepModal({{ json_encode($step) }})" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-brand-emerald text-xs font-bold text-brand-emerald transition hover:bg-emerald-800 hover:border-emerald-800 hover:text-white cursor-pointer" title="Edit Tahapan">
                                            <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                            <span>Edit</span>
                                        </button>
                                        <button type="button" onclick="deleteStepItem('{{ $step->title }}', '{{ route('admin.spmb-settings.form.steps.delete', $step->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white cursor-pointer" title="Hapus Tahapan">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 px-6 text-center text-slate-400">Belum ada langkah formulir dikonfigurasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Tab 2 to N: Fields for each Step -->
        @foreach($steps as $step)
            <div id="formTabContent-step_{{ $step->id }}" class="form-tab-content p-8 space-y-6 {{ $activeTab === 'step_' . $step->id ? '' : 'hidden' }}">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-extrabold text-base text-slate-800">Daftar Pertanyaan: {{ $step->title }}</h3>
                        <p class="text-[11px] text-slate-400">Kelola isian kolom formulir di tahapan ini.</p>
                    </div>
                    <button onclick="openAddFieldModal({{ $step->id }})" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Kolom Input
                    </button>
                </div>
                
                <div class="overflow-x-auto border border-slate-100 rounded-xl">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                                <th class="py-4 px-6">Urutan</th>
                                <th class="py-4 px-6">Label Kolom</th>
                                <th class="py-4 px-6">Berlaku Untuk</th>
                                <th class="py-4 px-6">Key Database</th>
                                <th class="py-4 px-6">Tipe Form</th>
                                <th class="py-4 px-6 text-center">Wajib Diisi</th>
                                <th class="py-4 px-6">Pilihan Pilihan (Dropdown)</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            @forelse($step->fields as $field)
                                @php
                                    $isSystemField = in_array($field->field_name, ['candidate_name', 'spmb_period_id', 'spmb_wave_id', 'spmb_type_id', 'spmb_class_program_id']);
                                    $isDefaultField = $field->units->isEmpty();
                                    $isUnitAdmin = auth()->user()->isUnitAdmin();
                                    $canEditField = !($isUnitAdmin && $isDefaultField);
                                    $canDeleteField = !$isSystemField && !($isUnitAdmin && $isDefaultField);
                                @endphp
                                <tr class="hover:bg-slate-50/30 transition">
                                    <td class="py-4 px-6 text-xs font-bold text-slate-700">#{{ $field->order }}</td>
                                    <td class="py-4 px-6 text-xs font-extrabold text-slate-800">{{ $field->label }}</td>
                                    <td class="py-4 px-6 space-x-1 space-y-1">
                                        @forelse($field->units as $u)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-100">
                                                {{ $u->code ?: $u->name }}
                                            </span>
                                        @empty
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-extrabold bg-slate-50 text-slate-550 border border-slate-200">
                                                Global
                                            </span>
                                        @endforelse
                                    </td>
                                    <td class="py-4 px-6 font-mono text-xs text-slate-500">{{ $field->field_name }}</td>
                                    <td class="py-4 px-6 font-semibold text-brand-emerald text-xs uppercase">{{ $field->type }}</td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide border {{ $field->is_required ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-slate-50 text-slate-500 border-slate-200' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $field->is_required ? 'bg-rose-500' : 'bg-slate-400' }}"></span>
                                            {{ $field->is_required ? 'Wajib' : 'Opsional' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-xs text-slate-500 max-w-xs truncate">
                                        {{ $field->options ?? '-' }}
                                    </td>
                                    <td class="py-4 px-6 align-middle text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                        @if($canEditField)
                                            <button type="button" onclick="openEditFieldModal({{ json_encode($field) }})" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-brand-emerald text-xs font-bold text-brand-emerald transition hover:bg-emerald-800 hover:border-emerald-800 hover:text-white cursor-pointer" title="Edit Kolom">
                                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                                <span>Edit</span>
                                            </button>
                                        @else
                                            <span class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 border border-slate-200 text-xs font-bold text-slate-400 cursor-not-allowed" title="Kolom Default Sistem (Hanya dapat diubah oleh Super Admin)">
                                                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                                                <span>Default</span>
                                            </span>
                                        @endif

                                        @if($isSuperAdmin && !$isSystemField)
                                            <button type="button" onclick="confirmTrashField('{{ addslashes($field->label) }}', '{{ route('admin.spmb-settings.form.fields.trash', $field->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-amber-300 text-xs font-bold text-amber-800 bg-amber-50 hover:bg-amber-600 hover:border-amber-600 hover:text-white transition shadow-2xs cursor-pointer" title="Pindahkan ke Tong Sampah">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Tong Sampah</span>
                                            </button>
                                        @endif

                                        @if($canDeleteField)
                                            <button type="button" onclick="deleteFieldItem('{{ $field->label }}', '{{ route('admin.spmb-settings.form.fields.delete', $field->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white cursor-pointer" title="Hapus Kolom">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Hapus</span>
                                            </button>
                                        @elseif($canEditField)
                                            <button type="button" onclick="showToast('Peringatan: Tidak dapat menghapus Kolom Sistem Utama karena diperlukan oleh sistem!', 'error')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 text-xs font-bold text-red-600 transition hover:bg-red-600 hover:text-white cursor-pointer" title="Kolom Sistem Utama (Proteksi)">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Hapus</span>
                                            </button>
                                        @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 px-6 text-center text-slate-400">Belum ada kolom input formulir di tahapan ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        @if($isSuperAdmin)
            <!-- Tab: Tong Sampah Kolom Input Formulir -->
            <div id="formTabContent-test_fields" class="form-tab-content p-8 space-y-6 {{ $activeTab === 'test_fields' ? '' : 'hidden' }}">
                <div class="bg-amber-50/80 border border-amber-200 rounded-2xl p-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="archive" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm text-amber-950">Tong Sampah: Kolom Input Formulir</h3>
                            <p class="text-[11px] text-amber-800">Menampilkan kolom input formulir yang diarsipkan / berstatus testing. Data ini diisolasi dan tidak muncul pada langkah pendaftaran calon murid baru.</p>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-100 rounded-2xl shadow-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] text-slate-500 font-bold uppercase tracking-wider bg-slate-50/80">
                                <th class="py-3.5 px-6 whitespace-nowrap">Langkah / Tahapan</th>
                                <th class="py-3.5 px-6 whitespace-nowrap">Label Kolom</th>
                                <th class="py-3.5 px-6 whitespace-nowrap">Key Database</th>
                                <th class="py-3.5 px-6 whitespace-nowrap">Tipe Form</th>
                                <th class="py-3.5 px-6 whitespace-nowrap">Berlaku Untuk</th>
                                <th class="py-3.5 px-6 text-center whitespace-nowrap">Status</th>
                                <th class="py-3.5 px-6 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs divide-y divide-slate-100/80">
                            @forelse($trashedFields as $field)
                                <tr class="hover:bg-slate-50/40 transition">
                                    <td class="py-4 px-6 align-middle whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200/60">
                                            <i data-lucide="folder" class="w-3.5 h-3.5 text-slate-500"></i>
                                            {{ $field->step?->title ?? 'Tahapan #' . $field->form_step_id }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 align-middle whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="font-extrabold text-xs text-slate-800 tracking-tight">{{ $field->label }}</span>
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                                <i data-lucide="lock" class="w-3 h-3 text-amber-600"></i> Terkunci
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 align-middle whitespace-nowrap font-mono text-xs text-slate-600">
                                        {{ $field->field_name }}
                                    </td>
                                    <td class="py-4 px-6 align-middle whitespace-nowrap font-semibold text-brand-emerald uppercase">
                                        {{ $field->type }}
                                    </td>
                                    <td class="py-4 px-6 align-middle whitespace-nowrap space-x-1 space-y-1">
                                        @forelse($field->units as $u)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-100">
                                                {{ $u->code ?: $u->name }}
                                            </span>
                                        @empty
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-extrabold bg-slate-50 text-slate-500 border border-slate-200">
                                                Global
                                            </span>
                                        @endforelse
                                    </td>
                                    <td class="py-4 px-6 align-middle text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Di Tong Sampah
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 align-middle text-right whitespace-nowrap">
                                        <button type="button" onclick="confirmRestoreField('{{ addslashes($field->label) }}', '{{ route('admin.spmb-settings.form.fields.restore', $field->id) }}')" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-emerald-300 text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-600 hover:border-emerald-600 hover:text-white transition shadow-2xs cursor-pointer" title="Pulihkan Kolom">
                                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                            <span>Pulihkan</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400 text-xs">Tong sampah kolom formulir kosong.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</div>

<!-- Modal A: Add Step Modal -->
<div id="addStepModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden">
        <div class="bg-brand-emerald text-white px-6 py-4 flex justify-between items-center">
            <h3 class="font-extrabold text-base flex items-center gap-1.5">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Tahapan Formulir
            </h3>
            <button onclick="closeAddStepModal()" class="text-white hover:text-brand-yellow font-bold text-lg">&times;</button>
        </div>
        <form action="{{ route('admin.spmb-settings.form.steps.store') }}?unit_id={{ $selectedUnitId }}" method="POST" hx-boost="false" class="p-6 space-y-4">
            @csrf
            @if($errors->any() && session('failed_modal') && session('failed_modal') === 'step_create')
                <div class="spmb-form-errors mx-6 mt-4 text-xs text-red-650 bg-red-50 p-3.5 rounded-xl border border-red-200 font-semibold space-y-1">
                    @foreach($errors->all() as $error)
                        <p>⚠️ {{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nama Tahapan*</label>
                <input type="text" name="title" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm" placeholder="Contoh: Dokumen Pendukung">
            </div>
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Berlaku Untuk Unit Sekolah</label>
                    <button type="button" onclick="toggleSelectAllUnits(this)" class="text-[10px] text-brand-emerald font-extrabold hover:underline">Pilih Semua</button>
                </div>
                <div class="grid grid-cols-2 gap-3 bg-slate-50 border border-slate-200 p-3.5 rounded-xl">
                    @foreach($units as $unit)
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="spmb_unit_ids[]" value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'checked' : '' }} class="rounded text-brand-emerald focus:ring-brand-emerald w-4 h-4 border-slate-350">
                            <span class="text-xs font-semibold text-slate-750">{{ $unit->name }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-[10px] text-slate-450 mt-1.5">*Kosongkan jika ingin berlaku secara Global (Semua Unit).</p>
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
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden">
        <div class="bg-brand-emerald text-white px-6 py-4 flex justify-between items-center">
            <h3 class="font-extrabold text-base flex items-center gap-1.5">
                <i data-lucide="edit-3" class="w-4 h-4"></i> Edit Tahapan Formulir
            </h3>
            <button onclick="closeEditStepModal()" class="text-white hover:text-brand-yellow font-bold text-lg">&times;</button>
        </div>
        <form id="editStepForm" method="POST" hx-boost="false" class="p-6 space-y-4">
            @csrf
            @if($errors->any() && session('failed_modal') && str_starts_with(session('failed_modal'), 'step_edit_'))
                <div class="spmb-form-errors mx-6 mt-4 text-xs text-red-655 bg-red-50 p-3.5 rounded-xl border border-red-200 font-semibold space-y-1">
                    @foreach($errors->all() as $error)
                        <p>⚠️ {{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nama Tahapan*</label>
                <input type="text" id="edit-step-title" name="title" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
            </div>
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Berlaku Untuk Unit Sekolah</label>
                    <button type="button" onclick="toggleSelectAllUnits(this)" class="text-[10px] text-brand-emerald font-extrabold hover:underline">Pilih Semua</button>
                </div>
                <div class="grid grid-cols-2 gap-3 bg-slate-50 border border-slate-200 p-3.5 rounded-xl">
                    @foreach($units as $unit)
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="spmb_unit_ids[]" value="{{ $unit->id }}" class="rounded text-brand-emerald focus:ring-brand-emerald w-4 h-4 border-slate-350">
                            <span class="text-xs font-semibold text-slate-750">{{ $unit->name }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-[10px] text-slate-450 mt-1.5">*Kosongkan jika ingin berlaku secara Global (Semua Unit).</p>
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
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden">
        <div class="bg-brand-emerald text-white px-6 py-4 flex justify-between items-center">
            <h3 class="font-extrabold text-base flex items-center gap-1.5">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Kolom Input
            </h3>
            <button onclick="closeAddFieldModal()" class="text-white hover:text-brand-yellow font-bold text-lg">&times;</button>
        </div>
        <form action="{{ route('admin.spmb-settings.form.fields.store') }}?unit_id={{ $selectedUnitId }}" method="POST" hx-boost="false" class="p-6 space-y-4">
            @csrf
            @if($errors->any() && session('failed_modal') && str_starts_with(session('failed_modal'), 'field_create_'))
                <div class="spmb-form-errors mx-6 mt-4 text-xs text-red-655 bg-red-50 p-3.5 rounded-xl border border-red-200 font-semibold space-y-1">
                    @foreach($errors->all() as $error)
                        <p>⚠️ {{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <input type="hidden" id="add-field-step-id" name="form_step_id">
            
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Label Input (Dibaca Pendaftar)*</label>
                <input type="text" id="add-field-label" name="label" oninput="autoGenerateFieldName(this.value, 'add-field-name')" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm" placeholder="Contoh: Golongan Darah">
            </div>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Key Database / Nama Kolom (Unik)*</label>
                    <span class="text-[10px] text-emerald-700 font-extrabold bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">⚡ Otomatis dibuat dari Label</span>
                </div>
                <input type="text" id="add-field-name" name="field_name" oninput="this.dataset.manuallyEdited = 'true'" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm" placeholder="Contoh: golongan_darah">
                <p class="text-[11px] text-slate-500 mt-1.5 leading-relaxed">
                    💡 <strong>Tips:</strong> Key ini adalah identitas teknis untuk menyimpan data. Anda <strong>bebas membuat nama apa saja</strong> (gunakan huruf kecil & underscore <code>_</code>). Contoh: <code>golongan_darah</code>, <code>anak_ke</code>, <code>riwayat_penyakit</code>. Sistem otomatis menyimpannya.
                </p>
            </div>
            @if(auth()->user()->isUnitAdmin())
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3.5 flex items-center gap-2.5">
                    <i data-lucide="shield" class="w-5 h-5 text-brand-emerald flex-shrink-0"></i>
                    <div>
                        <p class="text-xs font-bold text-slate-800">Khusus Unit: {{ auth()->user()->spmbUnit?->name ?? 'Unit Anda' }}</p>
                        <p class="text-[10px] text-slate-500">Kolom input ini otomatis berlaku khusus untuk unit sekolah Anda.</p>
                    </div>
                    <input type="hidden" name="spmb_unit_ids[]" value="{{ auth()->user()->spmb_unit_id }}">
                </div>
            @else
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Berlaku Untuk Unit Sekolah</label>
                        <button type="button" onclick="toggleSelectAllUnits(this)" class="text-[10px] text-brand-emerald font-extrabold hover:underline cursor-pointer">Pilih Semua</button>
                    </div>
                    <div class="grid grid-cols-2 gap-3 bg-slate-50 border border-slate-200 p-3.5 rounded-xl">
                        @foreach($units as $unit)
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" name="spmb_unit_ids[]" value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'checked' : '' }} class="rounded text-brand-emerald focus:ring-brand-emerald w-4 h-4 border-slate-350">
                                <span class="text-xs font-semibold text-slate-750">{{ $unit->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-[10px] text-slate-450 mt-1.5">*Kosongkan jika ingin berlaku secara <strong>Global</strong> (Semua Unit).</p>
                </div>
            @endif
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Jenis Form (Tipe)*</label>
                <select name="type" id="add-field-type" onchange="toggleOptionsInput('add')" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
                    <option value="text">Input Text (Teks Biasa)</option>
                    <option value="number">Input Number (Angka NIK/Telp)</option>
                    <option value="email">Input Email</option>
                    <option value="date">Input Date (Tanggal)</option>
                    <option value="select">Select Dropdown (Pilihan)</option>
                    <option value="checkbox">Checkbox (Pilihan Centang / Ganda)</option>
                    <option value="radio">Radio Button (Pilihan Tunggal)</option>
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
                <button type="button" onclick="closeAddFieldModal()" class="border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition cursor-pointer">Kembali</button>
                <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2 rounded-xl text-xs font-bold transition shadow-md cursor-pointer">Simpan Kolom</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal D: Edit Field Modal -->
<div id="editFieldModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden">
        <div class="bg-brand-emerald text-white px-6 py-4 flex justify-between items-center">
            <h3 class="font-extrabold text-base flex items-center gap-1.5">
                <i data-lucide="edit-3" class="w-4 h-4"></i> Edit Kolom Input
            </h3>
            <button onclick="closeEditFieldModal()" class="text-white hover:text-brand-yellow font-bold text-lg">&times;</button>
        </div>
        <form id="editFieldForm" method="POST" hx-boost="false" class="p-6 space-y-4">
            @csrf
            @if($errors->any() && session('failed_modal') && str_starts_with(session('failed_modal'), 'field_edit_'))
                <div class="spmb-form-errors mx-6 mt-4 text-xs text-red-655 bg-red-50 p-3.5 rounded-xl border border-red-200 font-semibold space-y-1">
                    @foreach($errors->all() as $error)
                        <p>⚠️ {{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Label Input (Dibaca Pendaftar)*</label>
                <input type="text" id="edit-field-label" name="label" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
            </div>
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Key Database / Nama Kolom (Unik)*</label>
                </div>
                <input type="text" id="edit-field-name" name="field_name" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
                <p class="text-[11px] text-slate-500 mt-1.5 leading-relaxed">
                    💡 <strong>Catatan:</strong> Gunakan huruf kecil dan garis bawah (contoh: <code>riwayat_alergi</code>). Kolom sistem utama terkunci demi integritas data.
                </p>
            </div>
            @if(auth()->user()->isUnitAdmin())
                <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3.5 flex items-center gap-2.5">
                    <i data-lucide="shield" class="w-5 h-5 text-brand-emerald flex-shrink-0"></i>
                    <div>
                        <p class="text-xs font-bold text-slate-800">Khusus Unit: {{ auth()->user()->spmbUnit?->name ?? 'Unit Anda' }}</p>
                        <p class="text-[10px] text-slate-500">Kolom input ini berlaku khusus untuk unit sekolah Anda.</p>
                    </div>
                    <input type="hidden" name="spmb_unit_ids[]" value="{{ auth()->user()->spmb_unit_id }}">
                </div>
            @else
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Berlaku Untuk Unit Sekolah</label>
                        <button type="button" onclick="toggleSelectAllUnits(this)" class="text-[10px] text-brand-emerald font-extrabold hover:underline cursor-pointer">Pilih Semua</button>
                    </div>
                    <div class="grid grid-cols-2 gap-3 bg-slate-50 border border-slate-200 p-3.5 rounded-xl">
                        @foreach($units as $unit)
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" name="spmb_unit_ids[]" value="{{ $unit->id }}" class="rounded text-brand-emerald focus:ring-brand-emerald w-4 h-4 border-slate-350">
                                <span class="text-xs font-semibold text-slate-750">{{ $unit->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="text-[10px] text-slate-450 mt-1.5">*Kosongkan jika ingin berlaku secara <strong>Global</strong> (Semua Unit).</p>
                </div>
            @endif
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Jenis Form (Tipe)*</label>
                <select name="type" id="edit-field-type" onchange="toggleOptionsInput('edit')" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
                    <option value="text">Input Text (Teks Biasa)</option>
                    <option value="number">Input Number (Angka NIK/Telp)</option>
                    <option value="email">Input Email</option>
                    <option value="date">Input Date (Tanggal)</option>
                    <option value="select">Select Dropdown (Pilihan)</option>
                    <option value="checkbox">Checkbox (Pilihan Centang / Ganda)</option>
                    <option value="radio">Radio Button (Pilihan Tunggal)</option>
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

    @if($isSuperAdmin)
        <!-- Modal Konfirmasi Pindahkan ke Tong Sampah -->
        <div id="trashFieldModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0">
                            <i data-lucide="archive" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-base text-slate-800">Pindahkan ke Tong Sampah?</h3>
                            <p class="text-xs text-slate-500">Arsipkan kolom input tanpa menghapus data pendaftar.</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed mb-6">
                        Apakah Anda yakin ingin memindahkan kolom "<strong id="trashFieldLabel" class="text-amber-800 font-extrabold"></strong>" ke <strong>Tong Sampah</strong>? Kolom ini tidak akan muncul pada formulir pendaftaran calon murid baru.
                    </p>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeTrashFieldModal()" class="px-4 py-2 rounded-xl text-xs font-bold border border-slate-200 text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                            Batal
                        </button>
                        <form id="trashFieldForm" method="POST" class="inline">
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
        <div id="restoreFieldModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">
                            <i data-lucide="rotate-ccw" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-base text-slate-800">Pulihkan Kolom Formulir?</h3>
                            <p class="text-xs text-slate-500">Kembalikan kolom input ke daftar utama aktif.</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed mb-6">
                        Apakah Anda yakin ingin memulihkan kolom "<strong id="restoreFieldLabel" class="text-emerald-800 font-extrabold"></strong>" dari Tong Sampah ke formulir aktif?
                    </p>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeRestoreFieldModal()" class="px-4 py-2 rounded-xl text-xs font-bold border border-slate-200 text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                            Batal
                        </button>
                        <form id="restoreFieldForm" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm cursor-pointer">
                                Pulihkan Kolom
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

<!-- Hidden Delete Form -->
<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    // Trash Dropdown Control
    function toggleFormTrashDropdown() {
        const menu = document.getElementById('formTrashDropdownMenu');
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }

    function closeFormTrashDropdown() {
        const menu = document.getElementById('formTrashDropdownMenu');
        if (menu) {
            menu.classList.add('hidden');
        }
    }

    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('formTrashDropdownWrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            closeFormTrashDropdown();
        }
    });

    // Confirmation Modal Controls for Trash & Restore
    function confirmTrashField(label, actionUrl) {
        const labelEl = document.getElementById('trashFieldLabel');
        const formEl = document.getElementById('trashFieldForm');
        const modalEl = document.getElementById('trashFieldModal');
        if (labelEl) labelEl.innerText = label;
        if (formEl) formEl.setAttribute('action', actionUrl);
        if (modalEl) modalEl.classList.remove('hidden');
    }

    function closeTrashFieldModal() {
        const modalEl = document.getElementById('trashFieldModal');
        if (modalEl) modalEl.classList.add('hidden');
    }

    function confirmRestoreField(label, actionUrl) {
        const labelEl = document.getElementById('restoreFieldLabel');
        const formEl = document.getElementById('restoreFieldForm');
        const modalEl = document.getElementById('restoreFieldModal');
        if (labelEl) labelEl.innerText = label;
        if (formEl) formEl.setAttribute('action', actionUrl);
        if (modalEl) modalEl.classList.remove('hidden');
    }

    function closeRestoreFieldModal() {
        const modalEl = document.getElementById('restoreFieldModal');
        if (modalEl) modalEl.classList.add('hidden');
    }

    // Tab switching memory
    function switchFormTab(tabId) {
        const panel = document.getElementById('formTabContent-' + tabId);
        if (!panel) return;

        document.querySelectorAll('.form-tab-content').forEach(el => el.classList.add('hidden'));
        panel.classList.remove('hidden');

        document.querySelectorAll('.form-tab-btn').forEach(btn => {
            btn.className = "form-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50 cursor-pointer";
        });
        
        const activeBtn = document.getElementById('formTabBtn-' + tabId);
        if (activeBtn) {
            activeBtn.className = "form-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow cursor-pointer";
        }

        // Highlight Tong Sampah dropdown and subtabs
        const isTrash = (tabId === 'test_fields');
        const trashDropdownBtn = document.getElementById('formTrashDropdownBtn');
        if (trashDropdownBtn) {
            if (isTrash) {
                trashDropdownBtn.className = "px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-xs cursor-pointer";
                const tIcon = trashDropdownBtn.querySelector('[data-lucide="trash-2"]');
                if (tIcon) tIcon.className = "w-4 h-4 text-white";
                const cIcon = trashDropdownBtn.querySelector('[data-lucide="chevron-down"]');
                if (cIcon) cIcon.className = "w-3.5 h-3.5 text-white/80";
            } else {
                trashDropdownBtn.className = "px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200/80 rounded-xl text-xs font-bold transition flex items-center gap-2 shadow-xs cursor-pointer";
                const tIcon = trashDropdownBtn.querySelector('[data-lucide="trash-2"]');
                if (tIcon) tIcon.className = "w-4 h-4 text-amber-600";
                const cIcon = trashDropdownBtn.querySelector('[data-lucide="chevron-down"]');
                if (cIcon) cIcon.className = "w-3.5 h-3.5 text-slate-400";
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
        
        // Update URL query parameter to sync with server
        const unitId = "{{ $selectedUnitId ?? '' }}";
        const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?tab=' + tabId + (unitId !== '' ? '&unit_id=' + unitId : '');
        window.history.replaceState({ path: newUrl }, '', newUrl);

        localStorage.setItem('spmb_form_active_tab', tabId);

        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Tab state is handled server-side via Laravel view variable $activeTab
    });

    // Clear validation errors
    function clearFormErrors() {
        document.querySelectorAll('.spmb-form-errors').forEach(el => {
            el.classList.add('hidden');
        });
    }

    // Step Modals
    function openAddStepModal() {
        clearFormErrors();
        document.getElementById('addStepModal').classList.remove('hidden');
        updateToggleUnitsText('addStepModal');
    }
    function closeAddStepModal() {
        clearFormErrors();
        document.getElementById('addStepModal').classList.add('hidden');
    }

    function openEditStepModal(step) {
        clearFormErrors();
        document.getElementById('edit-step-title').value = step.title;
        document.getElementById('edit-step-order').value = step.order;
        
        // Reset and check spmb_unit_ids checkboxes
        document.querySelectorAll('#editStepModal input[name="spmb_unit_ids[]"]').forEach(cb => cb.checked = false);
        if (step.units) {
            step.units.forEach(u => {
                const cb = document.querySelector(`#editStepModal input[name="spmb_unit_ids[]"][value="${u.id}"]`);
                if (cb) cb.checked = true;
            });
        }

        document.getElementById('editStepForm').setAttribute('action', '/admin/spmb-settings/form/steps/' + step.id + '?unit_id={{ $selectedUnitId }}');
        updateToggleUnitsText('editStepModal');
        document.getElementById('editStepModal').classList.remove('hidden');
    }
    function closeEditStepModal() {
        clearFormErrors();
        document.getElementById('editStepModal').classList.add('hidden');
    }

    function deleteStepItem(name, url) {
        confirmDelete(url + '?unit_id={{ $selectedUnitId }}', `Apakah Anda yakin ingin menghapus tahapan "${name}"? Seluruh kolom input di dalam tahapan ini juga akan ikut terhapus.`);
    }

    // Auto-generate Field Name (Key Database) from Label
    function autoGenerateFieldName(labelValue, targetInputId, force = false) {
        const targetInput = document.getElementById(targetInputId);
        if (!targetInput) return;
        if (targetInput.dataset.manuallyEdited === 'true' && !force) return;
        
        let slug = labelValue
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s_]/g, '') // Hapus simbol aneh
            .replace(/\s+/g, '_')         // Ganti spasi dengan underscore
            .replace(/_+/g, '_');         // Hapus underscore ganda
            
        targetInput.value = slug;
    }

    // Field Modals
    function openAddFieldModal(stepId) {
        clearFormErrors();
        document.getElementById('add-field-step-id').value = stepId;
        const labelInput = document.getElementById('add-field-label');
        const nameInput = document.getElementById('add-field-name');
        if (labelInput) labelInput.value = '';
        if (nameInput) {
            nameInput.value = '';
            nameInput.dataset.manuallyEdited = 'false';
        }
        document.getElementById('addFieldModal').classList.remove('hidden');
        toggleOptionsInput('add');
        updateToggleUnitsText('addFieldModal');
    }
    function closeAddFieldModal() {
        clearFormErrors();
        document.getElementById('addFieldModal').classList.add('hidden');
    }

    function openEditFieldModal(field) {
        clearFormErrors();
        document.getElementById('edit-field-label').value = field.label;
        document.getElementById('edit-field-name').value = field.field_name;
        document.getElementById('edit-field-type').value = field.type;
        
        // Reset and check spmb_unit_ids checkboxes
        document.querySelectorAll('#editFieldModal input[name="spmb_unit_ids[]"]').forEach(cb => cb.checked = false);
        if (field.units) {
            field.units.forEach(u => {
                const cb = document.querySelector(`#editFieldModal input[name="spmb_unit_ids[]"][value="${u.id}"]`);
                if (cb) cb.checked = true;
            });
        }

        document.getElementById('edit-field-options').value = field.options || '';
        document.getElementById('edit-field-required').checked = (field.is_required === 1 || field.is_required === '1' || field.is_required === true);
        document.getElementById('edit-field-order').value = field.order;
        document.getElementById('editFieldForm').setAttribute('action', '/admin/spmb-settings/form/fields/' + field.id + '?unit_id={{ $selectedUnitId }}');
        updateToggleUnitsText('editFieldModal');
        
        // System fields protection
        const systemFields = ['candidate_name', 'spmb_period_id', 'spmb_wave_id', 'spmb_type_id', 'spmb_class_program_id'];
        const nameInput = document.getElementById('edit-field-name');
        const typeInput = document.getElementById('edit-field-type');
        
        if (systemFields.includes(field.field_name)) {
            nameInput.readOnly = true;
            nameInput.title = "Kolom sistem utama tidak boleh diubah key databasenya.";
            typeInput.disabled = true;
            typeInput.title = "Kolom sistem utama tidak boleh diubah tipenya.";
            
            // Add a hidden input to submit type when disabled
            let hiddenType = document.getElementById('edit-field-type-hidden');
            if (!hiddenType) {
                hiddenType = document.createElement('input');
                hiddenType.type = 'hidden';
                hiddenType.id = 'edit-field-type-hidden';
                hiddenType.name = 'type';
                document.getElementById('editFieldForm').appendChild(hiddenType);
            }
            hiddenType.value = field.type;
        } else {
            nameInput.readOnly = false;
            nameInput.title = "";
            typeInput.disabled = false;
            typeInput.title = "";
            
            const hiddenType = document.getElementById('edit-field-type-hidden');
            if (hiddenType) {
                hiddenType.parentNode.removeChild(hiddenType);
            }
        }
        
        document.getElementById('editFieldModal').classList.remove('hidden');
        toggleOptionsInput('edit');
    }
    function closeEditFieldModal() {
        clearFormErrors();
        document.getElementById('editFieldModal').classList.add('hidden');
    }

    function deleteFieldItem(name, url) {
        confirmDelete(url + '?unit_id={{ $selectedUnitId }}', `Apakah Anda yakin ingin menghapus kolom input "${name}"?`);
    }

    // Toggle options field visibility for 'select', 'checkbox', 'radio' types
    function toggleOptionsInput(prefix) {
        const typeEl = document.getElementById(prefix + '-field-type');
        const wrapperEl = document.getElementById(prefix + '-options-wrapper');
        const optionsInput = document.getElementById(prefix + '-field-options');
        
        if (typeEl && (typeEl.value === 'select' || typeEl.value === 'checkbox' || typeEl.value === 'radio')) {
            wrapperEl.classList.remove('hidden');
            optionsInput.required = (typeEl.value === 'select' || typeEl.value === 'radio');
        } else {
            wrapperEl.classList.add('hidden');
            optionsInput.required = false;
        }
    }

    function toggleSelectAllUnits(button) {
        const parent = button.parentElement;
        const formGroup = parent.parentElement;
        const checkboxes = formGroup.querySelectorAll('input[name="spmb_unit_ids[]"]');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(cb => cb.checked = !allChecked);
        button.textContent = allChecked ? 'Pilih Semua' : 'Kosongkan';
    }

    function updateToggleUnitsText(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        const btn = modal.querySelector('button[onclick="toggleSelectAllUnits(this)"]');
        if (!btn) return;
        const checkboxes = modal.querySelectorAll('input[name="spmb_unit_ids[]"]');
        if (checkboxes.length === 0) return;
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        btn.textContent = allChecked ? 'Kosongkan' : 'Pilih Semua';
    }

    // Click outside handlers to close modals
    document.getElementById('addStepModal').addEventListener('click', function(e) {
        if (e.target === this) closeAddStepModal();
    });
    document.getElementById('editStepModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditStepModal();
    });
    document.getElementById('addFieldModal').addEventListener('click', function(e) {
        if (e.target === this) closeAddFieldModal();
    });
    document.getElementById('editFieldModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditFieldModal();
    });
    const trashFieldModalEl = document.getElementById('trashFieldModal');
    if (trashFieldModalEl) {
        trashFieldModalEl.addEventListener('click', function(e) {
            if (e.target === this) closeTrashFieldModal();
        });
    }
    const restoreFieldModalEl = document.getElementById('restoreFieldModal');
    if (restoreFieldModalEl) {
        restoreFieldModalEl.addEventListener('click', function(e) {
            if (e.target === this) closeRestoreFieldModal();
        });
    }

    // Auto-reopen modal if validation failed on redirect
    @if(session('failed_modal'))
        document.addEventListener("DOMContentLoaded", function() {
            let failed = "{{ session('failed_modal') }}";
            if (failed.startsWith('step_create')) {
                switchFormTab('crud_steps');
                openAddStepModal();
            } else if (failed.startsWith('step_edit_')) {
                switchFormTab('crud_steps');
                let id = failed.replace('step_edit_', '');
                // Find order and title from steps array or render it
                let title = "{{ old('title') }}";
                let order = "{{ old('order') }}";
                openEditStepModal({ id: id, title: title, order: order });
            } else if (failed.startsWith('field_create_')) {
                let stepId = failed.replace('field_create_', '');
                switchFormTab('step_' + stepId);
                openAddFieldModal(stepId);
                // Fill fields with old inputs if exists
                document.getElementsByName('label')[0].value = "{{ old('label') }}";
                document.getElementsByName('field_name')[0].value = "{{ old('field_name') }}";
                document.getElementById('add-field-type').value = "{{ old('type') }}";
                document.getElementById('add-field-options').value = "{{ old('options') }}";
                document.getElementById('add-field-required').checked = {{ old('is_required') ? 'true' : 'false' }};
                document.getElementById('add-field-order').value = "{{ old('order') }}";
                toggleOptionsInput('add');
            } else if (failed.startsWith('field_edit_')) {
                let id = failed.replace('field_edit_', '');
                // Query or load from old session input
                openEditFieldModal({
                    id: id,
                    label: "{{ old('label') }}",
                    field_name: "{{ old('field_name') }}",
                    type: "{{ old('type') }}",
                    options: "{{ old('options') }}",
                    is_required: {{ old('is_required') ? 'true' : 'false' }},
                    order: "{{ old('order') }}"
                });
            }

            // Unhide the failed errors block in the reopened modal
            document.querySelectorAll('.spmb-form-errors').forEach(el => {
                el.classList.remove('hidden');
            });
        });
    @endif

    // Escape key listener to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const stepAddModal = document.getElementById('addStepModal');
            if (stepAddModal && !stepAddModal.classList.contains('hidden')) closeAddStepModal();
            
            const stepEditModal = document.getElementById('editStepModal');
            if (stepEditModal && !stepEditModal.classList.contains('hidden')) closeEditStepModal();
            
            const fieldAddModal = document.getElementById('addFieldModal');
            if (fieldAddModal && !fieldAddModal.classList.contains('hidden')) closeAddFieldModal();
            
            const fieldEditModal = document.getElementById('editFieldModal');
            if (fieldEditModal && !fieldEditModal.classList.contains('hidden')) closeEditFieldModal();

            const tfModal = document.getElementById('trashFieldModal');
            if (tfModal && !tfModal.classList.contains('hidden')) closeTrashFieldModal();

            const rfModal = document.getElementById('restoreFieldModal');
            if (rfModal && !rfModal.classList.contains('hidden')) closeRestoreFieldModal();
        }
    });
</script>
@endsection
