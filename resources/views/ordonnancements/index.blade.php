<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Ordonnancements')"
            :description="__('Contrôle financier final, mandats et ordres de paiement.')"
            :breadcrumb="[
                ['label' => __('Chaîne de la dépense'), 'url' => route('execution.dashboard')],
                ['label' => __('Ordonnancements')],
            ]"
        >
            <x-slot:actions>
                @can('gererTypes', App\Models\Ordonnancement::class)
                    <a href="{{ route('ordonnancements.types.index') }}" class="btn btn-ghost btn-sm">{{ __('Types') }}</a>
                @endcan
                @can('create', App\Models\Ordonnancement::class)
                    <a href="{{ route('ordonnancements.create') }}"><x-primary-button type="button">{{ __('Nouvel ordonnancement') }}</x-primary-button></a>
                @endcan
            </x-slot:actions>
        </x-chaine.dossier-header>
    </x-slot>

    <x-ds.page-shell class="space-y-6">
        <x-ds.card>
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <x-input-label for="q" :value="__('Recherche')" />
                    <x-text-input id="q" name="q" class="block mt-1 w-full" :value="request('q')" placeholder="{{ __('N° ordonnancement, mandat, objet') }}" />
                </div>
                <div>
                    <x-input-label for="statut" :value="__('Statut')" />
                    <select id="statut" name="statut" class="form-select mt-1">
                        <option value="">— {{ __('Tous') }} —</option>
                        @foreach (\App\Enums\StatutOrdonnancement::cases() as $s)
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
                            <th>{{ __('Liquidation') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th class="text-right">{{ __('Montant') }}</th>
                            <th>{{ __('Mandat') }}</th>
                            <th>{{ __('Statut') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ordonnancements as $ordonnancement)
                            <tr>
                                <td class="font-mono"><a href="{{ route('ordonnancements.show', $ordonnancement) }}" class="text-ceeac-bright hover:underline">{{ $ordonnancement->numero ?? '#'.$ordonnancement->id }}</a></td>
                                <td class="font-mono text-sm">{{ $ordonnancement->liquidation?->numero ?? '—' }}</td>
                                <td>{{ $ordonnancement->type?->libelle ?? '—' }}</td>
                                <td class="text-right"><x-ds.money :value="$ordonnancement->montant_ordonnance ?: $ordonnancement->montant_liquide_ref" :decimals="2" :show-currency="false" /></td>
                                <td class="font-mono text-xs">{{ $ordonnancement->mandat_numero ?? '—' }}</td>
                                <td>@include('ordonnancements._statut-badge')</td>
                                <td>{{ optional($ordonnancement->date_ordonnancement)->format('d/m/Y') }}</td>
                                <td class="text-right"><a href="{{ route('ordonnancements.show', $ordonnancement) }}" class="text-ceeac-bright hover:underline">{{ __('Voir') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="p-0"><x-ds.empty-state :title="__('Aucun ordonnancement.')" class="border-0 shadow-none" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($ordonnancements->hasPages())
                <div class="card-footer">{{ $ordonnancements->links() }}</div>
            @endif
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
