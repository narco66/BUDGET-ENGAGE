@props(['statut'])

@php
    $map = [
        'valide' => ['bg-green-100', 'text-green-800', 'Validé'],
        'simule' => ['bg-blue-100', 'text-blue-800', 'Simulé'],
        'en_cours' => ['bg-gray-100', 'text-gray-700', 'En cours'],
        'echoue' => ['bg-red-100', 'text-red-800', 'Échoué'],
        'annule' => ['bg-amber-100', 'text-amber-800', 'Annulé'],
    ];
    [$bg, $text, $label] = $map[$statut] ?? ['bg-gray-100', 'text-gray-700', ucfirst($statut)];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {$bg} {$text}"]) }}>{{ $label }}</span>
