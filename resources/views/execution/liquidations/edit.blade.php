<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('execution.actions.edit') . ' — Liquidation #' . $liquidation->id"
            :breadcrumb="[
                ['label' => __('execution.dashboard'), 'url' => route('execution.dashboard')],
                ['label' => '#'.$liquidation->id, 'url' => route('execution.liquidations.show', $liquidation)],
                ['label' => __('Modifier')],
            ]"
        />
    </x-slot>

    <x-ds.page-shell>
        <x-ds.card>
            <form method="POST" action="{{ route('execution.liquidations.update', $liquidation) }}" class="space-y-4">@csrf @method('PUT')
                <div>
                    <x-input-label for="montant_liquide" :value="__('Montant liquidé')" />
                    <x-text-input id="montant_liquide" type="number" step="0.01" min="0.01" name="montant_liquide" class="block mt-1 w-full tabular-nums" :value="old('montant_liquide', $liquidation->montant_liquide)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('montant_liquide')" />
                </div>
                <div>
                    <x-input-label for="date_liquidation" :value="__('Date de liquidation')" />
                    <x-text-input id="date_liquidation" type="date" name="date_liquidation" class="block mt-1 w-full" :value="old('date_liquidation', $liquidation->date_liquidation?->format('Y-m-d'))" required />
                </div>
                <div>
                    <x-input-label for="reference" :value="__('Référence')" />
                    <x-text-input id="reference" name="reference" class="block mt-1 w-full" :value="old('reference', $liquidation->reference)" maxlength="100" />
                </div>
                <div>
                    <x-input-label for="commentaire" :value="__('Commentaire')" />
                    <textarea id="commentaire" name="commentaire" rows="2" class="form-textarea mt-1 w-full">{{ old('commentaire', $liquidation->commentaire) }}</textarea>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('liquidation')" />
                <div class="flex gap-4">
                    <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>
                    <a href="{{ route('execution.liquidations.show', $liquidation) }}" class="btn btn-ghost btn-sm self-center">{{ __('Annuler') }}</a>
                </div>
            </form>
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
