<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Sources de vérification — {{ __('Consultation') }}</h2></x-slot>
    <div class="py-8"><div class="max-w-3xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow-sm rounded-lg space-y-2">
        <p><strong>{{ __('Code') }}:</strong> {{ $source_verification->code }}</p>
        <p><strong>{{ __('Libellé') }}:</strong> {{ $source_verification->libelle }}</p>
        <p><strong>{{ __('Description') }}:</strong> {{ $source_verification->description }}</p>
        <p><strong>{{ __('Statut') }}:</strong> {{ $source_verification->statut }}</p>
        <a href="{{ route('rbm.sources-verification.edit', $source_verification) }}" class="text-indigo-600">{{ __('Modifier') }}</a>
    </div></div>
</x-app-layout>