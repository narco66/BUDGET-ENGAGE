# Module « Importation du Plan Annuel de Performance » — Guide technique & utilisateur

## 1. Vue d'ensemble

Le module importe le PAP dans BUDGET-ENGAGE selon la hiérarchie
`Pilier → Axe → Produit → Sous-produit`, alimente les montants budgétaires (TOTAL / CEEAC / PTF)
par exercice et version, et trace chaque opération (audit + historique restaurable).

## 2. Composants livrés

| Type | Fichier |
|------|---------|
| Migrations | `database/migrations/2026_06_26_120000_create_pap_lignes_budgetaires_table.php`, `..._120100_create_pap_imports_table.php` |
| Modèles | `app/Models/PapLigneBudgetaire.php`, `app/Models/PapImport.php` |
| Données officielles | `database/data/pap_2026.php` |
| Service | `app/Services/Rbm/PapImportService.php` |
| Contrôleur | `app/Http/Controllers/Rbm/PapImportController.php` |
| Form Request | `app/Http/Requests/Rbm/StorePapImportRequest.php` |
| Modèle Excel/CSV | `app/Exports/PapImportTemplateExport.php`, `app/Exports/Pap/*` |
| Commande CLI | `app/Console/Commands/ImportPap2026Command.php` |
| Vues | `resources/views/rbm/pap-import/{index,result,history,show,_issues}.blade.php` |
| Composant | `resources/views/components/pap-statut-badge.blade.php` |
| Tests | `tests/Feature/Rbm/PapImportTest.php` |

## 3. Permissions

`pap.view`, `pap.import`, `pap.replace`, `pap.restore` (ou `rbm.admin`). Attribuées par défaut aux
rôles `administrateur_general` et `administrateur_budget` via `RolePermissionSeeder`.

- `pap.view` : consulter le module et l'historique
- `pap.import` : simuler, créer, mettre à jour
- `pap.replace` : remplacer les données existantes (exige une **confirmation explicite**)
- `pap.restore` : restaurer une sauvegarde

## 4. Accès

Menu **Plan de Performance → Importation du PAP** (`/rbm/pap-import`).

## 5. Modes d'importation

| Mode | Effet |
|------|-------|
| `simulation` | Prévisualise (compteurs, créations/MAJ prévues, anomalies). **Aucune écriture.** |
| `creation` | Crée les lignes manquantes ; ignore les codes existants (comptés en doublons). |
| `mise_a_jour` | Crée les manquantes et met à jour les existantes. |
| `remplacement` | Snapshot + suppression des lignes PAP de l'exercice, puis recréation complète. |

## 6. Sources de données

1. **Document officiel PAP 2026** intégré (`database/data/pap_2026.php`).
2. **Fichier Excel/CSV** conforme au modèle téléchargeable (montants en FCFA).

## 7. Workflow d'import (UI)

1. Choisir exercice, version, mode et source.
2. *(Optionnel)* Téléverser le fichier (.xlsx/.xls/.csv, ≤ 10 Mo).
3. **Simuler** → écran de résultat : compteurs (piliers, axes, produits, sous-produits, lignes,
   créations, MAJ, suppressions, doublons), montant total, anomalies classées
   (**bloquante / avertissement**) avec feuille, ligne, colonne, valeur, règle, correction attendue.
4. Pour un remplacement : cocher la confirmation, puis **Lancer l'importation**.
5. Consulter l'historique et, si besoin, **restaurer** une sauvegarde.

## 8. Contrôles de validation

- Champs obligatoires (code, libellé, niveau) ; niveau parmi `pilier|axe|produit|sous_produit`.
- Unicité des codes (doublons → avertissement, ligne ignorée).
- Existence et cohérence du parent hiérarchique (manquant → **bloquant**).
- Montants ≥ 0 (négatif → **bloquant**).
- CEEAC + PTF = TOTAL (sinon avertissement).
- Cohérence des sous-totaux parent/enfants (sinon avertissement).

Toute anomalie **bloquante** annule l'import (statut `echoue`, aucune écriture).

## 9. Exécution en ligne de commande

```bash
# Import réel (remplacement) du PAP 2026 officiel
php artisan pap:import-2026 --mode=remplacement

# Simulation
php artisan pap:import-2026 --simulate

# Options : --mode=, --year=2026, --pap-version=1.0, --user=email@exemple
```

La commande affiche un tableau récapitulatif et vérifie la réconciliation avec l'en-tête officielle
(22 365 281 000 FCFA).

## 10. Audit & historisation

