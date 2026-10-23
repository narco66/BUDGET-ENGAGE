@extends('pdf.layout')

@section('title', 'ORDRE DE PAIEMENT')
@section('reference', $ordre->numero)

@php
    $pdfReference = $ordre->numero;
    $pdfGeneratedAt = $generatedAt->format('d/m/Y H:i');
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
@endphp

@section('content')
    <table class="pdf-meta">
        <tr><td class="label">{{ __('Ordonnancement') }}</td><td>{{ $ordonnancement->numero }}</td></tr>
        <tr><td class="label">{{ __('Bénéficiaire') }}</td><td>{{ $ordre->beneficiaire ?? $ordonnancement->fournisseur?->raison_sociale ?? '—' }}</td></tr>
        <tr><td class="label">{{ __('Mode de paiement') }}</td><td>{{ $ordre->mode_paiement ?? '—' }}</td></tr>
        <tr><td class="label">{{ __('Banque') }}</td><td>{{ $ordre->banque ?? '—' }}</td></tr>
        <tr><td class="label">{{ __('Compte bancaire') }}</td><td>{{ $ordre->compte_bancaire ?? '—' }}</td></tr>
        <tr><td class="label">{{ __('Date') }}</td><td>{{ optional($ordre->date_ordre)->format('d/m/Y') }}</td></tr>
    </table>

    <div class="pdf-montant">{{ __('MONTANT') }} : {{ $fmt($ordre->montant) }} {{ $ordonnancement->devise ?? 'XAF' }}</div>

    @if (! empty($qr))
        <table class="pdf-sceau">
            <tr>
                <td style="vertical-align:middle; font-size:9px; color:#334155;">
                    {{ __('Ordonnancement') }} {{ $ordonnancement->numero }} — {{ __('Visa') }} {{ $ordonnancement->visa_numero }}<br>
                    {{ __('Empreinte') }} : <span class="num">{{ \Illuminate\Support\Str::limit($ordonnancement->visa_hash, 40) }}</span>
                </td>
                <td style="width:120px; text-align:right;">{!! $qr !!}</td>
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
