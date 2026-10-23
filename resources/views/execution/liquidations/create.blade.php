<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Liquidation')"
            :description="__('Enregistrement depuis l\'engagement :num', ['num' => $engagement->numero])"
            :breadcrumb="[
                ['label' => __('Engagements'), 'url' => route('engagements.index')],
                ['label' => $engagement->numero, 'url' => route('engagements.show', $engagement)],
                ['label' => __('Liquidation')],
            ]"
        />
    </x-slot>

    <x-ds.page-shell narrow>
        <x-ds.card :title="__('Nouvelle liquidation')">
            <form method="POST" action="{{ route('engagements.liquidations.store', $engagement) }}" class="space-y-4">@csrf
                @php($imputations = $engagement->imputations->isNotEmpty() ? $engagement->imputations : collect([(object) ['id' => null, 'ligneBudgetaire' => $engagement->ligneBudgetaire, 'montant_impute' => $engagement->montant]]))
                <div>
                    <x-input-label for="engagement_imputation_id" :value="__('Imputation budgétaire')" />
                    <select id="engagement_imputation_id" name="engagement_imputation_id" class="form-select mt-1">
                        <option value="">— {{ __('Choisir') }} —</option>
                        @foreach($imputations as $imp)
                            <option value="{{ $imp->id }}" @selected((string) old('engagement_imputation_id') === (string) $imp->id)>
                                {{ $imp->ligneBudgetaire?->code ?? '—' }} — {{ $imp->ligneBudgetaire?->libelle ?? '' }} ({{ number_format((float) $imp->montant_impute, 2, ',', ' ') }})
                            </option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('engagement_imputation_id')" />
                </div>
                <div>
                    <x-input-label for="montant_liquide" :value="__('Montant liquidé')" />
                    <x-text-input id="montant_liquide" type="number" step="0.01" min="0.01" name="montant_liquide" class="block mt-1 w-full tabular-nums" :value="old('montant_liquide')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('montant_liquide')" />
                </div>
                <div>
                    <x-input-label for="date_liquidation" :value="__('Date de liquidation')" />
                    <x-text-input id="date_liquidation" type="date" name="date_liquidation" class="block mt-1 w-full" :value="old('date_liquidation', now()->format('Y-m-d'))" required />
                    <x-input-error class="mt-2" :messages="$errors->get('date_liquidation')" />
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
                    <a href="{{ route('engagements.show', $engagement) }}" class="btn btn-secondary">{{ __('Annuler') }}</a>
                </div>
            </form>
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
