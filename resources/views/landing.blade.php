@extends('layouts.landing')

@section('content')
    {{-- Header fixe --}}
    <header class="sticky top-0 z-50 border-b border-gray-200/80 bg-white/95 backdrop-blur-md shadow-card">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16 lg:h-[4.25rem]">
            <a href="{{ route('landing') }}" id="accueil" class="flex items-center gap-2 group">
                <x-brand-logo />
            </a>
            <nav class="hidden lg:flex items-center gap-8 text-sm font-medium text-gray-600">
                <a href="#accueil" class="hover:text-ceeac-bright transition-colors">{{ __('landing.nav.home') }}</a>
                <a href="#fonctionnalites" class="hover:text-ceeac-bright transition-colors">{{ __('landing.nav.features') }}</a>
                <a href="#modules" class="hover:text-ceeac-bright transition-colors">{{ __('landing.nav.modules') }}</a>
                <a href="#rapports" class="hover:text-ceeac-bright transition-colors">{{ __('landing.nav.reports') }}</a>
                <a href="#conformite" class="hover:text-ceeac-bright transition-colors">{{ __('landing.nav.security') }}</a>
                <a href="#contact" class="hover:text-ceeac-bright transition-colors">{{ __('landing.nav.contact') }}</a>
            </nav>
            <div class="flex items-center gap-2 sm:gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-institutional px-4 py-2 text-sm font-semibold text-white bg-ceeac-primary hover:bg-ceeac-bright shadow-soft transition-colors">
                        {{ __('dashboard.title') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center justify-center rounded-institutional px-4 py-2 text-sm font-semibold text-ceeac-primary border border-ceeac-primary/30 hover:bg-ceeac-muted transition-colors">
                        {{ __('landing.nav.login') }}
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-institutional px-4 py-2 text-sm font-semibold text-white bg-ceeac-primary hover:bg-ceeac-bright shadow-soft transition-colors">
                        {{ __('landing.nav.secure_access') }}
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-ceeac-primary via-[#1e40af] to-ceeac-bright text-white">
        <div class="absolute inset-0 opacity-[0.07] pointer-events-none" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 28px 28px;"></div>
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 grid lg:grid-cols-2 gap-12 lg:gap-16 items-center relative">
            <div class="space-y-6">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1 text-xs font-semibold uppercase tracking-wider ring-1 ring-white/20">
                    {{ __('landing.hero.badge') }}
                </p>
                <h1 class="text-3xl sm:text-4xl lg:text-[2.35rem] font-bold leading-tight tracking-tight">
                    {{ __('landing.hero.title') }}
                </h1>
                <p class="text-lg text-blue-100 max-w-xl leading-relaxed">
                    {{ __('landing.hero.subtitle') }}
                </p>
                <div class="flex flex-wrap gap-4 pt-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-institutional px-6 py-3 font-semibold bg-white text-ceeac-primary hover:bg-gray-100 shadow-soft transition-colors">
                            {{ __('landing.hero.cta_primary') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-institutional px-6 py-3 font-semibold bg-white text-ceeac-primary hover:bg-gray-100 shadow-soft transition-colors">
                            {{ __('landing.hero.cta_primary') }}
                        </a>
                    @endauth
                    <a href="#dashboard-demo" class="inline-flex items-center justify-center rounded-institutional px-6 py-3 font-semibold border border-white/40 text-white hover:bg-white/10 transition-colors">
                        {{ __('landing.hero.cta_demo') }}
                    </a>
                </div>
                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-6 border-t border-white/15">
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-blue-200">{{ __('landing.hero.stats_budget') }}</dt>
                        <dd class="mt-1 font-semibold text-white">360° sur les crédits</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-blue-200">{{ __('landing.hero.stats_control') }}</dt>
                        <dd class="mt-1 font-semibold text-white">Validation hiérarchique</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-wide text-blue-200">{{ __('landing.hero.stats_audit') }}</dt>
                        <dd class="mt-1 font-semibold text-white">Activity Log budgétaire</dd>
                    </div>
                </dl>
            </div>
            {{-- Illustration dashboard --}}
            <div class="relative rounded-institutional bg-white/10 backdrop-blur-sm ring-1 ring-white/20 p-6 shadow-soft lg:translate-y-2">
                <div class="rounded-lg bg-white text-gray-900 p-5 shadow-card space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-ceeac-primary">Synthèse exercice</span>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-ceeac-success/15 text-ceeac-success">Temps réel</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-ceeac-muted p-3">
                            <div class="text-xs text-gray-500">{{ __('landing.dashboard_preview.kpi_budget') }}</div>
                            <div class="text-lg font-bold tabular-nums text-ceeac-primary">42,8 Mrd</div>
                        </div>
                        <div class="rounded-lg bg-ceeac-muted p-3">
                            <div class="text-xs text-gray-500">{{ __('landing.dashboard_preview.kpi_engage') }}</div>
                            <div class="text-lg font-bold tabular-nums text-ceeac-bright">28,1 Mrd</div>
                        </div>
                        <div class="rounded-lg bg-ceeac-muted p-3">
                            <div class="text-xs text-gray-500">{{ __('landing.dashboard_preview.kpi_paye') }}</div>
                            <div class="text-lg font-bold tabular-nums text-ceeac-success">19,4 Mrd</div>
                        </div>
                        <div class="rounded-lg bg-ceeac-muted p-3">
                            <div class="text-xs text-gray-500">{{ __('landing.dashboard_preview.kpi_dispo') }}</div>
                            <div class="text-lg font-bold tabular-nums text-ceeac-warning">14,7 Mrd</div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs text-gray-500"><span>{{ __('landing.dashboard_preview.chart_legend') }}</span></div>
                        <div class="flex items-end gap-1 h-24 px-1">
                            @foreach ([40, 65, 45, 88, 52, 72] as $h)
                                <div class="flex-1 rounded-t bg-gradient-to-t from-ceeac-primary to-ceeac-sky opacity-90" style="height: {{ $h }}%"></div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Fonctionnalités --}}
    <section id="fonctionnalites" class="py-20 bg-ceeac-muted/60">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-14">
                <h2 class="text-2xl sm:text-3xl font-bold text-ceeac-primary">{{ __('landing.features.title') }}</h2>
                <p class="mt-3 text-gray-600 leading-relaxed">{{ __('landing.features.subtitle') }}</p>
            </div>
            <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6">
                @foreach (__('landing.features.items') as $key => $item)
                    <article class="rounded-institutional bg-white p-6 shadow-card border border-gray-100 hover:border-ceeac-sky/40 hover:shadow-soft transition-all">
                        <div class="h-10 w-10 rounded-lg bg-ceeac-primary/10 text-ceeac-primary flex items-center justify-center mb-4">
                            @switch($key)
                                @case('engagements')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    @break
                                @case('control')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    @break
                                @case('workflow')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    @break
                                @case('urgence')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    @break
                                @case('ordonnateur')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    @break
                                @case('audit')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    @break
                                @case('dashboards')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                                    @break
                                @default
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            @endswitch
                        </div>
                        <h3 class="font-semibold text-gray-900">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ $item['desc'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Modules --}}
    <section id="modules" class="py-20 bg-white">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-12">
                <h2 class="text-2xl sm:text-3xl font-bold text-ceeac-primary">{{ __('landing.modules.title') }}</h2>
                <p class="mt-3 text-gray-600">{{ __('landing.modules.subtitle') }}</p>
            </div>
            @php($mods = [
                ['key' => 'budget', 'color' => 'from-ceeac-primary to-blue-900'],
                ['key' => 'engagements', 'color' => 'from-ceeac-bright to-indigo-700'],
                ['key' => 'tiers', 'color' => 'from-sky-500 to-ceeac-primary'],
                ['key' => 'workflow', 'color' => 'from-violet-600 to-ceeac-primary'],
                ['key' => 'audit', 'color' => 'from-emerald-600 to-teal-800'],
                ['key' => 'reporting', 'color' => 'from-amber-500 to-orange-700'],
            ])
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($mods as $mod)
                    <div class="group relative overflow-hidden rounded-institutional border border-gray-200 shadow-card hover:shadow-soft transition-shadow">
                        <div class="h-2 bg-gradient-to-r {{ $mod['color'] }}"></div>
                        <div class="p-6">
                            <h3 class="font-semibold text-lg text-gray-900">{{ __('landing.modules.'.$mod['key']) }}</h3>
                            <p class="mt-2 text-sm text-gray-600 leading-relaxed">
                                @switch($mod['key'])
                                    @case('budget')
                                        Structuration des enveloppes par exercice, rattachement aux directions et natures de dépense.
                                        @break
                                    @case('engagements')
                                        Dossiers numériques, montants, statuts de validation et chaîne d'exécution.
                                        @break
                                    @case('tiers')
                                        Référentiels fournisseurs et agents pour sécuriser les paiements.
                                        @break
                                    @case('workflow')
                                        Étapes hiérarchiques, motifs de retour et décisions tracées.
                                        @break
                                    @case('audit')
                                        Contrôles automatiques, anomalies et scores de risque.
                                        @break
                                    @case('reporting')
                                        Rapports d'exécution, exports et journal des opérations budgétaires.
                                        @break
                                @endswitch
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Workflow --}}
    <section class="py-20 bg-ceeac-muted/60 border-y border-gray-200/80">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-12">
                <h2 class="text-2xl sm:text-3xl font-bold text-ceeac-primary">{{ __('landing.workflow.title') }}</h2>
                <p class="mt-3 text-gray-600">{{ __('landing.workflow.subtitle') }}</p>
            </div>
            <div class="flex flex-wrap lg:flex-nowrap items-stretch gap-3 lg:gap-0">
                @foreach (__('landing.workflow.steps') as $i => $step)
                    <div class="flex items-center flex-1 min-w-[140px] lg:min-w-0">
                        <div class="flex-1 rounded-institutional bg-white border border-gray-200 px-4 py-4 text-center shadow-card">
                            <span class="text-xs font-bold text-ceeac-bright">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <p class="mt-1 font-semibold text-gray-900 text-sm">{{ $step }}</p>
                        </div>
                        @if($i < count(__('landing.workflow.steps')) - 1)
                            <div class="hidden lg:flex shrink-0 w-8 items-center justify-center text-ceeac-sky">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Rapports / Dashboard demo anchor --}}
    <section id="rapports" class="py-20 bg-white">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-14 items-start">
            <div id="dashboard-demo">
                <h2 class="text-2xl sm:text-3xl font-bold text-ceeac-primary">{{ __('landing.dashboard_preview.title') }}</h2>
                <p class="mt-3 text-gray-600 leading-relaxed">{{ __('landing.dashboard_preview.subtitle') }}</p>
                <ul class="mt-8 space-y-4">
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 rounded-full bg-ceeac-danger shrink-0"></span>
                        <div>
                            <p class="font-medium text-gray-900">{{ __('landing.dashboard_preview.alert_title') }}</p>
                            <p class="text-sm text-gray-600">{{ __('landing.dashboard_preview.alert_1') }}</p>
                        </div>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 rounded-full bg-ceeac-warning shrink-0"></span>
                        <div>
                            <p class="font-medium text-gray-900">{{ __('landing.dashboard_preview.alert_2') }}</p>
                            <p class="text-sm text-gray-500">{{ __('landing.dashboard_preview.mock_notice') }}</p>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="rounded-institutional bg-gradient-to-br from-gray-50 to-white border border-gray-200 p-8 shadow-soft">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <p class="text-sm font-semibold text-ceeac-primary">Pilotage budgétaire</p>
                        <p class="text-xs text-gray-500 mt-1">{{ __('landing.nav.reports') }} — vue agrégée</p>
                    </div>
                    <span class="text-xs bg-ceeac-muted px-2 py-1 rounded-md font-medium text-gray-600">PDF · Excel</span>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-8">
                    @foreach (['kpi_budget' => ['w' => '34%', 'v' => '42,8 Mrd'], 'kpi_engage' => ['w' => '66%', 'v' => '28,1 Mrd'], 'kpi_paye' => ['w' => '45%', 'v' => '19,4 Mrd'], 'kpi_dispo' => ['w' => '22%', 'v' => '14,7 Mrd']] as $k => $bar)
                        <div class="rounded-lg bg-white border border-gray-100 p-4">
                            <div class="text-xs text-gray-500">{{ __('landing.dashboard_preview.'.$k) }}</div>
                            <div class="mt-2 h-2 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full rounded-full bg-ceeac-bright" style="width: {{ $bar['w'] }}"></div>
                            </div>
                            <div class="mt-2 text-lg font-bold tabular-nums text-gray-900">{{ $bar['v'] }}</div>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500">{{ __('landing.features.items.exports.desc') }}</p>
            </div>
        </div>
    </section>

    {{-- Conformité --}}
    <section id="conformite" class="py-20 bg-ceeac-primary text-white">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl sm:text-3xl font-bold">{{ __('landing.compliance.title') }}</h2>
            <p class="mt-4 max-w-3xl text-blue-100 leading-relaxed">{{ __('landing.compliance.text') }}</p>
            <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach (__('landing.compliance.badges') as $label)
                    <div class="rounded-institutional bg-white/10 backdrop-blur-sm ring-1 ring-white/20 px-5 py-6">
                        <p class="font-semibold leading-snug">{{ $label }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Avantages --}}
    <section class="py-20 bg-ceeac-muted/40">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-ceeac-primary">{{ __('landing.benefits.title') }}</h2>
            <p class="mt-3 text-gray-600 max-w-3xl">{{ __('landing.benefits.subtitle') }}</p>
            <ul class="mt-10 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach (__('landing.benefits.items') as $text)
                    <li class="flex gap-3 rounded-institutional bg-white p-5 border border-gray-100 shadow-card">
                        <svg class="w-6 h-6 shrink-0 text-ceeac-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-gray-700 leading-relaxed">{{ $text }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- CTA final --}}
    <section class="py-16 bg-gradient-to-r from-ceeac-bright to-ceeac-primary text-white">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-8 text-center sm:text-left">
            <h2 class="text-xl sm:text-2xl font-bold max-w-xl">{{ __('landing.cta_final.title') }}</h2>
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex whitespace-nowrap rounded-institutional bg-white px-8 py-3 font-semibold text-ceeac-primary shadow-soft hover:bg-gray-100 transition-colors">
                    {{ __('landing.cta_final.button') }}
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex whitespace-nowrap rounded-institutional bg-white px-8 py-3 font-semibold text-ceeac-primary shadow-soft hover:bg-gray-100 transition-colors">
                    {{ __('landing.cta_final.button') }}
                </a>
            @endauth
        </div>
    </section>

    {{-- Footer --}}
    <footer id="contact" class="bg-gray-900 text-gray-300 py-16">
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 grid md:grid-cols-3 gap-12">
            <div>
                <x-brand-logo variant="light" class="text-white" />
                <p class="mt-6 text-sm leading-relaxed text-gray-400">{{ __('landing.footer.product') }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('landing.footer.quick_links') }}</p>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="#fonctionnalites" class="hover:text-white transition-colors">{{ __('landing.nav.features') }}</a></li>
                    <li><a href="#modules" class="hover:text-white transition-colors">{{ __('landing.nav.modules') }}</a></li>
                    <li><a href="#conformite" class="hover:text-white transition-colors">{{ __('landing.nav.security') }}</a></li>
                    @guest
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">{{ __('landing.nav.login') }}</a></li>
                    @endguest
                </ul>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('landing.footer.contact') }}</p>
                <p class="mt-4 text-sm text-gray-400">{{ __('landing.contact.placeholder_org') }}</p>
                <a href="mailto:budget-support@ceeac.int" class="mt-2 inline-block text-sm font-medium text-ceeac-sky hover:text-white transition-colors">{{ __('landing.contact.placeholder_email') }}</a>
                <details class="mt-8 group">
                    <summary class="cursor-pointer text-sm font-medium text-gray-400 hover:text-white list-none flex items-center gap-2">
                        <span>{{ __('landing.footer.legal') }}</span>
                        <svg class="w-4 h-4 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <p class="mt-4 text-xs text-gray-500 leading-relaxed border-l-2 border-ceeac-primary pl-4">
                        Les données traitées dans BUDGET-ENGAGE relèvent du secret professionnel et des politiques de sécurité de la Commission. L'accès est strictement nominatif et journalisé. Pour toute demande relative à la protection des données, adressez-vous au délégué à la protection des données de la CEEAC.
                    </p>
                </details>
            </div>
        </div>
        <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 pt-8 border-t border-gray-800 text-center text-xs text-gray-500">
            © {{ date('Y') }} — {{ __('landing.footer.copyright') }}
        </div>
    </footer>
@endsection
