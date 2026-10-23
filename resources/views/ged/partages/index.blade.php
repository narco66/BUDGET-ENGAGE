<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <span>Gestion Documentaire</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">Documents partagés</span>
                </nav>
                <h2 class="page-title">Documents partagés</h2>
            </div>
        </div>
    </x-slot>

    <div class="card overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Document</th><th>Partagé par</th><th>Partagé avec</th>
                    <th>Accès</th><th>Expiration</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($partages as $partage)
                    <tr>
                        <td class="font-medium">
                            <a href="{{ route('ged.documents.show', $partage->document) }}" class="text-ceeac-bright hover:underline">
                                {{ $partage->document?->titre ?? '—' }}
                            </a>
                        </td>
                        <td>{{ $partage->partagePar?->name ?? '—' }}</td>
                        <td>{{ $partage->partageAvec?->name ?? ($partage->partage_avec_role ? 'Rôle : '.$partage->partage_avec_role : 'Lien') }}</td>
                        <td><span class="badge badge-info">{{ ucfirst($partage->niveau_acces) }}</span></td>
                        <td class="whitespace-nowrap">{{ $partage->date_expiration?->format('d/m/Y') ?? '—' }}</td>
                        <td class="text-right">
                            @can('ged.share')
                                <form class="inline" method="POST" action="{{ route('ged.partages.destroy', $partage) }}"
                                      onsubmit="return confirm('Révoquer ce partage ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Révoquer</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-8 text-gray-500">Aucun partage actif.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $partages->links() }}</div>
</x-app-layout>
