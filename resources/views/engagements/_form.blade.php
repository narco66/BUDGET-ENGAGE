@props([
    'engagement' => null,
    'exercices' => collect(),
    'lignes' => collect(),
    'disponibles' => [],
    'departements' => collect(),
    'directions' => collect(),
    'services' => collect(),
    'fournisseurs' => collect(),
    'agents' => collect(),
    'engagementTypes' => collect(),
    'sourcesFinancement' => collect(),
    'activites' => collect(),
])
@php($e = $engagement)
@php($bt = old('beneficiaire_type', $e?->beneficiaire_type->value ?? 'fournisseur'))
@php($initialImputations = old('imputations', ($e?->imputations?->count() ? $e->imputations->map(fn ($imp) => ['id' => $imp->id, 'ligne_budgetaire_id' => $imp->ligne_budgetaire_id, 'source_financement_id' => $imp->source_financement_id, 'activite_id' => $imp->activite_id, 'centre_cout' => $imp->centre_cout, 'centre_responsabilite' => $imp->centre_responsabilite, 'montant_impute' => (string) $imp->montant_impute, 'observation' => $imp->observation])->values()->all() : ($e ? [['ligne_budgetaire_id' => $e->ligne_budgetaire_id, 'montant_impute' => (string) $e->montant, 'observation' => null]] : [['ligne_budgetaire_id' => '', 'source_financement_id' => '', 'activite_id' => '', 'centre_cout' => '', 'centre_responsabilite' => '', 'montant_impute' => '', 'observation' => '']]))))
@php($lignesOptions = $lignes->map(fn ($ligne) => ['id' => $ligne->id, 'dispo' => (float) ($disponibles[$ligne->id] ?? 0), 'label' => (($ligne->exerciceBudgetaire?->annee ?? '?').' - '.$ligne->code.' - '.\Illuminate\Support\Str::limit($ligne->libelle, 40).' (dispo: '.number_format((float) ($disponibles[$ligne->id] ?? 0), 0, ',', ' ').')')])->values()->all())
@php($engTypesData = $engagementTypes->map(fn ($t) => ['id' => $t->id, 'libelle' => $t->libelle, 'champs' => $t->champs_specifiques ?? []])->values()->all())
@php($initialTypeValeurs = old('type_valeurs', $e ? $e->typeValeurs->pluck('valeur', 'cle')->toArray() : []))
@php($sourcesOptions = $sourcesFinancement->map(fn ($s) => ['id' => $s->id, 'label' => $s->libelle])->values()->all())
@php($activitesOptions = $activites->map(fn ($a) => ['id' => $a->id, 'label' => $a->code.' — '.\Illuminate\Support\Str::limit($a->libelle, 30)])->values()->all())

