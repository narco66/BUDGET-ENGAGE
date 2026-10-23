@php
    $isEdit = isset($ordonnancement);
    $defaultLigne = [
        'designation' => '', 'ligne_budgetaire_id' => '', 'source_financement_id' => '', 'activite_id' => '',
        'liquidation_ligne_id' => '', 'montant_liquide' => 0, 'montant_ordonnance' => 0,
        'mode_paiement' => '', 'echeance' => '', 'banque' => '', 'compte_bancaire' => '',
    ];
    $initialLignes = old('lignes');
    if (! $initialLignes) {
        $initialLignes = $isEdit
            ? $ordonnancement->lignes->map(fn ($l) => [
                'designation' => $l->designation,
                'ligne_budgetaire_id' => $l->ligne_budgetaire_id,
                'source_financement_id' => $l->source_financement_id,
                'activite_id' => $l->activite_id,
                'liquidation_ligne_id' => $l->liquidation_ligne_id,
                'montant_liquide' => $l->montant_liquide,
                'montant_ordonnance' => $l->montant_ordonnance,
                'mode_paiement' => $l->mode_paiement,
                'echeance' => optional($l->echeance)->format('Y-m-d'),
                'banque' => $l->banque,
                'compte_bancaire' => $l->compte_bancaire,
            ])->values()->all()
            : [$defaultLigne];
    }
@endphp

