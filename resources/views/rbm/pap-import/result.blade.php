<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $simulation ? __('Résultat de la simulation') : __('Résultat de l\'importation') }} — #{{ $import->id }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('rbm.pap-import.index') }}"><x-secondary-button type="button">{{ __('Nouvel import') }}</x-secondary-button></a>
                <a href="{{ route('rbm.pap-import.show', $import) }}"><x-secondary-button type="button">{{ __('Détail') }}</x-secondary-button></a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @php
                $bloquants = collect($import->rapport ?? [])->where('severite', 'bloquant');
                $avert = collect($import->rapport ?? [])->where('severite', 'avertissement');
            @endphp

            <div class="rounded-lg p-4 text-sm
                {{ $import->statut === 'valide' ? 'bg-green-50 border border-green-200 text-green-800' : '' }}
                {{ $import->statut === 'simule' ? 'bg-blue-50 border border-blue-200 text-blue-800' : '' }}
                {{ $import->statut === 'echoue' ? 'bg-red-50 border border-red-200 text-red-800' : '' }}">
                @if ($import->statut === 'echoue')
                    {{ __('Import refusé : :n anomalie(s) bloquante(s). Aucune donnée n\'a été modifiée.', ['n' => $bloquants->count()]) }}
                @elseif ($import->statut === 'simule')
                    {{ __('Simulation réalisée. Aucune donnée n\'a été écrite. Vérifiez la prévisualisation puis lancez l\'import.') }}
                @else
                    {{ __('Importation effectuée avec succès en mode « :mode ».', ['mode' => str_replace('_', ' ', $import->mode)]) }}
                @endif
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ([
                    ['Piliers', $import->nb_piliers], ['Axes', $import->nb_axes],
                    ['Produits', $import->nb_produits], ['Sous-produits', $import->nb_sous_produits],
                    ['Lignes', $import->nb_lignes], ['Créations', $import->nb_creations],
                    ['Mises à jour', $import->nb_mises_a_jour], ['Suppressions', $import->nb_suppressions],
                    ['Doublons', $import->nb_doublons], ['Avertissements', $import->nb_avertissements],
                    ['Erreurs', $import->nb_erreurs],
                ] as [$label, $val])
                    <div class="bg-white shadow-sm rounded-lg p-4">
                        <div class="text-2xl font-semibold text-gray-800">{{ number_format($val, 0, ',', ' ') }}</div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide">{{ __($label) }}</div>
                    </div>
                @endforeach
                <div class="bg-ceeac-bright/10 shadow-sm rounded-lg p-4 col-span-2">
                    <div class="text-2xl font-semibold text-gray-900">{{ number_format($import->montant_total, 0, ',', ' ') }} FCFA</div>
                    <div class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Montant total PAP') }}</div>
                </div>
            </div>

            @if ($bloquants->isNotEmpty())
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-6 py-3 border-b border-gray-100 font-semibold text-red-700">{{ __('Anomalies bloquantes') }}</div>
                    @include('rbm.pap-import._issues', ['issues' => $bloquants])
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
