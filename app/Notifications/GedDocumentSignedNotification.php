<?php

namespace App\Notifications;

use App\Models\GedDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class GedDocumentSignedNotification extends Notification
{
    use Queueable;

    public function __construct(protected GedDocument $document, protected string $signataire)
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
            'type' => 'ged.document_signed',
            'ged_document_id' => $this->document->id,
            'reference' => $this->document->reference,
            'message' => "Le document {$this->document->reference} a été signé par {$this->signataire}.",
        ];
    }
}
