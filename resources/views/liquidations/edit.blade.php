<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <a href="{{ route('liquidations.index') }}" class="hover:text-ceeac-primary">{{ __('Liquidations') }}</a>
                    <span class="text-gray-300">/</span>
                    <a href="{{ route('liquidations.show', $liquidation) }}" class="hover:text-ceeac-primary font-mono">{{ $liquidation->numero }}</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">{{ __('Modification') }}</span>
                </nav>
                <h2 class="page-title">{{ __('Modifier la liquidation') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-4">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('liquidations.update', $liquidation) }}" class="space-y-6">
                    @csrf @method('PUT')
                    @include('liquidations._form')
                    <div>
                        <x-input-label for="motif" :value="__('Motif de la modification (versionné)')" />
                        <x-text-input id="motif" name="motif" class="block mt-1 w-full" maxlength="1000" :value="old('motif')" />
                    </div>
                    <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-gray-100">
                        <button class="btn btn-primary">{{ __('Enregistrer les modifications') }}</button>
                        <a href="{{ route('liquidations.show', $liquidation) }}" class="btn btn-ghost">{{ __('Annuler') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
