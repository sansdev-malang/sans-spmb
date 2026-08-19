@extends('layouts.admin')

@section('title', 'Setting Pendaftaran - Admin Panel')
@section('page_title', 'Setting Pendaftaran')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h1 class="text-xl font-extrabold text-slate-800">Setting Pendaftaran (Aktifasi SPMB)</h1>
        <p class="text-xs text-slate-500 mt-1">Aktifkan atau nonaktifkan periode akademik, gelombang pendaftaran, jenis pendaftaran, dan biaya pendaftaran secara instan.</p>
    </div>



    <form method="POST" action="{{ route('admin.spmb-settings.registration.update') }}" class="space-y-6">
        @csrf
        
        <!-- Grid Sections -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Card 1: Periode Akademik (Checkboxes / Toggles) -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-4">
                <div class="flex items-center gap-2 text-brand-emerald font-extrabold text-sm border-b border-slate-100 pb-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-brand-emerald"></i>
                    <h3>Periode Akademik Aktif</h3>
                </div>
                <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Pilih periode tahun pelajaran mana saja yang sedang berjalan aktif untuk sistem pendaftaran online.</p>
                
                <div class="space-y-3 pt-2">
                    @forelse($periods as $period)
                        <label class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition">
                            <span class="text-xs font-bold text-slate-700">{{ $period->year }}</span>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="active_periods[]" value="{{ $period->id }}" {{ $period->is_active ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                            </div>
                        </label>
                    @empty
                        <p class="text-xs text-slate-400 font-semibold">Belum ada data periode. Buat di Setting Master.</p>
                    @endforelse
                </div>
            </div>

            <!-- Card 2: Gelombang Pendaftaran (Checkboxes / Toggles) -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-4">
                <div class="flex items-center gap-2 text-brand-emerald font-extrabold text-sm border-b border-slate-100 pb-2">
                    <i data-lucide="waves" class="w-4 h-4 text-brand-emerald"></i>
                    <h3>Gelombang Pendaftaran</h3>
                </div>
                <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Pilih gelombang masuk mana saja yang sedang dibuka untuk pendaftaran.</p>
                
                <div class="space-y-3 pt-2">
                    @forelse($waves as $wave)
                        <label class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition">
                            <span class="text-xs font-bold text-slate-700">{{ $wave->name }}</span>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="active_waves[]" value="{{ $wave->id }}" {{ $wave->is_active ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                            </div>
                        </label>
                    @empty
                        <p class="text-xs text-slate-400 font-semibold">Belum ada data gelombang. Buat di Setting Master.</p>
                    @endforelse
                </div>
            </div>

            <!-- Card 3: Kategori Jenis Pendaftaran (Checkboxes / Toggles) -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-4">
                <div class="flex items-center gap-2 text-brand-emerald font-extrabold text-sm border-b border-slate-100 pb-2">
                    <i data-lucide="tag" class="w-4 h-4 text-brand-emerald"></i>
                    <h3>Jenis Pendaftaran</h3>
                </div>
                <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Pilih kategori pendaftaran (Siswa Baru, Pindahan) yang aktif di formulir pendaftar.</p>
                
                <div class="space-y-3 pt-2">
                    @forelse($types as $type)
                        <label class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition">
                            <span class="text-xs font-bold text-slate-700">{{ $type->name }}</span>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="active_types[]" value="{{ $type->id }}" {{ $type->is_active ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                            </div>
                        </label>
                    @empty
                        <p class="text-xs text-slate-400 font-semibold">Belum ada data kategori. Buat di Setting Master.</p>
                    @endforelse
                </div>
            </div>

            <!-- Card 4: Biaya Tambahan Pendaftaran (Checkboxes / Toggles) -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-4">
                <div class="flex items-center gap-2 text-brand-emerald font-extrabold text-sm border-b border-slate-100 pb-2">
                    <i data-lucide="coins" class="w-4 h-4 text-brand-emerald"></i>
                    <h3>Biaya Tambahan Aktif</h3>
                </div>
                <p class="text-[10px] text-slate-400 leading-relaxed font-medium">Aktifkan besaran nominal biaya formulir tagihan Winpay pendaftaran online.</p>
                
                <div class="space-y-3 pt-2">
                    @forelse($fees as $fee)
                        <label class="flex items-center justify-between p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition">
                            <div>
                                <span class="text-xs font-bold text-slate-700 block">{{ $fee->name }}</span>
                                <span class="text-[10px] text-brand-emerald font-bold">Rp {{ number_format($fee->amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="active_fees[]" value="{{ $fee->id }}" {{ $fee->is_active ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 rounded-full transition-all peer-checked-emerald after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                            </div>
                        </label>
                    @empty
                        <p class="text-xs text-slate-400 font-semibold">Belum ada data biaya admin. Buat di Setting Biaya.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Submit Footer -->
        <div class="bg-white rounded-2xl border border-slate-100 p-4 flex justify-between items-center shadow-sm">
            <span class="text-xs text-slate-400 font-semibold">Tandai status di atas untuk mengaktifkan opsi pada form pendaftar.</span>
            <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl text-xs font-bold transition shadow-md flex items-center gap-1.5">
                <i data-lucide="save" class="w-4 h-4"></i>
                Simpan Aktifasi Pendaftaran
            </button>
        </div>

    </form>
</div>
@endsection
