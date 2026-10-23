# Rapport d'analyse — Plan Annuel de Performance (PAP) 2026 de la CEEAC

## 1. Structure réelle du document

Le PAP 2026 est une matrice budgétaire hiérarchique à **4 niveaux codés** :

| Niveau | Longueur du code | Exemple | Table cible |
|--------|------------------|---------|-------------|
| Pilier | 3 chiffres | `201` PILIER 1 | `piliers` |
| Axe stratégique | 4 chiffres | `2011` AXE 1 | `axes_strategiques` |
| Produit | 5 chiffres | `20111` | `produits` |
| Sous-produit | 6 chiffres | `201111` | `sous_produits` |

Chaque ligne porte trois montants pour 2026 : **TOTAL**, part **CEEAC**, part **PTF** (partenaires
techniques et financiers), ainsi que des colonnes comparatives 2025 (CEEAC voté, réalisations CEEAC,
PTF voté, réalisations PTF) lorsque renseignées.

Le document fournit aussi, par axe, des intitulés d'objectifs/résultats et des activités détaillées
(tâches au niveau 6 chiffres) ; il ne contient **pas** d'indicateurs chiffrés, d'unités de mesure
formalisées, de responsables nommés ni d'échéances dans la partie budgétaire analysée.

## 2. Volumétrie importée

- **6 piliers**, **21 axes**, **71 produits**, **214 sous-produits** — soit **312 lignes** budgétaires.
- La transcription couvre la hiérarchie complète du document jusqu'au niveau sous-produit (6 chiffres).
- Quelques codes en doublon du document (artefacts OCR sous l'axe 2012) sont suffixés (`201211b`,
  `201212b`) pour garantir l'unicité, le libellé d'origine étant préservé.

## 3. Règles de codification détectées

- Le code d'un niveau **préfixe** celui de son parent (`2011` → parent `201`).
- Les codes sont **uniques** dans tout le document.
- Un pilier n'a pas de parent ; tout autre niveau référence le code de son parent direct.

## 4. Réconciliation financière (vérifiée)

Les montants sont exprimés en **milliers de FCFA** dans le document ; ils sont convertis en **FCFA**
(× 1000) à l'import. La somme des piliers se réconcilie **exactement** avec l'en-tête officielle :

| Agrégat | Valeur importée (FCFA) | En-tête officielle | Écart |
|---------|------------------------|--------------------|-------|
| TOTAL | 22 365 281 000 | 22 365 281 000 | 0 |
| CEEAC | 8 335 000 000 | 8 335 000 000 | 0 |
| PTF | 14 030 281 000 | 14 030 281 000 | 0 |

Les niveaux Pilier et Axe se réconcilient parfaitement entre eux et avec le total général.

## 5. Incohérences détectées dans le document source

Le contrôle de cohérence des sous-totaux révèle **8 incohérences présentes dans le document source**
lui-même (4 au niveau axe, 4 au niveau produit). Elles sont conservées telles quelles (fidélité au
document) et signalées en **avertissements** non bloquants :

Niveau axe (somme des produits vs montant de l'axe) :

| Axe | Somme des produits (milliers) | Montant déclaré (milliers) | Écart |
|-----|-------------------------------|----------------------------|-------|
| 2022 | 370 000 | 275 000 | +95 000 |
| 2033 | 1 126 000 | 1 101 000 | +25 000 |
| 2052 | 75 000 | 150 000 | −75 000 |
| 2063 | 912 000 | 792 000 | +120 000 |

Niveau produit (somme des sous-produits vs montant du produit) :

| Produit | Somme des sous-produits (milliers) | Montant déclaré (milliers) | Écart |
|---------|------------------------------------|----------------------------|-------|
| 20214 | 100 000 | 50 000 | +50 000 |
| 20411 | 750 000 | 775 000 | −25 000 |
| 20512 | 231 000 | 297 500 | −66 500 |
| 20632 | 400 000 | 520 000 | −120 000 |

> La réconciliation officielle s'appuie sur le niveau **Axe**, qui est cohérent avec le total général.
> Les écarts ci-dessus n'affectent pas le total importé (calculé sur les piliers).

## 6. Adaptations apportées à la base de données

La hiérarchie RBM existait déjà (`piliers → axes_strategiques → produits → sous_produits → activites
→ taches → indicateurs`). Deux tables ont été ajoutées, sans modifier les tables existantes :

- **`pap_lignes_budgetaires`** — les montants PAP, rattachés à un exercice budgétaire, à une version
  (cadre logique) et au nœud RBM correspondant. Unicité `(exercice, code)`, suppression logique.
- **`pap_imports`** — journal/historique des imports (mode, statut, compteurs, montant, rapport
  d'anomalies, **snapshot** des données remplacées, empreinte fichier, utilisateur, IP, durée).

## 7. Dépendances entre données

- `pap_lignes_budgetaires.exercice_budgetaire_id` → `exercices_budgetaires`
- `pap_lignes_budgetaires.cadre_logique_id` → `cadres_logiques` (version du PAP : `PAP-2026-v1.0`)
- `pap_lignes_budgetaires.{pilier_id, axe_strategique_id, produit_id, sous_produit_id}` → nœuds RBM
- `parent_code` référence le code de la ligne parente (même exercice)

## 8. Principe de remplacement

Le mode **remplacement** ne touche que les `pap_lignes_budgetaires` de l'exercice ciblé : avant
suppression, un **snapshot JSON** complet est enregistré dans `pap_imports.snapshot` (restaurable).
Les nœuds référentiels RBM sont **upsertés** (jamais supprimés) car partagés. Aucune donnée d'un autre
exercice n'est affectée. L'ensemble s'exécute dans une **transaction atomique**.
