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
    $nbBesoins = collect($groupes)->sum(fn ($d) => $d['besoins']->count());
@endphp

@include('pdf.partials.ceeac-header', [
    'docTitle' => $titre,
    'metaRows' => [
        ['k' => 'REGROUPEMENT', 'v' => e($libelleGroupe)],
        ['k' => 'GROUPES', 'v' => collect($groupes)->count()],
        ['k' => 'BESOINS', 'v' => $nbBesoins],
        ['k' => 'ÉDITÉ LE', 'v' => $generatedAt->format('d/m/Y H:i')],
    ],
])

@forelse ($groupes as $label => $data)
    <h2 class="pdf-section">{{ $libelleGroupe }} : {{ $label }}</h2>
    <table class="pdf-data">
        <thead>
            <tr>
                <th>{{ __('Référence') }}</th>
                <th>{{ __('Objet') }}</th>
                <th>{{ __('Exercice') }}</th>
                <th>{{ __('Statut') }}</th>
                <th class="pdf-right">{{ __('Montant') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['besoins'] as $besoin)
                <tr>
                    <td>{{ $besoin->reference }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($besoin->objet, 45) }}</td>
                    <td>{{ $besoin->exerciceBudgetaire?->annee ?? '—' }}</td>
                    <td><span class="ceeac-badge" style="background: {{ $statutColor($besoin->statut?->value) }};">{{ $besoin->statut->libelle() }}</span></td>
                    <td class="pdf-right">{{ $fmt($besoin->montant_total) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="4" class="pdf-right">{{ __('Sous-total') }} {{ $label }}</td>
                <td class="pdf-right">{{ $fmt($data['total']) }}</td>
            </tr>
        </tbody>
    </table>
@empty
    <p style="text-align:center;padding:14px;color:#94A3B8;">{{ __('Aucun besoin.') }}</p>
@endforelse

<div class="pdf-montant">{{ __('TOTAL GÉNÉRAL') }} : {{ $fmt($total) }}</div>

<div class="ceeac-pied">
    <span class="l">BUDGET-ENGAGE · Commission de la CEEAC · {{ $titre }}</span>
    <span class="r"></span>
</div>
</body>
</html>
