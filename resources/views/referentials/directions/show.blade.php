<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">{{ $direction->code }} — {{ $direction->libelle }}</h2></x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm space-y-2">
            <div><span class="text-gray-600 font-medium">{{ __('Département') }} :</span> {{ $direction->departement?->libelle }}</div>
            <div><span class="text-gray-600 font-medium">{{ __('Statut') }} :</span> {{ $direction->statut }}</div>
            <div><span class="text-gray-600 font-medium">{{ __('Services') }} :</span> {{ $direction->services_count }}</div>
        </div>
        <a href="{{ route('referentials.directions.index') }}" class="text-sm text-indigo-600">{{ __('← Liste') }}</a>
    </div>
</x-app-layout>
