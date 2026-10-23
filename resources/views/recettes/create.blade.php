<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">{{ __('Nouvelle perception de recette') }}</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('recettes.perceptions.store') }}" class="bg-white shadow-sm rounded-lg p-6 space-y-4">
            @csrf
            <div>
                <x-input-label for="exercice_budgetaire_id" :value="__('Exercice')" />
                <select id="exercice_budgetaire_id" name="exercice_budgetaire_id" class="block mt-1 w-full border-gray-300 rounded-md" required onchange="window.location='{{ route('recettes.perceptions.create') }}?exercice_budgetaire_id='+this.value">
                    @foreach ($exercices as $ex)
                        <option value="{{ $ex->id }}" @selected($exerciceId === $ex->id)>{{ $ex->annee }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="budget_ligne_id" :value="__('Ligne de recette')" />
                <select id="budget_ligne_id" name="budget_ligne_id" class="block mt-1 w-full border-gray-300 rounded-md" required>
                    @foreach ($lignesRecette as $ligne)
                        <option value="{{ $ligne->id }}">{{ $ligne->code }} — {{ $ligne->libelle }} ({{ number_format((float) $ligne->montant_total, 0, ',', ' ') }})</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="date_perception" :value="__('Date perception')" />
                    <x-text-input id="date_perception" name="date_perception" type="date" class="block mt-1 w-full" :value="old('date_perception', now()->toDateString())" required />
                </div>
                <div>
                    <x-input-label for="montant" :value="__('Montant (FCFA)')" />
                    <x-text-input id="montant" name="montant" type="number" step="0.01" min="0.01" class="block mt-1 w-full" :value="old('montant')" required />
                </div>
            </div>
            <div>
                <x-input-label for="source_emetteur" :value="__('Source / émetteur')" />
                <x-text-input id="source_emetteur" name="source_emetteur" class="block mt-1 w-full" :value="old('source_emetteur')" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="reference" :value="__('Référence')" />
                    <x-text-input id="reference" name="reference" class="block mt-1 w-full" :value="old('reference')" />
                </div>
                <div>
                    <x-input-label for="mode_reglement" :value="__('Mode règlement')" />
                    <select id="mode_reglement" name="mode_reglement" class="block mt-1 w-full border-gray-300 rounded-md">
                        @foreach (['virement', 'cheque', 'especes', 'autre'] as $mode)
                            <option value="{{ $mode }}" @selected(old('mode_reglement', 'virement') === $mode)>{{ ucfirst($mode) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <x-input-label for="observation" :value="__('Observation')" />
                <textarea id="observation" name="observation" rows="3" class="block mt-1 w-full border-gray-300 rounded-md">{{ old('observation') }}</textarea>
            </div>
            @can('recettes.validate')
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="valider" value="1" class="rounded border-gray-300">
                    {{ __('Valider immédiatement') }}
                </label>
            @endcan
            <div class="flex gap-3">
                <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>
                <a href="{{ route('recettes.dashboard') }}"><x-secondary-button type="button">{{ __('Annuler') }}</x-secondary-button></a>
            </div>
        </form>
    </div>
</x-app-layout>
