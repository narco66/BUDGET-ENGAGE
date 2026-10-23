<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Nouvel engagement')"
            :description="__('Enregistrez le dossier en brouillon avant soumission au workflow.')"
            :breadcrumb="[
                ['label' => __('Engagements'), 'url' => route('engagements.index')],
                ['label' => __('Création')],
            ]"
        />
    </x-slot>

    <x-ds.page-shell class="space-y-4">
        <x-ds.alert type="info">
            {{ __('Renseignez le dossier puis enregistrez en brouillon avant soumission dans le workflow.') }}
        </x-ds.alert>
        <x-ds.card>
            <form method="POST" action="{{ route('engagements.store') }}" class="space-y-6">@csrf
                @include('engagements._form', [
                    'exercices' => $exercices,
                    'lignes' => $lignes,
                    'disponibles' => $disponibles,
                    'departements' => $departements,
                    'directions' => $directions,
                    'services' => $services,
                    'fournisseurs' => $fournisseurs,
                    'agents' => $agents,
                    'engagementTypes' => $engagementTypes,
                    'sourcesFinancement' => $sourcesFinancement,
                    'activites' => $activites,
                ])
                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <button class="btn btn-primary">{{ __('Enregistrer en brouillon') }}</button>
                    <a href="{{ route('engagements.index') }}" class="btn btn-ghost">{{ __('Annuler') }}</a>
                </div>
            </form>
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
