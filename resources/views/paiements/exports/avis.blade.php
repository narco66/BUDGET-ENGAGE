@extends('pdf.layout')

@section('title', 'Avis de paiement')
@section('reference', $paiement->numero)

@php
    $pdfReference = $paiement->numero;
    $pdfGeneratedAt = $generatedAt->format('d/m/Y H:i');
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
@endphp

@section('content')
    <p>{{ __('Nous vous informons qu\'un paiement a été effectué en votre faveur selon les éléments suivants :') }}</p>

    <table class="pdf-meta">
        <tr><td class="label">{{ __('Bénéficiaire') }}</td><td>{{ $paiement->fournisseur?->raison_sociale ?? '—' }}</td></tr>
        <tr><td class="label">{{ __('Mode de paiement') }}</td><td>{{ $paiement->mode_paiement ?? $paiement->type?->libelle ?? '—' }}</td></tr>
        <tr><td class="label">{{ __('Banque émettrice') }}</td><td>{{ $paiement->banque?->libelle ?? '—' }}</td></tr>
        <tr><td class="label">{{ __('Référence') }}</td><td>{{ $paiement->reference_bancaire ?? $paiement->numero }}</td></tr>
        <tr><td class="label">{{ __('Date de valeur') }}</td><td>{{ optional($paiement->date_valeur)->format('d/m/Y') ?? optional($paiement->execute_at)->format('d/m/Y') }}</td></tr>
        <tr><td class="label">{{ __('Objet') }}</td><td>{{ $paiement->objet }}</td></tr>
    </table>

    <div class="pdf-montant">{{ __('MONTANT VERSÉ') }} : {{ $fmt($paiement->montant_paye) }} {{ $paiement->devise_paiement ?? $paiement->devise ?? 'XAF' }}</div>

    @if (! empty($qr))
        <table class="pdf-sceau">
            <tr>
                <td style="font-size:9px;color:#334155;vertical-align:middle;">{{ __('Preuve') }} {{ $paiement->preuve_numero }} — {{ __('empreinte') }} {{ \Illuminate\Support\Str::limit($paiement->preuve_hash, 32) }}</td>
                <td style="width:110px;text-align:right;">{!! $qr !!}</td>
            </tr>
        </table>
    @endif
@endsection

@section('signatures')
@endsection
