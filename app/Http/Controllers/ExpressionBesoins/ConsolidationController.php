<?php

namespace App\Http\Controllers\ExpressionBesoins;

use App\Exports\BesoinConsolidationExport;
use App\Http\Controllers\Controller;
use App\Models\ExpressionBesoin;
use App\Policies\ExpressionBesoinPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ConsolidationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ExpressionBesoin::class);

        $lignes = $this->aggregate($request);

        $totaux = [
            'nb' => $lignes->sum('nb'),
            'demande' => $lignes->sum(fn ($l) => (float) $l->demande),
            'valide' => $lignes->sum(fn ($l) => (float) $l->valide),
            'converti' => $lignes->sum(fn ($l) => (float) $l->converti),
            'pap' => $lignes->sum(fn ($l) => (float) $l->pap),
            'hors_pap' => $lignes->sum(fn ($l) => (float) $l->hors_pap),
        ];

        return view('expression_besoins.consolidation', compact('lignes', 'totaux'));
    }

    public function excel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()?->can('expression_besoins.export'), 403);

        return Excel::download(new BesoinConsolidationExport($this->aggregate($request)), 'consolidation-besoins.xlsx');
    }

    public function pdf(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        abort_unless($request->user()?->can('expression_besoins.export'), 403);

        $lignes = $this->aggregate($request);

        $totaux = [
            'nb' => (int) $lignes->sum('nb'),
            'demande' => (string) $lignes->sum(fn ($l) => (float) $l->demande),
            'valide' => (string) $lignes->sum(fn ($l) => (float) $l->valide),
            'converti' => (string) $lignes->sum(fn ($l) => (float) $l->converti),
            'pap' => (string) $lignes->sum(fn ($l) => (float) $l->pap),
            'hors_pap' => (string) $lignes->sum(fn ($l) => (float) $l->hors_pap),
        ];

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('expression_besoins.exports.consolidation', [
            'lignes' => $lignes,
            'totaux' => $totaux,
            'generatedAt' => now(),
        ])->download('consolidation-besoins.pdf');
    }

    /**
     * Agrégation par exercice (source unique partagée par l'écran et l'export).
     *
     * @return Collection<int, object>
     */
    private function aggregate(Request $request): Collection
    {
        $query = ExpressionBesoin::query()
            ->join('exercices_budgetaires', 'exercices_budgetaires.id', '=', 'expression_besoins.exercice_budgetaire_id');

        if (! ExpressionBesoinPolicy::seesAll($request->user())) {
            $query->where(fn ($b) => $b->where('created_by', $request->user()->id)->orWhere('demandeur_id', $request->user()->id));
        }

        return $query
            ->selectRaw("exercices_budgetaires.annee as annee,
                COUNT(*) as nb,
                SUM(expression_besoins.montant_total) as demande,
                SUM(CASE WHEN expression_besoins.statut IN ('valide','converti') THEN expression_besoins.montant_total ELSE 0 END) as valide,
                SUM(CASE WHEN expression_besoins.statut = 'converti' THEN expression_besoins.montant_total ELSE 0 END) as converti,
                SUM(CASE WHEN expression_besoins.type_besoin = 'pap' THEN expression_besoins.montant_total ELSE 0 END) as pap,
                SUM(CASE WHEN expression_besoins.type_besoin = 'hors_pap' THEN expression_besoins.montant_total ELSE 0 END) as hors_pap")
            ->groupBy('exercices_budgetaires.annee')
            ->orderByDesc('exercices_budgetaires.annee')
            ->get();
    }
}
