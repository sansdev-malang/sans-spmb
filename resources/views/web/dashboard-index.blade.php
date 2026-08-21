@extends('layouts.portal')

@section('title', 'Pilih Pendaftaran Siswa - Portal SPMB')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 sm:px-6 lg:px-8 space-y-6">

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

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800">Daftar Calon Siswa</h1>
            <p class="text-sm text-slate-500 mt-1">Pilih calon siswa yang ingin Anda kelola, atau daftarkan calon siswa baru.</p>
        </div>
        <button onclick="openRegistrationModal()" class="bg-brand-emerald hover-emerald text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-md transition flex items-center gap-2">
            <i data-lucide="plus-circle" class="w-5 h-5"></i> Daftarkan Anak Baru
        </button>
    </div>

    @if($registrations->isEmpty())
        <div class="bg-white rounded-3xl p-12 shadow-sm border border-slate-100 flex flex-col items-center justify-center text-center">
            <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mb-4">
                <i data-lucide="users" class="w-12 h-12"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800">Belum Ada Pendaftaran</h3>
            <p class="text-slate-500 mt-2 max-w-sm">Anda belum mendaftarkan anak Anda ke sistem SPMB SANS. Silakan klik tombol di atas untuk memulai.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($registrations as $reg)
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition relative group overflow-hidden">
                    <!-- Status Badge -->
                    <div class="absolute top-4 right-4">
                        @if($reg->registration_status === 'verified')
                            <span class="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-1 rounded-lg uppercase tracking-wider">Terverifikasi</span>
                        @elseif($reg->registration_status === 'submitted')
                            <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-1 rounded-lg uppercase tracking-wider">Menunggu Verifikasi</span>
                        @else
                            <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-1 rounded-lg uppercase tracking-wider">Draft Form</span>
                        @endif
                    </div>
                    
                    <div class="flex items-center gap-4 mb-5">
                        <div class="h-12 w-12 bg-emerald-50 text-brand-emerald rounded-2xl flex items-center justify-center text-xl font-black">
                            {{ substr($reg->candidate_name ?? 'A', 0, 1) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 truncate pr-16">{{ $reg->candidate_name ?? 'Anak (Draft)' }}</h3>
                            <p class="text-xs text-slate-500 font-semibold">{{ $reg->unit->name ?? '-' }} • {{ $reg->grade->name ?? '-' }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 mb-6">
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 text-center">
                            <span class="block text-[10px] text-slate-400 font-bold uppercase mb-1">Status Bayar</span>
                            @if($reg->payment_status === 'paid')
                                <span class="text-xs font-bold text-green-600">LUNAS</span>
                            @elseif($reg->payment_status === 'pending')
                                <span class="text-xs font-bold text-amber-600">PENDING</span>
                            @else
                                <span class="text-xs font-bold text-slate-500">BELUM LUNAS</span>
                            @endif
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 text-center">
                            <span class="block text-[10px] text-slate-400 font-bold uppercase mb-1">Dibuat Pada</span>
                            <span class="text-xs font-bold text-slate-600">{{ $reg->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                    
                    <a href="{{ route('dashboard.detail', $reg->id) }}" class="block w-full py-3 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold text-center rounded-xl transition">
                        Kelola Pendaftaran
                    </a>
                </div>
            @endforeach
        </div>
    @endif

</div>

<!-- Modal Pendaftaran Baru -->
<div id="newRegistrationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300">
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-xl transform scale-95 transition-transform duration-300" id="registrationModalBody">
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
                        <select id="unitSelect" name="spmb_unit_id" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm font-semibold">
                            <option value="">Pilih Unit...</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
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
