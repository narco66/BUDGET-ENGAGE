<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>État de programmation PAP {{ $exercice->annee }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        .muted { color: #6b7280; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #d1d5db; padding: 4px 6px; }
        th { background: #f3f4f6; text-align: left; font-size: 9px; text-transform: uppercase; }
        td.num { text-align: right; font-variant-numeric: tabular-nums; }
        tr.pilier td { background: #eef2ff; font-weight: bold; }
        tr.axe td { background: #f9fafb; }
        tr.produit td.lib { padding-left: 18px; color: #374151; }
        tr.axe td.lib { padding-left: 10px; }
        tfoot td { background: #f3f4f6; font-weight: bold; }
        .cards { width: 100%; margin-top: 8px; }
        .cards td { border: none; padding: 2px 8px 2px 0; }
        .label { color: #6b7280; font-size: 9px; }
        .val { font-size: 13px; font-weight: bold; }
    </style>
</head>
<body>
    @php $fmt = fn ($v) => number_format((float) $v, 0, ',', ' '); @endphp

    <h1>État de programmation du Plan Annuel de Performance — {{ $exercice->annee }}</h1>
    <div class="muted">Généré le {{ $generatedAt->format('d/m/Y H:i') }} · CEEAC / BUDGET-ENGAGE</div>

    <table class="cards">
        <tr>
            <td><div class="label">Montant PAP total</div><div class="val">{{ $fmt($synthese['montant_total']) }} XAF</div></td>
            <td><div class="label">Financement CEEAC</div><div class="val">{{ $fmt($synthese['montant_ceeac']) }}</div></td>
            <td><div class="label">Financement PTF</div><div class="val">{{ $fmt($synthese['montant_ptf']) }}</div></td>
            <td><div class="label">Budgété (lié)</div><div class="val">{{ $fmt($synthese['montant_budgete']) }}</div></td>
            <td><div class="label">Couverture</div><div class="val">{{ $synthese['taux_couverture'] }} %</div></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Libellé</th>
                <th style="text-align:right">Total PAP</th>
                <th style="text-align:right">CEEAC</th>
                <th style="text-align:right">PTF</th>
                <th style="text-align:right">Budgété</th>
                <th style="text-align:right">Écart</th>
                <th style="text-align:right">Couv. %</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($hierarchie as $pilier)
                <tr class="pilier">
                    <td>{{ $pilier['code'] }}</td>
                    <td class="lib">{{ $pilier['libelle'] }}</td>
                    <td class="num">{{ $fmt($pilier['montant_total']) }}</td>
                    <td class="num">{{ $fmt($pilier['montant_ceeac']) }}</td>
                    <td class="num">{{ $fmt($pilier['montant_ptf']) }}</td>
                    <td class="num">{{ $fmt($pilier['montant_budgete']) }}</td>
                    <td class="num">{{ $fmt($pilier['ecart']) }}</td>
                    <td class="num">{{ $pilier['taux_couverture'] }}</td>
                </tr>
                @foreach ($pilier['axes'] as $axe)
                    <tr class="axe">
                        <td>{{ $axe['code'] }}</td>
                        <td class="lib">{{ $axe['libelle'] }}</td>
                        <td class="num">{{ $fmt($axe['montant_total']) }}</td>
                        <td class="num">{{ $fmt($axe['montant_ceeac']) }}</td>
                        <td class="num">{{ $fmt($axe['montant_ptf']) }}</td>
                        <td class="num">{{ $fmt($axe['montant_budgete']) }}</td>
                        <td class="num">{{ $fmt($axe['ecart']) }}</td>
                        <td class="num">{{ $axe['taux_couverture'] }}</td>
                    </tr>
                    @foreach ($axe['produits'] as $produit)
                        <tr class="produit">
                            <td>{{ $produit['code'] }}</td>
                            <td class="lib">{{ $produit['libelle'] }}</td>
                            <td class="num">{{ $fmt($produit['montant_total']) }}</td>
                            <td class="num">{{ $fmt($produit['montant_ceeac']) }}</td>
                            <td class="num">{{ $fmt($produit['montant_ptf']) }}</td>
                            <td class="num">{{ $fmt($produit['montant_budgete']) }}</td>
                            <td class="num">{{ $fmt($produit['ecart']) }}</td>
                            <td class="num">{{ $produit['taux_couverture'] }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">TOTAL</td>
                <td class="num">{{ $fmt($synthese['montant_total']) }}</td>
                <td class="num">{{ $fmt($synthese['montant_ceeac']) }}</td>
                <td class="num">{{ $fmt($synthese['montant_ptf']) }}</td>
                <td class="num">{{ $fmt($synthese['montant_budgete']) }}</td>
                <td class="num">{{ $fmt($synthese['montant_total'] - $synthese['montant_budgete']) }}</td>
                <td class="num">{{ $synthese['taux_couverture'] }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
