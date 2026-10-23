<?php

namespace App\Http\Controllers\Paiement;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use App\Models\PaiementBanque;
use App\Services\Paiement\PaiementTreasuryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaiementBanqueController extends Controller
{
    public function __construct(private readonly PaiementTreasuryService $treasury)
    {
    }

    public function index(): View
    {
        $this->authorize('gererTypes', Paiement::class);

        $banques = PaiementBanque::query()->orderBy('ordre')->orderBy('libelle')->get();
        $soldes = $banques->mapWithKeys(fn (PaiementBanque $b) => [$b->id => [
            'disponible' => $this->treasury->soldeDisponible($b),
            'plafond_restant' => $this->treasury->plafondRestant($b),
        ]]);

        return view('paiements.banques.index', compact('banques', 'soldes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('gererTypes', Paiement::class);

        $data = $this->validateData($request);
        $data['created_by'] = $request->user()->id;
        PaiementBanque::query()->create($data);

        return back()->with('status', __('Compte bancaire créé.'));
    }

    public function update(Request $request, PaiementBanque $paiementBanque): RedirectResponse
    {
        $this->authorize('gererTypes', Paiement::class);

        $data = $this->validateData($request, $paiementBanque);
        $data['updated_by'] = $request->user()->id;
        $paiementBanque->update($data);

        return back()->with('status', __('Compte bancaire mis à jour.'));
    }

    public function destroy(PaiementBanque $paiementBanque): RedirectResponse
    {
        $this->authorize('gererTypes', Paiement::class);

        $paiementBanque->delete();

        return back()->with('status', __('Compte bancaire supprimé.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, ?PaiementBanque $banque = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:60', Rule::unique('paiement_banques', 'code')->ignore($banque?->id)],
            'libelle' => ['required', 'string', 'max:255'],
            'banque' => ['nullable', 'string', 'max:150'],
            'agence' => ['nullable', 'string', 'max:150'],
            'titulaire' => ['nullable', 'string', 'max:200'],
            'iban' => ['nullable', 'string', 'max:60'],
            'rib' => ['nullable', 'string', 'max:60'],
            'bic_swift' => ['nullable', 'string', 'max:20'],
            'devise' => ['nullable', 'string', 'max:10'],
            'solde_initial' => ['nullable', 'numeric'],
            'plafond_decaissement' => ['nullable', 'numeric', 'min:0'],
            'est_actif' => ['nullable', 'boolean'],
            'ordre' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
