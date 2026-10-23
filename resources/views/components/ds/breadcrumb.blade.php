@props(['items' => []])

@if (count($items) > 0)
<nav aria-label="{{ __('Fil d\'Ariane') }}" {{ $attributes->merge(['class' => 'page-breadcrumb']) }}>
    @foreach ($items as $index => $item)
        @if ($index > 0)
            <svg class="w-3 h-3 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        @endif
        @if (! empty($item['url']) && $index < count($items) - 1)
            <a href="{{ $item['url'] }}" class="hover:text-ceeac-bright transition-colors">{{ $item['label'] }}</a>
        @else
            <span @class(['font-semibold text-ceeac-ink' => $index === count($items) - 1])>{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
@endif
