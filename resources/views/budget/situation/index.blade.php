<x-app-layout>
    <x-slot name="header">
        <x-ds.page-header
            :title="__('Situation budgétaire')"
            :description="__('Restes à engager, liquider, ordonnancer et payer par ligne de crédit.')"
            :breadcrumb="[
                ['label' => __('Budget'), 'url' => route('budget.exercices.index')],
                ['label' => __('Situation budgétaire')],
            ]"
        >
            <x-slot:actions>
                @if ($exercice)
                    <a href="{{ route('budget.situation.export', ['exercice_budgetaire_id' => $exercice->id, 'q' => $search]) }}">
                        <x-secondary-button type="button">{{ __('Export CSV') }}</x-secondary-button>
                    </a>
                @endif
                <a href="{{ route('budget.nomenclature.synthese', $exercice ? ['exercice_budgetaire_id' => $exercice->id] : []) }}">
                    <x-secondary-button type="button">{{ __('Synthèse budgétaire') }}</x-secondary-button>
                </a>
            </x-slot:actions>
        </x-ds.page-header>
    </x-slot>

    <x-ds.page-shell class="space-y-6">
        <x-ds.card>
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <x-input-label for="exercice_budgetaire_id" :value="__('Exercice')" />
                    <select id="exercice_budgetaire_id" name="exercice_budgetaire_id" class="form-input mt-1" onchange="this.form.submit()">
                        @foreach ($exercices as $ex)
                            <option value="{{ $ex->id }}" @selected($exercice && $ex->id === $exercice->id)>{{ $ex->annee }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[12rem]">
                    <x-input-label for="q" :value="__('Recherche (code ou libellé)')" />
                    <x-text-input id="q" name="q" type="search" class="block mt-1 w-full" :value="$search" />
                </div>
                <x-primary-button type="submit">{{ __('Filtrer') }}</x-primary-button>
            </form>
        </x-ds.card>

        @if (! $exercice || ! $synthese || $synthese['nb_lignes'] === 0)
            <x-ds.alert type="warning">{{ __('Aucune ligne budgétaire opérationnelle pour cet exercice.') }}</x-ds.alert>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <x-ds.kpi-card :label="__('Plafond crédits')" tone="primary">
                    <x-slot:value><x-ds.money :value="$synthese['plafond']" /></x-slot:value>
                    <x-slot:trend>{{ __(':n lignes', ['n' => $synthese['nb_lignes']]) }}</x-slot:trend>
                </x-ds.kpi-card>
                <x-ds.kpi-card :label="__('Engagé')">
                    <x-slot:value><x-ds.money :value="$synthese['montant_engage']" /></x-slot:value>
                    <x-slot:trend>{{ __('Reste à engager') }} : <x-ds.money :value="$synthese['reste_a_engager']" :show-currency="false" /></x-slot:trend>
                </x-ds.kpi-card>
                <x-ds.kpi-card :label="__('Liquidé / Ordonnancé')">
                    <x-slot:value><x-ds.money :value="$synthese['montant_liquide']" :show-currency="false" /> / <x-ds.money :value="$synthese['montant_ordonnance']" :show-currency="false" /></x-slot:value>
                    <x-slot:trend>{{ __('Reste à liquider') }} : <x-ds.money :value="$synthese['reste_a_liquider']" :show-currency="false" /></x-slot:trend>
                </x-ds.kpi-card>
                <x-ds.kpi-card :label="__('Payé')" tone="success">
                    <x-slot:value><x-ds.money :value="$synthese['montant_paye']" /></x-slot:value>
                    <x-slot:trend>{{ __('Reste à payer') }} : <x-ds.money :value="$synthese['reste_a_payer']" :show-currency="false" /></x-slot:trend>
                </x-ds.kpi-card>
            </div>

            <x-ds.card :padding="false">
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Code') }}</th>
                                <th>{{ __('Libellé') }}</th>
                                <th class="text-right">{{ __('Plafond') }}</th>
                                <th class="text-right">{{ __('Engagé') }}</th>
                                <th class="text-right">{{ __('Liquidé') }}</th>
                                <th class="text-right">{{ __('Ordonnancé') }}</th>
                                <th class="text-right">{{ __('Payé') }}</th>
                                <th class="text-right">{{ __('R. engager') }}</th>
                                <th class="text-right">{{ __('R. liquider') }}</th>
                                <th class="text-right">{{ __('R. ordonnancer') }}</th>
                                <th class="text-right">{{ __('R. payer') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lignes as $row)
                                <tr>
                                    <td class="font-mono">
                                        <a href="{{ route('budget.lignes.show', $row['id']) }}" class="text-ceeac-bright hover:underline">{{ $row['code'] }}</a>
                                    </td>
                                    <td>{{ $row['libelle'] }}</td>
                                    <td class="text-right"><x-ds.money :value="$row['plafond']" :show-currency="false" /></td>
                                    <td class="text-right"><x-ds.money :value="$row['montant_engage']" :show-currency="false" /></td>
                                    <td class="text-right"><x-ds.money :value="$row['montant_liquide']" :show-currency="false" /></td>
                                    <td class="text-right"><x-ds.money :value="$row['montant_ordonnance']" :show-currency="false" /></td>
                                    <td class="text-right"><x-ds.money :value="$row['montant_paye']" :show-currency="false" /></td>
                                    <td class="text-right text-amber-700"><x-ds.money :value="$row['reste_a_engager']" :show-currency="false" /></td>
                                    <td class="text-right"><x-ds.money :value="$row['reste_a_liquider']" :show-currency="false" /></td>
                                    <td class="text-right"><x-ds.money :value="$row['reste_a_ordonnancer']" :show-currency="false" /></td>
                                    <td class="text-right text-emerald-700"><x-ds.money :value="$row['reste_a_payer']" :show-currency="false" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-ds.card>
        @endif
    </x-ds.page-shell>
</x-app-layout>
