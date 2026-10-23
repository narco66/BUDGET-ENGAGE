<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl font-mono">{{ $ligne_budgetaire->code }}</h2></x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm space-y-2">
            <div>{{ __('Libellé') }} : {{ $ligne_budgetaire->libelle }}</div>
            <div>{{ __('Exercice') }} : {{ $ligne_budgetaire->exerciceBudgetaire?->annee ?? '—' }}</div>
            <div>{{ __('Nature') }} : {{ $ligne_budgetaire->natureDepense?->libelle ?? '—' }}</div>
            <div>{{ __('Source') }} : {{ $ligne_budgetaire->sourceFinancement?->libelle ?? '—' }}</div>
            <div>{{ __('Statut') }} : {{ $ligne_budgetaire->statut }}</div>
            <hr class="my-3">
            <div class="grid grid-cols-2 gap-2 tabular-nums">
                <div>{{ __('Voté') }} : {{ number_format((float) $ligne_budgetaire->montant_vote, 2, ',', ' ') }}</div>
                <div>{{ __('Révisé') }} : {{ number_format((float) $ligne_budgetaire->montant_revise, 2, ',', ' ') }}</div>
                <div>{{ __('Plafond') }} : {{ number_format((float) $restes['plafond'], 2, ',', ' ') }}</div>
                <div>{{ __('Crédit disponible') }} : {{ number_format((float) $restes['credit_disponible'], 2, ',', ' ') }}</div>
                <div>{{ __('Engagé') }} : {{ number_format((float) $ligne_budgetaire->montant_engage, 2, ',', ' ') }}</div>
                <div>{{ __('Liquidé') }} : {{ number_format((float) $ligne_budgetaire->montant_liquide, 2, ',', ' ') }}</div>
                <div>{{ __('Ordonnancé') }} : {{ number_format((float) $ligne_budgetaire->montant_ordonnance, 2, ',', ' ') }}</div>
                <div>{{ __('Payé') }} : {{ number_format((float) $ligne_budgetaire->montant_paye, 2, ',', ' ') }}</div>
            </div>
            <hr class="my-3">
            <div class="font-medium text-gray-700 mb-2">{{ __('Restes à exécuter') }}</div>
            <div class="grid grid-cols-2 gap-2 tabular-nums text-amber-800">
                <div>{{ __('Reste à engager') }} : {{ number_format((float) $restes['reste_a_engager'], 2, ',', ' ') }}</div>
                <div>{{ __('Reste à liquider') }} : {{ number_format((float) $restes['reste_a_liquider'], 2, ',', ' ') }}</div>
                <div>{{ __('Reste à ordonnancer') }} : {{ number_format((float) $restes['reste_a_ordonnancer'], 2, ',', ' ') }}</div>
                <div>{{ __('Reste à payer') }} : {{ number_format((float) $restes['reste_a_payer'], 2, ',', ' ') }}</div>
            </div>
        </div>
        <div class="flex gap-4 text-sm">
            <a href="{{ route('budget.lignes.index') }}" class="text-indigo-600">{{ __('← Liste') }}</a>
            @can('budget.view')
                <a href="{{ route('budget.situation.index', ['exercice_budgetaire_id' => $ligne_budgetaire->exercice_budgetaire_id]) }}" class="text-indigo-600">{{ __('Situation budgétaire →') }}</a>
            @endcan
        </div>
    </div>
</x-app-layout>
