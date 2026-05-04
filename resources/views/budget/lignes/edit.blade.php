<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">{{ __('Modifier la ligne') }} {{ $ligne->code }}</h2></x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <form method="POST" action="{{ route('budget.lignes.update', $ligne) }}" class="space-y-6">@csrf @method('PATCH')
                @include('budget.lignes._form', [
                    'ligne' => $ligne,
                    'exercices' => $exercices,
                    'departements' => $departements,
                    'directions' => $directions,
                    'services' => $services,
                    'natures' => $natures,
                    'sources' => $sources,
                ])
                <div class="flex gap-4"><x-primary-button>{{ __('Mettre à jour') }}</x-primary-button>
                    <a href="{{ route('budget.lignes.index') }}" class="text-sm text-gray-600">{{ __('Retour') }}</a></div>
            </form>
        </div>
    </div>
</x-app-layout>
