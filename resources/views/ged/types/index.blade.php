<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <span>Gestion Documentaire</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">Types</span>
                </nav>
                <h2 class="page-title">Types de documents</h2>
            </div>
            @can('create', App\Models\GedTypeDocument::class)
                <a href="{{ route('ged.types-documents.create') }}" class="btn btn-primary btn-sm">{{ __('Nouveau type') }}</a>
            @endcan
        </div>
    </x-slot>

    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Libellé</th>
                    <th>Catégorie</th>
                    <th>Préfixe</th>
                    <th class="text-center">Signable</th>
                    <th class="text-center">Documents</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($types as $type)
                    <tr>
                        <td class="font-mono">{{ $type->code }}</td>
                        <td class="font-medium">{{ $type->libelle }}</td>
                        <td>{{ $type->categorie?->libelle ?? '—' }}</td>
                        <td class="font-mono text-xs">{{ $type->prefixe_reference ?? '—' }}</td>
                        <td class="text-center">{{ $type->est_signable ? '✓' : '—' }}</td>
                        <td class="text-center tabular-nums">{{ $type->documents_count }}</td>
                        <td>
                            <span class="badge {{ $type->statut === 'actif' ? 'badge-success' : 'badge-neutral' }}">{{ ucfirst($type->statut) }}</span>
                        </td>
                        <td class="text-right space-x-2 whitespace-nowrap">
                            @can('update', $type)
                                <a href="{{ route('ged.types-documents.edit', $type) }}" class="text-ceeac-bright hover:underline">Modifier</a>
                            @endcan
                            @can('delete', $type)
                                <form class="inline" method="POST" action="{{ route('ged.types-documents.destroy', $type) }}"
                                      onsubmit="return confirm('Archiver ce type ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Archiver</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-8 text-gray-500">Aucun type de document.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $types->links() }}</div>
</x-app-layout>
