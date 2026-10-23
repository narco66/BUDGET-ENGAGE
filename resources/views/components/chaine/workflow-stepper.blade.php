@props(['steps' => []])

<nav aria-label="{{ __('Avancement de la chaîne de la dépense') }}" class="workflow-stepper">
    <ol class="workflow-stepper-list">
        @foreach ($steps as $index => $step)
            @php
                $status = $step['status'] ?? 'pending';
                $stateClass = match ($status) {
                    'done' => 'workflow-step--done',
                    'current' => 'workflow-step--current',
                    'error' => 'workflow-step--error',
                    'skipped' => 'workflow-step--skipped',
                    default => 'workflow-step--pending',
                };
            @endphp
            <li class="workflow-step {{ $stateClass }}">
                <div class="workflow-step-marker" aria-hidden="true">
                    @if ($status === 'done')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    @elseif ($status === 'error')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    @else
                        <span>{{ $index + 1 }}</span>
                    @endif
                </div>
                <div class="workflow-step-body min-w-0">
                    <div class="workflow-step-label">{{ $step['label'] ?? '' }}</div>
                    @if (! empty($step['reference']))
                        <div class="workflow-step-ref font-mono">
                            @if (! empty($step['url']))
                                <a href="{{ $step['url'] }}" class="text-ceeac-bright hover:underline">{{ $step['reference'] }}</a>
                            @else
                                {{ $step['reference'] }}
                            @endif
                        </div>
                    @endif
                    <div class="workflow-step-meta">
                        @if (! empty($step['statut_label']))
                            <span class="text-xs">{{ $step['statut_label'] }}</span>
                        @endif
                        @if (! empty($step['date']))
                            <span class="text-xs text-gray-400">· {{ $step['date'] }}</span>
                        @endif
                    </div>
                </div>
            </li>
        @endforeach
    </ol>
</nav>
