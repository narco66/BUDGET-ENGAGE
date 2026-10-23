@props([
    'label',
    'value' => null,
    'hint' => null,
    'tone' => 'default',
])

@php
    $toneClass = match ($tone) {
        'primary' => 'text-ceeac-primary',
        'success' => 'text-emerald-600',
        'warning' => 'text-amber-700',
        'danger' => 'text-ceeac-danger',
        default => 'text-ceeac-ink',
    };
@endphp

<div {{ $attributes->merge(['class' => 'financial-metric']) }}>
    <div class="financial-metric-label">{{ $label }}</div>
    <div class="financial-metric-value tabular-nums {{ $toneClass }}">
        @if ($value !== null){{ $value }}@else{{ $slot }}@endif
    </div>
    @if ($hint)
        <div class="financial-metric-hint">{{ $hint }}</div>
    @endif
</div>
