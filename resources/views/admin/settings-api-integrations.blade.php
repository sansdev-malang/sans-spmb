@extends('layouts.admin')

@section('title', 'Integrasi API & Aplikasi - Admin Panel')
@section('page_title', 'Integrasi API & Aplikasi')

@php
    $curTab = request('tab', session('active_tab', 'clients'));
@endphp

@push('styles')
<style>
    .custom-modal-scroll, .custom-code-scroll, pre {
        scrollbar-width: thin;
        scrollbar-color: rgba(148, 163, 184, 0.35) transparent;
    }
    .custom-modal-scroll::-webkit-scrollbar, .custom-code-scroll::-webkit-scrollbar, pre::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .custom-modal-scroll::-webkit-scrollbar-track, .custom-code-scroll::-webkit-scrollbar-track, pre::-webkit-scrollbar-track {
        background: rgba(15, 23, 42, 0.3);
        border-radius: 9999px;
    }
    .custom-modal-scroll::-webkit-scrollbar-thumb, .custom-code-scroll::-webkit-scrollbar-thumb, pre::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.35);
        border-radius: 9999px;
    }
    .custom-modal-scroll::-webkit-scrollbar-thumb:hover, .custom-code-scroll::-webkit-scrollbar-thumb:hover, pre::-webkit-scrollbar-thumb:hover {
        background: rgba(148, 163, 184, 0.6);
    }
</style>
@endpush

