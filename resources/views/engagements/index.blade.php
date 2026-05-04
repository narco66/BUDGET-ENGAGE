<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-2">
            <h2 class="font-semibold text-xl">{{ __('Engagements') }}</h2>
            @can('create', App\Models\Engagement::class)
                <a href="{{ route('engagements.create') }}"><x-secondary-button type="button">{{ __('Nouvel engagement') }}</x-secondary-button></a>
            @endcan
        </div>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
        <x-auth-session-status :status="session('status')" />
        <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left">{{ __('Numéro') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Exercice') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Ligne') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Montant') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Statut') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Créé par') }}</th>
                    <th class="px-4 py-3"></th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($engagements as $engagement)
                        @php($st = $engagement->statut instanceof \BackedEnum ? $engagement->statut->value : $engagement->statut)
                        @php($libStatut = match ($st) {
                            'brouillon' => __('Brouillon'),
                            'soumis' => __('Soumis'),
                            'en_validation' => __('En validation'),
                            'retourne' => __('Retourné'),
                            'rejete' => __('Rejeté'),
                            'approuve' => __('Approuvé'),
                            'annule' => __('Annulé'),
                            'liquide' => __('Liquide'),
                            'ordonnance' => __('Ordonnancé'),
                            'paye' => __('Payé'),
                            'cloture' => __('Clôturé'),
                            default => $st,
                        })
                        <tr>
                            <td class="px-4 py-3 font-mono whitespace-nowrap">{{ $engagement->numero }}</td>
                            <td class="px-4 py-3">{{ $engagement->exerciceBudgetaire?->annee ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono">{{ $engagement->ligneBudgetaire?->code ?? '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ number_format((float) $engagement->montant, 2, ',', ' ') }}</td>
                            <td class="px-4 py-3">{{ $libStatut }}</td>
                            <td class="px-4 py-3">{{ $engagement->createur?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('engagements.show', $engagement) }}" class="text-gray-600">{{ __('Voir') }}</a>
                                @can('update', $engagement)
                                    <a href="{{ route('engagements.edit', $engagement) }}" class="text-ceeac-bright hover:text-ceeac-primary font-medium">{{ __('Modifier') }}</a>
                                @endcan
                                @can('delete', $engagement)
                                    <form class="inline" method="POST" action="{{ route('engagements.destroy', $engagement) }}" onsubmit="return confirm(@json(__('Supprimer cet engagement ?')));">@csrf @method('DELETE')
                                        <button type="submit" class="text-red-600">{{ __('Supprimer') }}</button></form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">{{ __('Aucun engagement.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $engagements->links() }}
    </div>
</x-app-layout>
