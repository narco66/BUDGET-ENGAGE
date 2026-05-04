<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl">{{ __('Sources de financement') }}</h2>
            <a href="{{ route('referentials.sources-financement.create') }}"><x-secondary-button type="button">{{ __('Ajouter') }}</x-secondary-button></a>
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <x-auth-session-status :status="session('status')" />
        <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left">{{ __('Code') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Libellé') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Statut') }}</th>
                    <th class="px-4 py-3"></th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($sources as $source)
                        <tr>
                            <td class="px-4 py-3 font-mono">{{ $source->code }}</td>
                            <td class="px-4 py-3">{{ $source->libelle }}</td>
                            <td class="px-4 py-3">{{ $source->statut }}</td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('referentials.sources-financement.edit', $source) }}" class="text-indigo-600">{{ __('Modifier') }}</a>
                                <form class="inline" method="POST" action="{{ route('referentials.sources-financement.destroy', $source) }}" onsubmit="return confirm(@json(__('Supprimer ?')));">@csrf @method('DELETE')
                                    <button type="submit" class="text-red-600">{{ __('Supprimer') }}</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">{{ __('Aucune source.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $sources->links() }}
    </div>
</x-app-layout>
