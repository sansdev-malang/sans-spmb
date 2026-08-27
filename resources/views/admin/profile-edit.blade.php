@extends('layouts.admin')

@section('title', 'Profil Saya - Admin Panel')
@section('page_title', 'Profil Saya')

@section('content')
<div class="max-w-4xl space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h1 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
            <i data-lucide="user" class="w-5 h-5 text-brand-emerald"></i>
            Profil Saya
        </h1>
        <p class="text-xs text-slate-500 mt-1">Perbarui informasi profil akun Anda di sistem SPMB Sekolah Anak Saleh.</p>
    </div>

    <!-- Edit Profile Card -->
    <div class="bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden">
        <form method="POST" action="{{ route('admin.profile.update') }}" hx-boost="false" class="p-8 space-y-6">
            @csrf

            @if($errors->any())
                <div class="text-xs text-red-650 bg-red-50 p-3.5 rounded-xl border border-red-200 font-semibold space-y-1">
                    @foreach($errors->all() as $error)
                        <p>⚠️ {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6">
                <!-- Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-650 uppercase tracking-wider mb-2">Nama Lengkap*</label>
                    <input type="text" name="name" required 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm"
                        value="{{ old('name', $user->name) }}">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-slate-650 uppercase tracking-wider mb-2">Alamat Email*</label>
                    <input type="email" name="email" required 
                        class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-emerald text-sm"
                        value="{{ old('email', $user->email) }}">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="bg-brand-emerald hover-emerald text-white px-6 py-3 rounded-xl font-bold text-xs shadow-md transition flex items-center gap-1.5">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Profil
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
