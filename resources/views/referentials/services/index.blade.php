<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">{{ __('Services') }}</h2>
            <a href="{{ route('referentials.services.create') }}"><x-secondary-button type="button">{{ __('Ajouter') }}</x-secondary-button></a>
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <x-auth-session-status :status="session('status')" />
        <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left">{{ __('Direction') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Code') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Libellé') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Statut') }}</th>
                    <th class="px-4 py-3"></th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($services as $service)
                        <tr>
                            <td class="px-4 py-3">{{ $service->direction?->libelle }}</td>
                            <td class="px-4 py-3 font-mono">{{ $service->code }}</td>
                            <td class="px-4 py-3">{{ $service->libelle }}</td>
                            <td class="px-4 py-3">{{ $service->statut }}</td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('referentials.services.edit', $service) }}" class="text-indigo-600">{{ __('Modifier') }}</a>
                                <form class="inline" method="POST" action="{{ route('referentials.services.destroy', $service) }}" onsubmit="return confirm(@json(__('Confirmer ?')));">@csrf @method('DELETE')
                                    <button type="submit" class="text-red-600">{{ __('Archiver') }}</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('Aucun service.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $services->links() }}
    </div>
</x-app-layout>
