@extends('pdf.layout')

@section('title', 'Bordereau de transmission — Liquidation')
@section('reference', $liquidation->numero)

@php
    $pdfReference = $liquidation->numero;
    $pdfExercice = $liquidation->engagement?->exerciceBudgetaire?->annee;
    $pdfGeneratedAt = $generatedAt->format('d/m/Y H:i');
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
@endphp

@section('subtitle')
    {{ __('Engagement') }} {{ $liquidation->engagement?->numero }}
@endsection

@section('content')
    <table class="pdf-data">
        <thead><tr><th>{{ __('Ligne budgétaire') }}</th><th class="pdf-right">{{ __('Brut') }}</th><th class="pdf-right">{{ __('Déductions') }}</th><th class="pdf-right">{{ __('Net') }}</th></tr></thead>
        <tbody>
            @foreach ($liquidation->lignes as $l)
                @php($ded = (float) $l->montant_avances + $l->montant_acomptes + $l->montant_retenues + $l->montant_garanties + $l->montant_penalites)
                <tr>
                    <td>{{ $l->ligneBudgetaire?->code ?? '—' }} — {{ \Illuminate\Support\Str::limit($l->ligneBudgetaire?->libelle, 35) }}</td>
                    <td class="pdf-right">{{ $fmt($l->montant_brut) }}</td>
                    <td class="pdf-right">{{ $fmt($ded) }}</td>
                    <td class="pdf-right">{{ $fmt($l->montant_net) }}</td>
                </tr>
            @endforeach
            <tr class="total"><td colspan="3" class="pdf-right">{{ __('Montant net à ordonnancer') }}</td><td class="pdf-right">{{ $fmt($liquidation->montant_net) }}</td></tr>
        </tbody>
    </table>
@endsection

@section('signatures')
    <table class="pdf-signatures">
        <tr>
            <td>{{ __('Transmis par') }}</td>
            <td>{{ __('Reçu par (Ordonnancement)') }}</td>
        </tr>
    </table>
@endsection
