# Typographie — Design System BUDGET-ENGAGE

## Famille

**Inter** (Bunny Fonts) — `font-sans`

Chargée dans tous les layouts : `inter:300,400,500,600,700,800`

## Échelle

| Rôle | Classe / style | Taille | Graisse | Usage |
|------|----------------|--------|---------|-------|
| Hero H1 | `text-3xl` → `text-5xl` | 30–48px | 700 | Landing hero |
| Section H2 | `.section-title` | 24–30px | 700 | Titres de section |
| Page H1 | `.page-title` | 20px | 700 | En-têtes internes |
| Card H3 | `text-sm font-semibold` | 14px | 600 | En-têtes cartes |
| Corps | `text-sm` / `text-base` | 14–16px | 400 | Contenu |
| Label | `.form-label` | 12px | 600 | Formulaires |
| Eyebrow | `text-xs uppercase tracking-widest` | 12px | 700 | Sections landing |
| KPI | `.kpi-value` | 24px | 700 | Chiffres clés |
| Données | `.tabular-nums` | — | — | Montants, codes |
| Code ligne | `font-mono` | — | — | Codes budget |

## Titres HTML

```html
<h1 class="page-title">Situation budgétaire</h1>
<h2 class="section-title">Synthèse des recettes</h2>
```

## Montants

Toujours `tabular-nums` + composant :

```blade
<x-ds.money :value="$montant" :decimals="0" />
```

## Liens

```html
<a class="text-ceeac-bright hover:underline">Voir</a>
```

## Interdictions

- Pas de `uppercase tracking-widest` sur les boutons (sauf eyebrow)
- Pas de mélange Arial/Helvetica hors fallback système
