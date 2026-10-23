<?php

namespace App\Notifications;

use App\Models\ExpressionBesoin;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BesoinPendingValidationNotification extends Notification
{
    use Queueable;

    public function __construct(protected ExpressionBesoin $besoin, protected string $etape)
    {
    }

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
            'type' => 'besoin.pending_validation',
            'expression_besoin_id' => $this->besoin->id,
            'reference' => $this->besoin->reference,
            'etape' => $this->etape,
            'message' => "Le besoin {$this->besoin->reference} attend votre action : {$this->etape}.",
        ];
    }
}
