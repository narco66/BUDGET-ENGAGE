# Module 2 — Référentiel institutionnel RBM/GAR

Documentation technique du module **RBM/GAR** intégré à **BUDGET-ENGAGE** (Laravel 11, PHP 8.3, MySQL).

---

## 1. Vue d'ensemble

Le module fournit un référentiel hiérarchique conforme à la logique institutionnelle :

```
Pilier → Axe stratégique → Produit → Sous-produit → Activité → Tâche → Indicateur
```

Entités transversales : **Type d'indicateur**, **Unité de mesure**, **Source de vérification**, **Cadre logique**, **Chaîne de résultats**.

Le module est **additif** : aucun fichier existant du budget, des engagements ou des référentiels organisationnels n'a été supprimé.

---

## 2. Schéma de données

### 2.1 Tables principales

| Table | Clé parente | Unicité code |
|-------|-------------|--------------|
| `piliers` | — | globale |
| `axes_strategiques` | `pilier_id` | composite `(pilier_id, code)` |
| `produits` | `axe_strategique_id` | composite |
| `sous_produits` | `produit_id` | composite |
| `activites` | `sous_produit_id` | composite |
| `taches` | `activite_id` | composite |
| `types_indicateurs` | — | globale |
| `unites_mesure` | — | globale |
| `sources_verification` | — | globale |
| `indicateurs` | `type_indicateur_id`, `unite_mesure_id` | globale |
| `cadres_logiques` | — | globale |
| `chaines_resultats` | `cadre_logique_id` (nullable) | composite `(cadre_logique_id, code)` |

### 2.2 Tables pivot et support

| Table | Rôle |
|-------|------|
| `indicateur_tache` | Liaison N-N tâches ↔ indicateurs |
| `indicateur_source_verification` | Liaison N-N indicateurs ↔ sources |
| `cadre_logique_elements` | Éléments polymorphiques d'un cadre logique |
| `rbm_modification_historiques` | Historique des mutations |
| `rbm_observations` | Observations polymorphiques |

### 2.3 Champs communs

Chaque entité principale contient : `code`, `libelle`, `description`, `ordre`, `statut` (`actif`/`inactif`), `created_by`, `updated_by`, `timestamps`, `softDeletes`.

### 2.4 Migrations

| Fichier | Description |
|---------|-------------|
| `2026_05_26_100000_create_rbm_referential_tables.php` | Tables hiérarchiques et indicateurs |
| `2026_05_26_100100_create_rbm_support_tables.php` | Pivots, cadres logiques, historique |

**Commande de déploiement :**

```bash
php artisan migrate
```

> Ne jamais utiliser `migrate:fresh`, `migrate:refresh` ou `db:wipe` en production.

---

## 3. Architecture applicative

### 3.1 Modèles (`app/Models/`)

| Modèle | Trait / remarque |
|--------|------------------|
| `Pilier`, `AxeStrategique`, `Produit`, … | `HasRbmReferentialAttributes`, `SoftDeletes` |
| `AxeStrategique` | `protected $table = 'axes_strategiques'` |
| `TypeIndicateur` | `protected $table = 'types_indicateurs'` |
| `UniteMesure` | `protected $table = 'unites_mesure'` |
| `SourceVerification` | `protected $table = 'sources_verification'` |
| `CadreLogique` | `protected $table = 'cadres_logiques'` |
| `ChaineResultat` | `protected $table = 'chaines_resultats'` |

### 3.2 Services (`app/Services/Rbm/`)

| Service | Responsabilité |
|---------|----------------|
| `RbmReferentialService` | CRUD, archivage, restauration, historique, journal Spatie |
| `RbmUsageGuardService` | Blocage archivage si enfants actifs ou usage |
| `RbmTreeService` | Arborescence et matrice RBM |
| `RbmImportService` | Validation et import Excel/CSV |

### 3.3 Contrôleurs (`app/Http/Controllers/Rbm/`)

| Contrôleur | Rôle |
|------------|------|
| `PilierController` … `ChaineResultatController` | CRUD resource par entité |
| `RbmOverviewController` | Vues arborescence, matrice, synthèse |
| `RbmExportController` | Exports Excel/PDF |
| `RbmImportController` | Import Excel contrôlé |

### 3.4 Policies (`app/Policies/`)

Toutes héritent de `RbmEntityPolicy` et utilisent `ChecksRbmPermissions`.

Permissions globales : `rbm.view`, `rbm.create`, `rbm.update`, `rbm.delete`, `rbm.archive`, `rbm.restore`, `rbm.import`, `rbm.export`, `rbm.validate`, `rbm.admin`.

