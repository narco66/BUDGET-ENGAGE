@php
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');

    $statutLabel = function ($st) {
        $v = $st instanceof \BackedEnum ? $st->value : $st;
        return match ($v) {
            'brouillon'    => __('Brouillon'),
            'soumis'       => __('Soumis'),
            'en_validation'=> __('En validation'),
            'retourne'     => __('Retourné'),
            'rejete'       => __('Rejeté'),
            'approuve'     => __('Approuvé'),
            'annule'       => __('Annulé'),
            'liquide'      => __('Liquidé'),
            'ordonnance'   => __('Ordonnancé'),
            'paye'         => __('Payé'),
            'cloture'      => __('Clôturé'),
            default        => $v,
        };
    };

    $badgeClass = function ($st) {
        $v = $st instanceof \BackedEnum ? $st->value : $st;
        return match ($v) {
            'brouillon'    => 'badge-brouillon',
            'soumis'       => 'badge-soumis',
            'en_validation'=> 'badge-validation',
            'retourne'     => 'badge-retourne',
            'rejete'       => 'badge-rejete',
            'approuve'     => 'badge-approuve',
            'annule'       => 'badge-annule',
            'liquide'      => 'badge-liquide',
            'ordonnance'   => 'badge-ordonnance',
            'paye'         => 'badge-paye',
            'cloture'      => 'badge-cloture',
            default        => 'badge-neutral',
        };
    };

    // Compute percentages for progress bars
    $budgetF    = (float) $budgetRevise;
    $engageF    = (float) $engage;
    $payeF      = (float) $paye;
    $dispoF     = (float) $disponible;
    $engagePct  = $budgetF > 0 ? min(100, round($engageF / $budgetF * 100, 1)) : 0;
    $payePct    = $budgetF > 0 ? min(100, round($payeF   / $budgetF * 100, 1)) : 0;
    $dispoPct   = $budgetF > 0 ? min(100, round(max(0, $dispoF) / $budgetF * 100, 1)) : 0;
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-ds.page-header
            :title="__('dashboard.title')"
            :description="$exercice ? __('dashboard.subtitle').' — '.__('Exercice').' '.$exercice->annee : null"
            :breadcrumb="[
                ['label' => __('Accueil'), 'url' => route('dashboard')],
                ['label' => __('dashboard.title')],
            ]"
        >
            @if($exercice)
                <x-slot:actions>
                    @if($exercice->est_actif)
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-full">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            {{ __('Actif') }}
                        </span>
                    @endif
                    <time class="text-xs text-gray-400 font-medium hidden sm:block">
                        {{ __('Mis à jour') }} {{ now()->format('d/m/Y à H:i') }}
                    </time>
                </x-slot:actions>
            @endif
        </x-ds.page-header>
    </x-slot>

    <x-ds.page-shell>
    {{-- No active exercise warning --}}
    @unless($exercice)
        <x-ds.alert type="warning" class="mb-6 animate-slide-up">{{ __('dashboard.no_exercice') }}</x-ds.alert>
    @endunless

    {{-- ── KPI CARDS ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-7 animate-fade-in">

        {{-- Budget révisé --}}
        <div class="kpi-card">
            <div class="flex items-start justify-between gap-3">
                <div class="kpi-icon bg-blue-50">
                    <svg class="w-5 h-5 text-ceeac-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="kpi-trend bg-blue-50 text-ceeac-primary">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Révisé
                </span>
            </div>
            <div class="mt-4">
                <div class="kpi-value text-ceeac-primary animate-count-up">{{ $fmt($budgetRevise) }}</div>
                <div class="kpi-label mt-1">{{ __('dashboard.kpi_budget') }}</div>
            </div>
            <div class="mt-4 progress-bar">
                <div class="progress-fill bg-ceeac-primary" style="width: 100%"></div>
            </div>
            <p class="mt-1.5 text-[10px] text-gray-400">Enveloppe totale de référence</p>
        </div>

        {{-- Engagé --}}
        <div class="kpi-card">
            <div class="flex items-start justify-between gap-3">
                <div class="kpi-icon bg-indigo-50">
                    <svg class="w-5 h-5 text-ceeac-bright" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <span class="kpi-trend {{ $engagePct > 80 ? 'bg-red-50 text-red-700' : 'bg-indigo-50 text-indigo-700' }}">
                    {{ $engagePct }}%
                </span>
            </div>
            <div class="mt-4">
                <div class="kpi-value text-ceeac-bright animate-count-up">{{ $fmt($engage) }}</div>
                <div class="kpi-label mt-1">{{ __('dashboard.kpi_engage') }}</div>
            </div>
            <div class="mt-4 progress-bar">
                <div class="progress-fill bg-ceeac-bright" style="width: {{ $engagePct }}%"></div>
            </div>
            <p class="mt-1.5 text-[10px] text-gray-400">{{ $engagePct }}% du budget révisé</p>
        </div>

        {{-- Payé --}}
        <div class="kpi-card">
            <div class="flex items-start justify-between gap-3">
                <div class="kpi-icon bg-emerald-50">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="kpi-trend bg-emerald-50 text-emerald-700">{{ $payePct }}%</span>
            </div>
            <div class="mt-4">
                <div class="kpi-value text-emerald-600 animate-count-up">{{ $fmt($paye) }}</div>
                <div class="kpi-label mt-1">{{ __('dashboard.kpi_paye') }}</div>
            </div>
            <div class="mt-4 progress-bar">
                <div class="progress-fill bg-emerald-500" style="width: {{ $payePct }}%"></div>
            </div>
            <p class="mt-1.5 text-[10px] text-gray-400">{{ $payePct }}% du budget révisé</p>
        </div>

        {{-- Disponible --}}
        <div class="kpi-card">
            <div class="flex items-start justify-between gap-3">
                <div class="kpi-icon {{ $dispoF < 0 ? 'bg-red-50' : 'bg-amber-50' }}">
                    <svg class="w-5 h-5 {{ $dispoF < 0 ? 'text-red-500' : 'text-amber-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($dispoF < 0)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        @endif
                    </svg>
                </div>
                @if($dispoF < 0)
                    <span class="kpi-trend bg-red-50 text-red-700">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Dépassé
                    </span>
                @else
                    <span class="kpi-trend bg-amber-50 text-amber-700">{{ $dispoPct }}%</span>
                @endif
            </div>
            <div class="mt-4">
                <div class="kpi-value {{ $dispoF < 0 ? 'text-ceeac-danger' : 'text-amber-600' }} animate-count-up">{{ $fmt($disponible) }}</div>
                <div class="kpi-label mt-1">{{ __('dashboard.kpi_dispo') }}</div>
            </div>
            <div class="mt-4 progress-bar">
                <div class="progress-fill {{ $dispoF < 0 ? 'bg-red-500' : 'bg-amber-400' }}" style="width: {{ $dispoPct }}%"></div>
            </div>
            <p class="mt-1.5 text-[10px] text-gray-400">{{ $dispoF < 0 ? 'Dépassement détecté' : $dispoPct.'% restant disponible' }}</p>
        </div>
    </div>

    {{-- ── CHART + ALERTS ── --}}
    <div class="grid lg:grid-cols-3 gap-6 mb-7">

        {{-- Bar chart --}}
        <div class="card lg:col-span-2 animate-slide-up" style="animation-delay: 0.1s">
            <div class="card-header">
                <div>
                    <h3 class="text-sm font-semibold text-ceeac-primary">{{ __('dashboard.chart_title') }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5">6 derniers mois — nombre d'engagements créés</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1.5 text-xs text-gray-400 font-medium">
                        <span class="h-2.5 w-5 rounded-sm bg-gradient-to-r from-ceeac-primary to-ceeac-sky inline-block"></span>
                        Engagements
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="flex items-end gap-2 sm:gap-3 h-48 pb-1">
                    @foreach ($chartMonths as $row)
                        @php($h = $maxChart > 0 ? max(4, round(($row['count'] / $maxChart) * 100)) : 4)
                        <div class="flex-1 flex flex-col items-center gap-2 group">
                            <span class="text-xs font-bold text-gray-700 tabular-nums opacity-0 group-hover:opacity-100 transition-opacity">{{ $row['count'] }}</span>
                            <div class="w-full max-w-[52px] mx-auto relative">
                                <div class="rounded-t-lg bg-gradient-to-t from-ceeac-primary to-ceeac-sky shadow-sm
                                            hover:from-ceeac-bright hover:to-ceeac-sky transition-colors duration-200 cursor-default"
                                     style="height: {{ max(8, $h * 1.6) }}px; min-height: 8px;"
                                     title="{{ $row['count'] }} engagement(s) en {{ $row['label'] }}">
                                </div>
                            </div>
                            <span class="text-[10px] uppercase tracking-wide text-gray-400 font-medium">{{ $row['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                {{-- X-axis total line --}}
                <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-100">
                    <span class="text-xs text-gray-400">Volume total : <strong class="text-ceeac-ink">{{ collect($chartMonths)->sum('count') }} engagements</strong></span>
                    <span class="text-xs text-gray-400">Moyenne : <strong class="text-ceeac-ink">{{ $maxChart > 0 ? round(collect($chartMonths)->avg('count'), 1) : 0 }}/mois</strong></span>
                </div>
            </div>
        </div>

        {{-- Alerts panel --}}
        <div class="card bg-sidebar-gradient text-white animate-slide-up" style="animation-delay: 0.15s">
            <div class="card-header border-white/10">
                <h3 class="text-sm font-semibold text-white">{{ __('dashboard.alerts_title') }}</h3>
                <div class="relative">
                    <svg class="w-5 h-5 text-blue-300/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if($alertsEnValidation > 0 || $urgencesPending > 0)
                        <span class="absolute -top-1 -right-1 h-2 w-2 rounded-full bg-ceeac-danger ring-1 ring-white animate-pulse"></span>
                    @endif
                </div>
            </div>
            <div class="card-body space-y-4">

                <div class="rounded-xl bg-white/10 ring-1 ring-white/15 p-4 flex gap-3 items-start">
                    <div class="h-8 w-8 rounded-lg bg-amber-400/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold tabular-nums text-white">{{ $alertsEnValidation }}</p>
                        <p class="text-sm text-blue-100/80 mt-0.5">{{ __('dashboard.alert_validation', ['count' => '']) }}</p>
                    </div>
                </div>

                <div class="rounded-xl bg-white/10 ring-1 ring-white/15 p-4 flex gap-3 items-start">
                    <div class="h-8 w-8 rounded-lg bg-red-400/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold tabular-nums text-white">{{ $urgencesPending }}</p>
                        <p class="text-sm text-blue-100/80 mt-0.5">{{ __('dashboard.alert_urgences', ['count' => '']) }}</p>
                    </div>
                </div>

                {{-- Execution rate summary --}}
                <div class="rounded-xl bg-white/10 ring-1 ring-white/15 p-4">
                    <p class="text-xs font-semibold uppercase tracking-widest text-blue-300/70 mb-3">Taux d'exécution</p>
                    <div class="space-y-2">
                        <div>
                            <div class="flex justify-between text-xs text-blue-100/80 mb-1">
                                <span>Engagement</span><span class="font-bold text-white">{{ $engagePct }}%</span>
                            </div>
                            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
                                <div class="h-full rounded-full bg-ceeac-sky" style="width: {{ $engagePct }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs text-blue-100/80 mb-1">
                                <span>Paiement</span><span class="font-bold text-white">{{ $payePct }}%</span>
                            </div>
                            <div class="h-1.5 rounded-full bg-white/10 overflow-hidden">
                                <div class="h-full rounded-full bg-emerald-400" style="width: {{ $payePct }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                @can('audit.view')
                    <a href="{{ route('audit.index') }}"
                       class="flex items-center justify-between w-full rounded-xl bg-white/10 ring-1 ring-white/20 px-4 py-3
                              hover:bg-white/20 transition-colors duration-200 group">
                        <span class="text-sm font-semibold text-white">{{ __('audit.title') }}</span>
                        <svg class="w-4 h-4 text-ceeac-sky group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- ── RECENT ENGAGEMENTS ── --}}
    <div class="card animate-slide-up" style="animation-delay: 0.2s">
        <div class="card-header">
            <div>
                <h3 class="text-sm font-semibold text-ceeac-primary">{{ __('dashboard.recent_title') }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">8 derniers engagements modifiés</p>
            </div>
            <div class="flex items-center gap-3">
                @can('viewAny', App\Models\Engagement::class)
                    <a href="{{ route('engagements.index') }}"
                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-ceeac-bright hover:text-ceeac-primary transition-colors group">
                        Voir tout
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @endcan
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('dashboard.table_numero') }}</th>
                        <th>{{ __('dashboard.table_objet') }}</th>
                        <th>Ligne budgétaire</th>
                        <th>{{ __('dashboard.table_statut') }}</th>
                        <th class="text-right">{{ __('dashboard.table_montant') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentEngagements as $eng)
                        <tr>
                            <td class="font-mono whitespace-nowrap">
                                @can('view', $eng)
                                    <a href="{{ route('engagements.show', $eng) }}"
                                       class="text-ceeac-bright hover:text-ceeac-primary font-semibold hover:underline underline-offset-2 transition-colors">
                                        {{ $eng->numero }}
                                    </a>
                                @else
                                    <span class="text-ceeac-ink font-semibold">{{ $eng->numero }}</span>
                                @endcan
                                @if($eng->is_urgence)
                                    <span class="ml-1.5 badge bg-red-100 text-red-700 text-[10px] py-0.5 px-1.5">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        Urgence
                                    </span>
                                @endif
                            </td>
                            <td class="max-w-xs">
                                <span class="truncate block text-ceeac-ink" title="{{ $eng->objet }}">{{ $eng->objet }}</span>
                                @if($eng->createur)
                                    <span class="text-xs text-gray-400">par {{ $eng->createur->name }}</span>
                                @endif
                            </td>
                            <td class="text-gray-500 text-xs">
                                {{ $eng->ligneBudgetaire?->code ?? '—' }}
                            </td>
                            <td>
                                <span class="{{ $badgeClass($eng->statut) }}">{{ $statutLabel($eng->statut) }}</span>
                            </td>
                            <td class="text-right tabular-nums font-semibold text-ceeac-ink whitespace-nowrap">
                                {{ $fmt($eng->montant) }}
                                <span class="text-xs text-gray-400 font-normal ml-0.5">XAF</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-16">
                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                    <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="text-sm font-medium text-gray-500">{{ __('dashboard.empty_recent') }}</p>
                                    @can('create', App\Models\Engagement::class)
                                        <a href="{{ route('engagements.create') }}" class="btn btn-primary btn-sm mt-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            Créer un engagement
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($recentEngagements->count() > 0)
                    <tfoot>
                        <tr>
                            <td colspan="3">{{ $recentEngagements->count() }} engagement(s) affichés</td>
                            <td colspan="2" class="text-right">
                                Total affiché : <strong class="text-ceeac-ink tabular-nums">{{ $fmt($recentEngagements->sum('montant')) }} XAF</strong>
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    @if ($papSynthese)
        <div class="mt-7 animate-fade-in">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-ceeac-ink">{{ __('Plan Annuel de Performance') }} {{ $exercice->annee }}</h3>
                <a href="{{ route('rbm.pap-import.programmation') }}" class="text-sm text-ceeac-bright hover:underline">{{ __('État de programmation →') }}</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-5">
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Montant PAP total') }}</div>
                    <div class="text-2xl font-bold text-ceeac-ink tabular-nums mt-1">{{ $fmt($papSynthese['montant_total']) }} <span class="text-sm font-normal text-gray-400">XAF</span></div>
                    <div class="text-xs text-gray-400 mt-1">{{ $papSynthese['nb_piliers'] }} piliers · {{ $papSynthese['nb_axes'] }} axes · {{ $papSynthese['nb_produits'] }} produits · {{ $papSynthese['nb_sous_produits'] }} sous-produits</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Financement CEEAC') }}</div>
                    <div class="text-2xl font-bold text-emerald-600 tabular-nums mt-1">{{ $fmt($papSynthese['montant_ceeac']) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $papSynthese['montant_total'] > 0 ? round($papSynthese['montant_ceeac'] / $papSynthese['montant_total'] * 100, 1) : 0 }} % du total</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Financement PTF') }}</div>
                    <div class="text-2xl font-bold text-indigo-600 tabular-nums mt-1">{{ $fmt($papSynthese['montant_ptf']) }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $papSynthese['montant_total'] > 0 ? round($papSynthese['montant_ptf'] / $papSynthese['montant_total'] * 100, 1) : 0 }} % du total</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Couverture budgétaire') }}</div>
                    <div class="text-2xl font-bold text-ceeac-ink tabular-nums mt-1">{{ $papSynthese['taux_couverture'] }} %</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $papSynthese['nb_lignes_liees'] }} ligne(s) liée(s) · {{ $fmt($papSynthese['montant_budgete']) }} XAF</div>
                </div>
            </div>

            @php($papTotal = (float) $papSynthese['montant_total'])
            @php($ceeacPct = $papTotal > 0 ? round($papSynthese['montant_ceeac'] / $papTotal * 100, 1) : 0)
            @php($ptfPct = $papTotal > 0 ? round($papSynthese['montant_ptf'] / $papTotal * 100, 1) : 0)
            <div class="bg-white rounded-xl shadow-sm p-5 mb-5">
                <div class="text-sm font-semibold text-gray-700 mb-4">{{ __('Répartition par source de financement') }}</div>
                <div class="flex flex-col sm:flex-row items-center gap-6">
                    <div class="relative shrink-0" style="width: 150px; height: 150px;">
                        <div class="w-full h-full rounded-full" style="background: conic-gradient(#10b981 0 {{ $ceeacPct }}%, #6366f1 {{ $ceeacPct }}% 100%);"></div>
                        <div class="absolute inset-0 m-auto bg-white rounded-full flex flex-col items-center justify-center" style="width: 86px; height: 86px;">
                            <span class="text-[10px] uppercase tracking-wide text-gray-400">{{ __('Total') }}</span>
                            <span class="text-xs font-bold text-ceeac-ink tabular-nums">{{ $fmt($papSynthese['montant_total']) }}</span>
                        </div>
                    </div>
                    <div class="flex-1 w-full space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-sm text-gray-600"><span class="inline-block w-3 h-3 rounded-sm" style="background:#10b981"></span>{{ __('CEEAC') }}</span>
                            <span class="text-sm tabular-nums"><strong class="text-ceeac-ink">{{ $fmt($papSynthese['montant_ceeac']) }}</strong> <span class="text-gray-400">· {{ $ceeacPct }}%</span></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-sm text-gray-600"><span class="inline-block w-3 h-3 rounded-sm" style="background:#6366f1"></span>{{ __('PTF') }}</span>
                            <span class="text-sm tabular-nums"><strong class="text-ceeac-ink">{{ $fmt($papSynthese['montant_ptf']) }}</strong> <span class="text-gray-400">· {{ $ptfPct }}%</span></span>
                        </div>
                        <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                            <span>{{ __('Total PAP') }} {{ $exercice->annee }}</span>
                            <span class="tabular-nums font-semibold text-ceeac-ink">{{ $fmt($papSynthese['montant_total']) }} XAF</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-5 mb-5">
                <div class="text-sm font-semibold text-gray-700 mb-4">{{ __('Couverture budgétaire par pilier') }}</div>
                <div class="space-y-3">
                    @foreach ($papParPilier as $p)
                        @php($taux = min(100, (float) $p['taux_couverture']))
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-gray-600"><span class="font-mono text-gray-400">{{ $p['code'] }}</span> {{ \Illuminate\Support\Str::limit($p['libelle'], 48) }}</span>
                                <span class="tabular-nums font-medium {{ $p['taux_couverture'] >= 100 ? 'text-emerald-600' : ($p['taux_couverture'] > 0 ? 'text-ceeac-ink' : 'text-gray-400') }}">{{ $p['taux_couverture'] }}%</span>
                            </div>
                            <div class="h-2.5 w-full bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $taux >= 100 ? 'bg-emerald-500' : 'bg-ceeac-bright' }}" style="width: {{ $taux }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                    <span>{{ __('Couverture globale') }}</span>
                    <span class="tabular-nums font-semibold text-ceeac-ink">{{ $papSynthese['taux_couverture'] }}% · {{ $fmt($papSynthese['montant_budgete']) }} / {{ $fmt($papSynthese['montant_total']) }} XAF</span>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 text-sm font-semibold text-gray-700">{{ __('Répartition par pilier') }}</div>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left">{{ __('Pilier') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('Total PAP') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('CEEAC') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('PTF') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('Budgété') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('Couverture') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($papParPilier as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2"><span class="font-mono text-xs text-gray-500">{{ $p['code'] }}</span> {{ \Illuminate\Support\Str::limit($p['libelle'], 60) }}</td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($p['montant_total']) }}</td>
                                <td class="px-4 py-2 text-right tabular-nums text-emerald-600">{{ $fmt($p['montant_ceeac']) }}</td>
                                <td class="px-4 py-2 text-right tabular-nums text-indigo-600">{{ $fmt($p['montant_ptf']) }}</td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($p['montant_budgete']) }}</td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ $p['taux_couverture'] }} %</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($budgetSynthese)
        @php($bTotal = (float) $budgetSynthese['montant_depenses'])
        @php($bCeeacPct = $bTotal > 0 ? round($budgetSynthese['montant_ceeac'] / $bTotal * 100, 1) : 0)
        @php($bEquilibre = (float) $budgetSynthese['montant_recettes'] - $bTotal)
        <div class="mt-7 animate-fade-in">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-ceeac-ink">{{ __('Budget') }} {{ $exercice->annee }}</h3>
                <div class="flex items-center gap-3">
                    @if ($budgetAlertes->isNotEmpty())
                        <a href="{{ route('budget.nomenclature.synthese') }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                            ⚠ {{ $budgetAlertes->count() }} {{ __('alerte(s) de seuil') }}
                        </a>
                    @endif
                    <a href="{{ route('budget.nomenclature.synthese') }}" class="text-sm text-ceeac-bright hover:underline">{{ __('Synthèse budgétaire →') }}</a>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-5">
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Total recettes') }}</div>
                    <div class="text-2xl font-bold text-emerald-600 tabular-nums mt-1">{{ $fmt($budgetSynthese['montant_recettes']) }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Total dépenses') }}</div>
                    <div class="text-2xl font-bold text-indigo-600 tabular-nums mt-1">{{ $fmt($budgetSynthese['montant_depenses']) }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Équilibre (R − D)') }}</div>
                    <div class="text-2xl font-bold tabular-nums mt-1 {{ abs($bEquilibre) < 1 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $fmt($bEquilibre) }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Taux d\'engagement') }}</div>
                    <div class="text-2xl font-bold text-ceeac-ink tabular-nums mt-1">{{ $budgetSynthese['taux_engagement'] }} %</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $budgetSynthese['nb_lignes_operationnelles'] }} {{ __('ligne(s) opérationnelle(s)') }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <div class="text-sm font-semibold text-gray-700 mb-4">{{ __('Dépenses par source de financement') }}</div>
                    <div class="flex items-center gap-6">
                        <div class="relative shrink-0" style="width: 130px; height: 130px;">
                            <div class="w-full h-full rounded-full" style="background: conic-gradient(#10b981 0 {{ $bCeeacPct }}%, #6366f1 {{ $bCeeacPct }}% 100%);"></div>
                            <div class="absolute inset-0 m-auto bg-white rounded-full" style="width: 72px; height: 72px;"></div>
                        </div>
                        <div class="flex-1 space-y-2 text-sm">
                            <div class="flex items-center justify-between"><span class="flex items-center gap-2 text-gray-600"><span class="inline-block w-3 h-3 rounded-sm" style="background:#10b981"></span>{{ __('CEEAC') }}</span><span class="tabular-nums">{{ $fmt($budgetSynthese['montant_ceeac']) }} · {{ $bCeeacPct }}%</span></div>
                            <div class="flex items-center justify-between"><span class="flex items-center gap-2 text-gray-600"><span class="inline-block w-3 h-3 rounded-sm" style="background:#6366f1"></span>{{ __('PTF') }}</span><span class="tabular-nums">{{ $fmt($budgetSynthese['montant_ptf']) }} · {{ 100 - $bCeeacPct }}%</span></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-5">
                    <div class="text-sm font-semibold text-gray-700 mb-3">{{ __('Exécution par catégorie') }}</div>
                    @if ($budgetExecCategories->isEmpty())
                        <p class="text-sm text-gray-500">{{ __('Crédits ouverts non générés. Lancez « Générer les lignes opérationnelles » depuis la synthèse budgétaire.') }}</p>
                    @else
                        <div class="space-y-2">
                            @foreach ($budgetExecCategories as $c)
                                @php($t = min(100, (float) $c['taux_engagement']))
                                <div>
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="text-gray-600">{{ $c['libelle'] }}</span>
                                        <span class="tabular-nums text-gray-500">{{ $fmt($c['credits_ouverts']) }} · {{ $c['taux_engagement'] }}%</span>
                                    </div>
                                    <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full bg-ceeac-bright" style="width: {{ $t }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    </x-ds.page-shell>

</x-app-layout>
