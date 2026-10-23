# Design System BUDGET-ENGAGE

Documentation officielle du système de design de l'ERP **BUDGET-ENGAGE** (Commission de la CEEAC).

## Référence graphique

La **page d'accueil publique** (`/` → `resources/views/landing.blade.php`) constitue la référence visuelle institutionnelle :

- Identité bleu institutionnel CEEAC (`#1E3A8A` → `#2563EB`)
- Typographie **Inter** (300–800)
- Cartes arrondies (`rounded-2xl`), ombres douces, dégradés hero
- Composants `.btn`, `.card`, `.badge`, `.kpi-card`, `.data-table`

## Stack technique

| Couche | Technologie |
|--------|-------------|
| CSS | Tailwind CSS 3 + `@layer components` dans `resources/css/app.css` |
| Tokens | `tailwind.config.js` → palette `ceeac.*` |
| Composants | Blade (`resources/views/components/`, préfixe `ds/`) |
| Layouts | `layouts/landing`, `layouts/guest`, `layouts/app` |
| JS | Alpine.js (navigation, sidebar, landing) |

## Principes

1. **Institutionnel** — sobriété, lisibilité des montants, confiance
2. **Cohérent** — réutiliser les classes et composants DS, pas de styles ad hoc
3. **Accessible** — contraste, focus visible, statuts texte + couleur
4. **Responsive** — mobile-first, tableaux scrollables
5. **Non destructif** — migration progressive sans régression métier

## Structure de la documentation

| Fichier | Contenu |
|---------|---------|
| [colors.md](colors.md) | Palette, tokens, contrastes |
| [typography.md](typography.md) | Échelle typographique |
| [spacing-layout.md](spacing-layout.md) | Grille, espacements, breakpoints |
| [components.md](components.md) | Bibliothèque de composants |
| [forms.md](forms.md) | Formulaires et validation |
| [tables.md](tables.md) | Tableaux et données financières |
| [dashboards.md](dashboards.md) | KPI, graphiques, alertes |
| [accessibility.md](accessibility.md) | WCAG 2.1 |
| [migration-guide.md](migration-guide.md) | Plan de migration |
| [DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md) | Guide développeur |
| [AUDIT_INITIAL.md](AUDIT_INITIAL.md) | Rapport d'audit visuel |
| [GAP_TABLE.md](GAP_TABLE.md) | Tableau des écarts |
| [RAPPORT_FINAL.md](RAPPORT_FINAL.md) | Rapport de clôture lot 1 |

## Composants DS (Blade)

Préfixe `<x-ds.*>` :

- `page-header` — titre, description, fil d'Ariane, actions
- `page-shell` — conteneur page (`max-w-8xl`)
- `card` — carte structurée header/body/footer
- `kpi-card` — indicateur chiffré
- `empty-state` — état vide
- `alert` / `flash-messages` — retours utilisateur
- `money` — montants FCFA formatés
- `breadcrumb` — fil d'Ariane
- `section-heading` — titres de section (style landing)

## Fichiers sources

```
resources/css/app.css          # Classes composants (.btn, .card, …)
tailwind.config.js             # Tokens Tailwind
resources/views/components/ds/ # Composants Blade DS
app/Support/MoneyFormatter.php # Formatage monétaire
```

## Migration en cours

Voir [migration-guide.md](migration-guide.md) pour l'état page par page.
