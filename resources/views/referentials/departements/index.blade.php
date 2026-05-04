<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Départements') }}</h2>
            <a href="{{ route('referentials.departements.create') }}">
                <x-secondary-button type="button">{{ __('Ajouter') }}</x-secondary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-auth-session-status class="mb-2" :status="session('status')" />
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Code') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Libellé') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Statut') }}</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">{{ __('Directions') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($departements as $departement)
                            <tr>
                                <td class="px-4 py-3 font-mono">{{ $departement->code }}</td>
                                <td class="px-4 py-3">{{ $departement->libelle }}</td>
                                <td class="px-4 py-3">{{ $departement->statut }}</td>
                                <td class="px-4 py-3 text-right">{{ $departement->directions_count }}</td>
                                <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('referentials.departements.edit', $departement) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Modifier') }}</a>
                                    <form action="{{ route('referentials.departements.destroy', $departement) }}" method="POST" class="inline" onsubmit="return confirm(@json(__('Confirmer la suppression ?')));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">{{ __('Archiver') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('Aucun département.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $departements->links() }}</div>
        </div>
    </div>
</x-app-layout>
