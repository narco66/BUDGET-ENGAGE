<?php

namespace App\Policies;

use App\Models\SuiviPhysiqueRealisation;
use App\Models\User;

class SuiviPhysiqueRealisationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('suivi_physique.view');
    }

    public function view(User $user, SuiviPhysiqueRealisation $realisation): bool
    {
        return $user->can('suivi_physique.view');
    }

    public function create(User $user): bool
    {
        return $user->can('suivi_physique.create');
    }

    public function validate(User $user, SuiviPhysiqueRealisation $realisation): bool
    {
        return $user->can('suivi_physique.validate');
    }
}
