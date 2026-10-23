# Guide développeur — Chaîne de la dépense

## Nouvelle page liste

1. `<x-chaine.dossier-header>` dans le slot `header`
2. `<x-ds.page-shell>` pour le contenu
3. Filtres dans `<x-ds.card>`
4. Tableau `.data-table` dans `<x-ds.card :padding="false">`
5. `<x-chaine.status-badge>` pour les statuts
6. `<x-ds.money>` pour les montants
7. `<x-ds.empty-state>` si liste vide

## Nouvelle fiche détail

1. `dossier-header` + actions `@can`
2. `@include('chaine._workflow-panel')` si lié à un engagement
3. `<x-chaine.financial-summary>` pour synthèse
4. `<x-chaine.tabs>` pour sections (GED, workflow, historique)
5. Ne pas dupliquer flash — layout app gère `status`

## Nouveau PDF

Suivre [PDF_GUIDE.md](PDF_GUIDE.md). Ne pas dupliquer le CSS institutionnel.

## Permissions

Ne jamais afficher une action non autorisée. Conserver `@can` / policies existantes.

## Tests

```bash
php artisan test --filter=ChaineDepense
```
