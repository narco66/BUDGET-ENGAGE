<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl">{{ __('Paramètres de contrôle') }}</h2>
            <a href="{{ route('budget.parametres.create') }}"><x-secondary-button type="button">{{ __('Ajouter') }}</x-secondary-button></a>
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <x-auth-session-status :status="session('status')" />
        <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left">{{ __('Clé') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Type') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Valeur') }}</th>
                    <th class="px-4 py-3 text-center">{{ __('Actif') }}</th>
                    <th class="px-4 py-3"></th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($parametres as $parametre)
                        <tr>
                            <td class="px-4 py-3 font-mono">{{ $parametre->cle }}</td>
                            <td class="px-4 py-3">{{ $parametre->type_valeur }}</td>
                            <td class="px-4 py-3 max-w-md truncate font-mono text-xs" title="{{ $parametre->valeur }}">{{ $parametre->valeur }}</td>
                            <td class="px-4 py-3 text-center">{{ $parametre->actif ? __('Oui') : __('Non') }}</td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('budget.parametres.edit', $parametre) }}" class="text-indigo-600">{{ __('Modifier') }}</a>
                                <form class="inline" method="POST" action="{{ route('budget.parametres.destroy', $parametre) }}" onsubmit="return confirm(@json(__('Supprimer ce paramètre ?')));">@csrf @method('DELETE')
                                    <button type="submit" class="text-red-600">{{ __('Supprimer') }}</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('Aucun paramètre.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $parametres->links() }}
    </div>
</x-app-layout>
