# Refonte de la présentation — Module Expression de Besoins

**Périmètre** : reconstruction de la couche de présentation (vues écran + modèles PDF) du
module *Expression de Besoins* selon les maquettes institutionnelles CEEAC, **sans modifier**
la logique métier (modèles, services, policies, routes existantes, workflow, conversion).

**Principes respectés**
- **Non destructif** : aucune règle métier, route, service ou modèle existant supprimé. Les
  ajouts backend sont **additifs** (colonnes nullable, route lecture seule, méthodes d'export).
- **Données réelles uniquement** : aucune valeur de démonstration codée en dur ; les maquettes
  n'ont servi que de référence visuelle. Absence de donnée → `—`.
- **Composants réutilisables** : Design System partagé `x-ds.*` / `x-chaine.*` pour l'écran ;
  en-tête PDF institutionnel mutualisé pour les documents.
- **Logo centralisé** : `public/images/CEEAC-logo.jpg`.

---

## 1. Charte graphique institutionnelle

Palette CEEAC : bleu primaire `#1E3A8A`, secondaire clair, fond blanc, bordures fines,
en-têtes de tableau bleu foncé. Sémantique couleur des statuts (réutilisée écran + PDF) :

| Statut | Couleur |
|---|---|
| Validé / Converti | vert `#16A34A` |
| Rejeté / Annulé | rouge `#DC2626` |
| Retour correction | orange `#D97706` |
| Soumis / En validation | bleu `#2563EB` |
| Brouillon (défaut) | gris `#64748B` |

---

## 2. Vues écran

| Vue | État |
|---|---|
| **Dashboard** | KPIs (total, montant estimé, en validation, retours, taux de complétude), répartition par département (barres CSS proportionnelles, sans librairie), par statut, par source. |
| **Liste (index)** | Barre de **chips statut + compteurs**, **sélecteur de nature PAP / Hors-PAP** (réutilise le filtre `type_besoin`), filtres (recherche / type / exercice), tableau institutionnel, menu déroulant **« États PDF »**, lien **Archives**. |
| **Wizard création / édition** | Assistant **6 étapes** (Alpine) : Informations générales · Rattachement (cascade PAP / Hors-PAP) · Détail des lignes · Estimation financière (HT / TVA 18 % / TTC calculés) · Justification & pièces · Validation. Tous les noms de champs préservés (soumission inchangée). |
| **Fiche détail (show)** | Onglets DS : infos, justification, rattachement, lignes, pièces, budget, **traçabilité** (timeline + export), **validation/versions** (révision, motif, date). |
| **Archives** | Liste **lecture seule** des besoins supprimés (soft-deleted), colonne « Archivé le ». |
| **Consolidation** | Écran existant + nouvel **export PDF**. |

---

## 3. Documents PDF

### En-tête institutionnel mutualisé
`resources/views/pdf/partials/ceeac-header.blade.php` — paramétrable (`docTitle`,
`docSubtitle`, `metaRows`) : logo + bloc *Commission de la CEEAC* + *BUDGET-ENGAGE* +
cartouche du document + bloc métadonnées. Classes CSS `ceeac-*` ajoutées de façon **additive**
à `pdf/partials/styles.blade.php` (pied paginé via `counter(page)/counter(pages)`).
**Le layout PDF partagé `pdf.layout` (chaîne de la dépense) n'a pas été modifié.**

### Catalogue des PDF

| Document | Vue | Source |
|---|---|---|
| **Fiche d'état de besoins** | `exports/fiche.blade.php` (self-contained, 9 sections, QR, montant en lettres) | `BesoinExportController::fichePdf` |
| **Registre des besoins** | `exports/registre.blade.php` | `registrePdf` |
| **Historique & traçabilité** | `exports/historique.blade.php` | `historiquePdf` |
| **État consolidé** | `exports/consolidation.blade.php` | `ConsolidationController::pdf` |
| **Par structure** | `exports/etat_groupe.blade.php` (réutilisé) | `etatStructurePdf` |
| **Par catégorie** | `exports/etat_groupe.blade.php` (réutilisé) | `etatCategoriePdf` |
| **Besoins PAP / Hors PAP** | `exports/etat_groupe.blade.php` (réutilisé) | `etatPapPdf` / `etatHorsPapPdf` |
| **Bordereau de transmission** | `exports/bordereau.blade.php` (en-tête émetteur/destinataire + signatures) | `bordereauPdf` |
| **État des pièces justificatives** | `exports/etat_pieces.blade.php` | `etatPiecesPdf` |

Tous les états respectent les filtres courants (recherche / type / statut / exercice) et la
**visibilité par utilisateur** (un non-administrateur ne voit que ses besoins).

---

## 4. Versionnement GED des PDF générés

`app/Services/ExpressionBesoin/ExpressionBesoinDocumentArchiveService.php`

À chaque génération de la **fiche d'état de besoins**, le PDF est archivé dans la GED :
- **premier tirage** → dépôt initial (version 1), rattaché au besoin avec le rôle `fiche_generee` ;
- **tirages suivants** → **nouvelle version** du même document GED (les versions antérieures ne
  sont jamais supprimées — traçabilité complète des éditions officielles).

L'archivage est **best-effort** (try/catch + `report()`) : il ne bloque jamais le téléchargement
si la GED est indisponible. Aucun fichier n'est stocké hors GED (réutilise
`GedDocumentService::store` / `addVersion`, SHA-256, codification automatique).

