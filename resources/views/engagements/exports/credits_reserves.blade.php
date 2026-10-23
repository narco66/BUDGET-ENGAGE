@extends('pdf.layout')

@section('title', 'État des crédits réservés')

@php $pdfGeneratedAt = $generatedAt->format('d/m/Y H:i'); $fmt = fn ($v) => number_format((float) $v, 2, ',', ' '); @endphp

@section('subtitle')
    {{ $lignes->count() }} {{ __('ligne(s) budgétaire(s)') }}
@endsection

@section('content')
    <table class="pdf-data">
        <thead><tr><th>{{ __('Code') }}</th><th>{{ __('Libellé') }}</th><th>{{ __('Exercice') }}</th><th class="pdf-right">{{ __('Voté') }}</th><th class="pdf-right">{{ __('Révisé') }}</th><th class="pdf-right">{{ __('Engagé') }}</th><th class="pdf-right">{{ __('Disponible') }}</th></tr></thead>
        <tbody>
            @forelse ($lignes as $l)
                <tr>
                    <td>{{ $l['code'] }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($l['libelle'], 35) }}</td>
                    <td>{{ $l['annee'] }}</td>
                    <td class="pdf-right">{{ $fmt($l['vote']) }}</td>
                    <td class="pdf-right">{{ $fmt($l['revise']) }}</td>
                    <td class="pdf-right">{{ $fmt($l['engage']) }}</td>
                    <td class="pdf-right">{{ $fmt($l['disponible']) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;padding:14px;color:#94A3B8;">{{ __('Aucun crédit réservé.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection

@section('signatures')
@endsection
