<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">{{ __('Modifier') }} {{ $engagement->numero }}</h2></x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <form method="POST" action="{{ route('engagements.update', $engagement) }}" class="space-y-6">@csrf @method('PATCH')
                @include('engagements._form', [
                    'engagement' => $engagement,
                    'exercices' => $exercices,
                    'lignes' => $lignes,
                    'departements' => $departements,
                    'directions' => $directions,
                    'services' => $services,
                    'fournisseurs' => $fournisseurs,
                    'agents' => $agents,
                ])
                <div class="flex gap-4"><x-primary-button>{{ __('Mettre à jour') }}</x-primary-button>
                    <a href="{{ route('engagements.index') }}" class="text-sm text-gray-600">{{ __('Retour') }}</a></div>
            </form>
        </div>
    </div>
</x-app-layout>
