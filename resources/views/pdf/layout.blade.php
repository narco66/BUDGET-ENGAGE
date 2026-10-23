<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Document') — BUDGET-ENGAGE</title>
    @include('pdf.partials.styles')
    @stack('pdf-styles')
</head>
<body>
    @php
        $pdfTitle = trim($__env->yieldContent('title'));
        $pdfSubtitle = trim($__env->yieldContent('subtitle'));
        $pdfReference = $pdfReference ?? trim($__env->yieldContent('reference'));
        $pdfExercice = $pdfExercice ?? null;
        $pdfGeneratedAt = $pdfGeneratedAt ?? now()->format('d/m/Y H:i');
        $pdfGeneratedBy = $pdfGeneratedBy ?? auth()->user()?->name ?? 'Système';
        $pdfConfidentialite = $pdfConfidentialite ?? 'Usage interne';
        $pdfVersion = $pdfVersion ?? '1.0';
    @endphp

    <div class="pdf-entete">
        <h1>@yield('title')</h1>
        @hasSection('subtitle')
            <p class="org">@yield('subtitle')</p>
        @else
            <p class="org">Commission de la CEEAC — BUDGET-ENGAGE — Chaîne de la dépense</p>
        @endif
        @if ($pdfReference)
            <p class="meta-line">Réf. {{ $pdfReference }}@if($pdfExercice) · Exercice {{ $pdfExercice }}@endif</p>
        @endif
    </div>

    <div class="pdf-meta-bar">
        <span>Édité le {{ $pdfGeneratedAt }}</span>
        <span>Par {{ $pdfGeneratedBy }}</span>
        <span>Version {{ $pdfVersion }}</span>
        <span>{{ $pdfConfidentialite }}</span>
    </div>

    @yield('content')

    @hasSection('signatures')
        @yield('signatures')
    @else
        <table class="pdf-signatures">
            <tr>
                <td>L'Ordonnateur</td>
                <td>Le Contrôleur Budgétaire</td>
                <td>Le Responsable Financier</td>
            </tr>
        </table>
    @endif

    <div class="pdf-pied">
        <span class="pdf-pied-left">BUDGET-ENGAGE · {{ $pdfReference ?? 'Document' }}</span>
        <span class="pdf-pied-right">Page {PAGE_NUM} / {PAGE_COUNT}</span>
        <div class="pdf-clear"></div>
    </div>
</body>
</html>
