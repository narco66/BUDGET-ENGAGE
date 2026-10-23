@props([
    'title',
    'description' => null,
    'breadcrumb' => null,
])

<div {{ $attributes->merge(['class' => 'page-header']) }}>
    <div class="min-w-0 flex-1">
        @if ($breadcrumb)
            <x-ds.breadcrumb :items="$breadcrumb" class="mb-2" />
        @endif
        <h1 class="page-title">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-sm text-ceeac-slate max-w-3xl">{{ $description }}</p>
        @endif
    </div>
    @if (isset($actions))
        <div class="flex flex-wrap items-center gap-2 shrink-0">{{ $actions }}</div>
    @endif
</div>
