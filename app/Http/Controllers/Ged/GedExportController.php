<?php

namespace App\Http\Controllers\Ged;

use App\Exports\GedDocumentsExport;
use App\Http\Controllers\Controller;
use App\Models\GedDocument;
use App\Models\GedSignature;
use App\Support\GedDocumentSearch;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GedExportController extends Controller
{
    private function authorizeExport(Request $request): void
    {
        abort_unless($request->user()?->can('ged.export'), 403);
    }

    public function documentairePdf(Request $request): Response
    {
        $this->authorizeExport($request);

        $documents = GedDocumentSearch::query($request, $request->user())->limit(5000)->get();

        $colonnes = ['Référence', 'Titre', 'Type', 'Statut', 'Confidentialité', 'Date document'];
        $lignes = $documents->map(fn (GedDocument $d) => [
            $d->reference, $d->titre, $d->typeDocument?->libelle ?? '—',
            $d->statut->libelle(), $d->niveau_confidentialite->libelle(),
            $d->date_document?->format('d/m/Y') ?? '—',
        ])->all();

        return Pdf::loadView('ged.exports.registre', [
            'titre' => 'Registre documentaire',
            'colonnes' => $colonnes,
            'lignes' => $lignes,
            'generatedAt' => now(),
        ])->download('registre-documentaire.pdf');
    }

    public function documentaireExcel(Request $request): BinaryFileResponse
    {
        $this->authorizeExport($request);

        $documents = GedDocumentSearch::query($request, $request->user())->limit(10000)->get();

        return Excel::download(new GedDocumentsExport($documents), 'registre-documentaire.xlsx');
    }

    public function documentaireCsv(Request $request): StreamedResponse
    {
        $this->authorizeExport($request);

        $documents = GedDocumentSearch::query($request, $request->user())->limit(10000)->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="registre-documentaire.csv"',
        ];

        return response()->stream(function () use ($documents): void {
            $handle = fopen('php://output', 'wb');
            if ($handle === false) {
                return;
            }
            fputcsv($handle, ['Reference', 'Titre', 'Type', 'Statut', 'Confidentialite', 'Date document', 'Module', 'SHA-256']);
            foreach ($documents as $d) {
                fputcsv($handle, [
                    $d->reference, $d->titre, $d->typeDocument?->libelle,
                    $d->statut->value, $d->niveau_confidentialite->value,
                    $d->date_document?->format('Y-m-d'), $d->module_source, $d->hash_sha256,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    public function archivesPdf(Request $request): Response
    {
        $this->authorizeExport($request);

        $documents = GedDocument::query()
            ->visiblePour($request->user())
            ->archives()
            ->with(['typeDocument', 'archiveur'])
            ->orderByDesc('date_archivage')->limit(5000)->get();

        $lignes = $documents->map(fn (GedDocument $d) => [
            $d->reference, $d->titre, $d->typeDocument?->libelle ?? '—',
            $d->archiveur?->name ?? '—', $d->date_archivage?->format('d/m/Y H:i') ?? '—',
        ])->all();

        return Pdf::loadView('ged.exports.registre', [
            'titre' => 'Registre des archives',
            'colonnes' => ['Référence', 'Titre', 'Type', 'Archivé par', "Date d'archivage"],
            'lignes' => $lignes,
            'generatedAt' => now(),
        ])->download('registre-archives.pdf');
    }

    public function signaturesPdf(Request $request): Response
    {
        $this->authorizeExport($request);

        $signatures = GedSignature::query()
            ->whereHas('document', fn ($q) => $q->visiblePour($request->user()))
            ->with(['document', 'signataire'])
            ->orderByDesc('horodatage')->limit(5000)->get();

        $lignes = $signatures->map(fn (GedSignature $s) => [
            $s->document?->reference ?? '—', $s->signataire?->name ?? '—',
            $s->statut->libelle(), $s->horodatage?->format('d/m/Y H:i') ?? '—',
            $s->hash_signe ? substr($s->hash_signe, 0, 16).'…' : '—',
        ])->all();

        return Pdf::loadView('ged.exports.registre', [
            'titre' => 'Registre des signatures',
            'colonnes' => ['Document', 'Signataire', 'Statut', 'Horodatage', 'Empreinte'],
            'lignes' => $lignes,
            'generatedAt' => now(),
        ])->download('registre-signatures.pdf');
    }
}
