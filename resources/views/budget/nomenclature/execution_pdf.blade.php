<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>État d'exécution budgétaire {{ $exercice->annee }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        h2 { font-size: 12px; margin: 14px 0 4px; }
        .muted { color: #6b7280; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #d1d5db; padding: 4px 6px; }
        th { background: #f3f4f6; text-align: left; font-size: 9px; text-transform: uppercase; }
        td.num { text-align: right; font-variant-numeric: tabular-nums; }
        .cards td { border: none; padding: 2px 10px 2px 0; }
        .label { color: #6b7280; font-size: 9px; }
        .val { font-size: 13px; font-weight: bold; }
        tfoot td { background: #f3f4f6; font-weight: bold; }
    </style>
</head>
<body>
    @php $fmt = fn ($v) => number_format((float) $v, 0, ',', ' '); @endphp

    <h1>État d'exécution budgétaire — {{ $exercice->annee }}</h1>
    <div class="muted">Généré le {{ $generatedAt->format('d/m/Y H:i') }} · CEEAC / BUDGET-ENGAGE</div>

    <table class="cards">
        <tr>
            <td><div class="label">Recettes</div><div class="val">{{ $fmt($synthese['montant_recettes']) }}</div></td>
            <td><div class="label">Dépenses</div><div class="val">{{ $fmt($synthese['montant_depenses']) }}</div></td>
            <td><div class="label">Crédits ouverts</div><div class="val">{{ $fmt($synthese['credits_ouverts']) }}</div></td>
            <td><div class="label">Engagé</div><div class="val">{{ $fmt($synthese['montant_engage']) }}</div></td>
            <td><div class="label">Payé</div><div class="val">{{ $fmt($synthese['montant_paye']) }}</div></td>
            <td><div class="label">Disponible</div><div class="val">{{ $fmt($synthese['disponible']) }}</div></td>
            <td><div class="label">Taux engag.</div><div class="val">{{ $synthese['taux_engagement'] }}%</div></td>
        </tr>
    </table>

    <h2>Exécution par catégorie</h2>
    <table>
        <thead>
            <tr><th>Catégorie</th><th style="text-align:right">Ouverts</th><th style="text-align:right">Engagé</th><th style="text-align:right">Payé</th><th style="text-align:right">Disponible</th><th style="text-align:right">Taux</th></tr>
        </thead>
        <tbody>
            @foreach ($categories as $c)
                <tr>
                    <td>{{ $c['libelle'] }}</td>
                    <td class="num">{{ $fmt($c['credits_ouverts']) }}</td>
                    <td class="num">{{ $fmt($c['montant_engage']) }}</td>
                    <td class="num">{{ $fmt($c['montant_paye']) }}</td>
                    <td class="num">{{ $fmt($c['disponible']) }}</td>
                    <td class="num">{{ $c['taux_engagement'] }}%</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="num">{{ $fmt($synthese['credits_ouverts']) }}</td>
                <td class="num">{{ $fmt($synthese['montant_engage']) }}</td>
                <td class="num">{{ $fmt($synthese['montant_paye']) }}</td>
                <td class="num">{{ $fmt($synthese['disponible']) }}</td>
                <td class="num">{{ $synthese['taux_engagement'] }}%</td>
            </tr>
        </tfoot>
    </table>

    <h2>Détail par ligne opérationnelle ({{ count($lignes) }})</h2>
    <table>
        <thead>
            <tr><th>Code</th><th>Libellé</th><th style="text-align:right">Ouverts</th><th style="text-align:right">Engagé</th><th style="text-align:right">Payé</th><th style="text-align:right">Disponible</th></tr>
        </thead>
        <tbody>
            @foreach ($lignes as $l)
                <tr>
                    <td>{{ $l['code'] }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($l['libelle'], 55) }}</td>
                    <td class="num">{{ $fmt($l['credits_ouverts']) }}</td>
                    <td class="num">{{ $fmt($l['montant_engage']) }}</td>
                    <td class="num">{{ $fmt($l['montant_paye']) }}</td>
                    <td class="num">{{ $fmt($l['disponible']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
