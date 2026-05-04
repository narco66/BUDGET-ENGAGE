<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">{{ __('Ordonnancement') }} — <span class="font-mono">{{ $liquidation->engagement->numero }}</span></h2>
    </x-slot>
    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-4">{{ __('Liquidation du :date — montant :m', ['date' => $liquidation->date_liquidation->format('d/m/Y'), 'm' => number_format((float) $liquidation->montant_liquide, 2, ',', ' ')]) }}</p>
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
                    <textarea id="commentaire" name="commentaire" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">{{ old('commentaire') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('commentaire')" />
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('engagement')" />
                <div class="flex gap-4">
                    <x-primary-button>{{ __('Enregistrer') }}</x-primary-button>
                    <a href="{{ route('engagements.show', $liquidation->engagement) }}" class="text-sm text-gray-600 self-center">{{ __('Annuler') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
