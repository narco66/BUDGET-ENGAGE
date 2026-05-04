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
                <div>{{ __('Engagé') }} : {{ number_format((float) $ligne_budgetaire->montant_engage, 2, ',', ' ') }}</div>
                <div>{{ __('Payé') }} : {{ number_format((float) $ligne_budgetaire->montant_paye, 2, ',', ' ') }}</div>
            </div>
        </div>
        <a href="{{ route('budget.lignes.index') }}" class="text-sm text-indigo-600">{{ __('← Liste') }}</a>
    </div>
</x-app-layout>
