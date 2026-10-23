<?php

namespace App\Policies;

use App\Enums\NiveauConfidentialiteGed;
use App\Models\GedDocument;
use App\Models\User;

class GedDocumentPolicy
{
    /** Accès transverse à tous les documents (administration GED). */
    public static function seesAllDocuments(User $user): bool
    {
        return $user->can('ged.admin');
    }

    public function viewAny(User $user): bool
    {
        return $user->can('ged.view');
    }

    public function view(User $user, GedDocument $document): bool
    {
        if (! $user->can('ged.view')) {
            return false;
        }

        if (self::seesAllDocuments($user) || $document->created_by === $user->id) {
            return true;
        }

        // Les documents les plus sensibles restent réservés à l'administrateur GED
        // ou au propriétaire ; les autres niveaux sont visibles avec la permission ged.view.
        $niveau = $document->niveau_confidentialite;

        return ! in_array($niveau, [
            NiveauConfidentialiteGed::Confidentiel,
            NiveauConfidentialiteGed::TresConfidentiel,
        ], true);
    }

    public function create(User $user): bool
    {
        return $user->can('ged.create');
    }

    public function update(User $user, GedDocument $document): bool
    {
        if ($document->est_verrouille || $document->est_archive) {
            return self::seesAllDocuments($user);
        }

        if (! $user->can('ged.update')) {
            return false;
        }

        return self::seesAllDocuments($user) || $document->created_by === $user->id;
    }

    public function delete(User $user, GedDocument $document): bool
    {
        if ($document->est_verrouille || $document->est_archive) {
            return false;
        }

        if (! $user->can('ged.delete')) {
            return false;
        }

        return self::seesAllDocuments($user) || $document->created_by === $user->id;
    }

    public function restore(User $user, GedDocument $document): bool
    {
        return $user->can('ged.restore');
    }

    public function forceDelete(User $user, GedDocument $document): bool
    {
        return false;
    }

    /* --------------------------------------------------------------------- */
    /* Capacités documentaires fines (appelées via $user->can('xxx', $doc))  */
    /* --------------------------------------------------------------------- */

    public function download(User $user, GedDocument $document): bool
    {
        return $user->can('ged.download') && $this->view($user, $document);
    }

    public function preview(User $user, GedDocument $document): bool
    {
        return $user->can('ged.preview') && $this->view($user, $document);
    }

    public function share(User $user, GedDocument $document): bool
    {
        return $user->can('ged.share') && $this->view($user, $document);
    }

    public function sign(User $user, GedDocument $document): bool
    {
        return $user->can('ged.sign') && ! $document->est_archive;
    }

    public function validateDocument(User $user, GedDocument $document): bool
    {
        return $user->can('ged.validate');
    }

    public function reject(User $user, GedDocument $document): bool
    {
        return $user->can('ged.reject');
    }

    public function publish(User $user, GedDocument $document): bool
    {
        return $user->can('ged.publish');
    }

    public function archive(User $user, GedDocument $document): bool
    {
        return $user->can('ged.archive');
    }

    public function export(User $user): bool
    {
        return $user->can('ged.export');
    }
}
