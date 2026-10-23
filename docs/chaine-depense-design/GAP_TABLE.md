# Tableau des écarts — Chaîne de la dépense

| Module | Écran ou PDF | Problème constaté | Impact | Correction proposée | Priorité | Statut |
|--------|--------------|-------------------|--------|---------------------|----------|--------|
| Transversal | Toutes listes | Pas de pattern DS unifié | Incohérence visuelle | `x-chaine.dossier-header` + `x-ds.page-shell` | Majeur | ⏳ Partiel |
| Engagement | show | Pas d'indicateur chaîne | Orientation utilisateur | `x-chaine.workflow-stepper` | Majeur | ✅ |
| Engagement | index | Statuts inline dupliqués | Maintenance | `x-chaine.status-badge` | Modéré | ✅ |
| Besoins | index | Liens gris / pas empty-state DS | UX | Migration DS | Modéré | ✅ |
| Besoins | show, create, edit | Header legacy, onglets ad hoc | Cohérence | DS + `x-chaine.tabs` | Majeur | ✅ |
| Besoins | dashboard, consolidation, rapprochement | KPI ad hoc | Lisibilité | `x-ds.kpi-card` + `page-shell` | Modéré | ✅ |
| Execution | dashboard | KPI ad hoc | Lisibilité | `x-ds.kpi-card` + `data-table` | Modéré | ✅ |
| Execution | create liquidation | Breeze legacy | Rupture identité | `x-ds.card` + dossier-header | Majeur | ✅ |
| PDF | certificat engagement | CSS autonome | Dette / incohérence | `@extends('pdf.layout')` | Majeur | ✅ |
| PDF | 21 autres | CSS dupliqué | Dette | Migrer vers `pdf.layout` | Majeur | ✅ Chaîne dépense |
| Liquidation | index, show | Header legacy, pas workflow | Cohérence | DS + workflow + financial-summary | Majeur | ✅ |
| Liquidation | show | Onglets ad hoc Alpine | Fragmentation | `x-chaine.tabs` | Modéré | ✅ |
| Ordonnancement | index | Header legacy | Cohérence | DS | Modéré | ✅ |
| Ordonnancement | show | Idem onglets | Idem | `x-chaine.tabs` | Modéré | ✅ |
| Paiement | index | Header legacy | Cohérence | DS | Modéré | ✅ |
| Paiement | show | Idem onglets | Idem | Idem | Modéré | ✅ |
| PDF | fiche liquidation | CSS autonome | Dette | `pdf.layout` | Majeur | ✅ |
| Execution | ord/pay create | Breeze legacy | Rupture identité | DS | Majeur | ✅ |

## Pages migrées lot 1

- `engagements/index.blade.php`
- `engagements/show.blade.php`
- `expression_besoins/index.blade.php`
- `execution/dashboard.blade.php`
- `execution/liquidations/create.blade.php`
- `engagements/exports/certificat.blade.php`

## Pages migrées lot 2

- `liquidations/index.blade.php`, `liquidations/show.blade.php`
- `ordonnancements/index.blade.php`, `paiements/index.blade.php`
- `execution/ordonnancements/create.blade.php`, `execution/paiements/create.blade.php`
- `liquidations/exports/fiche.blade.php`
- Partials `_statut-badge` (liquidation, ordonnancement, paiement)

## Pages migrées lot 3

- `ordonnancements/show.blade.php`, `paiements/show.blade.php` (onglets DS, workflow, timeline)
- `liquidations/dashboard.blade.php`, `ordonnancements/dashboard.blade.php`, `paiements/dashboard.blade.php`
- PDF `liquidations/exports/certificat.blade.php`, `ordonnancements/exports/mandat.blade.php`

## Pages migrées lot 4

- `expression_besoins/show.blade.php`, `create.blade.php`, `edit.blade.php`
- `expression_besoins/dashboard.blade.php`, `consolidation.blade.php`, `rapprochement.blade.php`
- PDF : `expression_besoins/exports/fiche.blade.php`, `historique.blade.php`

## Pages migrées lot 5

- `liquidations/show.blade.php` — onglets `<x-chaine.tabs>`, timeline historique
- `engagements/dashboard`, `create`, `edit`
- `execution/liquidations|ordonnancements|paiements/show` et `edit`
- PDF bordereaux : `liquidations`, `engagements`, `ordonnancements`

## Pages migrées lot 6

- PDF chaîne dépense restants → `pdf.layout` (registres, rapports, quittances, ordres, fiche ordonnancement, crédits réservés, `engagement_pdf`)
- Partial `pdf/partials/report-table.blade.php`
- Libellé `workflow.title` corrigé dans engagements index/show
