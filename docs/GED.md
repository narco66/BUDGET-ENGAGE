# Module GED — Gestion Électronique des Documents

Module documentaire institutionnel de **BUDGET-ENGAGE** (Commission de la CEEAC) :
archivage électronique, classement, versioning, signature électronique interne,
workflow de validation, conservation numérique, recherche avancée, exports et
intégration aux modules métier.

Développé de façon **incrémentale et non destructive** : aucune table existante
n'a été modifiée, aucune donnée supprimée. Toutes les tables du module sont
préfixées `ged_`.

---

## 1. Architecture & tables

22 tables (préfixe `ged_`), migrations `database/migrations/2026_06_27_1000xx_*` :

| Table | Rôle |
|-------|------|
| `ged_categories` | Catégories documentaires (arborescence `parent_id`) |
| `ged_types_documents` | Types (préfixe de référence, confidentialité/conservation par défaut, signable) |
| `ged_dossiers` | Dossiers de classement (arborescence, rattachement structure/exercice) |
| `ged_conservation_rules` | Règles de conservation (durée, sort final, déclenchement) |
| `ged_workflows` / `ged_workflow_etapes` | Circuits de validation configurables |
| `ged_documents` | Document (métadonnées, fichier, statut, confidentialité, audit) |
| `ged_versions` | Versions de fichier (jamais supprimées physiquement) |
| `ged_signatures` | Signatures électroniques (scellé HMAC, horodatage) |
| `ged_validations` | Étapes de validation instanciées par document |
| `ged_metadonnees` | Métadonnées clé/valeur |
| `ged_tags` / `ged_document_tags` | Mots-clés |
| `ged_document_attachments` | **Pivot polymorphe** : rattachement aux modules métier |
| `ged_partages` | Partages (utilisateur / rôle / lien, niveau d'accès) |
| `ged_acces` | ACL fines par document |
| `ged_archives` | Registre d'archivage (verrouillage, restauration) |
| `ged_journaux` | Journal documentaire (action, IP, user-agent, valeurs) |
| `ged_commentaires` | Commentaires (fil de discussion) |
| `ged_favoris` | Favoris par utilisateur |
| `ged_corbeille` | Corbeille (suppression logique) |
| `ged_numerisations` | Lots de numérisation / OCR |

Toutes les tables possèdent index, clés étrangères, et le plus souvent
`timestamps` + `softDeletes` + champs d'audit (`created_by`/`updated_by`).

### Codification documentaire

`CEEAC/GED/{MODULE}/{TYPE}/{ANNEE}/{NUMÉRO}` — ex. `CEEAC/GED/ENGAGEMENT/CTR/2026/0001`.
Générée automatiquement au dépôt (`GedDocumentService::generateReference`).

---

## 2. Modèles & relations Eloquent

Modèles dans `app/Models/Ged*.php`. Principales relations de `GedDocument` :
`categorie`, `typeDocument`, `dossier`, `conservationRule`, `workflow`,
`exerciceBudgetaire`, `departement`/`direction`/`serviceStructure`,
`versions`, `signatures`, `validations`, `metadonnees`, `tags`,
`attachments`, `partages`, `acces`, `archive`, `journaux`, `commentaires`,
`favoris`, `numerisations`, `createur`/`modificateur`/`archiveur`.

Enums (`app/Enums`) : `StatutDocumentGed`, `NiveauConfidentialiteGed`,
`StatutSignatureGed`, `StatutValidationGed`, `SortFinalConservationGed`.

### Intégration métier (polymorphe)

Le trait `App\Models\Concerns\HasGedDocuments` est appliqué à `Engagement`,
`Liquidation`, `Ordonnancement`, `Paiement`, `LigneBudgetaire`,
`ExerciceBudgetaire`. Il expose :

```php
$engagement->gedDocuments;        // documents GED rattachés
$engagement->gedAttachments;      // lignes de rattachement
```

Pour afficher les documents associés sur une page métier :

```blade
@include('ged._documents_associes', ['entity' => $engagement])
```

(déjà intégré dans `resources/views/engagements/show.blade.php`).

---

## 3. Statuts & confidentialité

- **Statuts** : brouillon, déposé, en validation, validé, rejeté, signé,
  publié, archivé, expiré, supprimé.
- **Confidentialité** : public, interne, restreint, confidentiel, très confidentiel.

Cloisonnement : sans `ged.admin`, un utilisateur ne voit que ses propres
documents et ceux dont le niveau n'est ni *confidentiel* ni *très confidentiel*
(`App\Support\GedDocumentSearch`, `GedDocumentPolicy`).

---

## 4. Permissions & rôles

16 permissions (`GedSeeder`, idempotent) : `ged.view`, `ged.create`,
`ged.update`, `ged.delete`, `ged.archive`, `ged.restore`, `ged.download`,
`ged.preview`, `ged.share`, `ged.sign`, `ged.validate`, `ged.reject`,
`ged.publish`, `ged.search`, `ged.export`, `ged.admin`.

Rôle dédié **`gestionnaire_ged`** (accès complet). Les permissions sont aussi
octroyées **de façon additive** (`givePermissionTo`, jamais `syncPermissions`)
aux rôles existants : `administrateur_general`, `administrateur_budget`,
`controleur_budgetaire`, `controleur_financier`, `ordonnateur`, `comptable`,
`agent_initiateur`, `auditeur`.

Politiques : `GedDocumentPolicy` (avec capacités fines `download`, `preview`,
`share`, `sign`, `validateDocument`, `reject`, `publish`, `archive`, `export`),
`GedCategoriePolicy`, `GedTypeDocumentPolicy`, `GedDossierPolicy`,
`GedWorkflowPolicy`, `GedConservationRulePolicy`.

---

## 5. Workflow documentaire

Circuits configurables (`Circuits de validation`) avec étapes ordonnées :
type (dépôt, contrôle, validation, approbation, signature, publication,
archivage), permission ou rôle requis, délai.

`GedWorkflowService` : `start()` instancie les étapes (ou une étape générique si
aucun circuit n'est défini), `currentPendingStep()`, `decide()`
(valide / rejette / retourne). Décisions tracées et notifiées au déposant.

---

## 6. Signature électronique

`GedSignatureService` — signature interne par **scellé cryptographique** :

```
scellé = HMAC-SHA256( empreinte_SHA256 | id_signataire | horodatage | référence ,  clé )
```

La clé est dérivée de `APP_KEY` (jamais exposée). Après signature le document est
**verrouillé** (`est_verrouille`). `verify()` distingue :

- **intégrité fichier** : empreinte recalculée == empreinte signée ;
- **authenticité du scellé** : HMAC recalculé == scellé stocké.

Pages : vérification (`ged.signatures.verify`) et **certificat PDF**
(`ged.signatures.certificate`). L'architecture est isolée pour permettre une
bascule ultérieure vers un prestataire qualifié **eIDAS** sans refonte.

---

## 7. Archivage & conservation

- **Archivage** (`GedArchiveService`) : verrouillage en lecture seule, registre
  `ged_archives`, restauration contrôlée. Un document à **conservation
  obligatoire** ne peut être restauré.
- **Conservation** : règles par type (durée, sort final : conservation
  permanente / archivage définitif / destruction autorisée). Page des échéances
  et des documents expirés ; action « marquer expiré ».

---

## 8. Recherche, exports, listes

- **Recherche avancée** (`ged.search.index`) : mots-clés/OCR, référence, type,
  catégorie, dossier, statut, confidentialité, exercice, module, n° pièce, tag,
  4 plages de dates. Moteur unique `GedDocumentSearch` partagé avec les exports.
- **Exports** : registre documentaire **PDF / Excel / CSV** (filtres propagés),
  registre des archives (PDF), registre des signatures (PDF).
- **Listes** : documents partagés, confidentiels, expirés.

---

## 9. Stockage des fichiers

- Disque **`local`** (= `storage/app/private`). **Important** : le disque
  `private` référencé ailleurs dans le projet n'existe pas dans la
  configuration ; la GED utilise `local`.
- Arborescence : `ged/{année}/{mois}/{module}/{uuid}.{ext}`.
- Nom d'origine conservé, nom de stockage unique (UUID), empreinte **SHA-256**,
  validation MIME/extension/taille (50 Mo). Accès uniquement via contrôleur
  sécurisé (`ged.documents.download`), jamais en direct.

```bash
php artisan storage:link   # si exposition publique nécessaire (non requis pour la GED)
```

---

## 10. Journalisation & notifications

- Toutes les actions sensibles sont journalisées dans `ged_journaux`
  (utilisateur, action, IP, user-agent, valeurs, module) via
  `GedDocumentService::journal()`.
- Notifications (canal `database`) : demande de signature, décision de workflow,
  signature apposée (`app/Notifications/Ged*`).

---

## 11. Commandes artisan

```bash
php artisan ged:conservation-check          # recense les documents échus
php artisan ged:conservation-check --mark   # marque les documents échus comme expirés
php artisan db:seed --class=GedSeeder       # (ré)installe permissions + rôle GED
```

Planification recommandée (`routes/console.php` ou cron) :
`php artisan ged:conservation-check --mark` quotidiennement.

---

## 12. Sauvegarde & restauration

- **Données** : sauvegarder la base (tables `ged_*`) et le dossier
  `storage/app/private/ged`. Les deux doivent être cohérents (empreintes
  SHA-256 vérifiables).
- **Restauration** : restaurer la base puis le dossier `ged`. La page de
  vérification de signature permet de contrôler l'intégrité après restauration.
- Ne **jamais** exécuter `migrate:fresh`, `migrate:refresh`, `migrate:reset`,
  `db:wipe` en production.

---

## 13. Points d'intégration

| Module | Intégration |
|--------|-------------|
| Engagement / Liquidation / Ordonnancement / Paiement | Rattachement polymorphe + onglet « Documents associés » |
| Ligne / Exercice budgétaire | Rattachement polymorphe |
| Permissions (Spatie) | Permissions `ged.*` + rôle `gestionnaire_ged` |
| Journal d'activité | `ged_journaux` (dédié) |

---

## 14. Tests

`tests/Feature/Ged/GedDocumentTest.php` (SQLite `:memory:`) couvre : dépôt
(référence + SHA-256 + version), versioning, workflow, signature + détection
d'altération, archivage/restauration, contrôle d'accès, rattachement polymorphe.

```bash
php artisan test --filter=GedDocumentTest
```
