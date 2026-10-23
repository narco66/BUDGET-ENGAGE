<?php

namespace App\Services\Paiement;

use App\Models\Ordonnancement;
use App\Models\Paiement;
use App\Models\PaiementEcritureComptable;
use App\Models\PaiementRapprochement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Agrégations, indicateurs (tableaux de bord) et fabrique d'états (50+ états
 * PDF / Excel / CSV) du module Paiement. Registre déclaratif (availableReports)
 * + constructeur générique (buildReport).
 */
class PaiementReportingService
{
    /**
     * @return array<string, mixed>
     */
    public function kpis(?int $exerciceId = null): array
    {
        $base = fn () => $this->scoped($exerciceId);

        return [
            'nombre' => $base()->count(),
            'montant_paye_total' => (string) $base()->sum('montant_paye'),
            'reste_a_payer' => (string) $base()->sum('reste_a_payer'),
            'frais_bancaires' => (string) $base()->sum('frais_bancaires'),
            'executes' => $base()->where('statut', 'execute')->count(),
            'rapproches' => $base()->where('statut', 'rapproche')->count(),
            'rejetes' => $base()->whereIn('statut', ['echoue', 'rejete'])->count(),
            'programmes' => $base()->whereIn('statut', [
                'programme', 'controle_tresorerie', 'controle_comptable', 'validation_financiere', 'validation_signataires',
            ])->count(),
            'brouillons' => $base()->where('statut', 'brouillon')->count(),
        ];
    }

    /**
     * @return Collection<int, object>
     */
    public function parType(?int $exerciceId = null): Collection
    {
        return $this->scoped($exerciceId)
            ->selectRaw('paiement_type_id, count(*) as nombre, sum(montant_paye) as montant')
            ->groupBy('paiement_type_id')->with('type')->get();
    }

    /**
     * @return Collection<int, object>
     */
    public function parBanque(?int $exerciceId = null): Collection
    {
        return $this->scoped($exerciceId)
            ->selectRaw('paiement_banque_id, count(*) as nombre, sum(montant_paye) as montant')
            ->whereNotNull('paiement_banque_id')
            ->groupBy('paiement_banque_id')->with('banque')->get();
    }

    /**
     * @return Collection<int, object>
     */
    public function parStatut(?int $exerciceId = null): Collection
    {
        return $this->scoped($exerciceId)
            ->selectRaw('statut, count(*) as nombre, sum(montant_paye) as montant')
            ->groupBy('statut')->get();
    }

