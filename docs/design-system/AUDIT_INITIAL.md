# Audit initial — Design System BUDGET-ENGAGE

Date : juin 2026  
Référence : page d'accueil `/` (`landing.blade.php`)

## A. État initial

### Architecture visuelle

- **Landing** : design institutionnel mature (hero gradient, KPI mock, sections animées)
- **Auth** : split panel aligné landing (`layouts/guest.blade.php`)
- **App authentifiée** : sidebar gradient + topbar blanc (`layouts/app.blade.php`)
- **CSS** : Tailwind + ~250 lignes `@layer components` dans `app.css` (`.btn`, `.card`, `.data-table`, badges)
- **Incohérence** : ~300 vues internes mélangent patterns Breeze legacy et classes DS

### Technologies

- Laravel 11, Blade, Alpine.js, Tailwind 3, Vite, `@tailwindcss/forms`
- Police Inter via Bunny Fonts
- Pas de Vue/React — migration DS 100 % Blade

### Duplications

| Élément | Occurrences |
|---------|-------------|
| Flash session manuel | ~80 vues |
| `bg-white shadow-sm rounded-lg` | ~120 vues |
| `text-indigo-600` liens | ~60 vues |
| Styles inline landing | Consolidés → `app.css` |

### Risques

- Régression visuelle si migration massive sans tests
- Double affichage flash (layout + vue) — corrigé layout
- Statuts engagement sans badge uniforme sur certaines vues

## B. Design System créé

- Tokens `ceeac.*` étendus (50–950) + variables CSS `:root`
- Composants `<x-ds.*>` (10 composants)
- `MoneyFormatter` + `<x-ds.money>`
- Composants Breeze remappés sur `.btn` / `.form-input`
- Documentation `/docs/design-system/` (13 fichiers)

## C. Modifications réalisées (lot 1)

| Fichier | Modification |
|---------|--------------|
| `tailwind.config.js` | Échelle couleur + z-index |
| `resources/css/app.css` | Variables CSS, landing utilities, page-shell |
| `components/ds/*` | Bibliothèque Blade |
| `components/*-button.blade.php` | Classes `.btn` |
| `layouts/app.blade.php` | `<x-ds.flash-messages>` |
| `layouts/landing.blade.php` | Styles → app.css |
| `budget/lignes/index.blade.php` | Migration complète CRUD liste |

## D. Tests

- Suite PHPUnit existante : exécuter après chaque lot
- Test DS ajouté : `DesignSystemTest` (landing + composants)

## E. État final (lot 1)

- **Harmonisé** : landing, auth, layout app, composants de base, 1 page CRUD référence
- **Reste à migrer** : dashboard, nomenclature, engagements, GED, RBM, exécution, recettes, admin
- **Recommandation** : 1 module / sprint, suivre [migration-guide.md](migration-guide.md)
