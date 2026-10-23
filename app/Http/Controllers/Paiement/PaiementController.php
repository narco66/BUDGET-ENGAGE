<?php

namespace App\Http\Controllers\Paiement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Paiement\StorePaiementRequest;
use App\Http\Requests\Paiement\UpdatePaiementRequest;
use App\Models\Activite;
use App\Models\ExerciceBudgetaire;
use App\Models\Fournisseur;
use App\Models\LigneBudgetaire;
use App\Models\Ordonnancement;
use App\Models\Paiement;
use App\Models\PaiementBanque;
use App\Models\PaiementType;
use App\Models\SourceFinancement;
use App\Services\Paiement\PaiementAuditService;
use App\Services\Paiement\PaiementService;
use App\Services\Paiement\PaiementTreasuryService;
use App\Services\Paiement\PaiementWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaiementController extends Controller
{
    public function __construct(
        private readonly PaiementService $service,
        private readonly PaiementWorkflowService $workflow,
        private readonly PaiementTreasuryService $treasury,
        private readonly PaiementAuditService $audit,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Paiement::class);

        $query = Paiement::query()
            ->with(['ordonnancement', 'type', 'banque', 'fournisseur', 'createur'])
            ->latest();

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($b) use ($q): void {
                $b->where('numero', 'like', "%{$q}%")
                    ->orWhere('objet', 'like', "%{$q}%")
                    ->orWhere('reference_bancaire', 'like', "%{$q}%");
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->string('statut')->value());
        }

        if ($request->filled('exercice_budgetaire_id')) {
            $query->where('exercice_budgetaire_id', $request->integer('exercice_budgetaire_id'));
        }

        $paiements = $query->paginate(20)->appends($request->query());
        $exercices = ExerciceBudgetaire::query()->orderByDesc('annee')->get();

        return view('paiements.index', compact('paiements', 'exercices'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Paiement::class);

        return view('paiements.create', array_merge($this->formDependencies(), [
            'ordonnancementPreselect' => $request->integer('ordonnancement_id') ?: null,
        ]));
    }

    public function store(StorePaiementRequest $request): RedirectResponse
    {
        $this->authorize('create', Paiement::class);

        $paiement = $this->service->creer($request->validated(), $request->user());

        return redirect()->route('paiements.show', $paiement)
            ->with('status', __('Paiement enregistré en brouillon.'));
    }

    public function generate(Ordonnancement $ordonnancement, Request $request): RedirectResponse
    {
        $this->authorize('generer', Paiement::class);

        $paiement = $this->service->genererDepuisOrdonnancement($ordonnancement, $request->user());

        return redirect()->route('paiements.show', $paiement)
            ->with('status', __('Paiement généré automatiquement depuis l’ordonnancement.'));
    }

    public function show(Paiement $paiement): View
    {
        $this->authorize('view', $paiement);

        $paiement->load([
            'ordonnancement.liquidation.engagement.exerciceBudgetaire',
            'type', 'banque', 'lot', 'fournisseur',
            'lignes.ligneBudgetaire', 'lignes.sourceFinancement',
            'validations.validateur', 'documents.gedDocument', 'versions.createur',
            'rejets.createur', 'rapprochements', 'ecritures', 'createur', 'validateur', 'executeur',
        ]);

        $timeline = $this->audit->timeline($paiement);
        $pendingStep = $this->workflow->currentPendingStep($paiement);
        $canValidatePending = $pendingStep !== null
            && $this->workflow->userCanValidateStep(request()->user(), $pendingStep);

        $solde = $paiement->banque ? $this->treasury->soldeDisponible($paiement->banque) : null;
        $plafondRestant = $paiement->banque ? $this->treasury->plafondRestant($paiement->banque) : null;

        return view('paiements.show', compact('paiement', 'timeline', 'pendingStep', 'canValidatePending', 'solde', 'plafondRestant'));
    }

    public function edit(Paiement $paiement): View
    {
        $this->authorize('modifier', $paiement);

        $paiement->load('lignes');

        return view('paiements.edit', array_merge($this->formDependencies(), [
            'paiement' => $paiement,
        ]));
    }

    public function update(UpdatePaiementRequest $request, Paiement $paiement): RedirectResponse
    {
        $this->authorize('modifier', $paiement);

        $this->service->mettreAJour($paiement, $request->validated(), $request->user());

        return redirect()->route('paiements.show', $paiement)->with('status', __('Paiement mis à jour.'));
    }

    public function transmettreBanque(Paiement $paiement, Request $request): RedirectResponse
    {
        $this->authorize('transmettre', $paiement);

        $this->service->transmettreBanque($paiement, $request->user(), $request->boolean('derogation'));

        return redirect()->route('paiements.show', $paiement)->with('status', __('Paiement transmis à la banque.'));
    }

    public function executer(Paiement $paiement, Request $request): RedirectResponse
    {
        $this->authorize('executer', $paiement);

        $this->service->executer($paiement, $request->user(), $request->boolean('derogation'));

        return redirect()->route('paiements.show', $paiement)
            ->with('status', __('Paiement exécuté. Le crédit budgétaire et les écritures comptables ont été générés.'));
    }

    public function rapprocher(Paiement $paiement, Request $request): RedirectResponse
    {
        $this->authorize('rapprocher', $paiement);

        $this->service->rapprocher($paiement, $request->user());

        return redirect()->route('paiements.show', $paiement)->with('status', __('Paiement rapproché.'));
    }

    public function cloturer(Paiement $paiement, Request $request): RedirectResponse
    {
        $this->authorize('cloturer', $paiement);

        $this->service->cloturer($paiement, $request->user());

        return redirect()->route('paiements.show', $paiement)->with('status', __('Paiement clôturé.'));
    }

    public function destroy(Request $request, Paiement $paiement): RedirectResponse
    {
        $this->authorize('annuler', $paiement);

        $request->validate(['motif_annulation' => ['nullable', 'string', 'max:1000']]);

        $this->service->annuler($paiement, $request->user(), $request->input('motif_annulation'));

        return redirect()->route('paiements.index')->with('status', __('Paiement annulé.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formDependencies(): array
    {
        return [
            'ordonnancements' => Ordonnancement::query()
                ->whereIn('statut', ['mandate', 'transmis_paiement'])
                ->with('liquidation')
                ->latest()
                ->get(['id', 'numero', 'objet', 'montant_ordonnance', 'liquidation_id', 'exercice_budgetaire_id']),
            'paiementTypes' => PaiementType::query()->actifs()->orderBy('ordre')->get(),
            'banques' => PaiementBanque::query()->actives()->orderBy('ordre')->get(),
            'fournisseurs' => Fournisseur::query()->orderBy('raison_sociale')->get(['id', 'raison_sociale']),
            'lignesBudgetaires' => LigneBudgetaire::query()->orderBy('code')->get(['id', 'code', 'libelle']),
            'sourcesFinancement' => SourceFinancement::query()->orderBy('libelle')->get(['id', 'code', 'libelle']),
            'activites' => Activite::query()->orderBy('code')->get(['id', 'code', 'libelle']),
        ];
    }
}
