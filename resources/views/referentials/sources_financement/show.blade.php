<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">{{ $source->code }}</h2></x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm space-y-2">
            <div>{{ __('Libellé') }} : {{ $source->libelle }}</div>
            <div>{{ __('Statut') }} : {{ $source->statut }}</div>
        </div>
        <a href="{{ route('referentials.sources-financement.index') }}" class="text-sm text-indigo-600">{{ __('← Liste') }}</a>
    </div>
</x-app-layout>
