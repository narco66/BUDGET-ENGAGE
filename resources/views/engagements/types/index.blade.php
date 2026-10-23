<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <span>Engagements</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">Types d'engagement</span>
                </nav>
                <h2 class="page-title">Types d'engagement</h2>
            </div>
            @can('create', App\Models\EngagementType::class)
                <a href="{{ route('engagements.types.create') }}" class="btn btn-primary btn-sm">{{ __('Nouveau type') }}</a>
            @endcan
        </div>
    </x-slot>

    <div class="space-y-4">
        <x-auth-session-status class="mb-2" :status="session('status')" />

        <div class="card overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Libellé</th>
                        <th>Famille</th>
                        <th class="text-center">Champs spéc.</th>
                        <th class="text-center">Engagements</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($types as $type)
                        <tr>
                            <td class="font-mono">{{ $type->code }}</td>
                            <td class="font-medium">{{ $type->libelle }}</td>
                            <td>{{ $type->famille ?? '—' }}</td>
                            <td class="text-center tabular-nums">{{ count($type->champs_specifiques ?? []) }}</td>
                            <td class="text-center tabular-nums">{{ $type->engagements_count }}</td>
                            <td><span class="badge {{ $type->est_actif ? 'badge-success' : 'badge-neutral' }}">{{ $type->est_actif ? 'Actif' : 'Inactif' }}</span></td>
                            <td class="text-right space-x-2 whitespace-nowrap">
                                @can('update', $type)
                                    <a href="{{ route('engagements.types.edit', $type) }}" class="text-ceeac-bright hover:underline">Modifier</a>
                                @endcan
                                @can('delete', $type)
                                    <form class="inline" method="POST" action="{{ route('engagements.types.destroy', $type) }}"
                                          onsubmit="return confirm('Archiver ce type ?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Archiver</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-8 text-gray-500">Aucun type d'engagement.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $types->links() }}
    </div>
</x-app-layout>
