<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <span>Gestion Documentaire</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">Circuits de validation</span>
                </nav>
                <h2 class="page-title">Circuits de validation</h2>
            </div>
            @can('create', App\Models\GedWorkflow::class)
                <a href="{{ route('ged.workflows.create') }}" class="btn btn-primary btn-sm">{{ __('Nouveau circuit') }}</a>
            @endcan
        </div>
    </x-slot>

    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Libellé</th>
                    <th>Type</th>
                    <th class="text-center">Étapes</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($workflows as $workflow)
                    <tr>
                        <td class="font-mono">{{ $workflow->code }}</td>
                        <td class="font-medium">{{ $workflow->libelle }}</td>
                        <td>{{ $workflow->typeDocument?->libelle ?? 'Tous' }}</td>
                        <td class="text-center tabular-nums">{{ $workflow->etapes_count }}</td>
                        <td>
                            <span class="badge {{ $workflow->est_actif ? 'badge-success' : 'badge-neutral' }}">{{ $workflow->est_actif ? 'Actif' : 'Inactif' }}</span>
                            @if ($workflow->est_par_defaut)<span class="badge badge-info">Défaut</span>@endif
                        </td>
                        <td class="text-right space-x-2 whitespace-nowrap">
                            @can('update', $workflow)
                                <a href="{{ route('ged.workflows.edit', $workflow) }}" class="text-ceeac-bright hover:underline">Modifier</a>
                            @endcan
                            @can('delete', $workflow)
                                <form class="inline" method="POST" action="{{ route('ged.workflows.destroy', $workflow) }}"
                                      onsubmit="return confirm('Archiver ce circuit ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Archiver</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-8 text-gray-500">Aucun circuit. Un circuit générique à une étape est appliqué par défaut.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $workflows->links() }}</div>
</x-app-layout>
