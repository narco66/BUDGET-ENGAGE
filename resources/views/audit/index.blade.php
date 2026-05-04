@php
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-ceeac-primary leading-tight">{{ __('audit.title') }}</h2>
            @if($exercice)
                <p class="text-sm text-gray-500 mt-1">{{ __('audit.subtitle') }} — {{ __('Exercice') }} {{ $exercice->annee }}</p>
            @endif
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">

        {{-- Urgences --}}
        <section class="rounded-institutional bg-white border border-gray-100 shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-ceeac-muted/50">
                <h3 class="font-semibold text-ceeac-primary">{{ __('audit.section_urgences') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-gray-500 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3">{{ __('audit.col_numero') }}</th>
                            <th class="px-6 py-3">{{ __('audit.col_objet') }}</th>
                            <th class="px-6 py-3">{{ __('audit.col_montant') }}</th>
                            <th class="px-6 py-3">{{ __('audit.col_regularisation') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($urgences as $e)
                            <tr class="hover:bg-gray-50/80">
                                <td class="px-6 py-3 font-mono"><a href="{{ route('engagements.show', $e) }}" class="text-ceeac-bright hover:underline">{{ $e->numero }}</a></td>
                                <td class="px-6 py-3 max-w-md truncate text-gray-700">{{ $e->objet }}</td>
                                <td class="px-6 py-3 tabular-nums">{{ $fmt($e->montant) }}</td>
                                <td class="px-6 py-3 text-xs">{{ $e->statut_regularisation?->value ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">{{ __('audit.empty_urgences') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Risque --}}
        <section class="rounded-institutional bg-white border border-gray-100 shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-ceeac-muted/50 flex flex-wrap justify-between gap-2 items-center">
                <h3 class="font-semibold text-ceeac-primary">{{ __('audit.section_risk') }}</h3>
                <span class="text-xs text-gray-500">{{ __('audit.threshold_note', ['score' => $riskThreshold]) }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-gray-500 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3">{{ __('audit.col_numero') }}</th>
                            <th class="px-6 py-3">{{ __('audit.col_objet') }}</th>
                            <th class="px-6 py-3">{{ __('audit.col_montant') }}</th>
                            <th class="px-6 py-3">{{ __('audit.col_score') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($highRisk as $e)
                            <tr class="hover:bg-gray-50/80">
                                <td class="px-6 py-3 font-mono"><a href="{{ route('engagements.show', $e) }}" class="text-ceeac-bright hover:underline">{{ $e->numero }}</a></td>
                                <td class="px-6 py-3 max-w-md truncate text-gray-700">{{ $e->objet }}</td>
                                <td class="px-6 py-3 tabular-nums">{{ $fmt($e->montant) }}</td>
                                <td class="px-6 py-3"><span class="inline-flex px-2 py-0.5 rounded-md text-xs font-bold bg-ceeac-danger/15 text-ceeac-danger">{{ $e->score_risque }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">{{ __('audit.empty_risk') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Fractionnement (placeholder métier) --}}
        <section class="rounded-institutional border border-dashed border-gray-300 bg-gray-50 px-6 py-8">
            <h3 class="font-semibold text-gray-800">{{ __('audit.section_fractionnement') }}</h3>
            <p class="mt-2 text-sm text-gray-600">{{ __('audit.empty_fractionnement') }}</p>
        </section>

        {{-- Validation bloquée --}}
        <section class="rounded-institutional bg-white border border-gray-100 shadow-card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-ceeac-muted/50">
                <h3 class="font-semibold text-ceeac-primary">{{ __('audit.section_validation') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wide text-gray-500 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3">{{ __('audit.col_numero') }}</th>
                            <th class="px-6 py-3">{{ __('audit.col_objet') }}</th>
                            <th class="px-6 py-3">{{ __('audit.col_statut') }}</th>
                            <th class="px-6 py-3">{{ __('audit.col_montant') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($blockedValidation as $e)
                            <tr class="hover:bg-gray-50/80">
                                <td class="px-6 py-3 font-mono"><a href="{{ route('engagements.show', $e) }}" class="text-ceeac-bright hover:underline">{{ $e->numero }}</a></td>
                                <td class="px-6 py-3 max-w-md truncate text-gray-700">{{ $e->objet }}</td>
                                <td class="px-6 py-3">{{ __('En validation') }}</td>
                                <td class="px-6 py-3 tabular-nums">{{ $fmt($e->montant) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">{{ __('Aucun dossier en validation.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
