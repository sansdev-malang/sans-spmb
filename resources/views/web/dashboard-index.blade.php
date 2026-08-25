@extends('layouts.portal')

@section('title', 'Pilih Pendaftaran Siswa - Portal SPMB')

@section('content')
<div class="max-w-7xl mx-auto px-4 pt-4 pb-12 sm:px-6 lg:px-8 space-y-6">

    @if (session('success'))
        <div class="bg-green-50 text-green-700 p-4 rounded-xl text-sm border border-green-200 font-semibold shadow-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 text-red-700 p-4 rounded-xl text-sm border border-red-200 font-semibold shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- WELCOME BANNER & ONBOARDING CARD GRID (ALWAYS SHOWN) -->
    <div class="max-w-4xl mx-auto pt-2 pb-6 space-y-6 text-center">
        <div class="space-y-3">
            <div class="inline-flex items-center justify-center h-16 w-16 bg-emerald-50 dark:bg-emerald-950/30 rounded-2xl text-brand-emerald dark:text-emerald-450 mb-2">
                <i data-lucide="sparkles" class="w-8 h-8"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-850 dark:text-white tracking-tight">Selamat Datang di Portal Penerimaan Siswa Baru</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 max-w-xl mx-auto leading-relaxed">
                Langkah pertama pendidikan terbaik ananda di {{ \App\Models\Setting::get('school_name', 'Sekolah Anak Saleh') }} dimulai dari sini. Silakan daftarkan anak Anda untuk memulai proses seleksi masuk penerimaan siswa baru.
            </p>
        </div>

        <!-- Card Options for Each School Unit -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
            @foreach($units as $unit)
                @php 
                    $uCode = strtolower($unit->code);
                    $firstGrade = \App\Models\SpmbGrade::where('spmb_unit_id', $unit->id)->where('is_active', true)->orderBy('id')->first();
                    $firstGradeId = $firstGrade?->id ?? '';
                @endphp
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-150/80 dark:border-slate-800 shadow-sm flex flex-col justify-between items-center text-center space-y-4 hover:shadow-md transition">
                    <div class="h-12 w-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 text-brand-emerald dark:text-emerald-450 flex items-center justify-center font-bold text-base shadow-sm">
                        {{ strtoupper($unit->code) }}
                    </div>
                    <div class="space-y-1">
                        <h3 class="font-extrabold text-slate-800 dark:text-white text-sm">{{ $unit->name }}</h3>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 line-clamp-2">
                            {{ \App\Models\Setting::get('unit_' . $uCode . '_desc', 'Pilihan program pendidikan terbaik.') }}
                        </p>
                    </div>
                    
                    <button onclick="startRegistrationWithUnit('{{ $unit->id }}', '{{ $firstGradeId }}')" class="w-full py-2.5 bg-custom-primary hover:opacity-90 text-white rounded-xl text-xs font-bold transition shadow-sm dark:bg-emerald-600">
                        Daftarkan Sekarang
                    </button>
                </div>
            @endforeach
        </div>

        <!-- Supporting information banner -->
        <div class="bg-slate-100/50 dark:bg-slate-950/30 rounded-2xl p-4 border border-slate-150/60 dark:border-slate-850 max-w-xl mx-auto flex items-center gap-3 text-left">
            <i data-lucide="help-circle" class="w-5 h-5 text-slate-400 dark:text-slate-600 flex-shrink-0"></i>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-normal">
                Butuh bantuan atau informasi mengenai alur seleksi & biaya masuk? Anda dapat membaca detail brosur masing-masing unit dengan mengeklik menu unit di navbar atas sebelum mendaftar.
            </p>
        </div>
    </div>

    <!-- LIST OF CURRENT ACTIVE REGISTRATIONS (SHOWN UNDERNEATH IF NOT EMPTY) -->
    @if(!$registrations->isEmpty())
        <div class="pt-8 border-t border-slate-200/60 dark:border-slate-800 space-y-6">
            <div class="text-left max-w-4xl mx-auto">
                <h2 class="text-xl font-extrabold text-slate-850 dark:text-white">Pendaftaran Ananda Anda</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola tahapan pendaftaran atau selesaikan administrasi siswa di bawah ini.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-7xl mx-auto">
                @foreach($registrations as $reg)
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-100 dark:border-slate-800 hover:shadow-md transition relative group overflow-hidden text-left">
                        <!-- Status Badge -->
                        <div class="absolute top-4 right-4">
                            @if($reg->registration_status === 'completed')
                                <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded-lg uppercase tracking-wider">Lunas & Resmi</span>
                            @elseif($reg->registration_status === 'agreement_signed')
                                <span class="bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-1 rounded-lg uppercase tracking-wider">Menunggu Pelunasan</span>
                            @elseif($reg->registration_status === 'taaruf_completed')
                                <span class="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-1 rounded-lg uppercase tracking-wider">Ta'aruf Selesai</span>
                            @elseif($reg->registration_status === 'verified')
                                <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded-lg uppercase tracking-wider">Terverifikasi</span>
                            @elseif($reg->registration_status === 'submitted')
                                <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-1 rounded-lg uppercase tracking-wider">Menunggu Verifikasi</span>
                            @else
                                <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-1 rounded-lg uppercase tracking-wider">Draft Formulir</span>
                            @endif
                        </div>
                        
                        <div class="flex items-center gap-4 mb-5">
                            <div class="h-12 w-12 bg-emerald-50 text-brand-emerald rounded-2xl flex items-center justify-center text-xl font-black dark:bg-emerald-950/30">
                                {{ substr($reg->candidate_name ?? 'A', 0, 1) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 dark:text-white truncate pr-16">{{ $reg->candidate_name ?? 'Anak (Draft)' }}</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold">{{ $reg->unit->name ?? '-' }}@if(!empty($reg->grade->name)) • {{ $reg->grade->name }}@endif</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 mb-6">
                            <div class="bg-slate-50 dark:bg-slate-950 p-3 rounded-xl border border-slate-100 dark:border-slate-800 text-center">
                                <span class="block text-[10px] text-slate-400 font-bold uppercase mb-1">Status Bayar</span>
                                @if($reg->payment_status === 'paid')
                                    <span class="text-xs font-bold text-green-600">LUNAS</span>
                                @elseif($reg->payment_status === 'pending')
                                    <span class="text-xs font-bold text-amber-600">PENDING</span>
                                @else
                                    <span class="text-xs font-bold text-slate-500">BELUM LUNAS</span>
                                @endif
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-950 p-3 rounded-xl border border-slate-100 dark:border-slate-800 text-center">
                                <span class="block text-[10px] text-slate-400 font-bold uppercase mb-1">Dibuat Pada</span>
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-350">{{ $reg->created_at->format('d M Y') }}</span>
                            </div>
                        </div>
                        
                        <a href="{{ route('dashboard.detail', $reg->id) }}" class="block w-full py-3 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold text-center rounded-xl transition dark:bg-emerald-600 dark:hover:bg-emerald-500">
                            Kelola Pendaftaran
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

<!-- Modal Pendaftaran Baru -->
<div id="newRegistrationModal" onclick="closeRegistrationModal()" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 opacity-0 pointer-events-none transition-opacity duration-150">
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-xl transform scale-95 transition-transform duration-150" id="registrationModalBody" onclick="event.stopPropagation()">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h2 class="text-xl font-extrabold text-slate-800">Daftarkan Anak Baru</h2>
            <button onclick="closeRegistrationModal()" class="p-2 rounded-xl hover:bg-slate-50 text-slate-400 hover:text-slate-600 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('dashboard.registration.create') }}">
            @csrf
            <div class="p-6 space-y-5">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nama Calon Siswa (Sesuai Akte)</label>
                    <input type="text" name="candidate_name" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold" placeholder="Masukkan nama lengkap anak Anda">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Unit Sekolah</label>
                        <input type="hidden" name="spmb_unit_id" id="hiddenUnitInput">
                        <input type="text" id="unitNameDisplay" readonly class="w-full bg-slate-100 border border-slate-200 rounded-xl px-4 py-3 text-slate-500 text-sm font-semibold select-none cursor-not-allowed">
                        <select id="unitSelect" style="display: none;">
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" data-name="{{ $unit->name }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Tingkatan / Kelas</label>
                        <select id="gradeSelect" name="spmb_grade_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold disabled:opacity-50" disabled>
                            <option value="">Pilih Tingkatan...</option>
                            <!-- Options akan diisi via javascript -->
                        </select>
                    </div>
                </div>

                @php
                    $activePeriod = \App\Models\SpmbPeriod::where('is_active', true)->first();
                @endphp
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Jalur Pendaftaran</label>
                        <select name="spmb_type_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold">
                            <option value="">Pilih Jalur...</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Gelombang</label>
                        <select name="spmb_wave_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold">
                            <option value="">Pilih Gelombang...</option>
                            @foreach($waves as $wave)
                                <option value="{{ $wave->id }}">{{ $wave->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Tahun Pelajaran</label>
                        <input type="text" readonly value="{{ $activePeriod?->year ?? '-' }}" class="w-full bg-slate-100 border border-slate-200 rounded-xl px-4 py-3 text-slate-500 text-sm font-semibold select-none cursor-not-allowed">
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-slate-100 bg-slate-50 rounded-b-3xl flex justify-end gap-3">
                <button type="button" onclick="closeRegistrationModal()" class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-200 transition">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-brand-emerald hover-emerald transition shadow-sm">Buat Pendaftaran</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Data Grades untuk dependent dropdown
    const gradesData = @json($grades);

    function openRegistrationModal() {
        const modal = document.getElementById('newRegistrationModal');
        const modalBody = document.getElementById('registrationModalBody');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modalBody.classList.remove('scale-95');
        modalBody.classList.add('scale-100');
    }

    function closeRegistrationModal() {
        const modal = document.getElementById('newRegistrationModal');
        const modalBody = document.getElementById('registrationModalBody');
        modal.classList.add('opacity-0', 'pointer-events-none');
        modalBody.classList.remove('scale-100');
        modalBody.classList.add('scale-95');
    }

    function startRegistrationWithUnit(unitId, gradeId) {
        openRegistrationModal();
        
        // Update hidden input and display text
        const hiddenInput = document.getElementById('hiddenUnitInput');
        hiddenInput.value = unitId;
        
        const unitSelect = document.getElementById('unitSelect');
        unitSelect.value = unitId;
        
        const selectedOption = unitSelect.options[unitSelect.selectedIndex];
        document.getElementById('unitNameDisplay').value = selectedOption ? selectedOption.getAttribute('data-name') : '';
        
        // Trigger select change to update grades dropdown
        unitSelect.dispatchEvent(new Event('change'));
        
        setTimeout(() => {
            const gradeSelect = document.getElementById('gradeSelect');
            if (gradeSelect && gradeId) {
                gradeSelect.value = gradeId;
            }
        }, 100);
    }
    
    document.getElementById('unitSelect').addEventListener('change', function() {
        const unitId = this.value;
        const gradeSelect = document.getElementById('gradeSelect');
        
        // Reset grade options
        gradeSelect.innerHTML = '<option value="">Pilih Tingkatan...</option>';
        gradeSelect.disabled = true;
        
        if (unitId) {
            // Filter grades based on unit
            const filteredGrades = gradesData.filter(g => g.spmb_unit_id == unitId);
            
            if (filteredGrades.length > 0) {
                filteredGrades.forEach(g => {
                    const option = document.createElement('option');
                    option.value = g.id;
                    option.textContent = g.name;
                    gradeSelect.appendChild(option);
                });
                gradeSelect.disabled = false;
            }
        }
    });
</script>
@endpush
