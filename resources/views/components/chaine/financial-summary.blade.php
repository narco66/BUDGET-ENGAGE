@props([
    'title' => null,
    'columns' => 4,
])

<div {{ $attributes->merge(['class' => 'financial-summary']) }}>
    @if ($title)
        <div class="financial-summary-title">{{ $title }}</div>
    @endif
    <div @class([
        'financial-summary-grid',
        'grid-cols-2 md:grid-cols-3 xl:grid-cols-'.$columns => true,
    ])>
        {{ $slot }}
    </div>
</div>
