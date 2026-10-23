# Espacements & layout — Design System BUDGET-ENGAGE

## Échelle d'espacement

| Token | px | Usage |
|-------|-----|-------|
| 0.5 | 2 | Micro |
| 1 | 4 | Icône-texte |
| 2 | 8 | Inline compact |
| 3 | 12 | Gaps formulaires |
| 4 | 16 | Padding cartes mobile |
| 5 | 20 | — |
| 6 | 24 | Sections internes |
| 8 | 32 | Sections landing |
| 10 | 40 | — |
| 12 | 48 | Hero padding |
| 16 | 64 | Sections majeures |

## Conteneurs

| Classe | Max-width | Usage |
|--------|-----------|-------|
| `max-w-8xl` | 88rem | Landing, pages listes |
| `max-w-7xl` | 80rem | Legacy (à migrer) |
| `max-w-3xl` | 48rem | Formulaires, fiches |
| `.page-shell` | 88rem + `space-y-6` | Pages authentifiées |
| `.page-shell-narrow` | 48rem | CRUD create/edit |

## Grille landing

- Hero : `grid lg:grid-cols-2 gap-14`
- Features : `grid sm:grid-cols-2 xl:grid-cols-4 gap-5`
- Modules : `grid sm:grid-cols-2 lg:grid-cols-3 gap-6`

## Breakpoints (Tailwind)

| Préfixe | Min-width | Usage |
|---------|-----------|-------|
| `sm` | 640px | Tablettes portrait |
| `md` | 768px | Tablettes |
| `lg` | 1024px | Sidebar fixe, nav desktop |
| `xl` | 1280px | Grilles larges |
| `2xl` | 1536px | Grand écran |

## Z-index

| Token | Valeur | Élément |
|-------|--------|---------|
| `z-sticky` | 30 | Topbar |
| `z-sidebar` | 50 | Sidebar |
| `z-modal` | 60 | Modales |
| `z-tooltip` | 70 | Tooltips |

## Layouts

| Layout | Fichier | Contexte |
|--------|---------|----------|
| Public | `layouts/landing.blade.php` | `/` |
| Auth | `layouts/guest.blade.php` | login, register |
| App | `layouts/app.blade.php` | ERP authentifié |

## Sidebar

- Ouverte : `w-64` (16rem)
- Réduite : `w-[4.5rem]`
- Mobile : overlay + `z-sidebar`
