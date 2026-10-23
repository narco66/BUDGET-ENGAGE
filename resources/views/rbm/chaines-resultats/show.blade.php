<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Chaînes de résultats — {{ __('Consultation') }}</h2></x-slot>
    <div class="py-8"><div class="max-w-3xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow-sm rounded-lg space-y-2">
        <p><strong>{{ __('Code') }}:</strong> {{ $chaine_resultat->code }}</p>
        <p><strong>{{ __('Libellé') }}:</strong> {{ $chaine_resultat->libelle }}</p>
        <p><strong>{{ __('Description') }}:</strong> {{ $chaine_resultat->description }}</p>
        <p><strong>{{ __('Statut') }}:</strong> {{ $chaine_resultat->statut }}</p>
        <a href="{{ route('rbm.chaines-resultats.edit', $chaine_resultat) }}" class="text-indigo-600">{{ __('Modifier') }}</a>
    </div></div>
</x-app-layout>