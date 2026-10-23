<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="$engagement->numero"
            :description="\Illuminate\Support\Str::limit($engagement->objet, 120)"
            :reference="$engagement->numero"
            :statut="$engagement->statut"
            statut-module="engagement"
            :breadcrumb="[
                ['label' => __('Engagements'), 'url' => route('engagements.index')],
                ['label' => $engagement->numero],
            ]"
        >
            <x-slot:actions>
                @can('update', $engagement)
                    <a href="{{ route('engagements.edit', $engagement) }}" class="btn btn-secondary btn-sm">{{ __('Modifier') }}</a>
                @endcan
                @can('view', $engagement)
                    <a href="{{ route('engagements.export.pdf', $engagement) }}" class="btn btn-secondary btn-sm">{{ __('Export PDF') }}</a>
                    @if (in_array($engagement->statut instanceof \BackedEnum ? $engagement->statut->value : $engagement->statut, ['approuve', 'liquide', 'ordonnance', 'paye', 'cloture'], true))
                        <a href="{{ route('engagements.documents.certificat', $engagement) }}" class="btn btn-secondary btn-sm">{{ __('Certificat') }}</a>
                    @endif
                @endcan
                <a href="{{ route('engagements.workflow.show', $engagement) }}" class="btn btn-ghost btn-sm">{{ __('workflow.title') }}</a>
                @can('submit', $engagement)
                    <form method="POST" action="{{ route('engagements.submit', $engagement) }}" class="inline" onsubmit="return confirm(@json(__('Soumettre cet engagement pour validation ?')));">@csrf
                        <button class="btn btn-primary btn-sm">{{ __('Soumettre') }}</button>
                    </form>
                @endcan
                @if (\App\Policies\EngagementPolicy::allowsExecutionRecording($engagement) && $engagement->transmis_liquidation_at === null && \App\Support\EngagementPermissions::canTransferLiquidation(auth()->user()))
                    <form method="POST" action="{{ route('engagements.transmettre-liquidation', $engagement) }}" class="inline" onsubmit="return confirm(@json(__('Transmettre cet engagement à la liquidation ? Cette action est définitive.')));">@csrf
                        <button class="btn btn-primary btn-sm">{{ __('Transmettre à la liquidation') }}</button>
                    </form>
                @endif
                @can('cloturer', $engagement)
                    <form method="POST" action="{{ route('engagements.cloturer', $engagement) }}" class="inline" onsubmit="return confirm(@json(__('Clôturer définitivement cet engagement ?')));">@csrf
                        <button class="btn btn-secondary btn-sm">{{ __('Clôturer') }}</button>
                    </form>
                @endcan
            </x-slot:actions>
        </x-chaine.dossier-header>
    </x-slot>

    <x-ds.page-shell class="space-y-4">
        @include('chaine._workflow-panel', ['engagement' => $engagement])
        @if ($engagement->engagementType || $engagement->typeValeurs->isNotEmpty())
            <div class="card">
                <div class="card-header"><h3 class="font-semibold text-ceeac-primary">{{ __('Type ERP & champs spécifiques') }}</h3></div>
                <div class="card-body text-sm space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($engagement->engagementType)
                            <span class="badge badge-info">{{ $engagement->engagementType->code }} — {{ $engagement->engagementType->libelle }}</span>
                        @endif
                        <span class="badge badge-neutral">Mode : {{ ucfirst($engagement->mode_creation ?? 'manuel') }}</span>
                    </div>
                    @if ($engagement->typeValeurs->isNotEmpty())
                        <div class="grid md:grid-cols-2 gap-2">
                            @foreach ($engagement->typeValeurs as $tv)
                                <div><span class="text-gray-500">{{ ucfirst(str_replace('_', ' ', $tv->cle)) }}</span><p class="font-medium whitespace-pre-wrap">{{ $tv->valeur ?? '—' }}</p></div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-4">
            <div class="card lg:col-span-2">
                <div class="card-body text-sm space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-chaine.status-badge :statut="$engagement->statut" />
                        <span class="badge badge-neutral">Exercice {{ $engagement->exerciceBudgetaire?->annee ?? '—' }}</span>
                        @if($engagement->is_urgence)
                            <span class="badge badge-danger">Urgence</span>
                        @endif
                        @if($engagement->transmis_liquidation_at)
                            <span class="badge badge-info">Transmis à la liquidation le {{ $engagement->transmis_liquidation_at->format('d/m/Y') }}</span>
                        @endif
                    </div>
                    <div class="grid md:grid-cols-2 gap-3">
                        <div><span class="text-gray-500">{{ __('Imputations') }}</span><p class="font-medium">{{ max(1, $engagement->imputations->count()) }} ligne(s)</p></div>
                        <div><span class="text-gray-500">{{ __('Type') }}</span><p class="font-medium">{{ $engagement->type_engagement }}</p></div>
                        <div><span class="text-gray-500">{{ __('Montant') }}</span><p class="font-semibold tabular-nums text-ceeac-primary">{{ number_format((float) $engagement->montant, 2, ',', ' ') }}</p></div>
                        <div><span class="text-gray-500">{{ __('Date d’engagement') }}</span><p class="font-medium">{{ $engagement->date_engagement?->format('d/m/Y') ?? '—' }}</p></div>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('Bénéficiaire') }}</span>
                        <p class="font-medium mt-0.5">
                            @switch($engagement->beneficiaire_type)
                                @case(\App\Enums\BeneficiaireType::Fournisseur)
                                    {{ $engagement->beneficiaire()?->raison_sociale ?? __('Fournisseur #:id', ['id' => $engagement->beneficiaire_id]) }}
                                    @break
                                @case(\App\Enums\BeneficiaireType::Agent)
                                    @php($ag = $engagement->beneficiaire())
                                    {{ $ag ? $ag->nom.' '.$ag->prenom : __('Agent #:id', ['id' => $engagement->beneficiaire_id]) }}
                                    @break
                                @default
                                    {{ $engagement->beneficiaire_autre ?? '—' }}
                            @endswitch
                        </p>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('Objet') }}</span>
                        <p class="mt-1 text-gray-800 whitespace-pre-wrap">{{ $engagement->objet }}</p>
                    </div>
                    @if($engagement->is_urgence)
                        <div class="alert-warning">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
                            <span class="whitespace-pre-wrap">{{ $engagement->justification_urgence ?: 'Urgence déclarée sans justification détaillée.' }}</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card">
                <div class="card-body text-sm space-y-3">
                    <h3 class="font-semibold text-ceeac-primary">Traçabilité</h3>
                    @if ($engagement->expressionBesoin)
                        <div>
                            <span class="text-gray-500">{{ __('Besoin d’origine') }}</span>
                            <p class="font-medium">
                                <a href="{{ route('expression-besoins.besoins.show', $engagement->expressionBesoin) }}" class="text-ceeac-bright hover:underline font-mono">
                                    {{ $engagement->expressionBesoin->reference }}
                                </a>
                            </p>
                        </div>
                    @endif
                    <div><span class="text-gray-500">{{ __('Créé par') }}</span><p class="font-medium">{{ $engagement->createur?->name ?? '—' }}</p></div>
                    @if($engagement->submitted_at)<div><span class="text-gray-500">{{ __('Transmis le') }}</span><p class="font-medium">{{ $engagement->submitted_at->format('d/m/Y H:i') }}</p></div>@endif
                    @if($engagement->approved_at)<div><span class="text-gray-500">{{ __('Approuvé le') }}</span><p class="font-medium">{{ $engagement->approved_at->format('d/m/Y H:i') }}</p></div>@endif
                    @if($engagement->rejected_at)<div><span class="text-gray-500">{{ __('Rejeté le') }}</span><p class="font-medium">{{ $engagement->rejected_at->format('d/m/Y H:i') }}</p></div>@endif
                    @if($engagement->cancelled_at)<div><span class="text-gray-500">{{ __('Annulé le') }}</span><p class="font-medium">{{ $engagement->cancelled_at->format('d/m/Y H:i') }}</p></div>@endif
                    @if($engagement->motif_rejet)<div class="alert-danger"><span class="whitespace-pre-wrap">{{ $engagement->motif_rejet }}</span></div>@endif
                    @if($engagement->motif_annulation)<div class="alert-danger"><span class="whitespace-pre-wrap">{{ $engagement->motif_annulation }}</span></div>@endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-gray-900">Imputations budgétaires</h3></div>
            <div class="card-body text-sm">
                @php($rows = $engagement->imputations->isNotEmpty() ? $engagement->imputations : collect([(object) ['ligneBudgetaire' => $engagement->ligneBudgetaire, 'montant_impute' => $engagement->montant, 'observation' => null]]))
                <div class="overflow-x-auto rounded-xl border border-gray-100">
                    <table class="data-table min-w-full">
                        <thead>
                            <tr>
                                <th>Ligne budgétaire</th>
                                <th class="text-right">Montant imputé</th>
                                <th>Observation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                <tr>
                                    <td>{{ $row->ligneBudgetaire?->code ?? '—' }} — {{ $row->ligneBudgetaire?->libelle ?? '' }}</td>
                                    <td class="text-right tabular-nums">{{ number_format((float) $row->montant_impute, 2, ',', ' ') }}</td>
                                    <td>{{ $row->observation ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-gray-900">Suivi consolidé par imputation</h3></div>
            <div class="card-body text-sm space-y-3">
                @php($rowsConsolide = $rowsConsolide ?? ($engagement->imputations->isNotEmpty()
                    ? $engagement->imputations
                    : collect([(object) ['id' => null, 'ligneBudgetaire' => $engagement->ligneBudgetaire, 'montant_impute' => $engagement->montant]])))
                <div class="overflow-x-auto rounded-xl border border-gray-100">
                    <table class="data-table min-w-full">
                        <thead>
                            <tr>
                                <th>Ligne budgétaire</th>
                                <th class="text-right">Montant imputé</th>
                                <th class="text-right">Liquidé</th>
                                <th class="text-right">Reste à liquider</th>
                                <th class="text-right">Ordonnancé</th>
                                <th class="text-right">Reste à ordonnancer</th>
                                <th class="text-right">Payé</th>
                                <th class="text-right">Reste à payer</th>
                                <th>Statut conformité</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php($totImpute = 0.0)
                            @php($totLiq = 0.0)
                            @php($totOrd = 0.0)
                            @php($totPay = 0.0)
                            @foreach($rowsConsolide as $row)
                                @php($idImp = $row->id ?? null)
                                @php($liq = $engagement->liquidations->where('engagement_imputation_id', $idImp)->sum(fn ($l) => (float) $l->montant_liquide))
                                @php($ord = $engagement->liquidations->where('engagement_imputation_id', $idImp)->flatMap(fn ($l) => $l->ordonnancements)->sum(fn ($o) => (float) $o->montant_ordonnance))
                                @php($pay = $engagement->liquidations->where('engagement_imputation_id', $idImp)->flatMap(fn ($l) => $l->ordonnancements)->flatMap(fn ($o) => $o->paiements)->sum(fn ($p) => (float) $p->montant_paye))
                                @php($impute = (float) $row->montant_impute)
                                @php($resteLiq = max(0, $impute - $liq))
                                @php($resteOrd = max(0, $liq - $ord))
                                @php($restePay = max(0, $ord - $pay))
                                @php($status = $resteLiq > 0 ? 'A liquider' : ($resteOrd > 0 ? 'A ordonnancer' : ($restePay > 0 ? 'A payer' : 'OK')))
                                @php($statusClass = $status === 'OK' ? 'badge-success' : 'badge-warning')
                                @php($totImpute += $impute)
                                @php($totLiq += $liq)
                                @php($totOrd += $ord)
                                @php($totPay += $pay)
                                <tr>
                                    <td>{{ $row->ligneBudgetaire?->code ?? '—' }} — {{ $row->ligneBudgetaire?->libelle ?? '' }}</td>
                                    <td class="text-right tabular-nums">{{ number_format($impute, 2, ',', ' ') }}</td>
                                    <td class="text-right tabular-nums">{{ number_format($liq, 2, ',', ' ') }}</td>
                                    <td class="text-right tabular-nums {{ $resteLiq > 0 ? 'text-amber-700' : 'text-emerald-700' }}">{{ number_format($resteLiq, 2, ',', ' ') }}</td>
                                    <td class="text-right tabular-nums">{{ number_format($ord, 2, ',', ' ') }}</td>
                                    <td class="text-right tabular-nums {{ $resteOrd > 0 ? 'text-amber-700' : 'text-emerald-700' }}">{{ number_format($resteOrd, 2, ',', ' ') }}</td>
                                    <td class="text-right tabular-nums">{{ number_format($pay, 2, ',', ' ') }}</td>
                                    <td class="text-right tabular-nums {{ $restePay > 0 ? 'text-amber-700' : 'text-emerald-700' }}">{{ number_format($restePay, 2, ',', ' ') }}</td>
                                    <td><span class="badge {{ $statusClass }}">{{ $status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            @php($totResteLiq = max(0, $totImpute - $totLiq))
                            @php($totResteOrd = max(0, $totLiq - $totOrd))
                            @php($totRestePay = max(0, $totOrd - $totPay))
                            <tr class="bg-gray-50 font-semibold">
                                <td>Total</td>
                                <td class="text-right tabular-nums">{{ number_format($totImpute, 2, ',', ' ') }}</td>
                                <td class="text-right tabular-nums">{{ number_format($totLiq, 2, ',', ' ') }}</td>
                                <td class="text-right tabular-nums">{{ number_format($totResteLiq, 2, ',', ' ') }}</td>
                                <td class="text-right tabular-nums">{{ number_format($totOrd, 2, ',', ' ') }}</td>
                                <td class="text-right tabular-nums">{{ number_format($totResteOrd, 2, ',', ' ') }}</td>
                                <td class="text-right tabular-nums">{{ number_format($totPay, 2, ',', ' ') }}</td>
                                <td class="text-right tabular-nums">{{ number_format($totRestePay, 2, ',', ' ') }}</td>
                                <td>
                                    <span class="badge {{ ($totResteLiq <= 0 && $totResteOrd <= 0 && $totRestePay <= 0) ? 'badge-success' : 'badge-warning' }}">
                                        {{ ($totResteLiq <= 0 && $totResteOrd <= 0 && $totRestePay <= 0) ? 'OK' : 'En cours' }}
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-gray-900">Pièces justificatives</h3></div>
            <div class="card-body text-sm">
                @if($engagement->piecesJustificatives->isEmpty())
                    <p class="text-gray-500">Aucune pièce justificative attachée.</p>
                @else
                    <div class="space-y-2">
                        @foreach($engagement->piecesJustificatives as $piece)
                            <div class="flex items-center justify-between border border-gray-100 rounded-xl px-3 py-2">
                                <div>
                                    <p class="font-medium text-gray-800">{{ $piece->nom_original }}</p>
                                    <p class="text-xs text-gray-500">{{ $piece->type_piece }} · v{{ $piece->version }} · {{ $piece->deposant?->name ?? '—' }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('engagements.pieces-justificatives.download', [$engagement, $piece]) }}" class="btn btn-ghost btn-sm">Télécharger</a>
                                    @can('update', $engagement)
                                        <form method="POST" action="{{ route('engagements.pieces-justificatives.destroy', [$engagement, $piece]) }}" onsubmit="return confirm('Supprimer cette pièce ?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger btn-sm" type="submit">Supprimer</button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                @can('update', $engagement)
                    <form method="POST" action="{{ route('engagements.pieces-justificatives.store', $engagement) }}" enctype="multipart/form-data" class="mt-4 grid md:grid-cols-3 gap-3 items-end">
                        @csrf
                        <div>
                            <x-input-label for="piece" :value="'Fichier'" />
                            <input type="file" id="piece" name="piece" class="form-input mt-1" required />
                        </div>
                        <div>
                            <x-input-label for="type_piece" :value="'Type de pièce'" />
                            <input type="text" id="type_piece" name="type_piece" class="form-input mt-1" placeholder="devis, contrat, note..." />
                        </div>
                        <div>
                            <button class="btn btn-secondary w-full" type="submit">Ajouter la pièce</button>
                        </div>
                    </form>
                @endcan
            </div>
        </div>

        @can('decideWorkflow', $engagement)
            <div class="card">
                <div class="card-body space-y-4">
                <h3 class="font-semibold text-gray-900">{{ __('Décision sur l’étape en cours') }}</h3>
                <form method="POST" action="{{ route('engagements.workflow.decision', $engagement) }}" class="space-y-4">@csrf
                    <fieldset class="space-y-2">
                        <legend class="text-sm font-medium text-gray-700">{{ __('Décision') }}</legend>
                        <label class="flex items-center gap-2"><input type="radio" name="decision" value="valide" class="rounded-full border-gray-300 text-indigo-600" @checked(old('decision') === 'valide') required /> {{ __('workflow.decisions.valide') }}</label>
                        <label class="flex items-center gap-2"><input type="radio" name="decision" value="retourne" class="rounded-full border-gray-300 text-indigo-600" @checked(old('decision') === 'retourne') /> {{ __('workflow.decisions.retourne') }}</label>
                        <label class="flex items-center gap-2"><input type="radio" name="decision" value="rejete" class="rounded-full border-gray-300 text-indigo-600" @checked(old('decision') === 'rejete') /> {{ __('workflow.decisions.rejete') }}</label>
                    </fieldset>
                    <x-input-error class="mt-2" :messages="$errors->get('decision')" />
                    <div>
                        <x-input-label for="commentaire" :value="__('Commentaire (obligatoire si retour ou rejet)')" />
                        <textarea id="commentaire" name="commentaire" rows="3" class="form-input mt-1">{{ old('commentaire') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('commentaire')" />
                    </div>
                    <button class="btn btn-primary">{{ __('Enregistrer la décision') }}</button>
                </form>
                </div>
            </div>
        @endcan

        @if($engagement->liquidations->isNotEmpty() || auth()->user()->can('createLiquidation', $engagement))
                <div class="card">
                    <div class="card-body space-y-4">
                    <div class="flex justify-between items-center flex-wrap gap-2">
                        <h3 class="font-semibold text-gray-900">{{ __('Exécution budgétaire') }}</h3>
                        @can('createLiquidation', $engagement)
                            <a href="{{ route('engagements.liquidations.create', $engagement) }}" class="btn btn-secondary btn-sm">{{ __('Nouvelle liquidation') }}</a>
                        @endcan
                    </div>
                    @php($cumulLiq = $engagement->liquidations->sum(fn ($l) => (float) $l->montant_liquide))
                    <p class="text-sm text-gray-600">{{ __('Montant engagement') }} : <span class="tabular-nums font-medium">{{ number_format((float) $engagement->montant, 2, ',', ' ') }}</span>
                        — {{ __('Liquidé (cumul)') }} : <span class="tabular-nums">{{ number_format($cumulLiq, 2, ',', ' ') }}</span></p>
                    @forelse ($engagement->liquidations as $liq)
                        <div class="border border-gray-200 rounded-lg p-4 space-y-2 text-sm">
                            <div class="flex justify-between flex-wrap gap-2">
                                <span class="font-medium text-gray-900">{{ __('Liquidation') }} #{{ $liq->id }}</span>
                                <span class="tabular-nums font-medium">{{ number_format((float) $liq->montant_liquide, 2, ',', ' ') }}</span>
                            </div>
                            <div class="text-gray-600">{{ $liq->date_liquidation->format('d/m/Y') }} @if($liq->reference) — {{ $liq->reference }} @endif</div>
                            <div class="text-xs text-gray-500">
                                Imputation: {{ $liq->engagementImputation?->ligneBudgetaire?->code ?? $engagement->ligneBudgetaire?->code ?? '—' }}
                                — {{ $liq->engagementImputation?->ligneBudgetaire?->libelle ?? $engagement->ligneBudgetaire?->libelle ?? '' }}
                            </div>
                            @if($liq->commentaire)<p class="text-gray-700 whitespace-pre-wrap">{{ $liq->commentaire }}</p>@endif
                            <div class="text-xs text-gray-500">{{ __('Par') }} {{ $liq->createur?->name ?? '—' }}</div>
                            @can('addOrdonnancement', $liq)
                                <div class="pt-2">
                                    <a href="{{ route('liquidations.ordonnancements.create', $liq) }}" class="text-indigo-600 text-sm font-medium">{{ __('+ Ordonnancement') }}</a>
                                </div>
                            @endcan
                            @foreach ($liq->ordonnancements as $ord)
                                <div class="ms-4 mt-3 ps-3 border-s-2 border-indigo-200 space-y-1">
                                    <div class="flex justify-between gap-2 flex-wrap">
                                        <span class="text-gray-800">{{ __('Ordonnancement') }} #{{ $ord->id }}</span>
                                        <span class="tabular-nums">{{ number_format((float) $ord->montant_ordonnance, 2, ',', ' ') }}</span>
                                    </div>
                                    <div class="text-xs text-gray-600">{{ $ord->date_ordonnancement->format('d/m/Y') }} — {{ $ord->createur?->name ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">
                                        Imputation: {{ $ord->engagementImputation?->ligneBudgetaire?->code ?? $liq->engagementImputation?->ligneBudgetaire?->code ?? '—' }}
                                        — {{ $ord->engagementImputation?->ligneBudgetaire?->libelle ?? $liq->engagementImputation?->ligneBudgetaire?->libelle ?? '' }}
                                    </div>
                                    @can('addPaiement', $ord)
                                        <a href="{{ route('ordonnancements.paiements.create', $ord) }}" class="text-indigo-600 text-xs font-medium">{{ __('+ Paiement') }}</a>
                                    @endcan
                                    @foreach ($ord->paiements as $pay)
                                        <div class="ms-3 mt-2 ps-3 border-s border-gray-300 text-xs space-y-1">
                                            <div class="flex justify-between gap-2">
                                                <span>{{ __('Paiement') }} #{{ $pay->id }}</span>
                                                <span class="tabular-nums">{{ number_format((float) $pay->montant_paye, 2, ',', ' ') }}</span>
                                            </div>
                                            <div class="text-gray-600">{{ $pay->date_paiement->format('d/m/Y') }} @if($pay->mode_paiement) — {{ $pay->mode_paiement }} @endif — {{ $pay->createur?->name ?? '—' }}</div>
                                            <div class="text-gray-500">
                                                Imputation: {{ $pay->engagementImputation?->ligneBudgetaire?->code ?? $ord->engagementImputation?->ligneBudgetaire?->code ?? '—' }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">{{ __('Aucune liquidation pour cet engagement.') }}</p>
                    @endforelse
                    @error('montant_liquide')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    </div>
                </div>
        @endif

        @can('cancelApproved', $engagement)
            <div class="card border-red-200 bg-red-50">
                <div class="card-body space-y-3">
                <h3 class="font-semibold text-red-900">{{ __('Annuler l’engagement approuvé') }}</h3>
                <p class="text-sm text-red-800">{{ __('Aucune liquidation ne doit exister. Le montant engagé sur la ligne sera diminué du montant de cet engagement.') }}</p>
                <form method="POST" action="{{ route('engagements.cancel-approved', $engagement) }}" class="space-y-3" onsubmit="return confirm(@json(__('Confirmer l’annulation ?')));">@csrf
                    <div>
                        <x-input-label for="motif_annulation" :value="__('Motif d’annulation')" />
                        <textarea id="motif_annulation" name="motif_annulation" rows="3" required class="form-input mt-1">{{ old('motif_annulation') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('motif_annulation')" />
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('engagement')" />
                    <button type="submit" class="btn btn-danger">{{ __('Annuler l’engagement') }}</button>
                </form>
                </div>
            </div>
        @endcan

        @can('update', $engagement)
            <div class="card">
                <div class="card-header"><h3 class="font-semibold text-ceeac-primary">{{ __('Déposer une pièce dans la GED') }}</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('engagements.ged.store', $engagement) }}" enctype="multipart/form-data" class="grid md:grid-cols-3 gap-3 items-end">
                        @csrf
                        <div class="md:col-span-2">
                            <x-input-label for="fichier_ged" :value="__('Fichier (contrat, marché, certificat, avenant…)')" />
                            <input id="fichier_ged" name="fichier" type="file" required
                                   class="block mt-1 w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-ceeac-light file:px-4 file:py-2 file:text-ceeac-primary file:font-semibold cursor-pointer" />
                            <x-input-error class="mt-2" :messages="$errors->get('fichier')" />
                        </div>
                        <div>
                            <x-input-label for="titre_ged" :value="__('Titre (optionnel)')" />
                            <x-text-input id="titre_ged" name="titre" class="block mt-1 w-full" />
                        </div>
                        <div class="md:col-span-3"><button class="btn btn-secondary btn-sm">{{ __('Déposer la pièce') }}</button></div>
                    </form>
                </div>
            </div>
        @endcan

        @if ($engagement->reservationsBudgetaires->isNotEmpty())
            <div class="card overflow-x-auto">
                <div class="card-header"><h3 class="font-semibold text-ceeac-primary">{{ __('Réservations de crédit') }}</h3></div>
                <div class="card-body">
                    <table class="data-table">
                        <thead><tr><th>Ligne budgétaire</th><th>Sens</th><th class="text-right">Montant</th><th>Statut</th><th>Date</th></tr></thead>
                        <tbody>
                            @foreach ($engagement->reservationsBudgetaires as $r)
                                <tr>
                                    <td class="font-mono text-xs">{{ $r->ligneBudgetaire?->code ?? '—' }}</td>
                                    <td>{{ ucfirst($r->sens) }}</td>
                                    <td class="text-right tabular-nums">{{ number_format((float) $r->montant_reserve, 2, ',', ' ') }}</td>
                                    <td><span class="badge {{ $r->statut === 'active' ? 'badge-success' : 'badge-neutral' }}">{{ ucfirst($r->statut) }}</span></td>
                                    <td class="whitespace-nowrap">{{ $r->date_reservation?->format('d/m/Y H:i') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($engagement->versions->isNotEmpty())
            <div class="card overflow-x-auto">
                <div class="card-header"><h3 class="font-semibold text-ceeac-primary">{{ __('Versions') }}</h3></div>
                <div class="card-body">
                    <table class="data-table">
                        <thead><tr><th class="text-center">Version</th><th>Auteur</th><th>Motif</th><th>Date</th></tr></thead>
                        <tbody>
                            @foreach ($engagement->versions as $v)
                                <tr>
                                    <td class="text-center tabular-nums font-semibold">v{{ $v->numero_version }}</td>
                                    <td>{{ $v->createur?->name ?? '—' }}</td>
                                    <td>{{ $v->motif ?? '—' }}</td>
                                    <td class="whitespace-nowrap">{{ $v->created_at?->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($timeline->isNotEmpty())
            <div class="card overflow-x-auto">
                <div class="card-header"><h3 class="font-semibold text-ceeac-primary">{{ __('Journal des opérations') }}</h3></div>
                <div class="card-body">
                    <table class="data-table">
                        <thead><tr><th>Date</th><th>Action</th><th>Utilisateur</th><th>Détail</th></tr></thead>
                        <tbody>
                            @foreach ($timeline as $entry)
                                <tr>
                                    <td class="whitespace-nowrap">{{ $entry->created_at?->format('d/m/Y H:i') }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $entry->properties['action'] ?? '')) }}</td>
                                    <td>{{ $entry->causer?->name ?? 'Système' }}</td>
                                    <td>{{ $entry->description }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @include('ged._documents_associes', ['entity' => $engagement])

        <a href="{{ route('engagements.index') }}" class="btn btn-ghost">{{ __('← Liste des engagements') }}</a>
    </x-ds.page-shell>
</x-app-layout>
