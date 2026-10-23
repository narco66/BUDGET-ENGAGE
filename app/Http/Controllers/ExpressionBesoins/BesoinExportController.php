<?php

namespace App\Http\Controllers\ExpressionBesoins;

use App\Exports\BesoinsExport;
use App\Http\Controllers\Controller;
use App\Models\ExpressionBesoin;
use App\Policies\ExpressionBesoinPolicy;
use App\Services\ExpressionBesoin\ExpressionBesoinAuditService;
use App\Services\ExpressionBesoin\ExpressionBesoinDocumentArchiveService;
use App\Support\DocumentQrCode;
use App\Support\MontantEnLettres;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BesoinExportController extends Controller
{
    private const TAUX_TVA = 0.18;

    public function fichePdf(
        ExpressionBesoin $expression_besoin,
        ExpressionBesoinAuditService $audit,
        ExpressionBesoinDocumentArchiveService $archive,
    ): Response {
        $this->authorize('view', $expression_besoin);

        $expression_besoin->load([
            'exerciceBudgetaire', 'departement', 'direction', 'serviceStructure', 'demandeur', 'createur',
            'pilier', 'axeStrategique', 'produit', 'sousProduit', 'activite', 'tache', 'indicateur',
            'lignes.ligneBudgetaire', 'lignes.sourceFinancement', 'lignes.activite', 'validations.validateur',
        ]);

        $data = $this->buildFicheData($expression_besoin);
        $data['besoin'] = $expression_besoin;
        $data['timeline'] = $audit->timeline($expression_besoin);
        $data['pieces'] = $expression_besoin->gedAttachments()->with('document')->get();
        $data['qr'] = DocumentQrCode::imgTag(
            DocumentQrCode::payload('EB', $expression_besoin->reference, (string) $expression_besoin->exerciceBudgetaire?->annee),
            108,
        );
        $data['generatedAt'] = now();

        $contenu = Pdf::loadView('expression_besoins.exports.fiche', $data)->output();

        // Archivage versionné de la fiche officielle en GED (best-effort : ne bloque
        // jamais le téléchargement si la GED est indisponible).
        try {
            $archive->archiverFiche($expression_besoin, $contenu, request()->user());
        } catch (\Throwable $e) {
            report($e);
        }

        return response($contenu, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="fiche-etat-besoins-'.$expression_besoin->reference.'.pdf"',
        ]);
    }

    /**
     * Calcule l'ensemble des agrégats financiers et budgétaires de la fiche
     * à partir des données réelles du besoin.
     *
     * @return array<string, mixed>
     */
    private function buildFicheData(ExpressionBesoin $besoin): array
    {
        // Récapitulatif par catégorie de besoin (sur les lignes catégorisées).
        $categories = ['bien' => 'Biens', 'service' => 'Services', 'travaux' => 'Travaux', 'prestation' => 'Prestations intellectuelles'];
        $recapCategories = [];
        foreach ($categories as $cle => $libelle) {
            $recapCategories[$libelle] = (string) $besoin->lignes
                ->filter(fn ($l) => $this->normaliserCategorie($l->categorie) === $cle)
                ->sum(fn ($l) => (float) $l->montant);
        }

        $ht = (string) ($besoin->montant_total ?? $besoin->lignes->sum(fn ($l) => (float) $l->montant));
        $taxes = bcmul($ht, (string) self::TAUX_TVA, 2);
        $ttc = bcadd($ht, $taxes, 2);

        // Financement : classification heuristique des sources des lignes.
        $propres = '0';
        $partenaires = '0';
        foreach ($besoin->lignes as $ligne) {
            $cible = $this->classifierSource($ligne->sourceFinancement?->libelle ?? $ligne->sourceFinancement?->code);
            $$cible = bcadd($$cible, (string) $ligne->montant, 2);
        }
        $resteAFinancer = bcsub($ttc, bcadd($propres, $partenaires, 2), 2);

        // Situation budgétaire par ligne budgétaire utilisée.
        $budget = $besoin->lignes
            ->filter(fn ($l) => $l->ligne_budgetaire_id !== null)
            ->groupBy('ligne_budgetaire_id')
            ->map(function ($lignes) {
                $ligne = $lignes->first()->ligneBudgetaire;
                $demande = (string) $lignes->sum(fn ($l) => (float) $l->montant);
                $base = (string) ($ligne?->montant_revise ?: $ligne?->montant_vote ?: '0');
                $engage = (string) ($ligne?->montant_engage ?? '0');
                $disponible = $ligne?->creditDisponible() ?? '0';

                return [
                    'ligne' => $ligne,
                    'dotation_initiale' => (string) ($ligne?->montant_vote ?? '0'),
                    'dotation_revisee' => (string) ($ligne?->montant_revise ?? '0'),
                    'engages' => $engage,
                    'disponible' => $disponible,
                    'demande' => $demande,
                    'solde' => bcsub($disponible, $demande, 2),
                    'taux' => bccomp($base, '0', 2) > 0
                        ? bcmul(bcdiv(bcadd($engage, $demande, 2), $base, 6), '100', 2)
                        : '0',
                ];
            })->values();

        return [
            'recapCategories' => $recapCategories,
            'ht' => $ht,
            'taxes' => $taxes,
            'ttc' => $ttc,
            'financementPropres' => $propres,
            'financementPartenaires' => $partenaires,
            'resteAFinancer' => $resteAFinancer,
            'budget' => $budget,
            'montantEnLettres' => MontantEnLettres::convertir($ttc),
        ];
    }

    private function normaliserCategorie(?string $valeur): ?string
    {
        $v = mb_strtolower(trim((string) $valeur));

        return match (true) {
            $v === '' => null,
            str_contains($v, 'bien') => 'bien',
            str_contains($v, 'travaux') || str_contains($v, 'travau') => 'travaux',
            str_contains($v, 'prestation') || str_contains($v, 'intellect') => 'prestation',
            str_contains($v, 'service') => 'service',
            default => null,
        };
    }

    /** @return 'propres'|'partenaires' */
    private function classifierSource(?string $libelle): string
    {
        $v = mb_strtolower(trim((string) $libelle));

        if ($v !== '' && (str_contains($v, 'partenaire') || str_contains($v, 'ptf') || str_contains($v, 'bailleur')
            || str_contains($v, 'don') || str_contains($v, 'ue') || str_contains($v, 'banque'))) {
            return 'partenaires';
        }

        return 'propres';
    }

    public function historiquePdf(ExpressionBesoin $expression_besoin): Response
    {
        $this->authorize('view', $expression_besoin);

        $timeline = app(\App\Services\ExpressionBesoin\ExpressionBesoinAuditService::class)->timeline($expression_besoin);

        return Pdf::loadView('expression_besoins.exports.historique', [
            'besoin' => $expression_besoin,
            'timeline' => $timeline,
            'generatedAt' => now(),
        ])->download('historique-besoin-'.$expression_besoin->reference.'.pdf');
    }

    public function registrePdf(Request $request): Response
    {
        abort_unless($request->user()?->can('expression_besoins.export'), 403);

        $besoins = $this->query($request)->limit(5000)->get();

        return Pdf::loadView('expression_besoins.exports.registre', [
            'besoins' => $besoins,
            'generatedAt' => now(),
        ])->download('registre-besoins.pdf');
    }

    public function etatPapPdf(Request $request): Response
    {
        abort_unless($request->user()?->can('expression_besoins.export'), 403);

        $besoins = $this->query($request)->where('type_besoin', 'pap')->with('pilier')->limit(5000)->get();

        $groupes = $besoins
            ->groupBy(fn ($b) => $b->pilier?->libelle ?? 'Non rattaché à un pilier')
            ->map(fn ($g) => ['besoins' => $g, 'total' => (string) $g->sum(fn ($b) => (float) $b->montant_total)]);

        return Pdf::loadView('expression_besoins.exports.etat_groupe', [
            'titre' => 'État des besoins PAP',
            'libelleGroupe' => 'Pilier',
            'groupes' => $groupes,
            'total' => (string) $besoins->sum(fn ($b) => (float) $b->montant_total),
            'generatedAt' => now(),
        ])->download('etat-besoins-pap.pdf');
    }

    public function etatHorsPapPdf(Request $request): Response
    {
        abort_unless($request->user()?->can('expression_besoins.export'), 403);

        $besoins = $this->query($request)->where('type_besoin', 'hors_pap')->limit(5000)->get();

        $groupes = $besoins
            ->groupBy(fn ($b) => $b->categorie_hors_pap?->libelle() ?? 'Non catégorisé')
            ->map(fn ($g) => ['besoins' => $g, 'total' => (string) $g->sum(fn ($b) => (float) $b->montant_total)]);

        return Pdf::loadView('expression_besoins.exports.etat_groupe', [
            'titre' => 'État des besoins Hors PAP',
            'libelleGroupe' => 'Catégorie',
            'groupes' => $groupes,
            'total' => (string) $besoins->sum(fn ($b) => (float) $b->montant_total),
            'generatedAt' => now(),
        ])->download('etat-besoins-hors-pap.pdf');
    }

    public function etatStructurePdf(Request $request): Response
    {
        abort_unless($request->user()?->can('expression_besoins.export'), 403);

        $besoins = $this->query($request)->with('departement')->limit(5000)->get();

        $groupes = $besoins
            ->groupBy(fn ($b) => $b->departement?->libelle ?: ($b->structure_demandeuse ?: 'Structure non précisée'))
            ->map(fn ($g) => ['besoins' => $g, 'total' => (string) $g->sum(fn ($b) => (float) $b->montant_total)]);

        return Pdf::loadView('expression_besoins.exports.etat_groupe', [
            'titre' => 'État des besoins par structure',
            'libelleGroupe' => 'Structure',
            'groupes' => $groupes,
            'total' => (string) $besoins->sum(fn ($b) => (float) $b->montant_total),
            'generatedAt' => now(),
        ])->download('etat-besoins-par-structure.pdf');
    }

    public function etatCategoriePdf(Request $request): Response
    {
        abort_unless($request->user()?->can('expression_besoins.export'), 403);

        $besoins = $this->query($request)->limit(5000)->get();

        $groupes = $besoins
            ->groupBy(fn ($b) => $b->categorie_hors_pap?->libelle() ?: 'Programmé (PAP)')
            ->map(fn ($g) => ['besoins' => $g, 'total' => (string) $g->sum(fn ($b) => (float) $b->montant_total)]);

        return Pdf::loadView('expression_besoins.exports.etat_groupe', [
            'titre' => 'État des besoins par catégorie',
            'libelleGroupe' => 'Catégorie',
            'groupes' => $groupes,
            'total' => (string) $besoins->sum(fn ($b) => (float) $b->montant_total),
            'generatedAt' => now(),
        ])->download('etat-besoins-par-categorie.pdf');
    }

    public function bordereauPdf(Request $request): Response
    {
        abort_unless($request->user()?->can('expression_besoins.export'), 403);

        // Par défaut, le bordereau transmet les besoins validés (transmissibles).
        $query = $this->query($request);
        if (! $request->filled('statut')) {
            $query->whereIn('statut', ['valide', 'converti']);
        }
        $besoins = $query->limit(5000)->get();

        return Pdf::loadView('expression_besoins.exports.bordereau', [
            'besoins' => $besoins,
            'total' => (string) $besoins->sum(fn ($b) => (float) $b->montant_total),
            'destinataire' => trim((string) $request->input('destinataire')) ?: null,
            'objet' => trim((string) $request->input('objet_transmission')) ?: null,
            'emetteur' => $request->user(),
            'generatedAt' => now(),
        ])->download('bordereau-transmission-besoins.pdf');
    }

    public function etatPiecesPdf(Request $request): Response
    {
        abort_unless($request->user()?->can('expression_besoins.export'), 403);

        $besoins = $this->query($request)
            ->with(['gedAttachments.document'])
            ->limit(2000)
            ->get();

        return Pdf::loadView('expression_besoins.exports.etat_pieces', [
            'besoins' => $besoins,
            'generatedAt' => now(),
        ])->download('etat-pieces-besoins.pdf');
    }

    public function registreExcel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()?->can('expression_besoins.export'), 403);

        $besoins = $this->query($request)->limit(10000)->get();

        return Excel::download(new BesoinsExport($besoins), 'registre-besoins.xlsx');
    }

    /**
     * @return Builder<ExpressionBesoin>
     */
    private function query(Request $request): Builder
    {
        $query = ExpressionBesoin::query()
            ->with(['exerciceBudgetaire', 'departement', 'createur'])
            ->orderByDesc('created_at');

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(fn ($b) => $b->where('reference', 'like', "%{$q}%")->orWhere('objet', 'like', "%{$q}%"));
        }
        if ($request->filled('type_besoin')) {
            $query->where('type_besoin', $request->input('type_besoin'));
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->input('statut'));
        }
        if ($request->filled('exercice_budgetaire_id')) {
            $query->where('exercice_budgetaire_id', $request->integer('exercice_budgetaire_id'));
        }

        if (! ExpressionBesoinPolicy::seesAll($request->user())) {
            $query->where(fn ($b) => $b->where('created_by', $request->user()->id)->orWhere('demandeur_id', $request->user()->id));
        }

        return $query;
    }
}
