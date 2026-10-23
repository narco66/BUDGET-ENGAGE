# Module Expression des Besoins

Module unifié d'**expression des besoins** de BUDGET-ENGAGE (Commission de la
CEEAC) : besoins inscrits au **PAP** et besoins **Hors PAP**, avec rattachement
au référentiel RBM/GAR et financier, intégration GED, workflow de validation
hiérarchique et **conversion automatique en Engagement**.

Développé de façon **incrémentale et non destructive** (migrations
incrémentales, `SoftDeletes`, aucune donnée existante supprimée). Compatible
**PostgreSQL & MySQL** (colonnes `string` plutôt qu'`enum` natif).

---

## 1. Tables

| Table | Rôle |
|-------|------|
| `expression_besoins` | Fiche du besoin (en-tête, justification, rattachement PAP/Hors PAP, cycle, conversion, audit) |
| `expression_besoin_lignes` | Lignes du besoin (désignation, quantité, P.U., montant, ligne budgétaire, source, activité PAP) |
| `expression_besoin_validations` | Étapes du circuit de validation instanciées |

Migrations `database/migrations/2026_06_27_11000x_*`. Champs d'audit
(`created_by`/`updated_by`), `timestamps`, `softDeletes`.

### Référence

`EB-{ANNÉE}-{NUMÉRO}` (ex. `EB-2026-0001`), générée au premier enregistrement.

---

## 2. Modèles & enums

- `ExpressionBesoin` — réutilise `HasGedDocuments` (pièces) + `GeneratesGedUuid`.
  Relations : exercice, structure (département/direction/service), demandeur,
  RBM (pilier→tâche, indicateur), lignes, validations, engagement.
- `ExpressionBesoinLigne`, `ExpressionBesoinValidation`.
- Enums (`app/Enums`) : `TypeBesoin` (pap / hors_pap), `StatutBesoin`
  (brouillon, soumis, en_validation, retourne, rejete, valide, converti,
  annule), `PrioriteBesoin`, `NiveauUrgenceBesoin`, `CategorieBesoinHorsPap`
  (12 catégories). Les validations réutilisent `StatutValidationGed`.

---

## 3. Typologie

- **Besoin PAP** : rattachement **imposé** à Pilier → Axe → Produit →
  Sous-produit → **Activité** (+ Tâche, Indicateur, responsable).
- **Besoin Hors PAP** : **catégorie imposée** (fonctionnement, maintenance,
  informatique, RH, logistique, sécurité, urgence, imprévu, représentation,
  communication, investissement institutionnel, autres) + sous-catégorie,
  nature, centre de coût, centre de responsabilité (champs libres).

---

## 4. Fiche en 8 onglets

`resources/views/expression_besoins/_form.blade.php` (Alpine) :
1. Informations générales — 2. Justification — 3. Rattachement (PAP/Hors PAP,
sélecteurs RBM en cascade côté client) — 4. Lignes (multi-lignes dynamiques,
total auto) — 5. Pièces (dépôt **GED** automatique) — 6. Budget (crédit
disponible / impact par ligne) — 7. Validation — 8. Historique.

---

## 5. Contrôles métier (empêchés)

Expression vide, ligne sans désignation, **ligne sans ligne budgétaire**,
**ligne sans source de financement**, soumission sans justification, **besoin
PAP sans activité**, **besoin Hors PAP sans catégorie**, saisie sur **exercice
clôturé/archivé**. Implémentés dans `StoreExpressionBesoinRequest` (+ Update) et
au moment de la soumission.

---

## 6. Workflow de validation

`config/expression_besoin_workflow.php` — 5 étapes :
Chef de Service → Directeur → Département → **Contrôle Budgétaire** →
Validation Finale, mappées sur les permissions `workflow.*`.

`ExpressionBesoinWorkflowService` :
- `submit()` : Brouillon/Retourné → **En validation** (garde justification +
  ≥1 ligne), crée les 5 étapes, notifie les validateurs habilités.
- `decide()` : **Valider** (étape suivante ou → Validé), **Rejeter** (→ Rejeté),
  **Retourner** (→ Retourné, l'auteur corrige). **Ségrégation des rôles** :
  chaque validateur ne traite que son étape.

Notifications (canal `database`) : `BesoinPendingValidationNotification`,
`BesoinDecisionNotification`.

---

## 7. Conversion en engagement

`ExpressionBesoinToEngagementService::convert()` : besoin **Validé** →
**Engagement Brouillon** + imputations (lignes regroupées par ligne budgétaire).
Le crédit est réservé **à l'approbation de l'engagement** par le circuit
existant (`EngagementWorkflowService::finalizeApproval`) — **invariant
budgétaire préservé**, aucune duplication.

- Contrôle de disponibilité prévisionnelle (`CreditAvailabilityService::assertImputations`).
- Reprise des pièces **GED** sur l'engagement.
- Statut besoin → **Converti** (+ `engagement_id`, `converted_at/by`).
- **Anti-double conversion** : policy `convert` exige statut Validé **et**
  `engagement_id` null.

---

## 8. Permissions

`expression_besoins.{view,create,update,submit,validate,reject,convert_engagement,export,admin}`
(9 permissions, `ExpressionBesoinSeeder`, idempotent, octroi **additif** aux
rôles existants). Policy `ExpressionBesoinPolicy` (visibilité cloisonnée :
sans permission transverse, l'utilisateur ne voit que ses besoins).

---

## 9. Tableaux de bord & rapports

- **Dashboard** (`expression-besoins.dashboard`) : KPIs (PAP/Hors PAP, statuts,
  montants demandé/validé), répartitions par **statut / département / ligne
  budgétaire / source**, derniers besoins.
- **Rapports** : fiche **PDF**, registre **PDF** et **Excel**
  (`BesoinExportController` + `BesoinsExport`), filtres propagés.

---

## 10. Journalisation & audit

`ExpressionBesoinAuditService` : journal d'activité dédié (`log_name =
expression_besoin`) pour création, modification, soumission, décisions,
conversion ; `timeline()` alimente l'onglet **Historique**. En complément du
journal budgétaire global (`BudgetJournal` / Spatie Activitylog).

---

## 11. Services

`ExpressionBesoinService` (saisie + lignes + référence), `…WorkflowService`,
`…ToEngagementService`, `…GedService` (dépôt pièces GED), `…AuditService`. Le
reporting est porté par `BesoinExportController` + `BesoinsExport`.

---

## 12. Commandes / exploitation

```bash
php artisan migrate
php artisan db:seed --class=ExpressionBesoinSeeder
php artisan test tests/Feature/ExpressionBesoins
```

Ne **jamais** exécuter `migrate:fresh/refresh/reset`, `db:wipe`.

---

## 13. Tests

`tests/Feature/ExpressionBesoins/ExpressionBesoinTest.php` (SQLite `:memory:`,
21 tests) : création PAP/Hors PAP, contrôles §7, workflow (cycle complet, rejet,
blocage), modification, conversion (+ multi-lignes, reprise GED, anti-double),
audit, exports, contrôle d'accès.

---

## 14. Points d'intégration

| Module | Intégration |
|--------|-------------|
| RBM/PAP | Rattachement Pilier→Tâche + Indicateur (besoins PAP) |
| Budget | Imputation sur lignes budgétaires + contrôle de disponibilité |
| Engagement | Conversion besoin Validé → Engagement Brouillon (invariant budgétaire) |
| GED | Pièces déposées automatiquement, reprises à la conversion |
| Permissions (Spatie) | `expression_besoins.*` + circuit via `workflow.*` |
