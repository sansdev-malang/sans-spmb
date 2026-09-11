@extends('layouts.admin')

@section('title', 'Jadwal Ta\'aruf & Observasi')

@section('content')
<div class="space-y-6">

    <!-- Header & Unit Settings Trigger -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <div class="min-w-0">
                <h1 class="text-xl font-extrabold text-slate-800 dark:text-white truncate">Jadwal Ta'aruf & Observasi</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola jadwal tatap muka, wawancara kesiapan belajar, dan observasi pendaftar.</p>
            </div>
        </div>

        @if($currentUnit)
            <div class="flex items-center gap-2">
                <button type="button" 
                        onclick="openUnitSettingsModal()" 
                        class="bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-bold shadow-sm transition flex items-center gap-2">
                    <i data-lucide="settings-2" class="w-4 h-4 text-brand-emerald"></i>
                    <span>Ketentuan Unit: {{ $currentUnit->name }}</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Unit Tabs Navigation (If multiple units available) -->
    @if(count($units) > 1)
        <div class="flex items-center gap-2 overflow-x-auto">
            @foreach($units as $u)
                @php
                    $isActiveUnit = ($currentUnit && $currentUnit->id == $u->id);
                @endphp
                <a href="{{ route('admin.taaruf', array_merge(request()->query(), ['unit_id' => $u->id])) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 whitespace-nowrap {{ $isActiveUnit ? 'bg-brand-emerald text-white shadow-sm shadow-emerald-500/20' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800' }}">
                    <i data-lucide="building" class="w-3.5 h-3.5 {{ $isActiveUnit ? 'text-brand-yellow' : 'text-slate-400' }}"></i>
                    <span>{{ $u->name }}{{ !$u->is_active ? ' (Nonaktif)' : '' }}</span>
                </a>
            @endforeach
        </div>
    @endif

    <!-- 4 Stats Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Peserta Ta'aruf -->
        <a href="{{ route('admin.taaruf', array_merge(request()->query(), ['status' => 'all'])) }}" 
           class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-150 dark:border-slate-800 shadow-sm flex items-center gap-4 hover:border-slate-400 dark:hover:border-slate-600 transition group">
            <div class="h-12 w-12 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Total Peserta</span>
                <span class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white">{{ $counts['total'] }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Semua Status</span>
            </div>
        </a>

        <!-- 2. Belum Terjadwal -->
        <a href="{{ route('admin.taaruf', array_merge(request()->query(), ['status' => 'unscheduled'])) }}" 
           class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-150 dark:border-slate-800 shadow-sm flex items-center gap-4 hover:border-amber-400 dark:hover:border-amber-600 transition group">
            <div class="h-12 w-12 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                <i data-lucide="calendar-x-2" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider block">Belum Terjadwal</span>
                <span class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white">{{ $counts['unscheduled'] }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Perlu Dialokasikan</span>
            </div>
        </a>

        <!-- 3. Sudah Terjadwal -->
        <a href="{{ route('admin.taaruf', array_merge(request()->query(), ['status' => 'scheduled'])) }}" 
           class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-150 dark:border-slate-800 shadow-sm flex items-center gap-4 hover:border-blue-400 dark:hover:border-blue-600 transition group">
            <div class="h-12 w-12 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                <i data-lucide="calendar-clock" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider block">Sudah Terjadwal</span>
                <span class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white">{{ $counts['scheduled'] }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Menunggu Sesi</span>
            </div>
        </a>

        <!-- 4. Ta'aruf Selesai -->
        <a href="{{ route('admin.taaruf', array_merge(request()->query(), ['status' => 'completed'])) }}" 
           class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-150 dark:border-slate-800 shadow-sm flex items-center gap-4 hover:border-emerald-400 dark:hover:border-emerald-600 transition group">
            <div class="h-12 w-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-brand-emerald dark:text-emerald-400 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                <i data-lucide="user-check" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block">Ta'aruf Selesai</span>
                <span class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white">{{ $counts['completed'] }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Tahap Administrasi</span>
            </div>
        </a>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-150 dark:border-slate-800 shadow-sm">
        <form method="GET" action="{{ route('admin.taaruf') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @if($currentUnitId)
                <input type="hidden" name="unit_id" value="{{ $currentUnitId }}">
            @endif

            <!-- Search input -->
            <div class="relative">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari nama, ID, no HP..." 
                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl pl-10 pr-4 py-2 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald">
            </div>

            <!-- Status filter -->
            <div>
                <select name="status" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                    <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>Semua Status Jadwal</option>
                    <option value="unscheduled" {{ request('status') === 'unscheduled' ? 'selected' : '' }}>⚠️ Belum Terjadwal</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>📅 Sudah Terjadwal</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>✅ Ta'aruf Selesai</option>
                </select>
            </div>

            <!-- Date filter -->
            <div>
                <input type="date" 
                       name="date" 
                       value="{{ request('date') }}" 
                       class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3.5 py-2 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald">
            </div>

            <!-- Action buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>
                @if(request()->hasAny(['search', 'status', 'date']))
                    <a href="{{ route('admin.taaruf', ['unit_id' => $currentUnitId]) }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-bold transition" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-150 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-150 dark:border-slate-800 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/70 dark:bg-slate-950/50">
                        <th class="py-4 px-6">No. Registrasi</th>
                        <th class="py-4 px-6">Calon Murid</th>
                        <th class="py-4 px-6">Unit & Jenjang</th>
                        <th class="py-4 px-6">Jadwal Ta'aruf</th>
                        <th class="py-4 px-6">Lokasi & Penguji</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-xs text-slate-650 dark:text-slate-300 divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($registrations as $reg)
                        @php
                            $isScheduled = !empty($reg->observation_date);
                            $isCompleted = in_array($reg->registration_status, ['taaruf_completed', 'agreement_signed', 'completed']);
                            
                            $isPaudUnit = stripos($reg->unit?->code ?? '', 'PAUD') !== false || stripos($reg->unit?->name ?? '', 'PAUD') !== false || stripos($reg->unit?->name ?? '', 'TK') !== false || stripos($reg->unit?->name ?? '', 'KB') !== false;
                            $fallbackAddress = $isPaudUnit 
                                ? 'Jl. Candi Panggung Indah No. 1-3, Mojolangu, Kecamatan Lowokwaru, Kota Malang, Jawa Timur' 
                                : 'Jl. Arumba No.31, Tunggulwulung, Kec. Lowokwaru, Kota Malang, Jawa Timur';

                            $defaultLocation = $reg->unit?->taaruf_default_location ?: ($reg->unit?->name ?? 'Sekolah Dasar Anak Saleh');
                            $defaultRoom = $reg->unit?->taaruf_default_room ?? '';
                            $defaultAddress = $reg->unit?->taaruf_default_address ?: $fallbackAddress;
                        @endphp
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-855/40 transition">
                            <!-- 1. No. Registrasi -->
                            <td class="py-4 px-6">
                                <div class="font-mono text-xs font-bold text-slate-700 dark:text-slate-300">
                                    SANS-{{ substr($reg->period->year ?? '2026', 0, 4) }}-{{ str_pad($reg->id, 4, '0', STR_PAD_LEFT) }}
                                </div>
                                @if($reg->created_at)
                                    <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 flex items-center gap-1 font-medium whitespace-nowrap">
                                        <i data-lucide="calendar" class="w-3 h-3 text-slate-400"></i>
                                        <span>{{ $reg->created_at->translatedFormat('d M Y, H:i') }} WIB</span>
                                    </div>
                                @endif
                            </td>

                            <!-- 2. Calon Murid -->
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800 dark:text-white text-xs">{{ $reg->candidate_name ?? 'Draft' }}</div>
                                @php
                                    $parentName = $reg->father_name 
                                        ?: ($reg->mother_name 
                                        ?: ($reg->guardian_name 
                                        ?: ($reg->user->name ?? '-')));

                                    $parentContact = $reg->parent_phone 
                                        ?: ($reg->father_phone 
                                        ?: ($reg->mother_phone 
                                        ?: ($reg->guardian_phone 
                                        ?: ($reg->getFieldValue('father_phone') 
                                        ?: ($reg->getFieldValue('mother_phone') 
                                        ?: ($reg->getFieldValue('guardian_phone') ?: null))))));
                                @endphp
                                <div class="text-xs text-slate-400 flex items-center flex-wrap gap-x-2 gap-y-0.5 mt-0.5">
                                    <span>Ortu: {{ $parentName }}</span>
                                    @if($parentContact)
                                        <span class="inline-flex items-center gap-1 text-slate-500 dark:text-slate-400">
                                            <i data-lucide="phone" class="w-3 h-3 text-emerald-500"></i>
                                            <span>{{ $parentContact }}</span>
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- 2. Unit & Grade -->
                            <td class="py-4 px-6">
                                <span class="font-bold text-slate-800 dark:text-white block">{{ $reg->unit?->name }}</span>
                                <span class="text-[11px] text-slate-400">{{ $reg->grade?->name }} ({{ $reg->classProgram?->name ?? 'Reguler' }})</span>
                            </td>

                            <!-- 3. Schedule Time -->
                            <td class="py-4 px-6">
                                @if($isScheduled)
                                    <div class="font-bold text-slate-800 dark:text-white flex items-center gap-1.5">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-brand-emerald"></i>
                                        <span>{{ $reg->observation_date->translatedFormat('d M Y') }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1.5 mt-0.5 font-semibold">
                                        <i data-lucide="clock" class="w-3 h-3 text-slate-400"></i>
                                        <span>{{ $reg->observation_time }}</span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Belum Dijadwalkan
                                    </span>
                                @endif
                            </td>

                            <!-- 4. Location, Room, Address & Interviewer -->
                            <td class="py-4 px-6 max-w-xs">
                                @if($isScheduled)
                                    <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 flex items-center gap-1.5 truncate" title="{{ $reg->observation_location }}">
                                        <span>📍 {{ $reg->observation_location }}</span>
                                    </div>
                                    @if($reg->observation_room)
                                        <div class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 truncate mt-0.5" title="{{ $reg->observation_room }}">
                                            🚪 {{ $reg->observation_room }}
                                        </div>
                                    @endif
                                    @if($reg->observation_address)
                                        <div class="text-[10px] text-slate-400 truncate mt-0.5" title="{{ $reg->observation_address }}">
                                            🏠 {{ $reg->observation_address }}
                                        </div>
                                    @endif
                                    @if($reg->observation_interviewer)
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5" title="{{ $reg->observation_interviewer }}">
                                            👤 {{ $reg->observation_interviewer }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-[11px] text-slate-400 italic">-</span>
                                @endif
                            </td>

                            <!-- 5. Status Badge -->
                            <td class="py-4 px-6 text-center">
                                @if($isCompleted)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        Ta'aruf Selesai
                                    </span>
                                @elseif($isScheduled)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                        Terjadwal
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                        Siap Ta'aruf
                                    </span>
                                @endif
                            </td>

                            <!-- 6. Actions -->
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                    @if(!$isCompleted)
                                        <!-- Schedule Modal Trigger -->
                                        <button type="button" 
                                                onclick="openScheduleModalFromButton(this)" 
                                                data-action-url="{{ route('admin.taaruf.schedule.update', $reg->id) }}"
                                                data-candidate-name="{{ $reg->candidate_name }}"
                                                data-unit-name="{{ $reg->unit?->name }}"
                                                data-unit-code="{{ $reg->unit?->code }}"
                                                data-grade-name="{{ $reg->grade?->name }}"
                                                data-observation-date="{{ $reg->observation_date ? $reg->observation_date->format('Y-m-d') : date('Y-m-d') }}"
                                                data-observation-time="{{ $reg->observation_time ?? 'Sesi 1 (08:00 - 09:30 WIB)' }}"
                                                data-observation-location="{{ $reg->observation_location ?: $defaultLocation }}"
                                                data-observation-room="{{ $reg->observation_room ?: $defaultRoom }}"
                                                data-observation-address="{{ $reg->observation_address ?: $defaultAddress }}"
                                                data-observation-interviewer="{{ $reg->observation_interviewer }}"
                                                data-observation-notes="{{ $reg->observation_notes }}"
                                                data-is-scheduled="{{ $isScheduled ? '1' : '0' }}"
                                                class="px-3 py-1.5 bg-brand-emerald hover-emerald text-white rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-sm"
                                                title="{{ $isScheduled ? 'Ubah Jadwal' : 'Atur Jadwal' }}">
                                            <i data-lucide="{{ $isScheduled ? 'calendar-cog' : 'calendar-plus' }}" class="w-3.5 h-3.5"></i>
                                            <span>{{ $isScheduled ? 'Edit' : 'Atur Jadwal' }}</span>
                                        </button>

                                        <!-- Complete Ta'aruf Action (Hanya tampil jika jadwal sudah dibuat) -->
                                        @if($isScheduled)
                                            <form action="{{ route('admin.taaruf.complete', $reg->id) }}" method="POST" hx-boost="false" class="inline" onsubmit="return confirm('Selesaikan sesi Ta\'aruf ananda {{ addslashes($reg->candidate_name) }}? Status pendaftar akan beralih ke tahap Surat Pernyataan Kesanggupan.');">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-sm" title="Selesaikan Ta'aruf">
                                                    <i data-lucide="check-check" class="w-3.5 h-3.5"></i>
                                                    <span class="hidden sm:inline">Selesai</span>
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Delete/Cancel Schedule -->
                                        @if($isScheduled)
                                            <form action="{{ route('admin.taaruf.schedule.delete', $reg->id) }}" method="POST" hx-boost="false" class="inline" onsubmit="return confirm('Batalkan jadwal Ta\'aruf ananda {{ addslashes($reg->candidate_name) }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 bg-slate-100 hover:bg-rose-50 text-slate-400 hover:text-rose-600 dark:bg-slate-800 dark:hover:bg-rose-950/40 rounded-lg text-xs transition" title="Batalkan Jadwal">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <div class="inline-flex items-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-slate-500 bg-slate-100 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700/80 select-none">
                                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-500"></i>
                                                <span>Selesai</span>
                                            </span>

                                            @if($reg->registration_status === 'taaruf_completed')
                                                <form action="{{ route('admin.taaruf.revert', $reg->id) }}" method="POST" hx-boost="false" class="inline" onsubmit="return confirm('Batalkan penyelesaian Ta\'aruf ananda {{ addslashes($reg->candidate_name) }} dan kembalikan ke tahap penjadwalan / observasi?');">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-[11px] font-bold transition flex items-center gap-1 shadow-xs" title="Batal Selesaikan / Kembalikan ke Jadwal">
                                                        <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                                        <span>Kembalikan</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-6 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-3">
                                    <div class="h-12 w-12 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                        <i data-lucide="calendar-x-2" class="w-6 h-6"></i>
                                    </div>
                                    <span class="font-bold text-sm text-slate-600 dark:text-slate-300">Tidak ada data pendaftar pada tahapan Ta'aruf</span>
                                    <p class="text-xs text-slate-400 max-w-sm">Hanya pendaftar yang telah diverifikasi (status verified atau taaruf_completed) yang tampil di modul ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($registrations->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50">
                {{ $registrations->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Atur Jadwal Ta'aruf Calon Murid -->
<div id="scheduleModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-3xl w-full overflow-hidden shadow-2xl border border-slate-100 dark:border-slate-800 animate-scale-in">
        <form id="scheduleForm" method="POST" action="" hx-boost="false" class="space-y-0">
            @csrf

            <!-- Modal Header -->
            <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-950/50">
                <div>
                    <h3 class="text-base font-extrabold text-slate-800 dark:text-white" id="modalTitle">Atur Jadwal Ta'aruf</h3>
                    <p class="text-xs text-slate-400 mt-0.5" id="modalCandidateInfo">Nama Calon Murid</p>
                </div>
                <button type="button" onclick="closeScheduleModal()" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Body (2-Column Grid) -->
            <div class="p-5 sm:p-6 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <!-- 1. Tanggal Pelaksanaan -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Tanggal Pelaksanaan <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" 
                               id="modalObservationDate" 
                               name="observation_date" 
                               required 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald font-semibold">
                    </div>

                    <!-- 2. Waktu / Sesi Pelaksanaan -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Waktu / Sesi Pelaksanaan <span class="text-rose-500">*</span>
                        </label>
                        <div class="space-y-1.5">
                            <select id="modalTimePreset" onchange="applyTimePreset(this.value)" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-3 py-1.5 text-xs text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                                <option value="">-- Pilih Preset Sesi Cepat --</option>
                                <option value="Sesi 1 (08:00 - 09:30 WIB)">Sesi 1: 08:00 - 09:30 WIB (Pagi)</option>
                                <option value="Sesi 2 (09:30 - 11:00 WIB)">Sesi 2: 09:30 - 11:00 WIB (Pagi)</option>
                                <option value="Sesi 3 (11:00 - 12:30 WIB)">Sesi 3: 11:00 - 12:30 WIB (Siang)</option>
                                <option value="Sesi 4 (13:00 - 14:30 WIB)">Sesi 4: 13:00 - 14:30 WIB (Siang)</option>
                                <option value="Sesi 5 (14:30 - 16:00 WIB)">Sesi 5: 14:30 - 16:00 WIB (Sore)</option>
                            </select>
                            <input type="text" 
                                   id="modalObservationTime" 
                                   name="observation_time" 
                                   required 
                                   placeholder="Misal: Sesi 1 (08:00 - 09:30 WIB)" 
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald font-semibold">
                        </div>
                    </div>

                    <!-- 3. Lokasi / Gedung -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Lokasi / Gedung Pelaksanaan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               id="modalObservationLocation" 
                               name="observation_location" 
                               required 
                               placeholder="Misal: Kampus 1 SD Anak Saleh" 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald font-semibold">
                    </div>

                    <!-- 4. Ruangan -->
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Ruangan Pelaksanaan (Opsional)
                        </label>
                        <input type="text" 
                               id="modalObservationRoom" 
                               name="observation_room" 
                               placeholder="Misal: Ruang Observasi Lantai 1 / Ruang 102" 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                    </div>

                    <!-- 5. Alamat Lengkap (Full Width with Preset Chips) -->
                    <div class="md:col-span-2">
                        <div class="flex items-center justify-between gap-2 mb-1.5 flex-wrap">
                            <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                Alamat Lengkap (Opsional)
                            </label>
                            <div class="flex items-center gap-1.5 flex-wrap text-[11px]">
                                <span class="text-slate-400 font-medium">Pilihan Alamat Default:</span>
                                <button type="button" 
                                        onclick="setModalAddress('Jl. Arumba No.31, Tunggulwulung, Kec. Lowokwaru, Kota Malang, Jawa Timur')" 
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800 font-semibold transition cursor-pointer" 
                                        title="Alamat Kampus SD & SMP">
                                    🏫 SD & SMP (Arumba)
                                </button>
                                <button type="button" 
                                        onclick="setModalAddress('Jl. Candi Panggung Indah No. 1-3, Mojolangu, Kecamatan Lowokwaru, Kota Malang, Jawa Timur')" 
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-purple-50 hover:bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800 font-semibold transition cursor-pointer" 
                                        title="Alamat Kampus PAUD">
                                    🧸 PAUD (Candi Panggung)
                                </button>
                            </div>
                        </div>
                        <input type="text" 
                               id="modalObservationAddress" 
                               name="observation_address" 
                               placeholder="Misal: Jl. Arumba No.31, Tunggulwulung, Kec. Lowokwaru, Kota Malang, Jawa Timur" 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                    </div>

                    <!-- 6. Pewawancara / Penguji (Full Width) -->
                    <div class="md:col-span-2">
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Pewawancara / Penguji (Opsional)
                        </label>
                        <input type="text" 
                               id="modalObservationInterviewer" 
                               name="observation_interviewer" 
                               placeholder="Misal: Tim Observasi & Ustadzah Fatimah, S.Pd" 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                    </div>

                    <!-- 7. Catatan Khusus (Full Width) -->
                    <div class="md:col-span-2">
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Catatan Khusus Tambahan untuk Wali Murid (Opsional)
                        </label>
                        <textarea id="modalObservationNotes" 
                                  name="observation_notes" 
                                  rows="2" 
                                  placeholder="Misal: Harap membawa fotokopi buku KIA, raport, atau mainan kesukaan ananda..." 
                                  class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald"></textarea>
                    </div>

                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-5 sm:p-6 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeScheduleModal()" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-brand-emerald hover-emerald text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Jadwal</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Pengaturan Template Default Unit -->
@if($currentUnit)
<div id="unitSettingsModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-3xl w-full overflow-hidden shadow-2xl border border-slate-100 dark:border-slate-800 animate-scale-in">
        <form id="unitSettingsForm" method="POST" action="{{ route('admin.taaruf.units.settings', $currentUnit->id) }}" hx-boost="false" class="space-y-0">
            @csrf

            <!-- Header -->
            <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-950/50">
                <div>
                    <h3 class="text-base font-extrabold text-slate-800 dark:text-white">Pengaturan Ketentuan Ta'aruf: {{ $currentUnit->name }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Template panduan dan lokasi bawaan yang tampil otomatis pada kartu undangan unit ini.</p>
                </div>
                <button type="button" onclick="closeUnitSettingsModal()" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Body (2-Column Grid) -->
            <div class="p-5 sm:p-6 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <div class="md:col-span-2">
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Judul / Nama Sesi Unit <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="taaruf_title" 
                               value="{{ $currentUnit->taaruf_title ?? 'Sesi Ta\'aruf & Observasi' }}" 
                               required 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald font-semibold">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Lokasi / Gedung Default Unit <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="taaruf_default_location" 
                               value="{{ $currentUnit->taaruf_default_location ?: $currentUnit->name }}" 
                               required 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald font-semibold">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Ruangan Default Unit (Opsional)
                        </label>
                        <input type="text" 
                               name="taaruf_default_room" 
                               value="{{ $currentUnit->taaruf_default_room ?? '' }}" 
                               placeholder="Misal: Ruang Observasi Lantai 1" 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                    </div>

                    <div class="md:col-span-2">
                        <div class="flex items-center justify-between gap-2 mb-1.5 flex-wrap">
                            <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                Alamat Lengkap Default Unit (Opsional)
                            </label>
                            <div class="flex items-center gap-1.5 flex-wrap text-[11px]">
                                <span class="text-slate-400 font-medium">Pilihan Alamat Default:</span>
                                <button type="button" 
                                        onclick="setUnitSettingsAddress('Jl. Arumba No.31, Tunggulwulung, Kec. Lowokwaru, Kota Malang, Jawa Timur')" 
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-blue-50 hover:bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800 font-semibold transition cursor-pointer">
                                    🏫 SD & SMP (Arumba)
                                </button>
                                <button type="button" 
                                        onclick="setUnitSettingsAddress('Jl. Candi Panggung Indah No. 1-3, Mojolangu, Kecamatan Lowokwaru, Kota Malang, Jawa Timur')" 
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-purple-50 hover:bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border border-purple-200 dark:border-purple-800 font-semibold transition cursor-pointer">
                                    🧸 PAUD (Candi Panggung)
                                </button>
                            </div>
                        </div>
                        <input type="text" 
                               id="unitDefaultAddressInput"
                               name="taaruf_default_address" 
                               value="{{ $currentUnit->taaruf_default_address ?? '' }}" 
                               placeholder="Misal: Jl. Arumba No.31, Tunggulwulung, Kec. Lowokwaru, Kota Malang, Jawa Timur" 
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Panduan & Ketentuan Kehadiran Unit
                        </label>
                        <textarea name="taaruf_instructions" 
                                  rows="3" 
                                  class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald font-mono leading-relaxed">{{ $currentUnit->taaruf_instructions }}</textarea>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                            Perlengkapan / Berkas yang Wajib Dibawa
                        </label>
                        <textarea name="taaruf_required_items" 
                                  rows="3" 
                                  class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-2 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald font-mono leading-relaxed">{{ $currentUnit->taaruf_required_items }}</textarea>
                    </div>

                </div>
            </div>

            <!-- Footer -->
            <div class="p-5 sm:p-6 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeUnitSettingsModal()" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 bg-brand-emerald hover-emerald text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Ketentuan Unit</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    function setModalAddress(addr) {
        const input = document.getElementById('modalObservationAddress');
        if (input) {
            input.value = addr;
            input.focus();
        }
    }

    function setUnitSettingsAddress(addr) {
        const input = document.getElementById('unitDefaultAddressInput');
        if (input) {
            input.value = addr;
            input.focus();
        }
    }

    function openScheduleModalFromButton(btn) {
        const actionUrl = btn.getAttribute('data-action-url');
        const name = btn.getAttribute('data-candidate-name') || '';
        const unit = btn.getAttribute('data-unit-name') || '';
        const unitCode = btn.getAttribute('data-unit-code') || '';
        const grade = btn.getAttribute('data-grade-name') || '';
        const obsDate = btn.getAttribute('data-observation-date') || '';
        const obsTime = btn.getAttribute('data-observation-time') || '';
        let obsLoc = btn.getAttribute('data-observation-location') || '';
        if (!obsLoc || obsLoc === 'Sekolah Anak Saleh') {
            obsLoc = unit;
        }
        const obsRoom = btn.getAttribute('data-observation-room') || '';
        let obsAddress = btn.getAttribute('data-observation-address') || '';
        const obsInterviewer = btn.getAttribute('data-observation-interviewer') || '';
        const obsNotes = btn.getAttribute('data-observation-notes') || '';
        const isEdit = btn.getAttribute('data-is-scheduled') === '1';

        const form = document.getElementById('scheduleForm');
        form.action = actionUrl;
        form.setAttribute('action', actionUrl);

        document.getElementById('modalTitle').innerText = isEdit ? 'Edit Jadwal Ta\'aruf' : 'Atur Jadwal Ta\'aruf';
        document.getElementById('modalCandidateInfo').innerText = name + ' (' + unit + ' - ' + grade + ')';
        
        document.getElementById('modalObservationDate').value = obsDate;
        document.getElementById('modalObservationTime').value = obsTime;
        document.getElementById('modalObservationLocation').value = obsLoc;
        document.getElementById('modalObservationRoom').value = obsRoom;

        // Auto fallback default address if not filled
        if (!obsAddress) {
            const isPaud = /paud|tk|kb/i.test(unit) || /paud|tk|kb/i.test(unitCode);
            obsAddress = isPaud 
                ? 'Jl. Candi Panggung Indah No. 1-3, Mojolangu, Kecamatan Lowokwaru, Kota Malang, Jawa Timur'
                : 'Jl. Arumba No.31, Tunggulwulung, Kec. Lowokwaru, Kota Malang, Jawa Timur';
        }
        document.getElementById('modalObservationAddress').value = obsAddress;
        
        document.getElementById('modalObservationInterviewer').value = obsInterviewer;
        document.getElementById('modalObservationNotes').value = obsNotes;
        
        // Reset preset dropdown
        document.getElementById('modalTimePreset').value = '';

        document.getElementById('scheduleModal').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function closeScheduleModal() {
        document.getElementById('scheduleModal').classList.add('hidden');
    }

    function applyTimePreset(val) {
        if (val) {
            document.getElementById('modalObservationTime').value = val;
        }
    }

    function openUnitSettingsModal() {
        const modal = document.getElementById('unitSettingsModal');
        if (modal) modal.classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function closeUnitSettingsModal() {
        const modal = document.getElementById('unitSettingsModal');
        if (modal) modal.classList.add('hidden');
    }

    // Handle AJAX submission for scheduleForm
    document.addEventListener('DOMContentLoaded', function() {
        const scheduleForm = document.getElementById('scheduleForm');
        if (scheduleForm) {
            scheduleForm.addEventListener('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const actionUrl = scheduleForm.getAttribute('action') || scheduleForm.action;
                if (!actionUrl || actionUrl === window.location.href) {
                    if (typeof showToast === 'function') {
                        showToast("URL jadwal belum terpasang dengan benar. Silakan tutup dan buka kembali modal jadwal.", 'warning');
                    } else {
                        alert("URL jadwal belum terpasang dengan benar. Silakan tutup dan buka kembali modal jadwal.");
                    }
                    return;
                }

                const submitBtn = scheduleForm.querySelector('button[type="submit"]');
                const origHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menyimpan...</span>';

                const formData = new FormData(scheduleForm);
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw err; });
                    }
                    return response.json();
                })
                .then(data => {
                    if (window.showToast) {
                        showToast(data.message || "Jadwal Ta'aruf berhasil disimpan.", 'success');
                    }
                    closeScheduleModal();
                    setTimeout(() => {
                        window.location.reload();
                    }, 400);
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origHtml;
                    let msg = "Gagal menyimpan jadwal. Silakan periksa isian form.";
                    if (error && error.errors) {
                        msg = Object.values(error.errors).flat().join('\n');
                    } else if (error && error.message) {
                        msg = error.message;
                    }
                    if (window.showToast) {
                        showToast(msg, 'error');
                    } else {
                        alert(msg);
                    }
                });
            });
        }

        const unitSettingsForm = document.getElementById('unitSettingsForm');
        if (unitSettingsForm) {
            unitSettingsForm.addEventListener('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const actionUrl = unitSettingsForm.getAttribute('action') || unitSettingsForm.action;
                const submitBtn = unitSettingsForm.querySelector('button[type="submit"]');
                const origHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="inline-flex items-center gap-1.5"><svg class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menyimpan...</span>';

                const formData = new FormData(unitSettingsForm);
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                fetch(actionUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw err; });
                    }
                    return response.json();
                })
                .then(data => {
                    if (window.showToast) {
                        showToast(data.message || "Pengaturan unit berhasil disimpan.", 'success');
                    }
                    closeUnitSettingsModal();
                    setTimeout(() => {
                        window.location.reload();
                    }, 400);
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origHtml;
                    let msg = "Gagal menyimpan pengaturan unit.";
                    if (error && error.errors) {
                        msg = Object.values(error.errors).flat().join('\n');
                    } else if (error && error.message) {
                        msg = error.message;
                    }
                    if (window.showToast) {
                        showToast(msg, 'error');
                    } else {
                        alert(msg);
                    }
                });
            });
        }
    });

    // Close modal by clicking outside
    document.getElementById('scheduleModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeScheduleModal();
        }
    });

    document.getElementById('unitSettingsModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeUnitSettingsModal();
        }
    });

    // Close on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeScheduleModal();
            closeUnitSettingsModal();
        }
    });
</script>
@endsection
