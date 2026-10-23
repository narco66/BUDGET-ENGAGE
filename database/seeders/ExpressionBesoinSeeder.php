<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeder NON DESTRUCTIF du module Expression des Besoins.
 *
 * Crée les permissions expression_besoins.* (firstOrCreate) et les octroie de
 * façon additive (givePermissionTo) aux rôles existants — jamais syncPermissions.
 */
class ExpressionBesoinSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'expression_besoins.view',
            'expression_besoins.create',
            'expression_besoins.update',
            'expression_besoins.submit',
            'expression_besoins.validate',
            'expression_besoins.reject',
            'expression_besoins.convert_engagement',
            'expression_besoins.export',
            'expression_besoins.admin',
        ];

        foreach ($permissions as $name) {
            Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $grants = [
            'administrateur_general' => $permissions,

            'administrateur_budget' => [
                'expression_besoins.view', 'expression_besoins.create', 'expression_besoins.update',
                'expression_besoins.submit', 'expression_besoins.validate',
                'expression_besoins.convert_engagement', 'expression_besoins.export', 'expression_besoins.admin',
            ],

            // Initiateurs de besoins.
            'agent_initiateur' => [
                'expression_besoins.view', 'expression_besoins.create',
                'expression_besoins.update', 'expression_besoins.submit',
            ],

            // Circuit de validation hiérarchique.
            'responsable_service' => ['expression_besoins.view', 'expression_besoins.validate', 'expression_besoins.reject'],
            'responsable_direction' => ['expression_besoins.view', 'expression_besoins.create', 'expression_besoins.validate', 'expression_besoins.reject'],
            'responsable_departement' => ['expression_besoins.view', 'expression_besoins.validate', 'expression_besoins.reject'],
            'controleur_budgetaire' => ['expression_besoins.view', 'expression_besoins.validate', 'expression_besoins.reject', 'expression_besoins.export'],

            // Conversion en engagement (ordonnateur / comptable).
            'ordonnateur' => ['expression_besoins.view', 'expression_besoins.validate', 'expression_besoins.convert_engagement'],
            'comptable' => ['expression_besoins.view', 'expression_besoins.convert_engagement'],

            'auditeur' => ['expression_besoins.view', 'expression_besoins.export'],
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
