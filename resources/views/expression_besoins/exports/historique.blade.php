<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    @include('pdf.partials.styles')
</head>
<body>
@php
    $statutColor = match ($besoin->statut?->value) {
        'valide', 'converti' => '#16A34A',
        'rejete', 'annule' => '#DC2626',
        'retourne' => '#D97706',
        'soumis', 'en_validation' => '#2563EB',
        default => '#64748B',
    };
    $version = $besoin->version_revision ? '1.'.$besoin->version_revision : '1.0';
@endphp

@include('pdf.partials.ceeac-header', [
    'docTitle' => "Historique & traçabilité",
    'metaRows' => [
        ['k' => 'N° BESOIN', 'v' => e($besoin->reference)],
        ['k' => 'EXERCICE', 'v' => e($besoin->exerciceBudgetaire?->annee ?? '—')],
        ['k' => 'RÉVISION', 'v' => 'n° '.e($besoin->version_revision ?? 0)],
        ['k' => 'STATUT', 'v' => '<span class="ceeac-badge" style="background:'.$statutColor.';">'.e($besoin->statut?->libelle() ?? '—').'</span>'],
    ],
])

<table class="pdf-meta" style="margin-bottom:10px;">
    <tr>
        <td class="label">{{ __('Objet') }}</td>
        <td><strong>{{ $besoin->objet }}</strong></td>
    </tr>
    <tr>
        <td class="label">{{ __('Département') }}</td>
        <td>{{ $besoin->departement?->libelle ?? $besoin->structure_demandeuse ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">{{ __('Édité le') }}</td>
        <td>{{ $generatedAt->format('d/m/Y H:i') }} {{ __('par') }} {{ auth()->user()?->name ?? '—' }}</td>
    </tr>
</table>

<h2 class="pdf-section">{{ __('Journal des opérations') }}</h2>
<table class="pdf-data">
    <thead>
        <tr>
            <th style="width:18%;">{{ __('Date') }}</th>
            <th style="width:22%;">{{ __('Action') }}</th>
            <th style="width:22%;">{{ __('Utilisateur') }}</th>
            <th>{{ __('Détail') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($timeline as $entry)
            <tr>
                <td>{{ $entry->created_at?->format('d/m/Y H:i') }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $entry->properties['action'] ?? '')) }}</td>
                <td>{{ $entry->causer?->name ?? '—' }}</td>
                <td>{{ $entry->description }}</td>
            </tr>
        @empty
            <tr><td colspan="4" style="text-align:center;padding:14px;color:#94A3B8;">{{ __('Aucune opération journalisée.') }}</td></tr>
        @endforelse
    </tbody>
</table>

<div class="ceeac-pied">
    <span class="l">BUDGET-ENGAGE · Commission de la CEEAC · {{ $besoin->reference }} · Version {{ $version }}</span>
    <span class="r"></span>
</div>
</body>
</html>
