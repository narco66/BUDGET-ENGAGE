@extends('layouts.landing')

@section('content')

{{-- ═══════════════════════════════════════════════════════════
     HEADER
═══════════════════════════════════════════════════════════ --}}
<header :class="scrolled ? 'shadow-card border-gray-200/80' : 'border-transparent'"
        class="fixed top-0 inset-x-0 z-50 border-b bg-white/95 backdrop-blur-md transition-all duration-300">
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16 lg:h-[4.5rem]">

        {{-- Logo --}}
        <a href="{{ route('landing') }}" class="flex items-center gap-2.5 group shrink-0">
            <x-brand-mark variant="color" class="h-10 w-10 shrink-0 group-hover:scale-105 transition-transform duration-200" />
            <div class="hidden sm:flex flex-col leading-tight">
                <span class="text-sm font-bold text-ceeac-primary tracking-tight group-hover:text-ceeac-bright transition-colors">BUDGET-ENGAGE</span>
                <span class="text-[10px] text-gray-500 font-medium hidden md:block">{{ __('landing.slogan') }}</span>
            </div>
        </a>

        {{-- Desktop nav --}}
        <nav class="hidden lg:flex items-center gap-7 text-[13px] font-medium text-gray-600">
            <a href="#accueil"         class="hover:text-ceeac-bright transition-colors">Accueil</a>
            <a href="#fonctionnalites" class="hover:text-ceeac-bright transition-colors">{{ __('landing.nav.features') }}</a>
            <a href="#modules"         class="hover:text-ceeac-bright transition-colors">{{ __('landing.nav.modules') }}</a>
            <a href="#conformite"      class="hover:text-ceeac-bright transition-colors">À propos</a>
            <a href="#rapports"        class="hover:text-ceeac-bright transition-colors">{{ __('landing.nav.reports') }}</a>
            <a href="#conformite"      class="hover:text-ceeac-bright transition-colors">{{ __('landing.nav.security') }}</a>
            <a href="#contact"         class="hover:text-ceeac-bright transition-colors">{{ __('landing.nav.contact') }}</a>
        </nav>

        {{-- Actions --}}
        <div class="flex items-center gap-2 sm:gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    {{ __('dashboard.title') }}
                </a>
            @else
                <a href="{{ route('login') }}" class="hidden sm:inline-flex btn btn-secondary btn-sm">
                    {{ __('landing.nav.login') }}
                </a>
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    {{ __('landing.nav.secure_access') }}
                </a>
            @endauth

            {{-- Mobile hamburger --}}
            <button @click="mobileMenuOpen = !mobileMenuOpen"
                    class="lg:hidden btn btn-ghost btn-sm p-2"
                    aria-label="Menu">
                <svg x-show="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="mobileMenuOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="mobileMenuOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="lg:hidden border-t border-gray-100 bg-white/98 backdrop-blur-md shadow-card">
        <nav class="max-w-8xl mx-auto px-4 py-4 flex flex-col gap-1">
            <a @click="mobileMenuOpen=false" href="#fonctionnalites" class="px-4 py-3 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-ceeac-bright transition-colors">{{ __('landing.nav.features') }}</a>
            <a @click="mobileMenuOpen=false" href="#modules"         class="px-4 py-3 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-ceeac-bright transition-colors">{{ __('landing.nav.modules') }}</a>
            <a @click="mobileMenuOpen=false" href="#rapports"        class="px-4 py-3 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-ceeac-bright transition-colors">{{ __('landing.nav.reports') }}</a>
            <a @click="mobileMenuOpen=false" href="#conformite"      class="px-4 py-3 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-ceeac-bright transition-colors">{{ __('landing.nav.security') }}</a>
            @guest
                <div class="border-t border-gray-100 mt-2 pt-3">
                    <a href="{{ route('login') }}" class="btn btn-primary w-full">{{ __('landing.nav.secure_access') }}</a>
                </div>
            @endguest
        </nav>
    </div>
</header>

{{-- Spacer for fixed header --}}
<div class="h-16 lg:h-[4.5rem]"></div>


