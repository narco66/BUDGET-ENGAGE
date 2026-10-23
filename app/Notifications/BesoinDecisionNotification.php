<?php

namespace App\Notifications;

use App\Models\ExpressionBesoin;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BesoinDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(protected ExpressionBesoin $besoin, protected string $decision)
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
        $libelle = match ($this->decision) {
            'valide' => 'validé',
            'rejete' => 'rejeté',
            'retourne' => 'retourné pour correction',
            default => $this->decision,
        };

        return [
            'type' => 'besoin.decision',
            'expression_besoin_id' => $this->besoin->id,
            'reference' => $this->besoin->reference,
            'decision' => $this->decision,
            'message' => "Votre besoin {$this->besoin->reference} a été {$libelle}.",
        ];
    }
}
