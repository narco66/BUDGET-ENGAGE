<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Modifier l\'engagement')"
            :description="\Illuminate\Support\Str::limit($engagement->objet, 120)"
            :statut="$engagement->statut"
            statut-module="engagement"
            :breadcrumb="[
                ['label' => __('Engagements'), 'url' => route('engagements.index')],
                ['label' => $engagement->numero, 'url' => route('engagements.show', $engagement)],
                ['label' => __('Modification')],
            ]"
        />
    </x-slot>

    <x-ds.page-shell class="space-y-4">
        <x-ds.alert type="warning">
            {{ __('Modification autorisée uniquement pour les engagements en brouillon ou retournés.') }}
        </x-ds.alert>
        <x-ds.card>
            <form method="POST" action="{{ route('engagements.update', $engagement) }}" class="space-y-6">@csrf @method('PATCH')
                @include('engagements._form', [
                    'engagement' => $engagement,
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
                    <button class="btn btn-primary">{{ __('Mettre à jour') }}</button>
                    <a href="{{ route('engagements.show', $engagement) }}" class="btn btn-secondary">{{ __('Retour au détail') }}</a>
                    <a href="{{ route('engagements.index') }}" class="btn btn-ghost">{{ __('Liste') }}</a>
                </div>
            </form>
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
