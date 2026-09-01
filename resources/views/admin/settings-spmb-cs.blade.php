@extends('layouts.admin')

@section('title', 'Customer Service & Kontak Panitia - Admin SPMB')
@section('page_title', 'Customer Service & Kontak Panitia')

@section('content')
<div id="spmb-cs-container" class="w-full space-y-8">
    
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-850 dark:text-white">Customer Service & Kontak Panitia</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola nomor WhatsApp layanan Customer Service pusat dan kontak admin masing-masing unit sekolah.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-800 text-xs font-bold shadow-sm">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('admin.spmb-settings.cs.save') }}" method="POST" class="space-y-8">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left Side (Form Settings): 7 Cols -->
            <div class="lg:col-span-7 space-y-6">

                <!-- Card 1: Pusat Layanan CS Utama -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-100 dark:border-slate-800 space-y-5">
                    <div class="flex items-center gap-2.5 border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <i data-lucide="headphones" class="w-4.5 h-4.5"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-800 dark:text-white text-sm">Customer Service Pusat (Umum)</h3>
                            <p class="text-[11px] text-slate-400">Kontak yang dihubungi dari banner "Pusat Bantuan & Konsultasi SPMB".</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">No. WhatsApp CS Pusat</label>
                            <input type="text" name="spmb_cs_whatsapp" id="input_cs_wa" required
                                value="{{ $settings['spmb_cs_whatsapp'] }}" 
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-850 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs font-bold font-mono" 
                                placeholder="Misal: 081234567890">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Nama CS / Label Petugas</label>
                            <input type="text" name="spmb_cs_name" id="input_cs_name"
                                value="{{ $settings['spmb_cs_name'] }}" 
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-850 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs font-semibold" 
                                placeholder="Misal: Customer Service SPMB">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Jam Layanan Operasional CS</label>
                        <input type="text" name="spmb_cs_hours"
                            value="{{ $settings['spmb_cs_hours'] }}" 
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-850 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs font-semibold" 
                            placeholder="Misal: Senin - Jumat, 08:00 - 15:00 WIB">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Template Pesan WhatsApp Otomatis (Prefilled Text)</label>
                        <textarea name="spmb_cs_message" rows="3" 
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-850 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs font-medium leading-relaxed" 
                            placeholder="Pesan yang otomatis tertulis saat calon pendaftar klik Hubungi CS...">{{ $settings['spmb_cs_message'] }}</textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-4">
                        <h4 class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Kustomisasi Teks Banner Portal</h4>
                        
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Judul Banner</label>
                            <input type="text" name="spmb_cs_card_title" id="input_card_title"
                                value="{{ $settings['spmb_cs_card_title'] }}" 
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-2.5 text-slate-850 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs font-bold">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Deskripsi Banner</label>
                            <textarea name="spmb_cs_card_desc" id="input_card_desc" rows="2"
                                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-2 text-slate-850 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xs font-medium">{{ $settings['spmb_cs_card_desc'] }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Kontak WhatsApp per Unit Sekolah -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-100 dark:border-slate-800 space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <i data-lucide="building-2" class="w-4.5 h-4.5"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-slate-800 dark:text-white text-sm">Kontak Admin Khusus per Unit Sekolah</h3>
                                <p class="text-[11px] text-slate-400">Nomor ini yang terhubung saat pendaftar klik "Admin WA" di card jenjang atau di dashboard pendaftaran anak.</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach($units as $u)
                            <div class="p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-150 dark:border-slate-800 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-extrabold text-slate-850 dark:text-white flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-lg bg-emerald-600 text-white font-mono text-[10px]">{{ $u->code }}</span>
                                        {{ $u->name }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">No. WhatsApp Admin Unit</label>
                                        <input type="text" name="units[{{ $u->id }}][whatsapp_number]" 
                                            value="{{ $u->whatsapp_number }}" 
                                            placeholder="Contoh: 081234567890" 
                                            class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 dark:text-white font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Nama Petugas Admin (Opsional)</label>
                                        <input type="text" name="units[{{ $u->id }}][admin_contact_name]" 
                                            value="{{ $u->admin_contact_name }}" 
                                            placeholder="Contoh: Kak Nisa - Admin PAUD" 
                                            class="w-full bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-extrabold transition shadow-md shadow-emerald-600/20 flex items-center justify-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Seluruh Pengaturan Customer Service
                    </button>
                </div>

            </div>

            <!-- Right Side (Live Interactive Preview): 5 Cols -->
            <div class="lg:col-span-5 space-y-6">
                
                <div class="sticky top-8 space-y-6">
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-100 dark:border-slate-800 space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                            <i data-lucide="eye" class="w-4 h-4 text-emerald-600"></i>
                            <h4 class="font-extrabold text-slate-800 dark:text-white text-xs uppercase tracking-wider">Live Preview di Portal Pendaftar</h4>
                        </div>

                        <p class="text-xs text-slate-500 leading-relaxed">
                            Berikut adalah tampilan nyata banner Customer Service yang akan dilihat oleh calon pendaftar di portal utama:
                        </p>

                        <!-- Live Preview Card -->
                        <div class="pt-2">
                            <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-150/80 dark:border-slate-800 shadow-sm flex flex-col items-start gap-4 text-left">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="headphones" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <h4 id="preview_title" class="font-extrabold text-slate-850 dark:text-white text-xs">{{ $settings['spmb_cs_card_title'] }}</h4>
                                        <p id="preview_desc" class="text-[11px] text-slate-400 dark:text-slate-500 line-clamp-2 mt-0.5">{{ $settings['spmb_cs_card_desc'] }}</p>
                                    </div>
                                </div>
                                <div class="w-full pt-1 flex justify-between items-center border-t border-slate-100 dark:border-slate-800/60 mt-1">
                                    <div class="text-[10px] text-slate-400">
                                        <span class="font-bold text-slate-600 dark:text-slate-300 block" id="preview_name">{{ $settings['spmb_cs_name'] }}</span>
                                        <span class="font-mono text-emerald-600" id="preview_wa">{{ $settings['spmb_cs_whatsapp'] }}</span>
                                    </div>
                                    <span class="px-3.5 py-2 bg-emerald-600 text-white rounded-xl text-[11px] font-bold flex items-center gap-1.5 shadow-sm">
                                        <i data-lucide="message-square" class="w-3.5 h-3.5"></i> Konsultasi via WA
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </form>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const inputTitle = document.getElementById('input_card_title');
        const inputDesc = document.getElementById('input_card_desc');
        const inputWa = document.getElementById('input_cs_wa');
        const inputName = document.getElementById('input_cs_name');

        const prevTitle = document.getElementById('preview_title');
        const prevDesc = document.getElementById('preview_desc');
        const prevWa = document.getElementById('preview_wa');
        const prevName = document.getElementById('preview_name');

        if (inputTitle && prevTitle) {
            inputTitle.addEventListener('input', () => prevTitle.innerText = inputTitle.value || 'Pusat Bantuan & Konsultasi SPMB');
        }
        if (inputDesc && prevDesc) {
            inputDesc.addEventListener('input', () => prevDesc.innerText = inputDesc.value || 'Ada pertanyaan seputar persyaratan atau alur masuk?');
        }
        if (inputWa && prevWa) {
            inputWa.addEventListener('input', () => prevWa.innerText = inputWa.value || '081234567890');
        }
        if (inputName && prevName) {
            inputName.addEventListener('input', () => prevName.innerText = inputName.value || 'Customer Service SPMB');
        }
    });
</script>
@endsection
