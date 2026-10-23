<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Modifier l\'expression de besoin')"
            :reference="$besoin->reference"
            :description="\Illuminate\Support\Str::limit($besoin->objet, 120)"
            :statut="$besoin->statut"
            statut-module="besoin"
            :breadcrumb="[
                ['label' => __('Expression des besoins'), 'url' => route('expression-besoins.dashboard')],
                ['label' => $besoin->reference, 'url' => route('expression-besoins.besoins.show', $besoin)],
                ['label' => __('Modifier')],
            ]"
        />
    </x-slot>

    <x-ds.page-shell>
        @include('expression_besoins._form', [
            'besoin' => $besoin, 'documents' => $documents,
            'exercices' => $exercices, 'departements' => $departements, 'directions' => $directions,
            'services' => $services, 'demandeurs' => $demandeurs, 'piliers' => $piliers, 'axes' => $axes,
            'produits' => $produits, 'sousProduits' => $sousProduits, 'activites' => $activites,
            'taches' => $taches, 'indicateurs' => $indicateurs, 'lignesBudgetaires' => $lignesBudgetaires,
            'sourcesFinancement' => $sourcesFinancement, 'priorites' => $priorites, 'urgences' => $urgences,
        ])
    </x-ds.page-shell>
</x-app-layout>