Permissions granulaires : `piliers.manage`, `axes.manage`, `produits.manage`, `sous_produits.manage`, `activites.manage`, `taches.manage`, `indicateurs.manage`, `cadres_logiques.manage`.

---

## 4. Routes

Préfixe : `/rbm` — Nom : `rbm.*` — Middleware : `auth`, `verified`, `active`, permission RBM.

| Route | Nom | Description |
|-------|-----|-------------|
| `GET /rbm/piliers` | `rbm.piliers.index` | Liste des piliers |
| `GET /rbm/overview/tree` | `rbm.overview.tree` | Arborescence complète |
| `GET /rbm/overview/matrix` | `rbm.overview.matrix` | Matrice RBM/GAR |
| `GET /rbm/overview/synthesis` | `rbm.overview.synthesis` | Synthèse institutionnelle |
| `GET /rbm/export/catalogue/{entity}/excel` | `rbm.export.catalogue-excel` | Catalogue par entité |
| `GET /rbm/export/matrice/excel` | `rbm.export.matrice-excel` | Matrice Excel |
| `GET /rbm/export/synthesis/pdf` | `rbm.export.synthesis-pdf` | Synthèse PDF |
| `GET /rbm/import` | `rbm.import.index` | Formulaire d'import |
| `GET /rbm/import/template` | `rbm.import.template` | Modèle Excel |
| `POST /rbm/import` | `rbm.import.store` | Traitement import |

Entités resource : `piliers`, `axes-strategiques`, `produits`, `sous-produits`, `activites`, `taches`, `types-indicateurs`, `unites-mesure`, `sources-verification`, `indicateurs`, `cadres-logiques`, `chaines-resultats`.

---

## 5. Import Excel

### 5.1 Accès

Menu latéral **RBM / GAR → Import Excel** (permission `rbm.import` ou `rbm.admin`).

### 5.2 Format du fichier

Colonnes attendues (ligne 1 = en-têtes) :

```
Code pilier | Libelle pilier | Code axe | Libelle axe | Code produit | Libelle produit |
Code sous-produit | Libelle sous-produit | Code activite | Libelle activite |
Code tache | Libelle tache | Code indicateur | Libelle indicateur |
Code type indicateur | Code unite mesure | Ordre | Statut
```

### 5.3 Règles de validation

- Chaque ligne décrit un chemin hiérarchique complet jusqu'à la tâche.
- Les champs pilier → tâche sont **obligatoires**.
- Si un indicateur est renseigné : type et unité doivent exister en base.
- En cas d'erreur : **aucune ligne n'est importée** (transaction globale après validation).
- Les codes existants sont **mis à jour** (`updateOrCreate`).

### 5.4 Prérequis

Créer au préalable les types d'indicateurs et unités de mesure (manuellement ou via `RbmReferentialSeeder`).

---

## 6. Export

| Export | Route | Format |
|--------|-------|--------|
| Catalogue par entité | `rbm/export/catalogue/{entity}/excel` | Excel |
| Matrice complète | `rbm/export/matrice/excel` | Excel |
| Synthèse institutionnelle | `rbm/export/synthesis/pdf` | PDF (DomPDF) |

Entités catalogue : `piliers`, `axes-strategiques`, `produits`, `sous-produits`, `activites`, `taches`, `indicateurs`, `cadres-logiques`, `chaines-resultats`.

---

## 7. Factories (tests)

| Factory | Fichier |
|---------|---------|
| `PilierFactory` | `database/factories/PilierFactory.php` |
| `AxeStrategiqueFactory` | `database/factories/AxeStrategiqueFactory.php` |
| `ProduitFactory` | `database/factories/ProduitFactory.php` |
| `SousProduitFactory` | `database/factories/SousProduitFactory.php` |
| `ActiviteFactory` | `database/factories/ActiviteFactory.php` |
| `TacheFactory` | `database/factories/TacheFactory.php` |
| `TypeIndicateurFactory` | `database/factories/TypeIndicateurFactory.php` |
| `UniteMesureFactory` | `database/factories/UniteMesureFactory.php` |
| `IndicateurFactory` | `database/factories/IndicateurFactory.php` |
| `SourceVerificationFactory` | `database/factories/SourceVerificationFactory.php` |
| `CadreLogiqueFactory` | `database/factories/CadreLogiqueFactory.php` |
| `ChaineResultatFactory` | `database/factories/ChaineResultatFactory.php` |

Exemple :

```php
$pilier = Pilier::factory()
    ->has(AxeStrategique::factory()->has(Produit::factory()))
    ->create();
```

---

## 8. Seeders

```bash
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=RbmReferentialSeeder
```

`RbmReferentialSeeder` : données de démonstration non destructives (pilier, axe, type, unité).

