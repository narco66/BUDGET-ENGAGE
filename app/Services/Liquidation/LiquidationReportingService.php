<?php

namespace App\Services\Liquidation;

use App\Models\Engagement;
use App\Models\Liquidation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Agrégations, indicateurs (tableaux de bord) et fabrique d'états (40+ états
 * PDF / Excel / CSV) du module Liquidation. Un registre déclaratif
 * (availableReports) + un constructeur générique (buildReport) évitent la
 * multiplication de méthodes quasi identiques.
 */
class LiquidationReportingService
{
    /**
     * Indicateurs clés, éventuellement filtrés par exercice.
     *
     * @return array<string, mixed>
     */
    public function kpis(?int $exerciceId = null): array
    {
        $base = fn () => $this->scoped($exerciceId);

        return [
            'nombre' => $base()->count(),
            'montant_net_total' => (string) $base()->sum('montant_net'),
            'montant_liquide_total' => (string) $base()->sum('montant_liquide'),
            'total_penalites' => (string) $base()->sum('total_penalites'),
            'total_garanties' => (string) $base()->sum('total_garanties'),
            'certifiees' => $base()->where('statut', 'certifie')->count(),
            'en_circuit' => $base()->whereIn('statut', [
                'soumis', 'controle_technique', 'controle_administratif',
                'controle_budgetaire', 'controle_financier',
            ])->count(),
            'transmises' => $base()->where('statut', 'transmis_ordonnancement')->count(),
            'brouillons' => $base()->where('statut', 'brouillon')->count(),
        ];
    }

    /**
     * @return Collection<int, object>
     */
    public function parType(?int $exerciceId = null): Collection
    {
        return $this->scoped($exerciceId)
            ->selectRaw('liquidation_type_id, count(*) as nombre, sum(montant_net) as montant')
            ->groupBy('liquidation_type_id')->with('type')->get();
    }

    /**
     * @return Collection<int, object>
     */
    public function parStatut(?int $exerciceId = null): Collection
    {
        return $this->scoped($exerciceId)
            ->selectRaw('statut, count(*) as nombre, sum(montant_net) as montant')
            ->groupBy('statut')->get();
    }

    /**
     * Registre déclaratif des états disponibles.
     *
     * @return array<string, array{label: string, columns: array<int, string>}>
     */
    public function availableReports(): array
    {
        return [
            'registre' => ['label' => 'Registre des liquidations', 'columns' => ['N°', 'Engagement', 'Type', 'Objet', 'Statut', 'Brut', 'Net', 'Date']],
            'par-type' => ['label' => 'Liquidations par type', 'columns' => ['Type', 'Nombre', 'Montant net']],
            'par-statut' => ['label' => 'Liquidations par statut', 'columns' => ['Statut', 'Nombre', 'Montant net']],
            'par-fournisseur' => ['label' => 'Liquidations par fournisseur / PTF', 'columns' => ['Fournisseur', 'Nombre', 'Montant net']],
            'par-ligne' => ['label' => 'Liquidations par ligne budgétaire', 'columns' => ['Ligne budgétaire', 'Net imputé']],
            'restes' => ['label' => 'Restes à liquider par engagement', 'columns' => ['Engagement', 'Montant engagé', 'Liquidé', 'Reste']],
            'penalites' => ['label' => 'État des pénalités', 'columns' => ['N°', 'Engagement', 'Pénalités', 'Net']],
            'garanties' => ['label' => 'Retenues de garantie', 'columns' => ['N°', 'Engagement', 'Garanties', 'Net']],
            'avances' => ['label' => 'État des avances', 'columns' => ['N°', 'Engagement', 'Avances', 'Net']],
            'acomptes' => ['label' => 'État des acomptes', 'columns' => ['N°', 'Engagement', 'Acomptes', 'Net']],
            'service-fait' => ['label' => 'État des services faits', 'columns' => ['N°', 'Date constat.', 'Validé', 'Réserves']],
            'delais' => ['label' => 'Délais de liquidation', 'columns' => ['N°', 'Soumission', 'Certification', 'Délai (j)']],
            'certifiees' => ['label' => 'Liquidations certifiées', 'columns' => ['N°', 'Engagement', 'Certificat', 'Net', 'Date']],
            'partielles' => ['label' => 'Liquidations partielles', 'columns' => ['N°', 'Engagement', 'Net', 'Taux %']],
            'finales' => ['label' => 'Liquidations finales', 'columns' => ['N°', 'Engagement', 'Net', 'Date']],
        ];
    }

