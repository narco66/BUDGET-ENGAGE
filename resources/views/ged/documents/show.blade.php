<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <a href="{{ route('ged.documents.index') }}">Documents</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold font-mono">{{ $document->reference }}</span>
                </nav>
                <h2 class="page-title">{{ $document->titre }}</h2>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('download', $document)
                    @if ($fichierPresent)
                        <a href="{{ route('ged.documents.download', $document) }}" class="btn btn-secondary btn-sm">Télécharger</a>
                    @endif
                @endcan
                @can('update', $document)
                    <a href="{{ route('ged.documents.edit', $document) }}" class="btn btn-secondary btn-sm">Modifier</a>
                @endcan
            </div>
        </div>
    </x-slot>

    @php
        $taille = $document->taille ?? 0;
        $tailleLisible = $taille >= 1048576
            ? number_format($taille / 1048576, 2, ',', ' ').' Mo'
            : ($taille >= 1024 ? number_format($taille / 1024, 1, ',', ' ').' Ko' : $taille.' o');
    @endphp

    <div class="max-w-6xl mx-auto space-y-4">
        @unless ($fichierPresent)
            <div class="alert-warning">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Le fichier de la version courante est introuvable sur le stockage.</span>
            </div>
        @endunless

        <div class="grid lg:grid-cols-3 gap-4">
            <div class="card lg:col-span-2">
                <div class="card-body text-sm space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="badge {{ $document->statut->badge() }}">{{ $document->statut->libelle() }}</span>
                        <span class="badge {{ $document->niveau_confidentialite->badge() }}">{{ $document->niveau_confidentialite->libelle() }}</span>
                        <span class="badge badge-neutral">Version {{ $document->version_courante }}</span>
                        @if ($document->est_signe)
                            <span class="badge badge-success">Signé</span>
                        @endif
                        @if ($document->est_archive)
                            <span class="badge badge-neutral">Archivé</span>
                        @endif
                    </div>

                    <div class="grid md:grid-cols-2 gap-3">
                        <div><span class="text-gray-500">Type</span><p class="font-medium">{{ $document->typeDocument?->libelle ?? '—' }}</p></div>
                        <div><span class="text-gray-500">Catégorie</span><p class="font-medium">{{ $document->categorie?->libelle ?? '—' }}</p></div>
                        <div><span class="text-gray-500">Dossier</span><p class="font-medium">{{ $document->dossier?->libelle ?? '—' }}</p></div>
                        <div><span class="text-gray-500">Exercice</span><p class="font-medium">{{ $document->exerciceBudgetaire?->annee ?? '—' }}</p></div>
                        <div><span class="text-gray-500">Module rattaché</span><p class="font-medium">{{ $document->module_source ?? '—' }}</p></div>
                        <div><span class="text-gray-500">Date du document</span><p class="font-medium">{{ $document->date_document?->format('d/m/Y') ?? '—' }}</p></div>
                        <div><span class="text-gray-500">Date de dépôt</span><p class="font-medium">{{ $document->date_depot?->format('d/m/Y H:i') ?? '—' }}</p></div>
                        <div><span class="text-gray-500">Expiration</span><p class="font-medium">{{ $document->date_expiration?->format('d/m/Y') ?? '—' }}</p></div>
                    </div>

                    @if ($document->description)
                        <div>
                            <span class="text-gray-500">Description</span>
                            <p class="mt-1 text-gray-800 whitespace-pre-wrap">{{ $document->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card h-fit">
                <div class="card-body text-sm space-y-3">
                    <div><span class="text-gray-500">Fichier original</span><p class="font-medium break-all">{{ $document->nom_original ?? '—' }}</p></div>
                    <div><span class="text-gray-500">Taille</span><p class="font-medium">{{ $tailleLisible }}</p></div>
                    <div><span class="text-gray-500">Type MIME</span><p class="font-medium">{{ $document->mime_type ?? '—' }}</p></div>
                    <div>
                        <span class="text-gray-500">Empreinte SHA-256</span>
                        <p class="font-mono text-[11px] break-all text-gray-700">{{ $document->hash_sha256 ?? '—' }}</p>
                    </div>
                    <div><span class="text-gray-500">Déposé par</span><p class="font-medium">{{ $document->createur?->name ?? '—' }}</p></div>
                </div>
            </div>
        </div>

        {{-- Actions : circuit, archivage --}}
        <div class="grid lg:grid-cols-2 gap-4">
            {{-- Circuit de validation --}}
            <div class="card">
                <div class="card-header"><h3 class="font-semibold text-ceeac-primary">Circuit de validation</h3></div>
                <div class="card-body space-y-3 text-sm">
                    @if ($document->validations->isNotEmpty())
                        <table class="data-table">
                            <thead><tr><th>Étape</th><th>Statut</th><th>Validateur</th><th>Date</th></tr></thead>
                            <tbody>
                                @foreach ($document->validations as $validation)
                                    <tr>
                                        <td>{{ $validation->ordre }}. {{ $validation->libelle_etape }}</td>
                                        <td><span class="badge {{ $validation->statut->value === 'valide' ? 'badge-success' : ($validation->statut->value === 'en_attente' ? 'badge-warning' : 'badge-danger') }}">{{ $validation->statut->libelle() }}</span></td>
                                        <td>{{ $validation->validateur?->name ?? '—' }}</td>
                                        <td class="whitespace-nowrap">{{ $validation->date_action?->format('d/m/Y H:i') ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500">Aucun circuit en cours.</p>
                    @endif

                    @if ($etapeCourante && $peutDecider)
                        <form method="POST" action="{{ route('ged.documents.workflow.decide', $document) }}" class="space-y-2 border-t border-gray-100 pt-3">
                            @csrf
                            <label class="form-label">Décision pour « {{ $etapeCourante->libelle_etape }} »</label>
                            <div class="flex flex-wrap gap-2 items-end">
                                <select name="decision" class="form-select flex-1" required>
                                    <option value="valide">Valider</option>
                                    <option value="retourne">Retourner à l'auteur</option>
                                    <option value="rejete">Rejeter</option>
                                </select>
                                <input type="text" name="commentaire" class="form-input flex-1" placeholder="Commentaire / motif" maxlength="1000" />
                                <button class="btn btn-primary btn-sm">Enregistrer</button>
                            </div>
                        </form>
                    @endif

                    @can('update', $document)
                        @if (! in_array($document->statut->value, ['en_validation', 'archive']) && ! $document->est_archive)
                            <form method="POST" action="{{ route('ged.documents.workflow.submit', $document) }}" class="border-t border-gray-100 pt-3">
                                @csrf
                                <button class="btn btn-secondary btn-sm">Soumettre au circuit de validation</button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>

            {{-- Signature & archivage --}}
            <div class="card">
                <div class="card-header"><h3 class="font-semibold text-ceeac-primary">Signature &amp; archivage</h3></div>
                <div class="card-body space-y-3 text-sm">
                    @forelse ($document->signatures as $signature)
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-50 pb-2">
                            <div>
                                <span class="font-medium">{{ $signature->signataire?->name }}</span>
                                <span class="badge {{ $signature->statut->value === 'signe' ? 'badge-success' : ($signature->statut->value === 'en_attente' ? 'badge-warning' : 'badge-danger') }} ml-1">{{ $signature->statut->libelle() }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('ged.signatures.verify', $signature) }}" class="text-ceeac-bright hover:underline">Vérifier</a>
                                @if ($signature->statut->value === 'en_attente' && ($signature->signataire_id === auth()->id() || auth()->user()->can('ged.admin')))
                                    <form method="POST" action="{{ route('ged.signatures.sign', $signature) }}" class="inline"
                                          onsubmit="return confirm('Signer ce document ? Il sera verrouillé.');">
                                        @csrf
                                        <button class="text-emerald-700 hover:underline">Signer</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500">Aucune signature.</p>
                    @endforelse

                    @can('sign', $document)
                        @if ($fichierPresent && ! $document->est_archive)
                            <form method="POST" action="{{ route('ged.documents.signatures.request', $document) }}" class="flex flex-wrap gap-2 items-end border-t border-gray-100 pt-3">
                                @csrf
                                <select name="signataire_id" class="form-select flex-1" required>
                                    <option value="">{{ __('Choisir un signataire…') }}</option>
                                    @foreach ($signataires as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-secondary btn-sm">Demander signature</button>
                            </form>
                        @endif
                    @endcan

                    <div class="border-t border-gray-100 pt-3 flex flex-wrap gap-2">
                        @can('archive', $document)
                            @unless ($document->est_archive)
                                <form method="POST" action="{{ route('ged.documents.archive', $document) }}" class="flex gap-2 items-center"
                                      onsubmit="return confirm('Archiver et verrouiller ce document ?');">
                                    @csrf
                                    <input type="text" name="motif" class="form-input" placeholder="Motif d'archivage" maxlength="1000" />
                                    <button class="btn btn-secondary btn-sm">Archiver</button>
                                </form>
                            @endunless
                        @endcan
                        @can('restore', $document)
                            @if ($document->est_archive)
                                <form method="POST" action="{{ route('ged.documents.restore-archive', $document) }}"
                                      onsubmit="return confirm('Restaurer ce document depuis les archives ?');">
                                    @csrf
                                    <button class="btn btn-secondary btn-sm">Restaurer des archives</button>
                                </form>
                            @endif
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        {{-- Partage --}}
        @can('share', $document)
            <div class="card">
                <div class="card-header"><h3 class="font-semibold text-ceeac-primary">Partage du document</h3></div>
                <div class="card-body space-y-3 text-sm">
                    @forelse ($document->partages as $partage)
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-50 pb-2">
                            <span>
                                {{ $partage->partageAvec?->name ?? ($partage->partage_avec_role ? 'Rôle : '.$partage->partage_avec_role : 'Lien sécurisé') }}
                                <span class="badge badge-info ml-1">{{ ucfirst($partage->niveau_acces) }}</span>
                                @if ($partage->date_expiration)<span class="text-gray-500 ml-1">jusqu'au {{ $partage->date_expiration->format('d/m/Y') }}</span>@endif
                            </span>
                            <form method="POST" action="{{ route('ged.partages.destroy', $partage) }}" class="inline"
                                  onsubmit="return confirm('Révoquer ce partage ?');">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Révoquer</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-gray-500">Aucun partage actif.</p>
                    @endforelse

                    <form method="POST" action="{{ route('ged.documents.share', $document) }}" class="grid md:grid-cols-4 gap-2 items-end border-t border-gray-100 pt-3">
                        @csrf
                        <div>
                            <label class="form-label">Utilisateur</label>
                            <select name="partage_avec_user_id" class="form-select">
                                <option value="">—</option>
                                @foreach ($utilisateurs as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Ou rôle</label>
                            <input type="text" name="partage_avec_role" class="form-input" placeholder="auditeur…" />
                        </div>
                        <div>
                            <label class="form-label">Accès</label>
                            <select name="niveau_acces" class="form-select" required>
                                <option value="lecture">Lecture</option>
                                <option value="telechargement">Téléchargement</option>
                                <option value="edition">Édition</option>
                            </select>
                        </div>
                        <div class="flex gap-2 items-end">
                            <input type="date" name="date_expiration" class="form-input" />
                            <button class="btn btn-secondary btn-sm">Partager</button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan

        {{-- Rattachements aux modules métier --}}
        @can('update', $document)
            <div class="card">
                <div class="card-header"><h3 class="font-semibold text-ceeac-primary">Rattachements aux modules métier</h3></div>
                <div class="card-body space-y-3 text-sm">
                    @forelse ($document->attachments as $attachment)
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-50 pb-2">
                            <span>
                                <span class="badge badge-neutral">{{ class_basename($attachment->attachable_type) }}</span>
                                <span class="font-mono">#{{ $attachment->attachable_id }}</span>
                                @if ($attachment->role)<span class="text-gray-500 ml-1">({{ $attachment->role }})</span>@endif
                            </span>
                            <form method="POST" action="{{ route('ged.attachments.destroy', $attachment) }}" class="inline"
                                  onsubmit="return confirm('Détacher ce rattachement ?');">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Détacher</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-gray-500">Aucun rattachement métier.</p>
                    @endforelse

                    <form method="POST" action="{{ route('ged.documents.attachments.store', $document) }}" class="grid md:grid-cols-4 gap-2 items-end border-t border-gray-100 pt-3">
                        @csrf
                        <div>
                            <label class="form-label">Module</label>
                            <select name="attachable_type" class="form-select" required>
                                @foreach ($typesRattachables as $type)
                                    <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Identifiant</label>
                            <input type="number" name="attachable_id" class="form-input" min="1" required />
                        </div>
                        <div>
                            <label class="form-label">Rôle (optionnel)</label>
                            <input type="text" name="role" class="form-input" placeholder="piece_justificative…" />
                        </div>
                        <div>
                            <button class="btn btn-secondary btn-sm w-full">Rattacher</button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan

        {{-- Nouvelle version --}}
        @can('update', $document)
            <div class="card">
                <div class="card-header"><h3 class="font-semibold text-ceeac-primary">Déposer une nouvelle version</h3></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('ged.documents.versions.store', $document) }}"
                          enctype="multipart/form-data" class="grid md:grid-cols-3 gap-3 items-end">
                        @csrf
                        <div class="md:col-span-2">
                            <x-input-label for="fichier" :value="__('Nouveau fichier')" />
                            <input id="fichier" name="fichier" type="file" required
                                   class="block mt-1 w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0
                                          file:bg-ceeac-light file:px-4 file:py-2 file:text-ceeac-primary file:font-semibold cursor-pointer" />
                            <x-input-error class="mt-2" :messages="$errors->get('fichier')" />
                        </div>
                        <div>
                            <x-input-label for="commentaire" :value="__('Commentaire de version')" />
                            <x-text-input id="commentaire" name="commentaire" type="text" class="block mt-1 w-full" maxlength="1000" />
                        </div>
                        <div class="md:col-span-3">
                            <button class="btn btn-primary btn-sm">{{ __('Enregistrer la version') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan

        {{-- Historique des versions --}}
        <div class="card overflow-x-auto">
            <div class="card-header"><h3 class="font-semibold text-ceeac-primary">Historique des versions</h3></div>
            <div class="card-body">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-center">Version</th>
                            <th>Fichier</th>
                            <th>Empreinte SHA-256</th>
                            <th>Commentaire</th>
                            <th>Auteur</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($document->versions as $version)
                            <tr>
                                <td class="text-center tabular-nums font-semibold">v{{ $version->numero_version }}</td>
                                <td class="break-all">{{ $version->nom_original }}</td>
                                <td class="font-mono text-[11px] break-all text-gray-600">{{ $version->hash_sha256 }}</td>
                                <td>{{ $version->commentaire ?? '—' }}</td>
                                <td>{{ $version->createur?->name ?? '—' }}</td>
                                <td class="whitespace-nowrap">{{ $version->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-6 text-gray-500">Aucune version.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Journal documentaire --}}
        <div class="card overflow-x-auto">
            <div class="card-header"><h3 class="font-semibold text-ceeac-primary">Journal du document</h3></div>
            <div class="card-body">
                <table class="data-table">
                    <thead>
                        <tr><th>Date</th><th>Action</th><th>Utilisateur</th><th>Commentaire</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($document->journaux as $entree)
                            <tr>
                                <td class="whitespace-nowrap">{{ $entree->created_at?->format('d/m/Y H:i') }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $entree->action)) }}</td>
                                <td>{{ $entree->utilisateur?->name ?? '—' }}</td>
                                <td>{{ $entree->commentaire ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-6 text-gray-500">Aucune activité.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
