@extends('pdf.layout')

@section('title', 'Fiche de liquidation')
@section('reference', $liquidation->numero)

@php
    $pdfReference = $liquidation->numero;
    $pdfExercice = $liquidation->engagement?->exerciceBudgetaire?->annee;
    $pdfGeneratedAt = $generatedAt->format('d/m/Y H:i');
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
    $sf = $liquidation->serviceFait;
@endphp

@section('content')
    <h2 class="pdf-section">Identification</h2>
    <table class="pdf-meta">
        <tr><td class="label">Engagement</td><td>{{ $liquidation->engagement?->numero }}</td><td class="label">Exercice</td><td>{{ $liquidation->engagement?->exerciceBudgetaire?->annee }}</td></tr>
        <tr><td class="label">Type</td><td>{{ $liquidation->type?->libelle ?? '—' }}</td><td class="label">Mode</td><td>{{ $liquidation->mode_creation?->libelle() ?? '—' }}</td></tr>
        <tr><td class="label">Fournisseur</td><td>{{ $liquidation->fournisseur?->raison_sociale ?? '—' }}</td><td class="label">Date</td><td>{{ optional($liquidation->date_liquidation)->format('d/m/Y') }}</td></tr>
        <tr><td class="label">Statut</td><td colspan="3">{{ $liquidation->statut?->libelle() }}</td></tr>
        <tr><td class="label">Objet</td><td colspan="3">{{ $liquidation->objet ?? $liquidation->engagement?->objet }}</td></tr>
    </table>

    <h2 class="pdf-section">Prestations</h2>
    <table class="pdf-data">
        <thead><tr><th>Désignation</th><th>Ligne budg.</th><th class="pdf-right">Brut</th><th class="pdf-right">Retenues</th><th class="pdf-right">Pénalités</th><th class="pdf-right">Net</th></tr></thead>
        <tbody>
            @foreach ($liquidation->lignes as $l)
                <tr>
                    <td>{{ $l->designation }}</td>
                    <td>{{ $l->ligneBudgetaire?->code ?? '—' }}</td>
                    <td class="pdf-right">{{ $fmt($l->montant_brut) }}</td>
                    <td class="pdf-right">{{ $fmt($l->montant_retenues) }}</td>
                    <td class="pdf-right">{{ $fmt($l->montant_penalites) }}</td>
                    <td class="pdf-right">{{ $fmt($l->montant_net) }}</td>
                </tr>
            @endforeach
            <tr class="total"><td colspan="5" class="pdf-right">Montant net liquidé</td><td class="pdf-right">{{ $fmt($liquidation->montant_net) }} FCFA</td></tr>
        </tbody>
    </table>

    <h2 class="pdf-section">Service fait</h2>
    @if ($sf)
        <table class="pdf-meta">
            <tr><td class="label">Constaté le</td><td>{{ optional($sf->date_constatation)->format('d/m/Y') }}</td><td class="label">Validé</td><td>{{ $sf->valide ? 'Oui' : 'Non' }}</td></tr>
            <tr><td class="label">Conformités</td><td colspan="3">
                Quantitative: {{ $sf->conformite_quantitative ? 'OK' : '—' }} ·
                Qualitative: {{ $sf->conformite_qualitative ? 'OK' : '—' }} ·
                Technique: {{ $sf->conformite_technique ? 'OK' : '—' }} ·
                Contractuelle: {{ $sf->conformite_contractuelle ? 'OK' : '—' }}
            </td></tr>
            @if ($sf->reserves)<tr><td class="label">Réserves</td><td colspan="3">{{ $sf->reserves }} {{ $sf->reserves_levees ? '(levées)' : '' }}</td></tr>@endif
        </table>
    @else
        <p>Aucun service fait constaté.</p>
    @endif

    <h2 class="pdf-section">Circuit de validation</h2>
    <table class="pdf-data">
        <thead><tr><th>Étape</th><th>Décision</th><th>Validateur</th><th>Date</th></tr></thead>
        <tbody>
            @forelse ($liquidation->validations as $v)
                <tr>
                    <td>{{ $v->etape }}</td>
                    <td>{{ $v->decision?->value }}</td>
                    <td>{{ $v->validateur?->name ?? '—' }}</td>
                    <td>{{ optional($v->date_decision)->format('d/m/Y') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Non soumise au circuit.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pdf-montant">MONTANT NET LIQUIDÉ : {{ $fmt($liquidation->montant_net) }} FCFA</div>
@endsection
