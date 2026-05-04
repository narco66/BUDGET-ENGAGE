@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-ceeac-bright focus:ring-ceeac-bright rounded-institutional shadow-sm']) }}>
