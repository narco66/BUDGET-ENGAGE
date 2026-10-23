<?php

namespace App\Http\Controllers\Ged;

use App\Enums\NiveauConfidentialiteGed;
use App\Enums\StatutDocumentGed;
use App\Enums\StatutSignatureGed;
use App\Http\Controllers\Controller;
use App\Models\GedDocument;
use App\Models\GedJournal;
use Illuminate\Contracts\View\View;

class GedDashboardController extends Controller
{
    public function index(): View
    {
        $base = GedDocument::query();

        $kpis = [
            'total' => (clone $base)->count(),
            'deposes' => (clone $base)->where('statut', StatutDocumentGed::Depose->value)->count(),
            'valides' => (clone $base)->where('statut', StatutDocumentGed::Valide->value)->count(),
            'signes' => (clone $base)->where('est_signe', true)->count(),
            'archives' => (clone $base)->where('est_archive', true)->count(),
            'confidentiels' => (clone $base)->whereIn('niveau_confidentialite', [
                NiveauConfidentialiteGed::Confidentiel->value,
                NiveauConfidentialiteGed::TresConfidentiel->value,
            ])->count(),
            'expires' => (clone $base)->whereNotNull('date_expiration')
                ->whereDate('date_expiration', '<=', now())->count(),
            'attente_validation' => (clone $base)->where('statut', StatutDocumentGed::EnValidation->value)->count(),
            'attente_signature' => (clone $base)->where('signature_status', StatutSignatureGed::EnAttente->value)->count(),
            'volume_octets' => (int) (clone $base)->sum('taille'),
        ];

        $parStatut = GedDocument::query()
            ->selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut')
            ->toArray();

        $parModule = GedDocument::query()
            ->whereNotNull('module_source')
            ->selectRaw('module_source, COUNT(*) as total')
            ->groupBy('module_source')
            ->pluck('total', 'module_source')
            ->toArray();

        $parConfidentialite = GedDocument::query()
            ->selectRaw('niveau_confidentialite, COUNT(*) as total')
            ->groupBy('niveau_confidentialite')
            ->pluck('total', 'niveau_confidentialite')
            ->toArray();

        $parType = GedDocument::query()
            ->join('ged_types_documents', 'ged_types_documents.id', '=', 'ged_documents.type_document_id')
            ->whereNull('ged_documents.deleted_at')
            ->selectRaw('ged_types_documents.libelle as libelle, COUNT(*) as total')
            ->groupBy('ged_types_documents.libelle')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'libelle')
            ->toArray();

        $derniersDocuments = GedDocument::query()
            ->with(['typeDocument', 'createur'])
            ->latest('created_at')
            ->limit(8)
            ->get();

        $dernieresActivites = GedJournal::query()
            ->with(['utilisateur', 'document'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('ged.dashboard', compact(
            'kpis', 'parStatut', 'parModule', 'parConfidentialite', 'parType',
            'derniersDocuments', 'dernieresActivites'
        ));
    }
}
