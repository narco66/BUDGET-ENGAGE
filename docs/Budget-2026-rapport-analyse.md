# Rapport d'analyse — Budget 2026 de la CEEAC

## 1. Structure réelle du document

Le Budget 2026 comporte deux grandes **sections** :

- **Recettes** : internes (contributions des États membres) et externes (dons des PTF).
- **Dépenses** : nomenclature hiérarchique à 5 niveaux.

| Niveau | Exemple (dépenses) | Exemple (recettes) |
|--------|--------------------|--------------------|
| Titre | DEPENSES DE FONCTIONNEMENT | RECETTES INTERNES |
| Chapitre | 66 (Personnel), 61 (Services) | 7210 (Contributions) |
| Article | 661, 663, 611… | 7411 (Dons projets) |
| Paragraphe | 6610, 6631… | — |
| Ligne | 66101, 66331… | 72103, donateurs |

Chaque ligne porte **TOTAL / CEEAC-EM / PTF** pour 2026, avec colonnes comparatives 2025 (voté,
réalisations) et, pour les recettes, taux de réalisation et variation.

## 2. Volumétrie importée

- **258 lignes** : 5 titres, 14 chapitres, 31 articles, 36 paragraphes, 172 lignes.
- **27 lignes de recettes** (internes + externes), **231 lignes de dépenses** (fonctionnement,
  investissement, équipement).

## 3. Réconciliation (vérifiée)

| Agrégat | Valeur importée (FCFA) | Document |
|---------|------------------------|----------|
| Total recettes | 40 305 795 803 | 40 305 795 803 |
| Total dépenses | 40 305 795 803 | 40 305 795 803 |
| **Équilibre (R − D)** | **0** | équilibré |
| Recettes internes | 26 275 514 803 | idem |
| Recettes externes | 14 030 281 000 | idem |
| Dépenses CEEAC-EM | 26 275 514 803 | idem |
| Dépenses PTF | 14 030 281 000 | idem |

Décomposition des dépenses : Fonctionnement 13 677 514 803 + Investissement 25 887 281 000 +
Équipement 741 000 000 = 40 305 795 803.

## 4. Rapprochement avec le PAP 2026

La ligne « Dépenses liées au Plan annuel de performance » du budget vaut **22 365 281 000 FCFA**,
**exactement égale** à la somme des piliers du PAP 2026 déjà importé. Le service de contrôle
(`rapprochementPap`) confronte automatiquement ces deux montants et produit une information de
réconciliation (ou un avertissement en cas d'écart). CEEAC 8 335 000 000 / PTF 14 030 281 000.

## 5. Incohérences détectées dans le document source

Le contrôle de cohérence des sous-totaux signale **4 écarts présents dans le document source**,
conservés tels quels et signalés en **avertissements** non bloquants :

| Parent | Somme des enfants | Montant déclaré | Écart | Cause |
|--------|-------------------|-----------------|-------|-------|
| 7210 (contributions) | 26 275 514 800 | 26 275 514 803 | −3 | arrondi par pays |
| R-EXT (recettes externes) | 14 030 081 000 | 14 030 281 000 | −200 000 | 741 « Dons » vs titre |
| 23 (immobilisations) | 30 000 000 | 20 000 000 | +10 000 000 | OCR/ventilation 231/236 |
| 231 (bâtiments) | 10 000 000 | 20 000 000 | −10 000 000 | OCR/ventilation |

## 6. Adaptations apportées à la base de données

Deux tables ajoutées, sans modifier la chaîne de la dépense existante :

- **`budget_lignes`** — nomenclature scoped exercice + version : `section` (recette/depense),
  `niveau`, `categorie`, `code`, `parent_code`, montants TOTAL/CEEAC/PTF, comparatifs 2025
  (montant_anterieur, montant_realise, taux_realisation, variation), liens optionnels
  `pap_ligne_budgetaire_id` et `ligne_budgetaire_id`. Unicité `(exercice, code)`, suppression logique.
- **`budget_imports`** — journal/historique (mode, statut, compteurs par niveau + recettes/dépenses,
  montants, rapport d'anomalies, **snapshot** restaurable, empreinte fichier, utilisateur, IP, durée).

## 7. Codes en doublon

Les rares doublons OCR (ex. 66105 « Solde de tout compte » vs « Provision pour régularisation
échelon ») sont suffixés (`66105b`) pour garantir l'unicité, le libellé d'origine étant préservé.
