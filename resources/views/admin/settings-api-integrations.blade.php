@extends('layouts.admin')

@section('title', 'Integrasi API & Aplikasi - Admin Panel')
@section('page_title', 'Integrasi API & Aplikasi')

@section('content')
<div class="w-full space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                <i data-lucide="blocks" class="w-5 h-5 text-brand-emerald"></i>
                Integrasi API & Koneksi Aplikasi
            </h1>
            <p class="text-xs text-slate-500 mt-1">Penyediaan data pendaftaran SPMB agar dapat disinkronkan dengan aplikasi eksternal (SANS HRD, SANS SD, SANS SMP, SANS PAUD).</p>
        </div>
        <div class="bg-amber-50 border border-amber-200 text-amber-800 text-[10px] font-bold px-3 py-1.5 rounded-full flex items-center gap-1">
            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
            MODE SIMULASI / DEMO
        </div>
    </div>

    <!-- Alert Warning Mock Data -->
    <div class="bg-amber-50/50 border border-amber-200 rounded-2xl p-5 flex items-start gap-4 shadow-sm">
        <div class="h-10 w-10 bg-amber-100 text-amber-800 rounded-xl flex items-center justify-center flex-shrink-0">
            <i data-lucide="info" class="w-5 h-5"></i>
        </div>
        <div class="space-y-1">
            <h4 class="text-xs font-bold text-amber-900">Catatan Pengembangan (Developer Notice)</h4>
            <p class="text-[11px] text-amber-700 leading-relaxed">
                Halaman ini saat ini memuat <strong>data dummy (hardcoded)</strong> untuk mendemonstrasikan antarmuka manajemen API. 
                Token akses dan log integrasi yang tertera di bawah adalah simulasi. Modul autentikasi token riil menggunakan JWT/OAuth2 di sisi backend sedang dikembangkan oleh tim pengembang.
            </p>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Keys & Webhooks (Span 2) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- API Keys Card -->
            <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <div>
                        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Aplikasi Terkoneksi & Token Akses</h3>
                        <p class="text-[10px] text-slate-400 mt-0.5">Daftar kunci API aktif untuk masing-masing modul SANS.</p>
                    </div>
                    <button type="button" onclick="triggerMockAction('create_token')" class="bg-brand-emerald hover-emerald text-white px-3 py-1.5 rounded-xl text-[10px] font-bold shadow transition flex items-center gap-1">
                        <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i> Tambah Koneksi
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50 text-[10px] text-slate-500 uppercase tracking-wider font-extrabold">
                                <th class="py-3 px-5">Nama Aplikasi</th>
                                <th class="py-3 px-5">Client Token / ID</th>
                                <th class="py-3 px-5">Hak Akses</th>
                                <th class="py-3 px-5">Status</th>
                                <th class="py-3 px-5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                            <!-- sans-sd -->
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-4 px-5">
                                    <div class="font-bold text-slate-800">sans-sd</div>
                                    <div class="text-[9px] text-slate-400 mt-0.5 font-semibold">Sistem Akademik SD</div>
                                </td>
                                <td class="py-4 px-5 font-mono text-[10px]">
                                    <div class="flex items-center gap-1.5">
                                        <input type="password" readonly id="tok-sd" value="spmb_tok_sd_7x2v89q10wl9283" class="bg-transparent border-none p-0 focus:outline-none w-36 select-all font-mono text-[10px]">
                                        <button onclick="toggleTokenVisibility('tok-sd')" class="text-slate-400 hover:text-slate-600">
                                            <i id="eye-tok-sd" data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="py-4 px-5">
                                    <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[9px] font-semibold">read:candidates</span>
                                </td>
                                <td class="py-4 px-5">
                                    <span class="inline-flex items-center gap-1 text-[10px] text-emerald-600 font-extrabold">
                                        <span class="h-1.5 w-1.5 bg-emerald-500 rounded-full animate-ping"></span> Aktif
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-center space-x-1">
                                    <button onclick="triggerMockAction('regenerate', 'sans-sd')" class="p-1 text-slate-400 hover:text-slate-600" title="Regenerasi Token">
                                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button onclick="triggerMockAction('revoke', 'sans-sd')" class="p-1 text-red-400 hover:text-red-600" title="Hapus Akses">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- sans-smp -->
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-4 px-5">
                                    <div class="font-bold text-slate-800">sans-smp</div>
                                    <div class="text-[9px] text-slate-400 mt-0.5 font-semibold">Sistem Akademik SMP</div>
                                </td>
                                <td class="py-4 px-5 font-mono text-[10px]">
                                    <div class="flex items-center gap-1.5">
                                        <input type="password" readonly id="tok-smp" value="spmb_tok_smp_9a1x0837vlb2931" class="bg-transparent border-none p-0 focus:outline-none w-36 select-all font-mono text-[10px]">
                                        <button onclick="toggleTokenVisibility('tok-smp')" class="text-slate-400 hover:text-slate-600">
                                            <i id="eye-tok-smp" data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="py-4 px-5">
                                    <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[9px] font-semibold">read:candidates</span>
                                </td>
                                <td class="py-4 px-5">
                                    <span class="inline-flex items-center gap-1 text-[10px] text-emerald-600 font-extrabold">
                                        <span class="h-1.5 w-1.5 bg-emerald-500 rounded-full animate-ping"></span> Aktif
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-center space-x-1">
                                    <button onclick="triggerMockAction('regenerate', 'sans-smp')" class="p-1 text-slate-400 hover:text-slate-600" title="Regenerasi Token">
                                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button onclick="triggerMockAction('revoke', 'sans-smp')" class="p-1 text-red-400 hover:text-red-600" title="Hapus Akses">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- sans-paud -->
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-4 px-5">
                                    <div class="font-bold text-slate-800">sans-paud</div>
                                    <div class="text-[9px] text-slate-400 mt-0.5 font-semibold">Sistem Akademik PAUD</div>
                                </td>
                                <td class="py-4 px-5 font-mono text-[10px]">
                                    <div class="flex items-center gap-1.5">
                                        <input type="password" readonly id="tok-paud" value="spmb_tok_paud_5c2z9830wmn9283" class="bg-transparent border-none p-0 focus:outline-none w-36 select-all font-mono text-[10px]">
                                        <button onclick="toggleTokenVisibility('tok-paud')" class="text-slate-400 hover:text-slate-600">
                                            <i id="eye-tok-paud" data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="py-4 px-5">
                                    <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[9px] font-semibold">read:candidates</span>
                                </td>
                                <td class="py-4 px-5">
                                    <span class="inline-flex items-center gap-1.5 text-[10px] text-slate-400 font-bold">
                                        <span class="h-1.5 w-1.5 bg-slate-300 rounded-full"></span> Nonaktif
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-center space-x-1">
                                    <button onclick="triggerMockAction('regenerate', 'sans-paud')" class="p-1 text-slate-400 hover:text-slate-600" title="Regenerasi Token">
                                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button onclick="triggerMockAction('revoke', 'sans-paud')" class="p-1 text-red-400 hover:text-red-600" title="Hapus Akses">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- sans-hrd -->
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-4 px-5">
                                    <div class="font-bold text-slate-800">sans-hrd</div>
                                    <div class="text-[9px] text-slate-400 mt-0.5 font-semibold">Sistem Kepegawaian HRD</div>
                                </td>
                                <td class="py-4 px-5 font-mono text-[10px]">
                                    <div class="flex items-center gap-1.5">
                                        <input type="password" readonly id="tok-hrd" value="spmb_tok_hrd_1d0x923vmn98319" class="bg-transparent border-none p-0 focus:outline-none w-36 select-all font-mono text-[10px]">
                                        <button onclick="toggleTokenVisibility('tok-hrd')" class="text-slate-400 hover:text-slate-600">
                                            <i id="eye-tok-hrd" data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="py-4 px-5">
                                    <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[9px] font-semibold">read:committees</span>
                                </td>
                                <td class="py-4 px-5">
                                    <span class="inline-flex items-center gap-1 text-[10px] text-emerald-600 font-extrabold">
                                        <span class="h-1.5 w-1.5 bg-emerald-500 rounded-full animate-ping"></span> Aktif
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-center space-x-1">
                                    <button onclick="triggerMockAction('regenerate', 'sans-hrd')" class="p-1 text-slate-400 hover:text-slate-600" title="Regenerasi Token">
                                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button onclick="triggerMockAction('revoke', 'sans-hrd')" class="p-1 text-red-400 hover:text-red-600" title="Hapus Akses">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Webhook Settings Card -->
            <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Webhook Listener (Push Notification)</h3>
                    <p class="text-[10px] text-slate-400 mt-0.5">Kirim notifikasi HTTP POST otomatis ke server Anda setiap kali terjadi event tertentu.</p>
                </div>
                <form onsubmit="handleMockWebhook(event)" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Payload URL Webhook</label>
                        <input type="url" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-xs font-mono text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-emerald" 
                            placeholder="https://sans-sd.sch.id/api/v1/webhook-receiver" value="https://sans-sd.sch.id/api/v1/webhook-receiver">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Webhook Secret Token</label>
                            <input type="password" value="whsec_83nv91ns982nw81hsbw91js" readonly class="w-full bg-slate-100 border border-slate-300 rounded-xl px-4 py-2.5 text-xs font-mono text-slate-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Pilih Event Pemicu (Triggers)</label>
                            <div class="space-y-2 mt-1">
                                <label class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                                    <input type="checkbox" checked class="rounded border-slate-300 text-brand-emerald focus:ring-brand-emerald">
                                    candidate.paid (Lunas Observasi)
                                </label>
                                <label class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                                    <input type="checkbox" checked class="rounded border-slate-300 text-brand-emerald focus:ring-brand-emerald">
                                    candidate.verified (Diterima SD/SMP)
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow transition flex items-center gap-1.5">
                            <i data-lucide="save" class="w-4 h-4"></i> Simpan Webhook
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <!-- Right: Documentation Preview (Span 1) -->
        <div class="space-y-6">
            
            <div class="bg-slate-900 rounded-2xl shadow-lg border border-slate-800 text-white overflow-hidden flex flex-col h-full">
                <!-- Doc Header -->
                <div class="bg-slate-950 px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-extrabold text-brand-yellow uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="code" class="w-4 h-4"></i> Dokumentasi API
                    </span>
                    <span class="bg-emerald-500/10 text-emerald-400 text-[9px] px-2 py-0.5 rounded border border-emerald-500/20 font-bold">GET</span>
                </div>
                
                <!-- Doc Body -->
                <div class="p-6 flex-grow space-y-4">
                    <div>
                        <h4 class="text-xs font-extrabold text-slate-200">1. Endpoint Mengambil Data Pendaftar</h4>
                        <p class="text-[10px] text-slate-400 mt-1 leading-relaxed">Panggil URL berikut dari backend SANS SD/SMP menggunakan autentikasi bearer token di header.</p>
                        <div class="bg-slate-950 rounded-xl p-3 mt-2 text-[10px] font-mono text-emerald-400 break-all select-all">
                            GET http://sans-spmb.test/api/v1/candidates?status=paid
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-extrabold text-slate-200">2. Header Autentikasi</h4>
                        <div class="bg-slate-950 rounded-xl p-3 mt-2 text-[10px] font-mono text-slate-300 space-y-1">
                            <div>Authorization: Bearer &lt;TOKEN_APLIKASI&gt;</div>
                            <div>Accept: application/json</div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-extrabold text-slate-200">3. Contoh Skema Respons JSON</h4>
                        <div class="bg-slate-950 rounded-xl p-4 mt-2 font-mono text-[9px] text-brand-yellow overflow-x-auto leading-relaxed max-h-56">
