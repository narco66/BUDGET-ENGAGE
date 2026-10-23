@php
    use App\Enums\StatutBesoin;
    $retourCount = (int) ($parStatut[StatutBesoin::Retourne->value] ?? 0);
    $tauxCompletude = $kpis['total'] > 0 ? round(($kpis['valides'] + $kpis['convertis']) / $kpis['total'] * 100) : 0;
    $maxDep = max(1, (float) ($parDepartement->max('montant') ?? 0));
@endphp
<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Expression de besoins')"
            :description="__('Tableau de bord — recensement, validation et conversion des besoins.')"
            :breadcrumb="[
                ['label' => __('Expression de besoins')],
                ['label' => __('Tableau de bord')],
            ]"
        >
            <x-slot:actions>
                @can('viewAny', App\Models\ExpressionBesoin::class)
                    <a href="{{ route('expression-besoins.besoins.index') }}"><x-secondary-button type="button">{{ __('Liste des besoins') }}</x-secondary-button></a>
                @endcan
                @can('create', App\Models\ExpressionBesoin::class)
                    <a href="{{ route('expression-besoins.besoins.create') }}"><x-primary-button type="button">{{ __('Nouveau besoin') }}</x-primary-button></a>
                @endcan
            </x-slot:actions>
        </x-chaine.dossier-header>
    </x-slot>

    <x-ds.page-shell class="space-y-6">
        {{-- KPIs principaux --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <x-ds.kpi-card :label="__('Total besoins')" :value="(string) $kpis['total']" tone="primary" />
            <x-ds.kpi-card :label="__('Montant total estimé')" tone="success">
                <x-slot:value><x-ds.money :value="$kpis['montant_demande']" :decimals="0" :show-currency="false" /></x-slot:value>
            </x-ds.kpi-card>
            <x-ds.kpi-card :label="__('En cours de validation')" :value="(string) $kpis['en_validation']" tone="warning" />
            <x-ds.kpi-card :label="__('Retour pour correction')" :value="(string) $retourCount" tone="danger" />
            <x-ds.kpi-card :label="__('Taux de complétude')" :value="$tauxCompletude.' %'" tone="primary" />
        </div>

        {{-- KPIs secondaires --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <x-ds.kpi-card :label="__('Besoins PAP')" :value="(string) $kpis['pap']" />
            <x-ds.kpi-card :label="__('Hors PAP')" :value="(string) $kpis['hors_pap']" />
            <x-ds.kpi-card :label="__('Validés')" :value="(string) $kpis['valides']" tone="success" />
            <x-ds.kpi-card :label="__('Convertis')" :value="(string) $kpis['convertis']" tone="success" />
            <x-ds.kpi-card :label="__('Montant validé')" tone="success">
                <x-slot:value><x-ds.money :value="$kpis['montant_valide']" :decimals="0" :show-currency="false" /></x-slot:value>
            </x-ds.kpi-card>
        </div>

        <div class="grid lg:grid-cols-2 gap-4">
            {{-- Répartition par département (barres proportionnelles) --}}
            <x-ds.card :title="__('Répartition par département')" :padding="true">
                @forelse ($parDepartement as $row)
                    <div class="py-1.5 border-b border-gray-50 last:border-0">
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-gray-700">{{ $row->libelle }} <span class="text-gray-400">({{ $row->total }})</span></span>
                            <span class="font-semibold tabular-nums"><x-ds.money :value="$row->montant" :decimals="0" :show-currency="false" /></span>
                        </div>
                        <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full bg-ceeac-primary" style="width: {{ max(2, round((float) $row->montant / $maxDep * 100)) }}%"></div>
                        </div>
                    </div>
                @empty
                    <x-ds.empty-state :title="__('Aucune donnée.')" class="border-0 shadow-none py-6" />
                @endforelse
            </x-ds.card>

            {{-- Répartition par statut --}}
            <x-ds.card :title="__('Répartition par statut')" :padding="true">
                @forelse ($parStatut as $statut => $total)
                    @php($enum = StatutBesoin::tryFrom($statut))
                    <div class="flex items-center justify-between py-1.5 text-sm border-b border-gray-50 last:border-0">
                        <span class="flex items-center gap-2">
                            @if ($enum)<x-chaine.status-badge module="besoin" :statut="$enum" />@else {{ $statut }} @endif
                        </span>
                        <span class="font-semibold tabular-nums">{{ $total }}</span>
                    </div>
                @empty
                    <x-ds.empty-state :title="__('Aucun besoin enregistré.')" class="border-0 shadow-none py-6" />
                @endforelse
            </x-ds.card>
        </div>

        <div class="grid lg:grid-cols-3 gap-4">
            <x-ds.card :title="__('Par ligne budgétaire')" :padding="true">
                @forelse ($parLigne as $row)
                    <div class="flex items-center justify-between py-1.5 text-sm border-b border-gray-50 last:border-0">
                        <span class="text-gray-700 font-mono text-xs">{{ $row->code }}</span>
                        <span class="font-semibold tabular-nums"><x-ds.money :value="$row->montant" :decimals="0" :show-currency="false" /></span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-4 text-center">—</p>
                @endforelse
            </x-ds.card>
            <x-ds.card :title="__('Par source de financement')" :padding="true">
                @forelse ($parSource as $row)
                    <div class="flex items-center justify-between py-1.5 text-sm border-b border-gray-50 last:border-0">
                        <span class="text-gray-700">{{ $row->libelle }}</span>
                        <span class="font-semibold tabular-nums"><x-ds.money :value="$row->montant" :decimals="0" :show-currency="false" /></span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 py-4 text-center">—</p>
                @endforelse
            </x-ds.card>
            <x-ds.card :title="__('Montants')" :padding="true">
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between"><span class="text-gray-500">{{ __('Montant demandé') }}</span><span class="font-semibold text-ceeac-primary"><x-ds.money :value="$kpis['montant_demande']" :decimals="0" :show-currency="false" /></span></div>
                    <div class="flex items-center justify-between"><span class="text-gray-500">{{ __('Montant validé') }}</span><span class="font-semibold text-emerald-700"><x-ds.money :value="$kpis['montant_valide']" :decimals="0" :show-currency="false" /></span></div>
                    <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full rounded-full bg-emerald-500" style="width: {{ $kpis['montant_demande'] > 0 ? round($kpis['montant_valide'] / $kpis['montant_demande'] * 100) : 0 }}%"></div>
                    </div>
                </div>
            </x-ds.card>
        </div>

        {{-- Derniers besoins --}}
        <x-ds.card :title="__('Derniers besoins')" :padding="false">
            <div class="overflow-x-auto">
                @if ($derniers->isEmpty())
                    <x-ds.empty-state :title="__('Aucun besoin.')" class="border-0 shadow-none" />
                @else
                    <table class="data-table">
                        <thead><tr><th>{{ __('Référence') }}</th><th>{{ __('Objet') }}</th><th>{{ __('Département') }}</th><th>{{ __('Statut') }}</th><th class="text-right">{{ __('Montant estimé') }}</th></tr></thead>
                        <tbody>
                            @foreach ($derniers as $besoin)
                                <tr>
                                    <td class="font-mono text-xs"><a href="{{ route('expression-besoins.besoins.show', $besoin) }}" class="text-ceeac-bright hover:underline">{{ $besoin->reference }}</a></td>
                                    <td class="font-medium">{{ \Illuminate\Support\Str::limit($besoin->objet, 40) }}</td>
                                    <td class="text-sm">{{ $besoin->departement?->libelle ?? '—' }}</td>
                                    <td><x-chaine.status-badge module="besoin" :statut="$besoin->statut" /></td>
                                    <td class="text-right tabular-nums"><x-ds.money :value="$besoin->montant_total" :decimals="0" :show-currency="false" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
