<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Sous-produits — {{ __('Consultation') }}</h2></x-slot>
    <div class="py-8"><div class="max-w-3xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow-sm rounded-lg space-y-2">
        <p><strong>{{ __('Code') }}:</strong> {{ $sous_produit->code }}</p>
        <p><strong>{{ __('Libellé') }}:</strong> {{ $sous_produit->libelle }}</p>
        <p><strong>{{ __('Description') }}:</strong> {{ $sous_produit->description }}</p>
        <p><strong>{{ __('Statut') }}:</strong> {{ $sous_produit->statut }}</p>
        <a href="{{ route('rbm.sous-produits.edit', $sous_produit) }}" class="text-indigo-600">{{ __('Modifier') }}</a>
    </div></div>
</x-app-layout>