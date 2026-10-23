<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <span>Accueil</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">Gestion Documentaire</span>
                </nav>
                <h2 class="page-title">Tableau de bord GED</h2>
            </div>
        </div>
    </x-slot>

    @php
        $volume = $kpis['volume_octets'];
        $volumeLisible = $volume > 0
            ? number_format($volume / (1024 * 1024), 2, ',', ' ').' Mo'
            : '0 Mo';
    @endphp

    <div class="space-y-6">

        {{-- Bandeau d'information Phase 1 --}}
        <div class="alert-info">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Module de Gestion Électronique des Documents — socle initialisé. Le dépôt, le
                  classement, les archives, les signatures et la recherche avancée seront activés
                  progressivement.</span>
        </div>

        {{-- KPIs principaux --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            @php
                $cards = [
                    ['label' => 'Documents', 'value' => $kpis['total'], 'color' => 'text-ceeac-primary'],
                    ['label' => 'Déposés', 'value' => $kpis['deposes'], 'color' => 'text-sky-600'],
                    ['label' => 'Validés', 'value' => $kpis['valides'], 'color' => 'text-emerald-600'],
                    ['label' => 'Signés', 'value' => $kpis['signes'], 'color' => 'text-emerald-700'],
                    ['label' => 'Archivés', 'value' => $kpis['archives'], 'color' => 'text-slate-600'],
                    ['label' => 'Confidentiels', 'value' => $kpis['confidentiels'], 'color' => 'text-red-600'],
                    ['label' => 'Expirés', 'value' => $kpis['expires'], 'color' => 'text-amber-600'],
                    ['label' => 'Attente validation', 'value' => $kpis['attente_validation'], 'color' => 'text-amber-700'],
                    ['label' => 'Attente signature', 'value' => $kpis['attente_signature'], 'color' => 'text-amber-700'],
                    ['label' => 'Volume stocké', 'value' => $volumeLisible, 'color' => 'text-ceeac-bright'],
                ];
            @endphp

            @foreach ($cards as $card)
                <div class="card">
                    <div class="card-body">
                        <p class="text-[11px] font-bold uppercase tracking-widest text-ceeac-slate">{{ $card['label'] }}</p>
                        <p class="mt-2 text-2xl font-bold tabular-nums {{ $card['color'] }}">{{ $card['value'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Accès rapides --}}
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('ged.search.index') }}" class="btn btn-secondary btn-sm">Recherche avancée</a>
            <a href="{{ route('ged.archives.index') }}" class="btn btn-ghost btn-sm">Archives</a>
            <a href="{{ route('ged.conservation.index') }}" class="btn btn-ghost btn-sm">Conservation</a>
            <a href="{{ route('ged.confidentiels') }}" class="btn btn-ghost btn-sm">Confidentiels</a>
            <a href="{{ route('ged.expires') }}" class="btn btn-ghost btn-sm">Expirés</a>
            <a href="{{ route('ged.partages.index') }}" class="btn btn-ghost btn-sm">Partagés</a>
        </div>

        <div class="grid lg:grid-cols-2 gap-4">
            {{-- Répartition par statut --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-ceeac-primary">Documents par statut</h3>
                </div>
                <div class="card-body">
                    @forelse ($parStatut as $statut => $total)
                        <div class="flex items-center justify-between py-1.5 text-sm border-b border-gray-50 last:border-0">
                            <span class="text-gray-700">{{ ucfirst(str_replace('_', ' ', $statut)) }}</span>
                            <span class="font-semibold tabular-nums">{{ $total }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 py-4 text-center">Aucun document enregistré.</p>
                    @endforelse
                </div>
            </div>

            {{-- Répartition par module --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="font-semibold text-ceeac-primary">Documents par module métier</h3>
                </div>
                <div class="card-body">
                    @forelse ($parModule as $module => $total)
                        <div class="flex items-center justify-between py-1.5 text-sm border-b border-gray-50 last:border-0">
                            <span class="text-gray-700">{{ ucfirst(str_replace('_', ' ', $module)) }}</span>
                            <span class="font-semibold tabular-nums">{{ $total }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 py-4 text-center">Aucun document rattaché à un module.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-4">
            {{-- Répartition par type --}}
            <div class="card">
                <div class="card-header"><h3 class="font-semibold text-ceeac-primary">Documents par type</h3></div>
                <div class="card-body">
                    @forelse ($parType as $type => $total)
                        <div class="flex items-center justify-between py-1.5 text-sm border-b border-gray-50 last:border-0">
                            <span class="text-gray-700">{{ $type }}</span>
                            <span class="font-semibold tabular-nums">{{ $total }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 py-4 text-center">Aucun document typé.</p>
                    @endforelse
                </div>
            </div>

            {{-- Répartition par confidentialité --}}
            <div class="card">
                <div class="card-header"><h3 class="font-semibold text-ceeac-primary">Documents par confidentialité</h3></div>
                <div class="card-body">
                    @forelse ($parConfidentialite as $niveau => $total)
                        <div class="flex items-center justify-between py-1.5 text-sm border-b border-gray-50 last:border-0">
                            <span class="text-gray-700">{{ ucfirst(str_replace('_', ' ', $niveau)) }}</span>
                            <span class="font-semibold tabular-nums">{{ $total }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 py-4 text-center">Aucun document.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Derniers documents déposés --}}
        <div class="card overflow-x-auto">
            <div class="card-header"><h3 class="font-semibold text-ceeac-primary">Derniers documents déposés</h3></div>
            <div class="card-body">
                @if ($derniersDocuments->isEmpty())
                    <p class="text-sm text-gray-500 py-4 text-center">Aucun document.</p>
                @else
                    <table class="data-table">
                        <thead><tr><th>Référence</th><th>Titre</th><th>Type</th><th>Statut</th><th>Déposé par</th></tr></thead>
                        <tbody>
                            @foreach ($derniersDocuments as $doc)
                                <tr>
                                    <td class="font-mono text-xs">{{ $doc->reference }}</td>
                                    <td class="font-medium"><a href="{{ route('ged.documents.show', $doc) }}" class="text-ceeac-bright hover:underline">{{ $doc->titre }}</a></td>
                                    <td>{{ $doc->typeDocument?->libelle ?? '—' }}</td>
                                    <td><span class="badge {{ $doc->statut->badge() }}">{{ $doc->statut->libelle() }}</span></td>
                                    <td>{{ $doc->createur?->name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        {{-- Dernières activités documentaires --}}
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-ceeac-primary">Dernières activités documentaires</h3>
            </div>
            <div class="card-body overflow-x-auto">
                @if ($dernieresActivites->isEmpty())
                    <p class="text-sm text-gray-500 py-4 text-center">Aucune activité enregistrée pour le moment.</p>
                @else
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Action</th>
                                <th>Document</th>
                                <th>Utilisateur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dernieresActivites as $activite)
                                <tr>
                                    <td class="whitespace-nowrap">{{ $activite->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $activite->action)) }}</td>
                                    <td>{{ $activite->document?->titre ?? '—' }}</td>
                                    <td>{{ $activite->utilisateur?->name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
