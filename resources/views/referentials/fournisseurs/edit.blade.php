<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">{{ __('Modifier le fournisseur') }}</h2></x-slot>
    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <form method="POST" action="{{ route('referentials.fournisseurs.update', $fournisseur) }}" class="space-y-6">@csrf @method('PATCH')
                @include('referentials.fournisseurs._form', ['fournisseur' => $fournisseur])
                <div class="flex gap-4"><x-primary-button>{{ __('Mettre à jour') }}</x-primary-button>
                    <a href="{{ route('referentials.fournisseurs.index') }}" class="text-sm text-gray-600">{{ __('Retour') }}</a></div>
            </form>
        </div>
    </div>
</x-app-layout>
