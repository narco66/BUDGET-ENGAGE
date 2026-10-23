<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">{{ __('Réalisation indicateur') }}</h2></x-slot>
    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if (session('status'))<div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('status') }}</div>@endif
        <div class="bg-white shadow-sm rounded-lg p-6 text-sm space-y-2">
            <div>{{ __('Indicateur') }} : {{ $realisation->indicateur?->code }} — {{ $realisation->indicateur?->libelle }}</div>
            <div>{{ __('Période') }} : T{{ $realisation->trimestre }} {{ $realisation->annee }}</div>
            <div>{{ __('Cible / Réalisé') }} : {{ $realisation->valeur_cible ?? '—' }} / {{ $realisation->valeur_realisee }}</div>
            <div>{{ __('Taux') }} : {{ $realisation->tauxRealisation() !== null ? $realisation->tauxRealisation().' %' : '—' }}</div>
            <div>{{ __('Statut') }} : {{ $realisation->statut->value }}</div>
        </div>
        @can('validate', $realisation)
            @if (in_array($realisation->statut, [\App\Enums\StatutSuiviRealisation::Brouillon, \App\Enums\StatutSuiviRealisation::Soumis], true))
                <form method="POST" action="{{ route('suivi-evaluation.validate', $realisation) }}">@csrf<x-primary-button>{{ __('Valider') }}</x-primary-button></form>
            @endif
        @endcan
        <a href="{{ route('suivi-evaluation.index', ['exercice_budgetaire_id' => $realisation->exercice_budgetaire_id, 'trimestre' => $realisation->trimestre]) }}" class="text-sm text-indigo-600">{{ __('← Rapport S&E') }}</a>
    </div>
</x-app-layout>
