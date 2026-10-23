<?php

namespace App\Services\ExpressionBesoin;

use App\Models\ExpressionBesoin;
use App\Models\GedDocument;
use App\Models\User;
use App\Services\Ged\GedDocumentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Archivage versionné des documents PDF générés pour un besoin (fiche d'état,
 * historique…) dans la GED. Chaque (re)génération d'un même document logique
 * crée une nouvelle version GED sans supprimer les précédentes — traçabilité
 * complète des éditions officielles. Aucun fichier n'est stocké hors GED.
 */
class ExpressionBesoinDocumentArchiveService
{
    public function __construct(private readonly GedDocumentService $ged)
    {
    }

    /**
     * Archive la fiche d'état de besoins (rôle dédié, versionnée à chaque édition).
     */
    public function archiverFiche(ExpressionBesoin $besoin, string $contenu, User $user): GedDocument
    {
        return $this->archiver(
            besoin: $besoin,
            contenu: $contenu,
            nomFichier: 'fiche-etat-besoins-'.$besoin->reference.'.pdf',
            role: 'fiche_generee',
            titre: 'Fiche d\'état de besoins '.$besoin->reference,
            user: $user,
        );
    }

    /**
     * Dépose le contenu PDF en GED : nouvelle version si le document logique
     * (identifié par son rôle de rattachement) existe déjà, sinon dépôt initial.
     */
    public function archiver(
        ExpressionBesoin $besoin,
        string $contenu,
        string $nomFichier,
        string $role,
        string $titre,
        User $user,
    ): GedDocument {
        return DB::transaction(function () use ($besoin, $contenu, $nomFichier, $role, $titre, $user): GedDocument {
            $file = $this->fichierTemporaire($contenu, $nomFichier);

            $existant = $besoin->gedAttachments()
                ->where('role', $role)
                ->latest('id')
                ->first();

            if ($existant !== null && ($document = GedDocument::query()->find($existant->ged_document_id)) !== null) {
                $this->ged->addVersion($document, $file, 'Régénération du document.', $user);

                return $document->refresh();
            }

            $document = $this->ged->store([
                'titre' => $titre,
                'niveau_confidentialite' => 'interne',
                'module_source' => 'expression_besoin',
                'module_source_id' => $besoin->id,
                'exercice_budgetaire_id' => $besoin->exercice_budgetaire_id,
                'departement_id' => $besoin->departement_id,
                'date_document' => now()->toDateString(),
            ], $file, $user);

            $besoin->gedAttachments()->create([
                'ged_document_id' => $document->id,
                'role' => $role,
                'created_by' => $user->id,
            ]);

            return $document;
        });
    }

    /**
     * Matérialise un contenu PDF en mémoire en UploadedFile exploitable par la GED.
     */
    private function fichierTemporaire(string $contenu, string $nomFichier): UploadedFile
    {
        $chemin = tempnam(sys_get_temp_dir(), 'ged_pdf_');
        file_put_contents($chemin, $contenu);

        // Mode "test" (dernier argument) : autorise un fichier non issu d'un upload HTTP.
        return new UploadedFile($chemin, $nomFichier, 'application/pdf', null, true);
    }
}