<pre>{
  "status": "success",
  "data": [
    {
      "id": 4,
      "registration_no": "INV-2026-004",
      "full_name": "Ahmad Raihan",
      "registered_grade": "SD Kelas 1",
      "status": "paid",
      "parent": {
        "name": "Budi Santoso",
        "phone": "08123456789"
      }
    }
  ]
}</pre>
                        </div>
                    </div>
                </div>

                <!-- Doc Footer -->
                <div class="bg-slate-950 px-6 py-4 border-t border-slate-800 text-[10px] text-slate-400">
                    Gunakan Bearer Token di atas untuk melakukan handshaking data otomatis.
                </div>
            </div>

        </div>

    </div>
</div>

<script>
    // Visibility toggle for mock tokens
    function toggleTokenVisibility(inputId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById('eye-' + inputId);
        if (!input || !icon) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            input.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }

        if (window.lucide) {
            lucide.createIcons();
        }
    }

    // Save webhook simulated toast
    function handleMockWebhook(e) {
        e.preventDefault();
        showToast('Webhook payload URL berhasil disimpan! (Mode Simulasi)', 'success');
    }

    // Generic simulated action triggers
    function triggerMockAction(actionType, appName = '') {
        if (actionType === 'create_token') {
            showToast('Simulasi: Gagal menambahkan koneksi baru. Hak akses role admin Anda dibatasi dalam mode demo.', 'error');
        } else if (actionType === 'regenerate') {
            showToast(`Simulasi: Token untuk "${appName}" berhasil diregenerasi secara virtual.`, 'success');
        } else if (actionType === 'revoke') {
            showToast(`Simulasi: Hak akses untuk "${appName}" telah dicabut secara virtual.`, 'error');
        }
    }
</script>
@endsection
