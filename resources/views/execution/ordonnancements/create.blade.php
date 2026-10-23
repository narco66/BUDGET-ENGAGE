<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Ordonnancement')"
            :description="__('Liquidation du :date — montant :m', ['date' => $liquidation->date_liquidation->format('d/m/Y'), 'm' => number_format((float) $liquidation->montant_liquide, 2, ',', ' ')])"
            :breadcrumb="[
                ['label' => __('Engagements'), 'url' => route('engagements.index')],
                ['label' => $liquidation->engagement->numero, 'url' => route('engagements.show', $liquidation->engagement)],
                ['label' => __('Ordonnancement')],
            ]"
        />
    </x-slot>

    <x-ds.page-shell narrow>
        <x-ds.card :title="__('Nouvel ordonnancement')">
            <form method="POST" action="{{ route('liquidations.ordonnancements.store', $liquidation) }}" class="space-y-4">@csrf
                <div>
                    <x-input-label for="montant_ordonnance" :value="__('Montant ordonnancé')" />
                    <x-text-input id="montant_ordonnance" type="number" step="0.01" min="0.01" name="montant_ordonnance" class="block mt-1 w-full tabular-nums" :value="old('montant_ordonnance')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('montant_ordonnance')" />
                </div>
                <div>
                    <x-input-label for="date_ordonnancement" :value="__('Date d’ordonnancement')" />
                    <x-text-input id="date_ordonnancement" type="date" name="date_ordonnancement" class="block mt-1 w-full" :value="old('date_ordonnancement', now()->format('Y-m-d'))" required />
                    <x-input-error class="mt-2" :messages="$errors->get('date_ordonnancement')" />
                </div>
                <div>
                    <x-input-label for="reference" :value="__('Référence')" />
                    <x-text-input id="reference" name="reference" class="block mt-1 w-full" :value="old('reference')" maxlength="100" />
                    <x-input-error class="mt-2" :messages="$errors->get('reference')" />
                </div>
                <div>
                    <x-input-label for="commentaire" :value="__('Commentaire')" />
                    <textarea id="commentaire" name="commentaire" rows="2" class="form-input mt-1 w-full">{{ old('commentaire') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('commentaire')" />
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('engagement')" />
                <div class="flex flex-wrap gap-3 pt-2">
                    <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>
                    <a href="{{ route('engagements.show', $liquidation->engagement) }}" class="btn btn-secondary">{{ __('Annuler') }}</a>
                </div>
            </form>
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
