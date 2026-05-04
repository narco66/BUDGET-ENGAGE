<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-ceeac-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-ceeac-bright focus:bg-ceeac-bright active:bg-[#172554] focus:outline-none focus:ring-2 focus:ring-ceeac-bright focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
