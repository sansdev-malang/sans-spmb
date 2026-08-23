@extends('layouts.admin')

@section('title', 'Manajemen User - Admin Panel')
@section('page_title', 'Data User')

@section('content')
<div class="w-full space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex justify-between items-center">
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
        </button>
        <button onclick="switchUserTab('candidate_role')" id="userTabBtn-candidate_role" class="user-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50">
            <i data-lucide="user" class="w-4 h-4"></i> Orang Tua / Calon Siswa
        </button>
    </div>

    <!-- Tab Contents Container -->
    <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        
        <!-- Tab 1: Admin Role -->
        <div id="userTabContent-admin_role" class="user-tab-content p-8 space-y-6">
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Nama Pengguna</th>
                            <th class="py-4 px-6">Alamat Email</th>
                            <th class="py-4 px-6">Tanggal Dibuat</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($admins as $admin)
                            <tr class="hover:bg-slate-50/30 transition">
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
                                <td colspan="4" class="py-8 px-6 text-center text-slate-400">Belum ada user admin.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($admins->hasPages())
                <div class="pt-4">
                    {{ $admins->links() }}
                </div>
            @endif
        </div>

        <!-- Tab 2: Candidate Role -->
        <div id="userTabContent-candidate_role" class="user-tab-content p-8 space-y-6 hidden">
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[10px] text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50">
                            <th class="py-4 px-6">Nama Pengguna</th>
                            <th class="py-4 px-6">Alamat Email</th>
                            <th class="py-4 px-6">Tanggal Dibuat</th>
                            <th class="py-4 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-100">
                        @forelse($candidates as $cand)
                            <tr class="hover:bg-slate-50/30 transition">
                                <td class="py-4 px-6 font-extrabold text-slate-800">{{ $cand->name }}</td>
                                <td class="py-4 px-6 font-medium text-slate-600">{{ $cand->email }}</td>
                                <td class="py-4 px-6 text-slate-500 text-xs">{{ $cand->created_at->format('d M Y, H:i') }}</td>
                                <td class="py-4 px-6 text-right space-x-2">
                                    <button onclick="openEditUserModal({{ json_encode($cand) }})" class="text-xs text-brand-emerald font-bold hover:underline">Edit</button>
                                    <button onclick="openResetPasswordModal({{ json_encode($cand) }})" class="text-xs text-amber-600 font-bold hover:underline">Reset Password</button>
                                    <button onclick="deleteUser('{{ $cand->name }}', '{{ route('admin.users.destroy', $cand->id) }}')" class="text-xs text-red-600 font-bold hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 px-6 text-center text-slate-400">Belum ada user calon siswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($candidates->hasPages())
                <div class="pt-4">
                    {{ $candidates->links() }}
                </div>
            @endif
        </div>

    </div>
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
        <form action="{{ route('admin.users.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
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
        <form id="editUserForm" method="POST" class="p-6 space-y-4">
            @csrf
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
        <form id="resetPasswordForm" method="POST" class="p-6 space-y-4">
            @csrf
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
<form id="deleteUserForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    // Tab switching memory
    function switchUserTab(tabId) {
        document.querySelectorAll('.user-tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('userTabContent-' + tabId).classList.remove('hidden');

        document.querySelectorAll('.user-tab-btn').forEach(btn => {
            btn.className = "user-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-600 hover:bg-slate-50";
        });
        
        const activeBtn = document.getElementById('userTabBtn-' + tabId);
        activeBtn.className = "user-tab-btn px-5 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-brand-emerald text-white shadow";
        
        localStorage.setItem('spmb_users_active_tab', tabId);
    }

    document.addEventListener("DOMContentLoaded", function() {
        const savedTab = localStorage.getItem('spmb_users_active_tab') || 'admin_role';
        switchUserTab(savedTab);
    });

    // Add User Modal
    function openAddUserModal() {
        document.getElementById('addUserModal').classList.remove('hidden');
        toggleAddUnitSelect();
    }
    function closeAddUserModal() {
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
        document.getElementById('edit-name').value = user.name;
        document.getElementById('edit-email').value = user.email;
        document.getElementById('edit-role').value = user.role;
        document.getElementById('edit-spmb-unit-id').value = user.spmb_unit_id || '';
        toggleEditUnitSelect();
        document.getElementById('editUserForm').action = '/admin/users/' + user.id;
        document.getElementById('editUserModal').classList.remove('hidden');
    }
    function closeEditUserModal() {
        document.getElementById('editUserModal').classList.add('hidden');
    }

    // Reset Password Modal
    function openResetPasswordModal(user) {
        document.getElementById('reset-user-name').innerText = user.name + ' (' + user.email + ')';
        document.getElementById('resetPasswordForm').action = '/admin/users/' + user.id + '/reset-password';
        document.getElementById('resetPasswordModal').classList.remove('hidden');
    }
    function closeResetPasswordModal() {
        document.getElementById('resetPasswordModal').classList.add('hidden');
    }

    // Delete User
    function deleteUser(userName, actionUrl) {
        confirmDelete(actionUrl, `Apakah Anda yakin ingin menghapus user "${userName}"? Tindakan ini tidak dapat dibatalkan.`);
    }
</script>
@endsection
