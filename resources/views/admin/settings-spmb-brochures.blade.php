@extends('layouts.admin')

@section('title', 'Brosur & Dokumen SPMB - Admin SPMB')
@section('page_title', 'Brosur & Dokumen SPMB')

@section('content')
<div id="spmb-brochures-container" class="w-full space-y-8">
    
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-850 dark:text-white">Brosur & Dokumen SPMB</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola file brosur cetak dan berkas lampiran persyaratan yang dapat diunduh oleh calon orang tua murid pada masing-masing unit sekolah.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800 text-xs font-bold shadow-sm">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('admin.spmb-settings.brochures.save') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($units as $u)
                @php
                    $b = $brochures[$u->id];
                    $code = $b['code'];
                @endphp
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-150/80 dark:border-slate-800 flex flex-col justify-between space-y-6 hover:shadow-md transition">
                    
                    <!-- Unit Header -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-xs shadow-md shadow-emerald-600/20">
                                    {{ strtoupper($u->code) }}
                                </div>
                                <div>
                                    <h3 class="font-extrabold text-slate-850 dark:text-white text-sm">{{ $u->name }}</h3>
                                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Jenjang Pendidikan</span>
                                </div>
                            </div>
                        </div>

                        <!-- 1. Berkas Brosur Utama -->
                        <div class="p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-150 dark:border-slate-800 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-extrabold text-slate-800 dark:text-white flex items-center gap-1.5">
                                    <i data-lucide="file-text" class="w-4 h-4 text-emerald-600"></i> File Brosur Utama
                                </span>
                                @if(!empty($b['brochure_url']))
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold text-[10px] flex items-center gap-1">
                                        ● Aktif
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-500 font-bold text-[10px]">
                                        Belum Ada
                                    </span>
                                @endif
                            </div>

                            @if(!empty($b['brochure_url']))
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-between gap-2">
                                    <a href="{{ $b['brochure_url'] }}" target="_blank" class="text-[11px] text-emerald-600 hover:text-emerald-700 font-bold flex items-center gap-1.5 truncate">
                                        <i data-lucide="external-link" class="w-3.5 h-3.5 flex-shrink-0"></i> Buka Brosur Aktif
                                    </a>
                                    <label class="flex items-center gap-1 text-[10px] font-bold text-rose-600 cursor-pointer flex-shrink-0 hover:underline">
                                        <input type="checkbox" name="delete_unit_{{ $code }}_brochure" value="1" class="rounded text-rose-600 focus:ring-rose-500 w-3 h-3">
                                        Hapus
                                    </label>
                                </div>
                            @endif

                            <div class="space-y-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Unggah Berkas Baru (PDF / Gambar)</label>
                                    <input type="file" name="unit_{{ $code }}_brochure_file" accept=".pdf,.jpg,.jpeg,.png"
                                        class="w-full text-[11px] text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[11px] file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                                </div>
                                <div class="pt-1">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Atau Tautan Download Eksternal (URL)</label>
                                    <input type="url" name="unit_{{ $code }}_brochure_url_custom" 
                                        placeholder="Misal: https://drive.google.com/..." 
                                        value="{{ str_starts_with($b['brochure_url'], 'http') && !str_contains($b['brochure_url'], '/storage/') ? $b['brochure_url'] : '' }}"
                                        class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                </div>
                            </div>
                        </div>

                        <!-- 2. Berkas Lampiran Pendukung -->
                        <div class="p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-150 dark:border-slate-800 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-extrabold text-slate-800 dark:text-white flex items-center gap-1.5">
                                    <i data-lucide="paperclip" class="w-4 h-4 text-emerald-600"></i> Lampiran Dokumen Tambahan
                                </span>
                                @if(!empty($b['attachment_url']))
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold text-[10px]">
                                        ● Ada
                                    </span>
                                @endif
                            </div>

                            @if(!empty($b['attachment_url']))
                                <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-between gap-2">
                                    <a href="{{ $b['attachment_url'] }}" target="_blank" class="text-[11px] text-emerald-600 hover:text-emerald-700 font-bold flex items-center gap-1.5 truncate">
                                        <i data-lucide="external-link" class="w-3.5 h-3.5 flex-shrink-0"></i> Buka Lampiran
                                    </a>
                                    <label class="flex items-center gap-1 text-[10px] font-bold text-rose-600 cursor-pointer flex-shrink-0 hover:underline">
                                        <input type="checkbox" name="delete_unit_{{ $code }}_attachment" value="1" class="rounded text-rose-600 focus:ring-rose-500 w-3 h-3">
                                        Hapus
                                    </label>
                                </div>
                            @endif

                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Unggah Lampiran (PDF / Gambar)</label>
                                <input type="file" name="unit_{{ $code }}_attachment_file" accept=".pdf,.jpg,.jpeg,.png"
                                    class="w-full text-[11px] text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[11px] file:font-bold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 cursor-pointer">
                            </div>
                        </div>

                    </div>

                    <!-- Footer Action -->
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-[10px] text-slate-400 block text-center">
                            Tombol unduh brosur pada portal unit {{ $u->name }} otomatis aktif setelah disimpan.
                        </span>
                    </div>

                </div>
            @endforeach
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-extrabold transition shadow-md shadow-emerald-600/20 flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i> Simpan Seluruh Brosur & Dokumen
            </button>
        </div>

    </form>

</div>
@endsection
