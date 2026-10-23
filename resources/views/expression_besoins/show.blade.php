@php
    $b = $expressionBesoin;
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
@endphp
<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="$b->reference"
            :description="\Illuminate\Support\Str::limit($b->objet, 120)"
            :statut="$b->statut"
            statut-module="besoin"
            :breadcrumb="[
                ['label' => __('Expression des besoins'), 'url' => route('expression-besoins.dashboard')],
                ['label' => __('Liste'), 'url' => route('expression-besoins.besoins.index')],
                ['label' => $b->reference],
            ]"
        >
            <x-slot:actions>
                @can('export', App\Models\ExpressionBesoin::class)
                    <a href="{{ route('expression-besoins.besoins.export.pdf', $b) }}" class="btn btn-secondary btn-sm">{{ __('Fiche PDF') }}</a>
                @endcan
                @can('update', $b)
                    <a href="{{ route('expression-besoins.besoins.edit', $b) }}" class="btn btn-secondary btn-sm">{{ __('Modifier') }}</a>
                @endcan
                @can('convert', $b)
                    <form method="POST" action="{{ route('expression-besoins.besoins.convert', $b) }}" onsubmit="return confirm(@json(__('Convertir ce besoin en engagement ? Cette action est définitive.')))">
                        @csrf
                        <button class="btn btn-primary btn-sm">{{ __('Convertir en engagement') }}</button>
                    </form>
                @endcan
            </x-slot:actions>
        </x-chaine.dossier-header>
    </x-slot>

    <x-ds.page-shell class="space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="badge {{ $b->type_besoin->badge() }}">{{ $b->type_besoin->libelle() }}</span>
            <span class="badge badge-neutral">{{ __('Priorité') }} : {{ $b->priorite?->libelle() ?? '—' }}</span>
            <span class="badge badge-neutral">{{ __('Exercice') }} {{ $b->exerciceBudgetaire?->annee }}</span>
            @if ($b->engagement)
                <a href="{{ route('engagements.show', $b->engagement) }}" class="badge badge-success">{{ __('Engagement') }} {{ $b->engagement->numero }}</a>
            @endif
        </div>

        <x-chaine.financial-summary :title="__('Synthèse financière')">
            <x-chaine.financial-metric :label="__('Montant total')" tone="primary">
                <x-ds.money :value="$b->montant_total" :decimals="2" />
            </x-chaine.financial-metric>
            <x-chaine.financial-metric :label="__('Type')" :value="$b->type_besoin->libelle()" />
            <x-chaine.financial-metric :label="__('Urgence')" :value="$b->niveau_urgence?->libelle() ?? '—'" />
            <x-chaine.financial-metric :label="__('Lignes')" :value="(string) $b->lignes->count()" />
        </x-chaine.financial-summary>

        <x-ds.card>
            <x-chaine.tabs :tabs="[
                'infos' => __('Informations'),
                'justification' => __('Justification'),
                'rattachement' => __('Rattachement'),
                'lignes' => __('Lignes'),
                'pieces' => __('Pièces'),
                'budget' => __('Budget'),
                'validation' => __('Validation'),
                'historique' => __('Historique'),
            ]" active="infos">
                <x-chaine.tab-panel name="infos" class="grid md:grid-cols-3 gap-3 text-sm">
                    <div><span class="text-gray-500">{{ __('Structure') }}</span><p class="font-medium">{{ $b->structure_demandeuse ?? '—' }}</p></div>
                    <div><span class="text-gray-500">{{ __('Département') }}</span><p class="font-medium">{{ $b->departement?->libelle ?? '—' }}</p></div>
                    <div><span class="text-gray-500">{{ __('Direction') }}</span><p class="font-medium">{{ $b->direction?->libelle ?? '—' }}</p></div>
                    <div><span class="text-gray-500">{{ __('Service') }}</span><p class="font-medium">{{ $b->serviceStructure?->libelle ?? '—' }}</p></div>
                    <div><span class="text-gray-500">{{ __('Demandeur') }}</span><p class="font-medium">{{ $b->demandeur?->name ?? '—' }}</p></div>
                    <div><span class="text-gray-500">{{ __('Date') }}</span><p class="font-medium">{{ $b->date_besoin?->format('d/m/Y') ?? '—' }}</p></div>
                    <div><span class="text-gray-500">{{ __('Urgence') }}</span><p class="font-medium">{{ $b->niveau_urgence?->libelle() ?? '—' }}</p></div>
                    <div class="md:col-span-3"><span class="text-gray-500">{{ __('Objet') }}</span><p class="font-medium">{{ $b->objet }}</p></div>
                    <div class="md:col-span-3"><span class="text-gray-500">{{ __('Description') }}</span><p class="whitespace-pre-wrap">{{ $b->description ?? '—' }}</p></div>
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="justification" class="space-y-3 text-sm">
                    <div><span class="text-gray-500">{{ __('Justification') }}</span><p class="whitespace-pre-wrap">{{ $b->justification ?? '—' }}</p></div>
                    <div><span class="text-gray-500">{{ __('Objectif recherché') }}</span><p class="whitespace-pre-wrap">{{ $b->objectif_recherche ?? '—' }}</p></div>
                    <div><span class="text-gray-500">{{ __('Conséquences en cas de non-réalisation') }}</span><p class="whitespace-pre-wrap">{{ $b->consequences_non_realisation ?? '—' }}</p></div>
                    <div><span class="text-gray-500">{{ __('Observations') }}</span><p class="whitespace-pre-wrap">{{ $b->observations ?? '—' }}</p></div>
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="rattachement" class="grid md:grid-cols-3 gap-3 text-sm">
                    @if ($b->estPap())
                        <div><span class="text-gray-500">{{ __('Pilier') }}</span><p class="font-medium">{{ $b->pilier?->libelle ?? '—' }}</p></div>
                        <div><span class="text-gray-500">{{ __('Axe') }}</span><p class="font-medium">{{ $b->axeStrategique?->libelle ?? '—' }}</p></div>
                        <div><span class="text-gray-500">{{ __('Produit') }}</span><p class="font-medium">{{ $b->produit?->libelle ?? '—' }}</p></div>
                        <div><span class="text-gray-500">{{ __('Sous-produit') }}</span><p class="font-medium">{{ $b->sousProduit?->libelle ?? '—' }}</p></div>
                        <div><span class="text-gray-500">{{ __('Activité') }}</span><p class="font-medium">{{ $b->activite?->libelle ?? '—' }}</p></div>
                        <div><span class="text-gray-500">{{ __('Tâche') }}</span><p class="font-medium">{{ $b->tache?->libelle ?? '—' }}</p></div>
                        <div><span class="text-gray-500">{{ __('Indicateur') }}</span><p class="font-medium">{{ $b->indicateur?->libelle ?? '—' }}</p></div>
                        <div><span class="text-gray-500">{{ __('Responsable activité') }}</span><p class="font-medium">{{ $b->responsable_activite ?? '—' }}</p></div>
                    @else
                        <div><span class="text-gray-500">{{ __('Catégorie') }}</span><p class="font-medium">{{ $b->categorie_hors_pap?->libelle() ?? '—' }}</p></div>
                        <div><span class="text-gray-500">{{ __('Sous-catégorie') }}</span><p class="font-medium">{{ $b->sous_categorie ?? '—' }}</p></div>
                        <div><span class="text-gray-500">{{ __('Nature') }}</span><p class="font-medium">{{ $b->nature_besoin ?? '—' }}</p></div>
                        <div><span class="text-gray-500">{{ __('Centre de coût') }}</span><p class="font-medium">{{ $b->centre_cout ?? '—' }}</p></div>
                        <div><span class="text-gray-500">{{ __('Centre de responsabilité') }}</span><p class="font-medium">{{ $b->centre_responsabilite ?? '—' }}</p></div>
                    @endif
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="lignes">
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead><tr><th>{{ __('Désignation') }}</th><th class="text-right">{{ __('Qté') }}</th><th>{{ __('Unité') }}</th><th class="text-right">{{ __('P.U.') }}</th><th class="text-right">{{ __('Montant') }}</th><th>{{ __('Ligne budg.') }}</th><th>{{ __('Source') }}</th></tr></thead>
                            <tbody>
                                @forelse ($b->lignes as $ligne)
                                    <tr>
                                        <td class="font-medium">{{ $ligne->designation }}</td>
                                        <td class="text-right tabular-nums">{{ rtrim(rtrim(number_format((float) $ligne->quantite, 3, ',', ' '), '0'), ',') }}</td>
                                        <td>{{ $ligne->unite ?? '—' }}</td>
                                        <td class="text-right tabular-nums"><x-ds.money :value="$ligne->prix_unitaire" :decimals="2" :show-currency="false" /></td>
                                        <td class="text-right tabular-nums font-semibold"><x-ds.money :value="$ligne->montant" :decimals="2" :show-currency="false" /></td>
                                        <td class="font-mono text-xs">{{ $ligne->ligneBudgetaire?->code ?? '—' }}</td>
                                        <td>{{ $ligne->sourceFinancement?->libelle ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="p-0"><x-ds.empty-state :title="__('Aucune ligne.')" class="border-0 shadow-none" /></td></tr>
                                @endforelse
                            </tbody>
                            @if ($b->lignes->isNotEmpty())
                                <tfoot>
                                    <tr class="font-semibold"><td colspan="4" class="text-right">{{ __('Total') }}</td><td class="text-right tabular-nums text-ceeac-primary"><x-ds.money :value="$b->montant_total" :decimals="2" :show-currency="false" /></td><td colspan="2"></td></tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="pieces" class="space-y-3">
                    @can('update', $expressionBesoin)
                        <form method="POST" action="{{ route('expression-besoins.besoins.pieces.store', $b) }}" enctype="multipart/form-data" class="grid md:grid-cols-3 gap-3 items-end border-b border-gray-100 pb-3">
                            @csrf
                            <div class="md:col-span-2">
                                <x-input-label for="fichier" :value="__('Déposer une pièce (GED)')" />
                                <input id="fichier" name="fichier" type="file" required class="block mt-1 w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-ceeac-light file:px-4 file:py-2 file:text-ceeac-primary file:font-semibold cursor-pointer" />
                            </div>
                            <div><button class="btn btn-secondary btn-sm">{{ __('Déposer') }}</button></div>
                        </form>
                    @endcan
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead><tr><th>{{ __('Référence GED') }}</th><th>{{ __('Titre') }}</th><th>{{ __('Statut') }}</th><th></th></tr></thead>
                            <tbody>
                                @forelse ($attachments as $att)
                                    @php($doc = $att->document)
                                    <tr>
                                        <td class="font-mono text-xs">{{ $doc?->reference ?? '—' }}</td>
                                        <td>{{ $doc?->titre ?? '—' }}</td>
                                        <td>@if ($doc)<span class="badge {{ $doc->statut->badge() }}">{{ $doc->statut->libelle() }}</span>@endif</td>
                                        <td class="text-right space-x-2">
                                            @if ($doc)<a href="{{ route('ged.documents.show', $doc) }}" class="text-ceeac-bright hover:underline">{{ __('Ouvrir') }}</a>@endif
                                            @can('update', $expressionBesoin)
                                                <form class="inline" method="POST" action="{{ route('expression-besoins.besoins.pieces.destroy', [$b, $att]) }}" onsubmit="return confirm(@json(__('Détacher cette pièce ?')))">
                                                    @csrf @method('DELETE')
                                                    <button class="text-red-600 hover:underline">{{ __('Détacher') }}</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="p-0"><x-ds.empty-state :title="__('Aucune pièce.')" class="border-0 shadow-none" /></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="budget">
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead><tr><th>{{ __('Ligne budgétaire') }}</th><th class="text-right">{{ __('Crédit disponible') }}</th><th class="text-right">{{ __('Montant demandé') }}</th><th class="text-right">{{ __('Impact / Solde') }}</th></tr></thead>
                            <tbody>
                                @forelse ($budget as $row)
                                    @php($solde = bcsub((string) $row['disponible'], (string) $row['demande'], 2))
                                    <tr>
                                        <td class="font-mono text-xs">{{ $row['ligne']?->code ?? '—' }} — {{ $row['ligne']?->libelle }}</td>
                                        <td class="text-right tabular-nums"><x-ds.money :value="$row['disponible']" :decimals="2" :show-currency="false" /></td>
                                        <td class="text-right tabular-nums"><x-ds.money :value="$row['demande']" :decimals="2" :show-currency="false" /></td>
                                        <td class="text-right tabular-nums {{ bccomp($solde, '0', 2) < 0 ? 'text-red-600 font-semibold' : 'text-emerald-700' }}"><x-ds.money :value="$solde" :decimals="2" :show-currency="false" /></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="p-0"><x-ds.empty-state :title="__('Aucune imputation budgétaire.')" class="border-0 shadow-none" /></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="validation" class="space-y-4">
                    @if ($b->version_revision > 1)
                        <x-ds.alert type="warning">
                            {{ __('Révision n° :n — dernière le :date', ['n' => $b->version_revision, 'date' => $b->derniere_revision_at?->format('d/m/Y H:i')]) }}
                            @if($b->derniere_revision_motif) ({{ __('motif') }} : {{ $b->derniere_revision_motif }}) @endif
                        </x-ds.alert>
                    @endif

                    @can('revise', $b)
                        <form method="POST" action="{{ route('expression-besoins.besoins.revise', $b) }}" class="border border-amber-200 bg-amber-50/50 rounded-xl p-3 space-y-2" onsubmit="return confirm(@json(__('Remettre ce besoin validé en révision ? Le circuit de validation sera réinitialisé.')))">
                            @csrf
                            <label class="form-label">{{ __('Mise en révision (besoin validé)') }}</label>
                            <div class="flex flex-wrap gap-2 items-end">
                                <input type="text" name="motif_revision" class="form-input flex-1" placeholder="{{ __('Motif de la révision') }}" maxlength="1000" required />
                                <button class="btn btn-secondary btn-sm">{{ __('Mettre en révision') }}</button>
                            </div>
                        </form>
                    @endcan

                    @can('submit', $b)
                        @if (in_array($b->statut->value, ['brouillon', 'retourne']))
                            <form method="POST" action="{{ route('expression-besoins.besoins.workflow.submit', $b) }}" onsubmit="return confirm(@json(__('Soumettre ce besoin au circuit de validation ?')))">
                                @csrf
                                <button class="btn btn-primary btn-sm">{{ __('Soumettre au circuit de validation') }}</button>
                            </form>
                        @endif
                    @endcan

                    @can('cancel', $b)
                        <form method="POST" action="{{ route('expression-besoins.besoins.workflow.cancel', $b) }}" class="border border-red-100 bg-red-50/40 rounded-xl p-3 space-y-2" onsubmit="return confirm(@json(__('Annuler ce besoin ?')))">
                            @csrf
                            <label class="form-label text-red-800">{{ __('Annuler le besoin') }}</label>
                            <div class="flex flex-wrap gap-2 items-end">
                                <input type="text" name="motif" class="form-input flex-1" placeholder="{{ __('Motif d\'annulation (optionnel)') }}" maxlength="1000" />
                                <button class="btn btn-secondary btn-sm text-red-700">{{ __('Annuler le besoin') }}</button>
                            </div>
                        </form>
                    @endcan

                    @if ($etapeCourante && $peutDecider)
                        <form method="POST" action="{{ route('expression-besoins.besoins.workflow.decide', $b) }}" class="border border-gray-100 rounded-xl p-3 bg-gray-50/50 space-y-2">
                            @csrf
                            <label class="form-label">{{ __('Décision pour « :etape »', ['etape' => $etapeCourante->libelle_etape]) }}</label>
                            <div class="flex flex-wrap gap-2 items-end">
                                <select name="decision" class="form-select flex-1" required>
                                    <option value="valide">{{ __('Valider') }}</option>
                                    <option value="retourne">{{ __('Retourner à l\'auteur') }}</option>
                                    <option value="rejete">{{ __('Rejeter') }}</option>
                                </select>
                                <input type="text" name="commentaire" class="form-input flex-1" placeholder="{{ __('Commentaire / motif') }}" maxlength="1000" />
                                <button class="btn btn-primary btn-sm">{{ __('Enregistrer') }}</button>
                            </div>
                        </form>
                    @endif

                    @if ($b->validations->isEmpty())
                        <p class="text-sm text-gray-500">{{ __('Aucun circuit de validation démarré.') }}</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="data-table">
                                <thead><tr><th>{{ __('Étape') }}</th><th>{{ __('Statut') }}</th><th>{{ __('Validateur') }}</th><th>{{ __('Date') }}</th><th>{{ __('Commentaire') }}</th></tr></thead>
                                <tbody>
                                    @foreach ($b->validations as $v)
                                        <tr>
                                            <td>{{ $v->ordre }}. {{ $v->libelle_etape }}</td>
                                            <td><span class="badge {{ $v->statut->value==='valide' ? 'badge-success' : ($v->statut->value==='en_attente' ? 'badge-warning' : 'badge-danger') }}">{{ $v->statut->libelle() }}</span></td>
                                            <td>{{ $v->validateur?->name ?? '—' }}</td>
                                            <td class="whitespace-nowrap">{{ $v->date_action?->format('d/m/Y H:i') ?? '—' }}</td>
                                            <td>{{ $v->commentaire ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </x-chaine.tab-panel>

                <x-chaine.tab-panel name="historique" class="space-y-4">
                    <div class="grid md:grid-cols-2 gap-3 text-sm">
                        <div><span class="text-gray-500">{{ __('Créé par') }}</span><p class="font-medium">{{ $b->createur?->name ?? '—' }} — {{ $b->created_at?->format('d/m/Y H:i') }}</p></div>
                        <div><span class="text-gray-500">{{ __('Dernière modification') }}</span><p class="font-medium">{{ $b->modificateur?->name ?? '—' }} — {{ $b->updated_at?->format('d/m/Y H:i') }}</p></div>
                        <div><span class="text-gray-500">{{ __('Soumis le') }}</span><p class="font-medium">{{ $b->submitted_at?->format('d/m/Y H:i') ?? '—' }}</p></div>
                        <div><span class="text-gray-500">{{ __('Validé le') }}</span><p class="font-medium">{{ $b->validated_at?->format('d/m/Y H:i') ?? '—' }}</p></div>
                        @if ($b->converted_at)
                            <div><span class="text-gray-500">{{ __('Converti le') }}</span><p class="font-medium">{{ $b->converted_at?->format('d/m/Y H:i') }} {{ __('par') }} {{ $b->convertisseur?->name }}</p></div>
                        @endif
                        @if ($b->motif_rejet)
                            <div class="md:col-span-2"><span class="text-gray-500">{{ __('Motif de rejet') }}</span><p class="text-red-700">{{ $b->motif_rejet }}</p></div>
                        @endif
                    </div>

                    <div class="border-t border-gray-100 pt-3">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-semibold text-ceeac-primary">{{ __('Journal des opérations') }}</h3>
                            <a href="{{ route('expression-besoins.besoins.export.historique.pdf', $b) }}" class="btn btn-ghost btn-sm">{{ __('Exporter l\'historique (PDF)') }}</a>
                        </div>
                        <x-chaine.timeline :events="collect($timeline)->map(fn ($entry) => [
                            'label' => ucfirst(str_replace('_', ' ', $entry->properties['action'] ?? '')),
                            'user' => $entry->causer?->name ?? __('Système'),
                            'date' => $entry->created_at?->format('d/m/Y H:i'),
                            'detail' => $entry->description,
                        ])->all()" />
                    </div>
                </x-chaine.tab-panel>
            </x-chaine.tabs>
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
