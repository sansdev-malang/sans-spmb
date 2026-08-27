<x-guest-layout>
    <div class="mb-5 text-xs text-slate-555 dark:text-slate-400 font-medium leading-relaxed">
        {{ __('Lupa password akun Anda? Tidak masalah. Silakan masukkan alamat email Anda di bawah ini dan kami akan mengirimkan tautan reset password agar Anda dapat membuat password baru.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div class="space-y-2">
            <x-input-label for="email" :value="__('Email')" class="font-bold text-[10px] text-slate-450 dark:text-slate-400 uppercase tracking-wider block" />
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </span>
                <x-text-input id="email" class="block pl-10 w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 py-3 text-xs font-semibold focus:outline-none transition" type="email" name="email" :value="old('email')" required autofocus />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full bg-custom-primary hover:bg-custom-primary/95 text-white py-3.5 rounded-xl font-bold transition flex items-center justify-center gap-2 shadow-md text-xs uppercase tracking-wider">
                <span>Kirim Link Reset Password</span> <i data-lucide="send" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Back to login link -->
        <div class="text-center border-t border-slate-100 dark:border-slate-800/80 pt-4 mt-2 text-xs text-slate-550 dark:text-slate-400">
            Sudah ingat password Anda? 
            <a href="{{ route('login') }}" class="font-extrabold text-custom-primary hover:opacity-85 transition ml-0.5">
                Login Di Sini &rarr;
            </a>
        </div>
    </form>
</x-guest-layout>
