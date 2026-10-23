<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'BUDGET-ENGAGE') }} — {{ __('landing.nav.secure_access') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        .auth-pattern {
            background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.10) 1px, transparent 0);
            background-size: 28px 28px;
        }
        .float-card { animation: floatCard 5s ease-in-out infinite; }
        @keyframes floatCard {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-10px); }
        }
    </style>
</head>
<body class="h-full font-sans antialiased">
<div class="min-h-screen flex flex-col lg:flex-row">

    {{-- ── LEFT PANEL: Brand + Features ── --}}
    <aside class="relative lg:w-[44%] xl:w-[40%] bg-sidebar-gradient text-white overflow-hidden shrink-0
                  flex flex-col justify-between p-8 lg:p-12 xl:p-16">

        {{-- Decorative elements --}}
        <div class="absolute inset-0 auth-pattern pointer-events-none"></div>
        <div class="absolute -top-20 -right-20 w-72 h-72 rounded-full bg-ceeac-bright/20 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-80 h-80 rounded-full bg-blue-900/40 blur-3xl pointer-events-none"></div>

        <div class="relative z-10">
            {{-- Logo --}}
            <a href="{{ route('landing') }}" class="inline-flex items-center gap-2.5 group">
                <x-brand-mark variant="light" class="h-11 w-11 shrink-0 group-hover:scale-105 transition-transform" />
                <div class="flex flex-col leading-tight">
                    <span class="text-lg font-bold tracking-tight text-white group-hover:text-blue-100 transition-colors">BUDGET-ENGAGE</span>
                    <span class="text-[11px] text-ceeac-sky/90 font-medium">{{ __('landing.slogan') }}</span>
                </div>
            </a>

            {{-- Hero copy --}}
            <div class="mt-12 lg:mt-14">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 ring-1 ring-white/20 px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-widest mb-6">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-400"></span>
                    </span>
                    Commission de la CEEAC
                </span>

                <h1 class="text-2xl xl:text-3xl font-bold leading-tight tracking-tight">
                    {{ __('landing.hero.title') }}
                </h1>
                <p class="mt-4 text-blue-100/80 text-sm xl:text-base leading-relaxed max-w-sm">
                    {{ __('landing.slogan_alt') }}
                </p>
            </div>

            {{-- Feature bullets --}}
            <ul class="mt-10 space-y-4">
                @foreach ([
                    ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'text' => __('landing.benefits.items.trace')],
                    ['icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'text' => 'Accès sécurisé — TLS 1.3, sessions protégées'],
                    ['icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'text' => 'Workflow de validation multi-niveaux'],
                    ['icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'text' => 'Rapports PDF & Excel en temps réel'],
                ] as $feat)
                    <li class="flex items-start gap-3">
                        <div class="h-8 w-8 rounded-lg bg-white/10 ring-1 ring-white/15 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-ceeac-sky" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $feat['icon'] }}"/>
                            </svg>
                        </div>
                        <span class="text-sm text-blue-100/90 leading-relaxed">{{ $feat['text'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Mini dashboard widget --}}
        <div class="relative z-10 mt-10 lg:mt-0">
            <div class="float-card rounded-2xl bg-white/10 backdrop-blur-sm ring-1 ring-white/20 p-4">
                <div class="rounded-xl bg-white/15 p-3 mb-3">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-blue-200/80">Synthèse budgétaire</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/30 text-emerald-300 font-semibold">Actif</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ([['Budget', '42,8 Mrd', 'text-white'],['Engagé', '28,1 Mrd', 'text-ceeac-sky'],['Payé', '19,4 Mrd', 'text-emerald-300'],['Dispo.', '14,7 Mrd', 'text-amber-300']] as $w)
                            <div class="rounded-lg bg-white/10 px-2.5 py-2">
                                <div class="text-[9px] text-blue-200/60 mb-0.5">{{ $w[0] }}</div>
                                <div class="text-xs font-bold tabular-nums {{ $w[2] }}">{{ $w[1] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-[10px] text-blue-200/70 font-medium">Données en temps réel après connexion</span>
                </div>
            </div>
        </div>

        {{-- Footer note --}}
        <p class="relative z-10 text-[11px] text-blue-200/60 mt-8 lg:mt-0">
            {{ __('landing.footer.product') }}
        </p>
    </aside>

    {{-- ── RIGHT PANEL: Form ── --}}
    <div class="flex-1 flex flex-col justify-center bg-gray-50/80 px-4 py-10 sm:px-8 lg:px-12 xl:px-16 min-h-screen lg:min-h-0">

        {{-- Top link --}}
        <div class="flex justify-end mb-6 lg:mb-8">
            <a href="{{ route('landing') }}"
               class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-ceeac-primary transition-colors group">
                <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('landing.nav.home') }}
            </a>
        </div>

        {{-- Form card --}}
        <div class="w-full max-w-md mx-auto">
            <div class="bg-white rounded-2xl shadow-hover border border-gray-100 p-8 sm:p-10">
                {{ $slot }}
            </div>

            {{-- Security note --}}
            <p class="mt-6 text-center text-xs text-gray-400 leading-relaxed max-w-sm mx-auto">
                <svg class="w-3.5 h-3.5 inline-block mb-0.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Connexion chiffrée TLS 1.3 · Accès strictement nominatif et journalisé
            </p>
        </div>
    </div>
</div>
</body>
</html>
