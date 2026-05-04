<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Modifier la direction') }}</h2></x-slot>
    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('referentials.directions.update', $direction) }}" class="space-y-6">
                    @csrf @method('PATCH')
                    @include('referentials.directions._form', ['direction' => $direction, 'departements' => $departements])
                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Mettre à jour') }}</x-primary-button>
                        <a href="{{ route('referentials.directions.index') }}" class="text-sm text-gray-600">{{ __('Retour') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
