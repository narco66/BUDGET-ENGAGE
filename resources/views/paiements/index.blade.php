<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Paiements')"
            :description="__('Trésorerie, exécution bancaire, rapprochement et écritures comptables.')"
            :breadcrumb="[
                ['label' => __('Chaîne de la dépense'), 'url' => route('execution.dashboard')],
                ['label' => __('Paiements')],
            ]"
        >
            <x-slot:actions>
                @can('gererTypes', App\Models\Paiement::class)
                    <a href="{{ route('paiements.banques.index') }}" class="btn btn-ghost btn-sm">{{ __('Banques') }}</a>
                    <a href="{{ route('paiements.types.index') }}" class="btn btn-ghost btn-sm">{{ __('Types') }}</a>
                @endcan
                @can('create', App\Models\Paiement::class)
                    <a href="{{ route('paiements.create') }}"><x-primary-button type="button">{{ __('Nouveau paiement') }}</x-primary-button></a>
                @endcan
            </x-slot:actions>
        </x-chaine.dossier-header>
    </x-slot>

    <x-ds.page-shell class="space-y-6">
        <x-ds.card>
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <x-input-label for="q" :value="__('Recherche')" />
                    <x-text-input id="q" name="q" class="block mt-1 w-full" :value="request('q')" placeholder="{{ __('N° paiement, réf. bancaire, objet') }}" />
                </div>
                <div>
                    <x-input-label for="statut" :value="__('Statut')" />
                    <select id="statut" name="statut" class="form-select mt-1">
                        <option value="">— {{ __('Tous') }} —</option>
                        @foreach (\App\Enums\StatutPaiement::cases() as $s)
                            <option value="{{ $s->value }}" @selected(request('statut') === $s->value)>{{ $s->libelle() }}</option>
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
            </form>
        </x-ds.card>

        <x-ds.card :padding="false">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('N°') }}</th>
                            <th>{{ __('Ordonnancement') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Banque') }}</th>
                            <th class="text-right">{{ __('Montant') }}</th>
                            <th>{{ __('Statut') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($paiements as $paiement)
                            <tr>
                                <td class="font-mono"><a href="{{ route('paiements.show', $paiement) }}" class="text-ceeac-bright hover:underline">{{ $paiement->numero ?? '#'.$paiement->id }}</a></td>
                                <td class="font-mono text-sm">{{ $paiement->ordonnancement?->numero ?? '—' }}</td>
                                <td>{{ $paiement->type?->libelle ?? '—' }}</td>
                                <td class="text-xs">{{ $paiement->banque?->code ?? '—' }}</td>
                                <td class="text-right"><x-ds.money :value="$paiement->montant_paye ?: $paiement->reste_a_payer" :decimals="2" :show-currency="false" /></td>
                                <td>@include('paiements._statut-badge')</td>
                                <td>{{ optional($paiement->date_paiement)->format('d/m/Y') }}</td>
                                <td class="text-right"><a href="{{ route('paiements.show', $paiement) }}" class="text-ceeac-bright hover:underline">{{ __('Voir') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="p-0"><x-ds.empty-state :title="__('Aucun paiement.')" class="border-0 shadow-none" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($paiements->hasPages())
                <div class="card-footer">{{ $paiements->links() }}</div>
            @endif
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