Chaque import écrit dans `pap_imports` (utilisateur, IP, user-agent, mode, version, compteurs,
montant, durée, rapport d'anomalies, snapshot) et dans `journaux_audit`
(action `import_pap` / `restauration_pap`). Le journal d'audit n'est pas modifiable par les
utilisateurs ordinaires.

## 11. Tests

`php artisan test tests/Feature/Rbm/PapImportTest.php` — 10 tests couvrant : réconciliation du jeu
réel, simulation sans écriture, remplacement + historisation + restauration, parent manquant,
montant négatif, incohérence de sous-total (avertissement), non-suppression des autres exercices,
permissions, confirmation de remplacement, téléchargement du modèle.

## 12. Intégration tableaux de bord & lignes budgétaires

**Liaison budgétaire.** Une colonne `pap_ligne_budgetaire_id` (nullable) a été ajoutée à
`lignes_budgetaires` : une ligne budgétaire peut être rattachée à une ligne PAP (produit ou
sous-produit). Le champ « Ligne PAP rattachée » est disponible dans le formulaire de la ligne
budgétaire. Une ligne PAP peut donc être financée par une ou plusieurs lignes budgétaires.

**Agrégation** — `app/Services/Rbm/PapBudgetService.php` :
- `synthese($exercice)` : montant PAP total, CEEAC, PTF, comptes par niveau, montant budgété (somme
  des `montant_revise` des lignes liées), nombre de lignes liées, taux de couverture.
- `programmationParPilier($exercice)` : synthèse par pilier (montant PAP / CEEAC / PTF, budgété, écart,
  couverture).
- `programmationHierarchie($exercice)` : ventilation imbriquée **Pilier → Axe → Produit**. Le montant
  budgété est remonté à chaque niveau via le préfixe du code PAP rattaché (3/4/5 chiffres) ; une ligne
  budgétaire liée à un sous-produit (6 chiffres) alimente son produit, son axe et son pilier.
- `programmationFlat($exercice)` : version à plat (une ligne par pilier/axe/produit) pour les exports.

**Tableau de bord** (`/dashboard`) : une section « Plan Annuel de Performance » affiche les cartes
(total, CEEAC, PTF, couverture) et la répartition par pilier, visible pour les profils disposant de
`pap.view`/`pap.import`/`rbm.admin`.

**État de programmation** (`/rbm/pap-import/programmation`) : tableau **dépliable** Pilier → Axe →
Produit confrontant le montant PAP programmé et le montant budgété des lignes liées (écart, couverture),
avec sélecteur d'exercice, **filtre par niveau** (`niveau=tous|pilier|axe|produit`) et **filtre par
source** (`source=tous|ceeac|ptf|mixte`). En mode « tous / toutes » l'arborescence est dépliable ;
dès qu'un filtre niveau ou source est posé, un tableau à plat avec colonnes **Niveau** et **Source**
(badge CEEAC / PTF / Mixte) est affiché. Une ligne est classée *CEEAC* (CEEAC>0, PTF=0), *PTF*
(PTF>0, CEEAC=0), *Mixte* (les deux > 0) ou *Néant* (montant nul) via `PapBudgetService::classifySource()`.

**Graphes du tableau de bord** :
- **Camembert (donut) CEEAC vs PTF** : répartition du montant PAP total par source de financement
  (montants + parts en %).
- **Barres « Couverture budgétaire par pilier »** : taux de couverture par pilier + couverture globale.

> Note technique : pour les statistiques de la vue, utiliser des directives Blade **inline**
> `@php(...)` plutôt qu'un bloc `@php ... @endphp` lorsqu'un `@php(...)` inline existe déjà dans la même
> vue — le `@endphp` d'un bloc peut sinon être apparié au mauvais `@php(` (regex paresseuse de Blade).

Exports de l'état de programmation :
- **Excel** : `/rbm/pap-import/programmation/export/excel` (`PapProgrammationExport`, feuille à plat
  pilier/axe/produit + ligne total).
- **PDF** : `/rbm/pap-import/programmation/export/pdf` (DomPDF, A4 paysage, vue
  `rbm.pap-import.programmation_pdf`).
- **Répartition par source de financement (CEEAC vs PTF)** :
  `/rbm/pap-import/programmation/export/source-financement` (`PapSourceFinancementExport`, classeur à
  2 feuilles : « Synthèse » CEEAC/PTF avec parts en %, et « Détail par source » par pilier/axe/produit
  avec part CEEAC % et part PTF %).

## 13. Limites connues / évolutions possibles

- Granularité : hiérarchie complète jusqu'au **sous-produit** (312 lignes : 6 piliers, 21 axes,
  71 produits, 214 sous-produits).
- Le document source contient 8 incohérences de sous-totaux (4 axes + 4 produits, voir le rapport
  d'analyse), signalées en avertissements non bloquants.
- Formats avancés (PDF/Markdown/JSON/API), files d'attente asynchrones et GED ne sont pas inclus dans
  cette itération (cœur fonctionnel) et constituent des évolutions ultérieures.
