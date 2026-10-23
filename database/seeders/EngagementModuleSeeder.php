<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeder NON DESTRUCTIF du module Engagement (montée ERP).
 *
 * Crée les permissions granulaires engagement.* et les octroie de façon
 * additive (givePermissionTo) aux rôles existants. Les permissions historiques
 * engagements.* (pluriel) restent inchangées et continuent de gouverner le flux
 * existant.
 */
class EngagementModuleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'engagement.view',
            'engagement.create',
            'engagement.generate',
            'engagement.update',
            'engagement.validate',
            'engagement.reject',
            'engagement.cancel',
            'engagement.reserve_budget',
            'engagement.transfer_liquidation',
            'engagement.export',
            'engagement.admin',
        ];

        foreach ($permissions as $name) {
            Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $grants = [
            'administrateur_general' => $permissions,
            'administrateur_budget' => [
                'engagement.view', 'engagement.create', 'engagement.generate', 'engagement.update',
                'engagement.validate', 'engagement.reserve_budget', 'engagement.transfer_liquidation',
                'engagement.export', 'engagement.admin',
            ],
            'agent_initiateur' => ['engagement.view', 'engagement.create', 'engagement.update'],
            'responsable_service' => ['engagement.view', 'engagement.validate', 'engagement.reject'],
            'responsable_direction' => ['engagement.view', 'engagement.create', 'engagement.validate', 'engagement.reject'],
            'responsable_departement' => ['engagement.view', 'engagement.validate', 'engagement.reject'],
            'controleur_budgetaire' => ['engagement.view', 'engagement.validate', 'engagement.reject', 'engagement.reserve_budget', 'engagement.export'],
            'controleur_financier' => ['engagement.view', 'engagement.validate', 'engagement.reject'],
            'ordonnateur' => ['engagement.view', 'engagement.validate', 'engagement.reserve_budget', 'engagement.transfer_liquidation', 'engagement.cancel'],
            'comptable' => ['engagement.view', 'engagement.transfer_liquidation', 'engagement.export'],
            'auditeur' => ['engagement.view', 'engagement.export'],
        ];

        foreach ($grants as $roleName => $perms) {
            $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role !== null) {
                $role->givePermissionTo($perms);
            }
        }

        $this->syncLegacyPermissions();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Aligne les permissions historiques (engagements.*) et granulaires (engagement.*).
     */
    private function syncLegacyPermissions(): void
    {
        $roles = Role::query()->where('guard_name', 'web')->get();

        foreach ($roles as $role) {
            if ($role->hasPermissionTo('engagement.create') && ! $role->hasPermissionTo('engagements.create')) {
                $role->givePermissionTo('engagements.create');
            }
            if ($role->hasPermissionTo('engagements.create') && ! $role->hasPermissionTo('engagement.view')) {
                $role->givePermissionTo('engagement.view');
            }
            if ($role->hasPermissionTo('engagement.generate') && ! $role->hasPermissionTo('engagements.submit')) {
                $role->givePermissionTo('engagements.submit');
            }
            if ($role->hasPermissionTo('engagements.submit') && ! $role->hasPermissionTo('engagement.generate')) {
                $role->givePermissionTo('engagement.generate');
            }
        }
    }
}
