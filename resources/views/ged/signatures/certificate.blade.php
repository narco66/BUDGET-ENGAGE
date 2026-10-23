<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>Certificat de signature — {{ $signature->uuid }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1E293B; font-size: 12px; }
        .entete { text-align: center; border-bottom: 2px solid #1E3A8A; padding-bottom: 10px; margin-bottom: 18px; }
        .entete h1 { color: #1E3A8A; font-size: 18px; margin: 0; }
        .entete p { color: #64748B; margin: 4px 0 0; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td { padding: 6px 8px; border-bottom: 1px solid #E2E8F0; vertical-align: top; }
        td.label { color: #64748B; width: 35%; }
        td.value { font-weight: bold; }
        .mono { font-family: DejaVu Sans Mono, monospace; font-size: 9px; word-break: break-all; }
        .statut { margin-top: 18px; padding: 10px; border-radius: 6px; text-align: center; font-weight: bold; }
        .ok { background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; }
        .ko { background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA; }
        .pied { margin-top: 24px; font-size: 10px; color: #64748B; text-align: center; }
    </style>
</head>
<body>
    @php($valide = $resultat['signe'] && $resultat['integrite_fichier'] && $resultat['scelle_valide'])

    <div class="entete">
        <h1>Certificat de signature électronique</h1>
        <p>Commission de la CEEAC — Gestion Électronique des Documents</p>
    </div>

    <table>
        <tr><td class="label">Document</td><td class="value">{{ $signature->document->titre }}</td></tr>
        <tr><td class="label">Référence documentaire</td><td class="value mono">{{ $signature->document->reference }}</td></tr>
        <tr><td class="label">Identifiant de signature</td><td class="value mono">{{ $signature->uuid }}</td></tr>
        <tr><td class="label">Signataire</td><td class="value">{{ $signature->signataire?->name }}</td></tr>
        <tr><td class="label">Horodatage</td><td class="value">{{ $signature->horodatage?->format('d/m/Y H:i:s') }}</td></tr>
        <tr><td class="label">Algorithme</td><td class="value">{{ $signature->algorithme }}</td></tr>
        <tr><td class="label">Empreinte SHA-256 signée</td><td class="value mono">{{ $signature->hash_signe }}</td></tr>
        <tr><td class="label">Scellé HMAC</td><td class="value mono">{{ $signature->signature_value }}</td></tr>
    </table>

    <div class="statut {{ $valide ? 'ok' : 'ko' }}">
        {{ $valide
            ? 'SIGNATURE VALIDE — Document intègre et scellé authentique'
            : 'SIGNATURE NON VÉRIFIÉE — Le document a pu être altéré' }}
    </div>

    <div class="pied">
        Document généré le {{ now()->format('d/m/Y H:i:s') }}.
        Vérifiez l'authenticité de ce certificat dans l'application BUDGET-ENGAGE (module GED).
    </div>
</body>
</html>
