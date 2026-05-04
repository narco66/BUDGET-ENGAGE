@props(['fournisseur' => null])
@php($f = $fournisseur)

<div class="space-y-4">
    <div>
        <x-input-label for="raison_sociale" :value="__('Raison sociale')" />
        <x-text-input id="raison_sociale" name="raison_sociale" class="block mt-1 w-full" :value="old('raison_sociale', $f?->raison_sociale)" required />
        <x-input-error class="mt-2" :messages="$errors->get('raison_sociale')" />
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="sigle" :value="__('Sigle')" />
            <x-text-input id="sigle" name="sigle" class="block mt-1 w-full" :value="old('sigle', $f?->sigle)" maxlength="100" />
            <x-input-error class="mt-2" :messages="$errors->get('sigle')" />
        </div>
        <div>
            <x-input-label for="numero_fiscal" :value="__('N° fiscal / RCCM')" />
            <x-text-input id="numero_fiscal" name="numero_fiscal" class="block mt-1 w-full" :value="old('numero_fiscal', $f?->numero_fiscal)" maxlength="100" />
            <x-input-error class="mt-2" :messages="$errors->get('numero_fiscal')" />
        </div>
    </div>
    <div>
        <x-input-label for="type_fournisseur" :value="__('Type')" />
        <x-text-input id="type_fournisseur" name="type_fournisseur" class="block mt-1 w-full" :value="old('type_fournisseur', $f?->type_fournisseur)" maxlength="100" />
        <x-input-error class="mt-2" :messages="$errors->get('type_fournisseur')" />
    </div>
    <div>
        <x-input-label for="adresse" :value="__('Adresse')" />
        <textarea id="adresse" name="adresse" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('adresse', $f?->adresse) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('adresse')" />
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="telephone" :value="__('Téléphone')" />
            <x-text-input id="telephone" name="telephone" class="block mt-1 w-full" :value="old('telephone', $f?->telephone)" maxlength="50" />
            <x-input-error class="mt-2" :messages="$errors->get('telephone')" />
        </div>
        <div>
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" type="email" name="email" class="block mt-1 w-full" :value="old('email', $f?->email)" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="banque" :value="__('Banque')" />
            <x-text-input id="banque" name="banque" class="block mt-1 w-full" :value="old('banque', $f?->banque)" />
            <x-input-error class="mt-2" :messages="$errors->get('banque')" />
        </div>
        <div>
            <x-input-label for="compte_bancaire" :value="__('Compte bancaire')" />
            <x-text-input id="compte_bancaire" name="compte_bancaire" class="block mt-1 w-full" :value="old('compte_bancaire', $f?->compte_bancaire)" maxlength="100" />
            <x-input-error class="mt-2" :messages="$errors->get('compte_bancaire')" />
        </div>
    </div>
    <div>
        <x-input-label for="statut" :value="__('Statut')" />
        <select id="statut" name="statut" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
            @foreach (['actif' => __('Actif'), 'inactif' => __('Inactif'), 'suspendu' => __('Suspendu')] as $val => $label)
                <option value="{{ $val }}" @selected(old('statut', $f?->statut ?? 'actif') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('statut')" />
    </div>
</div>
