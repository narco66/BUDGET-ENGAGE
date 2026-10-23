@props(['name', 'label' => null])

<div x-show="tab === @js($name)" x-cloak role="tabpanel" {{ $attributes->merge(['class' => 'chaine-tab-panel']) }}>
    {{ $slot }}
</div>
