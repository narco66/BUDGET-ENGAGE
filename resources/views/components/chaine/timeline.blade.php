@props(['events' => []])

<div {{ $attributes->merge(['class' => 'chaine-timeline']) }}>
    @forelse ($events as $event)
        <div class="chaine-timeline-item">
            <div class="chaine-timeline-marker" aria-hidden="true"></div>
            <div class="chaine-timeline-body">
                <div class="flex flex-wrap items-baseline justify-between gap-2">
                    <span class="font-medium text-ceeac-ink">{{ $event['label'] ?? '' }}</span>
                    @if (! empty($event['date']))
                        <time class="text-xs text-gray-400">{{ $event['date'] }}</time>
                    @endif
                </div>
                @if (! empty($event['user']))
                    <p class="text-xs text-gray-500 mt-0.5">{{ $event['user'] }}</p>
                @endif
                @if (! empty($event['detail']))
                    <p class="text-sm text-gray-600 mt-1 whitespace-pre-wrap">{{ $event['detail'] }}</p>
                @endif
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-500">{{ __('Aucun événement enregistré.') }}</p>
    @endforelse
</div>
