<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">{{ __('Modifier l’exercice') }} {{ $exercice_budgetaire->annee }}</h2></x-slot>
    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <form method="POST" action="{{ route('budget.exercices.update', $exercice_budgetaire) }}" class="space-y-6">@csrf @method('PATCH')
                @include('budget.exercices._form', ['exercice_budgetaire' => $exercice_budgetaire])
                <div class="flex gap-4"><x-primary-button>{{ __('Mettre à jour') }}</x-primary-button>
                    <a href="{{ route('budget.exercices.index') }}" class="text-sm text-gray-600">{{ __('Retour') }}</a></div>
            </form>
        </div>
    </div>
</x-app-layout>
