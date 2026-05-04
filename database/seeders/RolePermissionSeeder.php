<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'users.manage',
            'roles.manage',
            'referentials.manage',
            'exercises.manage',
            'budget.import',
            'engagements.create',
            'engagements.submit',
            'workflow.validate_service',
            'workflow.validate_direction',
            'workflow.validate_department',
            'workflow.budget_control',
            'workflow.financial_control',
            'workflow.ordonnateur',
            'workflow.ordonnateur_delegue',
            'execution.liquidation',
            'execution.payment',
            'reports.view',
            'reports.view_limited',
            'audit.view',
            'audit.alerts.manage',
            'parameters.manage',
        ];

        foreach ($permissions as $name) {
            Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $matrix = [
            'administrateur_general' => $permissions,
            'administrateur_budget' => [
                'referentials.manage',
                'exercises.manage',
                'budget.import',
                'engagements.create',
                'engagements.submit',
                'workflow.budget_control',
                'reports.view',
                'audit.view',
                'parameters.manage',
            ],
            'agent_initiateur' => [
                'engagements.create',
                'engagements.submit',
                'reports.view_limited',
            ],
            'responsable_direction' => [
                'workflow.validate_direction',
                'engagements.create',
                'reports.view',
            ],
            'responsable_departement' => [
                'workflow.validate_department',
                'reports.view',
            ],
            'controleur_budgetaire' => [
                'workflow.budget_control',
                'reports.view',
                'audit.view',
            ],
            'controleur_financier' => [
                'workflow.financial_control',
                'reports.view',
                'audit.view',
            ],
            'comptable' => [
                'execution.liquidation',
                'execution.payment',
                'reports.view',
            ],
            'ordonnateur' => [
                'workflow.ordonnateur',
                'workflow.ordonnateur_delegue',
                'reports.view',
            ],
            'auditeur' => [
                'reports.view',
                'audit.view',
            ],
        ];

        foreach ($matrix as $roleName => $perms) {
            $role = Role::query()->firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms);
        }
    }
}
