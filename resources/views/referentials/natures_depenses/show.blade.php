<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">{{ $nature->code }}</h2></x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm space-y-2">
            <div>{{ __('Libellé') }} : {{ $nature->libelle }}</div>
            <div>{{ __('Statut') }} : {{ $nature->statut }}</div>
            @if ($nature->description)<div>{{ __('Description') }} : {{ $nature->description }}</div>@endif
        </div>
        <a href="{{ route('referentials.natures-depenses.index') }}" class="text-sm text-indigo-600">{{ __('← Liste') }}</a>
    </div>
</x-app-layout>
