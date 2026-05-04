<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">{{ $agent->nom }} {{ $agent->prenom }}</h2></x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm space-y-2">
            @if($agent->matricule)
                <div>{{ __('Matricule') }} : {{ $agent->matricule }}</div>
            @endif
            <div>{{ __('Statut') }} : {{ $agent->statut }}</div>
            @if($agent->service)
                <div>{{ __('Service') }} : {{ $agent->service->libelle }}</div>
                <div>{{ __('Direction') }} : {{ $agent->service->direction->libelle ?? '—' }}</div>
                <div>{{ __('Département') }} : {{ $agent->service->direction->departement->libelle ?? '—' }}</div>
            @endif
        </div>
        <a href="{{ route('referentials.agents.index') }}" class="text-sm text-indigo-600">{{ __('← Liste') }}</a>
    </div>
</x-app-layout>
