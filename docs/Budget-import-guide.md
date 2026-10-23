# Module « Importation du Budget » — Guide technique & utilisateur

## 1. Vue d'ensemble

Le module importe le Budget (recettes + dépenses) dans une couche de nomenclature dédiée scopée par
exercice et version, avec contrôles, modes d'import, historisation/restauration, audit, rapprochement
PAP et synthèse budgétaire. Il est **distinct** de l'import opérationnel `lignes_budgetaires`
(qui alimente la chaîne de la dépense) ; un lien optionnel relie les deux couches.

## 2. Composants livrés

| Type | Fichier |
|------|---------|
| Migrations | `2026_06_26_140000_create_budget_lignes_table.php`, `..._140100_create_budget_imports_table.php` |
| Modèles | `app/Models/BudgetLigne.php`, `app/Models/BudgetImport.php` |
| Données officielles | `database/data/budget_2026.php` |
| Service | `app/Services/Budget/BudgetImportService.php` |
| Contrôleur | `app/Http/Controllers/Budget/BudgetNomenclatureImportController.php` |
| Form Request | `app/Http/Requests/Budget/StoreBudgetImportRequest.php` |
| Modèle Excel/CSV | `app/Exports/BudgetImportTemplateExport.php`, `app/Exports/Budget/*` |
| Commande CLI | `app/Console/Commands/ImportBudget2026Command.php` |
| Vues | `resources/views/budget/nomenclature/{index,result,history,show,synthese}.blade.php` |
| Tests | `tests/Feature/Budget/BudgetImportTest.php` |

## 3. Permissions

`budget.view`, `budget.import.run`, `budget.replace`, `budget.restore` — attribuées par défaut aux
rôles `administrateur_general` et `administrateur_budget`.

## 4. Accès

Menu **Budget → Importation du Budget** (`/budget/nomenclature`) et **Budget → Synthèse budgétaire**.

## 5. Modes d'importation

| Mode | Effet |
|------|-------|
| `simulation` | Prévisualise (compteurs, créations/MAJ, anomalies). Aucune écriture. |
| `creation` | Crée les lignes manquantes ; ignore les codes existants. |
| `mise_a_jour` | Crée les manquantes et met à jour les existantes. |
| `fusion` | Idem mise à jour (réservé aux évolutions de réconciliation). |
| `remplacement` | Snapshot + suppression des lignes budget de l'exercice, puis recréation. |

## 6. Sources & format

- **Document officiel Budget 2026** intégré (`database/data/budget_2026.php`, montants en FCFA).
- **Fichier Excel/CSV** conforme au modèle (feuilles « Instructions » + « Lignes Budget »).

Colonnes du modèle : Section, Niveau, Catégorie, Code, Code parent, Libellé, Montant total,
Montant CEEAC, Montant PTF, Source.

## 7. Contrôles de validation

- Champs obligatoires (code, libellé, section, niveau) ; section ∈ {recette, depense} ;
  niveau ∈ {titre, chapitre, article, paragraphe, ligne}.
- Unicité des codes (doublon → avertissement).
- Existence du parent + **niveau parent supérieur** (sinon erreur majeure).
- Montants ≥ 0 (négatif → bloquant).
- CEEAC + PTF = TOTAL (sinon avertissement).
- Cohérence des sous-totaux parent/enfants (sinon avertissement).
- **Rapprochement PAP** : la ligne `catégorie = pap` est comparée au total du PAP importé.

Anomalies classées : **bloquant / majeur / avertissement / information**. Toute anomalie bloquante
annule l'import (statut `echoue`, aucune écriture).

## 8. Exécution en ligne de commande

```bash
php artisan budget:import-2026 --mode=remplacement       # import réel
php artisan budget:import-2026 --simulate                # simulation
# Options : --mode=, --year=2026, --budget-version=1.0, --user=email@exemple
```

La commande affiche un tableau récapitulatif et vérifie l'équilibre recettes = dépenses =
40 305 795 803 FCFA.

## 9. Synthèse budgétaire

`/budget/nomenclature/synthese` : totaux recettes / dépenses, **équilibre R − D**, ventilation
CEEAC / PTF, **rapprochement Budget ↔ PAP**, et tableaux par titre (recettes et dépenses).

## 10. Audit & historisation

