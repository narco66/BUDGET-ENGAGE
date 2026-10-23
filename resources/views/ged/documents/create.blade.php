<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <a href="{{ route('ged.documents.index') }}">Documents</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">Dépôt</span>
                </nav>
                <h2 class="page-title">Déposer un document</h2>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-4">
        <div class="alert-info">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>La référence documentaire (CEEAC/GED/…) et l'empreinte SHA-256 sont générées
                  automatiquement au dépôt. Le fichier est conservé sur un stockage privé sécurisé.</span>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('ged.documents.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @include('ged.documents._form', [
                        'categories' => $categories,
                        'types' => $types,
                        'dossiers' => $dossiers,
                        'exercices' => $exercices,
                        'niveaux' => $niveaux,
                    ])

                    <div>
                        <x-input-label for="fichier" :value="__('Fichier à déposer')" />
                        <input id="fichier" name="fichier" type="file" required
                               class="block mt-1 w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0
                                      file:bg-ceeac-light file:px-4 file:py-2 file:text-ceeac-primary file:font-semibold
                                      hover:file:bg-ceeac-sky/30 cursor-pointer" />
                        <p class="mt-1 text-xs text-gray-500">
                            Formats acceptés : PDF, Word, Excel, PowerPoint, images, txt, csv, zip — 50 Mo max.
                        </p>
                        <x-input-error class="mt-2" :messages="$errors->get('fichier')" />
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-2">
                        <button class="btn btn-primary">{{ __('Déposer le document') }}</button>
                        <a href="{{ route('ged.documents.index') }}" class="btn btn-ghost">{{ __('Annuler') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
