@extends('layouts.admin')

@section('title', 'Data Pembayaran (Lunas) - Admin Panel')
@section('page_title', 'Data Pembayaran')

@section('content')
<div class="space-y-6">
    <!-- Header Summary Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800">Daftar Transaksi Lunas (Data Pembayaran)</h1>
            <p class="text-xs text-slate-500 mt-1">Menampilkan daftar seluruh transaksi pembayaran pendaftaran calon siswa yang berstatus berhasil (lunas).</p>
        </div>
        <div class="flex gap-2">
            <button class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                📥 Ekspor CSV
            </button>
        </div>
    </div>

    <!-- Financial Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Card 1: Count -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-[9px] text-slate-400 font-bold block uppercase tracking-wider">Transaksi Lunas</span>
                <span class="text-2xl font-black text-slate-800 block mt-1">{{ $stats['count'] }}</span>
            </div>
            <div class="h-10 w-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
        </div>
        <!-- Card 2: Revenue Bruto -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-[9px] text-slate-400 font-bold block uppercase tracking-wider">Total Revenue (Bruto)</span>
                <span class="text-base font-black text-emerald-600 block mt-1">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</span>
            </div>
            <div class="h-10 w-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="coins" class="w-5 h-5"></i>
            </div>
        </div>
        <!-- Card 3: Admin Fee PG -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-[9px] text-slate-400 font-bold block uppercase tracking-wider">Biaya Admin PG</span>
                <span class="text-base font-black text-amber-600 block mt-1">Rp {{ number_format($stats['admin_fee'], 0, ',', '.') }}</span>
            </div>
            <div class="h-10 w-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="percent" class="w-5 h-5"></i>
            </div>
        </div>
        <!-- Card 4: Net Revenue -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-[9px] text-slate-400 font-bold block uppercase tracking-wider">Pendapatan Bersih (Netto)</span>
                <span class="text-base font-black text-blue-600 block mt-1">Rp {{ number_format($stats['revenue'] - $stats['admin_fee'], 0, ',', '.') }}</span>
            </div>
            <div class="h-10 w-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Collapsible Payment Channels breakdown -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden transition-all duration-300">
        <button onclick="document.getElementById('financial-detail-panel').classList.toggle('hidden'); this.querySelector('.chevron-icon').classList.toggle('rotate-180');" 
                class="w-full flex items-center justify-between px-6 py-4 bg-slate-50/50 hover:bg-slate-50 transition text-xs font-extrabold text-slate-700 uppercase tracking-wider">
            <span class="flex items-center gap-2">
                <i data-lucide="bar-chart-3" class="w-4 h-4 text-brand-emerald"></i>
                Lihat Rekap Pendapatan per Metode, Jenis Biaya, & Unit
            </span>
            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200 chevron-icon"></i>
        </button>
        
        <div id="financial-detail-panel" class="hidden p-6 border-t border-slate-100 bg-white space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Column 1: Metode Pembayaran -->
                <div class="space-y-3">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1.5 pb-2 border-b border-slate-100">
                        <i data-lucide="wallet" class="w-3.5 h-3.5 text-brand-emerald"></i>
                        Per Metode Pembayaran
                    </h4>
                    <div class="space-y-2.5">
                        @forelse($channelStats as $cs)
                            @php
                                $percent = $stats['revenue'] > 0 ? round(($cs['sum'] / $stats['revenue']) * 100) : 0;
                            @endphp
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs font-bold text-slate-650">
                                    <span>{{ $cs['name'] }} ({{ $cs['count'] }} Tx)</span>
                                    <span>Rp {{ number_format($cs['sum'], 0, ',', '.') }} ({{ $percent }}%)</span>
                                </div>
                                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                    <div class="bg-brand-emerald h-full rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-[10px] text-slate-400 font-semibold py-2">Belum ada transaksi.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Column 2: Jenis Biaya -->
                <div class="space-y-3">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1.5 pb-2 border-b border-slate-100">
                        <i data-lucide="tag" class="w-3.5 h-3.5 text-brand-emerald"></i>
                        Per Jenis Biaya
                    </h4>
                    <div class="space-y-2.5">
                        @forelse($categoryStats as $cs)
                            @php
                                $percent = $stats['revenue'] > 0 ? round(($cs['sum'] / $stats['revenue']) * 100) : 0;
                            @endphp
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs font-bold text-slate-650">
                                    <span>{{ $cs['name'] }} ({{ $cs['count'] }} Tx)</span>
                                    <span>Rp {{ number_format($cs['sum'], 0, ',', '.') }} ({{ $percent }}%)</span>
                                </div>
                                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                    <div class="bg-brand-emerald h-full rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-[10px] text-slate-400 font-semibold py-2">Belum ada transaksi.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Column 3: Nama Biaya -->
                <div class="space-y-3">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1.5 pb-2 border-b border-slate-100">
                        <i data-lucide="file-text" class="w-3.5 h-3.5 text-brand-emerald"></i>
                        Per Nama Biaya
                    </h4>
                    <div class="space-y-2.5 max-h-[300px] overflow-y-auto pr-1">
                        @forelse($feeNameStats as $fns)
                            @php
                                $percent = $stats['revenue'] > 0 ? round(($fns['sum'] / $stats['revenue']) * 100) : 0;
                            @endphp
                            <div class="space-y-1">
                                <div class="flex justify-between text-[11px] font-bold text-slate-650">
                                    <span class="truncate max-w-[120px]" title="{{ $fns['name'] }}">{{ $fns['name'] }} ({{ $fns['count'] }} Tx)</span>
                                    <span>Rp {{ number_format($fns['sum'], 0, ',', '.') }} ({{ $percent }}%)</span>
                                </div>
                                <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-brand-emerald h-full rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-[10px] text-slate-400 font-semibold py-2">Belum ada transaksi.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Column 4: Unit / Jenjang -->
                <div class="space-y-3">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-1.5 pb-2 border-b border-slate-100">
                        <i data-lucide="layers" class="w-3.5 h-3.5 text-brand-emerald"></i>
                        Per Unit / Jenjang
                    </h4>
                    <div class="space-y-2.5">
                        @forelse($unitStats as $us)
                            @php
                                $percent = $stats['revenue'] > 0 ? round(($us['sum'] / $stats['revenue']) * 100) : 0;
                            @endphp
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs font-bold text-slate-650">
                                    <span>{{ $us['name'] }} ({{ $us['count'] }} Tx)</span>
                                    <span>Rp {{ number_format($us['sum'], 0, ',', '.') }} ({{ $percent }}%)</span>
                                </div>
                                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                    <div class="bg-brand-emerald h-full rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-[10px] text-slate-400 font-semibold py-2">Belum ada transaksi.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Net Revenue Info Banner -->
            <div class="p-4 bg-emerald-50/20 border border-emerald-100 rounded-2xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="space-y-1">
                    <span class="font-extrabold text-slate-800 text-xs flex items-center gap-1">
                        <i data-lucide="shield-check" class="w-4 h-4 text-brand-emerald"></i>
                        Pusat Sinkronisasi Winpay SNAP API
                    </span>
                    <p class="text-slate-500 text-[10px] leading-relaxed font-semibold">
                        Semua data keuangan di atas tersinkron secara real-time dengan status transaksi sukses dari sistem payment gateway Winpay.
                    </p>
                </div>
                <div class="bg-white px-4 py-2.5 rounded-xl border border-slate-100 flex items-center gap-3">
                    <div class="h-8 w-8 bg-emerald-50 text-brand-emerald rounded-full flex items-center justify-center flex-shrink-0">
                        <i data-lucide="trending-up" class="w-4.5 h-4.5"></i>
                    </div>
                    <div>
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest block">Volume Pendapatan Bersih</span>
                        <span class="text-sm font-black text-brand-emerald block">
                            Rp {{ number_format($stats['revenue'] - $stats['admin_fee'], 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments List Table -->
    <div id="payments-card" class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden" hx-boost="true" hx-target="#payments-card" hx-select="#payments-card">
        
        <!-- Search & Filter Form -->
        <form action="{{ route('admin.payments.data') }}" method="GET" hx-boost="false" class="p-6 bg-slate-50/50 border-b border-slate-100 space-y-4">
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
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari invoice, Winpay ID (215584), VA, atau nama siswa..." 
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
                        <th class="py-4 px-6">Keterangan Biaya</th>
                        <th class="py-4 px-6">Metode</th>
                        <th class="py-4 px-6">Nominal</th>
                        <th class="py-4 px-6">Ref Gateway (Winpay)</th>
                        <th class="py-4 px-6">Waktu Transaksi</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    @forelse($payments as $pay)
                        <tr class="hover:bg-slate-50/55 transition group">
                            <td class="py-4 px-6 text-center text-slate-400 font-bold text-xs">
                                {{ ($payments->currentPage() - 1) * $payments->perPage() + $loop->iteration }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-2">
                                    <div class="p-1.5 bg-slate-50 rounded-lg text-slate-400 group-hover:bg-white group-hover:text-brand-emerald border border-transparent group-hover:border-slate-100 transition duration-200">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <div>
                                        <div class="font-mono text-xs font-bold text-slate-700 select-all tracking-tight">{{ $pay->invoice_number }}</div>
                                        <div class="text-[9px] text-slate-400 font-medium tracking-wide mt-0.5">Merchant Ref di Winpay</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-semibold text-slate-800">{{ $pay->registration->candidate_name ?? 'Draft / Belum isi biodata' }}</div>
                                @if($pay->registration)
                                    <div class="text-[9px] text-slate-400 font-medium mt-0.5">
                                        {{ $pay->registration->admission_level ?: ($pay->registration->grade->name ?? '') }}
                                        @if(($pay->registration->admission_level || isset($pay->registration->grade)) && isset($pay->registration->unit))
                                            •
                                        @endif
                                        {{ $pay->registration->unit->name ?? '' }}
                                    </div>
                                @else
                                    <div class="text-[9px] text-slate-400 font-medium mt-0.5 italic">Draft Pendaftaran</div>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                @php
                                    $fee = \App\Models\SpmbFee::where('name', 'like', '%' . ($pay->registration->admission_level ?? 'TK A') . '%')->first()
                                        ?? \App\Models\SpmbFee::where('is_active', true)->first()
                                        ?? (object)['name' => 'Pendaftaran TK A'];
                                @endphp
                                <div class="font-semibold text-slate-700 text-xs">{{ $fee->name }}</div>
                                <div class="text-[9px] text-slate-400 font-medium mt-0.5">{{ $fee->category->name ?? 'Formulir Pendaftaran' }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-brand-emerald"></span>
                                    <span>{{ $pay->payment_method }}</span>
                                </div>
                                @if(is_array($pay->payment_info) && isset($pay->payment_info['virtualAccountNo']))
                                    <div class="mt-1 flex items-center gap-1">
                                        <span class="font-mono text-[10px] text-slate-500 font-bold select-all bg-slate-50 border border-slate-200/50 px-1.5 py-0.5 rounded">
                                            {{ $pay->payment_info['virtualAccountNo'] }}
                                        </span>
                                        <button onclick="navigator.clipboard.writeText('{{ $pay->payment_info['virtualAccountNo'] }}'); toastr.success('Nomor VA disalin')" class="text-slate-350 hover:text-slate-600 transition" title="Salin Nomor VA">
                                            <i data-lucide="copy" class="w-2.5 h-2.5"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-850 text-xs">
                                Rp {{ number_format($pay->amount, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono text-xs text-slate-500 font-semibold select-all" title="{{ $pay->reference_id ?? '-' }}">
                                        {{ $pay->reference_id ? (substr($pay->reference_id, 0, 8) . '...' . substr($pay->reference_id, -4)) : '-' }}
                                    </span>
                                    @if($pay->reference_id)
                                        <button onclick="navigator.clipboard.writeText('{{ $pay->reference_id }}'); toastr.success('Ref ID disalin ke clipboard')" class="text-slate-350 hover:text-slate-600 transition" title="Salin Full Ref ID">
                                            <i data-lucide="copy" class="w-3 h-3"></i>
                                        </button>
                                    @endif
                                </div>
                                <div class="text-[9px] text-slate-400 font-medium tracking-wide mt-0.5">Gateway Reference</div>
                                @php
                                    $gatewayId = null;
                                    if (is_array($pay->payment_info)) {
                                        $gatewayId = $pay->payment_info['callback_payload']['paymentRequestId']
                                            ?? ($pay->payment_info['callback_payload']['additionalInfo']['paymentSysId'] 
                                                ?? ($pay->payment_info['callback_payload']['id_transaksi'] ?? null));
                                    }
                                @endphp
                                @if($gatewayId)
                                    <div class="mt-1.5 flex">
                                        <span class="inline-flex items-center gap-1 text-[10px] text-emerald-700 font-mono font-extrabold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100 select-all">
                                            <i data-lucide="check-circle" class="w-2.5 h-2.5 text-emerald-500"></i>
                                            Winpay ID: {{ $gatewayId }}
                                        </span>
                                    </div>
                                @else
                                    <div class="mt-1.5 text-[9px] text-slate-400 italic flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 animate-pulse"></span>
                                        Menunggu callback...
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-1 text-slate-700 text-xs font-semibold">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span>{{ $pay->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}</span>
                                    <span class="text-[8px] bg-slate-100 text-slate-500 px-1 rounded font-bold">WIB</span>
                                </div>
                                <div class="flex items-center gap-1 font-mono text-[9px] text-slate-400 mt-1 pl-4.5">
                                    <span>{{ $pay->created_at->format('Y-m-d H:i:s') }}</span>
                                    <span class="text-[7px] border border-slate-200 px-0.5 rounded font-bold uppercase">UTC (Winpay)</span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider
                                    @if($pay->status === 'success') bg-green-50 text-green-700 border border-green-200
                                    @elseif($pay->status === 'pending') bg-yellow-50 text-yellow-700 border border-yellow-200
                                    @else bg-red-50 text-red-700 border border-red-200 @endif">
                                    <span class="w-1.5 h-1.5 rounded-full 
                                        @if($pay->status === 'success') bg-green-500
                                        @elseif($pay->status === 'pending') bg-yellow-500
                                        @else bg-red-500 @endif"></span>
                                    {{ $pay->status }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($pay->status === 'success')
                                    <a href="{{ route('dashboard.payment.receipt', $pay->id) }}" hx-boost="false" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-emerald bg-emerald-50 hover:bg-emerald-100 border border-emerald-100 hover:border-emerald-200 transition duration-200" title="Unduh Bukti Pembayaran Resmi">
                                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                        <span>Bukti Bayar</span>
                                    </a>
                                @else
                                    <span class="text-slate-400 text-xs font-medium">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-12 px-6 text-center text-slate-400">
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