{{-- ═══════════════════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════════════════ --}}
<section id="accueil" class="relative overflow-hidden bg-sidebar-gradient text-white">
    {{-- Decorative background --}}
    <div class="absolute inset-0 dot-grid pointer-events-none opacity-60"></div>
    <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-ceeac-bright/20 blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 rounded-full bg-blue-900/40 blur-3xl pointer-events-none"></div>

    <div class="relative max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28 grid lg:grid-cols-2 gap-14 lg:gap-20 items-center">

        {{-- Left content --}}
        <div class="space-y-7 animate-fade-in">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 ring-1 ring-white/25 px-4 py-1.5 text-xs font-bold uppercase tracking-widest backdrop-blur-sm">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                </span>
                {{ __('landing.hero.badge') }}
            </span>

            <h1 class="text-3xl sm:text-4xl lg:text-[2.6rem] xl:text-5xl font-bold leading-[1.15] tracking-tight text-balance">
                {{ __('landing.hero.title') }}
            </h1>

            <p class="text-base lg:text-lg text-blue-100/90 max-w-xl leading-relaxed">
                {{ __('landing.hero.subtitle') }}
            </p>

            <div class="flex flex-wrap gap-3 pt-1">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-xl px-6 py-3 font-semibold bg-white text-ceeac-primary hover:bg-gray-100 shadow-float transition-all hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        {{ __('landing.hero.cta_primary') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl px-6 py-3 font-semibold bg-white text-ceeac-primary hover:bg-gray-100 shadow-float transition-all hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        {{ __('landing.hero.cta_primary') }}
                    </a>
                @endauth
                <a href="#dashboard-demo" class="inline-flex items-center gap-2 rounded-xl px-6 py-3 font-semibold border border-white/30 text-white hover:bg-white/10 backdrop-blur-sm transition-all hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    {{ __('landing.hero.cta_demo') }}
                </a>
            </div>

            {{-- Hero stats --}}
            <dl class="grid grid-cols-3 gap-6 pt-7 border-t border-white/15 mt-2">
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-widest text-blue-300/70">{{ __('landing.hero.stats_budget') }}</dt>
                    <dd class="mt-1.5 text-sm font-semibold text-white leading-snug">360° sur les crédits</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-widest text-blue-300/70">{{ __('landing.hero.stats_control') }}</dt>
                    <dd class="mt-1.5 text-sm font-semibold text-white leading-snug">Validation multi-niveaux</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-semibold uppercase tracking-widest text-blue-300/70">{{ __('landing.hero.stats_audit') }}</dt>
                    <dd class="mt-1.5 text-sm font-semibold text-white leading-snug">Log budgétaire complet</dd>
                </div>
            </dl>
        </div>

        {{-- Right: Mock dashboard card --}}
        <div class="relative hero-float">
            {{-- Glow behind card --}}
            <div class="absolute inset-4 rounded-2xl bg-ceeac-bright/30 blur-2xl"></div>

            <div class="relative rounded-2xl bg-white/10 backdrop-blur-sm ring-1 ring-white/25 p-1 shadow-float">
                <div class="rounded-xl bg-white text-gray-900 overflow-hidden shadow-card">

                    {{-- Mock topbar --}}
                    <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 bg-gray-50/80">
                        <div class="flex items-center gap-2">
                            <div class="h-2.5 w-2.5 rounded-full bg-red-400"></div>
                            <div class="h-2.5 w-2.5 rounded-full bg-yellow-400"></div>
                            <div class="h-2.5 w-2.5 rounded-full bg-green-400"></div>
                        </div>
                        <span class="text-xs font-semibold text-ceeac-primary">Tableau de bord — Exercice {{ date('Y') }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-semibold">En direct</span>
                    </div>

                    <div class="p-5 space-y-4">
                        {{-- KPI row --}}
                        <div class="grid grid-cols-2 gap-3">
                            @foreach ([
                                ['label' => 'Budget révisé', 'value' => '42,8 Mrd', 'color' => 'text-ceeac-primary', 'bg' => 'bg-blue-50', 'trend' => '+2,1%'],
                                ['label' => 'Engagé',        'value' => '28,1 Mrd', 'color' => 'text-ceeac-bright',  'bg' => 'bg-indigo-50','trend' => '65,7%'],
                                ['label' => 'Payé',          'value' => '19,4 Mrd', 'color' => 'text-emerald-600',   'bg' => 'bg-emerald-50','trend' => '45,3%'],
                                ['label' => 'Disponible',    'value' => '14,7 Mrd', 'color' => 'text-amber-600',     'bg' => 'bg-amber-50', 'trend' => '34,3%'],
                            ] as $kpi)
                                <div class="rounded-xl {{ $kpi['bg'] }} p-3.5">
                                    <div class="text-[10px] text-gray-500 font-medium">{{ $kpi['label'] }}</div>
                                    <div class="mt-1 text-base font-bold tabular-nums {{ $kpi['color'] }}">{{ $kpi['value'] }}</div>
                                    <div class="mt-0.5 text-[10px] font-semibold text-gray-400">{{ $kpi['trend'] }}</div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Mini bar chart --}}
                        <div class="rounded-xl bg-gray-50 p-3.5">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-[10px] font-bold uppercase tracking-wide text-gray-500">Flux mensuel (engagements)</span>
                                <span class="text-[10px] text-gray-400">Jan → Jun</span>
                            </div>
                            <div class="flex items-end gap-1.5 h-16">
                                @foreach ([30, 52, 41, 78, 60, 88, 45, 66, 50, 72, 38, 80] as $idx => $h)
                                    <div class="flex-1 rounded-t-sm transition-all duration-500"
                                         style="height: {{ $h }}%; background: linear-gradient(to top, #1E3A8A, #60A5FA); opacity: {{ $idx >= 6 ? '0.35' : '0.9' }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Status row --}}
                        <div class="flex items-center justify-between">
                            <div class="flex gap-1.5 flex-wrap">
                                <span class="badge badge-success text-[10px] py-0.5">12 Approuvés</span>
                                <span class="badge badge-warning text-[10px] py-0.5">5 En attente</span>
                                <span class="badge badge-danger text-[10px] py-0.5">2 Alertes</span>
                            </div>
                            <span class="text-[10px] text-gray-400">Mise à jour il y a 2 min</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════
     STATS STRIP
═══════════════════════════════════════════════════════════ --}}
<div class="bg-white border-b border-gray-100 shadow-sm">
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-gray-100">
            @foreach ([
                ['number' => '8',   'suffix' => ' modules',    'label' => 'Modules intégrés',         'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                ['number' => '100', 'suffix' => '%',           'label' => 'Traçabilité des opérations','icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                ['number' => '5',   'suffix' => ' niveaux',    'label' => 'Workflow de validation',    'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                ['number' => '24',  'suffix' => '/7',          'label' => 'Disponibilité garantie',   'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ] as $stat)
                <div class="flex items-center gap-4 px-6 lg:px-10 py-6">
                    <div class="h-11 w-11 rounded-xl bg-ceeac-light flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-ceeac-bright" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $stat['icon'] }}"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-2xl font-bold tabular-nums text-ceeac-primary leading-none">
                            {{ $stat['number'] }}<span class="text-ceeac-bright text-xl">{{ $stat['suffix'] }}</span>
                        </div>
                        <div class="text-xs text-gray-500 font-medium mt-0.5">{{ $stat['label'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════
     FONCTIONNALITÉS
═══════════════════════════════════════════════════════════ --}}
<section id="fonctionnalites" class="py-24 bg-gray-50/70">
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="max-w-3xl mb-16 section-fade">
            <span class="inline-block text-xs font-bold uppercase tracking-widest text-ceeac-bright mb-3">Fonctionnalités</span>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-ceeac-primary tracking-tight">
                {{ __('landing.features.title') }}
            </h2>
            <p class="mt-4 text-gray-600 leading-relaxed text-base lg:text-lg">
                {{ __('landing.features.subtitle') }}
            </p>
        </div>

        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5">
            @php
            $featureIcons = [
                'engagements' => ['path' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'gradient' => 'from-blue-600 to-indigo-600'],
                'control'     => ['path' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'gradient' => 'from-ceeac-bright to-sky-500'],
                'workflow'    => ['path' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'gradient' => 'from-violet-600 to-purple-600'],
                'urgence'     => ['path' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'gradient' => 'from-red-500 to-rose-600'],
                'ordonnateur' => ['path' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'gradient' => 'from-amber-500 to-orange-500'],
                'audit'       => ['path' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', 'gradient' => 'from-teal-500 to-emerald-600'],
                'dashboards'  => ['path' => 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z', 'gradient' => 'from-ceeac-primary to-ceeac-bright'],
                'exports'     => ['path' => 'M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'gradient' => 'from-slate-600 to-gray-700'],
            ];
            @endphp

            @foreach (__('landing.features.items') as $key => $item)
                @php $icon = $featureIcons[$key] ?? ['path' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'gradient' => 'from-gray-500 to-gray-600']; @endphp
                <article class="section-fade group relative bg-white rounded-2xl p-6 border border-gray-100 shadow-card
                                hover:shadow-hover hover:-translate-y-1 hover:border-transparent
                                transition-all duration-300 cursor-default overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-br from-white via-white to-blue-50/30 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative">
                        <div class="h-12 w-12 rounded-xl bg-gradient-to-br {{ $icon['gradient'] }} flex items-center justify-center mb-5 shadow-sm group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $icon['path'] }}"/>
                            </svg>
                        </div>
                        <h3 class="font-semibold text-gray-900 text-[15px] mb-2">{{ $item['title'] }}</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">{{ $item['desc'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════
     MODULES
═══════════════════════════════════════════════════════════ --}}
<section id="modules" class="py-24 bg-white">
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="max-w-3xl mb-16 section-fade">
            <span class="inline-block text-xs font-bold uppercase tracking-widest text-ceeac-bright mb-3">Architecture</span>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-ceeac-primary tracking-tight">
                {{ __('landing.modules.title') }}
            </h2>
            <p class="mt-4 text-gray-600 text-base lg:text-lg leading-relaxed">{{ __('landing.modules.subtitle') }}</p>
        </div>

        @php
        $modules = [
            ['key' => 'budget',      'gradient' => 'from-ceeac-primary to-blue-900',   'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 10v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'desc' => 'Structuration des enveloppes par exercice, rattachement aux directions et natures de dépense.'],
            ['key' => 'engagements', 'gradient' => 'from-ceeac-bright to-indigo-700',  'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'desc' => 'Dossiers numériques, montants, statuts de validation et chaîne d\'exécution complète.'],
            ['key' => 'tiers',       'gradient' => 'from-sky-500 to-cyan-700',         'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'desc' => 'Référentiels fournisseurs et agents pour sécuriser les paiements et la conformité KYC.'],
            ['key' => 'workflow',    'gradient' => 'from-violet-600 to-purple-800',    'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'desc' => 'Étapes hiérarchiques, motifs de retour et décisions entièrement tracées par acteur.'],
            ['key' => 'audit',       'gradient' => 'from-emerald-600 to-teal-800',     'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'desc' => 'Contrôles automatiques, anomalies et scores de risque avec alertes en temps réel.'],
            ['key' => 'reporting',   'gradient' => 'from-amber-500 to-orange-700',     'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'desc' => 'Rapports d\'exécution, exports PDF/Excel et journal des opérations budgétaires.'],
        ];
        @endphp

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($modules as $mod)
                <div class="section-fade group relative rounded-2xl border border-gray-100 shadow-card
                            hover:shadow-hover hover:-translate-y-1 transition-all duration-300 overflow-hidden bg-white">
                    <div class="h-1.5 bg-gradient-to-r {{ $mod['gradient'] }}"></div>
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div class="h-12 w-12 rounded-xl bg-gradient-to-br {{ $mod['gradient'] }} flex items-center justify-center shrink-0 shadow-sm group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $mod['icon'] }}"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 text-[15px]">{{ __('landing.modules.'.$mod['key']) }}</h3>
                                <p class="mt-2 text-sm text-gray-500 leading-relaxed">{{ $mod['desc'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════
     WORKFLOW STEPS
═══════════════════════════════════════════════════════════ --}}
<section class="py-24 bg-gradient-to-b from-gray-50 to-white border-y border-gray-100">
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="max-w-3xl mb-16 section-fade">
            <span class="inline-block text-xs font-bold uppercase tracking-widest text-ceeac-bright mb-3">Processus</span>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-ceeac-primary tracking-tight">
                {{ __('landing.workflow.title') }}
            </h2>
            <p class="mt-4 text-gray-600 text-base lg:text-lg leading-relaxed">{{ __('landing.workflow.subtitle') }}</p>
        </div>

        @php
        $stepDetails = [
            ['color' => 'bg-blue-600',   'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
            ['color' => 'bg-violet-600', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['color' => 'bg-amber-500',  'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            ['color' => 'bg-emerald-600','icon' => 'M5 13l4 4L19 7'],
            ['color' => 'bg-sky-500',    'icon' => 'M9 5l7 7-7 7'],
            ['color' => 'bg-teal-600',   'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
        ];
        $steps = __('landing.workflow.steps');
        @endphp

        {{-- Desktop stepper --}}
        <div class="hidden lg:flex items-center section-fade">
            @foreach ($steps as $i => $step)
                <div class="flex-1 flex flex-col items-center gap-3 relative">
                    {{-- Connector line --}}
                    @if ($i < count($steps) - 1)
                        <div class="absolute top-6 left-1/2 w-full h-0.5 bg-gradient-to-r from-gray-200 to-gray-200 z-0"></div>
                        <div class="absolute top-6 left-1/2 w-1/2 h-0.5 bg-gradient-to-r {{ $stepDetails[$i]['color'] }} to-{{ $stepDetails[$i+1]['color'] ?? 'gray-200' }} z-0 opacity-30"></div>
                    @endif

                    {{-- Step dot --}}
                    <div class="relative z-10 h-12 w-12 rounded-full {{ $stepDetails[$i]['color'] }} flex items-center justify-center shadow-btn ring-4 ring-white">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stepDetails[$i]['icon'] }}"/>
                        </svg>
                    </div>

                    {{-- Label --}}
                    <div class="text-center px-2">
                        <span class="block text-[10px] font-bold uppercase tracking-widest text-ceeac-bright mb-1">{{ str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="block text-sm font-semibold text-gray-900">{{ $step }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Mobile stepper --}}
        <div class="lg:hidden space-y-4 section-fade">
            @foreach ($steps as $i => $step)
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-full {{ $stepDetails[$i]['color'] }} flex items-center justify-center shrink-0 shadow-sm ring-2 ring-white">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stepDetails[$i]['icon'] }}"/>
                        </svg>
                    </div>
                    <div class="flex-1 rounded-xl bg-white border border-gray-100 px-4 py-3 shadow-card">
                        <span class="text-[10px] font-bold text-ceeac-bright uppercase tracking-widest">Étape {{ str_pad((string)($i+1), 2, '0', STR_PAD_LEFT) }}</span>
                        <p class="font-semibold text-gray-900 mt-0.5 text-sm">{{ $step }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════
     DASHBOARD PREVIEW (RAPPORTS)
═══════════════════════════════════════════════════════════ --}}
<section id="rapports" class="py-24 bg-white">
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-14 xl:gap-20 items-center">

        {{-- Left: copy + alerts --}}
        <div id="dashboard-demo" class="section-fade">
            <span class="inline-block text-xs font-bold uppercase tracking-widest text-ceeac-bright mb-3">Pilotage</span>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-ceeac-primary tracking-tight">
                {{ __('landing.dashboard_preview.title') }}
            </h2>
            <p class="mt-4 text-gray-600 text-base lg:text-lg leading-relaxed max-w-xl">
                {{ __('landing.dashboard_preview.subtitle') }}
            </p>

            <ul class="mt-10 space-y-5">
                <li class="flex gap-4 rounded-2xl bg-red-50 border border-red-100 p-5">
                    <div class="h-10 w-10 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-red-800 text-sm">{{ __('landing.dashboard_preview.alert_title') }}</p>
                        <p class="mt-1 text-sm text-red-600">{{ __('landing.dashboard_preview.alert_1') }}</p>
                    </div>
                </li>
                <li class="flex gap-4 rounded-2xl bg-amber-50 border border-amber-100 p-5">
                    <div class="h-10 w-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-amber-800 text-sm">{{ __('landing.dashboard_preview.alert_2') }}</p>
                        <p class="mt-1 text-xs text-amber-600">{{ __('landing.dashboard_preview.mock_notice') }}</p>
                    </div>
                </li>
                <li class="flex gap-4 rounded-2xl bg-emerald-50 border border-emerald-100 p-5">
                    <div class="h-10 w-10 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-emerald-800 text-sm">Exports PDF & Excel disponibles</p>
                        <p class="mt-1 text-xs text-emerald-600">{{ __('landing.features.items.exports.desc') }}</p>
                    </div>
                </li>
            </ul>
        </div>

        {{-- Right: mock report card --}}
        <div class="section-fade">
            <div class="gradient-border rounded-2xl shadow-hover overflow-hidden bg-white">
                <div class="px-6 py-4 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-ceeac-primary">Pilotage budgétaire</p>
                        <p class="text-xs text-gray-400 mt-0.5">Vue agrégée — Exercice {{ date('Y') }}</p>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-xs bg-blue-100 text-blue-700 px-2.5 py-1 rounded-lg font-semibold">PDF</span>
                        <span class="text-xs bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-lg font-semibold">Excel</span>
                    </div>
                </div>

                <div class="p-6 space-y-5">
                    @foreach ([
                        ['label' => 'Budget révisé',  'value' => '42,8 Mrd XAF', 'pct' => 100, 'bar_color' => 'bg-ceeac-primary'],
                        ['label' => 'Engagé',         'value' => '28,1 Mrd XAF', 'pct' => 66,  'bar_color' => 'bg-ceeac-bright'],
                        ['label' => 'Liquidé',        'value' => '22,6 Mrd XAF', 'pct' => 53,  'bar_color' => 'bg-violet-500'],
                        ['label' => 'Payé',           'value' => '19,4 Mrd XAF', 'pct' => 45,  'bar_color' => 'bg-emerald-500'],
                        ['label' => 'Disponible',     'value' => '14,7 Mrd XAF', 'pct' => 34,  'bar_color' => 'bg-amber-400'],
                    ] as $row)
                        <div class="space-y-1.5">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-medium text-gray-600">{{ $row['label'] }}</span>
                                <span class="text-xs font-bold tabular-nums text-gray-900">{{ $row['value'] }}</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill {{ $row['bar_color'] }}" style="width: {{ $row['pct'] }}%"></div>
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-400">
                        <span>Taux d'exécution global : <strong class="text-emerald-600 font-bold">65,7%</strong></span>
                        <span class="flex items-center gap-1">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Temps réel
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════
     CONFORMITÉ & SÉCURITÉ
═══════════════════════════════════════════════════════════ --}}
<section id="conformite" class="py-24 bg-ceeac-gradient text-white relative overflow-hidden">
    <div class="absolute inset-0 dot-grid pointer-events-none opacity-50"></div>
    <div class="absolute -bottom-20 -right-20 w-96 h-96 rounded-full bg-white/5 blur-3xl pointer-events-none"></div>

    <div class="relative max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mb-14 section-fade">
            <span class="inline-block text-xs font-bold uppercase tracking-widest text-ceeac-sky mb-3">Conformité</span>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight">{{ __('landing.compliance.title') }}</h2>
            <p class="mt-4 text-blue-100/90 text-base lg:text-lg leading-relaxed max-w-2xl">{{ __('landing.compliance.text') }}</p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 section-fade">
            @php
            $complianceIcons = [
                'worldbank' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'intosai'   => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                'coso'      => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                'owasp'     => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
            ];
            @endphp

            @foreach (__('landing.compliance.badges') as $key => $label)
                <div class="rounded-2xl bg-white/10 backdrop-blur-sm ring-1 ring-white/20 p-6 hover:bg-white/15 transition-colors duration-200">
                    <div class="h-10 w-10 rounded-xl bg-white/15 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $complianceIcons[$key] ?? 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' }}"/>
                        </svg>
                    </div>
                    <p class="font-semibold text-white text-sm leading-snug">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════
     BÉNÉFICES
═══════════════════════════════════════════════════════════ --}}
<section class="py-24 bg-gray-50/70">
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="max-w-3xl mb-16 section-fade">
            <span class="inline-block text-xs font-bold uppercase tracking-widest text-ceeac-bright mb-3">Avantages</span>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-ceeac-primary tracking-tight">
                {{ __('landing.benefits.title') }}
            </h2>
            <p class="mt-4 text-gray-600 text-base lg:text-lg max-w-2xl leading-relaxed">{{ __('landing.benefits.subtitle') }}</p>
        </div>

        <ul class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 section-fade">
            @foreach (__('landing.benefits.items') as $text)
                <li class="flex gap-4 rounded-2xl bg-white p-6 border border-gray-100 shadow-card
                            hover:shadow-hover hover:-translate-y-0.5 transition-all duration-200">
                    <div class="h-8 w-8 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <span class="text-gray-700 text-sm leading-relaxed">{{ $text }}</span>
                </li>
            @endforeach
        </ul>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════
     CTA FINAL
═══════════════════════════════════════════════════════════ --}}
<section class="py-20 relative overflow-hidden bg-ceeac-hero text-white">
    <div class="absolute inset-0 dot-grid pointer-events-none opacity-40"></div>
    <div class="absolute -top-16 right-16 w-72 h-72 rounded-full bg-ceeac-bright/25 blur-3xl pointer-events-none"></div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center section-fade">
        <span class="inline-block text-xs font-bold uppercase tracking-widest text-ceeac-sky mb-5">Commencer</span>
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight leading-tight text-balance">
            {{ __('landing.cta_final.title') }}
        </h2>
        <p class="mt-5 text-blue-100/90 text-base lg:text-lg max-w-xl mx-auto leading-relaxed">
            Rejoignez la Commission de la CEEAC dans la transformation numérique de sa gestion budgétaire.
        </p>
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-3.5 font-semibold text-ceeac-primary shadow-float hover:bg-gray-50 hover:-translate-y-0.5 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    {{ __('landing.cta_final.button') }}
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-3.5 font-semibold text-ceeac-primary shadow-float hover:bg-gray-50 hover:-translate-y-0.5 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    {{ __('landing.cta_final.button') }}
                </a>
                <a href="#fonctionnalites" class="inline-flex items-center gap-2 rounded-xl border border-white/30 px-8 py-3.5 font-semibold text-white hover:bg-white/10 backdrop-blur-sm hover:-translate-y-0.5 transition-all">
                    Découvrir les fonctionnalités
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </a>
            @endauth
        </div>
    </div>
</section>


{{-- ═══════════════════════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════════════════════ --}}
<footer id="contact" class="bg-ceeac-deep text-gray-300">
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-8">

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">

            {{-- Brand column --}}
            <div class="lg:col-span-2">
                <x-brand-logo variant="light" class="mb-5" />
                <p class="text-sm leading-relaxed text-gray-400 max-w-sm">
                    {{ __('landing.footer.product') }}
                </p>
                <div class="mt-6 flex items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-white/10 ring-1 ring-white/15 text-gray-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Système opérationnel
                    </span>
                    <span class="text-xs text-gray-500">v2.0 — {{ date('Y') }}</span>
                </div>
            </div>

            {{-- Quick links --}}
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500 mb-5">{{ __('landing.footer.quick_links') }}</p>
                <ul class="space-y-3 text-sm">
                    <li><a href="#fonctionnalites" class="text-gray-400 hover:text-white transition-colors">{{ __('landing.nav.features') }}</a></li>
                    <li><a href="#modules"         class="text-gray-400 hover:text-white transition-colors">{{ __('landing.nav.modules') }}</a></li>
                    <li><a href="#rapports"        class="text-gray-400 hover:text-white transition-colors">{{ __('landing.nav.reports') }}</a></li>
                    <li><a href="#conformite"      class="text-gray-400 hover:text-white transition-colors">{{ __('landing.nav.security') }}</a></li>
                    @guest
                        <li class="pt-1"><a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-ceeac-sky hover:text-white font-medium transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            {{ __('landing.nav.login') }}
                        </a></li>
                    @endguest
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-500 mb-5">{{ __('landing.footer.contact') }}</p>
                <p class="text-sm text-gray-400 leading-relaxed mb-3">{{ __('landing.contact.placeholder_org') }}</p>
                <a href="mailto:{{ __('landing.contact.placeholder_email') }}"
                   class="inline-flex items-center gap-2 text-sm font-medium text-ceeac-sky hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    {{ __('landing.contact.placeholder_email') }}
                </a>

                <details class="mt-8 group">
                    <summary class="cursor-pointer text-sm text-gray-500 hover:text-gray-300 list-none flex items-center gap-2 transition-colors">
                        <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        {{ __('landing.footer.legal') }}
                    </summary>
                    <p class="mt-4 text-xs text-gray-500 leading-relaxed border-l-2 border-ceeac-bright/40 pl-4">
                        Les données traitées dans BUDGET-ENGAGE relèvent du secret professionnel et des politiques de sécurité de la Commission. L'accès est strictement nominatif et journalisé. Pour toute demande relative à la protection des données, adressez-vous au DPO de la CEEAC.
                    </p>
                </details>
            </div>
        </div>

        {{-- Footer bottom --}}
        <div class="border-t border-white/10 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500">
            <p>© {{ date('Y') }} — {{ __('landing.footer.copyright') }}</p>
            <div class="flex items-center gap-6">
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    TLS 1.3
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    OWASP Compliant
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    Laravel 11
                </span>
            </div>
        </div>
    </div>
</footer>

@endsection
