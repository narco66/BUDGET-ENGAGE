@extends('pdf.layout')

@section('title', 'Fiche engagement')
@section('reference', $engagement->numero)

@php
    $pdfReference = $engagement->numero;
    $pdfExercice = $engagement->exerciceBudgetaire?->annee;
    $pdfGeneratedAt = $generatedAt->format('d/m/Y H:i');
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
@endphp

@section('content')
    <table class="pdf-meta">
        <tr><td class="label">{{ __('Statut') }}</td><td>{{ $engagement->statut instanceof \BackedEnum ? $engagement->statut->value : $engagement->statut }}</td></tr>
        <tr><td class="label">{{ __('Exercice') }}</td><td>{{ $engagement->exerciceBudgetaire?->annee ?? '—' }}</td></tr>
        <tr><td class="label">{{ __('Ligne budgétaire') }}</td><td>{{ $engagement->ligneBudgetaire?->code ?? '—' }} — {{ $engagement->ligneBudgetaire?->libelle ?? '—' }}</td></tr>
        <tr><td class="label">{{ __('Montant') }}</td><td>{{ $fmt($engagement->montant) }}</td></tr>
        <tr><td class="label">{{ __('Objet') }}</td><td colspan="3">{{ $engagement->objet }}</td></tr>
        <tr><td class="label">{{ __('Créateur') }}</td><td>{{ $engagement->createur?->name ?? '—' }}</td><td class="label">{{ __('Score risque') }}</td><td>{{ $engagement->score_risque ?? '—' }}</td></tr>
    </table>

    <h2 class="pdf-section">{{ __('Pièces justificatives') }}</h2>
    @if ($engagement->piecesJustificatives->isEmpty())
        <p>{{ __('Aucune pièce justificative.') }}</p>
    @else
        <ul style="margin:0;padding-left:16px;">
            @foreach ($engagement->piecesJustificatives as $piece)
                <li>{{ $piece->nom_original }} ({{ $piece->mime_type ?? 'n/a' }}, {{ $piece->taille ?? 0 }} {{ __('octets') }})</li>
            @endforeach
        </ul>
    @endif

    <h2 class="pdf-section">{{ __('Circuit de validation') }}</h2>
    @if ($engagement->validationsWorkflow->isEmpty())
        <p>{{ __('Aucune étape de workflow.') }}</p>
    @else
        <ul style="margin:0;padding-left:16px;">
            @foreach ($engagement->validationsWorkflow as $step)
                <li>
                    {{ $step->etape }} — {{ $step->decision instanceof \BackedEnum ? $step->decision->value : $step->decision }}
                    @if ($step->validateur) ({{ $step->validateur->name }}) @endif
                </li>
            @endforeach
        </ul>
    @endif
@endsection
