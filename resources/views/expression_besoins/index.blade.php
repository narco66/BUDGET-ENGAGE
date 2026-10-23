@php
    use App\Enums\StatutBesoin;
    $chips = [
        '' => ['label' => __('Tous'), 'count' => $compteurs['_total']],
        StatutBesoin::Brouillon->value => ['label' => __('Brouillon'), 'count' => $compteurs[StatutBesoin::Brouillon->value]],
        StatutBesoin::Soumis->value => ['label' => __('Soumis'), 'count' => $compteurs[StatutBesoin::Soumis->value]],
        StatutBesoin::EnValidation->value => ['label' => __('En validation'), 'count' => $compteurs[StatutBesoin::EnValidation->value]],
        StatutBesoin::Retourne->value => ['label' => __('Retour correction'), 'count' => $compteurs[StatutBesoin::Retourne->value]],
        StatutBesoin::Valide->value => ['label' => __('Validé'), 'count' => $compteurs[StatutBesoin::Valide->value]],
        StatutBesoin::Rejete->value => ['label' => __('Rejeté'), 'count' => $compteurs[StatutBesoin::Rejete->value]],
        StatutBesoin::Converti->value => ['label' => __('Converti'), 'count' => $compteurs[StatutBesoin::Converti->value]],
    ];
    $statutActif = (string) request('statut');

    // Onglets de nature (réutilise le filtre type_besoin).
    $typeActif = (string) request('type_besoin');
    $typeOnglets = ['' => __('Toutes natures')];
    foreach (\App\Enums\TypeBesoin::cases() as $t) {
        $typeOnglets[$t->value] = $t->libelle();
    }
