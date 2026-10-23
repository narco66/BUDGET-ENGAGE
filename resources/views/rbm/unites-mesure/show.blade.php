<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Unités de mesure — {{ __('Consultation') }}</h2></x-slot>
    <div class="py-8"><div class="max-w-3xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow-sm rounded-lg space-y-2">
        <p><strong>{{ __('Code') }}:</strong> {{ $unite_mesure->code }}</p>
        <p><strong>{{ __('Libellé') }}:</strong> {{ $unite_mesure->libelle }}</p>
        <p><strong>{{ __('Description') }}:</strong> {{ $unite_mesure->description }}</p>
        <p><strong>{{ __('Statut') }}:</strong> {{ $unite_mesure->statut }}</p>
        <a href="{{ route('rbm.unites-mesure.edit', $unite_mesure) }}" class="text-indigo-600">{{ __('Modifier') }}</a>
    </div></div>
</x-app-layout>