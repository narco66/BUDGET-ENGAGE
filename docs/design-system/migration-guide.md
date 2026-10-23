# Guide de migration — Design System BUDGET-ENGAGE

## Ordre de migration (§15 prompt)

| Lot | Domaine | Statut |
|-----|---------|--------|
| 0 | Tokens + composants DS | ✅ Fait |
| 1 | Pages publiques (landing) | ✅ Référence (styles → app.css) |
| 2 | Authentification (guest) | ✅ Aligné |
| 3 | Layout interne (app) | ✅ Flash unifié |
| 4 | Composants Breeze | ✅ btn / form-input |
| 5 | Exemple CRUD | ✅ `budget/lignes/index` |
| 6 | Budget (reste) | ⏳ À faire |
| 7 | PAP / RBM | ⏳ À faire |
| 8 | Chaîne dépense | ⏳ Partiel (execution/*.blade) |
| 9 | Recettes, S&E | ⏳ À faire |
| 10 | Reporting | ⏳ À faire |
| 11 | Admin, GED | ⏳ À faire |

## Remplacements

| Ancien | Nouveau |
|--------|---------|
| `bg-white shadow-sm sm:rounded-lg` | `<x-ds.card>` |
| `text-indigo-600` | `text-ceeac-bright hover:underline` |
| `bg-green-50 border border-green-200` (flash) | `<x-ds.flash-messages />` (layout) |
| `<h2 class="font-semibold text-xl">` | `<x-ds.page-header :title="…">` |
| `number_format(…)` inline | `<x-ds.money>` |
| Table ad hoc | `.data-table` dans card |
| `max-w-7xl mx-auto py-8` | `<x-ds.page-shell>` |

## Checklist par page

- [ ] Layout correct (`x-app-layout` / `guest` / `landing`)
- [ ] `<x-ds.page-header>` ou slot header DS
- [ ] `<x-ds.page-shell>` conteneur
- [ ] Pas de flash dupliqué (layout gère `status`, `success`, `error`)
- [ ] Tableaux `.data-table`
- [ ] Boutons `<x-primary-button>` / `.btn`
- [ ] Montants `<x-ds.money>` ou `tabular-nums`
- [ ] État vide `<x-ds.empty-state>`
- [ ] Test route + permissions inchangés

## Procédure développeur

1. Ouvrir la vue Blade
2. Remplacer l'en-tête par `<x-ds.page-header>`
3. Encapsuler le contenu dans `<x-ds.page-shell>`
4. Migrer table → `<x-ds.card :padding="false">` + `.data-table`
5. Supprimer flash/session status dupliqués
6. `php artisan test --filter=NomDuModule`

## Incompatibilités connues

- Slot `$header` dans `x-app-layout` : utiliser `<x-ds.page-header>` **dans** le slot
- Pagination Laravel : placer dans `card-footer`
- PDF exports : hors scope DS (templates dédiés)

## Pages migrées (référence)

- `resources/views/budget/lignes/index.blade.php`
- `resources/views/budget/situation/index.blade.php`
- `resources/views/recettes/dashboard.blade.php`
- `resources/views/dashboard.blade.php` (en-tête + conteneur)

## Dette résiduelle

~300 vues utilisent encore des patterns legacy — migration progressive par module prioritaire (budget, engagements, exécution).
