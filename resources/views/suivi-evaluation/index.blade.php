<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">{{ __('Suivi & Évaluation') }}</h2>
            @can('create', App\Models\IndicateurRealisation::class)
                <a href="{{ route('suivi-evaluation.create', $exercice ? ['exercice_budgetaire_id' => $exercice->id] : []) }}">
                    <x-primary-button type="button">{{ __('Saisir un indicateur') }}</x-primary-button>
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <form method="GET" class="bg-white p-4 shadow-sm rounded-lg flex flex-wrap items-end gap-3">
            <div>
                <x-input-label for="exercice_budgetaire_id" :value="__('Exercice')" />
                <select id="exercice_budgetaire_id" name="exercice_budgetaire_id" class="block mt-1 border-gray-300 rounded-md" onchange="this.form.submit()">
                    @foreach ($exercices as $ex)
                        <option value="{{ $ex->id }}" @selected($exercice && $ex->id === $exercice->id)>{{ $ex->annee }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="trimestre" :value="__('Trimestre')" />
                <select id="trimestre" name="trimestre" class="block mt-1 border-gray-300 rounded-md">
                    @for ($t = 1; $t <= 4; $t++)
                        <option value="{{ $t }}" @selected($trimestre === $t)>T{{ $t }}</option>
                    @endfor
                </select>
            </div>
            <x-primary-button type="submit">{{ __('Actualiser') }}</x-primary-button>
        </form>

        @if ($exercice && $synthese)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white rounded-xl shadow-sm p-5"><div class="text-xs text-gray-500 uppercase">{{ __('Indicateurs saisis (T:trimestre)', ['trimestre' => $trimestre]) }}</div><div class="text-2xl font-bold">{{ $synthese['nb_indicateurs_saisis'] }}</div></div>
                <div class="bg-white rounded-xl shadow-sm p-5"><div class="text-xs text-gray-500 uppercase">{{ __('Taux réalisation moyen') }}</div><div class="text-2xl font-bold">{{ $synthese['taux_realisation_moyen'] }} %</div></div>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
                <div class="px-5 py-3 border-b font-semibold">{{ __('Rapport trimestriel T:trim :annee', ['trim' => $trimestre, 'annee' => $exercice->annee]) }}</div>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left">{{ __('Code') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Indicateur') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('Cible') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('Réalisé') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('Taux') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($rapport as $row)
                            <tr class="{{ $row['saisi'] ? '' : 'text-gray-400' }}">
                                <td class="px-4 py-2 font-mono">{{ $row['code'] }}</td>
                                <td class="px-4 py-2">{{ $row['libelle'] }}</td>
                                <td class="px-4 py-2 text-right">{{ $row['valeur_cible'] !== null ? number_format($row['valeur_cible'], 2, ',', ' ') : '—' }}</td>
                                <td class="px-4 py-2 text-right">{{ $row['valeur_realisee'] !== null ? number_format($row['valeur_realisee'], 2, ',', ' ') : '—' }}</td>
                                <td class="px-4 py-2 text-right">{{ $row['taux_realisation'] !== null ? $row['taux_realisation'].' %' : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
