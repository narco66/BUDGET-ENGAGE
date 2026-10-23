<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeder NON DESTRUCTIF du module GED.
 *
 * - Crée les permissions ged.* (firstOrCreate : idempotent).
 * - Crée le rôle dédié « gestionnaire_ged ».
 * - Octroie (sans révoquer) les permissions GED aux rôles existants concernés.
 *
 * N'utilise jamais syncPermissions sur un rôle existant (ce qui écraserait
 * ses permissions actuelles) : uniquement givePermissionTo (additif).
 */
class GedSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $gedPermissions = [
            'ged.view',
            'ged.create',
            'ged.update',
            'ged.delete',
            'ged.archive',
            'ged.restore',
            'ged.download',
            'ged.preview',
            'ged.share',
            'ged.sign',
            'ged.validate',
            'ged.reject',
            'ged.publish',
            'ged.search',
            'ged.export',
            'ged.admin',
        ];

        foreach ($gedPermissions as $name) {
            Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Rôle dédié à la gestion documentaire : accès complet GED.
        $gestionnaire = Role::query()->firstOrCreate(['name' => 'gestionnaire_ged', 'guard_name' => 'web']);
        $gestionnaire->givePermissionTo($gedPermissions);

        // Octroi additif aux rôles existants (sans toucher à leurs autres permissions).
        $grants = [
            // L'administrateur général dispose de tout le périmètre GED.
            'administrateur_general' => $gedPermissions,

            // L'administrateur budget gère et alimente la GED.
            'administrateur_budget' => [
                'ged.view', 'ged.create', 'ged.update', 'ged.download', 'ged.preview',
                'ged.share', 'ged.search', 'ged.export', 'ged.archive', 'ged.publish',
            ],

            // Profils de validation / signature dans la chaîne documentaire.
            'controleur_budgetaire' => ['ged.view', 'ged.download', 'ged.preview', 'ged.search', 'ged.validate', 'ged.reject'],
            'controleur_financier' => ['ged.view', 'ged.download', 'ged.preview', 'ged.search', 'ged.validate', 'ged.reject'],
            'ordonnateur' => ['ged.view', 'ged.download', 'ged.preview', 'ged.search', 'ged.sign', 'ged.validate'],

            // Saisie / dépôt de pièces.
            'comptable' => ['ged.view', 'ged.create', 'ged.update', 'ged.download', 'ged.preview', 'ged.search'],
            'agent_initiateur' => ['ged.view', 'ged.create', 'ged.download', 'ged.preview', 'ged.search'],

            // Consultation / contrôle.
            'auditeur' => ['ged.view', 'ged.download', 'ged.preview', 'ged.search', 'ged.export'],
        ];

        foreach ($grants as $roleName => $perms) {
            $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role !== null) {
                $role->givePermissionTo($perms);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
