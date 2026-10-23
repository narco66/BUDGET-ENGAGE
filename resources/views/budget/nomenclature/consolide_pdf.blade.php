<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>État consolidé PAP / Budget / Exécution {{ $exercice->annee }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 15px; margin-bottom: 2px; }
        .muted { color: #6b7280; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
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

    <h1>État consolidé PAP ↔ Budget ↔ Exécution — {{ $exercice->annee }}</h1>
    <div class="muted">Généré le {{ $generatedAt->format('d/m/Y H:i') }} · CEEAC / BUDGET-ENGAGE</div>

    <table class="cards">
        <tr>
            <td><div class="label">PAP programmé</div><div class="val">{{ $fmt($synthese['papTotal']) }}</div></td>
            <td><div class="label">Ligne PAP du budget</div><div class="val">{{ $fmt($synthese['papLigneBudget']) }}</div></td>
            <td><div class="label">Budget total dépenses</div><div class="val">{{ $fmt($synthese['totalDepenses']) }}</div></td>
            <td><div class="label">Engagé</div><div class="val">{{ $fmt($synthese['engage']) }}</div></td>
            <td><div class="label">Taux engag.</div><div class="val">{{ $synthese['tauxEngagement'] }}%</div></td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Pilier</th>
                <th style="text-align:right">PAP programmé</th>
                <th style="text-align:right">Budgété</th>
                <th style="text-align:right">Taux budg.</th>
                <th style="text-align:right">Engagé</th>
                <th style="text-align:right">Payé</th>
                <th style="text-align:right">Taux engag.</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($piliers as $p)
                <tr>
                    <td>{{ $p['code'] }} — {{ \Illuminate\Support\Str::limit($p['libelle'], 50) }}</td>
                    <td class="num">{{ $fmt($p['pap_total']) }}</td>
                    <td class="num">{{ $fmt($p['budgete']) }}</td>
                    <td class="num">{{ $p['taux_budgetisation'] }}%</td>
                    <td class="num">{{ $fmt($p['engage']) }}</td>
                    <td class="num">{{ $fmt($p['paye']) }}</td>
                    <td class="num">{{ $p['taux_engagement'] }}%</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="num">{{ $fmt(collect($piliers)->sum('pap_total')) }}</td>
                <td class="num">{{ $fmt(collect($piliers)->sum('budgete')) }}</td>
                <td class="num">—</td>
                <td class="num">{{ $fmt(collect($piliers)->sum('engage')) }}</td>
                <td class="num">{{ $fmt(collect($piliers)->sum('paye')) }}</td>
                <td class="num">—</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
