@extends('layouts.admin')

@section('title', 'Aktivasi SPMB - Admin Panel')
@section('page_title', 'Aktivasi SPMB')

@section('content')
<div class="w-full space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center gap-3">
            <div class="h-12 w-12 bg-emerald-50 dark:bg-emerald-950/20 text-brand-emerald rounded-2xl flex items-center justify-center shadow-inner">
                <i data-lucide="power" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-800">Aktivasi & Periode SPMB</h1>
                <p class="text-xs text-slate-500 mt-1">Pusat kontrol terpadu untuk mengaktifkan atau menonaktifkan periode, gelombang, jenjang unit, tingkatan kelas, dan komponen pendaftaran lainnya secara instan.</p>
            </div>
        </div>
    </div>

    <!-- Tab Navigation Pills -->
    <div class="flex flex-wrap gap-2 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
        <button type="button" onclick="switchActivationTab('jalur_gelombang')" id="activationTabBtn-jalur_gelombang" class="activation-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow">
            <i data-lucide="git-merge" class="w-4 h-4"></i> Jalur & Gelombang
        </button>
        <button type="button" onclick="switchActivationTab('struktur_akademik')" id="activationTabBtn-struktur_akademik" class="activation-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
            <i data-lucide="graduation-cap" class="w-4 h-4"></i> Struktur Akademik
        </button>
        <button type="button" onclick="switchActivationTab('biaya_tarif')" id="activationTabBtn-biaya_tarif" class="activation-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
            <i data-lucide="coins" class="w-4 h-4"></i> Biaya Pendaftaran
        </button>
        @foreach($gateways as $gw)
            <button type="button" onclick="switchActivationTab('gateway_{{ $gw->code }}')" id="activationTabBtn-gateway_{{ $gw->code }}" class="activation-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
                <i data-lucide="credit-card" class="w-4 h-4"></i> {{ $gw->name }}
            </button>
        @endforeach
    </div>

    <form method="POST" action="{{ route('admin.spmb-settings.registration.update') }}" class="space-y-6">
        @csrf
        
        <!-- TAB 1: Jalur & Gelombang -->
        <div id="activationTabContent-jalur_gelombang" class="activation-tab-content space-y-4">
            <div class="space-y-1">
                <h2 class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                    <i data-lucide="settings" class="w-3.5 h-3.5"></i> Konfigurasi Jalur & Gelombang
                </h2>
                <p class="text-[10px] text-slate-400">Aktifkan periode pendaftaran, gelombang masuk aktif, serta tipe jalur masuk sekolah.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card 1: Periode Akademik -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-4 flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-brand-emerald font-extrabold text-xs border-b border-slate-100 pb-2">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                            <h3>Periode Akademik Aktif</h3>
                        </div>
                        <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Tahun pelajaran aktif untuk sistem pendaftaran online.</p>
                    </div>
                    
                    <div class="space-y-2 pt-2">
                        @forelse($periods as $period)
                            <label class="flex items-center justify-between p-3 rounded-xl border border-slate-150 hover:bg-slate-50/50 cursor-pointer transition opacity-55 has-[:checked]:opacity-100 hover:opacity-85">
                                <span class="text-xs font-bold text-slate-700">{{ $period->year }}</span>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="active_periods[]" value="{{ $period->id }}" {{ $period->is_active ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                                </div>
                            </label>
                        @empty
                            <p class="text-xs text-slate-400 font-semibold py-2">Belum ada data periode.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Card 2: Gelombang Pendaftaran -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-4 flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-brand-emerald font-extrabold text-xs border-b border-slate-100 pb-2">
                            <i data-lucide="waves" class="w-4 h-4"></i>
                            <h3>Gelombang Pendaftaran</h3>
                        </div>
                        <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Gelombang masuk yang sedang dibuka untuk pendaftaran.</p>
                    </div>
                    
                    <div class="space-y-2 pt-2">
                        @forelse($waves as $wave)
                            <label class="flex items-center justify-between p-3 rounded-xl border border-slate-150 hover:bg-slate-50/50 cursor-pointer transition opacity-55 has-[:checked]:opacity-100 hover:opacity-85">
                                <span class="text-xs font-bold text-slate-700">{{ $wave->name }}</span>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="active_waves[]" value="{{ $wave->id }}" {{ $wave->is_active ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                                </div>
                            </label>
                        @empty
                            <p class="text-xs text-slate-400 font-semibold py-2">Belum ada data gelombang.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Card 3: Jenis Pendaftaran -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-4 flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-brand-emerald font-extrabold text-xs border-b border-slate-100 pb-2">
                            <i data-lucide="tag" class="w-4 h-4"></i>
                            <h3>Jenis Pendaftaran</h3>
                        </div>
                        <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Kategori pendaftaran yang aktif di formulir pendaftar.</p>
                    </div>
                    
                    <div class="space-y-2 pt-2">
                        @forelse($types as $type)
                            <label class="flex items-center justify-between p-3 rounded-xl border border-slate-150 hover:bg-slate-50/50 cursor-pointer transition opacity-55 has-[:checked]:opacity-100 hover:opacity-85">
                                <span class="text-xs font-bold text-slate-700">{{ $type->name }}</span>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="active_types[]" value="{{ $type->id }}" {{ $type->is_active ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                                </div>
                            </label>
                        @empty
                            <p class="text-xs text-slate-400 font-semibold py-2">Belum ada data kategori.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Card 4: Program Kelas -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-4 flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-brand-emerald font-extrabold text-xs border-b border-slate-100 pb-2">
                            <i data-lucide="book-open" class="w-4 h-4"></i>
                            <h3>Program Kelas</h3>
                        </div>
                        <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Program kelas (seperti Reguler, Tahfidz) yang bisa dipilih.</p>
                    </div>
                    
                    <div class="space-y-2 pt-2">
                        @forelse($classPrograms as $program)
                            <label class="flex items-center justify-between p-3 rounded-xl border border-slate-150 hover:bg-slate-50/50 cursor-pointer transition opacity-55 has-[:checked]:opacity-100 hover:opacity-85">
                                <span class="text-xs font-bold text-slate-700">{{ $program->name }}</span>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="active_programs[]" value="{{ $program->id }}" {{ $program->is_active ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                                </div>
                            </label>
                        @empty
                            <p class="text-xs text-slate-400 font-semibold py-2">Belum ada data program kelas.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: Struktur Akademik -->
        <div id="activationTabContent-struktur_akademik" class="activation-tab-content space-y-4 hidden">
            <div class="space-y-1">
                <h2 class="text-xs font-black uppercase tracking-widest text-slate-405 flex items-center gap-2">
                    <i data-lucide="graduation-cap" class="w-3.5 h-3.5"></i> Konfigurasi Jenjang & Struktur Akademik
                </h2>
                <p class="text-[10px] text-slate-400">Aktifkan unit sekolah, tingkatan kelas per jenjang, serta layanan non-formal tambahan.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 5: Unit Pendidikan -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-4 flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-brand-emerald font-extrabold text-xs border-b border-slate-100 pb-2">
                            <i data-lucide="school" class="w-4 h-4"></i>
                            <h3>Unit Pendidikan Aktif</h3>
                        </div>
                        <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Tampilkan atau sembunyikan pendaftaran jenjang unit sekolah.</p>
                    </div>
                    
                    <div class="space-y-2 pt-2">
                        @forelse($units as $unit)
                            <label class="flex items-center justify-between p-3 rounded-xl border border-slate-150 hover:bg-slate-50/50 cursor-pointer transition opacity-55 has-[:checked]:opacity-100 hover:opacity-85">
                                <div>
                                    <span class="text-xs font-bold text-slate-700 block">{{ $unit->name }}</span>
                                    <span class="text-[9px] text-slate-400 font-semibold">{{ $unit->code }}</span>
                                </div>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="active_units[]" value="{{ $unit->id }}" {{ $unit->is_active ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                                </div>
                            </label>
                        @empty
                            <p class="text-xs text-slate-400 font-semibold py-2">Belum ada data unit.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Card 8: Tingkatan Kelas (Grouped by Unit) -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-4 flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-brand-emerald font-extrabold text-xs border-b border-slate-100 pb-2">
                            <i data-lucide="layers" class="w-4 h-4"></i>
                            <h3>Tingkatan Kelas</h3>
                        </div>
                        <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Tingkat kelas aktif kelompok tujuan pendaftaran online.</p>
                    </div>
                    
                    <div class="space-y-3 pt-2 max-h-[220px] overflow-y-auto pr-1">
                        @php
                            $groupedGrades = $grades->groupBy('spmb_unit_id');
                        @endphp
                        @forelse($units as $unit)
                            @if(isset($groupedGrades[$unit->id]))
                                <div class="space-y-1.5 pb-2">
                                    <div class="text-[9px] font-black uppercase text-slate-400 tracking-wider flex items-center gap-1">
                                        <i data-lucide="chevron-right" class="w-2.5 h-2.5"></i> {{ $unit->code }}
                                    </div>
                                    <div class="space-y-1.5 pl-1.5">
                                        @foreach($groupedGrades[$unit->id] as $grade)
                                            <label class="flex items-center justify-between p-2 rounded-lg border border-slate-150 hover:bg-slate-50/50 cursor-pointer transition opacity-55 has-[:checked]:opacity-100 hover:opacity-85">
                                                <span class="text-[11px] font-bold text-slate-650">{{ $grade->name }}</span>
                                                <div class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" name="active_grades[]" value="{{ $grade->id }}" {{ $grade->is_active ? 'checked' : '' }} class="sr-only peer">
                                                    <div class="w-7 h-4 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[1px] after:left-[1px] after:bg-white after:border-slate-350 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:after:translate-x-full"></div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @empty
                            <p class="text-xs text-slate-400 font-semibold py-2">Belum ada data tingkatan kelas.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Card 7: Layanan Non-Formal (Tambahan) -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-4 flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-brand-emerald font-extrabold text-xs border-b border-slate-100 pb-2">
                            <i data-lucide="puzzle" class="w-4 h-4"></i>
                            <h3>Layanan Non-Formal</h3>
                        </div>
                        <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Layanan tambahan opsional yang ditawarkan pada pendaftaran.</p>
                    </div>
                    
                    <div class="space-y-2 pt-2">
                        @forelse($extraServices as $service)
                            <label class="flex items-center justify-between p-3 rounded-xl border border-slate-150 hover:bg-slate-50/50 cursor-pointer transition opacity-55 has-[:checked]:opacity-100 hover:opacity-85">
                                <div>
                                    <span class="text-xs font-bold text-slate-700 block">{{ $service->name }}</span>
                                    <span class="text-[9px] text-slate-400 font-semibold font-mono">{{ $service->code }}</span>
                                </div>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="active_services[]" value="{{ $service->id }}" {{ $service->is_active ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                                </div>
                            </label>
                        @empty
                            <p class="text-xs text-slate-400 font-semibold py-2">Belum ada data layanan tambahan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: Biaya Pendaftaran -->
        <div id="activationTabContent-biaya_tarif" class="activation-tab-content space-y-4 hidden">
            <div class="space-y-1">
                <h2 class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                    <i data-lucide="settings" class="w-3.5 h-3.5"></i> Aktivasi Nominal Biaya & Tarif
                </h2>
                <p class="text-[10px] text-slate-400">Aktifkan atau nonaktifkan tarif biaya pendaftaran formulir, uang pangkal administrasi, dan tambahan operasional per unit.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Loop through Fee Categories dynamically (Match Tarif & Biaya Tabs) -->
                @foreach($feeCategories as $category)
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-brand-emerald font-extrabold text-xs border-b border-slate-100 pb-2">
                                <i data-lucide="coins" class="w-4 h-4"></i>
                                <h3>Aktivasi {{ $category->name }}</h3>
                            </div>
                            <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Kelola aktifasi komponen {{ strtolower($category->name) }}.</p>
                        </div>
                        
                        <div class="space-y-4 pt-2 max-h-[300px] overflow-y-auto pr-1">
                            @php
                                $groupedFees = $category->fees->groupBy(function($fee) {
                                    return $fee->spmb_unit_id ?? 'umum';
                                });
                            @endphp
                            
                            @forelse($groupedFees as $unitKey => $feesList)
                                @php
                                    $firstFee = $feesList->first();
                                    $unitName = $firstFee->unit->name ?? 'Semua Unit (Umum)';
                                    $unitCode = $firstFee->unit->code ?? 'UMUM';
                                @endphp
                                <div class="space-y-2">
                                    <div class="text-[9px] font-black uppercase text-slate-450 tracking-wider flex items-center gap-1">
                                        <i data-lucide="chevron-right" class="w-2.5 h-2.5 text-brand-emerald"></i> {{ $unitCode }} ({{ $unitName }})
                                    </div>
                                    <div class="space-y-2 pl-1.5">
                                        @foreach($feesList as $fee)
                                            <label class="flex items-center justify-between p-3 rounded-xl border border-slate-150 hover:bg-slate-50/50 cursor-pointer transition opacity-55 has-[:checked]:opacity-100 hover:opacity-85">
                                                <div>
                                                    <span class="text-xs font-bold text-slate-700 block">
                                                        {{ $fee->name }}
                                                    </span>
                                                    <span class="text-[10px] text-brand-emerald font-bold">Rp {{ number_format($fee->amount, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" name="active_fees[]" value="{{ $fee->id }}" {{ $fee->is_active ? 'checked' : '' }} class="sr-only peer">
                                                    <div class="w-9 h-5 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-slate-400 font-semibold py-2">Belum ada data komponen biaya.</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @foreach($gateways as $gw)
            <!-- TAB Gateway: {{ $gw->name }} -->
            <div id="activationTabContent-gateway_{{ $gw->code }}" class="activation-tab-content space-y-4 hidden">
                <div class="space-y-1">
                    <h2 class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                        <i data-lucide="credit-card" class="w-3.5 h-3.5"></i> Aktivasi Channel {{ $gw->name }}
                    </h2>
                    <p class="text-[10px] text-slate-400">Aktifkan atau nonaktifkan metode pembayaran yang didukung oleh {{ $gw->name }} untuk SPMB.</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-4">
                    <div class="flex items-center gap-2 text-brand-emerald font-extrabold text-xs border-b border-slate-100 pb-2">
                        <i data-lucide="check-square" class="w-4 h-4"></i>
                        <h3>Metode Pembayaran Tersedia ({{ $gw->name }})</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 pt-2">
                        @forelse($gw->paymentChannels as $chan)
                            <label class="flex items-center justify-between p-3 rounded-xl border border-slate-150 hover:bg-slate-50/50 cursor-pointer transition opacity-55 has-[:checked]:opacity-100 hover:opacity-85">
                                <div>
                                    <span class="text-xs font-bold text-slate-700 block">
                                        {{ $chan->name }}
                                    </span>
                                    <span class="text-[9px] uppercase font-black text-slate-400 tracking-wider">
                                        Code: {{ $chan->code }} | Type: {{ $chan->type }}
                                    </span>
                                </div>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="active_channels[]" value="{{ $chan->id }}" {{ $chan->is_active ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                                </div>
                            </label>
                        @empty
                            <div class="col-span-full py-4 text-center">
                                <p class="text-xs text-slate-400 font-semibold">Belum ada channel pembayaran terdaftar untuk gateway ini.</p>
                                @if($gw->code === 'winpay')
                                    <div class="mt-2">
                                        <a href="{{ route('admin.settings') }}" class="inline-flex items-center gap-1 bg-brand-emerald text-white px-3 py-1.5 rounded-lg text-[10px] font-bold shadow hover:bg-emerald-600">
                                            <i data-lucide="refresh-cw" class="w-3 h-3"></i> Singkronkan Channel di Pengaturan Teknis
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Submit Footer -->
        <div class="bg-white rounded-2xl border border-slate-100 p-4 flex flex-col sm:flex-row gap-3 justify-between items-center shadow-sm">
            <span class="text-xs text-slate-400 font-semibold text-center sm:text-left">Tandai status di atas untuk mengaktifkan atau menonaktifkan komponen di pendaftaran online.</span>
            <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl text-xs font-bold transition shadow-md flex items-center gap-1.5 w-full sm:w-auto justify-center">
                <i data-lucide="save" class="w-4 h-4"></i>
                Simpan Konfigurasi Aktivasi
            </button>
        </div>

    </form>
</div>

<script>
    function switchActivationTab(tabId) {
        document.querySelectorAll('.activation-tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.activation-tab-btn').forEach(el => {
            el.classList.remove('bg-brand-emerald', 'text-white', 'shadow');
            el.classList.add('text-slate-600', 'hover:bg-slate-50');
        });
        document.getElementById('activationTabContent-' + tabId).classList.remove('hidden');
        document.getElementById('activationTabBtn-' + tabId).classList.remove('text-slate-600', 'hover:bg-slate-50');
        document.getElementById('activationTabBtn-' + tabId).classList.add('bg-brand-emerald', 'text-white', 'shadow');
    }
</script>
@endsection
