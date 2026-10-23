<x-app-layout>
    <x-slot name="header">
        <x-chaine.dossier-header
            :title="__('Archives des besoins')"
            :description="__('Expressions de besoins supprimées — consultation en lecture seule.')"
            :breadcrumb="[
                ['label' => __('Expression de besoins'), 'url' => route('expression-besoins.dashboard')],
                ['label' => __('Besoins'), 'url' => route('expression-besoins.besoins.index')],
                ['label' => __('Archives')],
            ]"
        >
            <x-slot:actions>
                <a href="{{ route('expression-besoins.besoins.index') }}" class="btn btn-ghost btn-sm">{{ __('Retour aux besoins') }}</a>
            </x-slot:actions>
        </x-chaine.dossier-header>
    </x-slot>

    <x-ds.page-shell class="space-y-5">
        <x-ds.alert type="info">
            {{ __('Cette vue recense les besoins archivés (supprimés). Les données sont conservées à des fins de traçabilité.') }}
        </x-ds.alert>

        {{-- Recherche --}}
        <x-ds.card>
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <x-input-label for="q" :value="__('Recherche')" />
                    <x-text-input id="q" name="q" class="block mt-1 w-full" :value="request('q')" placeholder="{{ __('Référence, objet…') }}" />
                </div>
                <x-primary-button type="submit">{{ __('Filtrer') }}</x-primary-button>
                @if (request('q'))
                    <a href="{{ route('expression-besoins.besoins.archives') }}" class="btn btn-ghost btn-sm">{{ __('Réinitialiser') }}</a>
                @endif
            </form>
        </x-ds.card>

        {{-- Tableau --}}
        <x-ds.card :padding="false">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Référence') }}</th>
                            <th>{{ __('Intitulé du besoin') }}</th>
                            <th>{{ __('Département') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th class="text-right">{{ __('Montant estimé') }}</th>
                            <th>{{ __('Statut') }}</th>
                            <th>{{ __('Archivé le') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($besoins as $besoin)
                            <tr>
                                <td class="font-mono">{{ $besoin->reference }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($besoin->objet, 48) }}</td>
                                <td class="text-sm">{{ $besoin->departement?->libelle ?? '—' }}</td>
                                <td><span class="badge {{ $besoin->type_besoin?->badge() }}">{{ $besoin->type_besoin?->libelle() }}</span></td>
                                <td class="text-right"><x-ds.money :value="$besoin->montant_total" :decimals="0" :show-currency="false" /></td>
                                <td><x-chaine.status-badge module="besoin" :statut="$besoin->statut" /></td>
                                <td class="text-sm whitespace-nowrap">{{ $besoin->deleted_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-0"><x-ds.empty-state :title="__('Aucun besoin archivé.')" :description="__('Les besoins supprimés apparaîtront ici.')" class="border-0 shadow-none" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($besoins->hasPages())
                <div class="card-footer">{{ $besoins->withQueryString()->links() }}</div>
            @endif
        </x-ds.card>
    </x-ds.page-shell>
</x-app-layout>
