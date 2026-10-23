<?php

namespace Database\Seeders;

use App\Models\PaiementBanque;
use Illuminate\Database\Seeder;

class PaiementBanqueSeeder extends Seeder
{
    public function run(): void
    {
        $banques = [
            ['code' => 'BEAC-XAF', 'libelle' => 'Compte principal BEAC', 'banque' => 'BEAC', 'titulaire' => 'CEEAC', 'iban' => 'CM2110002000000123456789012', 'rib' => '10002 00000 01234567890 12', 'bic_swift' => 'BEACCMCX', 'devise' => 'XAF', 'plafond_decaissement' => 5_000_000_000],
            ['code' => 'CITI-USD', 'libelle' => 'Compte opérations USD', 'banque' => 'Citibank', 'titulaire' => 'CEEAC', 'iban' => 'US64CITI0000001234567890', 'bic_swift' => 'CITIUS33', 'devise' => 'USD', 'plafond_decaissement' => 10_000_000],
            ['code' => 'BGFI-EUR', 'libelle' => 'Compte partenaires EUR', 'banque' => 'BGFIBank', 'titulaire' => 'CEEAC', 'iban' => 'GA2140002000005678901234567', 'bic_swift' => 'BGFIGALX', 'devise' => 'EUR', 'plafond_decaissement' => 8_000_000],
        ];

        foreach ($banques as $ordre => $banque) {
            PaiementBanque::query()->updateOrCreate(
                ['code' => $banque['code']],
                array_merge($banque, ['ordre' => $ordre, 'est_actif' => true]),
            );
        }
    }
}
