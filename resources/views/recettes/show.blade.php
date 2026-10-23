<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl font-mono">{{ $perception->numero }}</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if (session('status'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('status') }}</div>
        @endif

        <div class="bg-white shadow-sm rounded-lg p-6 text-sm space-y-2">
            <div>{{ __('Exercice') }} : {{ $perception->exerciceBudgetaire?->annee }}</div>
            <div>{{ __('Ligne') }} : {{ $perception->budgetLigne?->code }} — {{ $perception->budgetLigne?->libelle }}</div>
            <div>{{ __('Date') }} : {{ $perception->date_perception?->format('d/m/Y') }}</div>
            <div>{{ __('Montant') }} : {{ number_format((float) $perception->montant, 2, ',', ' ') }} FCFA</div>
            <div>{{ __('Statut') }} : {{ $perception->statut->value }}</div>
            <div>{{ __('Source') }} : {{ $perception->source_emetteur ?? '—' }}</div>
            <div>{{ __('Référence') }} : {{ $perception->reference ?? '—' }}</div>
        </div>

        <div class="flex flex-wrap gap-3">
            @can('validate', $perception)
                @if ($perception->statut === \App\Enums\StatutRecettePerception::Brouillon)
                    <form method="POST" action="{{ route('recettes.perceptions.validate', $perception) }}">@csrf
                        <x-primary-button>{{ __('Valider') }}</x-primary-button>
                    </form>
                @endif
            @endcan
            @can('cancel', $perception)
                @if ($perception->statut !== \App\Enums\StatutRecettePerception::Annule)
                    <form method="POST" action="{{ route('recettes.perceptions.cancel', $perception) }}" class="flex gap-2 items-end">
                        @csrf
                        <div>
                            <x-input-label for="motif_annulation" :value="__('Motif annulation')" />
                            <x-text-input id="motif_annulation" name="motif_annulation" class="block mt-1" required />
                        </div>
                        <x-danger-button>{{ __('Annuler') }}</x-danger-button>
                    </form>
                @endif
            @endcan
            <a href="{{ route('recettes.dashboard', ['exercice_budgetaire_id' => $perception->exercice_budgetaire_id]) }}" class="text-sm text-indigo-600 self-center">{{ __('← Tableau de bord recettes') }}</a>
        </div>
    </div>
</x-app-layout>
