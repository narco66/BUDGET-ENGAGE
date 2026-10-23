<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Tableau de bord des ordonnancements')"
            :description="__('Contrôle financier, mandats et transmission au paiement.')"
            :breadcrumb="[
                ['label' => __('Chaîne de la dépense'), 'url' => route('execution.dashboard')],
                ['label' => __('Ordonnancements')],
            ]"
        >
            <x-slot:actions>
                <a href="{{ route('ordonnancements.reports.index') }}"><x-secondary-button type="button">{{ __('États & rapports') }}</x-secondary-button></a>
                <a href="{{ route('ordonnancements.index') }}"><x-primary-button type="button">{{ __('Liste') }}</x-primary-button></a>
            </x-slot:actions>
        </x-chaine.dossier-header>
    </x-slot>

    <x-ds.page-shell class="space-y-6">
        <x-ds.card>
            <form method="GET" class="flex items-end gap-3">
                <div>
                    <x-input-label for="exercice_budgetaire_id" :value="__('Exercice')" />
                    <select id="exercice_budgetaire_id" name="exercice_budgetaire_id" class="form-select mt-1" onchange="this.form.submit()">
                        <option value="">— {{ __('Tous') }} —</option>
                        @foreach ($exercices as $ex)
                            <option value="{{ $ex->id }}" @selected((string) $exerciceId === (string) $ex->id)>{{ $ex->annee }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </x-ds.card>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-ds.kpi-card :label="__('Ordonnancements')" :value="(string) $kpis['nombre']" tone="primary" />
            <x-ds.kpi-card :label="__('Montant ordonnancé')" tone="primary">
                <x-slot:value><x-ds.money :value="$kpis['montant_ordonnance_total']" :decimals="2" :show-currency="false" /></x-slot:value>
            </x-ds.kpi-card>
            <x-ds.kpi-card :label="__('Mandatés')" :value="(string) $kpis['mandates']" tone="success" />
            <x-ds.kpi-card :label="__('En circuit')" :value="(string) $kpis['en_circuit']" />
            <x-ds.kpi-card :label="__('Transmis au paiement')" :value="(string) $kpis['transmis']" tone="success" />
            <x-ds.kpi-card :label="__('Montant transmis')" tone="success">
                <x-slot:value><x-ds.money :value="$kpis['montant_transmis_paiement']" :decimals="2" :show-currency="false" /></x-slot:value>
            </x-ds.kpi-card>
            <x-ds.kpi-card :label="__('Brouillons')" :value="(string) $kpis['brouillons']" />
            <x-ds.kpi-card :label="__('Reste à ordonnancer')" tone="warning">
                <x-slot:value><x-ds.money :value="$kpis['reste_a_ordonnancer']" :decimals="2" :show-currency="false" /></x-slot:value>
            </x-ds.kpi-card>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-ds.card :title="__('Par type')" :padding="false">
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead><tr><th>{{ __('Type') }}</th><th class="text-right">{{ __('Nb') }}</th><th class="text-right">{{ __('Montant') }}</th></tr></thead>
                        <tbody>
                            @forelse ($parType as $row)
                                <tr><td>{{ $row->type?->libelle ?? '—' }}</td><td class="text-right">{{ $row->nombre }}</td><td class="text-right"><x-ds.money :value="$row->montant" :decimals="2" :show-currency="false" /></td></tr>
                            @empty
                                <tr><td colspan="3" class="p-0"><x-ds.empty-state :title="__('Aucune donnée')" class="border-0 shadow-none" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ds.card>

            <x-ds.card :title="__('Par statut')" :padding="false">
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead><tr><th>{{ __('Statut') }}</th><th class="text-right">{{ __('Nb') }}</th><th class="text-right">{{ __('Montant') }}</th></tr></thead>
                        <tbody>
                            @forelse ($parStatut as $row)
                                @php($st = $row->statut instanceof \App\Enums\StatutOrdonnancement ? $row->statut : \App\Enums\StatutOrdonnancement::tryFrom((string) $row->statut))
                                <tr><td>{{ $st?->libelle() ?? ($row->statut ?? '—') }}</td><td class="text-right">{{ $row->nombre }}</td><td class="text-right"><x-ds.money :value="$row->montant" :decimals="2" :show-currency="false" /></td></tr>
                            @empty
                                <tr><td colspan="3" class="p-0"><x-ds.empty-state :title="__('Aucune donnée')" class="border-0 shadow-none" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ds.card>
        </div>
    </x-ds.page-shell>
</x-app-layout>
