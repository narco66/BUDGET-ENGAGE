<?php

namespace App\Http\Controllers\ExpressionBesoins;

use App\Http\Controllers\Controller;
use App\Models\ExpressionBesoin;
use App\Models\GedDocumentAttachment;
use App\Services\ExpressionBesoin\ExpressionBesoinGedService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PieceController extends Controller
{
    public function __construct(private readonly ExpressionBesoinGedService $ged)
    {
    }

    public function store(Request $request, ExpressionBesoin $expression_besoin): RedirectResponse
    {
        $this->authorize('update', $expression_besoin);

        $validated = $request->validate([
            'fichier' => [
                'required', 'file', 'max:51200',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg,jpeg,gif,txt,csv,zip',
            ],
            'titre' => ['nullable', 'string', 'max:255'],
        ]);

        $this->ged->deposer(
            $expression_besoin,
            $request->file('fichier'),
            $request->user(),
            $validated['titre'] ?? null,
        );

        return redirect()
            ->route('expression-besoins.besoins.show', $expression_besoin)
            ->with('success', __('Pièce déposée dans la GED et rattachée au besoin.'));
    }

    public function destroy(ExpressionBesoin $expression_besoin, GedDocumentAttachment $attachment): RedirectResponse
    {
        $this->authorize('update', $expression_besoin);

        abort_unless(
            $attachment->attachable_type === ExpressionBesoin::class
            && $attachment->attachable_id === $expression_besoin->id,
            404
        );

        $attachment->delete();

        return redirect()
            ->route('expression-besoins.besoins.show', $expression_besoin)
            ->with('success', __('Pièce détachée du besoin.'));
    }
}
