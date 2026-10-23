<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    @include('pdf.partials.styles')
</head>
<body>
@php
    $totalPieces = $besoins->sum(fn ($b) => $b->gedAttachments->count());
@endphp

@include('pdf.partials.ceeac-header', [
    'docTitle' => "État des pièces justificatives",
    'metaRows' => [
        ['k' => 'BESOINS', 'v' => $besoins->count()],
        ['k' => 'PIÈCES', 'v' => $totalPieces],
        ['k' => 'ÉDITÉ LE', 'v' => $generatedAt->format('d/m/Y H:i')],
    ],
])

<table class="pdf-data">
    <thead>
        <tr>
            <th style="width:16%;">{{ __('Réf. besoin') }}</th>
            <th style="width:26%;">{{ __('Objet') }}</th>
            <th>{{ __('Pièce') }}</th>
            <th style="width:18%;">{{ __('Référence GED') }}</th>
            <th style="width:12%;">{{ __('Déposée le') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($besoins as $besoin)
            @php($pieces = $besoin->gedAttachments)
            @if ($pieces->isEmpty())
                <tr>
                    <td>{{ $besoin->reference }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($besoin->objet, 40) }}</td>
                    <td colspan="3" style="color:#94A3B8;">{{ __('Aucune pièce justificative') }}</td>
                </tr>
            @else
                @foreach ($pieces as $i => $piece)
                    <tr>
                        @if ($i === 0)
                            <td rowspan="{{ $pieces->count() }}">{{ $besoin->reference }}</td>
                            <td rowspan="{{ $pieces->count() }}">{{ \Illuminate\Support\Str::limit($besoin->objet, 40) }}</td>
                        @endif
                        <td>{{ $piece->document?->titre ?? '—' }}</td>
                        <td style="font-family:monospace;font-size:8px;">{{ $piece->document?->reference ?? '—' }}</td>
                        <td>{{ optional($piece->document?->date_depot)->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                @endforeach
            @endif
        @empty
            <tr><td colspan="5" style="text-align:center;padding:14px;color:#94A3B8;">{{ __('Aucun besoin.') }}</td></tr>
        @endforelse
    </tbody>
</table>

<div class="ceeac-pied">
    <span class="l">BUDGET-ENGAGE · Commission de la CEEAC · État des pièces justificatives</span>
    <span class="r"></span>
</div>
</body>
</html>
