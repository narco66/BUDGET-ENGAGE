# Composants — Chaîne de la dépense

## En-tête dossier

```blade
<x-chaine.dossier-header
    :title="$engagement->numero"
    :description="$engagement->objet"
    :statut="$engagement->statut"
    statut-module="engagement"
    :breadcrumb="[['label' => __('Engagements'), 'url' => route('engagements.index')], ['label' => $engagement->numero]]"
>
    <x-slot:actions>
        {{-- boutons @can --}}
    </x-slot:actions>
</x-chaine.dossier-header>
```

## Workflow transversal (depuis engagement)

```blade
@include('chaine._workflow-panel', ['engagement' => $engagement])
```

## Badge statut

```blade
<x-chaine.status-badge :statut="$engagement->statut" />
<x-chaine.status-badge module="besoin" :statut="$besoin->statut" />
<x-chaine.status-badge module="liquidation" :statut="$liquidation->statut" />
```

## Résumé financier

```blade
<x-chaine.financial-summary :title="__('Synthèse financière')">
    <x-chaine.financial-metric :label="__('Engagé')" tone="primary">
        <x-ds.money :value="$montant" />
    </x-chaine.financial-metric>
</x-chaine.financial-summary>
```

## Alerte contrôle métier

```blade
<x-chaine.control-alert type="block" :title="__('Crédits insuffisants')" :impact="__('Engagement bloqué')" :action="__('Réduire le montant ou changer d\'imputation')">
    {{ __('Disponible : :m', ['m' => $dispo]) }}
</x-chaine.control-alert>
```
