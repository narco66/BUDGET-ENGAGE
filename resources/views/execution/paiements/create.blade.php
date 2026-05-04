<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">{{ __('Paiement') }} — <span class="font-mono">{{ $ordonnancement->liquidation->engagement->numero }}</span></h2>
    </x-slot>
    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-4">{{ __('Ordonnancement du :date — montant :m', ['date' => $ordonnancement->date_ordonnancement->format('d/m/Y'), 'm' => number_format((float) $ordonnancement->montant_ordonnance, 2, ',', ' ')]) }}</p>
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
                    <textarea id="commentaire" name="commentaire" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('commentaire') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('commentaire')" />
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('engagement')" />
                <div class="flex gap-4">
                    <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>
                    <a href="{{ route('engagements.show', $ordonnancement->liquidation->engagement) }}" class="text-sm text-gray-600 self-center">{{ __('Annuler') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
