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
                    <div class="relative">
                        <input type="password" id="current_password" name="current_password" required 
                            class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 pr-11 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm"
                            placeholder="Masukkan kata sandi lama">
                        <button type="button" onclick="togglePasswordVisibility('current_password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-500 hover:text-slate-700 transition">
                            <i id="current_password_icon" data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- New Password -->
                <div>
                    <label class="block text-xs font-bold text-slate-650 uppercase tracking-wider mb-2">Kata Sandi Baru*</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required 
                            class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 pr-11 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm"
                            placeholder="Min. 8 karakter">
                        <button type="button" onclick="togglePasswordVisibility('password')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-500 hover:text-slate-700 transition">
                            <i id="password_icon" data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-xs font-bold text-slate-650 uppercase tracking-wider mb-2">Konfirmasi Kata Sandi Baru*</label>
                    <div class="relative">
                        <input type="password" id="password_confirmation" name="password_confirmation" required 
                            class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 pr-11 text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm"
                            placeholder="Masukkan ulang kata sandi baru">
                        <button type="button" onclick="togglePasswordVisibility('password_confirmation')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-500 hover:text-slate-700 transition">
                            <i id="password_confirmation_icon" data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
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

    <script>
        function togglePasswordVisibility(fieldId) {
            const input = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '_icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'eye-closed');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            
            // Re-initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    </script>
</div>
@endsection
