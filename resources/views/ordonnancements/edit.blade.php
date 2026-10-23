<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <a href="{{ route('ordonnancements.index') }}" class="hover:text-ceeac-primary">{{ __('Ordonnancements') }}</a>
                    <span class="text-gray-300">/</span>
                    <a href="{{ route('ordonnancements.show', $ordonnancement) }}" class="hover:text-ceeac-primary font-mono">{{ $ordonnancement->numero }}</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">{{ __('Modification') }}</span>
                </nav>
                <h2 class="page-title">{{ __('Modifier l’ordonnancement') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-4">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('ordonnancements.update', $ordonnancement) }}" class="space-y-6">
                    @csrf @method('PUT')
                    @include('ordonnancements._form')
                    <div>
                        <x-input-label for="motif" :value="__('Motif de la modification (versionné)')" />
                        <x-text-input id="motif" name="motif" class="block mt-1 w-full" maxlength="1000" :value="old('motif')" />
                    </div>
                    <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-gray-100">
                        <button class="btn btn-primary">{{ __('Enregistrer les modifications') }}</button>
                        <a href="{{ route('ordonnancements.show', $ordonnancement) }}" class="btn btn-ghost">{{ __('Annuler') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
