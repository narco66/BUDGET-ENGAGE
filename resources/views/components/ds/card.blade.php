@props(['title' => null, 'padding' => true])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if ($title || isset($header))
        <div class="card-header">
            @if ($title)<h3 class="text-sm font-semibold text-ceeac-ink">{{ $title }}</h3>@endif
            @isset($header){{ $header }}@endisset
        </div>
    @endif
    <div @class(['card-body' => $padding])>{{ $slot }}</div>
    @isset($footer)<div class="card-footer">{{ $footer }}</div>@endisset
</div>
