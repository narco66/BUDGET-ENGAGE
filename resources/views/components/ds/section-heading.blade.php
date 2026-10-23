@props(['eyebrow' => null, 'title', 'subtitle' => null])

<div {{ $attributes }}>
    @if ($eyebrow)
        <span class="inline-block text-xs font-bold uppercase tracking-widest text-ceeac-bright mb-3">{{ $eyebrow }}</span>
    @endif
    <h2 class="section-title">{{ $title }}</h2>
    @if ($subtitle)
        <p class="section-subtitle">{{ $subtitle }}</p>
    @endif
</div>
