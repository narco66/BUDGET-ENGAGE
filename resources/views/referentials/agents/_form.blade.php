@props(['agent' => null, 'services' => collect()])
@php($a = $agent)

<div class="space-y-4">
    <div>
        <x-input-label for="service_id" :value="__('Service')" />
        <select id="service_id" name="service_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
            <option value="">{{ __('— Aucun —') }}</option>
            @foreach ($services as $svc)
                <option value="{{ $svc->id }}" @selected(old('service_id', $a?->service_id) == $svc->id)>
                    {{ $svc->direction->departement->libelle ?? '' }} — {{ $svc->direction->libelle ?? '' }} — {{ $svc->libelle }}
                </option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('service_id')" />
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="matricule" :value="__('Matricule')" />
            <x-text-input id="matricule" name="matricule" class="block mt-1 w-full" :value="old('matricule', $a?->matricule)" maxlength="100" />
            <x-input-error class="mt-2" :messages="$errors->get('matricule')" />
        </div>
        <div>
            <x-input-label for="statut" :value="__('Statut')" />
            <select id="statut" name="statut" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                @foreach (['actif' => __('Actif'), 'inactif' => __('Inactif'), 'suspendu' => __('Suspendu')] as $val => $label)
                    <option value="{{ $val }}" @selected(old('statut', $a?->statut ?? 'actif') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('statut')" />
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="nom" :value="__('Nom')" />
            <x-text-input id="nom" name="nom" class="block mt-1 w-full" :value="old('nom', $a?->nom)" required />
            <x-input-error class="mt-2" :messages="$errors->get('nom')" />
        </div>
        <div>
            <x-input-label for="prenom" :value="__('Prénom')" />
            <x-text-input id="prenom" name="prenom" class="block mt-1 w-full" :value="old('prenom', $a?->prenom)" />
            <x-input-error class="mt-2" :messages="$errors->get('prenom')" />
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="fonction" :value="__('Fonction')" />
            <x-text-input id="fonction" name="fonction" class="block mt-1 w-full" :value="old('fonction', $a?->fonction)" />
            <x-input-error class="mt-2" :messages="$errors->get('fonction')" />
        </div>
        <div>
            <x-input-label for="grade" :value="__('Grade')" />
            <x-text-input id="grade" name="grade" class="block mt-1 w-full" :value="old('grade', $a?->grade)" />
            <x-input-error class="mt-2" :messages="$errors->get('grade')" />
        </div>
    </div>
    <div>
        <x-input-label for="categorie" :value="__('Catégorie')" />
        <x-text-input id="categorie" name="categorie" class="block mt-1 w-full" :value="old('categorie', $a?->categorie)" maxlength="100" />
        <x-input-error class="mt-2" :messages="$errors->get('categorie')" />
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="email" :value="__('E-mail')" />
            <x-text-input id="email" type="email" name="email" class="block mt-1 w-full" :value="old('email', $a?->email)" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>
        <div>
            <x-input-label for="telephone" :value="__('Téléphone')" />
            <x-text-input id="telephone" name="telephone" class="block mt-1 w-full" :value="old('telephone', $a?->telephone)" maxlength="50" />
            <x-input-error class="mt-2" :messages="$errors->get('telephone')" />
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="banque" :value="__('Banque')" />
            <x-text-input id="banque" name="banque" class="block mt-1 w-full" :value="old('banque', $a?->banque)" />
            <x-input-error class="mt-2" :messages="$errors->get('banque')" />
        </div>
        <div>
            <x-input-label for="compte_bancaire" :value="__('Compte bancaire')" />
            <x-text-input id="compte_bancaire" name="compte_bancaire" class="block mt-1 w-full" :value="old('compte_bancaire', $a?->compte_bancaire)" maxlength="100" />
            <x-input-error class="mt-2" :messages="$errors->get('compte_bancaire')" />
        </div>
    </div>
</div>
