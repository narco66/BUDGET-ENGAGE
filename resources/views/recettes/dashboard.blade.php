<x-app-layout>
    <x-slot name="header">
        <x-ds.page-header
            :title="__('Gestion des recettes')"
            :description="__('Suivi des perceptions et rapprochement avec le budget de recettes.')"
            :breadcrumb="[
                ['label' => __('Accueil'), 'url' => route('dashboard')],
                ['label' => __('Recettes')],
            ]"
        >
            <x-slot:actions>
                @can('create', App\Models\RecettePerception::class)
                    <a href="{{ route('recettes.perceptions.create', $exercice ? ['exercice_budgetaire_id' => $exercice->id] : []) }}">
                        <x-primary-button type="button">{{ __('Nouvelle perception') }}</x-primary-button>
                    </a>
                @endcan
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
            </form>
        </x-ds.card>

        @if ($exercice && $synthese)
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <x-ds.kpi-card :label="__('Prévu')" tone="primary">
                    <x-slot:value><x-ds.money :value="$synthese['montant_prevu']" /></x-slot:value>
                </x-ds.kpi-card>
                <x-ds.kpi-card :label="__('Perçu')" tone="success">
                    <x-slot:value><x-ds.money :value="$synthese['montant_percu']" /></x-slot:value>
                </x-ds.kpi-card>
                <x-ds.kpi-card :label="__('Écart')">
                    <x-slot:value><x-ds.money :value="$synthese['ecart']" /></x-slot:value>
                </x-ds.kpi-card>
                <x-ds.kpi-card :label="__('Taux réalisation')" :value="$synthese['taux_realisation'].' %'" />
            </div>

            <x-ds.card :title="__('Rapprochement par ligne de recette')" :padding="false">
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('Code') }}</th>
                                <th>{{ __('Libellé') }}</th>
                                <th class="text-right">{{ __('Prévu') }}</th>
                                <th class="text-right">{{ __('Perçu') }}</th>
                                <th class="text-right">{{ __('Taux') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rapprochement as $row)
                                <tr>
                                    <td class="font-mono">{{ $row['code'] }}</td>
                                    <td>{{ $row['libelle'] }}</td>
                                    <td class="text-right"><x-ds.money :value="$row['montant_prevu']" :show-currency="false" /></td>
                                    <td class="text-right"><x-ds.money :value="$row['montant_percu']" :show-currency="false" /></td>
                                    <td class="text-right">{{ $row['taux_realisation'] }} %</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="p-0"><x-ds.empty-state :title="__('Aucune ligne de recette importée.')" class="border-0 shadow-none" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ds.card>

            <x-ds.card :title="__('Dernières perceptions')" :padding="false">
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>{{ __('N°') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Ligne') }}</th>
                                <th class="text-right">{{ __('Montant') }}</th>
                                <th>{{ __('Statut') }}</th>
                                <th class="text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($perceptions as $p)
                                <tr>
                                    <td class="font-mono">{{ $p->numero }}</td>
                                    <td>{{ $p->date_perception?->format('d/m/Y') }}</td>
                                    <td>{{ $p->budgetLigne?->code ?? '—' }}</td>
                                    <td class="text-right"><x-ds.money :value="$p->montant" :show-currency="false" /></td>
                                    <td><span class="badge badge-neutral">{{ $p->statut->value }}</span></td>
                                    <td class="text-right"><a href="{{ route('recettes.perceptions.show', $p) }}" class="text-ceeac-bright hover:underline">{{ __('Voir') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-0"><x-ds.empty-state :title="__('Aucune perception.')" class="border-0 shadow-none" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ds.card>
        @endif
    </x-ds.page-shell>
</x-app-layout>
