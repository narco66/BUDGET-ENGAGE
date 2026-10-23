# Modèle de données budget — BUDGET-ENGAGE

Document de référence pour la Phase 2 (fusion imports, restes à payer, situation budgétaire).

## Vue d'ensemble

Le module budget repose sur **deux niveaux** complémentaires :

| Niveau | Table / Modèle | Rôle |
|--------|----------------|------|
| Nomenclature | `budget_lignes` / `BudgetLigne` | Structure hiérarchique (titres, chapitres, articles…) importée depuis Excel/CSV 2026 |
| Opérationnel | `lignes_budgetaires` / `LigneBudgetaire` | Lignes de crédit consommées par engagements, liquidations, ordonnancements et paiements |

Les montants d'exécution (`montant_engage`, `montant_liquide`, `montant_ordonnance`, `montant_paye`) sont maintenus sur **`lignes_budgetaires`**.

## Imports budget

### Canonique : `budget_imports` / `BudgetImport`

- Point d'entrée du module **Nomenclature** (`/budget/nomenclature`)
- Modes : import Excel 2026, simulation, remplacement, restauration
- Alimente `budget_lignes` puis, via synchronisation, `lignes_budgetaires`

### Legacy : `imports_budget` / `ImportBudget` (déprécié)

- Ancien flux CSV plat (département / direction / service)
- **Non supprimé** : données conservées, pont automatique vers `budget_imports`
- Service : `App\Services\Budget\BudgetLegacyImportBridge`
- Colonnes de liaison :
  - `imports_budget.migrated_budget_import_id` → `budget_imports.id`
  - `budget_imports.legacy_import_budget_id` → `imports_budget.id`

Migration incrémentale : `2026_06_27_190000_bridge_legacy_budget_imports.php`

Les routes `budget.imports.*` restent actives pour compatibilité mais redirigent vers la nomenclature.

## Chaîne d'exécution et restes

Service institutionnel : `App\Services\Budget\BudgetRestesService`

```
Plafond (montant_revise ou montant_vote)
  → Engagé
    → Liquidé
      → Ordonnancé
        → Payé
```

Restes calculés :

| Reste | Formule |
|-------|---------|
| À engager | plafond − engagé |
| À liquider | engagé − liquidé (≥ 0) |
| À ordonnancer | liquidé − ordonnancé (≥ 0) |
| À payer | ordonnancé − payé (≥ 0) |

Accès :

- Par ligne : `$ligne->restesExecution()` ou `$ligne->creditDisponible()`
- Par exercice : `BudgetRestesService::syntheseExercice()` et `lignesExercice()`
- Interface : `/budget/situation` (`BudgetSituationController`)

## Reporting

`BudgetReportService` agrège recettes/dépenses (nomenclature) et exécution (lignes liées), enrichi des montants ordonnancés et des restes via `BudgetRestesService`.

## Bonnes pratiques

1. **Nouveaux imports** : toujours via `/budget/nomenclature`
2. **Ne pas** utiliser `migrate:fresh` en production
3. **Synchroniser** après import nomenclature pour créer/mettre à jour les lignes opérationnelles
4. **Consulter la situation budgétaire** pour le suivi des restes à exécuter
