@props(['service' => null, 'directions'])
@php($s = $service)

<div class="space-y-4">
    <div>
        <x-input-label for="direction_id" :value="__('Direction')" />
        <select id="direction_id" name="direction_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
            @foreach ($directions as $direction)
                <option value="{{ $direction->id }}" @selected(old('direction_id', $s?->direction_id) == $direction->id)>
                    {{ $direction->departement?->code }} — {{ $direction->libelle }} ({{ $direction->code }})
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('direction_id')" />
    </div>
    <div>
        <x-input-label for="code" :value="__('Code')" />
        <x-text-input id="code" name="code" class="block mt-1 w-full" :value="old('code', $s?->code)" required maxlength="50" />
        <x-input-error class="mt-2" :messages="$errors->get('code')" />
    </div>
    <div>
        <x-input-label for="libelle" :value="__('Libellé')" />
        <x-text-input id="libelle" name="libelle" class="block mt-1 w-full" :value="old('libelle', $s?->libelle)" required />
        <x-input-error class="mt-2" :messages="$errors->get('libelle')" />
    </div>
    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $s?->description) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>
    <div>
        <x-input-label for="statut" :value="__('Statut')" />
        <select id="statut" name="statut" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
            @foreach (['actif' => __('Actif'), 'inactif' => __('Inactif')] as $val => $label)
                <option value="{{ $val }}" @selected(old('statut', $s?->statut ?? 'actif') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('statut')" />
    </div>
</div>
