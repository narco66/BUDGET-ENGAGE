<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <a href="{{ route('ged.dossiers.index') }}">Dossiers</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">{{ $dossier->libelle }}</span>
                </nav>
                <h2 class="page-title">Modifier le dossier</h2>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('ged.dossiers.update', $dossier) }}" class="space-y-6">
                    @csrf
                    @method('PUT')
                    @include('ged.dossiers._form', ['dossier' => $dossier, 'parents' => $parents, 'categories' => $categories, 'exercices' => $exercices])
                    <div class="flex items-center gap-3">
                        <button class="btn btn-primary">{{ __('Enregistrer') }}</button>
                        <a href="{{ route('ged.dossiers.index') }}" class="btn btn-ghost">{{ __('Annuler') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
