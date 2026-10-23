<?php

namespace App\Services\Budget;

use App\Models\BudgetImport;
use App\Models\BudgetLigne;
use App\Models\ExerciceBudgetaire;
use App\Models\JournalAudit;
use App\Models\PapLigneBudgetaire;
use App\Models\User;
use App\Support\BudgetJournal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class BudgetImportService
{
    public const MODES = ['simulation', 'creation', 'mise_a_jour', 'fusion', 'remplacement'];

    public const SECTIONS = ['recette', 'depense'];

    public const NIVEAUX = ['titre', 'chapitre', 'article', 'paragraphe', 'ligne'];

    private const ORDRE_NIVEAU = ['titre' => 1, 'chapitre' => 2, 'article' => 3, 'paragraphe' => 4, 'ligne' => 5];

    /**
     * Montant officiel des depenses liees au PAP (entete du document) pour le rapprochement.
     */
    public const MONTANT_PAP = 22_365_281_000;

    /**
     * Construit les lignes normalisees a partir du jeu de donnees officiel Budget 2026 (FCFA).
     *
     * @return array<int, array<string, mixed>>
     */
    public function rowsFromDataset(): array
    {
        $data = require database_path('data/budget_2026.php');
        $rows = [];
        $ordre = 0;

        foreach ($data as $line) {
            $rows[] = $this->normalizeRow([
                'section' => (string) $line['section'],
                'niveau' => (string) $line['niveau'],
                'categorie' => $line['categorie'] ?? null,
                'code' => (string) $line['code'],
                'parent_code' => $line['parent'] !== null ? (string) $line['parent'] : '',
                'libelle' => (string) $line['libelle'],
                'montant_total' => (float) $line['total'],
                'montant_ceeac' => (float) $line['ceeac'],
                'montant_ptf' => (float) $line['ptf'],
                'montant_anterieur' => $line['montant_anterieur'] ?? null,
                'montant_realise' => $line['montant_realise'] ?? null,
                'montant_ptf_anterieur' => $line['montant_ptf_anterieur'] ?? null,
                'montant_ptf_realise' => $line['montant_ptf_realise'] ?? null,
                'taux_realisation' => $line['taux_realisation'] ?? null,
                'variation' => $line['variation'] ?? null,
                'ordre' => $ordre++,
                'source' => 'Budget 2026',
            ]);
        }

        return $rows;
    }

    /**
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
                'section' => Str::of($mapped['section'] ?? '')->lower()->value(),
                'niveau' => Str::of($mapped['niveau'] ?? '')->lower()->value(),
                'categorie' => $mapped['categorie'] ?? null,
                'code' => $mapped['code'] ?? '',
                'parent_code' => $mapped['parent_code'] ?? '',
                'libelle' => $mapped['libelle'] ?? '',
                'montant_total' => $this->parseAmount($mapped['montant_total'] ?? ''),
                'montant_ceeac' => $this->parseAmount($mapped['montant_ceeac'] ?? ''),
                'montant_ptf' => $this->parseAmount($mapped['montant_ptf'] ?? ''),
                'ordre' => $ordre++,
                'source' => ($mapped['source'] ?? '') !== '' ? $mapped['source'] : 'Budget',
                '_line' => $ordre + 1,
            ]);
        }

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    public function validate(array $rows): array
    {
        $issues = [];
        $seenCodes = [];
        $byCode = [];

        foreach ($rows as $row) {
            $byCode[$row['code']] = $row;
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
            if (! in_array($row['section'], self::SECTIONS, true)) {
                $issues[] = $this->issue('bloquant', $line, 'section', $row['section'], 'Section invalide.', 'Utiliser : '.implode(', ', self::SECTIONS));
            }
            if (! in_array($row['niveau'], self::NIVEAUX, true)) {
                $issues[] = $this->issue('bloquant', $line, 'niveau', $row['niveau'], 'Niveau invalide.', 'Utiliser : '.implode(', ', self::NIVEAUX));

                continue;
            }

            if (isset($seenCodes[$code])) {
                $issues[] = $this->issue('avertissement', $line, 'code', $code, 'Code en doublon dans le fichier.', 'La ligne sera ignoree.');
            }
            $seenCodes[$code] = true;

            // Coherence hierarchique : parent obligatoire sauf titre ; niveau parent superieur.
            if ($row['niveau'] === 'titre') {
                if ($row['parent_code'] !== '') {
                    $issues[] = $this->issue('avertissement', $line, 'parent_code', $row['parent_code'], 'Un titre ne doit pas avoir de parent.', 'Laisser la colonne parent vide.');
                }
            } else {
                if ($row['parent_code'] === '') {
                    $issues[] = $this->issue('bloquant', $line, 'parent_code', $code, "Parent obligatoire pour un niveau {$row['niveau']}.", 'Renseigner le code parent.');
                } elseif (! isset($byCode[$row['parent_code']])) {
                    $issues[] = $this->issue('bloquant', $line, 'parent_code', $row['parent_code'], 'Parent introuvable dans le fichier.', "Ajouter le parent {$row['parent_code']} ou corriger le code.");
                } else {
                    $parent = $byCode[$row['parent_code']];
                    $op = self::ORDRE_NIVEAU[$parent['niveau']] ?? 99;
                    $oc = self::ORDRE_NIVEAU[$row['niveau']] ?? 0;
                    if ($op >= $oc) {
                        $issues[] = $this->issue('majeur', $line, 'parent_code', $row['parent_code'], "Niveau parent ({$parent['niveau']}) incompatible avec {$row['niveau']}.", 'Le parent doit etre d\'un niveau superieur.');
                    }
                }
            }

            foreach (['montant_total', 'montant_ceeac', 'montant_ptf'] as $champ) {
                if ($row[$champ] < 0) {
                    $issues[] = $this->issue('bloquant', $line, $champ, (string) $row[$champ], 'Montant negatif non autorise.', 'Saisir un montant positif ou nul.');
                }
            }

            $somme = round($row['montant_ceeac'] + $row['montant_ptf'], 2);
            if (abs($somme - round($row['montant_total'], 2)) > 0.5) {
                $issues[] = $this->issue('avertissement', $line, 'montant_total', (string) $row['montant_total'],
                    'CEEAC + PTF != TOTAL (CEEAC='.$this->fmt($row['montant_ceeac']).', PTF='.$this->fmt($row['montant_ptf']).', TOTAL='.$this->fmt($row['montant_total']).').',
                    'Verifier la ventilation CEEAC/PTF.');
            }
        }

        return array_merge($issues, $this->checkSubtotals($rows));
    }

    /**
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

        foreach ($childrenTotals as $parentCode => $somme) {
            if (! isset($byCode[$parentCode])) {
                continue;
            }
            $parent = $byCode[$parentCode];
            if (abs(round($somme, 2) - round($parent['montant_total'], 2)) > 0.5) {
                $issues[] = $this->issue('avertissement', $parent['_line'] ?? ($parent['ordre'] + 2), 'montant_total', (string) $parent['montant_total'],
                    "Incoherence de sous-total sur {$parentCode} : somme des enfants = ".$this->fmt($somme).' vs total declare = '.$this->fmt($parent['montant_total']).'.',
                    'Verifier les montants des lignes enfants (incoherence presente dans le document source).');
            }
        }

        return $issues;
    }

    /**
     * Rapprochement avec le PAP importe : compare la ligne budgetaire PAP a la somme des piliers PAP.
     *
     * @return array<int, array<string, mixed>>
     */
    public function rapprochementPap(array $rows, ExerciceBudgetaire $exercice): array
    {
        $issues = [];
        $papRow = collect($rows)->firstWhere('categorie', 'pap');
        $papTotal = (float) PapLigneBudgetaire::query()
            ->where('exercice_budgetaire_id', $exercice->id)
            ->where('niveau', 'pilier')
            ->sum('montant_total');

        if ($papTotal <= 0) {
            $issues[] = $this->issue('information', 0, 'pap', '-', 'Aucun PAP importe pour cet exercice.', 'Importer le PAP pour activer le rapprochement budget/PAP.');

            return $issues;
        }

        $budgetPap = $papRow !== null ? (float) $papRow['montant_total'] : 0.0;
        if (abs($budgetPap - $papTotal) > 0.5) {
            $issues[] = $this->issue('avertissement', $papRow['_line'] ?? 0, 'montant_total', (string) $budgetPap,
                'Ecart budget/PAP : ligne PAP du budget = '.$this->fmt($budgetPap).' vs total du PAP importe = '.$this->fmt($papTotal).'.',
                'Verifier la coherence entre le Budget et le PAP.');
        } else {
            $issues[] = $this->issue('information', $papRow['_line'] ?? 0, 'montant_total', (string) $budgetPap,
                'Rapprochement PAP OK : '.$this->fmt($papTotal).' FCFA (budget = PAP).', '-');
        }

        return $issues;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, int|float>
     */
    public function summarize(array $rows): array
    {
        $counts = ['titre' => 0, 'chapitre' => 0, 'article' => 0, 'paragraphe' => 0, 'ligne' => 0];
        $nbRecettes = 0;
        $nbDepenses = 0;
        $montantRecettes = 0.0;
        $montantDepenses = 0.0;
        $ceeac = 0.0;
        $ptf = 0.0;

        foreach ($rows as $row) {
            if (isset($counts[$row['niveau']])) {
                $counts[$row['niveau']]++;
            }
            $row['section'] === 'recette' ? $nbRecettes++ : $nbDepenses++;

            if ($row['niveau'] === 'titre') {
                if ($row['section'] === 'recette') {
                    $montantRecettes += $row['montant_total'];
                } else {
                    $montantDepenses += $row['montant_total'];
                    $ceeac += $row['montant_ceeac'];
                    $ptf += $row['montant_ptf'];
                }
            }
        }

        return [
            'nb_titres' => $counts['titre'],
            'nb_chapitres' => $counts['chapitre'],
            'nb_articles' => $counts['article'],
            'nb_paragraphes' => $counts['paragraphe'],
            'nb_lignes' => $counts['ligne'],
            'nb_recettes' => $nbRecettes,
            'nb_depenses' => $nbDepenses,
            'nb_total' => count($rows),
            'montant_recettes' => $montantRecettes,
            'montant_depenses' => $montantDepenses,
            'montant_ceeac' => $ceeac,
            'montant_ptf' => $ptf,
            'ecart_equilibre' => $montantRecettes - $montantDepenses,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{nb_creations:int, nb_mises_a_jour:int}
     */
    public function diffAgainstDb(array $rows, ExerciceBudgetaire $exercice): array
    {
        $existing = BudgetLigne::query()->where('exercice_budgetaire_id', $exercice->id)->pluck('code')->flip();
        $creations = 0;
        $updates = 0;
        $seen = [];
        foreach ($rows as $row) {
            if ($row['code'] === '' || isset($seen[$row['code']])) {
                continue;
            }
            $seen[$row['code']] = true;
            $existing->has($row['code']) ? $updates++ : $creations++;
        }

        return ['nb_creations' => $creations, 'nb_mises_a_jour' => $updates];
    }

    /**
     * Execute (ou simule) un import budgetaire de maniere atomique et tracee.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $meta
     */
    public function run(array $rows, ExerciceBudgetaire $exercice, string $mode, User $user, array $meta = []): BudgetImport
    {
        if (! in_array($mode, self::MODES, true)) {
            throw new \InvalidArgumentException("Mode d'import inconnu : {$mode}");
        }

        $startedAt = now();
        $version = (string) ($meta['version'] ?? '1.0');

        $issues = array_merge($this->validate($rows), $this->rapprochementPap($rows, $exercice));
        $blocking = array_values(array_filter($issues, fn ($i) => $i['severite'] === 'bloquant'));
        $warnings = array_values(array_filter($issues, fn ($i) => in_array($i['severite'], ['avertissement', 'majeur'], true)));
        $summary = $this->summarize($rows);
        $diff = $this->diffAgainstDb($rows, $exercice);

        $import = new BudgetImport([
            'exercice_budgetaire_id' => $exercice->id,
            'version' => $version,
            'mode' => $mode,
            'statut' => 'en_cours',
            'fichier_original' => $meta['fichier_original'] ?? null,
            'chemin_fichier' => $meta['chemin_fichier'] ?? null,
            'checksum' => $meta['checksum'] ?? null,
            'taille' => $meta['taille'] ?? null,
            'source_label' => $meta['source_label'] ?? 'Document Budget 2026',
            'nb_titres' => $summary['nb_titres'],
            'nb_chapitres' => $summary['nb_chapitres'],
            'nb_articles' => $summary['nb_articles'],
            'nb_paragraphes' => $summary['nb_paragraphes'],
            'nb_lignes' => $summary['nb_lignes'],
            'nb_recettes' => $summary['nb_recettes'],
            'nb_depenses' => $summary['nb_depenses'],
            'nb_avertissements' => count($warnings),
            'nb_erreurs' => count($blocking),
            'montant_recettes' => $summary['montant_recettes'],
            'montant_depenses' => $summary['montant_depenses'],
            'montant_ceeac' => $summary['montant_ceeac'],
            'montant_ptf' => $summary['montant_ptf'],
            'rapport' => $issues,
            'resume' => $summary,
            'user_id' => $user->id,
            'adresse_ip' => $meta['adresse_ip'] ?? null,
            'user_agent' => $meta['user_agent'] ?? null,
            'started_at' => $startedAt,
        ]);

        if ($blocking !== []) {
            $import->statut = 'echoue';
            $import->finished_at = now();
            $import->duree_ms = (int) $startedAt->diffInMilliseconds(now());
            $import->save();

            return $import;
        }

        if ($mode === 'simulation') {
            $import->statut = 'simule';
            $import->nb_creations = $diff['nb_creations'];
            $import->nb_mises_a_jour = $diff['nb_mises_a_jour'];
            $import->finished_at = now();
            $import->duree_ms = (int) $startedAt->diffInMilliseconds(now());
            $import->save();

            return $import;
        }

        try {
            DB::transaction(function () use ($rows, $exercice, $mode, $version, $user, $import): void {
                $suppressions = 0;
                if ($mode === 'remplacement') {
                    $anciennes = BudgetLigne::query()->where('exercice_budgetaire_id', $exercice->id)->get();
                    $import->snapshot = $anciennes->toJson();
                    $suppressions = $anciennes->count();
                    BudgetLigne::query()->where('exercice_budgetaire_id', $exercice->id)->forceDelete();
                }

                $result = $this->persistRows($rows, $exercice, $version, $mode, $user);

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
                    'action' => 'import_budget',
                    'entite' => 'budget_import',
                    'entite_id' => $import->id,
                    'anciennes_valeurs' => $mode === 'remplacement' ? ['lignes_supprimees' => $suppressions] : null,
                    'nouvelles_valeurs' => [
                        'mode' => $mode,
                        'exercice' => $exercice->annee,
                        'version' => $version,
                        'creations' => $result['creations'],
                        'mises_a_jour' => $result['updates'],
                        'montant_recettes' => $import->montant_recettes,
                        'montant_depenses' => $import->montant_depenses,
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

        BudgetJournal::record($user, "Import Budget {$exercice->annee} ({$mode})", null, [
            'creations' => $import->nb_creations,
            'mises_a_jour' => $import->nb_mises_a_jour,
            'suppressions' => $import->nb_suppressions,
            'montant_depenses' => $import->montant_depenses,
        ]);

        return $import;
    }

    public function restore(BudgetImport $source, User $user): BudgetImport
    {
        $snapshot = $source->decodedSnapshot();
        $exercice = $source->exerciceBudgetaire;

        $restore = new BudgetImport([
            'exercice_budgetaire_id' => $source->exercice_budgetaire_id,
            'version' => $source->version,
            'mode' => 'remplacement',
            'statut' => 'en_cours',
            'source_label' => "Restauration de l'import #{$source->id}",
            'restored_from_id' => $source->id,
            'user_id' => $user->id,
            'started_at' => now(),
        ]);

        DB::transaction(function () use ($snapshot, $exercice, $restore, $user, $source): void {
            $supprimees = BudgetLigne::query()->where('exercice_budgetaire_id', $exercice->id)->get();
            $restore->snapshot = $supprimees->toJson();
            BudgetLigne::query()->where('exercice_budgetaire_id', $exercice->id)->forceDelete();

            $count = 0;
            foreach ($snapshot as $attrs) {
                unset($attrs['id'], $attrs['created_at'], $attrs['updated_at'], $attrs['deleted_at']);
                $attrs['updated_by'] = $user->id;
                BudgetLigne::query()->create($attrs);
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
                'action' => 'restauration_budget',
                'entite' => 'budget_import',
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
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{creations:int, updates:int, doublons:int}
     */
    private function persistRows(array $rows, ExerciceBudgetaire $exercice, string $version, string $mode, User $user): array
    {
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

            $existing = BudgetLigne::query()
                ->where('exercice_budgetaire_id', $exercice->id)
                ->where('code', $row['code'])
                ->first();

            if ($existing !== null && $mode === 'creation') {
                $doublons++;

                continue;
            }

            $values = [
                'version' => $version,
                'section' => $row['section'],
                'niveau' => $row['niveau'],
                'categorie' => $row['categorie'],
                'libelle' => $row['libelle'],
                'parent_code' => $row['parent_code'] !== '' ? $row['parent_code'] : null,
                'montant_total' => $row['montant_total'],
                'montant_ceeac' => $row['montant_ceeac'],
                'montant_ptf' => $row['montant_ptf'],
                'montant_anterieur' => $row['montant_anterieur'],
                'montant_realise' => $row['montant_realise'],
                'montant_ptf_anterieur' => $row['montant_ptf_anterieur'],
                'montant_ptf_realise' => $row['montant_ptf_realise'],
                'taux_realisation' => $row['taux_realisation'],
                'variation' => $row['variation'],
                'ordre' => $row['ordre'],
                'source' => $row['source'],
                'updated_by' => $user->id,
            ];

            if ($existing !== null) {
                $existing->update($values);
                $updates++;
            } else {
                BudgetLigne::query()->create(array_merge($values, [
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
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeRow(array $row): array
    {
        return [
            'section' => (string) ($row['section'] ?? ''),
            'niveau' => (string) ($row['niveau'] ?? ''),
            'categorie' => $row['categorie'] !== null && $row['categorie'] !== '' ? (string) $row['categorie'] : null,
            'code' => trim((string) ($row['code'] ?? '')),
            'parent_code' => trim((string) ($row['parent_code'] ?? '')),
            'libelle' => trim((string) ($row['libelle'] ?? '')),
            'montant_total' => (float) ($row['montant_total'] ?? 0),
            'montant_ceeac' => (float) ($row['montant_ceeac'] ?? 0),
            'montant_ptf' => (float) ($row['montant_ptf'] ?? 0),
            'montant_anterieur' => isset($row['montant_anterieur']) ? (float) $row['montant_anterieur'] : null,
            'montant_realise' => isset($row['montant_realise']) ? (float) $row['montant_realise'] : null,
            'montant_ptf_anterieur' => isset($row['montant_ptf_anterieur']) ? (float) $row['montant_ptf_anterieur'] : null,
            'montant_ptf_realise' => isset($row['montant_ptf_realise']) ? (float) $row['montant_ptf_realise'] : null,
            'taux_realisation' => isset($row['taux_realisation']) ? (float) $row['taux_realisation'] : null,
            'variation' => isset($row['variation']) ? (float) $row['variation'] : null,
            'ordre' => (int) ($row['ordre'] ?? 0),
            'source' => (string) ($row['source'] ?? 'Budget'),
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
            'section' => 'section',
            'niveau' => 'niveau',
            'categorie' => 'categorie',
            'code' => 'code',
            'code parent' => 'parent_code',
            'parent' => 'parent_code',
            'libelle' => 'libelle',
            'montant total' => 'montant_total',
            'total' => 'montant_total',
            'montant ceeac' => 'montant_ceeac',
            'ceeac' => 'montant_ceeac',
            'ceeac-em' => 'montant_ceeac',
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
            'feuille' => 'Lignes Budget',
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
