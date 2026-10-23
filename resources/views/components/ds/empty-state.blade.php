@props([
    'title' => __('Aucune donnée'),
    'description' => null,
    'actionUrl' => null,
    'actionLabel' => null,
])

<div {{ $attributes->merge(['class' => 'card']) }}>
    <div class="card-body text-center py-12 px-6">
        <div class="mx-auto h-12 w-12 rounded-xl bg-ceeac-light flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-ceeac-bright" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
        </div>
        <h3 class="text-base font-semibold text-ceeac-ink">{{ $title }}</h3>
        @if ($description)
            <p class="mt-2 text-sm text-ceeac-slate max-w-md mx-auto">{{ $description }}</p>
        @endif
        @if ($actionUrl && $actionLabel)
            <a href="{{ $actionUrl }}" class="btn btn-primary btn-sm mt-5 inline-flex">{{ $actionLabel }}</a>
        @endif
        @if (isset($slot) && ! $slot->isEmpty())
            <div class="mt-4">{{ $slot }}</div>
        @endif
    </div>
</div>
