@props([
    'engagement' => null,
    'exercices' => collect(),
    'lignes' => collect(),
    'departements' => collect(),
    'directions' => collect(),
    'services' => collect(),
    'fournisseurs' => collect(),
    'agents' => collect(),
])
@php($e = $engagement)
@php($bt = old('beneficiaire_type', $e?->beneficiaire_type->value ?? 'fournisseur'))

<div class="space-y-6" x-data="{ benefType: @js($bt) }">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-input-label for="exercice_budgetaire_id" :value="__('Exercice budgétaire')" />
            <select id="exercice_budgetaire_id" name="exercice_budgetaire_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                @foreach ($exercices as $ex)
                    <option value="{{ $ex->id }}" @selected(old('exercice_budgetaire_id', $e?->exercice_budgetaire_id) == $ex->id)>{{ $ex->annee }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('exercice_budgetaire_id')" />
        </div>
        <div>
            <x-input-label for="ligne_budgetaire_id" :value="__('Ligne budgétaire')" />
            <select id="ligne_budgetaire_id" name="ligne_budgetaire_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                @foreach ($lignes as $ligne)
                    <option value="{{ $ligne->id }}" @selected(old('ligne_budgetaire_id', $e?->ligne_budgetaire_id) == $ligne->id)>
                        {{ $ligne->exerciceBudgetaire?->annee ?? '?' }} — {{ $ligne->code }} — {{ $ligne->libelle }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('ligne_budgetaire_id')" />
        </div>
    </div>

    @php($typesConnus = ['achat', 'prestation', 'mission', 'salaire', 'charge'])
    @php($curType = old('type_engagement', $e?->type_engagement))
    <div>
        <x-input-label for="type_engagement" :value="__('Type d’engagement')" />
        <select id="type_engagement" name="type_engagement" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
            @foreach (['achat' => __('Achat'), 'prestation' => __('Prestation'), 'mission' => __('Mission'), 'salaire' => __('Salaire'), 'charge' => __('Charge sociale / autre charge')] as $val => $label)
                <option value="{{ $val }}" @selected($curType === $val)>{{ $label }}</option>
            @endforeach
            @if ($curType && ! in_array($curType, $typesConnus, true))
                <option value="{{ $curType }}" selected>{{ $curType }}</option>
            @endif
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('type_engagement')" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <x-input-label for="departement_id" :value="__('Département')" />
            <select id="departement_id" name="departement_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                <option value="">{{ __('— Aucun —') }}</option>
                @foreach ($departements as $dep)
                    <option value="{{ $dep->id }}" @selected(old('departement_id', $e?->departement_id) == $dep->id)>{{ $dep->libelle }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('departement_id')" />
        </div>
        <div>
            <x-input-label for="direction_id" :value="__('Direction')" />
            <select id="direction_id" name="direction_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
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
            <select id="service_id" name="service_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
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
        <select id="beneficiaire_type" name="beneficiaire_type" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required x-model="benefType">
            <option value="fournisseur">{{ __('Fournisseur') }}</option>
            <option value="agent">{{ __('Agent') }}</option>
            <option value="autre">{{ __('Autre') }}</option>
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('beneficiaire_type')" />
    </div>

    <template x-if="benefType === 'fournisseur'">
        <div class="space-y-2">
            <x-input-label for="beneficiaire_id" :value="__('Fournisseur')" />
            <select id="beneficiaire_id" name="beneficiaire_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
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
            <select id="beneficiaire_id_agent" name="beneficiaire_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
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
        <textarea id="beneficiaire_autre" name="beneficiaire_autre" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('beneficiaire_autre', $e?->beneficiaire_autre) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('beneficiaire_autre')" />
    </div>

    <div>
        <x-input-label for="objet" :value="__('Objet')" />
        <textarea id="objet" name="objet" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>{{ old('objet', $e?->objet) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('objet')" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-input-label for="montant" :value="__('Montant')" />
            <x-text-input id="montant" type="number" name="montant" step="0.01" min="0.01" class="block mt-1 w-full tabular-nums"
                :value="old('montant', $e?->montant)" required />
            <x-input-error class="mt-2" :messages="$errors->get('montant')" />
        </div>
        <div>
            <x-input-label for="date_engagement" :value="__('Date d’engagement')" />
            <x-text-input id="date_engagement" type="date" name="date_engagement" class="block mt-1 w-full"
                :value="old('date_engagement', $e?->date_engagement?->format('Y-m-d'))" />
            <x-input-error class="mt-2" :messages="$errors->get('date_engagement')" />
        </div>
    </div>

    <div class="border border-gray-200 rounded-lg p-4 space-y-3 bg-gray-50">
        <div class="flex items-center gap-2">
            <input id="is_urgence" type="checkbox" name="is_urgence" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm"
                @checked(old('is_urgence', $e?->is_urgence ?? false)) />
            <x-input-label for="is_urgence" :value="__('Procédure d’urgence')" class="!mb-0" />
        </div>
        <div>
            <x-input-label for="justification_urgence" :value="__('Justification (si urgence)')" />
            <textarea id="justification_urgence" name="justification_urgence" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('justification_urgence', $e?->justification_urgence) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('justification_urgence')" />
        </div>
    </div>
</div>