<div class="space-y-6" x-data="{
    benefType: @js($bt),
    imputations: @js($initialImputations),
    lignes: @js($lignesOptions),
    engTypes: @js($engTypesData),
    engTypeId: @js((string) old('engagement_type_id', $e?->engagement_type_id ?? '')),
    typeValeurs: @js((object) $initialTypeValeurs),
    sources: @js($sourcesOptions),
    activitesList: @js($activitesOptions),
    currentTypeChamps() {
        const t = this.engTypes.find(x => String(x.id) === String(this.engTypeId));
        return t ? t.champs : [];
    },
    onTypeChange() {
        for (const c of this.currentTypeChamps()) {
            if (!(c.cle in this.typeValeurs)) this.typeValeurs[c.cle] = '';
        }
    },
    addImputation() { this.imputations.push({ ligne_budgetaire_id: '', source_financement_id: '', activite_id: '', centre_cout: '', centre_responsabilite: '', montant_impute: '', observation: '' }); },
    removeImputation(index) {
        if (this.imputations.length <= 1) return;
        this.imputations.splice(index, 1);
    },
    total() {
        return this.imputations.reduce((sum, item) => sum + (parseFloat(item.montant_impute || 0) || 0), 0);
    },
    lineDispo(id) {
        if (!id) return null;
        const l = this.lignes.find(x => String(x.id) === String(id));
        return l ? l.dispo : null;
    },
    fmt(v) {
        return new Intl.NumberFormat('fr-FR').format(Math.round(v || 0));
    }
}">
    <div class="grid md:grid-cols-2 gap-4">
        <div class="alert-info">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span>Complétez les champs obligatoires pour garantir la traçabilité et la conformité.</span>
        </div>
        <div class="alert-warning">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
            <span>Les engagements d’urgence exigent une justification détaillée.</span>
        </div>
    </div>
    <div>
        <div>
            <x-input-label for="exercice_budgetaire_id" :value="__('Exercice budgétaire')" />
            <select id="exercice_budgetaire_id" name="exercice_budgetaire_id" class="form-select mt-1" required>
                @foreach ($exercices as $ex)
                    <option value="{{ $ex->id }}" @selected(old('exercice_budgetaire_id', $e?->exercice_budgetaire_id) == $ex->id)>{{ $ex->annee }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('exercice_budgetaire_id')" />
        </div>
    </div>

    @php($typesConnus = ['achat', 'prestation', 'mission', 'salaire', 'charge'])
    @php($curType = old('type_engagement', $e?->type_engagement))
    <div>
        <x-input-label for="type_engagement" :value="__('Type d’engagement')" />
        <select id="type_engagement" name="type_engagement" class="form-select mt-1" required>
            @foreach (['achat' => __('Achat'), 'prestation' => __('Prestation'), 'mission' => __('Mission'), 'salaire' => __('Salaire'), 'charge' => __('Charge sociale / autre charge')] as $val => $label)
                <option value="{{ $val }}" @selected($curType === $val)>{{ $label }}</option>
            @endforeach
            @if ($curType && ! in_array($curType, $typesConnus, true))
                <option value="{{ $curType }}" selected>{{ $curType }}</option>
            @endif
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('type_engagement')" />
    </div>

    @if ($engagementTypes->isNotEmpty())
    <div>
        <x-input-label for="engagement_type_id" :value="__('Type d’engagement (référentiel ERP — pilote les champs spécifiques)')" />
        <select id="engagement_type_id" name="engagement_type_id" class="form-select mt-1" x-model="engTypeId" @change="onTypeChange()">
            <option value="">{{ __('— Aucun —') }}</option>
            @foreach ($engagementTypes as $type)
                <option value="{{ $type->id }}">{{ $type->code }} — {{ $type->libelle }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('engagement_type_id')" />
    </div>

    <div x-show="currentTypeChamps().length > 0" x-cloak x-init="onTypeChange()"
         class="border border-ceeac-light rounded-xl p-4 bg-ceeac-muted/40 space-y-3">
        <h3 class="font-semibold text-ceeac-primary text-sm">Champs spécifiques du type</h3>
        <x-input-error :messages="$errors->get('type_valeurs')" />
        <div class="grid md:grid-cols-2 gap-3">
            <template x-for="champ in currentTypeChamps()" :key="champ.cle">
                <div>
                    <label class="form-label" x-text="champ.libelle + (champ.requis ? ' *' : '')"></label>
                    <template x-if="champ.type === 'textarea'">
                        <textarea :name="`type_valeurs[${champ.cle}]`" x-model="typeValeurs[champ.cle]" rows="2" class="form-input"></textarea>
                    </template>
                    <template x-if="champ.type !== 'textarea'">
                        <input :type="champ.type === 'number' ? 'number' : (champ.type === 'date' ? 'date' : 'text')"
                               :name="`type_valeurs[${champ.cle}]`" x-model="typeValeurs[champ.cle]" class="form-input">
                    </template>
                </div>
            </template>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <x-input-label for="departement_id" :value="__('Département')" />
            <select id="departement_id" name="departement_id" class="form-select mt-1">
                <option value="">{{ __('— Aucun —') }}</option>
                @foreach ($departements as $dep)
                    <option value="{{ $dep->id }}" @selected(old('departement_id', $e?->departement_id) == $dep->id)>{{ $dep->libelle }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('departement_id')" />
        </div>
        <div>
            <x-input-label for="direction_id" :value="__('Direction')" />
            <select id="direction_id" name="direction_id" class="form-select mt-1">
                <option value="">{{ __('— Aucune —') }}</option>
                @foreach ($directions as $dir)
                    <option value="{{ $dir->id }}" @selected(old('direction_id', $e?->direction_id) == $dir->id)>
                        {{ $dir->departement->libelle ?? '' }} — {{ $dir->libelle }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('direction_id')" />
        </div>
        <div>
            <x-input-label for="service_id" :value="__('Service')" />
            <select id="service_id" name="service_id" class="form-select mt-1">
                <option value="">{{ __('— Aucun —') }}</option>
                @foreach ($services as $svc)
                    <option value="{{ $svc->id }}" @selected(old('service_id', $e?->service_id) == $svc->id)>
                        {{ $svc->direction->departement->libelle ?? '' }} — {{ $svc->libelle }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('service_id')" />
        </div>
    </div>

    <div>
        <x-input-label for="beneficiaire_type" :value="__('Type de bénéficiaire')" />
        <select id="beneficiaire_type" name="beneficiaire_type" class="form-select mt-1" required x-model="benefType">
            <option value="fournisseur">{{ __('Fournisseur') }}</option>
            <option value="agent">{{ __('Agent') }}</option>
            <option value="autre">{{ __('Autre') }}</option>
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('beneficiaire_type')" />
    </div>

    <template x-if="benefType === 'fournisseur'">
        <div class="space-y-2">
            <x-input-label for="beneficiaire_id" :value="__('Fournisseur')" />
            <select id="beneficiaire_id" name="beneficiaire_id" class="form-select mt-1">
                <option value="">{{ __('— Choisir —') }}</option>
                @foreach ($fournisseurs as $fournisseur)
                    <option value="{{ $fournisseur->id }}" @selected(old('beneficiaire_type', $e?->beneficiaire_type->value) === 'fournisseur' && (int) old('beneficiaire_id', $e?->beneficiaire_id) === $fournisseur->id)>{{ $fournisseur->raison_sociale }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('beneficiaire_id')" />
        </div>
    </template>

    <template x-if="benefType === 'agent'">
        <div class="space-y-2">
            <x-input-label for="beneficiaire_id_agent" :value="__('Agent')" />
            <select id="beneficiaire_id_agent" name="beneficiaire_id" class="form-select mt-1">
                <option value="">{{ __('— Choisir —') }}</option>
                @foreach ($agents as $agent)
                    <option value="{{ $agent->id }}" @selected(old('beneficiaire_type', $e?->beneficiaire_type->value) === 'agent' && (int) old('beneficiaire_id', $e?->beneficiaire_id) === $agent->id)>{{ $agent->nom }} {{ $agent->prenom }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('beneficiaire_id')" />
        </div>
    </template>

    <div x-show="benefType === 'autre'" x-cloak>
        <x-input-label for="beneficiaire_autre" :value="__('Bénéficiaire (libellé)')" />
        <textarea id="beneficiaire_autre" name="beneficiaire_autre" rows="2" class="form-input mt-1">{{ old('beneficiaire_autre', $e?->beneficiaire_autre) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('beneficiaire_autre')" />
    </div>

    <div>
        <x-input-label for="objet" :value="__('Objet')" />
        <textarea id="objet" name="objet" rows="3" class="form-input mt-1" required>{{ old('objet', $e?->objet) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('objet')" />
    </div>

    <div class="space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h3 class="font-semibold text-gray-900">Imputations budgétaires</h3>
            <button type="button" class="btn btn-secondary btn-sm" @click="addImputation()">Ajouter une ligne</button>
        </div>
        <div class="overflow-x-auto rounded-xl border border-gray-100">
            <table class="data-table min-w-full text-sm">
                <thead>
                    <tr>
                        <th>Ligne budgétaire</th>
                        <th>Montant imputé</th>
                        <th>Observation</th>
                        <th>Source fin.</th>
                        <th>Activité PAP</th>
                        <th>C. coût</th>
                        <th>C. resp.</th>
                        <th class="w-24"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, index) in imputations" :key="index">
                        <tr>
                            <td>
                                <input type="hidden" :name="`imputations[${index}][id]`" x-model="item.id">
                                <select class="form-select" :name="`imputations[${index}][ligne_budgetaire_id]`" x-model="item.ligne_budgetaire_id" required>
                                    <option value="">— Choisir —</option>
                                    <template x-for="ligne in lignes" :key="ligne.id">
                                        <option :value="ligne.id" x-text="ligne.label"></option>
                                    </template>
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0.01" class="form-input tabular-nums"
                                       :class="{ 'border-red-400 ring-1 ring-red-400': lineDispo(item.ligne_budgetaire_id) !== null && parseFloat(item.montant_impute || 0) > lineDispo(item.ligne_budgetaire_id) }"
                                       :name="`imputations[${index}][montant_impute]`" x-model="item.montant_impute" required>
                                <template x-if="lineDispo(item.ligne_budgetaire_id) !== null && parseFloat(item.montant_impute || 0) > lineDispo(item.ligne_budgetaire_id)">
                                    <p class="text-xs text-red-600 mt-1">Dépasse le disponible (<span x-text="fmt(lineDispo(item.ligne_budgetaire_id))"></span>)</p>
                                </template>
                            </td>
                            <td>
                                <input type="text" class="form-input" :name="`imputations[${index}][observation]`" x-model="item.observation" placeholder="Observation optionnelle">
                            </td>
                            <td>
                                <select class="form-select" :name="`imputations[${index}][source_financement_id]`" x-model="item.source_financement_id">
                                    <option value="">—</option>
                                    <template x-for="s in sources" :key="s.id"><option :value="s.id" x-text="s.label"></option></template>
                                </select>
                            </td>
                            <td>
                                <select class="form-select" :name="`imputations[${index}][activite_id]`" x-model="item.activite_id">
                                    <option value="">—</option>
                                    <template x-for="a in activitesList" :key="a.id"><option :value="a.id" x-text="a.label"></option></template>
                                </select>
                            </td>
                            <td><input type="text" class="form-input" :name="`imputations[${index}][centre_cout]`" x-model="item.centre_cout"></td>
                            <td><input type="text" class="form-input" :name="`imputations[${index}][centre_responsabilite]`" x-model="item.centre_responsabilite"></td>
                            <td class="text-right">
                                <button type="button" class="btn btn-danger btn-sm" @click="removeImputation(index)" :disabled="imputations.length <= 1">Supprimer</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('imputations')" />
        <x-input-error class="mt-2" :messages="$errors->get('imputations.*.ligne_budgetaire_id')" />
        <x-input-error class="mt-2" :messages="$errors->get('imputations.*.montant_impute')" />
        <div class="kpi-card flex items-center justify-between">
            <span class="text-sm text-gray-500">Montant total (auto-calculé)</span>
            <span class="text-xl font-semibold tabular-nums text-ceeac-primary" x-text="new Intl.NumberFormat('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(total())"></span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-input-label for="date_engagement" :value="__('Date d’engagement')" />
            <x-text-input id="date_engagement" type="date" name="date_engagement" class="form-input mt-1"
                :value="old('date_engagement', $e?->date_engagement?->format('Y-m-d'))" />
            <x-input-error class="mt-2" :messages="$errors->get('date_engagement')" />
        </div>
    </div>

    <div class="border border-amber-100 rounded-xl p-4 space-y-3 bg-amber-50/60">
        <div class="flex items-center gap-2">
            <input id="is_urgence" type="checkbox" name="is_urgence" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm"
                @checked(old('is_urgence', $e?->is_urgence ?? false)) />
            <x-input-label for="is_urgence" :value="__('Procédure d’urgence')" class="!mb-0" />
        </div>
        <div>
            <x-input-label for="justification_urgence" :value="__('Justification (si urgence)')" />
            <textarea id="justification_urgence" name="justification_urgence" rows="2" class="form-input mt-1">{{ old('justification_urgence', $e?->justification_urgence) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('justification_urgence')" />
        </div>
    </div>
</div>
