<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Virements de crédits') }}</h2>
            <a href="{{ route('budget.nomenclature.synthese') }}"><x-secondary-button type="button">{{ __('Synthèse budgétaire') }}</x-secondary-button></a>
        </div>
    </x-slot>

    @php
        $fmt = fn ($v) => number_format((float) $v, 0, ',', ' ');
        $dispo = fn ($l) => ((float) $l->montant_revise > 0 ? (float) $l->montant_revise : (float) $l->montant_vote) - (float) $l->montant_engage;
        $lignesJs = $lignes->map(fn ($l) => ['id' => $l->id, 'label' => $l->code.' — '.\Illuminate\Support\Str::limit($l->libelle, 45), 'dispo' => $dispo($l)])->values()->all();
    @endphp

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
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

            @if (! $exercice || $lignes->isEmpty())
                <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg text-sm">
                    {{ __('Aucune ligne budgétaire pour cet exercice.') }}
                </div>
            @else
                <form method="POST" action="{{ route('budget.virements.store') }}" class="bg-white p-6 shadow-sm rounded-lg space-y-4"
                      x-data="{ lignes: @js($lignesJs), src: '', montant: '',
                                dispoSrc() { const l = this.lignes.find(x => String(x.id) === String(this.src)); return l ? l.dispo : null; },
                                fmt(v){ return new Intl.NumberFormat('fr-FR').format(Math.round(v||0)); } }">
                    @csrf
                    <input type="hidden" name="exercice_budgetaire_id" value="{{ $exercice->id }}">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="ligne_source_id" :value="__('Ligne source (débit)')" />
                            <select id="ligne_source_id" name="ligne_source_id" x-model="src" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">— {{ __('Choisir') }} —</option>
                                <template x-for="l in lignes" :key="l.id">
                                    <option :value="l.id" x-text="l.label + ' (dispo: ' + fmt(l.dispo) + ')'"></option>
                                </template>
                            </select>
                            <p class="text-xs text-gray-500 mt-1" x-show="dispoSrc() !== null">{{ __('Disponible source') }} : <span x-text="fmt(dispoSrc())"></span> FCFA</p>
                            <x-input-error class="mt-2" :messages="$errors->get('ligne_source_id')" />
                        </div>
                        <div>
                            <x-input-label for="ligne_destination_id" :value="__('Ligne destination (crédit)')" />
                            <select id="ligne_destination_id" name="ligne_destination_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">— {{ __('Choisir') }} —</option>
                                <template x-for="l in lignes" :key="'d'+l.id">
                                    <option :value="l.id" x-text="l.label"></option>
                                </template>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('ligne_destination_id')" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="montant" :value="__('Montant (FCFA)')" />
                            <x-text-input id="montant" name="montant" type="number" step="0.01" min="0.01" class="block mt-1 w-full tabular-nums" x-model="montant" required />
                            <p class="text-xs text-red-600 mt-1" x-show="dispoSrc() !== null && parseFloat(montant || 0) > dispoSrc()">{{ __('Dépasse le disponible de la source') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('montant')" />
                        </div>
                        <div>
                            <x-input-label for="motif" :value="__('Motif')" />
                            <x-text-input id="motif" name="motif" type="text" class="block mt-1 w-full" maxlength="1000" required />
                            <x-input-error class="mt-2" :messages="$errors->get('motif')" />
                        </div>
                    </div>
                    <x-primary-button>{{ __('Proposer le virement') }}</x-primary-button>
                </form>

                @if ($enAttente->isNotEmpty())
                    <div class="bg-white shadow-sm rounded-lg overflow-hidden border-l-4 border-amber-400">
                        <div class="px-5 py-3 border-b border-gray-100 text-sm font-semibold text-gray-700">{{ __('Virements en attente d\'approbation') }} ({{ $enAttente->count() }})</div>
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-gray-500">
                                <tr>
                                    <th class="px-4 py-2 text-left">#</th>
                                    <th class="px-4 py-2 text-left">{{ __('Source') }}</th>
                                    <th class="px-4 py-2 text-left">{{ __('Destination') }}</th>
                                    <th class="px-4 py-2 text-right">{{ __('Montant') }}</th>
                                    <th class="px-4 py-2 text-left">{{ __('Motif') }}</th>
                                    <th class="px-4 py-2 text-left">{{ __('Proposé par') }}</th>
                                    @if ($peutApprouver)<th class="px-4 py-2 text-right">{{ __('Décision') }}</th>@endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($enAttente as $v)
                                    <tr>
                                        <td class="px-4 py-2">#{{ $v->id }}</td>
                                        <td class="px-4 py-2"><span class="font-mono text-xs text-gray-500">{{ $v->source?->code }}</span></td>
                                        <td class="px-4 py-2"><span class="font-mono text-xs text-gray-500">{{ $v->destination?->code }}</span></td>
                                        <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($v->montant) }}</td>
                                        <td class="px-4 py-2 text-gray-600">{{ \Illuminate\Support\Str::limit($v->motif, 40) }}</td>
                                        <td class="px-4 py-2">{{ $v->user?->name }}</td>
                                        @if ($peutApprouver)
                                            <td class="px-4 py-2">
                                                <div class="flex items-center justify-end gap-2">
                                                    <form method="POST" action="{{ route('budget.virements.approve', $v) }}" onsubmit="return confirm('{{ __('Approuver et appliquer ce virement ?') }}');">
                                                        @csrf
                                                        <button class="px-2 py-1 rounded bg-emerald-600 text-white text-xs">{{ __('Approuver') }}</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('budget.virements.reject', $v) }}" onsubmit="return this.motif.value.trim() !== '' || (alert('{{ __('Motif requis') }}'), false);">
                                                        @csrf
                                                        <input type="text" name="motif" placeholder="{{ __('Motif rejet') }}" class="border-gray-300 rounded text-xs py-1" required>
                                                        <button class="px-2 py-1 rounded bg-red-600 text-white text-xs">{{ __('Rejeter') }}</button>
                                                    </form>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 text-sm font-semibold text-gray-700">{{ __('Historique des virements traités') }}</div>
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="px-4 py-2 text-left">{{ __('Date') }}</th>
                                <th class="px-4 py-2 text-left">{{ __('Statut') }}</th>
                                <th class="px-4 py-2 text-left">{{ __('Source') }}</th>
                                <th class="px-4 py-2 text-left">{{ __('Destination') }}</th>
                                <th class="px-4 py-2 text-right">{{ __('Montant') }}</th>
                                <th class="px-4 py-2 text-left">{{ __('Motif') }}</th>
                                <th class="px-4 py-2 text-left">{{ __('Proposé / Approuvé') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @php($vbadge = ['approuve' => 'bg-emerald-100 text-emerald-800', 'valide' => 'bg-emerald-100 text-emerald-800', 'rejete' => 'bg-red-100 text-red-800'])
                            @forelse ($virements as $v)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 whitespace-nowrap">{{ ($v->decision_at ?? $v->created_at)?->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-2"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $vbadge[$v->statut] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($v->statut) }}</span></td>
                                    <td class="px-4 py-2"><span class="font-mono text-xs text-gray-500">{{ $v->source?->code }}</span></td>
                                    <td class="px-4 py-2"><span class="font-mono text-xs text-gray-500">{{ $v->destination?->code }}</span></td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ $fmt($v->montant) }}</td>
                                    <td class="px-4 py-2 text-gray-600">{{ \Illuminate\Support\Str::limit($v->motif, 45) }}</td>
                                    <td class="px-4 py-2 text-xs text-gray-500">{{ $v->user?->name }} @if ($v->approbateur)→ {{ $v->approbateur->name }}@endif</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-4 py-6 text-center text-gray-400">{{ __('Aucun virement traité.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
