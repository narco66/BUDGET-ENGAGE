<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Import RBM/GAR') }}</h2>
            <a href="{{ route('rbm.import.template') }}">
                <x-secondary-button type="button">{{ __('Télécharger le modèle Excel') }}</x-secondary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white p-6 shadow-sm rounded-lg text-sm text-gray-600 space-y-2">
                <p>{{ __('Importez une matrice hiérarchique complète : Pilier → Axe → Produit → Sous-produit → Activité → Tâche → Indicateur (optionnel).') }}</p>
                <p>{{ __('Les types d\'indicateurs et unités de mesure doivent exister avant l\'import des indicateurs.') }}</p>
            </div>

            <form method="POST" action="{{ route('rbm.import.store') }}" enctype="multipart/form-data" class="bg-white p-6 shadow-sm rounded-lg space-y-4">
                @csrf
                <div>
                    <x-input-label for="fichier" :value="__('Fichier Excel (.xlsx, .xls, .csv)')" />
                    <input id="fichier" name="fichier" type="file" accept=".xlsx,.xls,.csv" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required />
                    <x-input-error class="mt-2" :messages="$errors->get('fichier')" />
                </div>
                <x-primary-button>{{ __('Importer') }}</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
