@props([
    'compact' => false,
    'variant' => 'dark',
])

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
    <x-brand-mark :variant="$variant === 'light' ? 'light' : 'color'" class="{{ $compact ? 'h-9 w-9 shrink-0' : 'h-11 w-11 shrink-0' }}" />
    <div class="flex flex-col leading-tight">
        <span class="{{ $compact ? 'text-base sm:text-lg' : 'text-lg sm:text-xl' }} font-bold tracking-tight {{ $variant === 'light' ? 'text-white' : 'text-ceeac-primary' }}">
            BUDGET-ENGAGE
        </span>
        @unless($compact)
            <span class="text-[11px] sm:text-xs font-medium {{ $variant === 'light' ? 'text-ceeac-sky' : 'text-gray-500' }} max-w-[14rem] sm:max-w-none">
                {{ __('landing.slogan') }}
            </span>
        @endunless
    </div>
</div>
