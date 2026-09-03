@extends('layouts.admin')

@section('title', 'Manajemen User - Admin Panel')
@section('page_title', 'Data User')

@section('content')
<div id="users-page-container" hx-boost="true" hx-target="#users-page-container" hx-select="#users-page-container" class="w-full space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                <i data-lucide="users-round" class="w-5 h-5 text-brand-emerald"></i>
                Manajemen Pengguna (User Data)
            </h1>
            <p class="text-xs text-slate-500 mt-1">Kelola data login pengguna sistem SPMB Sekolah Anak Saleh (Admin dan Orang Tua/Calon Siswa).</p>
        </div>
        <button onclick="openAddUserModal()" class="bg-brand-emerald hover-emerald text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah User Baru
        </button>
    </div>

    <!-- Tab Navigation Pills -->
    <div class="flex flex-wrap gap-2 bg-white p-2 rounded-2xl border border-slate-100 shadow-sm">
        <button onclick="switchUserTab('admin_role')" id="userTabBtn-admin_role" class="user-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow">
            <i data-lucide="shield-check" class="w-4 h-4"></i> Panitia / Admin
            <span class="bg-white/20 text-white text-[10px] px-2 py-0.5 rounded-full font-bold ml-0.5">{{ $adminsCount }}</span>
        </button>
        <button onclick="switchUserTab('candidate_role')" id="userTabBtn-candidate_role" class="user-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
            <i data-lucide="user-check" class="w-4 h-4 text-emerald-600"></i> Orang Tua / Calon Siswa (Aktif)
            <span class="bg-slate-100 text-slate-700 text-[10px] px-2 py-0.5 rounded-full font-bold border border-slate-200 ml-0.5">{{ $candidatesCount }}</span>
        </button>
        <button onclick="switchUserTab('unregistered_role')" id="userTabBtn-unregistered_role" class="user-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
            <i data-lucide="user-x" class="w-4 h-4 text-amber-500"></i> Belum Memilih Unit / Belum Bayar
            <span class="bg-amber-100 text-amber-800 text-[10px] px-2 py-0.5 rounded-full font-bold border border-amber-200 ml-0.5">{{ $unregisteredCount }}</span>
        </button>
    </div>

    <!-- Tab Contents Container -->
    <div id="users-card" class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden" hx-boost="true" hx-target="#users-card" hx-select="#users-card">
        
        <!-- Search & Filter Form -->
        <form action="{{ route('admin.users') }}" method="GET" hx-boost="false" class="p-6 bg-slate-50/50 border-b border-slate-100 space-y-4">
            <input type="hidden" name="tab" id="active-tab-input" value="{{ request('tab', 'admin_role') }}">
            @if(request('admins_page'))
                <input type="hidden" name="admins_page" value="{{ request('admins_page') }}">
            @endif
            @if(request('candidates_page'))
                <input type="hidden" name="candidates_page" value="{{ request('candidates_page') }}">
            @endif
            @if(request('unregistered_page'))
                <input type="hidden" name="unregistered_page" value="{{ request('unregistered_page') }}">
            @endif
            
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    <!-- Search Input Container -->
                    <div class="relative w-full md:w-80 flex items-center">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." 
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
                    
                    @if(auth()->user()->isSuperAdmin())
                        <!-- Filter Unit -->
                        <select name="unit_id" onchange="this.form.submit()" class="py-2.5 px-3 text-xs rounded-xl border border-slate-200 bg-white font-bold text-slate-650 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                            <option value="">Semua Unit/Jenjang</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>{{ strtoupper($unit->name) }}</option>
                            @endforeach
                        </select>
                    @endif

                    <!-- Per Page Select -->
                    <select name="per_page" onchange="this.form.submit()" class="py-2.5 px-3 text-xs rounded-xl border border-slate-200 bg-white font-bold text-slate-650 focus:outline-none focus:ring-2 focus:ring-brand-emerald">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 Baris</option>
                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25 Baris</option>
                        <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50 Baris</option>
                        <option value="100" {{ request('per_page', 100) == 100 ? 'selected' : '' }}>100 Baris</option>
                    </select>
                </div>
            </div>
        </form>
        
        <!-- Tab 1: Admin Role -->
        <div id="userTabContent-admin_role" class="user-tab-content p-8 space-y-6">
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6 text-center w-12">No.</th>
                            <th class="py-4 px-6">Nama Pengguna</th>
                            <th class="py-4 px-6">Alamat Email</th>
                            <th class="py-4 px-6">Tanggal Dibuat</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($admins as $admin)
                            <tr class="hover:bg-slate-50/30 transition">
                                <td class="py-4 px-6 text-center text-slate-500 font-bold text-xs">
                                    {{ ($admins->currentPage() - 1) * $admins->perPage() + $loop->iteration }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-extrabold text-slate-800 flex items-center gap-1.5 flex-wrap">
                                        {{ $admin->name }}
                                        @if($admin->id === Auth::id())
                                            <span class="bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full text-[9px] font-bold border border-emerald-200">Anda</span>
                                        @endif
                                        @if($admin->role === 'super_admin')
                                            <span class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full text-[9px] font-bold border border-indigo-200">Developer</span>
                                        @else
                                            @if($admin->spmb_unit_id)
                                                <span class="bg-amber-50 text-amber-600 px-2 py-0.5 rounded-full text-[9px] font-bold border border-amber-200">{{ $admin->spmbUnit->name }}</span>
                                            @else
                                                <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full text-[9px] font-bold border border-slate-200">Global Admin</span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 px-6 font-medium text-slate-600">{{ $admin->email }}</td>
                                <td class="py-4 px-6 text-slate-500 text-xs">{{ $admin->created_at->format('d M Y, H:i') }}</td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <button onclick="openEditUserModal({{ json_encode($admin) }})" class="text-xs text-brand-emerald font-bold hover:underline">Edit</button>
                                    <button onclick="openResetPasswordModal({{ json_encode($admin) }})" class="text-xs text-amber-600 font-bold hover:underline">Reset Password</button>
                                    @if($admin->id !== Auth::id())
                                        <button onclick="deleteUser('{{ $admin->name }}', '{{ route('admin.users.destroy', $admin->id) }}')" class="text-xs text-red-600 font-bold hover:underline">Hapus</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 px-6 text-center text-slate-400">Belum ada user admin.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($admins->hasPages())
                <div class="pt-4">
                    {{ $admins->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        <!-- Tab 2: Candidate Role (Aktif / Sudah Bayar Formulir) -->
        <div id="userTabContent-candidate_role" class="user-tab-content p-8 space-y-6 hidden">
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6 text-center w-12">No.</th>
                            <th class="py-4 px-6">Nama Pengguna</th>
                            <th class="py-4 px-6">Alamat Email & Kontak</th>
                            <th class="py-4 px-6">Tanggal Dibuat</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($candidates as $cand)
                            <tr class="hover:bg-slate-50/30 transition">
                                <td class="py-4 px-6 text-center text-slate-500 font-bold text-xs">
                                    {{ ($candidates->currentPage() - 1) * $candidates->perPage() + $loop->iteration }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-extrabold text-slate-800">{{ $cand->name }}</div>
                                    @if($cand->registrations->isNotEmpty())
                                        <div class="text-[10px] text-slate-400 font-semibold mt-1.5 flex flex-wrap gap-1.5 items-center">
                                            <span class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full text-[9px] font-bold border border-emerald-200">
                                                {{ $cand->registrations->count() }} Pendaftaran Aktif
                                            </span>
                                            @foreach($cand->registrations as $reg)
                                                <span class="bg-emerald-50 text-brand-emerald px-2 py-0.5 rounded-full border border-emerald-100 text-[9px] font-extrabold uppercase">
                                                    {{ $reg->candidate_name }} ({{ $reg->unit->name ?? '-' }})
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-[10px] text-slate-400 mt-1 font-semibold">
                                            Belum ada pendaftaran anak
                                        </div>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-medium text-slate-600">{{ $cand->email }}</div>
                                    @php
                                        $candPhone = $cand->registrations->first()?->parent_phone;
                                    @endphp
                                    @if(!empty($candPhone))
                                        @php
                                            $cleanPhone = preg_replace('/[^0-9]/', '', $candPhone);
                                            if (str_starts_with($cleanPhone, '0')) $cleanPhone = '62' . substr($cleanPhone, 1);
                                        @endphp
                                        <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-emerald-600 font-bold hover:underline mt-0.5">
                                            <i data-lucide="message-circle" class="w-3 h-3"></i> {{ $candPhone }}
                                        </a>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-slate-500 text-xs">
                                    {{ $cand->created_at->format('d M Y, H:i') }}
                                    <span class="text-[10px] text-slate-400 block font-normal">{{ $cand->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <button onclick="openEditUserModal({{ json_encode($cand) }})" class="text-xs text-brand-emerald font-bold hover:underline">Edit</button>
                                    <button onclick="openResetPasswordModal({{ json_encode($cand) }})" class="text-xs text-amber-600 font-bold hover:underline">Reset Password</button>
                                    <button onclick="deleteUser('{{ $cand->name }}', '{{ route('admin.users.destroy', $cand->id) }}')" class="text-xs text-red-600 font-bold hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 px-6 text-center text-slate-400">Belum ada user calon siswa yang telah melunasi formulir.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($candidates->hasPages())
                <div class="pt-4">
                    {{ $candidates->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        <!-- Tab 3: Unregistered / Unpaid Role (Belum Memilih Unit / Belum Bayar Formulir) -->
        <div id="userTabContent-unregistered_role" class="user-tab-content p-8 space-y-6 hidden">
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6 text-center w-12">No.</th>
                            <th class="py-4 px-6">Nama Pengguna</th>
                            <th class="py-4 px-6">Alamat Email & No. HP</th>
                            <th class="py-4 px-6">Status Prospek / Kendala</th>
                            <th class="py-4 px-6">Tanggal Registrasi Akun</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($unregistered as $unreg)
                            <tr class="hover:bg-slate-50/30 transition">
                                <td class="py-4 px-6 text-center text-slate-500 font-bold text-xs">
                                    {{ ($unregistered->currentPage() - 1) * $unregistered->perPage() + $loop->iteration }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-extrabold text-slate-800">{{ $unreg->name }}</div>
                                    @if($unreg->registrations->isNotEmpty())
                                        <div class="text-[10px] text-slate-400 font-semibold mt-1 flex flex-wrap gap-1 items-center">
                                            @foreach($unreg->registrations as $reg)
                                                <span>{{ $reg->candidate_name ?? 'Draft' }} ({{ $reg->unit->name ?? '-' }})</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-medium text-slate-600">{{ $unreg->email }}</div>
                                    @php
                                        $leadPhone = $unreg->registrations->first()?->parent_phone;
                                    @endphp
                                    @if(!empty($leadPhone))
                                        @php
                                            $cleanLeadPhone = preg_replace('/[^0-9]/', '', $leadPhone);
                                            if (str_starts_with($cleanLeadPhone, '0')) $cleanLeadPhone = '62' . substr($cleanLeadPhone, 1);
                                            $waMsg = "Halo Bapak/Ibu " . $unreg->name . ", kami dari Panitia SPMB Sekolah Anak Saleh ingin menanyakan apakah ada kendala dalam proses pendaftaran ananda?";
                                        @endphp
                                        <a href="https://wa.me/{{ $cleanLeadPhone }}?text={{ urlencode($waMsg) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-emerald-600 font-bold hover:underline mt-0.5" title="Chat Follow-up WhatsApp">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> {{ $leadPhone }}
                                        </a>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">No. HP belum diisi</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if($unreg->registrations->isEmpty())
                                        <span class="inline-flex items-center gap-1 bg-slate-100 text-slate-650 px-2.5 py-1 rounded-full text-[10px] font-bold border border-slate-200">
                                            <i data-lucide="help-circle" class="w-3 h-3 text-slate-400"></i> Belum Memilih Unit
                                        </span>
                                    @else
                                        <div class="flex flex-col gap-1">
                                            @foreach($unreg->registrations as $reg)
                                                <span class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 px-2.5 py-1 rounded-full text-[10px] font-bold border border-rose-200 w-fit">
                                                    <i data-lucide="alert-circle" class="w-3 h-3 text-rose-500"></i> Belum Bayar Formulir ({{ $reg->unit->name ?? '-' }})
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-slate-500 text-xs">
                                    {{ $unreg->created_at->format('d M Y, H:i') }}
                                    <span class="text-[10px] text-slate-400 block font-normal">{{ $unreg->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <button onclick="openEditUserModal({{ json_encode($unreg) }})" class="text-xs text-brand-emerald font-bold hover:underline">Edit</button>
                                    <button onclick="openResetPasswordModal({{ json_encode($unreg) }})" class="text-xs text-amber-600 font-bold hover:underline">Reset Password</button>
                                    <button onclick="deleteUser('{{ $unreg->name }}', '{{ route('admin.users.destroy', $unreg->id) }}')" class="text-xs text-red-600 font-bold hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 px-6 text-center text-slate-400">Tidak ada akun pendaftar yang belum memilih unit atau tertunda pembayaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($unregistered->hasPages())
                <div class="pt-4">
                    {{ $unregistered->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

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

<!-- Modal 1: Add User Modal -->
<div id="addUserModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden">
        <div class="bg-brand-emerald text-white px-6 py-4 flex justify-between items-center">
            <h3 class="font-extrabold text-base flex items-center gap-1.5">
                <i data-lucide="user-plus" class="w-4 h-4"></i> Tambah Pengguna Baru
            </h3>
            <button onclick="closeAddUserModal()" class="text-white hover:text-brand-yellow font-bold text-lg">&times;</button>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST" hx-boost="false" class="p-6 space-y-4">
            @csrf
            @if($errors->any() && session('failed_modal') && session('failed_modal') === 'user_create')
                <div class="spmb-user-errors mx-6 mt-4 text-xs text-red-650 bg-red-50 p-3.5 rounded-xl border border-red-200 font-semibold space-y-1">
                    @foreach($errors->all() as $error)
                        <p>⚠️ {{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nama Lengkap*</label>
                <input type="text" name="name" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Alamat Email*</label>
                <input type="email" name="email" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm" placeholder="user@example.com">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Kata Sandi (Password)*</label>
                <input type="password" name="password" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm" placeholder="Min. 8 karakter">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Peran Pengguna (Role)*</label>
                <select name="role" id="add-role-select" onchange="toggleAddUnitSelect()" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-bold">
                    <option value="admin">Panitia / Admin</option>
                    <option value="candidate">Orang Tua / Calon Siswa</option>
                    <option value="super_admin">Developer / IT (Super Admin)</option>
                </select>
            </div>
            <div id="add-unit-select-wrapper" class="hidden">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Unit Tugas (Khusus Admin Unit)</label>
                <select name="spmb_unit_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-bold">
                    <option value="">Semua Unit (Global Admin)</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeAddUserModal()" class="border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition">Kembali</button>
                <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2 rounded-xl text-xs font-bold transition shadow-md">Simpan User</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Edit User Modal -->
<div id="editUserModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden">
        <div class="bg-brand-emerald text-white px-6 py-4 flex justify-between items-center">
            <h3 class="font-extrabold text-base flex items-center gap-1.5">
                <i data-lucide="user-cog" class="w-4 h-4"></i> Edit Informasi Pengguna
            </h3>
            <button onclick="closeEditUserModal()" class="text-white hover:text-brand-yellow font-bold text-lg">&times;</button>
        </div>
        <form id="editUserForm" method="POST" hx-boost="false" class="p-6 space-y-4">
            @csrf
            @if($errors->any() && session('failed_modal') && str_starts_with(session('failed_modal'), 'user_edit_'))
                <div class="spmb-user-errors mx-6 mt-4 text-xs text-red-655 bg-red-50 p-3.5 rounded-xl border border-red-200 font-semibold space-y-1">
                    @foreach($errors->all() as $error)
                        <p>⚠️ {{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nama Lengkap*</label>
                <input type="text" id="edit-name" name="name" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Alamat Email*</label>
                <input type="email" id="edit-email" name="email" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Peran Pengguna (Role)*</label>
                <select id="edit-role" name="role" onchange="toggleEditUnitSelect()" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-bold">
                    <option value="admin">Panitia / Admin</option>
                    <option value="candidate">Orang Tua / Calon Siswa</option>
                    <option value="super_admin">Developer / IT (Super Admin)</option>
                </select>
            </div>
            <div id="edit-unit-select-wrapper" class="hidden">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Unit Tugas (Khusus Admin Unit)</label>
                <select id="edit-spmb-unit-id" name="spmb_unit_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-xs font-bold">
                    <option value="">Semua Unit (Global Admin)</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeEditUserModal()" class="border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition">Kembali</button>
                <button type="submit" class="bg-brand-emerald hover-emerald text-white px-5 py-2 rounded-xl text-xs font-bold transition shadow-md">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Reset Password Modal -->
<div id="resetPasswordModal" class="fixed inset-0 z-50 overflow-y-auto hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden">
        <div class="bg-amber-600 text-white px-6 py-4 flex justify-between items-center">
            <h3 class="font-extrabold text-base flex items-center gap-1.5">
                <i data-lucide="key-round" class="w-4 h-4"></i> Reset Password Pengguna
            </h3>
            <button onclick="closeResetPasswordModal()" class="text-white hover:text-brand-yellow font-bold text-lg">&times;</button>
        </div>
        <form id="resetPasswordForm" method="POST" hx-boost="false" class="p-6 space-y-4">
            @csrf
            @if($errors->any() && session('failed_modal') && str_starts_with(session('failed_modal'), 'user_reset_'))
                <div class="spmb-user-errors mx-6 mt-4 text-xs text-red-655 bg-red-50 p-3.5 rounded-xl border border-red-200 font-semibold space-y-1">
                    @foreach($errors->all() as $error)
                        <p>⚠️ {{ $error }}</p>
                    @endforeach
                </div>
            @endif
            <div>
                <span class="text-xs text-slate-400 font-bold block uppercase mb-1">Akun User</span>
                <span id="reset-user-name" class="font-extrabold text-slate-800 text-sm"></span>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Kata Sandi (Password) Baru*</label>
                <input type="password" name="password" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm" placeholder="Min. 8 karakter">
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeResetPasswordModal()" class="border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition">Kembali</button>
                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2 rounded-xl text-xs font-bold transition shadow-md">Reset Password</button>
            </div>
        </form>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="deleteUserForm" method="POST" hx-boost="false" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    // Clear validation errors
    function clearUserErrors() {
        document.querySelectorAll('.spmb-user-errors').forEach(el => {
            el.classList.add('hidden');
        });
    }

    // Tab switching memory
    function switchUserTab(tabId) {
        document.querySelectorAll('.user-tab-content').forEach(el => el.classList.add('hidden'));
        let targetEl = document.getElementById('userTabContent-' + tabId);
        if (!targetEl) {
            tabId = 'admin_role';
            targetEl = document.getElementById('userTabContent-admin_role');
        }
        if (targetEl) {
            targetEl.classList.remove('hidden');
        }

        document.querySelectorAll('.user-tab-btn').forEach(btn => {
            btn.className = "user-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50";
        });
        
        const activeBtn = document.getElementById('userTabBtn-' + tabId);
        if (activeBtn) {
            activeBtn.className = "user-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow";
        }
        
        // Update input active-tab value
        const activeTabInput = document.getElementById('active-tab-input');
        if (activeTabInput) {
            activeTabInput.value = tabId;
        }
        
        // Update URL query parameter
        const currentParams = new URLSearchParams(window.location.search);
        currentParams.set('tab', tabId);
        const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?' + currentParams.toString();
        window.history.replaceState({ path: newUrl }, '', newUrl);

        localStorage.setItem('spmb_users_active_tab', tabId);
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Tab state is handled server-side / URL query parameter, fallback to local storage
        const urlParams = new URLSearchParams(window.location.search);
        const savedTab = urlParams.get('tab') || localStorage.getItem('spmb_users_active_tab') || 'admin_role';
        switchUserTab(savedTab);
    });

    // Add User Modal
    function openAddUserModal() {
        clearUserErrors();
        document.getElementById('addUserModal').classList.remove('hidden');
        toggleAddUnitSelect();
    }
    function closeAddUserModal() {
        clearUserErrors();
        document.getElementById('addUserModal').classList.add('hidden');
    }

    function toggleAddUnitSelect() {
        const role = document.getElementById('add-role-select').value;
        const wrapper = document.getElementById('add-unit-select-wrapper');
        if (role === 'admin') {
            wrapper.classList.remove('hidden');
        } else {
            wrapper.classList.add('hidden');
        }
    }

    function toggleEditUnitSelect() {
        const role = document.getElementById('edit-role').value;
        const wrapper = document.getElementById('edit-unit-select-wrapper');
        if (role === 'admin') {
            wrapper.classList.remove('hidden');
        } else {
            wrapper.classList.add('hidden');
        }
    }

    // Edit User Modal
    function openEditUserModal(user) {
        clearUserErrors();
        document.getElementById('edit-name').value = user.name;
        document.getElementById('edit-email').value = user.email;
        document.getElementById('edit-role').value = user.role;
        document.getElementById('edit-spmb-unit-id').value = user.spmb_unit_id || '';
        toggleEditUnitSelect();
        document.getElementById('editUserForm').setAttribute('action', '/admin/users/' + user.id);
        document.getElementById('editUserModal').classList.remove('hidden');
    }
    function closeEditUserModal() {
        clearUserErrors();
        document.getElementById('editUserModal').classList.add('hidden');
    }

    // Reset Password Modal
    function openResetPasswordModal(user) {
        clearUserErrors();
        document.getElementById('reset-user-name').innerText = user.name + ' (' + user.email + ')';
        document.getElementById('resetPasswordForm').setAttribute('action', '/admin/users/' + user.id + '/reset-password');
        document.getElementById('resetPasswordModal').classList.remove('hidden');
    }
    function closeResetPasswordModal() {
        clearUserErrors();
        document.getElementById('resetPasswordModal').classList.add('hidden');
    }

    // Delete User
    function deleteUser(userName, actionUrl) {
        confirmDelete(actionUrl, `Apakah Anda yakin ingin menghapus user "${userName}"? Tindakan ini tidak dapat dibatalkan.`);
    }

    // Click outside handlers to close modals
    document.getElementById('addUserModal').addEventListener('click', function(e) {
        if (e.target === this) closeAddUserModal();
    });
    document.getElementById('editUserModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditUserModal();
    });
    document.getElementById('resetPasswordModal').addEventListener('click', function(e) {
        if (e.target === this) closeResetPasswordModal();
    });

    // Escape key listener to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const addUserModal = document.getElementById('addUserModal');
            if (addUserModal && !addUserModal.classList.contains('hidden')) closeAddUserModal();
            
            const editUserModal = document.getElementById('editUserModal');
            if (editUserModal && !editUserModal.classList.contains('hidden')) closeEditUserModal();
            
            const resetPasswordModal = document.getElementById('resetPasswordModal');
            if (resetPasswordModal && !resetPasswordModal.classList.contains('hidden')) closeResetPasswordModal();
        }
    });

    // Auto-reopen modal if validation failed on redirect
    @if(session('failed_modal'))
        document.addEventListener("DOMContentLoaded", function() {
            let failed = "{{ session('failed_modal') }}";
            if (failed.startsWith('user_create')) {
                switchUserTab('admin_role');
                openAddUserModal();
                // Repopulate form inputs with old values
                document.getElementsByName('name')[0].value = "{{ old('name') }}";
                document.getElementsByName('email')[0].value = "{{ old('email') }}";
                document.getElementById('add-role-select').value = "{{ old('role') }}";
                document.getElementsByName('spmb_unit_id')[0].value = "{{ old('spmb_unit_id') }}";
                toggleAddUnitSelect();
            } else if (failed.startsWith('user_edit_')) {
                let id = failed.replace('user_edit_', '');
                let role = "{{ old('role') }}";
                if (role === 'candidate') {
                    switchUserTab('candidate_role');
                } else {
                    switchUserTab('admin_role');
                }
                openEditUserModal({
                    id: id,
                    name: "{{ old('name') }}",
                    email: "{{ old('email') }}",
                    role: role,
                    spmb_unit_id: "{{ old('spmb_unit_id') }}"
                });
            } else if (failed.startsWith('user_reset_')) {
                let id = failed.replace('user_reset_', '');
                openResetPasswordModal({
                    id: id,
                    name: "User",
                    email: ""
                });
            }

            // Unhide the failed errors block in the reopened modal
            document.querySelectorAll('.spmb-user-errors').forEach(el => {
                el.classList.remove('hidden');
            });
        });
    @endif
</script>
@endsection
