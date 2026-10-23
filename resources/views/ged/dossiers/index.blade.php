<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <span>Gestion Documentaire</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">Dossiers</span>
                </nav>
                <h2 class="page-title">Dossiers documentaires</h2>
            </div>
            @can('create', App\Models\GedDossier::class)
                <a href="{{ route('ged.dossiers.create') }}" class="btn btn-primary btn-sm">{{ __('Nouveau dossier') }}</a>
            @endcan
        </div>
    </x-slot>

    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Libellé</th>
                    <th>Code</th>
                    <th>Parent</th>
                    <th>Exercice</th>
                    <th class="text-center">Documents</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dossiers as $dossier)
                    <tr>
                        <td class="font-medium">{{ $dossier->libelle }}</td>
                        <td class="font-mono text-xs">{{ $dossier->code ?? '—' }}</td>
                        <td>{{ $dossier->parent?->libelle ?? '—' }}</td>
                        <td>{{ $dossier->exerciceBudgetaire?->annee ?? '—' }}</td>
                        <td class="text-center tabular-nums">{{ $dossier->documents_count }}</td>
                        <td>
                            <span class="badge {{ $dossier->statut === 'actif' ? 'badge-success' : 'badge-neutral' }}">{{ ucfirst($dossier->statut) }}</span>
                        </td>
                        <td class="text-right space-x-2 whitespace-nowrap">
                            @can('update', $dossier)
                                <a href="{{ route('ged.dossiers.edit', $dossier) }}" class="text-ceeac-bright hover:underline">Modifier</a>
                            @endcan
                            @can('delete', $dossier)
                                <form class="inline" method="POST" action="{{ route('ged.dossiers.destroy', $dossier) }}"
                                      onsubmit="return confirm('Archiver ce dossier ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Archiver</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-8 text-gray-500">Aucun dossier.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $dossiers->links() }}</div>
</x-app-layout>
