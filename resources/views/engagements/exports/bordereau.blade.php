@extends('pdf.layout')

@section('title', 'Bordereau de transmission des engagements')

@php
    $pdfGeneratedAt = $generatedAt->format('d/m/Y H:i');
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
@endphp

@section('subtitle')
    {{ $engagements->count() }} {{ __('engagement(s)') }} — {{ __('transmis le') }} {{ $generatedAt->format('d/m/Y') }}
@endsection

@section('content')
    <table class="pdf-data">
        <thead><tr><th>N°</th><th>{{ __('Numéro engagement') }}</th><th>{{ __('Objet') }}</th><th>{{ __('Bénéficiaire') }}</th><th class="pdf-right">{{ __('Montant') }}</th></tr></thead>
        <tbody>
            @foreach ($engagements as $i => $e)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $e->numero }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($e->objet, 45) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($e->beneficiaire_autre ?? ($e->beneficiaire_type instanceof \BackedEnum ? $e->beneficiaire_type->value : $e->beneficiaire_type), 22) }}</td>
                    <td class="pdf-right">{{ $fmt($e->montant) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="pdf-montant">{{ __('TOTAL TRANSMIS') }} : {{ $fmt($total) }} FCFA</div>
@endsection

@section('signatures')
    <table class="pdf-signatures">
        <tr>
            <td>{{ __('Le Service émetteur') }}</td>
            <td>{{ __('Le Service récepteur (Liquidation)') }}</td>
        </tr>
    </table>
@endsection
