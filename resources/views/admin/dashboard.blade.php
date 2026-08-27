@extends('layouts.admin')

@section('title', 'Admin Dashboard - Portal SPMB')
@section('page_title', 'Dashboard Utama')

@section('content')
<div class="space-y-8">
    
    <!-- Top Greeting Header -->
    <div class="bg-gradient-to-r from-emerald-800 to-emerald-950 rounded-3xl p-8 text-white shadow-lg relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="absolute -right-16 -top-16 w-44 h-44 rounded-full bg-white/10 blur-2xl"></div>
        <div class="absolute -left-10 -bottom-10 w-36 h-36 rounded-full bg-emerald-400/20 blur-xl"></div>
        
        <div class="space-y-2 relative z-10">
            <h2 class="text-2xl font-black tracking-wide">Selamat Datang di Portal Panitia SPMB</h2>
            <p class="text-xs text-brand-yellow font-bold uppercase tracking-widest">
                Sekolah Anak Saleh • 
                @if(auth()->user()->isSuperAdmin())
                    Pusat Kendali Administrasi
                @else
                    Pusat Kendali {{ auth()->user()->spmbUnit->name ?? 'Unit' }}
                @endif
            </p>
            <p class="text-[11px] text-emerald-100 max-w-xl font-medium leading-relaxed mt-2">
                @if(auth()->user()->isSuperAdmin())
                    Berikut adalah rangkuman performa statistik pendaftaran calon siswa baru secara real-time. Kelola verifikasi data berkas secara berkala pada menu Verifikasi Data.
                @else
                    Berikut adalah rangkuman performa statistik pendaftaran calon siswa baru pada unit {{ auth()->user()->spmbUnit->name ?? 'Unit' }} secara real-time. Kelola verifikasi data berkas secara berkala pada menu Verifikasi Data.
                @endif
            </p>
        </div>
        <div class="flex gap-2 relative z-10 flex-shrink-0">
            <a href="{{ route('admin.verification') }}" class="bg-brand-yellow hover:bg-yellow-500 text-slate-900 px-5 py-3 rounded-2xl text-xs font-black shadow-md transition flex items-center gap-1.5">
                <i data-lucide="check-square" class="w-4 h-4 text-slate-900"></i> Verifikasi Berkas Masuk
            </a>
        </div>
    </div>

    <!-- Stats Grid: 5 Premium Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <!-- Stat 1 -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-[9px] text-slate-400 font-bold block uppercase tracking-wider">Total Pendaftar</span>
                <span class="text-2xl font-black text-slate-800 block mt-1">{{ $totalCandidates }}</span>
            </div>
            <div class="h-10 w-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
        </div>
        <!-- Stat 2 -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-[9px] text-slate-400 font-bold block uppercase tracking-wider">Berkas Masuk</span>
                <span class="text-2xl font-black text-amber-600 block mt-1">{{ $submittedCandidates }}</span>
            </div>
            <div class="h-10 w-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="file-text" class="w-5 h-5"></i>
            </div>
        </div>
        <!-- Stat 3 -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-[9px] text-slate-400 font-bold block uppercase tracking-wider">Terverifikasi</span>
                <span class="text-2xl font-black text-green-600 block mt-1">{{ $verifiedCandidates }}</span>
            </div>
            <div class="h-10 w-10 bg-green-50 text-green-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
        </div>
        <!-- Stat 4 -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-[9px] text-slate-400 font-bold block uppercase tracking-wider">Transaksi Lunas</span>
                <span class="text-2xl font-black text-emerald-600 block mt-1">{{ $paidTransactions }}</span>
            </div>
            <div class="h-10 w-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="wallet" class="w-5 h-5"></i>
            </div>
        </div>
        <!-- Stat 5 (Span full on mobile) -->
        <div class="col-span-2 md:col-span-1 bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-[9px] text-slate-400 font-bold block uppercase tracking-wider">Total Revenue</span>
                <span class="text-lg font-black text-slate-800 block mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
            </div>
            <div class="h-10 w-10 bg-brand-yellow/10 text-brand-yellow rounded-xl flex items-center justify-center flex-shrink-0">
                <i data-lucide="coins" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Charts & Graphics Sections -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Graph 1: Target Pendaftaran Level (Bar Progress) -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wide flex items-center gap-1.5">
                    <i data-lucide="graduation-cap" class="w-4 h-4 text-brand-emerald"></i>
                    Pendaftar per Tingkat Kelas
                </h3>
            </div>
            <div class="space-y-4 pt-2">
                @forelse($levelStats as $stat)
                    @php
                        $percentage = $totalCandidates > 0 ? round(($stat->count / $totalCandidates) * 100) : 0;
                    @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs font-bold text-slate-700">
                            <span>{{ $stat->admission_level }}</span>
                            <span>{{ $stat->count }} Siswa ({{ $percentage }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-brand-emerald h-full rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6 font-semibold">Belum ada tingkat kelas terdaftar.</p>
                @endforelse
            </div>
        </div>

        <!-- Graph 2: Form Status Metrics (Circular Progress Bars) -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wide flex items-center gap-1.5">
                    <i data-lucide="pie-chart" class="w-4 h-4 text-brand-emerald"></i>
                    Status Peninjauan Berkas
                </h3>
            </div>
            <div class="grid grid-cols-2 gap-4 pt-2">
                @foreach($statusStats as $label => $count)
                    @php
                        $percentage = $totalCandidates > 0 ? round(($count / $totalCandidates) * 100) : 0;
                        $colorClass = $label === 'Verified' ? 'bg-green-500' : ($label === 'Submitted' ? 'bg-blue-500' : ($label === 'Failed' ? 'bg-red-500' : 'bg-slate-400'));
                        $textColor = $label === 'Verified' ? 'text-green-600' : ($label === 'Submitted' ? 'text-blue-600' : ($label === 'Failed' ? 'text-red-600' : 'text-slate-500'));
                    @endphp
                    <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/50 space-y-2 flex flex-col justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">{{ $label }}</span>
                        <div class="flex items-baseline justify-between">
                            <span class="text-xl font-black text-slate-800">{{ $count }}</span>
                            <span class="text-[10px] font-bold {{ $textColor }}">{{ $percentage }}%</span>
                        </div>
                        <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                            <div class="{{ $colorClass }} h-full rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Graph 3: Payment Status Metrics -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wide flex items-center gap-1.5">
                    <i data-lucide="bar-chart-3" class="w-4 h-4 text-brand-emerald"></i>
                    Status Pembayaran Formulir
                </h3>
            </div>
            <div class="space-y-4 pt-2">
                @foreach($paymentStats as $label => $count)
                    @php
                        $percentage = $totalCandidates > 0 ? round(($count / $totalCandidates) * 100) : 0;
                        $colorClass = $label === 'Paid' ? 'bg-emerald-500' : ($label === 'Pending' ? 'bg-yellow-500' : 'bg-slate-300');
                    @endphp
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs font-bold text-slate-700">
                            <span class="flex items-center gap-1">
                                <span class="h-2 w-2 rounded-full {{ $colorClass }}"></span>
                                {{ $label }}
                            </span>
                            <span>{{ $count }} Transaksi ({{ $percentage }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                            <div class="{{ $colorClass }} h-full rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Bottom Dashboard Row: Recent Registrations & System Status / Activity Logs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Column 1 & 2: Recent Registrations -->
        <div class="md:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wide flex items-center gap-1.5">
                    <i data-lucide="history" class="w-4 h-4 text-brand-emerald"></i>
                    5 Pendaftaran Terbaru
                </h3>
                <a href="{{ route('admin.verification') }}" class="text-[10px] font-bold text-brand-emerald hover:underline flex items-center gap-1">
                    Lihat Semua <i data-lucide="chevron-right" class="w-3 h-3"></i>
                </a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[9px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-3 px-4 text-center w-10">No.</th>
                            <th class="py-3 px-4">Calon Siswa</th>
                            <th class="py-3 px-4">Unit</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-slate-100">
                        @forelse($recentRegistrations as $reg)
                            <tr class="hover:bg-slate-50/30 transition">
                                <td class="py-3 px-4 text-center text-slate-400 font-bold">{{ $loop->iteration }}</td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800">{{ $reg->candidate_name }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Wali: {{ $reg->father_name ?? $reg->mother_name ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-650">{{ strtoupper($reg->unit->name ?? '-') }}</td>
                                <td class="py-3 px-4 text-center">
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-slate-100 text-slate-600 border-slate-200',
                                            'submitted' => 'bg-blue-50 text-blue-600 border-blue-100',
                                            'verified' => 'bg-green-50 text-green-600 border-green-100',
                                            'taaruf_completed' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
                                            'agreement_signed' => 'bg-purple-50 text-purple-600 border-purple-100',
                                            'completed' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                            'failed' => 'bg-red-50 text-red-600 border-red-100',
                                        ];
                                        $color = $statusColors[$reg->registration_status] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                                    @endphp
                                    <span class="inline-block px-2.5 py-0.5 rounded-full border {{ $color }} text-[9px] font-extrabold uppercase">
                                        {{ $reg->registration_status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('admin.verification') }}?search={{ urlencode($reg->candidate_name) }}" class="inline-block bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 px-3 py-1 rounded-lg font-bold transition">
                                        Periksa
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400 font-semibold">Belum ada pendaftaran masuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Column 3: System Info & Recent Activity Logs -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
            @if(auth()->user()->isSuperAdmin())
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wide flex items-center gap-1.5">
                        <i data-lucide="shield-alert" class="w-4 h-4 text-brand-emerald"></i>
                        Status & Aktivitas Sistem
                    </h3>
                </div>
                
                <!-- System Indicators -->
                <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                    <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl">
                        <span class="text-[9px] font-bold text-slate-400 block uppercase">Total User</span>
                        <span class="font-extrabold text-slate-800 text-sm mt-0.5 block">{{ $totalUsersCount }} Akun</span>
                    </div>
                    <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl">
                        <span class="text-[9px] font-bold text-slate-400 block uppercase">Gelombang Aktif</span>
                        <span class="font-extrabold text-brand-emerald text-xs mt-0.5 block truncate" title="{{ $activeWave->name ?? 'Tidak ada' }}">
                            {{ $activeWave->name ?? 'Tidak ada' }}
                        </span>
                    </div>
                </div>

                <!-- Recent Mini Logs -->
                <div class="space-y-3 pt-2">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-wider block">Log Aktivitas Terbaru</span>
                    <div class="space-y-2.5">
                        @forelse($recentLogs as $log)
                            <div class="text-[11px] leading-relaxed border-l-2 border-brand-emerald pl-2 py-0.5">
                                <div class="flex justify-between text-[9px] text-slate-400 font-semibold">
                                    <span class="font-bold text-slate-600 truncate max-w-[80px]" title="{{ $log->user_name }}">{{ $log->user_name }}</span>
                                    <span>{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-slate-650 font-medium mt-0.5">{{ $log->description }}</p>
                            </div>
                        @empty
                            <p class="text-[10px] text-slate-400 font-semibold text-center py-4">Belum ada log terekam.</p>
                        @endforelse
                    </div>
                    <a href="{{ route('admin.activity-logs') }}" class="text-[9px] font-bold text-brand-emerald hover:underline block pt-2">
                        Lihat Semua Log →
                    </a>
                </div>
            @else
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wide flex items-center gap-1.5">
                        <i data-lucide="info" class="w-4 h-4 text-brand-emerald"></i>
                        Informasi Unit
                    </h3>
                </div>
                
                <div class="space-y-4 pt-2">
                    <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl space-y-2.5">
                        <div>
                            <span class="text-[9px] font-bold text-slate-400 block uppercase">Unit Operasional</span>
                            <span class="font-extrabold text-slate-800 text-sm mt-0.5 block">{{ auth()->user()->spmbUnit->name ?? 'Unit' }}</span>
                        </div>
                        <div>
                            <span class="text-[9px] font-bold text-slate-400 block uppercase">Alamat / Keterangan</span>
                            <span class="text-xs text-slate-600 font-semibold mt-0.5 block leading-relaxed">
                                {{ auth()->user()->spmbUnit->address ?? 'Pusat Kendali Administrasi Unit Sekolah Anak Saleh.' }}
                            </span>
                        </div>
                    </div>

                    <div class="p-4 bg-emerald-50/50 border border-emerald-100 rounded-2xl text-xs text-brand-emerald leading-relaxed">
                        <div class="flex gap-2 items-start">
                            <i data-lucide="check-circle-2" class="w-4.5 h-4.5 text-brand-emerald flex-shrink-0 mt-0.5"></i>
                            <div>
                                <span class="font-bold block text-slate-800">Status Gelombang</span>
                                <span class="text-[11px] text-slate-500 font-medium block mt-0.5">
                                    Gelombang registrasi yang aktif saat ini: 
                                    <strong class="text-brand-emerald">{{ $activeWave->name ?? 'Tidak ada gelombang aktif' }}</strong>.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
