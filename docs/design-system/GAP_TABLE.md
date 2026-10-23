# Tableau des écarts — Design System BUDGET-ENGAGE

| Élément | Style de référence (landing / DS) | Écart constaté | Page / zone | Correction | Priorité |
|---------|-----------------------------------|----------------|-------------|------------|----------|
| Boutons | `.btn.btn-primary` arrondi xl | Breeze `rounded-md uppercase tracking-widest` | ~200 vues CRUD | Composants Breeze remappés | **Majeur** ✅ |
| Champs | `.form-input rounded-xl` | `rounded-institutional` mixte | Formulaires | `<x-text-input>` → form-input | **Majeur** ✅ |
| Cartes | `.card` / `<x-ds.card>` | `bg-white shadow-sm` ad hoc | Budget, RBM, GED | Migration progressive | **Majeur** ⏳ |
| Tableaux | `.data-table` | `<table class="min-w-full divide-y">` | Listes CRUD | Pattern lignes/index | **Majeur** ⏳ |
| Liens | `text-ceeac-bright` | `text-indigo-600` | Actions table | Remplacement global | **Modéré** ⏳ |
| Flash | `<x-ds.flash-messages>` | Blocs green-50 manuels | ~80 vues | Supprimer doublons | **Modéré** ⏳ |
| En-têtes | `<x-ds.page-header>` | `h2 font-semibold text-xl` | Headers slot | Composant DS | **Modéré** ⏳ |
| Montants | `<x-ds.money>` tabular-nums | `number_format` sans classe | Fiches budget | Composant money | **Modéré** ⏳ |
| KPI dashboard | `.kpi-card` | Mix cartes Tailwind | dashboard.blade.php | Refonte dashboard | **Modéré** ⏳ |
| Pagination | Dans `.card-footer` | Nu sous table | Listes | Encapsuler | **Mineur** ⏳ |
| États vides | `<x-ds.empty-state>` | Texte gris colspan | Listes | Composant DS | **Mineur** ⏳ |
| Landing styles | `app.css` | Inline `<style>` landing | landing layout | Consolidé | **Mineur** ✅ |
| Auth layout | Split gradient | — | login | OK | — |
| Sidebar | `bg-sidebar-gradient` | — | app layout | OK | — |
| Statuts | badge + libellé | Texte brut parfois | lignes/index | badge-neutral | **Critique** ⏳ |
| Focus accessibilité | ring ceeac-bright | Parfois absent | Anciens boutons | Via .btn | **Critique** ✅ |
| Navigation mobile | Menu landing Alpine | navigation.blade.php legacy | Top nav Breeze | Coexistence OK | **Mineur** |

## Légende priorités

- **Critique** : accessibilité ou confusion métier
- **Majeur** : identité visuelle fragmentée
- **Modéré** : confort / cohérence
- **Mineur** : polish

## Page migrée de référence

`resources/views/budget/lignes/index.blade.php` — modèle pour toutes les listes CRUD.
