<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <a href="{{ route('ged.documents.index') }}">Documents</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold font-mono">{{ $document->reference }}</span>
                </nav>
                <h2 class="page-title">Modifier le document</h2>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-4">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('ged.documents.update', $document) }}" class="space-y-6">
                    @csrf
                    @method('PUT')
                    @include('ged.documents._form', [
                        'document' => $document,
                        'categories' => $categories,
                        'types' => $types,
                        'dossiers' => $dossiers,
                        'exercices' => $exercices,
                        'niveaux' => $niveaux,
                    ])

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <button class="btn btn-primary">{{ __('Enregistrer les modifications') }}</button>
                        <a href="{{ route('ged.documents.show', $document) }}" class="btn btn-ghost">{{ __('Annuler') }}</a>
                    </div>
                </form>
                <p class="mt-4 text-xs text-gray-500">
                    Pour remplacer le fichier, utilisez « Nouvelle version » depuis la fiche du document :
                    les versions antérieures sont conservées.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
