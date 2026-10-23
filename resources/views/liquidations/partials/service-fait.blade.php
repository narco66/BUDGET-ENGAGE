@php $modifiable = auth()->user()->can('modifier', $liquidation); @endphp

@if ($sf)
    <div class="rounded-lg border border-gray-200 p-4 space-y-2 text-sm">
        <div class="flex items-center justify-between">
            <span class="font-medium">{{ __('Service fait constaté le') }} {{ optional($sf->date_constatation)->format('d/m/Y') }}</span>
            @if ($sf->valide)
                <span class="badge badge-success">{{ __('Validé') }}</span>
            @else
                <span class="badge badge-warning">{{ __('À valider') }}</span>
            @endif
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
            @foreach ([
                'Quantitative' => $sf->conformite_quantitative, 'Qualitative' => $sf->conformite_qualitative,
                'Technique' => $sf->conformite_technique, 'Contractuelle' => $sf->conformite_contractuelle,
            ] as $lib => $ok)
                <div class="flex items-center gap-1">
                    <span class="{{ $ok === false ? 'text-red-600' : ($ok ? 'text-green-600' : 'text-gray-300') }}">●</span>
                    <span class="text-gray-500">{{ $lib }}</span>
                </div>
            @endforeach
        </div>
        @if ($sf->reserves)
            <div class="text-amber-700 bg-amber-50 rounded p-2">
                <span class="font-medium">{{ __('Réserves') }} :</span> {{ $sf->reserves }}
                @if ($sf->reserves_levees)<span class="badge badge-success ml-2">{{ __('Levées') }}</span>@endif
            </div>
        @endif
        @if ($sf->responsable)<div class="text-gray-400">{{ __('Responsable') }} : {{ $sf->responsable->name }}</div>@endif

        @if ($modifiable)
            <div class="flex flex-wrap gap-2 pt-2">
                @unless ($sf->valide)
                    <form method="POST" action="{{ route('liquidations.service-fait.valider', $liquidation) }}">@csrf
                        <button class="btn btn-primary btn-sm">{{ __('Valider le service fait') }}</button>
                    </form>
                @endunless
                @if ($sf->reserves && ! $sf->reserves_levees)
                    <form method="POST" action="{{ route('liquidations.service-fait.lever-reserves', $liquidation) }}">@csrf
                        <button class="btn btn-secondary btn-sm">{{ __('Lever les réserves') }}</button>
                    </form>
                @endif
            </div>
        @endif
    </div>
@else
    <p class="text-gray-400 text-sm">{{ __('Aucun service fait constaté.') }}</p>
@endif

@if ($modifiable)
    <details class="rounded-lg border border-gray-200 p-4">
        <summary class="cursor-pointer text-sm font-medium text-ceeac-primary">{{ $sf ? __('Reconstater le service fait') : __('Constater le service fait') }}</summary>
        <form method="POST" action="{{ route('liquidations.service-fait.store', $liquidation) }}" class="mt-3 space-y-3">@csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <x-input-label for="date_constatation" :value="__('Date de constatation')" />
                    <x-text-input id="date_constatation" type="date" name="date_constatation" class="block mt-1 w-full" :value="now()->format('Y-m-d')" required />
                </div>
                <div>
                    <x-input-label for="commission" :value="__('Commission / Responsable')" />
                    <x-text-input id="commission" name="commission" class="block mt-1 w-full" maxlength="255" />
                </div>
            </div>
            <div class="flex flex-wrap gap-4 text-sm">
                @foreach (['conformite_quantitative' => 'Quantitative', 'conformite_qualitative' => 'Qualitative', 'conformite_technique' => 'Technique', 'conformite_contractuelle' => 'Contractuelle'] as $name => $lib)
                    <label class="inline-flex items-center gap-1"><input type="checkbox" name="{{ $name }}" value="1" class="form-checkbox"> {{ $lib }}</label>
                @endforeach
            </div>
            <div>
                <x-input-label for="reserves" :value="__('Réserves (le cas échéant)')" />
                <textarea id="reserves" name="reserves" rows="2" class="form-textarea mt-1 block w-full"></textarea>
            </div>
            <button class="btn btn-primary btn-sm">{{ __('Enregistrer le service fait') }}</button>
        </form>
    </details>

    <details class="rounded-lg border border-gray-200 p-4">
        <summary class="cursor-pointer text-sm font-medium text-ceeac-primary">{{ __('Ajouter une réception') }}</summary>
        <form method="POST" action="{{ route('liquidations.receptions.store', $liquidation) }}" class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">@csrf
            <div>
                <x-input-label for="type_reception" :value="__('Type')" />
                <select id="type_reception" name="type_reception" class="form-select mt-1" required>
                    @foreach (\App\Enums\TypeReceptionLiquidation::cases() as $t)
                        <option value="{{ $t->value }}">{{ $t->libelle() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="date_reception" :value="__('Date')" />
                <x-text-input id="date_reception" type="date" name="date_reception" class="block mt-1 w-full" :value="now()->format('Y-m-d')" required />
            </div>
            <div>
                <x-input-label for="numero_pv" :value="__('N° PV')" />
                <x-text-input id="numero_pv" name="numero_pv" class="block mt-1 w-full" maxlength="100" />
            </div>
            <div class="flex items-end">
                <label class="inline-flex items-center gap-1 text-sm"><input type="checkbox" name="conforme" value="1" class="form-checkbox"> {{ __('Conforme') }}</label>
            </div>
            <div class="md:col-span-2"><button class="btn btn-primary btn-sm">{{ __('Enregistrer la réception') }}</button></div>
        </form>
    </details>
@endif

@if ($liquidation->receptions->isNotEmpty())
    <table class="data-table">
        <thead><tr><th>{{ __('Type') }}</th><th>{{ __('Date') }}</th><th>{{ __('N° PV') }}</th><th>{{ __('Conforme') }}</th></tr></thead>
        <tbody>
            @foreach ($liquidation->receptions as $r)
                <tr>
                    <td>{{ $r->type_reception?->libelle() }}</td>
                    <td>{{ optional($r->date_reception)->format('d/m/Y') }}</td>
                    <td class="font-mono text-xs">{{ $r->numero_pv ?? '—' }}</td>
                    <td>{!! $r->conforme ? '<span class="badge badge-success">Oui</span>' : '<span class="badge badge-neutral">—</span>' !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
