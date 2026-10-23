@extends('pdf.layout')

@section('title', "Certificat d'engagement")
@section('reference', $engagement->numero)

@php
    $pdfReference = $engagement->numero;
    $pdfExercice = $engagement->exerciceBudgetaire?->annee;
    $pdfGeneratedAt = $generatedAt->format('d/m/Y H:i');
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
    $st = $engagement->statut instanceof \BackedEnum ? $engagement->statut->value : $engagement->statut;
@endphp

@section('content')
    <h2 class="pdf-section">Identification</h2>
    <table class="pdf-meta">
        <tr><td class="label">Exercice</td><td>{{ $engagement->exerciceBudgetaire?->annee }}</td><td class="label">Date</td><td>{{ $engagement->date_engagement?->format('d/m/Y') ?? '—' }}</td></tr>
        <tr><td class="label">Type</td><td>{{ $engagement->engagementType?->libelle ?? $engagement->type_engagement }}</td><td class="label">Mode</td><td>{{ ucfirst($engagement->mode_creation ?? 'manuel') }}</td></tr>
        <tr><td class="label">Structure</td><td colspan="3">{{ $engagement->departement?->libelle ?? '—' }}{{ $engagement->direction ? ' / '.$engagement->direction->libelle : '' }}{{ $engagement->serviceStructure ? ' / '.$engagement->serviceStructure->libelle : '' }}</td></tr>
        <tr><td class="label">Bénéficiaire</td><td colspan="3">
            @php($benef = $engagement->beneficiaire())
            @if ($benef){{ $benef->raison_sociale ?? trim(($benef->nom ?? '').' '.($benef->prenom ?? '')) }}@else{{ $engagement->beneficiaire_autre ?? '—' }}@endif
        </td></tr>
        <tr><td class="label">Objet</td><td colspan="3">{{ $engagement->objet }}</td></tr>
        <tr><td class="label">Statut</td><td colspan="3">{{ ucfirst(str_replace('_', ' ', $st)) }}</td></tr>
    </table>

    @if ($engagement->typeValeurs->isNotEmpty())
        <h2 class="pdf-section">Informations spécifiques</h2>
        <table class="pdf-meta">
            @foreach ($engagement->typeValeurs as $tv)
                <tr><td class="label">{{ ucfirst(str_replace('_', ' ', $tv->cle)) }}</td><td colspan="3">{{ $tv->valeur ?? '—' }}</td></tr>
            @endforeach
        </table>
    @endif

    <h2 class="pdf-section">Imputations budgétaires</h2>
    <table class="pdf-data">
        <thead><tr><th>Ligne budgétaire</th><th>Source</th><th>Activité</th><th class="pdf-right">Montant</th></tr></thead>
        <tbody>
            @forelse ($engagement->imputations as $imp)
                <tr>
                    <td>{{ $imp->ligneBudgetaire?->code ?? '—' }} — {{ \Illuminate\Support\Str::limit($imp->ligneBudgetaire?->libelle, 30) }}</td>
                    <td>{{ $imp->sourceFinancement?->libelle ?? '—' }}</td>
                    <td>{{ $imp->activite?->code ?? '—' }}</td>
                    <td class="pdf-right">{{ $fmt($imp->montant_impute) }}</td>
                </tr>
            @empty
                <tr><td colspan="3">{{ $engagement->ligneBudgetaire?->code ?? '—' }}</td><td class="pdf-right">{{ $fmt($engagement->montant) }}</td></tr>
            @endforelse
            <tr class="total"><td colspan="3" class="pdf-right">Total engagé</td><td class="pdf-right">{{ $fmt($engagement->montant) }} FCFA</td></tr>
        </tbody>
    </table>

    <div class="pdf-montant">MONTANT ENGAGÉ : {{ $fmt($engagement->montant) }} FCFA</div>
@endsection

@section('signatures')
    <table class="pdf-signatures">
        <tr>
            <td>L'Ordonnateur</td>
            <td>Le Contrôleur Budgétaire</td>
            <td>Le Responsable Financier</td>
        </tr>
    </table>
@endsection
