<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">{{ __('Exercice') }} {{ $exercice_budgetaire->annee }}</h2></x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm space-y-2">
            <div>{{ __('Période') }} : {{ $exercice_budgetaire->date_debut?->format('d/m/Y') }} — {{ $exercice_budgetaire->date_fin?->format('d/m/Y') }}</div>
            <div>{{ __('Statut') }} : {{ $exercice_budgetaire->statut instanceof \BackedEnum ? $exercice_budgetaire->statut->value : $exercice_budgetaire->statut }}</div>
            <div>{{ __('Actif') }} : {{ $exercice_budgetaire->est_actif ? __('Oui') : __('Non') }}</div>
            <div>{{ __('Nombre de lignes') }} : {{ $exercice_budgetaire->lignes_budgetaires_count }}</div>
        </div>
        <a href="{{ route('budget.exercices.index') }}" class="text-sm text-indigo-600">{{ __('← Liste') }}</a>
    </div>
</x-app-layout>
