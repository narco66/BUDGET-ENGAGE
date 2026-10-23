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
    'docTitle' => "Bordereau de transmission",
    'metaRows' => [
        ['k' => 'N° BORDEREAU', 'v' => 'BT-'.$generatedAt->format('Ymd-His')],
        ['k' => 'DATE', 'v' => $generatedAt->format('d/m/Y')],
        ['k' => 'PIÈCES', 'v' => $besoins->count()],
    ],
])

<table class="pdf-meta" style="margin-bottom:10px;">
    <tr>
        <td class="label">{{ __('Émetteur') }}</td>
        <td>{{ $emetteur?->name ?? '—' }}</td>
        <td class="label">{{ __('Destinataire') }}</td>
        <td>{{ $destinataire ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">{{ __('Objet') }}</td>
        <td colspan="3">{{ $objet ?? __('Transmission des expressions de besoins') }}</td>
    </tr>
</table>

<h2 class="pdf-section">{{ __('Pièces transmises') }}</h2>
<table class="pdf-data">
    <thead>
        <tr>
            <th style="width:6%;">{{ __('N°') }}</th>
            <th>{{ __('Référence') }}</th>
            <th>{{ __('Objet') }}</th>
            <th>{{ __('Type') }}</th>
            <th>{{ __('Statut') }}</th>
            <th class="pdf-right">{{ __('Montant') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($besoins as $i => $besoin)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $besoin->reference }}</td>
                <td>{{ \Illuminate\Support\Str::limit($besoin->objet, 45) }}</td>
                <td>{{ $besoin->type_besoin?->libelle() ?? '—' }}</td>
                <td>{{ $besoin->statut?->libelle() ?? '—' }}</td>
                <td class="pdf-right">{{ $fmt($besoin->montant_total) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" style="text-align:center;padding:14px;color:#94A3B8;">{{ __('Aucune pièce à transmettre.') }}</td></tr>
        @endforelse
        @if ($besoins->isNotEmpty())
            <tr class="total">
                <td colspan="5" class="pdf-right">{{ __('TOTAL') }}</td>
                <td class="pdf-right">{{ $fmt($total) }}</td>
            </tr>
        @endif
    </tbody>
</table>

<table class="pdf-signatures">
    <tr>
        <td>{{ __('L\'émetteur') }}<br><span style="font-size:8px;">{{ $emetteur?->name ?? '' }}</span></td>
        <td>{{ __('Le destinataire') }}<br><span style="font-size:8px;">{{ $destinataire ?? '' }}</span></td>
    </tr>
</table>

<div class="ceeac-pied">
    <span class="l">BUDGET-ENGAGE · Commission de la CEEAC · Bordereau de transmission</span>
    <span class="r"></span>
</div>
</body>
</html>
