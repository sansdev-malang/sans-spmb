@extends('layouts.admin')

@section('title', 'Biaya Admin Transaksi - Admin Panel')
@section('page_title', 'Biaya Admin Transaksi')

@section('content')
<div id="technical-settings-container" hx-boost="true" hx-target="#technical-settings-container" hx-select="#technical-settings-container" class="w-full space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="percent" class="w-5 h-5 text-brand-emerald"></i>
                Biaya Admin Transaksi Gateway
            </h1>
            <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-semibold">
                Konfigurasi skema & nominal biaya transaksi per kategori channel pembayaran
            </p>
        </div>
        <div>
            <a href="{{ route('admin.payment-channels.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 transition">
                <i data-lucide="credit-card" class="w-4 h-4 text-brand-emerald"></i>
                Buka CRUD Channel Pembayaran
            </a>
        </div>
    </div>

    <!-- Tab Navigation Pills for Payment Gateways -->
    <div class="flex flex-wrap gap-2 bg-white dark:bg-slate-900 p-2 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm">
        @foreach($gatewayFees as $gwCode => $gwData)
            <button type="button" onclick="switchGatewayTab('{{ $gwCode }}')" id="gwTabBtn-{{ $gwCode }}" class="gw-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === $gwCode ? 'bg-brand-emerald text-white shadow' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <i data-lucide="credit-card" class="w-4 h-4"></i> {{ $gwData['gateway_name'] }}
            </button>
        @endforeach
    </div>

    <!-- Main Configuration Form -->
    <form action="{{ route('admin.settings.update') }}" method="POST" hx-boost="false" class="bg-white dark:bg-slate-900 rounded-2xl shadow-md border border-slate-100 dark:border-slate-800 overflow-hidden">
        @csrf
        <input type="hidden" name="active_tab" id="active-tab-input" value="{{ $activeTab }}">
        
        <!-- Hidden fields for other settings to preserve values and pass validation -->
        @foreach($settings as $key => $val)
            @if(!in_array($key, ['fee_winpay_va', 'fee_winpay_retail', 'fee_winpay_qris', 'fee_winpay_ewallet', 'fee_bni_va', 'fee_bni_qris']))
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endif
        @endforeach
        
        <div class="p-6 sm:p-8 space-y-6">
            <div class="bg-emerald-50/50 dark:bg-emerald-950/20 rounded-2xl p-4 border border-emerald-100 dark:border-emerald-900/50 flex gap-3 text-xs text-emerald-800 dark:text-emerald-300 leading-relaxed">
                <i data-lucide="shield-check" class="w-5 h-5 text-brand-emerald flex-shrink-0 mt-0.5"></i>
                <div>
                    <span class="font-extrabold block mb-0.5">Sinkronisasi Biaya Gateway & Channel:</span>
                    <span>Mengubah nilai biaya di bawah akan secara otomatis memperbarui default dan mengupdate seluruh channel pembayaran aktif terkait di gateway yang dipilih. Tarif resmi Winpay: VA Rp 4.500 (Flat), Retail Rp 4.500 (Flat), QRIS 0.70% (MDR), E-Wallet 2.00% (MDR).</span>
                </div>
            </div>

            @foreach($gatewayFees as $gwCode => $gwData)
                <div id="gwTabContent-{{ $gwCode }}" class="gw-tab-content space-y-6 {{ $activeTab === $gwCode ? '' : 'hidden' }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        @foreach($gwData['fields'] as $field)
                            @php
                                $isFlat = in_array($field['type'], ['va', 'retail']);
                                $icon = match($field['type']) {
                                    'va' => 'credit-card',
                                    'retail' => 'store',
                                    'qris' => 'qr-code',
                                    'ewallet' => 'wallet',
                                    default => 'tag'
                                };
                                $colorClass = match($field['type']) {
                                    'va' => 'text-blue-600 bg-blue-50 border-blue-100 dark:bg-blue-950/40 dark:text-blue-400 dark:border-blue-900/40',
                                    'retail' => 'text-amber-600 bg-amber-50 border-amber-100 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-900/40',
                                    'qris' => 'text-emerald-600 bg-emerald-50 border-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/40',
                                    'ewallet' => 'text-purple-600 bg-purple-50 border-purple-100 dark:bg-purple-950/40 dark:text-purple-400 dark:border-purple-900/40',
                                    default => 'text-slate-600 bg-slate-50 border-slate-100'
                                };
                            @endphp
                            <div class="p-5 rounded-2xl bg-slate-50/60 dark:bg-slate-950/50 border border-slate-200/60 dark:border-slate-800 flex flex-col justify-between space-y-4">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <div class="flex items-center gap-2">
                                            <span class="p-1.5 rounded-lg border {{ $colorClass }}">
                                                <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
                                            </span>
                                            <span class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-wider">
                                                {{ $field['label'] }}
                                            </span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider {{ $isFlat ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300' }}">
                                            {{ $isFlat ? 'Flat (Rp)' : 'Persen (MDR %)' }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 leading-relaxed mb-3">
                                        {{ $field['desc'] }}
                                    </p>
                                </div>

                                <div class="relative">
                                    @if($isFlat)
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold text-xs">Rp</span>
                                        <input type="number" id="{{ $field['key'] }}" name="{{ $field['key'] }}" required min="0" step="1"
                                            class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-10 pr-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-extrabold font-mono transition"
                                            value="{{ $field['value'] }}">
                                    @else
                                        <input type="number" id="{{ $field['key'] }}" name="{{ $field['key'] }}" required min="0" step="0.01"
                                            class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl pl-4 pr-10 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-extrabold font-mono transition"
                                            value="{{ $field['value'] }}">
                                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 font-bold text-xs">%</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Submit Panel -->
        <div class="bg-slate-50 dark:bg-slate-950 border-t border-slate-100 dark:border-slate-800 px-6 sm:px-8 py-5 flex items-center justify-between">
            <span class="text-[11px] text-slate-400 hidden sm:inline-block">
                * Perubahan biaya akan langsung aktif pada kalkulasi pendaftaran & pembayaran siswa.
            </span>
            <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-3.5 rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5 ml-auto">
                <i data-lucide="save" class="w-4 h-4 text-brand-yellow"></i>
                Simpan & Terapkan Perubahan Biaya
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
</div>

<script>
    function switchGatewayTab(tabId) {
        const panelEl = document.getElementById('gwTabContent-' + tabId);
        const btnEl = document.getElementById('gwTabBtn-' + tabId);
        if (!panelEl || !btnEl) return;

        document.querySelectorAll('.gw-tab-content').forEach(el => el.classList.add('hidden'));
        panelEl.classList.remove('hidden');

        document.querySelectorAll('.gw-tab-btn').forEach(btn => {
            btn.className = "gw-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800";
        });
        
        btnEl.className = "gw-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow";
        
        // Set hidden active_tab input value
        const activeTabInput = document.getElementById('active-tab-input');
        if (activeTabInput) {
            activeTabInput.value = tabId;
        }
        
        // Update URL query parameter
        const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?tab=' + tabId;
        window.history.replaceState({ path: newUrl }, '', newUrl);

        localStorage.setItem('spmb_gateway_fees_active_tab', tabId);
    }
</script>
@endsection
