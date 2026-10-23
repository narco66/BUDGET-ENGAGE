<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">{{ __('Suivi physique') }}</h2>
            @can('create', App\Models\SuiviPhysiqueRealisation::class)
                <a href="{{ route('suivi-physique.create', $exercice ? ['exercice_budgetaire_id' => $exercice->id] : []) }}">
                    <x-primary-button type="button">{{ __('Nouvelle saisie') }}</x-primary-button>
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
                    <option value="">{{ __('Tous') }}</option>
                    @for ($t = 1; $t <= 4; $t++)
                        <option value="{{ $t }}" @selected($trimestre === $t)>T{{ $t }}</option>
                    @endfor
                </select>
            </div>
            <x-primary-button type="submit">{{ __('Filtrer') }}</x-primary-button>
        </form>

        @if ($exercice && $synthese)
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl shadow-sm p-5"><div class="text-xs text-gray-500 uppercase">{{ __('Activités suivies') }}</div><div class="text-2xl font-bold">{{ $synthese['activites_suivies'] }}</div></div>
                <div class="bg-white rounded-xl shadow-sm p-5"><div class="text-xs text-gray-500 uppercase">{{ __('Saisies validées') }}</div><div class="text-2xl font-bold">{{ $synthese['nb_realisations'] }}</div></div>
                <div class="bg-white rounded-xl shadow-sm p-5"><div class="text-xs text-gray-500 uppercase">{{ __('Avancement moyen') }}</div><div class="text-2xl font-bold">{{ $synthese['taux_avancement_moyen'] }} %</div></div>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr><th class="px-4 py-2 text-left">{{ __('Activité') }}</th><th class="px-4 py-2 text-right">{{ __('Avancement') }}</th><th class="px-4 py-2 text-right">{{ __('Saisies') }}</th></tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($parActivite as $row)
                            <tr>
                                <td class="px-4 py-2"><span class="font-mono">{{ $row['code'] }}</span> — {{ $row['libelle'] }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($row['taux_avancement'], 1) }} %</td>
                                <td class="px-4 py-2 text-right">{{ $row['nb_saisies'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">{{ __('Aucune réalisation validée.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
