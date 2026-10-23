<?php

namespace App\Http\Controllers\Liquidation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Liquidation\StoreLiquidationRequest;
use App\Http\Requests\Liquidation\UpdateLiquidationRequest;
use App\Models\Activite;
use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use App\Models\Fournisseur;
use App\Models\LigneBudgetaire;
use App\Models\Liquidation;
use App\Models\LiquidationType;
use App\Models\SourceFinancement;
use App\Models\Tache;
use App\Services\Liquidation\LiquidationAuditService;
use App\Services\Liquidation\LiquidationService;
use App\Services\Liquidation\LiquidationWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiquidationController extends Controller
{
    public function __construct(
        private readonly LiquidationService $service,
        private readonly LiquidationWorkflowService $workflow,
        private readonly LiquidationAuditService $audit,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Liquidation::class);

        $query = Liquidation::query()
            ->with(['engagement', 'type', 'fournisseur', 'createur'])
            ->latest();

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($b) use ($q): void {
                $b->where('numero', 'like', "%{$q}%")
                    ->orWhere('objet', 'like', "%{$q}%")
                    ->orWhereHas('engagement', fn ($e) => $e->where('numero', 'like', "%{$q}%"));
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut')->value());
        }

        if ($request->filled('exercice_budgetaire_id')) {
            $query->where('exercice_budgetaire_id', $request->integer('exercice_budgetaire_id'));
        }

        $liquidations = $query->paginate(20)->appends($request->query());
        $exercices = ExerciceBudgetaire::query()->orderByDesc('annee')->get();

        return view('liquidations.index', compact('liquidations', 'exercices'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Liquidation::class);

        $engagementPreselect = $request->integer('engagement_id') ?: null;

        return view('liquidations.create', array_merge($this->formDependencies(), [
            'engagementPreselect' => $engagementPreselect,
        ]));
    }

    public function store(StoreLiquidationRequest $request): RedirectResponse
    {
        $this->authorize('create', Liquidation::class);

        $liquidation = $this->service->creer($request->validated(), $request->user());

        return redirect()->route('liquidations.show', $liquidation)
            ->with('status', __('Liquidation enregistrée en brouillon.'));
    }

    public function generate(Engagement $engagement, Request $request): RedirectResponse
    {
        $this->authorize('generer', Liquidation::class);

        $liquidation = $this->service->genererDepuisEngagement($engagement, $request->user());

        return redirect()->route('liquidations.show', $liquidation)
            ->with('status', __('Liquidation générée automatiquement depuis l’engagement.'));
    }

    public function show(Liquidation $liquidation): View
    {
        $this->authorize('view', $liquidation);

        $liquidation->load([
            'engagement.exerciceBudgetaire',
            'type',
            'fournisseur',
            'lignes.ligneBudgetaire',
            'lignes.sourceFinancement',
            'lignes.activite',
            'servicesFaits.responsable',
            'servicesFaits.validateur',
            'receptions.responsable',
            'validations.validateur',
            'documents.gedDocument',
            'versions.createur',
            'ordonnancements.createur',
            'createur',
            'validateur',
        ]);

        $timeline = $this->audit->timeline($liquidation);
        $pendingStep = $this->workflow->currentPendingStep($liquidation);
        $canValidatePending = $pendingStep !== null
            && $this->workflow->userCanValidateStep(request()->user(), $pendingStep);

        return view('liquidations.show', compact('liquidation', 'timeline', 'pendingStep', 'canValidatePending'));
    }

    public function edit(Liquidation $liquidation): View
    {
        $this->authorize('modifier', $liquidation);

        $liquidation->load('lignes');

        return view('liquidations.edit', array_merge($this->formDependencies(), [
            'liquidation' => $liquidation,
        ]));
    }

    public function update(UpdateLiquidationRequest $request, Liquidation $liquidation): RedirectResponse
    {
        $this->authorize('modifier', $liquidation);

        $this->service->mettreAJour($liquidation, $request->validated(), $request->user());

        return redirect()->route('liquidations.show', $liquidation)
            ->with('status', __('Liquidation mise à jour.'));
    }

    public function certifier(Liquidation $liquidation, Request $request): RedirectResponse
    {
        $this->authorize('certifier', $liquidation);

        $this->service->certifier($liquidation, $request->user());

        return redirect()->route('liquidations.show', $liquidation)
            ->with('status', __('Liquidation certifiée. Le crédit budgétaire a été impacté.'));
    }

    public function destroy(Request $request, Liquidation $liquidation): RedirectResponse
    {
        $this->authorize('annuler', $liquidation);

        $request->validate(['motif_annulation' => ['nullable', 'string', 'max:1000']]);

        $this->service->annuler($liquidation, $request->user(), $request->input('motif_annulation'));

        return redirect()->route('liquidations.index')
            ->with('status', __('Liquidation annulée.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formDependencies(): array
    {
        return [
            'engagements' => Engagement::query()
                ->whereIn('statut', ['approuve', 'liquide', 'ordonnance'])
                ->orderByDesc('created_at')
                ->get(['id', 'numero', 'objet', 'montant', 'exercice_budgetaire_id']),
            'liquidationTypes' => LiquidationType::query()->actifs()->orderBy('ordre')->get(),
            'fournisseurs' => Fournisseur::query()->orderBy('raison_sociale')->get(['id', 'raison_sociale']),
            'lignesBudgetaires' => LigneBudgetaire::query()->orderBy('code')->get(['id', 'code', 'libelle']),
            'sourcesFinancement' => SourceFinancement::query()->orderBy('libelle')->get(['id', 'code', 'libelle']),
            'activites' => Activite::query()->orderBy('code')->get(['id', 'code', 'libelle']),
            'taches' => Tache::query()->orderBy('code')->get(['id', 'code', 'libelle']),
        ];
    }
}
