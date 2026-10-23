<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            GedSeeder::class,
            ExpressionBesoinSeeder::class,
            EngagementModuleSeeder::class,
            EngagementTypeSeeder::class,
            LiquidationTypeSeeder::class,
            OrdonnancementTypeSeeder::class,
            PaiementTypeSeeder::class,
            PaiementBanqueSeeder::class,
            ParametresControleSeeder::class,
            CeeacDemoSeeder::class,
        ]);
    }
}
