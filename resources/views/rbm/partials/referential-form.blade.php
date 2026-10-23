@props(['item' => null, 'parentField' => null, 'parents' => null, 'parentLabel' => null, 'extra' => null])

<div class="space-y-4">
    @if($parentField && $parents)
        <div>
            <x-input-label :for="$parentField" :value="$parentLabel ?? __('Parent')" />
            <select id="{{ $parentField }}" name="{{ $parentField }}" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                <option value="">{{ __('Sélectionner') }}</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" @selected(old($parentField, $item?->{$parentField}) == $parent->id)>{{ $parent->code }} — {{ $parent->libelle }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get($parentField)" />
        </div>
    @endif
    <div>
        <x-input-label for="code" :value="__('Code')" />
        <x-text-input id="code" name="code" type="text" class="block mt-1 w-full" :value="old('code', $item?->code)" required maxlength="50" />
        <x-input-error class="mt-2" :messages="$errors->get('code')" />
    </div>
    <div>
        <x-input-label for="libelle" :value="__('Libellé')" />
        <x-text-input id="libelle" name="libelle" type="text" class="block mt-1 w-full" :value="old('libelle', $item?->libelle)" required />
        <x-input-error class="mt-2" :messages="$errors->get('libelle')" />
    </div>
    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $item?->description) }}</textarea>
    </div>
    <div>
        <x-input-label for="ordre" :value="__('Ordre')" />
        <x-text-input id="ordre" name="ordre" type="number" min="0" class="block mt-1 w-full" :value="old('ordre', $item?->ordre ?? 0)" />
    </div>
    <div>
        <x-input-label for="statut" :value="__('Statut')" />
        <select id="statut" name="statut" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
            @foreach (['actif' => __('Actif'), 'inactif' => __('Inactif')] as $val => $label)
                <option value="{{ $val }}" @selected(old('statut', $item?->statut ?? 'actif') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    @if($extra){!! $extra !!}@endif
</div>