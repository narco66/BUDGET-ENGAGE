<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <span>Gestion Documentaire</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">Archives</span>
                </nav>
                <h2 class="page-title">Archives électroniques</h2>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('ged.archives.index') }}" class="grid md:grid-cols-4 gap-3">
                    <input type="number" name="annee" value="{{ request('annee') }}" placeholder="Année d'archivage" class="form-input" />
                    <input type="text" name="module" value="{{ request('module') }}" placeholder="Module" class="form-input" />
                    <div class="md:col-span-2 flex items-center gap-2">
                        <button class="btn btn-secondary btn-sm" type="submit">{{ __('Filtrer') }}</button>
                        <a href="{{ route('ged.archives.index') }}" class="btn btn-ghost btn-sm">{{ __('Réinitialiser') }}</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Titre</th>
                        <th>Type</th>
                        <th>Archivé par</th>
                        <th>Date d'archivage</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $document)
                        <tr>
                            <td class="font-mono text-xs whitespace-nowrap">{{ $document->reference }}</td>
                            <td class="font-medium">{{ $document->titre }}</td>
                            <td>{{ $document->typeDocument?->libelle ?? '—' }}</td>
                            <td>{{ $document->archiveur?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap">{{ $document->date_archivage?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('ged.documents.show', $document) }}" class="text-gray-600 hover:text-ceeac-primary">Voir</a>
                                @can('restore', $document)
                                    <form class="inline" method="POST" action="{{ route('ged.documents.restore-archive', $document) }}"
                                          onsubmit="return confirm('Restaurer ce document depuis les archives ?');">
                                        @csrf
                                        <button type="submit" class="text-ceeac-bright hover:underline">Restaurer</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-8 text-gray-500">Aucune archive.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $documents->links() }}
    </div>
</x-app-layout>
