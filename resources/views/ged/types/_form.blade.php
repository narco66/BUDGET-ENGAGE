@props(['typeDocument' => null, 'categories' => collect(), 'niveaux' => [], 'sortsFinaux' => []])
@php($t = $typeDocument)

<div class="grid md:grid-cols-2 gap-4">
    <div>
        <x-input-label for="code" :value="__('Code')" />
        <x-text-input id="code" name="code" type="text" class="block mt-1 w-full"
                      :value="old('code', $t?->code)" required maxlength="50" />
        <x-input-error class="mt-2" :messages="$errors->get('code')" />
    </div>

    <div>
        <x-input-label for="libelle" :value="__('Libellé')" />
        <x-text-input id="libelle" name="libelle" type="text" class="block mt-1 w-full"
                      :value="old('libelle', $t?->libelle)" required maxlength="255" />
        <x-input-error class="mt-2" :messages="$errors->get('libelle')" />
    </div>

    <div>
        <x-input-label for="categorie_id" :value="__('Catégorie')" />
        <select id="categorie_id" name="categorie_id" class="form-select mt-1">
            <option value="">{{ __('— Aucune —') }}</option>
            @foreach ($categories as $categorie)
                <option value="{{ $categorie->id }}" @selected((int) old('categorie_id', $t?->categorie_id) === $categorie->id)>{{ $categorie->libelle }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('categorie_id')" />
    </div>

    <div>
        <x-input-label for="prefixe_reference" :value="__('Préfixe de référence')" />
        <x-text-input id="prefixe_reference" name="prefixe_reference" type="text" class="block mt-1 w-full"
                      :value="old('prefixe_reference', $t?->prefixe_reference)" maxlength="30"
                      placeholder="CTR, FACT, RAP…" />
        <x-input-error class="mt-2" :messages="$errors->get('prefixe_reference')" />
    </div>

    <div>
        <x-input-label for="niveau_confidentialite_defaut" :value="__('Confidentialité par défaut')" />
        <select id="niveau_confidentialite_defaut" name="niveau_confidentialite_defaut" class="form-select mt-1">
            @foreach ($niveaux as $niveau)
                <option value="{{ $niveau->value }}"
                    @selected(old('niveau_confidentialite_defaut', $t?->niveau_confidentialite_defaut?->value ?? 'interne') === $niveau->value)>
                    {{ $niveau->libelle() }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('niveau_confidentialite_defaut')" />
    </div>

    <div>
        <x-input-label for="sort_final" :value="__('Sort final (conservation)')" />
        <select id="sort_final" name="sort_final" class="form-select mt-1">
            <option value="">{{ __('— Non défini —') }}</option>
            @foreach ($sortsFinaux as $sort)
                <option value="{{ $sort->value }}" @selected(old('sort_final', $t?->sort_final?->value) === $sort->value)>{{ $sort->libelle() }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('sort_final')" />
    </div>

    <div>
        <x-input-label for="duree_conservation_mois" :value="__('Durée de conservation (mois)')" />
        <x-text-input id="duree_conservation_mois" name="duree_conservation_mois" type="number" min="0" class="block mt-1 w-full"
                      :value="old('duree_conservation_mois', $t?->duree_conservation_mois)" />
        <x-input-error class="mt-2" :messages="$errors->get('duree_conservation_mois')" />
    </div>

    <div>
        <x-input-label for="ordre" :value="__('Ordre')" />
        <x-text-input id="ordre" name="ordre" type="number" min="0" class="block mt-1 w-full"
                      :value="old('ordre', $t?->ordre ?? 0)" />
        <x-input-error class="mt-2" :messages="$errors->get('ordre')" />
    </div>

    <div>
        <x-input-label for="statut" :value="__('Statut')" />
        <select id="statut" name="statut" class="form-select mt-1" required>
            @foreach (['actif' => __('Actif'), 'inactif' => __('Inactif')] as $val => $label)
                <option value="{{ $val }}" @selected(old('statut', $t?->statut ?? 'actif') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('statut')" />
    </div>

    <div class="flex items-center gap-2 mt-6">
        <input type="hidden" name="est_signable" value="0" />
        <input type="checkbox" id="est_signable" name="est_signable" value="1"
               class="rounded border-gray-300 text-ceeac-bright focus:ring-ceeac-bright"
               @checked(old('est_signable', $t?->est_signable)) />
        <label for="est_signable" class="text-sm text-gray-700">{{ __('Document signable électroniquement') }}</label>
    </div>

    <div class="md:col-span-2">
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="2" class="form-input mt-1">{{ old('description', $t?->description) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>
</div>
