<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Directions') }}</h2>
            <a href="{{ route('referentials.directions.create') }}"><x-secondary-button type="button">{{ __('Ajouter') }}</x-secondary-button></a>
        </div>
    </x-slot>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-auth-session-status class="mb-2" :status="session('status')" />
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left">{{ __('Département') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('Code') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('Libellé') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('Statut') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('Services') }}</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($directions as $direction)
                            <tr>
                                <td class="px-4 py-3">{{ $direction->departement?->libelle }}</td>
                                <td class="px-4 py-3 font-mono">{{ $direction->code }}</td>
                                <td class="px-4 py-3">{{ $direction->libelle }}</td>
                                <td class="px-4 py-3">{{ $direction->statut }}</td>
                                <td class="px-4 py-3 text-right">{{ $direction->services_count }}</td>
                                <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('referentials.directions.edit', $direction) }}" class="text-indigo-600">{{ __('Modifier') }}</a>
                                    <form action="{{ route('referentials.directions.destroy', $direction) }}" method="POST" class="inline" onsubmit="return confirm(@json(__('Confirmer ?')));">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600">{{ __('Archiver') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('Aucune direction.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div>{{ $directions->links() }}</div>
        </div>
    </div>
</x-app-layout>
