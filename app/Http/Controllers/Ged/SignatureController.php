<?php

namespace App\Http\Controllers\Ged;

use App\Http\Controllers\Controller;
use App\Models\GedDocument;
use App\Models\GedSignature;
use App\Models\User;
use App\Services\Ged\GedSignatureService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SignatureController extends Controller
{
    public function __construct(private readonly GedSignatureService $signatures)
    {
    }

    public function request(Request $request, GedDocument $document): RedirectResponse
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'signataire_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $signataire = User::query()->findOrFail($validated['signataire_id']);
        $this->signatures->request($document, $signataire, $request->user());

        return redirect()
            ->route('ged.documents.show', $document)
            ->with('success', __('Demande de signature envoyée à :nom.', ['nom' => $signataire->name]));
    }

    public function sign(GedSignature $signature): RedirectResponse
    {
        $signature->loadMissing('document');
        $this->authorize('sign', $signature->document);
        $this->assertSignataire($signature);

        $this->signatures->sign(
            $signature,
            request()->user(),
            request()->ip(),
            (string) request()->userAgent()
        );

        return redirect()
            ->route('ged.documents.show', $signature->document)
            ->with('success', __('Document signé et verrouillé.'));
    }

    public function reject(Request $request, GedSignature $signature): RedirectResponse
    {
        $signature->loadMissing('document');
        $this->authorize('sign', $signature->document);
        $this->assertSignataire($signature);

        $validated = $request->validate([
            'motif_rejet' => ['required', 'string', 'max:1000'],
        ]);

        $this->signatures->reject($signature, $request->user(), $validated['motif_rejet']);

        return redirect()
            ->route('ged.documents.show', $signature->document)
            ->with('success', __('Signature refusée.'));
    }

    public function verify(GedSignature $signature): View
    {
        $signature->loadMissing(['document', 'signataire']);
        $this->authorize('view', $signature->document);

        $resultat = $this->signatures->verify($signature);

        return view('ged.signatures.verify', compact('signature', 'resultat'));
    }

    public function certificate(GedSignature $signature): Response
    {
        $signature->loadMissing(['document', 'signataire']);
        $this->authorize('view', $signature->document);

        abort_unless($signature->statut->value === 'signe', 404);

        $resultat = $this->signatures->verify($signature);

        $pdf = Pdf::loadView('ged.signatures.certificate', compact('signature', 'resultat'));

        return $pdf->download('certificat-signature-'.$signature->uuid.'.pdf');
    }

    private function assertSignataire(GedSignature $signature): void
    {
        $user = request()->user();
        abort_unless(
            $signature->signataire_id === $user->id || $user->can('ged.admin'),
            403,
            __("Vous n'êtes pas le signataire désigné.")
        );
    }
}
