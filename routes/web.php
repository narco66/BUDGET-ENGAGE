<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Audit\AuditOverviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Budget\ExerciceBudgetaireController;
use App\Http\Controllers\Budget\LigneBudgetaireController;
use App\Http\Controllers\Budget\ParametreControleController;
use App\Http\Controllers\Engagements\EngagementController;
use App\Http\Controllers\Execution\LiquidationController;
use App\Http\Controllers\Execution\OrdonnancementController;
use App\Http\Controllers\Execution\PaiementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reports\BudgetActivityJournalController;
use App\Http\Controllers\Reports\ExecutionReportController;
use App\Http\Controllers\Referentials\AgentController;
use App\Http\Controllers\Referentials\DepartementController;
use App\Http\Controllers\Referentials\DirectionController;
use App\Http\Controllers\Referentials\FournisseurController;
use App\Http\Controllers\Referentials\NatureDepenseController;
use App\Http\Controllers\Referentials\ServiceController;
use App\Http\Controllers\Referentials\SourceFinancementController;
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
    Route::get('engagements/{engagement}/liquidations/create', [LiquidationController::class, 'create'])->name('engagements.liquidations.create');
    Route::post('engagements/{engagement}/liquidations', [LiquidationController::class, 'store'])->name('engagements.liquidations.store');
    Route::get('liquidations/{liquidation}/ordonnancements/create', [OrdonnancementController::class, 'create'])->name('liquidations.ordonnancements.create');
    Route::post('liquidations/{liquidation}/ordonnancements', [OrdonnancementController::class, 'store'])->name('liquidations.ordonnancements.store');
    Route::get('ordonnancements/{ordonnancement}/paiements/create', [PaiementController::class, 'create'])->name('ordonnancements.paiements.create');
    Route::post('ordonnancements/{ordonnancement}/paiements', [PaiementController::class, 'store'])->name('ordonnancements.paiements.store');
    Route::resource('engagements', EngagementController::class);

    Route::get('reports/execution', [ExecutionReportController::class, 'index'])->name('reports.execution.index');
    Route::get('reports/activity-budget', [BudgetActivityJournalController::class, 'index'])->name('reports.activity.index');
});

Route::middleware(['auth', 'verified', 'active', 'permission:users.manage'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
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

        Route::middleware('permission:parameters.manage')->group(function () {
            Route::resource('parametres', ParametreControleController::class)
                ->parameters(['parametres' => 'parametre_controle']);
        });
    });

require __DIR__.'/auth.php';
