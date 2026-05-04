@props(['departement' => null])
@php($d = $departement)

<div class="space-y-4">
    <div>
        <x-input-label for="code" :value="__('Code')" />
        <x-text-input id="code" name="code" type="text" class="block mt-1 w-full" :value="old('code', $d?->code)" required maxlength="50" />
        <x-input-error class="mt-2" :messages="$errors->get('code')" />
    </div>
    <div>
        <x-input-label for="libelle" :value="__('Libellé')" />
        <x-text-input id="libelle" name="libelle" type="text" class="block mt-1 w-full" :value="old('libelle', $d?->libelle)" required />
        <x-input-error class="mt-2" :messages="$errors->get('libelle')" />
    </div>
    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $d?->description) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>
    <div>
        <x-input-label for="statut" :value="__('Statut')" />
        <select id="statut" name="statut" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
            @foreach (['actif' => __('Actif'), 'inactif' => __('Inactif')] as $val => $label)
                <option value="{{ $val }}" @selected(old('statut', $d?->statut ?? 'actif') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('statut')" />
    </div>
</div>
