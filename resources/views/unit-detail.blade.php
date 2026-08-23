@extends('layouts.portal')

@section('title')
    Sekolah Anak Saleh - Detail Jenjang {{ $unit->name }}
@endsection

@section('content')
@php
    $uCode = strtolower($unit->code);
    $schoolName = \App\Models\Setting::get('school_name', 'Sekolah Anak Saleh');
    $uDesc = \App\Models\Setting::get('unit_' . $uCode . '_desc', '');
    $uContent = array_filter(explode(',', \App\Models\Setting::get('unit_' . $uCode . '_content', '')));
    $uFeatures = array_filter(explode(',', \App\Models\Setting::get('unit_' . $uCode . '_features', '')));
    $uRequirements = array_filter(explode(',', \App\Models\Setting::get('unit_' . $uCode . '_requirements', '')));
    $uFlow = array_filter(explode(',', \App\Models\Setting::get('unit_' . $uCode . '_flow', '')));
    $uBrochureUrl = \App\Models\Setting::get('unit_' . $uCode . '_brochure_url', '');
    $uAttachmentUrl = \App\Models\Setting::get('unit_' . $uCode . '_attachment_url', '');
    
    $iconName = 'book-open';
    if ($uCode === 'paud') {
        $iconName = 'car';
    } elseif ($uCode === 'smp') {
        $iconName = 'flask-conical';
    }
@endphp

<!-- Header Hero Banner for the Unit -->
<div class="relative bg-slate-50 dark:bg-slate-950 overflow-hidden py-16 border-b border-slate-100 dark:border-slate-800 transition">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 text-center space-y-4">
        <div class="inline-flex items-center gap-2 bg-emerald-50 dark:bg-emerald-950/40 text-brand-emerald dark:text-emerald-400 font-extrabold text-[10px] uppercase tracking-widest px-3.5 py-1.5 rounded-full shadow-sm">
            <i data-lucide="{{ $iconName }}" class="w-4 h-4"></i> Program Pendidikan {{ $unit->name }}
        </div>
        
        <h1 class="text-3xl md:text-5xl font-black text-custom-primary dark:text-emerald-400 leading-tight">
            Detail Informasi & Pendaftaran {{ $unit->name }}
        </h1>
        
        <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 font-medium max-w-xl mx-auto leading-relaxed">
            Temukan kurikulum kelas, syarat kelengkapan berkas, dan prosedur pendaftaran awal untuk jenjang {{ $unit->name }} di {{ $schoolName }}.
        </p>
    </div>
</div>

