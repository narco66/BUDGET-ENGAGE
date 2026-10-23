<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Liquidations')"
            :description="__('Constatation du service fait et certification de la dette.')"
            :breadcrumb="[
                ['label' => __('Chaîne de la dépense'), 'url' => route('execution.dashboard')],
                ['label' => __('Liquidations')],
            ]"
        >
            <x-slot:actions>
                @can('gererTypes', App\Models\Liquidation::class)
                    <a href="{{ route('liquidations.types.index') }}" class="btn btn-ghost btn-sm">{{ __('Types') }}</a>
                @endcan
                @can('create', App\Models\Liquidation::class)
                    <a href="{{ route('liquidations.create') }}"><x-primary-button type="button">{{ __('Nouvelle liquidation') }}</x-primary-button></a>
                @endcan
            </x-slot:actions>
        </x-chaine.dossier-header>
    </x-slot>

    <x-ds.page-shell class="space-y-6">
        <x-ds.card>
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <x-input-label for="q" :value="__('Recherche')" />
                    <x-text-input id="q" name="q" class="block mt-1 w-full" :value="request('q')" placeholder="{{ __('N° liquidation, objet, engagement') }}" />
                </div>
                <div>
                    <x-input-label for="statut" :value="__('Statut')" />
                    <select id="statut" name="statut" class="form-select mt-1">
                        <option value="">— {{ __('Tous') }} —</option>
                        @foreach (\App\Enums\StatutLiquidation::cases() as $s)
                            <option value="{{ $s->value }}" @selected(request('statut') === $s->value)>{{ $s->libelle() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="exercice_budgetaire_id" :value="__('Exercice')" />
                    <select id="exercice_budgetaire_id" name="exercice_budgetaire_id" class="form-select mt-1">
                        <option value="">— {{ __('Tous') }} —</option>
                        @foreach ($exercices as $ex)
                            <option value="{{ $ex->id }}" @selected((string) request('exercice_budgetaire_id') === (string) $ex->id)>{{ $ex->annee }}</option>
                        @endforeach
                    </select>
                </div>
                <x-primary-button type="submit">{{ __('Filtrer') }}</x-primary-button>
            </form>
        </x-ds.card>

        <x-ds.card :padding="false">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('N°') }}</th>
                            <th>{{ __('Engagement') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Objet') }}</th>
                            <th class="text-right">{{ __('Montant net') }}</th>
                            <th>{{ __('Statut') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($liquidations as $liquidation)
                            <tr>
                                <td class="font-mono whitespace-nowrap">
                                    <a href="{{ route('liquidations.show', $liquidation) }}" class="text-ceeac-bright hover:underline">{{ $liquidation->numero ?? '#'.$liquidation->id }}</a>
                                </td>
                                <td class="font-mono text-sm">
                                    @if ($liquidation->engagement)
                                        <a href="{{ route('engagements.show', $liquidation->engagement) }}" class="text-ceeac-bright hover:underline">{{ $liquidation->engagement->numero }}</a>
                                    @else — @endif
                                </td>
                                <td>{{ $liquidation->type?->libelle ?? '—' }}</td>
                                <td class="max-w-xs truncate" title="{{ $liquidation->objet ?? $liquidation->engagement?->objet }}">{{ \Illuminate\Support\Str::limit($liquidation->objet ?? $liquidation->engagement?->objet, 40) }}</td>
                                <td class="text-right"><x-ds.money :value="$liquidation->montant_net ?? $liquidation->montant_liquide" :decimals="2" :show-currency="false" /></td>
                                <td>@include('liquidations._statut-badge')</td>
                                <td>{{ optional($liquidation->date_liquidation)->format('d/m/Y') }}</td>
                                <td class="text-right"><a href="{{ route('liquidations.show', $liquidation) }}" class="text-ceeac-bright hover:underline">{{ __('Voir') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="p-0"><x-ds.empty-state :title="__('Aucune liquidation.')" :action-url="route('liquidations.create')" :action-label="__('Nouvelle liquidation')" class="border-0 shadow-none" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($liquidations->hasPages())
                <div class="card-footer">{{ $liquidations->links() }}</div>
            @endif
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
