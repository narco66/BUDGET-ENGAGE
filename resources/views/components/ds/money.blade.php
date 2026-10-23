@props([
    'value',
    'decimals' => 0,
    'currency' => 'FCFA',
    'compact' => false,
    'showCurrency' => true,
])

<span {{ $attributes->merge(['class' => 'tabular-nums']) }}>
    {{ $compact
        ? \App\Support\MoneyFormatter::formatCompact($value)
        : \App\Support\MoneyFormatter::format($value, $decimals, $currency, $showCurrency) }}
</span>