Chaque import écrit dans `budget_imports` (utilisateur, IP, user-agent, mode, version, compteurs,
montants, durée, rapport, snapshot) et dans `journaux_audit` (action `import_budget` /
`restauration_budget`). Le mode remplacement crée une **sauvegarde restaurable** avant suppression.

## 11. Tests

`php artisan test tests/Feature/Budget/BudgetImportTest.php` — 11 tests : équilibre recettes/dépenses
du jeu réel, rapprochement PAP, simulation sans écriture, remplacement + restauration, parent manquant,
montant négatif, incohérence de sous-total (avertissement), non-suppression des autres exercices,
permissions + confirmation de remplacement, rendu de la synthèse, téléchargement du modèle.

## 12. Génération des crédits opérationnels (chaîne de la dépense)

`app/Services/Budget/BudgetSyncService.php` — `synchronize($exercice, $user)` génère / met à jour des
**lignes budgétaires opérationnelles** (`lignes_budgetaires`) à partir des **postes exécutables** du
budget (feuilles de la section dépenses, montant > 0). Chaque poste devient une ligne de crédit :

- `montant_vote` = `montant_revise` = montant du poste budgétaire ;
- `nature_depense` créée/retrouvée par catégorie (code `BUDG-<categorie>`) ;
- `source_financement` selon CEEAC / PTF / Mixte (code `BUDG-<source>`) ;
- lien `budget_lignes.ligne_budgetaire_id` → ligne opérationnelle.

Propriétés : **idempotent** (réexécution = mises à jour), **préserve les montants d'exécution**
(engagé / liquidé / ordonnancé / payé jamais écrasés), atomique, audité (`synchronisation_budget`).
La somme des crédits ouverts générés se réconcilie au total des dépenses (40 305 795 803 FCFA).

Accès : bouton **« Générer les lignes opérationnelles »** sur la page *Synthèse budgétaire*
(`POST /budget/nomenclature/synchronize`, permission `budget.sync`, confirmation requise).

## 13. Suivi de l'exécution & tableau de bord

