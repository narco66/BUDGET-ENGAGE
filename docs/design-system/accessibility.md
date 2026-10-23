# Accessibilité — Design System BUDGET-ENGAGE

Objectif : **WCAG 2.1 niveau AA** sur les parcours critiques.

## Contraste

Voir [colors.md](colors.md). Privilégier `ceeac-ink` / `ceeac-primary` sur fond clair.

## Focus

Tous les `.btn` et `.form-input` ont :

```css
focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ceeac-bright
```

Ne pas supprimer le focus visible.

## Navigation clavier

- Sidebar : liens `<a>` natifs
- Menus Alpine : `@click.outside`, fermeture Escape (à généraliser)
- Modales : piège focus via `<x-modal>` Breeze

## Formulaires

- Chaque input a un `<label for="…">`
- Erreurs associées visuellement sous le champ
- `aria-invalid` recommandé sur champs en erreur (migration progressive)

## Statuts budgétaires

Toujours combiner :

1. **Libellé texte** (ex. « Approuvé »)
2. **Badge** coloré
3. **Icône** optionnelle sur alertes

```html
<span class="badge badge-success" aria-label="Statut : approuvé">Approuvé</span>
```

## Images

- Logo : attribut `alt="CEEAC"` ou texte alternatif
- Icônes décoratives : `aria-hidden="true"`

## Zones cliquables

Minimum **44×44 px** sur mobile — classes `.btn` respectent `py-2.5`.

## Lecteurs d'écran

- Fil d'Ariane : `<nav aria-label="Fil d'Ariane">`
- Alertes : `role="alert"` sur `<x-ds.alert>`

## Tests recommandés

- Navigation Tab sur login → dashboard → liste CRUD
- axe DevTools sur landing et une page liste migrée
- Zoom 200 % sans perte de contenu
