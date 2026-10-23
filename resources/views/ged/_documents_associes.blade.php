{{--
    Partial réutilisable affichant les documents GED rattachés à une entité métier.
    Usage : @include('ged._documents_associes', ['entity' => $engagement])
    L'entité doit utiliser le trait App\Models\Concerns\HasGedDocuments.
--}}
@can('ged.view')
    @php($gedDocs = method_exists($entity, 'gedDocuments') ? $entity->gedDocuments()->get() : collect())
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold text-ceeac-primary">Documents associés (GED)</h3>
        </div>
        <div class="card-body overflow-x-auto">
            @if ($gedDocs->isEmpty())
                <p class="text-sm text-gray-500 py-2">Aucun document GED rattaché.</p>
            @else
                <table class="data-table">
                    <thead>
                        <tr><th>Référence</th><th>Titre</th><th>Statut</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($gedDocs as $doc)
                            <tr>
                                <td class="font-mono text-xs">{{ $doc->reference }}</td>
                                <td class="font-medium">{{ $doc->titre }}</td>
                                <td><span class="badge {{ $doc->statut->badge() }}">{{ $doc->statut->libelle() }}</span></td>
                                <td class="text-right">
                                    <a href="{{ route('ged.documents.show', $doc) }}" class="text-ceeac-bright hover:underline">Ouvrir</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endcan