    /**
     * @return array<string, array{label: string, columns: array<int, string>}>
     */
    public function availableReports(): array
    {
        return [
            'registre' => ['label' => 'Registre des paiements', 'columns' => ['N°', 'Ordonnancement', 'Type', 'Banque', 'Statut', 'Montant', 'Date']],
            'journal' => ['label' => 'Journal des paiements', 'columns' => ['Date', 'N°', 'Bénéficiaire', 'Banque', 'Réf. bancaire', 'Montant']],
            'par-type' => ['label' => 'Paiements par type', 'columns' => ['Type', 'Nombre', 'Montant']],
            'par-banque' => ['label' => 'Paiements par banque', 'columns' => ['Banque', 'Nombre', 'Montant']],
            'par-devise' => ['label' => 'Paiements par devise', 'columns' => ['Devise', 'Nombre', 'Montant']],
            'par-statut' => ['label' => 'Paiements par statut', 'columns' => ['Statut', 'Nombre', 'Montant']],
            'par-fournisseur' => ['label' => 'Paiements par fournisseur / PTF', 'columns' => ['Fournisseur', 'Nombre', 'Montant']],
            'par-ligne' => ['label' => 'Paiements par ligne budgétaire', 'columns' => ['Ligne budgétaire', 'Payé']],
            'par-source' => ['label' => 'Paiements par source de financement', 'columns' => ['Source', 'Payé']],
            'restes' => ['label' => 'Restes à payer par ordonnancement', 'columns' => ['Ordonnancement', 'Ordonnancé', 'Payé', 'Reste']],
            'rejetes' => ['label' => 'Paiements rejetés', 'columns' => ['N°', 'Ordonnancement', 'Statut', 'Montant']],
            'en-attente' => ['label' => 'Paiements en attente', 'columns' => ['N°', 'Statut', 'Montant', 'Date prévue']],
            'internationaux' => ['label' => 'Paiements internationaux', 'columns' => ['N°', 'Devise', 'Taux', 'Montant']],
            'delais' => ['label' => 'Délais de paiement', 'columns' => ['N°', 'Programmé', 'Exécuté', 'Délai (j)']],
            'frais' => ['label' => 'Frais bancaires', 'columns' => ['N°', 'Banque', 'Frais', 'Montant']],
            'rapprochements' => ['label' => 'Rapprochements bancaires', 'columns' => ['Réf. relevé', 'Date', 'Montant relevé', 'Statut', 'Mode']],
            'ecritures' => ['label' => 'Journal des écritures comptables', 'columns' => ['N° écriture', 'Compte', 'Libellé', 'Débit', 'Crédit']],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{title: string, columns: array<int, string>, rows: array<int, array<int, string>>, total: ?string}
     */
    public function buildReport(string $slug, array $filters = []): array
    {
        $def = $this->availableReports()[$slug] ?? null;
        if ($def === null) {
            abort(404);
        }

        $exerciceId = isset($filters['exercice_budgetaire_id']) ? (int) $filters['exercice_budgetaire_id'] : null;
        $fmt = fn ($v) => number_format((float) $v, 2, ',', ' ');

        [$rows, $total] = match ($slug) {
            'registre', 'journal', 'rejetes', 'en-attente', 'internationaux', 'delais', 'frais' => $this->perPaiementRows($slug, $exerciceId, $fmt),
            'par-type' => [$this->parType($exerciceId)->map(fn ($r) => [$r->type?->libelle ?? '—', (string) $r->nombre, $fmt($r->montant)])->all(), (string) $this->scoped($exerciceId)->sum('montant_paye')],
            'par-banque' => [$this->parBanque($exerciceId)->map(fn ($r) => [$r->banque?->code ?? '—', (string) $r->nombre, $fmt($r->montant)])->all(), (string) $this->scoped($exerciceId)->sum('montant_paye')],
            'par-statut' => [$this->parStatut($exerciceId)->map(fn ($r) => [$this->statutLabel($r->statut), (string) $r->nombre, $fmt($r->montant)])->all(), (string) $this->scoped($exerciceId)->sum('montant_paye')],
            'par-devise' => [$this->scoped($exerciceId)->selectRaw('devise, count(*) n, sum(montant_paye) m')->groupBy('devise')->get()->map(fn ($r) => [(string) ($r->devise ?? '—'), (string) $r->n, $fmt($r->m)])->all(), (string) $this->scoped($exerciceId)->sum('montant_paye')],
            'par-fournisseur' => [$this->scoped($exerciceId)->selectRaw('fournisseur_id, count(*) n, sum(montant_paye) m')->whereNotNull('fournisseur_id')->groupBy('fournisseur_id')->with('fournisseur')->get()->map(fn ($r) => [$r->fournisseur?->raison_sociale ?? '—', (string) $r->n, $fmt($r->m)])->all(), (string) $this->scoped($exerciceId)->sum('montant_paye')],
            'par-source' => $this->parJointure($exerciceId, 'sources_financement', 'libelle', 'source_financement_id', $fmt),
            'par-ligne' => $this->parJointure($exerciceId, 'lignes_budgetaires', 'code', 'ligne_budgetaire_id', $fmt),
            'restes' => $this->restesRows($exerciceId, $fmt),
            'rapprochements' => $this->rapprochementsRows($exerciceId, $fmt),
            'ecritures' => $this->ecrituresRows($exerciceId, $fmt),
            default => [[], null],
        };

        return [
            'title' => $def['label'],
            'columns' => $def['columns'],
            'rows' => $rows,
            'total' => $total !== null ? $fmt($total) : null,
        ];
    }

    /**
     * @return array{0: array<int, array<int, string>>, 1: ?string}
     */
    private function perPaiementRows(string $slug, ?int $exerciceId, callable $fmt): array
    {
        $query = $this->scoped($exerciceId)->with(['ordonnancement', 'banque', 'fournisseur']);

        $query = match ($slug) {
            'rejetes' => $query->whereIn('statut', ['echoue', 'rejete']),
            'en-attente' => $query->whereIn('statut', ['programme', 'controle_tresorerie', 'controle_comptable', 'validation_financiere', 'validation_signataires', 'valide', 'transmis_banque']),
            'internationaux' => $query->whereNotNull('devise_paiement')->whereColumn('devise_paiement', '!=', 'devise'),
            'delais' => $query->whereNotNull('execute_at'),
            'frais' => $query->where('frais_bancaires', '>', 0),
            default => $query,
        };

        $items = $query->latest()->limit(10000)->get();
        $total = null;

        $rows = $items->map(function (Paiement $p) use ($slug, $fmt): array {
            $num = (string) ($p->numero ?? '#'.$p->id);

            return match ($slug) {
                'journal' => [optional($p->date_paiement)->format('d/m/Y') ?? '—', $num, (string) ($p->fournisseur?->raison_sociale ?? '—'), (string) ($p->banque?->code ?? '—'), (string) ($p->reference_bancaire ?? '—'), $fmt($p->montant_paye)],
                'rejetes' => [$num, (string) $p->ordonnancement?->numero, $this->statutLabel($p->statut?->value), $fmt($p->reste_a_payer)],
                'en-attente' => [$num, $this->statutLabel($p->statut?->value), $fmt($p->reste_a_payer), optional($p->date_prevue)->format('d/m/Y') ?? '—'],
                'internationaux' => [$num, $p->devise.'→'.$p->devise_paiement, (string) $p->taux_change, $fmt($p->montant_paye)],
                'delais' => [$num, optional($p->programmed_at)->format('d/m/Y') ?? '—', optional($p->execute_at)->format('d/m/Y') ?? '—', $this->delaiJours($p)],
                'frais' => [$num, (string) ($p->banque?->code ?? '—'), $fmt($p->frais_bancaires), $fmt($p->montant_paye)],
                default => [$num, (string) $p->ordonnancement?->numero, (string) ($p->type?->libelle ?? '—'), (string) ($p->banque?->code ?? '—'), $this->statutLabel($p->statut?->value), $fmt($p->montant_paye), optional($p->date_paiement)->format('d/m/Y') ?? '—'],
            };
        })->all();

        if (in_array($slug, ['registre', 'journal'], true)) {
            $total = (string) $items->sum('montant_paye');
        }

        return [$rows, $total];
    }

    /**
     * @return array{0: array<int, array<int, string>>, 1: ?string}
     */
    private function parJointure(?int $exerciceId, string $table, string $labelCol, string $fk, callable $fmt): array
    {
        $rows = $this->scoped($exerciceId)
            ->join('paiement_lignes', 'paiement_lignes.paiement_id', '=', 'paiements.id')
            ->join($table, $table.'.id', '=', 'paiement_lignes.'.$fk)
            ->whereNull('paiement_lignes.deleted_at')
            ->selectRaw($table.'.'.$labelCol.' as label, sum(paiement_lignes.montant_paye) as montant')
            ->groupBy($table.'.'.$labelCol)
            ->get();

        return [$rows->map(fn ($r) => [(string) $r->label, $fmt($r->montant)])->all(), (string) $rows->sum('montant')];
    }

    /**
     * @return array{0: array<int, array<int, string>>, 1: ?string}
     */
    private function restesRows(?int $exerciceId, callable $fmt): array
    {
        $ordonnancements = Ordonnancement::query()
            ->when($exerciceId, fn ($q) => $q->where('exercice_budgetaire_id', $exerciceId))
            ->where('statut', 'mandate')
            ->withSum(['paiements as paye' => fn ($q) => $q->whereNull('cancelled_at')], 'montant_paye')
            ->limit(10000)->get();

        $rows = $ordonnancements->map(function (Ordonnancement $o) use ($fmt): array {
            $paye = (string) ($o->paye ?? '0');
            $reste = bcsub((string) $o->montant_ordonnance, $paye, 2);

            return [(string) $o->numero, $fmt($o->montant_ordonnance), $fmt($paye), $fmt($reste)];
        })->all();

        return [$rows, null];
    }

    /**
     * @return array{0: array<int, array<int, string>>, 1: ?string}
     */
    private function rapprochementsRows(?int $exerciceId, callable $fmt): array
    {
        $items = PaiementRapprochement::query()
            ->when($exerciceId, fn ($q) => $q->whereHas('paiement', fn ($p) => $p->where('exercice_budgetaire_id', $exerciceId)))
            ->latest('date_operation')->limit(10000)->get();

        $rows = $items->map(fn (PaiementRapprochement $r) => [
            (string) ($r->reference_releve ?? '—'), optional($r->date_operation)->format('d/m/Y') ?? '—',
            $fmt($r->montant_releve), (string) $r->statut, (string) $r->mode,
        ])->all();

        return [$rows, (string) $items->sum('montant_releve')];
    }

    /**
     * @return array{0: array<int, array<int, string>>, 1: ?string}
     */
    private function ecrituresRows(?int $exerciceId, callable $fmt): array
    {
        $items = PaiementEcritureComptable::query()
            ->when($exerciceId, fn ($q) => $q->whereHas('paiement', fn ($p) => $p->where('exercice_budgetaire_id', $exerciceId)))
            ->latest('date_ecriture')->limit(10000)->get();

        $rows = $items->map(fn (PaiementEcritureComptable $e) => [
            (string) $e->numero_ecriture, (string) $e->compte, \Illuminate\Support\Str::limit((string) $e->libelle, 40),
            $fmt($e->debit), $fmt($e->credit),
        ])->all();

        return [$rows, null];
    }

    private function delaiJours(Paiement $p): string
    {
        if ($p->programmed_at === null || $p->execute_at === null) {
            return '—';
        }

        return (string) $p->programmed_at->diffInDays($p->execute_at);
    }

    private function statutLabel(string|\App\Enums\StatutPaiement|null $statut): string
    {
        if ($statut instanceof \App\Enums\StatutPaiement) {
            return $statut->libelle();
        }

        return $statut ? (\App\Enums\StatutPaiement::tryFrom($statut)?->libelle() ?? $statut) : '—';
    }

    /**
     * @return Builder<Paiement>
     */
    private function scoped(?int $exerciceId = null): Builder
    {
        $query = Paiement::query();

        if ($exerciceId !== null) {
            $query->where('exercice_budgetaire_id', $exerciceId);
        }

        return $query;
    }
}
