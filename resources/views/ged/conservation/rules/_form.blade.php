@props(['rule' => null, 'types' => collect(), 'sortsFinaux' => []])
@php($r = $rule)

<div class="grid md:grid-cols-2 gap-4">
    <div>
        <x-input-label for="code" :value="__('Code')" />
        <x-text-input id="code" name="code" type="text" class="block mt-1 w-full"
                      :value="old('code', $r?->code)" required maxlength="50" />
        <x-input-error class="mt-2" :messages="$errors->get('code')" />
    </div>
    <div>
        <x-input-label for="libelle" :value="__('Libellé')" />
        <x-text-input id="libelle" name="libelle" type="text" class="block mt-1 w-full"
                      :value="old('libelle', $r?->libelle)" required maxlength="255" />
        <x-input-error class="mt-2" :messages="$errors->get('libelle')" />
    </div>
    <div>
        <x-input-label for="type_document_id" :value="__('Type de document (optionnel)')" />
        <select id="type_document_id" name="type_document_id" class="form-select mt-1">
            <option value="">{{ __('— Tous —') }}</option>
            @foreach ($types as $type)
                <option value="{{ $type->id }}" @selected((int) old('type_document_id', $r?->type_document_id) === $type->id)>{{ $type->libelle }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="duree_conservation_mois" :value="__('Durée de conservation (mois)')" />
        <x-text-input id="duree_conservation_mois" name="duree_conservation_mois" type="number" min="0" class="block mt-1 w-full"
                      :value="old('duree_conservation_mois', $r?->duree_conservation_mois ?? 0)" required />
        <x-input-error class="mt-2" :messages="$errors->get('duree_conservation_mois')" />
    </div>
    <div>
        <x-input-label for="sort_final" :value="__('Sort final')" />
        <select id="sort_final" name="sort_final" class="form-select mt-1" required>
            @foreach ($sortsFinaux as $sort)
                <option value="{{ $sort->value }}" @selected(old('sort_final', $r?->sort_final?->value) === $sort->value)>{{ $sort->libelle() }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('sort_final')" />
    </div>
    <div>
        <x-input-label for="declenchement" :value="__('Déclenchement')" />
        <select id="declenchement" name="declenchement" class="form-select mt-1" required>
            @foreach (['date_document' => 'Date du document', 'date_archivage' => 'Date d\'archivage', 'date_depot' => 'Date de dépôt'] as $val => $label)
                <option value="{{ $val }}" @selected(old('declenchement', $r?->declenchement ?? 'date_document') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('declenchement')" />
    </div>
    <div>
        <x-input-label for="alerte_avant_jours" :value="__('Alerte avant échéance (jours)')" />
        <x-text-input id="alerte_avant_jours" name="alerte_avant_jours" type="number" min="0" class="block mt-1 w-full"
                      :value="old('alerte_avant_jours', $r?->alerte_avant_jours)" />
        <x-input-error class="mt-2" :messages="$errors->get('alerte_avant_jours')" />
    </div>
    <div class="flex items-end gap-6">
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="hidden" name="conservation_obligatoire" value="0" />
            <input type="checkbox" name="conservation_obligatoire" value="1" class="rounded border-gray-300 text-ceeac-bright focus:ring-ceeac-bright"
                   @checked(old('conservation_obligatoire', $r?->conservation_obligatoire)) /> {{ __('Conservation obligatoire') }}
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="hidden" name="est_active" value="0" />
            <input type="checkbox" name="est_active" value="1" class="rounded border-gray-300 text-ceeac-bright focus:ring-ceeac-bright"
                   @checked(old('est_active', $r?->est_active ?? true)) /> {{ __('Active') }}
        </label>
    </div>
    <div class="md:col-span-2">
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="2" class="form-input mt-1">{{ old('description', $r?->description) }}</textarea>
    </div>
</div>
