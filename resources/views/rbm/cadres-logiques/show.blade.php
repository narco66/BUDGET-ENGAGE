<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Cadres logiques — {{ __('Consultation') }}</h2></x-slot>
    <div class="py-8"><div class="max-w-3xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow-sm rounded-lg space-y-2">
        <p><strong>{{ __('Code') }}:</strong> {{ $cadre_logique->code }}</p>
        <p><strong>{{ __('Libellé') }}:</strong> {{ $cadre_logique->libelle }}</p>
        <p><strong>{{ __('Description') }}:</strong> {{ $cadre_logique->description }}</p>
        <p><strong>{{ __('Statut') }}:</strong> {{ $cadre_logique->statut }}</p>
        <a href="{{ route('rbm.cadres-logiques.edit', $cadre_logique) }}" class="text-indigo-600">{{ __('Modifier') }}</a>
    </div></div>
</x-app-layout>