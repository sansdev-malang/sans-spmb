@extends('layouts.admin')

@section('title', 'Riwayat Pembayaran (Log) - Admin Panel')
@section('page_title', 'Riwayat Pembayaran (Log)')

@section('content')
<div class="space-y-6">
    <!-- Header Summary Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800">Riwayat Transaksi Pembayaran (Log)</h1>
            <p class="text-xs text-slate-500 mt-1">Log riwayat transaksi pembayaran pendaftaran calon siswa terintegrasi Winpay SNAP API secara real-time.</p>
        </div>
        <div class="flex gap-2">
            <button class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                📥 Ekspor CSV
            </button>
        </div>
    </div>

    <!-- Payments List Table -->
    <div id="mock-payments-card" class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden" hx-boost="true" hx-target="#mock-payments-card" hx-select="#mock-payments-card">
        
        <!-- Search & Filter Form -->
        <form action="{{ route('admin.payments') }}" method="GET" hx-boost="false" class="p-6 bg-slate-50/50 border-b border-slate-100 space-y-4">
            @if(request('unit_id'))
                <input type="hidden" name="unit_id" value="{{ request('unit_id') }}">
            @endif
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    <!-- Search Input Container -->
                    <div class="relative w-full md:w-80 flex items-center">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari invoice, ref, atau nama..." 
                               class="w-full pl-9 pr-20 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-emerald transition">
                        
                        <!-- Clear (X) Button -->
                        @if(request('search'))
                            <button type="button" onclick="this.form.querySelector('input[name=search]').value = ''; this.form.submit();" 
                                    class="absolute right-12 inset-y-0 pr-1 flex items-center text-slate-400 hover:text-slate-600 transition"
                                    title="Hapus Pencarian">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        @endif

                        <!-- Integrated Search Button -->
                        <button type="submit" class="absolute right-1.5 top-1.5 bottom-1.5 px-3 bg-brand-emerald hover-emerald text-white rounded-lg text-[10px] font-bold shadow-sm transition">
                            Cari
                        </button>
                    </div>
                    
                    <!-- Filter Status -->
                    <select name="status" onchange="this.form.submit()" class="py-2.5 px-3 text-xs rounded-xl border border-slate-200 bg-white font-bold text-slate-650 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                        <option value="">Semua Status</option>
                        <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed / Expired</option>
                    </select>

                    <!-- Per Page Select -->
                    <select name="per_page" onchange="this.form.submit()" class="py-2.5 px-3 text-xs rounded-xl border border-slate-200 bg-white font-bold text-slate-650 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 Baris</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 Baris</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 Baris</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 Baris</option>
                    </select>

                    <!-- Advanced Filter Toggle Button -->
                    <button type="button" onclick="document.getElementById('adv-filters').classList.toggle('hidden')" 
                            class="flex items-center gap-1.5 py-2.5 px-3.5 text-xs rounded-xl border border-slate-200 bg-white hover:bg-slate-50 font-bold text-slate-600 transition">
                        <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>
                        Filter Lanjutan
                    </button>
                </div>
            </div>

            <!-- Slide-down Advanced Filters Panel -->
            <div id="adv-filters" class="{{ (request('start_date') || request('end_date') || request('method') || request('category_id') || request('fee_id')) ? '' : 'hidden' }} border-t border-slate-100 pt-4 space-y-4 transition-all duration-300">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <!-- Date Range: Start -->
                    <div class="space-y-1">
                        <label class="text-[9px] font-extrabold uppercase text-slate-400 block">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" 
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-emerald focus:border-transparent">
                    </div>
                    <!-- Date Range: End -->
                    <div class="space-y-1">
                        <label class="text-[9px] font-extrabold uppercase text-slate-400 block">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" 
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-emerald focus:border-transparent">
                    </div>
                    <!-- Filter: Metode Pembayaran -->
                    <div class="space-y-1">
                        <label class="text-[9px] font-extrabold uppercase text-slate-400 block">Metode</label>
                        <select name="method" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                            <option value="">Semua Metode</option>
                            @foreach(\App\Models\SpmbPaymentChannel::where('is_active', true)->get() as $channel)
                                <option value="{{ $channel->code }}" {{ request('method') === $channel->code ? 'selected' : '' }}>{{ $channel->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Filter: Jenis Biaya -->
                    <div class="space-y-1">
                        <label class="text-[9px] font-extrabold uppercase text-slate-400 block">Jenis Biaya</label>
                        <select name="category_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                            <option value="">Semua Jenis</option>
                            @foreach(\App\Models\SpmbFeeCategory::all() as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Filter: Nama Biaya -->
                    <div class="space-y-1">
                        <label class="text-[9px] font-extrabold uppercase text-slate-400 block">Nama Biaya</label>
                        <select name="fee_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                            <option value="">Semua Nama Biaya</option>
                            @foreach(\App\Models\SpmbFee::all() as $fee)
                                <option value="{{ $fee->id }}" {{ request('fee_id') == $fee->id ? 'selected' : '' }}>{{ $fee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <!-- Action Buttons in Advanced Filter -->
                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="resetAdvancedFilters(this.form)" class="text-xs font-bold text-slate-500 hover:text-slate-700 px-4 py-2 rounded-xl transition">
                        Reset Filter
                    </button>
                    <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>

        <script>
            function resetAdvancedFilters(form) {
                form.querySelector('input[name=start_date]').value = '';
                form.querySelector('input[name=end_date]').value = '';
                form.querySelector('select[name=method]').value = '';
                form.querySelector('select[name=category_id]').value = '';
                form.querySelector('select[name=fee_id]').value = '';
                form.submit();
            }
        </script>

        @if(auth()->user()->isSuperAdmin())
            <!-- Unit Tabs -->
            <div class="px-6 pt-4 bg-slate-50/50 border-b border-slate-100 flex flex-wrap gap-2 text-[10px] font-bold">
                <!-- Semua Unit Tab -->
                <a href="{{ route(Route::currentRouteName(), request()->except(['page', 'unit_id'])) }}" 
                   class="px-4 py-2.5 rounded-t-xl transition-all duration-200 border-b-2 {{ !request()->filled('unit_id') ? 'border-brand-emerald text-brand-emerald bg-white shadow-sm' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                    Semua Unit
                </a>
                
                <!-- Dynamic Unit Tabs -->
                @foreach(\App\Models\SpmbUnit::where('is_active', true)->get() as $unit)
                    <a href="{{ route(Route::currentRouteName(), array_merge(request()->except(['page']), ['unit_id' => $unit->id])) }}" 
                       class="px-4 py-2.5 rounded-t-xl transition-all duration-200 border-b-2 {{ request('unit_id') == $unit->id ? 'border-brand-emerald text-brand-emerald bg-white shadow-sm' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                        {{ strtoupper($unit->name) }}
                    </a>
                @endforeach
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                        <th class="py-4 px-6 text-center w-12">No.</th>
                        <th class="py-4 px-6">No. Invoice</th>
                        <th class="py-4 px-6">Calon Siswa</th>
                        <th class="py-4 px-6">Jenis Biaya</th>
                        <th class="py-4 px-6">Nama Biaya</th>
                        <th class="py-4 px-6">Metode</th>
                        <th class="py-4 px-6">Nominal</th>
                        <th class="py-4 px-6">Ref (Merchant)</th>
                        <th class="py-4 px-6">Waktu Transaksi</th>
                        <th class="py-4 px-6 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    @forelse($payments as $pay)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-6 text-center text-slate-500 font-bold text-xs">
                                {{ ($payments->currentPage() - 1) * $payments->perPage() + $loop->iteration }}
                            </td>
                            <td class="py-4 px-6 font-mono text-xs font-bold text-slate-700">
                                {{ $pay->invoice_number }}
                            </td>
                            <td class="py-4 px-6 font-semibold text-slate-800">
                                {{ $pay->registration->candidate_name ?? 'Draft / Belum isi biodata' }}
                            </td>
                            <td class="py-4 px-6 text-slate-600 font-medium">
                                @php
                                    $fee = \App\Models\SpmbFee::where('name', 'like', '%' . ($pay->registration->admission_level ?? 'TK A') . '%')->first()
                                        ?? \App\Models\SpmbFee::where('is_active', true)->first()
                                        ?? (object)['name' => 'Pendaftaran TK A'];
                                @endphp
                                {{ $fee->category->name ?? 'Formulir Pendaftaran' }}
                            </td>
                            <td class="py-4 px-6 text-slate-600 font-medium">
                                {{ $fee->name }}
                            </td>
                            <td class="py-4 px-6 text-slate-600 font-bold">
                                <div>{{ $pay->payment_method }}</div>
                                @if(is_array($pay->payment_info) && isset($pay->payment_info['virtualAccountNo']))
                                    <div class="text-[10px] text-slate-400 font-mono font-medium mt-0.5 select-all">VA: {{ $pay->payment_info['virtualAccountNo'] }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-800">
                                Rp {{ number_format($pay->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-mono text-xs text-slate-700 font-semibold select-all">{{ $pay->reference_id ?? '-' }}</div>
                                <div class="text-[9px] text-slate-400 mt-0.5 uppercase font-bold tracking-wider">Merchant Ref</div>
                                @php
                                    $gatewayId = null;
                                    if (is_array($pay->payment_info)) {
                                        $gatewayId = $pay->payment_info['callback_payload']['additionalInfo']['paymentSysId'] 
                                            ?? ($pay->payment_info['callback_payload']['id_transaksi'] ?? null);
                                    }
                                @endphp
                                @if($gatewayId)
                                    <div class="text-[10px] text-emerald-600 font-mono font-extrabold mt-1.5 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100 inline-block">Winpay ID: {{ $gatewayId }}</div>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-slate-500 text-xs">
                                {{ $pay->created_at->format('d M Y, H:i') }} WIB
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider
                                    @if($pay->status === 'success') bg-green-50 text-green-700 border border-green-200
                                    @elseif($pay->status === 'pending') bg-yellow-50 text-yellow-700 border border-yellow-200
                                    @else bg-red-50 text-red-700 border border-red-200 @endif">
                                    {{ $pay->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 px-6 text-center text-slate-400">
                                Belum ada riwayat transaksi pembayaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($payments->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
