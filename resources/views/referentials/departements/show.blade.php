<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Département') }} — {{ $departement->code }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-2 text-sm">
                <div><span class="font-medium text-gray-600">{{ __('Libellé') }} :</span> {{ $departement->libelle }}</div>
                <div><span class="font-medium text-gray-600">{{ __('Statut') }} :</span> {{ $departement->statut }}</div>
                @if ($departement->description)
                    <div><span class="font-medium text-gray-600">{{ __('Description') }} :</span> {{ $departement->description }}</div>
                @endif
                <div><span class="font-medium text-gray-600">{{ __('Nombre de directions') }} :</span> {{ $departement->directions_count }}</div>
            </div>
            <a href="{{ route('referentials.departements.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">{{ __('← Retour à la liste') }}</a>
        </div>
    </div>
</x-app-layout>
