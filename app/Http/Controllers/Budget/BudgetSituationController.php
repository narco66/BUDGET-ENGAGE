<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\ExerciceBudgetaire;
use App\Models\ImportBudget;
use App\Services\Budget\BudgetLegacyImportBridge;
use App\Services\Budget\BudgetRestesService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BudgetSituationController extends Controller
{
    public function __construct(
        private readonly BudgetRestesService $restes,
    ) {
    }

    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->can('budget.view')
            || $request->user()?->can('exercises.manage')
            || $request->user()?->can('reports.view'),
            403
        );

        $exercices = ExerciceBudgetaire::query()->orderByDesc('annee')->get();
        $exercice = $this->resolveExercice($request, $exercices);
        $search = $request->string('q')->toString();

        $synthese = $exercice ? $this->restes->syntheseExercice($exercice) : null;
        $lignes = $exercice ? $this->restes->lignesExercice($exercice, $search !== '' ? $search : null) : collect();

        return view('budget.situation.index', compact('exercices', 'exercice', 'synthese', 'lignes', 'search'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        abort_unless(
            $request->user()?->can('budget.view')
            || $request->user()?->can('exercises.manage')
            || $request->user()?->can('reports.view'),
            403
        );

        $exercices = ExerciceBudgetaire::query()->orderByDesc('annee')->get();
        $exercice = $this->resolveExercice($request, $exercices);

        abort_if($exercice === null, 404);

        $lignes = $this->restes->lignesExercice($exercice);
        $filename = 'situation-budgetaire-'.$exercice->annee.'.csv';

        return response()->streamDownload(function () use ($lignes): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Code', 'Libellé', 'Plafond', 'Engagé', 'Liquidé', 'Ordonnancé', 'Payé',
                'Reste à engager', 'Reste à liquider', 'Reste à ordonnancer', 'Reste à payer',
            ], ';');

            foreach ($lignes as $row) {
                fputcsv($out, [
                    $row['code'],
                    $row['libelle'],
                    $row['plafond'],
                    $row['montant_engage'],
                    $row['montant_liquide'],
                    $row['montant_ordonnance'],
                    $row['montant_paye'],
                    $row['reste_a_engager'],
                    $row['reste_a_liquider'],
                    $row['reste_a_ordonnancer'],
                    $row['reste_a_payer'],
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, ExerciceBudgetaire>  $exercices
     */
    private function resolveExercice(Request $request, $exercices): ?ExerciceBudgetaire
    {
        if ($request->filled('exercice_budgetaire_id')) {
            return $exercices->firstWhere('id', (int) $request->input('exercice_budgetaire_id'));
        }

        return $exercices->firstWhere('est_actif', true) ?? $exercices->first();
    }
}
