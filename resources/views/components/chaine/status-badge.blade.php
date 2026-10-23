@props([
    'module' => 'engagement',
    'statut' => null,
    'label' => null,
    'class' => null,
])

@php
    $presented = match ($module) {
        'besoin' => \App\Support\ChaineDepense\StatutPresenter::besoin($statut),
        'liquidation' => \App\Support\ChaineDepense\StatutPresenter::liquidation($statut),
        'ordonnancement' => \App\Support\ChaineDepense\StatutPresenter::ordonnancement($statut),
        'paiement' => \App\Support\ChaineDepense\StatutPresenter::paiement($statut),
        default => \App\Support\ChaineDepense\StatutPresenter::engagement($statut),
    };
    $text = $label ?? $presented['label'];
    $badgeClass = $class ?? $presented['class'];
@endphp

<span {{ $attributes->merge(['class' => $badgeClass]) }} role="status">
    <span class="sr-only">{{ __('Statut') }} : </span>{{ $text }}
</span>
