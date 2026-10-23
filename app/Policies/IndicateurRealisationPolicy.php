<?php

namespace App\Policies;

use App\Models\IndicateurRealisation;
use App\Models\User;

class IndicateurRealisationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('suivi_evaluation.view');
    }

    public function view(User $user, IndicateurRealisation $realisation): bool
    {
        return $user->can('suivi_evaluation.view');
    }

    public function create(User $user): bool
    {
        return $user->can('suivi_evaluation.create');
    }

    public function validate(User $user, IndicateurRealisation $realisation): bool
    {
        return $user->can('suivi_evaluation.validate');
    }
}
