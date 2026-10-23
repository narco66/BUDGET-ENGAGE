<?php

namespace App\Notifications;

use App\Models\GedDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GedDocumentDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(protected GedDocument $document, protected string $decision)
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
            'valide' => 'validée',
            'rejete' => 'rejetée',
            'retourne' => 'retournée',
            default => $this->decision,
        };

        return [
            'type' => 'ged.workflow_decision',
            'ged_document_id' => $this->document->id,
            'reference' => $this->document->reference,
            'decision' => $this->decision,
            'message' => "Une étape de validation a été {$libelle} pour le document {$this->document->reference}.",
        ];
    }
}
