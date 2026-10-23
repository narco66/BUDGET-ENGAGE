<?php

namespace App\Services\Rbm;

use App\Models\AxeStrategique;
use App\Models\CadreLogique;
use App\Models\ExerciceBudgetaire;
use App\Models\JournalAudit;
use App\Models\PapImport;
use App\Models\PapLigneBudgetaire;
use App\Models\Pilier;
use App\Models\Produit;
use App\Models\SousProduit;
use App\Models\User;
use App\Support\BudgetJournal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class PapImportService
{
    public const MODES = ['simulation', 'creation', 'mise_a_jour', 'remplacement'];

    public const NIVEAUX = ['pilier', 'axe', 'produit', 'sous_produit'];

    /** Ordre hierarchique pour le tri parents-avant-enfants. */
    private const ORDRE_NIVEAU = ['pilier' => 1, 'axe' => 2, 'produit' => 3, 'sous_produit' => 4];

    private const PARENT_NIVEAU = ['axe' => 'pilier', 'produit' => 'axe', 'sous_produit' => 'produit'];

    /**
     * Construit les lignes normalisees a partir du jeu de donnees officiel PAP 2026.
     * Les montants du fichier sont en milliers de FCFA -> conversion en FCFA.
     *
     * @return array<int, array<string, mixed>>
     */
    public function rowsFromDataset(): array
    {
        $data = require database_path('data/pap_2026.php');
        $sousProduits = require database_path('data/pap_2026_sous_produits.php');
        $data = array_merge($data, $sousProduits);
        $rows = [];
        $ordre = 0;

        foreach ($data as $line) {
            $rows[] = $this->normalizeRow([
                'code' => (string) $line['code'],
                'libelle' => (string) $line['libelle'],
                'niveau' => (string) $line['niveau'],
                'parent_code' => $line['parent'] !== null ? (string) $line['parent'] : '',
                'montant_total' => ((float) $line['total']) * 1000,
                'montant_ceeac' => ((float) $line['ceeac']) * 1000,
                'montant_ptf' => ((float) $line['ptf']) * 1000,
                'ordre' => $ordre++,
                'source' => 'PAP 2026',
            ]);
        }

        return $rows;
    }

    /**
     * Convertit les lignes brutes d'un fichier Excel/CSV (format modele) en lignes normalisees.
     *
     * @param  array<int, array<int, mixed>>  $rawRows
     * @return array<int, array<string, mixed>>
     */
    public function parseSpreadsheet(array $rawRows): array
    {
        if ($rawRows === []) {
            return [];
        }

        $headers = $this->normalizeHeaders(array_shift($rawRows));
        $rows = [];
        $ordre = 0;

        foreach ($rawRows as $rawRow) {
            $mapped = [];
            foreach ($headers as $index => $key) {
                $mapped[$key] = trim((string) ($rawRow[$index] ?? ''));
            }

            if (($mapped['code'] ?? '') === '' && ($mapped['libelle'] ?? '') === '') {
                continue;
            }

            $rows[] = $this->normalizeRow([
                'code' => $mapped['code'] ?? '',
                'libelle' => $mapped['libelle'] ?? '',
                'niveau' => Str::of($mapped['niveau'] ?? '')->lower()->replace([' ', '-'], '_')->value(),
                'parent_code' => $mapped['parent_code'] ?? '',
                'montant_total' => $this->parseAmount($mapped['montant_total'] ?? ''),
                'montant_ceeac' => $this->parseAmount($mapped['montant_ceeac'] ?? ''),
                'montant_ptf' => $this->parseAmount($mapped['montant_ptf'] ?? ''),
                'ordre' => $ordre++,
                'source' => $mapped['source'] !== '' ? ($mapped['source'] ?? 'PAP') : 'PAP',
                '_line' => $ordre + 1,
            ]);
        }

        return $rows;
    }

    /**
     * Valide et classe les anomalies par severite.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>> Anomalies classees.
     */
    public function validate(array $rows): array
    {
        $issues = [];
        $seenCodes = [];
        $codesByNiveau = [];

        foreach ($rows as $row) {
            $codesByNiveau[$row['niveau']][$row['code']] = true;
        }

        foreach ($rows as $row) {
            $line = $row['_line'] ?? ($row['ordre'] + 2);
            $code = $row['code'];

            if ($code === '') {
                $issues[] = $this->issue('bloquant', $line, 'code', $code, 'Le code est obligatoire.', 'Renseigner un code unique.');

                continue;
            }

            if (($row['libelle'] ?? '') === '') {
                $issues[] = $this->issue('bloquant', $line, 'libelle', $code, 'Le libelle est obligatoire.', 'Renseigner un libelle.');
            }

            if (! in_array($row['niveau'], self::NIVEAUX, true)) {
                $issues[] = $this->issue('bloquant', $line, 'niveau', $row['niveau'], 'Niveau invalide.', 'Utiliser : '.implode(', ', self::NIVEAUX));

                continue;
            }

            // Unicite des codes.
            if (isset($seenCodes[$code])) {
                $issues[] = $this->issue('avertissement', $line, 'code', $code, 'Code en doublon dans le fichier.', 'La ligne sera ignoree.');
            }
            $seenCodes[$code] = true;

            // Coherence hierarchique du parent.
            $parentNiveau = self::PARENT_NIVEAU[$row['niveau']] ?? null;
            if ($parentNiveau === null) {
                if ($row['parent_code'] !== '') {
                    $issues[] = $this->issue('avertissement', $line, 'parent_code', $row['parent_code'], 'Un pilier ne doit pas avoir de parent.', 'Laisser la colonne parent vide.');
                }
            } else {
                if ($row['parent_code'] === '') {
                    $issues[] = $this->issue('bloquant', $line, 'parent_code', $code, "Parent obligatoire pour un niveau {$row['niveau']}.", "Renseigner le code du {$parentNiveau} parent.");
                } elseif (! isset($codesByNiveau[$parentNiveau][$row['parent_code']])) {
                    $issues[] = $this->issue('bloquant', $line, 'parent_code', $row['parent_code'], "Parent {$parentNiveau} introuvable dans le fichier.", "Ajouter le {$parentNiveau} {$row['parent_code']} ou corriger le code parent.");
                }
            }

            // Montants negatifs.
            foreach (['montant_total', 'montant_ceeac', 'montant_ptf'] as $champ) {
                if ($row[$champ] < 0) {
                    $issues[] = $this->issue('bloquant', $line, $champ, (string) $row[$champ], 'Montant negatif non autorise.', 'Saisir un montant positif ou nul.');
                }
            }

            // Coherence CEEAC + PTF = TOTAL.
            $somme = round($row['montant_ceeac'] + $row['montant_ptf'], 2);
            if (abs($somme - round($row['montant_total'], 2)) > 0.01) {
                $issues[] = $this->issue(
                    'avertissement',
                    $line,
                    'montant_total',
                    (string) $row['montant_total'],
                    'Incoherence : CEEAC + PTF != TOTAL (CEEAC='.$this->fmt($row['montant_ceeac']).', PTF='.$this->fmt($row['montant_ptf']).', TOTAL='.$this->fmt($row['montant_total']).').',
                    'Verifier la ventilation CEEAC/PTF.'
                );
            }
        }

        // Coherence des sous-totaux (somme des enfants vs parent).
        $issues = array_merge($issues, $this->checkSubtotals($rows));

        return $issues;
    }

    /**
     * Controle de coherence : la somme des enfants doit egaler le montant du parent.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function checkSubtotals(array $rows): array
    {
        $issues = [];
        $byCode = [];
        $childrenTotals = [];

        foreach ($rows as $row) {
            $byCode[$row['code']] = $row;
            if ($row['parent_code'] !== '') {
                $childrenTotals[$row['parent_code']] = ($childrenTotals[$row['parent_code']] ?? 0) + $row['montant_total'];
            }
        }

        foreach ($childrenTotals as $parentCode => $sommeEnfants) {
            if (! isset($byCode[$parentCode])) {
                continue;
            }
            $parent = $byCode[$parentCode];
            if (abs(round($sommeEnfants, 2) - round($parent['montant_total'], 2)) > 0.01) {
                $issues[] = $this->issue(
                    'avertissement',
                    $parent['_line'] ?? ($parent['ordre'] + 2),
                    'montant_total',
                    (string) $parent['montant_total'],
                    "Incoherence de sous-total sur {$parentCode} : somme des enfants = ".$this->fmt($sommeEnfants).' vs total declare = '.$this->fmt($parent['montant_total']).'.',
                    'Verifier les montants des lignes enfants (incoherence presente dans le document source).'
                );
            }
        }

        return $issues;
    }

    /**
     * Synthese chiffree d'un ensemble de lignes (montant total = somme des piliers).
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, int|float>
     */
    public function summarize(array $rows): array
    {
        $counts = ['pilier' => 0, 'axe' => 0, 'produit' => 0, 'sous_produit' => 0];
        $montantTotal = 0.0;

        foreach ($rows as $row) {
            if (isset($counts[$row['niveau']])) {
                $counts[$row['niveau']]++;
            }
            if ($row['niveau'] === 'pilier') {
                $montantTotal += $row['montant_total'];
            }
        }

        return [
            'nb_piliers' => $counts['pilier'],
            'nb_axes' => $counts['axe'],
            'nb_produits' => $counts['produit'],
            'nb_sous_produits' => $counts['sous_produit'],
            'nb_lignes' => count($rows),
            'montant_total' => $montantTotal,
        ];
    }

    /**
     * Calcule les creations / mises a jour previsionnelles par rapport a la base.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{nb_creations:int, nb_mises_a_jour:int}
     */
    public function diffAgainstDb(array $rows, ExerciceBudgetaire $exercice): array
    {
        $existing = PapLigneBudgetaire::query()
            ->where('exercice_budgetaire_id', $exercice->id)
            ->pluck('code')
            ->flip();

        $creations = 0;
        $updates = 0;
        $seen = [];

        foreach ($rows as $row) {
            if ($row['code'] === '' || isset($seen[$row['code']])) {
                continue;
            }
            $seen[$row['code']] = true;
            if ($existing->has($row['code'])) {
                $updates++;
            } else {
                $creations++;
            }
        }

        return ['nb_creations' => $creations, 'nb_mises_a_jour' => $updates];
    }

    /**
     * Execute (ou simule) un import PAP de maniere atomique et tracee.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $meta  fichier_original, chemin_fichier, checksum, taille, source_label, version
     */
    public function run(array $rows, ExerciceBudgetaire $exercice, string $mode, User $user, array $meta = []): PapImport
    {
        if (! in_array($mode, self::MODES, true)) {
            throw new \InvalidArgumentException("Mode d'import inconnu : {$mode}");
        }

        $startedAt = now();
        $version = (string) ($meta['version'] ?? '1.0');

        $issues = $this->validate($rows);
        $blocking = array_values(array_filter($issues, fn ($i) => $i['severite'] === 'bloquant'));
        $warnings = array_values(array_filter($issues, fn ($i) => $i['severite'] === 'avertissement'));
        $summary = $this->summarize($rows);
        $diff = $this->diffAgainstDb($rows, $exercice);

        $import = new PapImport([
            'exercice_budgetaire_id' => $exercice->id,
            'version' => $version,
            'mode' => $mode,
            'statut' => 'en_cours',
            'fichier_original' => $meta['fichier_original'] ?? null,
            'chemin_fichier' => $meta['chemin_fichier'] ?? null,
            'checksum' => $meta['checksum'] ?? null,
            'taille' => $meta['taille'] ?? null,
            'source_label' => $meta['source_label'] ?? 'Document PAP 2026',
            'nb_piliers' => $summary['nb_piliers'],
            'nb_axes' => $summary['nb_axes'],
            'nb_produits' => $summary['nb_produits'],
            'nb_sous_produits' => $summary['nb_sous_produits'],
            'nb_lignes' => $summary['nb_lignes'],
            'nb_avertissements' => count($warnings),
            'nb_erreurs' => count($blocking),
            'montant_total' => $summary['montant_total'],
            'rapport' => $issues,
            'resume' => $summary,
            'user_id' => $user->id,
            'adresse_ip' => $meta['adresse_ip'] ?? null,
            'user_agent' => $meta['user_agent'] ?? null,
            'started_at' => $startedAt,
        ]);

        // Anomalies bloquantes : on n'ecrit rien.
        if ($blocking !== []) {
            $import->statut = 'echoue';
            $import->finished_at = now();
            $import->duree_ms = (int) $startedAt->diffInMilliseconds(now());
            $import->save();

            return $import;
        }

        // Mode simulation : aucune ecriture de donnees PAP, on previsualise.
        if ($mode === 'simulation') {
            $import->statut = 'simule';
            $import->nb_creations = $diff['nb_creations'];
            $import->nb_mises_a_jour = $diff['nb_mises_a_jour'];
            $import->finished_at = now();
            $import->duree_ms = (int) $startedAt->diffInMilliseconds(now());
            $import->save();

            return $import;
        }

        $cadre = $this->resolveCadreLogique($exercice, $version, $user);
        $import->cadre_logique_id = $cadre->id;

        try {
            DB::transaction(function () use ($rows, $exercice, $cadre, $mode, $user, $import): void {
                $suppressions = 0;

                if ($mode === 'remplacement') {
                    $anciennes = PapLigneBudgetaire::query()
                        ->where('exercice_budgetaire_id', $exercice->id)
                        ->get();
                    $import->snapshot = $anciennes->toJson();
                    $suppressions = $anciennes->count();
                    PapLigneBudgetaire::query()
                        ->where('exercice_budgetaire_id', $exercice->id)
                        ->forceDelete();
                }

                $result = $this->persistRows($rows, $exercice, $cadre, $mode, $user);

                $import->nb_creations = $result['creations'];
                $import->nb_mises_a_jour = $result['updates'];
                $import->nb_doublons = $result['doublons'];
                $import->nb_suppressions = $suppressions;
                $import->statut = 'valide';
                $import->finished_at = now();
                $import->duree_ms = (int) $import->started_at->diffInMilliseconds(now());
                $import->save();

                JournalAudit::query()->create([
                    'user_id' => $user->id,
                    'action' => 'import_pap',
                    'entite' => 'pap_import',
                    'entite_id' => $import->id,
                    'anciennes_valeurs' => $mode === 'remplacement' ? ['lignes_supprimees' => $suppressions] : null,
                    'nouvelles_valeurs' => [
                        'mode' => $mode,
                        'exercice' => $exercice->annee,
                        'version' => $import->version,
                        'creations' => $result['creations'],
                        'mises_a_jour' => $result['updates'],
                        'montant_total' => $import->montant_total,
                    ],
                    'adresse_ip' => $import->adresse_ip,
                    'user_agent' => $import->user_agent,
                    'created_at' => now(),
                ]);
            });
        } catch (Throwable $e) {
            $import->statut = 'echoue';
            $import->finished_at = now();
            $import->duree_ms = (int) $startedAt->diffInMilliseconds(now());
            $import->rapport = array_merge($issues, [
                $this->issue('bloquant', 0, '-', '-', 'Echec technique : '.$e->getMessage(), 'Import annule (transaction restauree).'),
            ]);
            $import->nb_erreurs = $import->nb_erreurs + 1;
            $import->save();

            throw $e;
        }

        BudgetJournal::record($user, "Import PAP {$exercice->annee} ({$mode})", null, [
            'creations' => $import->nb_creations,
            'mises_a_jour' => $import->nb_mises_a_jour,
            'suppressions' => $import->nb_suppressions,
            'montant_total' => $import->montant_total,
        ]);

        return $import;
    }

    /**
     * Restaure les lignes PAP a partir du snapshot d'un import de remplacement.
     */
    public function restore(PapImport $source, User $user): PapImport
    {
        $snapshot = $source->decodedSnapshot();
        $exercice = $source->exerciceBudgetaire;

        $restore = new PapImport([
            'exercice_budgetaire_id' => $source->exercice_budgetaire_id,
            'cadre_logique_id' => $source->cadre_logique_id,
            'version' => $source->version,
            'mode' => 'remplacement',
            'statut' => 'en_cours',
            'source_label' => "Restauration de l'import #{$source->id}",
            'restored_from_id' => $source->id,
            'user_id' => $user->id,
            'started_at' => now(),
        ]);

        DB::transaction(function () use ($snapshot, $exercice, $restore, $user, $source): void {
            $supprimees = PapLigneBudgetaire::query()
                ->where('exercice_budgetaire_id', $exercice->id)
                ->get();
            $restore->snapshot = $supprimees->toJson();
            PapLigneBudgetaire::query()
                ->where('exercice_budgetaire_id', $exercice->id)
                ->forceDelete();

            $count = 0;
            foreach ($snapshot as $attrs) {
                unset($attrs['id'], $attrs['created_at'], $attrs['updated_at'], $attrs['deleted_at']);
                $attrs['updated_by'] = $user->id;
                PapLigneBudgetaire::query()->create($attrs);
                $count++;
            }

            $restore->nb_suppressions = $supprimees->count();
            $restore->nb_creations = $count;
            $restore->nb_lignes = $count;
            $restore->statut = 'valide';
            $restore->finished_at = now();
            $restore->duree_ms = (int) $restore->started_at->diffInMilliseconds(now());
            $restore->save();

            JournalAudit::query()->create([
                'user_id' => $user->id,
                'action' => 'restauration_pap',
                'entite' => 'pap_import',
                'entite_id' => $restore->id,
                'anciennes_valeurs' => ['import_source' => $source->id],
                'nouvelles_valeurs' => ['lignes_restaurees' => $count],
                'adresse_ip' => null,
                'user_agent' => null,
                'created_at' => now(),
            ]);
        });

        return $restore;
    }

    /**
     * Persiste les lignes (noeuds RBM + lignes budgetaires PAP).
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{creations:int, updates:int, doublons:int}
     */
    private function persistRows(array $rows, ExerciceBudgetaire $exercice, CadreLogique $cadre, string $mode, User $user): array
    {
        // Tri parents-avant-enfants.
        usort($rows, function ($a, $b) {
            $na = self::ORDRE_NIVEAU[$a['niveau']] ?? 99;
            $nb = self::ORDRE_NIVEAU[$b['niveau']] ?? 99;

            return $na <=> $nb ?: strcmp($a['code'], $b['code']);
        });

        /** @var array<string, Model> $nodeByCode */
        $nodeByCode = [];
        $creations = 0;
        $updates = 0;
        $doublons = 0;
        $seen = [];

        foreach ($rows as $row) {
            if (isset($seen[$row['code']])) {
                $doublons++;

                continue;
            }
            $seen[$row['code']] = true;

            $node = $this->upsertRbmNode($row, $nodeByCode, $user);
            $nodeByCode[$row['code']] = $node;

            $link = $this->nodeLink($row['niveau'], $node);

            $existing = PapLigneBudgetaire::query()
                ->where('exercice_budgetaire_id', $exercice->id)
                ->where('code', $row['code'])
                ->first();

            if ($existing !== null && $mode === 'creation') {
                $doublons++;

                continue;
            }

            $values = array_merge($link, [
                'cadre_logique_id' => $cadre->id,
                'libelle' => $row['libelle'],
                'niveau' => $row['niveau'],
                'parent_code' => $row['parent_code'] !== '' ? $row['parent_code'] : null,
                'montant_total' => $row['montant_total'],
                'montant_ceeac' => $row['montant_ceeac'],
                'montant_ptf' => $row['montant_ptf'],
                'ordre' => $row['ordre'],
                'source' => $row['source'],
                'updated_by' => $user->id,
            ]);

            if ($existing !== null) {
                $existing->update($values);
                $updates++;
            } else {
                PapLigneBudgetaire::query()->create(array_merge($values, [
                    'exercice_budgetaire_id' => $exercice->id,
                    'code' => $row['code'],
                    'created_by' => $user->id,
                ]));
                $creations++;
            }
        }

        return ['creations' => $creations, 'updates' => $updates, 'doublons' => $doublons];
    }

    /**
     * Upsert d'un noeud RBM (pilier/axe/produit/sous_produit) selon son niveau.
     *
     * @param  array<string, mixed>  $row
     * @param  array<string, Model>  $nodeByCode
     */
    private function upsertRbmNode(array $row, array $nodeByCode, User $user): Model
    {
        $parent = $row['parent_code'] !== '' ? ($nodeByCode[$row['parent_code']] ?? null) : null;

        return match ($row['niveau']) {
            'pilier' => $this->upsertReferential(Pilier::class, ['code' => $row['code']], [
                'libelle' => $row['libelle'], 'ordre' => $row['ordre'], 'statut' => 'actif',
            ], $user),
            'axe' => $this->upsertReferential(AxeStrategique::class, ['pilier_id' => $parent?->id, 'code' => $row['code']], [
                'libelle' => $row['libelle'], 'ordre' => $row['ordre'], 'statut' => 'actif',
            ], $user),
            'produit' => $this->upsertReferential(Produit::class, ['axe_strategique_id' => $parent?->id, 'code' => $row['code']], [
                'libelle' => $row['libelle'], 'ordre' => $row['ordre'], 'statut' => 'actif',
            ], $user),
            'sous_produit' => $this->upsertReferential(SousProduit::class, ['produit_id' => $parent?->id, 'code' => $row['code']], [
                'libelle' => $row['libelle'], 'ordre' => $row['ordre'], 'statut' => 'actif',
            ], $user),
            default => throw new \InvalidArgumentException("Niveau inconnu : {$row['niveau']}"),
        };
    }

    /**
     * @return array<string, int|null>
     */
    private function nodeLink(string $niveau, Model $node): array
    {
        $link = ['pilier_id' => null, 'axe_strategique_id' => null, 'produit_id' => null, 'sous_produit_id' => null];
        $link[match ($niveau) {
            'pilier' => 'pilier_id',
            'axe' => 'axe_strategique_id',
            'produit' => 'produit_id',
            'sous_produit' => 'sous_produit_id',
        }] = $node->id;

        return $link;
    }

    private function resolveCadreLogique(ExerciceBudgetaire $exercice, string $version, User $user): CadreLogique
    {
        $code = "PAP-{$exercice->annee}-v{$version}";

        return $this->upsertReferential(CadreLogique::class, ['code' => $code], [
            'libelle' => "Plan Annuel de Performance {$exercice->annee} (v{$version})",
            'annee' => $exercice->annee,
            'version' => $version,
            'statut' => 'actif',
        ], $user);
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $keys
     * @param  array<string, mixed>  $values
     */
    private function upsertReferential(string $modelClass, array $keys, array $values, User $user): Model
    {
        $model = $modelClass::query()->updateOrCreate($keys, array_merge($values, ['updated_by' => $user->id]));

        if ($model->wasRecentlyCreated) {
            $model->forceFill(['created_by' => $user->id])->save();
        }

        return $model;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeRow(array $row): array
    {
        return [
            'code' => trim((string) ($row['code'] ?? '')),
            'libelle' => trim((string) ($row['libelle'] ?? '')),
            'niveau' => (string) ($row['niveau'] ?? ''),
            'parent_code' => trim((string) ($row['parent_code'] ?? '')),
            'montant_total' => (float) ($row['montant_total'] ?? 0),
            'montant_ceeac' => (float) ($row['montant_ceeac'] ?? 0),
            'montant_ptf' => (float) ($row['montant_ptf'] ?? 0),
            'ordre' => (int) ($row['ordre'] ?? 0),
            'source' => (string) ($row['source'] ?? 'PAP'),
            '_line' => $row['_line'] ?? ((int) ($row['ordre'] ?? 0) + 2),
        ];
    }

    /**
     * @param  array<int, mixed>  $headerRow
     * @return array<int, string>
     */
    private function normalizeHeaders(array $headerRow): array
    {
        $aliases = [
            'code' => 'code',
            'libelle' => 'libelle',
            'niveau' => 'niveau',
            'code parent' => 'parent_code',
            'parent' => 'parent_code',
            'montant total' => 'montant_total',
            'total' => 'montant_total',
            'montant ceeac' => 'montant_ceeac',
            'ceeac' => 'montant_ceeac',
            'montant ptf' => 'montant_ptf',
            'ptf' => 'montant_ptf',
            'source' => 'source',
        ];

        $headers = [];
        foreach ($headerRow as $header) {
            $normalized = Str::ascii(mb_strtolower(trim((string) $header)));
            $headers[] = $aliases[$normalized] ?? Str::snake($normalized);
        }

        return $headers;
    }

    private function parseAmount(string $value): float
    {
        $clean = preg_replace('/[^0-9.\-]/', '', str_replace([' ', ','], ['', '.'], $value));

        return $clean === '' || $clean === '-' ? 0.0 : (float) $clean;
    }

    /**
     * @return array<string, mixed>
     */
    private function issue(string $severite, int $ligne, string $colonne, string $valeur, string $regle, string $correction): array
    {
        return [
            'severite' => $severite,
            'feuille' => 'Lignes PAP',
            'ligne' => $ligne,
            'colonne' => $colonne,
            'valeur' => $valeur,
            'regle' => $regle,
            'correction' => $correction,
        ];
    }

    private function fmt(float $montant): string
    {
        return number_format($montant, 0, '.', ' ');
    }
}
