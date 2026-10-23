<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <span>Gestion Documentaire</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">Catégories</span>
                </nav>
                <h2 class="page-title">Catégories documentaires</h2>
            </div>
            @can('create', App\Models\GedCategorie::class)
                <a href="{{ route('ged.categories.create') }}" class="btn btn-primary btn-sm">{{ __('Nouvelle catégorie') }}</a>
            @endcan
        </div>
    </x-slot>

    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Libellé</th>
                    <th>Parente</th>
                    <th class="text-center">Types</th>
                    <th class="text-center">Documents</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $categorie)
                    <tr>
                        <td class="font-mono">{{ $categorie->code }}</td>
                        <td class="font-medium">{{ $categorie->libelle }}</td>
                        <td>{{ $categorie->parent?->libelle ?? '—' }}</td>
                        <td class="text-center tabular-nums">{{ $categorie->types_documents_count }}</td>
                        <td class="text-center tabular-nums">{{ $categorie->documents_count }}</td>
                        <td>
                            <span class="badge {{ $categorie->statut === 'actif' ? 'badge-success' : 'badge-neutral' }}">
                                {{ ucfirst($categorie->statut) }}
                            </span>
                        </td>
                        <td class="text-right space-x-2 whitespace-nowrap">
                            @can('update', $categorie)
                                <a href="{{ route('ged.categories.edit', $categorie) }}" class="text-ceeac-bright hover:underline">Modifier</a>
                            @endcan
                            @can('delete', $categorie)
                                <form class="inline" method="POST" action="{{ route('ged.categories.destroy', $categorie) }}"
                                      onsubmit="return confirm('Archiver cette catégorie ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Archiver</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-8 text-gray-500">Aucune catégorie.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $categories->links() }}</div>
</x-app-layout>
