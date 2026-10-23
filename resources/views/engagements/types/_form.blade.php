@props(['type' => null])
@php($t = $type)

<div class="grid md:grid-cols-3 gap-4">
    <div>
        <x-input-label for="code" :value="__('Code')" />
        <x-text-input id="code" name="code" class="block mt-1 w-full" :value="old('code', $t?->code)" required maxlength="50" />
        <x-input-error class="mt-2" :messages="$errors->get('code')" />
    </div>
    <div class="md:col-span-2">
        <x-input-label for="libelle" :value="__('Libellé')" />
        <x-text-input id="libelle" name="libelle" class="block mt-1 w-full" :value="old('libelle', $t?->libelle)" required maxlength="255" />
        <x-input-error class="mt-2" :messages="$errors->get('libelle')" />
    </div>
    <div>
        <x-input-label for="famille" :value="__('Famille')" />
        <x-text-input id="famille" name="famille" class="block mt-1 w-full" :value="old('famille', $t?->famille)" maxlength="60"
                      placeholder="juridique, comptable…" />
    </div>
    <div>
        <x-input-label for="nature" :value="__('Nature')" />
        <x-text-input id="nature" name="nature" class="block mt-1 w-full" :value="old('nature', $t?->nature)" maxlength="60" />
    </div>
    <div>
        <x-input-label for="ordre" :value="__('Ordre')" />
        <x-text-input id="ordre" name="ordre" type="number" min="0" class="block mt-1 w-full" :value="old('ordre', $t?->ordre ?? 0)" />
    </div>
    <div>
        <x-input-label for="workflow_code" :value="__('Circuit dédié (code, optionnel)')" />
        <x-text-input id="workflow_code" name="workflow_code" class="block mt-1 w-full" :value="old('workflow_code', $t?->workflow_code)" maxlength="100" />
    </div>
    <div class="md:col-span-3">
        <x-input-label for="description" :value="__('Description')" />
        <textarea id="description" name="description" rows="2" class="form-input mt-1">{{ old('description', $t?->description) }}</textarea>
    </div>

    <div class="md:col-span-3">
        <x-input-label for="champs_specifiques" :value="__('Champs spécifiques (JSON)')" />
        <textarea id="champs_specifiques" name="champs_specifiques" rows="6" class="form-input mt-1 font-mono text-xs"
                  placeholder='[{"cle":"contrat","libelle":"Référence du contrat","type":"text","requis":true}]'>{{ old('champs_specifiques', $t ? json_encode($t->champs_specifiques ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
        <p class="text-xs text-gray-500 mt-1">Liste d'objets : <code>cle</code>, <code>libelle</code>, <code>type</code> (text/number/date/textarea/select), <code>requis</code>.</p>
        <x-input-error class="mt-2" :messages="$errors->get('champs_specifiques')" />
    </div>

    <div class="md:col-span-3">
        <x-input-label for="documents_obligatoires" :value="__('Documents obligatoires (codes séparés par des virgules)')" />
        <x-text-input id="documents_obligatoires" name="documents_obligatoires" class="block mt-1 w-full"
                      :value="old('documents_obligatoires', $t ? implode(', ', $t->documents_obligatoires ?? []) : '')"
                      placeholder="contrat, marche, certificat" />
    </div>

    <div class="md:col-span-3 flex flex-wrap gap-6">
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="hidden" name="est_actif" value="0" />
            <input type="checkbox" name="est_actif" value="1" class="rounded border-gray-300 text-ceeac-bright focus:ring-ceeac-bright"
                   @checked(old('est_actif', $t?->est_actif ?? true)) /> {{ __('Actif') }}
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="hidden" name="permet_generation" value="0" />
            <input type="checkbox" name="permet_generation" value="1" class="rounded border-gray-300 text-ceeac-bright focus:ring-ceeac-bright"
                   @checked(old('permet_generation', $t?->permet_generation ?? true)) /> {{ __('Génération automatique autorisée') }}
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="hidden" name="permet_creation_manuelle" value="0" />
            <input type="checkbox" name="permet_creation_manuelle" value="1" class="rounded border-gray-300 text-ceeac-bright focus:ring-ceeac-bright"
                   @checked(old('permet_creation_manuelle', $t?->permet_creation_manuelle ?? true)) /> {{ __('Création manuelle autorisée') }}
        </label>
    </div>
</div>
