@extends('pdf.layout')

@section('title', 'QUITTANCE')
@section('reference', $paiement->quittance_numero ?? $paiement->numero)

@php
    $pdfReference = $paiement->quittance_numero ?? $paiement->numero;
    $pdfGeneratedAt = $generatedAt->format('d/m/Y H:i');
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
@endphp

@section('content')
    <p>{{ __('Pour solde de tout compte au titre de l\'opération ci-dessous, il est délivré quittance pour la somme de :') }}</p>
    <div class="pdf-montant">{{ $fmt($paiement->montant_paye) }} {{ $paiement->devise_paiement ?? $paiement->devise ?? 'XAF' }}</div>

    <table class="pdf-meta" style="margin-top:14px;">
        <tr><td class="label">{{ __('Bénéficiaire') }}</td><td>{{ $paiement->fournisseur?->raison_sociale ?? '—' }}</td></tr>
        <tr><td class="label">{{ __('Objet') }}</td><td>{{ $paiement->objet }}</td></tr>
        <tr><td class="label">{{ __('Ordonnancement') }}</td><td>{{ $paiement->ordonnancement?->numero }}</td></tr>
        <tr><td class="label">{{ __('Date') }}</td><td>{{ optional($paiement->execute_at)->format('d/m/Y') }}</td></tr>
    </table>

    @if ($paiement->preuve_numero)
        <table class="pdf-sceau">
            <tr>
                <td style="vertical-align:top;">
                    {{ __('Preuve') }} N° <span class="num">{{ $paiement->preuve_numero }}</span><br>
                    {{ __('Empreinte d\'intégrité') }} : <span class="num">{{ $paiement->preuve_hash }}</span>
                </td>
                @if (! empty($qr))
                    <td style="width:120px; text-align:right; vertical-align:top;">{!! $qr !!}</td>
                @endif
            </tr>
        </table>
    @endif
@endsection

@section('signatures')
@endsection
