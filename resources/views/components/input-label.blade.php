@props(['value'])

<label {{ $attributes->merge(['class' => 'form-label normal-case tracking-normal text-sm text-gray-700']) }}>
    {{ $value ?? $slot }}
</label>
