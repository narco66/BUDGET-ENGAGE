# Audit initial — Chaîne de la dépense

Date : juin 2026

## Synthèse

| Indicateur | Valeur |
|------------|--------|
| Vues Blade inventoriées | ~94 |
| Templates PDF | 23 |
| Stack PDF | DomPDF (barryvdh/laravel-dompdf) |
| Composants DS utilisés avant refonte | 0 |
| Double stack ERP / Execution | Oui (liquidation → paiement) |

## Architecture

- **Modules ERP** : CRUD complet, workflow, GED, états réglementaires (`liquidations/`, `ordonnancements/`, `paiements/`)
- **Module Execution** : suivi simplifié (`execution/`) + formulaires legacy depuis engagement
- **Expression de besoin** : module autonome avec conversion en engagement
- **Engagement** : pivot de la chaîne, suivi consolidé par imputation déjà présent

## Principales anomalies (avant refonte)

| Module | Problème | Priorité |
|--------|----------|----------|
| Tous | Composants `x-ds.*` / `x-chaine.*` absents | Majeur |
| PDF (23 fichiers) | CSS dupliqué, pas de layout partagé | Majeur |
| Execution create | Styles Breeze legacy (`bg-white shadow-sm`) | Majeur |
| Engagement show | Pas de vue transversale workflow | Majeur |
| Engagements index | Statuts dupliqués inline (match PHP) | Modéré |
| Double PDF engagement | `certificat` vs `engagement_pdf` | Modéré |
| Routes | `liquidations.show` vs `execution.liquidations.show` | Modéré |

## Lot 1 — corrections appliquées

Voir [GAP_TABLE.md](GAP_TABLE.md) et [README.md](README.md).