<!-- Detailed Specifications Grid -->
<div class="bg-white dark:bg-slate-900 py-16 transition">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Column: Deskripsi, Kurikulum, & Flagships (6 Columns) -->
            <div class="lg:col-span-6 space-y-6">
                
                <!-- About -->
                <div class="bg-slate-50 dark:bg-slate-950 p-8 rounded-3xl border border-slate-200/60 dark:border-slate-800/80 shadow-sm space-y-3">
                    <h3 class="text-sm font-extrabold text-custom-primary dark:text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="info" class="w-4.5 h-4.5 text-emerald-600"></i> Tentang Jenjang {{ $unit->name }}
                    </h3>
                    <p class="text-xs text-slate-600 dark:text-slate-350 leading-relaxed font-medium">
                        {{ $uDesc }}
                    </p>
                </div>

                <!-- Kurikulum / Layanan Kelas -->
                <div class="bg-slate-50 dark:bg-slate-950 p-8 rounded-3xl border border-slate-200/60 dark:border-slate-800/80 shadow-sm space-y-4">
                    <h3 class="text-sm font-extrabold text-custom-primary dark:text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="layers" class="w-4.5 h-4.5 text-emerald-600"></i> Kurikulum & Layanan Pendidikan
                    </h3>
                    <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-350 font-medium">
                        @foreach($uContent as $item)
                            <li class="flex items-start gap-3">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 mt-1.5 flex-shrink-0"></span>
                                <span>{{ trim($item) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Flagship Programs -->
                <div class="bg-slate-50 dark:bg-slate-950 p-8 rounded-3xl border border-slate-200/60 dark:border-slate-800/80 shadow-sm space-y-4">
                    <h3 class="text-sm font-extrabold text-custom-primary dark:text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="sparkles" class="w-4.5 h-4.5 text-emerald-600"></i> Program Unggulan Utama
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs text-slate-600 dark:text-slate-350 font-semibold">
                        @foreach($uFeatures as $feat)
                            <div class="flex items-center gap-2">
                                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 flex-shrink-0"></i>
                                <span>{{ trim($feat) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Right Column: Requirements & Admission Steps Flow (6 Columns) -->
            <div class="lg:col-span-6 space-y-6">
                
                <!-- Requirements -->
                <div class="bg-slate-50 dark:bg-slate-950 p-8 rounded-3xl border border-slate-200/60 dark:border-slate-800/80 shadow-sm space-y-4">
                    <h3 class="text-sm font-extrabold text-custom-primary dark:text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="file-check" class="w-4.5 h-4.5 text-emerald-600"></i> Syarat & Ketentuan Pendaftaran
                    </h3>
                    <ul class="space-y-3.5 text-xs text-slate-600 dark:text-slate-350 font-medium">
                        @foreach($uRequirements as $req)
                            <li class="flex items-start gap-3">
                                <i data-lucide="check-square" class="w-4 h-4 text-brand-yellow flex-shrink-0 mt-0.5"></i>
                                <span>{{ trim($req) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Registration & Selection Flow -->
                <div class="bg-[#fcfdfd] dark:bg-slate-950/65 p-8 rounded-3xl border-2 border-slate-150 dark:border-slate-850 shadow-md space-y-4">
                    <h3 class="text-sm font-extrabold text-custom-primary dark:text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="git-branch" class="w-4.5 h-4.5 text-emerald-600"></i> Tahapan Alur Seleksi
                    </h3>
                    <div class="space-y-4 relative pl-4 border-l border-slate-200 dark:border-slate-800">
                        @foreach($uFlow as $flowIndex => $step)
                            <div class="relative">
                                <!-- Numeric step circle badge -->
                                <span class="absolute -left-[23.5px] top-0.5 h-4.5 w-4.5 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-900 flex items-center justify-center font-bold text-[9px] text-white">
                                    {{ $flowIndex + 1 }}
                                </span>
                                <p class="text-xs text-slate-700 dark:text-slate-350 font-bold leading-relaxed">
                                    {{ trim($step) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Unduhan Brosur & Dokumen Pendukung -->
                <div class="bg-slate-50 dark:bg-slate-950 p-8 rounded-3xl border border-slate-200/60 dark:border-slate-800/80 shadow-sm space-y-4">
                    <h3 class="text-sm font-extrabold text-custom-primary dark:text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="download-cloud" class="w-4.5 h-4.5 text-emerald-600"></i> Brosur & Dokumen Pendukung
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Silakan unduh brosur cetak dan berkas persyaratan pendaftaran di bawah ini untuk disimpan secara offline.</p>
                    
                    <div class="flex flex-col sm:flex-row gap-3 pt-2">
                        @if(!empty($uBrochureUrl))
                            <a href="{{ $uBrochureUrl }}" target="_blank" download class="flex-1 bg-custom-primary hover:opacity-90 text-white py-3 px-4 rounded-xl text-xs font-bold flex items-center justify-center gap-2 shadow-sm">
                                <i data-lucide="file-text" class="w-4 h-4"></i> Unduh Brosur
                            </a>
                        @else
                            <button disabled class="flex-1 bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-600 py-3 px-4 rounded-xl text-xs font-bold flex items-center justify-center gap-2 cursor-not-allowed">
                                <i data-lucide="file-x" class="w-4 h-4"></i> Brosur Belum Rilis
                            </button>
                        @endif

                        @if(!empty($uAttachmentUrl))
                            <a href="{{ $uAttachmentUrl }}" target="_blank" download class="flex-1 border border-custom-primary text-custom-primary dark:border-emerald-500 dark:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-900 py-3 px-4 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition">
                                <i data-lucide="paperclip" class="w-4 h-4"></i> Lampiran Berkas
                            </a>
                        @else
                            <button disabled class="flex-1 border border-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-600 py-3 px-4 rounded-xl text-xs font-bold flex items-center justify-center gap-2 cursor-not-allowed">
                                <i data-lucide="paperclip" class="w-4 h-4"></i> Tidak Ada Lampiran
                            </button>
                        @endif
                    </div>
                </div>

            </div>

        </div>

        <!-- Back & Register Action Box -->
        <div class="mt-16 bg-slate-50 dark:bg-slate-950 p-8 rounded-3xl border border-slate-150 dark:border-slate-800 text-center space-y-6">
            <h3 class="text-xl font-extrabold text-slate-800 dark:text-white">Tertarik Mendaftarkan Ananda di Jenjang {{ $unit->name }}?</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-lg mx-auto">Klik tombol daftar di bawah untuk kembali ke halaman utama dan melakukan pengisian formulir pendaftaran awal secara instan.</p>
            <div class="flex justify-center gap-4">
                <a href="/#pendaftaran" class="bg-brand-yellow hover:opacity-90 text-slate-900 px-6 py-3 rounded-full font-bold text-xs shadow transition">
                    Daftar Sekarang &rarr;
                </a>
                <a href="/" class="border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 px-6 py-3 rounded-full font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-900 transition">
                    Kembali Ke Beranda
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
