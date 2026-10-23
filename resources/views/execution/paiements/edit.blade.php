<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('execution.actions.edit') . ' — Paiement #' . $paiement->id"
            :breadcrumb="[
                ['label' => __('execution.dashboard'), 'url' => route('execution.dashboard', ['tab' => 'paiements'])],
                ['label' => '#'.$paiement->id, 'url' => route('execution.paiements.show', $paiement)],
                ['label' => __('Modifier')],
            ]"
        />
    </x-slot>

    <x-ds.page-shell>
        <x-ds.card>
            <form method="POST" action="{{ route('execution.paiements.update', $paiement) }}" class="space-y-4">@csrf @method('PUT')
                <div>
                    <x-input-label for="montant_paye" :value="__('Montant payé')" />
                    <x-text-input id="montant_paye" type="number" step="0.01" min="0.01" name="montant_paye" class="block mt-1 w-full tabular-nums" :value="old('montant_paye', $paiement->montant_paye)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('montant_paye')" />
                </div>
                <div>
                    <x-input-label for="date_paiement" :value="__('Date de paiement')" />
                    <x-text-input id="date_paiement" type="date" name="date_paiement" class="block mt-1 w-full" :value="old('date_paiement', $paiement->date_paiement?->format('Y-m-d'))" required />
                </div>
                <div>
                    <x-input-label for="reference_paiement" :value="__('Référence')" />
                    <x-text-input id="reference_paiement" name="reference_paiement" class="block mt-1 w-full" :value="old('reference_paiement', $paiement->reference_paiement)" maxlength="100" />
                </div>
                <div>
                    <x-input-label for="mode_paiement" :value="__('Mode de paiement')" />
                    <x-text-input id="mode_paiement" name="mode_paiement" class="block mt-1 w-full" :value="old('mode_paiement', $paiement->mode_paiement)" maxlength="100" />
                </div>
                <div>
                    <x-input-label for="commentaire" :value="__('Commentaire')" />
                    <textarea id="commentaire" name="commentaire" rows="2" class="form-textarea mt-1 w-full">{{ old('commentaire', $paiement->commentaire) }}</textarea>
                </div>
                <div class="flex gap-4">
                    <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>
                    <a href="{{ route('execution.paiements.show', $paiement) }}" class="btn btn-ghost btn-sm self-center">{{ __('Annuler') }}</a>
                </div>
            </form>
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
