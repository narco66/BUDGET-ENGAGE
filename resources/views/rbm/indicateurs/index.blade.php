<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Indicateurs') }}</h2>
            <a href="{{ route('rbm.indicateurs.create') }}">
                <x-secondary-button type="button">{{ __('Ajouter') }}</x-secondary-button>
            </a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-auth-session-status :status="session('status')" />
            @include('rbm.partials.filters')
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Code') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Libellé') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Type') }}</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Statut') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($indicateurs as $indicateur)
                            <tr>
                                <td class="px-4 py-3 font-mono">{{ $indicateur->code }}</td>
                                <td class="px-4 py-3">{{ $indicateur->libelle }}</td>
                                <td class="px-4 py-3">{{ $indicateur->typeIndicateur?->libelle }}</td>
                                <td class="px-4 py-3">{{ $indicateur->statut }}</td>
                                <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('rbm.indicateurs.show', $indicateur) }}" class="text-gray-600">{{ __('Voir') }}</a>
                                    <a href="{{ route('rbm.indicateurs.edit', $indicateur) }}" class="text-indigo-600">{{ __('Modifier') }}</a>
                                    <form action="{{ route('rbm.indicateurs.destroy', $indicateur) }}" method="POST" class="inline" onsubmit="return confirm(@json(__('Confirmer l\'archivage ?')));">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600">{{ __('Archiver') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('Aucun enregistrement.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $indicateurs->links() }}</div>
        </div>
    </div>
</x-app-layout>
