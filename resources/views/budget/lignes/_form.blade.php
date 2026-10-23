@props([
    'ligne' => null,
    'exercices' => collect(),
    'departements' => collect(),
    'directions' => collect(),
    'services' => collect(),
    'natures' => collect(),
    'sources' => collect(),
    'papLignes' => collect(),
])
@php($ln = $ligne)

<div class="space-y-4">
    <div>
        <x-input-label for="exercice_budgetaire_id" :value="__('Exercice budgétaire')" />
        <select id="exercice_budgetaire_id" name="exercice_budgetaire_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
            @foreach ($exercices as $exercice)
                <option value="{{ $exercice->id }}" @selected(old('exercice_budgetaire_id', $ln?->exercice_budgetaire_id) == $exercice->id)>
                    {{ $exercice->annee }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('exercice_budgetaire_id')" />
    </div>
    <div>
        <x-input-label for="code" :value="__('Code ligne')" />
        <x-text-input id="code" name="code" class="block mt-1 w-full font-mono" :value="old('code', $ln?->code)" required maxlength="100" />
        <x-input-error class="mt-2" :messages="$errors->get('code')" />
    </div>
    <div>
        <x-input-label for="libelle" :value="__('Libellé')" />
        <x-text-input id="libelle" name="libelle" class="block mt-1 w-full" :value="old('libelle', $ln?->libelle)" required />
        <x-input-error class="mt-2" :messages="$errors->get('libelle')" />
    </div>
    <div>
        <x-input-label for="nature_depense_id" :value="__('Nature de dépense')" />
        <select id="nature_depense_id" name="nature_depense_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
            <option value="">{{ __('— Choisir —') }}</option>
            @foreach ($natures as $nature)
                <option value="{{ $nature->id }}" @selected(old('nature_depense_id', $ln?->nature_depense_id) == $nature->id)>
                    {{ $nature->code }} — {{ $nature->libelle }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('nature_depense_id')" />
    </div>
    <div>
        <x-input-label for="source_financement_id" :value="__('Source de financement')" />
        <select id="source_financement_id" name="source_financement_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
            <option value="">{{ __('— Aucune —') }}</option>
            @foreach ($sources as $src)
                <option value="{{ $src->id }}" @selected(old('source_financement_id', $ln?->source_financement_id) == $src->id)>
                    {{ $src->code }} — {{ $src->libelle }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('source_financement_id')" />
    </div>
    @if ($papLignes->isNotEmpty())
        <div>
            <x-input-label for="pap_ligne_budgetaire_id" :value="__('Ligne PAP rattachée')" />
            <select id="pap_ligne_budgetaire_id" name="pap_ligne_budgetaire_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                <option value="">{{ __('— Aucune —') }}</option>
                @foreach ($papLignes as $pap)
                    <option value="{{ $pap->id }}" @selected(old('pap_ligne_budgetaire_id', $ln?->pap_ligne_budgetaire_id) == $pap->id)>
                        [{{ $pap->exerciceBudgetaire?->annee }}] {{ $pap->code }} — {{ \Illuminate\Support\Str::limit($pap->libelle, 70) }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('pap_ligne_budgetaire_id')" />
        </div>
    @endif
    <div>
        <x-input-label for="departement_id" :value="__('Département')" />
        <select id="departement_id" name="departement_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
            <option value="">{{ __('— Aucun —') }}</option>
            @foreach ($departements as $dep)
                <option value="{{ $dep->id }}" @selected(old('departement_id', $ln?->departement_id) == $dep->id)>{{ $dep->libelle }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('departement_id')" />
    </div>
    <div>
        <x-input-label for="direction_id" :value="__('Direction')" />
        <select id="direction_id" name="direction_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
            <option value="">{{ __('— Aucune —') }}</option>
            @foreach ($directions as $dir)
                <option value="{{ $dir->id }}" @selected(old('direction_id', $ln?->direction_id) == $dir->id)>
                    {{ $dir->departement->libelle ?? '' }} — {{ $dir->libelle }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('direction_id')" />
    </div>
    <div>
        <x-input-label for="service_id" :value="__('Service')" />
        <select id="service_id" name="service_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
            <option value="">{{ __('— Aucun —') }}</option>
            @foreach ($services as $svc)
                <option value="{{ $svc->id }}" @selected(old('service_id', $ln?->service_id) == $svc->id)>
                    {{ $svc->direction->departement->libelle ?? '' }} — {{ $svc->direction->libelle ?? '' }} — {{ $svc->libelle }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('service_id')" />
    </div>
    @php($montants = ['montant_vote', 'montant_revise', 'montant_engage', 'montant_liquide', 'montant_ordonnance', 'montant_paye'])
    @php($labels = [
        'montant_vote' => __('Montant voté'),
        'montant_revise' => __('Montant révisé'),
        'montant_engage' => __('Engagé'),
        'montant_liquide' => __('Liquidé'),
        'montant_ordonnance' => __('Ordonnancé'),
        'montant_paye' => __('Payé'),
    ])
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach ($montants as $field)
            <div>
                <x-input-label :for="$field" :value="$labels[$field]" />
                <x-text-input :id="$field" type="number" step="0.01" min="0" :name="$field" class="block mt-1 w-full tabular-nums"
                    :value="old($field, $ln ? $ln->{$field} : ($field === 'montant_vote' || $field === 'montant_revise' ? '' : '0'))" @required($field === 'montant_vote' || $field === 'montant_revise') />
                <x-input-error class="mt-2" :messages="$errors->get($field)" />
            </div>
        @endforeach
    </div>
    <div>
        <x-input-label for="statut" :value="__('Statut')" />
        <select id="statut" name="statut" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
            @foreach (['active' => __('Active'), 'suspendue' => __('Suspendue'), 'cloturee' => __('Clôturée')] as $val => $label)
                <option value="{{ $val }}" @selected(old('statut', $ln?->statut ?? 'active') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('statut')" />
    </div>
</div>
