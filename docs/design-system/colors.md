# Couleurs — Design System BUDGET-ENGAGE

## Palette institutionnelle (référence landing)

| Token | HEX | RGB | Usage |
|-------|-----|-----|-------|
| `ceeac-primary` | `#1E3A8A` | 30, 58, 138 | Titres, sidebar, identité |
| `ceeac-deep` | `#172554` | 23, 37, 84 | Footer, overlays |
| `ceeac-bright` | `#2563EB` | 37, 99, 235 | Liens, CTA, focus |
| `ceeac-sky` | `#60A5FA` | 96, 165, 250 | Accents hero, badges |
| `ceeac-light` | `#DBEAFE` | 219, 234, 254 | Fonds légers, hover |
| `ceeac-muted` | `#F1F5F9` | 241, 245, 249 | Fond app authentifiée |
| `ceeac-ink` | `#1E293B` | 30, 41, 59 | Texte principal |
| `ceeac-slate` | `#64748B` | 100, 116, 139 | Texte secondaire |

## États sémantiques

| Token | HEX | Fond | Usage |
|-------|-----|------|-------|
| `success` | `#10B981` | `#ECFDF5` | Validation, payé, succès |
| `warning` | `#F59E0B` | `#FFFBEB` | Alertes, seuils |
| `danger` | `#EF4444` | `#FEF2F2` | Erreurs, rejet, annulation |
| `info` | `#0EA5E9` | `#F0F9FF` | Information, soumis |
| `accent` | `#7C3AED` | `#F5F3FF` | RBM, workflow |

## Échelle numérique (Tailwind)

Disponible sous `ceeac-50` … `ceeac-950` (voir `tailwind.config.js`).

## Dégradés

| Classe | Définition |
|--------|------------|
| `bg-ceeac-gradient` | `#1E3A8A` → `#2563EB` (135°) |
| `bg-ceeac-hero` | `#172554` → `#1E3A8A` → `#2563EB` |
| `bg-sidebar-gradient` | Sidebar verticale |
| `bg-success-gradient` | Boutons succès |
| `bg-danger-gradient` | Boutons danger |

## Variables CSS (`:root`)

```css
--ceeac-primary: #1E3A8A;
--ceeac-bright: #2563EB;
/* … voir resources/css/app.css */
```

## Contrastes (WCAG AA)

| Combinaison | Ratio approx. | Statut |
|-------------|---------------|--------|
| `ceeac-ink` sur blanc | 12:1 | ✅ |
| `ceeac-primary` sur blanc | 8:1 | ✅ |
| `ceeac-bright` sur blanc | 4.6:1 | ✅ liens |
| Blanc sur `ceeac-primary` | 8:1 | ✅ boutons |
| `ceeac-slate` sur blanc | 4.5:1 | ✅ secondaire |

## Interdictions

- Ne pas utiliser `text-indigo-600` pour les liens (préférer `text-ceeac-bright`)
- Ne pas distinguer un statut budgétaire **uniquement** par la couleur
- Ne pas introduire de palette parallèle (Material, Bootstrap, etc.)

## Classes utilitaires

```html
<span class="badge badge-success">Approuvé</span>
<div class="alert-warning">…</div>
<button class="btn btn-primary">Enregistrer</button>
```
