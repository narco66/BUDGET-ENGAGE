<?php

namespace App\Http\Controllers\Engagements;

use App\Http\Controllers\Controller;
use App\Http\Requests\Engagements\StorePieceJustificativeRequest;
use App\Models\Engagement;
use App\Models\PieceJustificative;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PieceJustificativeController extends Controller
{
    public function store(StorePieceJustificativeRequest $request, Engagement $engagement): RedirectResponse
    {
        $this->authorize('update', $engagement);

        $file = $request->file('piece');
        $path = $file->store("engagements/{$engagement->id}/pieces", 'private');
        $typePiece = (string) ($request->string('type_piece')->value() ?: 'justificatif');
        $originalName = $file->getClientOriginalName();
        $lastVersion = PieceJustificative::query()
            ->where('engagement_id', $engagement->id)
            ->where('type_piece', $typePiece)
            ->where('nom_original', $originalName)
            ->max('version');

        PieceJustificative::query()->create([
            'engagement_id' => $engagement->id,
            'type_piece' => $typePiece,
            'nom_original' => $originalName,
            'nom_stocke' => basename($path),
            'chemin' => $path,
            'extension' => $file->getClientOriginalExtension(),
            'mime_type' => $file->getClientMimeType(),
            'taille' => $file->getSize(),
            'version' => ((int) $lastVersion) + 1,
            'checksum' => hash_file('sha256', $file->getRealPath()),
            'uploaded_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('engagements.show', $engagement)
            ->with('status', __('Pièce justificative ajoutée.'));
    }

    public function download(Engagement $engagement, PieceJustificative $pieceJustificative): BinaryFileResponse
    {
        $this->authorize('view', $engagement);

        abort_unless($pieceJustificative->engagement_id === $engagement->id, 404);

        return Response::download(
            Storage::disk('private')->path($pieceJustificative->chemin),
            $pieceJustificative->nom_original
        );
    }

    public function destroy(Engagement $engagement, PieceJustificative $pieceJustificative): RedirectResponse
    {
        $this->authorize('update', $engagement);

        abort_unless($pieceJustificative->engagement_id === $engagement->id, 404);

        $pieceJustificative->delete();

        return redirect()
            ->route('engagements.show', $engagement)
            ->with('status', __('Pièce justificative supprimée.'));
    }
}
