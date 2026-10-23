@php
    $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');
    $fmt0 = fn ($v) => number_format((float) $v, 0, ',', ' ');
    $logo = public_path('images/CEEAC-logo.jpg');
    $statut = $besoin->statut?->value;
    $statutLib = $besoin->statut?->libelle() ?? '—';
    $statutColor = match ($statut) {
        'valide', 'converti' => '#16A34A',
        'rejete', 'annule' => '#DC2626',
        'retourne' => '#D97706',
        'soumis', 'en_validation' => '#2563EB',
        default => '#64748B',
    };
    $version = $besoin->version_revision ? '1.'.$besoin->version_revision : '1.0';
    // Avis dérivé d'un statut de validation.
    $avisDe = fn ($s) => match ($s) {
        'valide', 'approuve' => ['Favorable', '#16A34A'],
        'rejete' => ['Défavorable', '#DC2626'],
        'retourne' => ['Retour', '#D97706'],
        'en_attente' => ['En attente', '#64748B'],
        default => [ucfirst(str_replace('_', ' ', (string) $s)), '#475569'],
    };
    $intervenants = [];
    $intervenants[] = ['role' => 'Demandeur', 'nom' => $besoin->demandeur?->name ?? '—', 'fonction' => 'Demandeur', 'avis' => ['Favorable', '#16A34A'], 'statut' => 'Soumis', 'date' => $besoin->submitted_at ?? $besoin->date_besoin];
    foreach ($besoin->validations as $v) {
        $intervenants[] = [
            'role' => $v->libelle_etape,
            'nom' => $v->validateur?->name ?? '—',
            'fonction' => $v->libelle_etape,
            'avis' => $avisDe($v->statut?->value),
            'statut' => $v->statut?->libelle() ?? '—',
            'date' => $v->date_action,
        ];
    }
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>Fiche d'état de besoins — {{ $besoin->reference }}</title>
    @include('pdf.partials.styles')
    <style>
        body { font-size: 9px; }
        .fiche-head { width: 100%; border-collapse: collapse; border-bottom: 2px solid #1E3A8A; padding-bottom: 6px; margin-bottom: 12px; }
        .fiche-head td { vertical-align: top; padding: 0 6px; }
        .fiche-head .logo { width: 70px; }
        .fiche-head .logo img { width: 64px; height: 64px; }
        .fiche-org { font-size: 8.5px; color: #1E3A8A; font-weight: bold; line-height: 1.25; }
        .fiche-org small { display: block; color: #64748B; font-weight: normal; font-size: 7.5px; margin-top: 2px; }
        .fiche-center { text-align: center; }
        .fiche-center .app { color: #1E3A8A; font-size: 16px; font-weight: bold; letter-spacing: .5px; }
        .fiche-center .sys { color: #64748B; font-size: 7.5px; margin: 1px 0 5px; }
        .fiche-title { display: inline-block; background: #1E3A8A; color: #fff; font-size: 11px; font-weight: bold; padding: 5px 14px; border-radius: 5px; }
        .fiche-meta { width: 200px; border: 1px solid #E2E8F0; border-radius: 5px; border-collapse: separate; font-size: 7.5px; }
        .fiche-meta td { padding: 3px 6px; border-bottom: 1px solid #EEF2F7; }
        .fiche-meta td.k { color: #64748B; }
        .fiche-meta td.v { text-align: right; font-weight: bold; color: #1E293B; }
        .badge-statut { display: inline-block; color: #fff; font-size: 7px; font-weight: bold; padding: 2px 7px; border-radius: 10px; }

        .sec { margin: 0 0 4px; }
        .sec-title { color: #1E3A8A; font-size: 10px; font-weight: bold; padding: 4px 0 3px; border-bottom: 1px solid #E2E8F0; margin-bottom: 5px; }
        .sec-title .n { display: inline-block; background: #1E3A8A; color: #fff; width: 14px; height: 14px; line-height: 14px; text-align: center; border-radius: 3px; font-size: 8px; margin-right: 5px; }
        table.kv { width: 100%; border-collapse: collapse; }
        table.kv td { padding: 2.5px 5px; vertical-align: top; }
        table.kv td.k { color: #64748B; width: 26%; }
        table.cols { width: 100%; border-collapse: collapse; }
        table.cols > tbody > tr > td { vertical-align: top; padding: 0 6px; }
        table.cols > tbody > tr > td:first-child { padding-left: 0; }
        table.cols > tbody > tr > td:last-child { padding-right: 0; }

        table.grid { width: 100%; border-collapse: collapse; margin-top: 3px; }
        table.grid th { background: #1E3A8A; color: #fff; text-align: left; padding: 4px 5px; font-size: 7.5px; }
        table.grid td { padding: 3px 5px; border-bottom: 1px solid #E2E8F0; font-size: 7.5px; }
        table.grid tr:nth-child(even) td { background: #F8FAFC; }
        table.grid tr.total td { font-weight: bold; background: #EFF6FF; border-top: 1px solid #1E3A8A; }
        .r { text-align: right; }
        .c { text-align: center; }
        .prio-haute { color: #fff; background: #DC2626; padding: 1px 5px; border-radius: 8px; font-size: 6.5px; }
        .prio-moyenne { color: #fff; background: #D97706; padding: 1px 5px; border-radius: 8px; font-size: 6.5px; }
        .prio-basse { color: #fff; background: #64748B; padding: 1px 5px; border-radius: 8px; font-size: 6.5px; }
        .recap { width: 100%; border-collapse: collapse; font-size: 8px; }
        .recap td { padding: 2.5px 4px; border-bottom: 1px solid #EEF2F7; }
        .recap td.v { text-align: right; font-family: monospace; }
        .recap tr.sub td { font-weight: bold; border-top: 1px solid #CBD5E1; }
        .recap tr.ttc td { font-weight: bold; color: #1E3A8A; font-size: 9px; }
        .cartouche { margin-top: 6px; padding: 6px; background: #1E3A8A; color: #fff; text-align: center; font-weight: bold; font-size: 9px; border-radius: 4px; }
        .en-lettres { margin-top: 6px; font-size: 7.5px; color: #334155; font-style: italic; }
        .ok-box { margin-top: 6px; padding: 6px; background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 4px; font-size: 7.5px; color: #065F46; }
        .ko-box { margin-top: 6px; padding: 6px; background: #FEF2F2; border: 1px solid #FECACA; border-radius: 4px; font-size: 7.5px; color: #991B1B; }

        table.visas { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 3px; }
        table.visas td { border: 1px solid #E2E8F0; vertical-align: top; padding: 4px; font-size: 6.5px; text-align: center; }
        table.visas .vrole { color: #1E3A8A; font-weight: bold; font-size: 6.5px; }
        table.visas .vnom { font-weight: bold; margin-top: 2px; }
        table.visas .vsign { height: 22px; border-top: 1px solid #CBD5E1; margin-top: 6px; }

        .doc-info td.k { color: #64748B; width: 40%; }
        .conf { background: #FEF3C7; color: #92400E; padding: 1px 6px; border-radius: 8px; font-size: 7px; }
        .pied { position: fixed; bottom: 6mm; left: 12mm; right: 12mm; font-size: 7px; color: #94A3B8; border-top: 1px solid #E2E8F0; padding-top: 3px; }
        .pied .l { float: left; } .pied .r { float: right; }
        .pied .r:after { content: "Page " counter(page) " sur " counter(pages); }
        @page { margin: 12mm 12mm 16mm 12mm; }
    </style>
</head>
<body>
    {{-- ===================== EN-TÊTE INSTITUTIONNEL ===================== --}}
    <table class="fiche-head">
        <tr>
            <td class="logo">
                @if (file_exists($logo))<img src="{{ $logo }}" alt="CEEAC">@endif
            </td>
            <td>
                <div class="fiche-org">
                    COMMISSION DE LA COMMUNAUTÉ ÉCONOMIQUE DES ÉTATS DE L'AFRIQUE CENTRALE
                    <small>Unité – Intégration – Développement</small>
                </div>
            </td>
            <td class="fiche-center">
                <div class="app">BUDGET-ENGAGE</div>
                <div class="sys">SYSTÈME INTÉGRÉ DE GESTION DE LA CHAÎNE DE LA DÉPENSE</div>
                <span class="fiche-title">FICHE D'ÉTAT DE BESOINS</span>
            </td>
            <td style="width:205px;">
                <table class="fiche-meta">
                    <tr><td class="k">N° FICHE</td><td class="v">{{ $besoin->reference }}</td></tr>
                    <tr><td class="k">EXERCICE</td><td class="v">{{ $besoin->exerciceBudgetaire?->annee ?? '—' }}</td></tr>
                    <tr><td class="k">DATE D'ÉTABLISSEMENT</td><td class="v">{{ optional($besoin->date_besoin)->format('d/m/Y') ?? optional($besoin->created_at)->format('d/m/Y') }}</td></tr>
                    <tr><td class="k">VERSION</td><td class="v">{{ $version }}</td></tr>
                    <tr><td class="k">STATUT</td><td class="v"><span class="badge-statut" style="background: {{ $statutColor }};">{{ mb_strtoupper($statutLib) }}</span></td></tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ============ 1 & 2 : IDENTIFICATION / PLANIFICATION ============ --}}
    <table class="cols"><tr>
        <td style="width:50%;">
            <div class="sec-title"><span class="n">1</span>IDENTIFICATION DE LA STRUCTURE DEMANDEUSE</div>
            <table class="kv">
                <tr><td class="k">Département</td><td>{{ $besoin->departement?->libelle ?? '—' }}</td></tr>
                <tr><td class="k">Direction</td><td>{{ $besoin->direction?->libelle ?? '—' }}</td></tr>
                <tr><td class="k">Service</td><td>{{ $besoin->serviceStructure?->libelle ?? '—' }}</td></tr>
                <tr><td class="k">Unité administrative</td><td>{{ $besoin->structure_demandeuse ?? '—' }}</td></tr>
                <tr><td class="k">Demandeur</td><td>{{ $besoin->demandeur?->name ?? '—' }}</td></tr>
                <tr><td class="k">Contact</td><td>{{ $besoin->demandeur?->email ?? '—' }}</td></tr>
                <tr><td class="k">Période couverte</td><td>
                    @if ($besoin->exerciceBudgetaire?->date_debut)
                        {{ optional($besoin->exerciceBudgetaire->date_debut)->format('d/m/Y') }} au {{ optional($besoin->exerciceBudgetaire->date_fin)->format('d/m/Y') }}
                    @else — @endif
                </td></tr>
            </table>
        </td>
        <td style="width:50%;">
            <div class="sec-title"><span class="n">2</span>RÉFÉRENCES DE PLANIFICATION</div>
            <table class="kv">
                <tr><td class="k">Type de besoin</td><td><strong>{{ $besoin->type_besoin?->libelle() ?? '—' }}</strong></td></tr>
                @if ($besoin->estPap())
                    <tr><td class="k">Pilier</td><td>{{ $besoin->pilier?->libelle ?? '—' }}</td></tr>
                    <tr><td class="k">Axe stratégique</td><td>{{ $besoin->axeStrategique?->libelle ?? '—' }}</td></tr>
                    <tr><td class="k">Produit</td><td>{{ $besoin->produit?->libelle ?? '—' }}</td></tr>
                    <tr><td class="k">Sous-produit</td><td>{{ $besoin->sousProduit?->libelle ?? '—' }}</td></tr>
                    <tr><td class="k">Activité</td><td>{{ $besoin->activite?->libelle ?? '—' }}</td></tr>
                    <tr><td class="k">Tâche</td><td>{{ $besoin->tache?->libelle ?? '—' }}</td></tr>
                    <tr><td class="k">Indicateur associé</td><td>{{ $besoin->indicateur?->libelle ?? '—' }}</td></tr>
                @else
                    <tr><td class="k">Catégorie (Hors PAP)</td><td>{{ $besoin->categorie_hors_pap?->libelle() ?? '—' }}</td></tr>
                    <tr><td class="k">Sous-catégorie</td><td>{{ $besoin->sous_categorie ?? '—' }}</td></tr>
                    <tr><td class="k">Nature</td><td>{{ $besoin->nature_besoin ?? '—' }}</td></tr>
                    <tr><td class="k">Centre de coût</td><td>{{ $besoin->centre_cout ?? '—' }}</td></tr>
                    <tr><td class="k">Centre de responsabilité</td><td>{{ $besoin->centre_responsabilite ?? '—' }}</td></tr>
                @endif
                <tr><td class="k">Justification</td><td>{{ \Illuminate\Support\Str::limit($besoin->justification, 180) ?: '—' }}</td></tr>
            </table>
        </td>
    </tr></table>

    {{-- ===================== 3 : TABLEAU DES BESOINS ===================== --}}
    <div class="sec-title" style="margin-top:8px;"><span class="n">3</span>TABLEAU DÉTAILLÉ DES BESOINS</div>
    <table class="grid">
        <thead><tr>
            <th class="c">N°</th><th>Code</th><th>Désignation</th><th>Catégorie</th><th>Spécifications</th>
            <th class="c">Unité</th><th class="r">Qté</th><th class="r">P.U. estim.</th><th class="r">Montant estim.</th>
            <th class="c">Priorité</th><th class="c">Échéance</th>
        </tr></thead>
        <tbody>
            @foreach ($besoin->lignes as $i => $l)
                @php($prio = mb_strtolower((string) ($l->priorite ?? $besoin->priorite?->value)))
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td>{{ $l->code_article ?? '—' }}</td>
                    <td>{{ $l->designation }}</td>
                    <td>{{ $l->categorie ?? '—' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($l->specifications, 40) ?: '—' }}</td>
                    <td class="c">{{ $l->unite ?? '—' }}</td>
                    <td class="r">{{ $fmt0($l->quantite) }}</td>
                    <td class="r">{{ $fmt0($l->prix_unitaire) }}</td>
                    <td class="r">{{ $fmt0($l->montant) }}</td>
                    <td class="c">
                        @if (str_contains($prio, 'haut') || str_contains($prio, 'elev'))<span class="prio-haute">Haute</span>
                        @elseif (str_contains($prio, 'moy'))<span class="prio-moyenne">Moyenne</span>
                        @elseif ($prio !== '')<span class="prio-basse">{{ ucfirst($prio) }}</span>
                        @else — @endif
                    </td>
                    <td class="c">{{ optional($l->echeance)->format('d/m/Y') ?? '—' }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="6" class="r">TOTAL GÉNÉRAL</td>
                <td class="r">{{ $fmt0($besoin->lignes->sum('quantite')) }}</td>
                <td></td>
                <td class="r">{{ $fmt0($besoin->montant_total) }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>

    {{-- ============ 4 / 5 / 6 : FINANCIER / BUDGET / PIÈCES ============ --}}
    <table class="cols" style="margin-top:8px;"><tr>
        <td style="width:34%;">
            <div class="sec-title"><span class="n">4</span>RÉCAPITULATIF FINANCIER (FCFA)</div>
            <table class="recap">
                @foreach ($recapCategories as $lib => $val)
                    <tr><td>Total {{ $lib }}</td><td class="v">{{ $fmt0($val) }}</td></tr>
                @endforeach
                <tr class="sub"><td>Sous-total HT</td><td class="v">{{ $fmt0($ht) }}</td></tr>
                <tr><td>Taxes (18%)</td><td class="v">{{ $fmt0($taxes) }}</td></tr>
                <tr class="ttc"><td>MONTANT TTC</td><td class="v">{{ $fmt0($ttc) }}</td></tr>
                <tr><td>Ressources propres</td><td class="v">{{ $fmt0($financementPropres) }}</td></tr>
                <tr><td>Partenaires</td><td class="v">{{ $fmt0($financementPartenaires) }}</td></tr>
                <tr class="sub"><td>Reste à financer</td><td class="v">{{ $fmt0($resteAFinancer) }}</td></tr>
            </table>
            <div class="en-lettres">Arrêté à la somme de : {{ $montantEnLettres }}.</div>
        </td>
        <td style="width:38%;">
            <div class="sec-title"><span class="n">5</span>SITUATION BUDGÉTAIRE</div>
            @php($soldeNeg = $budget->contains(fn ($b) => bccomp((string) $b['solde'], '0', 2) < 0))
            <table class="grid">
                <thead><tr><th>Ligne budg.</th><th class="r">Disponible</th><th class="r">Demandé</th><th class="r">Solde prév.</th><th class="r">Taux</th></tr></thead>
                <tbody>
                    @forelse ($budget as $b)
                        <tr>
                            <td>{{ $b['ligne']?->code ?? '—' }}</td>
                            <td class="r">{{ $fmt0($b['disponible']) }}</td>
                            <td class="r">{{ $fmt0($b['demande']) }}</td>
                            <td class="r">{{ $fmt0($b['solde']) }}</td>
                            <td class="r">{{ $fmt($b['taux']) }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="c">Aucune imputation budgétaire.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if ($budget->isNotEmpty())
                @if ($soldeNeg)
                    <div class="ko-box">⚠ Crédits insuffisants sur une ou plusieurs lignes pour couvrir le présent besoin.</div>
                @else
                    <div class="ok-box">✓ Crédits disponibles suffisants pour couvrir le présent besoin.</div>
                @endif
            @endif
        </td>
        <td style="width:28%;">
            <div class="sec-title"><span class="n">6</span>PIÈCES JUSTIFICATIVES</div>
            <table class="grid">
                <thead><tr><th>Type / Pièce</th><th class="c">Statut</th></tr></thead>
                <tbody>
                    @forelse ($pieces as $p)
                        <tr>
                            <td>{{ \Illuminate\Support\Str::limit($p->document?->titre ?? $p->role ?? 'Pièce', 28) }}</td>
                            <td class="c"><span class="prio-basse" style="background:#16A34A;">Valide</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="c">Aucune pièce jointe.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </td>
    </tr></table>

    {{-- ===================== 7 : AVIS, VISAS, VALIDATIONS ===================== --}}
    <div class="sec-title" style="margin-top:8px;"><span class="n">7</span>AVIS, VISAS ET VALIDATIONS</div>
    <table class="visas"><tr>
        @foreach ($intervenants as $v)
            <td>
                <div class="vrole">{{ \Illuminate\Support\Str::limit($v['role'], 22) }}</div>
                <div class="vnom">{{ \Illuminate\Support\Str::limit($v['nom'], 20) }}</div>
                <div style="color:{{ $v['avis'][1] }}; font-weight:bold; margin-top:2px;">{{ $v['avis'][0] }}</div>
                <div style="color:#64748B;">{{ $v['statut'] }}</div>
                <div style="color:#94A3B8; font-size:6px;">{{ optional($v['date'])->format('d/m/Y') ?? '—' }}</div>
                <div class="vsign"></div>
            </td>
        @endforeach
    </tr></table>

    {{-- ============ 8 & 9 : HISTORIQUE / INFOS DOCUMENT ============ --}}
    <table class="cols" style="margin-top:8px;"><tr>
        <td style="width:62%;">
            <div class="sec-title"><span class="n">8</span>HISTORIQUE ET TRAÇABILITÉ</div>
            <table class="grid">
                <thead><tr><th>Date</th><th>Heure</th><th>Utilisateur</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse ($timeline as $h)
                        <tr>
                            <td>{{ optional($h->created_at)->format('d/m/Y') }}</td>
                            <td>{{ optional($h->created_at)->format('H:i') }}</td>
                            <td>{{ $h->causer?->name ?? 'Système' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($h->description, 48) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="c">Aucun évènement.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </td>
        <td style="width:38%;">
            <div class="sec-title"><span class="n">9</span>INFORMATIONS DOCUMENT</div>
            <table class="kv doc-info">
                <tr><td class="k">Référence unique</td><td>{{ $besoin->reference }}</td></tr>
                <tr><td class="k">Généré le</td><td>{{ $generatedAt->format('d/m/Y H:i') }}</td></tr>
                <tr><td class="k">Généré par</td><td>{{ auth()->user()?->name ?? 'Système (BUDGET-ENGAGE)' }}</td></tr>
                <tr><td class="k">Format</td><td>A4</td></tr>
                <tr><td class="k">Confidentialité</td><td><span class="conf">Interne</span></td></tr>
                <tr><td class="k">Version</td><td>{{ $version }}</td></tr>
            </table>
            @if (! empty($qr))
                <table style="width:100%; margin-top:4px;"><tr>
                    <td style="width:115px;">{!! $qr !!}</td>
                    <td style="font-size:7px; color:#64748B; vertical-align:middle;">Scannez ce QR Code pour vérifier l'authenticité de ce document sur BUDGET-ENGAGE.</td>
                </tr></table>
            @endif
        </td>
    </tr></table>

    <div class="pied">
        <span class="l">BUDGET-ENGAGE · Document généré automatiquement — Ne pas modifier manuellement · {{ $besoin->reference }}</span>
        <span class="r"></span>
    </div>
</body>
</html>
