@extends('layouts.admin')

@section('title', 'Log Sistem - Admin Panel')
@section('page_title', 'Log Sistem')

@section('content')
<div id="logs-container" hx-boost="true" hx-target="#logs-container" hx-select="#logs-container" class="w-full space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                <i data-lucide="scroll-text" class="w-5 h-5 text-brand-emerald"></i>
                Log Sistem & Pembayaran
            </h1>
            <p class="text-xs text-slate-500 mt-1">Pantau callback gateway, error exceptions, dan rekonsiliasi data transaksi langsung dari server.</p>
        </div>
        
        <form action="{{ route('admin.logs.clear') }}" method="POST" hx-boost="false" onsubmit="return confirm('Apakah Anda yakin ingin menghapus seluruh log sistem? File laravel.log akan dikosongkan.');">
            @csrf
            <button type="submit" class="bg-red-650 bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-red-200 shadow-sm">
                <i data-lucide="trash-2" class="w-4 h-4"></i> Bersihkan Log
            </button>
        </form>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <form action="{{ route('admin.logs') }}" method="GET" hx-boost="false" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Pencarian Kata Kunci</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari invoice, error message, dll..." class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-9 pr-4 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                </div>
            </div>
            
            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Filter Log Level</label>
                <select name="level" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                    <option value="">Semua Level</option>
                    <option value="info" {{ $level === 'info' ? 'selected' : '' }}>INFO (General logs / webhook)</option>
                    <option value="warning" {{ $level === 'warning' ? 'selected' : '' }}>WARNING (Mismatches / anomalies)</option>
                    <option value="error" {{ $level === 'error' ? 'selected' : '' }}>ERROR (Exceptions / failure)</option>
                    <option value="debug" {{ $level === 'debug' ? 'selected' : '' }}>DEBUG (Developer logs)</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Tampilkan Baris</label>
                <select name="limit" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                    <option value="10" {{ $limit === '10' ? 'selected' : '' }}>10 baris</option>
                    <option value="25" {{ $limit === '25' ? 'selected' : '' }}>25 baris</option>
                    <option value="50" {{ $limit === '50' ? 'selected' : '' }}>50 baris</option>
                    <option value="100" {{ $limit === '100' ? 'selected' : '' }}>100 baris</option>
                    <option value="all" {{ $limit === 'all' ? 'selected' : '' }}>Semua baris</option>
                </select>
            </div>
            
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-brand-emerald hover-emerald text-white py-2 rounded-xl text-xs font-bold shadow transition flex items-center justify-center gap-1">
                    <i data-lucide="filter" class="w-4 h-4"></i> Terapkan Filter
                </button>
                @if($search || $level || $limit !== '50')
                    <a href="{{ route('admin.logs') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 p-2 rounded-xl text-xs font-bold transition flex items-center justify-center border border-slate-300">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Logs List Table -->
    <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-[10px] text-slate-500 uppercase tracking-wider font-extrabold select-none">
                        <th class="py-3.5 px-6 w-44">Waktu</th>
                        <th class="py-3.5 px-6 w-28 text-center">Level</th>
                        <th class="py-3.5 px-6">Pesan Log</th>
                        <th class="py-3.5 px-6 w-24 text-right">Rincian</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-100">
                    @forelse($logs as $index => $log)
                        <tr class="hover:bg-slate-50/50 transition cursor-pointer" onclick="toggleTrace('trace-{{ $index }}')">
                            <td class="py-4 px-6 text-slate-500 font-mono whitespace-nowrap">
                                {{ $log['timestamp'] }}
                            </td>
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                @php
                                    $levelColor = 'bg-blue-100 text-blue-750';
                                    if ($log['level'] === 'ERROR') {
                                        $levelColor = 'bg-red-100 text-red-750';
                                    } elseif ($log['level'] === 'WARNING') {
                                        $levelColor = 'bg-orange-100 text-orange-750';
                                    } elseif ($log['level'] === 'DEBUG') {
                                        $levelColor = 'bg-slate-100 text-slate-650';
                                    }
                                @endphp
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold tracking-wide {{ $levelColor }}">
                                    {{ $log['level'] }}
                                </span>
                            </td>
                            <td class="py-4 px-6 font-medium text-slate-800 break-all">
                                {{ $log['message'] }}
                            </td>
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                @if(!empty($log['trace']))
                                    <button class="text-xs text-brand-emerald font-bold hover:underline flex items-center gap-0.5 ml-auto" type="button">
                                        Trace <i data-lucide="chevron-down" class="w-3.5 h-3.5" id="icon-trace-{{ $index }}"></i>
                                    </button>
                                @else
                                    <span class="text-[10px] text-slate-400 font-medium italic">Empty</span>
                                @endif
                            </td>
                        </tr>
                        @if(!empty($log['trace']))
                            <tr id="trace-{{ $index }}" class="hidden bg-slate-900 text-slate-300 font-mono text-[10px]">
                                <td colspan="4" class="p-6 break-all">
                                    <div class="max-h-72 overflow-y-auto whitespace-pre-wrap leading-relaxed">
                                        {{ $log['trace'] }}
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 px-6 text-center text-slate-400 text-xs font-semibold">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i data-lucide="file-warning" class="w-8 h-8 text-slate-300"></i>
                                    <span>Tidak ada entri log sistem yang ditemukan.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        @if($totalPages > 1)
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between bg-slate-50/50 text-xs select-none">
                <span class="text-slate-500 font-medium">Halaman {{ $page }} dari {{ $totalPages }} (Total: {{ $total }} baris)</span>
                
                <div class="flex items-center gap-1.5">
                    @if($page > 1)
                        <a href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}" class="border border-slate-200 hover:bg-white text-slate-700 px-3 py-1.5 rounded-xl font-bold transition">Sebelumnya</a>
                    @else
                        <span class="border border-slate-100 text-slate-350 px-3 py-1.5 rounded-xl font-bold cursor-not-allowed">Sebelumnya</span>
                    @endif

                    @if($page < $totalPages)
                        <a href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}" class="border border-slate-200 hover:bg-white text-slate-700 px-3 py-1.5 rounded-xl font-bold transition">Berikutnya</a>
                    @else
                        <span class="border border-slate-100 text-slate-350 px-3 py-1.5 rounded-xl font-bold cursor-not-allowed">Berikutnya</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

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
    function toggleTrace(id) {
        const el = document.getElementById(id);
        if (!el) return;
        
        el.classList.toggle('hidden');
        
        // Toggle arrow icon rotation
        const idx = id.replace('trace-', '');
        const icon = document.getElementById('icon-trace-' + idx);
        if (icon) {
            if (el.classList.contains('hidden')) {
                icon.style.transform = 'rotate(0deg)';
            } else {
                icon.style.transform = 'rotate(180deg)';
            }
        }
    }
</script>
@endsection
