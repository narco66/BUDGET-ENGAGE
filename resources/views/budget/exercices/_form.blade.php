@props(['exercice_budgetaire' => null])
@php($ex = $exercice_budgetaire)

<div class="space-y-4">
    <div>
        <x-input-label for="annee" :value="__('Année')" />
        <x-text-input id="annee" type="number" name="annee" class="block mt-1 w-full" :value="old('annee', $ex?->annee)" required min="2000" max="2100" />
        <x-input-error class="mt-2" :messages="$errors->get('annee')" />
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-input-label for="date_debut" :value="__('Date début')" />
            <x-text-input id="date_debut" type="date" name="date_debut" class="block mt-1 w-full"
                :value="old('date_debut', $ex?->date_debut?->format('Y-m-d'))" required />
            <x-input-error class="mt-2" :messages="$errors->get('date_debut')" />
        </div>
        <div>
            <x-input-label for="date_fin" :value="__('Date fin')" />
            <x-text-input id="date_fin" type="date" name="date_fin" class="block mt-1 w-full"
                :value="old('date_fin', $ex?->date_fin?->format('Y-m-d'))" required />
            <x-input-error class="mt-2" :messages="$errors->get('date_fin')" />
        </div>
    </div>
    <div>
        <x-input-label for="statut" :value="__('Statut')" />
        <select id="statut" name="statut" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
            @php($statuts = [
                'preparatoire' => __('Préparatoire'),
                'ouvert' => __('Ouvert'),
                'suspendu' => __('Suspendu'),
                'cloture' => __('Clôturé'),
                'archive' => __('Archivé'),
            ])
            @php($currentStatut = old('statut', $ex?->statut instanceof \BackedEnum ? $ex->statut->value : ($ex?->statut ?? 'preparatoire')))
            @foreach ($statuts as $val => $label)
                <option value="{{ $val }}" @selected($currentStatut === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('statut')" />
    </div>
    <div class="flex items-center gap-2">
        <input id="est_actif" type="checkbox" name="est_actif" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm"
            @checked(old('est_actif', $ex?->est_actif ?? false)) />
        <x-input-label for="est_actif" :value="__('Exercice courant (actif)')" class="!mb-0" />
    </div>
    <x-input-error class="mt-2" :messages="$errors->get('est_actif')" />
</div>
