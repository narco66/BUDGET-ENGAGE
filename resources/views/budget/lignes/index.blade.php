<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl">{{ __('Lignes budgétaires') }}</h2>
            <a href="{{ route('budget.lignes.create') }}"><x-secondary-button type="button">{{ __('Ajouter') }}</x-secondary-button></a>
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <x-auth-session-status :status="session('status')" />
        <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left">{{ __('Exercice') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Code') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Libellé') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Nature') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Département') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Voté') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Statut') }}</th>
                    <th class="px-4 py-3"></th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($lignes as $ligne)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $ligne->exerciceBudgetaire?->annee ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono">{{ $ligne->code }}</td>
                            <td class="px-4 py-3 max-w-xs truncate" title="{{ $ligne->libelle }}">{{ $ligne->libelle }}</td>
                            <td class="px-4 py-3">{{ $ligne->natureDepense?->code ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $ligne->departement?->libelle ?? '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ number_format((float) $ligne->montant_vote, 2, ',', ' ') }}</td>
                            <td class="px-4 py-3">{{ $ligne->statut }}</td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('budget.lignes.edit', $ligne) }}" class="text-indigo-600">{{ __('Modifier') }}</a>
                                <form class="inline" method="POST" action="{{ route('budget.lignes.destroy', $ligne) }}" onsubmit="return confirm(@json(__('Archiver cette ligne ?')));">@csrf @method('DELETE')
                                    <button type="submit" class="text-red-600">{{ __('Archiver') }}</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">{{ __('Aucune ligne.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $lignes->links() }}
    </div>
</x-app-layout>
