@php
    $libelles = [
        'disponibilite' => 'Disponibilité des crédits',
        'conformite_budgetaire' => 'Conformité budgétaire',
        'conformite_comptable' => 'Conformité comptable',
        'coherence_imputations' => 'Cohérence des imputations',
        'conformite_documentaire' => 'Conformité documentaire',
    ];
@endphp
<div class="space-y-2">
    @foreach ($libelles as $key => $lib)
        @php($ok = $controles[$key] ?? false)
        <div class="flex items-center justify-between text-sm border-b border-gray-100 py-2">
            <span>{{ $lib }}</span>
            @if ($ok)
                <span class="badge badge-success">{{ __('Conforme') }}</span>
            @else
                <span class="badge badge-danger">{{ __('Non conforme') }}</span>
            @endif
        </div>
    @endforeach
    <p class="text-xs text-gray-400 pt-2">{{ __('Tous les contrôles doivent être conformes avant la génération du mandat.') }}</p>
</div>
