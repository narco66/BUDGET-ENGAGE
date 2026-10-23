@php
    $decisionLabels = ['en_attente' => ['En attente', 'badge-neutral'], 'valide' => ['Validé', 'badge-success'], 'rejete' => ['Rejeté', 'badge-danger'], 'retourne' => ['Retourné', 'badge-warning']];
    $stepLabels = [
        'controle_technique' => 'Contrôle technique',
        'controle_administratif' => 'Contrôle administratif',
        'controle_budgetaire' => 'Contrôle budgétaire',
        'controle_financier' => 'Contrôle financier',
        'validation' => 'Validation finale',
    ];
@endphp

<table class="data-table">
    <thead><tr><th>{{ __('Étape') }}</th><th>{{ __('Décision') }}</th><th>{{ __('Validateur') }}</th><th>{{ __('Date') }}</th></tr></thead>
    <tbody>
        @forelse ($liquidation->validations as $v)
            @php([$lib, $cls] = $decisionLabels[$v->decision?->value] ?? ['—', 'badge-neutral'])
            <tr>
                <td>{{ $stepLabels[$v->etape] ?? $v->etape }}</td>
                <td><span class="badge {{ $cls }}">{{ $lib }}</span></td>
                <td class="text-sm">{{ $v->validateur?->name ?? '—' }}</td>
                <td class="text-sm">{{ optional($v->date_decision)->format('d/m/Y H:i') ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-gray-400 text-center py-4">{{ __('Liquidation non soumise au circuit.') }}</td></tr>
        @endforelse
    </tbody>
</table>

@if ($pendingStep && $canValidatePending)
    <div class="rounded-lg border border-ceeac-primary/30 bg-ceeac-primary/5 p-4">
        <h4 class="text-sm font-semibold mb-3">{{ __('Décision pour l’étape') }} : {{ $stepLabels[$pendingStep->etape] ?? $pendingStep->etape }}</h4>
        <form method="POST" action="{{ route('liquidations.decision', $liquidation) }}" class="space-y-3">@csrf
            <div class="flex flex-wrap gap-4 text-sm">
                <label class="inline-flex items-center gap-1"><input type="radio" name="decision" value="valide" checked class="form-radio"> {{ __('Valider') }}</label>
                <label class="inline-flex items-center gap-1"><input type="radio" name="decision" value="retourne" class="form-radio"> {{ __('Retourner') }}</label>
                <label class="inline-flex items-center gap-1"><input type="radio" name="decision" value="rejete" class="form-radio"> {{ __('Rejeter') }}</label>
            </div>
            <div>
                <x-input-label for="commentaire" :value="__('Commentaire (obligatoire si retour/rejet)')" />
                <textarea id="commentaire" name="commentaire" rows="2" class="form-textarea mt-1 block w-full">{{ old('commentaire') }}</textarea>
            </div>
            <button class="btn btn-primary btn-sm">{{ __('Enregistrer la décision') }}</button>
        </form>
    </div>
@endif
