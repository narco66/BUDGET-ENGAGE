@extends('pdf.layout')

@section('title', $titre)

@php $pdfGeneratedAt = $generatedAt->format('d/m/Y H:i'); $fmt = fn ($v) => number_format((float) $v, 2, ',', ' '); @endphp

@section('subtitle')
    {{ $engagements->count() }} {{ __('engagement(s)') }}
@endsection

@section('content')
    <table class="pdf-data">
        <thead><tr><th>{{ __('Numéro') }}</th><th>{{ __('Type') }}</th><th>{{ __('Exercice') }}</th><th>{{ __('Département') }}</th><th>{{ __('Objet') }}</th><th class="pdf-right">{{ __('Montant') }}</th><th>{{ __('Statut') }}</th></tr></thead>
        <tbody>
            @forelse ($engagements as $e)
                @php($st = $e->statut instanceof \BackedEnum ? $e->statut->value : $e->statut)
                <tr>
                    <td>{{ $e->numero }}</td>
                    <td>{{ $e->engagementType?->code ?? $e->type_engagement }}</td>
                    <td>{{ $e->exerciceBudgetaire?->annee ?? '—' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($e->departement?->libelle, 18) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($e->objet, 40) }}</td>
                    <td class="pdf-right">{{ $fmt($e->montant) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $st)) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;padding:14px;color:#94A3B8;">{{ __('Aucun engagement.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection

@section('signatures')
@endsection
