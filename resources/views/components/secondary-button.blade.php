<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-secondary normal-case tracking-normal']) }}>
    {{ $slot }}
</button>
