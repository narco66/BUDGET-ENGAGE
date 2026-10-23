<?php

namespace App\Notifications;

use App\Models\GedDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GedSignatureRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(protected GedDocument $document)
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
            'type' => 'ged.signature_requested',
            'ged_document_id' => $this->document->id,
            'reference' => $this->document->reference,
            'message' => "Une signature vous est demandée pour le document {$this->document->reference}.",
        ];
    }
}
