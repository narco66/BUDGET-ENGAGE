@props(['workflow' => null, 'types' => collect()])
@php($w = $workflow)
@php($typesEtape = ['depot' => 'Dépôt', 'controle' => 'Contrôle', 'validation' => 'Validation', 'approbation' => 'Approbation', 'signature' => 'Signature', 'publication' => 'Publication', 'archivage' => 'Archivage'])
@php($initialEtapes = old('etapes', $w?->etapes?->map(fn ($e) => [
    'libelle' => $e->libelle, 'type_etape' => $e->type_etape,
    'permission_requise' => $e->permission_requise, 'role_requis' => $e->role_requis,
    'delai_jours' => $e->delai_jours,
])->values()->all() ?: [['libelle' => '', 'type_etape' => 'validation', 'permission_requise' => '', 'role_requis' => '', 'delai_jours' => '']]))

<div class="space-y-5">
    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <x-input-label for="code" :value="__('Code')" />
            <x-text-input id="code" name="code" type="text" class="block mt-1 w-full"
                          :value="old('code', $w?->code)" required maxlength="50" />
            <x-input-error class="mt-2" :messages="$errors->get('code')" />
        </div>
        <div>
            <x-input-label for="libelle" :value="__('Libellé du circuit')" />
            <x-text-input id="libelle" name="libelle" type="text" class="block mt-1 w-full"
                          :value="old('libelle', $w?->libelle)" required maxlength="255" />
            <x-input-error class="mt-2" :messages="$errors->get('libelle')" />
        </div>
        <div>
            <x-input-label for="type_document_id" :value="__('Type de document (optionnel)')" />
            <select id="type_document_id" name="type_document_id" class="form-select mt-1">
                <option value="">{{ __('— Tous —') }}</option>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}" @selected((int) old('type_document_id', $w?->type_document_id) === $type->id)>{{ $type->libelle }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-6">
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="est_actif" value="0" />
                <input type="checkbox" name="est_actif" value="1" class="rounded border-gray-300 text-ceeac-bright focus:ring-ceeac-bright"
                       @checked(old('est_actif', $w?->est_actif ?? true)) /> {{ __('Actif') }}
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="est_par_defaut" value="0" />
                <input type="checkbox" name="est_par_defaut" value="1" class="rounded border-gray-300 text-ceeac-bright focus:ring-ceeac-bright"
                       @checked(old('est_par_defaut', $w?->est_par_defaut)) /> {{ __('Par défaut') }}
            </label>
        </div>
        <div class="md:col-span-2">
            <x-input-label for="description" :value="__('Description')" />
            <textarea id="description" name="description" rows="2" class="form-input mt-1">{{ old('description', $w?->description) }}</textarea>
        </div>
    </div>

    {{-- Étapes du circuit --}}
    <div x-data="{
        etapes: @js($initialEtapes),
        types: @js($typesEtape),
        add() { this.etapes.push({ libelle: '', type_etape: 'validation', permission_requise: '', role_requis: '', delai_jours: '' }); },
        remove(i) { if (this.etapes.length > 1) this.etapes.splice(i, 1); }
    }">
        <div class="flex items-center justify-between mb-2">
            <h3 class="font-semibold text-ceeac-primary">Étapes du circuit</h3>
            <button type="button" @click="add()" class="btn btn-secondary btn-sm">{{ __('Ajouter une étape') }}</button>
        </div>
        <x-input-error class="mb-2" :messages="$errors->get('etapes')" />

        <div class="space-y-3">
            <template x-for="(etape, i) in etapes" :key="i">
                <div class="grid md:grid-cols-12 gap-2 items-end border border-gray-100 rounded-xl p-3 bg-gray-50/50">
                    <div class="md:col-span-1 text-center">
                        <span class="form-label">N°</span>
                        <p class="font-semibold tabular-nums" x-text="i + 1"></p>
                    </div>
                    <div class="md:col-span-3">
                        <label class="form-label">Libellé</label>
                        <input type="text" :name="`etapes[${i}][libelle]`" x-model="etape.libelle" class="form-input" required />
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Type</label>
                        <select :name="`etapes[${i}][type_etape]`" x-model="etape.type_etape" class="form-select">
                            <template x-for="(label, val) in types" :key="val">
                                <option :value="val" x-text="label"></option>
                            </template>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Permission</label>
                        <input type="text" :name="`etapes[${i}][permission_requise]`" x-model="etape.permission_requise" class="form-input" placeholder="ged.validate" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="form-label">Rôle requis</label>
                        <input type="text" :name="`etapes[${i}][role_requis]`" x-model="etape.role_requis" class="form-input" placeholder="ordonnateur" />
                    </div>
                    <div class="md:col-span-1">
                        <label class="form-label">Délai (j)</label>
                        <input type="number" min="0" :name="`etapes[${i}][delai_jours]`" x-model="etape.delai_jours" class="form-input" />
                    </div>
                    <div class="md:col-span-1">
                        <button type="button" @click="remove(i)" class="btn btn-danger btn-sm w-full">✕</button>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
