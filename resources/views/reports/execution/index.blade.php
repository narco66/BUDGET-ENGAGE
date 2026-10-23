<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <span>Rapports</span><span class="text-gray-300">/</span><span class="text-ceeac-primary font-semibold">{{ __('reports.execution.title') }}</span>
                </nav>
                <h2 class="page-title">{{ __('reports.execution.title') }}</h2>
            </div>
        </div>
    </x-slot>
    <div class="space-y-6">
        @if($limitedScope)
            <p class="alert-warning">{{ __('reports.execution.limited_scope') }}</p>
        @endif

        <form method="GET" action="{{ route('reports.execution.index') }}" class="card card-body flex flex-wrap gap-4 items-end">
            <div>
                <x-input-label for="exercice_budgetaire_id" :value="__('reports.execution.filter_exercice')" />
                <select id="exercice_budgetaire_id" name="exercice_budgetaire_id" class="form-select mt-1 text-sm">
                    <option value="">{{ __('reports.execution.all_exercices') }}</option>
                    @foreach ($exercices as $ex)
                        <option value="{{ $ex->id }}" @selected((string) $filters['exercice_budgetaire_id'] === (string) $ex->id)>{{ $ex->annee }}</option>
                    @endforeach
                </select>
            </div>
            <a href="{{ route('engagements.export.excel', ['exercice_budgetaire_id' => $filters['exercice_budgetaire_id']]) }}"
               class="btn btn-success btn-sm">
                {{ __('Export Excel') }}
            </a>
            <a href="{{ route('engagements.export.csv', ['exercice_budgetaire_id' => $filters['exercice_budgetaire_id']]) }}"
               class="btn btn-secondary btn-sm">
                {{ __('Export CSV') }}
            </a>
            <button class="btn btn-primary btn-sm" type="submit">{{ __('Filtrer') }}</button>
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="kpi-card text-sm">
                <div class="text-gray-500">{{ __('reports.execution.kpi_engagements') }}</div>
                <div class="text-2xl font-semibold tabular-nums">{{ $countEngagements }}</div>
            </div>
            <div class="kpi-card text-sm">
                <div class="text-gray-500">{{ __('reports.execution.kpi_montant_engagements') }}</div>
                <div class="text-xl font-semibold tabular-nums">{{ number_format((float) $sumMontantEngagements, 2, ',', ' ') }}</div>
            </div>
            <div class="kpi-card text-sm">
                <div class="text-gray-500">{{ __('reports.execution.kpi_liquide') }}</div>
                <div class="text-xl font-semibold tabular-nums">{{ number_format((float) $sumLiquide, 2, ',', ' ') }}</div>
            </div>
            <div class="kpi-card text-sm">
                <div class="text-gray-500">{{ __('reports.execution.kpi_ordonnance') }}</div>
                <div class="text-xl font-semibold tabular-nums">{{ number_format((float) $sumOrdonnance, 2, ',', ' ') }}</div>
            </div>
            <div class="kpi-card text-sm">
                <div class="text-gray-500">{{ __('reports.execution.kpi_paye') }}</div>
                <div class="text-xl font-semibold tabular-nums">{{ number_format((float) $sumPaye, 2, ',', ' ') }}</div>
            </div>
        </div>

        <div class="card overflow-x-auto">
            <table class="data-table">
                <thead><tr>
                    <th>{{ __('reports.execution.table_numero') }}</th>
                    <th>{{ __('reports.execution.table_statut') }}</th>
                    <th>{{ __('reports.execution.table_exercice') }}</th>
                    <th>{{ __('reports.execution.table_ligne') }}</th>
                    <th class="text-right">{{ __('reports.execution.table_montant') }}</th>
                    <th class="text-right">{{ __('reports.execution.table_liquide') }}</th>
                    <th class="text-right">{{ __('reports.execution.table_ord') }}</th>
                    <th class="text-right">{{ __('reports.execution.table_paye') }}</th>
                    <th>{{ __('reports.execution.table_createur') }}</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @forelse ($engagements as $engagement)
                        @php($st = $engagement->statut instanceof \BackedEnum ? $engagement->statut->value : $engagement->statut)
                        @php($libStatut = match ($st) {
                            'brouillon' => __('Brouillon'), 'soumis' => __('Soumis'), 'en_validation' => __('En validation'),
                            'retourne' => __('Retourné'), 'rejete' => __('Rejeté'), 'approuve' => __('Approuvé'), 'annule' => __('Annulé'),
                            'liquide' => __('Liquide'), 'ordonnance' => __('Ordonnancé'), 'paye' => __('Payé'), 'cloture' => __('Clôturé'),
                            default => $st,
                        })
                        @php($badgeClass = match ($st) {'brouillon'=>'badge-brouillon','soumis'=>'badge-soumis','en_validation'=>'badge-validation','retourne'=>'badge-retourne','rejete'=>'badge-rejete','approuve'=>'badge-approuve','annule'=>'badge-annule','liquide'=>'badge-liquide','ordonnance'=>'badge-ordonnance','paye'=>'badge-paye','cloture'=>'badge-cloture',default=>'badge-neutral'})
                        <tr>
                            <td class="font-mono">{{ $engagement->numero }}</td>
                            <td><span class="{{ $badgeClass }}">{{ $libStatut }}</span></td>
                            <td>{{ $engagement->exerciceBudgetaire?->annee ?? '—' }}</td>
                            <td class="font-mono">{{ $engagement->ligneBudgetaire?->code ?? '—' }}</td>
                            <td class="text-right tabular-nums">{{ number_format((float) $engagement->montant, 2, ',', ' ') }}</td>
                            <td class="text-right tabular-nums">{{ number_format((float) $engagement->report_total_liquide, 2, ',', ' ') }}</td>
                            <td class="text-right tabular-nums">{{ number_format((float) $engagement->report_total_ordonnance, 2, ',', ' ') }}</td>
                            <td class="text-right tabular-nums">{{ number_format((float) $engagement->report_total_paye, 2, ',', ' ') }}</td>
                            <td>{{ $engagement->createur?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap">
                                <a href="{{ route('engagements.show', $engagement) }}" class="text-indigo-600">{{ __('Voir') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="px-4 py-8 text-center text-gray-500">{{ __('reports.execution.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $engagements->links() }}</div>
    </div>
</x-app-layout>
