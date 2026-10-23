@php $peutRejeter = auth()->user()->can('signalerRejet', $paiement); @endphp

@if ($paiement->rejets->isNotEmpty())
    <table class="data-table">
        <thead><tr><th>{{ __('Motif') }}</th><th>{{ __('Description') }}</th><th>{{ __('Date') }}</th><th>{{ __('Référence') }}</th></tr></thead>
        <tbody>
            @foreach ($paiement->rejets as $r)
                <tr>
                    <td><span class="badge badge-danger">{{ str_replace('_', ' ', $r->motif) }}</span></td>
                    <td class="text-sm">{{ $r->description ?? '—' }}</td>
                    <td class="text-sm">{{ optional($r->date_rejet)->format('d/m/Y') }}</td>
                    <td class="font-mono text-xs">{{ $r->reference_rejet ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p class="text-gray-400 text-sm">{{ __('Aucun rejet bancaire.') }}</p>
@endif

@if ($peutRejeter)
    <details class="rounded-lg border border-gray-200 p-4">
        <summary class="cursor-pointer text-sm font-medium text-red-600">{{ __('Signaler un rejet bancaire') }}</summary>
        <form method="POST" action="{{ route('paiements.rejet.store', $paiement) }}" class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">@csrf
            <div>
                <x-input-label for="motif" :value="__('Motif')" />
                <select id="motif" name="motif" class="form-select mt-1" required>
                    @foreach (['rejet_bancaire' => 'Rejet bancaire', 'compte_invalide' => 'Compte invalide', 'insuffisance_provision' => 'Insuffisance de provision', 'erreur_devise' => 'Erreur de devise', 'doublon' => 'Doublon', 'annulation' => 'Annulation'] as $v => $lib)
                        <option value="{{ $v }}">{{ $lib }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="date_rejet" :value="__('Date du rejet')" />
                <x-text-input id="date_rejet" type="date" name="date_rejet" class="block mt-1 w-full" :value="now()->format('Y-m-d')" />
            </div>
            <div>
                <x-input-label for="reference_rejet" :value="__('Référence')" />
                <x-text-input id="reference_rejet" name="reference_rejet" class="block mt-1 w-full" maxlength="100" />
            </div>
            <div class="md:col-span-2">
                <x-input-label for="description" :value="__('Description')" />
                <textarea id="description" name="description" rows="2" class="form-textarea mt-1 block w-full"></textarea>
            </div>
            <div class="md:col-span-2"><button class="btn btn-danger btn-sm">{{ __('Enregistrer le rejet') }}</button></div>
        </form>
    </details>
@endif
