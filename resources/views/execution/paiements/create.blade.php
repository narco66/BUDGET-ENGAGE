<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Paiement')"
            :description="__('Ordonnancement du :date — montant :m', ['date' => $ordonnancement->date_ordonnancement->format('d/m/Y'), 'm' => number_format((float) $ordonnancement->montant_ordonnance, 2, ',', ' ')])"
            :breadcrumb="[
                ['label' => __('Engagements'), 'url' => route('engagements.index')],
                ['label' => $ordonnancement->liquidation->engagement->numero, 'url' => route('engagements.show', $ordonnancement->liquidation->engagement)],
                ['label' => __('Paiement')],
            ]"
        />
    </x-slot>

    <x-ds.page-shell narrow>
        <x-ds.card :title="__('Nouveau paiement')">
            <form method="POST" action="{{ route('ordonnancements.paiements.store', $ordonnancement) }}" class="space-y-4">@csrf
                <div>
                    <x-input-label for="montant_paye" :value="__('Montant payé')" />
                    <x-text-input id="montant_paye" type="number" step="0.01" min="0.01" name="montant_paye" class="block mt-1 w-full tabular-nums" :value="old('montant_paye')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('montant_paye')" />
                </div>
                <div>
                    <x-input-label for="date_paiement" :value="__('Date de paiement')" />
                    <x-text-input id="date_paiement" type="date" name="date_paiement" class="block mt-1 w-full" :value="old('date_paiement', now()->format('Y-m-d'))" required />
                    <x-input-error class="mt-2" :messages="$errors->get('date_paiement')" />
                </div>
                <div>
                    <x-input-label for="reference_paiement" :value="__('Référence paiement')" />
                    <x-text-input id="reference_paiement" name="reference_paiement" class="block mt-1 w-full" :value="old('reference_paiement')" maxlength="100" />
                    <x-input-error class="mt-2" :messages="$errors->get('reference_paiement')" />
                </div>
                <div>
                    <x-input-label for="mode_paiement" :value="__('Mode de paiement')" />
                    <x-text-input id="mode_paiement" name="mode_paiement" class="block mt-1 w-full" :value="old('mode_paiement')" maxlength="100" />
                    <x-input-error class="mt-2" :messages="$errors->get('mode_paiement')" />
                </div>
                <div>
                    <x-input-label for="commentaire" :value="__('Commentaire')" />
                    <textarea id="commentaire" name="commentaire" rows="2" class="form-input mt-1 w-full">{{ old('commentaire') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('commentaire')" />
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('engagement')" />
                <div class="flex flex-wrap gap-3 pt-2">
                    <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>
                    <a href="{{ route('engagements.show', $ordonnancement->liquidation->engagement) }}" class="btn btn-secondary">{{ __('Annuler') }}</a>
                </div>
            </form>
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
