<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Suivi de la chaîne de la dépense')"
            :description="__('Liquidations, ordonnancements et paiements — vue consolidée.')"
            :breadcrumb="[
                ['label' => __('Accueil'), 'url' => route('dashboard')],
                ['label' => __('Chaîne de la dépense')],
            ]"
        />
    </x-slot>

    <x-ds.page-shell class="space-y-6">
        <div class="grid md:grid-cols-3 lg:grid-cols-6 gap-3">
            <x-ds.kpi-card :label="__('Liquidations')" :value="(string) $kpis['liquidations']" />
            <x-ds.kpi-card :label="__('Ordonnancements')" :value="(string) $kpis['ordonnancements']" />
            <x-ds.kpi-card :label="__('Paiements')" :value="(string) $kpis['paiements']" />
            <x-ds.kpi-card :label="__('Montant liquidé')" tone="primary">
                <x-slot:value><x-ds.money :value="$kpis['montant_liquide']" :decimals="2" :show-currency="false" /></x-slot:value>
            </x-ds.kpi-card>
            <x-ds.kpi-card :label="__('Montant ordonnancé')">
                <x-slot:value><x-ds.money :value="$kpis['montant_ordonnance']" :decimals="2" :show-currency="false" /></x-slot:value>
            </x-ds.kpi-card>
            <x-ds.kpi-card :label="__('Montant payé')" tone="success">
                <x-slot:value><x-ds.money :value="$kpis['montant_paye']" :decimals="2" :show-currency="false" /></x-slot:value>
            </x-ds.kpi-card>
        </div>

        <x-ds.card :padding="false">
            <x-slot:header>
                <div class="flex flex-wrap items-center justify-between gap-3 w-full">
                    <div class="flex flex-wrap gap-2">
                        @foreach (['liquidations' => __('Liquidations'), 'ordonnancements' => __('Ordonnancements'), 'paiements' => __('Paiements')] as $key => $label)
                            <a href="{{ route('execution.dashboard', ['tab' => $key, 'q' => $search]) }}"
                               class="btn btn-sm {{ $tab === $key ? 'btn-primary' : 'btn-ghost' }}">{{ $label }}</a>
                        @endforeach
                    </div>
                    <form method="GET" class="flex gap-2">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('N° engagement…') }}" class="form-input form-input-sm">
                        <button class="btn btn-secondary btn-sm">{{ __('Filtrer') }}</button>
                    </form>
                </div>
            </x-slot:header>

            <div class="overflow-x-auto">
                @if ($tab === 'liquidations')
                    <table class="data-table">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Engagement') }}</th><th>{{ __('Ligne') }}</th><th class="text-right">{{ __('Montant') }}</th><th>{{ __('Réf.') }}</th><th class="text-right">{{ __('Actions') }}</th></tr></thead>
                        <tbody>
                            @forelse ($liquidations as $liq)
                                <tr>
                                    <td>{{ $liq->date_liquidation?->format('d/m/Y') }}</td>
                                    <td class="font-mono"><a href="{{ route('engagements.show', $liq->engagement) }}" class="text-ceeac-bright hover:underline">{{ $liq->engagement?->numero }}</a></td>
                                    <td>{{ $liq->engagementImputation?->ligneBudgetaire?->code ?? '—' }}</td>
                                    <td class="text-right"><x-ds.money :value="$liq->montant_liquide" :decimals="2" :show-currency="false" /></td>
                                    <td>{{ $liq->reference ?? '—' }}</td>
                                    <td class="text-right"><a href="{{ route('execution.liquidations.show', $liq) }}" class="text-ceeac-bright hover:underline">{{ __('Voir') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-0"><x-ds.empty-state :title="__('Aucune liquidation.')" class="border-0 shadow-none" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @elseif ($tab === 'ordonnancements')
                    <table class="data-table">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Engagement') }}</th><th>{{ __('Ligne') }}</th><th class="text-right">{{ __('Montant') }}</th><th>{{ __('Réf.') }}</th><th class="text-right">{{ __('Actions') }}</th></tr></thead>
                        <tbody>
                            @forelse ($ordonnancements as $ord)
                                <tr>
                                    <td>{{ $ord->date_ordonnancement?->format('d/m/Y') }}</td>
                                    <td class="font-mono"><a href="{{ route('engagements.show', $ord->liquidation?->engagement) }}" class="text-ceeac-bright hover:underline">{{ $ord->liquidation?->engagement?->numero }}</a></td>
                                    <td>{{ $ord->engagementImputation?->ligneBudgetaire?->code ?? '—' }}</td>
                                    <td class="text-right"><x-ds.money :value="$ord->montant_ordonnance" :decimals="2" :show-currency="false" /></td>
                                    <td>{{ $ord->reference ?? '—' }}</td>
                                    <td class="text-right"><a href="{{ route('execution.ordonnancements.show', $ord) }}" class="text-ceeac-bright hover:underline">{{ __('Voir') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-0"><x-ds.empty-state :title="__('Aucun ordonnancement.')" class="border-0 shadow-none" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <table class="data-table">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Engagement') }}</th><th>{{ __('Ligne') }}</th><th class="text-right">{{ __('Montant') }}</th><th>{{ __('Réf.') }}</th><th class="text-right">{{ __('Actions') }}</th></tr></thead>
                        <tbody>
                            @forelse ($paiements as $pay)
                                <tr>
                                    <td>{{ $pay->date_paiement?->format('d/m/Y') }}</td>
                                    <td class="font-mono"><a href="{{ route('engagements.show', $pay->ordonnancement?->liquidation?->engagement) }}" class="text-ceeac-bright hover:underline">{{ $pay->ordonnancement?->liquidation?->engagement?->numero }}</a></td>
                                    <td>{{ $pay->engagementImputation?->ligneBudgetaire?->code ?? '—' }}</td>
                                    <td class="text-right"><x-ds.money :value="$pay->montant_paye" :decimals="2" :show-currency="false" /></td>
                                    <td>{{ $pay->reference ?? '—' }}</td>
                                    <td class="text-right"><a href="{{ route('execution.paiements.show', $pay) }}" class="text-ceeac-bright hover:underline">{{ __('Voir') }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-0"><x-ds.empty-state :title="__('Aucun paiement.')" class="border-0 shadow-none" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
