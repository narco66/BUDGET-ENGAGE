@php $fmt = fn ($v) => number_format((float) $v, 2, ',', ' '); @endphp
<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Rapprochement Besoin → Engagement → Liquidation')"
            :description="__('Suivi des montants de la demande initiale à la liquidation.')"
            :breadcrumb="[
                ['label' => __('Expression des besoins'), 'url' => route('expression-besoins.dashboard')],
                ['label' => __('Rapprochement')],
            ]"
        >
            <x-slot:actions>
                @can('export', App\Models\ExpressionBesoin::class)
                    <a href="{{ route('expression-besoins.rapprochement.excel') }}"><x-secondary-button type="button">{{ __('Export Excel') }}</x-secondary-button></a>
                @endcan
            </x-slot:actions>
        </x-chaine.dossier-header>
    </x-slot>

    <x-ds.page-shell class="space-y-4">
        <x-ds.card :padding="false">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Besoin') }}</th>
                            <th>{{ __('Engagement') }}</th>
                            <th class="text-right">{{ __('Montant besoin') }}</th>
                            <th class="text-right">{{ __('Montant engagé') }}</th>
                            <th class="text-right">{{ __('Montant liquidé') }}</th>
                            <th class="text-right">{{ __('Écart (engagé − liquidé)') }}</th>
                            <th>{{ __('Statut engagement') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($besoins as $besoin)
                            @php($eng = $besoin->engagement)
                            @php($liquide = $eng ? $eng->liquidations->sum(fn ($l) => (float) $l->montant_liquide) : 0)
                            @php($ecart = ($eng ? (float) $eng->montant : 0) - $liquide)
                            <tr>
                                <td>
                                    <a href="{{ route('expression-besoins.besoins.show', $besoin) }}" class="font-mono text-xs text-ceeac-bright hover:underline">{{ $besoin->reference }}</a>
                                </td>
                                <td>
                                    @if ($eng)
                                        <a href="{{ route('engagements.show', $eng) }}" class="font-mono text-xs text-ceeac-bright hover:underline">{{ $eng->numero }}</a>
                                    @else — @endif
                                </td>
                                <td class="text-right tabular-nums"><x-ds.money :value="$besoin->montant_total" :decimals="2" :show-currency="false" /></td>
                                <td class="text-right tabular-nums">@if($eng)<x-ds.money :value="$eng->montant" :decimals="2" :show-currency="false" />@else — @endif</td>
                                <td class="text-right tabular-nums text-emerald-700"><x-ds.money :value="$liquide" :decimals="2" :show-currency="false" /></td>
                                <td class="text-right tabular-nums {{ $ecart > 0 ? 'text-amber-700' : 'text-emerald-700' }}"><x-ds.money :value="$ecart" :decimals="2" :show-currency="false" /></td>
                                <td>
                                    @if ($eng)
                                        <x-chaine.status-badge module="engagement" :statut="$eng->statut" />
                                    @else — @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-0"><x-ds.empty-state :title="__('Aucun besoin converti en engagement.')" class="border-0 shadow-none" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-ds.card>

        <div>{{ $besoins->links() }}</div>
    </x-ds.page-shell>
</x-app-layout>
