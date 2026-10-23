@extends('pdf.layout')

@section('title', 'Bordereau bancaire de paiement')
@section('reference', $paiement->numero)

@php
    $pdfReference = $paiement->numero;
    $pdfGeneratedAt = $generatedAt->format('d/m/Y H:i');
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
@endphp

@section('subtitle')
    {{ __('Compte') }} {{ $paiement->banque?->code ?? '—' }}
@endsection

@section('content')
    <table class="pdf-data">
        <thead><tr><th>{{ __('Désignation') }}</th><th>{{ __('Ligne budgétaire') }}</th><th class="pdf-right">{{ __('Montant payé') }}</th><th class="pdf-right">{{ __('Frais') }}</th></tr></thead>
        <tbody>
            @foreach ($paiement->lignes as $l)
                <tr>
                    <td>{{ $l->designation }}</td>
                    <td>{{ $l->ligneBudgetaire?->code ?? '—' }}</td>
                    <td class="pdf-right">{{ $fmt($l->montant_paye) }}</td>
                    <td class="pdf-right">{{ $fmt($l->frais_bancaires) }}</td>
                </tr>
            @endforeach
            <tr class="total"><td colspan="2" class="pdf-right">{{ __('Total décaissé') }}</td><td class="pdf-right">{{ $fmt($paiement->montant_paye) }}</td><td class="pdf-right">{{ $fmt($paiement->frais_bancaires) }}</td></tr>
        </tbody>
    </table>

    <p style="margin-top:14px;font-size:10px;">{{ __('Bénéficiaire') }} : <strong>{{ $paiement->fournisseur?->raison_sociale ?? '—' }}</strong> — {{ __('Référence') }} : {{ $paiement->reference_bancaire ?? '—' }}</p>
@endsection

@section('signatures')
@endsection
