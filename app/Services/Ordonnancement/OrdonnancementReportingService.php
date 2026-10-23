<?php

namespace App\Services\Ordonnancement;

use App\Models\Liquidation;
use App\Models\Ordonnancement;
use App\Models\OrdonnancementMandat;
use App\Models\OrdonnancementOrdrePaiement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Agrégations, indicateurs (tableaux de bord) et fabrique d'états (40+ états
 * PDF / Excel / CSV) du module Ordonnancement. Registre déclaratif
 * (availableReports) + constructeur générique (buildReport).
 */
class OrdonnancementReportingService
{
    /**
     * @return array<string, mixed>
     */
    public function kpis(?int $exerciceId = null): array
    {
        $base = fn () => $this->scoped($exerciceId);

        return [
            'nombre' => $base()->count(),
            'montant_ordonnance_total' => (string) $base()->sum('montant_ordonnance'),
            'montant_transmis_paiement' => (string) $base()->sum('montant_transmis_paiement'),
            'reste_a_ordonnancer' => (string) $base()->sum('reste_a_ordonnancer'),
            'mandates' => $base()->where('statut', 'mandate')->count(),
            'transmis' => $base()->where('statut', 'transmis_paiement')->count(),
            'en_circuit' => $base()->whereIn('statut', [
                'soumis', 'controle_budgetaire', 'controle_comptable', 'controle_financier', 'visa_ordonnateur',
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
            ->selectRaw('ordonnancement_type_id, count(*) as nombre, sum(montant_ordonnance) as montant')
            ->groupBy('ordonnancement_type_id')->with('type')->get();
    }

    /**
     * @return Collection<int, object>
     */
    public function parStatut(?int $exerciceId = null): Collection
    {
        return $this->scoped($exerciceId)
            ->selectRaw('statut, count(*) as nombre, sum(montant_ordonnance) as montant')
            ->groupBy('statut')->get();
    }

    /**
     * @return array<string, array{label: string, columns: array<int, string>}>
     */
    public function availableReports(): array
    {
        return [
            'registre' => ['label' => 'Registre des ordonnancements', 'columns' => ['N°', 'Liquidation', 'Type', 'Objet', 'Statut', 'Montant', 'Date']],
            'par-type' => ['label' => 'Ordonnancements par type', 'columns' => ['Type', 'Nombre', 'Montant']],
            'par-statut' => ['label' => 'Ordonnancements par statut', 'columns' => ['Statut', 'Nombre', 'Montant']],
            'par-fournisseur' => ['label' => 'Ordonnancements par fournisseur / PTF', 'columns' => ['Fournisseur', 'Nombre', 'Montant']],
            'par-source' => ['label' => 'Ordonnancements par source de financement', 'columns' => ['Source', 'Montant ordonnancé']],
            'par-ligne' => ['label' => 'Ordonnancements par ligne budgétaire', 'columns' => ['Ligne budgétaire', 'Montant ordonnancé']],
            'restes' => ['label' => 'Restes à ordonnancer par liquidation', 'columns' => ['Liquidation', 'Liquidé', 'Ordonnancé', 'Reste']],
            'mandates' => ['label' => 'Mandats émis', 'columns' => ['N° Mandat', 'Ordonnancement', 'Montant', 'Date', 'Statut']],
            'ordres-paiement' => ['label' => 'Ordres de paiement', 'columns' => ['N° OP', 'Ordonnancement', 'Montant', 'Date', 'Mode']],
            'transmis' => ['label' => 'Transmissions au paiement', 'columns' => ['N°', 'Ordonnancement', 'Montant transmis', 'Date']],
            'delais' => ['label' => 'Délais d’ordonnancement', 'columns' => ['N°', 'Soumission', 'Mandat', 'Délai (j)']],
            'partiels' => ['label' => 'Ordonnancements partiels', 'columns' => ['N°', 'Montant', 'Taux %']],
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
            'registre', 'transmis', 'delais', 'partiels' => $this->perOrdonnancementRows($slug, $exerciceId, $fmt),
            'par-type' => [$this->parType($exerciceId)->map(fn ($r) => [$r->type?->libelle ?? '—', (string) $r->nombre, $fmt($r->montant)])->all(), (string) $this->scoped($exerciceId)->sum('montant_ordonnance')],
            'par-statut' => [$this->parStatut($exerciceId)->map(fn ($r) => [$this->statutLabel($r->statut), (string) $r->nombre, $fmt($r->montant)])->all(), (string) $this->scoped($exerciceId)->sum('montant_ordonnance')],
            'par-fournisseur' => [$this->scoped($exerciceId)->selectRaw('fournisseur_id, count(*) n, sum(montant_ordonnance) m')->whereNotNull('fournisseur_id')->groupBy('fournisseur_id')->with('fournisseur')->get()->map(fn ($r) => [$r->fournisseur?->raison_sociale ?? '—', (string) $r->n, $fmt($r->m)])->all(), (string) $this->scoped($exerciceId)->sum('montant_ordonnance')],
            'par-source' => $this->parJointure($exerciceId, 'sources_financement', 'libelle', 'source_financement_id', $fmt),
            'par-ligne' => $this->parJointure($exerciceId, 'lignes_budgetaires', 'code', 'ligne_budgetaire_id', $fmt),
            'restes' => $this->restesRows($exerciceId, $fmt),
            'mandates' => $this->mandatsRows($exerciceId, $fmt),
            'ordres-paiement' => $this->ordresPaiementRows($exerciceId, $fmt),
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
    private function perOrdonnancementRows(string $slug, ?int $exerciceId, callable $fmt): array
    {
        $query = $this->scoped($exerciceId)->with('liquidation');

        $query = match ($slug) {
            'transmis' => $query->where('statut', 'transmis_paiement'),
            'partiels' => $query->where('taux_ordonnancement', '<', 100)->where('taux_ordonnancement', '>', 0),
            'delais' => $query->whereNotNull('mandate_at'),
            default => $query,
        };

        $items = $query->latest()->limit(10000)->get();
        $total = null;

        $rows = $items->map(function (Ordonnancement $o) use ($slug, $fmt): array {
            $num = (string) ($o->numero ?? '#'.$o->id);

            return match ($slug) {
                'transmis' => [$num, (string) $o->liquidation?->numero, $fmt($o->montant_transmis_paiement), optional($o->transmis_paiement_at)->format('d/m/Y') ?? '—'],
                'delais' => [$num, optional($o->submitted_at)->format('d/m/Y') ?? '—', optional($o->mandate_at)->format('d/m/Y') ?? '—', $this->delaiJours($o)],
                'partiels' => [$num, $fmt($o->montant_ordonnance), $fmt($o->taux_ordonnancement)],
                default => [$num, (string) $o->liquidation?->numero, (string) ($o->type?->libelle ?? '—'), \Illuminate\Support\Str::limit((string) $o->objet, 40), $this->statutLabel($o->statut?->value), $fmt($o->montant_ordonnance), optional($o->date_ordonnancement)->format('d/m/Y') ?? '—'],
            };
        })->all();

        if (in_array($slug, ['registre', 'transmis'], true)) {
            $total = (string) $items->sum($slug === 'transmis' ? 'montant_transmis_paiement' : 'montant_ordonnance');
        }

        return [$rows, $total];
    }

    /**
     * @return array{0: array<int, array<int, string>>, 1: ?string}
     */
    private function parJointure(?int $exerciceId, string $table, string $labelCol, string $fk, callable $fmt): array
    {
        $rows = $this->scoped($exerciceId)
            ->join('ordonnancement_lignes', 'ordonnancement_lignes.ordonnancement_id', '=', 'ordonnancements.id')
            ->join($table, $table.'.id', '=', 'ordonnancement_lignes.'.$fk)
            ->whereNull('ordonnancement_lignes.deleted_at')
            ->selectRaw($table.'.'.$labelCol.' as label, sum(ordonnancement_lignes.montant_ordonnance) as montant')
            ->groupBy($table.'.'.$labelCol)
            ->get();

        return [$rows->map(fn ($r) => [(string) $r->label, $fmt($r->montant)])->all(), (string) $rows->sum('montant')];
    }

    /**
     * @return array{0: array<int, array<int, string>>, 1: ?string}
     */
    private function restesRows(?int $exerciceId, callable $fmt): array
    {
        $liquidations = Liquidation::query()
            ->when($exerciceId, fn ($q) => $q->where('exercice_budgetaire_id', $exerciceId))
            ->where('statut', 'certifie')
            ->withSum(['ordonnancements as ordonnance' => fn ($q) => $q->whereNull('cancelled_at')], 'montant_ordonnance')
            ->limit(10000)->get();

        $rows = $liquidations->map(function (Liquidation $l) use ($fmt): array {
            $ordonnance = (string) ($l->ordonnance ?? '0');
            $reste = bcsub((string) $l->montant_liquide, $ordonnance, 2);

            return [(string) $l->numero, $fmt($l->montant_liquide), $fmt($ordonnance), $fmt($reste)];
        })->all();

        return [$rows, null];
    }

    /**
     * @return array{0: array<int, array<int, string>>, 1: ?string}
     */
    private function mandatsRows(?int $exerciceId, callable $fmt): array
    {
        $mandats = OrdonnancementMandat::query()
            ->with('ordonnancement')
            ->when($exerciceId, fn ($q) => $q->whereHas('ordonnancement', fn ($o) => $o->where('exercice_budgetaire_id', $exerciceId)))
            ->latest()->limit(10000)->get();

        $rows = $mandats->map(fn (OrdonnancementMandat $m) => [
            (string) $m->numero, (string) $m->ordonnancement?->numero, $fmt($m->montant),
            optional($m->date_mandat)->format('d/m/Y') ?? '—', (string) $m->statut,
        ])->all();

        return [$rows, (string) $mandats->sum('montant')];
    }

    /**
     * @return array{0: array<int, array<int, string>>, 1: ?string}
     */
    private function ordresPaiementRows(?int $exerciceId, callable $fmt): array
    {
        $ordres = OrdonnancementOrdrePaiement::query()
            ->with('ordonnancement')
            ->when($exerciceId, fn ($q) => $q->whereHas('ordonnancement', fn ($o) => $o->where('exercice_budgetaire_id', $exerciceId)))
            ->latest()->limit(10000)->get();

        $rows = $ordres->map(fn (OrdonnancementOrdrePaiement $op) => [
            (string) $op->numero, (string) $op->ordonnancement?->numero, $fmt($op->montant),
            optional($op->date_ordre)->format('d/m/Y') ?? '—', (string) ($op->mode_paiement ?? '—'),
        ])->all();

        return [$rows, (string) $ordres->sum('montant')];
    }

    private function delaiJours(Ordonnancement $o): string
    {
        if ($o->submitted_at === null || $o->mandate_at === null) {
            return '—';
        }

        return (string) $o->submitted_at->diffInDays($o->mandate_at);
    }

    private function statutLabel(string|\App\Enums\StatutOrdonnancement|null $statut): string
    {
        if ($statut instanceof \App\Enums\StatutOrdonnancement) {
            return $statut->libelle();
        }

        return $statut ? (\App\Enums\StatutOrdonnancement::tryFrom($statut)?->libelle() ?? $statut) : '—';
    }

    /**
     * @return Builder<Ordonnancement>
     */
    private function scoped(?int $exerciceId = null): Builder
    {
        $query = Ordonnancement::query();

        if ($exerciceId !== null) {
            $query->where('exercice_budgetaire_id', $exerciceId);
        }

        return $query;
    }
}
