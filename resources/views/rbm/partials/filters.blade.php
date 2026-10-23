<form method="GET" class="flex flex-wrap gap-3 items-end mb-4">
    <div>
        <label class="text-sm text-gray-600">{{ __('Recherche') }}</label>
        <input type="text" name="q" value="{{ request('q') }}" class="border-gray-300 rounded-md shadow-sm" placeholder="{{ __('Code ou libellé') }}">
    </div>
    <div>
        <label class="text-sm text-gray-600">{{ __('Statut') }}</label>
        <select name="statut" class="border-gray-300 rounded-md shadow-sm">
            <option value="">{{ __('Tous') }}</option>
            <option value="actif" @selected(request('statut') === 'actif')>{{ __('Actif') }}</option>
            <option value="inactif" @selected(request('statut') === 'inactif')>{{ __('Inactif') }}</option>
        </select>
    </div>
    <x-secondary-button type="submit">{{ __('Filtrer') }}</x-secondary-button>
</form>