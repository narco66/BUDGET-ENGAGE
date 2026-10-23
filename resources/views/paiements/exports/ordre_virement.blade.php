@extends('pdf.layout')

@section('title', 'ORDRE DE VIREMENT')
@section('reference', $paiement->numero)

@php
    $pdfReference = $paiement->numero;
    $pdfGeneratedAt = $generatedAt->format('d/m/Y H:i');
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
@endphp

@section('content')
    <table class="pdf-meta">
        <tr><td class="label">{{ __('Donneur d\'ordre') }}</td><td>CEEAC — {{ $paiement->banque?->libelle ?? '—' }}</td></tr>
        <tr><td class="label">{{ __('Compte à débiter') }}</td><td>{{ $paiement->banque?->iban ?? $paiement->banque?->rib ?? '—' }} ({{ $paiement->banque?->bic_swift ?? '—' }})</td></tr>
        <tr><td class="label">{{ __('Bénéficiaire') }}</td><td>{{ $paiement->fournisseur?->raison_sociale ?? '—' }}</td></tr>
        <tr><td class="label">{{ __('Mode') }}</td><td>{{ $paiement->mode_paiement ?? $paiement->type?->libelle ?? 'Virement' }}</td></tr>
        <tr><td class="label">{{ __('Référence bancaire') }}</td><td>{{ $paiement->reference_bancaire ?? '—' }}</td></tr>
        <tr><td class="label">{{ __('Ordonnancement') }}</td><td>{{ $paiement->ordonnancement?->numero }}</td></tr>
        <tr><td class="label">{{ __('Objet') }}</td><td>{{ $paiement->objet }}</td></tr>
        <tr><td class="label">{{ __('Date d\'exécution') }}</td><td>{{ optional($paiement->execute_at)->format('d/m/Y') ?? optional($paiement->date_paiement)->format('d/m/Y') }}</td></tr>
    </table>

    <div class="pdf-montant">{{ __('MONTANT') }} : {{ $fmt($paiement->montant_paye ?: $paiement->reste_a_payer) }} {{ $paiement->devise_paiement ?? $paiement->devise ?? 'XAF' }}</div>

    @if ($paiement->preuve_numero)
        <table class="pdf-sceau">
            <tr>
                <td style="vertical-align:top;">
                    <strong>{{ __('Sceau d\'exécution') }}</strong> — {{ __('Preuve') }} N° <span class="num">{{ $paiement->preuve_numero }}</span><br>
                    {{ __('Empreinte (HMAC-SHA256)') }} : <span class="num">{{ $paiement->preuve_hash }}</span>
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
            <td>{{ __('L\'Ordonnateur') }}</td>
            <td>{{ __('Le Comptable Payeur') }}</td>
        </tr>
    </table>
@endsection
