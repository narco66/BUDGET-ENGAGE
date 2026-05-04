@php
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
    $statutLabel = function ($st) {
        $v = $st instanceof \BackedEnum ? $st->value : $st;
        return match ($v) {
            'brouillon' => __('Brouillon'),
            'soumis' => __('Soumis'),
            'en_validation' => __('En validation'),
            'retourne' => __('Retourné'),
            'rejete' => __('Rejeté'),
            'approuve' => __('Approuvé'),
            'annule' => __('Annulé'),
            'liquide' => __('Liquide'),
            'ordonnance' => __('Ordonnancé'),
            'paye' => __('Payé'),
            'cloture' => __('Clôturé'),
            default => $v,
        };
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-ceeac-primary leading-tight">{{ __('dashboard.title') }}</h2>
            @if($exercice)
                <p class="text-sm text-gray-500 mt-1">{{ __('dashboard.subtitle') }} — <span class="font-medium text-gray-700">{{ __('Exercice') }} {{ $exercice->annee }}</span></p>
            @endif
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <x-auth-session-status class="mb-2" :status="session('status')" />

        @unless($exercice)
            <div class="rounded-institutional border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900 shadow-card">
                {{ __('dashboard.no_exercice') }}
            </div>
        @endunless

        {{-- KPI --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
            <div class="rounded-institutional bg-white border border-gray-100 p-6 shadow-card">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('dashboard.kpi_budget') }}</p>
                <p class="mt-2 text-2xl font-bold tabular-nums text-ceeac-primary">{{ $fmt($budgetRevise) }}</p>
            </div>
            <div class="rounded-institutional bg-white border border-gray-100 p-6 shadow-card">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('dashboard.kpi_engage') }}</p>
                <p class="mt-2 text-2xl font-bold tabular-nums text-ceeac-bright">{{ $fmt($engage) }}</p>
            </div>
            <div class="rounded-institutional bg-white border border-gray-100 p-6 shadow-card">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('dashboard.kpi_paye') }}</p>
                <p class="mt-2 text-2xl font-bold tabular-nums text-ceeac-success">{{ $fmt($paye) }}</p>
            </div>
            <div class="rounded-institutional bg-white border border-gray-100 p-6 shadow-card">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('dashboard.kpi_dispo') }}</p>
                <p class="mt-2 text-2xl font-bold tabular-nums {{ (float) $disponible < 0 ? 'text-ceeac-danger' : 'text-gray-900' }}">{{ $fmt($disponible) }}</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            {{-- Graphique --}}
            <div class="lg:col-span-2 rounded-institutional bg-white border border-gray-100 p-6 shadow-card">
                <h3 class="text-sm font-semibold text-ceeac-primary">{{ __('dashboard.chart_title') }}</h3>
                <div class="mt-6 flex items-end gap-2 h-48 px-2 border-b border-gray-100">
                    @foreach ($chartMonths as $row)
                        @php($h = $maxChart > 0 ? round(($row['count'] / $maxChart) * 100) : 0)
                        <div class="flex-1 flex flex-col items-center gap-2">
                            <span class="text-xs font-semibold text-gray-700">{{ $row['count'] }}</span>
                            <div class="w-full max-w-[48px] mx-auto rounded-t-md bg-gradient-to-t from-ceeac-primary to-ceeac-sky min-h-[8px]" style="height: {{ max(8, $h * 1.6) }}px"></div>
                            <span class="text-[10px] uppercase text-gray-500">{{ $row['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Alertes --}}
            <div class="rounded-institutional bg-gradient-to-br from-ceeac-primary to-[#172554] text-white p-6 shadow-soft space-y-5">
                <h3 class="text-sm font-semibold">{{ __('dashboard.alerts_title') }}</h3>
                <ul class="space-y-4 text-sm">
                    <li class="flex gap-3 items-start">
                        <span class="mt-1 inline-flex h-2 w-2 rounded-full bg-ceeac-warning shrink-0"></span>
                        <span>{{ __('dashboard.alert_validation', ['count' => $alertsEnValidation]) }}</span>
                    </li>
                    <li class="flex gap-3 items-start">
                        <span class="mt-1 inline-flex h-2 w-2 rounded-full bg-ceeac-danger shrink-0"></span>
                        <span>{{ __('dashboard.alert_urgences', ['count' => $urgencesPending]) }}</span>
                    </li>
                </ul>
                @can('audit.view')
                    <a href="{{ route('audit.index') }}" class="inline-flex text-xs font-semibold text-ceeac-sky hover:text-white underline-offset-2 hover:underline">
                        {{ __('audit.title') }} →
                    </a>
                @endcan
            </div>
        </div>

        {{-- Récent --}}
        <div class="rounded-institutional bg-white border border-gray-100 shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-ceeac-primary">{{ __('dashboard.recent_title') }}</h3>
                @can('viewAny', App\Models\Engagement::class)
                    <a href="{{ route('engagements.index') }}" class="text-xs font-semibold text-ceeac-bright hover:text-ceeac-primary">{{ __('Voir tout') }}</a>
                @endcan
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-ceeac-muted/80 text-left text-xs uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-6 py-3">{{ __('dashboard.table_numero') }}</th>
                            <th class="px-6 py-3">{{ __('dashboard.table_objet') }}</th>
                            <th class="px-6 py-3">{{ __('dashboard.table_statut') }}</th>
                            <th class="px-6 py-3 text-right">{{ __('dashboard.table_montant') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($recentEngagements as $eng)
                            <tr class="hover:bg-gray-50/80">
                                <td class="px-6 py-3 font-mono whitespace-nowrap">
                                    <a href="{{ route('engagements.show', $eng) }}" class="text-ceeac-bright hover:underline">{{ $eng->numero }}</a>
                                </td>
                                <td class="px-6 py-3 text-gray-700 max-w-md truncate">{{ $eng->objet }}</td>
                                <td class="px-6 py-3">{{ $statutLabel($eng->statut) }}</td>
                                <td class="px-6 py-3 text-right tabular-nums font-medium">{{ $fmt($eng->montant) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">{{ __('dashboard.empty_recent') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
