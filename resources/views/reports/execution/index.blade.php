<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">{{ __('reports.execution.title') }}</h2>
    </x-slot>
    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if($limitedScope)
            <p class="text-sm text-amber-800 bg-amber-50 border border-amber-100 rounded-lg px-4 py-2">{{ __('reports.execution.limited_scope') }}</p>
        @endif

        <form method="GET" action="{{ route('reports.execution.index') }}" class="flex flex-wrap gap-4 items-end bg-white shadow-sm sm:rounded-lg p-4">
            <div>
                <x-input-label for="exercice_budgetaire_id" :value="__('reports.execution.filter_exercice')" />
                <select id="exercice_budgetaire_id" name="exercice_budgetaire_id" class="block mt-1 border-gray-300 rounded-md shadow-sm text-sm">
                    <option value="">{{ __('reports.execution.all_exercices') }}</option>
                    @foreach ($exercices as $ex)
                        <option value="{{ $ex->id }}" @selected((string) $filters['exercice_budgetaire_id'] === (string) $ex->id)>{{ $ex->annee }}</option>
                    @endforeach
                </select>
            </div>
            <x-primary-button type="submit">{{ __('Filtrer') }}</x-primary-button>
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white shadow-sm sm:rounded-lg p-4 text-sm">
                <div class="text-gray-500">{{ __('reports.execution.kpi_engagements') }}</div>
                <div class="text-2xl font-semibold tabular-nums">{{ $countEngagements }}</div>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg p-4 text-sm">
                <div class="text-gray-500">{{ __('reports.execution.kpi_montant_engagements') }}</div>
                <div class="text-xl font-semibold tabular-nums">{{ number_format((float) $sumMontantEngagements, 2, ',', ' ') }}</div>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg p-4 text-sm">
                <div class="text-gray-500">{{ __('reports.execution.kpi_liquide') }}</div>
                <div class="text-xl font-semibold tabular-nums">{{ number_format((float) $sumLiquide, 2, ',', ' ') }}</div>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg p-4 text-sm">
                <div class="text-gray-500">{{ __('reports.execution.kpi_ordonnance') }}</div>
                <div class="text-xl font-semibold tabular-nums">{{ number_format((float) $sumOrdonnance, 2, ',', ' ') }}</div>
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg p-4 text-sm">
                <div class="text-gray-500">{{ __('reports.execution.kpi_paye') }}</div>
                <div class="text-xl font-semibold tabular-nums">{{ number_format((float) $sumPaye, 2, ',', ' ') }}</div>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
            <table class="min-w-full text-sm divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-3 text-left">{{ __('reports.execution.table_numero') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('reports.execution.table_statut') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('reports.execution.table_exercice') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('reports.execution.table_ligne') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('reports.execution.table_montant') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('reports.execution.table_liquide') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('reports.execution.table_ord') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('reports.execution.table_paye') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('reports.execution.table_createur') }}</th>
                    <th class="px-4 py-3"></th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($engagements as $engagement)
                        @php($st = $engagement->statut instanceof \BackedEnum ? $engagement->statut->value : $engagement->statut)
                        @php($libStatut = match ($st) {
                            'brouillon' => __('Brouillon'), 'soumis' => __('Soumis'), 'en_validation' => __('En validation'),
                            'retourne' => __('Retourné'), 'rejete' => __('Rejeté'), 'approuve' => __('Approuvé'), 'annule' => __('Annulé'),
                            'liquide' => __('Liquide'), 'ordonnance' => __('Ordonnancé'), 'paye' => __('Payé'), 'cloture' => __('Clôturé'),
                            default => $st,
                        })
                        <tr>
                            <td class="px-4 py-3 font-mono">{{ $engagement->numero }}</td>
                            <td class="px-4 py-3">{{ $libStatut }}</td>
                            <td class="px-4 py-3">{{ $engagement->exerciceBudgetaire?->annee ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono">{{ $engagement->ligneBudgetaire?->code ?? '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ number_format((float) $engagement->montant, 2, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ number_format((float) $engagement->report_total_liquide, 2, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ number_format((float) $engagement->report_total_ordonnance, 2, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ number_format((float) $engagement->report_total_paye, 2, ',', ' ') }}</td>
                            <td class="px-4 py-3">{{ $engagement->createur?->name ?? '—' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap"><a href="{{ route('engagements.show', $engagement) }}" class="text-indigo-600">{{ __('Voir') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="px-4 py-8 text-center text-gray-500">{{ __('reports.execution.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $engagements->links() }}
    </div>
</x-app-layout>
