@extends('layouts.portal')

@section('title', 'Edit Profil - Portal SPMB')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8 sm:px-6 lg:px-8 space-y-6">

    <!-- Header Title -->
    <div class="border-b border-slate-150 dark:border-slate-800 pb-4">
        <h1 class="text-2xl font-extrabold text-slate-850 dark:text-white flex items-center gap-2">
            <i data-lucide="user" class="w-6 h-6 text-brand-emerald dark:text-emerald-400"></i>
            Pengaturan Profil
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola data nama, email, kata sandi, dan keamanan akun wali murid Anda.</p>
    </div>

    <div class="grid grid-cols-1 gap-6">
        
        <!-- Profile Information Card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-150/80 dark:border-slate-800 space-y-6">
            <div>
                <h2 class="text-sm font-extrabold text-slate-850 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="id-card" class="w-4 h-4 text-brand-emerald"></i>
                    Informasi Akun
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Perbarui nama dan alamat email login akun Anda.</p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('patch')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-xs font-bold text-slate-655 dark:text-slate-450 uppercase tracking-wider mb-2">Nama Lengkap*</label>
                        <input type="text" name="name" required
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm"
                            value="{{ old('name', $user->name) }}">
                        @if($errors->has('name'))
                            <p class="text-xs text-red-650 mt-1">⚠️ {{ $errors->first('name') }}</p>
                        @endif
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-bold text-slate-655 dark:text-slate-450 uppercase tracking-wider mb-2">Alamat Email*</label>
                        <input type="email" name="email" required
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm"
                            value="{{ old('email', $user->email) }}">
                        @if($errors->has('email'))
                            <p class="text-xs text-red-650 mt-1">⚠️ {{ $errors->first('email') }}</p>
                        @endif
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800/60 flex justify-end">
                    <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl font-bold text-xs shadow-md transition flex items-center gap-1.5">
                        <i data-lucide="save" class="w-4 h-4"></i> Perbarui Akun
                    </button>
                </div>
            </form>
        </div>

        <!-- Update Password Card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-150/80 dark:border-slate-800 space-y-6">
            <div>
                <h2 class="text-sm font-extrabold text-slate-850 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="key-round" class="w-4 h-4 text-amber-500"></i>
                    Perbarui Kata Sandi
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pastikan akun Anda menggunakan kata sandi yang kuat dan unik.</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                @method('put')

                <div class="grid grid-cols-1 gap-4">
                    <!-- Current Password -->
                    <div>
                        <label class="block text-xs font-bold text-slate-655 dark:text-slate-450 uppercase tracking-wider mb-2">Kata Sandi Saat Ini*</label>
                        <input type="password" name="current_password" required
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm"
                            placeholder="Masukkan kata sandi lama">
                        @if($errors->updatePassword->has('current_password'))
                            <p class="text-xs text-red-650 mt-1">⚠️ {{ $errors->updatePassword->first('current_password') }}</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- New Password -->
                        <div>
                            <label class="block text-xs font-bold text-slate-655 dark:text-slate-450 uppercase tracking-wider mb-2">Kata Sandi Baru*</label>
                            <input type="password" name="password" required
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm"
                                placeholder="Min. 8 karakter">
                            @if($errors->updatePassword->has('password'))
                                <p class="text-xs text-red-650 mt-1">⚠️ {{ $errors->updatePassword->first('password') }}</p>
                            @endif
                        </div>

                        <!-- Confirm New Password -->
                        <div>
                            <label class="block text-xs font-bold text-slate-655 dark:text-slate-450 uppercase tracking-wider mb-2">Konfirmasi Kata Sandi Baru*</label>
                            <input type="password" name="password_confirmation" required
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm"
                                placeholder="Masukkan ulang kata sandi baru">
                            @if($errors->updatePassword->has('password_confirmation'))
                                <p class="text-xs text-red-650 mt-1">⚠️ {{ $errors->updatePassword->first('password_confirmation') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800/60 flex justify-end">
                    <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl font-bold text-xs shadow-md transition flex items-center gap-1.5">
                        <i data-lucide="key-round" class="w-4 h-4"></i> Perbarui Kata Sandi
                    </button>
                </div>
            </form>
        </div>

        <!-- Danger Zone Card -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-150/80 dark:border-slate-800 space-y-6">
            <div>
                <h2 class="text-sm font-extrabold text-red-600 dark:text-red-400 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="shield-alert" class="w-4 h-4 text-red-600"></i>
                    Zona Bahaya
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Tindakan tidak dapat dibatalkan. Mohon berhati-hati.</p>
            </div>

            <div class="bg-red-50/50 dark:bg-red-950/10 rounded-2xl p-6 border border-red-100 dark:border-red-950/40 space-y-4">
                <div>
                    <h3 class="text-xs font-bold text-red-650 dark:text-red-450 uppercase tracking-wide">Hapus Akun Permanen</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Setelah akun Anda dihapus, semua data calon siswa yang Anda daftarkan, riwayat pembayaran, serta berkas akan dihapus secara permanen dari basis data sistem Sekolah Anak Saleh.</p>
                </div>
                
                <button type="button" id="toggleDeleteBtn" onclick="toggleDeleteForm()" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                    <i data-lucide="trash-2" class="w-4 h-4"></i> Hapus Akun Saya
                </button>
                
                <div id="deleteConfirmForm" class="hidden pt-4 border-t border-red-100 dark:border-red-950/30 space-y-4">
                    <form method="POST" action="{{ route('profile.destroy') }}" class="space-y-4">
                        @csrf
                        @method('delete')
                        <div>
                            <label class="block text-xs font-bold text-slate-655 dark:text-slate-450 uppercase tracking-wider mb-2">Konfirmasi Kata Sandi*</label>
                            <input type="password" name="password" required 
                                class="max-w-md w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 rounded-xl px-4 py-3 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500 text-sm"
                                placeholder="Masukkan kata sandi Anda untuk konfirmasi">
                            @if($errors->userDeletion->has('password'))
                                <p class="text-xs text-red-650 mt-1">⚠️ {{ $errors->userDeletion->first('password') }}</p>
                            @endif
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-sm transition">
                                Konfirmasi Hapus Akun
                            </button>
                            <button type="button" onclick="toggleDeleteForm()" class="bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-5 py-2.5 rounded-xl font-bold text-xs transition">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

@if(session('status') === 'profile-updated')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof showToast === 'function') {
                showToast("Informasi profil Anda berhasil diperbarui.", "success");
            }
        });
    </script>
@endif

@if(session('status') === 'password-updated')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof showToast === 'function') {
                showToast("Kata sandi Anda berhasil diperbarui.", "success");
            }
        });
    </script>
@endif

@push('scripts')
<script>
    function toggleDeleteForm() {
        const form = document.getElementById('deleteConfirmForm');
        const btn = document.getElementById('toggleDeleteBtn');
        if (form && btn) {
            const isHidden = form.classList.contains('hidden');
            if (isHidden) {
                form.classList.remove('hidden');
                btn.classList.add('hidden');
            } else {
                form.classList.add('hidden');
                btn.classList.remove('hidden');
            }
        }
    }

    @if($errors->userDeletion->isNotEmpty())
        document.addEventListener("DOMContentLoaded", function() {
            toggleDeleteForm();
        });
    @endif
</script>
@endpush

@endsection
