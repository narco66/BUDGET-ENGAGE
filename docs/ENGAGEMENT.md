# Module Engagement (niveau ERP institutionnel)

Montée en niveau **ERP** du module Engagement **existant** de BUDGET-ENGAGE
(2ᵉ maillon de la chaîne de la dépense), réalisée de façon **additive et non
destructive** : le flux historique (création, workflow à 6 étapes, réservation
de crédit à l'approbation, exécution liquidation→ordonnancement→paiement,
annulation, urgences/régularisations) reste **inchangé**. Compatible
PostgreSQL & MySQL ; `SoftDeletes` ; journalisation systématique.

> Principe directeur : **étendre / superposer, jamais dupliquer ni casser**. Les
> entités demandées par le cahier des charges sont mappées sur l'existant quand
> il existe (cf. tableau ci-dessous).

---

## 1. Mapping cahier des charges → implémentation

| Demandé | Implémentation |
|---|---|
| `engagements` | Table existante **étendue** (`engagement_type_id`, `mode_creation`, `transmis_liquidation_at/par`) |
| `engagement_lignes` | = `engagement_imputations` existante, **étendue** (source, activité, centres) |
| `engagement_validations` | = `validations_workflow` existante (6 étapes) — **réutilisée** |
| `engagement_documents` | = **GED** (`HasGedDocuments` + `ged_document_attachments`) + `pieces_justificatives` legacy |
| `engagement_types` / `engagement_type_valeurs` | **Nouvelles** (référentiel polymorphe + EAV) |
| `engagement_versions` / `engagement_reservations_budgetaires` | **Nouvelles** |
| `engagement_historiques` | Journal d'activité dédié (`EngagementAuditService`, log `engagement`) |

---

## 2. Architecture polymorphe (types)

Référentiel **administrable** `engagement_types` (12 types seedés : administratif,
juridique, comptable, pluriannuel, provisionnel, complémentaire, rectificatif,
avenant, annulation, régularisation, interne, externe). Chaque type porte :
`champs_specifiques` (définition EAV : `cle`, `libelle`, `type`, `requis`),
`documents_obligatoires`, `permet_generation`, `permet_creation_manuelle`,
`workflow_code`.

- `engagement_type_valeurs` stocke les valeurs spécifiques (EAV) sans toucher au
  schéma de base.
- `EngagementTypeService` : `syncTypeValeurs()` (persiste les clés définies par
  le type) + `missingRequiredFields()` (champs obligatoires).
- Formulaire d'engagement **étendu additivement** (Alpine) : sélecteur de type +
  section dynamique des champs spécifiques + dimensions analytiques des lignes
  (source de financement, activité PAP, centre de coût, centre de responsabilité).
- CRUD du référentiel : `engagement-types` (route hors préfixe `engagements/`).

---

## 3. Modes de création

`mode_creation` : `manuel` (création directe), `generation` (depuis une
Expression de Besoins validée — cf. `ExpressionBesoinToEngagementService`),
`modification`. La double génération depuis un besoin est empêchée
(`engagement_id` sur le besoin).

---

## 4. Réservation des crédits (ledger)

La **source de vérité** reste `lignes_budgetaires.montant_engage`, incrémentée à
l'approbation par `EngagementWorkflowService::finalizeApproval` (**inchangé**).
En complément, un **registre détaillé** `engagement_reservations_budgetaires` est
alimenté **par événements** (zéro modification du cœur) :

- `RecordEngagementReservation` sur `EngagementApproved` → `recordForEngagement`
  (idempotent).
- `ReleaseEngagementReservation` sur `EngagementStatusChanged` (→ `annule`) →
  `releaseForEngagement`.

---

## 5. Versions & audit

- `EngagementVersionService::snapshot()` : instantané complet (en-tête +
  imputations + EAV) capturé **avant** chaque modification (historise les
  anciennes valeurs) → `engagement_versions`.
