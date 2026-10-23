@php $fmt = fn ($v) => number_format((float) $v, 2, ',', ' '); @endphp
<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="$ordonnancement->numero ?? '#'.$ordonnancement->id"
            :description="\Illuminate\Support\Str::limit($ordonnancement->objet ?? $ordonnancement->liquidation?->engagement?->objet, 120)"
            :statut="$ordonnancement->statut"
            statut-module="ordonnancement"
            :breadcrumb="[
                ['label' => __('Ordonnancements'), 'url' => route('ordonnancements.index')],
                ['label' => $ordonnancement->numero ?? '#'.$ordonnancement->id],
            ]"
        >
            <x-slot:actions>
                @can('modifier', $ordonnancement)
                    <a href="{{ route('ordonnancements.edit', $ordonnancement) }}" class="btn btn-secondary btn-sm">{{ __('Modifier') }}</a>
                @endcan
                @can('soumettre', $ordonnancement)
                    <form method="POST" action="{{ route('ordonnancements.soumettre', $ordonnancement) }}" onsubmit="return confirm(@json(__('Soumettre au circuit ?')))">@csrf
                        <button class="btn btn-primary btn-sm">{{ __('Soumettre') }}</button>
                    </form>
                @endcan
                @can('mandater', $ordonnancement)
                    <form method="POST" action="{{ route('ordonnancements.mandater', $ordonnancement) }}" onsubmit="return confirm(@json(__('Générer le mandat ? Le crédit budgétaire sera impacté.')))">@csrf
                        <button class="btn btn-primary btn-sm">{{ __('Générer le mandat') }}</button>
                    </form>
                @endcan
                @can('transmettre', $ordonnancement)
                    <form method="POST" action="{{ route('ordonnancements.transmettre-paiement', $ordonnancement) }}" onsubmit="return confirm(@json(__('Transmettre au paiement ?')))">@csrf
                        <button class="btn btn-primary btn-sm">{{ __('Transmettre au paiement') }}</button>
                    </form>
                @endcan
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" class="btn btn-ghost btn-sm">{{ __('Documents') }} ▾</button>
                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-1 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-40 py-1 text-sm">
                        <a href="{{ route('ordonnancements.fiche', $ordonnancement) }}" class="block px-4 py-2 hover:bg-gray-50">{{ __('Fiche d’ordonnancement (PDF)') }}</a>
                        <a href="{{ route('ordonnancements.bordereau', $ordonnancement) }}" class="block px-4 py-2 hover:bg-gray-50">{{ __('Bordereau (PDF)') }}</a>
                        @if ($ordonnancement->mandat_numero)
                            <a href="{{ route('ordonnancements.mandat', $ordonnancement) }}" class="block px-4 py-2 hover:bg-gray-50">{{ __('Mandat de paiement (PDF)') }}</a>
                            <a href="{{ route('ordonnancements.ordre-paiement', $ordonnancement) }}" class="block px-4 py-2 hover:bg-gray-50">{{ __('Ordre de paiement (PDF)') }}</a>
                        @endif
                    </div>
                </div>
            </x-slot:actions>
        </x-chaine.dossier-header>
    </x-slot>

    <x-ds.page-shell class="space-y-4">
        @if ($ordonnancement->liquidation?->engagement)
            @include('chaine._workflow-panel', ['engagement' => $ordonnancement->liquidation->engagement])
        @endif
        @if ($errors->any())
            <x-ds.alert type="danger">
                <ul class="list-disc list-inside text-sm">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </x-ds.alert>
        @endif

        <x-chaine.financial-summary :title="__('Synthèse financière')">
            <x-chaine.financial-metric :label="__('Liquidation')">
                @if ($ordonnancement->liquidation)
                    <a href="{{ route('liquidations.show', $ordonnancement->liquidation) }}" class="font-mono text-ceeac-bright hover:underline">{{ $ordonnancement->liquidation->numero }}</a>
                @else — @endif
            </x-chaine.financial-metric>
            <x-chaine.financial-metric :label="__('Type')" :value="$ordonnancement->type?->libelle ?? '—'" />
            <x-chaine.financial-metric :label="__('Montant ordonnancé')" tone="primary">
                <x-ds.money :value="$ordonnancement->montant_ordonnance" :decimals="2" />
            </x-chaine.financial-metric>
            <x-chaine.financial-metric :label="__('Taux d\'ordonnancement')" :value="$fmt($ordonnancement->taux_ordonnancement).' %'" />
        </x-chaine.financial-summary>

        <x-ds.card>
            <x-chaine.tabs :tabs="[
                'general' => __('Informations'),
                'liquidation' => __('Liquidation'),
                'controle' => __('Contrôle financier'),
                'ordonnancement' => __('Ordonnancement'),
                'imputations' => __('Imputations'),
                'ged' => __('GED'),
                'workflow' => __('workflow.title'),
                'historique' => __('Historique'),
            ]" active="general">
                <x-chaine.tab-panel name="general" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-400">{{ __('Numéro') }} :</span> <span class="font-mono">{{ $ordonnancement->numero }}</span></div>
                    <div><span class="text-gray-400">{{ __('Mode') }} :</span> {{ $ordonnancement->mode_creation?->libelle() ?? '—' }}</div>
                    <div><span class="text-gray-400">{{ __('Date') }} :</span> {{ optional($ordonnancement->date_ordonnancement)->format('d/m/Y') }}</div>
                    <div><span class="text-gray-400">{{ __('Devise') }} :</span> {{ $ordonnancement->devise }}</div>
                    <div><span class="text-gray-400">{{ __('Fournisseur') }} :</span> {{ $ordonnancement->fournisseur?->raison_sociale ?? '—' }}</div>
                    <div><span class="text-gray-400">{{ __('Créé par') }} :</span> {{ $ordonnancement->createur?->name }}</div>
                    <div class="md:col-span-2"><span class="text-gray-400">{{ __('Objet') }} :</span> {{ $ordonnancement->objet ?? '—' }}</div>
                    @if ($ordonnancement->mandat_numero)
                        <div class="md:col-span-2 mt-2 p-3 bg-green-50 rounded-lg">
                            <span class="text-gray-500">{{ __('Mandat') }} :</span> <span class="font-mono">{{ $ordonnancement->mandat_numero }}</span>
                            @if ($ordonnancement->visa_numero) — <span class="text-gray-500">{{ __('Visa') }} :</span> <span class="font-mono">{{ $ordonnancement->visa_numero }}</span>@endif
                            <span class="text-gray-400 text-xs block">{{ __('Empreinte') }} : <span class="font-mono">{{ \Illuminate\Support\Str::limit($ordonnancement->visa_hash, 24) }}</span></span>
                        </div>
                    @endif
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="liquidation" class="space-y-2 text-sm">
                    @php($liq = $ordonnancement->liquidation)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div><span class="text-gray-400">{{ __('Liquidation') }} :</span> <a href="{{ route('liquidations.show', $liq) }}" class="text-ceeac-bright hover:underline font-mono">{{ $liq?->numero }}</a></div>
                        <div><span class="text-gray-400">{{ __('Montant liquidé') }} :</span> <x-ds.money :value="$liq?->montant_net" :decimals="2" :show-currency="false" /></div>
                        <div><span class="text-gray-400">{{ __('Engagement') }} :</span> <span class="font-mono">{{ $liq?->engagement?->numero }}</span></div>
                        <div><span class="text-gray-400">{{ __('Exercice') }} :</span> {{ $liq?->engagement?->exerciceBudgetaire?->annee }}</div>
                    </div>
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="controle">
                    @include('ordonnancements.partials.controle-financier', ['controles' => $controles])
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="ordonnancement">
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead><tr><th>{{ __('Désignation') }}</th><th>{{ __('Ligne budg.') }}</th><th class="text-right">{{ __('Liquidé') }}</th><th class="text-right">{{ __('Ordonnancé') }}</th><th>{{ __('Mode') }}</th></tr></thead>
                            <tbody>
                                @foreach ($ordonnancement->lignes as $l)
                                    <tr>
                                        <td>{{ $l->designation }}</td>
                                        <td class="font-mono text-xs">{{ $l->ligneBudgetaire?->code }}</td>
                                        <td class="text-right"><x-ds.money :value="$l->montant_liquide" :decimals="2" :show-currency="false" /></td>
                                        <td class="text-right font-semibold"><x-ds.money :value="$l->montant_ordonnance" :decimals="2" :show-currency="false" /></td>
                                        <td class="text-xs">{{ $l->mode_paiement ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="imputations">
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead><tr><th>{{ __('Ligne budgétaire') }}</th><th>{{ __('Source') }}</th><th class="text-right">{{ __('Ordonnancé') }}</th></tr></thead>
                            <tbody>
                                @foreach ($ordonnancement->lignes as $l)
                                    <tr>
                                        <td class="font-mono text-xs">{{ $l->ligneBudgetaire?->code }} — {{ \Illuminate\Support\Str::limit($l->ligneBudgetaire?->libelle, 35) }}</td>
                                        <td class="text-xs">{{ $l->sourceFinancement?->libelle ?? '—' }}</td>
                                        <td class="text-right"><x-ds.money :value="$l->montant_ordonnance" :decimals="2" :show-currency="false" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="ged" class="space-y-4">
                    @include('ordonnancements.partials.ged', ['ordonnancement' => $ordonnancement])
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="workflow" class="space-y-4">
                    @include('ordonnancements.partials.workflow', ['ordonnancement' => $ordonnancement, 'pendingStep' => $pendingStep, 'canValidatePending' => $canValidatePending])
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
                @if ($ordonnancement->mandats->isNotEmpty()){{ $ordonnancement->mandats->count() }} {{ __('mandat(s)') }}, {{ $ordonnancement->ordresPaiement->count() }} {{ __('ordre(s) de paiement') }} @endif
            </div>
            @can('annuler', $ordonnancement)
                <form method="POST" action="{{ route('ordonnancements.destroy', $ordonnancement) }}" onsubmit="return confirm(@json(__('Annuler définitivement cet ordonnancement ?')))" class="flex items-end gap-2">
                    @csrf @method('DELETE')
                    <input type="text" name="motif_annulation" placeholder="{{ __('Motif d’annulation') }}" class="form-input text-sm" maxlength="1000">
                    <button class="btn btn-danger">{{ __('Annuler l’ordonnancement') }}</button>
                </form>
            @endcan
        </div>
    </x-ds.page-shell>
</x-app-layout>
