@php
    $isEdit = isset($liquidation);
    $defaultLigne = [
        'designation' => '', 'ligne_budgetaire_id' => '', 'source_financement_id' => '', 'activite_id' => '',
        'quantite_realisee' => '', 'prix_unitaire' => '', 'montant_brut' => 0,
        'montant_avances' => 0, 'montant_acomptes' => 0, 'montant_retenues' => 0,
        'montant_garanties' => 0, 'montant_penalites' => 0, 'montant_taxes' => 0, 'montant_revision_prix' => 0,
    ];
    $initialLignes = old('lignes');
    if (! $initialLignes) {
        $initialLignes = $isEdit
            ? $liquidation->lignes->map(fn ($l) => [
                'designation' => $l->designation,
                'ligne_budgetaire_id' => $l->ligne_budgetaire_id,
                'source_financement_id' => $l->source_financement_id,
                'activite_id' => $l->activite_id,
                'quantite_realisee' => $l->quantite_realisee,
                'prix_unitaire' => $l->prix_unitaire,
                'montant_brut' => $l->montant_brut,
                'montant_avances' => $l->montant_avances,
                'montant_acomptes' => $l->montant_acomptes,
                'montant_retenues' => $l->montant_retenues,
                'montant_garanties' => $l->montant_garanties,
                'montant_penalites' => $l->montant_penalites,
                'montant_taxes' => $l->montant_taxes,
                'montant_revision_prix' => $l->montant_revision_prix,
            ])->values()->all()
            : [$defaultLigne];
    }
@endphp

