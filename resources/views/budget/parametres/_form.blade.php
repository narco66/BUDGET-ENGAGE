@props(['parametre_controle' => null])
@php($p = $parametre_controle)

<div class="space-y-4">
    <div>
        <x-input-label for="cle" :value="__('Clé')" />
        <x-text-input id="cle" name="cle" class="block mt-1 w-full font-mono {{ $p ? 'bg-gray-50 text-gray-700' : '' }}" :value="old('cle', $p?->cle)" required maxlength="100" @readonly($p !== null) />
        @if($p)
            <p class="mt-1 text-xs text-gray-500">{{ __('La clé est en lecture seule.') }}</p>
        @endif
        <x-input-error class="mt-2" :messages="$errors->get('cle')" />
    </div>
    <div>
        <x-input-label for="type_valeur" :value="__('Type de valeur')" />
        <select id="type_valeur" name="type_valeur" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
            @foreach (['string' => __('Texte'), 'integer' => __('Entier'), 'decimal' => __('Décimal'), 'boolean' => __('Booléen'), 'json' => __('JSON'), 'date' => __('Date')] as $val => $label)
                <option value="{{ $val }}" @selected(old('type_valeur', $p?->type_valeur ?? 'string') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('type_valeur')" />
    </div>
    <div>
        <x-input-label for="valeur" :value="__('Valeur')" />
        <textarea id="valeur" name="valeur" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm font-mono text-sm" required>{{ old('valeur', $p?->valeur) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('valeur')" />
    </div>
    <div>
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $p?->description) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>
    <div class="flex items-center gap-2">
        <input id="actif" type="checkbox" name="actif" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm"
            @checked(old('actif', $p?->actif ?? true)) />
        <x-input-label for="actif" :value="__('Paramètre actif')" class="!mb-0" />
    </div>
    <x-input-error class="mt-2" :messages="$errors->get('actif')" />
</div>
