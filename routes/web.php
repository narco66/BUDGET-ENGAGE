<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Audit\AuditOverviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Budget\ExerciceBudgetaireController;
use App\Http\Controllers\Budget\LigneBudgetaireController;
use App\Http\Controllers\Budget\ImportBudgetController;
use App\Http\Controllers\Budget\BudgetSituationController;
use App\Http\Controllers\Budget\BudgetNomenclatureImportController;
use App\Http\Controllers\Budget\VirementCreditController;
use App\Http\Controllers\Budget\BudgetAlerteController;
use App\Http\Controllers\Budget\ParametreControleController;
use App\Http\Controllers\Recettes\RecetteDashboardController;
use App\Http\Controllers\Recettes\RecettePerceptionController;
use App\Http\Controllers\SuiviPhysique\SuiviPhysiqueController;
use App\Http\Controllers\SuiviEvaluation\SuiviEvaluationController;
use App\Http\Controllers\Engagements\EngagementController;
use App\Http\Controllers\Engagements\EngagementDashboardController;
use App\Http\Controllers\Engagements\EngagementDocumentController;
use App\Http\Controllers\Engagements\EngagementGedController;
use App\Http\Controllers\Engagements\EngagementTransmissionController;
use App\Http\Controllers\Engagements\EngagementTypeController;
use App\Http\Controllers\Execution\ExecutionDashboardController;
use App\Http\Controllers\Execution\LiquidationController;
use App\Http\Controllers\Execution\OrdonnancementController;
use App\Http\Controllers\Execution\PaiementController;
use App\Http\Controllers\Liquidation\LiquidationController as ErpLiquidationController;
use App\Http\Controllers\Liquidation\LiquidationDashboardController;
use App\Http\Controllers\Liquidation\LiquidationDocumentController;
use App\Http\Controllers\Liquidation\LiquidationGedController;
use App\Http\Controllers\Liquidation\LiquidationReportController;
use App\Http\Controllers\Liquidation\LiquidationServiceFaitController;
use App\Http\Controllers\Liquidation\LiquidationTransmissionController;
use App\Http\Controllers\Liquidation\LiquidationTypeController;
use App\Http\Controllers\Liquidation\LiquidationWorkflowController;
use App\Http\Controllers\Ordonnancement\OrdonnancementController as ErpOrdonnancementController;
use App\Http\Controllers\Ordonnancement\OrdonnancementDashboardController;
use App\Http\Controllers\Ordonnancement\OrdonnancementDocumentController;
use App\Http\Controllers\Ordonnancement\OrdonnancementGedController;
use App\Http\Controllers\Ordonnancement\OrdonnancementReportController;
use App\Http\Controllers\Ordonnancement\OrdonnancementTransmissionController;
use App\Http\Controllers\Ordonnancement\OrdonnancementTypeController;
use App\Http\Controllers\Ordonnancement\OrdonnancementWorkflowController;
use App\Http\Controllers\Paiement\PaiementController as ErpPaiementController;
use App\Http\Controllers\Paiement\PaiementBanqueController;
use App\Http\Controllers\Paiement\PaiementDashboardController;
use App\Http\Controllers\Paiement\PaiementDocumentController;
use App\Http\Controllers\Paiement\PaiementReportController;
use App\Http\Controllers\Paiement\PaiementGedController;
use App\Http\Controllers\Paiement\PaiementRejetController;
use App\Http\Controllers\Paiement\PaiementTypeController;
use App\Http\Controllers\Paiement\PaiementWorkflowController;
use App\Http\Controllers\Engagements\PieceJustificativeController;
use App\Http\Controllers\ExpressionBesoins\BesoinConversionController;
use App\Http\Controllers\ExpressionBesoins\BesoinExportController;
use App\Http\Controllers\ExpressionBesoins\BesoinWorkflowController;
use App\Http\Controllers\ExpressionBesoins\ConsolidationController as BesoinConsolidationController;
use App\Http\Controllers\ExpressionBesoins\DashboardController as ExpressionBesoinDashboardController;
use App\Http\Controllers\ExpressionBesoins\RapprochementController as BesoinRapprochementController;
use App\Http\Controllers\ExpressionBesoins\ExpressionBesoinController;
use App\Http\Controllers\ExpressionBesoins\PieceController as ExpressionBesoinPieceController;
use App\Http\Controllers\Ged\ArchiveController as GedArchiveController;
use App\Http\Controllers\Ged\CategorieController as GedCategorieController;
use App\Http\Controllers\Ged\ConservationController as GedConservationController;
use App\Http\Controllers\Ged\ConservationRuleController as GedConservationRuleController;
use App\Http\Controllers\Ged\DocumentAttachmentController as GedDocumentAttachmentController;
use App\Http\Controllers\Ged\DocumentController as GedDocumentController;
use App\Http\Controllers\Ged\DocumentWorkflowController as GedDocumentWorkflowController;
use App\Http\Controllers\Ged\DossierController as GedDossierController;
use App\Http\Controllers\Ged\GedDashboardController;
use App\Http\Controllers\Ged\GedExportController;
use App\Http\Controllers\Ged\SearchController as GedSearchController;
use App\Http\Controllers\Ged\ShareController as GedShareController;
use App\Http\Controllers\Ged\SignatureController as GedSignatureController;
use App\Http\Controllers\Ged\SpecialListController as GedSpecialListController;
use App\Http\Controllers\Ged\TypeDocumentController as GedTypeDocumentController;
use App\Http\Controllers\Ged\WorkflowController as GedWorkflowController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reports\ExportController;
use App\Http\Controllers\Reports\BudgetActivityJournalController;
use App\Http\Controllers\Reports\ExecutionReportController;
use App\Http\Controllers\Referentials\AgentController;
use App\Http\Controllers\Referentials\DepartementController;
use App\Http\Controllers\Referentials\DirectionController;
use App\Http\Controllers\Referentials\FournisseurController;
use App\Http\Controllers\Referentials\NatureDepenseController;
use App\Http\Controllers\Referentials\ServiceController;
use App\Http\Controllers\Referentials\SourceFinancementController;
use App\Http\Controllers\Rbm\ActiviteController;
use App\Http\Controllers\Rbm\AxeStrategiqueController;
use App\Http\Controllers\Rbm\CadreLogiqueController;
use App\Http\Controllers\Rbm\ChaineResultatController;
use App\Http\Controllers\Rbm\IndicateurController;
use App\Http\Controllers\Rbm\PilierController;
use App\Http\Controllers\Rbm\ProduitController;
use App\Http\Controllers\Rbm\RbmExportController;
use App\Http\Controllers\Rbm\RbmImportController;
use App\Http\Controllers\Rbm\PapImportController;
use App\Http\Controllers\Rbm\RbmOverviewController;
use App\Http\Controllers\Rbm\SousProduitController;
use App\Http\Controllers\Rbm\SourceVerificationController;
use App\Http\Controllers\Rbm\TacheController;
use App\Http\Controllers\Rbm\TypeIndicateurController;
use App\Http\Controllers\Rbm\UniteMesureController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('landing');
})->name('landing');

