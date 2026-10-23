<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('execution.actions.edit') . ' — Ordonnancement #' . $ordonnancement->id"
            :breadcrumb="[
                ['label' => __('execution.dashboard'), 'url' => route('execution.dashboard', ['tab' => 'ordonnancements'])],
                ['label' => '#'.$ordonnancement->id, 'url' => route('execution.ordonnancements.show', $ordonnancement)],
                ['label' => __('Modifier')],
            ]"
        />
    </x-slot>

    <x-ds.page-shell>
        <x-ds.card>
            <form method="POST" action="{{ route('execution.ordonnancements.update', $ordonnancement) }}" class="space-y-4">@csrf @method('PUT')
                <div>
                    <x-input-label for="montant_ordonnance" :value="__('Montant ordonnancé')" />
                    <x-text-input id="montant_ordonnance" type="number" step="0.01" min="0.01" name="montant_ordonnance" class="block mt-1 w-full tabular-nums" :value="old('montant_ordonnance', $ordonnancement->montant_ordonnance)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('montant_ordonnance')" />
                </div>
                <div>
                    <x-input-label for="date_ordonnancement" :value="__('Date d\'ordonnancement')" />
                    <x-text-input id="date_ordonnancement" type="date" name="date_ordonnancement" class="block mt-1 w-full" :value="old('date_ordonnancement', $ordonnancement->date_ordonnancement?->format('Y-m-d'))" required />
                </div>
                <div>
                    <x-input-label for="reference" :value="__('Référence')" />
                    <x-text-input id="reference" name="reference" class="block mt-1 w-full" :value="old('reference', $ordonnancement->reference)" maxlength="100" />
                </div>
                <div>
                    <x-input-label for="commentaire" :value="__('Commentaire')" />
                    <textarea id="commentaire" name="commentaire" rows="2" class="form-textarea mt-1 w-full">{{ old('commentaire', $ordonnancement->commentaire) }}</textarea>
                </div>
                <div class="flex gap-4">
                    <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>
                    <a href="{{ route('execution.ordonnancements.show', $ordonnancement) }}" class="btn btn-ghost btn-sm self-center">{{ __('Annuler') }}</a>
                </div>
            </form>
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
