<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div class="space-y-2">
            <x-input-label for="name" :value="__('Nama Lengkap Orang Tua / Wali')" class="font-bold text-xs text-slate-450 dark:text-slate-400 uppercase tracking-wider block" />
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                    <i data-lucide="user" class="w-4 h-4"></i>
                </span>
                <x-text-input id="name" class="block pl-10 w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 py-3 text-xs font-semibold focus:outline-none transition" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <!-- Email Address -->
        <div class="space-y-2">
            <x-input-label for="email" :value="__('Email')" class="font-bold text-xs text-slate-450 dark:text-slate-400 uppercase tracking-wider block" />
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </span>
                <x-text-input id="email" class="block pl-10 w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 py-3 text-xs font-semibold focus:outline-none transition" type="email" name="email" :value="old('email')" required autocomplete="username" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <x-input-label for="password" :value="__('Password')" class="font-bold text-xs text-slate-450 dark:text-slate-400 uppercase tracking-wider block" />
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </span>
                <x-text-input id="password" class="block pl-10 pr-10 w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 py-3 text-xs font-semibold focus:outline-none transition"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />
                <button type="button" onclick="toggleRegisterPasswordVisibility('password')" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-custom-primary transition" aria-label="Lihat password">
                    <i id="password-toggle-icon" data-lucide="eye" class="w-4 h-4"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Confirm Password -->
        <div class="space-y-2">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" class="font-bold text-xs text-slate-450 dark:text-slate-400 uppercase tracking-wider block" />
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </span>
                <x-text-input id="password_confirmation" class="block pl-10 pr-10 w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 py-3 text-xs font-semibold focus:outline-none transition"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />
                <button type="button" onclick="toggleRegisterPasswordVisibility('password_confirmation')" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-custom-primary transition" aria-label="Lihat password">
                    <i id="password_confirmation-toggle-icon" data-lucide="eye" class="w-4 h-4"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <!-- Register Button -->
        <div class="pt-2">
            <button type="submit" class="w-full bg-custom-primary hover:bg-custom-primary/95 text-white py-3.5 rounded-xl font-bold transition flex items-center justify-center gap-2 shadow-md text-xs uppercase tracking-wider">
                <span>Daftar Sekarang</span> <i data-lucide="user-plus" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Login Link -->
        <div class="text-center border-t border-slate-100 dark:border-slate-800/80 pt-4 mt-2 text-xs text-slate-550 dark:text-slate-400">
            Sudah punya akun? 
            <a href="{{ route('login') }}" class="font-extrabold text-custom-primary hover:opacity-85 transition ml-0.5">
                Login Di Sini &rarr;
            </a>
        </div>
    </form>

    <script>
        function toggleRegisterPasswordVisibility(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(`${fieldId}-toggle-icon`);

            if (!passwordInput || !toggleIcon) return;

            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            toggleIcon.setAttribute('data-lucide', isPassword ? 'eye-closed' : 'eye');

            if (window.lucide) {
                lucide.createIcons();
            }
        }
    </script>
</x-guest-layout>
