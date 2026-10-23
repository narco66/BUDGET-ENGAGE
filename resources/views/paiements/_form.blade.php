@php
    $isEdit = isset($paiement);
    $defaultLigne = [
        'designation' => '', 'ligne_budgetaire_id' => '', 'source_financement_id' => '', 'activite_id' => '',
        'ordonnancement_ligne_id' => '', 'montant_ordonnance' => 0, 'montant_paye' => 0,
        'frais_bancaires' => 0, 'reference_bancaire' => '',
    ];
    $initialLignes = old('lignes');
    if (! $initialLignes) {
        $initialLignes = $isEdit
            ? $paiement->lignes->map(fn ($l) => [
                'designation' => $l->designation,
                'ligne_budgetaire_id' => $l->ligne_budgetaire_id,
                'source_financement_id' => $l->source_financement_id,
                'activite_id' => $l->activite_id,
                'ordonnancement_ligne_id' => $l->ordonnancement_ligne_id,
                'montant_ordonnance' => $l->montant_ordonnance,
                'montant_paye' => $l->montant_paye,
                'frais_bancaires' => $l->frais_bancaires,
                'reference_bancaire' => $l->reference_bancaire,
            ])->values()->all()
            : [$defaultLigne];
    }
@endphp

<div x-data="paiementForm({{ \Illuminate\Support\Js::from($initialLignes) }}, {{ \Illuminate\Support\Js::from($defaultLigne) }})">
    <div class="flex flex-wrap gap-1 border-b border-gray-200 mb-5">
        @foreach (['general' => 'Informations générales', 'tresorerie' => 'Trésorerie', 'paiement' => 'Paiement', 'calculs' => 'Calculs'] as $key => $label)
            <button type="button" @click="activeTab = '{{ $key }}'"
                :class="activeTab === '{{ $key }}' ? 'border-ceeac-primary text-ceeac-primary' : 'border-transparent text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition">{{ $label }}</button>
        @endforeach
    </div>

    {{-- Informations générales --}}
    <div x-show="activeTab === 'general'" class="space-y-4">
        @unless ($isEdit)
            <div>
                <x-input-label for="ordonnancement_id" :value="__('Ordonnancement (mandaté)')" />
                <select id="ordonnancement_id" name="ordonnancement_id" class="form-select mt-1" required>
                    <option value="">— {{ __('Choisir un ordonnancement') }} —</option>
                    @foreach ($ordonnancements as $ord)
                        <option value="{{ $ord->id }}" @selected((string) old('ordonnancement_id', $ordonnancementPreselect ?? '') === (string) $ord->id)>
                            {{ $ord->numero }} — {{ \Illuminate\Support\Str::limit($ord->objet, 45) }} ({{ number_format((float) $ord->montant_ordonnance, 2, ',', ' ') }})
                        </option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('ordonnancement_id')" />
            </div>
            <div>
                <x-input-label for="mode_creation" :value="__('Mode de création')" />
                <select id="mode_creation" name="mode_creation" class="form-select mt-1">
                    @foreach (\App\Enums\ModeCreationPaiement::cases() as $mode)
                        <option value="{{ $mode->value }}" @selected(old('mode_creation', 'manuel') === $mode->value)>{{ $mode->libelle() }}</option>
                    @endforeach
                </select>
            </div>
        @endunless

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="paiement_type_id" :value="__('Type de paiement')" />
                <select id="paiement_type_id" name="paiement_type_id" class="form-select mt-1">
                    <option value="">— {{ __('Aucun') }} —</option>
                    @foreach ($paiementTypes as $type)
                        <option value="{{ $type->id }}" @selected((string) old('paiement_type_id', $isEdit ? $paiement->paiement_type_id : '') === (string) $type->id)>{{ $type->libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="mode_paiement" :value="__('Mode de paiement')" />
                <x-text-input id="mode_paiement" name="mode_paiement" class="block mt-1 w-full" maxlength="60"
                    :value="old('mode_paiement', $isEdit ? $paiement->mode_paiement : '')" placeholder="virement, chèque…" />
            </div>
            <div>
                <x-input-label for="date_paiement" :value="__('Date du paiement')" />
                <x-text-input id="date_paiement" type="date" name="date_paiement" class="block mt-1 w-full"
                    :value="old('date_paiement', $isEdit ? optional($paiement->date_paiement)->format('Y-m-d') : now()->format('Y-m-d'))" />
            </div>
            <div>
                <x-input-label for="reference_paiement" :value="__('Référence')" />
                <x-text-input id="reference_paiement" name="reference_paiement" class="block mt-1 w-full" maxlength="100"
                    :value="old('reference_paiement', $isEdit ? $paiement->reference_paiement : '')" />
            </div>
        </div>

        <div>
            <x-input-label for="objet" :value="__('Objet')" />
            <textarea id="objet" name="objet" rows="2" class="form-textarea mt-1 block w-full">{{ old('objet', $isEdit ? $paiement->objet : '') }}</textarea>
        </div>

        @unless ($isEdit)
            <div>
                <x-input-label for="justification" :value="__('Justification (obligatoire si manuel / rectificatif / annulation)')" />
                <textarea id="justification" name="justification" rows="2" class="form-textarea mt-1 block w-full">{{ old('justification') }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('justification')" />
            </div>
        @endunless
    </div>

    {{-- Trésorerie --}}
    <div x-show="activeTab === 'tresorerie'" class="grid grid-cols-1 md:grid-cols-2 gap-4" style="display:none;">
        <div>
            <x-input-label for="paiement_banque_id" :value="__('Compte bancaire')" />
            <select id="paiement_banque_id" name="paiement_banque_id" class="form-select mt-1">
                <option value="">— {{ __('Aucun (espèces / compensation)') }} —</option>
                @foreach ($banques as $b)
                    <option value="{{ $b->id }}" @selected((string) old('paiement_banque_id', $isEdit ? $paiement->paiement_banque_id : '') === (string) $b->id)>{{ $b->code }} — {{ $b->libelle }} ({{ $b->devise }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="priorite" :value="__('Priorité')" />
            <select id="priorite" name="priorite" class="form-select mt-1">
                @foreach (['' => '—', 'normale' => 'Normale', 'haute' => 'Haute', 'urgente' => 'Urgente'] as $v => $lib)
                    <option value="{{ $v }}" @selected(old('priorite', $isEdit ? $paiement->priorite : '') === $v)>{{ $lib }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <x-input-label for="date_prevue" :value="__('Date prévue de paiement')" />
            <x-text-input id="date_prevue" type="date" name="date_prevue" class="block mt-1 w-full"
                :value="old('date_prevue', $isEdit ? optional($paiement->date_prevue)->format('Y-m-d') : '')" />
        </div>
        <div>
            <x-input-label for="frais_bancaires" :value="__('Frais bancaires')" />
            <x-text-input id="frais_bancaires" type="number" step="0.01" min="0" name="frais_bancaires" class="block mt-1 w-full tabular-nums"
                :value="old('frais_bancaires', $isEdit ? $paiement->frais_bancaires : '0')" />
        </div>
        <div>
            <x-input-label for="devise" :value="__('Devise d’origine')" />
            <x-text-input id="devise" name="devise" class="block mt-1 w-full" maxlength="10" :value="old('devise', $isEdit ? $paiement->devise : 'XAF')" />
        </div>
        <div>
            <x-input-label for="devise_paiement" :value="__('Devise de paiement (si différente)')" />
            <x-text-input id="devise_paiement" name="devise_paiement" class="block mt-1 w-full" maxlength="10" :value="old('devise_paiement', $isEdit ? $paiement->devise_paiement : '')" />
        </div>
        <div>
            <x-input-label for="taux_change" :value="__('Taux de change')" />
            <x-text-input id="taux_change" type="number" step="0.000001" min="0" name="taux_change" class="block mt-1 w-full tabular-nums" :value="old('taux_change', $isEdit ? $paiement->taux_change : '')" />
        </div>
    </div>

    {{-- Paiement (lignes) --}}
    <div x-show="activeTab === 'paiement'" class="space-y-4" style="display:none;">
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
                    <input type="hidden" :name="`lignes[${i}][ordonnancement_ligne_id]`" :value="ligne.ordonnancement_ligne_id">
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
                        <label class="form-label">{{ __('Montant ordonnancé') }}</label>
                        <input type="number" step="0.01" min="0" class="form-input mt-1 w-full tabular-nums" :name="`lignes[${i}][montant_ordonnance]`" x-model.number="ligne.montant_ordonnance">
                    </div>
                    <div>
                        <label class="form-label">{{ __('Montant à payer') }}</label>
                        <input type="number" step="0.01" min="0" class="form-input mt-1 w-full tabular-nums" :name="`lignes[${i}][montant_paye]`" x-model.number="ligne.montant_paye" required>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Reste') }}</label>
                        <div class="mt-2 font-mono tabular-nums" x-text="fmt(num(ligne.montant_ordonnance) - num(ligne.montant_paye))"></div>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Réf. bancaire') }}</label>
                        <input type="text" class="form-input mt-1 w-full" :name="`lignes[${i}][reference_bancaire]`" x-model="ligne.reference_bancaire" maxlength="100">
                    </div>
                </div>
            </div>
        </template>
        <button type="button" class="btn btn-ghost" @click="addLigne()">+ {{ __('Ajouter une ligne') }}</button>
    </div>

    {{-- Calculs --}}
    <div x-show="activeTab === 'calculs'" class="space-y-2" style="display:none;">
        <div class="card"><div class="card-body grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
            <div class="flex justify-between border-b border-gray-100 py-1"><span class="text-gray-500">Total ordonnancé</span><span class="font-mono tabular-nums" x-text="fmt(sum('montant_ordonnance'))"></span></div>
            <div class="flex justify-between border-b border-gray-100 py-1"><span class="text-gray-500">Total à payer</span><span class="font-mono tabular-nums font-bold text-ceeac-primary" x-text="fmt(sum('montant_paye'))"></span></div>
            <div class="flex justify-between border-b border-gray-100 py-1"><span class="text-gray-500">Reste à payer</span><span class="font-mono tabular-nums" x-text="fmt(sum('montant_ordonnance') - sum('montant_paye'))"></span></div>
        </div></div>
        <p class="text-xs text-gray-400">{{ __('À payer = Ordonnancé − déjà payé. Contrôle trésorerie et recalcul définitif au serveur.') }}</p>
    </div>
</div>

@push('scripts')
<script>
function paiementForm(initial, blank) {
    return {
        activeTab: 'general',
        lignes: initial.length ? initial.map(l => ({ ...blank, ...l })) : [{ ...blank }],
        addLigne() { this.lignes.push({ ...blank }); },
        removeLigne(i) { if (this.lignes.length > 1) this.lignes.splice(i, 1); },
        num(v) { const n = parseFloat(v); return isNaN(n) ? 0 : n; },
        sum(field) { return this.lignes.reduce((t, l) => t + this.num(l[field]), 0); },
        fmt(v) { return (this.num(v)).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
    };
}
</script>
@endpush
