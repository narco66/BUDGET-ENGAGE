<?php

namespace App\Notifications;

use App\Models\Engagement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EngagementStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Engagement $engagement,
        protected string $from,
        protected string $to
    ) {}

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
            'engagement_id' => $this->engagement->id,
            'numero' => $this->engagement->numero,
            'from' => $this->from,
            'to' => $this->to,
            'message' => "L'engagement {$this->engagement->numero} est passe de {$this->from} a {$this->to}.",
        ];
    }
}
