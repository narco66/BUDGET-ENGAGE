@props(['type' => 'info'])

@php
$class = match ($type) {
    'success' => 'alert-success',
    'warning' => 'alert-warning',
    'danger', 'error' => 'alert-danger',
    default => 'alert-info',
};
@endphp

<div {{ $attributes->merge(['class' => $class]) }} role="alert">
    {{ $slot }}
</div>
