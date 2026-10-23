@extends('pdf.layout')

@section('title', 'MANDAT DE PAIEMENT')
@section('reference', $mandat->numero)

@php
    $pdfReference = $mandat->numero;
    $pdfExercice = $ordonnancement->liquidation?->engagement?->exerciceBudgetaire?->annee;
    $pdfGeneratedAt = $generatedAt->format('d/m/Y H:i');
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
@endphp

@section('content')
    <table class="pdf-meta">
        <tr><td class="label">Ordonnancement</td><td>{{ $ordonnancement->numero }}</td></tr>
        <tr><td class="label">Liquidation</td><td>{{ $ordonnancement->liquidation?->numero }}</td></tr>
        <tr><td class="label">Engagement</td><td>{{ $ordonnancement->liquidation?->engagement?->numero }}</td></tr>
        <tr><td class="label">Bénéficiaire</td><td>{{ $mandat->beneficiaire ?? $ordonnancement->fournisseur?->raison_sociale ?? '—' }}</td></tr>
        <tr><td class="label">Date du mandat</td><td>{{ optional($mandat->date_mandat)->format('d/m/Y') }}</td></tr>
        <tr><td class="label">Objet</td><td>{{ $ordonnancement->objet }}</td></tr>
    </table>

    <div class="pdf-montant">MONTANT À PAYER : {{ $fmt($mandat->montant) }} {{ $ordonnancement->devise ?? 'XAF' }}</div>

    <table class="pdf-sceau">
        <tr>
            <td style="vertical-align:top;">
                <strong>Sceau d'intégrité</strong> — Mandat N° <span class="num">{{ $mandat->numero }}</span><br>
                Empreinte (HMAC-SHA256) : <span class="num">{{ $mandat->hash_integrite ?? $ordonnancement->visa_hash }}</span><br>
                Visa ordonnateur : {{ $ordonnancement->visa_numero }} — généré le {{ optional($ordonnancement->mandate_at)->format('d/m/Y H:i') }}.
            </td>
            @if (! empty($qr))
                <td style="width:120px; text-align:right; vertical-align:top;">{!! $qr !!}</td>
            @endif
        </tr>
    </table>
@endsection

@section('signatures')
    <table class="pdf-signatures">
        <tr>
            <td>L'Ordonnateur</td>
            <td>Le Comptable Payeur</td>
            <td>Le Responsable Financier</td>
        </tr>
    </table>
@endsection
