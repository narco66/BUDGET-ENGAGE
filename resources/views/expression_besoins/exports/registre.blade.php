<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    @include('pdf.partials.styles')
</head>
<body>
@php
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
    $statutColor = fn ($s) => match ($s) {
        'valide', 'converti' => '#16A34A',
        'rejete', 'annule' => '#DC2626',
        'retourne' => '#D97706',
        'soumis', 'en_validation' => '#2563EB',
        default => '#64748B',
    };
    $totalRegistre = $besoins->sum(fn ($b) => (float) $b->montant_total);
@endphp

@include('pdf.partials.ceeac-header', [
    'docTitle' => "Registre des expressions de besoins",
    'metaRows' => [
        ['k' => 'NOMBRE', 'v' => $besoins->count()],
        ['k' => 'ÉDITÉ LE', 'v' => $generatedAt->format('d/m/Y H:i')],
        ['k' => 'PAR', 'v' => e(auth()->user()?->name ?? '—')],
    ],
])

<table class="pdf-data">
    <thead>
        <tr>
            <th>{{ __('Référence') }}</th>
            <th>{{ __('Type') }}</th>
            <th>{{ __('Objet') }}</th>
            <th>{{ __('Département') }}</th>
            <th>{{ __('Exercice') }}</th>
            <th>{{ __('Statut') }}</th>
            <th class="pdf-right">{{ __('Montant') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($besoins as $besoin)
            <tr>
                <td>{{ $besoin->reference }}</td>
                <td>{{ $besoin->type_besoin->libelle() }}</td>
                <td>{{ \Illuminate\Support\Str::limit($besoin->objet, 48) }}</td>
                <td>{{ $besoin->departement?->libelle ?? $besoin->structure_demandeuse ?? '—' }}</td>
                <td>{{ $besoin->exerciceBudgetaire?->annee ?? '—' }}</td>
                <td><span class="ceeac-badge" style="background: {{ $statutColor($besoin->statut?->value) }};">{{ $besoin->statut->libelle() }}</span></td>
                <td class="pdf-right">{{ $fmt($besoin->montant_total) }}</td>
            </tr>
        @empty
            <tr><td colspan="7" style="text-align:center;padding:14px;color:#94A3B8;">{{ __('Aucun besoin.') }}</td></tr>
        @endforelse
        @if ($besoins->isNotEmpty())
            <tr class="total">
                <td colspan="6" class="pdf-right">{{ __('TOTAL GÉNÉRAL') }}</td>
                <td class="pdf-right">{{ $fmt($totalRegistre) }}</td>
            </tr>
        @endif
    </tbody>
</table>

<div class="ceeac-pied">
    <span class="l">BUDGET-ENGAGE · Commission de la CEEAC · Registre des expressions de besoins</span>
    <span class="r"></span>
</div>
</body>
</html>
