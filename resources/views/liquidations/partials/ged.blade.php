@php
    $modifiable = auth()->user()->can('modifier', $liquidation);
    $herites = $liquidation->documents->where('categorie', 'herite');
    $specifiques = $liquidation->documents->where('categorie', 'specifique');
@endphp

@if ($modifiable)
    <div class="flex flex-wrap items-start gap-3">
        <form method="POST" action="{{ route('liquidations.heriter-documents', $liquidation) }}">@csrf
            <button class="btn btn-secondary btn-sm">{{ __('Hériter des pièces de l’engagement') }}</button>
        </form>
    </div>

    <form method="POST" action="{{ route('liquidations.documents.store', $liquidation) }}" enctype="multipart/form-data" class="rounded-lg border border-gray-200 p-4 grid grid-cols-1 md:grid-cols-3 gap-3">@csrf
        <div>
            <x-input-label for="fichier" :value="__('Pièce justificative')" />
            <input id="fichier" type="file" name="fichier" class="form-input mt-1 w-full text-sm" required>
        </div>
        <div>
            <x-input-label for="titre" :value="__('Titre')" />
            <x-text-input id="titre" name="titre" class="block mt-1 w-full" maxlength="255" />
        </div>
        <div>
            <x-input-label for="type_piece" :value="__('Type de pièce')" />
            <x-text-input id="type_piece" name="type_piece" class="block mt-1 w-full" maxlength="100" placeholder="facture, pv_reception…" />
        </div>
        <div class="md:col-span-3"><button class="btn btn-primary btn-sm">{{ __('Déposer dans la GED') }}</button></div>
    </form>
    <x-input-error class="mt-1" :messages="$errors->get('fichier')" />
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <h4 class="text-sm font-semibold text-gray-500 mb-2">{{ __('Documents spécifiques') }}</h4>
        @forelse ($specifiques as $doc)
            <div class="flex items-center justify-between text-sm py-1 border-b border-gray-100">
                <span>{{ $doc->gedDocument?->titre ?? ('Doc #'.$doc->ged_document_id) }}</span>
                <span class="badge badge-neutral">{{ $doc->type_piece ?? '—' }}</span>
            </div>
        @empty
            <p class="text-gray-400 text-sm">{{ __('Aucune pièce spécifique.') }}</p>
        @endforelse
    </div>
    <div>
        <h4 class="text-sm font-semibold text-gray-500 mb-2">{{ __('Documents hérités') }}</h4>
        @forelse ($herites as $doc)
            <div class="flex items-center justify-between text-sm py-1 border-b border-gray-100">
                <span>{{ $doc->gedDocument?->titre ?? ('Doc #'.$doc->ged_document_id) }}</span>
                <span class="badge badge-info">{{ $doc->source ?? 'hérité' }}</span>
            </div>
        @empty
            <p class="text-gray-400 text-sm">{{ __('Aucune pièce héritée.') }}</p>
        @endforelse
    </div>
</div>
