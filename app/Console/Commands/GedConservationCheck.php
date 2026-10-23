<?php

namespace App\Console\Commands;

use App\Enums\StatutDocumentGed;
use App\Models\GedDocument;
use App\Models\GedJournal;
use Illuminate\Console\Command;

class GedConservationCheck extends Command
{
    protected $signature = 'ged:conservation-check {--mark : Marque les documents échus comme expirés}';

    protected $description = 'Contrôle la conservation : recense (et marque éventuellement) les documents arrivés à échéance.';

    public function handle(): int
    {
        $echus = GedDocument::query()
            ->whereNotNull('date_expiration')
            ->whereDate('date_expiration', '<=', now())
            ->where('statut', '!=', StatutDocumentGed::Expire->value)
            ->get();

        $this->info("Documents arrivés à échéance : {$echus->count()}");

        if ($echus->isEmpty()) {
            return self::SUCCESS;
        }

        $this->table(
            ['Référence', 'Titre', 'Expiration'],
            $echus->map(fn (GedDocument $d) => [
                $d->reference,
                \Illuminate\Support\Str::limit($d->titre, 40),
                $d->date_expiration?->format('Y-m-d'),
            ])->all()
        );

        if (! $this->option('mark')) {
            $this->comment('Exécutez avec --mark pour marquer ces documents comme expirés.');

            return self::SUCCESS;
        }

        foreach ($echus as $document) {
            $document->update(['statut' => StatutDocumentGed::Expire->value]);
            GedJournal::query()->create([
                'ged_document_id' => $document->id,
                'user_id' => null,
                'action' => 'conservation_expiration_auto',
                'module_source' => $document->module_source,
                'commentaire' => 'Marqué expiré par la commande de conservation.',
                'created_at' => now(),
            ]);
        }

        $this->info("{$echus->count()} document(s) marqué(s) comme expiré(s).");

        return self::SUCCESS;
    }
}
