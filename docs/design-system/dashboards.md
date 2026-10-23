# Tableaux de bord — Design System BUDGET-ENGAGE

## Référence landing (mock dashboard)

La section hero de `/` montre la composition cible :

- Grille KPI 2×2 avec fond coloré léger
- Badges statut (`badge-success`, `badge-warning`)
- Barres de progression `.progress-bar` / `.progress-fill`
- Mention « Mise à jour il y a X min »

## KPI internes

```blade
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    <x-ds.kpi-card label="Budget révisé" value="42,8 Mrd" tone="primary" />
    <x-ds.kpi-card label="Engagé" value="28,1 Mrd" trend="65,7%" tone="success" />
</div>
```

Classe legacy : `.kpi-card` (dashboard.blade.php)

## Alertes cockpit

Utiliser `.alert-warning` / cartes colorées comme sur la landing (rapports section).

## Filtres exercice

Pattern standard :

```blade
<form method="GET" class="card">
    <div class="card-body flex flex-wrap items-end gap-3">
        <select class="form-select" name="exercice_budgetaire_id" onchange="this.form.submit()">…</select>
    </div>
</form>
```

## Graphiques

- Conteneur `.card`
- Hauteur minimale responsive
- Légende textuelle obligatoire (pas couleur seule)

## Dashboard principal

Fichier : `resources/views/dashboard.blade.php` — à migrer vers composants `ds/*` (priorité Phase 6 UX).

## Données affichées

- Période de référence (exercice, trimestre)
- Date de dernière actualisation si disponible
- Source (nomenclature, lignes opérationnelles)
