@extends('pdf.layout')

@section('title', 'Bordereau de transmission — Ordonnancement')
@section('reference', $ordonnancement->numero)

@php
    $pdfReference = $ordonnancement->numero;
    $pdfExercice = $ordonnancement->liquidation?->engagement?->exerciceBudgetaire?->annee;
    $pdfGeneratedAt = $generatedAt->format('d/m/Y H:i');
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
@endphp

@section('subtitle')
    {{ __('Liquidation') }} {{ $ordonnancement->liquidation?->numero }}
@endsection

@section('content')
    <table class="pdf-data">
        <thead><tr><th>{{ __('Ligne budgétaire') }}</th><th class="pdf-right">{{ __('Liquidé') }}</th><th class="pdf-right">{{ __('Ordonnancé') }}</th></tr></thead>
        <tbody>
            @foreach ($ordonnancement->lignes as $l)
                <tr>
                    <td>{{ $l->ligneBudgetaire?->code ?? '—' }} — {{ \Illuminate\Support\Str::limit($l->ligneBudgetaire?->libelle, 35) }}</td>
                    <td class="pdf-right">{{ $fmt($l->montant_liquide) }}</td>
                    <td class="pdf-right">{{ $fmt($l->montant_ordonnance) }}</td>
                </tr>
            @endforeach
            <tr class="total"><td class="pdf-right">{{ __('Montant net à payer') }}</td><td></td><td class="pdf-right">{{ $fmt($ordonnancement->montant_ordonnance) }}</td></tr>
        </tbody>
    </table>
@endsection

@section('signatures')
    <table class="pdf-signatures">
        <tr>
            <td>{{ __('L\'Ordonnateur') }}</td>
            <td>{{ __('Reçu par (Paiement)') }}</td>
        </tr>
    </table>
@endsection
