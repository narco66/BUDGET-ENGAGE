<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <a href="{{ route('ged.conservation.index') }}">Conservation</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">Règles</span>
                </nav>
                <h2 class="page-title">Règles de conservation</h2>
            </div>
            @can('create', App\Models\GedConservationRule::class)
                <a href="{{ route('ged.conservation-rules.create') }}" class="btn btn-primary btn-sm">{{ __('Nouvelle règle') }}</a>
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
                    <th class="text-center">Durée (mois)</th>
                    <th>Sort final</th>
                    <th class="text-center">Obligatoire</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rules as $rule)
                    <tr>
                        <td class="font-mono">{{ $rule->code }}</td>
                        <td class="font-medium">{{ $rule->libelle }}</td>
                        <td>{{ $rule->typeDocument?->libelle ?? 'Tous' }}</td>
                        <td class="text-center tabular-nums">{{ $rule->duree_conservation_mois }}</td>
                        <td>{{ $rule->sort_final?->libelle() ?? '—' }}</td>
                        <td class="text-center">{{ $rule->conservation_obligatoire ? '✓' : '—' }}</td>
                        <td class="text-right space-x-2 whitespace-nowrap">
                            @can('update', $rule)
                                <a href="{{ route('ged.conservation-rules.edit', $rule) }}" class="text-ceeac-bright hover:underline">Modifier</a>
                            @endcan
                            @can('delete', $rule)
                                <form class="inline" method="POST" action="{{ route('ged.conservation-rules.destroy', $rule) }}"
                                      onsubmit="return confirm('Archiver cette règle ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Archiver</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-8 text-gray-500">Aucune règle de conservation.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $rules->links() }}</div>
</x-app-layout>
