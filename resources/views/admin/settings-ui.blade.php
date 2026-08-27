@extends('layouts.admin')

@section('title', 'Setting UI Portal - Admin Panel')
@section('page_title', 'Setting UI Portal')

@section('content')
<!-- Quill editor stylesheets and script library -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
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
        min-height: 180px;
        font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-size: 13px;
        color: #334155;
    }
</style>
<div id="ui-settings-container" hx-boost="true" hx-target="#ui-settings-container" hx-select="#ui-settings-container" class="w-full space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h1 class="text-xl font-extrabold text-slate-800">Pengaturan Tampilan Portal Pendaftaran (UI Portal)</h1>
        <p class="text-xs text-slate-500 mt-1">Mengustomisasi logo, warna, judul, banner slider, dan konten informasi per jenjang sekolah.</p>
    </div>

    <!-- Form Configuration -->
    <form id="ui-settings-form" action="{{ route('admin.ui-settings.save') }}" method="POST" enctype="multipart/form-data" hx-boost="false" class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        @csrf
        @php
            $activeTab = request()->get('tab', 'global');
        @endphp
        <input type="hidden" name="active_tab" id="active-tab-input" value="{{ $activeTab }}">
        
        <!-- Navigation Tabs -->
        <div class="bg-slate-50/75 border-b border-slate-100 px-6 flex flex-wrap gap-1">
            <button type="button" onclick="switchSettingsTab('global')" id="tab-btn-global" 
                class="tab-button border-b-2 py-4 px-6 text-xs transition focus:outline-none {{ $activeTab === 'global' ? 'border-brand-emerald text-brand-emerald font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-700 font-bold' }}">
                Global / Banner
            </button>
            <button type="button" onclick="switchSettingsTab('identity')" id="tab-btn-identity" 
                class="tab-button border-b-2 py-4 px-6 text-xs transition focus:outline-none {{ $activeTab === 'identity' ? 'border-brand-emerald text-brand-emerald font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-700 font-bold' }}">
                Identitas Sekolah
            </button>

            @foreach($units as $unit)
                @php $unitCode = strtolower($unit->code); @endphp
                <button type="button" onclick="switchSettingsTab('unit-{{ $unitCode }}')" id="tab-btn-unit-{{ $unitCode }}" 
                    class="tab-button border-b-2 py-4 px-6 text-xs transition focus:outline-none {{ $activeTab === 'unit-' . $unitCode ? 'border-brand-emerald text-brand-emerald font-extrabold' : 'border-transparent text-slate-500 hover:text-slate-700 font-bold' }}">
                    Jenjang {{ $unit->name }}
                </button>
            @endforeach
        </div>

        <!-- Tabs Content Container -->
        <div class="p-8">
            
            <!-- TAB 1: GLOBAL / BANNER -->
            <div id="tab-content-global" class="tab-panel space-y-6 {{ $activeTab === 'global' ? '' : 'hidden' }}">
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
            <div id="tab-content-identity" class="tab-panel space-y-6 {{ $activeTab === 'identity' ? '' : 'hidden' }}">
                <!-- School Name & Tagline -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Nama Instansi Sekolah / Brand</label>
                        <input type="text" name="school_name" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-bold"
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
                        <div class="text-xs font-extrabold text-slate-700">Logo Instansi Sekolah</div>
                        
                        <!-- Preview container -->
                        <div class="h-24 w-full flex items-center justify-center border border-slate-150 rounded-xl bg-white p-3" id="logo-preview-box">
                            @if(!empty($settings['school_logo_url']))
                                <img src="{{ $settings['school_logo_url'] }}" alt="Logo" class="max-h-full object-contain" />
                            @else
                                <span class="text-[10px] text-slate-400 font-bold">Belum Ada Logo (Fallback: 🎓)</span>
                            @endif
                        </div>

                        <input type="file" name="school_logo" accept="image/*" onchange="previewSelectedImage(event, 'logo-preview-box')" class="text-[10px] text-slate-500 w-full file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" />
                        <p class="text-[9px] text-slate-400">Maks file 2MB (PNG, SVG, atau JPG)</p>
                    </div>

                    <!-- Favicon Upload -->
                    <div class="border border-slate-200 rounded-2xl p-6 flex flex-col gap-3 bg-slate-50/20">
                        <div class="text-xs font-extrabold text-slate-700">Favicon Browser</div>
                        
                        <!-- Preview container -->
                        <div class="h-24 w-full flex items-center justify-center border border-slate-150 rounded-xl bg-white p-3" id="favicon-preview-box">
                            @if(!empty($settings['school_favicon_url']))
                                <img src="{{ $settings['school_favicon_url'] }}" alt="Favicon" class="max-h-full object-contain" />
                            @else
                                <span class="text-[10px] text-slate-400 font-bold">Default (Fallback: 🎓)</span>
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

            <!-- DYNAMIC UNITS TABS -->
            @foreach($units as $unit)
                @php $code = strtolower($unit->code); @endphp
                <div id="tab-content-unit-{{ $code }}" class="tab-panel space-y-6 {{ $activeTab === 'unit-' . $code ? '' : 'hidden' }}">
                    <div class="bg-slate-50/50 p-6 rounded-2xl border border-slate-150 space-y-6">
                        <h4 class="text-xs font-extrabold text-brand-emerald uppercase tracking-wider">Pengaturan Jenjang {{ $unit->name }}</h4>
                        
                        <div class="grid grid-cols-1 gap-6">
                            <!-- 1. Deskripsi Singkat -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-2">Deskripsi Singkat Jenjang</label>
                                <textarea name="unit_{{ $code }}_desc" rows="2" required class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald">{{ $settings['unit_' . $code . '_desc'] }}</textarea>
                            </div>

                            <!-- 2. Terdiri Dari Apa Saja -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-2">Terdiri Dari Apa Saja (Program / Kelas - Pisahkan dengan koma)</label>
                                <textarea name="unit_{{ $code }}_content" rows="2" required class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald" placeholder="Contoh: Sentra Bermain, Kelompok Bermain A (3-4 Tahun), Kelompok Bermain B (4-5 Tahun)">{{ $settings['unit_' . $code . '_content'] }}</textarea>
                            </div>

                            <!-- 3. Program Unggulan -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-2">Program Unggulan (Pisahkan dengan koma)</label>
                                <input type="text" name="unit_{{ $code }}_features" required class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald" 
                                    value="{{ $settings['unit_' . $code . '_features'] }}" placeholder="Contoh: Tahfidz Juz 30, Bilingual Program, Lab Komputer" />
                            </div>

                            <!-- 4. Syarat Pendaftaran -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-2">Syarat Pendaftaran (Pisahkan dengan koma)</label>
                                <textarea name="unit_{{ $code }}_requirements" rows="2" required class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald" placeholder="Contoh: Mengisi Form Online, Fotokopi Akta Lahir & KK, Pasfoto 3x4 (2 lembar)">{{ $settings['unit_' . $code . '_requirements'] }}</textarea>
                            </div>

                            <!-- 5. Alur Pendaftaran -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider mb-2">Alur Pendaftaran (Pisahkan dengan koma)</label>
                                <textarea name="unit_{{ $code }}_flow" rows="2" required class="w-full bg-white border border-slate-300 rounded-xl px-4 py-3 text-xs focus:outline-none focus:ring-2 focus:ring-brand-emerald" placeholder="Contoh: 1. Isi Form Pendaftaran, 2. Bayar Uang Pendaftaran, 3. Mengikuti Observasi, 4. Daftar Ulang">{{ $settings['unit_' . $code . '_flow'] }}</textarea>
                                <p class="text-[9px] text-slate-400 mt-2 font-medium">💡 Pisahkan butir informasi/keunggulan/persyaratan di atas menggunakan tanda koma ( , ) agar otomatis diubah menjadi checklist/list terformat di portal pendaftar.</p>
                            </div>

                            <!-- 6. Brosur & Lampiran Uploads -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-200">
                                <!-- Brochure file input -->
                                <div class="space-y-3 bg-white p-4 rounded-xl border border-slate-200">
                                    <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider">File Brosur Unit (PDF/Gambar)</label>
                                    
                                    @if(!empty($settings['unit_' . $code . '_brochure_url']))
                                        <div class="flex items-center justify-between bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                                            <a href="{{ $settings['unit_' . $code . '_brochure_url'] }}" target="_blank" class="text-[10px] text-brand-emerald hover:underline font-extrabold truncate max-w-[180px]">📄 Lihat Brosur Aktif</a>
                                            <label class="flex items-center gap-1 text-[9px] text-red-650 font-bold cursor-pointer hover:text-red-700">
                                                <input type="checkbox" name="delete_unit_{{ $code }}_brochure" value="1" class="rounded text-red-600 focus:ring-red-500 w-3 h-3"> Hapus
                                            </label>
                                        </div>
                                    @endif

                                    <input type="file" name="unit_{{ $code }}_brochure" accept="application/pdf,image/*" class="text-[10px] text-slate-500 w-full file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" />
                                    <p class="text-[9px] text-slate-400 font-medium">PDF atau Gambar (Maks 4MB)</p>
                                </div>

                                <!-- Attachment file input -->
                                <div class="space-y-3 bg-white p-4 rounded-xl border border-slate-200">
                                    <label class="block text-[10px] font-bold text-slate-600 uppercase tracking-wider">File Lampiran/Pendukung (PDF/Zip/Doc)</label>
                                    
                                    @if(!empty($settings['unit_' . $code . '_attachment_url']))
                                        <div class="flex items-center justify-between bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                                            <a href="{{ $settings['unit_' . $code . '_attachment_url'] }}" target="_blank" class="text-[10px] text-brand-emerald hover:underline font-extrabold truncate max-w-[180px]">📄 Lihat Lampiran Aktif</a>
                                            <label class="flex items-center gap-1 text-[9px] text-red-650 font-bold cursor-pointer hover:text-red-700">
                                                <input type="checkbox" name="delete_unit_{{ $code }}_attachment" value="1" class="rounded text-red-600 focus:ring-red-500 w-3 h-3"> Hapus
                                            </label>
                                        </div>
                                    @endif

                                    <input type="file" name="unit_{{ $code }}_attachment" accept="application/pdf,application/zip,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" class="text-[10px] text-slate-500 w-full file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" />
                                    <p class="text-[9px] text-slate-400 font-medium">PDF, Zip, Word, Excel (Maks 5MB)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            


        </div>

        <!-- Submit Panel -->
        <div class="bg-slate-50 border-t border-slate-100 px-8 py-4 flex justify-between items-center">
            <span class="text-xs text-slate-400 font-semibold">Semua perubahan hanya berlaku pada domain calon pendaftar.</span>
            <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl text-xs font-bold shadow-md transition">
                Simpan Perubahan Tampilan
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
    <script>
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

        // Restore active tab
        (function() {
            const activeTabInput = document.getElementById('active-tab-input');
            const savedTab = activeTabInput ? activeTabInput.value : (localStorage.getItem('spmb_ui_active_tab') || 'global');
            if (savedTab && document.getElementById('tab-btn-' + savedTab)) {
                switchSettingsTab(savedTab);
            }
        })();
    </script>
</div>
@endsection
