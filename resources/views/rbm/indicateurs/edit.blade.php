<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">{{ __('Indicateurs') }} — {{ __('Modification') }}</h2></x-slot>
    <div class="py-8"><div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('rbm.indicateurs.update', $indicateur) }}" class="bg-white p-6 shadow-sm rounded-lg space-y-4">
            @csrf @method('PUT') @include('rbm.indicateurs._form', ['indicateur' => $indicateur])
            <div class="flex gap-2"><x-primary-button>{{ __('Enregistrer') }}</x-primary-button></div>
        </form>
    </div></div>
</x-app-layout>