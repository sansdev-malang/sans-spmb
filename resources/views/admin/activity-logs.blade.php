@extends('layouts.admin')

@section('title', 'Log Aktivitas (Audit Trail) - Admin Panel')
@section('page_title', 'Log Aktivitas')

@section('content')
<div class="space-y-6">
    <!-- Header Summary Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800">Log Aktivitas (Audit Trail)</h1>
            <p class="text-xs text-slate-500 mt-1">Lacak dan pantau riwayat tindakan penting yang dilakukan oleh administrator sistem secara real-time.</p>
        </div>
    </div>

    <!-- Activity Logs Card -->
    <div id="activity-logs-card" class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden" hx-boost="true" hx-target="#activity-logs-card" hx-select="#activity-logs-card">
        
        <!-- Search & Filter Form -->
        <form action="{{ route('admin.activity-logs') }}" method="GET" hx-boost="false" class="p-6 bg-slate-50/50 border-b border-slate-100 space-y-4">
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    <!-- Search Input Container -->
                    <div class="relative w-full md:w-80 flex items-center">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pelaku, deskripsi, atau aksi..." 
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
                    
                    <!-- Filter Action Type -->
                    <select name="action_type" onchange="this.form.submit()" class="py-2.5 px-3 text-xs rounded-xl border border-slate-200 bg-white font-bold text-slate-650 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                        <option value="">Semua Jenis Aksi</option>
                        @foreach($actionTypes as $type)
                            <option value="{{ $type }}" {{ request('action_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>

                    <!-- Per Page Select -->
                    <select name="per_page" onchange="this.form.submit()" class="py-2.5 px-3 text-xs rounded-xl border border-slate-200 bg-white font-bold text-slate-650 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 Baris</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 Baris</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 Baris</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 Baris</option>
                    </select>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                        <th class="py-4 px-6 text-center w-12">No.</th>
                        <th class="py-4 px-6">Pelaku (User)</th>
                        <th class="py-4 px-6">Aksi</th>
                        <th class="py-4 px-6">Deskripsi Aktivitas</th>
                        <th class="py-4 px-6">IP Address</th>
                        <th class="py-4 px-6">Waktu Kejadian</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-4 px-6 text-center text-slate-500 font-bold text-xs">
                                {{ ($logs->currentPage() - 1) * $logs->perPage() + $loop->iteration }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-800 text-xs">{{ $log->user_name }}</span>
                                    @if($log->user)
                                        <span class="text-[10px] text-slate-400 font-semibold">{{ $log->user->email }}</span>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">Sistem / Terhapus</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                @php
                                    $action = $log->action;
                                    $badgeColor = 'bg-slate-50 text-slate-700 border-slate-200';
                                    
                                    if (str_contains($action, 'CREATE')) {
                                        $badgeColor = 'bg-blue-50 text-blue-700 border-blue-200';
                                    } elseif (str_contains($action, 'UPDATE')) {
                                        $badgeColor = 'bg-sky-50 text-sky-700 border-sky-200';
                                    } elseif (str_contains($action, 'DELETE') || str_contains($action, 'REJECT')) {
                                        $badgeColor = 'bg-red-50 text-red-700 border-red-200';
                                    } elseif (str_contains($action, 'VERIFY') || str_contains($action, 'COMPLETE')) {
                                        $badgeColor = 'bg-green-50 text-green-700 border-green-200';
                                    } elseif (str_contains($action, 'RESET')) {
                                        $badgeColor = 'bg-amber-50 text-amber-700 border-amber-200';
                                    }
                                @endphp
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $badgeColor }}">
                                    {{ $action }}
                                </span>
                            </td>
                            <td class="py-4 px-6 font-medium text-slate-650 text-xs max-w-sm break-words">
                                {{ $log->description }}
                            </td>
                            <td class="py-4 px-6 font-mono text-[10px] text-slate-500 font-bold">
                                {{ $log->ip_address ?? '-' }}
                            </td>
                            <td class="py-4 px-6 text-slate-400 font-semibold text-xs">
                                {{ $log->created_at->format('d M Y, H:i') }} WIB
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-6 text-center text-slate-400">
                                Belum ada riwayat aktivitas log.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($logs->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
