<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Axes stratégiques — {{ __('Consultation') }}</h2></x-slot>
    <div class="py-8"><div class="max-w-3xl mx-auto sm:px-6 lg:px-8 bg-white p-6 shadow-sm rounded-lg space-y-2">
        <p><strong>{{ __('Code') }}:</strong> {{ $axe_strategique->code }}</p>
        <p><strong>{{ __('Libellé') }}:</strong> {{ $axe_strategique->libelle }}</p>
        <p><strong>{{ __('Description') }}:</strong> {{ $axe_strategique->description }}</p>
        <p><strong>{{ __('Statut') }}:</strong> {{ $axe_strategique->statut }}</p>
        <a href="{{ route('rbm.axes-strategiques.edit', $axe_strategique) }}" class="text-indigo-600">{{ __('Modifier') }}</a>
    </div></div>
</x-app-layout>