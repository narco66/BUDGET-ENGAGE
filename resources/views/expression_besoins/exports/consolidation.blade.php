<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    @include('pdf.partials.styles')
</head>
<body>
@php
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
@endphp

@include('pdf.partials.ceeac-header', [
    'docTitle' => "État consolidé des besoins",
    'metaRows' => [
        ['k' => 'EXERCICES', 'v' => $lignes->count()],
        ['k' => 'BESOINS', 'v' => (int) $totaux['nb']],
        ['k' => 'ÉDITÉ LE', 'v' => $generatedAt->format('d/m/Y H:i')],
    ],
])

<table class="pdf-data">
    <thead>
        <tr>
            <th>{{ __('Exercice') }}</th>
            <th class="pdf-right">{{ __('Nbre besoins') }}</th>
            <th class="pdf-right">{{ __('Montant demandé') }}</th>
            <th class="pdf-right">{{ __('Validé') }}</th>
            <th class="pdf-right">{{ __('Converti') }}</th>
            <th class="pdf-right">{{ __('PAP') }}</th>
            <th class="pdf-right">{{ __('Hors PAP') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($lignes as $ligne)
            <tr>
                <td><strong>{{ $ligne->annee }}</strong></td>
                <td class="pdf-right">{{ (int) $ligne->nb }}</td>
                <td class="pdf-right">{{ $fmt($ligne->demande) }}</td>
                <td class="pdf-right">{{ $fmt($ligne->valide) }}</td>
                <td class="pdf-right">{{ $fmt($ligne->converti) }}</td>
                <td class="pdf-right">{{ $fmt($ligne->pap) }}</td>
                <td class="pdf-right">{{ $fmt($ligne->hors_pap) }}</td>
            </tr>
        @empty
            <tr><td colspan="7" style="text-align:center;padding:14px;color:#94A3B8;">{{ __('Aucune donnée.') }}</td></tr>
        @endforelse
        @if ($lignes->isNotEmpty())
            <tr class="total">
                <td>{{ __('TOTAL') }}</td>
                <td class="pdf-right">{{ (int) $totaux['nb'] }}</td>
                <td class="pdf-right">{{ $fmt($totaux['demande']) }}</td>
                <td class="pdf-right">{{ $fmt($totaux['valide']) }}</td>
                <td class="pdf-right">{{ $fmt($totaux['converti']) }}</td>
                <td class="pdf-right">{{ $fmt($totaux['pap']) }}</td>
                <td class="pdf-right">{{ $fmt($totaux['hors_pap']) }}</td>
            </tr>
        @endif
    </tbody>
</table>

<div class="pdf-montant">{{ __('MONTANT TOTAL DEMANDÉ') }} : {{ $fmt($totaux['demande']) }}</div>

<div class="ceeac-pied">
    <span class="l">BUDGET-ENGAGE · Commission de la CEEAC · État consolidé des besoins</span>
    <span class="r"></span>
</div>
</body>
</html>
