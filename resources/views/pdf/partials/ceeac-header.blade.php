{{--
    En-tête institutionnel CEEAC réutilisable pour documents PDF auto-portants.
    Paramètres attendus (via @include) :
      - $docTitle    : string  — libellé du cartouche (obligatoire)
      - $docSubtitle : string  — sous-titre sous le nom de l'application (optionnel)
      - $metaRows    : array   — lignes du bloc métadonnées : [['k' => 'Label', 'v' => 'Valeur'], ...] (optionnel)
    Styles : voir pdf/partials/styles.blade.php (classes ceeac-*).
--}}
@php
    $logo = public_path('images/CEEAC-logo.jpg');
    $metaRows = $metaRows ?? [];
    $docSubtitle = $docSubtitle ?? 'Système intégré de gestion budgétaire';
@endphp
<table class="ceeac-head">
    <tr>
        <td class="logo">
            @if (file_exists($logo))
                <img src="{{ $logo }}" alt="CEEAC">
            @endif
        </td>
        <td>
            <div class="ceeac-org">
                COMMISSION DE LA CEEAC
                <small>Communauté Économique des États de l'Afrique Centrale</small>
                <small>Paix · Stabilité · Développement</small>
            </div>
        </td>
        <td class="ceeac-center">
            <div class="app">BUDGET-ENGAGE</div>
            <div class="sys">{{ $docSubtitle }}</div>
            <span class="ceeac-doctitle">{{ mb_strtoupper($docTitle) }}</span>
        </td>
        <td style="text-align:right;">
            @if (! empty($metaRows))
                <table class="ceeac-metabox">
                    @foreach ($metaRows as $row)
                        <tr>
                            <td class="k">{{ $row['k'] }}</td>
                            <td class="v">{!! $row['v'] !!}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </td>
    </tr>
</table>
