<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Chaînes de résultats — {{ __('Modification') }}</h2></x-slot>
    <div class="py-8"><div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('rbm.chaines-resultats.update', $chaine_resultat) }}" class="bg-white p-6 shadow-sm rounded-lg space-y-4">
            @csrf @method('PUT')
            @include('rbm.chaines-resultats._form', ['item' => $chaineResultat])
            <div class="flex gap-2">
                <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>
                <a href="{{ route('rbm.chaines-resultats.index') }}"><x-secondary-button type="button">{{ __('Annuler') }}</x-secondary-button></a>
            </div>
        </form>
    </div></div>
</x-app-layout>