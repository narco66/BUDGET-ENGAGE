@props([
    'title',
    'reference' => null,
    'description' => null,
    'breadcrumb' => null,
    'statut' => null,
    'statutModule' => 'engagement',
])

<x-ds.page-header :title="$title" :description="$description" :breadcrumb="$breadcrumb">
    <x-slot:actions>
        @if ($statut)
            <x-chaine.status-badge :module="$statutModule" :statut="$statut" />
        @endif
        @isset($actions){{ $actions }}@endisset
    </x-slot:actions>
</x-ds.page-header>
