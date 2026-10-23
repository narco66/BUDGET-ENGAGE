<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>{{ $titre }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1E293B; font-size: 10px; }
        .entete { border-bottom: 2px solid #1E3A8A; padding-bottom: 8px; margin-bottom: 12px; }
        .entete h1 { color: #1E3A8A; font-size: 15px; margin: 0; }
        .entete p { color: #64748B; margin: 3px 0 0; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1E3A8A; color: #fff; text-align: left; padding: 5px 6px; font-size: 9px; }
        td { padding: 4px 6px; border-bottom: 1px solid #E2E8F0; font-size: 9px; vertical-align: top; }
        tr:nth-child(even) td { background: #F8FAFC; }
        .pied { margin-top: 12px; font-size: 8px; color: #94A3B8; text-align: right; }
    </style>
</head>
<body>
    <div class="entete">
        <h1>{{ $titre }}</h1>
        <p>Commission de la CEEAC — Gestion Électronique des Documents — {{ count($lignes) }} enregistrement(s)</p>
    </div>

    <table>
        <thead>
            <tr>
                @foreach ($colonnes as $colonne)
                    <th>{{ $colonne }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($lignes as $ligne)
                <tr>
                    @foreach ($ligne as $cellule)
                        <td>{{ $cellule }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($colonnes) }}" style="text-align:center;padding:14px;color:#94A3B8;">Aucun enregistrement.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pied">Généré le {{ $generatedAt->format('d/m/Y H:i:s') }} — BUDGET-ENGAGE</div>
</body>
</html>
