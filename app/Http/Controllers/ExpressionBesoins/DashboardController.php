<?php

namespace App\Http\Controllers\ExpressionBesoins;

use App\Enums\StatutBesoin;
use App\Enums\TypeBesoin;
use App\Http\Controllers\Controller;
use App\Models\ExpressionBesoin;
use App\Policies\ExpressionBesoinPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $base = ExpressionBesoin::query();

        // Cloisonnement : sans visibilité transverse, on ne compte que ses besoins.
        if (! ExpressionBesoinPolicy::seesAll($request->user())) {
            $base->where(function ($q) use ($request): void {
                $q->where('created_by', $request->user()->id)
                    ->orWhere('demandeur_id', $request->user()->id);
            });
        }

        $kpis = [
            'total' => (clone $base)->count(),
            'pap' => (clone $base)->where('type_besoin', TypeBesoin::Pap->value)->count(),
            'hors_pap' => (clone $base)->where('type_besoin', TypeBesoin::HorsPap->value)->count(),
            'en_validation' => (clone $base)->whereIn('statut', [
                StatutBesoin::Soumis->value,
                StatutBesoin::EnValidation->value,
            ])->count(),
            'valides' => (clone $base)->where('statut', StatutBesoin::Valide->value)->count(),
            'rejetes' => (clone $base)->where('statut', StatutBesoin::Rejete->value)->count(),
            'convertis' => (clone $base)->where('statut', StatutBesoin::Converti->value)->count(),
            'montant_demande' => (float) (clone $base)->sum('montant_total'),
            'montant_valide' => (float) (clone $base)->whereIn('statut', [
                StatutBesoin::Valide->value, StatutBesoin::Converti->value,
            ])->sum('montant_total'),
        ];

        $parStatut = (clone $base)
            ->selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut')
            ->toArray();

        $derniers = (clone $base)
            ->with(['exerciceBudgetaire', 'createur'])
            ->latest('created_at')
            ->limit(8)
            ->get();

        $besoinIds = (clone $base)->pluck('id');

        $parDepartement = (clone $base)
            ->leftJoin('departements', 'departements.id', '=', 'expression_besoins.departement_id')
            ->selectRaw("COALESCE(departements.libelle, 'Non rattaché') as libelle, COUNT(*) as total, SUM(montant_total) as montant")
            ->groupByRaw("COALESCE(departements.libelle, 'Non rattaché')")
            ->orderByDesc('montant')
            ->get();

        $parLigne = \App\Models\ExpressionBesoinLigne::query()
            ->whereIn('expression_besoin_id', $besoinIds)
            ->join('lignes_budgetaires', 'lignes_budgetaires.id', '=', 'expression_besoin_lignes.ligne_budgetaire_id')
            ->selectRaw('lignes_budgetaires.code as code, SUM(expression_besoin_lignes.montant) as montant')
            ->groupBy('lignes_budgetaires.code')
            ->orderByDesc('montant')
            ->limit(8)
            ->get();

        $parSource = \App\Models\ExpressionBesoinLigne::query()
            ->whereIn('expression_besoin_id', $besoinIds)
            ->join('sources_financement', 'sources_financement.id', '=', 'expression_besoin_lignes.source_financement_id')
            ->selectRaw('sources_financement.libelle as libelle, SUM(expression_besoin_lignes.montant) as montant')
            ->groupBy('sources_financement.libelle')
            ->orderByDesc('montant')
            ->get();

        return view('expression_besoins.dashboard', compact(
            'kpis', 'parStatut', 'derniers', 'parDepartement', 'parLigne', 'parSource'
        ));
    }
}
