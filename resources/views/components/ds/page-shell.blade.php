@props(['narrow' => false])

<div {{ $attributes->merge(['class' => $narrow ? 'page-shell-narrow' : 'page-shell']) }}>
    {{ $slot }}
</div>
