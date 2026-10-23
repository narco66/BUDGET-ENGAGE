@extends('pdf.layout')

@section('title', 'REÇU')
@section('reference', $paiement->numero)

@php
    $pdfReference = $paiement->numero;
    $pdfGeneratedAt = $generatedAt->format('d/m/Y H:i');
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
@endphp

@section('content')
    <p>{{ __('Reçu de la Commission de la CEEAC la somme de :') }}</p>
    <div class="pdf-montant">{{ $fmt($paiement->montant_paye) }} {{ $paiement->devise_paiement ?? $paiement->devise ?? 'XAF' }}</div>

    <table class="pdf-meta" style="margin-top:14px;">
        <tr><td class="label">{{ __('Reçu par') }}</td><td>{{ $paiement->fournisseur?->raison_sociale ?? '—' }}</td></tr>
        <tr><td class="label">{{ __('Au titre de') }}</td><td>{{ $paiement->objet }}</td></tr>
        <tr><td class="label">{{ __('Mode') }}</td><td>{{ $paiement->mode_paiement ?? '—' }}</td></tr>
        <tr><td class="label">{{ __('Date') }}</td><td>{{ optional($paiement->execute_at)->format('d/m/Y') ?? optional($paiement->date_paiement)->format('d/m/Y') }}</td></tr>
    </table>

    @if (! empty($qr))
        <div style="text-align:right; margin-top:10px;">{!! $qr !!}</div>
    @endif
@endsection

@section('signatures')
    <table class="pdf-signatures">
        <tr><td>{{ __('Le Bénéficiaire') }}</td><td>{{ __('Le Comptable') }}</td></tr>
    </table>
@endsection