Route::middleware(['auth', 'verified', 'active'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/audit', [AuditOverviewController::class, 'index'])->name('audit.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('engagements/{engagement}/submit', [EngagementController::class, 'submit'])->name('engagements.submit');
    Route::post('engagements/{engagement}/workflow/decision', [EngagementController::class, 'workflowDecision'])->name('engagements.workflow.decision');
    Route::post('engagements/{engagement}/cancel-approved', [EngagementController::class, 'cancelApproved'])->name('engagements.cancel-approved');
    Route::post('engagements/{engagement}/cloturer', [EngagementController::class, 'cloturer'])->name('engagements.cloturer');
    Route::get('engagements/{engagement}/workflow', [EngagementController::class, 'workflow'])->name('engagements.workflow.show');
    Route::post('engagements/{engagement}/pieces-justificatives', [PieceJustificativeController::class, 'store'])
        ->name('engagements.pieces-justificatives.store')
        ->middleware('can:update,engagement');
    Route::get('engagements/{engagement}/pieces-justificatives/{pieceJustificative}/download', [PieceJustificativeController::class, 'download'])
        ->name('engagements.pieces-justificatives.download')
        ->middleware('can:view,engagement');
    Route::delete('engagements/{engagement}/pieces-justificatives/{pieceJustificative}', [PieceJustificativeController::class, 'destroy'])
        ->name('engagements.pieces-justificatives.destroy')
        ->middleware('can:update,engagement');
    Route::post('engagements/{engagement}/ged', [EngagementGedController::class, 'store'])
        ->name('engagements.ged.store')
        ->middleware('can:update,engagement');
    Route::post('engagements/{engagement}/transmettre-liquidation', [EngagementTransmissionController::class, 'transmit'])
        ->name('engagements.transmettre-liquidation');
    Route::get('engagements/{engagement}/liquidations/create', [LiquidationController::class, 'create'])->name('engagements.liquidations.create');
    Route::post('engagements/{engagement}/liquidations', [LiquidationController::class, 'store'])->name('engagements.liquidations.store');
    Route::get('liquidations/{liquidation}/ordonnancements/create', [OrdonnancementController::class, 'create'])->name('liquidations.ordonnancements.create');
    Route::post('liquidations/{liquidation}/ordonnancements', [OrdonnancementController::class, 'store'])->name('liquidations.ordonnancements.store');
    Route::get('ordonnancements/{ordonnancement}/paiements/create', [PaiementController::class, 'create'])->name('ordonnancements.paiements.create');
    Route::post('ordonnancements/{ordonnancement}/paiements', [PaiementController::class, 'store'])->name('ordonnancements.paiements.store');

    /*
     * Module Liquidation ERP. Routes statiques (create / types) déclarées avant
     * liquidations/{liquidation} pour éviter la capture du segment.
     */
    Route::post('engagements/{engagement}/generer-liquidation', [ErpLiquidationController::class, 'generate'])->name('engagements.generer-liquidation');

    Route::get('liquidations/types', [LiquidationTypeController::class, 'index'])->name('liquidations.types.index');
    Route::post('liquidations/types', [LiquidationTypeController::class, 'store'])->name('liquidations.types.store');
    Route::put('liquidations/types/{liquidationType}', [LiquidationTypeController::class, 'update'])->name('liquidations.types.update');
    Route::delete('liquidations/types/{liquidationType}', [LiquidationTypeController::class, 'destroy'])->name('liquidations.types.destroy');

    Route::get('liquidations/tableau-de-bord', [LiquidationDashboardController::class, 'index'])->name('liquidations.dashboard');
    Route::get('liquidations/etats', [LiquidationReportController::class, 'index'])->name('liquidations.reports.index');
    Route::get('liquidations/etats/{rapport}/{format}', [LiquidationReportController::class, 'show'])
        ->where('format', 'pdf|xlsx|csv')->name('liquidations.reports.show');

    Route::get('liquidations', [ErpLiquidationController::class, 'index'])->name('liquidations.index');
    Route::get('liquidations/create', [ErpLiquidationController::class, 'create'])->name('liquidations.create');
    Route::post('liquidations', [ErpLiquidationController::class, 'store'])->name('liquidations.store');
    Route::get('liquidations/{liquidation}', [ErpLiquidationController::class, 'show'])->name('liquidations.show');
    Route::get('liquidations/{liquidation}/edit', [ErpLiquidationController::class, 'edit'])->name('liquidations.edit');
    Route::put('liquidations/{liquidation}', [ErpLiquidationController::class, 'update'])->name('liquidations.update');
    Route::delete('liquidations/{liquidation}', [ErpLiquidationController::class, 'destroy'])->name('liquidations.destroy');
    Route::post('liquidations/{liquidation}/certifier', [ErpLiquidationController::class, 'certifier'])->name('liquidations.certifier');

    Route::get('liquidations/{liquidation}/certificat', [LiquidationDocumentController::class, 'certificat'])->name('liquidations.certificat');
    Route::get('liquidations/{liquidation}/fiche', [LiquidationDocumentController::class, 'fiche'])->name('liquidations.fiche');
    Route::get('liquidations/{liquidation}/bordereau', [LiquidationDocumentController::class, 'bordereau'])->name('liquidations.bordereau');

    Route::post('liquidations/{liquidation}/service-fait', [LiquidationServiceFaitController::class, 'store'])->name('liquidations.service-fait.store');
    Route::post('liquidations/{liquidation}/service-fait/valider', [LiquidationServiceFaitController::class, 'valider'])->name('liquidations.service-fait.valider');
    Route::post('liquidations/{liquidation}/service-fait/lever-reserves', [LiquidationServiceFaitController::class, 'leverReserves'])->name('liquidations.service-fait.lever-reserves');
    Route::post('liquidations/{liquidation}/receptions', [LiquidationServiceFaitController::class, 'storeReception'])->name('liquidations.receptions.store');

    Route::post('liquidations/{liquidation}/soumettre', [LiquidationWorkflowController::class, 'submit'])->name('liquidations.soumettre');
    Route::post('liquidations/{liquidation}/decision', [LiquidationWorkflowController::class, 'decision'])->name('liquidations.decision');

    Route::post('liquidations/{liquidation}/documents', [LiquidationGedController::class, 'store'])->name('liquidations.documents.store');
    Route::post('liquidations/{liquidation}/heriter-documents', [LiquidationGedController::class, 'heriter'])->name('liquidations.heriter-documents');

    Route::post('liquidations/{liquidation}/transmettre-ordonnancement', [LiquidationTransmissionController::class, 'transmit'])->name('liquidations.transmettre-ordonnancement');

    /*
     * Module Ordonnancement ERP. Routes statiques (create / types) avant
     * ordonnancements/{ordonnancement} pour éviter la capture du segment.
     */
    Route::post('liquidations/{liquidation}/generer-ordonnancement', [ErpOrdonnancementController::class, 'generate'])->name('liquidations.generer-ordonnancement');

    Route::get('ordonnancements/types', [OrdonnancementTypeController::class, 'index'])->name('ordonnancements.types.index');
    Route::post('ordonnancements/types', [OrdonnancementTypeController::class, 'store'])->name('ordonnancements.types.store');
    Route::put('ordonnancements/types/{ordonnancementType}', [OrdonnancementTypeController::class, 'update'])->name('ordonnancements.types.update');
    Route::delete('ordonnancements/types/{ordonnancementType}', [OrdonnancementTypeController::class, 'destroy'])->name('ordonnancements.types.destroy');

    Route::get('ordonnancements/tableau-de-bord', [OrdonnancementDashboardController::class, 'index'])->name('ordonnancements.dashboard');
    Route::get('ordonnancements/etats', [OrdonnancementReportController::class, 'index'])->name('ordonnancements.reports.index');
    Route::get('ordonnancements/etats/{rapport}/{format}', [OrdonnancementReportController::class, 'show'])
        ->where('format', 'pdf|xlsx|csv')->name('ordonnancements.reports.show');

    Route::get('ordonnancements', [ErpOrdonnancementController::class, 'index'])->name('ordonnancements.index');
    Route::get('ordonnancements/create', [ErpOrdonnancementController::class, 'create'])->name('ordonnancements.create');
    Route::post('ordonnancements', [ErpOrdonnancementController::class, 'store'])->name('ordonnancements.store');
    Route::get('ordonnancements/{ordonnancement}', [ErpOrdonnancementController::class, 'show'])->name('ordonnancements.show');
    Route::get('ordonnancements/{ordonnancement}/edit', [ErpOrdonnancementController::class, 'edit'])->name('ordonnancements.edit');
    Route::put('ordonnancements/{ordonnancement}', [ErpOrdonnancementController::class, 'update'])->name('ordonnancements.update');
    Route::delete('ordonnancements/{ordonnancement}', [ErpOrdonnancementController::class, 'destroy'])->name('ordonnancements.destroy');
    Route::post('ordonnancements/{ordonnancement}/mandater', [ErpOrdonnancementController::class, 'mandater'])->name('ordonnancements.mandater');

    Route::get('ordonnancements/{ordonnancement}/fiche', [OrdonnancementDocumentController::class, 'fiche'])->name('ordonnancements.fiche');
    Route::get('ordonnancements/{ordonnancement}/bordereau', [OrdonnancementDocumentController::class, 'bordereau'])->name('ordonnancements.bordereau');
    Route::get('ordonnancements/{ordonnancement}/mandat', [OrdonnancementDocumentController::class, 'mandat'])->name('ordonnancements.mandat');
    Route::get('ordonnancements/{ordonnancement}/ordre-paiement', [OrdonnancementDocumentController::class, 'ordrePaiement'])->name('ordonnancements.ordre-paiement');

    Route::post('ordonnancements/{ordonnancement}/soumettre', [OrdonnancementWorkflowController::class, 'submit'])->name('ordonnancements.soumettre');
    Route::post('ordonnancements/{ordonnancement}/decision', [OrdonnancementWorkflowController::class, 'decision'])->name('ordonnancements.decision');

    Route::post('ordonnancements/{ordonnancement}/documents', [OrdonnancementGedController::class, 'store'])->name('ordonnancements.documents.store');
    Route::post('ordonnancements/{ordonnancement}/heriter-documents', [OrdonnancementGedController::class, 'heriter'])->name('ordonnancements.heriter-documents');

    Route::post('ordonnancements/{ordonnancement}/transmettre-paiement', [OrdonnancementTransmissionController::class, 'transmit'])->name('ordonnancements.transmettre-paiement');

    /*
     * Module Paiement ERP. Routes statiques (create / types / banques) avant
     * paiements/{paiement} pour éviter la capture du segment.
     */
    Route::post('ordonnancements/{ordonnancement}/generer-paiement', [ErpPaiementController::class, 'generate'])->name('ordonnancements.generer-paiement');

    Route::get('paiements/types', [PaiementTypeController::class, 'index'])->name('paiements.types.index');
    Route::post('paiements/types', [PaiementTypeController::class, 'store'])->name('paiements.types.store');
    Route::put('paiements/types/{paiementType}', [PaiementTypeController::class, 'update'])->name('paiements.types.update');
    Route::delete('paiements/types/{paiementType}', [PaiementTypeController::class, 'destroy'])->name('paiements.types.destroy');

    Route::get('paiements/banques', [PaiementBanqueController::class, 'index'])->name('paiements.banques.index');
    Route::post('paiements/banques', [PaiementBanqueController::class, 'store'])->name('paiements.banques.store');
    Route::put('paiements/banques/{paiementBanque}', [PaiementBanqueController::class, 'update'])->name('paiements.banques.update');
    Route::delete('paiements/banques/{paiementBanque}', [PaiementBanqueController::class, 'destroy'])->name('paiements.banques.destroy');

    Route::get('paiements/tableau-de-bord', [PaiementDashboardController::class, 'index'])->name('paiements.dashboard');
    Route::get('paiements/etats', [PaiementReportController::class, 'index'])->name('paiements.reports.index');
    Route::get('paiements/etats/{rapport}/{format}', [PaiementReportController::class, 'show'])
        ->where('format', 'pdf|xlsx|csv')->name('paiements.reports.show');

    Route::get('paiements', [ErpPaiementController::class, 'index'])->name('paiements.index');
    Route::get('paiements/create', [ErpPaiementController::class, 'create'])->name('paiements.create');
    Route::post('paiements', [ErpPaiementController::class, 'store'])->name('paiements.store');
    Route::get('paiements/{paiement}', [ErpPaiementController::class, 'show'])->name('paiements.show');
    Route::get('paiements/{paiement}/edit', [ErpPaiementController::class, 'edit'])->name('paiements.edit');
    Route::put('paiements/{paiement}', [ErpPaiementController::class, 'update'])->name('paiements.update');
    Route::delete('paiements/{paiement}', [ErpPaiementController::class, 'destroy'])->name('paiements.destroy');

    Route::post('paiements/{paiement}/programmer', [PaiementWorkflowController::class, 'programmer'])->name('paiements.programmer');
    Route::post('paiements/{paiement}/decision', [PaiementWorkflowController::class, 'decision'])->name('paiements.decision');
    Route::post('paiements/{paiement}/transmettre-banque', [ErpPaiementController::class, 'transmettreBanque'])->name('paiements.transmettre-banque');
    Route::post('paiements/{paiement}/executer', [ErpPaiementController::class, 'executer'])->name('paiements.executer');
    Route::post('paiements/{paiement}/rapprocher', [ErpPaiementController::class, 'rapprocher'])->name('paiements.rapprocher');
    Route::post('paiements/{paiement}/cloturer', [ErpPaiementController::class, 'cloturer'])->name('paiements.cloturer');
    Route::post('paiements/{paiement}/rejet', [PaiementRejetController::class, 'store'])->name('paiements.rejet.store');

    Route::post('paiements/{paiement}/documents', [PaiementGedController::class, 'store'])->name('paiements.documents.store');
    Route::post('paiements/{paiement}/heriter-documents', [PaiementGedController::class, 'heriter'])->name('paiements.heriter-documents');

    Route::get('paiements/{paiement}/ordre-virement', [PaiementDocumentController::class, 'ordreVirement'])->name('paiements.ordre-virement');
    Route::get('paiements/{paiement}/bordereau', [PaiementDocumentController::class, 'bordereau'])->name('paiements.bordereau');
    Route::get('paiements/{paiement}/avis', [PaiementDocumentController::class, 'avis'])->name('paiements.avis');
    Route::get('paiements/{paiement}/recu', [PaiementDocumentController::class, 'recu'])->name('paiements.recu');
    Route::get('paiements/{paiement}/quittance', [PaiementDocumentController::class, 'quittance'])->name('paiements.quittance');

    Route::middleware('permission:execution.liquidation|execution.payment')
        ->prefix('execution')
        ->name('execution.')
        ->group(function (): void {
            Route::get('/', [ExecutionDashboardController::class, 'index'])->name('dashboard');

            Route::get('liquidations/{liquidation}', [LiquidationController::class, 'show'])->name('liquidations.show');
            Route::get('liquidations/{liquidation}/edit', [LiquidationController::class, 'edit'])->name('liquidations.edit');
            Route::put('liquidations/{liquidation}', [LiquidationController::class, 'update'])->name('liquidations.update');
            Route::post('liquidations/{liquidation}/cancel', [LiquidationController::class, 'cancel'])->name('liquidations.cancel');

            Route::get('ordonnancements/{ordonnancement}', [OrdonnancementController::class, 'show'])->name('ordonnancements.show');
            Route::get('ordonnancements/{ordonnancement}/edit', [OrdonnancementController::class, 'edit'])->name('ordonnancements.edit');
            Route::put('ordonnancements/{ordonnancement}', [OrdonnancementController::class, 'update'])->name('ordonnancements.update');
            Route::post('ordonnancements/{ordonnancement}/cancel', [OrdonnancementController::class, 'cancel'])->name('ordonnancements.cancel');

            Route::get('paiements/{paiement}', [PaiementController::class, 'show'])->name('paiements.show');
            Route::get('paiements/{paiement}/edit', [PaiementController::class, 'edit'])->name('paiements.edit');
            Route::put('paiements/{paiement}', [PaiementController::class, 'update'])->name('paiements.update');
            Route::post('paiements/{paiement}/cancel', [PaiementController::class, 'cancel'])->name('paiements.cancel');
        });

    // Tableau de bord & documents ERP (définis avant la resource pour éviter la capture de {engagement}).
    Route::get('engagements/tableau-de-bord', [EngagementDashboardController::class, 'index'])->name('engagements.dashboard');
    Route::get('engagements/documents/registre/pdf', [EngagementDocumentController::class, 'registrePdf'])->name('engagements.documents.registre.pdf');
    Route::get('engagements/documents/registre/excel', [EngagementDocumentController::class, 'registreExcel'])->name('engagements.documents.registre.excel');
    Route::get('engagements/documents/bordereau/pdf', [EngagementDocumentController::class, 'bordereauPdf'])->name('engagements.documents.bordereau.pdf');
    Route::get('engagements/documents/credits/pdf', [EngagementDocumentController::class, 'creditsReservesPdf'])->name('engagements.documents.credits.pdf');
    Route::get('engagements/documents/credits/excel', [EngagementDocumentController::class, 'creditsReservesExcel'])->name('engagements.documents.credits.excel');
    Route::get('engagements/{engagement}/certificat', [EngagementDocumentController::class, 'certificat'])->name('engagements.documents.certificat');
    Route::resource('engagements', EngagementController::class);

    Route::get('reports/execution', [ExecutionReportController::class, 'index'])->name('reports.execution.index');
    Route::get('reports/activity-budget', [BudgetActivityJournalController::class, 'index'])->name('reports.activity.index');
    Route::get('reports/activity-budget/export/csv', [BudgetActivityJournalController::class, 'exportCsv'])->name('reports.activity.export.csv');
    Route::get('engagements/{engagement}/export/pdf', [ExportController::class, 'engagementPdf'])
        ->name('engagements.export.pdf');
    Route::get('engagements/export/excel', [ExportController::class, 'engagementsExcel'])
        ->name('engagements.export.excel');
    Route::get('engagements/export/csv', [ExportController::class, 'engagementsCsv'])
        ->name('engagements.export.csv');
});

