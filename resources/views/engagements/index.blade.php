<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Engagements')"
            :description="__('Réservation des crédits et certificats d\'engagement.')"
            :breadcrumb="[
                ['label' => __('Accueil'), 'url' => route('dashboard')],
                ['label' => __('Engagements')],
            ]"
        >
            <x-slot:actions>
                @can('create', App\Models\Engagement::class)
                    <a href="{{ route('engagements.create') }}"><x-primary-button type="button">{{ __('Nouvel engagement') }}</x-primary-button></a>
                @endcan
            </x-slot:actions>
        </x-chaine.dossier-header>
    </x-slot>

    <x-ds.page-shell class="space-y-6">
        <x-ds.card>
            <form method="GET" action="{{ route('engagements.index') }}" class="grid md:grid-cols-4 gap-3">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('Numéro ou objet') }}" class="form-input md:col-span-2" />
                <select name="statut" class="form-select">
                    <option value="">{{ __('Tous les statuts') }}</option>
                    @foreach (['brouillon','soumis','en_validation','retourne','rejete','approuve','annule','liquide','ordonnance','paye','cloture'] as $statut)
                        <option value="{{ $statut }}" @selected(request('statut') === $statut)>{{ ucfirst(str_replace('_', ' ', $statut)) }}</option>
                    @endforeach
                </select>
                <select name="exercice_budgetaire_id" class="form-select">
                    <option value="">{{ __('Tous exercices') }}</option>
                    @foreach($exercices as $ex)
                        <option value="{{ $ex->id }}" @selected((string) request('exercice_budgetaire_id') === (string) $ex->id)>{{ $ex->annee }}</option>
                    @endforeach
                </select>
                <div class="md:col-span-4 flex items-center gap-2">
                    <x-primary-button type="submit">{{ __('Filtrer') }}</x-primary-button>
                    <a href="{{ route('engagements.index') }}" class="btn btn-ghost btn-sm">{{ __('Réinitialiser') }}</a>
                </div>
            </form>
        </x-ds.card>

        <x-ds.card :padding="false">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr>
                        <th>{{ __('Numéro') }}</th>
                        <th>{{ __('Exercice') }}</th>
                        <th>{{ __('Ligne') }}</th>
                        <th class="text-right">{{ __('Montant') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th>{{ __('Créé par') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($engagements as $engagement)
                            <tr>
                                <td class="font-mono whitespace-nowrap"><a href="{{ route('engagements.show', $engagement) }}" class="text-ceeac-bright hover:underline">{{ $engagement->numero }}</a></td>
                                <td>{{ $engagement->exerciceBudgetaire?->annee ?? '—' }}</td>
                                <td class="font-mono">{{ $engagement->ligneBudgetaire?->code ?? '—' }}</td>
                                <td class="text-right"><x-ds.money :value="$engagement->montant" :decimals="2" :show-currency="false" /></td>
                                <td><x-chaine.status-badge :statut="$engagement->statut" /></td>
                                <td>{{ $engagement->createur?->name ?? '—' }}</td>
                                <td class="text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('engagements.show', $engagement) }}" class="text-ceeac-bright hover:underline">{{ __('Voir') }}</a>
                                    <a href="{{ route('engagements.workflow.show', $engagement) }}" class="text-gray-500 hover:text-ceeac-primary">{{ __('workflow.title') }}</a>
                                    @can('update', $engagement)
                                        <a href="{{ route('engagements.edit', $engagement) }}" class="text-ceeac-bright hover:underline">{{ __('Modifier') }}</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-0"><x-ds.empty-state :title="__('Aucun engagement.')" :action-url="route('engagements.create')" :action-label="__('Nouvel engagement')" class="border-0 shadow-none" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($engagements->hasPages())
                <div class="card-footer">{{ $engagements->links() }}</div>
            @endif
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
