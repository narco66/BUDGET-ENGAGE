# Composants — Design System BUDGET-ENGAGE

## Boutons (CSS)

| Classe | Variante | Usage |
|--------|----------|-------|
| `.btn.btn-primary` | Dégradé bleu | Action principale |
| `.btn.btn-secondary` | Bordure grise | Action secondaire |
| `.btn.btn-danger` | Rouge | Suppression, annulation |
| `.btn.btn-ghost` | Transparent | Actions tertiaires |
| `.btn.btn-success` | Vert | Validation exceptionnelle |
| `.btn-sm` / `.btn-lg` | Tailles | Contexte |

Composants Blade alias : `<x-primary-button>`, `<x-secondary-button>`, `<x-danger-button>` (mappés sur `.btn`).

## Cartes

```blade
<x-ds.card title="Synthèse">
    Contenu
    <x-slot:footer>Pied</x-slot:footer>
</x-ds.card>
```

Classes CSS : `.card`, `.card-header`, `.card-body`, `.card-footer`

## Badges statut

```html
<span class="badge badge-success">Validé</span>
<span class="badge badge-warning">En attente</span>
<span class="badge badge-danger">Rejeté</span>
<span class="badge badge-approuve">Approuvé</span>
```

Engagements : `.badge-brouillon`, `.badge-soumis`, `.badge-approuve`, etc.

## Alertes

```blade
<x-ds.alert type="warning">Message</x-ds.alert>
<x-ds.flash-messages /> {{-- dans layout app --}}
```

## Page header

```blade
<x-ds.page-header
    :title="__('Lignes budgétaires')"
    :description="__('…')"
    :breadcrumb="[['label' => 'Budget', 'url' => '…'], ['label' => 'Lignes']]"
>
    <x-slot:actions>
        <x-primary-button>Ajouter</x-primary-button>
    </x-slot:actions>
</x-ds.page-header>
```

## KPI

```blade
<x-ds.kpi-card label="Engagé" :value="'28,1 Mrd'" trend="65,7%" tone="success" />
```

## État vide

```blade
<x-ds.empty-state
    title="Aucun engagement"
    description="Créez votre premier dossier."
    action-url="{{ route('engagements.create') }}"
    action-label="Nouvel engagement"
/>
```

## Montants

```blade
<x-ds.money :value="1000000" :decimals="0" />
<x-ds.money :value="42800000000" compact />
```

## Erreurs à éviter

- Dupliquer `bg-white shadow-sm rounded-lg` → utiliser `.card`
- Tables sans `.data-table`
- Boutons Breeze legacy (`rounded-md uppercase tracking-widest`)
