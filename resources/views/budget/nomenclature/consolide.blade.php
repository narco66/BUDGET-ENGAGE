<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('État consolidé PAP ↔ Budget ↔ Exécution') }}</h2>
            <div class="flex items-center gap-2">
                @if ($exercice && $consolide->isNotEmpty())
                    <a href="{{ route('budget.nomenclature.consolide.export.excel', ['exercice_budgetaire_id' => $exercice->id]) }}"><x-secondary-button type="button">{{ __('Export Excel') }}</x-secondary-button></a>
                    <a href="{{ route('budget.nomenclature.consolide.export.pdf', ['exercice_budgetaire_id' => $exercice->id]) }}"><x-secondary-button type="button">{{ __('Export PDF') }}</x-secondary-button></a>
                @endif
                <a href="{{ route('budget.nomenclature.synthese') }}"><x-secondary-button type="button">{{ __('Synthèse budgétaire') }}</x-secondary-button></a>
            </div>
        </div>
    </x-slot>

    @php $fmt = fn ($v) => number_format((float) $v, 0, ',', ' '); @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form method="GET" class="bg-white p-4 shadow-sm rounded-lg flex items-end gap-3">
                <div>
                    <x-input-label for="exercice_budgetaire_id" :value="__('Exercice')" />
                    <select id="exercice_budgetaire_id" name="exercice_budgetaire_id" class="block mt-1 border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                        @foreach ($exercices as $ex)
                            <option value="{{ $ex->id }}" @selected($exercice && $ex->id === $exercice->id)>{{ $ex->annee }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            @if (! $exercice || $consolide->isEmpty())
                <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg text-sm">
                    {{ __('Aucune donnée PAP/Budget pour cet exercice. Importez le PAP et le Budget d\'abord.') }}
                </div>
            @else
                @php $ecartPap = (float) ($papLigneBudget?->montant_total ?? 0) - (float) $papTotal; @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('PAP programmé') }}</div>
                        <div class="text-2xl font-bold text-ceeac-ink tabular-nums mt-1">{{ $fmt($papTotal) }}</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Ligne PAP du budget') }}</div>
                        <div class="text-2xl font-bold text-indigo-600 tabular-nums mt-1">{{ $fmt($papLigneBudget?->montant_total ?? 0) }}</div>
                        <div class="text-xs mt-1 {{ abs($ecartPap) < 1 ? 'text-emerald-600' : 'text-amber-600' }}">{{ __('Écart') }} : {{ $fmt($ecartPap) }} @if (abs($ecartPap) < 1) ✓ @endif</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Budget total dépenses') }}</div>
                        <div class="text-2xl font-bold text-gray-800 tabular-nums mt-1">{{ $fmt($totalDepenses) }}</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Exécution (engagé)') }}</div>
                        <div class="text-2xl font-bold text-ceeac-ink tabular-nums mt-1">{{ $fmt($execution['montant_engage'] ?? 0) }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ __('Taux') }} : {{ $execution['taux_engagement'] ?? 0 }}%</div>
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 text-sm font-semibold text-gray-700">{{ __('Par pilier : programmé (PAP) → budgété → exécuté') }}</div>
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="px-4 py-2 text-left">{{ __('Pilier') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('PAP programmé') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('Budgété') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('Taux budg.') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('Engagé') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('Payé') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('Taux engag.') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($consolide as $c)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2"><span class="font-mono text-xs text-gray-500">{{ $c['code'] }}</span> {{ \Illuminate\Support\Str::limit($c['libelle'], 55) }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($c['pap_total']) }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($c['budgete']) }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ $c['taux_budgetisation'] }}%</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($c['engage']) }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($c['paye']) }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ $c['taux_engagement'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 font-semibold">
                            <tr>
                                <td class="px-4 py-2">{{ __('Total') }}</td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($consolide->sum('pap_total')) }}</td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($consolide->sum('budgete')) }}</td>
                                <td class="px-4 py-2 text-right tabular-nums">—</td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($consolide->sum('engage')) }}</td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($consolide->sum('paye')) }}</td>
                                <td class="px-4 py-2 text-right tabular-nums">—</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <p class="text-xs text-gray-500">
                    {{ __('« Budgété » = lignes budgétaires rattachées aux lignes PAP du pilier. Reliez vos lignes budgétaires au PAP (champ « Ligne PAP rattachée ») pour enrichir cette consolidation.') }}
                </p>
            @endif
        </div>
    </div>
</x-app-layout>
