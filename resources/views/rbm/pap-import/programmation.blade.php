<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('État de programmation du PAP') }}</h2>
            <div class="flex items-center gap-2">
                @if ($exercice)
                    <a href="{{ route('rbm.pap-import.programmation.export.excel', ['exercice_budgetaire_id' => $exercice->id]) }}">
                        <x-secondary-button type="button">{{ __('Export Excel') }}</x-secondary-button>
                    </a>
                    <a href="{{ route('rbm.pap-import.programmation.export.pdf', ['exercice_budgetaire_id' => $exercice->id]) }}">
                        <x-secondary-button type="button">{{ __('Export PDF') }}</x-secondary-button>
                    </a>
                    <a href="{{ route('rbm.pap-import.programmation.export.source', ['exercice_budgetaire_id' => $exercice->id]) }}">
                        <x-secondary-button type="button">{{ __('Export source (CEEAC/PTF)') }}</x-secondary-button>
                    </a>
                @endif
                <a href="{{ route('rbm.pap-import.index') }}"><x-secondary-button type="button">{{ __('Importation') }}</x-secondary-button></a>
            </div>
        </div>
    </x-slot>

    @php $fmt = fn ($v) => number_format((float) $v, 0, ',', ' '); @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form method="GET" class="bg-white p-4 shadow-sm rounded-lg flex flex-wrap items-end gap-4">
                <div>
                    <x-input-label for="exercice_budgetaire_id" :value="__('Exercice')" />
                    <select id="exercice_budgetaire_id" name="exercice_budgetaire_id" class="block mt-1 border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                        @foreach ($exercices as $ex)
                            <option value="{{ $ex->id }}" @selected($exercice && $ex->id === $exercice->id)>{{ $ex->annee }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="niveau" :value="__('Niveau')" />
                    <select id="niveau" name="niveau" class="block mt-1 border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                        @foreach (['tous' => __('Tous (arborescence)'), 'pilier' => __('Piliers'), 'axe' => __('Axes'), 'produit' => __('Produits')] as $val => $lbl)
                            <option value="{{ $val }}" @selected($niveau === $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="source" :value="__('Source de financement')" />
                    <select id="source" name="source" class="block mt-1 border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                        @foreach (['tous' => __('Toutes'), 'ceeac' => __('CEEAC uniquement'), 'ptf' => __('PTF uniquement'), 'mixte' => __('Mixte (CEEAC + PTF)')] as $val => $lbl)
                            <option value="{{ $val }}" @selected($source === $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            @if (! $synthese || $synthese['nb_lignes'] === 0)
                <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg text-sm">
                    {{ __('Aucune donnée PAP pour cet exercice. Lancez d\'abord une importation.') }}
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Montant PAP total') }}</div>
                        <div class="text-2xl font-bold tabular-nums mt-1">{{ $fmt($synthese['montant_total']) }} <span class="text-sm text-gray-400">XAF</span></div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Montant budgété (lié)') }}</div>
                        <div class="text-2xl font-bold tabular-nums mt-1">{{ $fmt($synthese['montant_budgete']) }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ $synthese['nb_lignes_liees'] }} {{ __('ligne(s) budgétaire(s) liée(s)') }}</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Taux de couverture') }}</div>
                        <div class="text-2xl font-bold tabular-nums mt-1">{{ $synthese['taux_couverture'] }} %</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <div class="text-xs uppercase tracking-wide text-gray-500">{{ __('Écart à programmer') }}</div>
                        <div class="text-2xl font-bold tabular-nums mt-1">{{ $fmt($synthese['montant_total'] - $synthese['montant_budgete']) }}</div>
                    </div>
                </div>

                @if ($afficheListe)
                    @php
                        $niveauLbl = ['tous' => __('Tous'), 'pilier' => __('Piliers'), 'axe' => __('Axes'), 'produit' => __('Produits')][$niveau];
                        $sourceLbl = ['tous' => __('Toutes'), 'ceeac' => 'CEEAC', 'ptf' => 'PTF', 'mixte' => __('Mixte')][$source];
                        $sourceBadge = ['CEEAC' => 'bg-emerald-100 text-emerald-700', 'PTF' => 'bg-indigo-100 text-indigo-700', 'Mixte' => 'bg-amber-100 text-amber-700', 'Néant' => 'bg-gray-100 text-gray-500'];
                    @endphp
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                        <div class="px-5 py-3 border-b border-gray-100 text-sm font-semibold text-gray-700">
                            {{ __('Ventilation filtrée') }} — {{ __('Niveau') }} : {{ $niveauLbl }} · {{ __('Source') }} : {{ $sourceLbl }}
                            <span class="text-gray-400 font-normal">({{ $lignesFiltrees->count() }} {{ __('ligne(s)') }})</span>
                        </div>
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-500">
                                <tr>
                                    <th class="px-4 py-2 text-left">{{ __('Niveau') }}</th>
                                    <th class="px-4 py-2 text-left">{{ __('Source') }}</th>
                                    <th class="px-4 py-2 text-left">{{ __('Code') }}</th>
                                    <th class="px-4 py-2 text-left">{{ __('Libellé') }}</th>
                                    <th class="px-4 py-2 text-right">{{ __('Total PAP') }}</th>
                                    <th class="px-4 py-2 text-right">{{ __('CEEAC') }}</th>
                                    <th class="px-4 py-2 text-right">{{ __('PTF') }}</th>
                                    <th class="px-4 py-2 text-right">{{ __('Budgété') }}</th>
                                    <th class="px-4 py-2 text-right">{{ __('Écart') }}</th>
                                    <th class="px-4 py-2 text-right">{{ __('Couv.') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($lignesFiltrees as $r)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ $r['niveau'] }}</span></td>
                                        <td class="px-4 py-2"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sourceBadge[$r['source']] ?? 'bg-gray-100 text-gray-500' }}">{{ $r['source'] }}</span></td>
                                        <td class="px-4 py-2 font-mono text-xs text-gray-500">{{ $r['code'] }}</td>
                                        <td class="px-4 py-2">{{ \Illuminate\Support\Str::limit($r['libelle'], 60) }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($r['montant_total']) }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums text-emerald-600">{{ $fmt($r['montant_ceeac']) }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums text-indigo-600">{{ $fmt($r['montant_ptf']) }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($r['montant_budgete']) }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums {{ $r['ecart'] > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $fmt($r['ecart']) }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums">{{ $r['taux_couverture'] }}%</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="10" class="px-4 py-6 text-center text-gray-400">{{ __('Aucune ligne ne correspond à ces filtres.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-700">{{ __('Ventilation Pilier → Axe → Produit') }}</span>
                        <span class="text-xs text-gray-400">{{ __('Cliquez sur un pilier ou un axe pour déplier.') }}</span>
                    </div>
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="px-4 py-2 text-left">{{ __('Pilier / Axe / Produit') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('Total PAP') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('CEEAC') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('PTF') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('Budgété') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('Écart') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('Couv.') }}</th>
                            </tr>
                        </thead>
                            @foreach ($hierarchie as $pilier)
                                <tbody x-data="{ open: false }" class="divide-y divide-gray-100">
                                    <tr class="bg-indigo-50/60 cursor-pointer hover:bg-indigo-50" @click="open = !open">
                                        <td class="px-4 py-2 font-semibold">
                                            <span x-text="open ? '▾' : '▸'" class="inline-block w-4 text-gray-400"></span>
                                            <span class="font-mono text-xs text-gray-500">{{ $pilier['code'] }}</span> {{ \Illuminate\Support\Str::limit($pilier['libelle'], 70) }}
                                        </td>
                                        <td class="px-4 py-2 text-right tabular-nums font-semibold">{{ $fmt($pilier['montant_total']) }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums text-emerald-600">{{ $fmt($pilier['montant_ceeac']) }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums text-indigo-600">{{ $fmt($pilier['montant_ptf']) }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($pilier['montant_budgete']) }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums {{ $pilier['ecart'] > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $fmt($pilier['ecart']) }}</td>
                                        <td class="px-4 py-2 text-right tabular-nums">{{ $pilier['taux_couverture'] }}%</td>
                                    </tr>
                                    @foreach ($pilier['axes'] as $axe)
                                        <tr x-show="open" x-cloak x-data="{ openAxe: false }" class="cursor-pointer hover:bg-gray-50" @click="openAxe = !openAxe">
                                            <td class="px-4 py-2 pl-8">
                                                <span x-text="openAxe ? '▾' : '▸'" class="inline-block w-4 text-gray-400"></span>
                                                <span class="font-mono text-xs text-gray-500">{{ $axe['code'] }}</span> {{ \Illuminate\Support\Str::limit($axe['libelle'], 65) }}
                                            </td>
                                            <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($axe['montant_total']) }}</td>
                                            <td class="px-4 py-2 text-right tabular-nums text-emerald-600">{{ $fmt($axe['montant_ceeac']) }}</td>
                                            <td class="px-4 py-2 text-right tabular-nums text-indigo-600">{{ $fmt($axe['montant_ptf']) }}</td>
                                            <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($axe['montant_budgete']) }}</td>
                                            <td class="px-4 py-2 text-right tabular-nums {{ $axe['ecart'] > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $fmt($axe['ecart']) }}</td>
                                            <td class="px-4 py-2 text-right tabular-nums">{{ $axe['taux_couverture'] }}%</td>
                                        </tr>
                                        @foreach ($axe['produits'] as $produit)
                                            <tr x-show="open && openAxe" x-cloak class="text-gray-600">
                                                <td class="px-4 py-2 pl-16">
                                                    <span class="font-mono text-xs text-gray-400">{{ $produit['code'] }}</span> {{ \Illuminate\Support\Str::limit($produit['libelle'], 60) }}
                                                </td>
                                                <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($produit['montant_total']) }}</td>
                                                <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($produit['montant_ceeac']) }}</td>
                                                <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($produit['montant_ptf']) }}</td>
                                                <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($produit['montant_budgete']) }}</td>
                                                <td class="px-4 py-2 text-right tabular-nums {{ $produit['ecart'] > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $fmt($produit['ecart']) }}</td>
                                                <td class="px-4 py-2 text-right tabular-nums">{{ $produit['taux_couverture'] }}%</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            @endforeach
                        <tfoot class="bg-gray-50 font-semibold">
                            <tr>
                                <td class="px-4 py-2">{{ __('Total') }}</td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($synthese['montant_total']) }}</td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($synthese['montant_ceeac']) }}</td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($synthese['montant_ptf']) }}</td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($synthese['montant_budgete']) }}</td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($synthese['montant_total'] - $synthese['montant_budgete']) }}</td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ $synthese['taux_couverture'] }}%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @endif

                <p class="text-xs text-gray-500">
                    {{ __('Reliez une ligne budgétaire à une ligne PAP depuis le formulaire de la ligne budgétaire (champ « Ligne PAP rattachée ») pour alimenter la couverture.') }}
                </p>
            @endif
        </div>
    </div>
</x-app-layout>
