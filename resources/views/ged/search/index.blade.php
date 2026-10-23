<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <span>Gestion Documentaire</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">Recherche avancée</span>
                </nav>
                <h2 class="page-title">Recherche avancée</h2>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        {{-- Formulaire de critères --}}
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('ged.search.index') }}" class="space-y-4">
                    <div class="grid md:grid-cols-3 gap-3">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Mots-clés (titre, réf., description, OCR)" class="form-input md:col-span-2" />
                        <input type="text" name="reference" value="{{ request('reference') }}" placeholder="Référence" class="form-input" />

                        <select name="type_document_id" class="form-select">
                            <option value="">Type — tous</option>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}" @selected((int) request('type_document_id') === $type->id)>{{ $type->libelle }}</option>
                            @endforeach
                        </select>
                        <select name="categorie_id" class="form-select">
                            <option value="">Catégorie — toutes</option>
                            @foreach ($categories as $categorie)
                                <option value="{{ $categorie->id }}" @selected((int) request('categorie_id') === $categorie->id)>{{ $categorie->libelle }}</option>
                            @endforeach
                        </select>
                        <select name="dossier_id" class="form-select">
                            <option value="">Dossier — tous</option>
                            @foreach ($dossiers as $dossier)
                                <option value="{{ $dossier->id }}" @selected((int) request('dossier_id') === $dossier->id)>{{ $dossier->libelle }}</option>
                            @endforeach
                        </select>

                        <select name="statut" class="form-select">
                            <option value="">Statut — tous</option>
                            @foreach ($statuts as $statut)
                                <option value="{{ $statut->value }}" @selected(request('statut') === $statut->value)>{{ $statut->libelle() }}</option>
                            @endforeach
                        </select>
                        <select name="niveau_confidentialite" class="form-select">
                            <option value="">Confidentialité — toutes</option>
                            @foreach ($niveaux as $niveau)
                                <option value="{{ $niveau->value }}" @selected(request('niveau_confidentialite') === $niveau->value)>{{ $niveau->libelle() }}</option>
                            @endforeach
                        </select>
                        <select name="exercice_budgetaire_id" class="form-select">
                            <option value="">Exercice — tous</option>
                            @foreach ($exercices as $exercice)
                                <option value="{{ $exercice->id }}" @selected((int) request('exercice_budgetaire_id') === $exercice->id)>{{ $exercice->annee }}</option>
                            @endforeach
                        </select>

                        <input type="text" name="module_source" value="{{ request('module_source') }}" placeholder="Module (engagement, paiement…)" class="form-input" />
                        <input type="number" name="module_source_id" value="{{ request('module_source_id') }}" placeholder="N° pièce métier" class="form-input" />
                        <select name="tag_id" class="form-select">
                            <option value="">Tag — tous</option>
                            @foreach ($tags as $tag)
                                <option value="{{ $tag->id }}" @selected((int) request('tag_id') === $tag->id)>{{ $tag->libelle }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Plages de dates --}}
                    <div class="grid md:grid-cols-4 gap-3">
                        @foreach ([
                            'date_document' => 'Date du document',
                            'date_depot' => 'Date de dépôt',
                            'date_expiration' => 'Expiration',
                            'date_archivage' => 'Archivage',
                        ] as $field => $label)
                            <div>
                                <label class="form-label">{{ $label }}</label>
                                <div class="flex gap-1">
                                    <input type="date" name="{{ $field }}_from" value="{{ request($field.'_from') }}" class="form-input" />
                                    <input type="date" name="{{ $field }}_to" value="{{ request($field.'_to') }}" class="form-input" />
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button class="btn btn-primary btn-sm" type="submit">{{ __('Rechercher') }}</button>
                        <a href="{{ route('ged.search.index') }}" class="btn btn-ghost btn-sm">{{ __('Réinitialiser') }}</a>
                        @if ($documents)
                            <span class="text-gray-300">|</span>
                            <a href="{{ route('ged.exports.documentaire.pdf', request()->query()) }}" class="btn btn-secondary btn-sm">Export PDF</a>
                            <a href="{{ route('ged.exports.documentaire.excel', request()->query()) }}" class="btn btn-secondary btn-sm">Export Excel</a>
                            <a href="{{ route('ged.exports.documentaire.csv', request()->query()) }}" class="btn btn-secondary btn-sm">Export CSV</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Résultats --}}
        @if ($documents)
            <div class="card overflow-x-auto">
                <div class="card-header">
                    <h3 class="font-semibold text-ceeac-primary">Résultats ({{ $documents->total() }})</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Référence</th><th>Titre</th><th>Type</th><th>Statut</th>
                            <th>Confidentialité</th><th>Date document</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $document)
                            <tr>
                                <td class="font-mono text-xs whitespace-nowrap">{{ $document->reference }}</td>
                                <td class="font-medium">{{ $document->titre }}</td>
                                <td>{{ $document->typeDocument?->libelle ?? '—' }}</td>
                                <td><span class="badge {{ $document->statut->badge() }}">{{ $document->statut->libelle() }}</span></td>
                                <td><span class="badge {{ $document->niveau_confidentialite->badge() }}">{{ $document->niveau_confidentialite->libelle() }}</span></td>
                                <td class="whitespace-nowrap">{{ $document->date_document?->format('d/m/Y') ?? '—' }}</td>
                                <td class="text-right"><a href="{{ route('ged.documents.show', $document) }}" class="text-gray-600 hover:text-ceeac-primary">Voir</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-8 text-gray-500">Aucun résultat pour ces critères.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $documents->links() }}
        @else
            <div class="alert-info">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span>Saisissez au moins un critère puis lancez la recherche.</span>
            </div>
        @endif
    </div>
</x-app-layout>
