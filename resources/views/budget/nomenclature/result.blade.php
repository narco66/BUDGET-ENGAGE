<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $simulation ? __('Résultat de la simulation') : __('Résultat de l\'importation') }} — #{{ $import->id }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('budget.nomenclature.index') }}"><x-secondary-button type="button">{{ __('Nouvel import') }}</x-secondary-button></a>
                <a href="{{ route('budget.nomenclature.show', $import) }}"><x-secondary-button type="button">{{ __('Détail') }}</x-secondary-button></a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @php
                $bloquants = collect($import->rapport ?? [])->where('severite', 'bloquant');
                $majeurs = collect($import->rapport ?? [])->where('severite', 'majeur');
                $avert = collect($import->rapport ?? [])->where('severite', 'avertissement');
                $infos = collect($import->rapport ?? [])->where('severite', 'information');
            @endphp

            <div class="rounded-lg p-4 text-sm
                {{ $import->statut === 'valide' ? 'bg-green-50 border border-green-200 text-green-800' : '' }}
                {{ $import->statut === 'simule' ? 'bg-blue-50 border border-blue-200 text-blue-800' : '' }}
                {{ $import->statut === 'echoue' ? 'bg-red-50 border border-red-200 text-red-800' : '' }}">
                @if ($import->statut === 'echoue')
                    {{ __('Import refusé : :n anomalie(s) bloquante(s). Aucune donnée n\'a été modifiée.', ['n' => $bloquants->count()]) }}
                @elseif ($import->statut === 'simule')
                    {{ __('Simulation réalisée. Aucune donnée n\'a été écrite.') }}
                @else
                    {{ __('Importation effectuée avec succès en mode « :mode ».', ['mode' => str_replace('_', ' ', $import->mode)]) }}
                @endif
            </div>

            @foreach ($infos as $info)
                <div class="bg-gray-50 border border-gray-200 text-gray-700 px-4 py-2 rounded text-sm">{{ $info['regle'] }}</div>
            @endforeach

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ([
                    ['Titres', $import->nb_titres], ['Chapitres', $import->nb_chapitres],
                    ['Articles', $import->nb_articles], ['Paragraphes', $import->nb_paragraphes],
                    ['Lignes', $import->nb_lignes], ['Recettes', $import->nb_recettes],
                    ['Dépenses', $import->nb_depenses], ['Créations', $import->nb_creations],
                    ['Mises à jour', $import->nb_mises_a_jour], ['Suppressions', $import->nb_suppressions],
                    ['Doublons', $import->nb_doublons], ['Avertissements', $import->nb_avertissements],
                ] as [$label, $val])
                    <div class="bg-white shadow-sm rounded-lg p-4">
                        <div class="text-2xl font-semibold text-gray-800">{{ number_format($val, 0, ',', ' ') }}</div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide">{{ __($label) }}</div>
                    </div>
                @endforeach
                <div class="bg-emerald-50 shadow-sm rounded-lg p-4 col-span-2">
                    <div class="text-xl font-semibold text-gray-900">{{ number_format($import->montant_recettes, 0, ',', ' ') }} FCFA</div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Total recettes') }}</div>
                </div>
                <div class="bg-indigo-50 shadow-sm rounded-lg p-4 col-span-2">
                    <div class="text-xl font-semibold text-gray-900">{{ number_format($import->montant_depenses, 0, ',', ' ') }} FCFA</div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Total dépenses') }} · CEEAC {{ number_format($import->montant_ceeac, 0, ',', ' ') }} · PTF {{ number_format($import->montant_ptf, 0, ',', ' ') }}</div>
                </div>
            </div>

            @if ($bloquants->isNotEmpty())
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-3 border-b border-gray-100 font-semibold text-red-700">{{ __('Anomalies bloquantes') }}</div>
                    @include('rbm.pap-import._issues', ['issues' => $bloquants])
                </div>
            @endif
            @if ($majeurs->isNotEmpty())
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-3 border-b border-gray-100 font-semibold text-orange-700">{{ __('Erreurs majeures (:n)', ['n' => $majeurs->count()]) }}</div>
                    @include('rbm.pap-import._issues', ['issues' => $majeurs])
                </div>
            @endif
            @if ($avert->isNotEmpty())
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-3 border-b border-gray-100 font-semibold text-amber-700">{{ __('Avertissements (:n)', ['n' => $avert->count()]) }}</div>
                    @include('rbm.pap-import._issues', ['issues' => $avert])
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
