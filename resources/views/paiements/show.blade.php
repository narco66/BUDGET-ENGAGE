@php $fmt = fn ($v) => number_format((float) $v, 2, ',', ' '); @endphp
<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="$paiement->numero ?? '#'.$paiement->id"
            :description="\Illuminate\Support\Str::limit($paiement->objet ?? $paiement->ordonnancement?->liquidation?->engagement?->objet, 120)"
            :statut="$paiement->statut"
            statut-module="paiement"
            :breadcrumb="[
                ['label' => __('Paiements'), 'url' => route('paiements.index')],
                ['label' => $paiement->numero ?? '#'.$paiement->id],
            ]"
        >
            <x-slot:actions>
                @can('modifier', $paiement)
                    <a href="{{ route('paiements.edit', $paiement) }}" class="btn btn-secondary btn-sm">{{ __('Modifier') }}</a>
                @endcan
                @can('programmer', $paiement)
                    <form method="POST" action="{{ route('paiements.programmer', $paiement) }}" onsubmit="return confirm(@json(__('Programmer et soumettre au circuit ?')))">@csrf
                        <button class="btn btn-primary btn-sm">{{ __('Programmer') }}</button>
                    </form>
                @endcan
                @can('transmettre', $paiement)
                    <form method="POST" action="{{ route('paiements.transmettre-banque', $paiement) }}" onsubmit="return confirm(@json(__('Transmettre à la banque ?')))">@csrf
                        <button class="btn btn-primary btn-sm">{{ __('Transmettre à la banque') }}</button>
                    </form>
                @endcan
                @can('executer', $paiement)
                    <form method="POST" action="{{ route('paiements.executer', $paiement) }}" onsubmit="return confirm(@json(__('Confirmer l’exécution bancaire ?')))">@csrf
                        <button class="btn btn-primary btn-sm">{{ __('Exécuter') }}</button>
                    </form>
                @endcan
                @can('rapprocher', $paiement)
                    <form method="POST" action="{{ route('paiements.rapprocher', $paiement) }}">@csrf
                        <button class="btn btn-secondary btn-sm">{{ __('Rapprocher') }}</button>
                    </form>
                @endcan
                @can('cloturer', $paiement)
                    <form method="POST" action="{{ route('paiements.cloturer', $paiement) }}">@csrf
                        <button class="btn btn-secondary btn-sm">{{ __('Clôturer') }}</button>
                    </form>
                @endcan
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click="open = !open" class="btn btn-ghost btn-sm">{{ __('Documents') }} ▾</button>
                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-1 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-40 py-1 text-sm">
                        <a href="{{ route('paiements.ordre-virement', $paiement) }}" class="block px-4 py-2 hover:bg-gray-50">{{ __('Ordre de virement (PDF)') }}</a>
                        <a href="{{ route('paiements.bordereau', $paiement) }}" class="block px-4 py-2 hover:bg-gray-50">{{ __('Bordereau bancaire (PDF)') }}</a>
                        <a href="{{ route('paiements.avis', $paiement) }}" class="block px-4 py-2 hover:bg-gray-50">{{ __('Avis de paiement (PDF)') }}</a>
                        @if ($paiement->preuve_numero)
                            <a href="{{ route('paiements.recu', $paiement) }}" class="block px-4 py-2 hover:bg-gray-50">{{ __('Reçu (PDF)') }}</a>
                            <a href="{{ route('paiements.quittance', $paiement) }}" class="block px-4 py-2 hover:bg-gray-50">{{ __('Quittance (PDF)') }}</a>
                        @endif
                    </div>
                </div>
            </x-slot:actions>
        </x-chaine.dossier-header>
    </x-slot>

    <x-ds.page-shell class="space-y-4">
        @if ($paiement->ordonnancement?->liquidation?->engagement)
            @include('chaine._workflow-panel', ['engagement' => $paiement->ordonnancement->liquidation->engagement])
        @endif
        @if ($errors->any())
            <x-ds.alert type="danger">
                <ul class="list-disc list-inside text-sm">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </x-ds.alert>
        @endif

        <x-chaine.financial-summary :title="__('Synthèse financière')">
            <x-chaine.financial-metric :label="__('Ordonnancement')">
                @if ($paiement->ordonnancement)
                    <a href="{{ route('ordonnancements.show', $paiement->ordonnancement) }}" class="font-mono text-ceeac-bright hover:underline">{{ $paiement->ordonnancement->numero }}</a>
                @else — @endif
            </x-chaine.financial-metric>
            <x-chaine.financial-metric :label="__('Banque')" :value="$paiement->banque?->code ?? '—'" />
            <x-chaine.financial-metric :label="__('Montant payé')" tone="primary">
                <x-ds.money :value="$paiement->montant_paye" :decimals="2" />
            </x-chaine.financial-metric>
            <x-chaine.financial-metric :label="__('Taux de paiement')" :value="$fmt($paiement->taux_paiement).' %'" />
        </x-chaine.financial-summary>

        <x-ds.card>
            <x-chaine.tabs :tabs="[
                'general' => __('Informations'),
                'ordonnancement' => __('Ordonnancement'),
                'tresorerie' => __('Trésorerie'),
                'paiement' => __('Paiement'),
                'ecritures' => __('Écritures'),
                'ged' => __('GED'),
                'workflow' => __('workflow.title'),
                'rejets' => __('Rejets'),
                'historique' => __('Historique'),
            ]" active="general">
                <x-chaine.tab-panel name="general" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-400">{{ __('Numéro') }} :</span> <span class="font-mono">{{ $paiement->numero }}</span></div>
                    <div><span class="text-gray-400">{{ __('Mode') }} :</span> {{ $paiement->mode_creation?->libelle() ?? '—' }}</div>
                    <div><span class="text-gray-400">{{ __('Type') }} :</span> {{ $paiement->type?->libelle ?? '—' }}</div>
                    <div><span class="text-gray-400">{{ __('Mode de paiement') }} :</span> {{ $paiement->mode_paiement ?? '—' }}</div>
                    <div><span class="text-gray-400">{{ __('Date') }} :</span> {{ optional($paiement->date_paiement)->format('d/m/Y') }}</div>
                    <div><span class="text-gray-400">{{ __('Fournisseur') }} :</span> {{ $paiement->fournisseur?->raison_sociale ?? '—' }}</div>
                    <div class="md:col-span-2"><span class="text-gray-400">{{ __('Objet') }} :</span> {{ $paiement->objet ?? '—' }}</div>
                    @if ($paiement->preuve_numero)
                        <div class="md:col-span-2 mt-2 p-3 bg-green-50 rounded-lg">
                            <span class="text-gray-500">{{ __('Preuve') }} :</span> <span class="font-mono">{{ $paiement->preuve_numero }}</span>
                            @if ($paiement->quittance_numero) — <span class="text-gray-500">{{ __('Quittance') }} :</span> <span class="font-mono">{{ $paiement->quittance_numero }}</span> @endif
                            <span class="text-gray-400 text-xs block">{{ __('Empreinte') }} : <span class="font-mono">{{ \Illuminate\Support\Str::limit($paiement->preuve_hash, 24) }}</span></span>
                        </div>
                    @endif
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="ordonnancement" class="space-y-2 text-sm">
                    @php($ord = $paiement->ordonnancement)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div><span class="text-gray-400">{{ __('Ordonnancement') }} :</span> <a href="{{ route('ordonnancements.show', $ord) }}" class="text-ceeac-bright hover:underline font-mono">{{ $ord?->numero }}</a></div>
                        <div><span class="text-gray-400">{{ __('Montant ordonnancé') }} :</span> <x-ds.money :value="$ord?->montant_ordonnance" :decimals="2" :show-currency="false" /></div>
                        <div><span class="text-gray-400">{{ __('Liquidation') }} :</span> <span class="font-mono">{{ $ord?->liquidation?->numero }}</span></div>
                        <div><span class="text-gray-400">{{ __('Engagement') }} :</span> <span class="font-mono">{{ $ord?->liquidation?->engagement?->numero }}</span></div>
                    </div>
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="tresorerie" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-400">{{ __('Compte bancaire') }} :</span> {{ $paiement->banque?->libelle ?? '—' }} ({{ $paiement->banque?->bic_swift ?? '—' }})</div>
                    <div><span class="text-gray-400">{{ __('Référence bancaire') }} :</span> <span class="font-mono">{{ $paiement->reference_bancaire ?? '—' }}</span></div>
                    @if ($solde !== null)
                        <div><span class="text-gray-400">{{ __('Solde disponible') }} :</span> <x-ds.money :value="$solde" :decimals="2" :show-currency="false" /></div>
                    @endif
                    @if ($plafondRestant !== null)
                        <div><span class="text-gray-400">{{ __('Plafond restant') }} :</span> <x-ds.money :value="$plafondRestant" :decimals="2" :show-currency="false" /></div>
                    @endif
                    <div><span class="text-gray-400">{{ __('Priorité') }} :</span> {{ $paiement->priorite ?? '—' }}</div>
                    <div><span class="text-gray-400">{{ __('Date prévue') }} :</span> {{ optional($paiement->date_prevue)->format('d/m/Y') ?? '—' }}</div>
                    <div><span class="text-gray-400">{{ __('Devises') }} :</span> {{ $paiement->devise }}@if ($paiement->devise_paiement) → {{ $paiement->devise_paiement }} ({{ __('taux') }} {{ $paiement->taux_change }}) @endif</div>
                    <div><span class="text-gray-400">{{ __('Frais bancaires') }} :</span> <x-ds.money :value="$paiement->frais_bancaires" :decimals="2" :show-currency="false" /></div>
                    <div><span class="text-gray-400">{{ __('Écart de change') }} :</span> <x-ds.money :value="$paiement->ecart_change" :decimals="2" :show-currency="false" /></div>
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="paiement">
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead><tr><th>{{ __('Désignation') }}</th><th>{{ __('Ligne budg.') }}</th><th class="text-right">{{ __('Ordonnancé') }}</th><th class="text-right">{{ __('Payé') }}</th><th class="text-right">{{ __('Reste') }}</th></tr></thead>
                            <tbody>
                                @foreach ($paiement->lignes as $l)
                                    <tr>
                                        <td>{{ $l->designation }}</td>
                                        <td class="font-mono text-xs">{{ $l->ligneBudgetaire?->code }}</td>
                                        <td class="text-right"><x-ds.money :value="$l->montant_ordonnance" :decimals="2" :show-currency="false" /></td>
                                        <td class="text-right font-semibold"><x-ds.money :value="$l->montant_paye" :decimals="2" :show-currency="false" /></td>
                                        <td class="text-right"><x-ds.money :value="$l->reste_a_payer" :decimals="2" :show-currency="false" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="ecritures">
                    @php($totDebit = $paiement->ecritures->sum('debit'))
                    @php($totCredit = $paiement->ecritures->sum('credit'))
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead><tr><th>{{ __('N° écriture') }}</th><th>{{ __('Compte') }}</th><th>{{ __('Libellé') }}</th><th class="text-right">{{ __('Débit') }}</th><th class="text-right">{{ __('Crédit') }}</th></tr></thead>
                            <tbody>
                                @forelse ($paiement->ecritures as $e)
                                    <tr>
                                        <td class="font-mono text-xs">{{ $e->numero_ecriture }}</td>
                                        <td class="font-mono">{{ $e->compte }}</td>
                                        <td class="text-sm">{{ $e->libelle }}</td>
                                        <td class="text-right">@if($e->debit > 0)<x-ds.money :value="$e->debit" :decimals="2" :show-currency="false" />@endif</td>
                                        <td class="text-right">@if($e->credit > 0)<x-ds.money :value="$e->credit" :decimals="2" :show-currency="false" />@endif</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="p-0"><x-ds.empty-state :title="__('Aucune écriture (générée à l’exécution).')" class="border-0 shadow-none" /></td></tr>
                                @endforelse
                                @if ($paiement->ecritures->isNotEmpty())
                                    <tr class="font-bold bg-gray-50">
                                        <td colspan="3" class="text-right">{{ __('Totaux') }}</td>
                                        <td class="text-right"><x-ds.money :value="$totDebit" :decimals="2" :show-currency="false" /></td>
                                        <td class="text-right"><x-ds.money :value="$totCredit" :decimals="2" :show-currency="false" /></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="ged" class="space-y-4">
                    @include('paiements.partials.ged', ['paiement' => $paiement])
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="workflow" class="space-y-4">
                    @include('paiements.partials.workflow', ['paiement' => $paiement, 'pendingStep' => $pendingStep, 'canValidatePending' => $canValidatePending])
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="rejets" class="space-y-4">
                    @include('paiements.partials.rejets', ['paiement' => $paiement])
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

        <div class="flex items-center justify-end">
            @can('annuler', $paiement)
                <form method="POST" action="{{ route('paiements.destroy', $paiement) }}" onsubmit="return confirm(@json(__('Annuler définitivement ce paiement ?')))" class="flex items-end gap-2">
                    @csrf @method('DELETE')
                    <input type="text" name="motif_annulation" placeholder="{{ __('Motif d’annulation') }}" class="form-input text-sm" maxlength="1000">
                    <button class="btn btn-danger">{{ __('Annuler le paiement') }}</button>
                </form>
            @endcan
        </div>
    </x-ds.page-shell>
</x-app-layout>
