@extends('layouts.admin')

@section('title', 'Aktivasi SPMB - Admin Panel')
@section('page_title', 'Aktivasi SPMB')

@section('content')
<div id="registration-settings-container" hx-boost="true" hx-target="#registration-settings-container" hx-select="#registration-settings-container" class="w-full space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold text-slate-800">Aktivasi & Konfigurasi SPMB Per Unit</h1>
                <p class="text-xs text-slate-500 mt-1">Pusat kontrol terpadu untuk mengaktifkan atau menonaktifkan periode, gelombang, jalur, tingkatan kelas, dan tarif pendaftaran secara independen per unit sekolah.</p>
            </div>
            @if(!$isSuperAdmin)
                <div class="inline-flex items-center gap-2 bg-emerald-50 text-brand-emerald px-4 py-2 rounded-xl border border-emerald-100/80 text-xs font-bold self-start md:self-auto">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    <span>Unit: {{ $selectedUnit->name }}</span>
                </div>
            @endif
        </div>
    </div>

    @php
        $activeTab = request('tab', 'jalur_gelombang');
    @endphp

    <!-- Unit Switcher Bar (For Super Admin) -->
    @if($isSuperAdmin)
        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-black text-slate-400 uppercase tracking-wider flex items-center gap-1.5 mr-1">
                    <i data-lucide="layers" class="w-4 h-4 text-brand-emerald"></i> Pilih Unit Sekolah:
                </span>
                <div class="flex flex-wrap gap-2">
                    @foreach($units as $u)
                        <a href="{{ route('admin.spmb-settings.registration', ['unit_id' => $u->id, 'tab' => $activeTab]) }}" 
                           class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 {{ $selectedUnitId == $u->id ? 'bg-brand-emerald text-white shadow-md ring-2 ring-brand-emerald/30 font-extrabold' : 'bg-slate-100/80 text-slate-600 hover:bg-slate-200/70' }}">
                            <span class="w-2.5 h-2.5 rounded-full {{ $u->is_active ? 'bg-emerald-300 ring-2 ring-white/50' : 'bg-rose-400' }}"></span>
                            {{ $u->name }} ({{ $u->code }})
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="text-[11px] text-slate-400 font-medium flex items-center gap-1">
                <span>Sedang mengelola:</span>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-brand-emerald font-bold rounded-lg border border-emerald-100">
                    <i data-lucide="building-2" class="w-3 h-3"></i> {{ $selectedUnit->name }}
                </span>
            </div>
        </div>
    @else
        <!-- Unit Admin Scoped Badge -->
        <div class="bg-emerald-50/60 border border-emerald-200/80 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-emerald text-white flex items-center justify-center font-black text-sm shadow-sm flex-shrink-0">
                    {{ $selectedUnit->code }}
                </div>
                <div>
                    <div class="text-xs font-extrabold text-slate-800 flex items-center gap-2">
                        Konfigurasi Aktivasi Unit {{ $selectedUnit->name }}
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $selectedUnit->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ $selectedUnit->is_active ? 'Unit Aktif' : 'Unit Nonaktif' }}
                        </span>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Semua perubahan pada form di bawah akan langsung berlaku secara spesifik untuk unit <strong>{{ $selectedUnit->name }}</strong>.</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Tab Navigation Pills -->
    <div class="flex flex-wrap gap-2 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
        <button type="button" onclick="switchActivationTab('jalur_gelombang')" id="activationTabBtn-jalur_gelombang" class="activation-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'jalur_gelombang' ? 'bg-brand-emerald text-white shadow' : 'text-slate-600 hover:bg-slate-50' }}">
            <i data-lucide="git-merge" class="w-4 h-4"></i> Jalur & Gelombang ({{ $selectedUnit->code }})
        </button>
        <button type="button" onclick="switchActivationTab('struktur_akademik')" id="activationTabBtn-struktur_akademik" class="activation-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'struktur_akademik' ? 'bg-brand-emerald text-white shadow' : 'text-slate-600 hover:bg-slate-50' }}">
            <i data-lucide="graduation-cap" class="w-4 h-4"></i> Struktur Akademik ({{ $selectedUnit->code }})
        </button>
        <button type="button" onclick="switchActivationTab('biaya_tarif')" id="activationTabBtn-biaya_tarif" class="activation-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'biaya_tarif' ? 'bg-brand-emerald text-white shadow' : 'text-slate-600 hover:bg-slate-50' }}">
            <i data-lucide="coins" class="w-4 h-4"></i> Biaya Pendaftaran ({{ $selectedUnit->code }})
        </button>
        @if($isSuperAdmin)
            @foreach($gateways as $gw)
                <button type="button" onclick="switchActivationTab('gateway_{{ $gw->code }}')" id="activationTabBtn-gateway_{{ $gw->code }}" class="activation-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'gateway_' . $gw->code ? 'bg-brand-emerald text-white shadow' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i data-lucide="credit-card" class="w-4 h-4"></i> {{ $gw->name }}
                </button>
            @endforeach
        @endif
    </div>

    <form method="POST" action="{{ route('admin.spmb-settings.registration.update') }}" hx-boost="false" class="space-y-6">
        @csrf
        <input type="hidden" name="unit_id" value="{{ $selectedUnitId }}">
        <input type="hidden" name="active_tab" id="active-tab-input" value="{{ $activeTab }}">
        @if($isSuperAdmin)
            <input type="hidden" name="has_channel_config" value="1">
        @endif
        
        <!-- TAB 1: Jalur & Gelombang -->
        <div id="activationTabContent-jalur_gelombang" class="activation-tab-content space-y-4 {{ $activeTab === 'jalur_gelombang' ? '' : 'hidden' }}">
            <div class="space-y-1">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                        <i data-lucide="settings" class="w-3.5 h-3.5 text-brand-emerald"></i> Konfigurasi Jalur & Gelombang - Unit {{ $selectedUnit->name }}
                    </h2>
                    <span class="text-[11px] font-bold text-brand-emerald bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-100">
                        {{ $selectedUnit->code }}
                    </span>
                </div>
                <p class="text-[10px] text-slate-400">Aktifkan periode pendaftaran, gelombang masuk aktif, serta tipe jalur masuk sekolah khusus untuk unit <strong>{{ $selectedUnit->name }}</strong>.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card 1: Periode Akademik -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-4 flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-brand-emerald font-extrabold text-xs border-b border-slate-100 pb-2">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                            <h3>Periode Akademik Aktif</h3>
                        </div>
                        <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Tahun pelajaran aktif pendaftaran untuk unit {{ $selectedUnit->name }}.</p>
                    </div>
                    
                    <div class="space-y-2 pt-2">
                        @forelse($periods as $period)
                            <label class="flex items-center justify-between p-3 rounded-xl border border-slate-150 hover:bg-slate-50/50 cursor-pointer transition opacity-55 has-[:checked]:opacity-100 hover:opacity-85">
                                <span class="text-xs font-bold text-slate-700">{{ $period->year }}</span>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="active_periods[]" value="{{ $period->id }}" {{ $period->unit_is_active ? 'checked' : '' }} class="sr-only peer">
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
                        <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Gelombang masuk dibuka untuk unit {{ $selectedUnit->name }}.</p>
                    </div>
                    
                    <div class="space-y-2 pt-2">
                        @forelse($waves as $wave)
                            <label class="flex items-center justify-between p-3 rounded-xl border border-slate-150 hover:bg-slate-50/50 cursor-pointer transition opacity-55 has-[:checked]:opacity-100 hover:opacity-85">
                                <span class="text-xs font-bold text-slate-700">{{ $wave->name }}</span>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="active_waves[]" value="{{ $wave->id }}" {{ $wave->unit_is_active ? 'checked' : '' }} class="sr-only peer">
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
                        <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Jalur masuk yang aktif untuk unit {{ $selectedUnit->name }}.</p>
                    </div>
                    
                    <div class="space-y-2 pt-2">
                        @forelse($types as $type)
                            <label class="flex items-center justify-between p-3 rounded-xl border border-slate-150 hover:bg-slate-50/50 cursor-pointer transition opacity-55 has-[:checked]:opacity-100 hover:opacity-85">
                                <span class="text-xs font-bold text-slate-700">{{ $type->name }}</span>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="active_types[]" value="{{ $type->id }}" {{ $type->unit_is_active ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                                </div>
                            </label>
                        @empty
                            <p class="text-xs text-slate-400 font-semibold py-2">Belum ada data jenis pendaftaran.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Card 4: Kategori Murid -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-4 flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-brand-emerald font-extrabold text-xs border-b border-slate-100 pb-2">
                            <i data-lucide="book-open" class="w-4 h-4"></i>
                            <h3>Kategori Murid</h3>
                        </div>
                        <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Kategori (Reguler, Inklusi) untuk unit {{ $selectedUnit->name }}.</p>
                    </div>
                    
                    <div class="space-y-2 pt-2">
                        @forelse($classPrograms as $program)
                            <label class="flex items-center justify-between p-3 rounded-xl border border-slate-150 hover:bg-slate-50/50 cursor-pointer transition opacity-55 has-[:checked]:opacity-100 hover:opacity-85">
                                <span class="text-xs font-bold text-slate-700">{{ $program->name }}</span>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="active_programs[]" value="{{ $program->id }}" {{ $program->unit_is_active ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                                </div>
                            </label>
                        @empty
                            <p class="text-xs text-slate-400 font-semibold py-2">Belum ada data kategori murid.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: Struktur Akademik -->
        <div id="activationTabContent-struktur_akademik" class="activation-tab-content space-y-4 {{ $activeTab === 'struktur_akademik' ? '' : 'hidden' }}">
            <div class="space-y-1">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                        <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-brand-emerald"></i> Konfigurasi Jenjang & Struktur Akademik - Unit {{ $selectedUnit->name }}
                    </h2>
                    <span class="text-[11px] font-bold text-brand-emerald bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-100">
                        {{ $selectedUnit->code }}
                    </span>
                </div>
                <p class="text-[10px] text-slate-400">Aktifkan unit sekolah, sub-unit, tingkatan kelas per jenjang, serta layanan non-formal tambahan untuk unit <strong>{{ $selectedUnit->name }}</strong>.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 {{ $subUnitData->isNotEmpty() ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }} gap-6">
                <!-- Card 5: Status Unit Pendidikan -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-4 flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-brand-emerald font-extrabold text-xs border-b border-slate-100 pb-2">
                            <i data-lucide="school" class="w-4 h-4"></i>
                            <h3>Status Unit Pendidikan</h3>
                        </div>
                        <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Buka atau tutup seluruh pendaftaran online untuk unit ini di halaman publik portal pendaftar.</p>
                    </div>
                    
                    <div class="space-y-3 pt-2">
                        <label class="flex items-center justify-between p-4 rounded-xl border border-slate-150 hover:bg-slate-50/50 cursor-pointer transition opacity-75 has-[:checked]:opacity-100 bg-slate-50/40">
                            <div>
                                <span class="text-sm font-extrabold text-slate-800 block">{{ $selectedUnit->name }}</span>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider font-mono">Kode Unit: {{ $selectedUnit->code }}</span>
                            </div>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="unit_is_active" value="1" {{ $selectedUnit->is_active ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                            </div>
                        </label>
                        
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-150/70 text-[11px] text-slate-500 space-y-1">
                            <div class="font-bold text-slate-700 flex items-center gap-1.5">
                                <i data-lucide="info" class="w-3.5 h-3.5 text-brand-emerald"></i> Informasi Status Unit
                            </div>
                            <p class="leading-relaxed">Jika dinonaktifkan, calon wali murid tidak akan dapat memilih atau mendaftar ke jenjang {{ $selectedUnit->name }} dari portal SPMB.</p>
                        </div>
                    </div>
                </div>

                @if($subUnitData->isNotEmpty())
                    <!-- Card Sub-Unit: Aktivasi Sub-Unit Pendidikan -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-brand-emerald font-extrabold text-xs border-b border-slate-100 pb-2">
                                <i data-lucide="shapes" class="w-4 h-4"></i>
                                <h3>Aktivasi Sub-Unit</h3>
                            </div>
                            <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Buka atau tutup pendaftaran untuk sub-unit di bawah {{ $selectedUnit->name }}.</p>
                        </div>
                        
                        <div class="space-y-2.5 pt-2 max-h-[250px] overflow-y-auto pr-1">
                            @foreach($subUnitData as $su)
                                @php
                                    $suTheme = match(strtolower($su->name)) {
                                        'playgroup', 'kb' => ['dot' => 'bg-sky-500', 'badge' => 'bg-sky-50 text-sky-700 border-sky-200/80'],
                                        'tk' => ['dot' => 'bg-emerald-500', 'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80'],
                                        'daycare', 'tpa' => ['dot' => 'bg-amber-500', 'badge' => 'bg-amber-50 text-amber-700 border-amber-200/80'],
                                        default => ['dot' => 'bg-indigo-500', 'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200/80']
                                    };
                                @endphp
                                <label class="flex items-center justify-between p-3 rounded-xl border border-slate-150 hover:bg-slate-50/50 cursor-pointer transition opacity-75 has-[:checked]:opacity-100 hover:opacity-100 bg-slate-50/40">
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full {{ $suTheme['dot'] }}"></span>
                                            <span class="text-xs font-extrabold text-slate-800">{{ $su->name }}</span>
                                        </div>
                                        <span class="text-[10px] text-slate-400 font-semibold block sub-unit-counter" data-sub-unit="{{ $su->name }}">
                                            {{ $su->active_count }}/{{ $su->total_count }} kelas aktif
                                        </span>
                                    </div>
                                    <div class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               data-sub-unit-toggle="{{ $su->name }}" 
                                               onchange="toggleSubUnit('{{ $su->name }}', this.checked)" 
                                               {{ $su->is_active ? 'checked' : '' }} 
                                               class="sr-only peer">
                                        <div class="w-9 h-5 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-150/70 text-[10px] text-slate-500 space-y-0.5">
                            <div class="font-bold text-slate-700 flex items-center gap-1">
                                <i data-lucide="info" class="w-3 h-3 text-brand-emerald"></i> Info Sub-Unit
                            </div>
                            <p class="leading-relaxed">Menonaktifkan sub-unit akan otomatis menonaktifkan seluruh tingkatan kelas di dalamnya.</p>
                        </div>
                    </div>
                @endif

                <!-- Card 6: Tingkatan Kelas Unit -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-4 flex flex-col justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-brand-emerald font-extrabold text-xs border-b border-slate-100 pb-2">
                            <i data-lucide="layers" class="w-4 h-4"></i>
                            <h3>Tingkatan Kelas ({{ $selectedUnit->code }})</h3>
                        </div>
                        <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Tingkat kelas aktif yang dibuka untuk pendaftaran unit {{ $selectedUnit->name }}.</p>
                    </div>
                    
                    <div class="space-y-2 pt-2 max-h-[250px] overflow-y-auto pr-1">
                        @forelse($grades as $grade)
                            @php
                                $gradeSuTheme = match(strtolower($grade->sub_unit ?? '')) {
                                    'playgroup', 'kb' => 'bg-sky-50 text-sky-700 border-sky-200/80',
                                    'tk' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                                    'daycare', 'tpa' => 'bg-amber-50 text-amber-700 border-amber-200/80',
                                    default => 'bg-slate-100 text-slate-600 border-slate-200'
                                };
                            @endphp
                            <label class="flex items-center justify-between p-2.5 rounded-xl border border-slate-150 hover:bg-slate-50/50 cursor-pointer transition opacity-55 has-[:checked]:opacity-100 hover:opacity-85">
                                <div>
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-xs font-bold text-slate-700">{{ $grade->name }}</span>
                                        @if($grade->sub_unit)
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded border {{ $gradeSuTheme }}">
                                                {{ $grade->sub_unit }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-[9px] text-slate-400 font-semibold">{{ $selectedUnit->name }}</span>
                                </div>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" 
                                           name="active_grades[]" 
                                           value="{{ $grade->id }}" 
                                           data-sub-unit="{{ $grade->sub_unit ?? '' }}" 
                                           onchange="syncGradeToSubUnit(this)" 
                                           {{ $grade->is_active ? 'checked' : '' }} 
                                           class="sr-only peer grade-checkbox">
                                    <div class="w-9 h-5 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                                </div>
                            </label>
                        @empty
                            <p class="text-xs text-slate-400 font-semibold py-2 text-center">Belum ada data tingkatan kelas untuk unit ini.</p>
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
                        <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Layanan tambahan opsional yang ditawarkan pada unit {{ $selectedUnit->name }}.</p>
                    </div>
                    
                    <div class="space-y-2 pt-2 max-h-[250px] overflow-y-auto pr-1">
                        @forelse($extraServices as $service)
                            <label class="flex items-center justify-between p-2.5 rounded-xl border border-slate-150 hover:bg-slate-50/50 cursor-pointer transition opacity-55 has-[:checked]:opacity-100 hover:opacity-85">
                                <div>
                                    <span class="text-xs font-bold text-slate-700 block">{{ $service->name }}</span>
                                    <span class="text-[9px] text-slate-400 font-semibold font-mono">{{ $service->code }}</span>
                                </div>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="active_services[]" value="{{ $service->id }}" {{ $service->unit_is_active ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                                </div>
                            </label>
                        @empty
                            <p class="text-xs text-slate-400 font-semibold py-2 text-center">Belum ada data layanan tambahan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 3: Biaya Pendaftaran -->
        <div id="activationTabContent-biaya_tarif" class="activation-tab-content space-y-4 {{ $activeTab === 'biaya_tarif' ? '' : 'hidden' }}">
            <div class="space-y-1">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                        <i data-lucide="coins" class="w-3.5 h-3.5 text-brand-emerald"></i> Aktivasi Nominal Biaya & Tarif - Unit {{ $selectedUnit->name }}
                    </h2>
                    <span class="text-[11px] font-bold text-brand-emerald bg-emerald-50 px-2.5 py-0.5 rounded-lg border border-emerald-100">
                        {{ $selectedUnit->code }}
                    </span>
                </div>
                <p class="text-[10px] text-slate-400">Aktifkan atau nonaktifkan tarif biaya formulir pendaftaran, uang pangkal administrasi, dan tambahan operasional khusus unit <strong>{{ $selectedUnit->name }}</strong>.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Loop through Fee Categories dynamically for Selected Unit -->
                @forelse($feeCategories as $category)
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 space-y-4 flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-brand-emerald font-extrabold text-xs border-b border-slate-100 pb-2">
                                <i data-lucide="coins" class="w-4 h-4"></i>
                                <h3>{{ $category->name }}</h3>
                            </div>
                            <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Komponen tarif {{ strtolower($category->name) }} untuk unit {{ $selectedUnit->name }}.</p>
                        </div>
                        
                        <div class="space-y-2.5 pt-2 max-h-[300px] overflow-y-auto pr-1">
                            @forelse($category->fees as $fee)
                                <label class="flex items-center justify-between p-3 rounded-xl border border-slate-150 hover:bg-slate-50/50 cursor-pointer transition opacity-55 has-[:checked]:opacity-100 hover:opacity-85">
                                    <div class="pr-2">
                                        <span class="text-xs font-bold text-slate-700 block">
                                            {{ $fee->name }}
                                        </span>
                                        <span class="text-[11px] text-brand-emerald font-extrabold">Rp {{ number_format($fee->amount, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                        <input type="checkbox" name="active_fees[]" value="{{ $fee->id }}" {{ $fee->is_active ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-9 h-5 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                                    </div>
                                </label>
                            @empty
                                <div class="p-4 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-center">
                                    <p class="text-[11px] text-slate-400 font-semibold">Tidak ada komponen biaya {{ strtolower($category->name) }} khusus unit {{ $selectedUnit->name }}.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-8 text-center bg-white rounded-2xl border border-slate-100">
                        <p class="text-xs text-slate-400 font-semibold">Belum ada data kategori biaya terdaftar.</p>
                    </div>
                @endforelse
            </div>
        </div>

        @if($isSuperAdmin)
            @foreach($gateways as $gw)
                <!-- TAB Gateway: {{ $gw->name }} -->
                <div id="activationTabContent-gateway_{{ $gw->code }}" class="activation-tab-content space-y-4 {{ $activeTab === 'gateway_' . $gw->code ? '' : 'hidden' }}">
                    <div class="space-y-1">
                        <h2 class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                            <i data-lucide="credit-card" class="w-3.5 h-3.5 text-brand-emerald"></i> Aktivasi Channel {{ $gw->name }}
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
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-lg bg-white dark:bg-slate-950/70 border border-slate-150 dark:border-slate-600 flex items-center justify-center p-1 shadow-sm overflow-hidden select-none flex-shrink-0">
                                            @if($chan->getLogoUrl())
                                                <img src="{{ $chan->getLogoUrl() }}" alt="{{ $chan->name }}" class="max-h-full max-w-full object-contain">
                                            @else
                                                <span class="font-extrabold text-[10px] text-slate-450 uppercase">
                                                    {{ substr($chan->code, 0, 3) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="text-xs font-bold text-slate-700 block">
                                                {{ $chan->name }}
                                            </span>
                                            <span class="text-[9px] uppercase font-black text-slate-400 tracking-wider">
                                                Code: {{ $chan->code }} | Type: {{ $chan->type }}
                                            </span>
                                        </div>
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
        @endif

        <!-- Submit Footer -->
        <div class="bg-white rounded-2xl border border-slate-100 p-4 flex flex-col sm:flex-row gap-3 justify-between items-center shadow-sm">
            <span class="text-xs text-slate-400 font-semibold text-center sm:text-left">
                Tandai status di atas untuk mengaktifkan atau menonaktifkan komponen pendaftaran online untuk unit <strong>{{ $selectedUnit->name }}</strong>.
            </span>
            <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl text-xs font-bold transition shadow-md flex items-center gap-1.5 w-full sm:w-auto justify-center">
                <i data-lucide="save" class="w-4 h-4"></i>
                Simpan Konfigurasi Aktivasi ({{ $selectedUnit->code }})
            </button>
        </div>
    </form>
    <script>
        function switchActivationTab(tabId) {
            document.querySelectorAll('.activation-tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.activation-tab-btn').forEach(el => {
                el.classList.remove('bg-brand-emerald', 'text-white', 'shadow');
                el.classList.add('text-slate-600', 'hover:bg-slate-50');
            });
            const content = document.getElementById('activationTabContent-' + tabId);
            const btn = document.getElementById('activationTabBtn-' + tabId);
            if (content) content.classList.remove('hidden');
            if (btn) {
                btn.classList.remove('text-slate-600', 'hover:bg-slate-50');
                btn.classList.add('bg-brand-emerald', 'text-white', 'shadow');
            }
            
            const activeTabInput = document.getElementById('active-tab-input');
            if (activeTabInput) {
                activeTabInput.value = tabId;
            }
            
            // Update URL query parameter without page reload
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tabId);
            window.history.replaceState({ path: url.href }, '', url.href);

            localStorage.setItem('spmb_activation_active_tab', tabId);
        }
        window.switchActivationTab = switchActivationTab;

        function toggleSubUnit(subUnitName, isChecked) {
            const gradeCheckboxes = document.querySelectorAll(`input.grade-checkbox[data-sub-unit="${subUnitName}"]`);
            gradeCheckboxes.forEach(cb => {
                cb.checked = isChecked;
            });
            updateSubUnitCounter(subUnitName);
        }
        window.toggleSubUnit = toggleSubUnit;

        function syncGradeToSubUnit(gradeEl) {
            const subUnitName = gradeEl.getAttribute('data-sub-unit');
            if (!subUnitName) return;
            
            const gradeCheckboxes = document.querySelectorAll(`input.grade-checkbox[data-sub-unit="${subUnitName}"]`);
            const activeCount = Array.from(gradeCheckboxes).filter(cb => cb.checked).length;
            
            const toggleEl = document.querySelector(`input[data-sub-unit-toggle="${subUnitName}"]`);
            if (toggleEl) {
                toggleEl.checked = activeCount > 0;
            }
            
            updateSubUnitCounter(subUnitName);
        }
        window.syncGradeToSubUnit = syncGradeToSubUnit;

        function updateSubUnitCounter(subUnitName) {
            const gradeCheckboxes = document.querySelectorAll(`input.grade-checkbox[data-sub-unit="${subUnitName}"]`);
            const activeCount = Array.from(gradeCheckboxes).filter(cb => cb.checked).length;
            const totalCount = gradeCheckboxes.length;
            
            const counterEl = document.querySelector(`.sub-unit-counter[data-sub-unit="${subUnitName}"]`);
            if (counterEl) {
                counterEl.textContent = `${activeCount}/${totalCount} kelas aktif`;
            }
        }
        window.updateSubUnitCounter = updateSubUnitCounter;

        (function() {
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab') || localStorage.getItem('spmb_activation_active_tab') || 'jalur_gelombang';
            if (document.getElementById('activationTabBtn-' + activeTab)) {
                switchActivationTab(activeTab);
            }
        })();
    </script>
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
@endsection
