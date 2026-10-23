<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">{{ __('Indicateur') }}</h2></x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow-sm rounded-lg space-y-2">
        <p><strong>{{ __('Code') }}:</strong> {{ $indicateur->code }}</p>
        <p><strong>{{ __('Type') }}:</strong> {{ $indicateur->typeIndicateur?->libelle }}</p>
        <p><strong>{{ __('Unité') }}:</strong> {{ $indicateur->uniteMesure?->libelle }}</p>
        <p><strong>{{ __('Tâches') }}:</strong> {{ $indicateur->taches->pluck('libelle')->join(', ') }}</p>
        <p><strong>{{ __('Sources') }}:</strong> {{ $indicateur->sourcesVerification->pluck('libelle')->join(', ') }}</p>
    </div>
</x-app-layout>