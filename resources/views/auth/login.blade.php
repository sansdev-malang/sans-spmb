<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div class="space-y-2">
            <x-input-label for="email" :value="__('Email')" class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-[0.18em] font-bold block" />
            <div class="relative group">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 group-focus-within:text-custom-primary transition">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </span>
                <x-text-input id="email" class="block pl-10 w-full rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/80 py-3 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-custom-primary/30 focus:border-custom-primary transition shadow-sm" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <x-input-label for="password" :value="__('Password')" class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-[0.18em] font-bold block" />
            <div class="relative group">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 group-focus-within:text-custom-primary transition">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                </span>
                <x-text-input id="password" class="block pl-10 pr-10 w-full rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-slate-50/80 py-3 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-custom-primary/30 focus:border-custom-primary transition shadow-sm"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />
                <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-custom-primary transition" aria-label="Lihat password">
                    <i id="password-toggle-icon" data-lucide="eye" class="w-4 h-4"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 dark:border-slate-700 text-custom-primary shadow-sm focus:ring-custom-primary focus:ring-offset-0 w-4 h-4 transition" name="remember">
                <span class="ms-2 text-xs font-semibold text-slate-600 dark:text-slate-200">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-xs font-extrabold text-custom-primary dark:text-emerald-400 hover:opacity-85 transition" href="{{ route('password.request') }}">
                    {{ __('Lupa password?') }}
                </a>
            @endif
        </div>

        <!-- Login Button -->
        <div class="pt-2">
            <button type="submit" class="w-full bg-custom-primary hover:opacity-95 text-white py-3.5 rounded-2xl font-black transition flex items-center justify-center gap-2 shadow-md hover:shadow-lg text-[11px] uppercase tracking-[0.18em]">
                <span>Masuk Sekarang</span> <i data-lucide="log-in" class="w-4 h-4"></i>
            </button>
        </div>
        
        <!-- Registration Link -->
        <div class="text-center border-t border-slate-100 dark:border-slate-800/80 pt-4 mt-2 text-xs text-slate-600 dark:text-slate-400">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-extrabold text-custom-primary dark:text-emerald-400 hover:opacity-85 transition ml-0.5">
                Daftar Sekarang &rarr;
            </a>
        </div>
    </form>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('password-toggle-icon');

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
