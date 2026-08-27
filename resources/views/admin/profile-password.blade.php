@extends('layouts.admin')

@section('title', 'Ganti Password - Admin Panel')
@section('page_title', 'Ganti Password')

@section('content')
<div class="max-w-4xl space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h1 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
            <i data-lucide="key-round" class="w-5 h-5 text-amber-600"></i>
            Ganti Password
        </h1>
        <p class="text-xs text-slate-500 mt-1">Pastikan akun Anda menggunakan kata sandi yang kuat dan aman.</p>
    </div>

    <!-- Change Password Card -->
    <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        <form method="POST" action="{{ route('admin.profile.password.update') }}" hx-boost="false" class="p-8 space-y-6">
            @csrf

            @if($errors->any())
                <div class="text-xs text-red-650 bg-red-50 p-3.5 rounded-xl border border-red-200 font-semibold space-y-1">
                    @foreach($errors->all() as $error)
                        <p>⚠️ {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6">
                <!-- Current Password -->
                <div>
                    <label class="block text-xs font-bold text-slate-650 uppercase tracking-wider mb-2">Kata Sandi Saat Ini*</label>
                    <input type="password" name="current_password" required 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm"
                        placeholder="Masukkan kata sandi lama">
                </div>

                <!-- New Password -->
                <div>
                    <label class="block text-xs font-bold text-slate-650 uppercase tracking-wider mb-2">Kata Sandi Baru*</label>
                    <input type="password" name="password" required 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm"
                        placeholder="Min. 8 karakter">
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-xs font-bold text-slate-650 uppercase tracking-wider mb-2">Konfirmasi Kata Sandi Baru*</label>
                    <input type="password" name="password_confirmation" required 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm"
                        placeholder="Masukkan ulang kata sandi baru">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-xl font-bold text-xs shadow-md transition flex items-center gap-1.5">
                    <i data-lucide="key-round" class="w-4 h-4"></i> Perbarui Password
                </button>
            </div>
        </form>
    </div>

    @if(session('success'))
        <script>
            if (typeof showToast === 'function') {
                showToast("{{ session('success') }}", 'success');
            }
        </script>
    @endif
</div>
@endsection
