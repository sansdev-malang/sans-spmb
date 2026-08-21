@extends('layouts.admin')

@section('title', 'Payment Gateway Config - Admin Panel')
@section('page_title', 'Payment Gateways')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                <i data-lucide="credit-card" class="w-5 h-5 text-brand-emerald"></i>
                Pengaturan Payment Gateway
            </h1>
            <p class="text-xs text-slate-500 mt-1 uppercase tracking-wider font-semibold">Integrasi Standar SNAP BI API Standard</p>
        </div>
        <div class="flex gap-2">
            <div class="bg-brand-yellow font-bold text-slate-900 text-[10px] uppercase tracking-widest px-3 py-1.5 rounded-full shadow-sm">
                Mode: W:{{ strtoupper(substr($settings['winpay_mode'], 0, 4)) }} | B:{{ strtoupper(substr($settings['bni_mode'], 0, 4)) }}
            </div>
        </div>
    </div>

    <!-- Tab Pills -->
    <div class="flex flex-wrap gap-2 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
        <button onclick="switchSettingsTab('active_mode')" id="tabBtn-active_mode" class="settings-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow">
            <i data-lucide="toggle-left" class="w-4 h-4"></i> Mode Aktif
        </button>
        <button onclick="switchSettingsTab('winpay_prod')" id="tabBtn-winpay_prod" class="settings-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
            <i data-lucide="shield-check" class="w-4 h-4"></i> Winpay Prod
        </button>
        <button onclick="switchSettingsTab('winpay_sandbox')" id="tabBtn-winpay_sandbox" class="settings-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
            <i data-lucide="beaker" class="w-4 h-4"></i> Winpay Sandbox
        </button>
        <button onclick="switchSettingsTab('bni_prod')" id="tabBtn-bni_prod" class="settings-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
            <i data-lucide="shield-check" class="w-4 h-4"></i> BNI Prod
        </button>
        <button onclick="switchSettingsTab('bni_sandbox')" id="tabBtn-bni_sandbox" class="settings-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
            <i data-lucide="beaker" class="w-4 h-4"></i> BNI Sandbox
        </button>
        <button onclick="switchSettingsTab('simulator_mode')" id="tabBtn-simulator_mode" class="settings-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
            <i data-lucide="terminal" class="w-4 h-4"></i> Simulator
        </button>
        <button onclick="switchSettingsTab('channels_list')" id="tabBtn-channels_list" class="settings-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
            <i data-lucide="layers" class="w-4 h-4"></i> Channels Winpay
        </button>
    </div>

    <!-- Main Configuration Form -->
    <form action="{{ route('admin.settings.update') }}" method="POST" class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        @csrf
        
        <div class="p-8">
            
            <!-- Tab 1: Mode Aktif -->
            <div id="tabContent-active_mode" class="settings-tab-content space-y-6">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="font-extrabold text-sm text-slate-800">Pilih Mode Lingkungan Aktif</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Tentukan environment mana yang akan digunakan untuk memproses tagihan pendaftar online saat ini.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Winpay Environment Select -->
                    <div>
                        <label for="winpay_mode" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Mode Lingkungan (Winpay)</label>
                        <select id="winpay_mode" name="winpay_mode" 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-bold">
                            <option value="simulator" {{ $settings['winpay_mode'] === 'simulator' ? 'selected' : '' }}>Simulator Lokal (Virtual Mock)</option>
                            <option value="sandbox" {{ $settings['winpay_mode'] === 'sandbox' ? 'selected' : '' }}>Winpay Sandbox (Uji Coba)</option>
                            <option value="production" {{ $settings['winpay_mode'] === 'production' ? 'selected' : '' }}>Winpay Production (Live)</option>
                        </select>
                    </div>

                    <!-- BNI Environment Select -->
                    <div>
                        <label for="bni_mode" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Mode Lingkungan (BNI SNAP)</label>
                        <select id="bni_mode" name="bni_mode" 
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-bold">
                            <option value="simulator" {{ $settings['bni_mode'] === 'simulator' ? 'selected' : '' }}>Simulator Lokal (Virtual Mock)</option>
                            <option value="sandbox" {{ $settings['bni_mode'] === 'sandbox' ? 'selected' : '' }}>BNI Sandbox (Uji Coba)</option>
                            <option value="production" {{ $settings['bni_mode'] === 'production' ? 'selected' : '' }}>BNI Production (Live)</option>
                        </select>
                    </div>

                    <!-- Explanations Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 md:col-span-2">
                        <div class="p-4 rounded-xl border border-slate-150 bg-slate-50 space-y-2">
                            <span class="text-xs font-extrabold text-slate-700 flex items-center gap-1">
                                <i data-lucide="terminal" class="w-3.5 h-3.5 text-slate-600"></i>
                                Simulator Lokal
                            </span>
                            <p class="text-[10px] text-slate-400 leading-relaxed font-semibold">Menggunakan simulator virtual internal website. Ideal untuk demo/uji coba lokal tanpa memerlukan koneksi internet atau kredensial asli.</p>
                        </div>
                        <div class="p-4 rounded-xl border border-slate-150 bg-slate-50 space-y-2">
                            <span class="text-xs font-extrabold text-slate-700 flex items-center gap-1">
                                <i data-lucide="beaker" class="w-3.5 h-3.5 text-slate-600"></i>
                                Sandbox Mode
                            </span>
                            <p class="text-[10px] text-slate-400 leading-relaxed font-semibold">Tersambung ke server Sandbox API Eksternal. Uji transaksi menggunakan virtual account dummy dengan koneksi internet aktif sebelum go-live.</p>
                        </div>
                        <div class="p-4 rounded-xl border border-slate-150 bg-slate-50 space-y-2">
                            <span class="text-xs font-extrabold text-slate-700 flex items-center gap-1">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-slate-600"></i>
                                Production Mode
                            </span>
                            <p class="text-[10px] text-slate-400 leading-relaxed font-semibold">Mode operasional nyata. Transaksi akan didebit menggunakan uang asli yang live terhubung langsung ke kas yayasan sekolah.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Winpay Production -->
            <div id="tabContent-winpay_prod" class="settings-tab-content space-y-6 hidden">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="font-extrabold text-sm text-slate-800">Kredensial Winpay Production (Live)</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Kunci API produksi untuk memproses pembayaran riil dari wali murid.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Merchant ID (Production)</label>
                        <input type="text" name="winpay_prod_merchant_id" value="{{ old('winpay_prod_merchant_id', $settings['winpay_prod_merchant_id']) }}"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="Masukkan Merchant ID Live">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Client Key (Production)</label>
                        <input type="text" name="winpay_prod_client_key" value="{{ old('winpay_prod_client_key', $settings['winpay_prod_client_key']) }}"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="Masukkan Client Key Live">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Client Secret (Production)</label>
                        <div class="relative">
                            <input type="password" id="winpay_prod_client_secret" name="winpay_prod_client_secret" value="{{ old('winpay_prod_client_secret', $settings['winpay_prod_client_secret']) }}"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-4 pr-10 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="••••••••••••••••••••••••••••••••">
                            <button type="button" onclick="toggleSecretVisibility('winpay_prod_client_secret')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <i id="eye-icon-winpay_prod_client_secret" data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Merchant RSA Private Key (.pem)</label>
                        <textarea name="winpay_prod_private_key" rows="4"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="-----BEGIN PRIVATE KEY-----">{{ $settings['winpay_prod_private_key'] }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Winpay RSA Public Key (.pem)</label>
                        <textarea name="winpay_prod_public_key" rows="3"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="-----BEGIN PUBLIC KEY-----">{{ $settings['winpay_prod_public_key'] }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Winpay Sandbox -->
            <div id="tabContent-winpay_sandbox" class="settings-tab-content space-y-6 hidden">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="font-extrabold text-sm text-slate-800">Kredensial Winpay Sandbox (Staging)</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Kunci API staging/sandbox untuk pengetesan integrasi.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Merchant ID (Sandbox)</label>
                        <input type="text" name="winpay_sandbox_merchant_id" value="{{ old('winpay_sandbox_merchant_id', $settings['winpay_sandbox_merchant_id']) }}"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="Masukkan Merchant ID Staging">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Client Key (Sandbox)</label>
                        <input type="text" name="winpay_sandbox_client_key" value="{{ old('winpay_sandbox_client_key', $settings['winpay_sandbox_client_key']) }}"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="Masukkan Client Key Staging">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Client Secret (Sandbox)</label>
                        <div class="relative">
                            <input type="password" id="winpay_sandbox_client_secret" name="winpay_sandbox_client_secret" value="{{ old('winpay_sandbox_client_secret', $settings['winpay_sandbox_client_secret']) }}"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-4 pr-10 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="••••••••••••••••••••••••••••••••">
                            <button type="button" onclick="toggleSecretVisibility('winpay_sandbox_client_secret')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <i id="eye-icon-winpay_sandbox_client_secret" data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Merchant RSA Private Key (.pem)</label>
                        <textarea name="winpay_sandbox_private_key" rows="4"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="-----BEGIN PRIVATE KEY-----">{{ $settings['winpay_sandbox_private_key'] }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Winpay RSA Public Key (.pem)</label>
                        <textarea name="winpay_sandbox_public_key" rows="3"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="-----BEGIN PUBLIC KEY-----">{{ $settings['winpay_sandbox_public_key'] }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Tab BNI Prod -->
            <div id="tabContent-bni_prod" class="settings-tab-content space-y-6 hidden">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="font-extrabold text-sm text-slate-800">Kredensial BNI Production (Live)</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Kunci API produksi untuk memproses pembayaran BNI SNAP QRIS.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Merchant ID (NMID)</label>
                        <input type="text" name="bni_prod_merchant_id" value="{{ old('bni_prod_merchant_id', $settings['bni_prod_merchant_id']) }}"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="Merchant ID Production">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Terminal ID (TID)</label>
                        <input type="text" name="bni_prod_terminal_id" value="{{ old('bni_prod_terminal_id', $settings['bni_prod_terminal_id']) }}"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="Terminal ID Production (Opsional)">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Client ID (Production)</label>
                        <input type="text" name="bni_prod_client_id" value="{{ old('bni_prod_client_id', $settings['bni_prod_client_id']) }}"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Client Secret (Production)</label>
                        <div class="relative">
                            <input type="password" id="bni_prod_client_secret" name="bni_prod_client_secret" value="{{ old('bni_prod_client_secret', $settings['bni_prod_client_secret']) }}"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-4 pr-10 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono">
                            <button type="button" onclick="toggleSecretVisibility('bni_prod_client_secret')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <i id="eye-icon-bni_prod_client_secret" data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Private Key (.pem)</label>
                        <textarea name="bni_prod_private_key" rows="4"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="-----BEGIN PRIVATE KEY-----">{{ $settings['bni_prod_private_key'] }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Tab BNI Sandbox -->
            <div id="tabContent-bni_sandbox" class="settings-tab-content space-y-6 hidden">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="font-extrabold text-sm text-slate-800">Kredensial BNI Sandbox (Uji Coba)</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Digunakan untuk testing SNAP API dari BNI tanpa transaksi nyata.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Merchant ID (NMID)</label>
                        <input type="text" name="bni_sandbox_merchant_id" value="{{ old('bni_sandbox_merchant_id', $settings['bni_sandbox_merchant_id']) }}"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="Merchant ID Sandbox">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Terminal ID (TID)</label>
                        <input type="text" name="bni_sandbox_terminal_id" value="{{ old('bni_sandbox_terminal_id', $settings['bni_sandbox_terminal_id']) }}"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="Terminal ID Sandbox (Opsional)">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Client ID (Sandbox)</label>
                        <input type="text" name="bni_sandbox_client_id" value="{{ old('bni_sandbox_client_id', $settings['bni_sandbox_client_id']) }}"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Client Secret (Sandbox)</label>
                        <div class="relative">
                            <input type="password" id="bni_sandbox_client_secret" name="bni_sandbox_client_secret" value="{{ old('bni_sandbox_client_secret', $settings['bni_sandbox_client_secret']) }}"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-4 pr-10 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono">
                            <button type="button" onclick="toggleSecretVisibility('bni_sandbox_client_secret')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <i id="eye-icon-bni_sandbox_client_secret" data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Private Key (.pem)</label>
                        <textarea name="bni_sandbox_private_key" rows="4"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="-----BEGIN PRIVATE KEY-----">{{ $settings['bni_sandbox_private_key'] }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Simulator Lokal -->
            <div id="tabContent-simulator_mode" class="settings-tab-content space-y-6 hidden">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="font-extrabold text-sm text-slate-800">Kredensial Simulator Lokal (Virtual Mock)</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Konfigurasi data mock local development untuk simulasi API.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Merchant ID (Simulator)</label>
                        <input type="text" name="winpay_merchant_id" value="{{ old('winpay_merchant_id', $settings['winpay_merchant_id']) }}"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Client Key (Simulator)</label>
                        <input type="text" name="winpay_client_key" value="{{ old('winpay_client_key', $settings['winpay_client_key']) }}"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Client Secret (Simulator)</label>
                        <div class="relative">
                            <input type="password" id="winpay_client_secret" name="winpay_client_secret" value="{{ old('winpay_client_secret', $settings['winpay_client_secret']) }}"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-4 pr-10 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono">
                            <button type="button" onclick="toggleSecretVisibility('winpay_client_secret')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <i id="eye-icon-winpay_client_secret" data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Merchant RSA Private Key (.pem)</label>
                        <textarea name="winpay_private_key" rows="4"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono">{{ $settings['winpay_private_key'] }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Winpay RSA Public Key (.pem)</label>
                        <textarea name="winpay_public_key" rows="3"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono">{{ $settings['winpay_public_key'] }}</textarea>
                    </div>
                    <div class="md:col-span-2 border-b border-slate-100 pb-2 mb-2 mt-4">
                        <h4 class="font-extrabold text-xs text-slate-800">Simulator BNI SNAP</h4>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Merchant ID (BNI Sim)</label>
                        <input type="text" name="bni_simulator_merchant_id" value="{{ old('bni_simulator_merchant_id', $settings['bni_simulator_merchant_id']) }}"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Terminal ID (BNI Sim)</label>
                        <input type="text" name="bni_simulator_terminal_id" value="{{ old('bni_simulator_terminal_id', $settings['bni_simulator_terminal_id']) }}"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Client ID (BNI Sim)</label>
                        <input type="text" name="bni_simulator_client_id" value="{{ old('bni_simulator_client_id', $settings['bni_simulator_client_id']) }}"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Client Secret (BNI Sim)</label>
                        <div class="relative">
                            <input type="password" id="bni_simulator_client_secret" name="bni_simulator_client_secret" value="{{ old('bni_simulator_client_secret', $settings['bni_simulator_client_secret']) }}"
                                class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-4 pr-10 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono">
                            <button type="button" onclick="toggleSecretVisibility('bni_simulator_client_secret')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <i id="eye-icon-bni_simulator_client_secret" data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Private Key (BNI Sim)</label>
                        <textarea name="bni_simulator_private_key" rows="2"
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="-----BEGIN PRIVATE KEY-----">{{ $settings['bni_simulator_private_key'] }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Tab 5: Channels List -->
            <div id="tabContent-channels_list" class="settings-tab-content space-y-6 hidden">
                <div class="border-b border-slate-100 pb-3 flex justify-between items-center">
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-800 flex items-center gap-1.5">
                            <i data-lucide="layers" class="w-4 h-4 text-brand-emerald"></i>
                            Metode Pembayaran Tersedia (Winpay Channels)
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">Daftar channel Virtual Account dan QRIS yang aktif terhubung secara otomatis pada portal calon siswa.</p>
                    </div>
                    <!-- Sync Button -->
                    <button type="submit" form="sync-channels-form" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Sinkronisasi
                    </button>
                </div>
                
                <div class="overflow-hidden border border-slate-150 rounded-xl bg-slate-50/50">
                    <table class="min-w-full divide-y divide-slate-200 text-xs text-left">
                        <thead class="bg-slate-50 font-extrabold text-slate-700">
                            <tr>
                                <th class="px-5 py-3.5">Nama Channel</th>
                                <th class="px-5 py-3.5">Kode SNAP API</th>
                                <th class="px-5 py-3.5">Tipe Pembayaran</th>
                                <th class="px-5 py-3.5">Status Channel</th>
                                <th class="px-5 py-3.5">Integrasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600 font-semibold bg-white font-medium">
                            @foreach($channels as $channel)
                                <tr>
                                    <td class="px-5 py-3.5 text-slate-800 font-bold">{{ $channel->name }}</td>
                                    <td class="px-5 py-3.5 font-mono text-[10px] text-brand-emerald">{{ $channel->code }}</td>
                                    <td class="px-5 py-3.5">{{ $channel->type }}</td>
                                    <td class="px-5 py-3.5">
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" {{ $channel->is_active ? 'checked' : '' }} onchange="togglePaymentChannel({{ $channel->id }})">
                                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-emerald"></div>
                                            <span class="ms-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider peer-checked:text-brand-emerald">
                                                {{ $channel->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </label>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-400 font-bold">Winpay BI / SNAP</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Submit Panel -->
        <div class="bg-slate-50 border-t border-slate-100 px-8 py-4 flex justify-between items-center">
            <span class="text-[11px] text-slate-400 font-semibold flex items-center gap-1">
                <i data-lucide="info" class="w-3.5 h-3.5"></i>
                Setiap tab menyimpan data kredensial masing-masing.
            </span>
            <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5">
                <i data-lucide="save" class="w-4 h-4"></i>
                Simpan Pengaturan API
            </button>
        </div>
    </form>

    <!-- Hidden Forms for Sync and Toggle Channel -->
    <form id="sync-channels-form" action="{{ route('admin.settings.channels.sync') }}" method="POST" class="hidden">
        @csrf
    </form>
    <form id="toggle-channel-form" action="" method="POST" class="hidden">
        @csrf
    </form>
</div>

<script>
    // JS trigger to submit global toggle form
    function togglePaymentChannel(channelId) {
        const form = document.getElementById('toggle-channel-form');
        form.action = `/admin/settings/channels/${channelId}/toggle`;
        form.submit();
    }

    // Tab switching memory script
    function switchSettingsTab(tabId) {
        document.querySelectorAll('.settings-tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('tabContent-' + tabId).classList.remove('hidden');

        document.querySelectorAll('.settings-tab-btn').forEach(btn => {
            btn.className = "settings-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50";
        });
        
        const activeBtn = document.getElementById('tabBtn-' + tabId);
        activeBtn.className = "settings-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow";
        
        localStorage.setItem('winpay_settings_active_tab', tabId);
    }

    // Toggle Client Secret text/password visibility
    function toggleSecretVisibility(inputId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById('eye-icon-' + inputId);
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

    document.addEventListener("DOMContentLoaded", function() {
        const savedTab = localStorage.getItem('winpay_settings_active_tab') || 'active_mode';
        switchSettingsTab(savedTab);
    });
</script>
@endsection
