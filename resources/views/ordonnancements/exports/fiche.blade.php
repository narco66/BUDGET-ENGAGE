@extends('pdf.layout')

@section('title', 'Fiche d\'ordonnancement')
@section('reference', $ordonnancement->numero)

@php
    $pdfReference = $ordonnancement->numero;
    $pdfExercice = $ordonnancement->liquidation?->engagement?->exerciceBudgetaire?->annee;
    $pdfGeneratedAt = $generatedAt->format('d/m/Y H:i');
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
@endphp

@section('subtitle')
    {{ $ordonnancement->statut?->libelle() }}
@endsection

@section('content')
    <h2 class="pdf-section">{{ __('Identification') }}</h2>
    <table class="pdf-meta">
        <tr><td class="label">{{ __('Liquidation') }}</td><td>{{ $ordonnancement->liquidation?->numero }}</td><td class="label">{{ __('Exercice') }}</td><td>{{ $ordonnancement->liquidation?->engagement?->exerciceBudgetaire?->annee }}</td></tr>
        <tr><td class="label">{{ __('Type') }}</td><td>{{ $ordonnancement->type?->libelle ?? '—' }}</td><td class="label">{{ __('Mode') }}</td><td>{{ $ordonnancement->mode_creation?->libelle() ?? '—' }}</td></tr>
        <tr><td class="label">{{ __('Fournisseur') }}</td><td>{{ $ordonnancement->fournisseur?->raison_sociale ?? '—' }}</td><td class="label">{{ __('Date') }}</td><td>{{ optional($ordonnancement->date_ordonnancement)->format('d/m/Y') }}</td></tr>
        <tr><td class="label">{{ __('Objet') }}</td><td colspan="3">{{ $ordonnancement->objet }}</td></tr>
    </table>

    <h2 class="pdf-section">{{ __('Lignes d\'ordonnancement') }}</h2>
    <table class="pdf-data">
        <thead><tr><th>{{ __('Désignation') }}</th><th>{{ __('Ligne budg.') }}</th><th class="pdf-right">{{ __('Liquidé') }}</th><th class="pdf-right">{{ __('Ordonnancé') }}</th><th>{{ __('Mode') }}</th></tr></thead>
        <tbody>
            @foreach ($ordonnancement->lignes as $l)
                <tr>
                    <td>{{ $l->designation }}</td>
                    <td>{{ $l->ligneBudgetaire?->code ?? '—' }}</td>
                    <td class="pdf-right">{{ $fmt($l->montant_liquide) }}</td>
                    <td class="pdf-right">{{ $fmt($l->montant_ordonnance) }}</td>
                    <td>{{ $l->mode_paiement ?? '—' }}</td>
                </tr>
            @endforeach
            <tr class="total"><td colspan="3" class="pdf-right">{{ __('Montant ordonnancé') }}</td><td class="pdf-right">{{ $fmt($ordonnancement->montant_ordonnance) }}</td><td></td></tr>
        </tbody>
    </table>

    <h2 class="pdf-section">{{ __('Circuit de validation') }}</h2>
    <table class="pdf-data">
        <thead><tr><th>{{ __('Étape') }}</th><th>{{ __('Décision') }}</th><th>{{ __('Validateur') }}</th><th>{{ __('Date') }}</th></tr></thead>
        <tbody>
            @forelse ($ordonnancement->validations as $v)
                <tr>
                    <td>{{ $v->etape }}</td>
                    <td>{{ $v->decision?->value }}</td>
                    <td>{{ $v->validateur?->name ?? '—' }}</td>
                    <td>{{ optional($v->date_decision)->format('d/m/Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">{{ __('Non soumis au circuit.') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    @if ($ordonnancement->mandat_numero)
        <p style="margin-top:12px;font-size:9px;">{{ __('Mandat') }} : <strong>{{ $ordonnancement->mandat_numero }}</strong> — {{ __('Visa') }} : {{ $ordonnancement->visa_numero }} — {{ __('Empreinte') }} : {{ \Illuminate\Support\Str::limit($ordonnancement->visa_hash, 32) }}</p>
    @endif
@endsection
