<?php

namespace App\Http\Controllers\Rbm;

use App\Exports\RbmMatriceExport;
use App\Exports\RbmReferentialExport;
use App\Http\Controllers\Controller;
use App\Models\Activite;
use App\Models\AxeStrategique;
use App\Models\CadreLogique;
use App\Models\ChaineResultat;
use App\Models\Indicateur;
use App\Models\Pilier;
use App\Models\Produit;
use App\Models\SousProduit;
use App\Models\Tache;
use App\Services\Rbm\RbmTreeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RbmExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:rbm.export|rbm.admin|rbm.view');
    }

    public function catalogueExcel(string $entity): BinaryFileResponse
    {
        $config = $this->entityConfig($entity);

        return Excel::download(
            new RbmReferentialExport($config['model'], $config['columns']),
            "catalogue-{$entity}.xlsx"
        );
    }

    public function matriceExcel(RbmTreeService $treeService): BinaryFileResponse
    {
        return Excel::download(
            new RbmMatriceExport($treeService->matrixData()['rows']),
            'matrice-rbm-gar.xlsx'
        );
    }

    public function synthesisPdf(RbmTreeService $treeService): Response
    {
        $pdf = Pdf::loadView('rbm.exports.synthesis_pdf', [
            'piliers' => $treeService->fullTree(),
            'generatedAt' => now(),
        ]);

        return $pdf->download('synthese-institutionnelle-rbm.pdf');
    }

    /**
     * @return array{model: class-string, columns: array<int, string>}
     */
    protected function entityConfig(string $entity): array
    {
        return match ($entity) {
            'piliers' => ['model' => Pilier::class, 'columns' => ['code', 'libelle', 'description', 'ordre', 'statut']],
            'axes-strategiques' => ['model' => AxeStrategique::class, 'columns' => ['pilier_id', 'code', 'libelle', 'ordre', 'statut']],
            'produits' => ['model' => Produit::class, 'columns' => ['axe_strategique_id', 'code', 'libelle', 'ordre', 'statut']],
            'sous-produits' => ['model' => SousProduit::class, 'columns' => ['produit_id', 'code', 'libelle', 'ordre', 'statut']],
            'activites' => ['model' => Activite::class, 'columns' => ['sous_produit_id', 'code', 'libelle', 'ordre', 'statut']],
            'taches' => ['model' => Tache::class, 'columns' => ['activite_id', 'code', 'libelle', 'ordre', 'statut']],
            'indicateurs' => ['model' => Indicateur::class, 'columns' => ['code', 'libelle', 'type_indicateur_id', 'unite_mesure_id', 'statut']],
            'cadres-logiques' => ['model' => CadreLogique::class, 'columns' => ['code', 'libelle', 'annee', 'version', 'statut']],
            'chaines-resultats' => ['model' => ChaineResultat::class, 'columns' => ['code', 'libelle', 'niveau', 'statut']],
            default => abort(404),
        };
    }
}
