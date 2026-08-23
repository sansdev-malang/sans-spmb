@extends('layouts.admin')

@section('title', 'Biaya Admin Transaksi - Admin Panel')
@section('page_title', 'Biaya Admin Transaksi')

@section('content')
<div class="w-full space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h1 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
            <i data-lucide="percent" class="w-5 h-5 text-brand-emerald"></i>
            Biaya Admin Transaksi
        </h1>
        <p class="text-xs text-slate-400 mt-1 uppercase tracking-wider font-semibold">Pengaturan biaya transaksi yang dibebankan kepada wali murid</p>
    </div>

    <!-- Tab Navigation Pills for Payment Gateways -->
    <div class="flex flex-wrap gap-2 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
        @foreach($gatewayFees as $gwCode => $gwData)
            <button type="button" onclick="switchGatewayTab('{{ $gwCode }}')" id="gwTabBtn-{{ $gwCode }}" class="gw-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $loop->first ? 'bg-brand-emerald text-white shadow' : 'text-slate-600 hover:bg-slate-50' }}">
                <i data-lucide="credit-card" class="w-4 h-4"></i> {{ $gwData['gateway_name'] }}
            </button>
        @endforeach
    </div>

    <!-- Main Configuration Form -->
    <form action="{{ route('admin.settings.update') }}" method="POST" class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        @csrf
        
        <!-- Hidden fields for other settings to preserve values and pass validation -->
        @foreach($settings as $key => $val)
            @if(!array_key_exists($key, $gatewayFees))
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endif
        @endforeach
        
        <div class="p-8 space-y-6">
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-150 flex gap-3 text-xs text-slate-500 leading-relaxed mb-4">
                <i data-lucide="info" class="w-5 h-5 text-brand-emerald flex-shrink-0 mt-0.5"></i>
                <span>Tentukan nominal biaya admin tetap atau persentase MDR yang akan secara otomatis ditambahkan ke total biaya pendaftaran saat proses pembayaran. Silakan pilih tab di atas untuk mengonfigurasi masing-masing gateway.</span>
            </div>

            @foreach($gatewayFees as $gwCode => $gwData)
                <div id="gwTabContent-{{ $gwCode }}" class="gw-tab-content space-y-6 {{ $loop->first ? '' : 'hidden' }}">
                    @foreach($gwData['fields'] as $field)
                        <div>
                            <label for="{{ $field['key'] }}" class="block text-xs font-extrabold text-slate-700 uppercase tracking-wider mb-2">
                                {{ $field['label'] }}
                            </label>
                            <div class="relative">
                                @if($field['type'] === 'va')
                                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400 font-bold text-xs">Rp</span>
                                    <input type="number" id="{{ $field['key'] }}" name="{{ $field['key'] }}" required min="0" step="1"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-3.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-bold transition"
                                        value="{{ $field['value'] }}">
                                @else
                                    <input type="number" id="{{ $field['key'] }}" name="{{ $field['key'] }}" required min="0" step="0.01"
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-4 pr-10 py-3.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-bold transition"
                                        value="{{ $field['value'] }}">
                                    <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-450 font-bold text-xs">%</span>
                                @endif
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1.5 font-medium">{{ $field['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <!-- Submit Panel -->
        <div class="bg-slate-50 border-t border-slate-100 px-8 py-5 flex justify-end">
            <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-3.5 rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5">
                <i data-lucide="save" class="w-4 h-4"></i>
                Simpan Perubahan Biaya
            </button>
        </div>
    </form>
</div>

<script>
    function switchGatewayTab(tabId) {
        document.querySelectorAll('.gw-tab-content').forEach(el => el.classList.add('hidden'));
        const activeContent = document.getElementById('gwTabContent-' + tabId);
        if (activeContent) activeContent.classList.remove('hidden');

        document.querySelectorAll('.gw-tab-btn').forEach(btn => {
            btn.className = "gw-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50";
        });
        
        const activeBtn = document.getElementById('gwTabBtn-' + tabId);
        if (activeBtn) {
            activeBtn.className = "gw-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow";
        }
        
        localStorage.setItem('spmb_gateway_fees_active_tab', tabId);
    }

    document.addEventListener("DOMContentLoaded", function() {
        const savedTab = localStorage.getItem('spmb_gateway_fees_active_tab');
        if (savedTab && document.getElementById('gwTabBtn-' + savedTab)) {
            switchGatewayTab(savedTab);
        }
    });
</script>
@endsection
