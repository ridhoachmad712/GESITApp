<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-lg font-bold text-gray-900">Masuk</h1>
        <p class="mt-1 text-sm text-gray-500">Gunakan akun yang terdaftar untuk mengakses dokumen sesuai hak akses Anda.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-unm-600 shadow-sm focus:ring-unm-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="mt-6 flex items-center justify-between gap-3">
            @if (Route::has('password.request'))
                <a class="text-sm text-gray-500 transition hover:text-unm-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-unm-500 rounded-md" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button>
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        @if (Route::has('register'))
            <p class="mt-6 border-t border-gray-100 pt-4 text-center text-sm text-gray-500">
                Mahasiswa belum punya akun?
                <a href="{{ route('register') }}" class="font-semibold text-unm-600 hover:text-unm-700">Daftar di sini</a>
            </p>
        @endif
    </form>
</x-guest-layout>