@endphp
<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Expression de besoins')"
            :description="__('Recensement, validation et conversion des besoins en engagements.')"
            :breadcrumb="[
                ['label' => __('Expression de besoins'), 'url' => route('expression-besoins.dashboard')],
                ['label' => __('Besoins')],
            ]"
        >
            <x-slot:actions>
                <a href="{{ route('expression-besoins.besoins.archives') }}" class="btn btn-ghost btn-sm">{{ __('Archives') }}</a>
                @can('export', App\Models\ExpressionBesoin::class)
                    <a href="{{ route('expression-besoins.besoins.export.registre.excel', request()->query()) }}" class="btn btn-ghost btn-sm">{{ __('Export Excel') }}</a>
                    <div class="relative inline-block" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" @click="open = ! open" class="btn btn-ghost btn-sm">{{ __('États PDF') }} ▾</button>
                        <div x-show="open" x-cloak style="display:none;"
                             class="absolute right-0 z-50 mt-2 w-64 rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5">
                            <a href="{{ route('expression-besoins.besoins.export.registre.pdf', request()->query()) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Registre des besoins') }}</a>
                            <a href="{{ route('expression-besoins.consolidation.pdf', request()->query()) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('État consolidé') }}</a>
                            <a href="{{ route('expression-besoins.besoins.export.etat-structure.pdf', request()->query()) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Par structure') }}</a>
                            <a href="{{ route('expression-besoins.besoins.export.etat-categorie.pdf', request()->query()) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Par catégorie') }}</a>
                            <a href="{{ route('expression-besoins.besoins.export.etat-pap.pdf', request()->query()) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Besoins PAP') }}</a>
                            <a href="{{ route('expression-besoins.besoins.export.etat-hors-pap.pdf', request()->query()) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Besoins Hors PAP') }}</a>
                            <a href="{{ route('expression-besoins.besoins.export.etat-pieces.pdf', request()->query()) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('État des pièces') }}</a>
                            <div class="my-1 border-t border-gray-100"></div>
                            <a href="{{ route('expression-besoins.besoins.export.bordereau.pdf', request()->query()) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Bordereau de transmission') }}</a>
                        </div>
                    </div>
                @endcan
                @can('create', App\Models\ExpressionBesoin::class)
                    <a href="{{ route('expression-besoins.besoins.create') }}"><x-primary-button type="button">{{ __('Nouveau besoin') }}</x-primary-button></a>
                @endcan
            </x-slot:actions>
        </x-chaine.dossier-header>
    </x-slot>

    <x-ds.page-shell class="space-y-5">
        {{-- Barre de statuts (chips + compteurs) --}}
        <div class="flex flex-wrap items-center gap-2">
            @foreach ($chips as $value => $chip)
                @php($actif = $statutActif === (string) $value)
                <a href="{{ request()->fullUrlWithQuery(['statut' => $value === '' ? null : $value, 'page' => null]) }}"
                   class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 text-sm font-medium transition {{ $actif ? 'border-ceeac-primary bg-ceeac-primary text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-ceeac-bright hover:text-ceeac-primary' }}">
                    {{ $chip['label'] }}
                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $actif ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' }}">{{ $chip['count'] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Sélecteur de nature : PAP / Hors-PAP (réutilise le filtre type_besoin) --}}
        <div class="inline-flex items-center rounded-lg border border-gray-200 bg-white p-1 text-sm">
            @foreach ($typeOnglets as $value => $label)
                @php($actifType = $typeActif === (string) $value)
                <a href="{{ request()->fullUrlWithQuery(['type_besoin' => $value === '' ? null : $value, 'page' => null]) }}"
                   class="rounded-md px-3.5 py-1.5 font-medium transition {{ $actifType ? 'bg-ceeac-primary text-white shadow-sm' : 'text-gray-600 hover:text-ceeac-primary' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Filtres --}}
        <x-ds.card>
            <form method="GET" class="flex flex-wrap items-end gap-3">
                @if (request('statut'))<input type="hidden" name="statut" value="{{ request('statut') }}">@endif
                <div class="flex-1 min-w-[220px]">
                    <x-input-label for="q" :value="__('Recherche')" />
                    <x-text-input id="q" name="q" class="block mt-1 w-full" :value="request('q')" placeholder="{{ __('Référence, objet…') }}" />
                </div>
                <div>
                    <x-input-label for="type_besoin" :value="__('Type')" />
                    <select id="type_besoin" name="type_besoin" class="form-select mt-1">
                        <option value="">— {{ __('Tous') }} —</option>
                        @foreach (\App\Enums\TypeBesoin::cases() as $t)
                            <option value="{{ $t->value }}" @selected(request('type_besoin') === $t->value)>{{ $t->libelle() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="exercice_budgetaire_id" :value="__('Exercice')" />
                    <select id="exercice_budgetaire_id" name="exercice_budgetaire_id" class="form-select mt-1">
                        <option value="">— {{ __('Tous') }} —</option>
                        @foreach ($exercices as $ex)
                            <option value="{{ $ex->id }}" @selected((string) request('exercice_budgetaire_id') === (string) $ex->id)>{{ $ex->annee }}</option>
                        @endforeach
                    </select>
                </div>
                <x-primary-button type="submit">{{ __('Filtrer') }}</x-primary-button>
                @if (request()->hasAny(['q', 'type_besoin', 'exercice_budgetaire_id']))
                    <a href="{{ route('expression-besoins.besoins.index', request('statut') ? ['statut' => request('statut')] : []) }}" class="btn btn-ghost btn-sm">{{ __('Réinitialiser') }}</a>
                @endif
            </form>
        </x-ds.card>

        {{-- Tableau --}}
        <x-ds.card :padding="false">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Référence') }}</th>
                            <th>{{ __('Intitulé du besoin') }}</th>
                            <th>{{ __('Département') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th class="text-right">{{ __('Montant estimé') }}</th>
                            <th>{{ __('Statut') }}</th>
                            <th>{{ __('Date soumission') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($besoins as $besoin)
                            <tr>
                                <td class="font-mono"><a href="{{ route('expression-besoins.besoins.show', $besoin) }}" class="text-ceeac-bright hover:underline">{{ $besoin->reference }}</a></td>
                                <td>{{ \Illuminate\Support\Str::limit($besoin->objet, 48) }}</td>
                                <td class="text-sm">{{ $besoin->departement?->libelle ?? '—' }}</td>
                                <td><span class="badge {{ $besoin->type_besoin?->badge() }}">{{ $besoin->type_besoin?->libelle() }}</span></td>
                                <td class="text-right"><x-ds.money :value="$besoin->montant_total" :decimals="0" :show-currency="false" /></td>
                                <td><x-chaine.status-badge module="besoin" :statut="$besoin->statut" /></td>
                                <td class="text-sm whitespace-nowrap">{{ optional($besoin->submitted_at ?? $besoin->created_at)->format('d/m/Y') }}</td>
                                <td class="text-right"><a href="{{ route('expression-besoins.besoins.show', $besoin) }}" class="text-ceeac-bright hover:underline">{{ __('Voir') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="p-0"><x-ds.empty-state :title="__('Aucune expression de besoin.')" :description="__('Ajustez les filtres ou créez un nouveau besoin.')" class="border-0 shadow-none" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($besoins->hasPages())
                <div class="card-footer">{{ $besoins->withQueryString()->links() }}</div>
            @endif
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
