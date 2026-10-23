@include('rbm.partials.referential-form', ['item' => $indicateur ?? null])
<div>
    <x-input-label for="type_indicateur_id" :value="__('Type d\'indicateur')" />
    <select id="type_indicateur_id" name="type_indicateur_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
        <option value="">{{ __('Sélectionner') }}</option>
        @foreach($typesIndicateurs as $type)
            <option value="{{ $type->id }}" @selected(old('type_indicateur_id', $indicateur?->type_indicateur_id) == $type->id)>{{ $type->libelle }}</option>
        @endforeach
    </select>
</div>
<div>
    <x-input-label for="unite_mesure_id" :value="__('Unité de mesure')" />
    <select id="unite_mesure_id" name="unite_mesure_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
        <option value="">{{ __('Sélectionner') }}</option>
        @foreach($unitesMesure as $unite)
            <option value="{{ $unite->id }}" @selected(old('unite_mesure_id', $indicateur?->unite_mesure_id) == $unite->id)>{{ $unite->libelle }}</option>
        @endforeach
    </select>
</div>
<div>
    <x-input-label for="tache_ids" :value="__('Tâches liées')" />
    <select id="tache_ids" name="tache_ids[]" multiple class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
        @foreach($taches as $tache)
            <option value="{{ $tache->id }}" @selected(collect(old('tache_ids', $indicateur?->taches?->pluck('id') ?? []))->contains($tache->id))>{{ $tache->code }} — {{ $tache->libelle }}</option>
        @endforeach
    </select>
</div>
<div>
    <x-input-label for="source_verification_ids" :value="__('Sources de vérification')" />
    <select id="source_verification_ids" name="source_verification_ids[]" multiple class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
        @foreach($sourcesVerification as $source)
            <option value="{{ $source->id }}" @selected(collect(old('source_verification_ids', $indicateur?->sourcesVerification?->pluck('id') ?? []))->contains($source->id))>{{ $source->libelle }}</option>
        @endforeach
    </select>
</div>