- `EngagementAuditService` : journal dédié (log `engagement`) + `timeline()`
  affichée sur la fiche (création, modification, réservation, libération,
  transmission).

---

## 6. GED native

`EngagementGedService::deposer()` dépose toute pièce dans la GED
(`module_source='engagement'`) et la rattache (polymorphe). Les documents hérités
d'une Expression de Besoins sont repris à la conversion. Aucun document hors GED.

---

## 7. Workflow

Circuit existant à 6 étapes (`config/engagement_workflow.php`) : Service →
Directeur → Département → Contrôle Budgétaire → Contrôle Financier → Ordonnateur,
puis réservation des crédits à l'approbation finale. Paramétrable par type via
`workflow_code` (évolution).

---

## 8. Transmission à la Liquidation

`EngagementToLiquidationService::transmit()` **réutilise**
`EngagementExecutionService::recordLiquidation` (aucune duplication des contrôles
budgétaires) : pour chaque imputation, liquide le **montant restant** et reprend
les pièces GED sur la liquidation. **Anti-double transmission** via le marqueur
`transmis_liquidation_at`. Accessible aux titulaires de
`engagement.transfer_liquidation`, sur un engagement approuvé.

---

## 9. Documents & états

`EngagementDocumentController` : **certificat d'engagement** (PDF officiel),
**registre** (PDF + Excel), **bordereau de transmission** (PDF), **état des
crédits réservés** (PDF + Excel, par ligne via `CreditAvailabilityService`).
Gabarits sous `resources/views/engagements/exports/`.

---

## 10. Tableau de bord

`engagements/tableau-de-bord` : KPIs (statuts + **crédits réservés** depuis le
ledger), répartitions par **type / département / source / ligne**, et **alertes
budgétaires** (lignes à taux d'engagement ≥ 90 %).

---

## 11. Permissions

`engagement.{view,create,generate,update,validate,reject,cancel,reserve_budget,
transfer_liquidation,export,admin}` (`EngagementModuleSeeder`, **additives**).
Les permissions historiques `engagements.*` (pluriel) + `workflow.*` qui
gouvernent le flux existant restent **inchangées**.

---

## 12. Services

`EngagementTypeService`, `EngagementReservationService`, `EngagementVersionService`,
`EngagementAuditService`, `EngagementGedService`, `EngagementToLiquidationService`
(tous sous `App\Services\Engagement`). Le flux historique conserve
`EngagementWorkflowService`, `EngagementExecutionService`,
`EngagementCancellationService`, `Budget\CreditAvailabilityService`.

---

## 13. Exploitation

```bash
php artisan migrate
php artisan db:seed --class=EngagementModuleSeeder
php artisan db:seed --class=EngagementTypeSeeder
php artisan test tests/Feature/Engagements
```

Ne **jamais** exécuter `migrate:fresh/refresh/reset`, `db:wipe`. Toutes les
migrations sont incrémentales et additives.

---

## 14. Tests

`tests/Feature/Engagements/` : `EngagementTypeManagementTest`,
`EngagementTypePolymorphismTest` (EAV + dimensions + champ requis),
`EngagementErpServicesTest` (ledger réserve/libère/idempotent, version, GED),
`EngagementDocumentsTest` (certificat/registre/bordereau/crédits/dashboard),
`EngagementTransmissionTest` (transmission, anti-double, reprise GED) — en plus
des suites historiques de workflow/exécution (non-régression).

---

## 15. Points d'intégration

| Module | Intégration |
|---|---|
| Expression des Besoins | Génération automatique (mode `generation`) + reprise GED |
| Budget / PAP | Imputations multi-lignes + dimensions (source, activité PAP, centres) ; contrôle de disponibilité ; ledger de réservation |
| GED | Pièces déposées et reprises automatiquement |
| Liquidation → Ordonnancement → Paiement | Transmission anti-double réutilisant `recordLiquidation` |
| Permissions (Spatie) | `engagement.*` additives + `workflow.*` historiques |
