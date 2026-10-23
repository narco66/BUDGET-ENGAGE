# Design System — Chaîne de la dépense

Extension du Design System BUDGET-ENGAGE pour les modules :

1. Expression de besoin
2. Engagement
3. Liquidation
4. Ordonnancement
5. Paiement
6. Suivi (cockpit `/execution`)

## Composants `<x-chaine.*>`

| Composant | Rôle |
|-----------|------|
| `dossier-header` | En-tête dossier (titre, fil d'Ariane, statut, actions) |
| `status-badge` | Badge statut unifié (besoin / engagement / liquidation) |
| `workflow-stepper` | Indicateur d'avancement multi-étapes |
| `financial-summary` | Grille de métriques financières |
| `financial-metric` | Cellule montant / indicateur |
| `tabs` / `tab-panel` | Onglets structurés (fiches détail) |
| `timeline` | Chronologie traçabilité |
| `control-alert` | Alerte métier structurée (impact, action) |

## Services PHP

- `App\Support\ChaineDepense\StatutPresenter`
- `App\Support\ChaineDepense\DossierWorkflowPresenter`

## PDF

Layout partagé : `resources/views/pdf/layout.blade.php`

Styles : `resources/views/pdf/partials/styles.blade.php`

## Pages migrées (lot 1)

- `engagements/index`, `engagements/show` (+ workflow transversal)
- `expression_besoins/index`
- `execution/dashboard`
- `execution/liquidations/create`
- PDF `engagements/exports/certificat.blade.php`

## Pages migrées (lot 2)

- `liquidations/index`, `liquidations/show` (en-tête, workflow, synthèse financière)
- `ordonnancements/index`, `paiements/index`
- `execution/ordonnancements/create`, `execution/paiements/create`
- PDF `liquidations/exports/fiche.blade.php`
- Badges `_statut-badge` → composant `<x-chaine.status-badge>`

## Pages migrées (lot 3)

- `ordonnancements/show`, `paiements/show` (onglets `<x-chaine.tabs>`, timeline)
- `liquidations/dashboard`, `ordonnancements/dashboard`, `paiements/dashboard`
- PDF `liquidations/exports/certificat.blade.php`, `ordonnancements/exports/mandat.blade.php`

## Pages migrées lot 4

- `expression_besoins/show`, `create`, `edit` (dossier-header, onglets, synthèse financière)
- `expression_besoins/dashboard`, `consolidation`, `rapprochement`
- PDF `expression_besoins/exports/fiche.blade.php`, `historique.blade.php`

## Pages migrées (lot 5)

- `liquidations/show` (onglets DS), `engagements/dashboard`, `create`, `edit`
- Module `execution/*` show et edit (liquidation, ordonnancement, paiement)
- PDF bordereaux liquidations, engagements, ordonnancements

## Pages migrées (lot 6)

- Tous les PDF chaîne de la dépense → `pdf.layout` (registres, rapports, quittances, ordres de virement/paiement, etc.)
- Partial `pdf/partials/report-table.blade.php` pour états tabulaires

## Documentation

- [AUDIT_INITIAL.md](AUDIT_INITIAL.md)
- [GAP_TABLE.md](GAP_TABLE.md)
- [COMPONENTS.md](COMPONENTS.md)
- [PDF_GUIDE.md](PDF_GUIDE.md)
- [DEVELOPER_GUIDE.md](DEVELOPER_GUIDE.md)
