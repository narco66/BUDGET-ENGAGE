<?php

namespace App\Services\Budget;

use App\Models\JournalAudit;
use App\Models\LigneBudgetaire;
use App\Models\User;
use App\Models\VirementCredit;
use App\Support\BudgetJournal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Modification budgetaire : virement de credits entre deux lignes budgetaires, avec tracabilite.
 */
class VirementCreditService
{
    /**
     * Etape 1 : proposition d'un virement (aucun mouvement de credit).
     */
    public function propose(LigneBudgetaire $source, LigneBudgetaire $destination, string $montant, string $motif, User $user, ?string $ip = null): VirementCredit
    {
        $montant = number_format((float) $montant, 2, '.', '');
        $this->validate($source, $destination, $montant, $motif);

        $virement = VirementCredit::query()->create([
            'exercice_budgetaire_id' => $source->exercice_budgetaire_id,
            'ligne_source_id' => $source->id,
            'ligne_destination_id' => $destination->id,
            'montant' => $montant,
            'motif' => $motif,
            'source_revise_avant' => 0,
            'source_revise_apres' => 0,
            'destination_revise_avant' => 0,
            'destination_revise_apres' => 0,
            'statut' => 'propose',
            'user_id' => $user->id,
            'adresse_ip' => $ip,
        ]);

        BudgetJournal::record($user, 'Proposition de virement de crédit', null, [
            'virement' => $virement->id,
            'source' => $source->code,
            'destination' => $destination->code,
            'montant' => $montant,
        ]);

        return $virement;
    }

    /**
     * Etape 2 : approbation -> application effective du virement (mouvement de credit).
     */
    public function approve(VirementCredit $virement, User $approbateur, ?string $commentaire = null): VirementCredit
    {
        if ($virement->statut !== 'propose') {
            throw ValidationException::withMessages(['virement' => __('Ce virement n\'est pas en attente d\'approbation.')]);
        }

        return DB::transaction(function () use ($virement, $approbateur, $commentaire): VirementCredit {
            $source = LigneBudgetaire::query()->whereKey($virement->ligne_source_id)->lockForUpdate()->firstOrFail();
            $destination = LigneBudgetaire::query()->whereKey($virement->ligne_destination_id)->lockForUpdate()->firstOrFail();

            $montant = (string) $virement->montant;
            $dispoSource = bcsub($this->plafond($source), (string) $source->montant_engage, 2);
            if (bccomp($montant, $dispoSource, 2) > 0) {
                throw ValidationException::withMessages([
                    'montant' => __('Crédit disponible insuffisant sur la ligne source (disponible : :d).', [
                        'd' => number_format((float) $dispoSource, 0, ',', ' '),
                    ]),
                ]);
            }

            $srcAvant = (string) $source->montant_revise;
            $dstAvant = (string) $destination->montant_revise;
            $srcApres = bcsub($srcAvant, $montant, 2);
            $dstApres = bcadd($dstAvant, $montant, 2);

            $source->update(['montant_revise' => $srcApres]);
            $destination->update(['montant_revise' => $dstApres]);

            $virement->update([
                'statut' => 'approuve',
                'approuve_par' => $approbateur->id,
                'decision_at' => now(),
                'motif_decision' => $commentaire,
                'source_revise_avant' => $srcAvant,
                'source_revise_apres' => $srcApres,
                'destination_revise_avant' => $dstAvant,
                'destination_revise_apres' => $dstApres,
            ]);

            JournalAudit::query()->create([
                'user_id' => $approbateur->id,
                'action' => 'virement_credit_approuve',
                'entite' => 'virement_credit',
                'entite_id' => $virement->id,
                'anciennes_valeurs' => [
                    'source' => ['code' => $source->code, 'revise' => $srcAvant],
                    'destination' => ['code' => $destination->code, 'revise' => $dstAvant],
                ],
                'nouvelles_valeurs' => [
                    'montant' => $montant,
                    'propose_par' => $virement->user_id,
                    'source' => ['code' => $source->code, 'revise' => $srcApres],
                    'destination' => ['code' => $destination->code, 'revise' => $dstApres],
                ],
                'adresse_ip' => null,
                'user_agent' => null,
                'created_at' => now(),
            ]);

            BudgetJournal::record($approbateur, 'Approbation de virement de crédit', null, [
                'virement' => $virement->id,
                'source' => $source->code,
                'destination' => $destination->code,
                'montant' => $montant,
            ]);

            return $virement->fresh();
        });
    }

    /**
     * Rejet d'une proposition (aucun mouvement de credit).
     */
    public function reject(VirementCredit $virement, User $approbateur, string $motif): VirementCredit
    {
        if ($virement->statut !== 'propose') {
            throw ValidationException::withMessages(['virement' => __('Ce virement n\'est pas en attente d\'approbation.')]);
        }

        $virement->update([
            'statut' => 'rejete',
            'approuve_par' => $approbateur->id,
            'decision_at' => now(),
            'motif_decision' => $motif,
        ]);

        JournalAudit::query()->create([
            'user_id' => $approbateur->id,
            'action' => 'virement_credit_rejete',
            'entite' => 'virement_credit',
            'entite_id' => $virement->id,
            'anciennes_valeurs' => null,
            'nouvelles_valeurs' => ['motif' => $motif, 'propose_par' => $virement->user_id],
            'adresse_ip' => null,
            'user_agent' => null,
            'created_at' => now(),
        ]);

        return $virement->fresh();
    }

    private function validate(LigneBudgetaire $source, LigneBudgetaire $destination, string $montant, string $motif): void
    {
        $errors = [];

        if ($source->id === $destination->id) {
            $errors['ligne_destination_id'] = __('La ligne source et la ligne destination doivent être différentes.');
        }
        if ($source->exercice_budgetaire_id !== $destination->exercice_budgetaire_id) {
            $errors['ligne_destination_id'] = __('Les deux lignes doivent appartenir au même exercice.');
        }
        if (bccomp($montant, '0', 2) <= 0) {
            $errors['montant'] = __('Le montant du virement doit être strictement positif.');
        }
        if (trim($motif) === '') {
            $errors['motif'] = __('Le motif est obligatoire.');
        }

        $dispoSource = bcsub($this->plafond($source), (string) $source->montant_engage, 2);
        if (bccomp($montant, $dispoSource, 2) > 0) {
            $errors['montant'] = __('Le montant dépasse le crédit disponible de la ligne source (disponible : :d).', [
                'd' => number_format((float) $dispoSource, 0, ',', ' '),
            ]);
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function plafond(LigneBudgetaire $ligne): string
    {
        return (float) $ligne->montant_revise > 0
            ? number_format((float) $ligne->montant_revise, 2, '.', '')
            : number_format((float) $ligne->montant_vote, 2, '.', '');
    }
}
