<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Historique des importations budgétaires') }}</h2>
            <a href="{{ route('budget.nomenclature.index') }}"><x-secondary-button type="button">{{ __('Nouvel import') }}</x-secondary-button></a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left">#</th>
                            <th class="px-4 py-2 text-left">{{ __('Exercice') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Version') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Mode') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Statut') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('Recettes') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('Dépenses') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Par') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($imports as $import)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2"><a href="{{ route('budget.nomenclature.show', $import) }}" class="text-ceeac-bright hover:underline">#{{ $import->id }}</a></td>
                                <td class="px-4 py-2">{{ $import->exerciceBudgetaire?->annee }}</td>
                                <td class="px-4 py-2">{{ $import->version }}</td>
                                <td class="px-4 py-2">{{ ucfirst(str_replace('_', ' ', $import->mode)) }}</td>
                                <td class="px-4 py-2"><x-pap-statut-badge :statut="$import->statut" /></td>
                                <td class="px-4 py-2 text-right">{{ number_format($import->montant_recettes, 0, ',', ' ') }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($import->montant_depenses, 0, ',', ' ') }}</td>
                                <td class="px-4 py-2">{{ $import->user?->name }}</td>
                                <td class="px-4 py-2">{{ $import->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-4 py-6 text-center text-gray-400">{{ __('Aucun import enregistré.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $imports->links() }}
        </div>
    </div>
</x-app-layout>
