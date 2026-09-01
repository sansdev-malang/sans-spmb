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
            <div class="text-center max-w-xl mx-auto space-y-1">
                <h2 class="text-2xl font-extrabold text-slate-850 dark:text-white tracking-tight">Pendaftaran Ananda Anda</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Kelola tahapan pendaftaran atau selesaikan administrasi siswa di bawah ini.</p>
            </div>

            <div class="flex flex-wrap justify-center gap-6 max-w-7xl mx-auto pt-2">
                @foreach($registrations as $reg)
                    @php
                        $isPaid = $reg->payments()->where('payment_type', 'registration_fee')->where('status', 'success')->exists();
                        $status = $reg->registration_status;
                        $regNum = $reg->registration_number ?: ('REG-' . str_pad($reg->id, 4, '0', STR_PAD_LEFT));
                    @endphp
                    <div class="w-full md:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)] max-w-md bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-150/80 dark:border-slate-800 hover:shadow-lg transition-all duration-300 relative group flex flex-col justify-between text-left">
                        
                        <div>
                            <!-- Header Bar: ID & Status Badge -->
                            <div class="flex items-center justify-between gap-2 pb-3 mb-3 border-b border-slate-100 dark:border-slate-800">
                                <span class="text-[10px] font-mono font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-lg flex items-center gap-1.5 border border-slate-200/60 dark:border-slate-700">
                                    <i data-lucide="tag" class="w-3 h-3 text-emerald-600"></i> {{ $reg->id_label }}
                                </span>
                                
                                @if($status === 'completed')
                                    <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Resmi Terdaftar
                                    </span>
                                @elseif($status === 'agreement_signed')
                                    <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-400 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pelunasan Administrasi
                                    </span>
                                @elseif($status === 'taaruf_completed')
                                    <span class="inline-flex items-center gap-1 bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-400 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> Ta'aruf Selesai
                                    </span>
                                @elseif($status === 'verified')
                                    <span class="inline-flex items-center gap-1 bg-teal-100 text-teal-800 dark:bg-teal-950 dark:text-teal-400 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span> Berkas Terverifikasi
                                    </span>
                                @elseif($status === 'submitted')
                                    <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-400 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span> Verifikasi Berkas
                                    </span>
                                @elseif($status === 'failed')
                                    <span class="inline-flex items-center gap-1 bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-400 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span> Perlu Perbaikan
                                    </span>
                                @else
                                    @if($isPaid)
                                        <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider border border-amber-200 dark:border-amber-900/40">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Draf Formulir
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 text-[10px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider border border-rose-200 dark:border-rose-900/40">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Belum Bayar
                                        </span>
                                    @endif
                                @endif
                            </div>
                            
                            <!-- Centered Candidate Profile Header -->
                            <div class="flex flex-col items-center text-center mb-5">
                                <div class="h-16 w-16 bg-emerald-600 text-white dark:bg-emerald-500 dark:text-white rounded-2xl flex items-center justify-center text-2xl font-black shadow-lg shadow-emerald-600/20 mb-2.5 ring-4 ring-emerald-50 dark:ring-emerald-950/40 flex-shrink-0">
                                    {{ strtoupper(substr(trim($reg->candidate_name ?? 'A'), 0, 1)) }}
                                </div>
                                <h3 class="font-extrabold text-slate-850 dark:text-white text-base truncate max-w-full leading-tight">
                                    {{ $reg->candidate_name ?? 'Anak (Draft)' }}
                                </h3>
                                <p class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-1">
                                    {{ $reg->unit->name ?? '-' }}
                                </p>
                            </div>
                            
                            <!-- Detailed Information Chips / Table Grid -->
                            <div class="bg-slate-50 dark:bg-slate-950 rounded-2xl p-3.5 border border-slate-100 dark:border-slate-800 text-[11px] space-y-2 mb-4">
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                    <span class="flex items-center gap-1.5"><i data-lucide="layers" class="w-3.5 h-3.5 text-slate-400"></i> Jenjang / Tingkat</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $reg->grade->name ?? ($reg->admission_level ?: '-') }}</span>
                                </div>
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                    <span class="flex items-center gap-1.5"><i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i> Tahun Pelajaran</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $reg->period->year ?? '2026-2027' }}</span>
                                </div>
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                    <span class="flex items-center gap-1.5"><i data-lucide="compass" class="w-3.5 h-3.5 text-slate-400"></i> Jalur & Gelombang</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $reg->type->name ?? 'Reguler' }} • {{ $reg->wave->name ?? 'Gel. 1' }}</span>
                                </div>
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                                    <span class="flex items-center gap-1.5"><i data-lucide="book-open" class="w-3.5 h-3.5 text-slate-400"></i> Program Kelas</span>
                                    <span class="font-bold text-brand-emerald dark:text-emerald-400">{{ $reg->classProgram->name ?? ($reg->getFieldValue('class_program') ?: 'Reguler') }}</span>
                                </div>
                                @if($reg->extraServices && $reg->extraServices->count() > 0)
                                    <div class="flex justify-between items-center text-slate-600 dark:text-slate-400 border-t border-slate-200/60 dark:border-slate-800 pt-1.5 mt-1.5">
                                        <span class="flex items-center gap-1.5"><i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i> Layanan Non-Formal</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200 truncate max-w-[140px]">{{ $reg->extraServices->pluck('name')->implode(', ') }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Stage Next Action Banner / Hint -->
                            <div class="mb-4">
                                @if(!$isPaid)
                                    <div class="p-2.5 bg-rose-50 dark:bg-rose-950/30 rounded-xl border border-rose-200 dark:border-rose-900/40 text-[10px] text-rose-700 dark:text-rose-400 font-bold flex items-center gap-2">
                                        <i data-lucide="credit-card" class="w-4 h-4 flex-shrink-0"></i>
                                        <span>Biaya pendaftaran formulir belum diselesaikan.</span>
                                    </div>
                                @elseif($status === 'draft')
                                    <div class="p-2.5 bg-amber-50 dark:bg-amber-950/30 rounded-xl border border-amber-200 dark:border-amber-900/40 text-[10px] text-amber-700 dark:text-amber-400 font-bold flex items-center gap-2">
                                        <i data-lucide="edit-3" class="w-4 h-4 flex-shrink-0"></i>
                                        <span>Formulir & berkas belum dikirim ke panitia.</span>
                                    </div>
                                @elseif($status === 'submitted')
                                    <div class="p-2.5 bg-blue-50 dark:bg-blue-950/30 rounded-xl border border-blue-200 dark:border-blue-900/40 text-[10px] text-blue-700 dark:text-blue-400 font-bold flex items-center gap-2">
                                        <i data-lucide="clock" class="w-4 h-4 flex-shrink-0"></i>
                                        <span>Berkas sedang dalam antrean verifikasi panitia.</span>
                                    </div>
                                @elseif($status === 'verified')
                                    <div class="p-2.5 bg-teal-50 dark:bg-teal-950/30 rounded-xl border border-teal-200 dark:border-teal-900/40 text-[10px] text-teal-700 dark:text-teal-400 font-bold flex items-center gap-2">
                                        <i data-lucide="calendar-check" class="w-4 h-4 flex-shrink-0"></i>
                                        <span>Berkas lolos verifikasi! Bersiap untuk Ta'aruf.</span>
                                    </div>
                                @elseif($status === 'taaruf_completed')
                                    <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/30 rounded-xl border border-indigo-200 dark:border-indigo-900/40 text-[10px] text-indigo-700 dark:text-indigo-400 font-bold flex items-center gap-2">
                                        <i data-lucide="file-check" class="w-4 h-4 flex-shrink-0"></i>
                                        <span>Ta'aruf selesai. Silakan isi surat pernyataan.</span>
                                    </div>
                                @elseif($status === 'agreement_signed')
                                    <div class="p-2.5 bg-amber-50 dark:bg-amber-950/30 rounded-xl border border-amber-200 dark:border-amber-900/40 text-[10px] text-amber-700 dark:text-amber-400 font-bold flex items-center gap-2">
                                        <i data-lucide="wallet" class="w-4 h-4 flex-shrink-0"></i>
                                        <span>Dinyatakan Diterima! Lakukan daftar ulang.</span>
                                    </div>
                                @elseif($status === 'completed')
                                    <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/30 rounded-xl border border-emerald-200 dark:border-emerald-900/40 text-[10px] text-emerald-700 dark:text-emerald-400 font-bold flex items-center gap-2">
                                        <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0"></i>
                                        <span>Penerimaan ananda telah resmi selesai & lunas.</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Main Action Button -->
                        <div class="pt-2">
                            <a href="{{ route('dashboard.detail', $reg->id) }}" class="w-full py-3.5 px-4 bg-slate-900 hover:bg-emerald-600 dark:bg-slate-800 dark:hover:bg-emerald-600 text-white text-xs font-black rounded-2xl transition-all duration-200 flex items-center justify-center gap-2 shadow-sm group-hover:shadow-md">
                                <span>Buka Portal Pendaftaran</span>
                                <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                            </a>
                        </div>

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