// Référentiel des types d'engagement (architecture polymorphe).
Route::middleware(['auth', 'verified', 'active', 'permission:engagement.admin|engagement.view'])
    ->group(function () {
        Route::resource('engagement-types', EngagementTypeController::class)
            ->parameters(['engagement-types' => 'engagement_type'])
            ->names('engagements.types')
            ->except('show');
    });

Route::middleware(['auth', 'verified', 'active', 'permission:users.manage'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/timeline/export/csv', [AdminUserController::class, 'exportTimelineCsv'])->name('users.timeline.export.csv');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    });

Route::middleware(['auth', 'verified', 'active', 'permission:roles.manage'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/roles', [AdminRoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/{role}/edit', [AdminRoleController::class, 'edit'])->name('roles.edit');
        Route::patch('/roles/{role}', [AdminRoleController::class, 'update'])->name('roles.update');
    });

Route::middleware(['auth', 'verified', 'active', 'permission:referentials.manage'])
    ->prefix('referentials')
    ->name('referentials.')
    ->group(function () {
        Route::resource('departements', DepartementController::class);
        Route::resource('directions', DirectionController::class);
        Route::resource('services', ServiceController::class);
        Route::resource('natures-depenses', NatureDepenseController::class)->parameters([
            'natures-depenses' => 'nature_depense',
        ]);
        Route::resource('sources-financement', SourceFinancementController::class)->parameters([
            'sources-financement' => 'source_financement',
        ]);
        Route::resource('fournisseurs', FournisseurController::class);
        Route::resource('agents', AgentController::class);
    });

Route::middleware(['auth', 'verified', 'active', 'permission:rbm.view|rbm.admin|rbm.create|rbm.update'])
    ->prefix('rbm')
    ->name('rbm.')
    ->group(function () {
        Route::get('overview/tree', [RbmOverviewController::class, 'tree'])->name('overview.tree');
        Route::get('overview/matrix', [RbmOverviewController::class, 'matrix'])->name('overview.matrix');
        Route::get('overview/synthesis', [RbmOverviewController::class, 'synthesis'])->name('overview.synthesis');

        Route::get('export/catalogue/{entity}/excel', [RbmExportController::class, 'catalogueExcel'])->name('export.catalogue-excel');
        Route::get('export/matrice/excel', [RbmExportController::class, 'matriceExcel'])->name('export.matrice-excel');
        Route::get('export/synthesis/pdf', [RbmExportController::class, 'synthesisPdf'])->name('export.synthesis-pdf');

        Route::middleware('permission:rbm.import|rbm.admin')->group(function () {
            Route::get('import', [RbmImportController::class, 'index'])->name('import.index');
            Route::get('import/template', [RbmImportController::class, 'downloadTemplate'])->name('import.template');
            Route::post('import', [RbmImportController::class, 'store'])->name('import.store');
        });

        Route::post('piliers/{pilier}/restore', [PilierController::class, 'restore'])->name('piliers.restore');

        Route::resource('piliers', PilierController::class);
        Route::resource('axes-strategiques', AxeStrategiqueController::class)->parameters([
            'axes-strategiques' => 'axe_strategique',
        ]);
        Route::resource('produits', ProduitController::class);
        Route::resource('sous-produits', SousProduitController::class)->parameters([
            'sous-produits' => 'sous_produit',
        ]);
        Route::resource('activites', ActiviteController::class);
        Route::resource('taches', TacheController::class);
        Route::resource('types-indicateurs', TypeIndicateurController::class)->parameters([
            'types-indicateurs' => 'type_indicateur',
        ]);
        Route::resource('unites-mesure', UniteMesureController::class)->parameters([
            'unites-mesure' => 'unite_mesure',
        ]);
        Route::resource('sources-verification', SourceVerificationController::class)->parameters([
            'sources-verification' => 'source_verification',
        ]);
        Route::resource('indicateurs', IndicateurController::class);
        Route::resource('cadres-logiques', CadreLogiqueController::class)->parameters([
            'cadres-logiques' => 'cadre_logique',
        ]);
        Route::resource('chaines-resultats', ChaineResultatController::class)->parameters([
            'chaines-resultats' => 'chaine_resultat',
        ]);
    });

Route::middleware(['auth', 'verified', 'active', 'permission:budget.virement'])
    ->prefix('budget/virements')
    ->name('budget.virements.')
    ->group(function () {
        Route::get('/', [VirementCreditController::class, 'index'])->name('index');
        Route::post('/', [VirementCreditController::class, 'store'])->name('store');
        Route::post('{virement}/approve', [VirementCreditController::class, 'approve'])->name('approve');
        Route::post('{virement}/reject', [VirementCreditController::class, 'reject'])->name('reject');
    });

Route::middleware(['auth', 'verified', 'active', 'permission:budget.alertes'])
    ->prefix('budget/alertes')
    ->name('budget.alertes.')
    ->group(function () {
        Route::get('/', [BudgetAlerteController::class, 'historique'])->name('historique');
        Route::post('{alerte}/traiter', [BudgetAlerteController::class, 'traiter'])->name('traiter');
        Route::post('{alerte}/restaurer', [BudgetAlerteController::class, 'restaurer'])->name('restaurer');
    });

Route::middleware(['auth', 'verified', 'active', 'permission:budget.view|budget.import.run|budget.replace|budget.restore'])
    ->prefix('budget/nomenclature')
    ->name('budget.nomenclature.')
    ->group(function () {
        Route::get('/', [BudgetNomenclatureImportController::class, 'index'])->name('index');
        Route::get('template', [BudgetNomenclatureImportController::class, 'downloadTemplate'])->name('template');
        Route::get('template/csv', [BudgetNomenclatureImportController::class, 'downloadTemplateCsv'])->name('template.csv');
        Route::get('history', [BudgetNomenclatureImportController::class, 'history'])->name('history');
        Route::get('synthese', [BudgetNomenclatureImportController::class, 'synthese'])->name('synthese');
        Route::get('consolide', [BudgetNomenclatureImportController::class, 'consolide'])->name('consolide');
        Route::get('consolide/export/excel', [BudgetNomenclatureImportController::class, 'exportConsolideExcel'])->name('consolide.export.excel');
        Route::get('consolide/export/pdf', [BudgetNomenclatureImportController::class, 'exportConsolidePdf'])->name('consolide.export.pdf');
        Route::post('synchronize', [BudgetNomenclatureImportController::class, 'synchronize'])->name('synchronize');
        Route::get('execution/export/excel', [BudgetNomenclatureImportController::class, 'exportExecutionExcel'])->name('execution.export.excel');
        Route::get('execution/export/pdf', [BudgetNomenclatureImportController::class, 'exportExecutionPdf'])->name('execution.export.pdf');
        Route::post('simulate', [BudgetNomenclatureImportController::class, 'simulate'])->name('simulate');
        Route::post('/', [BudgetNomenclatureImportController::class, 'store'])->name('store');
        Route::get('{budgetImport}', [BudgetNomenclatureImportController::class, 'show'])->name('show');
        Route::post('{budgetImport}/restore', [BudgetNomenclatureImportController::class, 'restore'])->name('restore');
    });

Route::middleware(['auth', 'verified', 'active', 'permission:pap.view|pap.import|pap.replace|pap.restore|rbm.admin'])
    ->prefix('rbm/pap-import')
    ->name('rbm.pap-import.')
    ->group(function () {
        Route::get('/', [PapImportController::class, 'index'])->name('index');
        Route::get('template', [PapImportController::class, 'downloadTemplate'])->name('template');
        Route::get('template/csv', [PapImportController::class, 'downloadTemplateCsv'])->name('template.csv');
        Route::get('history', [PapImportController::class, 'history'])->name('history');
        Route::get('programmation', [PapImportController::class, 'programmation'])->name('programmation');
        Route::get('programmation/export/excel', [PapImportController::class, 'exportProgrammationExcel'])->name('programmation.export.excel');
        Route::get('programmation/export/pdf', [PapImportController::class, 'exportProgrammationPdf'])->name('programmation.export.pdf');
        Route::get('programmation/export/source-financement', [PapImportController::class, 'exportSourceFinancement'])->name('programmation.export.source');
        Route::post('simulate', [PapImportController::class, 'simulate'])->name('simulate');
        Route::post('/', [PapImportController::class, 'store'])->name('store');
        Route::get('{papImport}', [PapImportController::class, 'show'])->name('show');
        Route::post('{papImport}/restore', [PapImportController::class, 'restore'])->name('restore');
    });

Route::prefix('budget')
    ->name('budget.')
    ->middleware(['auth', 'verified', 'active'])
    ->group(function () {
        Route::middleware('permission:exercises.manage')->group(function () {
            Route::resource('exercices', ExerciceBudgetaireController::class)
                ->parameters(['exercices' => 'exercice_budgetaire']);
            Route::resource('lignes', LigneBudgetaireController::class)
                ->parameters(['lignes' => 'ligne_budgetaire']);
        });

        Route::middleware('permission:budget.view|exercises.manage|reports.view')->group(function () {
            Route::get('situation', [BudgetSituationController::class, 'index'])->name('situation.index');
            Route::get('situation/export', [BudgetSituationController::class, 'exportCsv'])->name('situation.export');
        });

        Route::middleware('permission:budget.import')->group(function () {
            Route::get('imports/template', [ImportBudgetController::class, 'downloadTemplate'])->name('imports.template');
            Route::post('imports', [ImportBudgetController::class, 'store'])->name('imports.store');
            Route::get('imports/{import}', [ImportBudgetController::class, 'show'])->name('imports.show');
            Route::post('imports/{import}/validate', [ImportBudgetController::class, 'validateImport'])->name('imports.validate');
            Route::post('imports/{import}/cancel', [ImportBudgetController::class, 'cancel'])->name('imports.cancel');
        });

        Route::middleware('permission:parameters.manage')->group(function () {
            Route::resource('parametres', ParametreControleController::class)
                ->parameters(['parametres' => 'parametre_controle']);
        });
    });

Route::middleware(['auth', 'verified', 'active', 'permission:recettes.view'])
    ->prefix('recettes')
    ->name('recettes.')
    ->group(function () {
        Route::get('/', [RecetteDashboardController::class, 'index'])->name('dashboard');
        Route::middleware('permission:recettes.create')->group(function () {
            Route::get('perceptions/create', [RecettePerceptionController::class, 'create'])->name('perceptions.create');
            Route::post('perceptions', [RecettePerceptionController::class, 'store'])->name('perceptions.store');
        });
        Route::get('perceptions/{perception}', [RecettePerceptionController::class, 'show'])->name('perceptions.show');
        Route::middleware('permission:recettes.validate')->group(function () {
            Route::post('perceptions/{perception}/validate', [RecettePerceptionController::class, 'validatePerception'])->name('perceptions.validate');
            Route::post('perceptions/{perception}/cancel', [RecettePerceptionController::class, 'cancel'])->name('perceptions.cancel');
        });
    });

Route::middleware(['auth', 'verified', 'active', 'permission:suivi_physique.view'])
    ->prefix('suivi-physique')
    ->name('suivi-physique.')
    ->group(function () {
        Route::get('/', [SuiviPhysiqueController::class, 'index'])->name('index');
        Route::middleware('permission:suivi_physique.create')->group(function () {
            Route::get('create', [SuiviPhysiqueController::class, 'create'])->name('create');
            Route::post('/', [SuiviPhysiqueController::class, 'store'])->name('store');
        });
        Route::get('{suivi_physique}', [SuiviPhysiqueController::class, 'show'])->name('show');
        Route::middleware('permission:suivi_physique.validate')->post('{suivi_physique}/validate', [SuiviPhysiqueController::class, 'validateRealisation'])->name('validate');
    });

Route::middleware(['auth', 'verified', 'active', 'permission:suivi_evaluation.view'])
    ->prefix('suivi-evaluation')
    ->name('suivi-evaluation.')
    ->group(function () {
        Route::get('/', [SuiviEvaluationController::class, 'index'])->name('index');
        Route::middleware('permission:suivi_evaluation.create')->group(function () {
            Route::get('create', [SuiviEvaluationController::class, 'create'])->name('create');
            Route::post('/', [SuiviEvaluationController::class, 'store'])->name('store');
        });
        Route::get('{suivi_evaluation}', [SuiviEvaluationController::class, 'show'])->name('show');
        Route::middleware('permission:suivi_evaluation.validate')->post('{suivi_evaluation}/validate', [SuiviEvaluationController::class, 'validateRealisation'])->name('validate');
    });

/*
|--------------------------------------------------------------------------
| Gestion Électronique des Documents (GED)
|--------------------------------------------------------------------------
| Module documentaire institutionnel. Les sous-rubriques (documents, dépôt,
| classement, archives, signatures, etc.) seront ajoutées de façon
| incrémentale. Le tableau de bord constitue le point d'entrée de la Phase 1.
*/
Route::middleware(['auth', 'verified', 'active', 'permission:ged.view|ged.admin'])
    ->prefix('ged')
    ->name('ged.')
    ->group(function () {
        Route::get('/', [GedDashboardController::class, 'index'])->name('dashboard');

        // Documents : dépôt, consultation, versioning, téléchargement sécurisé.
        Route::get('documents/{document}/download', [GedDocumentController::class, 'download'])
            ->name('documents.download');
        Route::post('documents/{document}/versions', [GedDocumentController::class, 'storeVersion'])
            ->name('documents.versions.store');
        Route::resource('documents', GedDocumentController::class);

        // Plan de classement documentaire.
        Route::resource('categories', GedCategorieController::class)
            ->parameters(['categories' => 'categorie'])->except('show');
        Route::resource('types-documents', GedTypeDocumentController::class)
            ->parameters(['types-documents' => 'type_document'])->except('show');
        Route::resource('dossiers', GedDossierController::class)->except('show');

        // Circuit de validation : exécution sur un document.
        Route::post('documents/{document}/workflow/submit', [GedDocumentWorkflowController::class, 'submit'])
            ->name('documents.workflow.submit');
        Route::post('documents/{document}/workflow/decide', [GedDocumentWorkflowController::class, 'decide'])
            ->name('documents.workflow.decide');

        // Signature électronique.
        Route::post('documents/{document}/signatures', [GedSignatureController::class, 'request'])
            ->name('documents.signatures.request');
        Route::post('signatures/{signature}/sign', [GedSignatureController::class, 'sign'])->name('signatures.sign');
        Route::post('signatures/{signature}/reject', [GedSignatureController::class, 'reject'])->name('signatures.reject');
        Route::get('signatures/{signature}/verify', [GedSignatureController::class, 'verify'])->name('signatures.verify');
        Route::get('signatures/{signature}/certificate', [GedSignatureController::class, 'certificate'])->name('signatures.certificate');

        // Archivage électronique.
        Route::get('archives', [GedArchiveController::class, 'index'])->name('archives.index');
        Route::post('documents/{document}/archive', [GedArchiveController::class, 'archive'])->name('documents.archive');
        Route::post('documents/{document}/restore-archive', [GedArchiveController::class, 'restore'])->name('documents.restore-archive');

        // Conservation numérique.
        Route::get('conservation', [GedConservationController::class, 'index'])->name('conservation.index');
        Route::post('documents/{document}/expirer', [GedConservationController::class, 'expirer'])->name('documents.expirer');
        Route::resource('conservation-rules', GedConservationRuleController::class)
            ->parameters(['conservation-rules' => 'conservation_rule'])->except('show');

        // Circuits de validation (modèles).
        Route::resource('workflows', GedWorkflowController::class)->except('show');

        // Recherche avancée.
        Route::get('recherche', [GedSearchController::class, 'index'])->name('search.index');

        // Exports / registres.
        Route::get('exports/documentaire/pdf', [GedExportController::class, 'documentairePdf'])->name('exports.documentaire.pdf');
        Route::get('exports/documentaire/excel', [GedExportController::class, 'documentaireExcel'])->name('exports.documentaire.excel');
        Route::get('exports/documentaire/csv', [GedExportController::class, 'documentaireCsv'])->name('exports.documentaire.csv');
        Route::get('exports/archives/pdf', [GedExportController::class, 'archivesPdf'])->name('exports.archives.pdf');
        Route::get('exports/signatures/pdf', [GedExportController::class, 'signaturesPdf'])->name('exports.signatures.pdf');

        // Partages.
        Route::get('partages', [GedShareController::class, 'index'])->name('partages.index');
        Route::post('documents/{document}/partages', [GedShareController::class, 'store'])->name('documents.share');
        Route::delete('partages/{partage}', [GedShareController::class, 'destroy'])->name('partages.destroy');

        // Listes spécialisées.
        Route::get('confidentiels', [GedSpecialListController::class, 'confidentiels'])->name('confidentiels');
        Route::get('expires', [GedSpecialListController::class, 'expires'])->name('expires');

        // Rattachement polymorphe aux modules métier.
        Route::post('documents/{document}/attachments', [GedDocumentAttachmentController::class, 'store'])->name('documents.attachments.store');
        Route::delete('attachments/{attachment}', [GedDocumentAttachmentController::class, 'destroy'])->name('attachments.destroy');
    });

/*
|--------------------------------------------------------------------------
| Expression des Besoins (PAP & Hors PAP)
|--------------------------------------------------------------------------
| Module d'expression unifiée des besoins. Les sous-rubriques (saisie en
| onglets, workflow, conversion en engagement, rapports) sont ajoutées de
| façon incrémentale. Le tableau de bord est le point d'entrée de la Phase 1.
*/
Route::middleware(['auth', 'verified', 'active', 'permission:expression_besoins.view|expression_besoins.admin'])
    ->prefix('expression-besoins')
    ->name('expression-besoins.')
    ->group(function () {
        Route::get('/', [ExpressionBesoinDashboardController::class, 'index'])->name('dashboard');
        Route::get('consolidation', [BesoinConsolidationController::class, 'index'])->name('consolidation');
        Route::get('consolidation/export/excel', [BesoinConsolidationController::class, 'excel'])->name('consolidation.excel');
        Route::get('consolidation/export/pdf', [BesoinConsolidationController::class, 'pdf'])->name('consolidation.pdf');
        Route::get('rapprochement', [BesoinRapprochementController::class, 'index'])->name('rapprochement');
        Route::get('rapprochement/export/excel', [BesoinRapprochementController::class, 'excel'])->name('rapprochement.excel');

        // Pièces justificatives (dépôt automatique dans la GED).
        Route::post('besoins/{expression_besoin}/pieces', [ExpressionBesoinPieceController::class, 'store'])
            ->name('besoins.pieces.store');
        Route::delete('besoins/{expression_besoin}/pieces/{attachment}', [ExpressionBesoinPieceController::class, 'destroy'])
            ->name('besoins.pieces.destroy');

        // Circuit de validation.
        Route::post('besoins/{expression_besoin}/workflow/submit', [BesoinWorkflowController::class, 'submit'])
            ->name('besoins.workflow.submit');
        Route::post('besoins/{expression_besoin}/workflow/decide', [BesoinWorkflowController::class, 'decide'])
            ->name('besoins.workflow.decide');
        Route::post('besoins/{expression_besoin}/workflow/cancel', [BesoinWorkflowController::class, 'cancel'])
            ->name('besoins.workflow.cancel');

        // Conversion en engagement.
        Route::post('besoins/{expression_besoin}/convert', [BesoinConversionController::class, 'convert'])
            ->name('besoins.convert');

        // Révision contrôlée d'un besoin validé.
        Route::post('besoins/{expression_besoin}/revise', [ExpressionBesoinController::class, 'revise'])
            ->name('besoins.revise');

        // Rapports / registres.
        Route::get('besoins/export/registre/pdf', [BesoinExportController::class, 'registrePdf'])->name('besoins.export.registre.pdf');
        Route::get('besoins/export/registre/excel', [BesoinExportController::class, 'registreExcel'])->name('besoins.export.registre.excel');
        Route::get('besoins/export/etat-pap/pdf', [BesoinExportController::class, 'etatPapPdf'])->name('besoins.export.etat-pap.pdf');
        Route::get('besoins/export/etat-hors-pap/pdf', [BesoinExportController::class, 'etatHorsPapPdf'])->name('besoins.export.etat-hors-pap.pdf');
        Route::get('besoins/export/etat-structure/pdf', [BesoinExportController::class, 'etatStructurePdf'])->name('besoins.export.etat-structure.pdf');
        Route::get('besoins/export/etat-categorie/pdf', [BesoinExportController::class, 'etatCategoriePdf'])->name('besoins.export.etat-categorie.pdf');
        Route::get('besoins/export/bordereau/pdf', [BesoinExportController::class, 'bordereauPdf'])->name('besoins.export.bordereau.pdf');
        Route::get('besoins/export/etat-pieces/pdf', [BesoinExportController::class, 'etatPiecesPdf'])->name('besoins.export.etat-pieces.pdf');
        Route::get('besoins/{expression_besoin}/export/pdf', [BesoinExportController::class, 'fichePdf'])->name('besoins.export.pdf');
        Route::get('besoins/{expression_besoin}/export/historique/pdf', [BesoinExportController::class, 'historiquePdf'])->name('besoins.export.historique.pdf');

        // Archives (besoins supprimés — lecture seule).
        Route::get('besoins/archives', [ExpressionBesoinController::class, 'archives'])->name('besoins.archives');

        // Fiche d'expression du besoin (8 onglets).
        Route::resource('besoins', ExpressionBesoinController::class)
            ->parameters(['besoins' => 'expression_besoin']);
    });

require __DIR__.'/auth.php';
