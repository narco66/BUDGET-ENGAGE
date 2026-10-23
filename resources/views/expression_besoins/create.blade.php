<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Nouvelle expression de besoin')"
            :description="__('Saisie structurée en onglets — PAP ou hors PAP.')"
            :breadcrumb="[
                ['label' => __('Expression des besoins'), 'url' => route('expression-besoins.dashboard')],
                ['label' => __('Liste'), 'url' => route('expression-besoins.besoins.index')],
                ['label' => __('Nouveau')],
            ]"
        />
    </x-slot>

    <x-ds.page-shell>
        @include('expression_besoins._form', [
            'exercices' => $exercices, 'departements' => $departements, 'directions' => $directions,
            'services' => $services, 'demandeurs' => $demandeurs, 'piliers' => $piliers, 'axes' => $axes,
            'produits' => $produits, 'sousProduits' => $sousProduits, 'activites' => $activites,
            'taches' => $taches, 'indicateurs' => $indicateurs, 'lignesBudgetaires' => $lignesBudgetaires,
            'sourcesFinancement' => $sourcesFinancement, 'priorites' => $priorites, 'urgences' => $urgences,
        ])
    </x-ds.page-shell>
</x-app-layout>
