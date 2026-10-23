@props(['categorie' => null, 'parents' => collect()])
@php($c = $categorie)

<div class="grid md:grid-cols-2 gap-4">
    <div>
        <x-input-label for="code" :value="__('Code')" />
        <x-text-input id="code" name="code" type="text" class="block mt-1 w-full"
                      :value="old('code', $c?->code)" required maxlength="50" />
        <x-input-error class="mt-2" :messages="$errors->get('code')" />
    </div>

    <div>
        <x-input-label for="libelle" :value="__('Libellé')" />
        <x-text-input id="libelle" name="libelle" type="text" class="block mt-1 w-full"
                      :value="old('libelle', $c?->libelle)" required maxlength="255" />
        <x-input-error class="mt-2" :messages="$errors->get('libelle')" />
    </div>

    <div>
        <x-input-label for="parent_id" :value="__('Catégorie parente')" />
        <select id="parent_id" name="parent_id" class="form-select mt-1">
            <option value="">{{ __('— Aucune (racine) —') }}</option>
            @foreach ($parents as $parent)
                <option value="{{ $parent->id }}" @selected((int) old('parent_id', $c?->parent_id) === $parent->id)>{{ $parent->libelle }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('parent_id')" />
    </div>

    <div>
        <x-input-label for="ordre" :value="__('Ordre')" />
        <x-text-input id="ordre" name="ordre" type="number" min="0" class="block mt-1 w-full"
                      :value="old('ordre', $c?->ordre ?? 0)" />
        <x-input-error class="mt-2" :messages="$errors->get('ordre')" />
    </div>

    <div>
        <x-input-label for="couleur" :value="__('Couleur (optionnel)')" />
        <x-text-input id="couleur" name="couleur" type="text" class="block mt-1 w-full"
                      :value="old('couleur', $c?->couleur)" maxlength="20" placeholder="#1E3A8A" />
        <x-input-error class="mt-2" :messages="$errors->get('couleur')" />
    </div>

    <div>
        <x-input-label for="statut" :value="__('Statut')" />
        <select id="statut" name="statut" class="form-select mt-1" required>
            @foreach (['actif' => __('Actif'), 'inactif' => __('Inactif')] as $val => $label)
                <option value="{{ $val }}" @selected(old('statut', $c?->statut ?? 'actif') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('statut')" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="2" class="form-input mt-1">{{ old('description', $c?->description) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>
</div>
