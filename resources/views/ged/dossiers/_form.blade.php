@props(['dossier' => null, 'parents' => collect(), 'categories' => collect(), 'exercices' => collect()])
@php($o = $dossier)

<div class="grid md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <x-input-label for="libelle" :value="__('Libellé du dossier')" />
        <x-text-input id="libelle" name="libelle" type="text" class="block mt-1 w-full"
                      :value="old('libelle', $o?->libelle)" required maxlength="255" />
        <x-input-error class="mt-2" :messages="$errors->get('libelle')" />
    </div>

    <div>
        <x-input-label for="code" :value="__('Code (optionnel)')" />
        <x-text-input id="code" name="code" type="text" class="block mt-1 w-full"
                      :value="old('code', $o?->code)" maxlength="100" />
        <x-input-error class="mt-2" :messages="$errors->get('code')" />
    </div>

    <div>
        <x-input-label for="reference" :value="__('Référence (optionnel)')" />
        <x-text-input id="reference" name="reference" type="text" class="block mt-1 w-full"
                      :value="old('reference', $o?->reference)" maxlength="150" />
        <x-input-error class="mt-2" :messages="$errors->get('reference')" />
    </div>

    <div>
        <x-input-label for="parent_id" :value="__('Dossier parent')" />
        <select id="parent_id" name="parent_id" class="form-select mt-1">
            <option value="">{{ __('— Aucun (racine) —') }}</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected((int) old('parent_id', $o?->parent_id) === $parent->id)>{{ $parent->libelle }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('parent_id')" />
    </div>

    <div>
        <x-input-label for="categorie_id" :value="__('Catégorie')" />
        <select id="categorie_id" name="categorie_id" class="form-select mt-1">
            <option value="">{{ __('— Aucune —') }}</option>
            @foreach ($categories as $categorie)
                <option value="{{ $categorie->id }}" @selected((int) old('categorie_id', $o?->categorie_id) === $categorie->id)>{{ $categorie->libelle }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('categorie_id')" />
    </div>

    <div>
        <x-input-label for="exercice_budgetaire_id" :value="__('Exercice budgétaire')" />
        <select id="exercice_budgetaire_id" name="exercice_budgetaire_id" class="form-select mt-1">
            <option value="">{{ __('— Aucun —') }}</option>
            @foreach ($exercices as $exercice)
                <option value="{{ $exercice->id }}" @selected((int) old('exercice_budgetaire_id', $o?->exercice_budgetaire_id) === $exercice->id)>{{ $exercice->annee }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('exercice_budgetaire_id')" />
    </div>

    <div>
        <x-input-label for="statut" :value="__('Statut')" />
        <select id="statut" name="statut" class="form-select mt-1" required>
            @foreach (['actif' => __('Actif'), 'inactif' => __('Inactif')] as $val => $label)
                <option value="{{ $val }}" @selected(old('statut', $o?->statut ?? 'actif') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('statut')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="2" class="form-input mt-1">{{ old('description', $o?->description) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>
</div>
