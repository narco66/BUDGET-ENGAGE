<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl">{{ __('Exercices budgétaires') }}</h2>
            <a href="{{ route('budget.exercices.create') }}"><x-secondary-button type="button">{{ __('Ajouter') }}</x-secondary-button></a>
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <x-auth-session-status :status="session('status')" />
        <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left">{{ __('Année') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Période') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Statut') }}</th>
                    <th class="px-4 py-3 text-center">{{ __('Actif') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Lignes') }}</th>
                    <th class="px-4 py-3"></th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($exercices as $exercice)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $exercice->annee }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $exercice->date_debut?->format('d/m/Y') }} — {{ $exercice->date_fin?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">{{ $exercice->statut instanceof \BackedEnum ? $exercice->statut->value : $exercice->statut }}</td>
                            <td class="px-4 py-3 text-center">{{ $exercice->est_actif ? __('Oui') : __('Non') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $exercice->lignes_budgetaires_count }}</td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('budget.exercices.edit', $exercice) }}" class="text-indigo-600">{{ __('Modifier') }}</a>
                                <form class="inline" method="POST" action="{{ route('budget.exercices.destroy', $exercice) }}" onsubmit="return confirm(@json(__('Supprimer cet exercice ?')));">@csrf @method('DELETE')
                                    <button type="submit" class="text-red-600">{{ __('Supprimer') }}</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('Aucun exercice.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $exercices->links() }}
    </div>
</x-app-layout>
