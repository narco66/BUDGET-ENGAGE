<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Importation du Budget') }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('budget.nomenclature.template') }}"><x-secondary-button type="button">{{ __('Modèle Excel') }}</x-secondary-button></a>
                <a href="{{ route('budget.nomenclature.template.csv') }}"><x-secondary-button type="button">{{ __('Modèle CSV') }}</x-secondary-button></a>
                <a href="{{ route('budget.nomenclature.synthese') }}"><x-secondary-button type="button">{{ __('Synthèse') }}</x-secondary-button></a>
                <a href="{{ route('budget.nomenclature.history') }}"><x-secondary-button type="button">{{ __('Historique') }}</x-secondary-button></a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">{{ session('error') }}</div>
            @endif

            <div class="bg-white p-6 shadow-sm rounded-lg text-sm text-gray-600 space-y-2">
                <p>{{ __('Importez le Budget officiel (document intégré CEEAC 2026) ou un fichier Excel/CSV conforme au modèle.') }}</p>
                <p>{{ __('Nomenclature : Titre → Chapitre → Article → Paragraphe → Ligne. Sections recettes et dépenses. Montants en FCFA.') }}</p>
                <p class="text-gray-500">{{ __('Lignes budgétaires (nomenclature) actuellement en base : :n', ['n' => number_format($lignesActuelles, 0, ',', ' ')]) }}</p>
            </div>

            <form method="POST" action="{{ route('budget.nomenclature.store') }}" enctype="multipart/form-data"
                  class="bg-white p-6 shadow-sm rounded-lg space-y-5" x-data="{ source: 'document', mode: 'simulation' }">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="exercice_budgetaire_id" :value="__('Exercice budgétaire')" />
                        <select id="exercice_budgetaire_id" name="exercice_budgetaire_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                            @foreach ($exercices as $exercice)
                                <option value="{{ $exercice->id }}" @selected($exercice->annee === 2026)>{{ $exercice->annee }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('exercice_budgetaire_id')" />
                    </div>
                    <div>
                        <x-input-label for="version" :value="__('Version du budget')" />
                        <x-text-input id="version" name="version" type="text" class="block mt-1 w-full" value="1.0" />
                    </div>
                    <div>
                        <x-input-label for="mode" :value="__('Mode d\'importation')" />
                        <select id="mode" name="mode" x-model="mode" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                            @foreach ($modes as $m)
                                <option value="{{ $m }}">{{ ucfirst(str_replace('_', ' ', $m)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <x-input-label :value="__('Source des données')" />
                    <div class="mt-2 flex flex-col gap-2 text-sm text-gray-700">
                        <label class="inline-flex items-center gap-2"><input type="radio" name="source" value="document" x-model="source" class="text-ceeac-bright">{{ __('Document officiel Budget 2026 (intégré)') }}</label>
                        <label class="inline-flex items-center gap-2"><input type="radio" name="source" value="fichier" x-model="source" class="text-ceeac-bright">{{ __('Fichier Excel/CSV conforme au modèle') }}</label>
                    </div>
                </div>

                <div x-show="source === 'fichier'" x-cloak>
                    <x-input-label for="fichier" :value="__('Fichier (.xlsx, .xls, .csv) — max 10 Mo')" />
                    <input id="fichier" name="fichier" type="file" accept=".xlsx,.xls,.csv" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" />
                    <x-input-error class="mt-2" :messages="$errors->get('fichier')" />
                </div>

                <div x-show="mode === 'remplacement'" x-cloak class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <label class="inline-flex items-start gap-2 text-sm text-amber-900">
                        <input type="checkbox" name="confirmation" value="1" class="mt-1 text-amber-600">
                        <span>{{ __('Je confirme le remplacement des lignes budgétaires de cet exercice. Une sauvegarde restaurable sera créée avant suppression.') }}</span>
                    </label>
                    <x-input-error class="mt-2" :messages="$errors->get('confirmation')" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-secondary-button type="submit" formaction="{{ route('budget.nomenclature.simulate') }}">{{ __('Simuler (prévisualisation)') }}</x-secondary-button>
                    <x-primary-button>{{ __('Lancer l\'importation') }}</x-primary-button>
                </div>
            </form>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-700">{{ __('Derniers imports') }}</div>
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left">#</th>
                            <th class="px-4 py-2 text-left">{{ __('Exercice') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Mode') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Statut') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('Lignes') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Par') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($imports as $import)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2"><a href="{{ route('budget.nomenclature.show', $import) }}" class="text-ceeac-bright hover:underline">#{{ $import->id }}</a></td>
                                <td class="px-4 py-2">{{ $import->exerciceBudgetaire?->annee }}</td>
                                <td class="px-4 py-2">{{ ucfirst(str_replace('_', ' ', $import->mode)) }}</td>
                                <td class="px-4 py-2"><x-pap-statut-badge :statut="$import->statut" /></td>
                                <td class="px-4 py-2 text-right">{{ $import->nb_titres + $import->nb_chapitres + $import->nb_articles + $import->nb_paragraphes + $import->nb_lignes }}</td>
                                <td class="px-4 py-2">{{ $import->user?->name }}</td>
                                <td class="px-4 py-2">{{ $import->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">{{ __('Aucun import pour le moment.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