`app/Services/Budget/BudgetReportService.php` :
- `synthese($exercice)` : totaux recettes/dépenses, équilibre, CEEAC/PTF, et **exécution** (crédits
  ouverts, engagé, liquidé, payé, disponible, taux d'engagement et de paiement) sur les lignes
  opérationnelles issues du budget.
- `executionParCategorie($exercice)` : exécution agrégée par catégorie de dépense.

Surfaces :
- **Page Synthèse budgétaire** : bloc *Exécution budgétaire* (crédits ouverts / engagé / payé /
  disponible + tableau par catégorie).
- **Tableau de bord** (`/dashboard`) : section *Budget* (cartes recettes/dépenses/équilibre/taux
  d'engagement, donut CEEAC/PTF des dépenses, barres d'exécution par catégorie), visible avec
  `budget.view`.

## 14. Contrôle de disponibilité des crédits à l'engagement

`app/Services/Budget/CreditAvailabilityService.php` calcule un **disponible prévisionnel** par ligne :

```
disponible = plafond (révisé/voté) − engagé (engagements approuvés) − réservations (engagements en cours)
```

Les réservations couvrent les engagements **non encore approuvés** (brouillon, soumis, en validation,
retourné) — ce qui empêche plusieurs engagements en circuit de **sur-consommer** collectivement une
ligne (cas que l'ancien contrôle, basé uniquement sur `montant_engage`, ne détectait qu'à l'approbation).

Points de contrôle :
- **Création / mise à jour** d'un engagement (`EngagementController::assertImputationsWithinCredit`) :
  chaque imputation est refusée si elle dépasse le disponible prévisionnel (la MAJ s'exclut elle-même
  de ses propres réservations).
- **Soumission** en circuit de validation (`assertEngagement`).
- **Approbation** (contrôle comptable existant `plafond − engagé`, backstop avec verrou de ligne).

Le **formulaire d'engagement** affiche le disponible par ligne (libellé `(dispo: …)`) et marque en
rouge toute imputation qui dépasse, côté client, avant soumission.

## 15. Export de l'état d'exécution

- **Excel** (`/budget/nomenclature/execution/export/excel`, `BudgetExecutionExport`) — 3 feuilles :
  *Synthèse* (recettes/dépenses/crédits/engagé/payé/disponible/taux), *Par catégorie*,
  *Lignes opérationnelles*.
- **PDF** (`/budget/nomenclature/execution/export/pdf`, DomPDF A4 paysage) — cartes de synthèse +
  exécution par catégorie + détail par ligne.

Boutons **Export Excel / Export PDF** dans le bloc *Exécution budgétaire* de la page Synthèse.

## 16. Alertes de seuil (ligne quasi épuisée)

`app/Services/Budget/BudgetAlertService.php` évalue chaque ligne après variation de sa consommation
(`montant_engage`) ou de son plafond, via un **observer** (`LigneBudgetaireObserver`). Niveaux :

| Niveau | Condition | Seuil (paramétrable) |
|--------|-----------|----------------------|
| Avertissement | taux d'engagement ≥ seuil alerte | `seuil_consommation_budget_alerte` (80 %) |
| Critique | taux ≥ seuil critique | `seuil_consommation_budget_critique` (95 %) |
| Dépassement | engagé > plafond | 100 % |

- Une alerte par ligne (`alertes_budgetaires`), mise à jour à la hausse, **résolue automatiquement**
  quand la consommation repasse sous le seuil.
- À l'escalade vers *critique* / *dépassement*, une **notification base de données**
  (`BudgetThresholdNotification`) est envoyée aux rôles administrateur général/budget et contrôleur
  budgétaire.
- Surfaces : panneau *Alertes de seuil* sur la page Synthèse, badge de comptage dans la section Budget
  du tableau de bord.

## 17. Modification budgétaire — virements de crédits (workflow proposition → approbation)

`app/Services/Budget/VirementCreditService.php` gère un transfert de crédit entre une **ligne source**
et une **ligne destination** (même exercice) en **deux étapes** :

1. **`propose()`** — crée un virement `propose` ; validation (lignes différentes, montant > 0, motif,
   **disponible source** = plafond − engagé suffisant). **Aucun mouvement de crédit.**
2. **`approve()`** — applique le virement (sous **verrou** de ligne, revalidation du disponible) :
   `montant_revise` source −montant, destination +montant ; statut `approuve`. Réévaluation auto des
   alertes (observer).
   **`reject()`** — statut `rejete`, sans mouvement.

- Traçabilité : table `virements_credits` (montants avant/après, motif, proposeur, **approbateur**,
  décision, IP) + `journaux_audit` (`virement_credit_approuve` / `virement_credit_rejete`).
- Permissions : `budget.virement` (proposer), `budget.virement.approve` (approuver/rejeter).
- Accès : menu **Budget → Virements de crédits** (`/budget/virements`) — formulaire de proposition,
  file *en attente d'approbation* (boutons Approuver/Rejeter), historique des virements traités.

## 17 bis. Traitement & historique des alertes

- **Traiter** une alerte (`BudgetAlertService::traiter`) : statut `traitee`, traçabilité
  (traitée par / le / commentaire). Bouton *Traiter* sur le panneau d'alertes de la Synthèse.
- **Restaurer** (`restaurer`) : rouvre une alerte traitée/résolue (statut `nouvelle`).
- **Résolution automatique** : quand la consommation repasse sous le seuil, l'alerte passe `resolue`
  (conservée en historique, plus supprimée) ; elle se **rouvre** automatiquement si le seuil est
  recroisé.
- Page **Budget → Historique des alertes** (`/budget/alertes`, permission `budget.alertes`) : alertes
  traitées/résolues avec bouton *Restaurer*.

## 18. État consolidé PAP ↔ Budget ↔ Exécution

`PapBudgetService::consolideParPilier($exercice)` confronte, par pilier :
**PAP programmé** → **budgété** (lignes budgétaires rattachées au PAP) → **engagé / payé** (exécution),
avec taux de budgétisation et d'engagement.

Page : menu **Budget → État consolidé PAP↔Budget** (`/budget/nomenclature/consolide`) — cartes
(PAP programmé, ligne PAP du budget + écart, budget total dépenses, exécution) + tableau par pilier.

**Exports** : `/budget/nomenclature/consolide/export/excel` (`BudgetConsolidatedExport`, 2 feuilles :
Synthèse consolidée + Par pilier) et `/export/pdf` (DomPDF A4 paysage). Boutons *Export Excel / PDF*
sur la page.

## 19. Limites connues / évolutions possibles

- 4 incohérences de sous-totaux présentes dans le document source (voir rapport d'analyse), signalées
  en avertissements.
- Formats avancés (PDF/MD/JSON/API), files asynchrones et GED : évolutions ultérieures.