<div x-data="ordonnancementForm({{ \Illuminate\Support\Js::from($initialLignes) }}, {{ \Illuminate\Support\Js::from($defaultLigne) }})">
    <div class="flex flex-wrap gap-1 border-b border-gray-200 mb-5">
        @foreach (['general' => 'Informations générales', 'ordonnancement' => 'Ordonnancement', 'calculs' => 'Calculs', 'imputations' => 'Imputations'] as $key => $label)
            <button type="button" @click="activeTab = '{{ $key }}'"
                :class="activeTab === '{{ $key }}' ? 'border-ceeac-primary text-ceeac-primary' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition">{{ $label }}</button>
        @endforeach
    </div>

    {{-- Informations générales --}}
    <div x-show="activeTab === 'general'" class="space-y-4">
        @unless ($isEdit)
            <div>
                <x-input-label for="liquidation_id" :value="__('Liquidation (certifiée)')" />
                <select id="liquidation_id" name="liquidation_id" class="form-select mt-1" required>
                    <option value="">— {{ __('Choisir une liquidation') }} —</option>
                    @foreach ($liquidations as $liq)
                        <option value="{{ $liq->id }}" @selected((string) old('liquidation_id', $liquidationPreselect ?? '') === (string) $liq->id)>
                            {{ $liq->numero }} — {{ \Illuminate\Support\Str::limit($liq->objet, 45) }} ({{ number_format((float) $liq->montant_net, 2, ',', ' ') }})
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('liquidation_id')" />
            </div>
            <div>
                <x-input-label for="mode_creation" :value="__('Mode de création')" />
                <select id="mode_creation" name="mode_creation" class="form-select mt-1">
                    @foreach (\App\Enums\ModeCreationOrdonnancement::cases() as $mode)
                        <option value="{{ $mode->value }}" @selected(old('mode_creation', 'manuel') === $mode->value)>{{ $mode->libelle() }}</option>
                    @endforeach
                </select>
            </div>
        @endunless

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="ordonnancement_type_id" :value="__('Type d’ordonnancement')" />
                <select id="ordonnancement_type_id" name="ordonnancement_type_id" class="form-select mt-1">
                    <option value="">— {{ __('Aucun') }} —</option>
                    @foreach ($ordonnancementTypes as $type)
                        <option value="{{ $type->id }}" @selected((string) old('ordonnancement_type_id', $isEdit ? $ordonnancement->ordonnancement_type_id : '') === (string) $type->id)>{{ $type->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="fournisseur_id" :value="__('Fournisseur / Bénéficiaire')" />
                <select id="fournisseur_id" name="fournisseur_id" class="form-select mt-1">
                    <option value="">— {{ __('Aucun') }} —</option>
                    @foreach ($fournisseurs as $f)
                        <option value="{{ $f->id }}" @selected((string) old('fournisseur_id', $isEdit ? $ordonnancement->fournisseur_id : '') === (string) $f->id)>{{ $f->raison_sociale }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="date_ordonnancement" :value="__('Date d’ordonnancement')" />
                <x-text-input id="date_ordonnancement" type="date" name="date_ordonnancement" class="block mt-1 w-full"
                    :value="old('date_ordonnancement', $isEdit ? optional($ordonnancement->date_ordonnancement)->format('Y-m-d') : now()->format('Y-m-d'))" required />
            </div>
            <div>
                <x-input-label for="devise" :value="__('Devise')" />
                <x-text-input id="devise" name="devise" class="block mt-1 w-full" maxlength="10" :value="old('devise', $isEdit ? $ordonnancement->devise : 'XAF')" />
            </div>
        </div>

        <div>
            <x-input-label for="objet" :value="__('Objet')" />
            <textarea id="objet" name="objet" rows="2" class="form-textarea mt-1 block w-full">{{ old('objet', $isEdit ? $ordonnancement->objet : '') }}</textarea>
        </div>

        @unless ($isEdit)
            <div>
                <x-input-label for="justification" :value="__('Justification (obligatoire si manuel / rectificatif / annulation)')" />
                <textarea id="justification" name="justification" rows="2" class="form-textarea mt-1 block w-full">{{ old('justification') }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('justification')" />
            </div>
        @endunless

        <div>
            <x-input-label for="reference" :value="__('Référence')" />
            <x-text-input id="reference" name="reference" class="block mt-1 w-full" maxlength="100" :value="old('reference', $isEdit ? $ordonnancement->reference : '')" />
        </div>
    </div>

    {{-- Ordonnancement (lignes) --}}
    <div x-show="activeTab === 'ordonnancement'" class="space-y-4" style="display:none;">
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
                    <input type="hidden" :name="`lignes[${i}][liquidation_ligne_id]`" :value="ligne.liquidation_ligne_id">
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
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                        <label class="form-label">{{ __('Montant liquidé') }}</label>
                        <input type="number" step="0.01" min="0" class="form-input mt-1 w-full tabular-nums" :name="`lignes[${i}][montant_liquide]`" x-model.number="ligne.montant_liquide">
                    </div>
                    <div>
                        <label class="form-label">{{ __('Montant à ordonnancer') }}</label>
                        <input type="number" step="0.01" min="0" class="form-input mt-1 w-full tabular-nums" :name="`lignes[${i}][montant_ordonnance]`" x-model.number="ligne.montant_ordonnance" required>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Reste') }}</label>
                        <div class="mt-2 font-mono tabular-nums" x-text="fmt(num(ligne.montant_liquide) - num(ligne.montant_ordonnance))"></div>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Mode paiement') }}</label>
                        <input type="text" class="form-input mt-1 w-full" :name="`lignes[${i}][mode_paiement]`" x-model="ligne.mode_paiement" maxlength="60" placeholder="virement…">
                    </div>
                    <div>
                        <label class="form-label">{{ __('Échéance') }}</label>
                        <input type="date" class="form-input mt-1 w-full" :name="`lignes[${i}][echeance]`" x-model="ligne.echeance">
                    </div>
                    <div>
                        <label class="form-label">{{ __('Banque') }}</label>
                        <input type="text" class="form-input mt-1 w-full" :name="`lignes[${i}][banque]`" x-model="ligne.banque" maxlength="150">
                    </div>
                    <div>
                        <label class="form-label">{{ __('Compte bancaire') }}</label>
                        <input type="text" class="form-input mt-1 w-full" :name="`lignes[${i}][compte_bancaire]`" x-model="ligne.compte_bancaire" maxlength="60">
                    </div>
                </div>
            </div>
        </template>
        <button type="button" class="btn btn-ghost" @click="addLigne()">+ {{ __('Ajouter une ligne') }}</button>
    </div>

    {{-- Calculs --}}
    <div x-show="activeTab === 'calculs'" class="space-y-2" style="display:none;">
        <div class="card"><div class="card-body grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
            <div class="flex justify-between border-b border-gray-100 py-1"><span class="text-gray-500">Total liquidé</span><span class="font-mono tabular-nums" x-text="fmt(sum('montant_liquide'))"></span></div>
            <div class="flex justify-between border-b border-gray-100 py-1"><span class="text-gray-500">Total à ordonnancer</span><span class="font-mono tabular-nums font-bold text-ceeac-primary" x-text="fmt(sum('montant_ordonnance'))"></span></div>
            <div class="flex justify-between border-b border-gray-100 py-1"><span class="text-gray-500">Reste</span><span class="font-mono tabular-nums" x-text="fmt(sum('montant_liquide') - sum('montant_ordonnance'))"></span></div>
        </div></div>
        <p class="text-xs text-gray-400">{{ __('Ordonnançable = Liquidé − déjà ordonnancé. Contrôle financier et recalcul définitif au serveur.') }}</p>
    </div>

    {{-- Imputations --}}
    <div x-show="activeTab === 'imputations'" class="space-y-2" style="display:none;">
        <table class="data-table">
            <thead><tr><th>{{ __('Ligne budgétaire') }}</th><th class="text-right">{{ __('Ordonnancé') }}</th></tr></thead>
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
function ordonnancementForm(initial, blank) {
    const lbLabels = @json($lignesBudgetaires->mapWithKeys(fn ($l) => [$l->id => $l->code])->all());
    return {
        activeTab: 'general',
        lignes: initial.length ? initial.map(l => ({ ...blank, ...l })) : [{ ...blank }],
        addLigne() { this.lignes.push({ ...blank }); },
        removeLigne(i) { if (this.lignes.length > 1) this.lignes.splice(i, 1); },
        num(v) { const n = parseFloat(v); return isNaN(n) ? 0 : n; },
        sum(field) { return this.lignes.reduce((t, l) => t + this.num(l[field]), 0); },
        imputations() {
            const map = {};
            this.lignes.forEach(l => {
                const id = l.ligne_budgetaire_id || '—';
                map[id] = (map[id] || 0) + this.num(l.montant_ordonnance);
            });
            return Object.entries(map).map(([id, value]) => ({ id, label: lbLabels[id] || '(non défini)', value }));
        },
        fmt(v) { return (this.num(v)).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
    };
}
</script>
@endpush
