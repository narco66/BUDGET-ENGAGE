<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Synthèse budgétaire') }}</h2>
            <a href="{{ route('budget.nomenclature.index') }}"><x-secondary-button type="button">{{ __('Importation') }}</x-secondary-button></a>
        </div>
    </x-slot>

    @php $fmt = fn ($v) => number_format((float) $v, 0, ',', ' '); @endphp

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
            @endif

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

            @if (! $exercice || ($recettes->isEmpty() && $depenses->isEmpty()))
                <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg text-sm">
                    {{ __('Aucun budget importé pour cet exercice. Lancez d\'abord une importation.') }}
                </div>
            @else
                @php $ecart = (float) $totalRecettes - (float) $totalDepenses; @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Total recettes') }}</div>
                        <div class="text-2xl font-bold text-emerald-600 tabular-nums mt-1">{{ $fmt($totalRecettes) }}</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Total dépenses') }}</div>
                        <div class="text-2xl font-bold text-indigo-600 tabular-nums mt-1">{{ $fmt($totalDepenses) }}</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Équilibre (R − D)') }}</div>
                        <div class="text-2xl font-bold tabular-nums mt-1 {{ abs($ecart) < 1 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $fmt($ecart) }}</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Dépenses CEEAC / PTF') }}</div>
                        <div class="text-base font-semibold tabular-nums mt-1"><span class="text-emerald-600">{{ $fmt($totalCeeac) }}</span> / <span class="text-indigo-600">{{ $fmt($totalPtf) }}</span></div>
                    </div>
                </div>

                @if ($papLigne)
                    @php $ecartPap = (float) $papLigne->montant_total - (float) $papTotal; @endphp
                    <div class="rounded-lg p-4 text-sm {{ abs($ecartPap) < 1 ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-amber-50 border border-amber-200 text-amber-800' }}">
                        {{ __('Rapprochement Budget ↔ PAP :') }}
                        {{ __('ligne PAP du budget') }} = <strong>{{ $fmt($papLigne->montant_total) }}</strong> ·
                        {{ __('total du PAP importé') }} = <strong>{{ $fmt($papTotal) }}</strong> ·
                        {{ __('écart') }} = <strong>{{ $fmt($ecartPap) }}</strong>
                        @if (abs($ecartPap) < 1) ✓ @endif
                    </div>
                @endif

                @if ($alertes->isNotEmpty())
                    @php($badge = ['depassement' => 'bg-red-100 text-red-800', 'critique' => 'bg-orange-100 text-orange-800', 'avertissement' => 'bg-amber-100 text-amber-800'])
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden border-l-4 border-amber-400">
                        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-700">{{ __('Alertes de seuil') }} <span class="text-gray-400 font-normal">({{ $alertes->count() }} {{ __('ligne(s) à surveiller') }})</span></span>
                            @can('budget.alertes')<a href="{{ route('budget.alertes.historique') }}" class="text-xs text-ceeac-bright hover:underline">{{ __('Historique →') }}</a>@endcan
                        </div>
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-500">
                                <tr>
                                    <th class="px-4 py-2 text-left">{{ __('Niveau') }}</th>
                                    <th class="px-4 py-2 text-left">{{ __('Ligne') }}</th>
                                    <th class="px-4 py-2 text-right">{{ __('Engagé') }}</th>
                                    <th class="px-4 py-2 text-right">{{ __('Plafond') }}</th>
                                    <th class="px-4 py-2 text-right">{{ __('Taux') }}</th>
                                    @can('budget.alertes')<th class="px-4 py-2 text-right">{{ __('Action') }}</th>@endcan
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($alertes as $a)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge[$a->niveau] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($a->niveau) }}</span></td>
                                        <td class="px-4 py-2"><span class="font-mono text-xs text-gray-500">{{ $a->code }}</span> {{ \Illuminate\Support\Str::limit($a->message, 50) }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($a->montant_engage) }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($a->plafond) }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums font-semibold {{ $a->niveau === 'depassement' ? 'text-red-600' : ($a->niveau === 'critique' ? 'text-orange-600' : 'text-amber-600') }}">{{ $a->taux }}%</td>
                                        @can('budget.alertes')
                                            <td class="px-4 py-2 text-right">
                                                <form method="POST" action="{{ route('budget.alertes.traiter', $a) }}">
                                                    @csrf
                                                    <button class="px-2 py-1 rounded bg-blue-100 text-blue-700 text-xs hover:bg-blue-200">{{ __('Traiter') }}</button>
                                                </form>
                                            </td>
                                        @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Execution budgetaire (lignes operationnelles) --}}
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                        <span class="text-sm font-semibold text-gray-700">{{ __('Exécution budgétaire (chaîne de la dépense)') }}</span>
                        <div class="flex items-center gap-2">
                            @if (($execution['nb_lignes_operationnelles'] ?? 0) > 0)
                                <a href="{{ route('budget.nomenclature.execution.export.excel', ['exercice_budgetaire_id' => $exercice->id]) }}"><x-secondary-button type="button">{{ __('Export Excel') }}</x-secondary-button></a>
                                <a href="{{ route('budget.nomenclature.execution.export.pdf', ['exercice_budgetaire_id' => $exercice->id]) }}"><x-secondary-button type="button">{{ __('Export PDF') }}</x-secondary-button></a>
                            @endif
                            @can('budget.sync')
                                <form method="POST" action="{{ route('budget.nomenclature.synchronize', ['exercice_budgetaire_id' => $exercice->id]) }}"
                                      onsubmit="return confirm('{{ __('Générer / mettre à jour les lignes budgétaires opérationnelles depuis le budget ? Les montants d\'exécution existants sont préservés.') }}');">
                                    @csrf
                                    <x-secondary-button>{{ __('Générer les lignes opérationnelles') }}</x-secondary-button>
                                </form>
                            @endcan
                        </div>
                    </div>

                    @if (($execution['nb_lignes_operationnelles'] ?? 0) === 0)
                        <p class="text-sm text-gray-500">{{ __('Aucune ligne opérationnelle générée. Cliquez sur « Générer les lignes opérationnelles » pour ouvrir les crédits dans la chaîne de la dépense.') }}</p>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            <div><div class="text-xs uppercase text-gray-500">{{ __('Crédits ouverts') }}</div><div class="text-lg font-bold tabular-nums">{{ $fmt($execution['credits_ouverts']) }}</div></div>
                            <div><div class="text-xs uppercase text-gray-500">{{ __('Engagé') }}</div><div class="text-lg font-bold tabular-nums">{{ $fmt($execution['montant_engage']) }}</div></div>
                            <div><div class="text-xs uppercase text-gray-500">{{ __('Payé') }}</div><div class="text-lg font-bold tabular-nums">{{ $fmt($execution['montant_paye']) }}</div></div>
                            <div><div class="text-xs uppercase text-gray-500">{{ __('Disponible') }}</div><div class="text-lg font-bold tabular-nums">{{ $fmt($execution['disponible']) }}</div></div>
                        </div>
                        <div class="text-xs text-gray-500 mb-2">{{ __('Taux d\'engagement') }} : {{ $execution['taux_engagement'] }}% · {{ __('Taux de paiement') }} : {{ $execution['taux_paiement'] }}% · {{ $execution['nb_lignes_operationnelles'] }} {{ __('ligne(s) opérationnelle(s)') }}</div>
                        <div class="h-2.5 w-full bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-ceeac-bright" style="width: {{ min(100, (float) $execution['taux_engagement']) }}%"></div>
                        </div>

                        @if ($executionParCategorie->isNotEmpty())
                            <table class="min-w-full text-sm mt-4">
                                <thead class="bg-gray-50 text-gray-500">
                                    <tr>
                                        <th class="px-3 py-2 text-left">{{ __('Catégorie') }}</th>
                                        <th class="px-3 py-2 text-right">{{ __('Ouverts') }}</th>
                                        <th class="px-3 py-2 text-right">{{ __('Engagé') }}</th>
                                        <th class="px-3 py-2 text-right">{{ __('Payé') }}</th>
                                        <th class="px-3 py-2 text-right">{{ __('Disponible') }}</th>
                                        <th class="px-3 py-2 text-right">{{ __('Taux') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($executionParCategorie as $c)
                                        <tr>
                                            <td class="px-3 py-2">{{ $c['libelle'] }}</td>
                                            <td class="px-3 py-2 text-right tabular-nums">{{ $fmt($c['credits_ouverts']) }}</td>
                                            <td class="px-3 py-2 text-right tabular-nums">{{ $fmt($c['montant_engage']) }}</td>
                                            <td class="px-3 py-2 text-right tabular-nums">{{ $fmt($c['montant_paye']) }}</td>
                                            <td class="px-3 py-2 text-right tabular-nums">{{ $fmt($c['disponible']) }}</td>
                                            <td class="px-3 py-2 text-right tabular-nums">{{ $c['taux_engagement'] }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    @endif
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="px-5 py-3 border-b border-gray-100 text-sm font-semibold text-emerald-700">{{ __('Recettes (par titre)') }}</div>
                        <table class="min-w-full text-sm">
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($recettes as $r)
                                    <tr><td class="px-4 py-2">{{ \Illuminate\Support\Str::limit($r->libelle, 50) }}</td><td class="px-4 py-2 text-right tabular-nums">{{ $fmt($r->montant_total) }}</td></tr>
                                @endforeach
                                <tr class="bg-gray-50 font-semibold"><td class="px-4 py-2">{{ __('Total') }}</td><td class="px-4 py-2 text-right tabular-nums">{{ $fmt($totalRecettes) }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="px-5 py-3 border-b border-gray-100 text-sm font-semibold text-indigo-700">{{ __('Dépenses (par titre)') }}</div>
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-500"><tr><th class="px-4 py-2 text-left">{{ __('Titre') }}</th><th class="px-4 py-2 text-right">{{ __('Total') }}</th><th class="px-4 py-2 text-right">CEEAC</th><th class="px-4 py-2 text-right">PTF</th></tr></thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($depenses as $d)
                                    <tr>
                                        <td class="px-4 py-2">{{ \Illuminate\Support\Str::limit($d->libelle, 40) }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($d->montant_total) }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums text-emerald-600">{{ $fmt($d->montant_ceeac) }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums text-indigo-600">{{ $fmt($d->montant_ptf) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50 font-semibold">
                                    <td class="px-4 py-2">{{ __('Total') }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($totalDepenses) }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($totalCeeac) }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($totalPtf) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
