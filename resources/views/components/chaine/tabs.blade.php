@props([
    'tabs' => [],
    'active' => null,
])

<div {{ $attributes->merge(['class' => 'chaine-tabs']) }} x-data="{ tab: @js($active ?? array_key_first($tabs)) }">
    <div class="chaine-tabs-nav" role="tablist">
        @foreach ($tabs as $key => $label)
            <button type="button"
                role="tab"
                :aria-selected="tab === @js($key)"
                @click="tab = @js($key)"
                :class="tab === @js($key) ? 'chaine-tab chaine-tab--active' : 'chaine-tab'"
            >{{ $label }}</button>
        @endforeach
    </div>
    <div class="chaine-tabs-panels">
        {!! $slot !!}
    </div>
</div>
