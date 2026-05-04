<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl">{{ __('Fournisseurs') }}</h2>
            <a href="{{ route('referentials.fournisseurs.create') }}"><x-secondary-button type="button">{{ __('Ajouter') }}</x-secondary-button></a>
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <x-auth-session-status :status="session('status')" />
        <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left">{{ __('Raison sociale') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Sigle') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Statut') }}</th>
                    <th class="px-4 py-3"></th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($fournisseurs as $fournisseur)
                        <tr>
                            <td class="px-4 py-3">{{ $fournisseur->raison_sociale }}</td>
                            <td class="px-4 py-3">{{ $fournisseur->sigle ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $fournisseur->statut }}</td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('referentials.fournisseurs.edit', $fournisseur) }}" class="text-indigo-600">{{ __('Modifier') }}</a>
                                <form class="inline" method="POST" action="{{ route('referentials.fournisseurs.destroy', $fournisseur) }}" onsubmit="return confirm(@json(__('Archiver ce fournisseur ?')));">@csrf @method('DELETE')
                                    <button type="submit" class="text-red-600">{{ __('Archiver') }}</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">{{ __('Aucun fournisseur.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $fournisseurs->links() }}
    </div>
</x-app-layout>
