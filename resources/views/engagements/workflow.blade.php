<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <a href="{{ route('engagements.index') }}" class="hover:text-ceeac-primary">Engagements</a>
                    <span class="text-gray-300">/</span>
                    <a href="{{ route('engagements.show', $engagement) }}" class="hover:text-ceeac-primary">{{ $engagement->numero }}</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">Workflow</span>
                </nav>
                <h2 class="page-title">Workflow de validation</h2>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        <div class="card">
            <div class="card-body">
                <div class="flex flex-wrap gap-3 items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Engagement</p>
                        <p class="font-semibold text-ceeac-primary">{{ $engagement->numero }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('engagements.show', $engagement) }}" class="btn btn-secondary btn-sm">Retour au détail</a>
                        @can('decideWorkflow', $engagement)
                            <span class="badge badge-warning">Action requise</span>
                        @else
                            <span class="badge badge-neutral">Lecture</span>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-4 gap-4">
            @php
                $stepsTotal = $engagement->validationsWorkflow->count();
                $stepsValidated = $engagement->validationsWorkflow->filter(fn ($s) => ($s->decision instanceof \BackedEnum ? $s->decision->value : $s->decision) === 'valide')->count();
                $stepsRejected = $engagement->validationsWorkflow->filter(fn ($s) => ($s->decision instanceof \BackedEnum ? $s->decision->value : $s->decision) === 'rejete')->count();
                $stepsPending = $engagement->validationsWorkflow->filter(fn ($s) => ($s->decision instanceof \BackedEnum ? $s->decision->value : $s->decision) === 'en_attente')->count();
            @endphp
            <div class="kpi-card text-sm">
                <div class="text-gray-500">Étapes totales</div>
                <div class="text-2xl font-semibold tabular-nums">{{ $stepsTotal }}</div>
            </div>
            <div class="kpi-card text-sm">
                <div class="text-gray-500">Validées</div>
                <div class="text-2xl font-semibold tabular-nums text-emerald-600">{{ $stepsValidated }}</div>
            </div>
            <div class="kpi-card text-sm">
                <div class="text-gray-500">En attente</div>
                <div class="text-2xl font-semibold tabular-nums text-amber-600">{{ $stepsPending }}</div>
            </div>
            <div class="kpi-card text-sm">
                <div class="text-gray-500">Rejets</div>
                <div class="text-2xl font-semibold tabular-nums text-red-600">{{ $stepsRejected }}</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="font-semibold text-gray-900">Imputations budgétaires</h3></div>
            <div class="card-body text-sm">
                @php($rows = $engagement->imputations->isNotEmpty() ? $engagement->imputations : collect([(object) ['ligneBudgetaire' => $engagement->ligneBudgetaire, 'montant_impute' => $engagement->montant, 'observation' => null]]))
                <div class="overflow-x-auto rounded-xl border border-gray-100">
                    <table class="data-table min-w-full">
                        <thead>
                            <tr>
                                <th>Ligne budgétaire</th>
                                <th class="text-right">Montant imputé</th>
                                <th>Observation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                <tr>
                                    <td>{{ $row->ligneBudgetaire?->code ?? '—' }} — {{ $row->ligneBudgetaire?->libelle ?? '' }}</td>
                                    <td class="text-right tabular-nums">{{ number_format((float) $row->montant_impute, 2, ',', ' ') }}</td>
                                    <td>{{ $row->observation ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body space-y-4">
                @forelse($engagement->validationsWorkflow as $step)
                    @php($decision = $step->decision instanceof \BackedEnum ? $step->decision->value : $step->decision)
                    @php($dotClass = match($decision) {
                        'valide' => 'bg-emerald-500 text-white',
                        'rejete' => 'bg-red-500 text-white',
                        'retourne' => 'bg-amber-500 text-white',
                        default => 'bg-gray-200 text-gray-600',
                    })
                    <div class="timeline-step">
                        <div class="timeline-dot {{ $dotClass }}">
                            <span class="text-xs font-bold">{{ $step->ordre }}</span>
                        </div>
                        <div class="flex-1 rounded-xl border border-gray-100 p-4 bg-white">
                            <div class="flex flex-wrap gap-2 justify-between">
                                <h3 class="font-semibold text-gray-900">{{ __('workflow.steps.'.$step->etape) }}</h3>
                                <span class="badge {{ $decision === 'valide' ? 'badge-success' : ($decision === 'rejete' ? 'badge-danger' : ($decision === 'retourne' ? 'badge-warning' : 'badge-neutral')) }}">
                                    {{ __('workflow.decisions.'.$decision) }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Validateur: {{ $step->validateur?->name ?? '—' }}
                                @if($step->date_decision)
                                    · {{ $step->date_decision->format('d/m/Y H:i') }}
                                @endif
                            </p>
                            @if($step->commentaire)
                                <p class="mt-3 text-sm text-gray-700 whitespace-pre-wrap">{{ $step->commentaire }}</p>
                            @endif
                            @if($decision === 'en_attente')
                                <div class="mt-3">
                                    <span class="badge badge-info">En attente de décision</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Aucune étape de validation enregistrée.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
