<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name', 'BUDGET-ENGAGE') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-ceeac-ink">

{{-- ══════════════════════════════════════════════════════
     APP SHELL — Alpine x-data sur body
     ══════════════════════════════════════════════════════ --}}
<div x-data="{
        sidebarOpen: true,
        mobileSidebar: false,
        init() {
            if (window.innerWidth < 1024) { this.sidebarOpen = false; }
            window.addEventListener('resize', () => {
                if (window.innerWidth < 1024) { this.sidebarOpen = false; }
                else { this.mobileSidebar = false; }
            });
        }
     }"
     class="min-h-screen flex">

    {{-- ── Overlay mobile ── --}}
    <div x-show="mobileSidebar"
         x-transition:enter="transition-opacity duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileSidebar = false"
         class="fixed inset-0 z-40 bg-ceeac-deep/70 backdrop-blur-sm lg:hidden"
         style="display:none;"></div>

    {{-- ══ SIDEBAR ════════════════════════════════════════ --}}
    <aside id="sidebar"
           :class="{
               'w-64': sidebarOpen || mobileSidebar,
               'w-0 lg:w-[4.5rem]': !sidebarOpen && !mobileSidebar
           }"
           class="fixed inset-y-0 left-0 z-50 flex flex-col
                  bg-sidebar-gradient text-white
                  shadow-sidebar overflow-hidden
                  transition-all duration-300 ease-in-out">

        {{-- Logo ── --}}
        <div class="flex items-center gap-3 px-4 h-16 shrink-0 border-b border-white/10">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0">
                <div class="shrink-0 h-9 w-9 rounded-xl bg-white/15 ring-2 ring-white/20 flex items-center justify-center overflow-hidden">
                    <img src="{{ asset('images/logo-ceeac.png') }}"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                         alt="CEEAC" class="h-8 w-8 object-contain">
                    <span style="display:none" class="text-white font-black text-xs">CE</span>
                </div>
                <div x-show="sidebarOpen || mobileSidebar"
                     x-transition:enter="transition-all duration-200 delay-100"
                     x-transition:enter-start="opacity-0 translate-x-2"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     class="min-w-0 leading-tight">
                    <p class="text-sm font-bold text-white truncate">BUDGET-ENGAGE</p>
                    <p class="text-[10px] text-blue-300/70 truncate">Commission CEEAC</p>
                </div>
            </a>
        </div>

        {{-- Nav ── --}}
        <nav class="flex-1 overflow-y-auto overflow-x-hidden py-4 px-2.5 space-y-0.5 scrollbar-hide">

            {{-- Pilotage --}}
            <div x-show="sidebarOpen || mobileSidebar" class="sidebar-section-label">Pilotage</div>
            <a href="{{ route('dashboard') }}"
               class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Tableau de bord</span>
            </a>

            @if(auth()->user()->can('reports.view') || auth()->user()->can('reports.view_limited') || auth()->user()->can('exercises.manage') || auth()->user()->can('audit.view'))
            @if(auth()->user()->can('reports.view') || auth()->user()->can('reports.view_limited') || auth()->user()->can('exercises.manage'))
            <a href="{{ route('reports.execution.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('reports.execution.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Rapport d'ex&eacute;cution</span>
            </a>
            @endif
            @if(auth()->user()->can('audit.view') || auth()->user()->can('reports.view') || auth()->user()->can('exercises.manage'))
            <a href="{{ route('reports.activity.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('reports.activity.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Journal op&eacute;rations</span>
            </a>
            @endif
            @endif
            @canany(['audit.view', 'audit.alerts.manage'])
            <a href="{{ route('audit.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('audit.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Audit &amp; Conformit&eacute;</span>
            </a>
            @endcanany

            {{-- Préparation budgétaire --}}
            @can('viewAny', App\Models\ExpressionBesoin::class)
            <div x-show="sidebarOpen || mobileSidebar" class="sidebar-section-label">Pr&eacute;paration budg&eacute;taire</div>
            <a href="{{ route('expression-besoins.dashboard') }}"
               class="sidebar-nav-item {{ request()->routeIs('expression-besoins.dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Tableau de bord</span>
            </a>
            <a href="{{ route('expression-besoins.besoins.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('expression-besoins.besoins.*') && ! request()->routeIs('expression-besoins.besoins.archives') && ! request()->routeIs('expression-besoins.besoins.create') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Besoins</span>
            </a>
            @can('create', App\Models\ExpressionBesoin::class)
            <a href="{{ route('expression-besoins.besoins.create') }}"
               class="sidebar-nav-item {{ request()->routeIs('expression-besoins.besoins.create') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M12 4v16m8-8H4"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Nouveau besoin</span>
            </a>
            @endcan
            <a href="{{ route('expression-besoins.consolidation') }}"
               class="sidebar-nav-item {{ request()->routeIs('expression-besoins.consolidation') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Consolidation</span>
            </a>
            <a href="{{ route('expression-besoins.rapprochement') }}"
               class="sidebar-nav-item {{ request()->routeIs('expression-besoins.rapprochement') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Rapprochement</span>
            </a>
            <a href="{{ route('expression-besoins.besoins.archives') }}"
               class="sidebar-nav-item {{ request()->routeIs('expression-besoins.besoins.archives') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Archives</span>
            </a>
            @endcan

            {{-- Budget & crédits --}}
            @can('exercises.manage')
            <div x-show="sidebarOpen || mobileSidebar" class="sidebar-section-label">Budget &amp; cr&eacute;dits</div>
            <a href="{{ route('budget.exercices.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('budget.exercices.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Exercices</span>
            </a>
            <a href="{{ route('budget.lignes.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('budget.lignes.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Lignes budg&eacute;taires</span>
            </a>
            @endcan
            {{-- Budget operations --}}
            @canany(['budget.view', 'budget.import.run', 'budget.replace', 'budget.restore'])
            <a href="{{ route('budget.nomenclature.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('budget.nomenclature.index') || request()->routeIs('budget.nomenclature.show') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Import budget</span>
            </a>
            <a href="{{ route('budget.nomenclature.synthese') }}"
               class="sidebar-nav-item {{ request()->routeIs('budget.nomenclature.synthese') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055zM20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Synth&egrave;se budg&eacute;taire</span>
            </a>
            <a href="{{ route('budget.situation.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('budget.situation.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Situation budg&eacute;taire</span>
            </a>
            <a href="{{ route('budget.nomenclature.consolide') }}"
               class="sidebar-nav-item {{ request()->routeIs('budget.nomenclature.consolide') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Consolid&eacute; PAP-Budget</span>
            </a>
            @endcanany
            @can('recettes.view')
            <a href="{{ route('recettes.dashboard') }}"
               class="sidebar-nav-item {{ request()->routeIs('recettes.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Recettes</span>
            </a>
            @endcan
            @canany(['budget.virement', 'budget.virement.approve'])
            <a href="{{ route('budget.virements.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('budget.virements.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Virements de cr&eacute;dits</span>
            </a>
            @endcanany
            @can('budget.alertes')
            <a href="{{ route('budget.alertes.historique') }}"
               class="sidebar-nav-item {{ request()->routeIs('budget.alertes.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Alertes budg&eacute;taires</span>
            </a>
            @endcan

            {{-- Exécution budgétaire --}}
            @if(auth()->user()->can('engagement.view') || auth()->user()->can('viewAny', App\Models\Engagement::class) || auth()->user()->can('viewAny', App\Models\EngagementType::class))
            <div x-show="sidebarOpen || mobileSidebar" class="sidebar-section-label">Ex&eacute;cution budg&eacute;taire</div>
            @endif
            @can('engagement.view')
            <a href="{{ route('engagements.dashboard') }}"
               class="sidebar-nav-item {{ request()->routeIs('engagements.dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Tableau de bord engagements</span>
            </a>
            @endcan
            @can('viewAny', App\Models\Engagement::class)
            <a href="{{ route('engagements.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('engagements.*') && ! request()->routeIs('engagements.dashboard') && ! request()->routeIs('engagements.types.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Engagements</span>
            </a>
            @endcan
            @can('viewAny', App\Models\EngagementType::class)
            <a href="{{ route('engagements.types.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('engagements.types.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Types d'engagement</span>
            </a>
            @endcan
            @can('viewAny', App\Models\Liquidation::class)
            <a href="{{ route('liquidations.dashboard') }}"
               class="sidebar-nav-item {{ request()->routeIs('liquidations.dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Pilotage liquidations</span>
            </a>
            <a href="{{ route('liquidations.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('liquidations.*') && ! request()->routeIs('liquidations.dashboard') && ! request()->routeIs('liquidations.reports.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 7h6m-6 4h6m-6 4h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Liquidations</span>
            </a>
            <a href="{{ route('liquidations.reports.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('liquidations.reports.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">États liquidations</span>
            </a>
            @endcan
            @can('viewAny', App\Models\Ordonnancement::class)
            <a href="{{ route('ordonnancements.dashboard') }}"
               class="sidebar-nav-item {{ request()->routeIs('ordonnancements.dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Pilotage ordonnancements</span>
            </a>
            <a href="{{ route('ordonnancements.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('ordonnancements.*') && ! request()->routeIs('ordonnancements.dashboard') && ! request()->routeIs('ordonnancements.reports.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Ordonnancements</span>
            </a>
            <a href="{{ route('ordonnancements.reports.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('ordonnancements.reports.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">États ordonnancements</span>
            </a>
            @endcan
            @can('viewAny', App\Models\Paiement::class)
            <a href="{{ route('paiements.dashboard') }}"
               class="sidebar-nav-item {{ request()->routeIs('paiements.dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Pilotage paiements</span>
            </a>
            <a href="{{ route('paiements.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('paiements.*') && ! request()->routeIs('paiements.dashboard') && ! request()->routeIs('paiements.reports.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2m-6-3h8m0 0l-3-3m3 3l-3 3"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Paiements</span>
            </a>
            <a href="{{ route('paiements.reports.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('paiements.reports.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">États paiements</span>
            </a>
            @endcan

            @can('referentials.manage')
            <div x-show="sidebarOpen || mobileSidebar" class="sidebar-section-label">R&eacute;f&eacute;rentiels</div>
            <a href="{{ route('referentials.departements.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('referentials.departements.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">D&eacute;partements</span>
            </a>
            <a href="{{ route('referentials.directions.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('referentials.directions.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Directions</span>
            </a>
            <a href="{{ route('referentials.services.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('referentials.services.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zm12 0a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Services</span>
            </a>
            <a href="{{ route('referentials.fournisseurs.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('referentials.fournisseurs.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Fournisseurs</span>
            </a>
            <a href="{{ route('referentials.agents.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('referentials.agents.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Agents</span>
            </a>
            <a href="{{ route('referentials.natures-depenses.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('referentials.natures-depenses.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Natures de d&eacute;pense</span>
            </a>
            <a href="{{ route('referentials.sources-financement.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('referentials.sources-financement.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Sources financement</span>
            </a>
            @endcan

            {{-- Performance & PAP --}}
            @if(auth()->user()->can('rbm.view') || auth()->user()->can('rbm.admin') || auth()->user()->can('rbm.create') || auth()->user()->can('rbm.update') || auth()->user()->can('pap.view') || auth()->user()->can('pap.import') || auth()->user()->can('pap.replace') || auth()->user()->can('pap.restore'))
            <div x-show="sidebarOpen || mobileSidebar" class="sidebar-section-label">Performance &amp; PAP</div>
            @endif
            @if(auth()->user()->can('rbm.view') || auth()->user()->can('rbm.admin') || auth()->user()->can('rbm.create') || auth()->user()->can('rbm.update'))
            <a href="{{ route('rbm.overview.tree') }}"
               class="sidebar-nav-item {{ request()->routeIs('rbm.overview.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M4 5a2 2 0 012-2h4a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM4 15a2 2 0 012-2h4a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Vue d'ensemble</span>
            </a>
            <a href="{{ route('rbm.piliers.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('rbm.piliers.*') ? 'active' : '' }}">
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Piliers</span>
            </a>
            <a href="{{ route('rbm.axes-strategiques.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('rbm.axes-strategiques.*') ? 'active' : '' }}">
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Axes strat&eacute;giques</span>
            </a>
            <a href="{{ route('rbm.produits.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('rbm.produits.*') ? 'active' : '' }}">
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Produits</span>
            </a>
            <a href="{{ route('rbm.indicateurs.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('rbm.indicateurs.*') ? 'active' : '' }}">
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Indicateurs</span>
            </a>
            <a href="{{ route('rbm.cadres-logiques.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('rbm.cadres-logiques.*') || request()->routeIs('rbm.chaines-resultats.*') ? 'active' : '' }}">
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Cadres logiques</span>
            </a>
            @can('suivi_physique.view')
            <a href="{{ route('suivi-physique.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('suivi-physique.*') ? 'active' : '' }}">
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Suivi physique</span>
            </a>
            @endcan
            @can('suivi_evaluation.view')
            <a href="{{ route('suivi-evaluation.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('suivi-evaluation.*') ? 'active' : '' }}">
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Suivi &amp; &Eacute;valuation</span>
            </a>
            @endcan
            @can('rbm.import')
            <a href="{{ route('rbm.import.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('rbm.import.*') ? 'active' : '' }}">
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Import Excel</span>
            </a>
            @endcan
            @endif

            @canany(['pap.view', 'pap.import', 'pap.replace', 'pap.restore', 'rbm.admin'])
            <a href="{{ route('rbm.pap-import.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('rbm.pap-import.index') || request()->routeIs('rbm.pap-import.show') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Import PAP</span>
            </a>
            <a href="{{ route('rbm.pap-import.programmation') }}"
               class="sidebar-nav-item {{ request()->routeIs('rbm.pap-import.programmation') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Programmation PAP</span>
            </a>
            <a href="{{ route('rbm.pap-import.history') }}"
               class="sidebar-nav-item {{ request()->routeIs('rbm.pap-import.history') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Historique PAP</span>
            </a>
            @endcanany

            {{-- Documents & GED --}}
            @can('ged.view')
            <div x-show="sidebarOpen || mobileSidebar" class="sidebar-section-label">Documents &amp; GED</div>
            <a href="{{ route('ged.dashboard') }}"
               class="sidebar-nav-item {{ request()->routeIs('ged.dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Tableau de bord GED</span>
            </a>
            <a href="{{ route('ged.documents.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('ged.documents.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Documents</span>
            </a>
            @can('create', App\Models\GedDocument::class)
            <a href="{{ route('ged.documents.create') }}"
               class="sidebar-nav-item {{ request()->routeIs('ged.documents.create') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Dépôt documentaire</span>
            </a>
            @endcan
            <a href="{{ route('ged.categories.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('ged.categories.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Catégories</span>
            </a>
            <a href="{{ route('ged.types-documents.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('ged.types-documents.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Types de documents</span>
            </a>
            <a href="{{ route('ged.dossiers.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('ged.dossiers.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Dossiers</span>
            </a>
            <a href="{{ route('ged.workflows.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('ged.workflows.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Circuits de validation</span>
            </a>
            <a href="{{ route('ged.archives.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('ged.archives.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Archives électroniques</span>
            </a>
            <a href="{{ route('ged.conservation.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('ged.conservation.*') || request()->routeIs('ged.conservation-rules.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Conservation numérique</span>
            </a>
            @can('ged.search')
            <a href="{{ route('ged.search.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('ged.search.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Recherche avancée</span>
            </a>
            @endcan
            <a href="{{ route('ged.partages.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('ged.partages.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Documents partagés</span>
            </a>
            <a href="{{ route('ged.confidentiels') }}"
               class="sidebar-nav-item {{ request()->routeIs('ged.confidentiels') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Documents confidentiels</span>
            </a>
            <a href="{{ route('ged.expires') }}"
               class="sidebar-nav-item {{ request()->routeIs('ged.expires') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Documents expirés</span>
            </a>
            @endcan

            {{-- Administration --}}
            @canany(['parameters.manage', 'users.manage', 'roles.manage'])
            <div x-show="sidebarOpen || mobileSidebar" class="sidebar-section-label">Administration</div>
            @can('parameters.manage')
            <a href="{{ route('budget.parametres.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('budget.parametres.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Paramètres de contrôle</span>
            </a>
            @endcan
            @can('users.manage')
            <a href="{{ route('admin.users.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Utilisateurs</span>
            </a>
            @endcan
            @can('roles.manage')
            <a href="{{ route('admin.roles.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M7 8h10M7 12h10M7 16h10M4 8h.01M4 12h.01M4 16h.01"/>
                </svg>
                <span x-show="sidebarOpen || mobileSidebar" class="sidebar-label truncate">Rôles & permissions</span>
            </a>
            @endcan
            @endcanany
        </nav>

        {{-- Footer sidebar ── --}}
        <div class="shrink-0 border-t border-white/10 px-2.5 py-3">
            <a href="{{ route('profile.edit') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-white/10 transition-all duration-200 group">
                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-ceeac-sky to-ceeac-bright flex items-center justify-center shrink-0 text-white font-bold text-xs ring-2 ring-white/20">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div x-show="sidebarOpen || mobileSidebar"
                     class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-blue-300/60 truncate">{{ Auth::user()->email }}</p>
                </div>
            </a>
        </div>
    </aside>

    {{-- ══ MAIN AREA ════════════════════════════════════════ --}}
    <div :class="{ 'lg:pl-64': sidebarOpen, 'lg:pl-[4.5rem]': !sidebarOpen }"
         class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out">

        {{-- ── TOPBAR ── --}}
        <header class="sticky top-0 z-30 h-16 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-sm flex items-center px-4 sm:px-6 gap-4">

            {{-- Hamburger desktop --}}
            <button @click="sidebarOpen = !sidebarOpen"
                    class="hidden lg:flex p-2 rounded-lg text-ceeac-slate hover:bg-gray-100 hover:text-ceeac-primary transition-colors duration-150 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Hamburger mobile --}}
            <button @click="mobileSidebar = !mobileSidebar"
                    class="lg:hidden p-2 rounded-lg text-ceeac-slate hover:bg-gray-100 hover:text-ceeac-primary transition-colors duration-150 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Breadcrumb / Title --}}
            <div class="flex-1 min-w-0">
                @isset($header)
                    {{ $header }}
                @endisset
            </div>

            {{-- Right side: Notifications + User --}}
            <div class="flex items-center gap-2">
                {{-- Notifications --}}
                <button class="relative p-2 rounded-lg text-ceeac-slate hover:bg-gray-100 hover:text-ceeac-primary transition-colors focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    {{-- Badge --}}
                    <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-ceeac-danger ring-2 ring-white"></span>
                </button>

                {{-- User dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-gray-100 transition-colors focus:outline-none">
                        <div class="h-8 w-8 rounded-full bg-ceeac-gradient flex items-center justify-center text-white font-bold text-xs ring-2 ring-ceeac-light">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div class="hidden sm:block text-left">
                            <p class="text-sm font-semibold text-ceeac-ink leading-none">{{ Auth::user()->name }}</p>
                        </div>
                        <svg class="w-4 h-4 text-ceeac-slate" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open"
                         @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 top-full mt-2 w-52 bg-white rounded-2xl shadow-float border border-gray-100 overflow-hidden z-50"
                         style="display:none;">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-semibold text-ceeac-ink">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-ceeac-slate truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <a href="{{ route('profile.edit') }}"
                           class="flex items-center gap-3 px-4 py-3 text-sm text-ceeac-ink hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 text-ceeac-slate" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Mon profil
                        </a>
                        <div class="border-t border-gray-100">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="flex w-full items-center gap-3 px-4 py-3 text-sm text-ceeac-danger hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- ── PAGE CONTENT ── --}}
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <x-ds.flash-messages />

            {{ $slot }}
        </main>

        {{-- ── FOOTER ── --}}
        @include('layouts.partials.app-footer')
    </div>
</div>

@stack('scripts')
</body>
</html>
