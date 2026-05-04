<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl">{{ __('Agents') }}</h2>
            <a href="{{ route('referentials.agents.create') }}"><x-secondary-button type="button">{{ __('Ajouter') }}</x-secondary-button></a>
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <x-auth-session-status :status="session('status')" />
        <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left">{{ __('Nom') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Matricule') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Service') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Statut') }}</th>
                    <th class="px-4 py-3"></th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($agents as $agent)
                        <tr>
                            <td class="px-4 py-3">{{ $agent->nom }} {{ $agent->prenom }}</td>
                            <td class="px-4 py-3 font-mono">{{ $agent->matricule ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $agent->service?->libelle ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $agent->statut }}</td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('referentials.agents.edit', $agent) }}" class="text-indigo-600">{{ __('Modifier') }}</a>
                                <form class="inline" method="POST" action="{{ route('referentials.agents.destroy', $agent) }}" onsubmit="return confirm(@json(__('Archiver cet agent ?')));">@csrf @method('DELETE')
                                    <button type="submit" class="text-red-600">{{ __('Archiver') }}</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('Aucun agent.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $agents->links() }}
    </div>
</x-app-layout>
