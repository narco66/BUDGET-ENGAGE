<?php

namespace App\Notifications;

use App\Models\AlerteBudgetaire;
use Illuminate\Notifications\Notification;

class BudgetThresholdNotification extends Notification
{
    public function __construct(private AlerteBudgetaire $alerte) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'alerte_budgetaire',
            'niveau' => $this->alerte->niveau,
            'ligne_budgetaire_id' => $this->alerte->ligne_budgetaire_id,
            'code' => $this->alerte->code,
            'taux' => (float) $this->alerte->taux,
            'message' => $this->alerte->message,
        ];
    }
}
