@include('rbm.partials.referential-form', ['item' => $item ?? null])
<div>
    <x-input-label for="cadre_logique_id" :value="__('Cadre logique')" />
    <select id="cadre_logique_id" name="cadre_logique_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
        <option value="">{{ __('Aucun') }}</option>
        @foreach($cadresLogiques ?? [] as $cadre)
            <option value="{{ $cadre->id }}" @selected(old('cadre_logique_id', $item?->cadre_logique_id) == $cadre->id)>{{ $cadre->libelle }}</option>
        @endforeach
    </select>
</div>
<div>
    <x-input-label for="niveau" :value="__('Niveau')" />
    <x-text-input id="niveau" name="niveau" type="text" class="block mt-1 w-full" :value="old('niveau', $item?->niveau)" required />
</div>
<div>
    <x-input-label for="indicateur_id" :value="__('Indicateur')" />
    <select id="indicateur_id" name="indicateur_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
        <option value="">{{ __('Aucun') }}</option>
        @foreach($indicateurs ?? [] as $indicateur)
            <option value="{{ $indicateur->id }}" @selected(old('indicateur_id', $item?->indicateur_id) == $indicateur->id)>{{ $indicateur->libelle }}</option>
        @endforeach
    </select>
</div>
<div>
    <x-input-label for="resultat_attendu" :value="__('Résultat attendu')" />
    <textarea id="resultat_attendu" name="resultat_attendu" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('resultat_attendu', $item?->resultat_attendu) }}</textarea>
</div>