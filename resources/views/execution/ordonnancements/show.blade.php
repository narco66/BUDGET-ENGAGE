@php $fmt = fn ($v) => number_format((float) $v, 2, ',', ' '); @endphp
<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Ordonnancement') . ' #' . $ordonnancement->id"
            :description="$ordonnancement->liquidation?->engagement?->numero"
            :breadcrumb="[
                ['label' => __('execution.dashboard'), 'url' => route('execution.dashboard', ['tab' => 'ordonnancements'])],
                ['label' => '#'.$ordonnancement->id],
            ]"
        >
            <x-slot:actions>
                <a href="{{ route('execution.liquidations.show', $ordonnancement->liquidation) }}" class="btn btn-ghost btn-sm">{{ __('Liquidation') }}</a>
                @can('update', $ordonnancement)
                    <a href="{{ route('execution.ordonnancements.edit', $ordonnancement) }}" class="btn btn-secondary btn-sm">{{ __('execution.actions.edit') }}</a>
                @endcan
                @can('addPaiement', $ordonnancement)
                    <a href="{{ route('ordonnancements.paiements.create', $ordonnancement) }}" class="btn btn-primary btn-sm">{{ __('Payer') }}</a>
                @endcan
            </x-slot:actions>
        </x-chaine.dossier-header>
    </x-slot>

    <x-ds.page-shell class="space-y-4">
        <x-chaine.financial-summary :title="__('Synthèse')">
            <x-chaine.financial-metric :label="__('Engagement')"><span class="font-mono">{{ $ordonnancement->liquidation?->engagement?->numero }}</span></x-chaine.financial-metric>
            <x-chaine.financial-metric :label="__('Montant ordonnancé')" tone="primary"><x-ds.money :value="$ordonnancement->montant_ordonnance" :decimals="2" :show-currency="false" /></x-chaine.financial-metric>
            <x-chaine.financial-metric :label="__('Date')" :value="$ordonnancement->date_ordonnancement?->format('d/m/Y') ?? '—'" />
            <x-chaine.financial-metric :label="__('Ligne')" :value="$ordonnancement->engagementImputation?->ligneBudgetaire?->code ?? '—'" />
        </x-chaine.financial-summary>

        <x-ds.card>
            <div class="grid md:grid-cols-2 gap-3 text-sm">
                <div><span class="text-gray-500">{{ __('Référence') }}</span><p class="font-medium">{{ $ordonnancement->reference ?? '—' }}</p></div>
                <div><span class="text-gray-500">{{ __('Saisi par') }}</span><p class="font-medium">{{ $ordonnancement->createur?->name ?? '—' }}</p></div>
            </div>
        </x-ds.card>

        @if ($ordonnancement->paiements->isNotEmpty())
            <x-ds.card :title="__('Paiements rattachés')" :padding="false">
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Montant') }}</th><th>{{ __('Mode') }}</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($ordonnancement->paiements as $pay)
                                <tr>
                                    <td>{{ $pay->date_paiement?->format('d/m/Y') }}</td>
                                    <td class="tabular-nums"><x-ds.money :value="$pay->montant_paye" :decimals="2" :show-currency="false" /></td>
                                    <td>{{ $pay->mode_paiement ?? '—' }}</td>
                                    <td><a href="{{ route('execution.paiements.show', $pay) }}" class="text-ceeac-bright hover:underline">{{ __('Voir') }}</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ds.card>
        @endif

        @can('cancel', $ordonnancement)
            @include('execution.partials.cancel-form', ['cancelRoute' => route('execution.ordonnancements.cancel', $ordonnancement)])
        @endcan
    </x-ds.page-shell>
</x-app-layout>
