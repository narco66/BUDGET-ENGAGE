<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-xl font-bold text-ceeac-primary">{{ __('auth.login_title') }}</h2>
        <p class="mt-2 text-sm text-gray-600">{{ __('auth.login_intro') }}</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('auth.email_label')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('auth.password_label')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-4 flex-wrap">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-ceeac-bright shadow-sm focus:ring-ceeac-bright" name="remember">
                <span class="text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-ceeac-bright hover:text-ceeac-primary focus:outline-none focus:underline" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center py-3 rounded-institutional normal-case tracking-normal text-base font-semibold">
                {{ __('landing.nav.secure_access') }}
            </x-primary-button>
        </div>
    </form>

    <p class="mt-8 text-xs text-gray-500 leading-relaxed border-t border-gray-100 pt-6">
        {{ __('auth.access_notice') }}
    </p>
</x-guest-layout>
