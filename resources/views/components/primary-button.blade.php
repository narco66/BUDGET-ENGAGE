<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-primary normal-case tracking-normal']) }}>
    {{ $slot }}
</button>
