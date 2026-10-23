<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">{{ __('Matrice RBM/GAR') }}</h2>
            <a href="{{ route('rbm.export.matrice-excel') }}"><x-secondary-button>{{ __('Export Excel') }}</x-secondary-button></a>
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 overflow-x-auto">
        <table class="min-w-full bg-white shadow-sm text-sm">
            <thead class="bg-gray-50"><tr>
                <th class="px-3 py-2 text-left">Pilier</th><th class="px-3 py-2 text-left">Axe</th>
                <th class="px-3 py-2 text-left">Produit</th><th class="px-3 py-2 text-left">Sous-produit</th>
                <th class="px-3 py-2 text-left">Activité</th><th class="px-3 py-2 text-left">Tâche</th>
                <th class="px-3 py-2 text-left">Indicateurs</th>
            </tr></thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-t">
                        <td class="px-3 py-2">{{ $row['pilier'] }}</td><td class="px-3 py-2">{{ $row['axe'] }}</td>
                        <td class="px-3 py-2">{{ $row['produit'] }}</td><td class="px-3 py-2">{{ $row['sous_produit'] }}</td>
                        <td class="px-3 py-2">{{ $row['activite'] }}</td><td class="px-3 py-2">{{ $row['tache'] }}</td>
                        <td class="px-3 py-2">{{ $row['indicateurs'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-3 py-8 text-center text-gray-500">{{ __('Aucune donnée.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>