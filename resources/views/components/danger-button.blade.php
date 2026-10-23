<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-danger normal-case tracking-normal']) }}>
    {{ $slot }}
</button>
