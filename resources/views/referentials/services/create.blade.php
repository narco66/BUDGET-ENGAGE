<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">{{ __('Nouveau service') }}</h2></x-slot>
    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <form method="POST" action="{{ route('referentials.services.store') }}" class="space-y-6">@csrf
                @include('referentials.services._form', ['directions' => $directions])
                <div class="flex gap-4"><x-primary-button>{{ __('Enregistrer') }}</x-primary-button>
                    <a href="{{ route('referentials.services.index') }}" class="text-sm text-gray-600">{{ __('Annuler') }}</a></div>
            </form>
        </div>
    </div>
</x-app-layout>
