@props([
    'type' => 'info',
    'title' => null,
    'impact' => null,
    'action' => null,
])

@php
    $alertType = match ($type) {
        'success', 'ok' => 'success',
        'warning', 'warn' => 'warning',
        'danger', 'error', 'block' => 'danger',
        default => 'info',
    };
@endphp

<x-ds.alert :type="$alertType" {{ $attributes }}>
    <div class="space-y-1">
        @if ($title)<p class="font-semibold">{{ $title }}</p>@endif
        <div>{{ $slot }}</div>
        @if ($impact)<p class="text-xs opacity-90">{{ __('Impact') }} : {{ $impact }}</p>@endif
        @if ($action)<p class="text-xs font-medium">{{ __('Action attendue') }} : {{ $action }}</p>@endif
    </div>
</x-ds.alert>
