<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <a href="{{ route('liquidations.index') }}" class="hover:text-ceeac-primary">{{ __('Liquidations') }}</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">{{ __('États') }}</span>
                </nav>
                <h2 class="page-title">{{ __('États & rapports de liquidation') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-4">
        <div class="card">
            <div class="card-body">
                <form method="GET" id="filtres" class="flex items-end gap-3">
                    <div>
                        <x-input-label for="exercice_budgetaire_id" :value="__('Exercice (filtre des états)')" />
                        <select id="exercice_budgetaire_id" name="exercice_budgetaire_id" class="form-select mt-1">
                            <option value="">— {{ __('Tous') }} —</option>
                            @foreach ($exercices as $ex)
                                <option value="{{ $ex->id }}" @selected((string) request('exercice_budgetaire_id') === (string) $ex->id)>{{ $ex->annee }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <table class="data-table">
                    <thead><tr><th>{{ __('État') }}</th><th class="text-center">{{ __('PDF') }}</th><th class="text-center">{{ __('Excel') }}</th><th class="text-center">{{ __('CSV') }}</th></tr></thead>
                    <tbody>
                        @foreach ($reports as $slug => $def)
                            <tr>
                                <td>{{ $def['label'] }}</td>
                                @foreach (['pdf', 'xlsx', 'csv'] as $format)
                                    <td class="text-center">
                                        <a href="{{ route('liquidations.reports.show', ['rapport' => $slug, 'format' => $format]) }}"
                                           class="text-ceeac-primary hover:underline text-sm"
                                           onclick="this.href = this.href.split('?')[0] + '?' + new URLSearchParams(new FormData(document.getElementById('filtres'))).toString()">
                                            {{ strtoupper($format === 'xlsx' ? 'xls' : $format) }}
                                        </a>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <p class="text-xs text-gray-400">{{ __(':n états × 3 formats (PDF / Excel / CSV).', ['n' => count($reports)]) }}</p>
    </div>
</x-app-layout>
