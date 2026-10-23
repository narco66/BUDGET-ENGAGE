<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <span>Gestion Documentaire</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">{{ $titre }}</span>
                </nav>
                <h2 class="page-title">{{ $titre }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        @isset($sous_titre)
            <p class="text-sm text-gray-500">{{ $sous_titre }}</p>
        @endisset

        <div class="card overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Référence</th><th>Titre</th><th>Type</th>
                        <th>Statut</th><th>Confidentialité</th><th>Expiration</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $document)
                        <tr>
                            <td class="font-mono text-xs whitespace-nowrap">{{ $document->reference }}</td>
                            <td class="font-medium">{{ $document->titre }}</td>
                            <td>{{ $document->typeDocument?->libelle ?? '—' }}</td>
                            <td><span class="badge {{ $document->statut->badge() }}">{{ $document->statut->libelle() }}</span></td>
                            <td><span class="badge {{ $document->niveau_confidentialite->badge() }}">{{ $document->niveau_confidentialite->libelle() }}</span></td>
                            <td class="whitespace-nowrap">{{ $document->date_expiration?->format('d/m/Y') ?? '—' }}</td>
                            <td class="text-right"><a href="{{ route('ged.documents.show', $document) }}" class="text-gray-600 hover:text-ceeac-primary">Voir</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-8 text-gray-500">Aucun document.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $documents->links() }}
    </div>
</x-app-layout>
