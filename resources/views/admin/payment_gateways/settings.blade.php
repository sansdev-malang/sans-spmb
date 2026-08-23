@extends('layouts.admin')

@section('title', 'Setting ' . $gateway->name . ' - Portal SPMB')
@section('page_title', 'Konfigurasi Gateway')

@section('content')
<div class="space-y-8">
    
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl font-extrabold leading-7 text-slate-900 sm:text-3xl sm:truncate dark:text-white text-left">
                Konfigurasi {{ $gateway->name }}
            </h2>
            <p class="text-xs text-brand-emerald font-semibold uppercase tracking-wider mt-1 text-left">
                Kode Gateway: {{ $gateway->code }} • Kelola Kunci & Kredensial API
            </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4 gap-3">
            <a href="{{ route('admin.payment-gateways.index') }}" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-xl shadow-sm text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-750 transition">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-1.5 text-slate-500"></i> Kembali ke List
            </a>
        </div>
    </div>



    <!-- Form Configuration -->
    <form action="{{ route('admin.payment-gateways.settings.save', $gateway->code) }}" method="POST" class="space-y-6 w-full text-left">
        @csrf
        
        <!-- Environment Mode Selector -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 space-y-4">
            <h3 class="font-extrabold text-slate-800 dark:text-white text-xs uppercase tracking-wider border-b border-slate-100 dark:border-slate-800 pb-3">Mode Lingkungan (Environment Mode)</h3>
            <div class="w-72">
                <label for="mode" class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Mode Aktif Saat Ini</label>
                <select id="mode" name="mode" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
                    <option value="simulator" {{ $gatewayMode === 'simulator' ? 'selected' : '' }}>Simulator (Pengujian Lokal)</option>
                    <option value="sandbox" {{ $gatewayMode === 'sandbox' ? 'selected' : '' }}>Sandbox (API Testing)</option>
                    <option value="production" {{ $gatewayMode === 'production' ? 'selected' : '' }}>Production (Pembayaran Riil Live)</option>
                </select>
            </div>
            <p class="text-[10px] text-slate-400 leading-normal">
                *Mengubah mode ini akan mengalihkan pemanggilan API transaksi SPMB pendaftar ke kredensial yang disetel di tab lingkungan masing-masing di bawah ini.
            </p>
        </div>

        <!-- Environments Configuration Tabs -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 overflow-hidden">
            <!-- Tabs Headers -->
            <div class="bg-slate-50 dark:bg-slate-950 border-b border-slate-100 dark:border-slate-800 flex text-xs select-none">
                <button type="button" id="tab-btn-simulator" onclick="switchTab('simulator')" class="tab-btn px-6 py-4 transition focus:outline-none uppercase tracking-wider border-brand-emerald text-brand-emerald dark:text-emerald-400 font-extrabold bg-white dark:bg-slate-900 border-b-2">
                    1. Simulator
                </button>
                <button type="button" id="tab-btn-sandbox" onclick="switchTab('sandbox')" class="tab-btn px-6 py-4 transition focus:outline-none uppercase tracking-wider text-slate-450 hover:text-slate-700">
                    2. Sandbox
                </button>
                <button type="button" id="tab-btn-production" onclick="switchTab('production')" class="tab-btn px-6 py-4 transition focus:outline-none uppercase tracking-wider text-slate-450 hover:text-slate-700">
                    3. Production
                </button>
            </div>

            <!-- Tabs Body -->
            <div class="p-6">
                
                @foreach(['simulator', 'sandbox', 'production'] as $env)
                    <div id="tab-panel-{{ $env }}" class="tab-panel space-y-5 animate-fade-in {{ $env !== 'simulator' ? 'hidden' : '' }}">
                        <div class="pb-2 border-b border-slate-100 dark:border-slate-800">
                            <h4 class="font-extrabold text-slate-800 dark:text-white text-xs uppercase tracking-wide">Pengaturan Kunci API {{ ucfirst($env) }}</h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">Parameter ini khusus digunakan ketika sistem berjalan di mode {{ $env }}.</p>
                        </div>

                        <div class="space-y-4">
                            @foreach($gateway->settings_schema as $field)
                                @php
                                    $key = $field['key'];
                                    $val = $settings[$env][$key] ?? '';
                                    $inputType = $field['type'] ?? 'text';
                                @endphp
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-2 items-start text-left">
                                    <label class="md:pt-3 text-xs font-bold text-slate-650 dark:text-slate-350 uppercase tracking-wide md:col-span-1">
                                        {{ $field['label'] }}
                                    </label>
                                    <div class="md:col-span-3">
                                        @if($inputType === 'textarea')
                                            <textarea name="settings[{{ $env }}][{{ $key }}]" rows="5" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="Masukkan nilai {{ strtolower($field['label']) }}...">{{ $val }}</textarea>
                                        @elseif($inputType === 'password')
                                            <input type="password" name="settings[{{ $env }}][{{ $key }}]" value="{{ $val }}" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="Masukkan nilai {{ strtolower($field['label']) }}...">
                                        @else
                                            <input type="text" name="settings[{{ $env }}][{{ $key }}]" value="{{ $val }}" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2.5 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-mono" placeholder="Masukkan nilai {{ strtolower($field['label']) }}...">
                                        @endif
                                        <span class="text-[9px] text-slate-400 mt-1 block uppercase tracking-wider">
                                            Setting Key: {{ $gateway->code }}_{{ $env === 'simulator' && $gateway->code === 'winpay' ? '' : ($env === 'simulator' ? 'simulator_' : $env . '_') }}{{ $key }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-3">
            <button type="submit" class="bg-brand-emerald hover-emerald text-white px-8 py-3 rounded-xl font-bold text-xs shadow-md transition flex items-center gap-1.5">
                <i data-lucide="save" class="w-4 h-4 text-brand-yellow"></i> Simpan Konfigurasi Kunci
            </button>
        </div>

    </form>
</div>

<script>
    function switchTab(env) {
        // Hide all panels
        document.querySelectorAll('.tab-panel').forEach(el => el.classList.add('hidden'));
        // Show active panel
        document.getElementById('tab-panel-' + env).classList.remove('remove', 'hidden');
        
        // Reset all header styles
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.className = "tab-btn px-6 py-4 transition focus:outline-none uppercase tracking-wider text-slate-450 hover:text-slate-700";
        });
        
        // Set active header style
        document.getElementById('tab-btn-' + env).className = "tab-btn px-6 py-4 transition focus:outline-none uppercase tracking-wider border-brand-emerald text-brand-emerald dark:text-emerald-400 font-extrabold bg-white dark:bg-slate-900 border-b-2";
    }
</script>
@endsection
