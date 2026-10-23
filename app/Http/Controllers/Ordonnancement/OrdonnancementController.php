<?php

namespace App\Http\Controllers\Ordonnancement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ordonnancement\StoreOrdonnancementRequest;
use App\Http\Requests\Ordonnancement\UpdateOrdonnancementRequest;
use App\Models\Activite;
use App\Models\ExerciceBudgetaire;
use App\Models\Fournisseur;
use App\Models\LigneBudgetaire;
use App\Models\Liquidation;
use App\Models\Ordonnancement;
use App\Models\OrdonnancementType;
use App\Models\SourceFinancement;
use App\Services\Ordonnancement\OrdonnancementAuditService;
use App\Services\Ordonnancement\OrdonnancementService;
use App\Services\Ordonnancement\OrdonnancementValidationService;
use App\Services\Ordonnancement\OrdonnancementWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrdonnancementController extends Controller
{
    public function __construct(
        private readonly OrdonnancementService $service,
        private readonly OrdonnancementWorkflowService $workflow,
        private readonly OrdonnancementValidationService $validation,
        private readonly OrdonnancementAuditService $audit,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Ordonnancement::class);

        $query = Ordonnancement::query()
            ->with(['liquidation', 'type', 'fournisseur', 'createur'])
            ->latest();

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($b) use ($q): void {
                $b->where('numero', 'like', "%{$q}%")
                    ->orWhere('objet', 'like', "%{$q}%")
                    ->orWhere('mandat_numero', 'like', "%{$q}%");
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut')->value());
        }

        if ($request->filled('exercice_budgetaire_id')) {
            $query->where('exercice_budgetaire_id', $request->integer('exercice_budgetaire_id'));
        }

        $ordonnancements = $query->paginate(20)->appends($request->query());
        $exercices = ExerciceBudgetaire::query()->orderByDesc('annee')->get();

        return view('ordonnancements.index', compact('ordonnancements', 'exercices'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Ordonnancement::class);

        return view('ordonnancements.create', array_merge($this->formDependencies(), [
            'liquidationPreselect' => $request->integer('liquidation_id') ?: null,
        ]));
    }

    public function store(StoreOrdonnancementRequest $request): RedirectResponse
    {
        $this->authorize('create', Ordonnancement::class);

        $ordonnancement = $this->service->creer($request->validated(), $request->user());

        return redirect()->route('ordonnancements.show', $ordonnancement)
            ->with('status', __('Ordonnancement enregistré en brouillon.'));
    }

    public function generate(Liquidation $liquidation, Request $request): RedirectResponse
    {
        $this->authorize('generer', Ordonnancement::class);

        $ordonnancement = $this->service->genererDepuisLiquidation($liquidation, $request->user());

        return redirect()->route('ordonnancements.show', $ordonnancement)
            ->with('status', __('Ordonnancement généré automatiquement depuis la liquidation.'));
    }

    public function show(Ordonnancement $ordonnancement): View
    {
        $this->authorize('view', $ordonnancement);

        $ordonnancement->load([
            'liquidation.engagement.exerciceBudgetaire',
            'type',
            'fournisseur',
            'lignes.ligneBudgetaire',
            'lignes.sourceFinancement',
            'lignes.activite',
            'validations.validateur',
            'documents.gedDocument',
            'versions.createur',
            'mandats.createur',
            'ordresPaiement',
            'paiements.createur',
            'createur',
            'validateur',
        ]);

        $timeline = $this->audit->timeline($ordonnancement);
        $pendingStep = $this->workflow->currentPendingStep($ordonnancement);
        $canValidatePending = $pendingStep !== null
            && $this->workflow->userCanValidateStep(request()->user(), $pendingStep);
        $controles = $this->validation->controleFinancier($ordonnancement);

        return view('ordonnancements.show', compact('ordonnancement', 'timeline', 'pendingStep', 'canValidatePending', 'controles'));
    }

    public function edit(Ordonnancement $ordonnancement): View
    {
        $this->authorize('modifier', $ordonnancement);

        $ordonnancement->load('lignes');

        return view('ordonnancements.edit', array_merge($this->formDependencies(), [
            'ordonnancement' => $ordonnancement,
        ]));
    }

    public function update(UpdateOrdonnancementRequest $request, Ordonnancement $ordonnancement): RedirectResponse
    {
        $this->authorize('modifier', $ordonnancement);

        $this->service->mettreAJour($ordonnancement, $request->validated(), $request->user());

        return redirect()->route('ordonnancements.show', $ordonnancement)
            ->with('status', __('Ordonnancement mis à jour.'));
    }

    public function mandater(Ordonnancement $ordonnancement, Request $request): RedirectResponse
    {
        $this->authorize('mandater', $ordonnancement);

        $this->service->genererMandat($ordonnancement, $request->user());

        return redirect()->route('ordonnancements.show', $ordonnancement)
            ->with('status', __('Mandat généré. Le crédit budgétaire a été impacté.'));
    }

    public function destroy(Request $request, Ordonnancement $ordonnancement): RedirectResponse
    {
        $this->authorize('annuler', $ordonnancement);

        $request->validate(['motif_annulation' => ['nullable', 'string', 'max:1000']]);

        $this->service->annuler($ordonnancement, $request->user(), $request->input('motif_annulation'));

        return redirect()->route('ordonnancements.index')
            ->with('status', __('Ordonnancement annulé.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formDependencies(): array
    {
        return [
            'liquidations' => Liquidation::query()
                ->whereIn('statut', ['certifie', 'transmis_ordonnancement'])
                ->with('engagement')
                ->latest()
                ->get(['id', 'numero', 'objet', 'montant_net', 'engagement_id', 'exercice_budgetaire_id']),
            'ordonnancementTypes' => OrdonnancementType::query()->actifs()->orderBy('ordre')->get(),
            'fournisseurs' => Fournisseur::query()->orderBy('raison_sociale')->get(['id', 'raison_sociale']),
            'lignesBudgetaires' => LigneBudgetaire::query()->orderBy('code')->get(['id', 'code', 'libelle']),
            'sourcesFinancement' => SourceFinancement::query()->orderBy('libelle')->get(['id', 'code', 'libelle']),
            'activites' => Activite::query()->orderBy('code')->get(['id', 'code', 'libelle']),
        ];
    }
}
