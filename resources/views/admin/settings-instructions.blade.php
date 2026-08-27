@extends('layouts.admin')

@section('title', 'Instruksi Daftar Ulang - Admin Panel')
@section('page_title', 'Instruksi Daftar Ulang')

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
        min-height: 180px;
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
    /* Hide Quill link/formula tooltip when it has the hidden class */
    .ql-tooltip.ql-hidden {
        display: none !important;
    }
</style>

<div id="instructions-settings-container" hx-boost="true" hx-target="#instructions-settings-container" hx-select="#instructions-settings-container" class="w-full space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center gap-3">
            <div class="h-12 w-12 bg-emerald-50 dark:bg-emerald-950/20 text-brand-emerald rounded-2xl flex items-center justify-center shadow-inner">
                <i data-lucide="scroll-text" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-800">Instruksi & Prosedur Daftar Ulang</h1>
                <p class="text-xs text-slate-500 mt-1">Instruksi ini akan ditampilkan kepada wali murid pada halaman rincian tagihan setelah menandatangani surat kesanggupan.</p>
            </div>
        </div>
    </div>

    <!-- Main Content Form -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
        <form method="POST" action="{{ route('admin.spmb-settings.instructions.save') }}" id="instructions-form" hx-boost="false" class="space-y-6">
            @csrf
            <input type="hidden" name="unit_id" value="{{ $selectedUnitId }}">

            <!-- Unit Selector Tabs (Only for Super Admin) -->
            @if($isSuperAdmin)
                <div class="flex flex-wrap gap-2 border-b border-slate-150 pb-4">
                    @foreach($units as $unit)
                        <button type="button" 
                            hx-get="{{ route('admin.spmb-settings.instructions') }}?unit_id={{ $unit->id }}" 
                            hx-target="#instructions-settings-container" 
                            hx-select="#instructions-settings-container" 
                            hx-push-url="true"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $selectedUnitId == $unit->id ? 'bg-brand-emerald text-white shadow' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                            {{ strtoupper($unit->name) }}
                        </button>
                    @endforeach
                </div>
            @else
                <div class="flex items-center justify-between pb-4 border-b border-slate-150">
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-slate-700">Unit Sekolah Anda</span>
                        <p class="text-[10px] text-slate-400">Pemberitahuan & instruksi daftar ulang disimpan khusus per unit.</p>
                    </div>
                    <div>
                        <span class="px-3 py-1.5 bg-emerald-50 text-brand-emerald font-bold rounded-lg text-xs uppercase tracking-wide">
                            Unit {{ auth()->user()->spmbUnit->name }}
                        </span>
                    </div>
                </div>
            @endif
            
            <div class="grid grid-cols-1 gap-6">
                <!-- 1. Instruksi Belum Lunas -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Instruksi Saat Tagihan Belum Lunas</label>
                    <p class="text-[10px] text-slate-400">Instruksi pembayaran cicilan, batas waktu, dan metode online yang dapat diakses wali murid.</p>
                    
                    <!-- Quill container -->
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                        <div id="quill-editor-instructions-unpaid"></div>
                    </div>
                    <input type="hidden" name="re_registration_instructions_unpaid" id="hidden-instructions-unpaid">
                </div>

                <!-- 2. Instruksi Sudah Lunas -->
                <div class="space-y-2 pt-6 border-t border-slate-100">
                    <label class="block text-xs font-bold text-slate-700">Instruksi Saat Tagihan Sudah Lunas (Resmi Terdaftar)</label>
                    <p class="text-[10px] text-slate-400">Ucapan selamat, panduan unduh berkas kelulusan (SKP), kwitansi resmi, dan langkah daftar ulang selanjutnya.</p>
                    
                    <!-- Quill container -->
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                        <div id="quill-editor-instructions-completed"></div>
                    </div>
                    <input type="hidden" name="re_registration_instructions_completed" id="hidden-instructions-completed">
                </div>
            </div>

            <!-- Form Footer -->
            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl font-bold text-xs shadow-md transition flex items-center gap-1.5">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Instruksi
                </button>
            </div>
        </form>
        <script>
            (function() {
                // Robust Quill initialization helper to prevent duplicate toolbars and handle HTMX swaps cleanly
                function safeInitQuill(selector, options, content) {
                    var container = document.querySelector(selector);
                    if (!container) return null;

                    // Remove any existing toolbar sibling above the editor
                    var prevEl = container.previousElementSibling;
                    if (prevEl && prevEl.classList.contains('ql-toolbar')) {
                        prevEl.parentNode.removeChild(prevEl);
                    }

                    // Reset container classes and content
                    container.className = '';
                    container.innerHTML = content;

                    // Initialize Quill
                    return new Quill(selector, options);
                }

                var options = {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline'],
                            [{'list': 'ordered'}, {'list': 'bullet'}],
                            ['clean']
                        ]
                    }
                };

                // Initialize Unpaid Instructions Editor
                var unpaidHtml = {!! json_encode($settings['re_registration_instructions_unpaid'] ?? '') !!};
                var unpaidQuill = safeInitQuill('#quill-editor-instructions-unpaid', Object.assign({}, options, {
                    placeholder: 'Tulis instruksi saat tagihan belum lunas...'
                }), unpaidHtml);

                // Initialize Completed Instructions Editor
                var completedHtml = {!! json_encode($settings['re_registration_instructions_completed'] ?? '') !!};
                var completedQuill = safeInitQuill('#quill-editor-instructions-completed', Object.assign({}, options, {
                    placeholder: 'Tulis instruksi saat pendaftaran sudah lunas...'
                }), completedHtml);

                // Sync Quill editor state to hidden input fields upon form submit
                var form = document.getElementById('instructions-form');
                if (form) {
                    form.addEventListener('submit', function() {
                        if (unpaidQuill) {
                            document.querySelector('#hidden-instructions-unpaid').value = unpaidQuill.root.innerHTML;
                        }
                        if (completedQuill) {
                            document.querySelector('#hidden-instructions-completed').value = completedQuill.root.innerHTML;
                        }
                    });
                }
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
</div>
@endsection
