<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Historique des alertes de seuil') }}</h2>
            <a href="{{ route('budget.nomenclature.synthese') }}"><x-secondary-button type="button">{{ __('Synthèse budgétaire') }}</x-secondary-button></a>
        </div>
    </x-slot>

    @php
        $fmt = fn ($v) => number_format((float) $v, 0, ',', ' ');
        $badge = ['depassement' => 'bg-red-100 text-red-800', 'critique' => 'bg-orange-100 text-orange-800', 'avertissement' => 'bg-amber-100 text-amber-800'];
        $sbadge = ['traitee' => 'bg-blue-100 text-blue-800', 'resolue' => 'bg-emerald-100 text-emerald-800'];
    @endphp

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('status') }}</div>
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

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500">
                        <tr>
                            <th class="px-4 py-2 text-left">{{ __('Statut') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Niveau') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Ligne') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('Taux') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Traitée par') }}</th>
                            <th class="px-4 py-2 text-left">{{ __('Commentaire') }}</th>
                            <th class="px-4 py-2 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($alertes as $a)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $sbadge[$a->statut] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($a->statut) }}</span></td>
                                <td class="px-4 py-2"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge[$a->niveau] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($a->niveau) }}</span></td>
                                <td class="px-4 py-2"><span class="font-mono text-xs text-gray-500">{{ $a->code }}</span></td>
                                <td class="px-4 py-2 text-right tabular-nums">{{ $a->taux }}%</td>
                                <td class="px-4 py-2 text-xs">{{ $a->traitePar?->name ?? '—' }} <span class="text-gray-400">{{ $a->traitee_le?->format('d/m/Y H:i') }}</span></td>
                                <td class="px-4 py-2 text-gray-600">{{ \Illuminate\Support\Str::limit($a->commentaire_traitement, 50) ?: '—' }}</td>
                                <td class="px-4 py-2 text-right">
                                    <form method="POST" action="{{ route('budget.alertes.restaurer', $a) }}" onsubmit="return confirm('{{ __('Rouvrir cette alerte ?') }}');">
                                        @csrf
                                        <button class="px-2 py-1 rounded bg-gray-100 text-gray-700 text-xs hover:bg-gray-200">{{ __('Restaurer') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">{{ __('Aucune alerte traitée ou résolue.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $alertes->links() }}
        </div>
    </div>
</x-app-layout>
