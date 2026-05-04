<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BudgetJournal
{
    /**
     * Écrit une entrée dans le journal d’activité « budget » (Spatie Activity Log).
     *
     * @param  array<string, mixed>  $properties
     */
    public static function record(User $user, string $description, ?Model $subject = null, array $properties = []): void
    {
        $logger = activity('budget')->causedBy($user)->withProperties($properties);

        if ($subject !== null) {
            $logger->performedOn($subject);
        }

        $logger->log($description);
    }
}