---

## 5. Modèle de données (ajouts additifs)

Migration `2026_07_01_090000` — `expression_besoin_lignes`, colonnes **nullable** :
`code_article`, `categorie` (bien / service / travaux / prestation), `specifications`,
`priorite`, `echeance`. Capturées de bout en bout (wizard → `StoreExpressionBesoinRequest`
→ `ExpressionBesoinService::normalizeLignes`) afin que la fiche se peuple en données réelles.
Les lignes antérieures restent à `NULL`.

---

## 6. Tests

Suite `tests/Feature/ExpressionBesoins/ExpressionBesoinTest.php` étendue :
- rendu des pages principales (dashboard, index, create, edit, show) ;
- filtre par nature PAP / Hors-PAP sur l'index ;
- archives (liste les supprimés, exclut les actifs) ;
- génération des 5 nouveaux états PDF ;
- **archivage + versionnement GED de la fiche** (v1 au premier tirage, v2 au second, sans doublon).

---

## 7. Inventaire des fichiers

**Créés**
- `resources/views/pdf/partials/ceeac-header.blade.php`
- `resources/views/expression_besoins/archives.blade.php`
- `resources/views/expression_besoins/exports/consolidation.blade.php`
- `resources/views/expression_besoins/exports/bordereau.blade.php`
- `resources/views/expression_besoins/exports/etat_pieces.blade.php`
- `app/Services/ExpressionBesoin/ExpressionBesoinDocumentArchiveService.php`
- `app/Support/MontantEnLettres.php`
- `database/migrations/2026_07_01_090000_add_fiche_columns_to_expression_besoin_lignes_table.php`

**Modifiés (présentation / additif)**
- `resources/views/pdf/partials/styles.blade.php` (classes `ceeac-*` additives)
- `resources/views/expression_besoins/` : `index`, `dashboard`, `_form`, `consolidation`,
  `exports/fiche`, `exports/registre`, `exports/historique`, `exports/etat_groupe`
- `app/Http/Controllers/ExpressionBesoins/` : `ExpressionBesoinController` (`archives`),
  `BesoinExportController` (4 états + archivage fiche), `ConsolidationController` (`pdf`)
- `app/Models/ExpressionBesoinLigne.php`, `app/Http/Requests/.../StoreExpressionBesoinRequest.php`,
  `app/Services/ExpressionBesoin/ExpressionBesoinService.php`
- `routes/web.php` (routes additives : `besoins/archives`, 4 états PDF, `consolidation/export/pdf`)

---

## 8. Limites connues (assumées honnêtement)

- **Financement propres / partenaires** : classification heuristique sur le libellé de la source
  (aucun champ dédié n'existe).
- **Versions de besoin** : seuls `version_revision`, `derniere_revision_at`, `derniere_revision_motif`
  existent — pas de cliché complet (*snapshot*) de l'état antérieur.
- **Archives** : lecture seule (pas de restauration ni purge depuis l'écran), par prudence.