    /**
     * Construit un état : titre, colonnes, lignes (chaînes), total optionnel.
     *
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
            'registre', 'certifiees', 'partielles', 'finales', 'penalites', 'garanties', 'avances', 'acomptes', 'service-fait', 'delais'
                => $this->perLiquidationRows($slug, $exerciceId, $fmt),
            'par-type' => [$this->parType($exerciceId)->map(fn ($r) => [$r->type?->libelle ?? '—', (string) $r->nombre, $fmt($r->montant)])->all(), (string) $this->scoped($exerciceId)->sum('montant_net')],
            'par-statut' => [$this->parStatut($exerciceId)->map(fn ($r) => [$this->statutLabel($r->statut), (string) $r->nombre, $fmt($r->montant)])->all(), (string) $this->scoped($exerciceId)->sum('montant_net')],
            'par-fournisseur' => [$this->scoped($exerciceId)->selectRaw('fournisseur_id, count(*) n, sum(montant_net) m')->whereNotNull('fournisseur_id')->groupBy('fournisseur_id')->with('fournisseur')->get()->map(fn ($r) => [$r->fournisseur?->raison_sociale ?? '—', (string) $r->n, $fmt($r->m)])->all(), (string) $this->scoped($exerciceId)->sum('montant_net')],
            'par-ligne' => $this->parLigneRows($exerciceId, $fmt),
            'restes' => $this->restesRows($exerciceId, $fmt),
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
    private function perLiquidationRows(string $slug, ?int $exerciceId, callable $fmt): array
    {
        $query = $this->scoped($exerciceId)->with(['engagement', 'serviceFait']);

        $query = match ($slug) {
            'certifiees' => $query->where('statut', 'certifie'),
            'finales' => $query->whereHas('type', fn ($t) => $t->where('nature', 'finale')),
            'partielles' => $query->where('taux_liquidation', '<', 100)->where('taux_liquidation', '>', 0),
            'penalites' => $query->where('total_penalites', '>', 0),
            'garanties' => $query->where('total_garanties', '>', 0),
            'avances' => $query->where('total_avances', '>', 0),
            'acomptes' => $query->where('total_acomptes', '>', 0),
            default => $query,
        };

        $items = $query->latest()->limit(10000)->get();
        $total = null;

        $rows = $items->map(function (Liquidation $l) use ($slug, $fmt, &$total): array {
            $num = (string) ($l->numero ?? '#'.$l->id);
            $eng = (string) $l->engagement?->numero;

            return match ($slug) {
                'penalites' => [$num, $eng, $fmt($l->total_penalites), $fmt($l->montant_net)],
                'garanties' => [$num, $eng, $fmt($l->total_garanties), $fmt($l->montant_net)],
                'avances' => [$num, $eng, $fmt($l->total_avances), $fmt($l->montant_net)],
                'acomptes' => [$num, $eng, $fmt($l->total_acomptes), $fmt($l->montant_net)],
                'service-fait' => [$num, optional($l->serviceFait?->date_constatation)->format('d/m/Y') ?? '—', $l->serviceFait?->valide ? 'Oui' : 'Non', $l->serviceFait?->reserves ? 'Oui' : 'Non'],
                'delais' => [$num, optional($l->submitted_at)->format('d/m/Y') ?? '—', optional($l->certifie_at)->format('d/m/Y') ?? '—', $this->delaiJours($l)],
                'certifiees' => [$num, $eng, (string) ($l->certificat_numero ?? '—'), $fmt($l->montant_net), optional($l->certifie_at)->format('d/m/Y') ?? '—'],
                'partielles' => [$num, $eng, $fmt($l->montant_net), $fmt($l->taux_liquidation)],
                'finales' => [$num, $eng, $fmt($l->montant_net), optional($l->date_liquidation)->format('d/m/Y') ?? '—'],
                default => [$num, $eng, (string) ($l->type?->libelle ?? '—'), \Illuminate\Support\Str::limit((string) ($l->objet ?? $l->engagement?->objet), 40), $this->statutLabel($l->statut?->value), $fmt($l->montant_brut), $fmt($l->montant_net), optional($l->date_liquidation)->format('d/m/Y') ?? '—'],
            };
        })->all();

        if (in_array($slug, ['registre', 'certifiees', 'partielles', 'finales', 'penalites', 'garanties', 'avances', 'acomptes'], true)) {
            $total = (string) $items->sum('montant_net');
        }

        return [$rows, $total];
    }

    /**
     * @return array{0: array<int, array<int, string>>, 1: ?string}
     */
    private function parLigneRows(?int $exerciceId, callable $fmt): array
    {
        $rows = $this->scoped($exerciceId)
            ->join('liquidation_lignes', 'liquidation_lignes.liquidation_id', '=', 'liquidations.id')
            ->join('lignes_budgetaires', 'lignes_budgetaires.id', '=', 'liquidation_lignes.ligne_budgetaire_id')
            ->whereNull('liquidation_lignes.deleted_at')
            ->selectRaw('lignes_budgetaires.code as code, sum(liquidation_lignes.montant_net) as montant')
            ->groupBy('lignes_budgetaires.code')
            ->get();

        return [$rows->map(fn ($r) => [(string) $r->code, $fmt($r->montant)])->all(), (string) $rows->sum('montant')];
    }

    /**
     * @return array{0: array<int, array<int, string>>, 1: ?string}
     */
    private function restesRows(?int $exerciceId, callable $fmt): array
    {
        $engagements = Engagement::query()
            ->when($exerciceId, fn ($q) => $q->where('exercice_budgetaire_id', $exerciceId))
            ->whereIn('statut', ['approuve', 'liquide', 'ordonnance', 'paye'])
            ->withSum(['liquidations as liquide' => fn ($q) => $q->whereNull('cancelled_at')], 'montant_liquide')
            ->limit(10000)->get();

        $rows = $engagements->map(function (Engagement $e) use ($fmt): array {
            $liquide = (string) ($e->liquide ?? '0');
            $reste = bcsub((string) $e->montant, $liquide, 2);

            return [(string) $e->numero, $fmt($e->montant), $fmt($liquide), $fmt($reste)];
        })->all();

        return [$rows, null];
    }

    private function delaiJours(Liquidation $l): string
    {
        if ($l->submitted_at === null || $l->certifie_at === null) {
            return '—';
        }

        return (string) $l->submitted_at->diffInDays($l->certifie_at);
    }

    private function statutLabel(string|\App\Enums\StatutLiquidation|null $statut): string
    {
        if ($statut instanceof \App\Enums\StatutLiquidation) {
            return $statut->libelle();
        }

        return $statut ? (\App\Enums\StatutLiquidation::tryFrom($statut)?->libelle() ?? $statut) : '—';
    }

    /**
     * @return Builder<Liquidation>
     */
    private function scoped(?int $exerciceId = null): Builder
    {
        $query = Liquidation::query();

        if ($exerciceId !== null) {
            $query->where('exercice_budgetaire_id', $exerciceId);
        }

        return $query;
    }
}
