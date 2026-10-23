<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Tableau de bord des engagements')"
            :description="__('Pilotage des réservations budgétaires et du circuit de validation.')"
            :breadcrumb="[
                ['label' => __('Engagements'), 'url' => route('engagements.index')],
                ['label' => __('Tableau de bord')],
            ]"
        >
            <x-slot:actions>
                @can('engagement.export')
                    <a href="{{ route('engagements.documents.registre.pdf') }}"><x-secondary-button type="button">{{ __('Registre PDF') }}</x-secondary-button></a>
                    <a href="{{ route('engagements.documents.registre.excel') }}"><x-secondary-button type="button">{{ __('Registre Excel') }}</x-secondary-button></a>
                    <a href="{{ route('engagements.documents.credits.pdf') }}" class="btn btn-ghost btn-sm">{{ __('État crédits PDF') }}</a>
                @endcan
            </x-slot:actions>
        </x-chaine.dossier-header>
    </x-slot>

    <x-ds.page-shell class="space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
            <x-ds.kpi-card :label="__('Total')" :value="(string) $kpis['total']" tone="primary" />
            <x-ds.kpi-card :label="__('Approuvés')" :value="(string) $kpis['approuves']" tone="success" />
            <x-ds.kpi-card :label="__('En validation')" :value="(string) $kpis['en_validation']" tone="warning" />
            <x-ds.kpi-card :label="__('Brouillons')" :value="(string) $kpis['brouillons']" />
            <x-ds.kpi-card :label="__('Rejetés')" :value="(string) $kpis['rejetes']" tone="danger" />
            <x-ds.kpi-card :label="__('Annulés')" :value="(string) $kpis['annules']" tone="danger" />
            <x-ds.kpi-card :label="__('Crédits réservés')" tone="primary">
                <x-slot:value><x-ds.money :value="$kpis['credits_reserves']" :decimals="2" :show-currency="false" /></x-slot:value>
            </x-ds.kpi-card>
        </div>

        <div class="grid lg:grid-cols-2 gap-4">
            <x-ds.card :title="__('Par type d\'engagement')" :padding="true">
                @forelse ($parType as $row)
                    <div class="flex items-center justify-between py-1.5 text-sm border-b border-gray-50 last:border-0">
                        <span class="text-gray-700">{{ $row->libelle }} <span class="text-gray-400">({{ $row->total }})</span></span>
                        <span class="font-semibold tabular-nums"><x-ds.money :value="$row->montant" :decimals="2" :show-currency="false" /></span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-4 text-center">—</p>
                @endforelse
            </x-ds.card>
            <x-ds.card :title="__('Par département')" :padding="true">
                @forelse ($parDepartement as $row)
                    <div class="flex items-center justify-between py-1.5 text-sm border-b border-gray-50 last:border-0">
                        <span class="text-gray-700">{{ $row->libelle }} <span class="text-gray-400">({{ $row->total }})</span></span>
                        <span class="font-semibold tabular-nums"><x-ds.money :value="$row->montant" :decimals="2" :show-currency="false" /></span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-4 text-center">—</p>
                @endforelse
            </x-ds.card>
            <x-ds.card :title="__('Par source de financement')" :padding="true">
                @forelse ($parSource as $row)
                    <div class="flex items-center justify-between py-1.5 text-sm border-b border-gray-50 last:border-0">
                        <span class="text-gray-700">{{ $row->libelle }}</span>
                        <span class="font-semibold tabular-nums"><x-ds.money :value="$row->montant" :decimals="2" :show-currency="false" /></span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-4 text-center">—</p>
                @endforelse
            </x-ds.card>
            <x-ds.card :title="__('Par ligne budgétaire (top)')" :padding="true">
                @forelse ($parLigne as $row)
                    <div class="flex items-center justify-between py-1.5 text-sm border-b border-gray-50 last:border-0">
                        <span class="text-gray-700 font-mono text-xs">{{ $row->code }}</span>
                        <span class="font-semibold tabular-nums"><x-ds.money :value="$row->montant" :decimals="2" :show-currency="false" /></span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-4 text-center">—</p>
                @endforelse
            </x-ds.card>
        </div>

        <x-ds.card :title="__('Alertes budgétaires (taux d\'engagement ≥ 90 %)')" :padding="false">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>{{ __('Ligne') }}</th><th>{{ __('Exercice') }}</th><th class="text-right">{{ __('Engagé') }}</th><th class="text-right">{{ __('Plafond') }}</th><th class="text-right">{{ __('Taux') }}</th><th class="text-right">{{ __('Disponible') }}</th></tr></thead>
                    <tbody>
                        @forelse ($alertes as $a)
                            <tr>
                                <td class="font-mono text-xs">{{ $a['code'] }} — {{ \Illuminate\Support\Str::limit($a['libelle'], 30) }}</td>
                                <td>{{ $a['annee'] ?? '—' }}</td>
                                <td class="text-right tabular-nums"><x-ds.money :value="$a['engage']" :decimals="2" :show-currency="false" /></td>
                                <td class="text-right tabular-nums"><x-ds.money :value="$a['plafond']" :decimals="2" :show-currency="false" /></td>
                                <td class="text-right tabular-nums font-semibold {{ $a['taux'] >= 100 ? 'text-red-600' : 'text-amber-600' }}">{{ number_format($a['taux'], 1, ',', ' ') }} %</td>
                                <td class="text-right tabular-nums"><x-ds.money :value="$a['disponible']" :decimals="2" :show-currency="false" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-0"><x-ds.empty-state :title="__('Aucune alerte budgétaire.')" class="border-0 shadow-none" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
