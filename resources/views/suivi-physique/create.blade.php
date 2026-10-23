<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">{{ __('Saisie suivi physique') }}</h2></x-slot>
    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('suivi-physique.store') }}" class="bg-white shadow-sm rounded-lg p-6 space-y-4">
            @csrf
            <div>
                <x-input-label for="exercice_budgetaire_id" :value="__('Exercice')" />
                <select id="exercice_budgetaire_id" name="exercice_budgetaire_id" class="block mt-1 w-full border-gray-300 rounded-md" required>
                    @foreach ($exercices as $ex)
                        <option value="{{ $ex->id }}" @selected($exerciceId === $ex->id)>{{ $ex->annee }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="activite_id" :value="__('Activité RBM')" />
                <select id="activite_id" name="activite_id" class="block mt-1 w-full border-gray-300 rounded-md" required>
                    @foreach ($activites as $a)
                        <option value="{{ $a->id }}">{{ $a->code }} — {{ $a->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><x-input-label for="trimestre" :value="__('Trimestre')" /><x-text-input id="trimestre" name="trimestre" type="number" min="1" max="4" class="block mt-1 w-full" :value="old('trimestre', now()->quarter)" required /></div>
                <div><x-input-label for="annee" :value="__('Année')" /><x-text-input id="annee" name="annee" type="number" class="block mt-1 w-full" :value="old('annee', now()->year)" required /></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><x-input-label for="periode_debut" :value="__('Début période')" /><x-text-input id="periode_debut" name="periode_debut" type="date" class="block mt-1 w-full" required /></div>
                <div><x-input-label for="periode_fin" :value="__('Fin période')" /><x-text-input id="periode_fin" name="periode_fin" type="date" class="block mt-1 w-full" required /></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><x-input-label for="taux_avancement" :value="__('Taux avancement (%)')" /><x-text-input id="taux_avancement" name="taux_avancement" type="number" step="0.01" min="0" max="100" class="block mt-1 w-full" required /></div>
                <div><x-input-label for="quantite_realisee" :value="__('Quantité réalisée')" /><x-text-input id="quantite_realisee" name="quantite_realisee" type="number" step="0.0001" min="0" class="block mt-1 w-full" /></div>
            </div>
            <div><x-input-label for="libelle_realisation" :value="__('Libellé réalisation')" /><x-text-input id="libelle_realisation" name="libelle_realisation" class="block mt-1 w-full" /></div>
            <div><x-input-label for="commentaire" :value="__('Commentaire')" /><textarea id="commentaire" name="commentaire" rows="3" class="block mt-1 w-full border-gray-300 rounded-md">{{ old('commentaire') }}</textarea></div>
            <div class="flex gap-3">
                <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>
                <a href="{{ route('suivi-physique.index') }}"><x-secondary-button type="button">{{ __('Annuler') }}</x-secondary-button></a>
            </div>
        </form>
    </div>
</x-app-layout>
