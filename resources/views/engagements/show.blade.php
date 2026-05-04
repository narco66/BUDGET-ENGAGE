<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl font-mono">{{ $engagement->numero }}</h2>
            <div class="flex flex-wrap gap-2">
                @can('update', $engagement)
                    <a href="{{ route('engagements.edit', $engagement) }}"><x-secondary-button type="button">{{ __('Modifier') }}</x-secondary-button></a>
                @endcan
                @can('submit', $engagement)
                    <form method="POST" action="{{ route('engagements.submit', $engagement) }}" class="inline" onsubmit="return confirm(@json(__('Soumettre cet engagement pour validation ?')));">@csrf
                        <x-primary-button>{{ __('Soumettre') }}</x-primary-button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>
    @php($st = $engagement->statut instanceof \BackedEnum ? $engagement->statut->value : $engagement->statut)
    @php($libStatut = match ($st) {
        'brouillon' => __('Brouillon'),
        'soumis' => __('Soumis'),
        'en_validation' => __('En validation'),
        'retourne' => __('Retourné'),
        'rejete' => __('Rejeté'),
        'approuve' => __('Approuvé'),
        'annule' => __('Annulé'),
        'liquide' => __('Liquide'),
        'ordonnance' => __('Ordonnancé'),
        'paye' => __('Payé'),
        'cloture' => __('Clôturé'),
        default => $st,
    })
    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <x-auth-session-status :status="session('status')" />
        <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm space-y-3">
            <div><span class="text-gray-500">{{ __('Statut') }}</span> : <span class="font-medium">{{ $libStatut }}</span></div>
            <div><span class="text-gray-500">{{ __('Exercice') }}</span> : {{ $engagement->exerciceBudgetaire?->annee ?? '—' }}</div>
            <div><span class="text-gray-500">{{ __('Ligne budgétaire') }}</span> : {{ $engagement->ligneBudgetaire?->code ?? '—' }} — {{ $engagement->ligneBudgetaire?->libelle ?? '' }}</div>
            @if($engagement->ligneBudgetaire?->natureDepense)
                <div><span class="text-gray-500">{{ __('Nature') }}</span> : {{ $engagement->ligneBudgetaire->natureDepense->libelle }}</div>
            @endif
            <div><span class="text-gray-500">{{ __('Type') }}</span> : {{ $engagement->type_engagement }}</div>
            <div><span class="text-gray-500">{{ __('Montant') }}</span> : <span class="tabular-nums font-medium">{{ number_format((float) $engagement->montant, 2, ',', ' ') }}</span></div>
            @if($engagement->date_engagement)
                <div><span class="text-gray-500">{{ __('Date d’engagement') }}</span> : {{ $engagement->date_engagement->format('d/m/Y') }}</div>
            @endif
            <hr class="border-gray-100">
            <div><span class="text-gray-500">{{ __('Bénéficiaire') }}</span> :
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
            </div>
            <div><span class="text-gray-500">{{ __('Objet') }}</span></div>
            <p class="text-gray-800 whitespace-pre-wrap">{{ $engagement->objet }}</p>
            @if($engagement->is_urgence)
                <div class="rounded-md bg-amber-50 border border-amber-100 p-3 text-amber-900">
                    <div class="font-medium">{{ __('Procédure d’urgence') }}</div>
                    @if($engagement->justification_urgence)
                        <p class="mt-1 whitespace-pre-wrap">{{ $engagement->justification_urgence }}</p>
                    @endif
                </div>
            @endif
            <hr class="border-gray-100">
            <div><span class="text-gray-500">{{ __('Créé par') }}</span> : {{ $engagement->createur?->name ?? '—' }}</div>
            @if($engagement->submitted_at)
                <div><span class="text-gray-500">{{ __('Transmis le') }}</span> : {{ $engagement->submitted_at->format('d/m/Y H:i') }}</div>
            @endif
            @if($engagement->approved_at)
                <div><span class="text-gray-500">{{ __('Approuvé le') }}</span> : {{ $engagement->approved_at->format('d/m/Y H:i') }}</div>
            @endif
            @if($engagement->rejected_at)
                <div><span class="text-gray-500">{{ __('Rejeté le') }}</span> : {{ $engagement->rejected_at->format('d/m/Y H:i') }}</div>
            @endif
            @if($engagement->motif_rejet)
                <div><span class="text-gray-500">{{ __('Motif') }}</span> : <span class="whitespace-pre-wrap">{{ $engagement->motif_rejet }}</span></div>
            @endif
            @if($engagement->cancelled_at)
                <div><span class="text-gray-500">{{ __('Annulé le') }}</span> : {{ $engagement->cancelled_at->format('d/m/Y H:i') }}</div>
            @endif
            @if($engagement->motif_annulation)
                <div><span class="text-gray-500">{{ __('Motif d’annulation') }}</span> : <span class="whitespace-pre-wrap">{{ $engagement->motif_annulation }}</span></div>
            @endif
            @if($engagement->ligneBudgetaire)
                <hr class="border-gray-100">
                <div class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Suivi sur la ligne budgétaire') }}</div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 tabular-nums text-sm">
                    <div><span class="text-gray-500">{{ __('Engagé') }}</span><br>{{ number_format((float) $engagement->ligneBudgetaire->montant_engage, 2, ',', ' ') }}</div>
                    <div><span class="text-gray-500">{{ __('Liquidé') }}</span><br>{{ number_format((float) $engagement->ligneBudgetaire->montant_liquide, 2, ',', ' ') }}</div>
                    <div><span class="text-gray-500">{{ __('Ordonnancé') }}</span><br>{{ number_format((float) $engagement->ligneBudgetaire->montant_ordonnance, 2, ',', ' ') }}</div>
                    <div><span class="text-gray-500">{{ __('Payé') }}</span><br>{{ number_format((float) $engagement->ligneBudgetaire->montant_paye, 2, ',', ' ') }}</div>
                </div>
            @endif
        </div>

        @if($engagement->validationsWorkflow->isNotEmpty())
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <h3 class="font-semibold text-gray-900">{{ __('Circuit de validation') }}</h3>
                <ul class="space-y-3 text-sm">
                    @foreach ($engagement->validationsWorkflow as $vw)
                        @php($dv = $vw->decision instanceof \BackedEnum ? $vw->decision->value : $vw->decision)
                        <li class="border border-gray-100 rounded-lg p-3 {{ $dv === 'en_attente' ? 'bg-indigo-50/40 border-indigo-100' : 'bg-gray-50' }}">
                            <div class="flex justify-between gap-2 flex-wrap">
                                <span class="font-medium">{{ __('workflow.steps.'.$vw->etape) }}</span>
                                <span class="text-gray-600">{{ __('workflow.decisions.'.$dv) }}</span>
                            </div>
                            @if($vw->validateur)
                                <div class="mt-1 text-xs text-gray-500">{{ __('Par') }} {{ $vw->validateur->name }} @if($vw->date_decision) — {{ $vw->date_decision->format('d/m/Y H:i') }} @endif</div>
                            @endif
                            @if($vw->commentaire)
                                <p class="mt-2 text-gray-700 whitespace-pre-wrap">{{ $vw->commentaire }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
                @error('workflow')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endif

        @can('decideWorkflow', $engagement)
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
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
                        <textarea id="commentaire" name="commentaire" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('commentaire') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('commentaire')" />
                    </div>
                    <x-primary-button>{{ __('Enregistrer la décision') }}</x-primary-button>
                </form>
            </div>
        @endcan

        @if($engagement->liquidations->isNotEmpty() || auth()->user()->can('createLiquidation', $engagement))
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                    <div class="flex justify-between items-center flex-wrap gap-2">
                        <h3 class="font-semibold text-gray-900">{{ __('Exécution budgétaire') }}</h3>
                        @can('createLiquidation', $engagement)
                            <a href="{{ route('engagements.liquidations.create', $engagement) }}"><x-secondary-button type="button">{{ __('Nouvelle liquidation') }}</x-secondary-button></a>
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
        @endif

        @can('cancelApproved', $engagement)
            <div class="border border-red-200 bg-red-50 rounded-lg p-6 space-y-3">
                <h3 class="font-semibold text-red-900">{{ __('Annuler l’engagement approuvé') }}</h3>
                <p class="text-sm text-red-800">{{ __('Aucune liquidation ne doit exister. Le montant engagé sur la ligne sera diminué du montant de cet engagement.') }}</p>
                <form method="POST" action="{{ route('engagements.cancel-approved', $engagement) }}" class="space-y-3" onsubmit="return confirm(@json(__('Confirmer l’annulation ?')));">@csrf
                    <div>
                        <x-input-label for="motif_annulation" :value="__('Motif d’annulation')" />
                        <textarea id="motif_annulation" name="motif_annulation" rows="3" required class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('motif_annulation') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('motif_annulation')" />
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('engagement')" />
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600">{{ __('Annuler l’engagement') }}</button>
                </form>
            </div>
        @endcan

        <a href="{{ route('engagements.index') }}" class="text-sm text-indigo-600">{{ __('← Liste des engagements') }}</a>
    </div>
</x-app-layout>
