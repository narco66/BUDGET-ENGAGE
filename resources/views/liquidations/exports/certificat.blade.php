@extends('pdf.layout')

@section('title', 'Certificat de liquidation')
@section('reference', $liquidation->numero)

@php
    $pdfReference = $liquidation->numero;
    $pdfExercice = $liquidation->engagement?->exerciceBudgetaire?->annee;
    $pdfGeneratedAt = $generatedAt->format('d/m/Y H:i');
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
@endphp

@section('content')
    <h2 class="pdf-section">Identification</h2>
    <table class="pdf-meta">
        <tr><td class="label">Engagement</td><td>{{ $liquidation->engagement?->numero }}</td><td class="label">Exercice</td><td>{{ $liquidation->engagement?->exerciceBudgetaire?->annee }}</td></tr>
        <tr><td class="label">Type</td><td>{{ $liquidation->type?->libelle ?? '—' }}</td><td class="label">Date</td><td>{{ optional($liquidation->date_liquidation)->format('d/m/Y') }}</td></tr>
        <tr><td class="label">Fournisseur</td><td>{{ $liquidation->fournisseur?->raison_sociale ?? '—' }}</td><td class="label">Statut</td><td>{{ $liquidation->statut?->libelle() }}</td></tr>
        <tr><td class="label">Objet</td><td colspan="3">{{ $liquidation->objet ?? $liquidation->engagement?->objet }}</td></tr>
    </table>

    <h2 class="pdf-section">Décompte financier</h2>
    <table class="pdf-data">
        <tbody>
            <tr><td>Montant brut</td><td class="pdf-right">{{ $fmt($liquidation->montant_brut) }}</td></tr>
            <tr><td>Avances</td><td class="pdf-right">- {{ $fmt($liquidation->total_avances) }}</td></tr>
            <tr><td>Acomptes</td><td class="pdf-right">- {{ $fmt($liquidation->total_acomptes) }}</td></tr>
            <tr><td>Retenues</td><td class="pdf-right">- {{ $fmt($liquidation->total_retenues) }}</td></tr>
            <tr><td>Garanties</td><td class="pdf-right">- {{ $fmt($liquidation->total_garanties) }}</td></tr>
            <tr><td>Pénalités</td><td class="pdf-right">- {{ $fmt($liquidation->total_penalites) }}</td></tr>
            <tr><td>Révision de prix</td><td class="pdf-right">{{ $fmt($liquidation->montant_revision_prix) }}</td></tr>
            <tr><td>Taxes</td><td class="pdf-right">+ {{ $fmt($liquidation->total_taxes) }}</td></tr>
            <tr class="total"><td>MONTANT NET LIQUIDÉ</td><td class="pdf-right">{{ $fmt($liquidation->montant_net) }} {{ $liquidation->devise ?? 'XAF' }}</td></tr>
        </tbody>
    </table>

    <div class="pdf-montant">MONTANT NET LIQUIDÉ : {{ $fmt($liquidation->montant_net) }} {{ $liquidation->devise ?? 'XAF' }}</div>

    @if ($liquidation->certificat_numero)
        <table class="pdf-sceau">
            <tr>
                <td style="vertical-align:top;">
                    <strong>Sceau de certification</strong> — Certificat N° <span class="num">{{ $liquidation->certificat_numero }}</span><br>
                    Empreinte d'intégrité (HMAC-SHA256) : <span class="num">{{ $liquidation->certificat_hash }}</span><br>
                    Certifiée le {{ optional($liquidation->certifie_at)->format('d/m/Y H:i') }}@if ($liquidation->validateur) par {{ $liquidation->validateur->name }}@endif.
                </td>
                @if (! empty($qr))
                    <td style="width:120px; text-align:right; vertical-align:top;">{!! $qr !!}</td>
                @endif
            </tr>
        </table>
    @endif
@endsection

@section('signatures')
    <table class="pdf-signatures">
        <tr>
            <td>Le Comptable</td>
            <td>Le Contrôleur Financier</td>
            <td>L'Ordonnateur</td>
        </tr>
    </table>
@endsection