@section('content')
<div class="w-full space-y-6">

    <!-- Header & Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Stat 1: Total Clients -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-100/60 dark:border-emerald-800/40 flex items-center justify-center font-bold text-lg">
                <i data-lucide="layers" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Aplikasi</span>
                <h3 class="text-xl font-extrabold text-slate-800 dark:text-slate-100">{{ $stats['total_clients'] ?? 0 }}</h3>
            </div>
        </div>

        <!-- Stat 2: Active Clients -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-100/60 dark:border-blue-800/40 flex items-center justify-center font-bold text-lg">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Aplikasi Aktif</span>
                <h3 class="text-xl font-extrabold text-slate-800 dark:text-slate-100">{{ $stats['active_clients'] ?? 0 }}</h3>
            </div>
        </div>

        <!-- Stat 3: Inbound Pull Today -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-100/60 dark:border-indigo-800/40 flex items-center justify-center font-bold text-lg">
                <i data-lucide="arrow-down-left" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Inbound Pull (Hari Ini)</span>
                <h3 class="text-xl font-extrabold text-slate-800 dark:text-slate-100">{{ $stats['total_inbound_today'] ?? 0 }}</h3>
            </div>
        </div>

        <!-- Stat 4: Outbound Webhooks Today -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-100/60 dark:border-amber-800/40 flex items-center justify-center font-bold text-lg">
                <i data-lucide="arrow-up-right" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Webhook Push (Hari Ini)</span>
                <h3 class="text-xl font-extrabold text-slate-800 dark:text-slate-100">{{ $stats['total_webhooks_today'] ?? 0 }}</h3>
            </div>
        </div>
    </div>

    <!-- Main Card with Tabs -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden" 
         x-data="{ 
             activeTab: (new URLSearchParams(window.location.search)).get('tab') || '{{ $curTab }}' || 'clients',
             switchTab(tab) {
                 this.activeTab = tab;
                 const url = new URL(window.location);
                 url.searchParams.set('tab', tab);
                 window.history.replaceState({}, '', url);
             }
         }"
         x-init="$watch('activeTab', value => {
             const url = new URL(window.location);
             url.searchParams.set('tab', value);
             window.history.replaceState({}, '', url);
         })">
        
        <!-- Tab Navigation Header -->
        <div class="border-b border-slate-100 dark:border-slate-800 px-6 pt-4 bg-slate-50/50 dark:bg-slate-950/40 flex flex-wrap justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <button type="button" @click="switchTab('clients')" 
                    :class="activeTab === 'clients' ? 'border-brand-emerald text-brand-emerald font-extrabold bg-white dark:bg-slate-800 shadow-sm' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-semibold'"
                    class="px-4 py-2.5 rounded-xl border text-xs transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="server" class="w-4 h-4"></i>
                    Aplikasi Terkoneksi & Token
                    <span class="bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/50 text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $clients->count() }}</span>
                </button>
                <button type="button" @click="switchTab('logs')" 
                    :class="activeTab === 'logs' ? 'border-brand-emerald text-brand-emerald font-extrabold bg-white dark:bg-slate-800 shadow-sm' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-semibold'"
                    class="px-4 py-2.5 rounded-xl border text-xs transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                    Riwayat & Log Audit
                    <span class="bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300/40 dark:border-slate-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $logs->total() }}</span>
                </button>
                <button type="button" @click="switchTab('docs')" 
                    :class="activeTab === 'docs' ? 'border-brand-emerald text-brand-emerald font-extrabold bg-white dark:bg-slate-800 shadow-sm' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 font-semibold'"
                    class="px-4 py-2.5 rounded-xl border text-xs transition flex items-center gap-2 cursor-pointer">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                    Panduan & Dokumentasi API
                </button>
            </div>

            <!-- Action Button for Tab 1 -->
            <div x-show="activeTab === 'clients'" x-cloak style="{{ $curTab !== 'clients' ? 'display: none;' : '' }}">
                <button type="button" onclick="openCreateClientModal()" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold shadow transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Koneksi Aplikasi
                </button>
            </div>
            <!-- Action Button for Tab 2 -->
            <div x-show="activeTab === 'logs'" x-cloak style="{{ $curTab !== 'logs' ? 'display: none;' : '' }}">
                <button type="button" 
                    onclick="showConfirmDialog({
                        title: 'Bersihkan Riwayat Log',
                        message: 'Apakah Anda yakin ingin menghapus seluruh riwayat log audit integrasi? Tindakan ini tidak dapat dibatalkan.',
                        confirmText: 'Ya, Bersihkan Log',
                        type: 'danger',
                        icon: 'trash-2',
                        formAction: '{{ route('admin.api-integrations.clear-logs') }}',
                        formMethod: 'POST'
                    })" 
                    class="bg-red-50 dark:bg-red-950/40 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800/60 px-3 py-1.5 rounded-xl text-[11px] font-bold transition flex items-center gap-1 cursor-pointer">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Bersihkan Log
                </button>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 1: APLIKASI TERKONEKSI & TOKEN AKSES -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'clients'" x-cloak style="{{ $curTab !== 'clients' ? 'display: none;' : '' }}" class="p-6">
            @if($clients->isEmpty())
            <div class="text-center py-12 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl bg-slate-50/50 dark:bg-slate-950/20">
                <div class="w-12 h-12 bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="network" class="w-6 h-6"></i>
                </div>
                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200">Belum Ada Aplikasi Terkoneksi</h4>
                <p class="text-xs text-slate-400 dark:text-slate-500 max-w-sm mx-auto mt-1">Daftarkan aplikasi eksternal (SANS PAUD, SD, atau SMP) untuk mengizinkan penarikan data calon santri baru atau penerimaan webhook otomatis.</p>
                <button type="button" onclick="openCreateClientModal()" class="mt-4 bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold shadow transition inline-flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Koneksi Sekarang
                </button>
            </div>
            @else
            <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-800">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40 text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider font-extrabold">
                            <th class="py-3 px-4">Nama Aplikasi & Keterangan</th>
                            <th class="py-3 px-4">Izin Scope Unit</th>
                            <th class="py-3 px-4">Izin Kelompok Data</th>
                            <th class="py-3 px-4">Webhook Push</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Aktivitas Terakhir</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs text-slate-700 dark:text-slate-300">
                        @foreach($clients as $client)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                            <!-- Name & Key -->
                            <td class="py-4 px-4">
                                <div class="font-extrabold text-slate-800 dark:text-slate-100 text-xs flex items-center gap-1.5">
                                    <i data-lucide="box" class="w-3.5 h-3.5 text-brand-emerald"></i>
                                    {{ $client->name }}
                                </div>
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">{{ $client->description ?: 'Tanpa keterangan' }}</div>
                                <div class="mt-1 flex items-center gap-1.5">
                                    <span class="inline-flex items-center gap-1 font-mono text-[9px] bg-slate-100 dark:bg-slate-800/90 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700/80 px-2 py-0.5 rounded-md select-none" title="Token disamarkan demi keamanan database">
                                        <i data-lucide="key" class="w-2.5 h-2.5 text-slate-400"></i>
                                        <span>{{ $client->token_preview }}</span>
                                    </span>
                                </div>
                            </td>

                            <!-- Allowed Units -->
                            <td class="py-4 px-4">
                                <div class="flex flex-wrap gap-1">
                                    @php $unitsList = $client->allowed_units ?: ['all']; @endphp
                                    @if(in_array('all', $unitsList))
                                        <span class="bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60 text-[9px] font-extrabold px-2 py-0.5 rounded">Semua Unit</span>
                                    @else
                                        @foreach($unitsList as $u)
                                            <span class="bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700/80 text-[9px] font-bold px-1.5 py-0.5 rounded uppercase">{{ $u }}</span>
                                        @endforeach
                                    @endif
                                </div>
                            </td>

                            <!-- Allowed Fields -->
                            <td class="py-4 px-4">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @php 
                                        $fields = $client->allowed_fields ?: ['bio', 'parents']; 
                                    @endphp
                                    @foreach($fields as $fKey)
                                        @php
                                            $fInfo = $availableFields[$fKey] ?? [
                                                'label' => match($fKey) {
                                                    'school_origin' => 'Sekolah Asal',
                                                    'program' => 'Kategori & Layanan',
                                                    'bio' => 'Biodata Calon Murid',
                                                    'parents' => 'Data Orang Tua',
                                                    'address' => 'Tempat Tinggal',
                                                    'guardian' => 'Data Wali',
                                                    'documents' => 'Data Lampiran',
                                                    'payments' => 'Pembayaran',
                                                    default => ucfirst(str_replace('_', ' ', $fKey))
                                                },
                                                'is_payment' => ($fKey === 'payments')
                                            ];
                                        @endphp
                                        <span class="{{ $fInfo['is_payment'] ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/60' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60' }} text-[9px] font-semibold px-1.5 py-0.5 rounded">
                                            {{ $fInfo['label'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            <!-- Webhook Status -->
                            <td class="py-4 px-4">
                                @if(!empty($client->webhook_url))
                                    <div class="text-[10px] font-mono text-slate-700 dark:text-slate-300 truncate max-w-[140px]" title="{{ $client->webhook_url }}">
                                        {{ $client->webhook_url }}
                                    </div>
                                    <div class="flex items-center gap-1 mt-1">
                                        <button type="button" onclick="testWebhookPing({{ $client->id }}, '{{ addslashes($client->name) }}')" class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 dark:hover:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 px-1.5 py-0.5 rounded border border-emerald-200 dark:border-emerald-800/60 flex items-center gap-1 transition cursor-pointer">
                                            <i data-lucide="zap" class="w-2.5 h-2.5"></i> Test Ping
                                        </button>
                                    </div>
                                @else
                                    <span class="text-[10px] text-slate-400 dark:text-slate-500 italic">Nonaktif</span>
                                @endif
                            </td>

                            <!-- Status Toggle -->
                            <td class="py-4 px-4 text-center">
                                <form action="{{ route('admin.api-integrations.toggle', $client->id) }}" method="POST" hx-boost="false">
                                    @csrf
                                    <input type="hidden" name="active_tab" value="clients">
                                    <button type="submit" class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-1 rounded-full transition cursor-pointer {{ $client->is_active ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/60' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $client->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                        {{ $client->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </button>
                                </form>
                            </td>

                            <!-- Last Activity -->
                            <td class="py-4 px-4 text-center text-[10px] text-slate-500 dark:text-slate-400 font-medium">
                                {{ $client->last_used_at ? $client->last_used_at->diffForHumans() : 'Belum pernah' }}
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" onclick='openEditClientModal(@json($client))' class="p-1.5 text-slate-400 dark:text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/50 rounded-lg transition cursor-pointer" title="Edit Pengaturan & Izin">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                    <button type="button" 
                                        onclick="showConfirmDialog({
                                            title: 'Regenerasi Kunci API Baru',
                                            message: 'Regenerasi kunci akan membatalkan Token API lama aplikasi &quot;{{ addslashes($client->name) }}&quot;. Anda harus memperbarui token baru di aplikasi eksternal terkait. Lanjutkan?',
                                            confirmText: 'Ya, Buat Kunci Baru',
                                            type: 'warning',
                                            icon: 'key',
                                            formAction: '{{ route('admin.api-integrations.regenerate', $client->id) }}',
                                            formMethod: 'POST'
                                        })" 
                                        class="p-1.5 text-slate-400 dark:text-slate-500 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/50 rounded-lg transition cursor-pointer" 
                                        title="Regenerasi Kunci API (Buat Token Baru)">
                                        <i data-lucide="key" class="w-4 h-4"></i>
                                    </button>
                                    <button type="button" 
                                        onclick="showConfirmDialog({
                                            title: 'Hapus Koneksi Aplikasi',
                                            message: 'Apakah Anda yakin ingin menghapus koneksi aplikasi &quot;{{ addslashes($client->name) }}&quot;? Akses API dan pengiriman webhook untuk aplikasi ini akan langsung dihentikan.',
                                            confirmText: 'Ya, Hapus Koneksi',
                                            type: 'danger',
                                            icon: 'trash-2',
                                            formAction: '{{ route('admin.api-integrations.destroy', $client->id) }}',
                                            formMethod: 'DELETE'
                                        })" 
                                        class="p-1.5 text-slate-400 dark:text-slate-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/50 rounded-lg transition cursor-pointer" 
                                        title="Hapus Akses">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 2: RIWAYAT & LOG AUDIT INTEGRASI -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'logs'" x-cloak style="{{ $curTab !== 'logs' ? 'display: none;' : '' }}" class="p-6 space-y-4">
            
            <!-- Filters -->
            <form method="GET" action="{{ route('admin.api-integrations') }}" hx-boost="false" class="flex flex-wrap items-center justify-between gap-3 bg-slate-50 dark:bg-slate-950/40 p-4 rounded-xl border border-slate-100 dark:border-slate-800">
                <input type="hidden" name="tab" value="logs">
                <div class="flex flex-wrap items-center gap-3">
                    <div>
                        <select name="log_type" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs rounded-xl px-3 py-2 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-emerald font-semibold">
                            <option value="">Semua Tipe Integrasi</option>
                            <option value="inbound_pull" {{ request('log_type') === 'inbound_pull' ? 'selected' : '' }}>Inbound API (Pull)</option>
                            <option value="outbound_webhook" {{ request('log_type') === 'outbound_webhook' ? 'selected' : '' }}>Outbound Webhook (Push)</option>
                            <option value="test_ping" {{ request('log_type') === 'test_ping' ? 'selected' : '' }}>Test Ping Webhook</option>
                        </select>
                    </div>
                    <div>
                        <select name="status_filter" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs rounded-xl px-3 py-2 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-emerald font-semibold">
                            <option value="">Semua Status Respon</option>
                            <option value="success" {{ request('status_filter') === 'success' ? 'selected' : '' }}>Hanya Sukses (2xx)</option>
                            <option value="error" {{ request('status_filter') === 'error' ? 'selected' : '' }}>Hanya Gagal (4xx / 5xx)</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 dark:hover:bg-slate-600 text-white text-xs font-bold px-4 py-2 rounded-xl transition cursor-pointer">
                        Terapkan Filter
                    </button>
                </div>
                <div class="text-[11px] text-slate-400 dark:text-slate-500 font-semibold">
                    Menampilkan {{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} log
                </div>
            </form>

            @if($logs->isEmpty())
            <div class="text-center py-10 text-slate-400 dark:text-slate-500 text-xs">
                Belum ada rekaman aktivitas integrasi API / Webhook.
            </div>
            @else
            <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-800">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40 text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider font-extrabold">
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Aplikasi / Caller</th>
                            <th class="py-3 px-4">Tipe</th>
                            <th class="py-3 px-4">Method & URL / Endpoint</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Durasi / IP</th>
                            <th class="py-3 px-4 text-center">Rincian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs text-slate-700 dark:text-slate-300">
                        @foreach($logs as $log)
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3 px-4 text-[10px] text-slate-500 dark:text-slate-400 font-mono">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-200 text-xs">
                                {{ $log->client_name ?: 'Unknown' }}
                            </td>
                            <td class="py-3 px-4">
                                @if($log->type === 'inbound_pull')
                                    <span class="bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60 text-[9px] font-bold px-2 py-0.5 rounded">INBOUND PULL</span>
                                @elseif($log->type === 'outbound_webhook')
                                    <span class="bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60 text-[9px] font-bold px-2 py-0.5 rounded">WEBHOOK PUSH</span>
                                @else
                                    <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60 text-[9px] font-bold px-2 py-0.5 rounded">TEST PING</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-mono text-[10px] text-slate-700 dark:text-slate-300 max-w-xs truncate" title="{{ $log->endpoint_or_url }}">
                                <span class="font-bold text-slate-900 dark:text-slate-100">{{ $log->method }}</span> {{ $log->endpoint_or_url }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($log->status_code >= 200 && $log->status_code < 300)
                                    <span class="bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/50 font-mono font-extrabold text-[10px] px-2 py-0.5 rounded">
                                        {{ $log->status_code }} OK
                                    </span>
                                @elseif($log->status_code >= 400 && $log->status_code < 500)
                                    <span class="bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-400 border border-amber-200/50 dark:border-amber-800/50 font-mono font-extrabold text-[10px] px-2 py-0.5 rounded">
                                        {{ $log->status_code }} WARN
                                    </span>
                                @else
                                    <span class="bg-red-100 dark:bg-red-950/60 text-red-800 dark:text-red-400 border border-red-200/50 dark:border-red-800/50 font-mono font-extrabold text-[10px] px-2 py-0.5 rounded">
                                        {{ $log->status_code ?: 'FAIL' }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center text-[10px] text-slate-500 dark:text-slate-400 font-mono">
                                <div>{{ $log->duration_ms !== null ? $log->duration_ms . ' ms' : '-' }}</div>
                                <div class="text-[9px] text-slate-400 dark:text-slate-500">{{ $log->ip_address }}</div>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <button type="button" onclick='showLogDetailModal(@json($log))' class="text-[10px] font-bold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 dark:hover:bg-blue-900/60 border border-blue-200/50 dark:border-blue-800/50 px-2 py-1 rounded transition cursor-pointer">
                                    Inspect
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pt-2">
                {{ $logs->links() }}
            </div>
            @endif
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 3: PANDUAN & DOKUMENTASI DEVELOPER -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'docs'" x-cloak style="{{ $curTab !== 'docs' ? 'display: none;' : '' }}" class="p-6 space-y-6">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Section 1: Inbound Pull -->
                <div class="bg-slate-900 text-slate-100 rounded-2xl p-6 shadow-md border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div class="flex items-center gap-2 font-extrabold text-xs text-brand-yellow uppercase tracking-wider">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            1. Inbound REST API (Tarik Data Siswa)
                        </div>
                        <span class="bg-emerald-500/20 text-emerald-400 text-[10px] font-extrabold px-2 py-0.5 rounded border border-emerald-500/30">GET</span>
                    </div>

                    <p class="text-xs text-slate-400 leading-relaxed">
                        Aplikasi unit (SANS SD, SMP, PAUD) dapat memanggil endpoint ini sewaktu-waktu menggunakan <strong>Bearer Token</strong> untuk menarik seluruh daftar siswa baru yang berstatus diterima.
                    </p>

                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Request Endpoint:</span>
                        <div class="bg-slate-950 p-3 rounded-xl font-mono text-[10px] text-emerald-400 mt-1 select-all break-all border border-slate-800">
                            GET {{ url('/api/v1/candidates?unit=sd&status=verified') }}
                        </div>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Request Headers:</span>
                        <div class="bg-slate-950 p-3 rounded-xl font-mono text-[10px] text-slate-300 mt-1 space-y-1 border border-slate-800">
                            <div><span class="text-blue-400">Authorization:</span> Bearer &lt;TOKEN_API_ANDA&gt;</div>
                            <div><span class="text-blue-400">Accept:</span> application/json</div>
                        </div>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Contoh Response JSON (Filtered):</span>
                        <div class="bg-slate-950 p-3 rounded-xl font-mono text-[9px] text-amber-300 mt-1 max-h-48 overflow-y-auto border border-slate-800 custom-modal-scroll">
<pre>{
  "status": "success",
  "meta": { "total": 1, "current_page": 1 },
  "data": [
    {
      "registration_no": "INV-SPMB-20260902-123",
      "unit": { "code": "sd", "name": "SD Anak Saleh" },
      "status": "verified",
      "student_bio": {
        "full_name": "Ahmad Raihan",
        "nisn": "0123456789",
        "gender": "Laki-laki",
        "address": { "city": "Kota Malang" }
      },
      "parent_info": {
        "father": { "name": "Budi Santoso", "phone": "08123456789" },
        "mother": { "name": "Siti Aminah" }
      }
    }
  ]
}</pre>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Outbound Webhook -->
                <div class="bg-slate-900 text-slate-100 rounded-2xl p-6 shadow-md border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div class="flex items-center gap-2 font-extrabold text-xs text-brand-yellow uppercase tracking-wider">
                            <i data-lucide="radio" class="w-4 h-4"></i>
                            2. Outbound Webhook (Push Real-Time)
                        </div>
                        <span class="bg-blue-500/20 text-blue-400 text-[10px] font-extrabold px-2 py-0.5 rounded border border-blue-500/30">POST</span>
                    </div>

                    <p class="text-xs text-slate-400 leading-relaxed">
                        SPMB akan menembakkan data JSON ke URL server Anda seketika saat calon siswa resmi diterima oleh panitia. Payload ditandatangani dengan <strong>HMAC-SHA256</strong>.
                    </p>

                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Webhook Headers yang Dikirim:</span>
                        <div class="bg-slate-950 p-3 rounded-xl font-mono text-[10px] text-slate-300 mt-1 space-y-1 border border-slate-800">
                            <div><span class="text-blue-400">X-Spmb-Event:</span> candidate.verified</div>
                            <div><span class="text-blue-400">X-Spmb-Signature:</span> &lt;HMAC_SHA256_HASH&gt;</div>
                            <div><span class="text-blue-400">X-Spmb-Delivery-Id:</span> 9f82d1c2-3e4b-482a...</div>
                            <div><span class="text-blue-400">X-Spmb-Timestamp:</span> 2026-09-12T17:00:00+07:00</div>
                        </div>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Contoh Verifikasi Signature di Sisi Unit (PHP):</span>
                        <div class="bg-slate-950 p-3 rounded-xl font-mono text-[9px] text-emerald-300 mt-1 max-h-48 overflow-y-auto border border-slate-800 custom-modal-scroll">
<pre>$signature = $_SERVER['HTTP_X_SPMB_SIGNATURE'] ?? '';
$rawBody = file_get_contents('php://input');
$calculated = hash_hmac('sha256', $rawBody, $webhookSecret);

if (hash_equals($signature, $calculated)) {
    // Webhook valid dan aman dari SPMB
    $data = json_decode($rawBody, true);
    // Simpan siswa ke database unit...
    http_response_code(200);
}</pre>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL: TAMBAH / EDIT KONEKSI APLIKASI -->
<!-- ========================================================================= -->
<div id="clientModal" onclick="if(event.target === this) closeClientModal()" class="fixed inset-0 z-50 bg-slate-900/70 dark:bg-slate-950/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-2xl w-full shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden flex flex-col max-h-[92vh] my-auto transform transition-all animate-in fade-in zoom-in duration-200">
        <!-- Modal Header (Fixed) -->
        <div class="px-6 py-3.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 flex justify-between items-center shrink-0">
            <h3 id="modalTitle" class="text-sm font-extrabold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4 text-brand-emerald"></i> Tambah Koneksi Aplikasi
            </h3>
            <button type="button" onclick="closeClientModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Modal Form with Dynamic Scrollable Body & Fixed Footer -->
        <form id="clientForm" action="{{ route('admin.api-integrations.store') }}" method="POST" hx-boost="false" class="flex flex-col flex-1 overflow-hidden min-h-0 m-0 p-0">
            @csrf
            <input type="hidden" name="active_tab" value="clients">
            <input type="hidden" name="client_id" id="inputClientId" value="">
            <div id="methodContainer"></div>

            <!-- Content Body (Only scrolls if needed on small screens) -->
            <div class="px-6 py-4 space-y-3.5 overflow-y-auto max-h-[calc(92vh-120px)] custom-modal-scroll overscroll-contain">
                <!-- Nama & Deskripsi -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Nama Aplikasi <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="inputName" required placeholder="misal: SANS SD - Sistem Akademik" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Keterangan / Deskripsi</label>
                        <input type="text" name="description" id="inputDescription" placeholder="misal: Sinkronisasi siswa baru & generate NIS" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                    </div>
                </div>

                <!-- 1. Izin Unit Jenjang -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">1. Filter Unit Jenjang yang Boleh Diakses <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 bg-slate-50 dark:bg-slate-950/40 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800">
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" name="allowed_units[]" value="all" id="unit_all" class="rounded border-slate-300 dark:border-slate-700 text-brand-emerald focus:ring-brand-emerald bg-white dark:bg-slate-800">
                            Semua Unit
                        </label>
                        @foreach($units as $u)
                        <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" name="allowed_units[]" value="{{ strtolower($u->code) }}" class="unit-chk rounded border-slate-300 dark:border-slate-700 text-brand-emerald focus:ring-brand-emerald bg-white dark:bg-slate-800">
                            {{ $u->name }} ({{ strtoupper($u->code) }})
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- 2. Izin Status Pendaftar -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">2. Filter Status Pendaftar yang Boleh Diambil <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 bg-slate-50 dark:bg-slate-950/40 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800">
                        <label class="flex items-center gap-2 text-xs font-bold text-emerald-700 dark:text-emerald-400 cursor-pointer">
                            <input type="checkbox" name="allowed_statuses[]" value="verified" checked class="status-chk rounded border-slate-300 dark:border-slate-700 text-brand-emerald focus:ring-brand-emerald bg-white dark:bg-slate-800">
                            Diterima (Verified / Lulus)
                        </label>
                        <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" name="allowed_statuses[]" value="submitted" class="status-chk rounded border-slate-300 dark:border-slate-700 text-brand-emerald focus:ring-brand-emerald bg-white dark:bg-slate-800">
                            Menunggu Verifikasi
                        </label>
                        <label class="flex items-center gap-2 text-xs font-medium text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="checkbox" name="allowed_statuses[]" value="all" class="status-chk rounded border-slate-300 dark:border-slate-700 text-brand-emerald focus:ring-brand-emerald bg-white dark:bg-slate-800">
                            Semua Status
                        </label>
                    </div>
                </div>

                <!-- 3. Izin Kelompok Data (Fields) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">3. Checklist Kelompok Data yang Diizinkan <span class="text-red-500">*</span></label>
                    <div class="space-y-1.5 bg-slate-50 dark:bg-slate-950/40 p-2.5 rounded-xl border border-slate-100 dark:border-slate-800">
                        @foreach($availableFields as $fKey => $fData)
                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer {{ $fData['is_payment'] ? 'border-t border-slate-200 dark:border-slate-800/80 pt-1.5 text-amber-900 dark:text-amber-400' : '' }}">
                                <input type="checkbox" name="allowed_fields[]" value="{{ $fKey }}" {{ !$fData['is_payment'] ? 'checked' : '' }} class="field-chk rounded border-slate-300 dark:border-slate-700 {{ $fData['is_payment'] ? 'text-amber-600 focus:ring-amber-500' : 'text-brand-emerald focus:ring-brand-emerald' }} bg-white dark:bg-slate-800">
                                <span class="flex items-center gap-1.5">
                                    @if($fData['icon'] === 'user') 👤
                                    @elseif($fData['icon'] === 'users') 👨‍👩‍👧
                                    @elseif($fData['icon'] === 'map-pin') 📍
                                    @elseif($fData['icon'] === 'shield' || $fData['icon'] === 'shield-check') 🛡️
                                    @elseif($fData['icon'] === 'file-text') 📄
                                    @elseif($fData['icon'] === 'credit-card') 💳
                                    @elseif($fData['icon'] === 'layers') 🗂️
                                    @else 📁
                                    @endif
                                    <strong>{{ $fData['label'] }}</strong>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- 4. Pengaturan Webhook (Opsional) -->
                <div class="border-t border-slate-100 dark:border-slate-800 pt-3">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">4. Pengaturan Webhook Listener (Push Otomatis - Opsional)</label>
                    <div class="space-y-2">
                        <div>
                            <input type="url" name="webhook_url" id="inputWebhookUrl" placeholder="https://sd.anakshaleh.sch.id/api/v1/webhook-receiver" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                        </div>
                        <div class="flex flex-wrap items-center gap-4">
                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                                <input type="checkbox" name="webhook_events[]" value="candidate.verified" checked class="event-chk rounded border-slate-300 dark:border-slate-700 text-brand-emerald focus:ring-brand-emerald bg-white dark:bg-slate-800">
                                Kirim saat Calon Siswa DITERIMA (candidate.verified)
                            </label>
                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer">
                                <input type="checkbox" name="webhook_events[]" value="payment.success" class="event-chk rounded border-slate-300 dark:border-slate-700 text-brand-emerald focus:ring-brand-emerald bg-white dark:bg-slate-800">
                                Kirim saat Formulir/DSP LUNAS (payment.success)
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer (Fixed) -->
            <div class="border-t border-slate-100 dark:border-slate-800 px-6 py-3.5 bg-slate-50/50 dark:bg-slate-950/40 flex justify-end gap-2 shrink-0">
                <button type="button" onclick="closeClientModal()" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-bold transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btnSubmitClient" class="px-5 py-2 bg-brand-emerald hover-emerald text-white rounded-xl text-xs font-bold shadow transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: TAMPILKAN TOKEN BARU (ONE-TIME DISPLAY) -->
<!-- ========================================================================= -->
@if(session('new_client_token'))
<div id="tokenDisplayModal" onclick="if(event.target === this) closeTokenDisplayModal()" class="fixed inset-0 z-50 bg-slate-900/70 dark:bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden flex flex-col max-h-[90vh] my-auto p-6 space-y-4 animate-in fade-in zoom-in duration-200 custom-modal-scroll overflow-y-auto">
        <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 rounded-2xl flex items-center justify-center mx-auto shrink-0">
            <i data-lucide="key" class="w-6 h-6"></i>
        </div>
        <div class="text-center shrink-0">
            <h3 class="text-base font-extrabold text-slate-800 dark:text-slate-100">Token API Berhasil Dibuat!</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Aplikasi: <strong>{{ session('new_client_token')['name'] }}</strong></p>
        </div>

        <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-xl p-3 text-[11px] text-amber-800 dark:text-amber-300 leading-relaxed shrink-0">
            <strong>⚠️ Perhatian Penting:</strong> Salin Token API ini sekarang. Demi alasan keamanan, token ini <strong>hanya ditampilkan satu kali</strong> dan tidak akan dapat dilihat kembali setelah jendela ini ditutup.
        </div>

        <!-- Token Input -->
        <div class="shrink-0">
            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Bearer API Token:</label>
            <div class="flex items-center gap-2">
                <input type="text" id="newApiToken" readonly value="{{ session('new_client_token')['token'] }}" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono text-emerald-700 dark:text-emerald-400 font-bold select-all focus:outline-none">
                <button type="button" onclick="copyToClipboard('newApiToken')" class="bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 dark:hover:bg-slate-600 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer shrink-0">
                    <i data-lucide="copy" class="w-3.5 h-3.5"></i> Salin
                </button>
            </div>
        </div>

        <!-- Secret Input -->
        @if(!empty(session('new_client_token')['webhook_secret']))
        <div class="shrink-0">
            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Webhook Secret Key (HMAC):</label>
            <div class="flex items-center gap-2">
                <input type="text" id="newWebhookSecret" readonly value="{{ session('new_client_token')['webhook_secret'] }}" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs font-mono text-slate-700 dark:text-slate-300 font-bold select-all focus:outline-none">
                <button type="button" onclick="copyToClipboard('newWebhookSecret')" class="bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 dark:hover:bg-slate-600 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer shrink-0">
                    <i data-lucide="copy" class="w-3.5 h-3.5"></i> Salin
                </button>
            </div>
        </div>
        @endif

        <div class="pt-2 flex justify-center shrink-0">
            <button type="button" onclick="closeTokenDisplayModal()" class="bg-brand-emerald hover-emerald text-white px-6 py-2.5 rounded-xl text-xs font-bold shadow transition cursor-pointer">
                Saya Sudah Menyimpan Token Ini
            </button>
        </div>
    </div>
</div>
@endif

<!-- ========================================================================= -->
<!-- MODAL: DETAIL LOG AUDIT (INSPECTOR PREMIUM) -->
<!-- ========================================================================= -->
<div id="logDetailModal" onclick="if(event.target === this) closeLogDetailModal()" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm hidden flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-3xl w-full shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col max-h-[92vh] my-auto animate-in fade-in zoom-in duration-200">
        
        <!-- Modal Header (Fixed) -->
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/50 flex justify-between items-center shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/50 flex items-center justify-center shrink-0">
                    <i data-lucide="terminal" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <span>Detail Log Integrasi</span>
                        <span id="logTypeBadge" class="text-[9px] font-extrabold px-2 py-0.5 rounded uppercase"></span>
                    </h3>
                    <p id="logTimestamp" class="text-[11px] text-slate-400 dark:text-slate-500 font-mono"></p>
                </div>
            </div>
            <button type="button" onclick="closeLogDetailModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-5 space-y-4 overflow-y-auto max-h-[calc(92vh-130px)] custom-modal-scroll overscroll-contain">
            
            <!-- Metadata Cards Grid -->
            <div class="bg-slate-50 dark:bg-slate-950/60 p-3.5 rounded-xl border border-slate-200/80 dark:border-slate-800 space-y-2.5">
                <!-- Row 1: Method & Endpoint URL -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-white dark:bg-slate-900 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        <span id="logMethodBadge" class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase font-mono tracking-wider shrink-0"></span>
                        <span id="logEndpoint" class="font-mono text-xs font-semibold text-slate-800 dark:text-slate-200 truncate select-all"></span>
                    </div>
                    <button type="button" onclick="copyLogEndpoint()" class="shrink-0 text-[11px] font-bold text-slate-600 dark:text-slate-300 hover:text-brand-emerald bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 px-2.5 py-1 rounded-md transition flex items-center gap-1 cursor-pointer">
                        <i data-lucide="copy" class="w-3 h-3"></i> <span id="btnCopyEndpointText">Salin URL</span>
                    </button>
                </div>

                <!-- Row 2: Status, Caller, Duration, IP -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                    <!-- Status -->
                    <div class="bg-white dark:bg-slate-900 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase block mb-0.5">HTTP Status</span>
                        <div id="logStatusBadge" class="inline-flex items-center gap-1 font-mono font-black text-xs"></div>
                    </div>
                    <!-- Client Name -->
                    <div class="bg-white dark:bg-slate-900 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase block mb-0.5">Aplikasi / Caller</span>
                        <span id="logClientName" class="font-bold text-slate-800 dark:text-slate-200 truncate block"></span>
                    </div>
                    <!-- Duration -->
                    <div class="bg-white dark:bg-slate-900 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase block mb-0.5">Durasi / Latency</span>
                        <span id="logDuration" class="font-mono font-bold text-slate-800 dark:text-slate-200"></span>
                    </div>
                    <!-- IP Address -->
                    <div class="bg-white dark:bg-slate-900 p-2.5 rounded-lg border border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase block mb-0.5">IP Address</span>
                        <span id="logIp" class="font-mono text-xs text-slate-700 dark:text-slate-300"></span>
                    </div>
                </div>
            </div>

            <!-- Error Banner (Shown only if error exists) -->
            <div id="logErrorBanner" class="hidden p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 rounded-xl flex items-start gap-3 text-rose-800 dark:text-rose-300">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5"></i>
                <div class="flex-1 min-w-0">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider block mb-1 text-rose-700 dark:text-rose-400">Pesan Error / Diagnosa:</span>
                    <p id="logErrorText" class="text-xs font-mono break-all whitespace-pre-wrap leading-relaxed select-all text-rose-900 dark:text-rose-200"></p>
                </div>
            </div>

            <!-- Payload Tabs Inspector -->
            <div class="space-y-2">
                <!-- Tab Buttons -->
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2">
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="switchLogInspectTab('req')" id="tabBtn-log-req" class="px-3 py-1.5 rounded-lg text-xs font-extrabold transition flex items-center gap-1.5 cursor-pointer bg-slate-800 text-white dark:bg-slate-100 dark:text-slate-900 shadow-sm">
                            <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i>
                            Request Payload
                        </button>
                        <button type="button" onclick="switchLogInspectTab('resp')" id="tabBtn-log-resp" class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i data-lucide="arrow-down-left" class="w-3.5 h-3.5"></i>
                            Response Body
                        </button>
                    </div>
                    <!-- Actions -->
                    <button type="button" onclick="copyCurrentLogPayload()" class="text-[11px] font-bold text-slate-600 dark:text-slate-300 hover:text-brand-emerald bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                        <span id="btnCopyPayloadText">Salin JSON</span>
                    </button>
                </div>

                <!-- Tab Pane: Request Payload -->
                <div id="tabPane-log-req" class="relative">
                    <pre id="logReqPayload" class="bg-slate-950 text-slate-100 p-4 rounded-xl font-mono text-xs leading-relaxed max-h-64 overflow-y-auto border border-slate-800 custom-modal-scroll whitespace-pre-wrap break-all select-all"></pre>
                </div>

                <!-- Tab Pane: Response Payload -->
                <div id="tabPane-log-resp" class="hidden relative">
                    <pre id="logRespPayload" class="bg-slate-950 text-slate-100 p-4 rounded-xl font-mono text-xs leading-relaxed max-h-64 overflow-y-auto border border-slate-800 custom-modal-scroll whitespace-pre-wrap break-all select-all"></pre>
                </div>
            </div>

        </div>

        <!-- Modal Footer (Fixed) -->
        <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/50 flex justify-between items-center shrink-0">
            <span id="logFooterId" class="text-[11px] font-mono text-slate-400 dark:text-slate-500"></span>
            <div class="flex items-center gap-2">
                <button type="button" onclick="copyEntireLogJson()" class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl text-xs transition cursor-pointer flex items-center gap-1.5">
                    <i data-lucide="file-json" class="w-3.5 h-3.5"></i> Salin Semua Log
                </button>
                <button type="button" onclick="closeLogDetailModal()" class="px-4 py-1.5 bg-slate-800 dark:bg-slate-200 text-white dark:text-slate-900 hover:bg-slate-900 dark:hover:bg-white font-bold rounded-xl text-xs transition cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Helper to synchronize body scroll locking
    function syncBodyScrollLock() {
        const clientModal = document.getElementById('clientModal');
        const tokenModal = document.getElementById('tokenDisplayModal');
        const logModal = document.getElementById('logDetailModal');
        const globalConfirm = document.getElementById('globalConfirmModal');

        const isClientOpen = clientModal && !clientModal.classList.contains('hidden');
        const isTokenOpen = !!tokenModal;
        const isLogOpen = logModal && !logModal.classList.contains('hidden');
        const isConfirmOpen = globalConfirm && !globalConfirm.classList.contains('pointer-events-none') && !globalConfirm.classList.contains('opacity-0');

        if (!isClientOpen && !isTokenOpen && !isLogOpen && !isConfirmOpen) {
            document.body.classList.remove('overflow-hidden');
        } else {
            document.body.classList.add('overflow-hidden');
        }
    }

    function openCreateClientModal() {
        document.getElementById('modalTitle').innerHTML = '<i data-lucide="plus-circle" class="w-4 h-4 text-brand-emerald"></i> Tambah Koneksi Aplikasi';
        document.getElementById('clientForm').action = "{{ route('admin.api-integrations.store') }}";
        document.getElementById('btnSubmitClient').innerHTML = '<i data-lucide="save" class="w-4 h-4"></i> Simpan Koneksi Baru';
        document.getElementById('methodContainer').innerHTML = '';
        document.getElementById('inputClientId').value = '';
        document.getElementById('inputName').value = '';
        document.getElementById('inputDescription').value = '';
        document.getElementById('inputWebhookUrl').value = '';
        
        // Reset checkboxes
        document.querySelectorAll('.unit-chk').forEach(c => c.checked = false);
        document.getElementById('unit_all').checked = true;
        document.querySelectorAll('.status-chk').forEach(c => c.checked = (c.value === 'verified'));
        document.querySelectorAll('.field-chk').forEach(c => c.checked = (c.value !== 'payments'));
        document.querySelectorAll('.event-chk').forEach(c => c.checked = (c.value === 'candidate.verified'));

        document.getElementById('clientModal').classList.remove('hidden');
        syncBodyScrollLock();
        if (window.lucide) lucide.createIcons();
    }

    function openEditClientModal(client) {
        document.getElementById('modalTitle').innerHTML = '<i data-lucide="edit-3" class="w-4 h-4 text-blue-600 dark:text-blue-400"></i> Edit Pengaturan: ' + client.name;
        document.getElementById('clientForm').action = "{{ url('/admin/api-integrations') }}/" + client.id + "/update";
        document.getElementById('btnSubmitClient').innerHTML = '<i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan';
        document.getElementById('methodContainer').innerHTML = '';
        document.getElementById('inputClientId').value = client.id;
        
        document.getElementById('inputName').value = client.name || '';
        document.getElementById('inputDescription').value = client.description || '';
        document.getElementById('inputWebhookUrl').value = client.webhook_url || '';

        // Allowed units
        const units = client.allowed_units || ['all'];
        document.getElementById('unit_all').checked = units.includes('all');
        document.querySelectorAll('.unit-chk').forEach(c => {
            c.checked = units.includes(c.value.toLowerCase());
        });

        // Allowed statuses
        const statuses = client.allowed_statuses || ['verified'];
        document.querySelectorAll('.status-chk').forEach(c => {
            c.checked = statuses.includes(c.value);
        });

        // Allowed fields
        const fields = client.allowed_fields || ['bio', 'parents'];
        document.querySelectorAll('.field-chk').forEach(c => {
            c.checked = fields.includes(c.value);
        });

        // Webhook events
        const events = client.webhook_events || ['candidate.verified'];
        document.querySelectorAll('.event-chk').forEach(c => {
            c.checked = events.includes(c.value);
        });

        document.getElementById('clientModal').classList.remove('hidden');
        syncBodyScrollLock();
        if (window.lucide) lucide.createIcons();
    }

    function closeClientModal() {
        document.getElementById('clientModal').classList.add('hidden');
        syncBodyScrollLock();
    }

    function closeTokenDisplayModal() {
        const modal = document.getElementById('tokenDisplayModal');
        if (modal) {
            modal.remove();
        }
        syncBodyScrollLock();
    }

    function closeLogDetailModal() {
        document.getElementById('logDetailModal').classList.add('hidden');
        syncBodyScrollLock();
    }

    // Bulletproof clipboard helper (Works on HTTPS, localhost, and HTTP custom domains)
    function safeCopyText(text, onSuccess, onError) {
        if (!text) return;
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text)
                .then(() => { if (typeof onSuccess === 'function') onSuccess(); })
                .catch(() => fallbackCopyText(text, onSuccess, onError));
        } else {
            fallbackCopyText(text, onSuccess, onError);
        }
    }

    function fallbackCopyText(text, onSuccess, onError) {
        try {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.top = '-9999px';
            textArea.style.left = '-9999px';
            textArea.setAttribute('readonly', '');
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            textArea.setSelectionRange(0, 99999);
            const successful = document.execCommand('copy');
            document.body.removeChild(textArea);
            if (successful) {
                if (typeof onSuccess === 'function') onSuccess();
            } else {
                if (typeof onError === 'function') onError();
            }
        } catch (err) {
            if (typeof onError === 'function') onError(err);
        }
    }

    function copyToClipboard(elementId) {
        const input = document.getElementById(elementId);
        if (!input) return;
        input.select();
        safeCopyText(input.value, () => {
            showToast('Berhasil disalin ke clipboard!', 'success');
        });
    }

    function testWebhookPing(clientId, clientName) {
        showToast(`Mengirim test ping webhook ke [${clientName}]...`, 'info');
        fetch(`/admin/api-integrations/${clientId}/test-webhook`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(`✅ ${data.message} (${data.duration_ms} ms)`, 'success');
            } else {
                showToast(`❌ ${data.message}`, 'error');
            }
        })
        .catch(err => {
            showToast(`Gagal mengirim ping: ${err.message}`, 'error');
        });
    }

    let currentActiveLog = null;
    let currentActiveLogTab = 'req';

    function switchLogInspectTab(tab) {
        currentActiveLogTab = tab;
        const reqBtn = document.getElementById('tabBtn-log-req');
        const respBtn = document.getElementById('tabBtn-log-resp');
        const reqPane = document.getElementById('tabPane-log-req');
        const respPane = document.getElementById('tabPane-log-resp');

        if (tab === 'req') {
            reqBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-extrabold transition flex items-center gap-1.5 cursor-pointer bg-slate-800 text-white dark:bg-slate-100 dark:text-slate-900 shadow-sm';
            respBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800';
            reqPane.classList.remove('hidden');
            respPane.classList.add('hidden');
        } else {
            respBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-extrabold transition flex items-center gap-1.5 cursor-pointer bg-slate-800 text-white dark:bg-slate-100 dark:text-slate-900 shadow-sm';
            reqBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800';
            respPane.classList.remove('hidden');
            reqPane.classList.add('hidden');
        }
        if (window.lucide) lucide.createIcons();
    }

    function showLogDetailModal(log) {
        currentActiveLog = log;
        switchLogInspectTab('req');

        // Type Badge
        const typeBadge = document.getElementById('logTypeBadge');
        if (log.type === 'inbound_pull') {
            typeBadge.innerText = 'Inbound API (Pull)';
            typeBadge.className = 'text-[9px] font-extrabold px-2 py-0.5 rounded uppercase bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60';
        } else if (log.type === 'outbound_webhook') {
            typeBadge.innerText = 'Outbound Webhook (Push)';
            typeBadge.className = 'text-[9px] font-extrabold px-2 py-0.5 rounded uppercase bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60';
        } else {
            typeBadge.innerText = 'Test Ping Webhook';
            typeBadge.className = 'text-[9px] font-extrabold px-2 py-0.5 rounded uppercase bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60';
        }

        // Timestamp
        document.getElementById('logTimestamp').innerText = log.created_at ? new Date(log.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'medium' }) : '-';

        // Method & Endpoint
        const methodBadge = document.getElementById('logMethodBadge');
        const method = (log.method || 'POST').toUpperCase();
        methodBadge.innerText = method;
        if (method === 'GET') {
            methodBadge.className = 'px-2.5 py-1 rounded-md text-[10px] font-black uppercase font-mono tracking-wider shrink-0 bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800';
        } else {
            methodBadge.className = 'px-2.5 py-1 rounded-md text-[10px] font-black uppercase font-mono tracking-wider shrink-0 bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800';
        }
        document.getElementById('logEndpoint').innerText = log.endpoint_or_url || '-';
        document.getElementById('logEndpoint').title = log.endpoint_or_url || '';

        // Status Badge
        const statusBadge = document.getElementById('logStatusBadge');
        const statusCode = log.status_code;
        if (statusCode >= 200 && statusCode < 300) {
            statusBadge.innerHTML = `<span class="px-2 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60">${statusCode} OK</span>`;
        } else if (statusCode >= 400 && statusCode < 500) {
            statusBadge.innerHTML = `<span class="px-2 py-0.5 rounded bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/60">${statusCode} WARN</span>`;
        } else {
            statusBadge.innerHTML = `<span class="px-2 py-0.5 rounded bg-rose-100 dark:bg-rose-950/60 text-rose-800 dark:text-rose-300 border border-rose-200/60 dark:border-rose-800/60">${statusCode || 'FAIL / TIMEOUT'}</span>`;
        }

        // Meta info
        document.getElementById('logClientName').innerText = log.client_name || 'Unknown Client';
        document.getElementById('logDuration').innerText = (log.duration_ms !== null ? log.duration_ms + ' ms' : '-');
        document.getElementById('logIp').innerText = log.ip_address || '-';
        document.getElementById('logFooterId').innerText = `ID Log: #LOG-${log.id || 'N/A'}`;

        // Error message banner
        const errorBanner = document.getElementById('logErrorBanner');
        const errorText = document.getElementById('logErrorText');
        if (log.error_message) {
            errorText.innerText = log.error_message;
            errorBanner.classList.remove('hidden');
        } else {
            errorBanner.classList.add('hidden');
            errorText.innerText = '';
        }

        // Code Pre blocks
        document.getElementById('logReqPayload').innerText = log.request_payload ? JSON.stringify(log.request_payload, null, 2) : '(Payload kosong)';
        document.getElementById('logRespPayload').innerText = log.response_payload ? JSON.stringify(log.response_payload, null, 2) : (log.error_message ? JSON.stringify({ error: log.error_message }, null, 2) : '(Response body kosong)');

        document.getElementById('logDetailModal').classList.remove('hidden');
        syncBodyScrollLock();
        if (window.lucide) lucide.createIcons();
    }

    function copyTokenPreview(tokenPreview, clientName) {
        safeCopyText(tokenPreview, () => {
            showToast(`Token preview '${tokenPreview}' untuk [${clientName}] disalin ke clipboard`, 'info');
        });
    }

    function copyLogEndpoint() {
        if (!currentActiveLog || !currentActiveLog.endpoint_or_url) return;
        safeCopyText(currentActiveLog.endpoint_or_url, () => {
            const btnText = document.getElementById('btnCopyEndpointText');
            if (btnText) {
                btnText.innerText = 'Tersalin!';
                setTimeout(() => { btnText.innerText = 'Salin URL'; }, 2000);
            }
            showToast('URL endpoint berhasil disalin', 'success');
        });
    }

    function copyCurrentLogPayload() {
        if (!currentActiveLog) return;
        const dataToCopy = (currentActiveLogTab === 'req')
            ? (currentActiveLog.request_payload ? JSON.stringify(currentActiveLog.request_payload, null, 2) : '')
            : (currentActiveLog.response_payload ? JSON.stringify(currentActiveLog.response_payload, null, 2) : currentActiveLog.error_message || '');
        
        safeCopyText(dataToCopy, () => {
            const btnText = document.getElementById('btnCopyPayloadText');
            if (btnText) {
                btnText.innerText = 'Tersalin!';
                setTimeout(() => { btnText.innerText = 'Salin JSON'; }, 2000);
            }
            showToast('JSON payload berhasil disalin ke clipboard', 'success');
        });
    }

    function copyEntireLogJson() {
        if (!currentActiveLog) return;
        safeCopyText(JSON.stringify(currentActiveLog, null, 2), () => {
            showToast('Seluruh objek Log berhasil disalin ke clipboard', 'success');
        });
    }

    // Global escape key handler to close active modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const logModal = document.getElementById('logDetailModal');
            if (logModal && !logModal.classList.contains('hidden')) {
                closeLogDetailModal();
                return;
            }
            const clientModal = document.getElementById('clientModal');
            if (clientModal && !clientModal.classList.contains('hidden')) {
                closeClientModal();
                return;
            }
            const tokenModal = document.getElementById('tokenDisplayModal');
            if (tokenModal) {
                closeTokenDisplayModal();
                return;
            }
        }
    });

    // Auto lock body if one-time token modal is rendered on page load
    document.addEventListener('DOMContentLoaded', function() {
        syncBodyScrollLock();
    });
</script>
@endsection
