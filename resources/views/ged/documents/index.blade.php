<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <span>Gestion Documentaire</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">Documents</span>
                </nav>
                <h2 class="page-title">Documents</h2>
            </div>
            @can('create', App\Models\GedDocument::class)
                <a href="{{ route('ged.documents.create') }}" class="btn btn-primary btn-sm">{{ __('Déposer un document') }}</a>
            @endcan
        </div>
    </x-slot>

    <div class="space-y-4">
        {{-- Filtres --}}
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('ged.documents.index') }}" class="grid md:grid-cols-4 gap-3">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Titre, référence, description"
                           class="form-input md:col-span-2" />
                    <select name="statut" class="form-select">
                        <option value="">{{ __('Tous les statuts') }}</option>
                        @foreach (App\Enums\StatutDocumentGed::cases() as $statut)
                            <option value="{{ $statut->value }}" @selected(request('statut') === $statut->value)>{{ $statut->libelle() }}</option>
                        @endforeach
                    </select>
                    <select name="type_document_id" class="form-select">
                        <option value="">{{ __('Tous les types') }}</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}" @selected((int) request('type_document_id') === $type->id)>{{ $type->libelle }}</option>
                        @endforeach
                    </select>
                    <select name="niveau_confidentialite" class="form-select">
                        <option value="">{{ __('Toutes confidentialités') }}</option>
                        @foreach (App\Enums\NiveauConfidentialiteGed::cases() as $niveau)
                            <option value="{{ $niveau->value }}" @selected(request('niveau_confidentialite') === $niveau->value)>{{ $niveau->libelle() }}</option>
                        @endforeach
                    </select>
                    <div class="md:col-span-4 flex items-center gap-2">
                        <button class="btn btn-secondary btn-sm" type="submit">{{ __('Filtrer') }}</button>
                        <a href="{{ route('ged.documents.index') }}" class="btn btn-ghost btn-sm">{{ __('Réinitialiser') }}</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="card overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Titre</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th>Confidentialité</th>
                        <th class="text-center">Version</th>
                        <th>Déposé par</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $document)
                        <tr>
                            <td class="font-mono text-xs whitespace-nowrap">{{ $document->reference ?? '—' }}</td>
                            <td class="font-medium">{{ $document->titre }}</td>
                            <td>{{ $document->typeDocument?->libelle ?? '—' }}</td>
                            <td><span class="badge {{ $document->statut->badge() }}">{{ $document->statut->libelle() }}</span></td>
                            <td><span class="badge {{ $document->niveau_confidentialite->badge() }}">{{ $document->niveau_confidentialite->libelle() }}</span></td>
                            <td class="text-center tabular-nums">v{{ $document->version_courante }}</td>
                            <td>{{ $document->createur?->name ?? '—' }}</td>
                            <td class="text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('ged.documents.show', $document) }}" class="text-gray-600 hover:text-ceeac-primary">Voir</a>
                                @can('download', $document)
                                    <a href="{{ route('ged.documents.download', $document) }}" class="text-ceeac-bright hover:underline">Télécharger</a>
                                @endcan
                                @can('update', $document)
                                    <a href="{{ route('ged.documents.edit', $document) }}" class="text-ceeac-bright hover:underline">Modifier</a>
                                @endcan
                                @can('delete', $document)
                                    <form class="inline" method="POST" action="{{ route('ged.documents.destroy', $document) }}"
                                          onsubmit="return confirm('Placer ce document dans la corbeille ?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Supprimer</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-8 text-gray-500">Aucun document.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $documents->links() }}
    </div>
</x-app-layout>