<div x-data="liquidationForm({{ \Illuminate\Support\Js::from($initialLignes) }}, {{ \Illuminate\Support\Js::from($defaultLigne) }})">
    {{-- Onglets --}}
    <div class="flex flex-wrap gap-1 border-b border-gray-200 mb-5">
        @foreach ([
            'general' => 'Informations générales',
            'prestations' => 'Prestations',
            'calculs' => 'Calculs',
            'imputations' => 'Imputations',
        ] as $key => $label)
            <button type="button" @click="activeTab = '{{ $key }}'"
                :class="activeTab === '{{ $key }}' ? 'border-ceeac-primary text-ceeac-primary' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition">{{ $label }}</button>
        @endforeach
    </div>

    {{-- Onglet : Informations générales --}}
    <div x-show="activeTab === 'general'" class="space-y-4">
        @unless ($isEdit)
            <div>
                <x-input-label for="engagement_id" :value="__('Engagement')" />
                <select id="engagement_id" name="engagement_id" class="form-select mt-1" required>
                    <option value="">— {{ __('Choisir un engagement') }} —</option>
                    @foreach ($engagements as $eng)
                        <option value="{{ $eng->id }}" @selected((string) old('engagement_id', $engagementPreselect ?? '') === (string) $eng->id)>
                            {{ $eng->numero }} — {{ \Illuminate\Support\Str::limit($eng->objet, 50) }} ({{ number_format((float) $eng->montant, 2, ',', ' ') }})
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('engagement_id')" />
            </div>
            <div>
                <x-input-label for="mode_creation" :value="__('Mode de création')" />
                <select id="mode_creation" name="mode_creation" class="form-select mt-1">
                    @foreach (\App\Enums\ModeCreationLiquidation::cases() as $mode)
                        <option value="{{ $mode->value }}" @selected(old('mode_creation', 'manuel') === $mode->value)>{{ $mode->libelle() }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('mode_creation')" />
            </div>
        @endunless

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="liquidation_type_id" :value="__('Type de liquidation')" />
                <select id="liquidation_type_id" name="liquidation_type_id" class="form-select mt-1">
                    <option value="">— {{ __('Aucun') }} —</option>
                    @foreach ($liquidationTypes as $type)
                        <option value="{{ $type->id }}" @selected((string) old('liquidation_type_id', $isEdit ? $liquidation->liquidation_type_id : '') === (string) $type->id)>{{ $type->libelle }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('liquidation_type_id')" />
            </div>
            <div>
                <x-input-label for="fournisseur_id" :value="__('Fournisseur / Bénéficiaire')" />
                <select id="fournisseur_id" name="fournisseur_id" class="form-select mt-1">
                    <option value="">— {{ __('Aucun') }} —</option>
                    @foreach ($fournisseurs as $f)
                        <option value="{{ $f->id }}" @selected((string) old('fournisseur_id', $isEdit ? $liquidation->fournisseur_id : '') === (string) $f->id)>{{ $f->raison_sociale }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('fournisseur_id')" />
            </div>
            <div>
                <x-input-label for="date_liquidation" :value="__('Date de liquidation')" />
                <x-text-input id="date_liquidation" type="date" name="date_liquidation" class="block mt-1 w-full"
                    :value="old('date_liquidation', $isEdit ? optional($liquidation->date_liquidation)->format('Y-m-d') : now()->format('Y-m-d'))" required />
                <x-input-error class="mt-2" :messages="$errors->get('date_liquidation')" />
            </div>
            <div>
                <x-input-label for="devise" :value="__('Devise')" />
                <x-text-input id="devise" name="devise" class="block mt-1 w-full" maxlength="10"
                    :value="old('devise', $isEdit ? $liquidation->devise : 'XAF')" />
                <x-input-error class="mt-2" :messages="$errors->get('devise')" />
            </div>
        </div>

        <div>
            <x-input-label for="objet" :value="__('Objet')" />
            <textarea id="objet" name="objet" rows="2" class="form-textarea mt-1 block w-full">{{ old('objet', $isEdit ? $liquidation->objet : '') }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('objet')" />
        </div>

        @unless ($isEdit)
            <div>
                <x-input-label for="justification" :value="__('Justification (obligatoire si manuelle / corrective)')" />
                <textarea id="justification" name="justification" rows="2" class="form-textarea mt-1 block w-full">{{ old('justification') }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('justification')" />
            </div>
        @endunless

        <div>
            <x-input-label for="reference" :value="__('Référence')" />
            <x-text-input id="reference" name="reference" class="block mt-1 w-full" maxlength="100"
                :value="old('reference', $isEdit ? $liquidation->reference : '')" />
        </div>
    </div>

    {{-- Onglet : Prestations --}}
    <div x-show="activeTab === 'prestations'" class="space-y-4" style="display:none;">
        <x-input-error class="mt-2" :messages="$errors->get('lignes')" />
        <template x-for="(ligne, i) in lignes" :key="i">
            <div class="border border-gray-200 rounded-lg p-4 space-y-3 bg-gray-50/50">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500" x-text="'Ligne ' + (i + 1)"></span>
                    <button type="button" class="text-red-600 text-xs hover:underline" @click="removeLigne(i)" x-show="lignes.length > 1">{{ __('Retirer') }}</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="md:col-span-2">
                        <label class="form-label">{{ __('Désignation') }}</label>
                        <input type="text" class="form-input mt-1 w-full" :name="`lignes[${i}][designation]`" x-model="ligne.designation" required maxlength="255">
                    </div>
                    <div>
                        <label class="form-label">{{ __('Ligne budgétaire') }}</label>
                        <select class="form-select mt-1 w-full" :name="`lignes[${i}][ligne_budgetaire_id]`" x-model="ligne.ligne_budgetaire_id" required>
                            <option value="">—</option>
                            @foreach ($lignesBudgetaires as $lb)
                                <option value="{{ $lb->id }}">{{ $lb->code }} — {{ \Illuminate\Support\Str::limit($lb->libelle, 40) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Source de financement') }}</label>
                        <select class="form-select mt-1 w-full" :name="`lignes[${i}][source_financement_id]`" x-model="ligne.source_financement_id">
                            <option value="">—</option>
                            @foreach ($sourcesFinancement as $sf)
                                <option value="{{ $sf->id }}">{{ $sf->code }} — {{ $sf->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Activité PAP') }}</label>
                        <select class="form-select mt-1 w-full" :name="`lignes[${i}][activite_id]`" x-model="ligne.activite_id">
                            <option value="">—</option>
                            @foreach ($activites as $act)
                                <option value="{{ $act->id }}">{{ $act->code }} — {{ \Illuminate\Support\Str::limit($act->libelle, 35) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                        <label class="form-label">{{ __('Qté réalisée') }}</label>
                        <input type="number" step="0.0001" min="0" class="form-input mt-1 w-full tabular-nums" :name="`lignes[${i}][quantite_realisee]`" x-model.number="ligne.quantite_realisee">
                    </div>
                    <div>
                        <label class="form-label">{{ __('Prix unitaire') }}</label>
                        <input type="number" step="0.01" min="0" class="form-input mt-1 w-full tabular-nums" :name="`lignes[${i}][prix_unitaire]`" x-model.number="ligne.prix_unitaire">
                    </div>
                    <div>
                        <label class="form-label">{{ __('Montant brut') }}</label>
                        <input type="number" step="0.01" min="0" class="form-input mt-1 w-full tabular-nums" :name="`lignes[${i}][montant_brut]`" x-model.number="ligne.montant_brut" :readonly="ligne.quantite_realisee > 0 && ligne.prix_unitaire > 0" :value="brut(ligne)">
                    </div>
                    <div>
                        <label class="form-label">{{ __('Avances') }}</label>
                        <input type="number" step="0.01" min="0" class="form-input mt-1 w-full tabular-nums" :name="`lignes[${i}][montant_avances]`" x-model.number="ligne.montant_avances">
                    </div>
                    <div>
                        <label class="form-label">{{ __('Acomptes') }}</label>
                        <input type="number" step="0.01" min="0" class="form-input mt-1 w-full tabular-nums" :name="`lignes[${i}][montant_acomptes]`" x-model.number="ligne.montant_acomptes">
                    </div>
                    <div>
                        <label class="form-label">{{ __('Retenues') }}</label>
                        <input type="number" step="0.01" min="0" class="form-input mt-1 w-full tabular-nums" :name="`lignes[${i}][montant_retenues]`" x-model.number="ligne.montant_retenues">
                    </div>
                    <div>
                        <label class="form-label">{{ __('Garanties') }}</label>
                        <input type="number" step="0.01" min="0" class="form-input mt-1 w-full tabular-nums" :name="`lignes[${i}][montant_garanties]`" x-model.number="ligne.montant_garanties">
                    </div>
                    <div>
                        <label class="form-label">{{ __('Pénalités') }}</label>
                        <input type="number" step="0.01" min="0" class="form-input mt-1 w-full tabular-nums" :name="`lignes[${i}][montant_penalites]`" x-model.number="ligne.montant_penalites">
                    </div>
                    <div>
                        <label class="form-label">{{ __('Taxes') }}</label>
                        <input type="number" step="0.01" min="0" class="form-input mt-1 w-full tabular-nums" :name="`lignes[${i}][montant_taxes]`" x-model.number="ligne.montant_taxes">
                    </div>
                    <div>
                        <label class="form-label">{{ __('Révision prix') }}</label>
                        <input type="number" step="0.01" class="form-input mt-1 w-full tabular-nums" :name="`lignes[${i}][montant_revision_prix]`" x-model.number="ligne.montant_revision_prix">
                    </div>
                    <div class="col-span-2">
                        <label class="form-label">{{ __('Net ligne') }}</label>
                        <div class="mt-1 font-mono font-semibold text-ceeac-primary tabular-nums" x-text="fmt(ligneNet(ligne))"></div>
                    </div>
                </div>
            </div>
        </template>

        <button type="button" class="btn btn-ghost" @click="addLigne()">+ {{ __('Ajouter une ligne') }}</button>
    </div>

    {{-- Onglet : Calculs (synthèse temps réel) --}}
    <div x-show="activeTab === 'calculs'" class="space-y-2" style="display:none;">
        <div class="card">
            <div class="card-body grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                <template x-for="row in synthese()" :key="row.label">
                    <div class="flex justify-between border-b border-gray-100 py-1">
                        <span class="text-gray-500" x-text="row.label"></span>
                        <span class="font-mono tabular-nums" :class="row.strong ? 'font-bold text-ceeac-primary' : ''" x-text="fmt(row.value)"></span>
                    </div>
                </template>
            </div>
        </div>
        <p class="text-xs text-gray-400">{{ __('Net = Brut − Avances − Acomptes − Retenues − Garanties − Pénalités ± Révision + Taxes. Recalcul définitif au serveur.') }}</p>
    </div>

    {{-- Onglet : Imputations (récapitulatif par ligne budgétaire) --}}
    <div x-show="activeTab === 'imputations'" class="space-y-2" style="display:none;">
        <table class="data-table">
            <thead><tr><th>{{ __('Ligne budgétaire') }}</th><th class="text-right">{{ __('Net imputé') }}</th></tr></thead>
            <tbody>
                <template x-for="row in imputations()" :key="row.id">
                    <tr><td x-text="row.label"></td><td class="text-right font-mono tabular-nums" x-text="fmt(row.value)"></td></tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
function liquidationForm(initial, blank) {
    const lbLabels = @json($lignesBudgetaires->mapWithKeys(fn ($l) => [$l->id => $l->code])->all());
    return {
        activeTab: 'general',
        lignes: initial.length ? initial.map(l => ({ ...blank, ...l })) : [{ ...blank }],
        addLigne() { this.lignes.push({ ...blank }); },
        removeLigne(i) { if (this.lignes.length > 1) this.lignes.splice(i, 1); },
        num(v) { const n = parseFloat(v); return isNaN(n) ? 0 : n; },
        brut(l) {
            if (this.num(l.quantite_realisee) > 0 && this.num(l.prix_unitaire) > 0) {
                const b = this.num(l.quantite_realisee) * this.num(l.prix_unitaire);
                l.montant_brut = b;
                return b;
            }
            return this.num(l.montant_brut);
        },
        ligneNet(l) {
            return this.brut(l) - this.num(l.montant_avances) - this.num(l.montant_acomptes)
                - this.num(l.montant_retenues) - this.num(l.montant_garanties) - this.num(l.montant_penalites)
                + this.num(l.montant_revision_prix) + this.num(l.montant_taxes);
        },
        sum(field) { return this.lignes.reduce((t, l) => t + this.num(l[field]), 0); },
        net() { return this.lignes.reduce((t, l) => t + this.ligneNet(l), 0); },
        synthese() {
            return [
                { label: 'Montant brut', value: this.lignes.reduce((t, l) => t + this.brut(l), 0) },
                { label: 'Avances', value: this.sum('montant_avances') },
                { label: 'Acomptes', value: this.sum('montant_acomptes') },
                { label: 'Retenues', value: this.sum('montant_retenues') },
                { label: 'Garanties', value: this.sum('montant_garanties') },
                { label: 'Pénalités', value: this.sum('montant_penalites') },
                { label: 'Révision prix', value: this.sum('montant_revision_prix') },
                { label: 'Taxes', value: this.sum('montant_taxes') },
                { label: 'Montant net', value: this.net(), strong: true },
            ];
        },
        imputations() {
            const map = {};
            this.lignes.forEach(l => {
                const id = l.ligne_budgetaire_id || '—';
                map[id] = (map[id] || 0) + this.ligneNet(l);
            });
            return Object.entries(map).map(([id, value]) => ({ id, label: lbLabels[id] || '(non défini)', value }));
        },
        fmt(v) { return (this.num(v)).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
    };
}
</script>
@endpush
