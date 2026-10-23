@php $fmt = fn ($v) => number_format((float) $v, 2, ',', ' '); @endphp
<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Consolidation budgétaire multi-exercices')"
            :description="__('Synthèse des montants demandés, validés et convertis par exercice.')"
            :breadcrumb="[
                ['label' => __('Expression des besoins'), 'url' => route('expression-besoins.dashboard')],
                ['label' => __('Consolidation')],
            ]"
        >
            <x-slot:actions>
                @can('export', App\Models\ExpressionBesoin::class)
                    <a href="{{ route('expression-besoins.consolidation.pdf') }}" class="btn btn-ghost btn-sm">{{ __('Export PDF') }}</a>
                    <a href="{{ route('expression-besoins.consolidation.excel') }}"><x-secondary-button type="button">{{ __('Export Excel') }}</x-secondary-button></a>
                @endcan
            </x-slot:actions>
        </x-chaine.dossier-header>
    </x-slot>

    <x-ds.page-shell>
        <x-ds.card :padding="false">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Exercice') }}</th>
                            <th class="text-center">{{ __('Nombre') }}</th>
                            <th class="text-right">{{ __('Montant demandé') }}</th>
                            <th class="text-right">{{ __('Montant validé') }}</th>
                            <th class="text-right">{{ __('Montant converti') }}</th>
                            <th class="text-right">{{ __('Dont PAP') }}</th>
                            <th class="text-right">{{ __('Dont Hors PAP') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lignes as $ligne)
                            <tr>
                                <td class="font-semibold">{{ $ligne->annee }}</td>
                                <td class="text-center tabular-nums">{{ $ligne->nb }}</td>
                                <td class="text-right tabular-nums"><x-ds.money :value="$ligne->demande" :decimals="2" :show-currency="false" /></td>
                                <td class="text-right tabular-nums text-emerald-700"><x-ds.money :value="$ligne->valide" :decimals="2" :show-currency="false" /></td>
                                <td class="text-right tabular-nums text-ceeac-primary"><x-ds.money :value="$ligne->converti" :decimals="2" :show-currency="false" /></td>
                                <td class="text-right tabular-nums"><x-ds.money :value="$ligne->pap" :decimals="2" :show-currency="false" /></td>
                                <td class="text-right tabular-nums"><x-ds.money :value="$ligne->hors_pap" :decimals="2" :show-currency="false" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-0"><x-ds.empty-state :title="__('Aucune donnée.')" class="border-0 shadow-none" /></td></tr>
                        @endforelse
                    </tbody>
                    @if ($lignes->isNotEmpty())
                        <tfoot>
                            <tr class="font-bold bg-gray-50">
                                <td>{{ __('Total') }}</td>
                                <td class="text-center tabular-nums">{{ $totaux['nb'] }}</td>
                                <td class="text-right tabular-nums"><x-ds.money :value="$totaux['demande']" :decimals="2" :show-currency="false" /></td>
                                <td class="text-right tabular-nums text-emerald-700"><x-ds.money :value="$totaux['valide']" :decimals="2" :show-currency="false" /></td>
                                <td class="text-right tabular-nums text-ceeac-primary"><x-ds.money :value="$totaux['converti']" :decimals="2" :show-currency="false" /></td>
                                <td class="text-right tabular-nums"><x-ds.money :value="$totaux['pap']" :decimals="2" :show-currency="false" /></td>
                                <td class="text-right tabular-nums"><x-ds.money :value="$totaux['hors_pap']" :decimals="2" :show-currency="false" /></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
