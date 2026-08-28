@extends('layouts.admin')

@section('title', 'Template Surat Pernyataan - Admin Panel')
@section('page_title', 'Surat Pernyataan')

@section('content')
<style>
    /* Custom styles for professional word-like Quill editor look */
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
        min-height: 350px;
        max-height: 550px;
        font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-size: 13px;
        color: #334155;
        line-height: 1.6;
    }
    .ql-editor.ql-blank::before {
        position: absolute !important;
        left: 15px !important;
        right: 15px !important;
        color: #94a3b8 !important;
        font-style: italic !important;
        pointer-events: none !important;
    }
    .ql-editor ol, .ql-editor ul {
        padding-left: 1.5rem !important;
    }
    .ql-editor li {
        margin-bottom: 0.25rem;
    }
    /* Hide Quill link/formula tooltip when it has the hidden class */
    .ql-tooltip.ql-hidden {
        display: none !important;
    }
</style>

<div id="agreements-settings-container" hx-boost="true" hx-target="#agreements-settings-container" hx-select="#agreements-settings-container" class="w-full space-y-6">
    @php
        $firstUnit = $units->first();
        $defaultTab = $firstUnit ? 'unit_' . $firstUnit->id : '';
        $activeTab = request()->get('tab', $defaultTab);
    @endphp
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center gap-3">
            <div class="h-12 w-12 bg-emerald-50 dark:bg-emerald-950/20 text-brand-emerald rounded-2xl flex items-center justify-center shadow-inner">
                <i data-lucide="file-signature" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-800">Surat Pernyataan Kesanggupan</h1>
                <p class="text-xs text-slate-500 mt-1">Kelola draf surat komitmen, tata tertib, dan syarat pembiayaan masuk sekolah yang wajib disetujui wali murid secara digital.</p>
            </div>
        </div>
    </div>



    <!-- Unit Tabs -->
    <div class="flex flex-wrap gap-2 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
        @foreach($units as $index => $unit)
            <button type="button" 
                    onclick="switchAgreementTab('unit_{{ $unit->id }}')" 
                    id="agreementTabBtn-unit_{{ $unit->id }}" 
                    class="agreement-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'unit_' . $unit->id ? 'bg-brand-emerald text-white shadow' : 'text-slate-600 hover:bg-slate-50' }}">
                <i data-lucide="school" class="w-4 h-4"></i> {{ $unit->name }}
            </button>
        @endforeach
    </div>

    <!-- Tab Contents -->
    @foreach($units as $index => $unit)
        <div id="agreementTabContent-unit_{{ $unit->id }}" class="agreement-tab-content {{ $activeTab === 'unit_' . $unit->id ? '' : 'hidden' }} bg-white rounded-3xl border border-slate-100 shadow-sm p-6 space-y-6">
            <div class="border-b border-slate-100 pb-4 flex justify-between items-center">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-850">Format Surat Pernyataan: {{ $unit->name }}</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Draf isi surat kesanggupan orang tua/wali murid khusus untuk jenjang {{ $unit->code }}.</p>
                </div>
                <span class="bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full text-[10px] font-extrabold tracking-wider uppercase">
                    Jenjang {{ $unit->code }}
                </span>
            </div>

            <form method="POST" action="{{ route('admin.spmb-settings.agreements.update', $unit->id) }}" hx-boost="false" class="space-y-6">
                @csrf
                <input type="hidden" name="active_tab" class="active-tab-input" value="{{ $activeTab }}">
                
                <!-- Title Field -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Judul Surat Pernyataan (Bisa multi-line)</label>
                    <textarea name="title" 
                              rows="4" 
                              class="w-full rounded-xl border border-slate-200 bg-white text-xs px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-slate-800 font-bold resize-y" 
                              required>{{ $unit->agreementTemplate->title ?? "KESANGGUPAN ORANGTUA/WALI MURID\nTAHUN AJARAN " . '{' . '{tahun_ajaran}' . '}' . "\nSEKOLAH ANAK SALEH\nKOTA MALANG" }}</textarea>
                </div>

                <!-- Editor Field (Rich WYSIWYG Editor) -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Konten / Isi Surat Kesanggupan (Edit seperti Word)</label>
                    
                    <!-- Quill Editor Container -->
                    <div id="quill-editor-unit_{{ $unit->id }}" class="quill-editor-container border border-slate-200 shadow-sm rounded-xl">
                        {!! $unit->agreementTemplate->content ?? '' !!}
                    </div>

                    <!-- Hidden Input to store HTML content during submit -->
                    <input type="hidden" name="content" id="hidden-content-unit_{{ $unit->id }}" value="{{ $unit->agreementTemplate->content ?? '' }}">
                    <p class="text-[10px] text-slate-400">Gunakan toolbar di atas untuk menebalkan teks, membuat judul, atau menambahkan daftar poin terstruktur.</p>
                </div>

                <!-- Helper Card for Placeholders -->
                <div class="bg-slate-50 rounded-2xl border border-slate-150 p-4 space-y-2.5">
                    <h4 class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                        <i data-lucide="info" class="w-4 h-4 text-brand-emerald"></i>
                        Token Placeholder Dinamis
                    </h4>
                    <p class="text-[10px] text-slate-500 leading-relaxed">
                        Anda dapat menyematkan kode-kode token di bawah ini di dalam Judul atau Konten Surat. Sistem akan secara otomatis mengganti kode tersebut dengan data asli calon siswa saat halaman dirender:
                    </p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 pt-1">
                        <div class="bg-white p-2 rounded-lg border border-slate-100 text-center">
                            <code class="text-[10px] text-brand-emerald font-bold">@{{ nama_calon_siswa }}</code>
                            <span class="block text-[9px] text-slate-400 mt-0.5">Nama Anak</span>
                        </div>
                        <div class="bg-white p-2 rounded-lg border border-slate-100 text-center">
                            <code class="text-[10px] text-brand-emerald font-bold">@{{ nama_unit }}</code>
                            <span class="block text-[9px] text-slate-400 mt-0.5">Nama Unit</span>
                        </div>
                        <div class="bg-white p-2 rounded-lg border border-slate-100 text-center">
                            <code class="text-[10px] text-brand-emerald font-bold">@{{ nama_kelas }}</code>
                            <span class="block text-[9px] text-slate-400 mt-0.5">Tingkat Kelas</span>
                        </div>
                        <div class="bg-white p-2 rounded-lg border border-slate-100 text-center">
                            <code class="text-[10px] text-brand-emerald font-bold">@{{ tahun_ajaran }}</code>
                            <span class="block text-[9px] text-slate-400 mt-0.5">Tahun Ajaran</span>
                        </div>
                    </div>
                </div>

                <!-- Checkbox Consent Labels -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700">Label Checkbox 1 (Tata Tertib & Peraturan)</label>
                        <input type="text" 
                               name="rules_consent_label" 
                               value="{{ $unit->agreementTemplate->rules_consent_label ?? 'Saya menyetujui seluruh tata tertib dan peraturan akademik Sekolah Anak Saleh.' }}" 
                               class="w-full rounded-xl border border-slate-200 bg-white text-xs px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-slate-700" 
                               required>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700">Label Checkbox 2 (Biaya & Komitmen Keuangan)</label>
                        <input type="text" 
                               name="fees_consent_label" 
                               value="{{ $unit->agreementTemplate->fees_consent_label ?? 'Saya menyanggupi pemenuhan seluruh rincian biaya pendidikan dan administrasi masuk yayasan.' }}" 
                               class="w-full rounded-xl border border-slate-200 bg-white text-xs px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-slate-700" 
                               required>
                    </div>
                </div>

                <!-- Kustomisasi Tempat & Tanda Tangan -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-slate-100 pt-4">
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700">Tempat Penandatanganan</label>
                        <input type="text" 
                               name="place" 
                               value="{{ $unit->agreementTemplate->place ?? 'Malang' }}" 
                               class="w-full rounded-xl border border-slate-200 bg-white text-xs px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-slate-700" 
                               required>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700">Nama Kepala Sekolah</label>
                        <input type="text" 
                               name="principal_name" 
                               value="{{ $unit->agreementTemplate->principal_name ?? 'Dra. Hj. Mike Supraptiwi, S.Psi, M.Pd' }}" 
                               class="w-full rounded-xl border border-slate-200 bg-white text-xs px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-slate-700 font-semibold" 
                               required>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700">Jabatan Kepala Sekolah</label>
                        <input type="text" 
                               name="principal_title" 
                               value="{{ $unit->agreementTemplate->principal_title ?? 'Kepala Sekolah' }}" 
                               class="w-full rounded-xl border border-slate-200 bg-white text-xs px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-slate-700" 
                               required>
                    </div>
                </div>

                <!-- Form Footer -->
                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl font-bold text-xs shadow-md transition flex items-center gap-1.5">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Format {{ $unit->code }}
                    </button>
                </div>
            </form>
        </div>
    @endforeach
    <script>
        function switchAgreementTab(unitId) {
            // Save active tab ID to localStorage to persist state across form submissions/reloads
            localStorage.setItem('active_agreement_unit_id', unitId);

            // Hide all content tabs
            document.querySelectorAll('.agreement-tab-content').forEach(function(content) {
                content.classList.add('hidden');
            });
            
            // Show active tab
            var contentEl = document.getElementById('agreementTabContent-' + unitId);
            if (contentEl) {
                contentEl.classList.remove('hidden');
            }
            
            // Reset all buttons to inactive style
            document.querySelectorAll('.agreement-tab-btn').forEach(function(btn) {
                btn.className = 'agreement-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50';
            });
            
            // Set active button style
            var activeBtn = document.getElementById('agreementTabBtn-' + unitId);
            if (activeBtn) {
                activeBtn.className = 'agreement-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow';
            }

            // Update hidden active_tab input values
            document.querySelectorAll('.active-tab-input').forEach(function(input) {
                input.value = unitId;
            });

            // Update URL query parameter
            const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?tab=' + unitId;
            window.history.replaceState({ path: newUrl }, '', newUrl);
        }

        // Tab restoration and Quill re-initialization for htmx swaps
        (function() {
            var savedUnitId = localStorage.getItem('active_agreement_unit_id') || '{{ $activeTab }}';
            if (savedUnitId && document.getElementById('agreementTabBtn-' + savedUnitId)) {
                switchAgreementTab(savedUnitId);
            }

            @foreach($units as $unit)
                (function() {
                    var unitId = "{{ $unit->id }}";
                    var editorElement = document.querySelector('#quill-editor-unit_' + unitId);
                    if (editorElement && !editorElement.classList.contains('ql-container')) {
                        var quill = new Quill('#quill-editor-unit_' + unitId, {
                            theme: 'snow',
                            placeholder: 'Tulis isi surat kesanggupan di sini...',
                            modules: {
                                toolbar: [
                                    [{'header': [1, 2, 3, false]}],
                                    ['bold', 'italic', 'underline', 'strike'],
                                    [{'list': 'ordered'}, {'list': 'bullet'}],
                                    ['clean']
                                ]
                            }
                        });

                        var form = document.querySelector('#agreementTabContent-unit_' + unitId + ' form');
                        if (form) {
                            form.addEventListener('submit', function() {
                                var hiddenInput = document.getElementById('hidden-content-unit_' + unitId);
                                if (hiddenInput) {
                                    hiddenInput.value = quill.root.innerHTML;
                                }
                            });
                        }
                    }
                })();
            @endforeach
        })();
    </script>

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
@endsection
