<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <span>Gestion Documentaire</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">Conservation</span>
                </nav>
                <h2 class="page-title">Conservation numérique</h2>
            </div>
            @can('create', App\Models\GedConservationRule::class)
                <a href="{{ route('ged.conservation-rules.index') }}" class="btn btn-secondary btn-sm">Règles de conservation</a>
            @endcan
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Documents à échéance --}}
        <div class="card overflow-x-auto">
            <div class="card-header">
                <h3 class="font-semibold text-ceeac-primary">Documents arrivant à échéance (60 jours)</h3>
            </div>
            <div class="card-body">
                <table class="data-table">
                    <thead>
                        <tr><th>Référence</th><th>Titre</th><th>Type</th><th>Expiration</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($aEcheance as $document)
                            <tr>
                                <td class="font-mono text-xs">{{ $document->reference }}</td>
                                <td class="font-medium">{{ $document->titre }}</td>
                                <td>{{ $document->typeDocument?->libelle ?? '—' }}</td>
                                <td class="whitespace-nowrap text-amber-700">{{ $document->date_expiration?->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-6 text-gray-500">Aucun document à échéance.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">{{ $aEcheance->links() }}</div>
            </div>
        </div>

        {{-- Documents expirés --}}
        <div class="card overflow-x-auto">
            <div class="card-header">
                <h3 class="font-semibold text-ceeac-primary">Documents expirés (échéance dépassée)</h3>
            </div>
            <div class="card-body">
                <table class="data-table">
                    <thead>
                        <tr><th>Référence</th><th>Titre</th><th>Type</th><th>Expiration</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse ($expires as $document)
                            <tr>
                                <td class="font-mono text-xs">{{ $document->reference }}</td>
                                <td class="font-medium">{{ $document->titre }}</td>
                                <td>{{ $document->typeDocument?->libelle ?? '—' }}</td>
                                <td class="whitespace-nowrap text-red-700">{{ $document->date_expiration?->format('d/m/Y') }}</td>
                                <td class="text-right whitespace-nowrap">
                                    <a href="{{ route('ged.documents.show', $document) }}" class="text-gray-600 hover:text-ceeac-primary">Voir</a>
                                    @can('archive', $document)
                                        <form class="inline ml-2" method="POST" action="{{ route('ged.documents.expirer', $document) }}"
                                              onsubmit="return confirm('Marquer ce document comme expiré ?');">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:text-red-800">Marquer expiré</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-6 text-gray-500">Aucun document expiré.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">{{ $expires->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
