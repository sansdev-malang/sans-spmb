@extends('layouts.admin')

@section('title', 'Setting UI Portal - Admin Panel')
@section('page_title', 'Setting UI Portal')

@section('content')
<style>
    /* Custom styles for Quill editor */
    .ql-toolbar.ql-snow {
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        border-color: #e2e8f0;
        background-color: #f8fafc;
        padding: 8px 12px;
    }
    .ql-container.ql-snow {
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
        border-color: #e2e8f0;
        background-color: #ffffff;
    }
    .ql-editor {
        position: relative !important;
        min-height: 180px;
        font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-size: 13px;
        color: #334155;
    }
    .ql-editor.ql-blank::before {
        position: absolute !important;
        left: 15px !important;
        right: 15px !important;
        color: #94a3b8 !important;
        font-style: italic !important;
        pointer-events: none !important;
    }
</style>
<div id="ui-settings-container" hx-boost="true" hx-target="#ui-settings-container" hx-select="#ui-settings-container" class="w-full space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h1 class="text-xl font-extrabold text-slate-800">Pengaturan Tampilan Portal Pendaftaran (UI Portal)</h1>
        <p class="text-xs text-slate-500 mt-1">
            @if($isSuperAdmin)
                Mengustomisasi logo yayasan, warna tema, banner slider, tautan footer, dan konten informasi seluruh jenjang sekolah.
            @else
                Mengustomisasi deskripsi, keunggulan, persyaratan masuk, alur pendaftaran, dan berkas brosur/lampiran untuk Jenjang {{ $units->first()->name ?? '' }}.
            @endif
        </p>
    </div>

    <!-- Form Configuration -->
    <form id="ui-settings-form" action="{{ route('admin.ui-settings.save') }}" method="POST" enctype="multipart/form-data" hx-boost="false" class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        @csrf
        @php
            $currentTab = $activeTab ?? request()->get('tab', $isSuperAdmin ? 'global' : ('unit-' . strtolower($units->first()->code ?? '')));
        @endphp
        <input type="hidden" name="active_tab" id="active-tab-input" value="{{ $currentTab }}">
        
        <!-- Navigation Tabs -->
        <div class="bg-slate-50/75 border-b border-slate-100 px-6 flex flex-wrap gap-1">
            @if($isSuperAdmin)
                <button type="button" onclick="switchSettingsTab('global')" id="tab-btn-global" 
                    class="tab-button border-b-2 py-4 px-6 text-xs transition focus:outline-none {{ $currentTab === 'global' ? 'border-brand-emerald text-brand-emerald font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-700 font-bold' }}">
                    Global / Banner
                </button>
                <button type="button" onclick="switchSettingsTab('identity')" id="tab-btn-identity" 
                    class="tab-button border-b-2 py-4 px-6 text-xs transition focus:outline-none {{ $currentTab === 'identity' ? 'border-brand-emerald text-brand-emerald font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-700 font-bold' }}">
                    Identitas Sekolah
                </button>
            @endif

            @foreach($units as $unit)
                @php $unitCode = strtolower($unit->code); @endphp
                <button type="button" onclick="switchSettingsTab('unit-{{ $unitCode }}')" id="tab-btn-unit-{{ $unitCode }}" 
                    class="tab-button border-b-2 py-4 px-6 text-xs transition focus:outline-none {{ $currentTab === 'unit-' . $unitCode ? 'border-brand-emerald text-brand-emerald font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-700 font-bold' }}">
                    Jenjang {{ $unit->name }}
                </button>
            @endforeach

            <button type="button" onclick="switchSettingsTab('testimonials')" id="tab-btn-testimonials" 
                class="tab-button border-b-2 py-4 px-6 text-xs transition focus:outline-none flex items-center gap-2 {{ $currentTab === 'testimonials' ? 'border-brand-emerald text-brand-emerald font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-700 font-bold' }}">
                <i data-lucide="message-square-quote" class="w-4 h-4"></i>
                <span>Testimoni & Review</span>
                <span class="px-1.5 py-0.5 text-[9px] font-black rounded-full bg-emerald-100 text-emerald-800">{{ $testimonials->count() }}</span>
            </button>
        </div>

        <!-- Tabs Content Container -->
        <div class="p-8">
            
            @if($isSuperAdmin)
            <!-- TAB 1: GLOBAL / BANNER -->
            <div id="tab-content-global" class="tab-panel space-y-6 {{ $currentTab === 'global' ? '' : 'hidden' }}">
                <!-- Section 1: Hero Banner Text -->
                <div class="space-y-4">
                    <h3 class="text-xs font-extrabold text-brand-emerald uppercase tracking-wider">A. Konten Teks Hero</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Judul Utama Hero (Hero Title)</label>
                            <input type="text" name="portal_hero_title" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-semibold"
                                value="{{ $settings['portal_hero_title'] }}">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Sub-judul Deskripsi (Hero Description)</label>
                            <textarea name="portal_hero_description" rows="3" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-medium">{{ $settings['portal_hero_description'] }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Colors & Theme Mode -->
                <div class="space-y-4 pt-6 border-t border-slate-100">
                    <h3 class="text-xs font-extrabold text-brand-emerald uppercase tracking-wider">B. Skema Warna & Mode Layout</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Warna Primer Portal</label>
                            <div class="flex gap-3 items-center">
                                <input type="color" name="portal_primary_color" id="primary-picker" onchange="updateHexLabel('primary')" class="h-10 w-12 rounded border border-slate-300 cursor-pointer" value="{{ $settings['portal_primary_color'] }}">
                                <span id="primary-hex" class="text-xs font-mono text-slate-500 font-bold">{{ $settings['portal_primary_color'] }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Warna Sekunder Portal</label>
                            <div class="flex gap-3 items-center">
                                <input type="color" name="portal_secondary_color" id="secondary-picker" onchange="updateHexLabel('secondary')" class="h-10 w-12 rounded border border-slate-300 cursor-pointer" value="{{ $settings['portal_secondary_color'] }}">
                                <span id="secondary-hex" class="text-xs font-mono text-slate-500 font-bold">{{ $settings['portal_secondary_color'] }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Mode Layout Standard (Default)</label>
                            <select name="portal_layout_mode" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-bold">
                                <option value="light" {{ $settings['portal_layout_mode'] === 'light' ? 'selected' : '' }}>Light Mode (Terang)</option>
                                <option value="dark" {{ $settings['portal_layout_mode'] === 'dark' ? 'selected' : '' }}>Dark Mode (Gelap)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Slider Images -->
                <div class="space-y-4 pt-6 border-t border-slate-100">
                    <h3 class="text-xs font-extrabold text-brand-emerald uppercase tracking-wider">C. Slide Gambar Hero (Slide Showcase)</h3>
                    
                    <!-- File input -->
                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Tambah Slide Baru</label>
                        <input type="file" name="school_hero_images[]" multiple accept="image/*" class="text-xs text-slate-500 w-full file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" />
                        <p class="text-[9px] text-slate-400">Anda dapat memilih beberapa gambar sekaligus (JPG, PNG, WebP maks 3MB per gambar)</p>
                    </div>

                    <!-- Current slides manager -->
                    <div class="space-y-2 mt-4">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Daftar Slide Saat Ini (Centang untuk menghapus)</label>
                        @php
                            $heroImages = json_decode($settings['school_hero_images'], true) ?: [];
                        @endphp

                        @if(count($heroImages) > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                @foreach($heroImages as $index => $img)
                                    <div class="relative border border-slate-200 rounded-2xl overflow-hidden group bg-slate-50">
                                        <img src="{{ $img }}" alt="Slide {{ $index }}" class="h-24 w-full object-cover" />
                                        
                                        <!-- Hover Delete Layer -->
                                        <div class="absolute inset-0 bg-slate-900/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                            <label class="flex items-center gap-1.5 text-white font-extrabold text-[10px] cursor-pointer bg-red-600/90 py-1 px-2.5 rounded-lg">
                                                <input type="checkbox" name="delete_hero_images[]" value="{{ $img }}" class="rounded text-red-600 focus:ring-red-500 w-3 h-3">
                                                Hapus
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-[10px] text-slate-400 font-bold bg-slate-50 border border-dashed border-slate-200 rounded-xl p-4 text-center">
                                Belum ada gambar hero khusus (menggunakan gambar default standard).
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- TAB 2: IDENTITAS SEKOLAH -->
            <div id="tab-content-identity" class="tab-panel space-y-6 {{ $currentTab === 'identity' ? '' : 'hidden' }}">
                <!-- School Name & Tagline -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Nama Instansi Sekolah / Brand</label>
                        <input type="text" name="school_name" {{ $isSuperAdmin ? 'required' : '' }} class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-bold"
                            value="{{ $settings['school_name'] }}">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Tagline Instansi / Semboyan</label>
                        <input type="text" name="school_tagline" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-bold"
                            value="{{ $settings['school_tagline'] }}" placeholder="Contoh: Yayasan Pendidikan Anak Saleh">
                    </div>
                </div>

                <!-- Brand assets -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
                    <!-- Logo Upload -->
                    <div class="border border-slate-200 rounded-2xl p-6 flex flex-col gap-3 bg-slate-50/20">
                        <div class="flex items-center justify-between gap-2">
                            <div class="text-xs font-extrabold text-slate-700">Logo Instansi Sekolah</div>
                            @if(!empty($settings['school_logo_url']))
                                <button type="button" onclick="clearExistingAsset('school_logo_url')" class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-2 py-1 text-[9px] font-bold text-red-600 hover:bg-red-100 transition" title="Hapus logo yang tersimpan">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    Hapus
                                </button>
                            @endif
                        </div>
                        
                        <!-- Preview container -->
                        <div class="h-24 w-full flex items-center justify-center border border-slate-150 rounded-xl bg-white p-3" id="logo-preview-box">
                            @if(!empty($settings['school_logo_url']))
                                <img src="{{ $settings['school_logo_url'] }}" alt="Logo" class="max-h-full object-contain" />
                            @else
                                <span class="text-[10px] text-slate-400 font-bold">Belum Ada Logo</span>
                            @endif
                        </div>

                        <input type="file" name="school_logo" accept="image/*" onchange="previewSelectedImage(event, 'logo-preview-box')" class="text-[10px] text-slate-500 w-full file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" />
                        <p class="text-[9px] text-slate-400">Maks file 2MB (PNG, SVG, atau JPG)</p>
                    </div>

                    <!-- Favicon Upload -->
                    <div class="border border-slate-200 rounded-2xl p-6 flex flex-col gap-3 bg-slate-50/20">
                        <div class="flex items-center justify-between gap-2">
                            <div class="text-xs font-extrabold text-slate-700">Favicon Browser</div>
                            @if(!empty($settings['school_favicon_url']))
                                <button type="button" onclick="clearExistingAsset('school_favicon_url')" class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-2 py-1 text-[9px] font-bold text-red-600 hover:bg-red-100 transition" title="Hapus favicon yang tersimpan">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    Hapus
                                </button>
                            @endif
                        </div>
                        
                        <!-- Preview container -->
                        <div class="h-24 w-full flex items-center justify-center border border-slate-150 rounded-xl bg-white p-3" id="favicon-preview-box">
                            @if(!empty($settings['school_favicon_url']))
                                <img src="{{ $settings['school_favicon_url'] }}" alt="Favicon" class="max-h-full object-contain" />
                            @else
                                <span class="text-[10px] text-slate-400 font-bold">Default</span>
                            @endif
                        </div>

                        <input type="file" name="school_favicon" accept="image/*" onchange="previewSelectedImage(event, 'favicon-preview-box')" class="text-[10px] text-slate-500 w-full file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" />
                        <p class="text-[9px] text-slate-400">Maks file 2MB (.ico, .png, atau .jpg)</p>
                    </div>
                </div>

                <!-- Footer & Copyright Configuration -->
                <div class="space-y-4 pt-6 border-t border-slate-100">
                    <h4 class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Konfigurasi Footer & Hak Cipta</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Tautan Contact Us</label>
                            <input type="text" name="footer_contact_url" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-bold"
                                value="{{ $settings['footer_contact_url'] }}" placeholder="Contoh: https://sekolah.sch.id/contact">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Tautan Privacy Policy</label>
                            <input type="text" name="footer_privacy_url" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-bold"
                                value="{{ $settings['footer_privacy_url'] }}" placeholder="Contoh: # atau URL eksternal">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Tautan Terms of Service</label>
                            <input type="text" name="footer_terms_url" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-bold"
                                value="{{ $settings['footer_terms_url'] }}" placeholder="Contoh: # atau URL eksternal">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Tautan FAQ</label>
                            <input type="text" name="footer_faq_url" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-bold"
                                value="{{ $settings['footer_faq_url'] }}" placeholder="Contoh: # atau URL eksternal">
                        </div>
                    </div>

                    <div class="space-y-2 pt-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Teks Hak Cipta (Copyright)</label>
                        <input type="text" name="footer_copyright_text" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-bold"
                            value="{{ $settings['footer_copyright_text'] }}" placeholder="Contoh: © 2026 {SchoolName}. All rights reserved.">
                        <p class="text-[9px] text-slate-400">💡 Anda dapat menyertakan kode `{SchoolName}` atau `{Year}` agar digantikan secara dinamis sesuai tahun saat ini dan nama sekolah aktif.</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- DYNAMIC UNITS TABS -->
            @foreach($units as $unit)
                @php $code = strtolower($unit->code); @endphp
                <div id="tab-content-unit-{{ $code }}" class="tab-panel space-y-6 {{ $currentTab === 'unit-' . $code ? '' : 'hidden' }}">
                    <div class="bg-slate-50/50 dark:bg-slate-900/60 p-6 rounded-2xl border border-slate-150 dark:border-slate-800 space-y-6">
                        <h4 class="text-xs font-extrabold text-brand-emerald dark:text-emerald-400 uppercase tracking-wider">Pengaturan Jenjang {{ $unit->name }}</h4>
                        
                        <div class="grid grid-cols-1 gap-6">
                            <!-- 1. Deskripsi Singkat -->
                            <div class="space-y-1.5">
                                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Deskripsi Singkat Jenjang</label>
                                <textarea name="unit_{{ $code }}_desc" rows="4" required class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 rounded-xl px-4 py-3 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:focus:ring-emerald-500 font-medium leading-relaxed" placeholder="Tuliskan gambaran umum dan visi jenjang ini...">{{ $settings['unit_' . $code . '_desc'] }}</textarea>
                            </div>

                            <!-- 2. Kurikulum & Layanan Pendidikan -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Kurikulum & Layanan Pendidikan</label>
                                    <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400">1 item per baris (tekan Enter)</span>
                                </div>
                                <textarea name="unit_{{ $code }}_content" rows="4" required class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 rounded-xl px-4 py-3 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:focus:ring-emerald-500 font-medium leading-relaxed" placeholder="Contoh:&#10;Special Curriculum of Anak Saleh: Kurikulum Panca Karakter Anak Saleh (ecological system Approach...)&#10;International Curriculum: Cambridge&#10;National Curriculum: From Kemendikdasmen RI&#10;Islamic Curriculum: Madrasah Dinniyah">{{ $settings['unit_' . $code . '_content'] }}</textarea>
                                <p class="text-[9px] text-slate-400 dark:text-slate-500">💡 Anda bisa menyertakan judul dan penjelasan dengan tanda titik dua (contoh: <code>Nama Kurikulum: Penjelasan...</code>).</p>
                            </div>

                            <!-- 3. Program Unggulan -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Program Unggulan Utama</label>
                                    <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400">1 program per baris (tekan Enter)</span>
                                </div>
                                <textarea name="unit_{{ $code }}_features" rows="6" required class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 rounded-xl px-4 py-3 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:focus:ring-emerald-500 font-medium leading-relaxed" placeholder="Contoh:&#10;Panca Karakter Anak Saleh (Five Good Characters of Anak Saleh)&#10;Homebase System&#10;Multilingual School (Bahasa, English, Arabic)&#10;IT and Coding Program">{{ $settings['unit_' . $code . '_features'] }}</textarea>
                            </div>

                            <!-- 4. Syarat Pendaftaran -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Syarat & Ketentuan Pendaftaran</label>
                                    <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400">1 syarat per baris (tekan Enter)</span>
                                </div>
                                <textarea name="unit_{{ $code }}_requirements" rows="4" required class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 rounded-xl px-4 py-3 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:focus:ring-emerald-500 font-medium leading-relaxed" placeholder="Contoh:&#10;Mengisi Formulir Pendaftaran Online Lengkap di Portal SPMB&#10;Pas Foto Formal Calon Murid (Background Polos)&#10;Scan / Foto Akta Kelahiran Calon Murid&#10;Scan / Foto Kartu Keluarga (KK)&#10;Ijazah / Surat Keterangan dari Sekolah Asal (Dapat Menyusul)&#10;NISN / Kartu Identitas Anak (KIA) / Kartu Pelajar (Opsional)&#10;Dokumen Asesmen Kebutuhan Khusus / Psikologi (Jika Ada)">{{ $settings['unit_' . $code . '_requirements'] }}</textarea>
                            </div>

                            <!-- 5. Alur Pendaftaran -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Tahapan Alur Pendaftaran & Seleksi</label>
                                    <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400">1 tahapan per baris (tekan Enter)</span>
                                </div>
                                <textarea name="unit_{{ $code }}_flow" rows="4" required class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 rounded-xl px-4 py-3 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:focus:ring-emerald-500 font-medium leading-relaxed" placeholder="Contoh:&#10;Pembuatan Akun & Registrasi Awal di Portal SPMB&#10;Pembayaran Biaya Awal Pendaftaran (Enrollment Fee)&#10;Pengisian Formulir Lengkap & Unggah Dokumen Berkas&#10;Verifikasi & Validasi Berkas oleh Panitia SPMB&#10;Assessment Siswa & Sesi Ta'aruf&#10;Pengumuman Hasil Seleksi & Persetujuan Pernyataan&#10;Daftar Ulang & Penyelesaian Administrasi Akhir">{{ $settings['unit_' . $code . '_flow'] }}</textarea>
                                <p class="text-[9px] text-slate-400 dark:text-slate-500">💡 Tuliskan setiap butir poin di baris baru (tekan Enter). Anda bebas menggunakan tanda koma di dalam kalimat tanpa khawatir teks terpotong.</p>
                            </div>

                            <!-- 6. Brosur & Lampiran Uploads -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-200 dark:border-slate-800">
                                <!-- Brochure file input -->
                                <div class="space-y-3 bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">File Brosur Unit (PDF/Gambar)</label>
                                    
                                    @if(!empty($settings['unit_' . $code . '_brochure_url']))
                                        <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-900/60 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                            <a href="{{ $settings['unit_' . $code . '_brochure_url'] }}" target="_blank" class="text-[10px] text-brand-emerald dark:text-emerald-400 hover:underline font-extrabold truncate max-w-[180px]">📄 Lihat Brosur Aktif</a>
                                            <label class="flex items-center gap-1 text-[9px] text-red-600 dark:text-red-400 font-bold cursor-pointer hover:text-red-700">
                                                <input type="checkbox" name="delete_unit_{{ $code }}_brochure" value="1" class="rounded text-red-600 focus:ring-red-500 w-3 h-3"> Hapus
                                            </label>
                                        </div>
                                    @endif

                                    <input type="file" name="unit_{{ $code }}_brochure" accept="application/pdf,image/*" class="text-[10px] text-slate-500 dark:text-slate-400 w-full file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-slate-100 dark:file:bg-slate-700 file:text-slate-700 dark:file:text-slate-200 hover:file:bg-slate-200 cursor-pointer" />
                                    <p class="text-[9px] text-slate-400 dark:text-slate-500 font-medium">PDF atau Gambar (Maks 4MB)</p>
                                </div>

                                <!-- Attachment file input -->
                                <div class="space-y-3 bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">File Lampiran/Pendukung (PDF/Zip/Doc)</label>
                                    
                                    @if(!empty($settings['unit_' . $code . '_attachment_url']))
                                        <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-900/60 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700">
                                            <a href="{{ $settings['unit_' . $code . '_attachment_url'] }}" target="_blank" class="text-[10px] text-brand-emerald dark:text-emerald-400 hover:underline font-extrabold truncate max-w-[180px]">📄 Lihat Lampiran Aktif</a>
                                            <label class="flex items-center gap-1 text-[9px] text-red-600 dark:text-red-400 font-bold cursor-pointer hover:text-red-700">
                                                <input type="checkbox" name="delete_unit_{{ $code }}_attachment" value="1" class="rounded text-red-600 focus:ring-red-500 w-3 h-3"> Hapus
                                            </label>
                                        </div>
                                    @endif

                                    <input type="file" name="unit_{{ $code }}_attachment" accept="application/pdf,application/zip,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" class="text-[10px] text-slate-500 dark:text-slate-400 w-full file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-slate-100 dark:file:bg-slate-700 file:text-slate-700 dark:file:text-slate-200 hover:file:bg-slate-200 cursor-pointer" />
                                    <p class="text-[9px] text-slate-400 dark:text-slate-500 font-medium">PDF, Zip, Word, Excel (Maks 5MB)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- TAB: TESTIMONI & REVIEW -->
            <div id="tab-content-testimonials" class="tab-panel space-y-6 {{ $currentTab === 'testimonials' ? '' : 'hidden' }}">
                <!-- Header Banner / Action Card -->
                <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-slate-50 dark:from-slate-900 dark:via-slate-850 dark:to-slate-900 border border-emerald-100/80 dark:border-slate-800 rounded-2xl p-5 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2.5">
                            <span class="p-2 rounded-xl bg-brand-emerald text-white shadow-sm flex items-center justify-center">
                                <i data-lucide="message-square-quote" class="w-4 h-4"></i>
                            </span>
                            <h3 class="text-sm sm:text-base font-black text-slate-800 dark:text-slate-100">Testimoni & "Kata Mereka"</h3>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed max-w-2xl">
                            Kelola ulasan dan kutipan testimoni dari orang tua wali murid atau alumni yang ditampilkan pada section <strong>"Kata Mereka Tentang Kami"</strong> di landing page portal SPMB.
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="openAddTestimonialModal()" class="w-full sm:w-auto bg-brand-emerald hover-emerald text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-md transition flex items-center justify-center gap-2 select-none active:scale-[0.98] cursor-pointer">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            <span>Tambah Testimoni</span>
                        </button>
                    </div>
                </div>

                @if($isSuperAdmin)
                    <!-- Unit Filter Pills -->
                    <div class="flex items-center gap-2 overflow-x-auto pb-1">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-1">Filter Jenjang:</span>
                        <button type="button" onclick="filterTestimonialCards('all', this)" class="testimonial-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition bg-brand-emerald text-white shadow-xs cursor-pointer">
                            Semua ({{ $testimonials->count() }})
                        </button>
                        <button type="button" onclick="filterTestimonialCards('general', this)" class="testimonial-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 cursor-pointer">
                            Umum / Semua Jenjang ({{ $testimonials->whereNull('spmb_unit_id')->count() }})
                        </button>
                        @foreach($allUnits as $u)
                            <button type="button" onclick="filterTestimonialCards('{{ $u->id }}', this)" class="testimonial-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold transition bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 cursor-pointer">
                                {{ $u->name }} ({{ $testimonials->where('spmb_unit_id', $u->id)->count() }})
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="flex items-center justify-between pb-1">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Unit:</span>
                            <span class="px-3 py-1 text-xs font-bold rounded-xl bg-emerald-50 dark:bg-emerald-950/80 text-brand-emerald dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/80">
                                Jenjang {{ $units->first()->name ?? 'Unit Anda' }} ({{ $testimonials->count() }} Testimoni)
                            </span>
                        </div>
                    </div>
                @endif

                <!-- Testimonials Grid / List -->
                @if($testimonials->isEmpty())
                    <div class="text-center py-12 px-4 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl bg-slate-50/50 dark:bg-slate-900/40">
                        <div class="w-16 h-16 rounded-full bg-emerald-50 dark:bg-emerald-950 text-brand-emerald dark:text-emerald-400 flex items-center justify-center mx-auto mb-3">
                            <i data-lucide="message-square" class="w-8 h-8 opacity-60"></i>
                        </div>
                        <h4 class="text-sm font-bold text-slate-700 dark:text-slate-300">Belum ada data testimoni</h4>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 max-w-md mx-auto">Tambahkan testimoni pertama untuk memperkaya materi promosi di landing page SPMB.</p>
                        <button type="button" onclick="openAddTestimonialModal()" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-emerald text-white text-xs font-bold hover-emerald shadow-sm transition cursor-pointer">
                            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Sekarang
                        </button>
                    </div>
                @else
                    <div id="testimonials-card-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach($testimonials as $item)
                            <div id="testimonial-card-{{ $item->id }}" data-unit-id="{{ $item->spmb_unit_id ?? 'general' }}" class="testimonial-card bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 flex flex-col justify-between space-y-4 shadow-xs hover:shadow-md dark:hover:border-slate-700 transition-all duration-200 {{ $item->is_active ? '' : 'border-slate-200/60 dark:border-slate-800/60 opacity-60 bg-slate-50/50 dark:bg-slate-950/40' }}">
                                <div class="space-y-3">
                                    <!-- Top Header: Avatar + Info + Toggle -->
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            @if($item->avatar_url)
                                                <img src="{{ $item->avatar_url }}" alt="{{ $item->name }}" class="w-11 h-11 rounded-full object-cover border-2 border-slate-100 dark:border-slate-700 shadow-xs flex-shrink-0" />
                                            @else
                                                <div class="w-11 h-11 rounded-full bg-emerald-50 dark:bg-emerald-950/80 border border-emerald-200 dark:border-emerald-800 text-brand-emerald dark:text-emerald-400 flex items-center justify-center font-black text-xs shadow-xs flex-shrink-0">
                                                    {{ $item->initials }}
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <h4 class="font-extrabold text-xs text-slate-800 dark:text-slate-100 truncate" title="{{ $item->name }}">{{ $item->name }}</h4>
                                                <p class="text-[10px] text-slate-500 dark:text-slate-400 font-medium truncate" title="{{ $item->role_title }}">{{ $item->role_title }}</p>
                                                <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                                    @if($item->unit)
                                                        <span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-brand-emerald dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60">
                                                            {{ $item->unit->name }}
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-0.5 text-[9px] font-bold rounded-full bg-purple-50 dark:bg-purple-950/50 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800/60">
                                                            Semua Jenjang
                                                        </span>
                                                    @endif
                                                    <span class="px-1.5 py-0.5 text-[9px] font-semibold rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                                        Urutan #{{ $item->order }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Active status toggle with distinct ON/OFF indicator -->
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <span id="testimonial-toggle-label-{{ $item->id }}" class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full transition-all {{ $item->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 shadow-xs' : 'bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-300 dark:border-slate-700' }}">
                                                {{ $item->is_active ? 'AKTIF' : 'OFF' }}
                                            </span>
                                            <label class="relative inline-flex items-center cursor-pointer" title="{{ $item->is_active ? 'Status: Aktif (Klik untuk nonaktifkan)' : 'Status: Nonaktif (Klik untuk aktifkan)' }}">
                                                <input type="checkbox" onchange="toggleTestimonialStatusAjax({{ $item->id }}, this)" class="sr-only peer" {{ $item->is_active ? 'checked' : '' }}>
                                                <div class="w-11 h-6 bg-slate-300 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 peer-focus:outline-none rounded-full peer peer-checked:bg-emerald-500 dark:peer-checked:bg-emerald-500 peer-checked:border-emerald-500 peer-checked:shadow-md peer-checked:shadow-emerald-500/35 peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-200 after:border after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-sm transition-all duration-200"></div>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Rating Stars -->
                                    <div class="flex items-center gap-1">
                                        @for($s = 1; $s <= 5; $s++)
                                            <span class="text-xs {{ $s <= $item->rating ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700' }}">★</span>
                                        @endfor
                                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 ml-1">({{ $item->rating }}.0)</span>
                                    </div>

                                    <!-- Content Quote -->
                                    <div class="bg-slate-50/80 dark:bg-slate-800/60 rounded-xl p-3 border border-slate-100 dark:border-slate-750">
                                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed italic line-clamp-4" title="{{ $item->content }}">
                                            "{{ $item->content }}"
                                        </p>
                                    </div>
                                </div>

                                <!-- Card Actions -->
                                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                                    <span id="testimonial-status-text-{{ $item->id }}" class="text-[10px] {{ $item->is_active ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-400 dark:text-slate-500 font-medium' }}">
                                        {{ $item->is_active ? '● Tayang di Landing Page' : '○ Dinonaktifkan' }}
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" onclick="openEditTestimonialModal({{ $item->id }})" class="p-1.5 text-slate-500 dark:text-slate-400 hover:text-brand-emerald dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-lg transition cursor-pointer" title="Edit Testimoni">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" onclick="openDeleteTestimonialModal({{ $item->id }}, '{{ addslashes($item->name) }}')" class="p-1.5 text-slate-400 dark:text-slate-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-lg transition cursor-pointer" title="Hapus Testimoni">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        <!-- Submit Panel (Hidden when on Testimonials Tab) -->
        <div id="settings-submit-panel" class="bg-slate-50 dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 px-8 py-4 flex justify-between items-center {{ $currentTab === 'testimonials' ? 'hidden' : '' }}">
            <span class="text-xs text-slate-400 font-semibold">Semua perubahan hanya berlaku pada domain calon pendaftar.</span>
            <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl text-xs font-bold shadow-md transition cursor-pointer">
                Simpan Perubahan Tampilan
            </button>
        </div>
    </form>

    <script>
        // Store testimonials in JS dictionary for secure, unescaped lookup
        const testimonialsData = @json($testimonials->keyBy('id'));

        // Tab switching script
        function switchSettingsTab(tabId) {
            const activeTabInput = document.getElementById('active-tab-input');
            if (activeTabInput) {
                activeTabInput.value = tabId;
            }

            // Toggle tab content panel visibility
            document.querySelectorAll('.tab-panel').forEach(panel => {
                panel.classList.add('hidden');
            });
            const activePanel = document.getElementById('tab-content-' + tabId);
            if (activePanel) {
                activePanel.classList.remove('hidden');
            }

            // Toggle submit panel visibility
            const submitPanel = document.getElementById('settings-submit-panel');
            if (submitPanel) {
                if (tabId === 'testimonials') {
                    submitPanel.classList.add('hidden');
                } else {
                    submitPanel.classList.remove('hidden');
                }
            }

            // Toggle button active visual states
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('border-brand-emerald', 'text-brand-emerald', 'font-extrabold');
                btn.classList.add('border-transparent', 'text-slate-500', 'hover:text-slate-700', 'font-bold');
            });
            const activeBtn = document.getElementById('tab-btn-' + tabId);
            if (activeBtn) {
                activeBtn.classList.remove('border-transparent', 'text-slate-500', 'hover:text-slate-700', 'font-bold');
                activeBtn.classList.add('border-brand-emerald', 'text-brand-emerald', 'font-extrabold');
            }

            // Update URL query parameter
            const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?tab=' + tabId;
            window.history.replaceState({ path: newUrl }, '', newUrl);

            // Save active tab to localStorage
            localStorage.setItem('spmb_ui_active_tab', tabId);

            if (window.lucide) {
                window.lucide.createIcons();
            }
        }

        // Color picker labels updater
        function updateHexLabel(type) {
            const picker = document.getElementById(type + '-picker');
            const label = document.getElementById(type + '-hex');
            if (picker && label) {
                label.textContent = picker.value.toUpperCase();
            }
        }

        // Local image reader for file upload previews
        function previewSelectedImage(event, containerId) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const container = document.getElementById(containerId);
                    if (container) {
                        container.innerHTML = `<img src="${e.target.result}" class="max-h-full object-contain" />`;
                    }
                }
                reader.readAsDataURL(file);
            }
        }

        function clearExistingAsset(fieldName) {
            const fieldInput = document.createElement('input');
            fieldInput.type = 'hidden';
            fieldInput.name = 'clear_' + fieldName;
            fieldInput.value = '1';
            document.getElementById('ui-settings-form').appendChild(fieldInput);

            const previewId = fieldName === 'school_logo_url' ? 'logo-preview-box' : 'favicon-preview-box';
            const previewBox = document.getElementById(previewId);
            if (previewBox) {
                previewBox.innerHTML = '<span class="text-[10px] text-slate-400 font-bold">Belum Ada Logo</span>';
                if (fieldName === 'school_favicon_url') {
                    previewBox.innerHTML = '<span class="text-[10px] text-slate-400 font-bold">Default</span>';
                }
            }
        }

        // --- TESTIMONIALS MODAL & AJAX FUNCTIONS ---
        function filterTestimonialCards(unitId, btnElem) {
            document.querySelectorAll('.testimonial-filter-btn').forEach(btn => {
                btn.classList.remove('bg-brand-emerald', 'text-white', 'shadow-xs');
                btn.classList.add('bg-slate-100', 'dark:bg-slate-800', 'text-slate-600', 'dark:text-slate-300');
            });
            btnElem.classList.remove('bg-slate-100', 'dark:bg-slate-800', 'text-slate-600', 'dark:text-slate-300');
            btnElem.classList.add('bg-brand-emerald', 'text-white', 'shadow-xs');

            const cards = document.querySelectorAll('.testimonial-card');
            cards.forEach(card => {
                const cardUnit = card.getAttribute('data-unit-id');
                if (unitId === 'all' || cardUnit === unitId) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }

        function previewTestimonialAvatar(event, containerId) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const container = document.getElementById(containerId);
                    if (container) {
                        container.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover" />`;
                    }
                }
                reader.readAsDataURL(file);
            }
        }

        function openAddTestimonialModal() {
            const modal = document.getElementById('modal-add-testimonial');
            if (!modal) return;
            modal.classList.remove('hidden');
            document.documentElement.classList.add('overflow-hidden');
            document.body.classList.add('overflow-hidden');
            if (window.lucide) window.lucide.createIcons();
        }

        function closeAddTestimonialModal() {
            const modal = document.getElementById('modal-add-testimonial');
            if (!modal) return;
            modal.classList.add('hidden');
            document.documentElement.classList.remove('overflow-hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function openEditTestimonialModal(id) {
            const item = testimonialsData[id];
            if (!item) return;

            const form = document.getElementById('form-edit-testimonial');
            form.action = `{{ url('admin/ui-settings/testimonials') }}/${item.id}`;

            document.getElementById('edit_name').value = item.name || '';
            document.getElementById('edit_role_title').value = item.role_title || '';
            document.getElementById('edit_spmb_unit_id').value = item.spmb_unit_id || '';
            document.getElementById('edit_rating').value = item.rating || 5;
            document.getElementById('edit_order').value = item.order || 1;
            document.getElementById('edit_content').value = item.content || '';
            document.getElementById('edit_is_active').checked = !!item.is_active;
            updateModalSwitchLabel(document.getElementById('edit_is_active'), 'edit-is-active-label');
            document.getElementById('edit_clear_avatar').value = '0';

            const previewBox = document.getElementById('edit-avatar-preview-box');
            const clearContainer = document.getElementById('edit-clear-avatar-container');

            if (item.avatar_url) {
                previewBox.innerHTML = `<img src="${item.avatar_url}" class="w-full h-full object-cover" />`;
                clearContainer.classList.remove('hidden');
            } else {
                previewBox.innerHTML = `<div class="w-full h-full bg-emerald-50 dark:bg-emerald-950 text-brand-emerald dark:text-emerald-400 font-black text-xs flex items-center justify-center">${item.initials || 'AS'}</div>`;
                clearContainer.classList.add('hidden');
            }

            const modal = document.getElementById('modal-edit-testimonial');
            if (!modal) return;
            modal.classList.remove('hidden');
            document.documentElement.classList.add('overflow-hidden');
            document.body.classList.add('overflow-hidden');
            if (window.lucide) window.lucide.createIcons();
        }

        function markClearEditAvatar() {
            document.getElementById('edit_clear_avatar').value = '1';
            const previewBox = document.getElementById('edit-avatar-preview-box');
            previewBox.innerHTML = `<div class="w-full h-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 flex items-center justify-center"><i data-lucide="user" class="w-6 h-6"></i></div>`;
            document.getElementById('edit-clear-avatar-container').classList.add('hidden');
            if (window.lucide) window.lucide.createIcons();
        }

        function closeEditTestimonialModal() {
            const modal = document.getElementById('modal-edit-testimonial');
            if (!modal) return;
            modal.classList.add('hidden');
            document.documentElement.classList.remove('overflow-hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function openDeleteTestimonialModal(id, name) {
            const form = document.getElementById('form-delete-testimonial');
            form.action = `{{ url('admin/ui-settings/testimonials') }}/${id}`;
            document.getElementById('delete-testimonial-name').textContent = name;
            const modal = document.getElementById('modal-delete-testimonial');
            if (!modal) return;
            modal.classList.remove('hidden');
            document.documentElement.classList.add('overflow-hidden');
            document.body.classList.add('overflow-hidden');
            if (window.lucide) window.lucide.createIcons();
        }

        function closeDeleteTestimonialModal() {
            const modal = document.getElementById('modal-delete-testimonial');
            if (!modal) return;
            modal.classList.add('hidden');
            document.documentElement.classList.remove('overflow-hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Global key listener for Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddTestimonialModal();
                closeEditTestimonialModal();
                closeDeleteTestimonialModal();
            }
        });

        function updateModalSwitchLabel(checkboxElem, labelId) {
            const label = document.getElementById(labelId);
            if (!label) return;
            if (checkboxElem.checked) {
                label.textContent = 'ON';
                label.className = 'text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 shadow-xs';
            } else {
                label.textContent = 'OFF';
                label.className = 'text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-300 dark:border-slate-700';
            }
        }

        function toggleTestimonialStatusAjax(id, inputElem) {
            const card = document.getElementById('testimonial-card-' + id);
            const statusText = document.getElementById('testimonial-status-text-' + id);
            const toggleLabel = document.getElementById('testimonial-toggle-label-' + id);
            const isChecked = inputElem.checked;

            // Instant optimistic update
            if (toggleLabel) {
                if (isChecked) {
                    toggleLabel.textContent = 'AKTIF';
                    toggleLabel.className = 'text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full transition-all bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 shadow-xs';
                } else {
                    toggleLabel.textContent = 'OFF';
                    toggleLabel.className = 'text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full transition-all bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-300 dark:border-slate-700';
                }
            }

            fetch(`{{ url('admin/ui-settings/testimonials') }}/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ is_active: isChecked })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.is_active) {
                        card.classList.remove('opacity-60', 'bg-slate-50/50', 'dark:bg-slate-950/40');
                        card.classList.add('border-slate-200', 'dark:border-slate-800');
                        if (statusText) {
                            statusText.className = 'text-[10px] text-emerald-600 dark:text-emerald-400 font-bold';
                            statusText.textContent = '● Tayang di Landing Page';
                        }
                    } else {
                        card.classList.add('opacity-60', 'bg-slate-50/50', 'dark:bg-slate-950/40');
                        if (statusText) {
                            statusText.className = 'text-[10px] text-slate-400 dark:text-slate-500 font-medium';
                            statusText.textContent = '○ Dinonaktifkan';
                        }
                    }
                    if (typeof showToast === 'function') {
                        showToast(data.message || 'Status testimoni berhasil diubah!', 'success');
                    }
                } else {
                    // Revert
                    inputElem.checked = !isChecked;
                    if (toggleLabel) {
                        toggleLabel.textContent = !isChecked ? 'AKTIF' : 'OFF';
                        toggleLabel.className = !isChecked 
                            ? 'text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full transition-all bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 shadow-xs'
                            : 'text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full transition-all bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-300 dark:border-slate-700';
                    }
                    if (typeof showToast === 'function') {
                        showToast(data.message || 'Gagal mengubah status', 'error');
                    }
                }
            })
            .catch(err => {
                console.error(err);
                inputElem.checked = !isChecked;
                if (toggleLabel) {
                    toggleLabel.textContent = !isChecked ? 'AKTIF' : 'OFF';
                    toggleLabel.className = !isChecked 
                        ? 'text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full transition-all bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 shadow-xs'
                        : 'text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full transition-all bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-300 dark:border-slate-700';
                }
                if (typeof showToast === 'function') {
                    showToast('Gagal menghubungi server.', 'error');
                }
            });
        }

        // Restore active tab & Reopen failed modal if validation errors exist
        (function() {
            const activeTabInput = document.getElementById('active-tab-input');
            const initialTab = '{{ $currentTab }}';
            let targetTab = initialTab;

            const urlParams = new URLSearchParams(window.location.search);
            const queryTab = urlParams.get('tab');

            const candidateTab = queryTab || activeTabInput?.value || localStorage.getItem('spmb_ui_active_tab');
            if (candidateTab && document.getElementById('tab-btn-' + candidateTab)) {
                targetTab = candidateTab;
            }

            if (targetTab && document.getElementById('tab-btn-' + targetTab)) {
                switchSettingsTab(targetTab);
            }

            @if(session('failed_modal') === 'add_testimonial')
                openAddTestimonialModal();
            @elseif(session('failed_modal') && str_starts_with(session('failed_modal'), 'edit_testimonial_'))
                @php
                    $failedEditId = (int) str_replace('edit_testimonial_', '', session('failed_modal'));
                @endphp
                openEditTestimonialModal({{ $failedEditId }});
            @endif
        })();
    </script>
</div>

@push('modals')
    <!-- MODAL 1: TAMBAH TESTIMONI -->
    <div id="modal-add-testimonial" onclick="closeAddTestimonialModal()" class="fixed inset-0 top-0 left-0 right-0 bottom-0 w-full h-full z-[99999] hidden bg-slate-950/80 backdrop-blur-xs overflow-y-auto flex items-center justify-center p-4 m-0">
        <div onclick="event.stopPropagation()" class="bg-white dark:bg-slate-900 rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 dark:border-slate-800 space-y-6 animate-scaleIn relative my-auto">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/80 text-brand-emerald dark:text-emerald-400 flex items-center justify-center">
                        <i data-lucide="message-square-plus" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-800 dark:text-slate-100">Tambah Testimoni Baru</h3>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500">Tambahkan testimoni orang tua wali murid atau alumni</p>
                    </div>
                </div>
                <button type="button" onclick="closeAddTestimonialModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            @if($errors->any() && session('failed_modal') === 'add_testimonial')
                <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 text-red-700 dark:text-red-400 text-xs font-medium space-y-1">
                    <div class="font-bold flex items-center gap-1.5"><i data-lucide="alert-circle" class="w-4 h-4"></i> Mohon periksa kesalahan input berikut:</div>
                    <ul class="list-disc list-inside space-y-0.5 pl-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="form-add-testimonial" action="{{ route('admin.ui-settings.testimonials.store') }}" method="POST" enctype="multipart/form-data" hx-boost="false" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nama Pemberi -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama Lengkap / Panggilan <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Bunda Sarah" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:focus:ring-emerald-500 font-semibold" />
                    </div>

                    <!-- Peran / Status -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Peran / Keterangan <span class="text-red-500">*</span></label>
                        <input type="text" name="role_title" value="{{ old('role_title') }}" required placeholder="Contoh: Orang Tua Murid SD Anak Saleh" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:focus:ring-emerald-500 font-medium" />
                    </div>

                    <!-- Jenjang Terkait -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kategori Jenjang</label>
                        @if($isSuperAdmin)
                            <select name="spmb_unit_id" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:focus:ring-emerald-500 font-semibold cursor-pointer">
                                <option value="">Semua Jenjang / Umum</option>
                                @foreach($allUnits as $u)
                                    <option value="{{ $u->id }}" {{ old('spmb_unit_id') == $u->id ? 'selected' : '' }}>Jenjang {{ $u->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="hidden" name="spmb_unit_id" value="{{ auth()->user()->spmb_unit_id }}">
                            <div class="w-full bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl px-3.5 py-2.5 text-xs font-bold flex items-center justify-between">
                                <span>Jenjang {{ $units->first()->name ?? 'Unit Anda' }}</span>
                                <span class="text-[9px] px-2 py-0.5 bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400 rounded-md font-black">Terkunci (Unit Anda)</span>
                            </div>
                        @endif
                    </div>

                    <!-- Rating & Urutan -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rating <span class="text-red-500">*</span></label>
                            <select name="rating" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:focus:ring-emerald-500 font-bold cursor-pointer">
                                <option value="5" {{ old('rating', '5') == '5' ? 'selected' : '' }}>★★★★★ (5)</option>
                                <option value="4" {{ old('rating') == '4' ? 'selected' : '' }}>★★★★☆ (4)</option>
                                <option value="3" {{ old('rating') == '3' ? 'selected' : '' }}>★★★☆☆ (3)</option>
                                <option value="2" {{ old('rating') == '2' ? 'selected' : '' }}>★★☆☆☆ (2)</option>
                                <option value="1" {{ old('rating') == '1' ? 'selected' : '' }}>★☆☆☆☆ (1)</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Urutan Tampil</label>
                            <input type="number" name="order" value="{{ old('order', ($testimonials->max('order') ?? 0) + 1) }}" min="1" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:focus:ring-emerald-500 font-semibold" />
                        </div>
                    </div>
                </div>

                <!-- Foto Profil / Avatar -->
                <div class="space-y-1.5 pt-2">
                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Foto Profil / Avatar (Opsional)</label>
                    <div class="flex items-center gap-4">
                        <div id="add-avatar-preview-box" class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 overflow-hidden flex-shrink-0">
                            <i data-lucide="user" class="w-6 h-6"></i>
                        </div>
                        <div class="flex-1">
                            <input type="file" name="avatar" accept="image/jpeg,image/png,image/jpg,image/webp" onchange="previewTestimonialAvatar(event, 'add-avatar-preview-box')" class="text-xs text-slate-500 dark:text-slate-400 w-full file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:bg-emerald-50 dark:file:bg-emerald-950/80 file:text-brand-emerald dark:file:text-emerald-400 hover:file:bg-emerald-100 cursor-pointer" />
                            <p class="text-[9px] text-slate-400 dark:text-slate-500 mt-1">Format: JPG, PNG, WEBP (Maks 2MB). Jika kosong, inisial nama akan otomatis digunakan.</p>
                        </div>
                    </div>
                </div>

                <!-- Isi Kutipan Testimoni -->
                <div class="space-y-1.5 pt-2">
                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kutipan / Pesan Testimoni <span class="text-red-500">*</span></label>
                    <textarea name="content" rows="4" required placeholder="Tuliskan ulasan atau kutipan testimoni di sini..." class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 rounded-xl p-3.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:focus:ring-emerald-500 font-medium leading-relaxed">{{ old('content') }}</textarea>
                </div>

                <!-- Status Aktif Switch Card -->
                <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                    <div class="space-y-0.5">
                        <label for="add_is_active" class="text-xs font-bold text-slate-800 dark:text-slate-200 cursor-pointer block">Status Penayangan Testimoni</label>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Aktifkan agar langsung tampil di landing page publik</p>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span id="add-is-active-label" class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 shadow-xs">ON</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" id="add_is_active" value="1" checked onchange="updateModalSwitchLabel(this, 'add-is-active-label')" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-300 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 peer-focus:outline-none rounded-full peer peer-checked:bg-emerald-500 dark:peer-checked:bg-emerald-500 peer-checked:border-emerald-500 peer-checked:shadow-md peer-checked:shadow-emerald-500/35 peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-200 after:border after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-sm transition-all duration-200"></div>
                        </label>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="closeAddTestimonialModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-2.5 rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Testimoni</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: EDIT TESTIMONI -->
    <div id="modal-edit-testimonial" onclick="closeEditTestimonialModal()" class="fixed inset-0 top-0 left-0 right-0 bottom-0 w-full h-full z-[99999] hidden bg-slate-950/80 backdrop-blur-xs overflow-y-auto flex items-center justify-center p-4 m-0">
        <div onclick="event.stopPropagation()" class="bg-white dark:bg-slate-900 rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 dark:border-slate-800 space-y-6 animate-scaleIn relative my-auto">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/80 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-800 dark:text-slate-100">Edit Testimoni</h3>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500">Perbarui data ulasan dan informasi testimoni</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditTestimonialModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            @if($errors->any() && str_starts_with(session('failed_modal') ?? '', 'edit_testimonial_'))
                <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 text-red-700 dark:text-red-400 text-xs font-medium space-y-1">
                    <div class="font-bold flex items-center gap-1.5"><i data-lucide="alert-circle" class="w-4 h-4"></i> Mohon periksa kesalahan input berikut:</div>
                    <ul class="list-disc list-inside space-y-0.5 pl-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="form-edit-testimonial" action="" method="POST" enctype="multipart/form-data" hx-boost="false" class="space-y-4">
                @csrf
                <input type="hidden" name="clear_avatar" id="edit_clear_avatar" value="0" />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nama Pemberi -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nama Lengkap / Panggilan <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="edit_name" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:focus:ring-emerald-500 font-semibold" />
                    </div>

                    <!-- Peran / Status -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Peran / Keterangan <span class="text-red-500">*</span></label>
                        <input type="text" name="role_title" id="edit_role_title" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:focus:ring-emerald-500 font-medium" />
                    </div>

                    <!-- Jenjang Terkait -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kategori Jenjang</label>
                        @if($isSuperAdmin)
                            <select name="spmb_unit_id" id="edit_spmb_unit_id" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:focus:ring-emerald-500 font-semibold cursor-pointer">
                                <option value="">Semua Jenjang / Umum</option>
                                @foreach($allUnits as $u)
                                    <option value="{{ $u->id }}">Jenjang {{ $u->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="hidden" name="spmb_unit_id" id="edit_spmb_unit_id" value="{{ auth()->user()->spmb_unit_id }}">
                            <div class="w-full bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl px-3.5 py-2.5 text-xs font-bold flex items-center justify-between">
                                <span>Jenjang {{ $units->first()->name ?? 'Unit Anda' }}</span>
                                <span class="text-[9px] px-2 py-0.5 bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400 rounded-md font-black">Terkunci (Unit Anda)</span>
                            </div>
                        @endif
                    </div>

                    <!-- Rating & Urutan -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rating <span class="text-red-500">*</span></label>
                            <select name="rating" id="edit_rating" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:focus:ring-emerald-500 font-bold cursor-pointer">
                                <option value="5">★★★★★ (5)</option>
                                <option value="4">★★★★☆ (4)</option>
                                <option value="3">★★★☆☆ (3)</option>
                                <option value="2">★★☆☆☆ (2)</option>
                                <option value="1">★☆☆☆☆ (1)</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Urutan Tampil</label>
                            <input type="number" name="order" id="edit_order" min="1" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:focus:ring-emerald-500 font-semibold" />
                        </div>
                    </div>
                </div>

                <!-- Foto Profil / Avatar -->
                <div class="space-y-1.5 pt-2">
                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Foto Profil / Avatar</label>
                    <div class="flex items-center gap-4">
                        <div id="edit-avatar-preview-box" class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 overflow-hidden flex-shrink-0">
                            <i data-lucide="user" class="w-6 h-6"></i>
                        </div>
                        <div class="flex-1 space-y-1.5">
                            <input type="file" name="avatar" accept="image/jpeg,image/png,image/jpg,image/webp" onchange="previewTestimonialAvatar(event, 'edit-avatar-preview-box')" class="text-xs text-slate-500 dark:text-slate-400 w-full file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:bg-emerald-50 dark:file:bg-emerald-950/80 file:text-brand-emerald dark:file:text-emerald-400 hover:file:bg-emerald-100 cursor-pointer" />
                            <div id="edit-clear-avatar-container" class="hidden">
                                <button type="button" onclick="markClearEditAvatar()" class="text-[10px] text-red-600 dark:text-red-400 hover:text-red-700 font-bold flex items-center gap-1 cursor-pointer">
                                    <i data-lucide="trash-2" class="w-3 h-3"></i> Hapus Foto Saat Ini (Gunakan Inisial)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Isi Kutipan Testimoni -->
                <div class="space-y-1.5 pt-2">
                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kutipan / Pesan Testimoni <span class="text-red-500">*</span></label>
                    <textarea name="content" id="edit_content" rows="4" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 rounded-xl p-3.5 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald dark:focus:ring-emerald-500 font-medium leading-relaxed"></textarea>
                </div>

                <!-- Status Aktif Switch Card -->
                <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700">
                    <div class="space-y-0.5">
                        <label for="edit_is_active" class="text-xs font-bold text-slate-800 dark:text-slate-200 cursor-pointer block">Status Penayangan Testimoni</label>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Aktifkan agar tampil di landing page publik</p>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <span id="edit-is-active-label" class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 shadow-xs">ON</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" id="edit_is_active" value="1" onchange="updateModalSwitchLabel(this, 'edit-is-active-label')" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-300 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 peer-focus:outline-none rounded-full peer peer-checked:bg-emerald-500 dark:peer-checked:bg-emerald-500 peer-checked:border-emerald-500 peer-checked:shadow-md peer-checked:shadow-emerald-500/35 peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-200 after:border after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-sm transition-all duration-200"></div>
                        </label>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" onclick="closeEditTestimonialModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-2.5 rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: HAPUS TESTIMONI -->
    <div id="modal-delete-testimonial" onclick="closeDeleteTestimonialModal()" class="fixed inset-0 top-0 left-0 right-0 bottom-0 w-full h-full z-[99999] hidden bg-slate-950/80 backdrop-blur-xs overflow-y-auto flex items-center justify-center p-4 m-0">
        <div onclick="event.stopPropagation()" class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-100 dark:border-slate-800 space-y-5 animate-scaleIn relative my-auto">
            <div class="w-14 h-14 rounded-2xl bg-red-50 dark:bg-red-950/80 text-red-600 dark:text-red-400 flex items-center justify-center mx-auto shadow-sm">
                <i data-lucide="alert-triangle" class="w-7 h-7"></i>
            </div>
            
            <div class="text-center space-y-2">
                <h3 class="text-base font-extrabold text-slate-800 dark:text-slate-100">Hapus Testimoni?</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Apakah Anda yakin ingin menghapus testimoni dari <strong id="delete-testimonial-name" class="text-slate-800 dark:text-slate-100"></strong>? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <form id="form-delete-testimonial" action="" method="POST" hx-boost="false" class="flex items-center justify-center gap-3 pt-2">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeDeleteTestimonialModal()" class="flex-1 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-xl text-xs font-bold shadow-md transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    <span>Ya, Hapus</span>
                </button>
            </form>
        </div>
    </div>
@endpush
@endsection
