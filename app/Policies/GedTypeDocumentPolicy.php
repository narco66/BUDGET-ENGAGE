<?php

namespace App\Policies;

use App\Models\GedTypeDocument;
use App\Models\User;

class GedTypeDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ged.view');
    }

    public function view(User $user, GedTypeDocument $type): bool
    {
        return $user->can('ged.view');
    }

    public function create(User $user): bool
    {
        return $user->can('ged.admin') || $user->can('ged.create');
    }

    public function update(User $user, GedTypeDocument $type): bool
    {
        return $user->can('ged.admin') || $user->can('ged.update');
    }

    public function delete(User $user, GedTypeDocument $type): bool
    {
        return $user->can('ged.admin') || $user->can('ged.delete');
    }

    public function restore(User $user, GedTypeDocument $type): bool
    {
        return $user->can('ged.admin') || $user->can('ged.restore');
    }

    public function forceDelete(User $user, GedTypeDocument $type): bool
    {
        return false;
    }
}
