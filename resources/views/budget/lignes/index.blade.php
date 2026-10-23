<x-app-layout>
    <x-slot name="header">
        <x-ds.page-header :title="__('Lignes budgétaires')" :description="__('Lignes opérationnelles de crédit par exercice.')" :breadcrumb="[
            ['label' => __('Budget'), 'url' => route('budget.exercices.index')],
            ['label' => __('Lignes budgétaires')],
        ]">
            <x-slot:actions>
                <a href="{{ route('budget.lignes.create') }}"><x-primary-button type="button">{{ __('Ajouter') }}</x-primary-button></a>
            </x-slot:actions>
        </x-ds.page-header>
    </x-slot>

    <x-ds.page-shell>
        <x-ds.card :padding="false">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Exercice') }}</th>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Libellé') }}</th>
                            <th>{{ __('Nature') }}</th>
                            <th>{{ __('Département') }}</th>
                            <th class="text-right">{{ __('Voté') }}</th>
                            <th>{{ __('Statut') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lignes as $ligne)
                            <tr>
                                <td class="whitespace-nowrap">{{ $ligne->exerciceBudgetaire?->annee ?? '—' }}</td>
                                <td class="font-mono"><a href="{{ route('budget.lignes.show', $ligne) }}" class="text-ceeac-bright hover:underline">{{ $ligne->code }}</a></td>
                                <td class="max-w-xs truncate" title="{{ $ligne->libelle }}">{{ $ligne->libelle }}</td>
                                <td>{{ $ligne->natureDepense?->code ?? '—' }}</td>
                                <td>{{ $ligne->departement?->libelle ?? '—' }}</td>
                                <td class="text-right"><x-ds.money :value="$ligne->montant_vote" :decimals="2" :show-currency="false" /></td>
                                <td><span class="badge badge-neutral">{{ $ligne->statut }}</span></td>
                                <td class="text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('budget.lignes.edit', $ligne) }}" class="text-ceeac-bright hover:underline">{{ __('Modifier') }}</a>
                                    <form class="inline" method="POST" action="{{ route('budget.lignes.destroy', $ligne) }}" onsubmit="return confirm(@json(__('Archiver cette ligne ?')));">@csrf @method('DELETE')
                                        <button type="submit" class="text-ceeac-danger hover:underline">{{ __('Archiver') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="p-0"><x-ds.empty-state :title="__('Aucune ligne budgétaire')" :description="__('Créez une ligne ou importez le budget nomenclature.')" :action-url="route('budget.lignes.create')" :action-label="__('Ajouter une ligne')" class="border-0 shadow-none" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($lignes->hasPages())
                <div class="card-footer">{{ $lignes->links() }}</div>
            @endif
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
