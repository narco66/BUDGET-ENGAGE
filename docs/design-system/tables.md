# Tableaux — Design System BUDGET-ENGAGE

## Tableau standard

```blade
<x-ds.card :padding="false">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th class="text-right">Montant</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="font-mono">66101</td>
                    <td class="text-right"><x-ds.money :value="$ligne->montant" :show-currency="false" :decimals="2" /></td>
                </tr>
            </tbody>
        </table>
    </div>
</x-ds.card>
```

## Règles financières

| Règle | Application |
|-------|-------------|
| Alignement montants | `text-right` + `tabular-nums` |
| Séparateur milliers | espace (`number_format`) |
| Devise | Colonne ou suffixe `FCFA` |
| Codes budget | `font-mono` |
| Lignes cliquables | lien `text-ceeac-bright hover:underline` |

## Listes CRUD

Structure type (voir `budget/lignes/index.blade.php` migré) :

1. `<x-ds.page-header>` avec actions
2. `<x-ds.page-shell>`
3. `<x-ds.card>` + `.data-table`
4. Pagination dans `.card-footer`
5. `<x-ds.empty-state>` si aucune ligne

## Mobile

- `overflow-x-auto` sur le conteneur
- Ne pas réduire la taille de police en dessous de 12px
- Option future : vue cartes (non implémentée globalement)

## Actions par ligne

```html
<a class="text-ceeac-bright hover:underline">Modifier</a>
<button class="text-ceeac-danger hover:underline">Archiver</button>
```

## Exports

Boutons secondaires dans le header :

```blade
<x-slot:actions>
    <a href="…" class="btn btn-secondary btn-sm">Export CSV</a>
</x-slot:actions>
```
