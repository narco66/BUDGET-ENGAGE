@php
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
    $sf = $liquidation->serviceFait;
@endphp
<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="$liquidation->numero ?? '#'.$liquidation->id"
            :description="\Illuminate\Support\Str::limit($liquidation->objet ?? $liquidation->engagement?->objet, 120)"
            :statut="$liquidation->statut"
            statut-module="liquidation"
            :breadcrumb="[
                ['label' => __('Liquidations'), 'url' => route('liquidations.index')],
                ['label' => $liquidation->numero ?? '#'.$liquidation->id],
            ]"
        >
            <x-slot:actions>
                @can('modifier', $liquidation)
                    <a href="{{ route('liquidations.edit', $liquidation) }}" class="btn btn-secondary btn-sm">{{ __('Modifier') }}</a>
                @endcan
                @can('soumettre', $liquidation)
                    <form method="POST" action="{{ route('liquidations.soumettre', $liquidation) }}" onsubmit="return confirm(@json(__('Soumettre au circuit de validation ?')))">@csrf
                        <button class="btn btn-primary btn-sm">{{ __('Soumettre') }}</button>
                    </form>
                @endcan
                @can('certifier', $liquidation)
                    <form method="POST" action="{{ route('liquidations.certifier', $liquidation) }}" onsubmit="return confirm(@json(__('Certifier ? Le crédit budgétaire sera impacté.')))">@csrf
                        <button class="btn btn-primary btn-sm">{{ __('Certifier') }}</button>
                    </form>
                @endcan
                @can('transmettre', $liquidation)
                    <form method="POST" action="{{ route('liquidations.transmettre-ordonnancement', $liquidation) }}" onsubmit="return confirm(@json(__('Transmettre à l’ordonnancement ?')))">@csrf
                        <button class="btn btn-primary btn-sm">{{ __('Transmettre à l’ordonnancement') }}</button>
                    </form>
                @endcan
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" class="btn btn-ghost btn-sm">{{ __('Documents') }} ▾</button>
                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-1 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-40 py-1 text-sm">
                        <a href="{{ route('liquidations.fiche', $liquidation) }}" class="block px-4 py-2 hover:bg-gray-50">{{ __('Fiche de liquidation (PDF)') }}</a>
                        <a href="{{ route('liquidations.bordereau', $liquidation) }}" class="block px-4 py-2 hover:bg-gray-50">{{ __('Bordereau (PDF)') }}</a>
                        @if ($liquidation->certificat_numero)
                            <a href="{{ route('liquidations.certificat', $liquidation) }}" class="block px-4 py-2 hover:bg-gray-50">{{ __('Certificat de liquidation (PDF)') }}</a>
                        @endif
                    </div>
                </div>
            </x-slot:actions>
        </x-chaine.dossier-header>
    </x-slot>

    <x-ds.page-shell class="space-y-4">
        @if ($liquidation->engagement)
            @include('chaine._workflow-panel', ['engagement' => $liquidation->engagement])
        @endif
        @if ($errors->any())
            <x-ds.alert type="danger">
                <ul class="list-disc list-inside text-sm">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </x-ds.alert>
        @endif

        <x-chaine.financial-summary :title="__('Synthèse financière')">
            <x-chaine.financial-metric :label="__('Engagement')">
                <a href="{{ route('engagements.show', $liquidation->engagement) }}" class="font-mono text-ceeac-bright hover:underline">{{ $liquidation->engagement?->numero ?? '—' }}</a>
            </x-chaine.financial-metric>
            <x-chaine.financial-metric :label="__('Type')" :value="$liquidation->type?->libelle ?? '—'" />
            <x-chaine.financial-metric :label="__('Montant net')" tone="primary">
                <x-ds.money :value="$liquidation->montant_net" :decimals="2" />
            </x-chaine.financial-metric>
            <x-chaine.financial-metric :label="__('Taux de liquidation')" :value="$fmt($liquidation->taux_liquidation).' %'" />
        </x-chaine.financial-summary>

        <x-ds.card>
            <x-chaine.tabs :tabs="[
                'general' => __('Informations'),
                'engagement' => __('Engagement'),
                'service_fait' => __('Service fait'),
                'prestations' => __('Prestations'),
                'calculs' => __('Calculs'),
                'imputations' => __('Imputations'),
                'ged' => __('GED'),
                'workflow' => __('workflow.title'),
                'historique' => __('Historique'),
            ]" active="general">
                <x-chaine.tab-panel name="general" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-400">{{ __('Numéro') }} :</span> <span class="font-mono">{{ $liquidation->numero }}</span></div>
                    <div><span class="text-gray-400">{{ __('Mode') }} :</span> {{ $liquidation->mode_creation?->libelle() ?? '—' }}</div>
                    <div><span class="text-gray-400">{{ __('Date') }} :</span> {{ optional($liquidation->date_liquidation)->format('d/m/Y') }}</div>
                    <div><span class="text-gray-400">{{ __('Devise') }} :</span> {{ $liquidation->devise }}</div>
                    <div><span class="text-gray-400">{{ __('Fournisseur') }} :</span> {{ $liquidation->fournisseur?->raison_sociale ?? '—' }}</div>
                    <div><span class="text-gray-400">{{ __('Créée par') }} :</span> {{ $liquidation->createur?->name }}</div>
                    <div class="md:col-span-2"><span class="text-gray-400">{{ __('Objet') }} :</span> {{ $liquidation->objet ?? '—' }}</div>
                    @if ($liquidation->justification)
                        <div class="md:col-span-2"><span class="text-gray-400">{{ __('Justification') }} :</span> {{ $liquidation->justification }}</div>
                    @endif
                    @if ($liquidation->certificat_numero)
                        <div class="md:col-span-2 mt-2 p-3 bg-green-50 rounded-lg">
                            <span class="text-gray-500">{{ __('Certificat') }} :</span> <span class="font-mono">{{ $liquidation->certificat_numero }}</span>
                            <span class="text-gray-400 text-xs block">{{ __('Empreinte') }} : <span class="font-mono">{{ \Illuminate\Support\Str::limit($liquidation->certificat_hash, 24) }}</span></span>
                        </div>
                    @endif
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="engagement" class="space-y-2 text-sm">
                    @php($eng = $liquidation->engagement)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div><span class="text-gray-400">{{ __('Numéro') }} :</span> <a href="{{ route('engagements.show', $eng) }}" class="text-ceeac-primary hover:underline font-mono">{{ $eng?->numero }}</a></div>
                        <div><span class="text-gray-400">{{ __('Montant engagé') }} :</span> <x-ds.money :value="$eng?->montant" :decimals="2" :show-currency="false" /></div>
                        <div><span class="text-gray-400">{{ __('Exercice') }} :</span> {{ $eng?->exerciceBudgetaire?->annee }}</div>
                        <div><span class="text-gray-400">{{ __('Statut') }} :</span> @if($eng)<x-chaine.status-badge module="engagement" :statut="$eng->statut" />@else — @endif</div>
                    </div>
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="service_fait" class="space-y-4">
                    @include('liquidations.partials.service-fait', ['liquidation' => $liquidation, 'sf' => $sf])
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="prestations">
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead><tr>
                                <th>{{ __('Désignation') }}</th><th>{{ __('Ligne budg.') }}</th>
                                <th class="text-right">{{ __('Brut') }}</th><th class="text-right">{{ __('Déductions') }}</th>
                                <th class="text-right">{{ __('Net') }}</th>
                            </tr></thead>
                            <tbody>
                                @foreach ($liquidation->lignes as $l)
                                    @php($ded = (float) $l->montant_avances + $l->montant_acomptes + $l->montant_retenues + $l->montant_garanties + $l->montant_penalites)
                                    <tr>
                                        <td>{{ $l->designation }}</td>
                                        <td class="font-mono text-xs">{{ $l->ligneBudgetaire?->code }}</td>
                                        <td class="text-right tabular-nums"><x-ds.money :value="$l->montant_brut" :decimals="2" :show-currency="false" /></td>
                                        <td class="text-right tabular-nums text-red-600">-<x-ds.money :value="$ded" :decimals="2" :show-currency="false" /></td>
                                        <td class="text-right tabular-nums font-semibold"><x-ds.money :value="$l->montant_net" :decimals="2" :show-currency="false" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="calculs" class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                    @foreach ([
                        __('Montant brut') => $liquidation->montant_brut,
                        __('Avances') => $liquidation->total_avances,
                        __('Acomptes') => $liquidation->total_acomptes,
                        __('Retenues') => $liquidation->total_retenues,
                        __('Garanties') => $liquidation->total_garanties,
                        __('Pénalités') => $liquidation->total_penalites,
                        __('Révision prix') => $liquidation->montant_revision_prix,
                        __('Taxes') => $liquidation->total_taxes,
                    ] as $lib => $val)
                        <div class="flex justify-between border-b border-gray-100 py-1"><span class="text-gray-500">{{ $lib }}</span><span class="tabular-nums"><x-ds.money :value="$val" :decimals="2" :show-currency="false" /></span></div>
                    @endforeach
                    <div class="flex justify-between border-b border-gray-100 py-1 col-span-2 md:col-span-3 mt-1"><span class="font-semibold">{{ __('Montant net liquidé') }}</span><span class="font-bold text-ceeac-primary tabular-nums"><x-ds.money :value="$liquidation->montant_net" :decimals="2" :show-currency="false" /></span></div>
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="imputations">
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead><tr><th>{{ __('Ligne budgétaire') }}</th><th>{{ __('Source') }}</th><th class="text-right">{{ __('Net imputé') }}</th></tr></thead>
                            <tbody>
                                @foreach ($liquidation->lignes as $l)
                                    <tr>
                                        <td class="font-mono text-xs">{{ $l->ligneBudgetaire?->code }} — {{ \Illuminate\Support\Str::limit($l->ligneBudgetaire?->libelle, 35) }}</td>
                                        <td class="text-xs">{{ $l->sourceFinancement?->libelle ?? '—' }}</td>
                                        <td class="text-right tabular-nums"><x-ds.money :value="$l->montant_net" :decimals="2" :show-currency="false" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="ged" class="space-y-4">
                    @include('liquidations.partials.ged', ['liquidation' => $liquidation])
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="workflow" class="space-y-4">
                    @include('liquidations.partials.workflow', ['liquidation' => $liquidation, 'pendingStep' => $pendingStep, 'canValidatePending' => $canValidatePending])
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="historique">
                    <x-chaine.timeline :events="collect($timeline)->map(fn ($h) => [
                        'label' => $h->description,
                        'user' => $h->user?->name ?? __('Système'),
                        'date' => $h->created_at?->format('d/m/Y H:i'),
                    ])->all()" />
                </x-chaine.tab-panel>
            </x-chaine.tabs>
        </x-ds.card>

        <div class="flex items-center justify-between">
            <div class="text-xs text-gray-400">
                @if ($liquidation->versions->isNotEmpty()){{ $liquidation->versions->count() }} {{ __('version(s) archivée(s)') }}@endif
            </div>
            @can('annuler', $liquidation)
                <form method="POST" action="{{ route('liquidations.destroy', $liquidation) }}" onsubmit="return confirm(@json(__('Annuler définitivement cette liquidation ?')))" class="flex items-end gap-2">
                    @csrf @method('DELETE')
                    <input type="text" name="motif_annulation" placeholder="{{ __('Motif d’annulation') }}" class="form-input text-sm" maxlength="1000">
                    <button class="btn btn-danger">{{ __('Annuler la liquidation') }}</button>
                </form>
            @endcan
        </div>
    </x-ds.page-shell>
</x-app-layout>
