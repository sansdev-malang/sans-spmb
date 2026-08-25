@extends('layouts.admin')

@section('title', 'Instruksi Daftar Ulang - Admin Panel')
@section('page_title', 'Instruksi Daftar Ulang')

@section('content')
<!-- Quill editor stylesheets and script library -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
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
        min-height: 180px;
        font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-size: 13px;
        color: #334155;
        line-height: 1.6;
    }
</style>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<div class="w-full space-y-6">
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
        <form method="POST" action="{{ route('admin.spmb-settings.instructions.save') }}" id="instructions-form" class="space-y-6">
            @csrf
            
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
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize Unpaid Instructions Editor
        var unpaidContainer = document.querySelector('#quill-editor-instructions-unpaid');
        if (unpaidContainer) {
            var unpaidQuill = new Quill('#quill-editor-instructions-unpaid', {
                theme: 'snow',
                placeholder: 'Tulis instruksi saat tagihan belum lunas...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{'list': 'ordered'}, {'list': 'bullet'}],
                        ['clean']
                    ]
                }
            });
            
            unpaidQuill.root.innerHTML = {!! json_encode($settings['re_registration_instructions_unpaid'] ?? '') !!};
            
            var form = document.getElementById('instructions-form');
            if (form) {
                form.addEventListener('submit', function() {
                    document.querySelector('#hidden-instructions-unpaid').value = unpaidQuill.root.innerHTML;
                });
            }
        }

        // Initialize Completed Instructions Editor
        var completedContainer = document.querySelector('#quill-editor-instructions-completed');
        if (completedContainer) {
            var completedQuill = new Quill('#quill-editor-instructions-completed', {
                theme: 'snow',
                placeholder: 'Tulis instruksi saat pendaftaran sudah lunas...',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{'list': 'ordered'}, {'list': 'bullet'}],
                        ['clean']
                    ]
                }
            });
            
            completedQuill.root.innerHTML = {!! json_encode($settings['re_registration_instructions_completed'] ?? '') !!};
            
            var form = document.getElementById('instructions-form');
            if (form) {
                form.addEventListener('submit', function() {
                    document.querySelector('#hidden-instructions-completed').value = completedQuill.root.innerHTML;
                });
            }
        }
    });
</script>
@endsection
