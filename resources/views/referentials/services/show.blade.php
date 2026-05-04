<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">{{ $service->code }} — {{ $service->libelle }}</h2></x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm space-y-2">
            <div>{{ __('Direction') }} : {{ $service->direction?->libelle }} ({{ $service->direction?->departement?->libelle }})</div>
            <div>{{ __('Statut') }} : {{ $service->statut }}</div>
        </div>
        <a href="{{ route('referentials.services.index') }}" class="text-sm text-indigo-600">{{ __('← Liste') }}</a>
    </div>
</x-app-layout>
