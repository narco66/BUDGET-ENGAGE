<?php

namespace App\Policies;

use App\Models\GedDossier;
use App\Models\User;

class GedDossierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ged.view');
    }

    public function view(User $user, GedDossier $dossier): bool
    {
        return $user->can('ged.view');
    }

    public function create(User $user): bool
    {
        return $user->can('ged.create');
    }

    public function update(User $user, GedDossier $dossier): bool
    {
        return $user->can('ged.admin') || $user->can('ged.update');
    }

    public function delete(User $user, GedDossier $dossier): bool
    {
        return $user->can('ged.admin') || $user->can('ged.delete');
    }

    public function restore(User $user, GedDossier $dossier): bool
    {
        return $user->can('ged.admin') || $user->can('ged.restore');
    }

    public function forceDelete(User $user, GedDossier $dossier): bool
    {
        return false;
    }
}
