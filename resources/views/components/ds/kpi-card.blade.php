@props([
    'label',
    'value' => null,
    'trend' => null,
    'icon' => null,
    'tone' => 'primary',
])

@php
$toneClasses = match ($tone) {
    'success' => ['icon' => 'bg-emerald-50 text-emerald-600', 'value' => 'text-emerald-600'],
    'warning' => ['icon' => 'bg-amber-50 text-amber-600', 'value' => 'text-amber-600'],
    'danger'  => ['icon' => 'bg-red-50 text-red-600', 'value' => 'text-red-600'],
    default   => ['icon' => 'bg-ceeac-light text-ceeac-bright', 'value' => 'text-ceeac-primary'],
};
@endphp

<div {{ $attributes->merge(['class' => 'kpi-card']) }}>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <div class="kpi-label">{{ $label }}</div>
            <div class="kpi-value mt-2 tabular-nums {{ $toneClasses['value'] }}">
                @if ($value !== null)
                    {{ $value }}
                @elseif (isset($valueSlot))
                    {{ $valueSlot }}
                @endif
            </div>
            @if ($trend !== null || isset($trendSlot))
                <div class="kpi-trend mt-2 bg-gray-100 text-gray-600">
                    @if ($trend !== null){{ $trend }}@else{{ $trendSlot }}@endif
                </div>
            @endif
        </div>
        @if ($icon)
            <div class="kpi-icon {{ $toneClasses['icon'] }}">{!! $icon !!}</div>
        @endif
    </div>
</div>
