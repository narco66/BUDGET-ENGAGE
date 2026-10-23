@php $fmt = fn ($v) => number_format((float) $v, 2, ',', ' '); @endphp
<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Liquidation') . ' #' . $liquidation->id"
            :description="$liquidation->engagement?->numero"
            :breadcrumb="[
                ['label' => __('execution.dashboard'), 'url' => route('execution.dashboard')],
                ['label' => '#'.$liquidation->id],
            ]"
        >
            <x-slot:actions>
                <a href="{{ route('engagements.show', $liquidation->engagement) }}" class="btn btn-ghost btn-sm">{{ __('execution.actions.view_engagement') }}</a>
                @can('update', $liquidation)
                    <a href="{{ route('execution.liquidations.edit', $liquidation) }}" class="btn btn-secondary btn-sm">{{ __('execution.actions.edit') }}</a>
                @endcan
                @can('addOrdonnancement', $liquidation)
                    <a href="{{ route('liquidations.ordonnancements.create', $liquidation) }}" class="btn btn-primary btn-sm">{{ __('Ordonnancer') }}</a>
                @endcan
            </x-slot:actions>
        </x-chaine.dossier-header>
    </x-slot>

    <x-ds.page-shell class="space-y-4">
        <x-chaine.financial-summary :title="__('Synthèse')">
            <x-chaine.financial-metric :label="__('Engagement')">
                <span class="font-mono">{{ $liquidation->engagement?->numero }}</span>
            </x-chaine.financial-metric>
            <x-chaine.financial-metric :label="__('Montant liquidé')" tone="primary">
                <x-ds.money :value="$liquidation->montant_liquide" :decimals="2" :show-currency="false" />
            </x-chaine.financial-metric>
            <x-chaine.financial-metric :label="__('Exercice')" :value="$liquidation->engagement?->exerciceBudgetaire?->annee ?? '—'" />
            <x-chaine.financial-metric :label="__('Ligne budgétaire')" :value="$liquidation->engagementImputation?->ligneBudgetaire?->code ?? '—'" />
        </x-chaine.financial-summary>

        <x-ds.card>
            <div class="grid md:grid-cols-2 gap-3 text-sm">
                <div><span class="text-gray-500">{{ __('Date') }}</span><p class="font-medium">{{ $liquidation->date_liquidation?->format('d/m/Y') }}</p></div>
                <div><span class="text-gray-500">{{ __('Référence') }}</span><p class="font-medium">{{ $liquidation->reference ?? '—' }}</p></div>
                <div><span class="text-gray-500">{{ __('Saisi par') }}</span><p class="font-medium">{{ $liquidation->createur?->name ?? '—' }}</p></div>
                @if ($liquidation->commentaire)
                    <div class="md:col-span-2"><span class="text-gray-500">{{ __('Commentaire') }}</span><p class="whitespace-pre-wrap">{{ $liquidation->commentaire }}</p></div>
                @endif
            </div>
        </x-ds.card>

        @if ($liquidation->ordonnancements->isNotEmpty())
            <x-ds.card :title="__('Ordonnancements rattachés')" :padding="false">
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Montant') }}</th><th>{{ __('Réf.') }}</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($liquidation->ordonnancements as $ord)
                                <tr>
                                    <td>{{ $ord->date_ordonnancement?->format('d/m/Y') }}</td>
                                    <td class="tabular-nums"><x-ds.money :value="$ord->montant_ordonnance" :decimals="2" :show-currency="false" /></td>
                                    <td>{{ $ord->reference ?? '—' }}</td>
                                    <td><a href="{{ route('execution.ordonnancements.show', $ord) }}" class="text-ceeac-bright hover:underline">{{ __('Voir') }}</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ds.card>
        @endif

        @can('cancel', $liquidation)
            @include('execution.partials.cancel-form', ['cancelRoute' => route('execution.liquidations.cancel', $liquidation)])
        @endcan
    </x-ds.page-shell>
</x-app-layout>
