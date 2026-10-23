@php $fmt = fn ($v) => number_format((float) $v, 2, ',', ' '); @endphp
<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Paiement') . ' #' . $paiement->id"
            :description="$paiement->ordonnancement?->liquidation?->engagement?->numero"
            :breadcrumb="[
                ['label' => __('execution.dashboard'), 'url' => route('execution.dashboard', ['tab' => 'paiements'])],
                ['label' => '#'.$paiement->id],
            ]"
        >
            <x-slot:actions>
                <a href="{{ route('execution.ordonnancements.show', $paiement->ordonnancement) }}" class="btn btn-ghost btn-sm">{{ __('Ordonnancement') }}</a>
                @can('update', $paiement)
                    <a href="{{ route('execution.paiements.edit', $paiement) }}" class="btn btn-secondary btn-sm">{{ __('execution.actions.edit') }}</a>
                @endcan
            </x-slot:actions>
        </x-chaine.dossier-header>
    </x-slot>

    <x-ds.page-shell class="space-y-4">
        <x-chaine.financial-summary :title="__('Synthèse')">
            <x-chaine.financial-metric :label="__('Engagement')"><span class="font-mono">{{ $paiement->ordonnancement?->liquidation?->engagement?->numero }}</span></x-chaine.financial-metric>
            <x-chaine.financial-metric :label="__('Montant payé')" tone="primary"><x-ds.money :value="$paiement->montant_paye" :decimals="2" :show-currency="false" /></x-chaine.financial-metric>
            <x-chaine.financial-metric :label="__('Date')" :value="$paiement->date_paiement?->format('d/m/Y') ?? '—'" />
            <x-chaine.financial-metric :label="__('Mode')" :value="$paiement->mode_paiement ?? '—'" />
        </x-chaine.financial-summary>

        <x-ds.card>
            <div class="grid md:grid-cols-2 gap-3 text-sm">
                <div><span class="text-gray-500">{{ __('Référence') }}</span><p class="font-medium">{{ $paiement->reference_paiement ?? '—' }}</p></div>
                <div><span class="text-gray-500">{{ __('Ligne') }}</span><p class="font-medium">{{ $paiement->engagementImputation?->ligneBudgetaire?->code ?? '—' }}</p></div>
                <div><span class="text-gray-500">{{ __('Saisi par') }}</span><p class="font-medium">{{ $paiement->createur?->name ?? '—' }}</p></div>
            </div>
        </x-ds.card>

        @can('cancel', $paiement)
            @include('execution.partials.cancel-form', ['cancelRoute' => route('execution.paiements.cancel', $paiement)])
        @endcan
    </x-ds.page-shell>
</x-app-layout>