---

## 9. Tests automatisés

```bash
php artisan test --filter=Rbm
```

| Fichier | Couverture |
|---------|------------|
| `tests/Feature/Rbm/PilierManagementTest.php` | CRUD, permissions, unicité |
| `tests/Feature/Rbm/RbmImportTest.php` | Template, import, rejet erreurs |

---

## 10. Permissions et rôles

Le rôle `administrateur_budget` reçoit : `rbm.view`, `rbm.create`, `rbm.update`, `rbm.archive`, `rbm.export`, `rbm.import` + permissions granulaires.

Le rôle `administrateur_general` reçoit toutes les permissions via la matrice complète.

---

## 11. Intégration avec l'existant

| Module | État |
|--------|------|
| Utilisateurs / rôles Spatie | Intégré |
| Menu latéral (`layouts/app.blade.php`) | Section RBM / GAR |
| Journal d'activité (`BudgetJournal`) | Mutations RBM tracées |
| Budget (`lignes_budgetaires`) | Non lié (phase 2) |
| Engagements | Non lié (phase 2) |
| PAP | Module absent (extension future) |

---

## 12. Contrôles métier

- Impossible de créer un enfant sans parent (validation FormRequest + FK).
- Unicité des codes par niveau parent (index composite).
- Unicité des libellés par niveau parent.
- Archivage (soft delete) plutôt que suppression physique.
- `forceDelete` interdit dans toutes les policies.
- Archivage bloqué si enfants actifs ou usage (chaîne de résultats).

---

## 13. Commandes utiles

```bash
# Migrations incrémentales
php artisan migrate

# Permissions
php artisan db:seed --class=RolePermissionSeeder

# Données démo RBM
php artisan db:seed --class=RbmReferentialSeeder

# Tests module
php artisan test --filter=Rbm

# Vider le cache permissions après seed
php artisan permission:cache-reset
```

---

## 14. Déploiement — précautions

1. Sauvegarder la base avant `php artisan migrate`.
2. Exécuter `RolePermissionSeeder` pour créer les nouvelles permissions.
3. Vérifier que les rôles métiers reçoivent `rbm.view` minimum.
4. Ne pas exécuter de commandes destructrices (`migrate:fresh`, `db:wipe`).
5. Tester l'import sur un fichier pilote avant production.

---

## 15. Rapport de non-régression

| Zone | Impact |
|------|--------|
| Routes budget / engagements | Aucun changement |
| Référentiels organisationnels | Aucun changement |
| Migrations existantes | Non modifiées |
| Tests existants | 54/55 passent (échec préexistant `RegistrationTest`) |
| Nouveaux tests RBM | 7 tests Feature |

---

## 16. Fichiers créés (synthèse)

### Migrations
- `database/migrations/2026_05_26_100000_create_rbm_referential_tables.php`
- `database/migrations/2026_05_26_100100_create_rbm_support_tables.php`

### Modèles
- `Pilier`, `AxeStrategique`, `Produit`, `SousProduit`, `Activite`, `Tache`
- `TypeIndicateur`, `UniteMesure`, `SourceVerification`, `Indicateur`
- `CadreLogique`, `CadreLogiqueElement`, `ChaineResultat`
- `RbmModificationHistorique`, `RbmObservation`
- `app/Models/Concerns/HasRbmReferentialAttributes.php`

### Contrôleurs
- `app/Http/Controllers/Rbm/*` (14 contrôleurs)

### Services
- `app/Services/Rbm/RbmReferentialService.php`
- `app/Services/Rbm/RbmUsageGuardService.php`
- `app/Services/Rbm/RbmTreeService.php`
- `app/Services/Rbm/RbmImportService.php`

### Exports
- `app/Exports/RbmReferentialExport.php`
- `app/Exports/RbmMatriceExport.php`
- `app/Exports/RbmImportTemplateExport.php`

### Vues
- `resources/views/rbm/**` (57+ fichiers Blade)

### Tests
- `tests/Feature/Rbm/PilierManagementTest.php`
- `tests/Feature/Rbm/RbmImportTest.php`

### Fichiers modifiés
- `routes/web.php`
- `database/seeders/RolePermissionSeeder.php`
- `resources/views/layouts/app.blade.php`

---

## 17. Extensions prévues (phase 2)

- Liaison `lignes_budgetaires` ↔ `activites`
- Module PAP aligné sur la chaîne de résultats
- Import par feuilles séparées (catalogues indépendants)
- Notifications sur validation de référentiel
- Table `rbm_imports` avec workflow de validation différée

---

*Document généré pour BUDGET-ENGAGE — Module RBM/GAR v1.0*
