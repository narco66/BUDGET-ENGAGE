@props([
    'document' => null,
    'categories' => collect(),
    'types' => collect(),
    'dossiers' => collect(),
    'exercices' => collect(),
    'niveaux' => [],
])
@php($d = $document)

<div class="space-y-5">
    <div class="grid md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <x-input-label for="titre" :value="__('Titre du document')" />
            <x-text-input id="titre" name="titre" type="text" class="block mt-1 w-full"
                          :value="old('titre', $d?->titre)" required maxlength="255" />
            <x-input-error class="mt-2" :messages="$errors->get('titre')" />
        </div>

        <div>
            <x-input-label for="type_document_id" :value="__('Type de document')" />
            <select id="type_document_id" name="type_document_id" class="form-select mt-1">
                <option value="">{{ __('— Aucun —') }}</option>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}" @selected((int) old('type_document_id', $d?->type_document_id) === $type->id)>
                        {{ $type->libelle }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('type_document_id')" />
        </div>

        <div>
            <x-input-label for="categorie_id" :value="__('Catégorie')" />
            <select id="categorie_id" name="categorie_id" class="form-select mt-1">
                <option value="">{{ __('— Aucune —') }}</option>
                @foreach ($categories as $categorie)
                    <option value="{{ $categorie->id }}" @selected((int) old('categorie_id', $d?->categorie_id) === $categorie->id)>
                        {{ $categorie->libelle }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('categorie_id')" />
        </div>

        <div>
            <x-input-label for="dossier_id" :value="__('Dossier de classement')" />
            <select id="dossier_id" name="dossier_id" class="form-select mt-1">
                <option value="">{{ __('— Aucun —') }}</option>
                @foreach ($dossiers as $dossier)
                    <option value="{{ $dossier->id }}" @selected((int) old('dossier_id', $d?->dossier_id) === $dossier->id)>
                        {{ $dossier->libelle }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('dossier_id')" />
        </div>

        <div>
            <x-input-label for="niveau_confidentialite" :value="__('Niveau de confidentialité')" />
            <select id="niveau_confidentialite" name="niveau_confidentialite" class="form-select mt-1">
                @foreach ($niveaux as $niveau)
                    <option value="{{ $niveau->value }}"
                        @selected(old('niveau_confidentialite', $d?->niveau_confidentialite?->value ?? 'interne') === $niveau->value)>
                        {{ $niveau->libelle() }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('niveau_confidentialite')" />
        </div>

        <div>
            <x-input-label for="date_document" :value="__('Date du document')" />
            <x-text-input id="date_document" name="date_document" type="date" class="block mt-1 w-full"
                          :value="old('date_document', $d?->date_document?->format('Y-m-d'))" />
            <x-input-error class="mt-2" :messages="$errors->get('date_document')" />
        </div>

        <div>
            <x-input-label for="date_expiration" :value="__('Date d\'expiration')" />
            <x-text-input id="date_expiration" name="date_expiration" type="date" class="block mt-1 w-full"
                          :value="old('date_expiration', $d?->date_expiration?->format('Y-m-d'))" />
            <x-input-error class="mt-2" :messages="$errors->get('date_expiration')" />
        </div>

        <div>
            <x-input-label for="exercice_budgetaire_id" :value="__('Exercice budgétaire')" />
            <select id="exercice_budgetaire_id" name="exercice_budgetaire_id" class="form-select mt-1">
                <option value="">{{ __('— Aucun —') }}</option>
                @foreach ($exercices as $exercice)
                    <option value="{{ $exercice->id }}" @selected((int) old('exercice_budgetaire_id', $d?->exercice_budgetaire_id) === $exercice->id)>
                        {{ $exercice->annee }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('exercice_budgetaire_id')" />
        </div>

        <div>
            <x-input-label for="module_source" :value="__('Module métier rattaché')" />
            <x-text-input id="module_source" name="module_source" type="text" class="block mt-1 w-full"
                          :value="old('module_source', $d?->module_source)" maxlength="100"
                          placeholder="engagement, liquidation, paiement…" />
            <x-input-error class="mt-2" :messages="$errors->get('module_source')" />
        </div>

        <div>
            <x-input-label for="module_source_id" :value="__('Identifiant métier (optionnel)')" />
            <x-text-input id="module_source_id" name="module_source_id" type="number" class="block mt-1 w-full"
                          :value="old('module_source_id', $d?->module_source_id)" />
            <x-input-error class="mt-2" :messages="$errors->get('module_source_id')" />
        </div>

        <div class="md:col-span-2">
            <x-input-label for="description" :value="__('Description')" />
            <textarea id="description" name="description" rows="3" class="form-input mt-1">{{ old('description', $d?->description) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('description')" />
        </div>
    </div>
</div>
