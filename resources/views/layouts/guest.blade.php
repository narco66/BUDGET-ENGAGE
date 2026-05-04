<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-900 bg-ceeac-muted/60">
        <div class="min-h-screen flex flex-col lg:flex-row">
            <aside class="lg:w-[42%] xl:w-[40%] bg-gradient-to-br from-ceeac-primary via-[#1e3a8a] to-ceeac-bright text-white p-8 lg:p-12 flex flex-col justify-between shrink-0">
                <div>
                    <a href="{{ route('landing') }}" class="inline-block">
                        <x-brand-logo variant="light" compact />
                    </a>
                    <h1 class="mt-10 text-2xl xl:text-3xl font-bold leading-tight">{{ __('landing.hero.title') }}</h1>
                    <p class="mt-4 text-blue-100 text-sm xl:text-base leading-relaxed max-w-md">{{ __('landing.slogan_alt') }}</p>
                    <ul class="mt-8 space-y-3 text-sm text-blue-100">
                        <li class="flex gap-2"><span class="text-ceeac-success">✓</span> {{ __('landing.benefits.items.trace') }}</li>
                        <li class="flex gap-2"><span class="text-ceeac-success">✓</span> {{ __('landing.nav.secure_access') }} — TLS, sessions sécurisées</li>
                        <li class="flex gap-2"><span class="text-ceeac-success">✓</span> {{ __('landing.compliance.badges.owasp') }}</li>
                    </ul>
                </div>
                <p class="text-xs text-blue-200/90 mt-12 lg:mt-0">{{ __('landing.footer.product') }}</p>
            </aside>
            <div class="flex-1 flex flex-col justify-center px-4 py-10 sm:px-8 lg:px-16">
                <div class="w-full max-w-md mx-auto rounded-institutional bg-white shadow-soft border border-gray-100 p-8 sm:p-10">
                    {{ $slot }}
                </div>
                <p class="text-center text-xs text-gray-500 mt-8">
                    <a href="{{ route('landing') }}" class="text-ceeac-bright hover:text-ceeac-primary font-medium">{{ __('landing.nav.home') }}</a>
                    <span class="mx-2">·</span>
                    <span>{{ __('landing.footer.contact') }}</span>
                </p>
            </div>
        </div>
    </body>
</html>
